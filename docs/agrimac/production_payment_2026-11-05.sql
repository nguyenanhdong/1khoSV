-- 1Kho CMS – 05/11/2026: phiếu thu theo đơn (trả nhiều lần, có dấu vết từng lần).
-- Mỗi lần đại lý trả tiền cho một đơn = 1 phiếu thu; không sửa / xoá, chỉ huỷ có lý do.
-- Còn nợ của đơn = giá trị đơn − tổng phiếu thu còn hiệu lực.
-- Chạy SAU production_stock_2026-11-05.sql. Chạy lại nhiều lần không lỗi.

CREATE TABLE IF NOT EXISTS `dealer_order_payment` (
    `id`          INT          NOT NULL AUTO_INCREMENT COMMENT 'Khoá chính',
    `code`        VARCHAR(20)  NOT NULL COMMENT 'Mã phiếu thu: PT + năm + số (VD PT20260001)',
    `order_id`    INT          NOT NULL COMMENT 'Đơn hàng (dealer_order.id)',
    `dealer_id`   INT          NULL COMMENT 'Đại lý (dealer.id)',
    `amount`      BIGINT       NOT NULL COMMENT 'Số tiền trả lần này',
    `method`      VARCHAR(30)  NOT NULL COMMENT 'Tiền mặt / Chuyển khoản',
    `paid_at`     DATE         NOT NULL COMMENT 'Ngày đại lý trả',
    `note`        VARCHAR(300) NULL COMMENT 'Ghi chú (số UNC, người nộp...)',
    `ledger_id`   INT          NULL COMMENT 'Dòng sổ công nợ (dealer_ledger.id) khi trả sau lúc giao hàng',
    `created_by`  INT          NOT NULL COMMENT 'Người ghi nhận (employee.id)',
    `created_at`  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `voided_at`   DATETIME     NULL COMMENT 'Thời điểm huỷ phiếu (NULL = còn hiệu lực)',
    `voided_by`   INT          NULL COMMENT 'Người huỷ',
    `void_reason` VARCHAR(300) NULL COMMENT 'Lý do huỷ',
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_payment_code` (`code`),
    KEY `idx_payment_order` (`order_id`),
    KEY `idx_payment_paid` (`paid_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci COMMENT='1Kho – Phiếu thu tiền theo đơn đại lý';

-- Chuyển dữ liệu cũ: phần "đã trả" khai báo trước đây (giá trị đơn − số còn nợ) → 1 phiếu thu.
-- Không đổi sổ công nợ: số nợ đã ghi sổ của đơn (debt_recorded) vốn đã trừ phần này.
INSERT INTO `dealer_order_payment` (`code`, `order_id`, `dealer_id`, `amount`, `method`, `paid_at`, `note`, `created_by`, `created_at`)
SELECT CONCAT('PTC', LPAD(o.id, 7, '0')), o.id, o.dealer_id, o.total_amount - o.debt_amount, COALESCE(o.paid_method, 'Tiền mặt'),
       DATE(COALESCE(o.debt_updated_at, o.ordered_at)), CONCAT('Chuyển từ dữ liệu cũ', IF(o.debt_note IS NULL, '', CONCAT(' · ', o.debt_note))),
       COALESCE(o.debt_updated_by, o.created_by, o.sale_id, 0), COALESCE(o.debt_updated_at, o.ordered_at)
FROM `dealer_order` o
WHERE o.type = 'new' AND o.dealer_id IS NOT NULL AND o.status <> 'cancelled'
  AND o.debt_mode IN ('partial', 'paid') AND o.total_amount - o.debt_amount > 0
  AND NOT EXISTS (SELECT 1 FROM `dealer_order_payment` p WHERE p.order_id = o.id);

-- Còn nợ của đơn chưa chọn hình thức = cả đơn
UPDATE `dealer_order` o
SET o.debt_mode = 'full', o.debt_amount = o.total_amount
WHERE o.type = 'new' AND o.dealer_id IS NOT NULL AND o.debt_mode IS NULL AND o.status <> 'cancelled';
