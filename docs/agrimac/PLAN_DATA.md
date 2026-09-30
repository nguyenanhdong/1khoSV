# 1Kho CMS — Kế hoạch xử lý dữ liệu theo trang

Cập nhật: 29/09/2026 · Phạm vi: `backend` (Yii2 + jQuery), DB `1kho`

## 1. Hiện trạng

Cả 11 trang đã **đọc từ DB** (`AgrimacRepo`) và **ghi qua `POST agrimac/api`** (`AgrimacService`, 28 thao tác, kiểm quyền RBAC ở server, transaction, validate server).

Các phần **chưa có ở trang nào**:

- **Phân trang và tìm kiếm phía server.** Hiện mỗi trang tải toàn bộ bản ghi. Dữ liệu demo ít nên chưa thấy chậm, nhưng khi có hàng nghìn đơn sẽ chậm.
- **Xoá hoặc ngừng dùng bản ghi** cho sản phẩm, linh kiện, NCC, đại lý, khách CRM. Chưa có thao tác nào.
- **Chọn kỳ báo cáo.** Hiện đang cố định `AgrimacData::PERIOD_LABEL = 'Tháng 5/2025'`, và các số liệu đang tính trên toàn bộ thời gian.

Chú thích mức ưu tiên:
- **P1:** cần để chạy thật.
- **P2:** nên có sớm.
- **P3:** bổ sung sau.

## 2. Tổng quan theo trang

| # | Trang | Đọc | Ghi | Việc còn lại chính | Ưu tiên |
|---|-------|-----|-----|--------------------|---------|
| 1 | Tổng quan | ✓ | — | Số liệu theo kỳ, cảnh báo tồn thật | P1 |
| 2 | CRM / Pipeline | ✓ | ✓ | Sửa/đánh dấu thất bại, lọc theo Sale | P2 |
| 3 | Đơn hàng | ✓ | ✓ | Phân trang + tìm kiếm, sửa đơn chờ duyệt, lưu nháp BOM | P1 |
| 4 | Bộ phận lắp ráp | ✓ | ✓ | Cảnh báo thiếu linh kiện trước khi lắp | P2 |
| 5 | Sản phẩm | ✓ | ✓ | Ngừng bán sản phẩm, sửa/ẩn danh mục | P2 |
| 6 | Quản lý kho | ✓ | ✓ | Phân trang lịch sử, phiếu nhiều dòng, kiểm kê | P1 |
| 7 | Đại lý & Công nợ | ✓ | ✓ | Sổ công nợ chi tiết, ngừng hợp tác, đối soát | P1 |
| 8 | Bảo hành | ✓ | ✓ | Tự chuyển "hết hạn", in tem QR, phân trang | P2 |
| 9 | Nhà cung cấp | ✓ | ✓ | Ngừng hợp tác, lịch sử nhập theo NCC | P3 |
| 10 | Kế toán & HH | ✓ | ✓ | Lọc theo kỳ, tỉ lệ HH lấy từ `config`, xuất Excel theo kỳ | P1 |
| 11 | Tài khoản | ✓ | ✓ | Đặt lại mật khẩu, ghi `last_login` | P2 |

## 3. Chi tiết từng trang

### 3.1 Tổng quan (`agrimac/dashboard`)
**Đã có:** các thẻ doanh thu, đơn đang xử lý, pipeline, khiếu nại, công nợ vượt hạn mức, tồn kho nhanh, đều đọc từ DB.

**Cần làm:**
- [ ] **P1** Bộ lọc kỳ báo cáo trên topbar (tháng/quý), thay hằng `PERIOD_LABEL`. Doanh thu tính theo `dealer_order.delivered_at` trong kỳ.
- [ ] **P1** Tồn kho nhanh cảnh báo theo `min_stock` của từng sản phẩm, thay cho ngưỡng cố định ≤ 3.
- [ ] **P2** Thêm cảnh báo linh kiện dưới `part.min_stock`.
- [ ] **P3** Biểu đồ doanh thu và số đơn theo tháng.

