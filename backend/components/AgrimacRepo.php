<?php

namespace backend\components;

use Yii;
use yii\db\Query;
use backend\components\AgrimacData as D;

/**
 * Đọc dữ liệu AgriMac từ DB, trả về mảng đúng định dạng view/JS đang dùng.
 * Khoá hiển thị (id) là mã nghiệp vụ: SP001, LK001, NCC01, DL001, DH2025001...
 */
class AgrimacRepo
{
    private static $cache = [];

    public static function flush()
    {
        self::$cache = [];
    }

    private static function once($key, callable $fn)
    {
        if (!array_key_exists($key, self::$cache)) {
            self::$cache[$key] = $fn();
        }
        return self::$cache[$key];
    }

    public static function vnDate($value, $withTime = false)
    {
        if (empty($value)) {
            return null;
        }
        $ts = is_numeric($value) ? (int)$value : strtotime($value);
        return date($withTime ? 'd/m/Y H:i' : 'd/m/Y', $ts);
    }

    public static function code($prefix, $id, $len = 3)
    {
        return $prefix . str_pad((string)$id, $len, '0', STR_PAD_LEFT);
    }

    /* ---------------- Danh mục gốc ---------------- */

    /** Cây chuyên mục (cha → con), count = số sản phẩm của mục (mục cha gồm cả mục con). */
    public static function categories()
    {
        return self::once('categories', function () {
            $counts = (new Query())->select(['n' => 'COUNT(*)', 'category_id'])->from('product')->where(['is_delete' => 0])
                ->groupBy('category_id')->indexBy('category_id')->column();
            $rows = (new Query())->select(['id', 'name', 'parent_id'])->from('category')->where(['is_delete' => 0])
                ->orderBy(['sort_order' => SORT_ASC, 'id' => SORT_ASC])->all();
            $children = [];
            foreach ($rows as $r) {
                $children[(int)$r['parent_id']][] = $r;
            }
            $out = [];
            foreach ($children[0] ?? [] as $parent) {
                $kids = $children[$parent['id']] ?? [];
                $total = (int)($counts[$parent['id']] ?? 0);
                foreach ($kids as $k) {
                    $total += (int)($counts[$k['id']] ?? 0);
                }
                $out[] = ['id' => (string)$parent['id'], 'name' => $parent['name'], 'parent' => null, 'count' => $total];
                foreach ($kids as $k) {
                    $out[] = ['id' => (string)$k['id'], 'name' => $k['name'], 'parent' => (string)$parent['id'], 'count' => (int)($counts[$k['id']] ?? 0)];
                }
            }
            return $out;
        });
    }

    public static function suppliers()
    {
        return self::once('suppliers', function () {
            $returns = [];
            $rows = (new Query())->select(['r.*', 'item_name' => 'COALESCE(p.name, pt.name)'])->from('supplier_return r')
                ->leftJoin('product p', "r.item_type = 'product' AND p.id = r.item_id")
                ->leftJoin('part pt', "r.item_type = 'part' AND pt.id = r.item_id")
                ->orderBy(['r.id' => SORT_DESC])->all();
            foreach ($rows as $r) {
                $returns[$r['supplier_id']][] = [
                    'id' => self::code('TH', $r['id']), 'itemType' => $r['item_type'], 'item' => $r['item_name'], 'qty' => (int)$r['qty'],
                    'reason' => $r['reason'], 'status' => $r['status'], 'date' => self::vnDate($r['created_at']),
                ];
            }
            return array_map(function ($s) use ($returns) {
                return [
                    'id' => $s['code'], 'name' => $s['name'], 'contact' => $s['phone'], 'email' => $s['email'], 'address' => $s['address'],
                    'taxCode' => $s['tax_code'], 'returns' => $returns[$s['id']] ?? [],
                ];
            }, (new Query())->from('supplier')->where(['status' => 1])->orderBy('code')->all());
        });
    }

    private static function productQuery()
    {
        return (new Query())->select(['p.*', 'sup_code' => 's.code'])->from('product p')
            ->leftJoin('supplier s', 's.id = p.supplier_id')->where(['p.is_delete' => 0]);
    }

