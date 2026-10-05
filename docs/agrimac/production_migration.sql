-- =====================================================================
-- 1Kho CMS – MIGRATION PRODUCTION (gộp schema.sql + schema_v2.sql + schema_v3.sql)
-- DB: MariaDB >= 10.3 (dùng ADD COLUMN IF NOT EXISTS / DROP ... IF EXISTS — MySQL 8 KHÔNG hỗ trợ cú pháp này).
-- Chạy được nhiều lần: chạy lại không lỗi, không nhân đôi dữ liệu.
--
-- TRƯỚC KHI CHẠY
--   1. Backup:   mysqldump --single-transaction --routines --default-character-set=utf8mb4 <db> > backup_<db>_$(date +%Y%m%d_%H%M%S).sql
--   2. Kiểm tra cột tiền có phần lẻ (phải trả về 0 dòng, nếu có thì dừng lại xem trước khi đổi sang BIGINT):
--        SELECT 'product' t, id FROM product WHERE price <> FLOOR(price) OR price_discount <> FLOOR(price_discount) OR fee_ship <> FLOOR(fee_ship)
--        UNION ALL SELECT 'order', id FROM `order` WHERE price <> FLOOR(price) OR total_price <> FLOOR(total_price) OR fee_ship <> FLOOR(fee_ship)
--        UNION ALL SELECT 'order_product', id FROM order_product WHERE price <> FLOOR(price) OR total_price <> FLOOR(total_price)
--        UNION ALL SELECT 'agent', id FROM agent WHERE account_balance <> FLOOR(account_balance) OR account_balance_sub <> FLOOR(account_balance_sub);
--   3. Chạy:     mysql --default-character-set=utf8mb4 <db> < production_migration.sql
--
-- Đợt cập nhật frontend 30/09/2026: chạy thêm production_frontend_2026-09-30.sql (index, số điện thoại +84).
-- Công nợ theo đơn đại lý 05/10/2026: chạy thêm production_debt_2026-10-05.sql.
-- KHÔNG gồm dữ liệu demo (đại lý, đơn, NCC, linh kiện, nhân viên mẫu của lệnh `php yii agrimac-seed`) — không chạy seed trên production.
-- =====================================================================

SET NAMES utf8mb4;

-- =====================================================================
-- PHẦN 1. ĐỔI CÁC CỘT TIỀN FLOAT → BIGINT
--   FLOAT chỉ chính xác ~7 chữ số, không đủ cho giá máy hàng trăm triệu – tỷ đồng.
--   Giữ FLOAT: cân nặng/kích thước, điểm đánh giá, xu/điểm ví, voucher.price (có thể là %),
--   order.voucher_point_refundable (xu).
-- =====================================================================

ALTER TABLE `product`
    MODIFY COLUMN `price`          BIGINT NULL DEFAULT 0 COMMENT 'Giá bán',
    MODIFY COLUMN `price_discount` BIGINT NULL DEFAULT 0 COMMENT 'Giá khuyến mại',
    MODIFY COLUMN `fee_ship`       BIGINT NULL DEFAULT 0 COMMENT 'Phí vận chuyển (Tính theo cân nặng)';

ALTER TABLE `product_classification_combination`
    MODIFY COLUMN `price`          BIGINT NULL DEFAULT 0 COMMENT 'Giá gốc',
    MODIFY COLUMN `price_discount` BIGINT NULL DEFAULT 0 COMMENT 'Giá khuyến mại';

ALTER TABLE `order`
    MODIFY COLUMN `price`         BIGINT NULL DEFAULT 0 COMMENT 'Tổng giá gốc các sản phẩm của đơn hàng',
    MODIFY COLUMN `price_voucher` BIGINT NULL DEFAULT 0 COMMENT 'Số tiền được giảm khi dùng voucher',
    MODIFY COLUMN `price_wallet`  BIGINT NULL DEFAULT 0 COMMENT 'Số điểm trong ví dùng để thanh toán nếu use_wallet_payment = 1',
    MODIFY COLUMN `fee_ship`      BIGINT NULL DEFAULT 0 COMMENT 'Phí vận chuyển',
    MODIFY COLUMN `total_price`   BIGINT NULL DEFAULT 0 COMMENT 'Tổng tiền đơn hàng = (price +  fee_ship) - price_voucher - price_wallet';

ALTER TABLE `order_product`
    MODIFY COLUMN `price_origin` BIGINT NOT NULL COMMENT 'Giá gốc (Chưa khuyến mại)',
    MODIFY COLUMN `price`        BIGINT NULL DEFAULT 0 COMMENT 'Đơn giá',
    MODIFY COLUMN `total_price`  BIGINT NULL DEFAULT 0 COMMENT 'Tổng tiền (price*quantity)';

ALTER TABLE `order_refund`
    MODIFY COLUMN `price_refund` BIGINT NOT NULL COMMENT 'Số tiền hoàn cho khách';

ALTER TABLE `agent`
    MODIFY COLUMN `account_balance`     BIGINT NULL DEFAULT 0 COMMENT 'Số dư tài khoản chính (Có thể rút tiền)',
    MODIFY COLUMN `account_balance_sub` BIGINT NULL DEFAULT 0 COMMENT 'Số dư tài khoản phụ (Không được rút). Chờ xx ngày sau khi giao dịch thành công thì chuyển vào tài khoản chính';

ALTER TABLE `history_agent_account_balance`
    MODIFY COLUMN `money`     BIGINT NULL DEFAULT 0 COMMENT 'Số tiền rút, Số tiền cộng,..',
    MODIFY COLUMN `money_sub` BIGINT NULL DEFAULT 0 COMMENT 'Số dư tài khoản phụ sau khi chuyển qua tài khoản chính (Khi is_from_sub = 1)';

