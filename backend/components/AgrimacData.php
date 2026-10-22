<?php

namespace backend\components;

/**
 * Hằng số cấu hình và hàm tiện ích dùng chung của 1Kho CMS.
 * Đọc dữ liệu: AgrimacRepo. Ghi dữ liệu: AgrimacService.
 */
class AgrimacData
{
    const MENU = [
        'dashboard'  => ['icon' => '📊', 'label' => 'Tổng quan'],
        'crm'        => ['icon' => '💬', 'label' => 'CRM / Pipeline'],
        'orders'     => ['icon' => '📋', 'label' => 'Đơn hàng'],
        'assembly'   => ['icon' => '🔩', 'label' => 'Bộ phận lắp ráp'],
        'products'   => ['icon' => '🚜', 'label' => 'Sản phẩm'],
        'inventory'  => ['icon' => '🏗', 'label' => 'Quản lý kho'],
        'dealers'    => ['icon' => '🏪', 'label' => 'Đại lý & Công nợ'],
        'warranty'   => ['icon' => '🛡', 'label' => 'Bảo hành'],
        'suppliers'  => ['icon' => '🏭', 'label' => 'Nhà cung cấp'],
        'carriers'   => ['icon' => '🚛', 'label' => 'Nhà xe'],
        'accounting' => ['icon' => '🧾', 'label' => 'Kế toán & HH'],
        'reports'    => ['icon' => '📈', 'label' => 'Báo cáo quản trị'],
        'users'      => ['icon' => '👥', 'label' => 'Tài khoản & Phân quyền'],
    ];

    /** Quản trị website / sàn 1kho (controller cũ): chỉ CEO thấy, gom trong nhóm "Quản trị website" của sidebar. */
    const LEGACY_MENU = [
        'banner'   => ['icon' => '🖼', 'label' => 'Banner', 'url' => '/banner/index'],
        'category' => ['icon' => '🗂', 'label' => 'Chuyên mục sàn', 'url' => '/category/index'],
        'voucher'  => ['icon' => '🎟', 'label' => 'Voucher', 'url' => '/voucher/index'],
        'notify'   => ['icon' => '🔔', 'label' => 'Thông báo', 'url' => '/notify/index'],
        'site-content' => ['icon' => '📝', 'label' => 'Nội dung & liên hệ', 'url' => '/site-content/index'],
    ];

    /**
     * 5 vai trò. Kinh doanh = Sale + KT bán hàng cũ (chăm sóc khách, bán hàng, tạo & duyệt đơn);
     * Kế toán công nợ = KT công nợ + KT xuất kho cũ (xuất kho, nhập kho, công nợ, hoa hồng).
     */
    const ROLES = [
        'admin'     => ['label' => 'CEO / Quản lý',       'color' => '#ef4444', 'icon' => '👑'],
        'sale'      => ['label' => 'Kinh doanh (Sales)',  'color' => '#8b5cf6', 'icon' => '👔'],
        'kt_congno' => ['label' => 'Kế toán công nợ',     'color' => '#f97316', 'icon' => '💰'],
        'assembly'  => ['label' => 'Bộ phận lắp ráp',     'color' => '#0ea5e9', 'icon' => '🔩'],
        'delivery'  => ['label' => 'Nhân viên giao hàng', 'color' => '#10b981', 'icon' => '🚚'],
    ];

    const ROLE_ACCESS = [
        'admin'     => 'all',
        'sale'      => ['dashboard', 'crm', 'orders', 'products', 'dealers', 'warranty'],
        'kt_congno' => ['dashboard', 'orders', 'assembly', 'inventory', 'products', 'suppliers', 'dealers', 'carriers', 'accounting'],
        'assembly'  => ['dashboard', 'assembly', 'inventory'],
        'delivery'  => ['dashboard', 'orders', 'carriers'],
    ];

    /* ---------------- Đơn hàng: 3 loại, mỗi loại một luồng trạng thái ---------------- */

    const ORDER_TYPES = [
        'new'      => ['label' => 'Đơn bán',      'short' => 'Bán',  'color' => '#059669', 'bg' => '#d1fae5'],
        'assembly' => ['label' => 'Đơn lắp ráp',  'short' => 'Ráp',  'color' => '#0ea5e9', 'bg' => '#e0f2fe'],
        'warranty' => ['label' => 'Đơn bảo hành', 'short' => 'BH',   'color' => '#ef4444', 'bg' => '#fee2e2'],
    ];

