<?php

namespace backend\components;

use Yii;
use yii\db\Expression;
use yii\db\Query;
use backend\components\AgrimacData as D;
use backend\components\AgrimacRepo as R;
use backend\components\AgrimacValidationException as Invalid;

/**
 * Ghi dữ liệu AgriMac. Mỗi thao tác (op) chạy trong một transaction, kiểm quyền theo vai trò,
 * validate phía server và trả dữ liệu mới theo định dạng AgrimacRepo.
 */
class AgrimacService
{
    /** op => [method, trang cần quyền truy cập, vai trò được phép (null = mọi vai trò vào được trang)] */
    const OPS = [
        'product.save'         => ['saveProduct', ['products', 'inventory'], null],
        'category.save'        => ['saveCategory', ['products'], null],
        'bom.save'             => ['saveBom', ['products'], ['admin', 'kt_xuatkho', 'sale']],
        'part.save'            => ['savePart', ['inventory'], null],
        'stock.import'         => ['stockImport', ['inventory'], null],
        'stock.export'         => ['stockExport', ['inventory'], null],
        'supplier.save'        => ['saveSupplier', ['suppliers', 'inventory'], null],
        'supplier.return'      => ['createReturn', ['suppliers'], null],
        'supplier.returnNext'  => ['nextReturnStatus', ['suppliers'], null],
        'dealer.save'          => ['saveDealer', ['dealers'], ['admin', 'sale']],
        'dealer.collect'       => ['collectPayment', ['dealers'], ['admin', 'kt_congno']],
        'lead.save'            => ['saveLead', ['crm'], null],
        'lead.stage'           => ['setLeadStage', ['crm'], null],
        'lead.note'            => ['addLeadNote', ['crm'], null],
        'order.create'         => ['createOrder', ['orders'], ['admin', 'sale', 'kt_banhang']],
        'order.approve'        => ['approveOrder', ['orders'], ['admin', 'kt_banhang']],
        'order.bom'            => ['saveOrderBom', ['orders'], ['admin', 'sale']],
        'order.send'           => ['sendToAssembly', ['orders'], ['admin', 'sale']],
        'order.pick'           => ['pickPart', ['assembly'], ['admin', 'assembly', 'kt_xuatkho']],
        'order.assemblyNote'   => ['saveAssemblyNote', ['assembly'], ['admin', 'assembly']],
        'order.assembled'      => ['finishAssembly', ['orders', 'assembly'], ['admin', 'assembly']],
        'order.export'         => ['exportOrder', ['orders'], ['admin', 'kt_xuatkho']],
        'order.deliver'        => ['deliverOrder', ['orders'], ['admin', 'delivery']],
        'order.cancel'         => ['cancelOrder', ['orders'], ['admin', 'kt_banhang', 'sale']],
        'market.status'        => ['setMarketOrderStatus', ['orders'], ['admin']],
        'market.refund'        => ['processMarketRefund', ['orders'], ['admin']],
        'warranty.activate'    => ['activateWarranty', ['warranty'], null],
        'claim.save'           => ['saveClaim', ['warranty'], null],
        'commission.pay'       => ['payCommission', ['accounting'], ['admin', 'kt_congno']],
        'user.create'          => ['createUser', ['users'], ['admin']],
        'user.permission'      => ['setPermission', ['users'], ['admin']],
    ];

    const IMAGE_PREFIX = '/uploads/images/product/';

    private $userId;
    private $role;
    private $db;

    public function __construct($userId, $role)
    {
        $this->userId = (int)$userId;
        $this->role = $role;
        $this->db = Yii::$app->db;
    }

    public function can($op)
    {
        if (!isset(self::OPS[$op])) {
            return false;
        }
        [, $pages, $roles] = self::OPS[$op];
        $pageOk = array_filter($pages, function ($p) {
            return D::canAccess($this->role, $p);
        });
        return $pageOk && ($roles === null || in_array($this->role, $roles, true));
    }

    /** @return array ['message' => string, 'data' => mixed] */
    public function run($op, array $data)
    {
        $tx = $this->db->beginTransaction();
        try {
            $result = $this->{self::OPS[$op][0]}($data);
            $tx->commit();
        } catch (\Throwable $e) {
            $tx->rollBack();
            throw $e;
        }
        R::flush();
        return $result;
    }

    /* ======================= Tiện ích ======================= */

    private function str(array $d, $key, $max = 255)
    {
        return mb_substr(trim((string)($d[$key] ?? '')), 0, $max);
    }

    private function int(array $d, $key)
    {
        $v = trim((string)($d[$key] ?? ''));
        return $v === '' ? null : (ctype_digit(ltrim($v, '-')) ? (int)$v : false);
    }

    private function require(array &$errors, array $d, array $fields)
    {
        foreach ($fields as $field => $label) {
            if (trim((string)($d[$field] ?? '')) === '') {
                $errors[$field] = $errors[$field] ?? 'Vui lòng nhập ' . $label;
            }
        }
    }

    private function positive(array &$errors, array $d, $key, $label, $min = 1)
    {
        $v = $this->int($d, $key);
        if ($v === false || $v === null || $v < $min) {
            $errors[$key] = $errors[$key] ?? $label . ' phải là số nguyên ≥ ' . $min;
            return null;
        }
        return $v;
    }

    private function fail(array $errors)
    {
        if ($errors) {
            throw new Invalid($errors);
        }
    }

    private static function isPhone($s)
    {
        return (bool)preg_match('/^0\d{9,10}$/', preg_replace('/[.\s-]/', '', (string)$s));
    }

    private function nextCode($table, $prefix, $len = 3)
    {
        $max = (new Query())->from($table)->where(['like', 'code', $prefix . '%', false])
            ->max(new Expression('CAST(SUBSTRING(code, ' . (strlen($prefix) + 1) . ') AS UNSIGNED)'), $this->db);
        return $prefix . str_pad((string)((int)$max + 1), $len, '0', STR_PAD_LEFT);
    }

    private function idByCode($table, $code, $extra = [])
    {
        $id = (new Query())->select('id')->from($table)->where(array_merge(['code' => (string)$code], $extra))->scalar($this->db);
        return $id ? (int)$id : null;
    }

    private function insert($table, array $row)
    {
        $this->db->createCommand()->insert($table, $row)->execute();
        return (int)$this->db->getLastInsertID();
    }

    private function update($table, array $row, $where)
    {
        return $this->db->createCommand()->update($table, $row, $where)->execute();
    }

    private function isoDate($value, $field, array &$errors, $allowFuture = false)
    {
        $value = trim((string)$value);
        $ts = $value !== '' ? strtotime($value) : false;
        if (!$ts || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            $errors[$field] = 'Ngày không hợp lệ';
            return null;
        }
        if (!$allowFuture && $value > date('Y-m-d')) {
            $errors[$field] = 'Ngày không được ở tương lai';
        }
        return $value;
    }

    private function employeeByName($name, $role = null)
    {
        foreach (R::staff() as $u) {
            if ($u['name'] === $name && $u['active'] && ($role === null || $u['role'] === $role)) {
                return $u['dbId'];
            }
        }
        return null;
    }

    /* ======================= Sản phẩm & danh mục ======================= */

