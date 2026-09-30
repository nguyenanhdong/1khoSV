<?php

namespace console\controllers;

use Yii;
use yii\console\Controller;
use yii\console\ExitCode;
use yii\db\Connection;
use yii\db\Query;
use yii\rbac\DbManager;
use console\components\AgrimacDemoData as Demo;

/**
 * Nạp dữ liệu demo AgriMac vào DB: `php yii agrimac-seed`.
 * Bỏ qua nếu đã có đại lý DL001 (dùng --force để nạp phần còn thiếu).
 */
class AgrimacSeedController extends Controller
{
    const DEMO_PASSWORD = 'AgriMac@2025';

    public $force = false;
    public $reset = false;

    const AGRIMAC_TABLES = [
        'commission_payout', 'commission', 'warranty_claim', 'warranty', 'supplier_return', 'stock_voucher_item', 'stock_voucher',
        'dealer_order_log', 'dealer_order_part', 'dealer_order_item', 'dealer_order', 'crm_lead_note', 'crm_lead',
        'dealer_ledger', 'dealer', 'product_bom', 'part', 'part_category', 'supplier',
    ];

    /** @var Connection kết nối giống backend (common/config/db.php) */
    private $db;
    /** @var DbManager */
    private $auth;
    private $ids = [];

    public function options($actionID)
    {
        return array_merge(parent::options($actionID), ['force', 'reset']);
    }

    public function actionIndex()
    {
        $this->db = Yii::createObject(require Yii::getAlias('@common/config/db.php'));
        $this->auth = new DbManager(['db' => $this->db]);
        if ($this->reset && !$this->confirm('Xoá toàn bộ dữ liệu 1Kho CMS (đơn hàng, kho, công nợ, bảo hành...) và nạp lại bộ demo?')) {
            return ExitCode::OK;
        }
        if (!$this->force && !$this->reset && (new Query())->from('dealer')->where(['code' => 'DL001'])->exists($this->db)) {
            $this->stdout("Dữ liệu demo đã có (đại lý DL001). Dùng --force để nạp phần còn thiếu.\n");
            return ExitCode::OK;
        }

        $tx = $this->db->beginTransaction();
        try {
            if ($this->reset) {
                $this->clearAgrimacData();
            }
            $this->seedStaff();
            $this->seedCatalog();
            $this->seedDealers();
            $this->seedOrders();
            $this->seedLeads();
            $this->seedWarranty();
            $this->seedStock();
            $tx->commit();
        } catch (\Throwable $e) {
            $tx->rollBack();
            $this->stderr('Lỗi, đã rollback: ' . $e->getMessage() . "\n");
            return ExitCode::UNSPECIFIED_ERROR;
        }
        $this->stdout("✓ Đã nạp dữ liệu demo 1Kho CMS. Mật khẩu nhân viên demo: " . self::DEMO_PASSWORD . "\n");
        return ExitCode::OK;
    }

    /** Chỉ xoá dữ liệu thuộc AgriMac; dữ liệu sàn 1kho dùng chung bảng product/category/employee được giữ nguyên. */
    private function clearAgrimacData()
    {
        foreach (self::AGRIMAC_TABLES as $table) {
            $this->db->createCommand()->delete($table)->execute();
        }
        $this->db->createCommand()->delete('product', ['code' => array_column(Demo::products(), 'id')])->execute();
        $demoCats = (new Query())->select('id')->from('category')
            ->where(['name' => array_column(Demo::categories(), 'name'), 'parent_id' => 0])
            ->andWhere(['not exists', (new Query())->from('product p')->where('p.category_id = category.id')])
            ->andWhere(['not exists', (new Query())->from('category c2')->where('c2.parent_id = category.id')])
            ->column($this->db);
        if ($demoCats) {
            $this->db->createCommand()->delete('category', ['id' => $demoCats])->execute();
        }
        $staffIds = (new Query())->select('id')->from('employee')->where(['username' => array_column(Demo::staff(), 'username')])->column($this->db);
        $this->db->createCommand()->delete('auth_assignment', ['item_name' => array_keys(\backend\components\AgrimacData::ROLES)])->execute();
        $this->db->createCommand()->delete('employee', ['id' => $staffIds ?: [0]])->execute();
        $this->stdout("Đã xoá dữ liệu 1Kho CMS cũ.\n");
    }

