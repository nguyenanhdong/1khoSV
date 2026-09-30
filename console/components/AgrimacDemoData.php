<?php

namespace console\components;

/**
 * Bộ dữ liệu demo AgriMac (lấy từ file thiết kế agrimac-cms_1.html), dùng cho `php yii agrimac-seed`.
 */
class AgrimacDemoData
{
    public static function categories()
    {
        return [
            ['id' => 'C1', 'name' => 'Máy cày nhỏ (<25HP)'],
            ['id' => 'C2', 'name' => 'Máy cày vừa (25-45HP)'],
            ['id' => 'C3', 'name' => 'Máy cày lớn (>45HP)'],
            ['id' => 'C4', 'name' => 'Phụ tùng & linh kiện'],
        ];
    }

    public static function suppliers()
    {
        return [
            ['id' => 'NCC01', 'name' => 'Kubota Vietnam', 'contact' => '028.3912.3456', 'address' => 'Q.Tân Bình, HCM', 'email' => '', 'returns' => [
                ['itemType' => 'product', 'item' => 'SP001', 'qty' => 1, 'reason' => 'Trầy sơn khung khi vận chuyển', 'status' => 'pending', 'date' => '2025-05-12'],
            ]],
            ['id' => 'NCC02', 'name' => 'Yanmar Vietnam', 'contact' => '028.3845.6789', 'address' => 'Q.Bình Thạnh, HCM', 'email' => '', 'returns' => []],
            ['id' => 'NCC03', 'name' => 'Iseki Trading VN', 'contact' => '028.3756.1234', 'address' => 'Q.1, HCM', 'email' => '', 'returns' => [
                ['itemType' => 'product', 'item' => 'SP003', 'qty' => 1, 'reason' => 'Hộp số kêu bất thường', 'status' => 'pending', 'date' => '2025-05-09'],
                ['itemType' => 'part', 'item' => 'LK014', 'qty' => 2, 'reason' => 'Rò rỉ tại mối hàn', 'status' => 'pending', 'date' => '2025-05-11'],
            ]],
        ];
    }

    public static function products()
    {
        return [
            ['id' => 'SP001', 'name' => 'Kubota L3408',  'catId' => 'C1', 'supId' => 'NCC01', 'price' => 285e6, 'cost' => 240e6, 'stock' => 12, 'specs' => '34HP · 4WD · 1450kg', 'hp' => 34, 'drive' => '4WD', 'weight' => 1450, 'sourceType' => 'import',   'minStock' => 3],
            ['id' => 'SP002', 'name' => 'Yanmar EF453T', 'catId' => 'C2', 'supId' => 'NCC02', 'price' => 320e6, 'cost' => 270e6, 'stock' => 8,  'specs' => '45HP · 4WD · 1680kg', 'hp' => 45, 'drive' => '4WD', 'weight' => 1680, 'sourceType' => 'both',     'minStock' => 3],
            ['id' => 'SP003', 'name' => 'Iseki TM3185',  'catId' => 'C3', 'supId' => 'NCC03', 'price' => 480e6, 'cost' => 400e6, 'stock' => 3,  'specs' => '85HP · 4WD · 3200kg', 'hp' => 85, 'drive' => '4WD', 'weight' => 3200, 'sourceType' => 'assembly', 'minStock' => 3],
            ['id' => 'SP004', 'name' => 'Kubota M7040',  'catId' => 'C3', 'supId' => 'NCC01', 'price' => 620e6, 'cost' => 520e6, 'stock' => 5,  'specs' => '70HP · 4WD · 2800kg', 'hp' => 70, 'drive' => '4WD', 'weight' => 2800, 'sourceType' => 'both',     'minStock' => 3],
            ['id' => 'SP005', 'name' => 'Yanmar EF313',  'catId' => 'C1', 'supId' => 'NCC02', 'price' => 245e6, 'cost' => 205e6, 'stock' => 0,  'specs' => '31HP · 2WD · 1200kg', 'hp' => 31, 'drive' => '2WD', 'weight' => 1200, 'sourceType' => 'import',   'minStock' => 3],
        ];
    }

