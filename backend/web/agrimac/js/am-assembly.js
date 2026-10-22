/* 1Kho CMS – Bộ phận lắp ráp: nhận linh kiện → bắt đầu → xong (ghi giờ) → kế toán nhập kho */
(function ($, AM) {
    'use strict';

    var orders = window.AM_ASM_ORDERS || [];
    var FLOW = (AM.data.orderFlows || {}).assembly || {};
    var STATUS = AM.data.orderStatus || {};
    var STEPS = ['exported', 'received', 'assembling', 'assembled', 'delivered'];
    var state = { selected: null, filter: 'active', busy: false };

    function findOrder(id) {
        return orders.filter(function (o) { return o.id === id; })[0] || null;
    }

    function applyOrder(data) {
        var cur = findOrder(data.order.id);
        if (!cur) return;
        Object.keys(cur).forEach(function (k) { delete cur[k]; });
        $.extend(cur, data.order);
    }

    function call(op, o) {
        if (state.busy) return;
        state.busy = true;
        AM.api(op, { id: o.id }).done(function (data) {
            if (data.order) applyOrder(data);
            render();
        }).always(function () { state.busy = false; });
    }

    function elapsed(o) {
        if (o.status === 'assembling' && o.startedTs) return Math.max(0, Math.round((Date.now() / 1000 - o.startedTs) / 60));
        return o.assemblyMinutes;
    }

    function badge(o) {
        var s = STATUS[o.status] || {};
        return AM.badge(FLOW[o.status] || o.status, s.color || '#6b7280', s.bg || '#f1f5f9');
    }

    function filtered() {
        if (state.filter === 'active') return orders.filter(function (o) { return o.status !== 'delivered'; });
        if (state.filter === 'all') return orders;
        return orders.filter(function (o) { return o.status === state.filter; });
    }

    function renderSummary() {
        var html = '<div style="font-weight:800;font-size:15px;margin-bottom:8px">🔩 Xưởng lắp ráp</div><div style="display:grid;grid-template-columns:1fr 1fr;gap:8px">';
        [['exported', 'Chờ nhận LK'], ['received', 'Chờ bắt đầu'], ['assembling', 'Đang lắp'], ['assembled', 'Chờ nhập kho']].forEach(function (s) {
            var n = orders.filter(function (o) { return o.status === s[0]; }).length, c = (STATUS[s[0]] || {}).color || '#fff';
            html += '<div style="background:rgba(255,255,255,0.08);border-radius:8px;padding:8px 10px;border-left:3px solid ' + c + '">' +
                '<div style="font-size:22px;font-weight:900;color:' + c + '">' + n + '</div><div style="font-size:10px;color:rgba(255,255,255,0.55)">' + s[1] + '</div></div>';
        });
        $('#am-asm-summary').html(html + '</div>');
        var chips = [['active', 'Đang xử lý'], ['exported', 'Chờ nhận'], ['assembling', 'Đang lắp'], ['assembled', 'Chờ nhập kho'], ['delivered', 'Đã nhập kho']];
        $('#am-asm-filters').html(chips.map(function (c) {
            return '<button class="am-asm-filter ' + (state.filter === c[0] ? 'active' : '') + '" data-filter="' + c[0] + '">' + c[1] + '</button>';
        }).join(''));
    }

    function renderList() {
        var list = filtered();
        if (!list.length) {
            $('#am-asm-list').html('<div class="am-empty" style="padding:32px"><div style="font-size:32px">🔩</div><div style="font-size:12px;margin-top:8px">Không có đơn lắp ráp nào</div></div>');
            return;
        }
        $('#am-asm-list').html(list.map(function (o) {
            var m = elapsed(o);
            return '<div class="am-asm-card ' + (o.id === state.selected ? 'selected' : '') + '" data-id="' + o.id + '">' +
                '<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px">' +
                '<span style="font-weight:800;font-size:13px">' + o.id + '</span>' + badge(o) + '</div>' +
                '<div style="font-size:12px;color:#374151">' + o.items.map(function (i) { return AM.esc(i.name) + ' × ' + i.qty; }).join('<br>') + '</div>' +
                (m !== null && m !== undefined ? '<div style="font-size:11px;color:#0ea5e9;font-weight:700;margin-top:6px" data-timer="' + o.id + '">⏱ ' + AM.formatMinutes(m) + (o.status === 'assembling' ? ' (đang chạy)' : '') + '</div>' : '') +
                '</div>';
        }).join(''));
    }

    function stepTime(o, k) {
        return { exported: o.exportedAt, received: o.partsReceivedAt, assembling: o.startedAt, assembled: o.finishedAt, delivered: o.stockedAt }[k];
    }

    function renderDetail() {
        var o = findOrder(state.selected), $d = $('#am-asm-detail');
        if (!o) {
            $d.html('<div class="am-card am-empty" style="flex:1;display:flex;align-items:center;justify-content:center;flex-direction:column"><div style="font-size:40px">🔩</div>' +
                '<div style="font-size:13px;margin-top:8px">Chọn một đơn lắp ráp bên trái</div></div>');
            return;
        }
        var cur = STEPS.indexOf(o.status), m = elapsed(o);
        var steps = STEPS.map(function (k, i) {
            var done = i <= cur, c = (STATUS[k] || {}).color || '#6b7280', t = stepTime(o, k);
            return '<div style="flex:1;text-align:center;position:relative">' +
                '<div class="am-step-dot" style="margin:0 auto 6px;background:' + (done ? c : '#f3f4f6') + ';color:' + (done ? '#fff' : '#d1d5db') + '">' + (done ? '✓' : i + 1) + '</div>' +
                '<div style="font-size:11px;font-weight:' + (i === cur ? 800 : 500) + ';color:' + (done ? '#1a2035' : '#9ca3af') + '">' + AM.esc(FLOW[k] || k) + '</div>' +
                '<div style="font-size:10px;color:#9ca3af;margin-top:2px">' + AM.esc(t || '') + '</div></div>';
        }).join('');

        var action = '';
        if (o.status === 'exported' && AM.can('admin', 'assembly')) action = ['partsReceive', '📥 Nhận linh kiện', '#0891b2'];
        else if (o.status === 'received' && AM.can('admin', 'assembly')) action = ['start', '▶ Bắt đầu lắp ráp', '#0ea5e9'];
        else if (o.status === 'assembling' && AM.can('admin', 'assembly')) action = ['finish', '✓ Lắp ráp xong', '#8b5cf6'];
        else if (o.status === 'assembled' && AM.can('admin', 'kt_congno')) action = ['stock', '📦 Nhập kho máy thành phẩm', '#059669'];
        var waiting = { exported: 'Bộ phận lắp ráp nhận linh kiện', received: 'Bộ phận lắp ráp bắt đầu', assembling: 'Bộ phận lắp ráp hoàn tất', assembled: 'Kế toán công nợ nhập kho' }[o.status];

        $d.html('<div class="am-card" style="flex:1;display:flex;flex-direction:column;overflow:hidden">' +
            '<div style="padding:16px 18px;border-bottom:1px solid #f3f4f6;display:flex;justify-content:space-between;align-items:center">' +
            '<div><div style="font-weight:900;font-size:17px">' + o.id + '</div><div style="font-size:11px;color:#9ca3af">Tạo ' + AM.esc(o.orderedAt || '') + ' · ' + AM.esc(o.sale || '') + '</div></div>' + badge(o) + '</div>' +
            '<div style="padding:16px 18px;overflow:auto;flex:1">' +
            '<div style="display:flex;gap:4px;margin-bottom:18px">' + steps + '</div>' +
            (m !== null && m !== undefined ? '<div style="background:#e0f2fe;border-radius:10px;padding:12px 14px;margin-bottom:14px;font-size:13px;color:#0369a1;font-weight:700" data-timer-big="' + o.id + '">⏱ Thời gian lắp: ' + AM.formatMinutes(m) + (o.status === 'assembling' ? ' — đang lắp' : '') + '</div>' : '') +
            '<div class="am-label" style="margin-bottom:6px">MÁY CẦN LẮP</div><table class="am-mini-table" style="margin-bottom:14px">' +
            o.items.map(function (i) { return '<tr><td>🚜 ' + AM.esc(i.name) + '<div class="am-mini-sub">' + AM.esc(i.productId) + '</div></td><td class="r b">× ' + i.qty + ' ' + AM.esc(i.unit) + '</td></tr>'; }).join('') + '</table>' +
            '<div class="am-label" style="margin-bottom:6px">LINH KIỆN ĐƯỢC XUẤT (' + o.parts.length + ' loại)</div>' +
            '<div style="display:flex;flex-direction:column;gap:6px;margin-bottom:14px">' + o.parts.map(function (p) {
                return '<div class="am-pick-item ' + (p.picked ? 'picked' : '') + '"><div class="am-check">' + (p.picked ? '<span style="color:#fff;font-size:12px">✓</span>' : '') + '</div>' +
                    '<div style="flex:1"><div style="font-size:12px;font-weight:700">' + AM.esc(p.name) + '</div><div style="font-size:10px;color:#9ca3af">' + AM.esc(p.id) + '</div></div>' +
                    '<div style="font-weight:800;font-size:13px">× ' + p.qtyTotal + ' ' + AM.esc(p.unit || '') + '</div></div>';
            }).join('') + '</div>' +
            '<div class="am-label" style="margin-bottom:6px">GHI CHÚ LẮP RÁP</div>' +
            '<textarea class="am-textarea" id="am-asm-note" style="height:70px"' + (AM.can('admin', 'assembly') && o.status !== 'delivered' ? '' : ' readonly') + ' placeholder="Vấn đề phát sinh, linh kiện lỗi...">' + AM.esc(o.assemblyNote || '') + '</textarea>' +
            '</div>' +
            '<div style="padding:12px 18px;border-top:1px solid #f3f4f6;display:flex;gap:8px">' +
            (action ? '<button class="am-btn-block" data-asm-action="' + action[0] + '" style="background:' + action[2] + ';padding:12px;font-size:14px">' + action[1] + '</button>'
                : (waiting ? '<div style="flex:1;font-size:12px;color:#6b7280;background:#f8fafc;border-radius:8px;padding:10px 12px">⏳ Đang chờ: <b>' + waiting + '</b></div>'
                    : '<div style="flex:1;font-size:12px;color:#059669;font-weight:700;background:#f0fdf4;border-radius:8px;padding:10px 12px">✓ Đã nhập kho' + (o.importCode ? ' · phiếu ' + AM.esc(o.importCode) : '') + '</div>')) +
            '</div></div>');
    }

    function render() {
        if (!state.selected || !findOrder(state.selected)) {
            var first = filtered()[0];
            state.selected = first ? first.id : null;
        }
        renderSummary();
        renderList();
        renderDetail();
    }

    var CONFIRM = {
        partsReceive: ['📥 Nhận linh kiện', 'Xác nhận đã nhận đủ linh kiện từ kho cho đơn này.', 'Đã nhận đủ', '#0891b2'],
        start: ['▶ Bắt đầu lắp ráp', 'Hệ thống bắt đầu tính giờ lắp ráp.', 'Bắt đầu', '#0ea5e9'],
        finish: ['✓ Lắp ráp xong', 'Dừng tính giờ và chuyển cho Kế toán công nợ nhập kho.', 'Đã lắp xong', '#8b5cf6'],
        stock: ['📦 Nhập kho', 'Nhập máy thành phẩm vào kho để bán.', 'Nhập kho', '#059669']
    };
    var OPS = { partsReceive: 'order.partsReceive', start: 'order.start', finish: 'order.finish', stock: 'order.stock' };

    $(function () {
        if (!$('#am-asm-list').length) return;
        var focus = AM.query('order');
        if (focus && findOrder(focus)) { state.selected = focus; state.filter = 'all'; }
        render();
        // Đồng hồ thời gian lắp chạy theo phút
        setInterval(function () {
            orders.forEach(function (o) {
                if (o.status !== 'assembling') return;
                var m = elapsed(o);
                $('[data-timer="' + o.id + '"]').text('⏱ ' + AM.formatMinutes(m) + ' (đang chạy)');
                $('[data-timer-big="' + o.id + '"]').text('⏱ Thời gian lắp: ' + AM.formatMinutes(m) + ' — đang lắp');
            });
        }, 30000);

        $('#am-asm-filters').on('click', '[data-filter]', function () {
            state.filter = $(this).data('filter');
            state.selected = null;
            render();
        });
        $('#am-asm-list').on('click', '.am-asm-card', function () {
            state.selected = $(this).data('id');
            render();
        });
        $('#am-asm-detail')
            .on('click', '[data-asm-action]', function () {
                var key = $(this).data('asm-action'), o = findOrder(state.selected), c = CONFIRM[key];
                AM.confirm({ title: c[0] + ' — ' + o.id, message: c[1], okLabel: c[2], okColor: c[3], onOk: function () { call(OPS[key], o); } });
            })
            .on('change', '#am-asm-note', function () {
                var o = findOrder(state.selected), note = this.value;
                AM.api('order.assemblyNote', { id: o.id, note: note }).done(function () { o.assemblyNote = note; AM.toast('✓ Đã lưu ghi chú'); });
            });
    });
})(jQuery, window.AM);