ALTER TABLE `advertisement`
    MODIFY COLUMN `price`          BIGINT NULL DEFAULT 0 COMMENT 'Giá mua/bán nếu có',
    MODIFY COLUMN `price_discount` BIGINT NULL DEFAULT 0 COMMENT 'Giá khuyến mại (nếu có)';

-- =====================================================================
-- PHẦN 2. THÊM CỘT CHO BẢNG CÓ SẴN
-- =====================================================================

-- 2.1 employee: nhân viên dùng CMS/App (vai trò gán qua auth_assignment)
ALTER TABLE `employee`
    ADD COLUMN IF NOT EXISTS `last_login`  DATETIME   NULL COMMENT 'Thời điểm đăng nhập gần nhất' AFTER `last_update`,
    ADD COLUMN IF NOT EXISTS `can_use_app` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1: được dùng app di động (Sale, giao hàng) / 0: chỉ dùng CMS' AFTER `last_login`;

-- 2.2 product: mã sản phẩm, nguồn hàng, giá vốn, thông số máy, ngưỡng tồn
ALTER TABLE `product`
    ADD COLUMN IF NOT EXISTS `code`        VARCHAR(30)  NULL COMMENT 'Mã sản phẩm, VD: SP00015 (tự sinh theo id khi tạo mới)' AFTER `id`,
    ADD COLUMN IF NOT EXISTS `supplier_id` INT          NULL COMMENT 'Nhà cung cấp máy (supplier.id), NULL nếu tự lắp ráp' AFTER `category_id`,
    ADD COLUMN IF NOT EXISTS `cost_price`  BIGINT       NOT NULL DEFAULT 0 COMMENT 'Giá vốn / 1 máy (nhập nguyên chiếc hoặc tổng BOM)' AFTER `price_discount`,
    ADD COLUMN IF NOT EXISTS `source_type` ENUM('import','assembly','both') NOT NULL DEFAULT 'import' COMMENT 'Nguồn hàng: import = nhập nguyên chiếc, assembly = tự lắp từ linh kiện, both = cả hai' AFTER `cost_price`,
    ADD COLUMN IF NOT EXISTS `horsepower`  SMALLINT     NULL COMMENT 'Công suất (HP)' AFTER `source_type`,
    ADD COLUMN IF NOT EXISTS `drive_type`  VARCHAR(10)  NULL COMMENT 'Hệ dẫn động: 2WD / 4WD' AFTER `horsepower`,
    ADD COLUMN IF NOT EXISTS `specs`       VARCHAR(255) NULL COMMENT 'Mô tả thông số ngắn, VD: 34HP · 4WD · 1450kg' AFTER `drive_type`,
    ADD COLUMN IF NOT EXISTS `min_stock`   INT          NOT NULL DEFAULT 3 COMMENT 'Ngưỡng cảnh báo sắp hết hàng' AFTER `quantity_in_stock`,
    ADD UNIQUE KEY IF NOT EXISTS `uq_product_code` (`code`),
    ADD KEY IF NOT EXISTS `idx_product_supplier` (`supplier_id`),
    ADD KEY IF NOT EXISTS `idx_product_list` (`is_delete`, `category_id`);

-- Gán mã cho sản phẩm chưa có: 'SP' + id đệm 0 đủ 5 chữ số (id > 99999 giữ nguyên độ dài, không bị cắt).
UPDATE `product`
SET `code` = CONCAT('SP', LPAD(`id`, GREATEST(5, LENGTH(`id`)), '0'))
WHERE `code` IS NULL OR `code` = '';

-- =====================================================================
-- PHẦN 3. BẢNG MỚI
-- =====================================================================