    private function saveProduct(array $d)
    {
        $code = $this->str($d, 'id', 30);
        $id = $code !== '' ? $this->idByCode('product', $code) : null;
        if ($code !== '' && !$id) {
            throw new Invalid(['name' => 'Sản phẩm không tồn tại']);
        }
        $errors = [];
        $this->require($errors, $d, ['name' => 'tên sản phẩm', 'catId' => 'danh mục', 'price' => 'giá bán']);
        $name = $this->str($d, 'name');
        if ($name !== '' && (new Query())->from('product')->where(['is_delete' => 0, 'name' => $name])->andFilterWhere(['<>', 'id', $id])->exists($this->db)) {
            $errors['name'] = 'Tên sản phẩm đã tồn tại';
        }
        $catId = (int)($d['catId'] ?? 0);
        if ($catId && !(new Query())->from('category')->where(['id' => $catId, 'is_delete' => 0])->exists($this->db)) {
            $errors['catId'] = 'Danh mục không hợp lệ';
        }
        $source = $d['sourceType'] ?? 'import';
        if (!isset(D::SOURCE_TYPES[$source])) {
            $errors['sourceType'] = 'Nguồn hàng không hợp lệ';
        }
        $supplierId = !empty($d['supId']) ? $this->idByCode('supplier', $d['supId']) : null;
        if (!empty($d['supId']) && !$supplierId) {
            $errors['supId'] = 'Nhà cung cấp không hợp lệ';
        }
        if (!$id && $source === 'import' && !$supplierId) {
            $errors['supId'] = 'Hàng nhập nguyên chiếc cần chọn nhà cung cấp';
        }
        $price = $this->positive($errors, $d, 'price', 'Giá bán');
        $nums = [];
        foreach (['cost' => 'Giá vốn', 'hp' => 'Công suất', 'weight' => 'Trọng lượng', 'minStock' => 'Tồn tối thiểu'] as $k => $label) {
            $v = $this->int($d, $k);
            if ($v === false || ($v !== null && $v < 0)) {
                $errors[$k] = $label . ' phải là số nguyên không âm';
            }
            $nums[$k] = $v === false ? null : $v;
        }
        $drive = in_array($d['drive'] ?? '', ['2WD', '4WD'], true) ? $d['drive'] : null;
        $images = array_values(array_filter((array)($d['images'] ?? []), function ($u) {
            return is_string($u) && strpos($u, self::IMAGE_PREFIX) === 0 && !preg_match('/[;\s]|\.\./', $u);
        }));
        if (count($images) > 20) {
            $errors['images'] = 'Tối đa 20 ảnh';
        }
        $this->fail($errors);

        $specs = implode(' · ', array_filter([$nums['hp'] ? $nums['hp'] . 'HP' : null, $drive, $nums['weight'] ? $nums['weight'] . 'kg' : null]));
        $row = [
            'name' => $name, 'category_id' => $catId, 'supplier_id' => $supplierId, 'source_type' => $source, 'price' => $price,
            'cost_price' => (int)$nums['cost'], 'horsepower' => $nums['hp'], 'drive_type' => $drive, 'weight' => $nums['weight'],
            'specs' => $specs ?: ($this->str($d, 'specs') ?: null), 'min_stock' => $nums['minStock'] === null ? 3 : $nums['minStock'],
            'description' => $this->str($d, 'description', 65000), 'image' => implode(';', $images),
        ];
        if ($id) {
            $this->update('product', $row, ['id' => $id]);
        } else {
            $newId = $this->insert('product', array_merge($row, ['quantity_in_stock' => 0, 'price_discount' => 0, 'status' => 1, 'is_delete' => 0]));
            $code = 'SP' . str_pad((string)$newId, 5, '0', STR_PAD_LEFT);
            $this->update('product', ['code' => $code], ['id' => $newId]);
        }
        return ['message' => ($id ? '✓ Đã cập nhật "' : '✓ Đã thêm sản phẩm "') . $name . '" (' . $code . ')', 'data' => [
            'product' => R::product($code), 'categories' => R::categories(),
        ]];
    }

    private function saveCategory(array $d)
    {
        $name = $this->str($d, 'name');
        if ($name === '') {
            throw new Invalid(['name' => 'Vui lòng nhập tên danh mục']);
        }
        $parentId = (int)($d['parentId'] ?? 0);
        if ($parentId && !(new Query())->from('category')->where(['id' => $parentId, 'parent_id' => 0, 'is_delete' => 0])->exists($this->db)) {
            throw new Invalid(['parentId' => 'Chuyên mục cha không hợp lệ']);
        }
        if ((new Query())->from('category')->where(['is_delete' => 0, 'name' => $name, 'parent_id' => $parentId])->exists($this->db)) {
            throw new Invalid(['name' => 'Danh mục đã tồn tại']);
        }
        $id = $this->insert('category', ['name' => $name, 'status' => 1, 'parent_id' => $parentId, 'is_delete' => 0]);
        return ['message' => '✓ Đã thêm danh mục "' . $name . '"', 'data' => ['id' => (string)$id, 'categories' => R::categories()]];
    }

    private function saveBom(array $d)
    {
        $productId = $this->idByCode('product', $d['productId'] ?? '');
        if (!$productId) {
            throw new Invalid(['updateCost' => 'Sản phẩm không tồn tại']);
        }
        $items = [];
        foreach ((array)($d['items'] ?? []) as $it) {
            $partId = $this->idByCode('part', $it['id'] ?? '');
            $qty = (int)($it['qty'] ?? 0);
            if (!$partId || $qty < 1) {
                throw new Invalid(['updateCost' => 'Linh kiện hoặc số lượng không hợp lệ']);
            }
            $items[$partId] = $qty;
        }
        if (!$items) {
            throw new Invalid(['updateCost' => 'BOM cần ít nhất 1 linh kiện']);
        }
        $this->db->createCommand()->delete('product_bom', ['product_id' => $productId])->execute();
        $cost = 0;
        $costs = (new Query())->select(['id', 'cost_price'])->from('part')->where(['id' => array_keys($items)])->indexBy('id')->column($this->db);
        foreach ($items as $partId => $qty) {
            $this->insert('product_bom', ['product_id' => $productId, 'part_id' => $partId, 'qty' => $qty]);
            $cost += $costs[$partId] * $qty;
        }
        if (!empty($d['updateCost'])) {
            $this->update('product', ['cost_price' => $cost], ['id' => $productId]);
        }
        return ['message' => '✓ Đã lưu BOM (' . count($items) . ' linh kiện · ' . D::money($cost) . '/máy)', 'data' => [
            'boms' => (object)R::defaultBoms(), 'product' => R::product($d['productId']),
        ]];
    }

    /* ======================= Kho ======================= */

    private function savePart(array $d)
    {
        $code = $this->str($d, 'id', 20);
        $id = $code !== '' ? $this->idByCode('part', $code) : null;
        $errors = [];
        $this->require($errors, $d, ['name' => 'tên linh kiện', 'unit' => 'đơn vị tính']);
        $name = $this->str($d, 'name');
        if ($name !== '' && (new Query())->from('part')->where(['name' => $name])->andFilterWhere(['<>', 'id', $id])->exists($this->db)) {
            $errors['name'] = 'Linh kiện đã tồn tại';
        }
        $newCat = $this->str($d, 'newCat', 100);
        $cat = $this->str($d, 'cat', 100);
        if ($newCat === '' && $cat === '') {
            $errors['cat'] = 'Chọn nhóm hoặc tạo nhóm mới';
        }
        if ($newCat !== '' && (new Query())->from('part_category')->where(['name' => $newCat])->exists($this->db)) {
            $errors['newCat'] = 'Nhóm đã tồn tại, hãy chọn ở trên';
        }
        $catId = $newCat === '' && $cat !== '' ? (new Query())->select('id')->from('part_category')->where(['name' => $cat])->scalar($this->db) : null;
        if ($newCat === '' && $cat !== '' && !$catId) {
            $errors['cat'] = 'Nhóm không hợp lệ';
        }
        $cost = $this->int($d, 'cost');
        if ($cost === false || $cost === null || $cost < 0) {
            $errors['cost'] = 'Giá vốn phải là số nguyên không âm';
        }
        $min = $this->int($d, 'minStock');
        if ($min === false || ($min !== null && $min < 0)) {
            $errors['minStock'] = 'Tồn tối thiểu phải là số nguyên không âm';
        }
        $supplierId = !empty($d['supId']) ? $this->idByCode('supplier', $d['supId']) : null;
        $this->fail($errors);

        if ($newCat !== '') {
            $catId = $this->insert('part_category', ['name' => $newCat, 'sort_order' => 99]);
        }
        $row = ['name' => $name, 'unit' => $this->str($d, 'unit', 20), 'category_id' => $catId, 'supplier_id' => $supplierId, 'cost_price' => $cost, 'min_stock' => (int)$min];
        if ($id) {
            $this->update('part', $row, ['id' => $id]);
        } else {
            $code = $this->nextCode('part', 'LK');
            $this->insert('part', array_merge($row, ['code' => $code, 'stock' => 0]));
        }
        return ['message' => ($id ? '✓ Đã cập nhật ' : '✓ Đã thêm linh kiện ') . $name . ' (' . $code . ')', 'data' => $this->inventoryData() + ['id' => $code]];
    }

    private function inventoryData()
    {
        return [
            'parts' => R::parts(), 'partCategories' => R::partCategories(), 'transactions' => R::stockTransactions(), 'suppliers' => R::suppliers(),
            'lowStock' => R::lowStockProducts(), 'stockValue' => R::stockValue(), 'stockChanged' => true,
        ];
    }

