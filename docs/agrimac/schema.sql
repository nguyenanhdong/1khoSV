-- =====================================================================
-- AgriMac CMS – thay đổi CSDL cho DB `1kho` (MariaDB 10.4)
-- Chạy được nhiều lần (IF NOT EXISTS / WHERE NOT EXISTS).
-- Tiền VNĐ dùng BIGINT (không phần lẻ). FLOAT chỉ chính xác ~7 chữ số,
-- không đủ cho giá máy cày hàng trăm triệu – tỷ đồng.
-- Chạy với: mysql --default-character-set=utf8mb4 1kho < schema.sql
-- =====================================================================

SET NAMES utf8mb4;

-- =====================================================================
-- A. ĐỔI CÁC CỘT TIỀN FLOAT → BIGINT (giữ nguyên NULL/DEFAULT/COMMENT cũ)
--    Đã kiểm tra: không có giá trị nào có phần lẻ trước khi đổi.
--    Giữ FLOAT: cân nặng/kích thước, điểm đánh giá, xu/điểm ví,
--    voucher.price (có thể là %), order.voucher_point_refundable (xu).
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
-- B. BỔ SUNG CỘT CHO BẢNG CÓ SẴN
-- =====================================================================

-- B1. employee: nhân viên dùng CMS/App (vai trò gán qua auth_assignment)
ALTER TABLE `employee`
    ADD COLUMN IF NOT EXISTS `last_login`  DATETIME   NULL COMMENT 'Thời điểm đăng nhập gần nhất' AFTER `last_update`,
    ADD COLUMN IF NOT EXISTS `can_use_app` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1: được dùng app di động (Sale, giao hàng) / 0: chỉ dùng CMS' AFTER `last_login`;

-- B2. product: máy cày thành phẩm
ALTER TABLE `product`
    ADD COLUMN IF NOT EXISTS `code`        VARCHAR(30)  NULL COMMENT 'Mã sản phẩm nội bộ, VD: SP001' AFTER `id`,
    ADD COLUMN IF NOT EXISTS `supplier_id` INT          NULL COMMENT 'Nhà cung cấp máy (supplier.id), NULL nếu tự lắp ráp' AFTER `category_id`,
    ADD COLUMN IF NOT EXISTS `cost_price`  BIGINT       NOT NULL DEFAULT 0 COMMENT 'Giá vốn / 1 máy (nhập nguyên chiếc hoặc tổng BOM)' AFTER `price_discount`,
    ADD COLUMN IF NOT EXISTS `source_type` ENUM('import','assembly','both') NOT NULL DEFAULT 'import' COMMENT 'Nguồn hàng: import = nhập nguyên chiếc, assembly = tự lắp từ linh kiện, both = cả hai' AFTER `cost_price`,
    ADD COLUMN IF NOT EXISTS `horsepower`  SMALLINT     NULL COMMENT 'Công suất (HP)' AFTER `source_type`,
    ADD COLUMN IF NOT EXISTS `drive_type`  VARCHAR(10)  NULL COMMENT 'Hệ dẫn động: 2WD / 4WD' AFTER `horsepower`,
    ADD COLUMN IF NOT EXISTS `specs`       VARCHAR(255) NULL COMMENT 'Mô tả thông số ngắn, VD: 34HP · 4WD · 1450kg' AFTER `drive_type`,
    ADD COLUMN IF NOT EXISTS `min_stock`   INT          NOT NULL DEFAULT 3 COMMENT 'Ngưỡng cảnh báo sắp hết hàng' AFTER `quantity_in_stock`,
    ADD UNIQUE KEY IF NOT EXISTS `uq_product_code` (`code`),
    ADD KEY IF NOT EXISTS `idx_product_supplier` (`supplier_id`);

-- B3. config: tham số AgriMac (type 0 = string/số)
INSERT IGNORE INTO `config` (`name`, `key`, `description`, `type`, `value`) VALUES
    ('Hoa hồng Sale (%)',         'COMMISSION_RATE_SALE',     'Phần trăm hoa hồng Sale trên doanh thu đơn hoàn thành',       0, '1'),
    ('Hoa hồng giao hàng (%)',    'COMMISSION_RATE_DELIVERY', 'Phần trăm hoa hồng NV giao hàng trên doanh thu đơn hoàn thành', 0, '0.3'),
    ('Thời hạn bảo hành (tháng)', 'WARRANTY_MONTHS',          'Số tháng bảo hành mặc định tính từ ngày kích hoạt',          0, '24');

-- B4. RBAC: 7 vai trò AgriMac (auth_item.type = 1 là role; bảng không có unique theo name)
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

-- =====================================================================
-- C. BẢNG MỚI
-- =====================================================================

-- C1. Nhà cung cấp (máy nguyên chiếc và linh kiện)
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci COMMENT='AgriMac – Nhà cung cấp';