    public static function dealers()
    {
        return [
            ['id' => 'DL001', 'name' => 'Đại lý Minh Hùng',   'province' => 'TP Cần Thơ', 'contact' => '0901.234.567', 'level' => 'gold',   'limit' => 3e9,   'debt' => 855e6, 'total' => 5.2e9],
            ['id' => 'DL002', 'name' => 'Đại lý Phúc Lộc',    'province' => 'An Giang',   'contact' => '0912.345.678', 'level' => 'silver', 'limit' => 1.5e9, 'debt' => 640e6, 'total' => 1.8e9],
            ['id' => 'DL003', 'name' => 'Đại lý Tân Thành',   'province' => 'Tiền Giang', 'contact' => '0923.456.789', 'level' => 'silver', 'limit' => 1e9,   'debt' => 1.1e9, 'total' => 1.2e9],
            ['id' => 'DL004', 'name' => 'Đại lý Bình Nguyên', 'province' => 'Đồng Tháp',  'contact' => '0934.567.890', 'level' => 'bronze', 'limit' => 500e6, 'debt' => 0,     'total' => 800e6],
        ];
    }

    public static function parts()
    {
        return [
            ['id' => 'LK001', 'name' => 'Động cơ Kubota D1105',   'cat' => 'Động cơ',    'unit' => 'cái', 'cost' => 45e6,  'stock' => 6,  'minStock' => 3],
            ['id' => 'LK002', 'name' => 'Động cơ Yanmar 3TNV88',  'cat' => 'Động cơ',    'unit' => 'cái', 'cost' => 52e6,  'stock' => 4,  'minStock' => 3],
            ['id' => 'LK003', 'name' => 'Hộp số 4WD tiêu chuẩn',  'cat' => 'Hộp số',     'unit' => 'bộ',  'cost' => 18e6,  'stock' => 8,  'minStock' => 3],
            ['id' => 'LK004', 'name' => 'Hộp số 2WD cơ bản',      'cat' => 'Hộp số',     'unit' => 'bộ',  'cost' => 11e6,  'stock' => 5,  'minStock' => 3],
            ['id' => 'LK005', 'name' => 'Bộ thủy lực 3 điểm',     'cat' => 'Thủy lực',   'unit' => 'bộ',  'cost' => 8.5e6, 'stock' => 10, 'minStock' => 4],
            ['id' => 'LK006', 'name' => 'Xi lanh thủy lực nâng',  'cat' => 'Thủy lực',   'unit' => 'cái', 'cost' => 3.2e6, 'stock' => 12, 'minStock' => 6],
            ['id' => 'LK007', 'name' => 'Cabin + khung máy',      'cat' => 'Thân máy',   'unit' => 'bộ',  'cost' => 22e6,  'stock' => 3,  'minStock' => 4],
            ['id' => 'LK008', 'name' => 'Bảng điều khiển cabin',  'cat' => 'Thân máy',   'unit' => 'bộ',  'cost' => 4.5e6, 'stock' => 7,  'minStock' => 3],
            ['id' => 'LK009', 'name' => 'Lốp trước 6.00-16',      'cat' => 'Lốp & Bánh', 'unit' => 'cái', 'cost' => 1.2e6, 'stock' => 20, 'minStock' => 8],
            ['id' => 'LK010', 'name' => 'Lốp sau 11.2-24',        'cat' => 'Lốp & Bánh', 'unit' => 'cái', 'cost' => 2.8e6, 'stock' => 16, 'minStock' => 8],
            ['id' => 'LK011', 'name' => 'Bình điện 12V-80Ah',     'cat' => 'Điện',       'unit' => 'cái', 'cost' => 1.5e6, 'stock' => 15, 'minStock' => 5],
            ['id' => 'LK012', 'name' => 'Bộ dây điện chính',      'cat' => 'Điện',       'unit' => 'bộ',  'cost' => 2.1e6, 'stock' => 9,  'minStock' => 4],
            ['id' => 'LK013', 'name' => 'Bộ lọc nhiên liệu+nhớt', 'cat' => 'Lọc',        'unit' => 'bộ',  'cost' => 350e3, 'stock' => 30, 'minStock' => 10],
            ['id' => 'LK014', 'name' => 'Két nước làm mát',       'cat' => 'Làm mát',    'unit' => 'cái', 'cost' => 3.8e6, 'stock' => 8,  'minStock' => 3],
            ['id' => 'LK015', 'name' => 'Bơm thủy lực roto',      'cat' => 'Thủy lực',   'unit' => 'cái', 'cost' => 6.5e6, 'stock' => 5,  'minStock' => 3],
        ];
    }