    private static function productRow(array $p)
    {
        return [
            'id' => $p['code'], 'name' => $p['name'], 'catId' => (string)$p['category_id'], 'supId' => $p['sup_code'],
            'price' => (int)$p['price'], 'cost' => (int)$p['cost_price'], 'stock' => (int)$p['quantity_in_stock'],
            'specs' => $p['specs'], 'hp' => $p['horsepower'] !== null ? (int)$p['horsepower'] : null, 'drive' => $p['drive_type'],
            'weight' => $p['weight'] ? (int)$p['weight'] : null, 'sourceType' => $p['source_type'], 'minStock' => (int)$p['min_stock'],
            'description' => (string)$p['description'], 'images' => array_values(array_filter(explode(';', (string)$p['image']))),
        ];
    }

    public static function product($code)
    {
        $row = self::productQuery()->andWhere(['p.code' => (string)$code])->one();
        return $row ? self::productRow($row) : null;
    }

    public static function productsByCodes(array $codes)
    {
        $codes = array_values(array_unique(array_filter($codes)));
        return $codes ? array_map([self::class, 'productRow'], self::productQuery()->andWhere(['p.code' => $codes])->orderBy('p.code')->all()) : [];
    }

    /** Danh sách có phân trang. $cat là chuyên mục cha thì gồm cả sản phẩm của mục con. */
    public static function productList($q = '', $cat = '', $page = 1, $perPage = 24)
    {
        $query = self::productQuery();
        if ($cat !== '' && $cat !== null) {
            $ids = (new Query())->select('id')->from('category')->where(['parent_id' => (int)$cat, 'is_delete' => 0])->column();
            $query->andWhere(['p.category_id' => array_merge([(int)$cat], array_map('intval', $ids))]);
        }
        $q = trim((string)$q);
        if ($q !== '') {
            $query->andWhere(['or', ['like', 'p.name', $q], ['like', 'p.code', $q]]);
        }
        $total = (int)(clone $query)->count();
        $perPage = max(1, min(100, (int)$perPage));
        $pages = max(1, (int)ceil($total / $perPage));
        $page = max(1, min($pages, (int)$page));
        $rows = $query->orderBy(['p.id' => SORT_DESC])->offset(($page - 1) * $perPage)->limit($perPage)->all();
        return ['rows' => array_map([self::class, 'productRow'], $rows), 'total' => $total, 'page' => $page, 'pages' => $pages, 'perPage' => $perPage];
    }

    /* ---------- Đơn hàng sàn 1kho (bảng `order`, khách mua qua app/web) ---------- */

    private static function marketOrderQuery()
    {
        return (new Query())->from(['o' => 'order'])
            ->leftJoin(['u' => 'user'], 'u.id = o.user_id')
            ->leftJoin(['a' => 'agent'], 'a.id = o.agent_id AND o.agent_id > 0')
            ->leftJoin(['v' => 'voucher'], 'v.id = o.voucher_id AND o.voucher_id > 0');
    }

    public static function marketOrderCounts()
    {
        $rows = (new Query())->select(['status', 'n' => 'COUNT(*)'])->from('order')->groupBy('status')->all();
        $counts = array_fill_keys(array_keys(D::MARKET_ORDER_STATUS), 0);
        foreach ($rows as $r) {
            $counts[(int)$r['status']] = (int)$r['n'];
        }
        return $counts;
    }