    /** Lấy id theo cột unique; nếu chưa có thì insert. */
    private function upsert($table, array $where, array $values)
    {
        $id = (new Query())->select('id')->from($table)->where($where)->scalar($this->db);
        if ($id) {
            return (int)$id;
        }
        $this->db->createCommand()->insert($table, array_merge($where, $values))->execute();
        return (int)$this->db->getLastInsertID();
    }

    private function id($type, $code)
    {
        if ($code === null) {
            return null;
        }
        if (!isset($this->ids[$type][$code])) {
            throw new \RuntimeException("Không tìm thấy $type '$code'");
        }
        return $this->ids[$type][$code];
    }

    private function seedStaff()
    {
        $auth = $this->auth;
        $this->ids['employee']['admin'] = (int)(new Query())->select('id')->from('employee')->where(['is_admin' => 1])->orderBy('id')->scalar($this->db);
        foreach (Demo::staff() as $u) {
            $id = $this->upsert('employee', ['username' => $u['username']], [
                'fullname'    => $u['name'],
                'email'       => $u['email'],
                'password'    => md5(md5(self::DEMO_PASSWORD)),
                'auth_key'    => Yii::$app->security->generateRandomString(32),
                'is_active'   => 1,
                'is_admin'    => 0,
                'can_use_app' => in_array($u['role'], ['sale', 'delivery'], true) ? 1 : 0,
                'create_date' => time(),
            ]);
            $this->ids['employee'][$u['username']] = $id;
            if (!$auth->getAssignment($u['role'], $id)) {
                $auth->assign($auth->getRole($u['role']), $id);
            }
        }
    }

    private function seedCatalog()
    {
        foreach (Demo::categories() as $c) {
            $this->ids['category'][$c['id']] = $this->upsert('category', ['name' => $c['name'], 'parent_id' => 0, 'is_delete' => 0], ['status' => 1]);
        }
        foreach (Demo::suppliers() as $s) {
            $this->ids['supplier'][$s['id']] = $this->upsert('supplier', ['code' => $s['id']], [
                'name' => $s['name'], 'phone' => $s['contact'], 'email' => $s['email'] ?: null, 'address' => $s['address'],
            ]);
        }
        foreach (Demo::products() as $p) {
            $this->ids['product'][$p['id']] = $this->upsert('product', ['code' => $p['id']], [
                'name' => $p['name'], 'category_id' => $this->id('category', $p['catId']),
                'supplier_id' => $this->id('supplier', $p['supId']), 'price' => $p['price'], 'price_discount' => 0,
                'cost_price' => $p['cost'], 'source_type' => $p['sourceType'], 'horsepower' => $p['hp'], 'drive_type' => $p['drive'],
                'specs' => $p['specs'], 'weight' => $p['weight'], 'quantity_in_stock' => $p['stock'], 'min_stock' => $p['minStock'],
                'status' => 1, 'is_delete' => 0, 'image' => '',
            ]);
        }
        foreach (array_values(array_unique(array_column(Demo::parts(), 'cat'))) as $i => $name) {
            $this->ids['part_category'][$name] = $this->upsert('part_category', ['name' => $name], ['sort_order' => $i + 1]);
        }
        foreach (Demo::parts() as $p) {
            $this->ids['part'][$p['id']] = $this->upsert('part', ['code' => $p['id']], [
                'name' => $p['name'], 'category_id' => $this->id('part_category', $p['cat']), 'unit' => $p['unit'],
                'cost_price' => $p['cost'], 'stock' => $p['stock'], 'min_stock' => $p['minStock'],
            ]);
        }
        foreach (Demo::defaultBoms() as $productCode => $bom) {
            foreach ($bom as [$partCode, $qty]) {
                $this->upsert('product_bom', ['product_id' => $this->id('product', $productCode), 'part_id' => $this->id('part', $partCode)], ['qty' => $qty]);
            }
        }
    }

