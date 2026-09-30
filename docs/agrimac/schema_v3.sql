-- =====================================================================
-- AgriMac CMS – đợt 3: dùng chung toàn bộ sản phẩm / chuyên mục (bỏ cờ is_agrimac)
-- Sản phẩm chưa có mã được gán mã 'SP' + id 5 chữ số (SP00015). Sản phẩm demo SP001..SP005 giữ nguyên mã.
-- =====================================================================
SET NAMES utf8mb4;

UPDATE `product` SET `code` = CONCAT('SP', LPAD(`id`, 5, '0')) WHERE `code` IS NULL OR `code` = '';

ALTER TABLE `product` DROP INDEX IF EXISTS `idx_product_agrimac`;
ALTER TABLE `product` DROP COLUMN IF EXISTS `is_agrimac`;
ALTER TABLE `category` DROP COLUMN IF EXISTS `is_agrimac`;

ALTER TABLE `product`
    MODIFY COLUMN `code` VARCHAR(30) NULL COMMENT 'Mã sản phẩm, VD: SP00015 (tự sinh theo id khi tạo mới)',
    ADD KEY IF NOT EXISTS `idx_product_list` (`is_delete`, `category_id`);