    public static function marketOrderList($q = '', $status = '', $payment = '', $page = 1, $perPage = 20)
    {
        $query = self::marketOrderQuery();
        if ($status !== '' && $status !== null && isset(D::MARKET_ORDER_STATUS[(int)$status])) {
            $query->andWhere(['o.status' => (int)$status]);
        }
        if ($payment !== '' && $payment !== null && isset(D::MARKET_PAYMENT[(int)$payment])) {
            $query->andWhere(['o.type_payment' => (int)$payment]);
        }
        $q = trim((string)$q);
        if ($q !== '') {
            $idQ = ltrim(preg_replace('/^#|^DS/i', '', $q), '0');
            $productOrders = (new Query())->select('op.order_id')->from(['op' => 'order_product'])
                ->innerJoin(['p' => 'product'], 'p.id = op.product_id')
                ->where(['or', ['like', 'p.name', $q], ['like', 'p.code', $q]]);
            $query->andWhere(['or',
                ctype_digit($idQ) ? ['o.id' => (int)$idQ] : '0=1',
                ['like', 'u.fullname', $q], ['like', 'u.phone', $q], ['like', 'a.fullname', $q],
                ['o.id' => $productOrders],
            ]);
        }
        $total = (int)(clone $query)->count('o.id');
        $perPage = max(1, min(100, (int)$perPage));
        $pages = max(1, (int)ceil($total / $perPage));
        $page = max(1, min($pages, (int)$page));
        $rows = $query->select([
            'o.id', 'o.create_at', 'o.status', 'o.type_payment', 'o.price', 'o.price_voucher', 'o.price_wallet', 'o.fee_ship', 'o.total_price',
            'customer' => 'u.fullname', 'phone' => 'u.phone', 'agent' => 'a.fullname',
        ])->orderBy(['o.id' => SORT_DESC])->offset(($page - 1) * $perPage)->limit($perPage)->all();

        $items = [];
        if ($rows) {
            $lines = (new Query())->select(['op.order_id', 'op.quantity', 'p.name'])->from(['op' => 'order_product'])
                ->leftJoin(['p' => 'product'], 'p.id = op.product_id')
                ->where(['op.order_id' => array_column($rows, 'id')])->orderBy(['op.id' => SORT_ASC])->all();
            foreach ($lines as $l) {
                $items[$l['order_id']][] = $l;
            }
        }
        $out = [];
        foreach ($rows as $r) {
            $lines = $items[$r['id']] ?? [];
            $out[] = [
                'id'       => (int)$r['id'],
                'code'     => '#' . $r['id'],
                'date'     => self::vnDate($r['create_at'], true),
                'customer' => (string)$r['customer'],
                'phone'    => (string)$r['phone'],
                'agent'    => $r['agent'] ?: '1KHO',
                'product'  => $lines ? (string)$lines[0]['name'] : '',
                'lines'    => count($lines),
                'qty'      => array_sum(array_map('intval', array_column($lines, 'quantity'))),
                'total'    => (int)$r['total_price'],
                'payment'  => (int)$r['type_payment'],
                'status'   => (int)$r['status'],
            ];
        }
        return ['rows' => $out, 'total' => $total, 'page' => $page, 'pages' => $pages, 'perPage' => $perPage, 'counts' => self::marketOrderCounts()];
    }

