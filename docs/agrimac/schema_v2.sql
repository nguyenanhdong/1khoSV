-- =====================================================================
-- AgriMac CMS – bổ sung schema đợt 2 (ghép dữ liệu). Chạy sau schema.sql.
-- Chạy được nhiều lần.
-- =====================================================================
SET NAMES utf8mb4;

-- Tách sản phẩm / danh mục AgriMac khỏi dữ liệu sàn 1kho dùng chung bảng
ALTER TABLE `product`
    ADD COLUMN IF NOT EXISTS `is_agrimac` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1: sản phẩm AgriMac CMS / 0: sản phẩm sàn 1kho' AFTER `id`,
    ADD KEY IF NOT EXISTS `idx_product_agrimac` (`is_agrimac`, `is_delete`);

ALTER TABLE `category`
    ADD COLUMN IF NOT EXISTS `is_agrimac` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1: danh mục AgriMac CMS / 0: danh mục sàn 1kho' AFTER `id`;

-- Thông tin duyệt / giao nhận của đơn đại lý
ALTER TABLE `dealer_order`
    ADD COLUMN IF NOT EXISTS `invoice_date`   DATE         NULL COMMENT 'Ngày hoá đơn' AFTER `invoice_no`,
    ADD COLUMN IF NOT EXISTS `receiver_name`  VARCHAR(255) NULL COMMENT 'Người nhận hàng tại đại lý' AFTER `delivered_at`,
    ADD COLUMN IF NOT EXISTS `receiver_phone` VARCHAR(30)  NULL COMMENT 'SĐT người nhận' AFTER `receiver_name`,
    ADD COLUMN IF NOT EXISTS `delivery_note`  VARCHAR(500) NULL COMMENT 'Ghi chú giao hàng' AFTER `receiver_phone`,
    ADD COLUMN IF NOT EXISTS `cancelled_at`   DATETIME     NULL COMMENT 'Thời điểm huỷ' AFTER `cancel_reason`;

-- Người phụ trách nhận / nhập máy vào kho khi lắp xong
ALTER TABLE `stock_voucher`
    ADD COLUMN IF NOT EXISTS `employee_id` INT NULL COMMENT 'Nhân viên liên quan (VD: NV giao hàng khi xuất giao)' AFTER `order_id`;

-- Chi trả hoa hồng (một lần chi có thể gộp nhiều đơn)
CREATE TABLE IF NOT EXISTS `commission_payout` (
    `id`          INT          NOT NULL AUTO_INCREMENT COMMENT 'Khoá chính',
    `employee_id` INT          NOT NULL COMMENT 'Nhân viên nhận (employee.id)',
    `role`        ENUM('sale','delivery') NOT NULL COMMENT 'Loại hoa hồng được chi',
    `amount`      BIGINT       NOT NULL COMMENT 'Số tiền chi',
    `method`      VARCHAR(30)  NOT NULL COMMENT 'Chuyển khoản / Tiền mặt / Cộng vào lương',
    `note`        VARCHAR(300) NULL COMMENT 'Số chứng từ / kỳ lương',
    `paid_at`     DATE         NOT NULL COMMENT 'Ngày chi',
    `created_by`  INT          NOT NULL COMMENT 'Người lập phiếu chi (employee.id)',
    `created_at`  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Thời điểm lập',
    PRIMARY KEY (`id`),
    KEY `idx_payout_emp` (`employee_id`, `role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci COMMENT='AgriMac – Phiếu chi hoa hồng';