    /** Các bước của từng loại đơn: trạng thái => nhãn (theo thứ tự). */
    const ORDER_FLOWS = [
        'new' => [
            'pending'   => 'Chờ duyệt',
            'confirmed' => 'Chờ xuất kho',
            'exported'  => 'Chờ gửi hàng',
            'shipping'  => 'Đang vận chuyển',
            'delivered' => 'Khách đã nhận',
        ],
        'warranty' => [
            'pending'   => 'Chờ duyệt',
            'confirmed' => 'Chờ xuất linh kiện',
            'exported'  => 'Chờ gửi hàng',
            'shipping'  => 'Đang vận chuyển',
            'delivered' => 'Khách đã nhận',
        ],
        'assembly' => [
            'pending'    => 'Chờ duyệt',
            'confirmed'  => 'Chờ xuất linh kiện',
            'exported'   => 'Chờ nhận linh kiện',
            'received'   => 'Đã nhận linh kiện',
            'assembling' => 'Đang lắp ráp',
            'assembled'  => 'Chờ nhập kho',
            'delivered'  => 'Đã nhập kho',
        ],
    ];

    /** Màu trạng thái (dùng chung 3 loại đơn); nhãn chi tiết lấy theo ORDER_FLOWS của từng loại. */
    const ORDER_STATUS = [
        'pending'    => ['label' => 'Chờ duyệt',        'color' => '#f59e0b', 'bg' => '#fef3c7'],
        'confirmed'  => ['label' => 'Chờ xuất kho',     'color' => '#3b82f6', 'bg' => '#dbeafe'],
        'exported'   => ['label' => 'Đã xuất kho',      'color' => '#6366f1', 'bg' => '#e0e7ff'],
        'received'   => ['label' => 'Đã nhận linh kiện', 'color' => '#0891b2', 'bg' => '#cffafe'],
        'assembling' => ['label' => 'Đang lắp ráp',     'color' => '#0ea5e9', 'bg' => '#e0f2fe'],
        'assembled'  => ['label' => 'Chờ nhập kho',     'color' => '#8b5cf6', 'bg' => '#ede9fe'],
        'shipping'   => ['label' => 'Đang vận chuyển',  'color' => '#f97316', 'bg' => '#ffedd5'],
        'delivered'  => ['label' => 'Hoàn thành',       'color' => '#10b981', 'bg' => '#d1fae5'],
        'cancelled'  => ['label' => 'Đã huỷ',           'color' => '#6b7280', 'bg' => '#f1f5f9'],
    ];

    /** Nhãn trạng thái theo loại đơn */
    public static function orderStatusLabel($type, $status)
    {
        return self::ORDER_FLOWS[$type][$status] ?? (self::ORDER_STATUS[$status]['label'] ?? $status);
    }

    const PRODUCT_UNITS = ['Cái', 'Bộ'];
    /** Ảnh gửi hàng: tối đa 2 ảnh, server nén còn ≤ 2MB mỗi ảnh */
    const SHIP_IMAGE_MAX = 2;
    const SHIP_IMAGE_BYTES = 2097152;

    /** Đơn hàng khách mua trên sàn 1kho (bảng `order`), khớp cột `order.status` và params `status_order`. */
    const MARKET_ORDER_STATUS = [
        0 => ['label' => 'Đang chờ xử lý', 'color' => '#f59e0b', 'bg' => '#fef3c7'],
        1 => ['label' => 'Đã xác nhận',    'color' => '#3b82f6', 'bg' => '#dbeafe'],
        2 => ['label' => 'Đang giao hàng', 'color' => '#f97316', 'bg' => '#ffedd5'],
        3 => ['label' => 'Đã mua hàng',    'color' => '#10b981', 'bg' => '#d1fae5'],
        4 => ['label' => 'Hoàn tiền',      'color' => '#8b5cf6', 'bg' => '#ede9fe'],
        5 => ['label' => 'Hủy',            'color' => '#6b7280', 'bg' => '#f1f5f9'],
    ];
    const MARKET_ORDER_CANCELLED = 5;
    const MARKET_ORDER_PURCHASED = 3;
    const MARKET_PAYMENT = [1 => 'Chuyển khoản', 2 => 'Thanh toán khi nhận hàng (COD)'];
    const MARKET_CANCEL_BY = [1 => 'Khách hàng', 2 => 'Đại lý', 3 => '1Kho'];
    const MARKET_REFUND_STATUS = [0 => 'Đang chờ', 1 => 'Đồng ý trả hàng/hoàn tiền', 2 => 'Từ chối'];
    const MARKET_REFUND_SITUATION = [1 => 'Đã nhận hàng và hàng có vấn đề', 2 => 'Chưa nhận hàng / nhận thiếu hàng'];