    /** Toàn bộ thông tin một đơn sàn: khách, địa chỉ giao, sản phẩm, thanh toán, voucher, đại lý, huỷ, hoàn tiền. */
    public static function marketOrder($id)
    {
        $o = self::marketOrderQuery()->select([
            'o.*', 'customer' => 'u.fullname', 'customer_phone' => 'u.phone', 'customer_address' => 'u.address',
            'customer_district' => 'u.district', 'customer_province' => 'u.province', 'wallet_point' => 'u.wallet_point',
            'agent_name' => 'a.fullname', 'agent_phone' => 'a.phone', 'agent_address' => 'a.address', 'agent_province' => 'a.province',
            'voucher_name' => 'v.name', 'voucher_type_price' => 'v.type_price', 'voucher_price' => 'v.price',
        ])->where(['o.id' => (int)$id])->one();
        if (!$o) {
            return null;
        }
        $address = $o['delivery_address_id'] > 0
            ? (new Query())->from('user_delivery_address')->where(['id' => (int)$o['delivery_address_id']])->one()
            : null;
        $lines = (new Query())->select(['op.*', 'p.code', 'p.name', 'p.image', 'p.is_delete'])->from(['op' => 'order_product'])
            ->leftJoin(['p' => 'product'], 'p.id = op.product_id')->where(['op.order_id' => (int)$o['id']])->orderBy(['op.id' => SORT_ASC])->all();
        $refunds = (new Query())->from('order_refund')->where(['order_id' => (int)$o['id']])->orderBy(['id' => SORT_DESC])->all();
        $join = function (...$parts) {
            return implode(', ', array_filter(array_map('trim', array_map('strval', $parts)), 'strlen'));
        };
        return [
            'id'        => (int)$o['id'],
            'code'      => '#' . $o['id'],
            'status'    => (int)$o['status'],
            'createdAt' => self::vnDate($o['create_at'], true),
            'updatedAt' => self::vnDate($o['updated_at'], true),
            'customer'  => [
                'id' => (int)$o['user_id'], 'name' => (string)$o['customer'], 'phone' => (string)$o['customer_phone'],
                'address' => $join($o['customer_address'], $o['customer_district'], $o['customer_province']),
                'walletPoint' => (int)$o['wallet_point'],
            ],
            'shipping'  => $address ? [
                'name' => (string)$address['fullname'], 'phone' => (string)$address['phone'],
                'address' => $join($address['address'], $address['district'], $address['province']),
            ] : null,
            'deliveryAddressId' => (int)$o['delivery_address_id'],
            'agent'     => $o['agent_id'] > 0 ? [
                'id' => (int)$o['agent_id'], 'name' => (string)$o['agent_name'], 'phone' => (string)$o['agent_phone'],
                'address' => $join($o['agent_address'], $o['agent_province']),
            ] : null,
            'items'     => array_map(function ($l) {
                $images = array_values(array_filter(explode(';', (string)$l['image'])));
                return [
                    'productId' => (int)$l['product_id'], 'code' => (string)$l['code'], 'name' => (string)($l['name'] ?? 'Sản phẩm #' . $l['product_id']),
                    'image' => $images[0] ?? '', 'deleted' => (int)$l['is_delete'] === 1,
                    'priceOrigin' => (int)$l['price_origin'], 'price' => (int)$l['price'], 'qty' => (int)$l['quantity'],
                    'total' => (int)$l['total_price'], 'classificationId' => (int)$l['product_classification_id'],
                ];
            }, $lines),
            'money'     => [
                'price' => (int)$o['price'], 'voucher' => (int)$o['price_voucher'], 'wallet' => (int)$o['price_wallet'],
                'feeShip' => (int)$o['fee_ship'], 'total' => (int)$o['total_price'],
            ],
            'useWallet' => (int)$o['use_wallet_payment'] === 1,
            'voucher'   => $o['voucher_id'] > 0 ? [
                'id' => (int)$o['voucher_id'], 'name' => $o['voucher_name'] !== null ? (string)$o['voucher_name'] : 'Voucher #' . $o['voucher_id'],
                'deleted' => $o['voucher_name'] === null,
                'pointRefundable' => (float)$o['voucher_point_refundable'],
            ] : null,
            'payment'   => (int)$o['type_payment'],
            'paidAt'    => self::vnDate($o['time_payment'], true),
            'reviewed'  => (int)$o['is_review'] === 1,
            'note'      => (string)$o['reason_cancel'],
            'cancel'    => (int)$o['status'] === D::MARKET_ORDER_CANCELLED || $o['time_cancel'] ? [
                'by' => D::MARKET_CANCEL_BY[(int)$o['type_cancel']] ?? null,
                'reason' => (string)$o['reason_cancel'],
                'at' => self::vnDate($o['time_cancel'], true),
            ] : null,
            'refunds'   => array_map(function ($r) {
                return [
                    'id' => (int)$r['id'], 'amount' => (int)$r['price_refund'],
                    'situation' => D::MARKET_REFUND_SITUATION[(int)$r['type_situation']] ?? '',
                    'reason' => (string)$r['reason'], 'note' => (string)$r['note'],
                    'status' => (int)$r['status'], 'statusLabel' => D::MARKET_REFUND_STATUS[(int)$r['status']] ?? '',
                    'rejectReason' => (string)$r['reason_cancel'],
                    'createdAt' => self::vnDate($r['created_at'], true), 'processedAt' => self::vnDate($r['time_process'], true),
                ];
            }, $refunds),
        ];
    }

    /** Gợi ý cho ô chọn sản phẩm. */
    public static function productSearch($q, $limit = 20)
    {
        $query = self::productQuery();
        $q = trim((string)$q);
        if ($q !== '') {
            $query->andWhere(['or', ['like', 'p.name', $q], ['like', 'p.code', $q]]);
        }
        return array_map([self::class, 'productRow'], $query->orderBy(['p.id' => SORT_DESC])->limit($limit)->all());
    }

    public static function lowStockProducts($limit = 8)
    {
        return array_map([self::class, 'productRow'], self::productQuery()->andWhere('p.quantity_in_stock <= p.min_stock')
            ->orderBy(['p.quantity_in_stock' => SORT_ASC, 'p.id' => SORT_DESC])->limit($limit)->all());
    }

    public static function lowStockCount()
    {
        return (int)self::productQuery()->andWhere('p.quantity_in_stock <= p.min_stock')->count();
    }

    public static function suppliedProducts()
    {
        return array_map([self::class, 'productRow'], self::productQuery()->andWhere(['not', ['p.supplier_id' => null]])->orderBy('p.code')->all());
    }

    public static function stockValue()
    {
        return (int)(new Query())->from('product')->where(['is_delete' => 0])->sum('quantity_in_stock * cost_price');
    }

