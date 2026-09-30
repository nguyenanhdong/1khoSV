/* AgriMac – Bộ phận lắp ráp: checklist nhận linh kiện theo BOM */
(function ($, AM) {
    'use strict';

    var orders = window.AM_ASM_ORDERS || [];
    var state = { selected: orders.length ? orders[0].id : null, filter: 'all', busy: false };
    var STATUS = {
        assembling: { label: 'Đang lắp ráp', color: '#0ea5e9', bg: '#e0f2fe' },
        assembled: { label: 'Lắp ráp xong', color: '#8b5cf6', bg: '#ede9fe' }
    };

    function findOrder(id) {
        return orders.filter(function (o) { return o.id === id; })[0] || null;
    }

    function isPicked(orderId, partId) {
        var o = findOrder(orderId), b = o && o.bom.filter(function (x) { return x.id === partId; })[0];
        return !!(b && b.picked);
    }

    function applyOrder(data) {
        var cur = findOrder(data.order.id);
        Object.keys(cur).forEach(function (k) { delete cur[k]; });
        $.extend(cur, data.order);
    }

    function call(op, payload) {
        if (state.busy) return $.Deferred().reject({ handled: true }).promise();
        state.busy = true;
        return AM.api(op, $.extend({ id: state.selected }, payload)).done(function (data) {
            if (data.order) applyOrder(data);
            render();
        }).always(function () { state.busy = false; });
    }
    function pickedCount(o) { return o.bom.filter(function (b) { return isPicked(o.id, b.id); }).length; }
    function progress(o) { return o.bom.length ? Math.round(pickedCount(o) / o.bom.length * 100) : 0; }

    function renderSummary() {
        var html = '<div style="font-weight:800;font-size:15px;margin-bottom:8px">🔩 Xưởng lắp ráp</div><div style="display:grid;grid-template-columns:1fr 1fr;gap:8px">';
        [['assembling', 'Đang lắp', '#0ea5e9'], ['assembled', 'Chờ xuất kho', '#8b5cf6']].forEach(function (s) {
            var n = orders.filter(function (o) { return o.status === s[0]; }).length;
            html += '<div style="background:rgba(255,255,255,0.08);border-radius:8px;padding:8px 10px;border-left:3px solid ' + s[2] + '">' +
                '<div style="font-size:22px;font-weight:900;color:' + s[2] + '">' + n + '</div>' +
                '<div style="font-size:10px;color:rgba(255,255,255,0.5);margin-top:1px">' + s[1] + '</div></div>';
        });
        $('#am-asm-summary').html(html + '</div>');
    }

    function renderList() {
        var list = state.filter === 'all' ? orders : orders.filter(function (o) { return o.status === state.filter; });
        if (!list.length) {
            $('#am-asm-list').html('<div class="am-empty" style="padding:32px"><div style="font-size:32px">🔩</div><div style="font-size:12px;margin-top:8px">Không có đơn nào</div></div>');
            return;
        }
        $('#am-asm-list').html(list.map(function (o) {
            var dealer = AM.dealer(o.dealerId) || {}, pct = progress(o), st = STATUS[o.status];
            var html = '<div class="am-asm-card ' + (o.id === state.selected ? 'selected' : '') + '" data-id="' + o.id + '">' +
                '<div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:6px"><div>' +
                '<div style="display:flex;align-items:center;gap:5px"><span style="font-weight:800;font-size:13px;color:#1a2035">' + o.id + '</span>' +
                (o.type === 'warranty' ? '<span style="font-size:9px;background:#fee2e2;color:#ef4444;padding:1px 5px;border-radius:4px;font-weight:700">BH</span>' : '') + '</div>' +
                '<div style="font-size:11px;color:#6b7280;margin-top:1px">' + AM.esc(o.product) + ' × ' + o.qty + '</div></div>' +
                '<span style="font-size:10px;background:' + st.bg + ';color:' + st.color + ';padding:3px 7px;border-radius:8px;font-weight:700">' + st.label + '</span></div>' +
                '<div style="font-size:11px;color:#9ca3af;margin-bottom:8px">🏪 ' + AM.esc(AM.shortDealer(dealer.name)) + ' · Sale: ' + AM.esc(AM.lastWord(o.sale)) + '</div>';
            if (o.bom.length) {
                html += '<div><div style="display:flex;justify-content:space-between;font-size:10px;color:#9ca3af;margin-bottom:3px"><span>Nhận linh kiện</span>' +
                    '<span style="font-weight:700;color:' + (pct === 100 ? '#059669' : '#0ea5e9') + '">' + pickedCount(o) + '/' + o.bom.length + ' (' + pct + '%)</span></div>' +
                    '<div class="am-bar" style="height:5px"><div style="width:' + pct + '%;background:' + (pct === 100 ? '#10b981' : '#0ea5e9') + '"></div></div></div>';
            } else {
                html += '<div style="font-size:10px;color:#f59e0b;font-weight:600">⚠ Chưa có danh sách linh kiện</div>';
            }
            return html + '</div>';
        }).join(''));
    }

    function groupByCategory(bom) {
        var groups = {};
        bom.forEach(function (b) {
            var p = AM.part(b.id);
            if (!p) return;
            (groups[p.cat] = groups[p.cat] || []).push({ id: b.id, qty: b.qty, part: p });
        });
        return groups;
    }

    function renderDetail() {
        var o = findOrder(state.selected), $d = $('#am-asm-detail');
        if (!o) {
            $d.html('<div class="am-card-lg" style="flex:1;display:flex;align-items:center;justify-content:center"><div class="am-empty">' +
                '<div style="font-size:48px">🔩</div><div style="font-weight:700;font-size:15px;margin-top:12px;color:#9ca3af">Chọn đơn hàng để bắt đầu</div>' +
                '<div style="font-size:12px;margin-top:6px">Danh sách linh kiện và checklist lắp ráp sẽ hiện ở đây</div></div></div>');
            return;
        }
        var dealer = AM.dealer(o.dealerId) || {}, pct = progress(o), st = STATUS[o.status], assembling = o.status === 'assembling';
        var html = '<div style="flex:1;background:#fff;border-radius:14px;overflow:hidden;box-shadow:0 2px 10px rgba(0,0,0,0.08);display:flex;flex-direction:column">' +
            '<div style="background:linear-gradient(90deg,#0f2027,#1a3a4a);padding:14px 20px;display:flex;justify-content:space-between;align-items:center"><div>' +
            '<div style="display:flex;align-items:center;gap:8px"><span style="font-weight:900;font-size:16px;color:#fff">' + o.id + '</span>' +
            (o.type === 'warranty' ? '<span style="font-size:10px;background:#fee2e2;color:#ef4444;padding:2px 7px;border-radius:6px;font-weight:700">BẢO HÀNH</span>' : '') +
            '<span style="font-size:11px;background:' + st.bg + ';color:' + st.color + ';padding:3px 8px;border-radius:8px;font-weight:700">' + st.label + '</span></div>' +
            '<div style="font-size:11px;color:rgba(255,255,255,0.5);margin-top:3px">' + AM.esc(dealer.name) + ' · ' + AM.esc(o.product) + ' × ' + o.qty + ' máy · Sale: ' + AM.esc(o.sale) + '</div></div>' +
            '<div style="text-align:right"><div style="font-size:10px;color:rgba(255,255,255,0.4)">Tiến độ nhận LK</div>' +
            '<div style="font-size:22px;font-weight:900;color:' + (pct === 100 ? '#4ade80' : '#fbbf24') + '">' + pct + '%</div></div></div>';

        var steps = [[pct > 0, '📥 Bắt đầu nhận LK'], [pct === 100, '✅ Đã nhận đủ LK'], [o.status === 'assembled', '🔩 Lắp ráp xong']];
        html += '<div style="background:#f8fafc;padding:10px 20px;border-bottom:1px solid #e5e7eb"><div style="display:flex;align-items:center">';
        steps.forEach(function (s, i) {
            html += '<div style="display:flex;align-items:center;flex-shrink:0"><div style="display:flex;align-items:center;gap:5px;padding:5px 12px;border-radius:20px;background:' +
                (s[0] ? '#0ea5e9' : '#f3f4f6') + ';color:' + (s[0] ? '#fff' : '#9ca3af') + ';font-size:11px;font-weight:' + (s[0] ? 700 : 400) + ';transition:all .3s">' +
                (s[0] ? '<span>✓</span>' : '') + s[1] + '</div>' +
                (i < steps.length - 1 ? '<div style="font-size:12px;color:#d1d5db;padding:0 4px">→</div>' : '') + '</div>';
        });
        html += '<div style="margin-left:auto;display:flex;gap:8px">' +
            '<button data-action="pick-all" style="padding:6px 13px;background:#f1f5f9;border:1px solid #e5e7eb;border-radius:8px;cursor:pointer;font-size:11px;font-weight:700;color:#374151">☑ Nhận tất cả</button>' +
            (AM.can('admin', 'assembly') && assembling && pct === 100
                ? '<button data-action="finish" style="padding:6px 14px;background:#8b5cf6;color:#fff;border:none;border-radius:8px;cursor:pointer;font-weight:800;font-size:12px">✓ Hoàn tất lắp ráp</button>' : '') +
            (o.status === 'assembled' ? '<span style="padding:6px 13px;background:#ede9fe;color:#8b5cf6;border-radius:8px;font-size:11px;font-weight:700">✓ Chờ KT xuất kho</span>' : '') +
            '</div></div></div>';

        html += '<div style="flex:1;overflow:auto;padding:16px 20px">';
        if (!o.bom.length) {
            html += '<div class="am-empty" style="padding:40px"><div style="font-size:40px">📋</div>' +
                '<div style="font-weight:700;margin-top:10px;font-size:14px">Chưa có danh sách linh kiện</div>' +
                '<div style="font-size:12px;margin-top:6px">Sale chưa tạo BOM cho đơn này</div></div>';
        } else {
            html += '<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">';
            $.each(groupByCategory(o.bom), function (cat, items) {
                var got = items.filter(function (b) { return isPicked(o.id, b.id); }).length;
                html += '<div><div style="display:flex;align-items:center;gap:7px;margin-bottom:10px">' +
                    '<div style="font-weight:800;font-size:13px;color:#1a2035">' + AM.esc(cat) + '</div>' +
                    '<div style="font-size:10px;background:#f1f5f9;color:#6b7280;padding:2px 7px;border-radius:10px;font-weight:600">' + got + '/' + items.length + ' nhận</div></div>' +
                    '<div style="display:flex;flex-direction:column;gap:7px">';
                items.forEach(function (b) {
                    var picked = isPicked(o.id, b.id), need = b.qty * o.qty, enough = b.part.stock >= need;
                    html += '<div class="am-pick-item ' + (picked ? 'picked' : '') + '" data-part="' + b.id + '" style="cursor:' + (assembling ? 'pointer' : 'default') + ';opacity:' + (o.status === 'assembled' ? 0.85 : 1) + '">' +
                        '<div class="am-check">' + (picked ? '<span style="color:#fff;font-size:13px;font-weight:900">✓</span>' : '') + '</div>' +
                        '<div style="flex:1;min-width:0"><div style="font-size:12px;font-weight:' + (picked ? 600 : 500) + ';color:' + (picked ? '#059669' : '#1a2035') +
                        ';text-decoration:' + (picked ? 'line-through' : 'none') + ';line-height:1.3">' + AM.esc(b.part.name) + '</div>' +
                        '<div style="font-size:10px;color:#9ca3af;margin-top:2px">Kho tồn: <b style="color:' + (enough ? '#059669' : '#ef4444') + '">' + b.part.stock + '</b> ' + b.part.unit +
                        (enough ? '' : '<span style="color:#ef4444;margin-left:4px">⚠ Không đủ!</span>') + '</div></div>' +
                        '<div style="text-align:right;flex-shrink:0"><div style="font-size:13px;font-weight:800;color:' + (picked ? '#059669' : '#0ea5e9') + '">×' + need + '</div>' +
                        '<div style="font-size:9px;color:#9ca3af">' + b.part.unit + '</div></div>' +
                        '<div style="text-align:right;flex-shrink:0;min-width:56px"><div style="font-size:11px;font-weight:700;color:#6b7280">' + AM.money(b.part.cost * need) + '</div></div></div>';
                });
                html += '</div></div>';
            });
            html += '</div>';
        }

        html += '<div style="margin-top:18px;padding-top:16px;border-top:1px solid #f3f4f6">' +
            '<div style="font-size:12px;font-weight:700;color:#374151;margin-bottom:7px">📝 Ghi chú lắp ráp</div>' +
            '<textarea id="am-asm-note" class="am-textarea" placeholder="Ghi chú kỹ thuật, vấn đề phát sinh, yêu cầu đặc biệt..." style="height:80px;padding:8px 10px;line-height:1.5">' +
            AM.esc(o.assemblyNote || '') + '</textarea></div>';

        if (o.bom.length) {
            var totalQty = o.bom.reduce(function (s, b) { return s + b.qty * o.qty; }, 0);
            var totalCost = o.bom.reduce(function (s, b) { var p = AM.part(b.id); return s + (p ? p.cost * b.qty * o.qty : 0); }, 0);
            html += '<div style="margin-top:12px;background:#f8fafc;border-radius:10px;padding:12px 16px;display:flex;gap:16px">';
            [['Tổng linh kiện', o.bom.length + ' loại', '#374151'], ['Tổng số lượng', totalQty + ' cái/bộ', '#374151'],
             ['Đã nhận', pickedCount(o) + '/' + o.bom.length, '#0ea5e9'], ['Chi phí LK', AM.money(totalCost), '#059669']].forEach(function (s) {
                html += '<div style="flex:1;text-align:center"><div style="font-size:10px;color:#9ca3af;margin-bottom:3px">' + s[0] + '</div>' +
                    '<div style="font-size:13px;font-weight:800;color:' + s[2] + '">' + s[1] + '</div></div>';
            });
            html += '</div>';
        }
        $d.html(html + '</div></div>');
    }

    function render() {
        renderSummary();
        renderList();
        renderDetail();
    }

    $(function () {
        render();

        $('.am-asm-filter').on('click', function () {
            $(this).addClass('active').siblings().removeClass('active');
            state.filter = $(this).data('filter');
            renderList();
        });

        $('#am-asm-list').on('click', '.am-asm-card', function () {
            state.selected = $(this).data('id');
            render();
        });

        $('#am-asm-detail')
            .on('click', '.am-pick-item', function () {
                var o = findOrder(state.selected), part = $(this).data('part');
                if (o.status !== 'assembling') return;
                call('order.pick', { partId: part, picked: isPicked(o.id, part) ? 0 : 1 });
            })
            .on('click', '[data-action="pick-all"]', function () {
                if (findOrder(state.selected).status !== 'assembling') return;
                call('order.pick', { all: 1 });
            })
            .on('click', '[data-action="finish"]', function () {
                call('order.assembled', {});
            })
            .on('change', '#am-asm-note', function () {
                var o = findOrder(state.selected), note = this.value;
                AM.api('order.assemblyNote', { id: o.id, note: note }).done(function () {
                    o.assemblyNote = note;
                    AM.toast('✓ Đã lưu ghi chú lắp ráp');
                });
            });
    });
})(jQuery, window.AM);
