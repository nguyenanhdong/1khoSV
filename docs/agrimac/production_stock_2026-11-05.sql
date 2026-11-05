-- 1Kho CMS – 05/11/2026: đơn bán có thể gồm cả máy và linh kiện lẻ.
-- (Phiếu nhập / xuất nhiều dòng không cần đổi bảng: stock_voucher_item vốn đã nhiều dòng.)
-- Chạy SAU production_product_2026-10-25.sql. Chạy lại nhiều lần không lỗi.

ALTER TABLE `dealer_order_item`
    ADD COLUMN IF NOT EXISTS `item_type` ENUM('product','part') NOT NULL DEFAULT 'product' COMMENT 'Loại dòng: product = máy, part = linh kiện' AFTER `order_id`,
    ADD COLUMN IF NOT EXISTS `part_id`   INT NULL COMMENT 'Linh kiện (part.id) khi item_type = part' AFTER `product_id`,
    MODIFY `product_id` INT NULL COMMENT 'Máy (product.id) khi item_type = product';