    public static function parts()
    {
        return self::once('parts', function () {
            $rows = (new Query())->select(['p.*', 'cat' => 'c.name', 'sup_code' => 's.code'])->from('part p')
                ->innerJoin('part_category c', 'c.id = p.category_id')->leftJoin('supplier s', 's.id = p.supplier_id')
                ->where(['p.status' => 1])->orderBy('p.code')->all();
            return array_map(function ($p) {
                return [
                    'id' => $p['code'], 'name' => $p['name'], 'cat' => $p['cat'], 'unit' => $p['unit'], 'cost' => (int)$p['cost_price'],
                    'stock' => (int)$p['stock'], 'minStock' => (int)$p['min_stock'], 'supId' => $p['sup_code'],
                ];
            }, $rows);
        });
    }

    public static function partCategories()
    {
        return (new Query())->select('name')->from('part_category')->where(['status' => 1])->orderBy(['sort_order' => SORT_ASC, 'id' => SORT_ASC])->column();
    }

    /** BOM mặc định theo tên sản phẩm (trang Đơn hàng dùng tên để tra). */
    public static function defaultBoms()
    {
        return self::once('boms', function () {
            $out = [];
            $rows = (new Query())->select(['product' => 'p.name', 'part' => 'pt.code', 'b.qty'])->from('product_bom b')
                ->innerJoin('product p', 'p.id = b.product_id')->innerJoin('part pt', 'pt.id = b.part_id')->orderBy('b.id')->all();
            foreach ($rows as $r) {
                $out[$r['product']][] = ['id' => $r['part'], 'qty' => (int)$r['qty']];
            }
            return $out;
        });
    }

    public static function dealers()
    {
        return self::once('dealers', function () {
            $history = [];
            $orders = (new Query())->select(['o.dealer_id', 'o.code', 'o.total_amount', 'at' => 'COALESCE(o.delivered_at, o.ordered_at)'])
                ->from('dealer_order o')->where(['o.status' => 'delivered', 'o.type' => 'new'])->all();
            foreach ($orders as $o) {
                $history[$o['dealer_id']][] = ['ts' => strtotime($o['at']), 'order' => $o['code'], 'val' => (int)$o['total_amount'], 'payment' => false];
            }
            $payments = (new Query())->from('dealer_ledger')->where(['type' => 'credit'])->all();
            foreach ($payments as $l) {
                $history[$l['dealer_id']][] = [
                    'ts' => strtotime($l['created_at']), 'order' => 'Thu tiền · ' . $l['method'] . ($l['note'] ? ' · ' . $l['note'] : ''),
                    'val' => (int)$l['amount'], 'payment' => true,
                ];
            }
            $rows = (new Query())->select(['d.*', 'province' => 'pr.province_name'])->from('dealer d')
                ->leftJoin('province pr', 'pr.id = d.province_id')->where(['d.status' => 1])->orderBy('d.code')->all();
            return array_map(function ($d) use ($history) {
                $h = $history[$d['id']] ?? [];
                usort($h, function ($a, $b) {
                    return $b['ts'] - $a['ts'];
                });
                return [
                    'id' => $d['code'], 'name' => $d['name'], 'province' => $d['province'], 'contact' => $d['phone'], 'contactName' => $d['contact_name'],
                    'level' => D::DEALER_LEVEL_CODES[$d['level']], 'limit' => (int)$d['credit_limit'], 'debt' => (int)$d['current_debt'],
                    'total' => (int)$d['total_purchase'], 'taxCode' => $d['tax_code'], 'address' => $d['address'],
                    'history' => array_map(function ($x) {
                        return ['date' => date('d/m', $x['ts']), 'order' => $x['order'], 'val' => $x['val'], 'payment' => $x['payment']];
                    }, $h),
                ];
            }, $rows);
        });
    }

    /* ---------------- Nhân sự ---------------- */