    /** BOM mặc định theo mã sản phẩm: [[mã linh kiện, số lượng/máy], ...]. */
    public static function defaultBoms()
    {
        return [
            'SP001' => [['LK001', 1], ['LK003', 1], ['LK005', 1], ['LK007', 1], ['LK009', 2], ['LK010', 2], ['LK011', 1], ['LK013', 1]],
            'SP002' => [['LK002', 1], ['LK003', 1], ['LK005', 1], ['LK006', 1], ['LK007', 1], ['LK009', 2], ['LK010', 2], ['LK012', 1], ['LK014', 1]],
            'SP003' => [['LK001', 1], ['LK003', 1], ['LK005', 1], ['LK006', 2], ['LK007', 1], ['LK008', 1], ['LK009', 2], ['LK010', 2], ['LK012', 1], ['LK014', 1], ['LK015', 1]],
            'SP004' => [['LK001', 1], ['LK003', 1], ['LK005', 1], ['LK006', 2], ['LK007', 1], ['LK008', 1], ['LK010', 4], ['LK011', 1], ['LK012', 1], ['LK014', 1], ['LK015', 1]],
        ];
    }

    /** Đơn hàng: bom = true → dùng BOM mặc định của sản phẩm. */
    public static function orders()
    {
        return [
            ['id' => 'DH2025001', 'type' => 'new', 'dealer' => 'DL001', 'product' => 'SP001', 'qty' => 3, 'price' => 285e6, 'sale' => 'hai.nv', 'delivery' => 'tuan.tm',
                'status' => 'delivered', 'date' => '2025-05-10', 'bom' => true, 'invoiceNo' => 'HD0000101', 'receiver' => 'Anh Hùng', 'deliveredAt' => '2025-05-12'],
            ['id' => 'DH2025002', 'type' => 'new', 'dealer' => 'DL002', 'product' => 'SP002', 'qty' => 2, 'price' => 320e6, 'sale' => 'mai.lt', 'delivery' => 'son.pv',
                'status' => 'delivering', 'date' => '2025-05-13', 'bom' => true, 'invoiceNo' => 'HD0000102'],
            ['id' => 'DH2025003', 'type' => 'new', 'dealer' => 'DL003', 'product' => 'SP003', 'qty' => 1, 'price' => 480e6, 'sale' => 'hai.nv', 'delivery' => null,
                'status' => 'assembling', 'date' => '2025-05-14', 'bom' => true, 'invoiceNo' => 'HD0000103'],
            ['id' => 'DH2025004', 'type' => 'new', 'dealer' => 'DL001', 'product' => 'SP004', 'qty' => 2, 'price' => 620e6, 'sale' => 'mai.lt', 'delivery' => null,
                'status' => 'confirmed', 'date' => '2025-05-15', 'bom' => false, 'invoiceNo' => 'HD0000104'],
            ['id' => 'DH2025005', 'type' => 'warranty', 'dealer' => 'DL001', 'product' => 'SP001', 'qty' => 1, 'price' => 0, 'sale' => 'hai.nv', 'delivery' => null,
                'status' => 'assembling', 'date' => '2025-05-16', 'bom' => [['LK005', 1], ['LK006', 1]], 'claim' => 'KC001'],
        ];
    }

