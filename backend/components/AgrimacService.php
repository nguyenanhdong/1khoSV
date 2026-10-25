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
        'bom.save'             => ['saveBom', ['products'], ['admin', 'kt_congno', 'sale']],
        'part.save'            => ['savePart', ['inventory'], null],
        'stock.import'         => ['stockImport', ['inventory'], null],
        'stock.export'         => ['stockExport', ['inventory'], null],
        'supplier.save'        => ['saveSupplier', ['suppliers', 'inventory', 'products'], null],
        'supplier.return'      => ['createReturn', ['suppliers'], null],
        'supplier.returnNext'  => ['nextReturnStatus', ['suppliers'], null],
        'dealer.save'          => ['saveDealer', ['dealers'], ['admin', 'sale']],
        'dealer.collect'       => ['collectPayment', ['dealers'], ['admin', 'kt_congno']],
        'lead.save'            => ['saveLead', ['crm'], null],
        'lead.stage'           => ['setLeadStage', ['crm'], null],
        'lead.note'            => ['addLeadNote', ['crm'], null],
        // Kinh doanh tạo & duyệt đơn; Kế toán công nợ xuất / nhập kho; Lắp ráp nhận LK – bắt đầu – xong; Giao hàng gửi nhà xe
        'order.create'         => ['createOrder', ['orders'], ['admin', 'sale']],
        'order.approve'        => ['approveOrder', ['orders'], ['admin', 'sale']],
        'order.export'         => ['exportOrder', ['orders', 'assembly'], ['admin', 'kt_congno']],
        'order.ship'           => ['shipOrder', ['orders'], ['admin', 'delivery']],
        'order.receive'        => ['receiveOrder', ['orders'], ['admin', 'delivery', 'sale']],
        'order.partsReceive'   => ['receiveParts', ['assembly'], ['admin', 'assembly']],
        'order.start'          => ['startAssembly', ['assembly'], ['admin', 'assembly']],
        'order.finish'         => ['finishAssembly', ['assembly'], ['admin', 'assembly']],
        'order.stock'          => ['stockAssembly', ['orders', 'assembly'], ['admin', 'kt_congno']],
        'order.assemblyNote'   => ['saveAssemblyNote', ['assembly'], ['admin', 'assembly']],
        'order.cancel'         => ['cancelOrder', ['orders'], ['admin', 'sale']],
        'order.debt'           => ['saveOrderDebt', ['orders', 'dealers'], ['admin', 'sale', 'kt_congno']],
        'carrier.save'         => ['saveCarrier', ['carriers', 'orders'], ['admin', 'kt_congno', 'delivery']],
        'carrier.delete'       => ['deleteCarrier', ['carriers'], ['admin', 'kt_congno', 'delivery']],
        'market.status'        => ['setMarketOrderStatus', ['orders'], ['admin']],
        'market.refund'        => ['processMarketRefund', ['orders'], ['admin']],
        'warranty.activate'    => ['activateWarranty', ['warranty'], null],
        'claim.save'           => ['saveClaim', ['warranty'], null],
        'commission.pay'       => ['payCommission', ['accounting'], ['admin', 'kt_congno']],
        'user.create'          => ['createUser', ['users'], ['admin']],
        'user.permission'      => ['setPermission', ['users'], ['admin']],
    ];

    const IMAGE_PREFIX = '/uploads/images/product/';
    /** Thư mục ảnh gửi hàng (đường dẫn web, không có / đầu) */
    const SHIP_IMAGE_DIR = 'uploads/images/shipping';

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
        // Nguồn hàng không bắt buộc: để trống = NULL
        $source = ($d['sourceType'] ?? '') === '' ? null : $d['sourceType'];
        if ($source !== null && !isset(D::SOURCE_TYPES[$source])) {
            $errors['sourceType'] = 'Nguồn hàng không hợp lệ';
        }
        $supplierId = !empty($d['supId']) ? $this->idByCode('supplier', $d['supId']) : null;
        if (!empty($d['supId']) && !$supplierId) {
            $errors['supId'] = 'Nhà cung cấp không hợp lệ';
        }
        if (!$id && $source === 'import' && !$supplierId) {
            $errors['supId'] = 'Hàng nhập nguyên chiếc cần chọn nhà cung cấp';
        }
        $price = $this->positive($errors, $d, 'price', 'Giá bán lẻ');
        $unit = in_array($d['unit'] ?? '', D::PRODUCT_UNITS, true) ? $d['unit'] : null;
        if (!$unit) {
            $errors['unit'] = 'Vui lòng chọn đơn vị tính';
        }
        $nums = [];
        foreach (['cost' => 'Giá vốn', 'dealerPrice' => 'Giá đại lý', 'weight' => 'Trọng lượng', 'minStock' => 'Tồn tối thiểu'] as $k => $label) {
            $v = $this->int($d, $k);
            if ($v === false || ($v !== null && $v < 0)) {
                $errors[$k] = $label . ' phải là số nguyên không âm';
            }
            $nums[$k] = $v === false ? null : $v;
        }
        $images = array_values(array_filter((array)($d['images'] ?? []), function ($u) {
            return is_string($u) && strpos($u, self::IMAGE_PREFIX) === 0 && !preg_match('/[;\s]|\.\./', $u);
        }));
        if (count($images) > 20) {
            $errors['images'] = 'Tối đa 20 ảnh';
        }
        // Linh kiện cấu thành (BOM): chỉ với sản phẩm tự lắp; gửi kèm cờ bomSent để phân biệt "không gửi" với "xoá hết"
        $bom = null;
        if (!empty($d['bomSent']) && in_array($source, ['assembly', 'both'], true)) {
            $bom = $this->readBom($d['bom'] ?? [], 'bom');
        }
        $this->fail($errors);

        // Công suất / hệ dẫn động không còn nhập ở CMS: giữ nguyên giá trị cũ trong DB
        $old = $id ? (new Query())->select(['horsepower', 'drive_type'])->from('product')->where(['id' => $id])->one($this->db) : [];
        $specs = implode(' · ', array_filter([
            !empty($old['horsepower']) ? $old['horsepower'] . 'HP' : null, $old['drive_type'] ?? null, $nums['weight'] ? $nums['weight'] . 'kg' : null,
        ]));
        $row = [
            'name' => $name, 'category_id' => $catId, 'supplier_id' => $supplierId, 'source_type' => $source, 'price' => $price,
            'price_dealer' => (int)$nums['dealerPrice'], 'unit' => $unit,
            'cost_price' => (int)$nums['cost'], 'weight' => $nums['weight'],
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
        $bomNote = '';
        if ($bom !== null) {
            $productId = $id ?: $newId;
            $bomCost = $this->writeBom($productId, $bom);
            // Chưa nhập giá vốn → lấy theo tổng linh kiện
            if ($bom && $nums['cost'] === null) {
                $this->update('product', ['cost_price' => $bomCost], ['id' => $productId]);
            }
            $bomNote = $bom ? ' · ' . count($bom) . ' linh kiện' : '';
        }
        return ['message' => ($id ? '✓ Đã cập nhật "' : '✓ Đã thêm sản phẩm "') . $name . '" (' . $code . ')' . $bomNote, 'data' => [
            'product' => R::product($code), 'categories' => R::categories(), 'boms' => (object)R::defaultBoms(),
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

    /** Đọc danh sách linh kiện [{id: mã LK, qty}] → [part_id => qty]; lỗi gắn vào ô $field */
    private function readBom($rows, $field)
    {
        $items = [];
        foreach ((array)$rows as $it) {
            $partId = $this->idByCode('part', $it['id'] ?? '');
            $qty = (int)($it['qty'] ?? 0);
            if (!$partId || $qty < 1) {
                throw new Invalid([$field => 'Linh kiện hoặc số lượng không hợp lệ']);
            }
            $items[$partId] = ($items[$partId] ?? 0) + $qty;
        }
        return $items;
    }

    /** Ghi đè BOM mặc định của sản phẩm, trả về giá vốn theo BOM / 1 máy */
    private function writeBom($productId, array $items)
    {
        $this->db->createCommand()->delete('product_bom', ['product_id' => $productId])->execute();
        $cost = 0;
        $costs = $items ? (new Query())->select(['cost_price', 'id'])->from('part')->where(['id' => array_keys($items)])->indexBy('id')->column($this->db) : [];
        foreach ($items as $partId => $qty) {
            $this->insert('product_bom', ['product_id' => $productId, 'part_id' => $partId, 'qty' => $qty]);
            $cost += (int)$costs[$partId] * $qty;
        }
        return $cost;
    }

    private function saveBom(array $d)
    {
        $productId = $this->idByCode('product', $d['productId'] ?? '');
        if (!$productId) {
            throw new Invalid(['updateCost' => 'Sản phẩm không tồn tại']);
        }
        $items = $this->readBom($d['items'] ?? [], 'updateCost');
        if (!$items) {
            throw new Invalid(['updateCost' => 'BOM cần ít nhất 1 linh kiện']);
        }
        $cost = $this->writeBom($productId, $items);
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

    /* ---------------- Đơn hàng: dùng chung ---------------- */

    private function loadOrder($code, array $statuses, array $types = null)
    {
        $o = (new Query())->from('dealer_order')->where(['code' => (string)$code])->one($this->db);
        if (!$o) {
            throw new Invalid([], 'Đơn hàng không tồn tại');
        }
        if ($types !== null && !in_array($o['type'], $types, true)) {
            throw new Invalid([], 'Thao tác không áp dụng cho ' . mb_strtolower(D::ORDER_TYPES[$o['type']]['label']));
        }
        if (!in_array($o['status'], $statuses, true)) {
            throw new Invalid([], 'Đơn ' . $o['code'] . ' đang ở trạng thái "' . D::orderStatusLabel($o['type'], $o['status']) . '", không thực hiện được thao tác này');
        }
        $o['items'] = (new Query())->select(['i.*', 'code' => 'p.code', 'name' => 'p.name', 'stock' => 'p.quantity_in_stock', 'cost' => 'p.cost_price'])
            ->from('dealer_order_item i')->innerJoin('product p', 'p.id = i.product_id')->where(['i.order_id' => $o['id']])->orderBy('i.id')->all($this->db);
        return $o;
    }

    private function setOrderStatus(array $o, $to, array $row = [], $note = null)
    {
        $this->update('dealer_order', array_merge($row, ['status' => $to, 'updated_at' => date('Y-m-d H:i:s')]), ['id' => $o['id']]);
        $this->insert('dealer_order_log', ['order_id' => $o['id'], 'from_status' => $o['status'], 'to_status' => $to, 'employee_id' => $this->userId, 'note' => $note]);
    }

    private function orderResult($message, $code)
    {
        $order = R::order($code);
        return ['message' => $message, 'data' => [
            'order' => $order, 'dealer' => $order['dealerId'] ? R::dealer($order['dealerId']) : null, 'parts' => R::parts(),
        ]];
    }

    private function productByKey($key)
    {
        $key = trim((string)$key);
        if ($key === '') {
            return null;
        }
        return (new Query())->from('product')->where(['is_delete' => 0])->andWhere(['or', ['code' => $key], ['name' => $key]])->orderBy('id')->one($this->db);
    }

    private function orderParts(array $o)
    {
        return (new Query())->select(['op.*', 'pt.code', 'pt.name', 'pt.stock', 'pt.cost_price'])->from('dealer_order_part op')
            ->innerJoin('part pt', 'pt.id = op.part_id')->where(['op.order_id' => $o['id']])->all($this->db);
    }

    /** Dòng phiếu kho cho linh kiện của đơn */
    private function partLines(array $parts)
    {
        return array_map(function ($p) {
            return ['part', ['table' => 'part', 'id' => (int)$p['part_id'], 'name' => $p['name'], 'stock' => (int)$p['stock'], 'stockCol' => 'stock'], (int)$p['qty_total'], (int)$p['unit_cost']];
        }, $parts);
    }

    /**
     * Đọc danh sách linh kiện [{part, qty}] từ form → [part_id => [row, qty]]; lỗi ghi vào $errors[$field].
     */
    private function readParts($rows, array &$errors, $field = 'parts')
    {
        $out = [];
        foreach ((array)$rows as $r) {
            $code = trim((string)($r['part'] ?? ''));
            $qty = (int)($r['qty'] ?? 0);
            if ($code === '' && $qty === 0) {
                continue;
            }
            $part = (new Query())->from('part')->where(['code' => $code])->one($this->db);
            if (!$part) {
                $errors[$field] = 'Linh kiện "' . $code . '" không tồn tại';
                continue;
            }
            if ($qty < 1) {
                $errors[$field] = 'Số lượng linh kiện "' . $part['name'] . '" phải ≥ 1';
                continue;
            }
            if (isset($out[$part['id']])) {
                $out[$part['id']][1] += $qty;
            } else {
                $out[$part['id']] = [$part, $qty];
            }
        }
        if (!$out && empty($errors[$field])) {
            $errors[$field] = 'Vui lòng chọn ít nhất một linh kiện';
        }
        return $out;
    }

    /** Ghi lại toàn bộ linh kiện của đơn; trả về tổng giá vốn (chốt giá linh kiện hiện tại). */
    private function writeOrderParts(array $o, array $parts, $perUnitDivisor = 1)
    {
        $this->db->createCommand()->delete('dealer_order_part', ['order_id' => $o['id']])->execute();
        $cost = 0;
        foreach ($parts as $partId => [$part, $qty]) {
            $this->insert('dealer_order_part', [
                'order_id' => $o['id'], 'order_item_id' => null, 'part_id' => $partId,
                'qty_per_unit' => max(1, (int)round($qty / max(1, $perUnitDivisor))), 'qty_total' => $qty, 'unit_cost' => (int)$part['cost_price'],
            ]);
            $cost += $qty * (int)$part['cost_price'];
        }
        return $cost;
    }

    /* ---------------- Tạo đơn ---------------- */

    /**
     * Chiết khấu: $type percent|amount, $value nhập (% cho phép số lẻ, hoặc số tiền) áp trên $base.
     * Trả [type, value, số tiền chiết khấu]; lỗi ghi vào $errors[$field].
     */
    private function discount($type, $value, $base, $field, array &$errors, $label = '')
    {
        $type = $type === 'percent' ? 'percent' : 'amount';
        $raw = trim(str_replace(',', '.', (string)$value));
        if ($type === 'amount') {
            $raw = preg_replace('/\D/', '', $raw);
        }
        $prefix = $label !== '' ? 'Chiết khấu "' . $label . '"' : 'Chiết khấu cuối đơn';
        if ($raw === '') {
            return [$type, 0, 0];
        }
        if (!is_numeric($raw) || (float)$raw < 0) {
            $errors[$field] = $prefix . ' không hợp lệ';
            return [$type, 0, 0];
        }
        $v = (float)$raw;
        if ($v == 0) {
            return [$type, 0, 0];
        }
        if ($type === 'percent') {
            if ($v > 100) {
                $errors[$field] = $prefix . ' tối đa 100%';
                return [$type, 0, 0];
            }
            return [$type, round($v, 2), (int)round($base * $v / 100)];
        }
        if ($v > $base) {
            $errors[$field] = $prefix . ' lớn hơn giá trị ' . ($label !== '' ? 'dòng' : 'đơn');
            return [$type, 0, 0];
        }
        return [$type, (int)$v, (int)$v];
    }

    private function createOrder(array $d)
    {
        $errors = [];
        $type = isset(D::ORDER_TYPES[$d['type'] ?? '']) ? $d['type'] : null;
        if (!$type) {
            $errors['type'] = 'Vui lòng chọn loại đơn';
        }

        // Sale phụ trách: Sale tạo đơn → chính họ (không đổi được); CEO tạo → chọn Sale
        if ($this->role === 'sale') {
            $saleId = $this->userId;
        } else {
            $saleId = $this->employeeByName($d['sale'] ?? '', 'sale');
            if (!$saleId && $type !== 'assembly') {
                $errors['sale'] = 'Vui lòng chọn Sale phụ trách';
            }
            $saleId = $saleId ?: $this->userId;
        }

        // Khách: đại lý hoặc khách lẻ (đơn lắp ráp không có khách)
        $dealerId = null;
        $customerType = null;
        $retail = ['customer_name' => null, 'customer_phone' => null, 'customer_address' => null];
        if ($type && $type !== 'assembly') {
            $customer = (string)($d['customer'] ?? '');
            if ($customer === 'retail') {
                $customerType = 'retail';
                $retail = [
                    'customer_name' => $this->str($d, 'customerName', 255),
                    'customer_phone' => $this->str($d, 'customerPhone', 30),
                    'customer_address' => $this->str($d, 'customerAddress', 300),
                ];
                $this->require($errors, $d, ['customerName' => 'tên khách hàng', 'customerPhone' => 'số điện thoại khách', 'customerAddress' => 'địa chỉ khách']);
                if ($retail['customer_phone'] !== '' && !self::isPhone($retail['customer_phone'])) {
                    $errors['customerPhone'] = 'Số điện thoại không hợp lệ (10–11 số, bắt đầu bằng 0)';
                }
            } else {
                $customerType = 'dealer';
                $dealerId = $this->idByCode('dealer', $customer, ['status' => 1]);
                if (!$dealerId) {
                    $errors['customer'] = $customer === '' ? 'Vui lòng chọn đại lý hoặc khách lẻ' : 'Đại lý không hợp lệ';
                }
            }
        }

        // Sản phẩm (đơn bán / lắp ráp) hoặc linh kiện (đơn bảo hành)
        $lines = [];
        $parts = [];
        if ($type === 'warranty') {
            $parts = $this->readParts($d['parts'] ?? [], $errors);
        } elseif ($type) {
            $seen = [];
            foreach ((array)($d['items'] ?? []) as $row) {
                $key = trim((string)($row['product'] ?? ''));
                if ($key === '' && trim((string)($row['qty'] ?? '')) === '') {
                    continue;
                }
                $product = $this->productByKey($key);
                if (!$product) {
                    $errors['items'] = 'Sản phẩm "' . $key . '" không tồn tại';
                    continue;
                }
                if (isset($seen[$product['id']])) {
                    $errors['items'] = 'Sản phẩm "' . $product['name'] . '" bị chọn trùng — gộp vào một dòng';
                    continue;
                }
                $seen[$product['id']] = true;
                $qty = (int)($row['qty'] ?? 0);
                $price = $type === 'assembly' ? 0 : (int)preg_replace('/\D/', '', (string)($row['price'] ?? ''));
                if ($qty < 1) {
                    $errors['items'] = 'Số lượng "' . $product['name'] . '" phải ≥ 1';
                } elseif ($type === 'new' && $price <= 0) {
                    $errors['items'] = 'Đơn giá "' . $product['name'] . '" phải lớn hơn 0';
                }
                $disc = $type === 'new' ? $this->discount($row['discountType'] ?? '', $row['discount'] ?? '', max(0, $qty) * $price, 'items', $errors, $product['name']) : ['amount', 0, 0];
                $lines[] = [$product, $qty, $price, $disc];
            }
            if (!$lines && empty($errors['items'])) {
                $errors['items'] = 'Vui lòng thêm ít nhất một sản phẩm';
            }
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
        $subtotal = 0;
        foreach ($lines as [, $qty, $price, $disc]) {
            $subtotal += $qty * $price - $disc[2];
        }
        // Chiết khấu cuối đơn (chỉ đơn bán)
        $orderDisc = $type === 'new' ? $this->discount($d['discountType'] ?? '', $d['discount'] ?? '', $subtotal, 'discount', $errors) : ['amount', 0, 0];
        $this->fail($errors);
        $total = $subtotal - $orderDisc[2];
        $leadId = $type === 'new' && !empty($d['lead']) ? $this->leadId($d['lead']) : null;
        $id = $this->insert('dealer_order', array_merge([
            'code' => $code, 'type' => $type, 'dealer_id' => $dealerId, 'customer_type' => $customerType,
            'lead_id' => $leadId ?: null, 'claim_id' => $claimId, 'status' => 'pending', 'total_amount' => $total,
            'subtotal' => $subtotal, 'discount_type' => $orderDisc[0], 'discount_value' => $orderDisc[1], 'discount_amount' => $orderDisc[2],
            'sale_id' => $saleId, 'created_by' => $this->userId, 'note' => $this->str($d, 'note', 500) ?: null, 'ordered_at' => date('Y-m-d H:i:s'),
        ], $retail));
        foreach ($lines as [$product, $qty, $price, $disc]) {
            $this->insert('dealer_order_item', [
                'order_id' => $id, 'product_id' => $product['id'], 'qty' => $qty, 'unit_price' => $price,
                'discount_type' => $disc[0], 'discount_value' => $disc[1], 'discount_amount' => $disc[2], 'line_total' => $qty * $price - $disc[2],
            ]);
        }
        if ($parts) {
            $cost = $this->writeOrderParts(['id' => $id], $parts);
            $this->update('dealer_order', ['total_cost' => $cost], ['id' => $id]);
        }
        $this->insert('dealer_order_log', ['order_id' => $id, 'to_status' => 'pending', 'employee_id' => $this->userId, 'note' => 'Tạo ' . mb_strtolower(D::ORDER_TYPES[$type]['label'])]);
        if ($leadId) {
            $this->update('crm_lead', ['order_id' => $id, 'stage' => 'won', 'updated_at' => date('Y-m-d H:i:s')], ['id' => $leadId]);
        }
        if ($claimId) {
            $this->update('warranty_claim', ['order_id' => $id], ['id' => $claimId]);
        }
        return $this->orderResult('✓ Đã tạo ' . mb_strtolower(D::ORDER_TYPES[$type]['label']) . ' ' . $code . ' — chờ duyệt', $code);
    }

    /* ---------------- Duyệt ---------------- */

    /** Số hoá đơn nội bộ tự sinh: HD + năm + 5 số (VD HD202600012) */
    private function nextInvoiceNo()
    {
        $prefix = 'HD' . date('Y');
        $max = (new Query())->from('dealer_order')->where(['like', 'invoice_no', $prefix . '%', false])
            ->max(new Expression('CAST(SUBSTRING(invoice_no, ' . (strlen($prefix) + 1) . ') AS UNSIGNED)'), $this->db);
        return $prefix . str_pad((string)((int)$max + 1), 5, '0', STR_PAD_LEFT);
    }

    private function approveOrder(array $d)
    {
        $o = $this->loadOrder($d['id'] ?? '', ['pending']);
        $errors = [];
        $invoice = null;
        $invoiceDate = null;
        if ($o['type'] === 'new') {
            $invoice = $this->str($d, 'invoiceNo', 20) ?: $this->nextInvoiceNo();
            if (!preg_match('/^[A-Za-z0-9\/-]{3,20}$/', $invoice)) {
                $errors['invoiceNo'] = 'Số hoá đơn 3–20 ký tự, chỉ gồm chữ, số, / -';
            } elseif ((new Query())->from('dealer_order')->where(['invoice_no' => $invoice])->andWhere(['<>', 'id', $o['id']])->exists($this->db)) {
                $errors['invoiceNo'] = 'Số hoá đơn đã dùng cho đơn khác';
            }
            $invoiceDate = !empty($d['invoiceDate']) ? $this->isoDate($d['invoiceDate'], 'invoiceDate', $errors) : date('Y-m-d');
        }
        $this->fail($errors);

        $over = false;
        if ($o['type'] === 'new' && $o['dealer_id']) {
            $dealer = (new Query())->from('dealer')->where(['id' => $o['dealer_id']])->one($this->db);
            $plannedDebt = $o['debt_mode'] !== null ? (int)$o['debt_amount'] : (int)$o['total_amount'];
            $over = $dealer['current_debt'] + $plannedDebt > $dealer['credit_limit'];
        }
        $note = $this->str($d, 'note', 500);
        $this->setOrderStatus($o, 'confirmed', [
            'invoice_no' => $invoice, 'invoice_date' => $invoiceDate, 'approved_by' => $this->userId, 'approved_at' => date('Y-m-d H:i:s'),
            'note' => $note !== '' ? $note : $o['note'],
        ], $over ? 'Duyệt khi đại lý vượt hạn mức công nợ' : null);
        $next = $o['type'] === 'assembly' ? 'chờ kho xuất linh kiện' : 'chuyển xuống kho';
        return $this->orderResult('✓ Đã duyệt ' . $o['code'] . ($invoice ? ' · HĐ ' . $invoice : '') . ' — ' . $next, $o['code']);
    }

    /* ---------------- Kho: xuất kho (máy / linh kiện) ---------------- */

    private function exportOrder(array $d)
    {
        $o = $this->loadOrder($d['id'] ?? '', ['confirmed']);
        $errors = [];
        $date = !empty($d['exportDate']) ? $this->isoDate($d['exportDate'], 'exportDate', $errors) : date('Y-m-d');
        $note = $this->str($d, 'note', 500) ?: null;
        $parts = [];
        if ($o['type'] === 'assembly') {
            $parts = $this->readParts($d['parts'] ?? [], $errors);
        }
        $this->fail($errors);

        $extra = ['order_id' => $o['id'], 'note' => $note, 'created_at' => $date . ' ' . date('H:i:s')];
        $label = '';
        if ($o['type'] === 'new') {
            $lines = [];
            foreach ($o['items'] as $it) {
                $lines[] = ['product', $this->itemRow('product', $it['code']), (int)$it['qty'], (int)$it['unit_price']];
            }
            $v = $this->writeVoucher('export', 'sale', -1, $lines, $extra);
            foreach ($o['items'] as $it) {
                for ($i = 0; $i < (int)$it['qty']; $i++) {
                    $this->insert('warranty', [
                        'serial' => $this->newSerial($it['code']), 'qr_token' => Yii::$app->security->generateRandomString(12),
                        'product_id' => $it['product_id'], 'order_id' => $o['id'], 'dealer_id' => $o['dealer_id'], 'status' => 'unactivated',
                        'customer_name' => $o['customer_type'] === 'retail' ? $o['customer_name'] : null,
                        'customer_phone' => $o['customer_type'] === 'retail' ? $o['customer_phone'] : null,
                        'customer_address' => $o['customer_type'] === 'retail' ? $o['customer_address'] : null,
                    ]);
                }
            }
            $cost = 0;
            foreach ($o['items'] as $it) {
                $cost += (int)$it['qty'] * (int)$it['cost'];
            }
            $this->update('dealer_order', ['total_cost' => $cost], ['id' => $o['id']]);
            $label = 'xuất ' . array_sum(array_column($o['items'], 'qty')) . ' máy, sinh serial bảo hành — chờ nhân viên giao hàng gửi đi';
        } elseif ($o['type'] === 'warranty') {
            $current = $this->orderParts($o);
            if (!$current) {
                throw new Invalid([], 'Đơn bảo hành chưa có linh kiện');
            }
            $v = $this->writeVoucher('export', 'warranty', -1, $this->partLines($current), $extra);
            $label = 'xuất linh kiện bảo hành — chờ nhân viên giao hàng gửi đi';
        } else {
            $totalUnits = array_sum(array_column($o['items'], 'qty'));
            $cost = $this->writeOrderParts($o, $parts, $totalUnits);
            $this->update('dealer_order', ['total_cost' => $cost], ['id' => $o['id']]);
            $v = $this->writeVoucher('export', 'assembly_issue', -1, $this->partLines($this->orderParts($o)), $extra);
            $label = 'xuất ' . count($parts) . ' loại linh kiện — chờ bộ phận lắp ráp nhận';
        }
        $this->setOrderStatus($o, 'exported', ['exporter_id' => $this->userId, 'exported_at' => $date . ' ' . date('H:i:s')], 'Phiếu ' . $v['code']);
        return $this->orderResult('✓ Phiếu ' . $v['code'] . ': ' . $label, $o['code']);
    }

    /** Định mức linh kiện mặc định của đơn lắp ráp = BOM từng sản phẩm × số lượng (nút "Lấy tự động") */
    public static function assemblyBom(array $items)
    {
        $need = [];
        foreach ($items as $it) {
            $bom = (new Query())->select(['b.qty', 'pt.code'])->from('product_bom b')->innerJoin('part pt', 'pt.id = b.part_id')
                ->where(['b.product_id' => $it['product_id']])->all();
            foreach ($bom as $b) {
                $need[$b['code']] = ($need[$b['code']] ?? 0) + (int)$b['qty'] * (int)$it['qty'];
            }
        }
        return $need;
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

    /**
     * Đưa số nợ đã ghi sổ của đơn về $newDebt: chỉ ghi phần chênh lệch vào sổ công nợ
     * (debit = tăng, debit_reverse = giảm) và cập nhật dư nợ đại lý. Trả về chênh lệch đã ghi.
     */
    private function applyOrderDebt(array $o, $newDebt, $note)
    {
        $delta = (int)$newDebt - (int)$o['debt_recorded'];
        if ($delta !== 0) {
            $dealer = (new Query())->from('dealer')->where(['id' => $o['dealer_id']])->one($this->db);
            $after = (int)$dealer['current_debt'] + $delta;
            $this->insert('dealer_ledger', [
                'dealer_id' => $dealer['id'], 'type' => $delta > 0 ? 'debit' : 'debit_reverse', 'amount' => abs($delta),
                'balance_after' => $after, 'order_id' => $o['id'], 'note' => $note, 'created_by' => $this->userId,
            ]);
            $this->update('dealer', ['current_debt' => $after], ['id' => $dealer['id']]);
        }
        $this->update('dealer_order', ['debt_recorded' => (int)$newDebt], ['id' => $o['id']]);
        return $delta;
    }

    /** Chọn / sửa thanh toán – công nợ của đơn đại lý (mọi trạng thái trừ đã huỷ). */
    private function saveOrderDebt(array $d)
    {
        $o = $this->loadOrder($d['id'] ?? '', ['pending', 'confirmed', 'exported', 'shipping', 'delivered']);
        if ($o['type'] !== 'new' || !$o['dealer_id']) {
            throw new Invalid([], 'Chỉ đơn bán cho đại lý mới ghi công nợ');
        }
        $total = (int)$o['total_amount'];
        $mode = (string)($d['mode'] ?? '');
        $method = $this->str($d, 'paidMethod', 30);
        $note = $this->str($d, 'note', 300);
        $errors = [];
        if (!isset(D::DEBT_MODES[$mode])) {
            $errors['mode'] = 'Vui lòng chọn hình thức thanh toán';
        }
        $debt = $mode === 'full' ? $total : 0;
        if ($mode === 'partial') {
            $debt = $this->int($d, 'debtAmount');
            if ($debt === null || $debt === false || $debt <= 0) {
                $errors['debtAmount'] = 'Vui lòng nhập số tiền còn nợ';
            } elseif ($debt >= $total) {
                $errors['debtAmount'] = 'Số nợ phải nhỏ hơn giá trị đơn (' . D::money($total) . '). Nợ toàn bộ thì chọn "Ghi nợ cả đơn"';
            }
        }
        if (in_array($mode, ['partial', 'paid'], true) && !in_array($method, D::PAID_METHODS, true)) {
            $errors['paidMethod'] = 'Vui lòng chọn hình thức đã trả';
        }
        $this->fail($errors);

        $this->update('dealer_order', [
            'debt_mode' => $mode, 'debt_amount' => $debt, 'paid_method' => $mode === 'full' ? null : $method, 'debt_note' => $note ?: null,
            'debt_updated_at' => date('Y-m-d H:i:s'), 'debt_updated_by' => $this->userId,
        ], ['id' => $o['id']]);
        // Đơn chưa giao xong: nợ chỉ ghi sổ khi khách xác nhận đã nhận (receiveOrder), lúc này chỉ lưu lựa chọn
        $delta = 0;
        if ($o['status'] === 'delivered') {
            $delta = $this->applyOrderDebt($o, $debt, 'Sửa công nợ đơn ' . $o['code'] . ' · ' . D::DEBT_MODES[$mode] . ($note ? ' · ' . $note : ''));
        }
        $this->insert('dealer_order_log', [
            'order_id' => $o['id'], 'from_status' => $o['status'], 'to_status' => $o['status'], 'employee_id' => $this->userId,
            'note' => 'Công nợ: ' . D::DEBT_MODES[$mode] . ' · nợ ' . D::money($debt) . ($mode !== 'full' ? ' · đã trả ' . D::money($total - $debt) . ' (' . $method . ')' : ''),
        ]);
        $msg = '✓ Đơn ' . $o['code'] . ': ' . D::DEBT_MODES[$mode] . ($debt > 0 ? ' · nợ ' . D::money($debt) : '');
        if ($o['status'] !== 'delivered') {
            $msg .= ' · ghi vào công nợ khi giao xong';
        } elseif ($delta !== 0) {
            $msg .= ' · dư nợ đại lý ' . ($delta > 0 ? '+' : '−') . D::money(abs($delta));
        }
        return $this->orderResult($msg, $o['code']);
    }

    /* ---------------- Giao hàng: gửi nhà xe → khách xác nhận nhận ---------------- */

    private function shipOrder(array $d)
    {
        $o = $this->loadOrder($d['id'] ?? '', ['exported'], ['new', 'warranty']);
        $errors = [];
        $carrierId = (int)($d['carrierId'] ?? 0);
        if (!$carrierId || !(new Query())->from('carrier')->where(['id' => $carrierId, 'is_delete' => 0])->exists($this->db)) {
            $errors['carrierId'] = 'Vui lòng chọn nhà xe';
        }
        if ($this->role === 'delivery') {
            $deliveryId = $this->userId;
        } else {
            $deliveryId = $this->employeeByName($d['delivery'] ?? '', 'delivery');
            if (!$deliveryId) {
                $errors['delivery'] = 'Vui lòng chọn nhân viên giao hàng';
            }
        }
        $date = !empty($d['shipDate']) ? $this->isoDate($d['shipDate'], 'shipDate', $errors) : date('Y-m-d');
        $images = [];
        foreach ((array)($d['images'] ?? []) as $url) {
            $url = trim((string)$url);
            if ($url === '') {
                continue;
            }
            if (strpos($url, '/' . self::SHIP_IMAGE_DIR . '/') !== 0 || strpos($url, '..') !== false) {
                $errors['images'] = 'Ảnh không hợp lệ, vui lòng tải lại';
            }
            $images[] = $url;
        }
        if (count($images) > D::SHIP_IMAGE_MAX) {
            $errors['images'] = 'Tối đa ' . D::SHIP_IMAGE_MAX . ' ảnh';
        }
        $this->fail($errors);

        $this->setOrderStatus($o, 'shipping', [
            'carrier_id' => $carrierId, 'delivery_id' => $deliveryId, 'shipped_at' => $date . ' ' . date('H:i:s'),
            'ship_images' => $images ? json_encode(array_values($images)) : null, 'delivery_note' => $this->str($d, 'note', 500) ?: null,
        ]);
        $carrier = (new Query())->select('name')->from('carrier')->where(['id' => $carrierId])->scalar($this->db);
        return $this->orderResult('✓ Đã gửi ' . $o['code'] . ' qua nhà xe ' . $carrier . ($images ? ' · ' . count($images) . ' ảnh' : ''), $o['code']);
    }

    /** Khách đã nhận hàng → hoàn thành: ghi công nợ đại lý (đơn bán), tính hoa hồng Sale + giao hàng */
    private function receiveOrder(array $d)
    {
        $o = $this->loadOrder($d['id'] ?? '', ['shipping'], ['new', 'warranty']);
        $errors = [];
        if (!empty($d['receiverPhone']) && !self::isPhone($d['receiverPhone'])) {
            $errors['receiverPhone'] = 'Số điện thoại không hợp lệ';
        }
        $date = !empty($d['receivedDate']) ? $this->isoDate($d['receivedDate'], 'receivedDate', $errors) : date('Y-m-d');
        $this->fail($errors);

        $debt = 0;
        if ($o['type'] === 'new') {
            $total = (int)$o['total_amount'];
            if ($o['dealer_id']) {
                // Chưa chọn hình thức thanh toán → ghi nợ cả đơn
                $mode = $o['debt_mode'] ?: 'full';
                $debt = $mode === 'full' ? $total : ($mode === 'paid' ? 0 : min($total, (int)$o['debt_amount']));
                $this->update('dealer_order', ['debt_mode' => $mode, 'debt_amount' => $debt], ['id' => $o['id']]);
                $this->applyOrderDebt($o, $debt, 'Giao đơn ' . $o['code'] . ($mode !== 'full' ? ' · ' . D::DEBT_MODES[$mode] : ''));
                $this->update('dealer', ['total_purchase' => new Expression('total_purchase + ' . $total)], ['id' => $o['dealer_id']]);
            }
            foreach (['sale' => $o['sale_id'], 'delivery' => $o['delivery_id']] as $role => $empId) {
                if (!$empId || (new Query())->from('commission')->where(['order_id' => $o['id'], 'employee_id' => $empId, 'role' => $role])->exists($this->db)) {
                    continue;
                }
                $rate = D::COMMISSION_RATES[$role];
                $this->insert('commission', [
                    'order_id' => $o['id'], 'employee_id' => $empId, 'role' => $role, 'base_amount' => $total, 'rate' => $rate,
                    'amount' => (int)round($total * $rate / 100), 'period' => substr($date, 0, 7),
                ]);
            }
        }
        $receiver = $this->str($d, 'receiver');
        $this->setOrderStatus($o, 'delivered', [
            'receiver_name' => $receiver !== '' ? $receiver : ($o['customer_name'] ?: null), 'receiver_phone' => $this->str($d, 'receiverPhone', 30) ?: null,
            'received_by' => $this->userId, 'delivered_at' => $date . ' ' . date('H:i:s'),
        ], $this->str($d, 'note', 300) ?: null);
        $msg = '✓ ' . $o['code'] . ': khách đã nhận hàng';
        if ($o['type'] === 'new' && $o['dealer_id']) {
            $msg .= $debt > 0 ? ' · ghi nợ ' . D::money($debt) : ' · đã thanh toán đủ';
        }
        return $this->orderResult($msg, $o['code']);
    }

    /* ---------------- Lắp ráp: nhận linh kiện → bắt đầu → xong → kho nhập máy ---------------- */

    private function receiveParts(array $d)
    {
        $o = $this->loadOrder($d['id'] ?? '', ['exported'], ['assembly']);
        $now = date('Y-m-d H:i:s');
        $this->update('dealer_order_part', ['is_picked' => 1, 'picked_by' => $this->userId, 'picked_at' => $now], ['order_id' => $o['id']]);
        $this->setOrderStatus($o, 'received', ['parts_received_at' => $now, 'assembler_id' => $this->userId]);
        return $this->orderResult('✓ Đã nhận linh kiện đơn ' . $o['code'] . ' — bấm "Bắt đầu" khi bắt tay vào lắp', $o['code']);
    }

    private function startAssembly(array $d)
    {
        $o = $this->loadOrder($d['id'] ?? '', ['received'], ['assembly']);
        $this->setOrderStatus($o, 'assembling', ['started_at' => date('Y-m-d H:i:s'), 'assembler_id' => $this->userId]);
        return $this->orderResult('▶ Bắt đầu lắp ráp ' . $o['code'] . ' lúc ' . date('H:i'), $o['code']);
    }

    private function finishAssembly(array $d)
    {
        $o = $this->loadOrder($d['id'] ?? '', ['assembling'], ['assembly']);
        $now = date('Y-m-d H:i:s');
        $minutes = (int)round((strtotime($now) - strtotime($o['started_at'])) / 60);
        $this->setOrderStatus($o, 'assembled', ['finished_at' => $now], 'Thời gian lắp: ' . self::duration($minutes));
        return $this->orderResult('✓ ' . $o['code'] . ' lắp xong sau ' . self::duration($minutes) . ' — chờ kế toán nhập kho', $o['code']);
    }

    public static function duration($minutes)
    {
        $minutes = max(0, (int)$minutes);
        if ($minutes < 60) {
            return $minutes . ' phút';
        }
        $h = intdiv($minutes, 60);
        return ($h >= 24 ? intdiv($h, 24) . ' ngày ' . ($h % 24) . ' giờ' : $h . ' giờ ' . ($minutes % 60) . ' phút');
    }

    /** Nhập máy lắp xong vào kho (giá vốn = chi phí linh kiện chia đều cho số máy) */
    private function stockAssembly(array $d)
    {
        $o = $this->loadOrder($d['id'] ?? '', ['assembled'], ['assembly']);
        $units = max(1, array_sum(array_column($o['items'], 'qty')));
        $perUnit = (int)round((int)$o['total_cost'] / $units);
        $lines = [];
        foreach ($o['items'] as $it) {
            $lines[] = ['product', $this->itemRow('product', $it['code']), (int)$it['qty'], $perUnit];
        }
        $now = date('Y-m-d H:i:s');
        $v = $this->writeVoucher('import', 'assembly_finish', 1, $lines, ['order_id' => $o['id'], 'note' => 'Nhập máy lắp xong ' . $o['code']]);
        $this->setOrderStatus($o, 'delivered', ['stocked_at' => $now, 'stocked_by' => $this->userId], 'Phiếu ' . $v['code']);
        return $this->orderResult('✓ Phiếu ' . $v['code'] . ': nhập ' . $units . ' máy thành phẩm vào kho', $o['code']);
    }

    private function saveAssemblyNote(array $d)
    {
        $o = $this->loadOrder($d['id'] ?? '', ['exported', 'received', 'assembling', 'assembled'], ['assembly']);
        $this->update('dealer_order', ['assembly_note' => $this->str($d, 'note', 5000) ?: null], ['id' => $o['id']]);
        return ['message' => null, 'data' => []];
    }

    /* ---------------- Huỷ ---------------- */

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

    /* ======================= Nhà xe ======================= */

    private function saveCarrier(array $d)
    {
        $id = (int)($d['id'] ?? 0);
        $errors = [];
        $this->require($errors, $d, ['name' => 'tên nhà xe']);
        $phone = $this->str($d, 'phone', 30);
        if ($phone !== '' && !self::isPhone($phone)) {
            $errors['phone'] = 'Số điện thoại không hợp lệ';
        }
        $name = $this->str($d, 'name');
        if ($name !== '' && (new Query())->from('carrier')->where(['name' => $name, 'is_delete' => 0])->andWhere(['<>', 'id', $id])->exists($this->db)) {
            $errors['name'] = 'Đã có nhà xe tên này';
        }
        if ($id && !(new Query())->from('carrier')->where(['id' => $id, 'is_delete' => 0])->exists($this->db)) {
            throw new Invalid([], 'Nhà xe không tồn tại');
        }
        $this->fail($errors);
        $row = ['name' => $name, 'phone' => $phone ?: null, 'address' => $this->str($d, 'address', 300) ?: null, 'note' => $this->str($d, 'note', 500) ?: null];
        if ($id) {
            $this->update('carrier', $row + ['updated_at' => date('Y-m-d H:i:s')], ['id' => $id]);
        } else {
            $this->insert('carrier', $row + ['created_by' => $this->userId]);
        }
        return ['message' => '✓ Đã lưu nhà xe "' . $name . '"', 'data' => ['carriers' => R::carriers()]];
    }

    private function deleteCarrier(array $d)
    {
        $id = (int)($d['id'] ?? 0);
        $name = (new Query())->select('name')->from('carrier')->where(['id' => $id, 'is_delete' => 0])->scalar($this->db);
        if (!$name) {
            throw new Invalid([], 'Nhà xe không tồn tại');
        }
        // Xoá mềm: các đơn đã gửi vẫn giữ tên nhà xe
        $this->update('carrier', ['is_delete' => 1, 'updated_at' => date('Y-m-d H:i:s')], ['id' => $id]);
        return ['message' => 'Đã xoá nhà xe "' . $name . '"', 'data' => ['carriers' => R::carriers()]];
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