    private function seedDealers()
    {
        $provinces = (new Query())->select(['id', 'province_name'])->from('province')->indexBy('province_name')->column($this->db);
        foreach (Demo::dealers() as $d) {
            $id = $this->upsert('dealer', ['code' => $d['id']], [
                'name' => $d['name'], 'phone' => $d['contact'], 'province_id' => $provinces[$d['province']] ?? null, 'level' => $d['level'],
                'credit_limit' => $d['limit'], 'current_debt' => $d['debt'], 'total_purchase' => $d['total'],
            ]);
            $this->ids['dealer'][$d['id']] = $id;
            if ($d['debt'] > 0 && !(new Query())->from('dealer_ledger')->where(['dealer_id' => $id])->exists($this->db)) {
                $this->db->createCommand()->insert('dealer_ledger', [
                    'dealer_id' => $id, 'type' => 'adjust', 'amount' => $d['debt'], 'balance_after' => $d['debt'],
                    'note' => 'Số dư công nợ đầu kỳ', 'created_by' => $this->ids['employee']['admin'],
                ])->execute();
            }
        }
    }

    private function seedOrders()
    {
        $boms = Demo::defaultBoms();
        $parts = array_column(Demo::parts(), null, 'id');
        $flow = ['pending', 'confirmed', 'assembling', 'assembled', 'delivering', 'delivered'];
        foreach (Demo::orders() as $o) {
            $total = $o['qty'] * $o['price'];
            $bom = $o['bom'] === true ? $boms[$o['product']] : ($o['bom'] ?: []);
            $cost = array_sum(array_map(function ($b) use ($parts, $o) {
                return $parts[$b[0]]['cost'] * $b[1] * $o['qty'];
            }, $bom));
            $step = array_search($o['status'], $flow, true);
            $id = $this->upsert('dealer_order', ['code' => $o['id']], [
                'type' => $o['type'], 'dealer_id' => $this->id('dealer', $o['dealer']), 'status' => $o['status'],
                'total_amount' => $total, 'total_cost' => $step >= 2 ? $cost : 0,
                'sale_id' => $this->id('employee', $o['sale']), 'approved_by' => $step >= 1 ? $this->ids['employee']['hoa.pt'] : null,
                'assembler_id' => $step >= 3 ? $this->ids['employee']['loi.nt'] : null,
                'exporter_id' => $step >= 4 ? $this->ids['employee']['duc.vm'] : null,
                'delivery_id' => $this->id('employee', $o['delivery']), 'invoice_no' => $o['invoiceNo'] ?? null,
                'invoice_date' => !empty($o['invoiceNo']) ? $o['date'] : null, 'ordered_at' => $o['date'] . ' 09:00:00',
                'delivered_at' => isset($o['deliveredAt']) ? $o['deliveredAt'] . ' 16:00:00' : null,
                'receiver_name' => $o['receiver'] ?? null,
            ]);
            $this->ids['order'][$o['id']] = $id;
            $itemId = $this->upsert('dealer_order_item', ['order_id' => $id, 'product_id' => $this->id('product', $o['product'])], [
                'qty' => $o['qty'], 'unit_price' => $o['price'], 'line_total' => $total,
            ]);
            foreach ($bom as [$partCode, $qty]) {
                $this->upsert('dealer_order_part', ['order_id' => $id, 'order_item_id' => $itemId, 'part_id' => $this->id('part', $partCode)], [
                    'qty_per_unit' => $qty, 'qty_total' => $qty * $o['qty'], 'unit_cost' => $parts[$partCode]['cost'], 'is_picked' => $step >= 3 ? 1 : 0,
                ]);
            }
            if (!(new Query())->from('dealer_order_log')->where(['order_id' => $id])->exists($this->db)) {
                for ($i = 0; $i <= $step; $i++) {
                    $this->db->createCommand()->insert('dealer_order_log', [
                        'order_id' => $id, 'from_status' => $i ? $flow[$i - 1] : null, 'to_status' => $flow[$i],
                        'employee_id' => $this->ids['employee']['admin'], 'note' => 'Dữ liệu demo',
                    ])->execute();
                }
            }
            if ($o['status'] === 'delivered' && $o['type'] === 'new') {
                foreach (['sale' => $o['sale'], 'delivery' => $o['delivery']] as $role => $username) {
                    $rate = \backend\components\AgrimacData::COMMISSION_RATES[$role];
                    $this->upsert('commission', ['order_id' => $id, 'employee_id' => $this->id('employee', $username), 'role' => $role], [
                        'base_amount' => $total, 'rate' => $rate, 'amount' => (int)round($total * $rate / 100), 'period' => substr($o['date'], 0, 7),
                    ]);
                }
            }
        }
    }