Bảng: `dealer_order`, `dealer_order_item`, `crm_lead`, `warranty_claim`, `dealer`, `product`, `part`

### 3.2 CRM / Pipeline (`agrimac/crm`)
**Đã có:** thêm khách (`lead.save`), đổi giai đoạn (`lead.stage`), ghi chú (`lead.note`), chuyển sang tạo đơn (tự đổi giai đoạn thành "Chốt đơn").

**Cần làm:**
- [ ] **P2** Sửa thông tin khách (tên, SĐT, sản phẩm, giá trị): mở rộng `lead.save` nhận `id`.
- [ ] **P2** Đánh dấu "Thất bại" kèm lý do (giai đoạn `lost` đã có trong DB nhưng chưa có trên giao diện), và xem lại khách đã thất bại.
- [ ] **P2** Lọc theo Sale. Vai trò Sale mặc định chỉ thấy khách của mình.
- [ ] **P3** Kéo-thả thẻ giữa các cột giai đoạn.

Bảng: `crm_lead`, `crm_lead_note`

### 3.3 Đơn hàng (`agrimac/orders`)
**Đã có:** tạo → duyệt + số HĐ → chọn BOM → gửi lắp ráp → xuất kho + serial → giao xong (ghi công nợ và hoa hồng), huỷ đơn. Có ghi lịch sử `dealer_order_log`.

**Cần làm:**
- [ ] **P1** Phân trang và tìm kiếm phía server theo mã đơn, đại lý, sản phẩm, Sale, khoảng ngày. Bộ lọc trạng thái hiện chỉ lọc trên client.
- [ ] **P1** Sửa đơn khi còn "Chờ duyệt": đổi số lượng, giá, ghi chú.
- [ ] **P2** Lưu nháp BOM khi Sale đang chọn. Hiện BOM chỉ lưu lúc bấm "Gửi lắp ráp", tải lại trang là mất phần đang chọn dở.
- [ ] **P2** Hiển thị lịch sử xử lý (`dealer_order_log`: ai làm, lúc nào) trong panel chi tiết.
- [ ] **P3** Đơn nhiều dòng sản phẩm: DB đã hỗ trợ (`dealer_order_item`), giao diện hiện mới cho 1 sản phẩm/đơn.
- [ ] **P3** In phiếu xuất kho và phiếu giao hàng.

Bảng: `dealer_order*`, `stock_voucher*`, `warranty`, `dealer_ledger`, `commission`

### 3.4 Bộ phận lắp ráp (`agrimac/assembly`)
**Đã có:** checklist nhận linh kiện (`order.pick`), ghi chú (`order.assemblyNote`), hoàn tất lắp ráp (`order.assembled`: trừ linh kiện, nhập máy vào kho).

**Cần làm:**
- [ ] **P2** Kiểm tra đủ tồn linh kiện cho cả đơn ngay khi Sale gửi lắp ráp. Hiện đến lúc hoàn tất mới báo thiếu.
- [ ] **P3** Ghi người nhận và thời điểm nhận từng linh kiện (DB đã có `picked_by`, `picked_at`, chưa hiển thị).

Bảng: `dealer_order_part`, `part`, `stock_voucher*`

### 3.5 Sản phẩm (`agrimac/products`)
**Đã có:** thêm/sửa sản phẩm kèm ảnh (`product.save`, `upload-image`), thêm danh mục, BOM mặc định (`bom.save`).

**Cần làm:**
- [ ] **P2** Ngừng bán hoặc ẩn sản phẩm (`is_delete = 1`). Không cho xoá khi đã phát sinh đơn hoặc phiếu kho.
- [ ] **P2** Sửa, đổi tên, ẩn danh mục.
- [ ] **P3** Tìm kiếm sản phẩm theo tên/mã.
- [ ] **P3** Dọn ảnh mồ côi: ảnh đã upload nhưng form bị huỷ.

