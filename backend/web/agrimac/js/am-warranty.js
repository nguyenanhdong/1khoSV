/* AgriMac – Bảo hành: kích hoạt theo serial, phiếu khiếu nại */
(function ($, AM) {
    'use strict';

    var cfg = window.AM_WARRANTY_PAGE || {};
    var warranties = cfg.warranties || [], claims = cfg.claims || [];
    var WARRANTY_MONTHS = 24;
    var CLAIM_STATUS = {
        pending: ['Chờ xử lý', '#6b7280', '#f1f5f9'],
        processing: ['Đang xử lý', '#f59e0b', '#fef3c7'],
        resolved: ['Đã xử lý', '#059669', '#d1fae5'],
        rejected: ['Từ chối', '#ef4444', '#fee2e2']
    };

    function byId(list, id) {
        return list.filter(function (x) { return x.id === id; })[0] || null;
    }

    function dash() { return '<span style="color:#d1d5db">—</span>'; }

    function technicians() {
        var names = AM.staffByRole('assembly');
        claims.forEach(function (c) { if (c.assignee && names.indexOf(c.assignee) < 0) names.push(c.assignee); });
        return names;
    }

    function renderWarranties() {
        $('#am-warranty-rows').html(warranties.map(function (w) {
            var dealer = AM.dealer(w.dealer) || {}, active = w.status === 'active';
            return '<tr><td><span style="font-weight:700;font-size:11px;color:#6b7280">' + AM.esc(w.serial) + '</span>' +
                (w.qr ? ' <a href="' + AM.esc(AM.url('activate', { token: w.qr })) + '" target="_blank" rel="noopener" title="Mở trang khách quét QR" style="font-size:11px">🔗</a>' : '') + '</td>' +
                '<td style="font-weight:600;font-size:12px">' + AM.esc(w.prodName) + '</td>' +
                '<td style="font-size:12px">' + AM.esc(AM.shortDealer(dealer.name)) + '</td>' +
                '<td>' + (w.custName ? '<span style="font-weight:600">' + AM.esc(w.custName) + '</span>' : '<span style="color:#d1d5db;font-size:12px">Chưa kích hoạt</span>') + '</td>' +
                '<td style="font-size:12px;color:#6b7280">' + AM.esc(w.phone || '—') + '</td>' +
                '<td style="font-size:11px">' + (w.activatedAt ? AM.esc(w.activatedAt) : dash()) + '</td>' +
                '<td style="font-size:11px">' + AM.esc(w.expires || '—') + '</td>' +
                '<td style="text-align:center">' + (w.claims > 0 ? '<span style="font-weight:700;color:#ef4444">' + w.claims + '</span>' : '<span style="color:#d1d5db">0</span>') + '</td>' +
                '<td>' + (active ? AM.badge('✓ Đang bảo hành', '#059669', '#d1fae5') : AM.badge('Chưa kích hoạt', '#9ca3af', '#f1f5f9')) + '</td>' +
                '<td>' + (active
                    ? '<button class="am-btn am-btn-sm" data-claim-for="' + w.id + '" style="background:#fee2e2;color:#ef4444">⚠ Khiếu nại</button>'
                    : '<button class="am-btn am-btn-sm" data-activate="' + w.id + '">Kích hoạt</button>') + '</td></tr>';
        }).join(''));
    }

    function renderClaims() {
        $('#am-claim-rows').html(claims.length ? claims.map(function (c) {
            var st = CLAIM_STATUS[c.status] || CLAIM_STATUS.pending, w = byId(warranties, c.wId) || {};
            var canOrder = AM.canOpen('orders') && c.status !== 'resolved' && c.status !== 'rejected';
            return '<tr><td><span style="font-weight:700">' + AM.esc(c.id) + '</span></td>' +
                '<td><div style="font-weight:600;font-size:12px">' + AM.esc(c.cust) + '</div><div style="font-size:11px;color:#9ca3af">' + AM.esc(w.phone || '') + '</div></td>' +
                '<td style="font-size:11px;color:#6b7280">' + AM.esc(c.serial) + '</td>' +
                '<td style="font-size:12px">' + AM.esc(c.prod) + '</td>' +
                '<td style="font-size:12px;max-width:160px">' + AM.esc(c.issue) + '</td>' +
                '<td style="font-size:12px">' + AM.esc(c.created) + '</td>' +
                '<td style="font-size:12px">' + (c.assignee ? AM.esc(c.assignee) : dash()) + '</td>' +
                '<td>' + AM.badge(st[0], st[1], st[2]) + (c.resolved ? '<div style="font-size:10px;color:#9ca3af;margin-top:3px">' + AM.esc(c.resolved) + '</div>' : '') + '</td>' +
                '<td style="font-size:11px;color:#6b7280">' + AM.esc(c.note || '') + (c.orderId ? '<div style="color:#2563eb;margin-top:2px">🔧 ' + AM.esc(c.orderId) + '</div>' : '') + '</td>' +
                '<td style="white-space:nowrap"><button class="am-btn am-btn-sm" data-update-claim="' + c.id + '">Cập nhật</button>' +
                (canOrder ? ' <button class="am-btn am-btn-sm" data-claim-order="' + c.id + '" style="background:#0ea5e9;color:#fff">🔧 Đơn BH</button>' : '') + '</td></tr>';
        }).join('') : '<tr><td colspan="10" class="am-empty" style="padding:24px">Chưa có phiếu khiếu nại</td></tr>');
    }

    function render() {
        renderWarranties();
        renderClaims();
    }

    function applyData(data) {
        AM.replace(warranties, data.warranties);
        AM.replace(claims, data.claims);
        render();
    }

    function showClaimsTab() {
        $('.am-tabs[data-group="warranty"] .am-tab[data-tab="claims"]').trigger('click');
    }

    function addMonths(iso, months) {
        var d = new Date(iso + 'T00:00:00');
        d.setMonth(d.getMonth() + months);
        return AM.pad(d.getDate(), 2) + '/' + AM.pad(d.getMonth() + 1, 2) + '/' + d.getFullYear();
    }

    function openActivateForm(w) {
        AM.form.open({
            title: '🛡 Kích hoạt bảo hành',
            sub: 'Thời hạn bảo hành ' + WARRANTY_MONTHS + ' tháng tính từ ngày kích hoạt',
            width: 520,
            submitLabel: '✓ Kích hoạt',
            submitColor: '#059669',
            fields: [
                { name: 'serial', label: 'Serial', readonly: true },
                { name: 'prodName', label: 'Sản phẩm', readonly: true },
                { name: 'custName', label: 'Tên khách hàng', required: true, full: true, placeholder: 'VD: Nguyễn Văn An' },
                { name: 'phone', label: 'Số điện thoại', type: 'tel', required: true },
                { name: 'date', label: 'Ngày kích hoạt', type: 'date', required: true }
            ],
            values: { serial: w.serial, prodName: w.prodName, date: AM.isoToday() },
            onChange: function (api) {
                var date = api.get('date');
                api.preview(date ? '<div style="font-size:12px;color:#6b7280">Hết hạn bảo hành: <b style="color:#059669">' + addMonths(date, WARRANTY_MONTHS) + '</b></div>' : '');
            },
            validate: function (v) {
                var e = {};
                if (v.phone && !AM.isPhone(v.phone)) e.phone = 'Số điện thoại không hợp lệ';
                if (v.date && v.date > AM.isoToday()) e.date = 'Ngày kích hoạt không được ở tương lai';
                return e;
            },
            onSubmit: function (v) {
                return AM.api('warranty.activate', $.extend({}, v, { id: w.id })).done(applyData);
            }
        });
    }

    function openClaimForm(presetId) {
        var active = warranties.filter(function (w) { return w.status === 'active'; });
        var techs = technicians();
        AM.form.open({
            title: '⚠ Tạo phiếu khiếu nại',
            sub: 'Chỉ máy đã kích hoạt và còn hạn bảo hành mới tạo được khiếu nại',
            width: 580,
            submitLabel: '💾 Tạo phiếu',
            fields: [
                { name: 'status', label: 'Trạng thái', type: 'select', required: true, full: true, options: [['pending', 'Chờ xử lý'], ['processing', 'Đang xử lý']] },
                { name: 'wId', label: 'Máy (serial)', type: 'select', required: true, full: true, placeholder: '— Chọn máy đang bảo hành —',
                    options: active.map(function (w) { return [w.id, w.serial + ' · ' + w.prodName + ' · ' + w.custName]; }) },
                { name: 'info', type: 'html' },
                { name: 'issue', label: 'Mô tả sự cố', type: 'textarea', required: true, full: true, placeholder: 'VD: Rò dầu thủy lực, không nâng được giàn cày' },
                { name: 'assignee', label: 'Kỹ thuật viên phụ trách', type: 'select', placeholder: '— Chưa phân công —', options: techs.map(function (n) { return [n, n]; }) },
                { name: 'note', label: 'Ghi chú xử lý', placeholder: 'VD: Hẹn kiểm tra 17/05' }
            ],
            values: { status: 'pending', wId: presetId || '' },
            onChange: function (api) {
                var w = byId(warranties, api.get('wId')), dealer = w ? AM.dealer(w.dealer) : null;
                api.html('info', w
                    ? '<div style="background:#f8fafc;border-radius:8px;padding:9px 12px;font-size:12px;color:#374151">👤 <b>' + AM.esc(w.custName) + '</b> · ' + AM.esc(w.phone) +
                      ' · 🏪 ' + AM.esc(dealer ? dealer.name : '—') + ' · Hạn BH: <b>' + AM.esc(w.expires) + '</b>' +
                      (w.claims > 0 ? ' · <span style="color:#ef4444">đã khiếu nại ' + w.claims + ' lần</span>' : '') + '</div>'
                    : '');
            },
            validate: function (v) {
                return v.issue && v.issue.length < 10 ? { issue: 'Mô tả sự cố tối thiểu 10 ký tự' } : {};
            },
            onSubmit: function (v) {
                return AM.api('claim.save', v).done(function (data) {
                    applyData(data);
                    showClaimsTab();
                });
            }
        });
    }

    function openUpdateClaim(c) {
        AM.form.open({
            title: '✏️ Cập nhật phiếu ' + c.id,
            sub: c.serial + ' · ' + c.prod + ' · ' + c.cust,
            width: 520,
            submitLabel: '💾 Cập nhật',
            fields: [
                { name: 'issue', label: 'Sự cố', type: 'textarea', readonly: true, full: true, height: 48 },
                { name: 'status', label: 'Trạng thái', type: 'select', required: true, options: $.map(CLAIM_STATUS, function (s, k) { return [[k, s[0]]]; }) },
                { name: 'assignee', label: 'Kỹ thuật viên', type: 'select', placeholder: '— Chưa phân công —', options: technicians().map(function (n) { return [n, n]; }) },
                { name: 'note', label: 'Ghi chú xử lý', type: 'textarea', full: true }
            ],
            values: c,
            validate: function (v) {
                var e = {};
                if (v.status === 'processing' && !v.assignee) e.assignee = 'Cần phân công kỹ thuật viên khi đang xử lý';
                if ((v.status === 'resolved' || v.status === 'rejected') && !v.note) e.note = 'Vui lòng ghi kết quả xử lý / lý do từ chối';
                return e;
            },
            onSubmit: function (v) {
                return AM.api('claim.save', $.extend({}, v, { id: c.id })).done(applyData);
            }
        });
    }

    $(function () {
        render();

        $('[data-action="new-claim"]').on('click', function () { openClaimForm(null); });
        $('#am-warranty-rows')
            .on('click', '[data-activate]', function () { openActivateForm(byId(warranties, $(this).data('activate'))); })
            .on('click', '[data-claim-for]', function () { openClaimForm($(this).data('claim-for')); });
        $('#am-claim-rows')
            .on('click', '[data-update-claim]', function () { openUpdateClaim(byId(claims, $(this).data('update-claim'))); })
            .on('click', '[data-claim-order]', function () {
                var c = byId(claims, $(this).data('claim-order')), w = byId(warranties, c.wId) || {};
                window.location.href = AM.url('orders', { new: 1, type: 'warranty', claim: c.id, dealer: w.dealer || '', product: c.prod });
            });
    });
})(jQuery, window.AM);