    private function itemRow($kind, $code)
    {
        if ($kind === 'part') {
            $row = (new Query())->from('part')->where(['code' => (string)$code])->one($this->db);
            return $row ? ['table' => 'part', 'id' => (int)$row['id'], 'name' => $row['name'], 'stock' => (int)$row['stock'], 'stockCol' => 'stock', 'cost' => (int)$row['cost_price']] : null;
        }
        $row = (new Query())->from('product')->where(['code' => (string)$code, 'is_delete' => 0])->one($this->db);
        return $row ? ['table' => 'product', 'id' => (int)$row['id'], 'name' => $row['name'], 'stock' => (int)$row['quantity_in_stock'], 'stockCol' => 'quantity_in_stock', 'cost' => (int)$row['cost_price']] : null;
    }

    /** Ghi phiếu kho và cập nhật tồn. $lines = [[kind, item row, qty, unit price], ...]; qty dương, chiều theo $direction (+1 nhập / -1 xuất). */
    private function writeVoucher($type, $reason, $direction, array $lines, array $extra = [])
    {
        $prefix = $direction > 0 ? 'NK' : 'XK';
        $code = $this->nextCode('stock_voucher', $prefix);
        $total = 0;
        foreach ($lines as [, , $qty, $price]) {
            $total += $qty * $price;
        }
        $voucherId = $this->insert('stock_voucher', array_merge([
            'code' => $code, 'type' => $type, 'reason' => $reason, 'total_amount' => $total, 'created_by' => $this->userId,
        ], $extra));
        foreach ($lines as [$kind, $item, $qty, $price]) {
            $after = $item['stock'] + $direction * $qty;
            if ($after < 0) {
                throw new Invalid(['qty' => 'Không đủ tồn kho "' . $item['name'] . '" (còn ' . $item['stock'] . ', cần ' . $qty . ')'], 'Không đủ tồn kho "' . $item['name'] . '"');
            }
            $this->update($item['table'], [$item['stockCol'] => $after], ['id' => $item['id']]);
            $this->insert('stock_voucher_item', ['voucher_id' => $voucherId, 'item_type' => $kind, 'item_id' => $item['id'], 'qty' => $qty, 'unit_price' => $price, 'stock_after' => $after]);
        }
        return ['id' => $voucherId, 'code' => $code];
    }

    private function stockImport(array $d)
    {
        $kind = ($d['kind'] ?? '') === 'part' ? 'part' : 'product';
        $errors = [];
        $item = $this->itemRow($kind, $d['itemId'] ?? '');
        if (!$item) {
            $errors['itemId'] = 'Vui lòng chọn hàng nhập';
        }
        $supplierId = !empty($d['supplierId']) ? $this->idByCode('supplier', $d['supplierId']) : null;
        $qty = $this->positive($errors, $d, 'qty', 'Số lượng');
        $price = $this->positive($errors, $d, 'price', 'Đơn giá');
        $this->fail($errors);

        $v = $this->writeVoucher('import', 'purchase', 1, [[$kind, $item, $qty, $price]], ['supplier_id' => $supplierId, 'note' => $this->str($d, 'note', 500) ?: null]);
        if ($kind === 'part') {
            $this->update('part', ['cost_price' => $price], ['id' => $item['id']]);
        }
        return ['message' => '✓ Nhập kho thành công: ' . $qty . ' × ' . $item['name'] . ' (' . $v['code'] . ')', 'data' => $this->inventoryData()];
    }

    private function stockExport(array $d)
    {
        $reasons = ['assembly_issue', 'warranty', 'sale', 'adjust', 'other'];
        $reason = in_array($d['reason'] ?? '', $reasons, true) ? $d['reason'] : null;
        $kind = ($d['kind'] ?? '') === 'product' ? 'product' : 'part';
        $errors = [];
        if (!$reason) {
            $errors['reason'] = 'Lý do xuất không hợp lệ';
        }
        $item = $this->itemRow($kind, $d['itemId'] ?? '');
        if (!$item) {
            $errors['itemId'] = 'Vui lòng chọn hàng xuất';
        }
        $qty = $this->positive($errors, $d, 'qty', 'Số lượng');
        if ($item && $qty && $qty > $item['stock']) {
            $errors['qty'] = 'Vượt tồn kho (' . $item['stock'] . ')';
        }
        $ref = $this->str($d, 'ref', 30);
        $orderId = $ref !== '' ? $this->idByCode('dealer_order', $ref) : null;
        if (in_array($reason, ['assembly_issue', 'sale'], true) && !$orderId) {
            $errors['ref'] = $ref === '' ? 'Cần nhập mã đơn hàng' : 'Không tìm thấy đơn ' . $ref;
        }
        $note = $this->str($d, 'note', 500);
        if (in_array($reason, ['adjust', 'other'], true) && $note === '') {
            $errors['note'] = 'Vui lòng ghi rõ lý do';
        }
        $this->fail($errors);

        $v = $this->writeVoucher($reason === 'adjust' ? 'adjust' : 'export', $reason === 'adjust' ? 'other' : $reason, -1,
            [[$kind, $item, $qty, $item['cost']]], ['order_id' => $orderId, 'note' => ($reason === 'adjust' ? '[Kiểm kê] ' : '') . $note ?: null]);
        return ['message' => '✓ Đã xuất ' . $qty . ' × ' . $item['name'] . ' (' . $v['code'] . ')', 'data' => $this->inventoryData()];
    }

    /* ======================= Nhà cung cấp ======================= */

    private function saveSupplier(array $d)
    {
        $code = $this->str($d, 'id', 20);
        $id = $code !== '' ? $this->idByCode('supplier', $code) : null;
        $errors = [];
        $this->require($errors, $d, ['name' => 'tên nhà cung cấp']);
        $name = $this->str($d, 'name');
        if ($name !== '' && (new Query())->from('supplier')->where(['name' => $name])->andFilterWhere(['<>', 'id', $id])->exists($this->db)) {
            $errors['name'] = 'Nhà cung cấp đã tồn tại';
        }
        $phone = $this->str($d, 'contact', 30);
        if ($phone !== '' && !preg_match('/^[\d.\s-]{8,15}$/', $phone)) {
            $errors['contact'] = 'Số điện thoại không hợp lệ';
        }
        $email = $this->str($d, 'email', 100);
        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Email không hợp lệ';
        }
        $tax = $this->str($d, 'taxCode', 20);
        if ($tax !== '' && !preg_match('/^\d{10}(-\d{3})?$/', $tax)) {
            $errors['taxCode'] = 'Mã số thuế gồm 10 số (hoặc 10 số-3 số)';
        }
        $this->fail($errors);