    /** Nhân viên có vai trò AgriMac (admin hệ thống hiển thị vai trò admin). */
    public static function staff()
    {
        return self::once('staff', function () {
            $roles = (new Query())->select(['user_id', 'item_name'])->from('auth_assignment')->where(['item_name' => array_keys(D::ROLES)])->all();
            $roleOf = [];
            foreach ($roles as $r) {
                $roleOf[$r['user_id']] = $roleOf[$r['user_id']] ?? $r['item_name'];
            }
            $rows = (new Query())->from('employee')->where(['or', ['id' => array_keys($roleOf) ?: [0]], ['is_admin' => 1]])->orderBy('id')->all();
            return array_map(function ($e) use ($roleOf) {
                return [
                    'id' => self::code('U', $e['id'], 2), 'dbId' => (int)$e['id'], 'name' => trim($e['fullname'] ?: $e['username']), 'username' => $e['username'],
                    'role' => $e['is_admin'] ? 'admin' : $roleOf[$e['id']], 'superAdmin' => (bool)$e['is_admin'], 'email' => (string)$e['email'], 'phone' => $e['phone'],
                    'active' => (bool)$e['is_active'], 'login' => $e['last_login'] ? date('d/m H:i', strtotime($e['last_login'])) : '—',
                ];
            }, $rows);
        });
    }

    public static function roleOfUser($userId)
    {
        $user = (new Query())->select(['is_admin'])->from('employee')->where(['id' => $userId])->one();
        if ($user && (int)$user['is_admin'] === 1) {
            return 'admin';
        }
        $role = (new Query())->select('item_name')->from('auth_assignment')
            ->where(['user_id' => $userId, 'item_name' => array_keys(D::ROLES)])->orderBy('id')->scalar();
        return $role ?: null;
    }

    /* ---------------- Đơn hàng ---------------- */

    public static function orders()
    {
        return self::once('orders', function () {
            $rows = (new Query())->select([
                'o.*', 'dealer_code' => 'd.code', 'product' => 'p.name', 'product_code' => 'p.code', 'i.qty',
                'sale' => 'es.fullname', 'delivery' => 'ed.fullname', 'claim_code' => 'wc.code',
            ])->from('dealer_order o')
                ->innerJoin('dealer d', 'd.id = o.dealer_id')
                ->leftJoin('dealer_order_item i', 'i.order_id = o.id')
                ->leftJoin('product p', 'p.id = i.product_id')
                ->leftJoin('employee es', 'es.id = o.sale_id')
                ->leftJoin('employee ed', 'ed.id = o.delivery_id')
                ->leftJoin('warranty_claim wc', 'wc.id = o.claim_id')
                ->orderBy(['o.ordered_at' => SORT_DESC, 'o.id' => SORT_DESC])->all();
            $ids = array_column($rows, 'id') ?: [0];

            $bom = [];
            foreach ((new Query())->select(['op.*', 'part' => 'pt.code'])->from('dealer_order_part op')
                ->innerJoin('part pt', 'pt.id = op.part_id')->where(['op.order_id' => $ids])->orderBy('op.id')->all() as $b) {
                $bom[$b['order_id']][] = ['id' => $b['part'], 'qty' => (int)$b['qty_per_unit'], 'unitCost' => (int)$b['unit_cost'], 'picked' => (bool)$b['is_picked']];
            }
            $com = [];
            foreach ((new Query())->select(['order_id', 'role', 'amount' => 'SUM(amount)'])->from('commission')->where(['order_id' => $ids])->groupBy(['order_id', 'role'])->all() as $c) {
                $com[$c['order_id']][$c['role']] = (int)$c['amount'];
            }
            $exports = (new Query())->select(['code', 'order_id'])->from('stock_voucher')
                ->where(['order_id' => $ids, 'type' => 'export', 'reason' => ['sale', 'warranty']])->indexBy('order_id')->column();
            $serials = [];
            foreach ((new Query())->select(['order_id', 'serial'])->from('warranty')->where(['order_id' => $ids])->orderBy('id')->all() as $w) {
                $serials[$w['order_id']][] = $w['serial'];
            }

            return array_map(function ($o) use ($bom, $com, $exports, $serials) {
                return [
                    'id' => $o['code'], 'type' => $o['type'], 'dealerId' => $o['dealer_code'], 'product' => $o['product'], 'productId' => $o['product_code'],
                    'qty' => (int)$o['qty'], 'total' => (int)$o['total_amount'], 'sale' => $o['sale'], 'delivery' => $o['delivery'],
                    'status' => $o['status'], 'date' => self::vnDate($o['ordered_at']),
                    'comSale' => $com[$o['id']]['sale'] ?? 0, 'comDeli' => $com[$o['id']]['delivery'] ?? 0,
                    'bom' => $bom[$o['id']] ?? [], 'warrantyRef' => $o['claim_code'], 'invoiceNo' => $o['invoice_no'],
                    'exportCode' => $exports[$o['id']] ?? null, 'serials' => $serials[$o['id']] ?? [],
                    'receiver' => $o['receiver_name'], 'receiverPhone' => $o['receiver_phone'], 'deliveredAt' => self::vnDate($o['delivered_at']),
                    'note' => $o['note'], 'assemblyNote' => $o['assembly_note'], 'cancelReason' => $o['cancel_reason'], 'cancelledAt' => self::vnDate($o['cancelled_at']),
                ];
            }, $rows);
        });
    }