Bảng: toàn bộ `product`, `category` (cây cha/con, đã bỏ cờ `is_agrimac` ở `schema_v3.sql`), `product_bom`. Sản phẩm đã phân trang + tìm kiếm server-side qua `agrimac/lookup`.

### 3.6 Quản lý kho (`agrimac/inventory`)
**Đã có:** tồn máy, tồn linh kiện, lịch sử phiếu, phiếu nhập (`stock.import`), phiếu xuất lẻ (`stock.export`), thêm/sửa linh kiện, tạo nhanh sản phẩm/linh kiện/NCC.

**Cần làm:**
- [ ] **P1** Phân trang và lọc lịch sử phiếu theo khoảng ngày, loại phiếu, hàng hoá.
- [ ] **P1** Phiếu nhập nhiều dòng trên một phiếu: DB đã hỗ trợ `stock_voucher_item`, giao diện đang 1 dòng/phiếu.
- [ ] **P2** Xem chi tiết một phiếu (các dòng, người lập, đơn liên quan).
- [ ] **P2** Kiểm kê: nhập số đếm thực tế, hệ thống tự tạo phiếu điều chỉnh chênh lệch.
- [ ] **P3** Thẻ kho: lịch sử nhập/xuất và tồn theo từng mặt hàng.

Bảng: `stock_voucher`, `stock_voucher_item`, `product`, `part`, `part_category`

### 3.7 Đại lý & Công nợ (`agrimac/dealers`)
**Đã có:** thêm/sửa đại lý (`dealer.save`), thu tiền (`dealer.collect` → `dealer_ledger`), lịch sử mua và thanh toán.

**Cần làm:**
- [ ] **P1** Sổ công nợ chi tiết (`dealer_ledger`): phát sinh nợ, thu tiền, điều chỉnh, số dư sau mỗi giao dịch, lọc theo kỳ.
- [ ] **P1** Đối soát: kiểm tra `dealer.current_debt` khớp với tổng `dealer_ledger`. Nên có lệnh console báo lệch.
- [ ] **P2** Ngừng hợp tác đại lý (`status = 0`): ẩn khỏi form tạo đơn, vẫn giữ lịch sử.
- [ ] **P2** Phiếu điều chỉnh công nợ (type `adjust`), chỉ KT công nợ và Admin được làm.
- [ ] **P3** Xuất Excel sổ công nợ theo đại lý.

Bảng: `dealer`, `dealer_ledger`, `province`

### 3.8 Bảo hành (`agrimac/warranty` + trang public `agrimac/activate`)
**Đã có:** danh sách serial, kích hoạt qua hotline (`warranty.activate`), khách tự kích hoạt qua QR, tạo và cập nhật phiếu khiếu nại (`claim.save`), tạo đơn bảo hành từ khiếu nại.

**Cần làm:**
- [ ] **P2** Tự chuyển bảo hành quá `expires_at` sang `expired`. Làm bằng lệnh console chạy hằng ngày, hoặc tính trạng thái ngay khi đọc.
- [ ] **P2** In tem QR cho serial sau khi xuất kho: link `activate?token=…`, cần thư viện tạo mã QR.
- [ ] **P2** Phân trang và tìm kiếm theo serial, SĐT khách.
- [ ] **P3** Đính kèm ảnh sự cố cho khiếu nại (cột `warranty_claim.images` đã có).

Bảng: `warranty`, `warranty_claim`

### 3.9 Nhà cung cấp (`agrimac/suppliers`)
**Đã có:** thêm/sửa NCC (`supplier.save`), yêu cầu trả hàng (`supplier.return`), chuyển trạng thái (`supplier.returnNext`, khi "Đã gửi" thì tự xuất kho).