    public static function leads()
    {
        return [
            ['name' => 'Ông Nguyễn Văn Nam', 'phone' => '0901.111.222', 'dealer' => 'DL001', 'stage' => 'interested', 'sale' => 'hai.nv', 'product' => 'SP001', 'value' => 285e6, 'notes' => [
                ['2025-05-14', 'Hỏi giá L3408 và EF453T, đang so sánh 2 loại'],
                ['2025-05-15', 'Đã gửi báo giá, khách đang cân nhắc ngân sách'],
            ]],
            ['name' => 'Bà Trần Thị Bích', 'phone' => '0912.333.444', 'dealer' => 'DL002', 'stage' => 'quoted', 'sale' => 'mai.lt', 'product' => 'SP002', 'value' => 640e6, 'notes' => [
                ['2025-05-12', 'Cần 2 máy vụ mùa tháng 6, gấp'],
                ['2025-05-13', 'Báo giá 2 máy EF453T, CK 2% thanh toán sớm'],
                ['2025-05-15', 'Khách hỏi thêm điều khoản bảo hành, đã gửi tài liệu'],
            ]],
            ['name' => 'Ông Lê Văn Phúc', 'phone' => '0923.555.666', 'dealer' => null, 'stage' => 'won', 'sale' => 'hai.nv', 'product' => 'SP003', 'value' => 480e6, 'notes' => [
                ['2025-05-10', 'Tiếp thị qua điện, khách cần máy lớn 80HP+'],
                ['2025-05-16', 'Đồng ý mua 1 TM3185, yêu cầu giao trước 20/05'],
            ]],
            ['name' => 'HTX Đông Bình', 'phone' => '0934.777.888', 'dealer' => 'DL004', 'stage' => 'approach', 'sale' => 'mai.lt', 'product' => 'SP004', 'value' => 1.86e9, 'notes' => [
                ['2025-05-16', 'HTX có nhu cầu 3 máy lớn cho vụ hè thu, đang khảo sát'],
            ]],
        ];
    }

    public static function warranties()
    {
        return [
            ['serial' => 'SP001-A3KX', 'qr' => 'q7Kx2mA3KXp9', 'product' => 'SP001', 'dealer' => 'DL001', 'order' => 'DH2025001', 'custName' => 'Nguyễn Văn An', 'phone' => '0901.999.111', 'activatedAt' => '2025-05-12 10:32:00', 'status' => 'active', 'claims' => 0],
            ['serial' => 'SP001-B7MN', 'qr' => 'r4Tn8vB7MNw2', 'product' => 'SP001', 'dealer' => 'DL001', 'order' => 'DH2025001', 'custName' => 'Trần Văn Bình', 'phone' => '0912.888.222', 'activatedAt' => '2025-05-12 14:15:00', 'status' => 'active', 'claims' => 1],
            ['serial' => 'SP002-D4RT', 'qr' => 'h2Lc5dD4RTz8', 'product' => 'SP002', 'dealer' => 'DL002', 'order' => 'DH2025002', 'custName' => null, 'phone' => null, 'activatedAt' => null, 'status' => 'unactivated', 'claims' => 0],
            ['serial' => 'SP004-F1AB', 'qr' => 'm9Wf3gF1ABe6', 'product' => 'SP004', 'dealer' => 'DL004', 'order' => null, 'custName' => 'Lê Văn Tám', 'phone' => '0934.222.333', 'activatedAt' => '2025-05-09 08:00:00', 'status' => 'active', 'claims' => 0],
        ];
    }

    public static function claims()
    {
        return [
            ['id' => 'KC001', 'serial' => 'SP001-B7MN', 'issue' => 'Rò dầu thủy lực, không cày được', 'created' => '2025-05-14 09:00:00',
                'status' => 'processing', 'assignee' => 'loi.nt', 'note' => 'Lên lịch kiểm tra 17/05'],
        ];
    }

