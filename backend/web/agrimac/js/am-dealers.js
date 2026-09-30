/* AgriMac – Đại lý & Công nợ: danh sách, chi tiết, thêm/sửa đại lý, thu tiền */
(function ($, AM) {
    'use strict';

    var cfg = window.AM_DEALER_PAGE || {};
    var dealers = cfg.dealers || [], provinces = cfg.provinces || [], levels = cfg.levels || {};
    var state = { selected: null, search: '' };

    var canManage = function () { return AM.can('admin', 'sale'); };
    var canCollect = function () { return AM.can('admin', 'kt_congno'); };
    var canOrder = function () { return AM.canOpen('orders') && AM.can('admin', 'sale', 'kt_banhang'); };

    function byId(id) {
        return dealers.filter(function (d) { return d.id === id; })[0] || null;
    }

    function pctOf(d) {
        return d.limit > 0 ? Math.round(d.debt / d.limit * 100) : 0;
    }

    function statusBadge(d) {
        if (d.debt === 0) return AM.badge('Sạch nợ', '#059669', '#d1fae5');
        if (d.debt > d.limit) return AM.badge('Quá hạn mức', '#ef4444', '#fee2e2');
        return AM.badge('Trong hạn', '#2563eb', '#dbeafe');
    }

    function renderToolbar() {
        $('#am-dealer-toolbar').html(canManage() ? '<button class="am-btn" data-action="new-dealer">+ Thêm đại lý</button>' : '');
    }

    function renderRows() {
        $('#am-dealer-rows').html(dealers.map(function (d, i) {
            var over = d.debt > d.limit, lv = levels[d.level] || { color: '#6b7280', bg: '#f1f5f9' }, sel = d.id === state.selected;
            return '<tr class="am-clickable" data-id="' + d.id + '" style="background:' + (sel ? '#fffbeb' : (i % 2 === 0 ? '#fff' : '#fafafa')) + '">' +
                '<td style="color:#9ca3af;font-size:11px">' + d.id + '</td>' +
                '<td><span style="font-weight:700">' + AM.esc(d.name) + '</span></td>' +
                '<td style="font-size:12px">' + AM.esc(d.province) + '</td>' +
                '<td style="font-size:12px">' + AM.esc(d.contact) + '</td>' +
                '<td>' + AM.badge(d.level, lv.color, lv.bg) + '</td>' +
                '<td style="font-size:12px">' + AM.money(d.limit) + '</td>' +
                '<td><span style="font-weight:700;color:' + (over ? '#ef4444' : '#1a2035') + '">' + AM.money(d.debt) + '</span>' +
                (over ? '<span style="font-size:10px;color:#ef4444;margin-left:4px">⚠ ' + pctOf(d) + '%</span>' : '') + '</td>' +
                '<td><span style="font-weight:600;color:#059669">' + AM.money(d.total) + '</span></td>' +
                '<td>' + statusBadge(d) + '</td>' +
                '<td><button class="am-btn am-btn-sm">Chi tiết</button></td></tr>';
        }).join(''));
    }

    function renderWarnings() {
        var over = dealers.filter(function (d) { return d.debt > d.limit; });
        $('#am-dealer-warnings').html(over.length
            ? '<div style="background:#fff5f5;border-radius:10px;padding:12px 16px;border:1.5px solid #fecaca">' +
              '<div style="font-weight:700;color:#ef4444;font-size:13px;margin-bottom:6px">⚠ Cảnh báo công nợ</div>' +
              over.map(function (d) {
                  return '<div style="font-size:12px;color:#374151"><b>' + AM.esc(d.name) + '</b> dư nợ ' + AM.money(d.debt) +
                      ' / hạn mức ' + AM.money(d.limit) + ' (' + pctOf(d) + '%) — cần thu tiền trước khi xuất hàng mới</div>';
              }).join('') + '</div>'
            : '');
    }

    function historyRows(d) {
        var q = state.search.toLowerCase();
        return d.history.filter(function (h) { return h.order.toLowerCase().indexOf(q) >= 0; }).map(function (h) {
            return '<div style="display:flex;justify-content:space-between;padding:7px 0;border-bottom:1px solid #f3f4f6;font-size:12px">' +
                '<div><div style="font-weight:600">' + AM.esc(h.order) + '</div><div style="font-size:11px;color:#9ca3af">' + AM.esc(h.date) + '</div></div>' +
                '<span style="font-weight:700;color:' + (h.payment ? '#2563eb' : '#059669') + '">' + (h.payment ? '−' : '') + AM.money(h.val) + '</span></div>';
        }).join('') || '<div style="font-size:12px;color:#9ca3af;padding:6px 0">Không có giao dịch phù hợp</div>';
    }

    function renderDetail() {
        var d = byId(state.selected), $p = $('#am-dealer-detail');
        if (!d) { $p.addClass('am-hidden').empty(); return; }
        var over = d.debt > d.limit, pct = pctOf(d);
        $p.html(
            '<div class="am-row-between" style="align-items:flex-start;margin-bottom:4px">' +
            '<div style="font-weight:800;font-size:15px">' + AM.esc(d.name) + '</div>' +
            (canManage() ? '<button data-action="edit-dealer" title="Sửa thông tin" style="background:#f1f5f9;border:none;border-radius:6px;padding:4px 8px;cursor:pointer;font-size:11px">✏️ Sửa</button>' : '') +
            '</div><div style="font-size:12px;color:#6b7280;margin-bottom:14px">' + AM.esc(d.province) + ' · ' + AM.esc(d.contact) +
            (d.contactName ? ' · ' + AM.esc(d.contactName) : '') + '</div>' +
            '<div style="background:#f8fafc;border-radius:10px;padding:12px;margin-bottom:14px">' +
            '<div style="font-size:11px;font-weight:700;color:#6b7280;margin-bottom:8px">CÔNG NỢ</div>' +
            '<div style="display:flex;justify-content:space-between;margin-bottom:4px;font-size:12px"><span>Hạn mức tín dụng:</span><span style="font-weight:700">' + AM.money(d.limit) + '</span></div>' +
            '<div style="display:flex;justify-content:space-between;margin-bottom:8px;font-size:12px"><span>Dư nợ hiện tại:</span><span style="font-weight:800;color:' + (over ? '#ef4444' : '#1a2035') + '">' + AM.money(d.debt) + '</span></div>' +
            '<div style="background:#e5e7eb;border-radius:4px;height:7px;overflow:hidden;margin-bottom:4px"><div style="width:' + Math.min(pct, 100) + '%;height:100%;background:' + (over ? '#ef4444' : '#3b82f6') + ';border-radius:4px"></div></div>' +
            '<div style="font-size:10px;color:#9ca3af">' + pct + '% hạn mức đã dùng</div></div>' +
            '<div style="font-size:11px;font-weight:700;color:#6b7280;margin-bottom:8px">LỊCH SỬ MUA HÀNG & THANH TOÁN</div>' +
            '<input id="am-history-search" class="am-input" value="' + AM.esc(state.search) + '" placeholder="🔍 Tìm kiếm đơn hàng..." style="padding:7px 10px;margin-bottom:10px">' +
            '<div id="am-history-list">' + historyRows(d) + '</div>' +
            '<div style="margin-top:12px;display:flex;gap:7px">' +
            (canCollect() ? '<button class="am-btn am-btn-green" data-action="collect"' + (d.debt === 0 ? ' disabled title="Đại lý không còn dư nợ" style="opacity:.45;cursor:not-allowed"' : '') + '>💰 Thu tiền</button>' : '') +
            (canOrder() ? '<button class="am-btn" data-action="order">📋 Tạo đơn mới</button>' : '') +
            '</div>'
        ).removeClass('am-hidden');
    }

    function render() {
        renderToolbar();
        renderRows();
        renderWarnings();
        renderDetail();
    }

    function upsertDealer(row) {
        var cur = byId(row.id);
        if (cur) $.extend(cur, row); else dealers.push(row);
    }

    function openDealerForm(d) {
        AM.form.open({
            title: d ? '✏️ Sửa đại lý ' + d.id : '🏪 Thêm đại lý mới',
            sub: d ? 'Dư nợ và tổng mua được cập nhật tự động từ đơn hàng và phiếu thu' : 'Đại lý mới bắt đầu với dư nợ = 0',
            width: 600,
            submitLabel: d ? '💾 Cập nhật' : '💾 Lưu đại lý',
            fields: [
                { name: 'code', label: 'Mã đại lý', readonly: true },
                { name: 'level', label: 'Cấp đại lý', type: 'select', required: true, options: Object.keys(levels).map(function (k) { return [k, k]; }) },
                { name: 'name', label: 'Tên đại lý', required: true, full: true, placeholder: 'VD: Đại lý Minh Hùng' },
                { name: 'contactName', label: 'Người liên hệ', placeholder: 'VD: Anh Hùng' },
                { name: 'contact', label: 'Số điện thoại', type: 'tel', required: true, placeholder: 'VD: 0901.234.567' },
                { name: 'province', label: 'Tỉnh / Thành phố', type: 'select', required: true, placeholder: '— Chọn tỉnh —', options: provinces.map(function (p) { return [p, p]; }) },
                { name: 'taxCode', label: 'Mã số thuế', placeholder: 'Dùng khi xuất hoá đơn' },
                { name: 'address', label: 'Địa chỉ', full: true, placeholder: 'Số nhà, đường, xã/phường, huyện' },
                { name: 'limit', label: 'Hạn mức công nợ (đ)', type: 'number', min: 0, required: true, hint: 'Duyệt đơn vượt mức này sẽ bị cảnh báo' }
            ],
            values: d ? $.extend({ code: d.id }, d) : { code: AM.nextCode(dealers, 'DL', 3), level: 'Đồng', limit: 500000000 },
            onChange: function (api) {
                var limit = +api.get('limit');
                api.preview(d && limit > 0 && d.debt > limit
                    ? '<div style="font-size:11px;color:#ef4444">⚠ Dư nợ hiện tại ' + AM.money(d.debt) + ' đang lớn hơn hạn mức mới.</div>' : '');
            },
            validate: function (v) {
                var e = {}, name = v.name.toLowerCase();
                if (v.name && dealers.some(function (x) { return x.name.toLowerCase() === name && (!d || x.id !== d.id); })) e.name = 'Tên đại lý đã tồn tại';
                if (v.contact && !AM.isPhone(v.contact)) e.contact = 'Số điện thoại không hợp lệ';
                if (v.taxCode && !/^\d{10}(-\d{3})?$/.test(v.taxCode)) e.taxCode = 'Mã số thuế gồm 10 số (hoặc 10 số-3 số)';
                if (!(+v.limit >= 0)) e.limit = 'Hạn mức không hợp lệ';
                return e;
            },
            onSubmit: function (v) {
                return AM.api('dealer.save', $.extend({}, v, { id: d ? d.id : '' })).done(function (data) {
                    upsertDealer(data.dealer);
                    state.selected = data.dealer.id;
                    render();
                });
            }
        });
    }

    function openCollectForm(d) {
        AM.form.open({
            title: '💰 Thu tiền — ' + d.name,
            sub: 'Phiếu thu ghi vào sổ công nợ và giảm dư nợ đại lý',
            width: 480,
            submitLabel: '💾 Ghi phiếu thu',
            submitColor: '#059669',
            fields: [
                { name: 'info', type: 'html', html: '<div class="am-row-between" style="background:#f8fafc;border-radius:8px;padding:10px 14px">' +
                    '<span style="font-size:12px;color:#6b7280">Dư nợ hiện tại</span><span style="font-weight:900;font-size:15px;color:#ef4444">' + AM.money(d.debt) + '</span></div>' },
                { name: 'amount', label: 'Số tiền thu (đ)', type: 'number', min: 1, max: d.debt, required: true, full: true },
                { name: 'method', label: 'Hình thức', type: 'select', required: true, options: [['Tiền mặt', 'Tiền mặt'], ['Chuyển khoản', 'Chuyển khoản']] },
                { name: 'date', label: 'Ngày thu', type: 'date', required: true },
                { name: 'note', label: 'Ghi chú', type: 'textarea', full: true, placeholder: 'Số chứng từ, nội dung chuyển khoản...' }
            ],
            values: { amount: d.debt, date: AM.isoToday() },
            onChange: function (api) {
                var a = +api.get('amount') || 0;
                api.preview(a > 0 && a <= d.debt
                    ? '<div class="am-row-between" style="background:#f0fdf4;border:1.5px solid #bbf7d0;border-radius:8px;padding:10px 14px">' +
                      '<span style="font-size:12px;color:#6b7280">Dư nợ sau khi thu</span><span style="font-weight:900;font-size:15px;color:#059669">' + AM.money(d.debt - a) + '</span></div>'
                    : '');
            },
            validate: function (v) {
                var e = {};
                if (!(+v.amount > 0)) e.amount = 'Số tiền phải lớn hơn 0';
                else if (+v.amount > d.debt) e.amount = 'Không được thu vượt dư nợ (' + AM.money(d.debt) + ')';
                return e;
            },
            onSubmit: function (v) {
                return AM.api('dealer.collect', $.extend({}, v, { dealerId: d.id })).done(function (data) {
                    upsertDealer(data.dealer);
                    render();
                });
            }
        });
    }

    $(function () {
        render();

        $('#am-dealer-toolbar').on('click', '[data-action="new-dealer"]', function () { openDealerForm(null); });
        $('#am-dealer-rows').on('click', 'tr[data-id]', function () {
            var id = $(this).data('id');
            state.selected = state.selected === id ? null : id;
            state.search = '';
            render();
        });
        $('#am-dealer-detail')
            .on('click', '[data-action="edit-dealer"]', function () { openDealerForm(byId(state.selected)); })
            .on('click', '[data-action="collect"]:not([disabled])', function () { openCollectForm(byId(state.selected)); })
            .on('click', '[data-action="order"]', function () {
                window.location.href = AM.url('orders', { new: 1, dealer: state.selected });
            })
            .on('input', '#am-history-search', function () {
                state.search = this.value;
                $('#am-history-list').html(historyRows(byId(state.selected)));
            });
    });
})(jQuery, window.AM);
