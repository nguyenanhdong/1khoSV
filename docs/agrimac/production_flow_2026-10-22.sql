-- =====================================================================
-- 1Kho CMS – TÁI CẤU TRÚC VAI TRÒ, 3 LOẠI ĐƠN HÀNG, NHÀ XE, SẢN PHẨM (22/10/2026)
-- DB: MariaDB >= 10.3. Chạy được nhiều lần.
-- Thứ tự: production_migration.sql → production_frontend_2026-09-30.sql → production_debt_2026-10-05.sql → file này.
--   mysql --default-character-set=utf8mb4 <db> < production_flow_2026-10-22.sql
-- =====================================================================

SET NAMES utf8mb4;

-- =====================================================================
-- 1. GỘP VAI TRÒ
--    KT Bán hàng (kt_banhang)  → Kinh doanh (sale)
--    KT Xuất kho (kt_xuatkho)  → Kế toán công nợ (kt_congno)
-- =====================================================================
-- Chuyển người dùng sang vai trò mới (bỏ qua nếu người đó đã có vai trò đích)
UPDATE `auth_assignment` a
LEFT JOIN `auth_assignment` b ON b.`user_id` = a.`user_id` AND b.`item_name` = 'sale'
SET a.`item_name` = 'sale'
WHERE a.`item_name` = 'kt_banhang' AND b.`id` IS NULL;
UPDATE `auth_assignment` a
LEFT JOIN `auth_assignment` b ON b.`user_id` = a.`user_id` AND b.`item_name` = 'kt_congno'
SET a.`item_name` = 'kt_congno'
WHERE a.`item_name` = 'kt_xuatkho' AND b.`id` IS NULL;
DELETE FROM `auth_assignment` WHERE `item_name` IN ('kt_banhang', 'kt_xuatkho');
DELETE FROM `auth_item_child` WHERE `parent` IN ('kt_banhang', 'kt_xuatkho') OR `child` IN ('kt_banhang', 'kt_xuatkho');
DELETE FROM `auth_item` WHERE `name` IN ('kt_banhang', 'kt_xuatkho') AND `type` = 1;
UPDATE `auth_item` SET `description` = 'CEO / Quản lý'       WHERE `name` = 'admin'     AND `type` = 1;
UPDATE `auth_item` SET `description` = 'Kinh doanh (Sales)'  WHERE `name` = 'sale'      AND `type` = 1;
UPDATE `auth_item` SET `description` = 'Kế toán công nợ'     WHERE `name` = 'kt_congno' AND `type` = 1;

-- =====================================================================
-- 2. SẢN PHẨM: giá đại lý, đơn vị tính (Cái / Bộ)
--    Công suất / hệ dẫn động (horsepower, drive_type) giữ nguyên trong DB, chỉ bỏ khỏi form CMS.
-- =====================================================================
ALTER TABLE `product`
    ADD COLUMN IF NOT EXISTS `price_dealer` BIGINT      NOT NULL DEFAULT 0 COMMENT 'Giá bán cho đại lý (giá chung mọi đại lý); 0 = chưa có, khi tạo đơn dùng giá bán lẻ' AFTER `price_discount`,
    ADD COLUMN IF NOT EXISTS `unit`         VARCHAR(10) NOT NULL DEFAULT 'Cái' COMMENT 'Đơn vị tính: Cái / Bộ' AFTER `price_dealer`;