    /** Đơn sàn 1kho trước đây nằm ở trang quản trị sàn (chỉ Admin), nay là một tab của trang Đơn hàng. */
    public static function canMarketOrders($role)
    {
        return $role === 'admin';
    }

    /** Thanh toán / công nợ của đơn đại lý (dealer_order.debt_mode) */
    const DEBT_MODES = ['full' => 'Ghi nợ cả đơn', 'partial' => 'Nợ một phần', 'paid' => 'Đã thanh toán đủ'];
    const PAID_METHODS = ['Tiền mặt', 'Chuyển khoản'];

    /** % hoa hồng trên doanh thu đơn hoàn thành (khớp config COMMISSION_RATE_SALE / _DELIVERY). */
    const COMMISSION_RATES = ['sale' => 1, 'delivery' => 0.3];

    /** Kỳ báo cáo của bộ dữ liệu mẫu; khi ghép DB sẽ lấy theo bộ lọc tháng. */

    const WARRANTY_MONTHS = 24;
    const HOTLINE = '1900 6868';

    const LEAD_STAGES = [
        'Tiếp cận' => ['color' => '#64748b', 'bg' => '#f1f5f9'],
        'Quan tâm' => ['color' => '#2563eb', 'bg' => '#dbeafe'],
        'Báo giá'  => ['color' => '#b45309', 'bg' => '#fef3c7'],
        'Chốt đơn' => ['color' => '#059669', 'bg' => '#d1fae5'],
    ];

    /** Mã lưu DB → nhãn hiển thị. */
    const DEALER_LEVEL_CODES = ['gold' => 'Vàng', 'silver' => 'Bạc', 'bronze' => 'Đồng'];
    const LEAD_STAGE_CODES = ['approach' => 'Tiếp cận', 'interested' => 'Quan tâm', 'quoted' => 'Báo giá', 'won' => 'Chốt đơn', 'lost' => 'Thất bại'];

    const DEALER_LEVELS = [
        'Vàng' => ['color' => '#b45309', 'bg' => '#fef3c7'],
        'Bạc'  => ['color' => '#475569', 'bg' => '#f1f5f9'],
        'Đồng' => ['color' => '#92400e', 'bg' => '#fefce8'],
    ];

    /** Module hiển thị trong ma trận phân quyền ở trang Tài khoản. */
    const PERMISSION_MODULES = [
        'crm' => 'CRM', 'orders' => 'Đơn hàng', 'assembly' => 'Lắp ráp', 'products' => 'Sản phẩm', 'inventory' => 'Kho',
        'dealers' => 'Đại lý', 'warranty' => 'Bảo hành', 'suppliers' => 'NCC', 'carriers' => 'Nhà xe',
        'accounting' => 'Kế toán', 'reports' => 'Báo cáo', 'users' => 'Tài khoản',
    ];

    const SOURCE_TYPES = [
        'import'   => 'Nhập nguyên chiếc',
        'assembly' => 'Tự lắp từ linh kiện',
        'both'     => 'Cả hai',
    ];

    public static function canAccess($role, $page)
    {
        $access = self::ROLE_ACCESS[$role] ?? [];
        return $access === 'all' || in_array($page, $access, true);
    }

    public static function menuFor($role)
    {
        return array_filter(self::MENU, function ($page) use ($role) {
            return self::canAccess($role, $page);
        }, ARRAY_FILTER_USE_KEY);
    }

    /** Định dạng tiền giống hàm B() của bản thiết kế: 5.20tỷ / 285tr / 350.000đ. */
    public static function money($value)
    {
        $value = (float)$value;
        if ($value >= 1e9) {
            return number_format($value / 1e9, 2, '.', '') . 'tỷ';
        }
        if ($value >= 1e6) {
            return round($value / 1e6) . 'tr';
        }
        return number_format($value, 0, ',', '.') . 'đ';
    }

    /** "Đại lý Minh Hùng" → "Minh Hùng". */
    public static function shortDealerName($name)
    {
        return implode(' ', array_slice(explode(' ', (string)$name), 2));
    }

    public static function lastWord($name)
    {
        $parts = explode(' ', (string)$name);
        return end($parts);
    }

    public static function stockColor($stock)
    {
        return $stock == 0 ? '#ef4444' : ($stock <= 3 ? '#f59e0b' : '#059669');
    }

    public static function indexBy(array $rows, $key = 'id')
    {
        $out = [];
        foreach ($rows as $row) {
            $out[$row[$key]] = $row;
        }
        return $out;
    }

}