-- C2. Danh mục linh kiện
CREATE TABLE IF NOT EXISTS `part_category` (
    `id`         INT          NOT NULL AUTO_INCREMENT COMMENT 'Khoá chính',
    `name`       VARCHAR(100) NOT NULL COMMENT 'Tên nhóm: Động cơ, Hộp số, Thủy lực, Thân máy, Lốp & Bánh, Điện, Lọc, Làm mát...',
    `sort_order` INT          NOT NULL DEFAULT 0 COMMENT 'Thứ tự hiển thị',
    `status`     TINYINT(1)   NOT NULL DEFAULT 1 COMMENT '1: hiển thị / 0: ẩn',
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci COMMENT='AgriMac – Danh mục linh kiện';

-- C3. Linh kiện (tồn kho riêng, nhập qua stock_voucher item_type = part)
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci COMMENT='AgriMac – Linh kiện lắp ráp';

-- C4. BOM mặc định theo sản phẩm
CREATE TABLE IF NOT EXISTS `product_bom` (
    `id`         INT NOT NULL AUTO_INCREMENT COMMENT 'Khoá chính',
    `product_id` INT NOT NULL COMMENT 'Máy thành phẩm (product.id)',
    `part_id`    INT NOT NULL COMMENT 'Linh kiện (part.id)',
    `qty`        INT NOT NULL DEFAULT 1 COMMENT 'Số lượng linh kiện cho 1 máy',
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_bom` (`product_id`, `part_id`),
    KEY `idx_bom_part` (`part_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci COMMENT='AgriMac – Định mức linh kiện mặc định của sản phẩm';

-- C5. Đại lý (bên mua hàng của công ty; khác bảng agent là người bán trên sàn)
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci COMMENT='AgriMac – Đại lý phân phối';

-- C6. Sổ công nợ đại lý
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci COMMENT='AgriMac – Sổ công nợ đại lý';

-- C7. CRM – khách tiềm năng
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci COMMENT='AgriMac – CRM khách tiềm năng';

CREATE TABLE IF NOT EXISTS `crm_lead_note` (
    `id`         INT       NOT NULL AUTO_INCREMENT COMMENT 'Khoá chính',
    `lead_id`    INT       NOT NULL COMMENT 'Khách tiềm năng (crm_lead.id)',
    `content`    TEXT      NOT NULL COMMENT 'Nội dung trao đổi',
    `created_by` INT       NOT NULL COMMENT 'Người ghi (employee.id)',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Thời điểm ghi',
    PRIMARY KEY (`id`),
    KEY `idx_note_lead` (`lead_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci COMMENT='AgriMac – Lịch sử trao đổi với khách tiềm năng';

-- C8. Đơn hàng đại lý (tách khỏi `order` B2C mà app mobile đang dùng)
CREATE TABLE IF NOT EXISTS `dealer_order` (
    `id`            INT          NOT NULL AUTO_INCREMENT COMMENT 'Khoá chính',
    `code`          VARCHAR(20)  NOT NULL COMMENT 'Mã đơn, VD: DH2025001',
    `type`          ENUM('new','warranty') NOT NULL DEFAULT 'new' COMMENT 'new = đơn bán mới, warranty = đơn bảo hành (gửi linh kiện thay thế)',
    `dealer_id`     INT          NOT NULL COMMENT 'Đại lý đặt hàng (dealer.id)',
    `lead_id`       INT          NULL COMMENT 'Khách tiềm năng nguồn (crm_lead.id)',
    `claim_id`      INT          NULL COMMENT 'Phiếu khiếu nại gốc khi type = warranty (warranty_claim.id)',
    `status`        ENUM('pending','confirmed','assembling','assembled','delivering','delivered','cancelled') NOT NULL DEFAULT 'pending' COMMENT 'Chờ duyệt / Đã duyệt / Đang lắp ráp / Lắp ráp xong / Đang giao / Hoàn thành / Huỷ',
    `total_amount`  BIGINT       NOT NULL DEFAULT 0 COMMENT 'Doanh thu (tổng dealer_order_item.line_total)',
    `total_cost`    BIGINT       NOT NULL DEFAULT 0 COMMENT 'Giá vốn linh kiện (chốt khi gửi lắp ráp)',
    `sale_id`       INT          NOT NULL COMMENT 'Sale phụ trách (employee.id)',
    `approved_by`   INT          NULL COMMENT 'KT bán hàng duyệt đơn (employee.id)',
    `assembler_id`  INT          NULL COMMENT 'Người lắp ráp (employee.id)',
    `exporter_id`   INT          NULL COMMENT 'KT xuất kho (employee.id)',
    `delivery_id`   INT          NULL COMMENT 'Nhân viên giao hàng (employee.id)',
    `invoice_no`    VARCHAR(50)  NULL COMMENT 'Số hoá đơn',
    `assembly_note` TEXT         NULL COMMENT 'Ghi chú lắp ráp',
    `note`          VARCHAR(500) NULL COMMENT 'Ghi chú đơn hàng',
    `cancel_reason` VARCHAR(300) NULL COMMENT 'Lý do huỷ',
    `ordered_at`    DATETIME     NOT NULL COMMENT 'Ngày đặt hàng',
    `delivered_at`  DATETIME     NULL COMMENT 'Ngày giao xong',
    `created_at`    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Ngày tạo bản ghi',
    `updated_at`    DATETIME     NULL COMMENT 'Ngày cập nhật gần nhất',
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_dorder_code` (`code`),
    KEY `idx_dorder_status` (`status`),
    KEY `idx_dorder_dealer` (`dealer_id`),
    KEY `idx_dorder_sale` (`sale_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci COMMENT='AgriMac – Đơn hàng đại lý';

CREATE TABLE IF NOT EXISTS `dealer_order_item` (
    `id`         INT    NOT NULL AUTO_INCREMENT COMMENT 'Khoá chính',
    `order_id`   INT    NOT NULL COMMENT 'Đơn hàng (dealer_order.id)',
    `product_id` INT    NOT NULL COMMENT 'Máy đặt mua (product.id)',
    `qty`        INT    NOT NULL COMMENT 'Số lượng máy',
    `unit_price` BIGINT NOT NULL DEFAULT 0 COMMENT 'Đơn giá bán / 1 máy',
    `line_total` BIGINT NOT NULL DEFAULT 0 COMMENT 'Thành tiền = qty × unit_price',
    PRIMARY KEY (`id`),
    KEY `idx_doi_order` (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci COMMENT='AgriMac – Dòng sản phẩm của đơn hàng đại lý';

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci COMMENT='AgriMac – BOM thực tế của đơn + checklist nhận linh kiện';

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci COMMENT='AgriMac – Lịch sử xử lý đơn hàng';

-- C9. Phiếu kho: nhập máy / nhập linh kiện / xuất linh kiện cho lắp ráp / xuất máy giao đại lý
CREATE TABLE IF NOT EXISTS `stock_voucher` (
    `id`           INT          NOT NULL AUTO_INCREMENT COMMENT 'Khoá chính',
    `code`         VARCHAR(20)  NOT NULL COMMENT 'Mã phiếu: NK001 (nhập) / XK001 (xuất)',
    `type`         ENUM('import','export','adjust','return') NOT NULL COMMENT 'import = nhập, export = xuất, adjust = kiểm kê điều chỉnh, return = trả NCC',
    `reason`       ENUM('purchase','assembly_issue','assembly_finish','sale','warranty','other') NOT NULL DEFAULT 'purchase' COMMENT 'purchase = mua từ NCC, assembly_issue = xuất LK cho lắp ráp, assembly_finish = nhập máy lắp xong, sale = xuất giao đại lý, warranty = xuất LK bảo hành',
    `supplier_id`  INT          NULL COMMENT 'Nhà cung cấp (phiếu nhập mua / trả hàng)',
    `order_id`     INT          NULL COMMENT 'Đơn hàng liên quan (dealer_order.id)',
    `total_amount` BIGINT       NOT NULL DEFAULT 0 COMMENT 'Tổng giá trị phiếu',
    `note`         VARCHAR(500) NULL COMMENT 'Ghi chú lô hàng',
    `created_by`   INT          NOT NULL COMMENT 'Người lập phiếu (employee.id)',
    `created_at`   TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Ngày lập phiếu',
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_sv_code` (`code`),
    KEY `idx_sv_type_date` (`type`, `created_at`),
    KEY `idx_sv_order` (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci COMMENT='AgriMac – Phiếu nhập/xuất kho';

CREATE TABLE IF NOT EXISTS `stock_voucher_item` (
    `id`         INT    NOT NULL AUTO_INCREMENT COMMENT 'Khoá chính',
    `voucher_id` INT    NOT NULL COMMENT 'Phiếu kho (stock_voucher.id)',
    `item_type`  ENUM('product','part') NOT NULL COMMENT 'product = máy thành phẩm, part = linh kiện',
    `item_id`    INT    NOT NULL COMMENT 'product.id hoặc part.id theo item_type',
    `qty`        INT    NOT NULL COMMENT 'Số lượng (luôn dương; chiều theo stock_voucher.type)',
    `unit_price` BIGINT NOT NULL DEFAULT 0 COMMENT 'Đơn giá (giá vốn khi nhập / giá bán khi xuất giao)',
    `stock_after` INT   NOT NULL DEFAULT 0 COMMENT 'Tồn kho sau khi ghi phiếu (đối soát)',
    PRIMARY KEY (`id`),
    KEY `idx_svi_voucher` (`voucher_id`),
    KEY `idx_svi_item` (`item_type`, `item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci COMMENT='AgriMac – Dòng hàng của phiếu kho';

-- C10. Trả hàng cho NCC
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci COMMENT='AgriMac – Yêu cầu trả hàng nhà cung cấp';

-- C11. Bảo hành: mỗi máy xuất kho sinh 1 dòng; khách quét QR để kích hoạt
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci COMMENT='AgriMac – Sổ bảo hành theo serial máy';

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci COMMENT='AgriMac – Phiếu khiếu nại bảo hành';

-- C12. Hoa hồng (tính khi đơn chuyển sang delivered)
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci COMMENT='AgriMac – Hoa hồng Sale / giao hàng';