**Cần làm:**
- [ ] **P3** Ngừng hợp tác NCC (`status = 0`).
- [ ] **P3** Lịch sử nhập hàng và tổng giá trị theo từng NCC.
- [ ] **P3** Trạng thái "Bị từ chối" cho yêu cầu trả hàng. Khi bị từ chối cần nhập lại kho số hàng đã xuất trả.

Bảng: `supplier`, `supplier_return`, `stock_voucher*`

### 3.10 Kế toán & Hoa hồng (`agrimac/accounting`)
**Đã có:** sổ thu chi các đơn hoàn thành, xuất CSV, tổng hợp hoa hồng theo nhân viên, chi trả (`commission.pay` → `commission_payout`).

**Cần làm:**
- [ ] **P1** Lọc theo kỳ (tháng) cho sổ thu chi, hoa hồng và file CSV. Cột `commission.period` đã có.
- [ ] **P1** Tỉ lệ hoa hồng đọc từ bảng `config` (`COMMISSION_RATE_SALE`, `COMMISSION_RATE_DELIVERY`), thay cho hằng `COMMISSION_RATES`.
- [ ] **P2** Thêm khoản thu tiền đại lý (`dealer_ledger` loại credit) vào sổ thu chi.
- [ ] **P3** Xuất file `.xlsx` thật thay cho CSV.

Bảng: `dealer_order`, `commission`, `commission_payout`, `dealer_ledger`, `config`

### 3.11 Tài khoản & Phân quyền (`agrimac/users`)
**Đã có:** tạo tài khoản kèm vai trò (`user.create`), đổi vai trò và khoá/mở (`user.permission`), dùng RBAC thật (`auth_assignment`).

**Cần làm:**
- [ ] **P2** Ghi `employee.last_login` khi đăng nhập (sửa `SiteController::actionLogin`).
- [ ] **P2** Đặt lại mật khẩu cho nhân viên.
- [ ] **P3** Gán vai trò AgriMac cho tài khoản 1kho có sẵn (hiện chỉ tạo mới được).

Bảng: `employee`, `auth_assignment`, `auth_item`

## 4. Việc chung cho nhiều trang

- [ ] **P1** (Đã xong cho sản phẩm: `R::productList` + `agrimac/lookup` + `AM.pagerHtml`; còn đơn hàng, giao dịch kho, bảo hành...) Hàm phân trang chung trong `AgrimacRepo` (`limit`/`offset` + tổng số dòng) và một endpoint đọc JSON, ví dụ `agrimac/list?type=orders&page=…&q=…`. JS dùng chung một thanh phân trang.
- [ ] **P1** Test tự động cho `AgrimacService` (Codeception đã có trong dự án), ưu tiên luồng đơn hàng, tồn kho và công nợ.
- [ ] **P2** Ghi nhật ký thao tác (ai sửa gì, lúc nào) cho sản phẩm, đại lý, công nợ.
- [ ] **P2** Giao diện responsive cho tablet ở xưởng lắp ráp và kho.
- [ ] **P3** Thông báo cho vai trò kế tiếp khi đơn chuyển trạng thái (bảng `notify` đã có sẵn).

## 5. Thứ tự đề xuất

1. **Đợt 1 (P1, chạy thật được):**
   - Phân trang và tìm kiếm chung.
   - Kỳ báo cáo cho Tổng quan và Kế toán; tỉ lệ hoa hồng lấy từ `config`.
   - Sổ công nợ chi tiết và đối soát.
   - Sửa đơn chờ duyệt.
   - Phiếu kho nhiều dòng.
   - Test cho Service.
2. **Đợt 2 (P2):**
   - Ngừng dùng bản ghi.
   - Lưu nháp BOM; hiển thị lịch sử đơn.
   - Kiểm kê.
   - Tự chuyển bảo hành hết hạn; in tem QR.
   - `last_login`, đặt lại mật khẩu.
   - CRM: sửa khách và đánh dấu thất bại.
3. **Đợt 3 (P3):** đơn nhiều sản phẩm, in chứng từ, biểu đồ, xuất `.xlsx`, thông báo.
