/* AgriMac – Kế toán: hoa hồng theo nhân viên, chi trả */
(function ($, AM) {
    'use strict';

    var rows = window.AM_COMMISSIONS || [];
    var ROLE = { sale: ['👔 Sale', '#7c3aed', '#ede9fe'], delivery: ['🚚 Giao hàng', '#0891b2', '#cffafe'] };
    var canPay = function () { return AM.can('admin', 'kt_congno'); };

    function remaining(r) { return r.amount - r.paid; }

    function render() {
        $('#am-com-rows').html(rows.length ? rows.map(function (r, i) {
            var role = ROLE[r.role], left = remaining(r);
            return '<tr><td><div style="font-weight:700;font-size:12px">' + AM.esc(r.name) + '</div>' +
                (r.payouts.length ? '<div style="font-size:10px;color:#9ca3af;margin-top:2px">' + r.payouts.map(function (p) {
                    return AM.esc(p.date + ' · ' + AM.money(p.amount) + ' · ' + p.method);
                }).join('<br>') + '</div>' : '') + '</td>' +
                '<td>' + AM.badge(role[0], role[1], role[2]) + '</td>' +
                '<td style="font-size:12px" title="' + AM.esc(r.orders.join(', ')) + '">' + r.orders.length + ' đơn</td>' +
                '<td style="font-size:12px">' + AM.money(r.base) + '</td>' +
                '<td><span style="font-weight:700;color:' + role[1] + '">' + AM.money(r.amount) + '</span></td>' +
                '<td style="font-size:12px;color:#059669;font-weight:600">' + (r.paid ? AM.money(r.paid) : '—') + '</td>' +
                '<td>' + (left > 0 ? '<span style="font-weight:800;color:#ef4444">' + AM.money(left) + '</span>' : AM.badge('✓ Đã trả đủ', '#059669', '#d1fae5')) + '</td>' +
                '<td>' + (canPay() && left > 0 ? '<button class="am-btn am-btn-sm am-btn-green" data-pay="' + i + '">💸 Chi trả</button>' : '') + '</td></tr>';
        }).join('') : '<tr><td colspan="8" class="am-empty" style="padding:24px">Chưa có hoa hồng trong kỳ</td></tr>');
        var total = rows.reduce(function (s, r) { return s + r.amount; }, 0), paid = rows.reduce(function (s, r) { return s + r.paid; }, 0);
        $('#am-com-summary').html('Tổng HH <b style="color:#1a2035">' + AM.money(total) + '</b> · đã trả <b style="color:#059669">' + AM.money(paid) +
            '</b> · còn <b style="color:#ef4444">' + AM.money(total - paid) + '</b>');
    }

    function openPayForm(r) {
        var left = remaining(r);
        AM.form.open({
            title: '💸 Chi trả hoa hồng — ' + r.name,
            sub: ROLE[r.role][0] + ' · ' + r.orders.length + ' đơn: ' + r.orders.join(', '),
            width: 480,
            submitLabel: '💾 Ghi phiếu chi',
            submitColor: '#059669',
            fields: [
                { name: 'info', type: 'html', html: '<div class="am-row-between" style="background:#f8fafc;border-radius:8px;padding:10px 14px">' +
                    '<span style="font-size:12px;color:#6b7280">Còn phải trả</span><b style="font-size:15px;color:#ef4444">' + AM.money(left) + '</b></div>' },
                { name: 'amount', label: 'Số tiền chi (đ)', type: 'number', min: 1, max: left, required: true, full: true },
                { name: 'method', label: 'Hình thức', type: 'select', required: true, options: [['Chuyển khoản', 'Chuyển khoản'], ['Tiền mặt', 'Tiền mặt'], ['Cộng vào lương', 'Cộng vào lương']] },
                { name: 'date', label: 'Ngày chi', type: 'date', required: true },
                { name: 'note', label: 'Ghi chú', full: true, placeholder: 'Số chứng từ / kỳ lương' }
            ],
            values: { amount: left, date: AM.isoToday() },
            validate: function (v) {
                if (!(+v.amount > 0)) return { amount: 'Số tiền phải lớn hơn 0' };
                if (+v.amount > left) return { amount: 'Không được chi vượt số còn phải trả (' + AM.money(left) + ')' };
                return {};
            },
            onSubmit: function (v) {
                return AM.api('commission.pay', $.extend({}, v, { employeeId: r.employeeId, role: r.role })).done(function (data) {
                    AM.replace(rows, data.commissions);
                    render();
                });
            }
        });
    }

    $(function () {
        render();
        $('#am-com-rows').on('click', '[data-pay]', function () { openPayForm(rows[$(this).data('pay')]); });
    });
})(jQuery, window.AM);
