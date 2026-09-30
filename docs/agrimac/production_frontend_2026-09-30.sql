-- =====================================================================
-- 1Kho – THAY ĐỔI DATABASE ĐỢT CẬP NHẬT FRONTEND (30/09/2026)
-- DB: MariaDB >= 10.3 (dùng ADD KEY IF NOT EXISTS). Chạy được nhiều lần.
--
-- Đợt này KHÔNG tạo bảng / cột mới: các chức năng mới dùng bảng có sẵn
--   yêu thích → user_favourite_product, theo dõi shop → user_follow_agent,
--   trả hàng/hoàn tiền → order_refund, xoá thông báo → notify_user.is_delete,
--   xoá tài khoản → user.status = 0 + gỡ phone/gg_id/fb_id, trang nội dung → config.
-- File này gồm: dữ liệu config cho trang nội dung, khởi tạo menu header, index cho truy vấn mới,
-- chuẩn hoá số điện thoại +84 do lỗi đăng nhập web cũ.
--
-- Nếu đã chạy production_migration.sql bản mới nhất thì mục 1 và 2 đã có sẵn (chạy lại không sao).
--
-- TRƯỚC KHI CHẠY: backup
--   mysqldump --single-transaction --routines --default-character-set=utf8mb4 <db> > backup_<db>_$(date +%Y%m%d_%H%M%S).sql
-- CHẠY:
--   mysql --default-character-set=utf8mb4 <db> < production_frontend_2026-09-30.sql
-- =====================================================================

SET NAMES utf8mb4;

-- =====================================================================
-- 1. TRANG NỘI DUNG (footer web: Hướng dẫn mua hàng, Thanh toán, Vận chuyển, Điều khoản, Tuyển dụng)
--    INSERT IGNORE theo UNIQUE `key`: không ghi đè nội dung đã sửa trong CMS → Nội dung & liên hệ.
--    Trang để trống sẽ tự ẩn link ở footer. Các trang Giới thiệu / Bảo mật / Đổi trả / Bảo hành
--    dùng lại config có sẵn (INTRODUCTION, PRIVACY_POLICY, RETURN_POLICY, WARRANTY_POLICY).
-- =====================================================================
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
-- 2. MENU HEADER WEB: chỉ hiện chuyên mục cấp 1 có show_in_header = 1 (tối đa 8, theo home_position).
--    Trước đây web bỏ qua cờ này nên giá trị cũ không có ý nghĩa. Lần chạy ĐẦU TIÊN bật 8 chuyên mục cấp 1
--    đầu tiên (theo id) và tắt các chuyên mục cấp 1 khác; các lần sau không đụng tới (dòng config đánh dấu).
--    Sau đó chỉnh ở CMS → Chuyên mục sàn → "Hiển thị menu header".
-- =====================================================================
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

-- =====================================================================
-- 3. INDEX CHO CÁC TRUY VẤN MỚI (không đổi dữ liệu)
-- =====================================================================
-- Chi tiết đơn / trang "Trả hàng hoàn tiền" tra yêu cầu theo đơn và khách
ALTER TABLE `order_refund`
    ADD KEY IF NOT EXISTS `idx_refund_order` (`order_id`, `user_id`),
    ADD KEY IF NOT EXISTS `idx_refund_user` (`user_id`, `status`);

-- Nút thả tim: kiểm tra / đổi trạng thái theo (khách, sản phẩm); danh sách yêu thích theo khách
ALTER TABLE `user_favourite_product`
    ADD KEY IF NOT EXISTS `idx_fav_user_product` (`user_id`, `product_id`),
    ADD KEY IF NOT EXISTS `idx_fav_product` (`product_id`);

-- Đăng nhập Google / Facebook tra theo Firebase UID; trang Mời bạn bè kiểm tra trùng mã giới thiệu
ALTER TABLE `user`
    ADD KEY IF NOT EXISTS `idx_user_gg` (`gg_id`),
    ADD KEY IF NOT EXISTS `idx_user_fb` (`fb_id`),
    ADD KEY IF NOT EXISTS `idx_user_referral` (`referral_code`);

-- =====================================================================
-- 4. SỐ ĐIỆN THOẠI +84 (lỗi đăng nhập web cũ lưu nguyên dạng Firebase "+84xxxxxxxxx",
--    trong khi app và dữ liệu cũ lưu "0xxxxxxxxx" → khách cũ bị tạo thêm tài khoản).
--    Code mới luôn lưu dạng 0... và khi đăng nhập vẫn tìm cả dạng +84, nên mục này chỉ để dữ liệu đồng nhất.
--
-- 4.1 Đổi +84... → 0... cho tài khoản KHÔNG bị trùng (an toàn, chỉ đổi định dạng).
UPDATE `user` u
LEFT JOIN `user` d ON d.`phone` = CONCAT('0', SUBSTRING(u.`phone`, 4)) AND d.`id` <> u.`id`
SET u.`phone` = CONCAT('0', SUBSTRING(u.`phone`, 4))
WHERE u.`phone` LIKE '+84%' AND d.`id` IS NULL;

-- 4.2 Tài khoản bị trùng (cùng một số, một bản +84 và một bản 0...): KHÔNG tự gộp.
--     Chạy câu dưới để xem; bản "+84" không có đơn hàng thì có thể khoá (status = 0, phone = NULL),
--     có đơn hàng thì cần quyết định gộp thủ công (chuyển order / user_delivery_address sang tài khoản 0...).
-- SELECT u.id AS id_plus84, u.phone, u.fullname, d.id AS id_zero, d.fullname AS name_zero,
--        (SELECT COUNT(*) FROM `order` o WHERE o.user_id = u.id) AS orders_plus84,
--        (SELECT COUNT(*) FROM `order` o WHERE o.user_id = d.id) AS orders_zero
-- FROM `user` u JOIN `user` d ON d.phone = CONCAT('0', SUBSTRING(u.phone, 4))
-- WHERE u.phone LIKE '+84%';

-- =====================================================================
-- KIỂM TRA SAU KHI CHẠY (tuỳ chọn)
--   SELECT `key`, LENGTH(value) FROM config WHERE `key` IN ('BUY_GUIDE','PAYMENT_GUIDE','SHIPPING_POLICY','TERMS_OF_USE','RECRUITMENT');
--   SELECT id, name FROM category WHERE parent_id = 0 AND show_in_header = 1;
--   SELECT COUNT(*) FROM user WHERE phone LIKE '+84%';   -- chỉ còn các tài khoản trùng ở mục 4.2
-- =====================================================================