    public static function order($code)
    {
        foreach (self::orders() as $o) {
            if ($o['id'] === $code) {
                return $o;
            }
        }
        return null;
    }

    public static function dealer($code)
    {
        foreach (self::dealers() as $d) {
            if ($d['id'] === $code) {
                return $d;
            }
        }
        return null;
    }

    /* ---------------- CRM ---------------- */

    /** @param int|null $saleId chỉ lấy lead của Sale này (null = tất cả) */
    public static function leads($saleId = null)
    {
        $notes = [];
        $noteQuery = (new Query())->select('n.*')->from('crm_lead_note n')->orderBy('n.created_at');
        if ($saleId !== null) {
            $noteQuery->innerJoin('crm_lead l', 'l.id = n.lead_id')->where(['l.sale_id' => (int)$saleId]);
        }
        foreach ($noteQuery->all() as $n) {
            $notes[$n['lead_id']][] = ['d' => date('d/m', strtotime($n['created_at'])), 't' => $n['content']];
        }
        $rows = (new Query())->select(['l.*', 'dealer' => 'd.code', 'product' => 'p.name', 'sale' => 'e.fullname'])->from('crm_lead l')
            ->leftJoin('dealer d', 'd.id = l.dealer_id')->leftJoin('product p', 'p.id = l.product_id')->leftJoin('employee e', 'e.id = l.sale_id')
            ->where(['<>', 'l.stage', 'lost'])->andFilterWhere(['l.sale_id' => $saleId])->orderBy('l.id')->all();
        return array_map(function ($l) use ($notes) {
            return [
                'id' => self::code('L', $l['id']), 'name' => $l['name'], 'phone' => $l['phone'], 'dealer' => $l['dealer'],
                'stage' => D::LEAD_STAGE_CODES[$l['stage']], 'sale' => $l['sale'], 'product' => $l['product'],
                'value' => (int)$l['expected_value'], 'notes' => $notes[$l['id']] ?? [],
            ];
        }, $rows);
    }

    /* ---------------- Bảo hành ---------------- */

    private static function warrantyRow(array $w)
    {
        return [
            'id' => self::code('BH', $w['id']), 'qr' => $w['qr_token'], 'serial' => $w['serial'], 'prodName' => $w['product'], 'dealer' => $w['dealer'],
            'custName' => $w['customer_name'], 'phone' => $w['customer_phone'], 'activatedAt' => self::vnDate($w['activated_at'], true),
            'expires' => self::vnDate($w['expires_at']), 'status' => $w['status'], 'claims' => (int)$w['claim_count'],
        ];
    }

    private static function warrantyQuery()
    {
        return (new Query())->select(['w.*', 'product' => 'p.name', 'dealer' => 'd.code'])->from('warranty w')
            ->innerJoin('product p', 'p.id = w.product_id')->leftJoin('dealer d', 'd.id = w.dealer_id');
    }

    public static function warranties()
    {
        return array_map([self::class, 'warrantyRow'], self::warrantyQuery()->orderBy('w.id')->all());
    }

    public static function warrantyByQr($token)
    {
        $row = self::warrantyQuery()->where(['w.qr_token' => (string)$token])->one();
        return $row ? self::warrantyRow($row) : null;
    }

    public static function claims()
    {
        $rows = (new Query())->select(['c.*', 'w.serial', 'cust' => 'w.customer_name', 'prod' => 'p.name', 'assignee' => 'e.fullname', 'order_code' => 'o.code'])
            ->from('warranty_claim c')->innerJoin('warranty w', 'w.id = c.warranty_id')->innerJoin('product p', 'p.id = w.product_id')
            ->leftJoin('employee e', 'e.id = c.assignee_id')->leftJoin('dealer_order o', 'o.id = c.order_id')
            ->orderBy(['c.id' => SORT_DESC])->all();
        return array_map(function ($c) {
            return [
                'id' => $c['code'], 'wId' => self::code('BH', $c['warranty_id']), 'cust' => $c['cust'], 'serial' => $c['serial'], 'prod' => $c['prod'],
                'issue' => $c['issue'], 'created' => self::vnDate($c['created_at']), 'status' => $c['status'], 'assignee' => $c['assignee'],
                'note' => $c['note'], 'resolved' => self::vnDate($c['resolved_at']), 'orderId' => $c['order_code'],
            ];
        }, $rows);
    }