-- =====================================================================
-- 3. NHÀ XE (đơn vị vận chuyển nhân viên giao hàng gửi hàng đi)
-- =====================================================================
CREATE TABLE IF NOT EXISTS `carrier` (
    `id`         INT          NOT NULL AUTO_INCREMENT COMMENT 'Khoá chính',
    `name`       VARCHAR(255) NOT NULL COMMENT 'Tên nhà xe',
    `phone`      VARCHAR(30)  NULL COMMENT 'Số điện thoại',
    `address`    VARCHAR(300) NULL COMMENT 'Địa chỉ bến / tuyến xe',
    `note`       VARCHAR(500) NULL COMMENT 'Ghi chú',
    `is_delete`  TINYINT(1)   NOT NULL DEFAULT 0 COMMENT '1: đã xoá (ẩn khỏi danh sách chọn, giữ lịch sử đơn)',
    `created_by` INT          NULL COMMENT 'Người tạo (employee.id)',
    `created_at` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Ngày tạo',
    `updated_at` DATETIME     NULL COMMENT 'Ngày cập nhật',
    PRIMARY KEY (`id`),
    KEY `idx_carrier_active` (`is_delete`, `name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci COMMENT='1Kho CMS – Nhà xe vận chuyển';

-- =====================================================================
-- 4. ĐƠN HÀNG: 3 loại (bán / lắp ráp / bảo hành), khách lẻ, nhiều sản phẩm, mốc thời gian từng bước
-- =====================================================================
ALTER TABLE `dealer_order`
    MODIFY COLUMN `type` ENUM('new','assembly','warranty') NOT NULL DEFAULT 'new'
        COMMENT 'Loại đơn: new = đơn bán, assembly = đơn lắp ráp (lắp xong nhập kho), warranty = đơn bảo hành (gửi linh kiện thay thế)',
    MODIFY COLUMN `dealer_id` INT NULL COMMENT 'Đại lý đặt hàng (dealer.id); NULL với khách lẻ và đơn lắp ráp',
    MODIFY COLUMN `sale_id` INT NULL COMMENT 'Sale phụ trách (employee.id); đơn lắp ráp là người tạo',
    -- tạm giữ cả trạng thái cũ để chuyển dữ liệu ở mục 5
    MODIFY COLUMN `status` ENUM('pending','confirmed','exported','received','assembling','assembled','shipping','delivered','cancelled','delivering') NOT NULL DEFAULT 'pending'
        COMMENT 'Trạng thái',
    ADD COLUMN IF NOT EXISTS `customer_type`    ENUM('dealer','retail') NULL COMMENT 'Khách của đơn bán / bảo hành: dealer = đại lý, retail = khách lẻ' AFTER `dealer_id`,
    ADD COLUMN IF NOT EXISTS `customer_name`    VARCHAR(255) NULL COMMENT 'Tên khách lẻ' AFTER `customer_type`,
    ADD COLUMN IF NOT EXISTS `customer_phone`   VARCHAR(30)  NULL COMMENT 'SĐT khách lẻ' AFTER `customer_name`,
    ADD COLUMN IF NOT EXISTS `customer_address` VARCHAR(300) NULL COMMENT 'Địa chỉ khách lẻ' AFTER `customer_phone`,
    ADD COLUMN IF NOT EXISTS `created_by`       INT          NULL COMMENT 'Người tạo đơn (employee.id)' AFTER `sale_id`,
    ADD COLUMN IF NOT EXISTS `approved_at`      DATETIME     NULL COMMENT 'Thời điểm duyệt' AFTER `approved_by`,
    ADD COLUMN IF NOT EXISTS `exported_at`      DATETIME     NULL COMMENT 'Thời điểm xuất kho (máy / linh kiện)' AFTER `exporter_id`,
    ADD COLUMN IF NOT EXISTS `parts_received_at` DATETIME    NULL COMMENT 'Lắp ráp: thời điểm bộ phận lắp ráp nhận linh kiện' AFTER `assembler_id`,
    ADD COLUMN IF NOT EXISTS `started_at`       DATETIME     NULL COMMENT 'Lắp ráp: thời điểm bắt đầu lắp' AFTER `parts_received_at`,
    ADD COLUMN IF NOT EXISTS `finished_at`      DATETIME     NULL COMMENT 'Lắp ráp: thời điểm lắp xong' AFTER `started_at`,
    ADD COLUMN IF NOT EXISTS `stocked_at`       DATETIME     NULL COMMENT 'Lắp ráp: thời điểm nhập máy thành phẩm vào kho' AFTER `finished_at`,
    ADD COLUMN IF NOT EXISTS `stocked_by`       INT          NULL COMMENT 'Lắp ráp: người nhập kho (employee.id)' AFTER `stocked_at`,
    ADD COLUMN IF NOT EXISTS `carrier_id`       INT          NULL COMMENT 'Nhà xe gửi hàng (carrier.id)' AFTER `delivery_id`,
    ADD COLUMN IF NOT EXISTS `shipped_at`       DATETIME     NULL COMMENT 'Thời điểm gửi hàng cho nhà xe' AFTER `carrier_id`,
    ADD COLUMN IF NOT EXISTS `ship_images`      TEXT         NULL COMMENT 'Ảnh gửi hàng (JSON mảng URL, tối đa 2 ảnh, mỗi ảnh ≤ 2MB)' AFTER `shipped_at`,
    ADD COLUMN IF NOT EXISTS `received_by`      INT          NULL COMMENT 'Người xác nhận khách đã nhận hàng (employee.id)' AFTER `receiver_phone`,
    ADD KEY IF NOT EXISTS `idx_dorder_type_status` (`type`, `status`),
    ADD KEY IF NOT EXISTS `idx_dorder_carrier` (`carrier_id`);

-- Đơn cũ: điền loại khách
UPDATE `dealer_order` SET `customer_type` = 'dealer' WHERE `customer_type` IS NULL AND `dealer_id` IS NOT NULL AND `type` <> 'assembly';

-- =====================================================================
-- 5. CHUYỂN TRẠNG THÁI ĐƠN CŨ SANG LUỒNG MỚI
--    Luồng cũ: mọi đơn bán đều qua lắp ráp. Luồng mới: đơn bán lấy máy có sẵn trong kho.
--      đã duyệt / đang lắp / lắp xong (đơn bán, đơn bảo hành) → Chờ xuất kho (confirmed)
--      đang giao (đã xuất kho, chưa có thông tin nhà xe)        → Chờ gửi hàng (exported)
-- =====================================================================
UPDATE `dealer_order` SET `status` = 'confirmed' WHERE `type` IN ('new', 'warranty') AND `status` IN ('assembling', 'assembled');
UPDATE `dealer_order` SET `status` = 'exported'  WHERE `status` = 'delivering';
UPDATE `dealer_order` SET `approved_at` = COALESCE(`updated_at`, `ordered_at`) WHERE `approved_at` IS NULL AND `approved_by` IS NOT NULL;

ALTER TABLE `dealer_order`
    MODIFY COLUMN `status` ENUM('pending','confirmed','exported','received','assembling','assembled','shipping','delivered','cancelled') NOT NULL DEFAULT 'pending'
        COMMENT 'Đơn bán / bảo hành: pending Chờ duyệt → confirmed Chờ xuất kho → exported Chờ gửi hàng → shipping Đang vận chuyển → delivered Khách đã nhận. Đơn lắp ráp: pending → confirmed Chờ xuất linh kiện → exported Chờ nhận linh kiện → received Đã nhận linh kiện → assembling Đang lắp → assembled Chờ nhập kho → delivered Đã nhập kho. cancelled = huỷ';

-- Linh kiện của đơn bảo hành không gắn với dòng sản phẩm
ALTER TABLE `dealer_order_part`
    MODIFY COLUMN `qty_per_unit` INT NOT NULL DEFAULT 1 COMMENT 'Số lượng / 1 máy (đơn lắp ráp); đơn bảo hành = số lượng gửi';
