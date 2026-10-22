-- =====================================================================
-- 1Kho CMS – GHI NHẬN CÔNG NỢ THEO ĐƠN ĐẠI LÝ (05/10/2026)
-- Mỗi đơn đại lý chọn được: ghi nợ cả đơn / nợ một phần / đã thanh toán đủ, sửa được bất cứ lúc nào
-- (trừ đơn đã huỷ). Nợ được ghi vào sổ công nợ khi đơn giao xong; sửa đơn đã giao chỉ ghi phần chênh lệch.
-- DB: MariaDB >= 10.3. Chạy được nhiều lần. Chạy SAU production_migration.sql.
--   mysql --default-character-set=utf8mb4 <db> < production_debt_2026-10-05.sql
-- =====================================================================

SET NAMES utf8mb4;

-- 1. Thông tin thanh toán / công nợ của từng đơn đại lý
ALTER TABLE `dealer_order`
    ADD COLUMN IF NOT EXISTS `debt_mode`       ENUM('full','partial','paid') NULL COMMENT 'Hình thức thanh toán: full = ghi nợ cả đơn, partial = nợ một phần, paid = đã thanh toán đủ; NULL = chưa chọn (khi giao xong tự ghi nợ cả đơn)' AFTER `total_cost`,
    ADD COLUMN IF NOT EXISTS `debt_amount`     BIGINT       NOT NULL DEFAULT 0 COMMENT 'Số tiền nợ của đơn theo lựa chọn (cả đơn = total_amount, đã thanh toán đủ = 0)' AFTER `debt_mode`,
    ADD COLUMN IF NOT EXISTS `debt_recorded`   BIGINT       NOT NULL DEFAULT 0 COMMENT 'Số nợ của đơn đã ghi vào sổ công nợ (dealer_ledger / dealer.current_debt); chỉ phát sinh khi đơn đã giao' AFTER `debt_amount`,
    ADD COLUMN IF NOT EXISTS `paid_method`     VARCHAR(30)  NULL COMMENT 'Hình thức trả phần không ghi nợ: Tiền mặt / Chuyển khoản' AFTER `debt_amount`,
    ADD COLUMN IF NOT EXISTS `debt_note`       VARCHAR(300) NULL COMMENT 'Ghi chú thanh toán / công nợ của đơn' AFTER `paid_method`,
    ADD COLUMN IF NOT EXISTS `debt_updated_at` DATETIME     NULL COMMENT 'Lần sửa công nợ gần nhất' AFTER `debt_note`,
    ADD COLUMN IF NOT EXISTS `debt_updated_by` INT          NULL COMMENT 'Người sửa công nợ gần nhất (employee.id)' AFTER `debt_updated_at`;

-- 2. Sổ công nợ: thêm loại "debit_reverse" = giảm nợ do sửa công nợ của đơn đã giao (không phải thu tiền)
ALTER TABLE `dealer_ledger`
    MODIFY COLUMN `type` ENUM('debit','credit','adjust','debit_reverse') NOT NULL
        COMMENT 'debit = phát sinh nợ theo đơn, credit = thu tiền, adjust = điều chỉnh / số dư đầu kỳ (tăng nợ), debit_reverse = giảm nợ do sửa công nợ của đơn đã giao';

-- 3. Đơn đã giao trước đợt này: hệ thống cũ đã ghi nợ cả đơn khi giao → đánh dấu "ghi nợ cả đơn"
UPDATE `dealer_order`
SET `debt_mode` = 'full', `debt_amount` = `total_amount`, `debt_recorded` = `total_amount`
WHERE `status` = 'delivered' AND `type` = 'new' AND `debt_mode` IS NULL;

-- Đơn đã giao đã có debt_amount nhưng chưa có debt_recorded (chạy bản nháp trước của file này)
UPDATE `dealer_order`
SET `debt_recorded` = `debt_amount`
WHERE `status` = 'delivered' AND `type` = 'new' AND `debt_recorded` = 0 AND `debt_amount` > 0;