    private function seedLeads()
    {
        foreach (Demo::leads() as $l) {
            $id = $this->upsert('crm_lead', ['phone' => $l['phone']], [
                'name' => $l['name'], 'dealer_id' => $this->id('dealer', $l['dealer']), 'sale_id' => $this->id('employee', $l['sale']),
                'product_id' => $this->id('product', $l['product']), 'expected_value' => $l['value'], 'stage' => $l['stage'],
            ]);
            if (!(new Query())->from('crm_lead_note')->where(['lead_id' => $id])->exists($this->db)) {
                foreach ($l['notes'] as [$date, $text]) {
                    $this->db->createCommand()->insert('crm_lead_note', [
                        'lead_id' => $id, 'content' => $text, 'created_by' => $this->id('employee', $l['sale']), 'created_at' => $date . ' 10:00:00',
                    ])->execute();
                }
            }
        }
    }

    private function seedWarranty()
    {
        $months = \backend\components\AgrimacData::WARRANTY_MONTHS;
        foreach (Demo::warranties() as $w) {
            $this->ids['warranty'][$w['serial']] = $this->upsert('warranty', ['serial' => $w['serial']], [
                'qr_token' => $w['qr'], 'product_id' => $this->id('product', $w['product']), 'order_id' => $this->id('order', $w['order']),
                'dealer_id' => $this->id('dealer', $w['dealer']), 'customer_name' => $w['custName'], 'customer_phone' => $w['phone'],
                'activated_at' => $w['activatedAt'], 'claim_count' => $w['claims'], 'status' => $w['status'],
                'expires_at' => $w['activatedAt'] ? date('Y-m-d', strtotime($w['activatedAt'] . " +$months months")) : null,
            ]);
        }
        foreach (Demo::claims() as $c) {
            $claimId = $this->upsert('warranty_claim', ['code' => $c['id']], [
                'warranty_id' => $this->id('warranty', $c['serial']), 'issue' => $c['issue'], 'status' => $c['status'],
                'assignee_id' => $this->id('employee', $c['assignee']), 'note' => $c['note'], 'created_at' => $c['created'],
            ]);
            foreach (Demo::orders() as $o) {
                if (($o['claim'] ?? null) === $c['id']) {
                    $this->db->createCommand()->update('dealer_order', ['claim_id' => $claimId], ['id' => $this->id('order', $o['id'])])->execute();
                    $this->db->createCommand()->update('warranty_claim', ['order_id' => $this->id('order', $o['id'])], ['id' => $claimId])->execute();
                }
            }
        }
    }

    private function seedStock()
    {
        foreach (Demo::stockVouchers() as $v) {
            $exists = (new Query())->from('stock_voucher')->where(['code' => $v['id']])->exists($this->db);
            $voucherId = $this->upsert('stock_voucher', ['code' => $v['id']], [
                'type' => $v['type'], 'reason' => $v['reason'], 'supplier_id' => $this->id('supplier', $v['supplier']),
                'order_id' => $this->id('order', $v['order']), 'total_amount' => $v['qty'] * $v['price'],
                'created_by' => $this->ids['employee']['duc.vm'], 'created_at' => $v['date'] . ' 08:30:00',
            ]);
            if (!$exists) {
                $this->db->createCommand()->insert('stock_voucher_item', [
                    'voucher_id' => $voucherId, 'item_type' => $v['itemType'], 'item_id' => $this->id($v['itemType'], $v['item']),
                    'qty' => $v['qty'], 'unit_price' => $v['price'],
                ])->execute();
            }
        }
        foreach (Demo::suppliers() as $s) {
            foreach ($s['returns'] as $r) {
                $where = ['supplier_id' => $this->id('supplier', $s['id']), 'item_type' => $r['itemType'], 'item_id' => $this->id($r['itemType'], $r['item'])];
                $this->upsert('supplier_return', $where, [
                    'qty' => $r['qty'], 'reason' => $r['reason'], 'status' => $r['status'],
                    'created_by' => $this->ids['employee']['duc.vm'], 'created_at' => $r['date'] . ' 11:00:00',
                ]);
            }
        }
    }
}