    /* ---------------- Kho ---------------- */

    public static function stockTransactions()
    {
        $rows = (new Query())->select([
            'v.code', 'v.type', 'v.reason', 'v.note', 'v.created_at', 'i.item_type', 'i.qty', 'i.unit_price',
            'item' => 'COALESCE(p.name, pt.name)', 'supplier' => 's.name', 'order_code' => 'o.code',
        ])->from('stock_voucher v')->innerJoin('stock_voucher_item i', 'i.voucher_id = v.id')
            ->leftJoin('product p', "i.item_type = 'product' AND p.id = i.item_id")
            ->leftJoin('part pt', "i.item_type = 'part' AND pt.id = i.item_id")
            ->leftJoin('supplier s', 's.id = v.supplier_id')->leftJoin('dealer_order o', 'o.id = v.order_id')
            ->orderBy(['v.created_at' => SORT_DESC, 'v.id' => SORT_DESC, 'i.id' => SORT_ASC])->limit(300)->all();
        return array_map(function ($r) {
            return [
                'id' => $r['code'], 'type' => $r['type'] === 'import' ? 'import' : 'export', 'itemType' => $r['item_type'], 'prod' => $r['item'],
                'qty' => (int)$r['qty'], 'ref' => $r['supplier'] ?: ($r['order_code'] ?: '—'), 'date' => self::vnDate($r['created_at']),
                'price' => (int)$r['unit_price'], 'reason' => $r['reason'] === 'purchase' ? null : $r['reason'], 'note' => $r['note'],
            ];
        }, $rows);
    }

    /* ---------------- Kế toán ---------------- */

    public static function commissions()
    {
        $rows = (new Query())->select(['c.employee_id', 'c.role', 'name' => 'e.fullname', 'base' => 'SUM(c.base_amount)', 'amount' => 'SUM(c.amount)',
            'orders' => "GROUP_CONCAT(o.code ORDER BY o.code SEPARATOR ',')"])
            ->from('commission c')->innerJoin('employee e', 'e.id = c.employee_id')->innerJoin('dealer_order o', 'o.id = c.order_id')
            ->groupBy(['c.employee_id', 'c.role'])->orderBy(['c.role' => SORT_DESC, 'e.fullname' => SORT_ASC])->all();
        $payouts = [];
        foreach ((new Query())->from('commission_payout')->orderBy('paid_at')->all() as $p) {
            $payouts[$p['employee_id'] . '|' . $p['role']][] = ['date' => self::vnDate($p['paid_at']), 'amount' => (int)$p['amount'], 'method' => $p['method'], 'note' => $p['note']];
        }
        return array_map(function ($r) use ($payouts) {
            $list = $payouts[$r['employee_id'] . '|' . $r['role']] ?? [];
            return [
                'employeeId' => (int)$r['employee_id'], 'name' => $r['name'], 'role' => $r['role'], 'orders' => explode(',', $r['orders']),
                'base' => (int)$r['base'], 'amount' => (int)$r['amount'], 'paid' => array_sum(array_column($list, 'amount')), 'payouts' => $list,
            ];
        }, $rows);
    }

    /* ---------------- Dữ liệu chung cho JS ---------------- */

    public static function clientConfig($role)
    {
        return [
            'role'        => $role,
            'pages'       => array_keys(D::menuFor($role)),
            'staff'       => self::staff(),
            'orderStatus' => D::ORDER_STATUS,
            'leadSale'    => AgrimacAuth::leadSaleScope() !== null ? trim((string)(Yii::$app->user->identity->fullname ?? '')) : null,
            'commission'  => D::COMMISSION_RATES,
            'leadStages'  => D::LEAD_STAGES,
            'dealers'     => D::indexBy(self::dealers()),
            'parts'       => self::parts(),
            'defaultBoms' => (object)self::defaultBoms(),
            'apiUrl'      => \yii\helpers\Url::to(['agrimac/api']),
            'lookupUrl'   => \yii\helpers\Url::to(['agrimac/lookup']),
        ];
    }
}