    public static function stockVouchers()
    {
        return [
            ['id' => 'NK001', 'type' => 'import', 'reason' => 'purchase', 'itemType' => 'product', 'item' => 'SP001', 'qty' => 5,  'supplier' => 'NCC01', 'order' => null,        'date' => '2025-05-01', 'price' => 240e6],
            ['id' => 'NK002', 'type' => 'import', 'reason' => 'purchase', 'itemType' => 'product', 'item' => 'SP002', 'qty' => 4,  'supplier' => 'NCC02', 'order' => null,        'date' => '2025-05-05', 'price' => 270e6],
            ['id' => 'NK003', 'type' => 'import', 'reason' => 'purchase', 'itemType' => 'product', 'item' => 'SP003', 'qty' => 2,  'supplier' => 'NCC03', 'order' => null,        'date' => '2025-05-08', 'price' => 400e6],
            ['id' => 'NK004', 'type' => 'import', 'reason' => 'purchase', 'itemType' => 'part',    'item' => 'LK001', 'qty' => 4,  'supplier' => 'NCC01', 'order' => null,        'date' => '2025-05-06', 'price' => 45e6],
            ['id' => 'NK005', 'type' => 'import', 'reason' => 'purchase', 'itemType' => 'part',    'item' => 'LK010', 'qty' => 20, 'supplier' => 'NCC02', 'order' => null,        'date' => '2025-05-07', 'price' => 2.8e6],
            ['id' => 'XK001', 'type' => 'export', 'reason' => 'sale',     'itemType' => 'product', 'item' => 'SP001', 'qty' => 3,  'supplier' => null,    'order' => 'DH2025001', 'date' => '2025-05-10', 'price' => 285e6],
            ['id' => 'XK002', 'type' => 'export', 'reason' => 'sale',     'itemType' => 'product', 'item' => 'SP002', 'qty' => 2,  'supplier' => null,    'order' => 'DH2025002', 'date' => '2025-05-13', 'price' => 320e6],
            ['id' => 'XK003', 'type' => 'export', 'reason' => 'assembly_issue', 'itemType' => 'part', 'item' => 'LK007', 'qty' => 1, 'supplier' => null,  'order' => 'DH2025003', 'date' => '2025-05-14', 'price' => 22e6],
        ];
    }

    /** Nhân viên demo (mật khẩu mặc định: AgriMac@2025). */
    public static function staff()
    {
        return [
            ['username' => 'hai.nv',   'name' => 'Nguyễn Văn Hải',   'role' => 'sale',       'email' => 'hai@agrimac.vn'],
            ['username' => 'mai.lt',   'name' => 'Lê Thị Mai',       'role' => 'sale',       'email' => 'mai@agrimac.vn'],
            ['username' => 'hoa.pt',   'name' => 'Phạm Thị Hoa',     'role' => 'kt_banhang', 'email' => 'hoa@agrimac.vn'],
            ['username' => 'lan.tt',   'name' => 'Trần Thị Lan',     'role' => 'kt_congno',  'email' => 'lan@agrimac.vn'],
            ['username' => 'duc.vm',   'name' => 'Võ Minh Đức',      'role' => 'kt_xuatkho', 'email' => 'duc@agrimac.vn'],
            ['username' => 'tuan.tm',  'name' => 'Trần Minh Tuấn',   'role' => 'delivery',   'email' => 'tuan@agrimac.vn'],
            ['username' => 'son.pv',   'name' => 'Phạm Văn Sơn',     'role' => 'delivery',   'email' => 'son@agrimac.vn'],
            ['username' => 'loi.nt',   'name' => 'Nguyễn Thành Lợi', 'role' => 'assembly',   'email' => 'loi@agrimac.vn'],
            ['username' => 'bao.tq',   'name' => 'Trần Quốc Bảo',    'role' => 'assembly',   'email' => 'bao@agrimac.vn'],
        ];
    }
}