-- 3.1 Nhà cung cấp (máy nguyên chiếc và linh kiện)
CREATE TABLE IF NOT EXISTS `supplier` (
    `id`         INT          NOT NULL AUTO_INCREMENT COMMENT 'Khoá chính',
    `code`       VARCHAR(20)  NOT NULL COMMENT 'Mã NCC, VD: NCC01',
    `name`       VARCHAR(255) NOT NULL COMMENT 'Tên nhà cung cấp',
    `phone`      VARCHAR(30)  NULL COMMENT 'Số điện thoại liên hệ',
    `email`      VARCHAR(100) NULL COMMENT 'Email liên hệ',
    `address`    VARCHAR(300) NULL COMMENT 'Địa chỉ',
    `tax_code`   VARCHAR(20)  NULL COMMENT 'Mã số thuế',
    `status`     TINYINT(1)   NOT NULL DEFAULT 1 COMMENT '1: đang hợp tác / 0: ngừng',
    `created_at` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Ngày tạo',
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_supplier_code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci COMMENT='1Kho CMS – Nhà cung cấp';

-- 3.2 Danh mục linh kiện
CREATE TABLE IF NOT EXISTS `part_category` (
    `id`         INT          NOT NULL AUTO_INCREMENT COMMENT 'Khoá chính',
    `name`       VARCHAR(100) NOT NULL COMMENT 'Tên nhóm: Động cơ, Hộp số, Thủy lực, Thân máy, Lốp & Bánh, Điện, Lọc, Làm mát...',
    `sort_order` INT          NOT NULL DEFAULT 0 COMMENT 'Thứ tự hiển thị',
    `status`     TINYINT(1)   NOT NULL DEFAULT 1 COMMENT '1: hiển thị / 0: ẩn',
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci COMMENT='1Kho CMS – Danh mục linh kiện';

-- 3.3 Linh kiện (tồn kho riêng, nhập qua stock_voucher item_type = part)
CREATE TABLE IF NOT EXISTS `part` (
    `id`          INT          NOT NULL AUTO_INCREMENT COMMENT 'Khoá chính',
    `code`        VARCHAR(20)  NOT NULL COMMENT 'Mã linh kiện, VD: LK001',
    `name`        VARCHAR(255) NOT NULL COMMENT 'Tên linh kiện',
    `category_id` INT          NOT NULL COMMENT 'Nhóm linh kiện (part_category.id)',
    `supplier_id` INT          NULL COMMENT 'Nhà cung cấp mặc định (supplier.id)',
    `unit`        VARCHAR(20)  NOT NULL DEFAULT 'cái' COMMENT 'Đơn vị tính: cái, bộ...',
    `cost_price`  BIGINT       NOT NULL DEFAULT 0 COMMENT 'Giá vốn hiện tại / 1 đơn vị (cập nhật theo phiếu nhập gần nhất)',
    `stock`       INT          NOT NULL DEFAULT 0 COMMENT 'Số lượng tồn kho',
    `min_stock`   INT          NOT NULL DEFAULT 0 COMMENT 'Ngưỡng cảnh báo sắp hết',
    `status`      TINYINT(1)   NOT NULL DEFAULT 1 COMMENT '1: đang dùng / 0: ngừng',
    `created_at`  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Ngày tạo',
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_part_code` (`code`),
    KEY `idx_part_category` (`category_id`),
    KEY `idx_part_supplier` (`supplier_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci COMMENT='1Kho CMS – Linh kiện lắp ráp';

-- 3.4 BOM mặc định theo sản phẩm
CREATE TABLE IF NOT EXISTS `product_bom` (
    `id`         INT NOT NULL AUTO_INCREMENT COMMENT 'Khoá chính',
    `product_id` INT NOT NULL COMMENT 'Máy thành phẩm (product.id)',
    `part_id`    INT NOT NULL COMMENT 'Linh kiện (part.id)',
    `qty`        INT NOT NULL DEFAULT 1 COMMENT 'Số lượng linh kiện cho 1 máy',
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_bom` (`product_id`, `part_id`),
    KEY `idx_bom_part` (`part_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci COMMENT='1Kho CMS – Định mức linh kiện mặc định của sản phẩm';

-- 3.5 Đại lý (bên mua sỉ của công ty; khác bảng agent là shop bán trên sàn)
CREATE TABLE IF NOT EXISTS `dealer` (
    `id`             INT          NOT NULL AUTO_INCREMENT COMMENT 'Khoá chính',
    `code`           VARCHAR(20)  NOT NULL COMMENT 'Mã đại lý, VD: DL001',
    `name`           VARCHAR(255) NOT NULL COMMENT 'Tên đại lý',
    `contact_name`   VARCHAR(255) NULL COMMENT 'Người liên hệ',
    `phone`          VARCHAR(30)  NULL COMMENT 'Số điện thoại',
    `address`        VARCHAR(300) NULL COMMENT 'Địa chỉ',
    `province_id`    INT          NULL COMMENT 'Tỉnh/thành (province.id)',
    `tax_code`       VARCHAR(20)  NULL COMMENT 'Mã số thuế (xuất hoá đơn)',
    `level`          ENUM('gold','silver','bronze') NOT NULL DEFAULT 'bronze' COMMENT 'Cấp đại lý: gold = Vàng, silver = Bạc, bronze = Đồng',
    `credit_limit`   BIGINT       NOT NULL DEFAULT 0 COMMENT 'Hạn mức công nợ tối đa',
    `current_debt`   BIGINT       NOT NULL DEFAULT 0 COMMENT 'Dư nợ hiện tại (cache = tổng dealer_ledger, cập nhật cùng giao dịch)',
    `total_purchase` BIGINT       NOT NULL DEFAULT 0 COMMENT 'Tổng giá trị đã mua (cache)',
    `sale_id`        INT          NULL COMMENT 'Sale phụ trách (employee.id)',
    `status`         TINYINT(1)   NOT NULL DEFAULT 1 COMMENT '1: hoạt động / 0: ngừng',
    `created_at`     TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Ngày tạo',
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_dealer_code` (`code`),
    KEY `idx_dealer_province` (`province_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci COMMENT='1Kho CMS – Đại lý phân phối';

-- 3.6 Sổ công nợ đại lý
CREATE TABLE IF NOT EXISTS `dealer_ledger` (
    `id`            INT          NOT NULL AUTO_INCREMENT COMMENT 'Khoá chính',
    `dealer_id`     INT          NOT NULL COMMENT 'Đại lý (dealer.id)',
    `type`          ENUM('debit','credit','adjust') NOT NULL COMMENT 'debit = phát sinh nợ (giao hàng), credit = thu tiền, adjust = điều chỉnh',
    `amount`        BIGINT       NOT NULL COMMENT 'Số tiền (luôn dương; chiều tăng/giảm nợ theo type)',
    `balance_after` BIGINT       NOT NULL DEFAULT 0 COMMENT 'Dư nợ sau giao dịch',
    `order_id`      INT          NULL COMMENT 'Đơn hàng liên quan (dealer_order.id)',
    `method`        VARCHAR(30)  NULL COMMENT 'Hình thức thu: cash / bank',
    `note`          VARCHAR(300) NULL COMMENT 'Ghi chú',
    `created_by`    INT          NOT NULL COMMENT 'Người ghi sổ (employee.id)',
    `created_at`    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Thời điểm ghi sổ',
    PRIMARY KEY (`id`),
    KEY `idx_ledger_dealer` (`dealer_id`, `created_at`),
    KEY `idx_ledger_order` (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci COMMENT='1Kho CMS – Sổ công nợ đại lý';

-- 3.7 CRM – khách tiềm năng và lịch sử trao đổi
CREATE TABLE IF NOT EXISTS `crm_lead` (
    `id`             INT          NOT NULL AUTO_INCREMENT COMMENT 'Khoá chính',
    `name`           VARCHAR(255) NOT NULL COMMENT 'Tên khách / tổ chức',
    `phone`          VARCHAR(30)  NULL COMMENT 'Số điện thoại',
    `dealer_id`      INT          NULL COMMENT 'Đại lý giới thiệu (dealer.id), NULL = khách trực tiếp',
    `sale_id`        INT          NOT NULL COMMENT 'Sale phụ trách (employee.id)',
    `product_id`     INT          NULL COMMENT 'Sản phẩm quan tâm (product.id)',
    `expected_value` BIGINT       NOT NULL DEFAULT 0 COMMENT 'Giá trị dự kiến',
    `stage`          ENUM('approach','interested','quoted','won','lost') NOT NULL DEFAULT 'approach' COMMENT 'Giai đoạn: Tiếp cận / Quan tâm / Báo giá / Chốt đơn / Thất bại',
    `order_id`       INT          NULL COMMENT 'Đơn hàng tạo ra khi chốt (dealer_order.id)',
    `created_at`     TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Ngày tạo',
    `updated_at`     DATETIME     NULL COMMENT 'Ngày cập nhật gần nhất',
    PRIMARY KEY (`id`),
    KEY `idx_lead_stage` (`stage`, `sale_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci COMMENT='1Kho CMS – CRM khách tiềm năng';

CREATE TABLE IF NOT EXISTS `crm_lead_note` (
    `id`         INT       NOT NULL AUTO_INCREMENT COMMENT 'Khoá chính',
    `lead_id`    INT       NOT NULL COMMENT 'Khách tiềm năng (crm_lead.id)',
    `content`    TEXT      NOT NULL COMMENT 'Nội dung trao đổi',
    `created_by` INT       NOT NULL COMMENT 'Người ghi (employee.id)',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Thời điểm ghi',
    PRIMARY KEY (`id`),
    KEY `idx_note_lead` (`lead_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci COMMENT='1Kho CMS – Lịch sử trao đổi với khách tiềm năng';

-- 3.8 Đơn hàng đại lý (tách khỏi `order` của sàn mà app mobile đang dùng)
CREATE TABLE IF NOT EXISTS `dealer_order` (
    `id`             INT          NOT NULL AUTO_INCREMENT COMMENT 'Khoá chính',
    `code`           VARCHAR(20)  NOT NULL COMMENT 'Mã đơn, VD: DH2025001',
    `type`           ENUM('new','warranty') NOT NULL DEFAULT 'new' COMMENT 'new = đơn bán mới, warranty = đơn bảo hành (gửi linh kiện thay thế)',
    `dealer_id`      INT          NOT NULL COMMENT 'Đại lý đặt hàng (dealer.id)',
    `lead_id`        INT          NULL COMMENT 'Khách tiềm năng nguồn (crm_lead.id)',
    `claim_id`       INT          NULL COMMENT 'Phiếu khiếu nại gốc khi type = warranty (warranty_claim.id)',
    `status`         ENUM('pending','confirmed','assembling','assembled','delivering','delivered','cancelled') NOT NULL DEFAULT 'pending' COMMENT 'Chờ duyệt / Đã duyệt / Đang lắp ráp / Lắp ráp xong / Đang giao / Hoàn thành / Huỷ',
    `total_amount`   BIGINT       NOT NULL DEFAULT 0 COMMENT 'Doanh thu (tổng dealer_order_item.line_total)',
    `total_cost`     BIGINT       NOT NULL DEFAULT 0 COMMENT 'Giá vốn linh kiện (chốt khi gửi lắp ráp)',
    `sale_id`        INT          NOT NULL COMMENT 'Sale phụ trách (employee.id)',
    `approved_by`    INT          NULL COMMENT 'KT bán hàng duyệt đơn (employee.id)',
    `assembler_id`   INT          NULL COMMENT 'Người lắp ráp (employee.id)',
    `exporter_id`    INT          NULL COMMENT 'KT xuất kho (employee.id)',
    `delivery_id`    INT          NULL COMMENT 'Nhân viên giao hàng (employee.id)',
    `invoice_no`     VARCHAR(50)  NULL COMMENT 'Số hoá đơn',
    `invoice_date`   DATE         NULL COMMENT 'Ngày hoá đơn',
    `assembly_note`  TEXT         NULL COMMENT 'Ghi chú lắp ráp',
    `note`           VARCHAR(500) NULL COMMENT 'Ghi chú đơn hàng',
    `cancel_reason`  VARCHAR(300) NULL COMMENT 'Lý do huỷ',
    `cancelled_at`   DATETIME     NULL COMMENT 'Thời điểm huỷ',
    `ordered_at`     DATETIME     NOT NULL COMMENT 'Ngày đặt hàng',
    `delivered_at`   DATETIME     NULL COMMENT 'Ngày giao xong',
    `receiver_name`  VARCHAR(255) NULL COMMENT 'Người nhận hàng tại đại lý',
    `receiver_phone` VARCHAR(30)  NULL COMMENT 'SĐT người nhận',
    `delivery_note`  VARCHAR(500) NULL COMMENT 'Ghi chú giao hàng',
    `created_at`     TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Ngày tạo bản ghi',
    `updated_at`     DATETIME     NULL COMMENT 'Ngày cập nhật gần nhất',
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_dorder_code` (`code`),
    KEY `idx_dorder_status` (`status`),
    KEY `idx_dorder_dealer` (`dealer_id`),
    KEY `idx_dorder_sale` (`sale_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci COMMENT='1Kho CMS – Đơn hàng đại lý';

-- Nếu bảng dealer_order đã được tạo từ bản schema cũ thì bổ sung các cột giao nhận
ALTER TABLE `dealer_order`
    ADD COLUMN IF NOT EXISTS `invoice_date`   DATE         NULL COMMENT 'Ngày hoá đơn' AFTER `invoice_no`,
    ADD COLUMN IF NOT EXISTS `receiver_name`  VARCHAR(255) NULL COMMENT 'Người nhận hàng tại đại lý' AFTER `delivered_at`,
    ADD COLUMN IF NOT EXISTS `receiver_phone` VARCHAR(30)  NULL COMMENT 'SĐT người nhận' AFTER `receiver_name`,
    ADD COLUMN IF NOT EXISTS `delivery_note`  VARCHAR(500) NULL COMMENT 'Ghi chú giao hàng' AFTER `receiver_phone`,
    ADD COLUMN IF NOT EXISTS `cancelled_at`   DATETIME     NULL COMMENT 'Thời điểm huỷ' AFTER `cancel_reason`;

CREATE TABLE IF NOT EXISTS `dealer_order_item` (
    `id`         INT    NOT NULL AUTO_INCREMENT COMMENT 'Khoá chính',
    `order_id`   INT    NOT NULL COMMENT 'Đơn hàng (dealer_order.id)',
    `product_id` INT    NOT NULL COMMENT 'Máy đặt mua (product.id)',
    `qty`        INT    NOT NULL COMMENT 'Số lượng máy',
    `unit_price` BIGINT NOT NULL DEFAULT 0 COMMENT 'Đơn giá bán / 1 máy',
    `line_total` BIGINT NOT NULL DEFAULT 0 COMMENT 'Thành tiền = qty × unit_price',
    PRIMARY KEY (`id`),
    KEY `idx_doi_order` (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci COMMENT='1Kho CMS – Dòng sản phẩm của đơn hàng đại lý';

CREATE TABLE IF NOT EXISTS `dealer_order_part` (
    `id`            INT        NOT NULL AUTO_INCREMENT COMMENT 'Khoá chính',
    `order_id`      INT        NOT NULL COMMENT 'Đơn hàng (dealer_order.id)',
    `order_item_id` INT        NULL COMMENT 'Dòng sản phẩm dùng linh kiện này (dealer_order_item.id)',
    `part_id`       INT        NOT NULL COMMENT 'Linh kiện (part.id)',
    `qty_per_unit`  INT        NOT NULL DEFAULT 1 COMMENT 'Số lượng / 1 máy',
    `qty_total`     INT        NOT NULL COMMENT 'Tổng số lượng = qty_per_unit × số máy',
    `unit_cost`     BIGINT     NOT NULL COMMENT 'Giá vốn / 1 đơn vị, chốt tại thời điểm chọn BOM',
    `is_picked`     TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1: bộ phận lắp ráp đã nhận linh kiện',
    `picked_by`     INT        NULL COMMENT 'Người nhận (employee.id)',
    `picked_at`     DATETIME   NULL COMMENT 'Thời điểm nhận',
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_dop` (`order_id`, `order_item_id`, `part_id`),
    KEY `idx_dop_part` (`part_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci COMMENT='1Kho CMS – BOM thực tế của đơn + checklist nhận linh kiện';

CREATE TABLE IF NOT EXISTS `dealer_order_log` (
    `id`          INT          NOT NULL AUTO_INCREMENT COMMENT 'Khoá chính',
    `order_id`    INT          NOT NULL COMMENT 'Đơn hàng (dealer_order.id)',
    `from_status` VARCHAR(20)  NULL COMMENT 'Trạng thái trước',
    `to_status`   VARCHAR(20)  NOT NULL COMMENT 'Trạng thái sau',
    `employee_id` INT          NOT NULL COMMENT 'Người thao tác (employee.id)',
    `note`        VARCHAR(300) NULL COMMENT 'Ghi chú',
    `created_at`  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Thời điểm chuyển trạng thái',
    PRIMARY KEY (`id`),
    KEY `idx_dol_order` (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci COMMENT='1Kho CMS – Lịch sử xử lý đơn hàng';

-- 3.9 Phiếu kho: nhập máy / nhập linh kiện / xuất linh kiện cho lắp ráp / xuất máy giao đại lý / trả NCC
CREATE TABLE IF NOT EXISTS `stock_voucher` (
    `id`           INT          NOT NULL AUTO_INCREMENT COMMENT 'Khoá chính',
    `code`         VARCHAR(20)  NOT NULL COMMENT 'Mã phiếu: NK001 (nhập) / XK001 (xuất)',
    `type`         ENUM('import','export','adjust','return') NOT NULL COMMENT 'import = nhập, export = xuất, adjust = kiểm kê điều chỉnh, return = trả NCC',
    `reason`       ENUM('purchase','assembly_issue','assembly_finish','sale','warranty','other') NOT NULL DEFAULT 'purchase' COMMENT 'purchase = mua từ NCC, assembly_issue = xuất LK cho lắp ráp, assembly_finish = nhập máy lắp xong, sale = xuất giao đại lý, warranty = xuất LK bảo hành',
    `supplier_id`  INT          NULL COMMENT 'Nhà cung cấp (phiếu nhập mua / trả hàng)',
    `order_id`     INT          NULL COMMENT 'Đơn hàng liên quan (dealer_order.id)',
    `employee_id`  INT          NULL COMMENT 'Nhân viên liên quan (VD: NV giao hàng khi xuất giao)',
    `total_amount` BIGINT       NOT NULL DEFAULT 0 COMMENT 'Tổng giá trị phiếu',
    `note`         VARCHAR(500) NULL COMMENT 'Ghi chú lô hàng',
    `created_by`   INT          NOT NULL COMMENT 'Người lập phiếu (employee.id)',
    `created_at`   TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Ngày lập phiếu',
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_sv_code` (`code`),
    KEY `idx_sv_type_date` (`type`, `created_at`),
    KEY `idx_sv_order` (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci COMMENT='1Kho CMS – Phiếu nhập/xuất kho';

ALTER TABLE `stock_voucher`
    ADD COLUMN IF NOT EXISTS `employee_id` INT NULL COMMENT 'Nhân viên liên quan (VD: NV giao hàng khi xuất giao)' AFTER `order_id`;

CREATE TABLE IF NOT EXISTS `stock_voucher_item` (
    `id`          INT    NOT NULL AUTO_INCREMENT COMMENT 'Khoá chính',
    `voucher_id`  INT    NOT NULL COMMENT 'Phiếu kho (stock_voucher.id)',
    `item_type`   ENUM('product','part') NOT NULL COMMENT 'product = máy thành phẩm, part = linh kiện',
    `item_id`     INT    NOT NULL COMMENT 'product.id hoặc part.id theo item_type',
    `qty`         INT    NOT NULL COMMENT 'Số lượng (luôn dương; chiều theo stock_voucher.type)',
    `unit_price`  BIGINT NOT NULL DEFAULT 0 COMMENT 'Đơn giá (giá vốn khi nhập / giá bán khi xuất giao)',
    `stock_after` INT    NOT NULL DEFAULT 0 COMMENT 'Tồn kho sau khi ghi phiếu (đối soát)',
    PRIMARY KEY (`id`),
    KEY `idx_svi_voucher` (`voucher_id`),
    KEY `idx_svi_item` (`item_type`, `item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci COMMENT='1Kho CMS – Dòng hàng của phiếu kho';

-- 3.10 Trả hàng cho nhà cung cấp
CREATE TABLE IF NOT EXISTS `supplier_return` (
    `id`          INT          NOT NULL AUTO_INCREMENT COMMENT 'Khoá chính',
    `supplier_id` INT          NOT NULL COMMENT 'Nhà cung cấp (supplier.id)',
    `item_type`   ENUM('product','part') NOT NULL COMMENT 'product = máy, part = linh kiện',
    `item_id`     INT          NOT NULL COMMENT 'product.id hoặc part.id',
    `qty`         INT          NOT NULL COMMENT 'Số lượng trả',
    `reason`      VARCHAR(300) NULL COMMENT 'Lý do trả hàng',
    `status`      ENUM('pending','sent','done','rejected') NOT NULL DEFAULT 'pending' COMMENT 'Chờ gửi / Đã gửi / NCC đã nhận / Bị từ chối',
    `voucher_id`  INT          NULL COMMENT 'Phiếu kho xuất trả (stock_voucher.id)',
    `created_by`  INT          NOT NULL COMMENT 'Người tạo yêu cầu (employee.id)',
    `created_at`  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Ngày tạo',
    PRIMARY KEY (`id`),
    KEY `idx_sr_supplier` (`supplier_id`, `status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci COMMENT='1Kho CMS – Yêu cầu trả hàng nhà cung cấp';

-- 3.11 Bảo hành: mỗi máy xuất kho sinh 1 dòng; khách quét QR để kích hoạt
CREATE TABLE IF NOT EXISTS `warranty` (
    `id`               INT          NOT NULL AUTO_INCREMENT COMMENT 'Khoá chính',
    `serial`           VARCHAR(50)  NOT NULL COMMENT 'Số serial máy, VD: SP001-A3KX',
    `qr_token`         VARCHAR(64)  NOT NULL COMMENT 'Mã bí mật in trên tem QR để kích hoạt',
    `product_id`       INT          NOT NULL COMMENT 'Máy (product.id)',
    `order_id`         INT          NULL COMMENT 'Đơn xuất máy (dealer_order.id)',
    `dealer_id`        INT          NULL COMMENT 'Đại lý bán (dealer.id)',
    `customer_name`    VARCHAR(255) NULL COMMENT 'Tên khách cuối (điền khi kích hoạt)',
    `customer_phone`   VARCHAR(30)  NULL COMMENT 'SĐT khách cuối',
    `customer_address` VARCHAR(300) NULL COMMENT 'Địa chỉ khách cuối',
    `activated_at`     DATETIME     NULL COMMENT 'Thời điểm kích hoạt',
    `expires_at`       DATE         NULL COMMENT 'Ngày hết hạn bảo hành',
    `claim_count`      INT          NOT NULL DEFAULT 0 COMMENT 'Số lần khiếu nại (cache)',
    `status`           ENUM('unactivated','active','expired','void') NOT NULL DEFAULT 'unactivated' COMMENT 'Chưa kích hoạt / Đang bảo hành / Hết hạn / Huỷ',
    `created_at`       TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Ngày tạo (lúc xuất kho)',
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_w_serial` (`serial`),
    UNIQUE KEY `uq_w_qr` (`qr_token`),
    KEY `idx_w_dealer` (`dealer_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci COMMENT='1Kho CMS – Sổ bảo hành theo serial máy';

CREATE TABLE IF NOT EXISTS `warranty_claim` (
    `id`          INT          NOT NULL AUTO_INCREMENT COMMENT 'Khoá chính',
    `code`        VARCHAR(20)  NOT NULL COMMENT 'Mã khiếu nại, VD: KC001',
    `warranty_id` INT          NOT NULL COMMENT 'Máy được khiếu nại (warranty.id)',
    `issue`       VARCHAR(500) NOT NULL COMMENT 'Mô tả sự cố',
    `images`      TEXT         NULL COMMENT 'Ảnh/video sự cố (JSON mảng URL)',
    `status`      ENUM('pending','processing','resolved','rejected') NOT NULL DEFAULT 'pending' COMMENT 'Chờ xử lý / Đang xử lý / Đã xử lý / Từ chối',
    `assignee_id` INT          NULL COMMENT 'Kỹ thuật viên phụ trách (employee.id)',
    `order_id`    INT          NULL COMMENT 'Đơn bảo hành gửi linh kiện (dealer_order.id)',
    `note`        VARCHAR(500) NULL COMMENT 'Ghi chú xử lý',
    `resolved_at` DATETIME     NULL COMMENT 'Thời điểm xử lý xong',
    `created_at`  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Ngày tạo',
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_wc_code` (`code`),
    KEY `idx_wc_status` (`status`),
    KEY `idx_wc_warranty` (`warranty_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci COMMENT='1Kho CMS – Phiếu khiếu nại bảo hành';

-- 3.12 Hoa hồng (tính khi đơn chuyển sang delivered) và phiếu chi hoa hồng
CREATE TABLE IF NOT EXISTS `commission` (
    `id`          INT          NOT NULL AUTO_INCREMENT COMMENT 'Khoá chính',
    `order_id`    INT          NOT NULL COMMENT 'Đơn hàng (dealer_order.id)',
    `employee_id` INT          NOT NULL COMMENT 'Nhân viên hưởng (employee.id)',
    `role`        ENUM('sale','delivery') NOT NULL COMMENT 'Vai trò hưởng hoa hồng',
    `base_amount` BIGINT       NOT NULL COMMENT 'Doanh thu làm căn cứ tính',
    `rate`        DECIMAL(5,2) NOT NULL COMMENT 'Tỉ lệ % áp dụng (lấy từ config lúc tính)',
    `amount`      BIGINT       NOT NULL COMMENT 'Tiền hoa hồng = base_amount × rate / 100',
    `period`      CHAR(7)      NOT NULL COMMENT 'Kỳ lương YYYY-MM',
    `is_paid`     TINYINT(1)   NOT NULL DEFAULT 0 COMMENT '1: đã chi trả',
    `paid_at`     DATETIME     NULL COMMENT 'Ngày chi trả',
    `created_at`  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Ngày tính',
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_com` (`order_id`, `employee_id`, `role`),
    KEY `idx_com_period` (`period`, `employee_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci COMMENT='1Kho CMS – Hoa hồng Sale / giao hàng';

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci COMMENT='1Kho CMS – Phiếu chi hoa hồng';

-- =====================================================================
-- PHẦN 4. DỮ LIỆU BẮT BUỘC
-- =====================================================================

-- 4.1 Tham số cấu hình (bảng config có UNIQUE `key` → INSERT IGNORE không ghi đè giá trị đã chỉnh)
INSERT IGNORE INTO `config` (`name`, `key`, `description`, `type`, `value`) VALUES
    ('Hoa hồng Sale (%)',         'COMMISSION_RATE_SALE',     'Phần trăm hoa hồng Sale trên doanh thu đơn hoàn thành',         0, '1'),
    ('Hoa hồng giao hàng (%)',    'COMMISSION_RATE_DELIVERY', 'Phần trăm hoa hồng NV giao hàng trên doanh thu đơn hoàn thành', 0, '0.3'),
    ('Thời hạn bảo hành (tháng)', 'WARRANTY_MONTHS',          'Số tháng bảo hành mặc định tính từ ngày kích hoạt',            0, '24');

-- 4.2 RBAC: 7 vai trò của CMS (auth_item.type = 1 là role). Tài khoản employee.is_admin = 1 tự là Admin, không cần gán.
--     Gán vai trò cho nhân viên: trang Tài khoản & Phân quyền trong CMS (ghi vào auth_assignment).
INSERT INTO `auth_item` (`name`, `type`, `description`, `created_at`, `updated_at`)
SELECT r.name, '1', r.description, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()
FROM (
    SELECT 'admin' AS name,  'Admin / CEO' AS description UNION ALL
    SELECT 'sale',           'Kinh doanh' UNION ALL
    SELECT 'kt_banhang',     'KT Bán hàng' UNION ALL
    SELECT 'kt_congno',      'KT Công nợ' UNION ALL
    SELECT 'kt_xuatkho',     'KT Xuất kho' UNION ALL
    SELECT 'assembly',       'Bộ phận lắp ráp' UNION ALL
    SELECT 'delivery',       'Nhân viên giao hàng'
) r
WHERE NOT EXISTS (SELECT 1 FROM `auth_item` a WHERE a.name = r.name);

-- 4.3 Menu header web: từ bản này menu chỉ hiện chuyên mục cấp 1 có show_in_header = 1 (tối đa 8, theo home_position).
--     Trước đây web bỏ qua cờ này nên giá trị cũ không có ý nghĩa. Lần chạy ĐẦU TIÊN: bật 8 chuyên mục cấp 1 đầu tiên
--     (theo id), tắt các chuyên mục cấp 1 còn lại. Lần chạy sau không đụng tới (dòng config đánh dấu đã tồn tại),
--     để giữ lựa chọn Admin chỉnh ở CMS → Chuyên mục sàn → "Hiển thị menu header".
INSERT IGNORE INTO `config` (`name`, `key`, `description`, `type`, `value`) VALUES
    ('Khởi tạo menu header', 'MIGRATION_HEADER_MENU', 'Đánh dấu migration đã khởi tạo cờ show_in_header của chuyên mục (không xoá)', 0, '1');
SET @header_first_run = ROW_COUNT();
UPDATE `category` c
LEFT JOIN (
    SELECT `id` FROM (
        SELECT `id` FROM `category` WHERE `parent_id` = 0 AND `is_delete` = 0 AND `status` = 1 ORDER BY `id` LIMIT 8
    ) first8
) t ON t.`id` = c.`id`
SET c.`show_in_header` = IF(t.`id` IS NULL, 0, 1)
WHERE @header_first_run = 1 AND c.`parent_id` = 0;

-- PHẦN 5. Trang nội dung web (footer). INSERT IGNORE: không ghi đè nội dung đã sửa trong CMS.
INSERT IGNORE INTO `config` (`name`, `key`, `description`, `type`, `value`) VALUES
('Hướng dẫn mua hàng', 'BUY_GUIDE', 'Trang Hướng dẫn mua hàng trên web (HTML)', 1,
'<h3>1. Tìm sản phẩm</h3><p>Chọn chuyên mục trên thanh menu hoặc gõ tên, mã sản phẩm vào ô tìm kiếm (gõ không dấu vẫn tìm được).</p><h3>2. Thêm vào giỏ hàng</h3><p>Tại trang chi tiết, chọn phân loại (nếu có), số lượng rồi bấm <strong>Thêm vào giỏ hàng</strong> hoặc <strong>Mua ngay</strong>.</p><h3>3. Đăng nhập</h3><p>Đăng nhập bằng số điện thoại (nhận mã OTP) hoặc tài khoản Google. Lần đầu đăng nhập hệ thống tự tạo tài khoản.</p><h3>4. Đặt hàng</h3><p>Trong giỏ hàng, tích chọn sản phẩm muốn mua, kiểm tra địa chỉ nhận hàng, chọn voucher (nếu có) và phương thức thanh toán, sau đó bấm <strong>Đặt hàng</strong>.</p><h3>5. Theo dõi đơn</h3><p>Vào <strong>Tài khoản → Đơn mua</strong> để xem trạng thái: Chờ xác nhận, Đã xác nhận, Đang giao, Đã mua. Đơn đang chờ xác nhận có thể huỷ; đơn đã mua có thể yêu cầu trả hàng/hoàn tiền trong 10 ngày.</p>'),
('Hình thức thanh toán', 'PAYMENT_GUIDE', 'Trang Thanh toán trên web (HTML). Thông tin tài khoản ngân hàng lấy từ cấu hình BANK_PAYMENT', 1,
'<h3>1. Thanh toán khi nhận hàng (COD)</h3><p>Quý khách thanh toán tiền mặt cho nhân viên giao hàng khi nhận hàng.</p><h3>2. Chuyển khoản ngân hàng</h3><p>Chuyển khoản theo thông tin tài khoản hiển thị ở bước đặt hàng, nội dung chuyển khoản ghi <strong>mã đơn hàng</strong> và <strong>số điện thoại</strong> đặt hàng. Đơn hàng được xác nhận sau khi 1Kho nhận được thanh toán.</p><h3>3. Voucher</h3><p>Voucher được áp dụng ở bước đặt hàng, số tiền giảm được trừ trực tiếp vào tổng tiền đơn.</p>'),
('Chính sách vận chuyển', 'SHIPPING_POLICY', 'Trang Vận chuyển trên web (HTML)', 1,
'<p>1Kho giao hàng toàn quốc thông qua đối tác vận chuyển và đại lý của từng shop.</p><h3>Phí vận chuyển</h3><p>Phí vận chuyển được tính theo cân nặng sản phẩm và hiển thị rõ ở bước đặt hàng trước khi quý khách xác nhận.</p><h3>Thời gian giao hàng</h3><p>Thời gian giao phụ thuộc khu vực nhận hàng và loại sản phẩm (máy móc cỡ lớn có thể cần lịch giao riêng). Nhân viên sẽ liên hệ trước khi giao.</p><h3>Kiểm tra hàng</h3><p>Quý khách vui lòng kiểm tra tình trạng sản phẩm khi nhận. Nếu hàng thiếu, sai hoặc hư hỏng, hãy gửi yêu cầu trả hàng/hoàn tiền trong mục Tài khoản → Đơn mua.</p>'),
('Điều khoản sử dụng', 'TERMS_OF_USE', 'Trang Điều khoản trên web (HTML). Để trống thì ẩn link ở footer', 1, ''),
('Tuyển dụng', 'RECRUITMENT', 'Trang Tuyển dụng trên web (HTML). Để trống thì ẩn link ở footer', 1, '');

-- =====================================================================
-- KIỂM TRA SAU KHI CHẠY (tuỳ chọn)
--   SELECT COUNT(*) FROM product WHERE code IS NULL OR code = '';                 -- phải = 0
--   SELECT name FROM auth_item WHERE type = 1;                                     -- có đủ 7 vai trò
--   SELECT `key`, value FROM config WHERE `key` IN ('COMMISSION_RATE_SALE','COMMISSION_RATE_DELIVERY','WARRANTY_MONTHS');
--   SELECT id, name FROM category WHERE parent_id = 0 AND show_in_header = 1;      -- menu header
-- =====================================================================