        $row = ['name' => $name, 'phone' => $phone ?: null, 'email' => $email ?: null, 'address' => $this->str($d, 'address', 300) ?: null, 'tax_code' => $tax ?: null];
        if ($id) {
            $this->update('supplier', $row, ['id' => $id]);
        } else {
            $code = $this->nextCode('supplier', 'NCC', 2);
            $this->insert('supplier', array_merge($row, ['code' => $code]));
        }
        return ['message' => ($id ? '✓ Đã cập nhật ' : '✓ Đã thêm nhà cung cấp ') . $name, 'data' => ['id' => $code, 'suppliers' => R::suppliers()]];
    }

    private function createReturn(array $d)
    {
        $supplierId = $this->idByCode('supplier', $d['supplierId'] ?? '');
        $kind = ($d['itemType'] ?? '') === 'part' ? 'part' : 'product';
        $errors = [];
        $item = $this->itemRow($kind, $d['itemId'] ?? '');
        if (!$supplierId) {
            $errors['itemId'] = 'Nhà cung cấp không hợp lệ';
        }
        if (!$item) {
            $errors['itemId'] = 'Vui lòng chọn hàng trả';
        }
        $qty = $this->positive($errors, $d, 'qty', 'Số lượng');
        if ($item && $qty && $qty > $item['stock']) {
            $errors['qty'] = 'Vượt tồn kho (' . $item['stock'] . ')';
        }
        $this->require($errors, $d, ['reason' => 'lý do trả hàng']);
        $this->fail($errors);

        $this->insert('supplier_return', [
            'supplier_id' => $supplierId, 'item_type' => $kind, 'item_id' => $item['id'], 'qty' => $qty,
            'reason' => $this->str($d, 'reason', 300), 'status' => 'pending', 'created_by' => $this->userId,
        ]);
        return ['message' => '✓ Đã tạo yêu cầu trả ' . $qty . ' × ' . $item['name'], 'data' => ['suppliers' => R::suppliers()]];
    }

    private function nextReturnStatus(array $d)
    {
        $id = (int)preg_replace('/\D/', '', (string)($d['id'] ?? ''));
        $r = (new Query())->from('supplier_return')->where(['id' => $id])->one($this->db);
        $next = ['pending' => 'sent', 'sent' => 'done'];
        if (!$r || !isset($next[$r['status']])) {
            throw new Invalid([], 'Yêu cầu trả hàng không thể chuyển trạng thái');
        }
        $row = ['status' => $next[$r['status']]];
        if ($r['status'] === 'pending') {
            $item = $r['item_type'] === 'part'
                ? $this->itemRow('part', (new Query())->select('code')->from('part')->where(['id' => $r['item_id']])->scalar($this->db))
                : $this->itemRow('product', (new Query())->select('code')->from('product')->where(['id' => $r['item_id']])->scalar($this->db));
            $v = $this->writeVoucher('return', 'other', -1, [[$r['item_type'], $item, (int)$r['qty'], $item['cost']]],
                ['supplier_id' => $r['supplier_id'], 'note' => 'Trả NCC: ' . $r['reason']]);
            $row['voucher_id'] = $v['id'];
        }
        $this->update('supplier_return', $row, ['id' => $id]);
        $labels = ['sent' => 'Đã gửi NCC (xuất kho trả hàng)', 'done' => 'NCC đã nhận'];
        return ['message' => '✓ ' . R::code('TH', $id) . ': ' . $labels[$row['status']], 'data' => ['suppliers' => R::suppliers()]];
    }

    /* ======================= Đại lý & công nợ ======================= */

    private function saveDealer(array $d)
    {
        $code = $this->str($d, 'id', 20);
        $id = $code !== '' ? $this->idByCode('dealer', $code) : null;
        $errors = [];
        $this->require($errors, $d, ['name' => 'tên đại lý', 'contact' => 'số điện thoại', 'province' => 'tỉnh / thành phố', 'limit' => 'hạn mức công nợ']);
        $name = $this->str($d, 'name');
        if ($name !== '' && (new Query())->from('dealer')->where(['name' => $name])->andFilterWhere(['<>', 'id', $id])->exists($this->db)) {
            $errors['name'] = 'Tên đại lý đã tồn tại';
        }
        if (!empty($d['contact']) && !self::isPhone($d['contact'])) {
            $errors['contact'] = 'Số điện thoại không hợp lệ';
        }
        $provinceId = (new Query())->select('id')->from('province')->where(['province_name' => (string)($d['province'] ?? '')])->scalar($this->db);
        if (!empty($d['province']) && !$provinceId) {
            $errors['province'] = 'Tỉnh / thành phố không hợp lệ';
        }
        $level = array_search($d['level'] ?? '', D::DEALER_LEVEL_CODES, true);
        if ($level === false) {
            $errors['level'] = 'Cấp đại lý không hợp lệ';
        }
        $tax = $this->str($d, 'taxCode', 20);
        if ($tax !== '' && !preg_match('/^\d{10}(-\d{3})?$/', $tax)) {
            $errors['taxCode'] = 'Mã số thuế gồm 10 số (hoặc 10 số-3 số)';
        }
        $limit = $this->int($d, 'limit');
        if ($limit === false || ($limit !== null && $limit < 0)) {
            $errors['limit'] = 'Hạn mức không hợp lệ';
        }
        $this->fail($errors);

        $row = [
            'name' => $name, 'contact_name' => $this->str($d, 'contactName') ?: null, 'phone' => $this->str($d, 'contact', 30),
            'province_id' => $provinceId, 'address' => $this->str($d, 'address', 300) ?: null, 'tax_code' => $tax ?: null,
            'level' => $level, 'credit_limit' => $limit,
        ];
        if ($id) {
            $this->update('dealer', $row, ['id' => $id]);
        } else {
            $code = $this->nextCode('dealer', 'DL');
            $this->insert('dealer', array_merge($row, ['code' => $code, 'sale_id' => $this->role === 'sale' ? $this->userId : null]));
        }
        return ['message' => ($id ? '✓ Đã cập nhật ' : '✓ Đã thêm ') . $name . ' (' . $code . ')', 'data' => ['dealer' => R::dealer($code)]];
    }

    private function collectPayment(array $d)
    {
        $dealer = (new Query())->from('dealer')->where(['code' => (string)($d['dealerId'] ?? '')])->one($this->db);
        if (!$dealer) {
            throw new Invalid(['amount' => 'Đại lý không tồn tại']);
        }
        $debt = (int)$dealer['current_debt'];
        $errors = [];
        $amount = $this->positive($errors, $d, 'amount', 'Số tiền');
        if ($amount && $amount > $debt) {
            $errors['amount'] = 'Không được thu vượt dư nợ (' . D::money($debt) . ')';
        }
        $method = in_array($d['method'] ?? '', ['Tiền mặt', 'Chuyển khoản'], true) ? $d['method'] : null;
        if (!$method) {
            $errors['method'] = 'Hình thức không hợp lệ';
        }
        $date = $this->isoDate($d['date'] ?? '', 'date', $errors);
        $this->fail($errors);

        $after = $debt - $amount;
        $this->insert('dealer_ledger', [
            'dealer_id' => $dealer['id'], 'type' => 'credit', 'amount' => $amount, 'balance_after' => $after, 'method' => $method,
            'note' => $this->str($d, 'note', 300) ?: null, 'created_by' => $this->userId, 'created_at' => $date . ' ' . date('H:i:s'),
        ]);
        $this->update('dealer', ['current_debt' => $after], ['id' => $dealer['id']]);
        return ['message' => '✓ Đã thu ' . D::money($amount) . ' — dư nợ còn ' . D::money($after), 'data' => ['dealer' => R::dealer($dealer['code'])]];
    }

    /* ======================= CRM ======================= */

    private function leadId($code)
    {
        $id = (int)preg_replace('/\D/', '', (string)$code);
        $query = (new Query())->from('crm_lead')->where(['id' => $id])->andFilterWhere(['sale_id' => AgrimacAuth::leadSaleScope()]);
        if (!$id || !$query->exists($this->db)) {
            throw new Invalid([], 'Khách tiềm năng không tồn tại hoặc không do bạn phụ trách');
        }
        return $id;
    }

    private function leadData($id)
    {
        return $this->find(R::leads(), R::code('L', $id));
    }

    private function saveLead(array $d)
    {
        $errors = [];
        $this->require($errors, $d, ['name' => 'tên khách / tổ chức', 'phone' => 'số điện thoại', 'product' => 'sản phẩm quan tâm', 'value' => 'giá trị dự kiến', 'sale' => 'Sale phụ trách']);
        $phone = $this->str($d, 'phone', 30);
        if ($phone !== '' && !self::isPhone($phone)) {
            $errors['phone'] = 'Số điện thoại không hợp lệ (10–11 số, bắt đầu bằng 0)';
        } elseif ($phone !== '') {
            $digits = preg_replace('/\D/', '', $phone);
            $exists = (new Query())->from('crm_lead')->where(new Expression("REPLACE(REPLACE(REPLACE(phone,'.',''),' ',''),'-','') = :p", [':p' => $digits]))->exists($this->db);
            if ($exists) {
                $errors['phone'] = 'Số điện thoại đã có trong pipeline';
            }
        }
        $productId = (new Query())->select('id')->from('product')->where(['is_delete' => 0])
            ->andWhere(['or', ['code' => (string)($d['product'] ?? '')], ['name' => (string)($d['product'] ?? '')]])->orderBy('id')->scalar($this->db);
        if (!empty($d['product']) && !$productId) {
            $errors['product'] = 'Sản phẩm không hợp lệ';
        }
        $dealerId = !empty($d['dealer']) ? $this->idByCode('dealer', $d['dealer']) : null;
        $saleId = AgrimacAuth::leadSaleScope() ?? $this->employeeByName($d['sale'] ?? '', 'sale');
        if (!empty($d['sale']) && !$saleId) {
            $errors['sale'] = 'Sale không hợp lệ';
        }
        $stage = array_search($d['stage'] ?? '', D::LEAD_STAGE_CODES, true);
        $value = $this->int($d, 'value');
        if ($value === false || ($value !== null && $value < 0)) {
            $errors['value'] = 'Giá trị không hợp lệ';
        }
        $this->fail($errors);

        $id = $this->insert('crm_lead', [
            'name' => $this->str($d, 'name'), 'phone' => $phone, 'dealer_id' => $dealerId, 'sale_id' => $saleId, 'product_id' => $productId,
            'expected_value' => (int)$value, 'stage' => $stage ?: 'approach',
        ]);
        $note = $this->str($d, 'note', 2000);
        if ($note !== '') {
            $this->insert('crm_lead_note', ['lead_id' => $id, 'content' => $note, 'created_by' => $this->userId]);
        }
        return ['message' => '✓ Đã thêm "' . $this->str($d, 'name') . '" vào giai đoạn ' . D::LEAD_STAGE_CODES[$stage ?: 'approach'], 'data' => ['lead' => $this->leadData($id)]];
    }

    private function setLeadStage(array $d)
    {
        $id = $this->leadId($d['id'] ?? '');
        $stage = array_search($d['stage'] ?? '', D::LEAD_STAGE_CODES, true);
        if ($stage === false) {
            throw new Invalid([], 'Giai đoạn không hợp lệ');
        }
        $this->update('crm_lead', ['stage' => $stage, 'updated_at' => date('Y-m-d H:i:s')], ['id' => $id]);
        return ['message' => '✓ Chuyển sang ' . D::LEAD_STAGE_CODES[$stage], 'data' => ['lead' => $this->leadData($id)]];
    }

    private function addLeadNote(array $d)
    {
        $id = $this->leadId($d['id'] ?? '');
        $text = $this->str($d, 'text', 2000);
        if ($text === '') {
            throw new Invalid([], 'Nội dung ghi chú trống');
        }
        $this->insert('crm_lead_note', ['lead_id' => $id, 'content' => $text, 'created_by' => $this->userId]);
        $this->update('crm_lead', ['updated_at' => date('Y-m-d H:i:s')], ['id' => $id]);
        return ['message' => '✓ Đã lưu ghi chú', 'data' => ['lead' => $this->leadData($id)]];
    }

    /* ======================= Đơn hàng ======================= */

    private function loadOrder($code, array $statuses)
    {
        $o = (new Query())->from('dealer_order')->where(['code' => (string)$code])->one($this->db);
        if (!$o) {
            throw new Invalid([], 'Đơn hàng không tồn tại');
        }
        if (!in_array($o['status'], $statuses, true)) {
            throw new Invalid([], 'Đơn ' . $o['code'] . ' đang ở trạng thái "' . D::ORDER_STATUS[$o['status']]['label'] . '", không thực hiện được thao tác này');
        }
        $o['item'] = (new Query())->from('dealer_order_item')->where(['order_id' => $o['id']])->one($this->db);
        return $o;
    }

    private function setOrderStatus(array $o, $to, array $row = [], $note = null)
    {
        $this->update('dealer_order', array_merge($row, ['status' => $to, 'updated_at' => date('Y-m-d H:i:s')]), ['id' => $o['id']]);
        $this->insert('dealer_order_log', ['order_id' => $o['id'], 'from_status' => $o['status'], 'to_status' => $to, 'employee_id' => $this->userId, 'note' => $note]);
    }

    private function orderResult($message, $code, $dealerCode = null)
    {
        $order = R::order($code);
        return ['message' => $message, 'data' => [
            'order' => $order, 'dealer' => R::dealer($dealerCode ?: $order['dealerId']), 'product' => R::product($order['productId']), 'parts' => R::parts(),
        ]];
    }

    private function createOrder(array $d)
    {
        $errors = [];
        $this->require($errors, $d, ['dealerId' => 'đại lý đặt hàng', 'product' => 'sản phẩm', 'qty' => 'số lượng', 'sale' => 'Sale phụ trách']);
        $type = ($d['type'] ?? '') === 'warranty' ? 'warranty' : 'new';
        $dealerId = $this->idByCode('dealer', $d['dealerId'] ?? '');
        if (!empty($d['dealerId']) && !$dealerId) {
            $errors['dealerId'] = 'Đại lý không hợp lệ';
        }
        $product = (new Query())->from('product')->where(['is_delete' => 0])
            ->andWhere(['or', ['code' => (string)($d['product'] ?? '')], ['name' => (string)($d['product'] ?? '')]])->one($this->db);
        if (!empty($d['product']) && !$product) {
            $errors['product'] = 'Sản phẩm không hợp lệ';
        }
        $qty = $this->positive($errors, $d, 'qty', 'Số lượng');
        $price = $this->int($d, 'price');
        if ($price === false || $price === null || $price < 0) {
            $errors['price'] = 'Đơn giá không hợp lệ';
        } elseif ($type === 'new' && $price === 0) {
            $errors['price'] = 'Đơn bán mới phải có giá bán lớn hơn 0';
        }
        if ($type === 'warranty') {
            $price = 0;
        }
        $saleId = $this->employeeByName($d['sale'] ?? '', 'sale');
        if (!empty($d['sale']) && !$saleId) {
            $errors['sale'] = 'Sale không hợp lệ';
        }
        $claimId = null;
        if ($type === 'warranty' && !empty($d['warrantyRef'])) {
            $claimId = $this->idByCode('warranty_claim', $d['warrantyRef']);
            if (!$claimId) {
                $errors['warrantyRef'] = 'Không tìm thấy phiếu khiếu nại ' . $d['warrantyRef'];
            }
        }
        $this->fail($errors);

        $code = $this->nextCode('dealer_order', 'DH' . date('Y'));
        $total = $qty * $price;
        $leadId = !empty($d['lead']) ? $this->leadId($d['lead']) : null;
        $id = $this->insert('dealer_order', [
            'code' => $code, 'type' => $type, 'dealer_id' => $dealerId, 'lead_id' => $leadId ?: null, 'claim_id' => $claimId, 'status' => 'pending',
            'total_amount' => $total, 'sale_id' => $saleId, 'note' => $this->str($d, 'note', 500) ?: null, 'ordered_at' => date('Y-m-d H:i:s'),
        ]);
        $this->insert('dealer_order_item', ['order_id' => $id, 'product_id' => $product['id'], 'qty' => $qty, 'unit_price' => $price, 'line_total' => $total]);
        $this->insert('dealer_order_log', ['order_id' => $id, 'to_status' => 'pending', 'employee_id' => $this->userId, 'note' => 'Tạo đơn']);
        if ($leadId) {
            $this->update('crm_lead', ['order_id' => $id, 'stage' => 'won', 'updated_at' => date('Y-m-d H:i:s')], ['id' => $leadId]);
        }
        if ($claimId) {
            $this->update('warranty_claim', ['order_id' => $id], ['id' => $claimId]);
        }
        return $this->orderResult('✓ Đã tạo đơn ' . $code . ' — chờ KT bán hàng duyệt', $code);
    }

    private function approveOrder(array $d)
    {
        $o = $this->loadOrder($d['id'] ?? '', ['pending']);
        $errors = [];
        $invoice = $this->str($d, 'invoiceNo', 20);
        $invoiceDate = null;
        if ($o['type'] === 'new') {
            if ($invoice === '') {
                $errors['invoiceNo'] = 'Vui lòng nhập số hoá đơn';
            }
            $invoiceDate = $this->isoDate($d['invoiceDate'] ?? '', 'invoiceDate', $errors);
        }
        if ($invoice !== '' && !preg_match('/^[A-Za-z0-9\/-]{3,20}$/', $invoice)) {
            $errors['invoiceNo'] = 'Số hoá đơn 3–20 ký tự, chỉ gồm chữ, số, / -';
        } elseif ($invoice !== '' && (new Query())->from('dealer_order')->where(['invoice_no' => $invoice])->andWhere(['<>', 'id', $o['id']])->exists($this->db)) {
            $errors['invoiceNo'] = 'Số hoá đơn đã dùng cho đơn khác';
        }
        $this->fail($errors);

        $dealer = (new Query())->from('dealer')->where(['id' => $o['dealer_id']])->one($this->db);
        $over = $o['type'] === 'new' && $dealer['current_debt'] + $o['total_amount'] > $dealer['credit_limit'];
        $this->setOrderStatus($o, 'confirmed', [
            'invoice_no' => $invoice ?: null, 'invoice_date' => $invoiceDate, 'approved_by' => $this->userId, 'note' => $this->str($d, 'note', 500) ?: null,
        ], $over ? 'Duyệt khi đại lý vượt hạn mức công nợ' : null);
        return $this->orderResult('✓ Đã duyệt đơn ' . $o['code'] . ($invoice ? ' · HĐ ' . $invoice : ''), $o['code']);
    }

    /** Ghi BOM của đơn (thay toàn bộ), giá vốn chốt theo giá linh kiện hiện tại. Trả về tổng giá vốn. */
    private function writeOrderBom(array $o, array $bom, $allowEmpty)
    {
        $items = [];
        foreach ($bom as $b) {
            $part = (new Query())->from('part')->where(['code' => (string)($b['id'] ?? '')])->one($this->db);
            $qty = (int)($b['qty'] ?? 0);
            if (!$part || $qty < 1) {
                throw new Invalid([], 'Linh kiện hoặc số lượng trong BOM không hợp lệ');
            }
            $items[$part['id']] = ['qty' => $qty, 'cost' => (int)$part['cost_price']];
        }
        if (!$items && !$allowEmpty) {
            throw new Invalid([], 'Đơn chưa có linh kiện — chọn BOM trước khi gửi lắp ráp');
        }
        $qtyOrder = (int)$o['item']['qty'];
        $this->db->createCommand()->delete('dealer_order_part', ['order_id' => $o['id']])->execute();
        $cost = 0;
        foreach ($items as $partId => $it) {
            $this->insert('dealer_order_part', [
                'order_id' => $o['id'], 'order_item_id' => $o['item']['id'], 'part_id' => $partId, 'qty_per_unit' => $it['qty'],
                'qty_total' => $it['qty'] * $qtyOrder, 'unit_cost' => $it['cost'],
            ]);
            $cost += $it['qty'] * $qtyOrder * $it['cost'];
        }
        return [$cost, count($items)];
    }

    private function saveOrderBom(array $d)
    {
        $o = $this->loadOrder($d['id'] ?? '', ['confirmed']);
        [$cost] = $this->writeOrderBom($o, (array)($d['bom'] ?? []), true);
        $this->update('dealer_order', ['total_cost' => $cost, 'updated_at' => date('Y-m-d H:i:s')], ['id' => $o['id']]);
        return ['message' => null, 'data' => ['order' => R::order($o['code'])]];
    }

    private function sendToAssembly(array $d)
    {
        $o = $this->loadOrder($d['id'] ?? '', ['confirmed']);
        [$cost, $count] = $this->writeOrderBom($o, (array)($d['bom'] ?? []), false);
        $this->setOrderStatus($o, 'assembling', ['total_cost' => $cost]);
        return $this->orderResult('✓ Đã gửi ' . $o['code'] . ' sang lắp ráp (' . $count . ' linh kiện)', $o['code']);
    }

    private function pickPart(array $d)
    {
        $o = $this->loadOrder($d['id'] ?? '', ['assembling']);
        $where = ['order_id' => $o['id']];
        if (empty($d['all'])) {
            $partId = $this->idByCode('part', $d['partId'] ?? '');
            if (!$partId) {
                throw new Invalid([], 'Linh kiện không thuộc đơn');
            }
            $where['part_id'] = $partId;
        }
        $picked = !empty($d['picked']) || !empty($d['all']);
        $this->update('dealer_order_part', ['is_picked' => $picked ? 1 : 0, 'picked_by' => $picked ? $this->userId : null, 'picked_at' => $picked ? date('Y-m-d H:i:s') : null], $where);
        return ['message' => null, 'data' => ['order' => R::order($o['code'])]];
    }

    private function saveAssemblyNote(array $d)
    {
        $o = $this->loadOrder($d['id'] ?? '', ['assembling', 'assembled']);
        $this->update('dealer_order', ['assembly_note' => $this->str($d, 'note', 5000) ?: null], ['id' => $o['id']]);
        return ['message' => null, 'data' => []];
    }

    private function orderParts(array $o)
    {
        return (new Query())->select(['op.*', 'pt.code', 'pt.name', 'pt.stock', 'pt.cost_price'])->from('dealer_order_part op')
            ->innerJoin('part pt', 'pt.id = op.part_id')->where(['op.order_id' => $o['id']])->all($this->db);
    }

    private function finishAssembly(array $d)
    {
        $o = $this->loadOrder($d['id'] ?? '', ['assembling']);
        $parts = $this->orderParts($o);
        $missing = array_filter($parts, function ($p) {
            return !$p['is_picked'];
        });
        if (!$parts || $missing) {
            throw new Invalid([], 'Chưa nhận đủ linh kiện (' . (count($parts) - count($missing)) . '/' . count($parts) . ') — bộ phận lắp ráp cần tick nhận đủ');
        }
        if ($o['type'] === 'new') {
            $lines = array_map(function ($p) {
                return ['part', ['table' => 'part', 'id' => (int)$p['part_id'], 'name' => $p['name'], 'stock' => (int)$p['stock'], 'stockCol' => 'stock'], (int)$p['qty_total'], (int)$p['unit_cost']];
            }, $parts);
            $this->writeVoucher('export', 'assembly_issue', -1, $lines, ['order_id' => $o['id'], 'note' => 'Xuất linh kiện lắp ráp ' . $o['code']]);
            $product = $this->itemRow('product', (new Query())->select('code')->from('product')->where(['id' => $o['item']['product_id']])->scalar($this->db));
            $perUnit = (int)round($o['total_cost'] / max(1, (int)$o['item']['qty']));
            $this->writeVoucher('import', 'assembly_finish', 1, [['product', $product, (int)$o['item']['qty'], $perUnit]], ['order_id' => $o['id'], 'note' => 'Nhập máy lắp xong ' . $o['code']]);
        }
        $this->setOrderStatus($o, 'assembled', ['assembler_id' => $this->userId]);
        return $this->orderResult('✓ ' . $o['code'] . ' lắp ráp xong' . ($o['type'] === 'new' ? ' — đã trừ linh kiện, nhập ' . $o['item']['qty'] . ' máy vào kho' : ''), $o['code']);
    }

    private function exportOrder(array $d)
    {
        $o = $this->loadOrder($d['id'] ?? '', ['assembled']);
        $errors = [];
        $deliveryId = $this->employeeByName($d['delivery'] ?? '', 'delivery');
        if (!$deliveryId) {
            $errors['delivery'] = 'Vui lòng chọn nhân viên giao hàng';
        }
        $date = $this->isoDate($d['exportDate'] ?? '', 'exportDate', $errors);
        $this->fail($errors);

        $extra = ['order_id' => $o['id'], 'employee_id' => $deliveryId, 'note' => $this->str($d, 'note', 500) ?: null, 'created_at' => $date . ' ' . date('H:i:s')];
        if ($o['type'] === 'new') {
            $product = $this->itemRow('product', (new Query())->select('code')->from('product')->where(['id' => $o['item']['product_id']])->scalar($this->db));
            $v = $this->writeVoucher('export', 'sale', -1, [['product', $product, (int)$o['item']['qty'], (int)$o['item']['unit_price']]], $extra);
            $productCode = (new Query())->select('code')->from('product')->where(['id' => $o['item']['product_id']])->scalar($this->db);
            for ($i = 0; $i < (int)$o['item']['qty']; $i++) {
                $this->insert('warranty', [
                    'serial' => $this->newSerial($productCode), 'qr_token' => Yii::$app->security->generateRandomString(12),
                    'product_id' => $o['item']['product_id'], 'order_id' => $o['id'], 'dealer_id' => $o['dealer_id'], 'status' => 'unactivated',
                ]);
            }
        } else {
            $lines = array_map(function ($p) {
                return ['part', ['table' => 'part', 'id' => (int)$p['part_id'], 'name' => $p['name'], 'stock' => (int)$p['stock'], 'stockCol' => 'stock'], (int)$p['qty_total'], (int)$p['unit_cost']];
            }, $this->orderParts($o));
            $v = $this->writeVoucher('export', 'warranty', -1, $lines, $extra);
        }
        $this->setOrderStatus($o, 'delivering', ['exporter_id' => $this->userId, 'delivery_id' => $deliveryId, 'delivery_note' => $extra['note']]);
        return $this->orderResult('✓ Đã tạo phiếu ' . $v['code'] . ' — giao cho ' . $d['delivery'], $o['code']);
    }

    private function newSerial($productCode)
    {
        $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        do {
            $s = $productCode . '-';
            for ($i = 0; $i < 4; $i++) {
                $s .= $chars[random_int(0, strlen($chars) - 1)];
            }
        } while ((new Query())->from('warranty')->where(['serial' => $s])->exists($this->db));
        return $s;
    }

    private function deliverOrder(array $d)
    {
        $o = $this->loadOrder($d['id'] ?? '', ['delivering']);
        $errors = [];
        $this->require($errors, $d, ['receiver' => 'người nhận hàng']);
        if (!empty($d['receiverPhone']) && !self::isPhone($d['receiverPhone'])) {
            $errors['receiverPhone'] = 'Số điện thoại không hợp lệ';
        }
        $date = $this->isoDate($d['deliveredDate'] ?? '', 'deliveredDate', $errors);
        $this->fail($errors);

        if ($o['type'] === 'new') {
            $dealer = (new Query())->from('dealer')->where(['id' => $o['dealer_id']])->one($this->db);
            $after = (int)$dealer['current_debt'] + (int)$o['total_amount'];
            $this->insert('dealer_ledger', [
                'dealer_id' => $dealer['id'], 'type' => 'debit', 'amount' => $o['total_amount'], 'balance_after' => $after,
                'order_id' => $o['id'], 'note' => 'Giao đơn ' . $o['code'], 'created_by' => $this->userId,
            ]);
            $this->update('dealer', ['current_debt' => $after, 'total_purchase' => new Expression('total_purchase + ' . (int)$o['total_amount'])], ['id' => $dealer['id']]);
            foreach (['sale' => $o['sale_id'], 'delivery' => $o['delivery_id']] as $role => $empId) {
                if (!$empId) {
                    continue;
                }
                $rate = D::COMMISSION_RATES[$role];
                $this->insert('commission', [
                    'order_id' => $o['id'], 'employee_id' => $empId, 'role' => $role, 'base_amount' => $o['total_amount'], 'rate' => $rate,
                    'amount' => (int)round($o['total_amount'] * $rate / 100), 'period' => substr($date, 0, 7),
                ]);
            }
        }
        $this->setOrderStatus($o, 'delivered', [
            'receiver_name' => $this->str($d, 'receiver'), 'receiver_phone' => $this->str($d, 'receiverPhone', 30) ?: null,
            'delivered_at' => $date . ' ' . date('H:i:s'), 'delivery_note' => $this->str($d, 'note', 500) ?: $o['delivery_note'],
        ]);
        return $this->orderResult('✓ Đơn ' . $o['code'] . ' hoàn thành' . ($o['type'] === 'new' ? ' · ghi nợ ' . D::money($o['total_amount']) : ''), $o['code']);
    }

    private function cancelOrder(array $d)
    {
        $o = $this->loadOrder($d['id'] ?? '', ['pending', 'confirmed']);
        $reason = $this->str($d, 'reason', 100);
        $detail = $this->str($d, 'detail', 200);
        $errors = [];
        if ($reason === '') {
            $errors['reason'] = 'Vui lòng chọn lý do huỷ';
        }
        if ($reason === 'Khác' && $detail === '') {
            $errors['detail'] = 'Vui lòng ghi rõ lý do';
        }
        $this->fail($errors);
        $text = $reason . ($detail ? ': ' . $detail : '');
        $this->setOrderStatus($o, 'cancelled', ['cancel_reason' => $text, 'cancelled_at' => date('Y-m-d H:i:s')], $text);
        return $this->orderResult('Đã huỷ đơn ' . $o['code'], $o['code']);
    }

    /* ======================= Đơn hàng sàn 1kho ======================= */

    /** Cập nhật trạng thái đơn sàn như trang quản trị sàn cũ, kèm các mốc thời gian mà app khách hàng dựa vào. */
    private function setMarketOrderStatus(array $d)
    {
        $id = (int)preg_replace('/\D/', '', (string)($d['id'] ?? ''));
        $o = (new Query())->from('order')->where(['id' => $id])->one($this->db);
        if (!$o) {
            throw new Invalid(['id' => 'Đơn hàng không tồn tại']);
        }
        $status = isset($d['status']) && $d['status'] !== '' ? (int)$d['status'] : null;
        $note = $this->str($d, 'note', 255);
        $errors = [];
        if ($status === null || !isset(D::MARKET_ORDER_STATUS[$status])) {
            $errors['status'] = 'Vui lòng chọn trạng thái';
        }
        if ($status === D::MARKET_ORDER_CANCELLED && $note === '') {
            $errors['note'] = 'Vui lòng nhập lý do hủy';
        }
        $this->fail($errors);

        $now = date('Y-m-d H:i:s');
        $set = ['status' => $status, 'reason_cancel' => $note !== '' ? $note : null];
        if ($status === D::MARKET_ORDER_PURCHASED && empty($o['time_payment'])) {
            $set['time_payment'] = $now;
        }
        if ($status === D::MARKET_ORDER_CANCELLED && (int)$o['status'] !== D::MARKET_ORDER_CANCELLED) {
            $set['type_cancel'] = 3;
            $set['time_cancel'] = $now;
        }
        $this->db->createCommand()->update('order', $set, ['id' => $id])->execute();
        $label = D::MARKET_ORDER_STATUS[$status]['label'];
        return ['message' => '✓ Đơn #' . $id . ' → ' . $label, 'data' => ['marketOrder' => R::marketOrder($id), 'counts' => R::marketOrderCounts()]];
    }

    /** Duyệt / từ chối yêu cầu trả hàng - hoàn tiền của khách (order_refund). Duyệt thì đơn chuyển sang "Hoàn tiền". */
    private function processMarketRefund(array $d)
    {
        $refund = (new Query())->from('order_refund')->where(['id' => (int)($d['refundId'] ?? 0)])->one($this->db);
        if (!$refund) {
            throw new Invalid([], 'Yêu cầu hoàn tiền không tồn tại');
        }
        if ((int)$refund['status'] !== 0) {
            throw new Invalid([], 'Yêu cầu này đã được xử lý');
        }
        $approve = ($d['decision'] ?? '') === 'approve';
        $note = $this->str($d, 'note', 400);
        if (!$approve && $note === '') {
            throw new Invalid(['note' => 'Vui lòng nhập lý do từ chối']);
        }
        $now = date('Y-m-d H:i:s');
        $this->db->createCommand()->update('order_refund', [
            'status' => $approve ? 1 : 2, 'reason_cancel' => $approve ? null : $note, 'time_process' => $now,
        ], ['id' => $refund['id']])->execute();
        if ($approve) {
            $this->db->createCommand()->update('order', ['status' => 4], ['id' => (int)$refund['order_id']])->execute();
        }
        return [
            'message' => ($approve ? '✓ Đã đồng ý hoàn tiền' : 'Đã từ chối yêu cầu') . ' · đơn #' . (int)$refund['order_id'],
            'data' => ['marketOrder' => R::marketOrder((int)$refund['order_id']), 'counts' => R::marketOrderCounts()],
        ];
    }

    /* ======================= Bảo hành ======================= */

    private function warrantyId($code)
    {
        return (int)preg_replace('/\D/', '', (string)$code);
    }

    private function warrantyData()
    {
        return ['warranties' => R::warranties(), 'claims' => R::claims()];
    }

    /** Kích hoạt bảo hành: nội bộ (hotline) hoặc khách tự kích hoạt qua QR. */
    public function activate(array $w, $name, $phone, $date, $address = null)
    {
        $errors = [];
        if (mb_strlen(trim((string)$name)) < 2) {
            $errors['custName'] = 'Vui lòng nhập họ tên';
        }
        if (!self::isPhone($phone)) {
            $errors['phone'] = 'Số điện thoại không hợp lệ (10–11 số, bắt đầu bằng 0)';
        }
        $date = $this->isoDate($date, 'date', $errors);
        if ($w['status'] !== 'unactivated') {
            $errors['custName'] = 'Máy đã được kích hoạt trước đó';
        }
        $this->fail($errors);
        $this->update('warranty', [
            'customer_name' => trim($name), 'customer_phone' => trim($phone), 'customer_address' => $address ? mb_substr(trim($address), 0, 300) : null,
            'activated_at' => $date . ' ' . date('H:i:s'), 'expires_at' => date('Y-m-d', strtotime($date . ' +' . D::WARRANTY_MONTHS . ' months')), 'status' => 'active',
        ], ['id' => $w['id']]);
        R::flush();
    }

    private function activateWarranty(array $d)
    {
        $w = (new Query())->from('warranty')->where(['id' => $this->warrantyId($d['id'] ?? '')])->one($this->db);
        if (!$w) {
            throw new Invalid([], 'Không tìm thấy máy');
        }
        $this->activate($w, $d['custName'] ?? '', $d['phone'] ?? '', $d['date'] ?? '');
        return ['message' => '✓ Đã kích hoạt bảo hành ' . $w['serial'], 'data' => $this->warrantyData()];
    }

    private function saveClaim(array $d)
    {
        $statuses = ['pending', 'processing', 'resolved', 'rejected'];
        $status = in_array($d['status'] ?? '', $statuses, true) ? $d['status'] : null;
        $assigneeId = !empty($d['assignee']) ? $this->employeeByName($d['assignee']) : null;
        $note = $this->str($d, 'note', 500);
        $errors = [];
        if (!$status) {
            $errors['status'] = 'Trạng thái không hợp lệ';
        }
        if (!empty($d['assignee']) && !$assigneeId) {
            $errors['assignee'] = 'Kỹ thuật viên không hợp lệ';
        }
        if ($status === 'processing' && !$assigneeId) {
            $errors['assignee'] = 'Cần phân công kỹ thuật viên khi đang xử lý';
        }
        if (in_array($status, ['resolved', 'rejected'], true) && $note === '') {
            $errors['note'] = 'Vui lòng ghi kết quả xử lý / lý do từ chối';
        }

        $code = $this->str($d, 'id', 20);
        if ($code !== '') {
            $claim = (new Query())->from('warranty_claim')->where(['code' => $code])->one($this->db);
            if (!$claim) {
                throw new Invalid([], 'Phiếu khiếu nại không tồn tại');
            }
            $this->fail($errors);
            $done = in_array($status, ['resolved', 'rejected'], true);
            $this->update('warranty_claim', [
                'status' => $status, 'assignee_id' => $assigneeId, 'note' => $note ?: null,
                'resolved_at' => $done ? ($claim['resolved_at'] ?: date('Y-m-d H:i:s')) : null,
            ], ['id' => $claim['id']]);
            return ['message' => '✓ Đã cập nhật ' . $code, 'data' => $this->warrantyData()];
        }

        $w = (new Query())->from('warranty')->where(['id' => $this->warrantyId($d['wId'] ?? '')])->one($this->db);
        if (!$w || $w['status'] !== 'active') {
            $errors['wId'] = 'Chỉ tạo khiếu nại cho máy đang bảo hành';
        } elseif ($w['expires_at'] && $w['expires_at'] < date('Y-m-d')) {
            $errors['wId'] = 'Máy đã hết hạn bảo hành (' . R::vnDate($w['expires_at']) . ')';
        }
        $issue = $this->str($d, 'issue', 500);
        if (mb_strlen($issue) < 10) {
            $errors['issue'] = 'Mô tả sự cố tối thiểu 10 ký tự';
        }
        if (!in_array($status, ['pending', 'processing'], true)) {
            $errors['status'] = 'Phiếu mới chỉ ở trạng thái Chờ / Đang xử lý';
        }
        $this->fail($errors);
        $code = $this->nextCode('warranty_claim', 'KC');
        $this->insert('warranty_claim', ['code' => $code, 'warranty_id' => $w['id'], 'issue' => $issue, 'status' => $status, 'assignee_id' => $assigneeId, 'note' => $note ?: null]);
        $this->update('warranty', ['claim_count' => new Expression('claim_count + 1')], ['id' => $w['id']]);
        return ['message' => '✓ Đã tạo phiếu ' . $code, 'data' => $this->warrantyData()];
    }

    /* ======================= Kế toán ======================= */

    private function payCommission(array $d)
    {
        $empId = (int)($d['employeeId'] ?? 0);
        $role = in_array($d['role'] ?? '', ['sale', 'delivery'], true) ? $d['role'] : null;
        $row = null;
        foreach (R::commissions() as $c) {
            if ($c['employeeId'] === $empId && $c['role'] === $role) {
                $row = $c;
            }
        }
        if (!$row) {
            throw new Invalid(['amount' => 'Không có hoa hồng để chi']);
        }
        $left = $row['amount'] - $row['paid'];
        $errors = [];
        $amount = $this->positive($errors, $d, 'amount', 'Số tiền');
        if ($amount && $amount > $left) {
            $errors['amount'] = 'Không được chi vượt số còn phải trả (' . D::money($left) . ')';
        }
        $method = in_array($d['method'] ?? '', ['Chuyển khoản', 'Tiền mặt', 'Cộng vào lương'], true) ? $d['method'] : null;
        if (!$method) {
            $errors['method'] = 'Hình thức không hợp lệ';
        }
        $date = $this->isoDate($d['date'] ?? '', 'date', $errors);
        $this->fail($errors);
        $this->insert('commission_payout', [
            'employee_id' => $empId, 'role' => $role, 'amount' => $amount, 'method' => $method, 'note' => $this->str($d, 'note', 300) ?: null,
            'paid_at' => $date, 'created_by' => $this->userId,
        ]);
        return ['message' => '✓ Đã chi ' . D::money($amount) . ' hoa hồng cho ' . $row['name'], 'data' => ['commissions' => R::commissions()]];
    }

    /* ======================= Tài khoản ======================= */

    private function assignRole($userId, $role)
    {
        $auth = Yii::$app->authManager;
        foreach (array_keys(D::ROLES) as $r) {
            if ($auth->getAssignment($r, $userId)) {
                $auth->revoke($auth->getRole($r), $userId);
            }
        }
        $auth->assign($auth->getRole($role), $userId);
    }

    private function createUser(array $d)
    {
        $errors = [];
        $this->require($errors, $d, ['role' => 'vai trò', 'name' => 'họ và tên', 'username' => 'tên đăng nhập', 'email' => 'email', 'password' => 'mật khẩu', 'password2' => 'xác nhận mật khẩu']);
        $role = $d['role'] ?? '';
        if ($role !== '' && !isset(D::ROLES[$role])) {
            $errors['role'] = 'Vai trò không hợp lệ';
        }
        $username = $this->str($d, 'username', 30);
        if ($username !== '' && !preg_match('/^[a-z0-9._]{3,30}$/', $username)) {
            $errors['username'] = 'Chỉ gồm chữ thường, số, . _ (3–30 ký tự)';
        } elseif ($username !== '' && (new Query())->from('employee')->where(['username' => $username])->exists($this->db)) {
            $errors['username'] = 'Tên đăng nhập đã tồn tại';
        }
        $email = $this->str($d, 'email', 100);
        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Email không hợp lệ';
        } elseif ($email !== '' && (new Query())->from('employee')->where(['email' => $email])->exists($this->db)) {
            $errors['email'] = 'Email đã được dùng';
        }
        if (!empty($d['phone']) && !self::isPhone($d['phone'])) {
            $errors['phone'] = 'Số điện thoại không hợp lệ';
        }
        $password = (string)($d['password'] ?? '');
        if ($password !== '' && !(strlen($password) >= 8 && preg_match('/[a-zA-Z]/', $password) && preg_match('/\d/', $password))) {
            $errors['password'] = 'Tối thiểu 8 ký tự, gồm cả chữ và số';
        }
        if (($d['password2'] ?? '') !== '' && $d['password2'] !== $password) {
            $errors['password2'] = 'Mật khẩu nhập lại không khớp';
        }
        $this->fail($errors);

        $user = new \common\models\User();
        $user->setPassword($password);
        $id = $this->insert('employee', [
            'fullname' => $this->str($d, 'name', 100), 'username' => $username, 'email' => $email, 'phone' => $this->str($d, 'phone', 20) ?: null,
            'password' => $user->password, 'auth_key' => Yii::$app->security->generateRandomString(32), 'is_active' => ($d['active'] ?? '1') === '1' ? 1 : 0,
            'is_admin' => 0, 'can_use_app' => in_array($role, ['sale', 'delivery'], true) ? 1 : 0, 'create_date' => time(),
        ]);
        $this->assignRole($id, $role);
        return ['message' => '✓ Đã tạo tài khoản ' . $username . ' (' . D::ROLES[$role]['label'] . ')', 'data' => ['staff' => R::staff()]];
    }

    private function setPermission(array $d)
    {
        $id = (int)($d['userId'] ?? 0);
        $e = (new Query())->from('employee')->where(['id' => $id])->one($this->db);
        if (!$e) {
            throw new Invalid([], 'Tài khoản không tồn tại');
        }
        $role = $d['role'] ?? '';
        if (!isset(D::ROLES[$role])) {
            throw new Invalid(['role' => 'Vai trò không hợp lệ']);
        }
        $active = ($d['active'] ?? '1') === '1';
        if ($id === $this->userId && (!$active || $role !== 'admin')) {
            throw new Invalid(['active' => 'Không thể tự khoá hoặc tự hạ quyền tài khoản đang đăng nhập']);
        }
        if ((int)$e['is_admin'] === 1 && $role !== 'admin') {
            throw new Invalid(['role' => 'Tài khoản super admin của hệ thống luôn có vai trò Admin']);
        }
        $this->update('employee', ['is_active' => $active ? 1 : 0, 'can_use_app' => in_array($role, ['sale', 'delivery'], true) ? 1 : 0], ['id' => $id]);
        if ((int)$e['is_admin'] !== 1) {
            $this->assignRole($id, $role);
        }
        return ['message' => '✓ ' . (trim((string)$e['fullname']) ?: $e['username']) . ': ' . D::ROLES[$role]['label'] . ' · ' . ($active ? 'Đang hoạt động' : 'Đã khóa'), 'data' => ['staff' => R::staff()]];
    }

    private function find(array $rows, $id)
    {
        foreach ($rows as $r) {
            if ($r['id'] === $id) {
                return $r;
            }
        }
        return null;
    }
}
