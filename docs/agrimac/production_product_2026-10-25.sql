-- 1Kho CMS – 25/10/2026
--   1. "Nguồn hàng" của sản phẩm không còn bắt buộc.
--   2. Chiết khấu theo từng dòng sản phẩm và chiết khấu cuối đơn.
-- Chạy SAU production_flow_2026-10-22.sql. Chạy lại nhiều lần không lỗi.

-- 1. NULL = chưa xác định nguồn hàng (khung "Linh kiện cấu thành" chỉ hiện khi chọn Tự lắp / Cả hai)
ALTER TABLE `product`
    MODIFY `source_type` ENUM('import','assembly','both') NULL DEFAULT NULL;

-- 2a. Chiết khấu từng dòng: line_total = qty * unit_price - discount_amount
ALTER TABLE `dealer_order_item`
    ADD COLUMN IF NOT EXISTS `discount_type`   ENUM('percent','amount') NOT NULL DEFAULT 'amount' COMMENT 'Kiểu chiết khấu dòng: percent = %, amount = số tiền' AFTER `unit_price`,
    ADD COLUMN IF NOT EXISTS `discount_value`  DECIMAL(15,2) NOT NULL DEFAULT 0 COMMENT 'Giá trị nhập: % (0-100, cho phép số lẻ) hoặc số tiền' AFTER `discount_type`,
    ADD COLUMN IF NOT EXISTS `discount_amount` BIGINT NOT NULL DEFAULT 0 COMMENT 'Số tiền chiết khấu của dòng' AFTER `discount_value`;

-- 2b. Chiết khấu cuối đơn: total_amount = subtotal - discount_amount
ALTER TABLE `dealer_order`
    ADD COLUMN IF NOT EXISTS `subtotal`        BIGINT NOT NULL DEFAULT 0 COMMENT 'Tổng các dòng (đã trừ chiết khấu dòng), trước chiết khấu cuối đơn' AFTER `total_amount`,
    ADD COLUMN IF NOT EXISTS `discount_type`   ENUM('percent','amount') NOT NULL DEFAULT 'amount' COMMENT 'Kiểu chiết khấu cuối đơn' AFTER `subtotal`,
    ADD COLUMN IF NOT EXISTS `discount_value`  DECIMAL(15,2) NOT NULL DEFAULT 0 COMMENT 'Giá trị nhập: % (0-100, cho phép số lẻ) hoặc số tiền' AFTER `discount_type`,
    ADD COLUMN IF NOT EXISTS `discount_amount` BIGINT NOT NULL DEFAULT 0 COMMENT 'Số tiền chiết khấu cuối đơn' AFTER `discount_value`;

-- Đơn cũ chưa có chiết khấu: subtotal = total_amount
UPDATE `dealer_order` SET `subtotal` = `total_amount` WHERE `subtotal` = 0 AND `discount_amount` = 0;
