/* 1Kho CMS – Đơn hàng: 3 loại đơn (bán / lắp ráp / bảo hành), mỗi loại một luồng trạng thái */
(function ($, AM) {
    'use strict';

    var orders = window.AM_ORDERS || [];
    AM.cacheProducts(window.AM_ORDER_PRODUCTS || []);
    var carriers = window.AM_CARRIERS || [];
    var TYPES = AM.data.orderTypes || {}, FLOWS = AM.data.orderFlows || {}, STATUS = AM.data.orderStatus || {};
    var state = { type: 'all', status: 'all', selected: null, search: '' };
    var CANCEL_REASONS = ['Khách huỷ đặt hàng', 'Đại lý vượt hạn mức công nợ', 'Hết hàng / không đủ linh kiện', 'Sai thông tin đơn', 'Khác'];

    /** Mốc thời gian của từng bước để hiện trên tiến trình */
    var STEP_TIME = {
        pending: 'orderedAt', confirmed: 'approvedAt', exported: 'exportedAt', received: 'partsReceivedAt',
        assembling: 'startedAt', assembled: 'finishedAt', shipping: 'shippedAt', delivered: 'deliveredAt'
    };

    function findOrder(id) {
        return orders.filter(function (o) { return o.id === id; })[0] || null;
    }

    function applyOrder(data) {
        var o = data.order, cur = findOrder(o.id);
        if (cur) {
            Object.keys(cur).forEach(function (k) { delete cur[k]; });
            $.extend(cur, o);
        } else {
            orders.unshift(o);
        }
        return findOrder(o.id);
    }

    function run(op, o, payload) {
        return AM.api(op, $.extend({ id: o.id }, payload || {})).done(function (data) {
            applyOrder(data);
            render();
        });
    }

    function typeBadge(type) {
        var t = TYPES[type] || { short: type, color: '#6b7280', bg: '#f1f5f9' };
        return AM.badge(t.short, t.color, t.bg);
    }

    function statusBadge(o) {
        var s = STATUS[o.status] || { color: '#6b7280', bg: '#f1f5f9' };
        return AM.badge(o.statusLabel || o.status, s.color, s.bg);
    }

    function customerLine(o) {
        if (o.type === 'assembly') return '🔩 Lắp ráp nhập kho';
        return (o.customerType === 'retail' ? '👤 ' : '🏪 ') + (o.customerType === 'retail' ? o.customer : AM.shortDealer(o.customer || ''));
    }

    /* ---------------- Danh sách ---------------- */
    function visibleOrders() {
        var q = state.search.toLowerCase();
        return orders.filter(function (o) {
            if (state.type !== 'all' && o.type !== state.type) return false;
            if (state.status !== 'all' && o.status !== state.status) return false;
            if (!q) return true;
            return (o.id + ' ' + (o.customer || '') + ' ' + (o.customerPhone || '') + ' ' + (o.product || '') + ' ' +
                (o.items || []).map(function (i) { return i.name + ' ' + i.productId; }).join(' ')).toLowerCase().indexOf(q) >= 0;
        });
    }

    function renderFilters() {
        var chip = function (attr, key, label, count, on, color, bg) {
            return '<button class="am-filter" ' + attr + '="' + key + '" style="' + (on ? 'border-color:' + color + ';background:' + bg + ';color:' + color : '') + '">' +
                label + (count > 0 ? ' <span class="am-filter-count" style="background:' + color + '">' + count + '</span>' : '') + '</button>';
        };
        var html = '<div style="display:flex;gap:6px;flex-wrap:wrap;align-items:center;width:100%">' +
            chip('data-type', 'all', 'Tất cả', orders.length, state.type === 'all', '#0f2027', '#e2e8f0');
        $.each(TYPES, function (key, t) {
            html += chip('data-type', key, t.label, orders.filter(function (o) { return o.type === key; }).length, state.type === key, t.color, t.bg);
        });
        html += '<input id="am-order-search" class="am-input" placeholder="🔍 Mã đơn, khách, SĐT, sản phẩm..." value="' + AM.esc(state.search) + '" style="max-width:260px;margin-left:auto">';
        if (canCreate()) html += '<button class="am-btn" data-action="new-order">➕ Tạo đơn</button>';
        html += '</div><div style="display:flex;gap:6px;flex-wrap:wrap;margin-top:8px">';
        var flow = state.type !== 'all' ? FLOWS[state.type] : null;
        var keys = flow ? Object.keys(flow).concat(['cancelled']) : Object.keys(STATUS);
        var pool = orders.filter(function (o) { return state.type === 'all' || o.type === state.type; });
        html += chip('data-status', 'all', 'Mọi trạng thái', 0, state.status === 'all', '#0f2027', '#e2e8f0');
        keys.forEach(function (k) {
            var s = STATUS[k] || {}, n = pool.filter(function (o) { return o.status === k; }).length;
            var label = flow && flow[k] ? flow[k] : (s.label || k);
            if (!flow && !n) return;
            html += chip('data-status', k, label, n, state.status === k, s.color || '#6b7280', s.bg || '#f1f5f9');
        });
        $('#am-order-filters').html(html + '</div>');
    }

    function renderRows() {
        var list = visibleOrders();
        if (!list.length) {
            $('#am-order-rows').html('<tr><td colspan="9" class="am-empty" style="padding:30px">Không có đơn hàng phù hợp</td></tr>');
            return;
        }
        $('#am-order-rows').html(list.map(function (o, i) {
            var sel = o.id === state.selected, b = AM.debtBadge(o);
            return '<tr class="am-clickable" data-id="' + o.id + '" style="background:' + (sel ? '#fffbeb' : (i % 2 === 0 ? '#fff' : '#fafafa')) +
                ';border-left:3px solid ' + (sel ? '#f59e0b' : 'transparent') + '">' +
                '<td>' + typeBadge(o.type) + '</td>' +
                '<td><span style="font-weight:800;font-size:12px">' + o.id + '</span></td>' +
                '<td style="max-width:170px"><div style="font-weight:600;font-size:12px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">' + AM.esc(customerLine(o)) + '</div>' +
                '<div style="font-size:10px;color:#9ca3af">' + AM.esc(o.customerPhone || '') + '</div></td>' +
                '<td style="font-size:12px;max-width:200px"><div style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap">' + AM.esc(o.product) + '</div></td>' +
                '<td style="text-align:center;font-weight:700">' + o.qty + '</td>' +
                '<td><span style="font-weight:700;color:#059669">' + (o.total > 0 ? AM.money(o.total) : '—') + '</span>' +
                (b ? '<div style="margin-top:2px">' + AM.badge(b.label, b.color, b.bg, 9) + '</div>' : '') + '</td>' +
                '<td style="font-size:12px">' + AM.esc(AM.lastWord(o.sale || '')) + '</td>' +
                '<td style="font-size:11px;color:#6b7280">' + AM.esc(o.date || '') + '</td>' +
                '<td>' + statusBadge(o) + '</td></tr>';
        }).join(''));
    }

    /* ---------------- Chi tiết ---------------- */
    function canCreate() { return AM.can('admin', 'sale'); }

    /** Nút thao tác theo loại đơn + trạng thái + vai trò */
    function actionsFor(o) {
        var a = [];
        var add = function (roles, key, label, bg, color) {
            if (roles.indexOf(AM.data.role) >= 0) a.push({ key: key, label: label, bg: bg, color: color });
        };
        switch (o.status) {
            case 'pending':
                add(['admin', 'sale'], 'approve', '✓ Duyệt đơn', '#059669');
                break;
            case 'confirmed':
                add(['admin', 'kt_congno'], 'export', o.type === 'new' ? '📤 Xuất kho' : '📤 Xuất linh kiện', '#3b82f6');
                break;
            case 'exported':
                if (o.type === 'assembly') add(['admin', 'assembly'], 'partsReceive', '📥 Nhận linh kiện', '#0891b2');
                else add(['admin', 'delivery'], 'ship', '🚛 Gửi nhà xe', '#f97316');
                break;
            case 'received':
                add(['admin', 'assembly'], 'start', '▶ Bắt đầu lắp ráp', '#0ea5e9');
                break;
            case 'assembling':
                add(['admin', 'assembly'], 'finish', '✓ Lắp ráp xong', '#8b5cf6');
                break;
            case 'assembled':
                add(['admin', 'kt_congno'], 'stock', '📦 Nhập kho máy thành phẩm', '#059669');
                break;
            case 'shipping':
                add(['admin', 'delivery', 'sale'], 'receive', '✓ Xác nhận khách đã nhận', '#10b981');
                break;
        }
        if ((o.status === 'pending' || o.status === 'confirmed') && AM.can('admin', 'sale')) {
            a.push({ key: 'cancel', label: '✕ Huỷ đơn', bg: '#fff', color: '#ef4444', border: '#fecaca', full: true });
        }
        return a;
    }

    /** Người đang chờ xử lý bước tiếp theo (gợi ý cho vai trò không có nút) */
    function waitingFor(o) {
        return {
            pending: 'Kinh doanh duyệt đơn', confirmed: 'Kế toán công nợ ' + (o.type === 'new' ? 'xuất kho' : 'xuất linh kiện'),
            exported: o.type === 'assembly' ? 'Bộ phận lắp ráp nhận linh kiện' : 'Nhân viên giao hàng gửi nhà xe',
            received: 'Bộ phận lắp ráp bắt đầu lắp', assembling: 'Bộ phận lắp ráp hoàn tất', assembled: 'Kế toán công nợ nhập kho',
            shipping: 'Xác nhận khách đã nhận hàng'
        }[o.status] || '';
    }

    var formatMinutes = AM.formatMinutes;

    function renderSteps(o) {
        var flow = FLOWS[o.type] || {}, keys = Object.keys(flow), cur = keys.indexOf(o.status);
        if (o.status === 'cancelled') {
            return '<div style="background:#f8fafc;border:1.5px solid #e5e7eb;border-radius:10px;padding:12px 14px;font-size:12px;color:#374151;margin-bottom:12px">' +
                '<div style="font-weight:800;color:#6b7280;margin-bottom:4px">✕ ĐƠN ĐÃ HUỶ</div>' + AM.esc(o.cancelReason || '') +
                '<div style="font-size:10px;color:#9ca3af;margin-top:4px">' + AM.esc(o.cancelledAt || '') + '</div></div>';
        }
        var html = '<div style="background:#f8fafc;border-radius:10px;padding:12px 14px;margin-bottom:12px"><div class="am-label" style="margin-bottom:10px">TIẾN TRÌNH</div>';
        keys.forEach(function (k, i) {
            var done = i <= cur, now = i === cur, c = (STATUS[k] || {}).color || '#6b7280';
            var time = o[k === 'delivered' && o.type === 'assembly' ? 'stockedAt' : STEP_TIME[k]];
            html += '<div style="display:flex;align-items:center;gap:9px;margin-bottom:7px">' +
                '<div class="am-step-dot" style="background:' + (done ? c : '#f3f4f6') + ';border:' + (now ? '2px solid ' + c : 'none') + ';color:' + (done ? '#fff' : '#d1d5db') + '">' +
                (done ? (now && k !== 'delivered' ? '●' : '✓') : '○') + '</div>' +
                '<div style="flex:1;font-size:12px;font-weight:' + (now ? 700 : 500) + ';color:' + (now ? c : done ? '#374151' : '#9ca3af') + '">' + AM.esc(flow[k]) + '</div>' +
                (time && done ? '<span style="font-size:10px;color:#9ca3af">' + AM.esc(time) + '</span>' : '') + '</div>';
        });
        if (o.type === 'assembly' && o.assemblyMinutes !== null && o.assemblyMinutes !== undefined) {
            html += '<div style="font-size:11px;color:#0ea5e9;font-weight:700;margin-top:4px">⏱ Thời gian lắp: ' + formatMinutes(o.assemblyMinutes) + '</div>';
        }
        return html + '</div>';
    }

    function linesTable(o) {
        if (o.type === 'warranty') {
            if (!o.parts.length) return '';
            return '<div class="am-label" style="margin:2px 0 6px">LINH KIỆN BẢO HÀNH</div>' +
                '<table class="am-mini-table">' + o.parts.map(function (p) {
                    return '<tr><td>' + AM.esc(p.name) + '<div class="am-mini-sub">' + AM.esc(p.id) + '</div></td><td class="r">× ' + p.qtyTotal + ' ' + AM.esc(p.unit || '') + '</td></tr>';
                }).join('') + '</table>';
        }
        var html = '<div class="am-label" style="margin:2px 0 6px">' + (o.type === 'assembly' ? 'MÁY CẦN LẮP' : 'SẢN PHẨM') + '</div><table class="am-mini-table">' +
            o.items.map(function (i) {
                return '<tr><td>' + AM.esc(i.name) + '<div class="am-mini-sub">' + AM.esc(i.productId) + '</div></td>' +
                    '<td class="r">× ' + i.qty + ' ' + AM.esc(i.unit) + '</td>' +
                    (o.type === 'new' ? '<td class="r">' + AM.money(i.price) + '</td><td class="r b">' + AM.money(i.total) + '</td>' : '') + '</tr>';
            }).join('');
        if (o.type === 'new') html += '<tr class="tot"><td colspan="3">Tổng giá trị đơn</td><td class="r b" style="color:#059669">' + AM.money(o.total) + '</td></tr>';
        html += '</table>';
        if (o.type === 'assembly' && o.parts.length) {
            html += '<div class="am-label" style="margin:10px 0 6px">LINH KIỆN ĐÃ XUẤT (' + AM.money(o.cost) + ')</div><table class="am-mini-table">' +
                o.parts.map(function (p) {
                    return '<tr><td>' + AM.esc(p.name) + '</td><td class="r">× ' + p.qtyTotal + ' ' + AM.esc(p.unit || '') + '</td><td class="r">' + (p.picked ? '✓ đã nhận' : '') + '</td></tr>';
                }).join('') + '</table>';
        }
        return html;
    }

    function factsHtml(o) {
        var rows = [
            ['🧾 Số hoá đơn', o.invoiceNo ? o.invoiceNo + (o.invoiceDate ? ' · ' + o.invoiceDate : '') : ''],
            ['📤 Phiếu xuất', o.exportCode], ['📥 Phiếu nhập', o.importCode],
            ['🔩 Thợ lắp', o.type === 'assembly' ? o.assembler : ''],
            ['🚛 Nhà xe', o.carrier ? o.carrier + (o.carrierPhone ? ' · ' + o.carrierPhone : '') : ''],
            ['🚚 NV giao hàng', o.delivery], ['🔢 Serial', (o.serials || []).join(', ')],
            ['👤 Người nhận', o.receiver ? o.receiver + (o.receiverPhone ? ' · ' + o.receiverPhone : '') : ''],
            ['🛡 Khiếu nại', o.warrantyRef], ['📝 Ghi chú', o.note], ['🚛 Ghi chú gửi', o.deliveryNote], ['🔧 Ghi chú lắp', o.assemblyNote]
        ].filter(function (r) { return r[1]; });
        var html = rows.length ? '<div class="am-facts">' + rows.map(function (r) {
            return '<div><span>' + r[0] + '</span><b>' + AM.esc(r[1]) + '</b></div>';
        }).join('') + '</div>' : '';
        if (o.shipImages && o.shipImages.length) {
            html += '<div class="am-label" style="margin:0 0 6px">ẢNH GỬI HÀNG</div><div style="display:flex;gap:8px;margin-bottom:12px">' +
                o.shipImages.map(function (u) {
                    return '<a href="' + AM.esc(u) + '" target="_blank" rel="noopener"><img src="' + AM.esc(u) + '" alt="" style="width:110px;height:82px;object-fit:cover;border-radius:8px;border:1px solid #e5e7eb"></a>';
                }).join('') + '</div>';
        }
        return html;
    }

    function renderDebtBox(o) {
        if (o.type !== 'new' || o.customerType !== 'dealer' || o.status === 'cancelled') return '';
        var modes = AM.data.debtModes || {}, chosen = !!o.debtMode;
        var rows = chosen
            ? [['Hình thức', modes[o.debtMode] || o.debtMode, '#1a2035'], ['Đã trả', AM.money(o.paid) + (o.paid > 0 && o.paidMethod ? ' · ' + o.paidMethod : ''), '#059669'], ['Còn nợ', AM.money(o.debt), o.debt > 0 ? '#ef4444' : '#059669']]
            : [['Hình thức', 'Chưa chọn — khách nhận hàng sẽ ghi nợ cả đơn', '#6b7280']];
        if (o.debtNote) rows.push(['Ghi chú', o.debtNote, '#374151']);
        if (chosen && o.status !== 'delivered') rows.push(['', 'Ghi vào công nợ đại lý khi khách nhận hàng', '#9ca3af']);
        return '<div style="background:#fff;border:1.5px solid ' + (chosen && o.debt > 0 ? '#fecaca' : '#e5e7eb') + ';border-radius:10px;padding:8px 12px;margin-bottom:12px">' +
            '<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:4px"><span class="am-label">💰 THANH TOÁN / CÔNG NỢ</span>' +
            (AM.canEditDebt() ? '<button type="button" class="am-btn am-btn-sm" data-action="edit-debt">✎ ' + (chosen ? 'Sửa' : 'Chọn') + '</button>' : '') + '</div>' +
            rows.map(function (r) {
                return '<div style="display:flex;gap:8px;padding:3px 0;font-size:11px"><span style="color:#9ca3af;min-width:72px">' + r[0] + '</span>' +
                    '<span style="font-weight:700;color:' + r[2] + ';word-break:break-word">' + AM.esc(r[1]) + '</span></div>';
            }).join('') + '</div>';
    }

    function renderDetail() {
        var o = findOrder(state.selected), $d = $('#am-order-detail');
        if (!o) { $d.addClass('am-hidden').empty(); return; }
        var acts = actionsFor(o), wait = waitingFor(o);
        var customer = o.type === 'assembly' ? '' :
            '<div class="am-cust"><div style="font-weight:800">' + AM.esc(customerLine(o)) + '</div>' +
            (o.customerPhone ? '<div>📞 ' + AM.esc(o.customerPhone) + '</div>' : '') +
            (o.customerAddress ? '<div>📍 ' + AM.esc(o.customerAddress) + '</div>' : '') +
            '<div style="color:#9ca3af">👔 Sale: ' + AM.esc(o.sale || '—') + '</div></div>';
        var buttons = acts.map(function (a) {
            return '<button class="am-btn-block" data-order-action="' + a.key + '" style="' + (a.full ? 'flex:0 0 100%;' : '') + 'background:' + a.bg + ';color:' + (a.color || '#fff') +
                (a.border ? ';border:1.5px solid ' + a.border : '') + '">' + a.label + '</button>';
        }).join('');
        if (!acts.length && wait && o.status !== 'delivered' && o.status !== 'cancelled') {
            buttons = '<div style="flex:1;font-size:11px;color:#6b7280;background:#f8fafc;border-radius:8px;padding:9px 12px">⏳ Đang chờ: <b>' + AM.esc(wait) + '</b></div>';
        }
        $d.html(
            '<div style="padding:14px 16px;border-bottom:1px solid #f3f4f6"><div style="display:flex;justify-content:space-between;align-items:flex-start;gap:8px"><div>' +
            '<div style="font-weight:900;font-size:15px;color:#1a2035">' + o.id + '</div>' +
            '<div style="font-size:11px;color:#9ca3af;margin-top:2px">' + AM.esc((TYPES[o.type] || {}).label || '') + ' · ' + AM.esc(o.orderedAt || o.date) + '</div></div>' +
            '<div style="display:flex;gap:4px;align-items:center">' + typeBadge(o.type) + statusBadge(o) +
            '<button type="button" class="am-btn am-btn-sm" data-action="close-detail" style="background:#f1f5f9;color:#374151">✕</button></div></div></div>' +
            '<div style="flex:1;overflow:auto;padding:14px 16px">' + customer + linesTable(o) + '<div style="height:12px"></div>' +
            renderDebtBox(o) + factsHtml(o) + renderSteps(o) + '</div>' +
            (buttons ? '<div style="padding:12px 16px;border-top:1px solid #f3f4f6;display:flex;gap:7px;flex-wrap:wrap">' + buttons + '</div>' : '')
        ).removeClass('am-hidden');
    }

    /* ---------------- Tạo đơn: nhiều sản phẩm / linh kiện ---------------- */
    var editor = { api: null, type: 'new' };

    function dealerOptions() {
        return [['retail', '👤 Khách lẻ (nhập thông tin khách)']].concat($.map(AM.data.dealers || {}, function (d) { return [[d.id, d.name + ' · ' + (d.province || '')]]; }));
    }

    function productPrice(p, customer) {
        if (!p) return '';
        var retail = p.retailPrice || p.price || 0;
        return customer === 'retail' ? retail : (p.dealerPrice || retail);
    }

    function lineRow(kind) {
        var picker = AM.pickerHtml('line_item', kind === 'parts' ? 'parts' : 'products', '', '', kind === 'parts' ? 'Gõ tên / mã linh kiện...' : 'Gõ tên / mã sản phẩm...');
        return '<tr data-line data-auto="1"><td style="min-width:220px">' + picker + '</td>' +
            '<td style="width:74px"><input type="number" class="am-input" data-line-qty min="1" value="1"></td>' +
            (kind === 'new' ? '<td style="width:130px"><input type="number" class="am-input" data-line-price min="0" placeholder="Đơn giá"></td><td style="width:90px;text-align:right;font-weight:700;font-size:12px" data-line-total>—</td>' : '') +
            '<td style="width:30px"><button type="button" class="am-line-del" data-line-del title="Xoá dòng">✕</button></td></tr>';
    }

    function partRow(code, name, qty) {
        return '<tr data-line><td style="min-width:240px">' + AM.pickerHtml('line_item', 'parts', code, code ? name + ' (' + code + ')' : '', 'Gõ tên / mã linh kiện...') + '</td>' +
            '<td style="width:80px"><input type="number" class="am-input" data-line-qty min="1" value="' + (qty || 1) + '"></td>' +
            '<td style="width:30px"><button type="button" class="am-line-del" data-line-del title="Xoá dòng">✕</button></td></tr>';
    }

    function linesEditor(kind) {
        var head = kind === 'parts' ? '<th>Linh kiện</th><th>SL</th><th></th>'
            : '<th>Sản phẩm</th><th>SL</th>' + (kind === 'new' ? '<th>Đơn giá (đ)</th><th style="text-align:right">Thành tiền</th>' : '') + '<th></th>';
        return '<div class="am-field am-field-full" data-field="' + (kind === 'parts' ? 'parts' : 'items') + '">' +
            '<label>' + (kind === 'parts' ? 'Linh kiện gửi bảo hành' : kind === 'assembly' ? 'Máy cần lắp ráp' : 'Sản phẩm') + ' <span class="am-req">*</span></label>' +
            '<table class="am-lines"><thead><tr>' + head + '</tr></thead><tbody data-lines="' + kind + '">' + lineRow(kind) + '</tbody></table>' +
            '<button type="button" class="am-btn am-btn-sm" data-line-add="' + kind + '" style="margin-top:6px;background:#f1f5f9;color:#0f2027">+ Thêm dòng</button>' +
            (kind === 'new' ? '<div class="am-field-hint">Giá tự điền: đại lý → giá đại lý, khách lẻ → giá bán lẻ. Sửa được từng dòng.</div>' : '') +
            '<div class="am-field-error"></div></div>';
    }

    function readLines() {
        var kind = editor.type === 'warranty' ? 'parts' : editor.type, out = [];
        $('#am-generic-form-el [data-lines] tr[data-line]').each(function () {
            var $r = $(this), code = $r.find('input[type=hidden][name=line_item]').val(), qty = $r.find('[data-line-qty]').val();
            if (!code && (!qty || qty === '1') && !$r.find('.am-picker-input').val()) return;
            if (kind === 'parts') out.push({ part: code, qty: qty });
            else out.push({ product: code, qty: qty, price: kind === 'new' ? $r.find('[data-line-price]').val() : 0 });
        });
        return out;
    }

    function creditBox(dealer, extra) {
        var after = dealer.debt + (extra || 0), pct = dealer.limit > 0 ? Math.round(after / dealer.limit * 100) : 0, over = after > dealer.limit;
        return '<div style="background:' + (over ? '#fff5f5' : '#f0fdf4') + ';border:1.5px solid ' + (over ? '#fecaca' : '#bbf7d0') +
            ';border-radius:8px;padding:10px 14px;font-size:12px;color:#374151;line-height:1.6">' +
            '<div style="font-weight:700;color:' + (over ? '#ef4444' : '#059669') + '">' + (over ? '⚠ Vượt hạn mức công nợ' : '✓ Trong hạn mức công nợ') + '</div>' +
            'Dư nợ <b>' + AM.money(dealer.debt) + '</b> + đơn này <b>' + AM.money(extra || 0) + '</b> = <b>' + AM.money(after) + '</b> / hạn mức <b>' + AM.money(dealer.limit) + '</b> (' + pct + '%)</div>';
    }

    function recalc() {
        var api = editor.api;
        if (!api) return;
        var total = 0, customer = api.get('customer'), html = '';
        $('#am-generic-form-el [data-lines] tr[data-line]').each(function () {
            var $r = $(this), qty = +$r.find('[data-line-qty]').val() || 0, price = +$r.find('[data-line-price]').val() || 0;
            if (editor.type === 'new') {
                $r.find('[data-line-total]').text(qty && price ? AM.money(qty * price) : '—');
                total += qty * price;
            }
            var code = $r.find('input[type=hidden][name=line_item]').val(), p = code ? AM.productCache[code] : null;
            if (editor.type === 'new' && p && qty > p.stock) {
                html += '<div style="font-size:11px;color:#b45309;margin-bottom:6px">⚠ ' + AM.esc(p.name) + ': tồn kho còn ' + p.stock + ' — cần nhập hoặc lắp thêm trước khi xuất.</div>';
            }
        });
        if (editor.type === 'new' && total > 0) {
            html += '<div class="am-row-between" style="background:#f8fafc;border-radius:8px;padding:10px 14px;margin-bottom:8px">' +
                '<span style="font-size:12px;color:#6b7280">Tổng giá trị đơn</span><span style="font-weight:900;font-size:15px;color:#059669">' + AM.money(total) + '</span></div>';
            var dealer = customer && customer !== 'retail' ? AM.dealer(customer) : null;
            if (dealer) html += creditBox(dealer, total);
        }
        api.preview(html);
    }

    function repriceAuto() {
        var customer = editor.api ? editor.api.get('customer') : '';
        $('#am-generic-form-el [data-lines="new"] tr[data-line][data-auto="1"]').each(function () {
            var code = $(this).find('input[type=hidden][name=line_item]').val(), p = code ? AM.productCache[code] : null;
            if (p) $(this).find('[data-line-price]').val(productPrice(p, customer));
        });
    }

    function openCreateOrder(prefill) {
        prefill = prefill || {};
        var sales = AM.staffByRole('sale'), isSale = AM.data.role === 'sale';
        var type = TYPES[prefill.type] ? prefill.type : 'new';
        editor.type = type;
        AM.form.open({
            title: '📋 Tạo đơn hàng',
            sub: 'Đơn mới ở trạng thái Chờ duyệt',
            width: 760,
            submitLabel: '💾 Tạo đơn',
            fields: [
                { name: 'type', label: 'Loại đơn', type: 'select', required: true, options: $.map(TYPES, function (t, k) { return [[k, t.label]]; }) },
                isSale
                    ? { name: 'saleName', label: 'Sale phụ trách', readonly: true, hint: 'Đơn do bạn tạo — bạn là Sale phụ trách' }
                    : { name: 'sale', label: 'Sale phụ trách', type: 'select', required: true, placeholder: '— Chọn Sale —', options: sales.map(function (n) { return [n, n]; }) },
                { name: 'customer', label: 'Đại lý đặt hàng / khách', type: 'select', required: true, full: true, placeholder: '— Chọn đại lý hoặc khách lẻ —', options: dealerOptions() },
                { name: 'customerName', label: 'Tên khách hàng', required: true, placeholder: 'VD: Anh Nguyễn Văn A' },
                { name: 'customerPhone', label: 'Số điện thoại', type: 'tel', required: true, placeholder: 'VD: 0912345678' },
                { name: 'customerAddress', label: 'Địa chỉ nhận hàng', required: true, full: true, placeholder: 'Số nhà, xã/phường, huyện, tỉnh' },
                { name: 'lines', type: 'html', html: '' },
                { name: 'warrantyRef', label: 'Mã phiếu khiếu nại', placeholder: 'VD: KC001', hint: 'Liên kết với phiếu khiếu nại bảo hành (nếu có)' },
                { name: 'note', label: 'Ghi chú', type: 'textarea', full: true, placeholder: 'Yêu cầu giao hàng, chiết khấu, điều khoản thanh toán...' }
            ],
            values: { type: type, saleName: AM.data.me ? AM.data.me.name : '', sale: prefill.sale || '', customer: prefill.dealer || '', warrantyRef: prefill.claim || '' },
            onChange: function (api, name) {
                editor.api = api;
                var t = api.get('type'), customer = api.get('customer');
                if (name === null || name === 'type') {
                    editor.type = t;
                    api.html('lines', linesEditor(t === 'warranty' ? 'parts' : t));
                }
                api.toggle('customer', t !== 'assembly');
                api.toggle('customerName', t !== 'assembly' && customer === 'retail');
                api.toggle('customerPhone', t !== 'assembly' && customer === 'retail');
                api.toggle('customerAddress', t !== 'assembly' && customer === 'retail');
                api.toggle('warrantyRef', t === 'warranty');
                if (!isSale) api.toggle('sale', t !== 'assembly');
                if (name === 'customer') repriceAuto();
                recalc();
            },
            validate: function (d) {
                var e = {}, lines = readLines(), key = editor.type === 'warranty' ? 'parts' : 'items';
                if (!lines.length) e[key] = 'Vui lòng thêm ít nhất một dòng';
                lines.forEach(function (l) {
                    if (!(l.product || l.part)) e[key] = 'Có dòng chưa chọn ' + (key === 'parts' ? 'linh kiện' : 'sản phẩm') + ' từ danh sách gợi ý';
                    else if (!(+l.qty >= 1)) e[key] = 'Số lượng phải ≥ 1';
                    else if (editor.type === 'new' && !(+l.price > 0)) e[key] = 'Đơn giá phải lớn hơn 0';
                });
                if (d.customer === 'retail' && d.customerPhone && !AM.isPhone(d.customerPhone)) e.customerPhone = 'Số điện thoại không hợp lệ';
                return e;
            },
            onSubmit: function (d) {
                var payload = { type: d.type, sale: d.sale || '', customer: d.type === 'assembly' ? '' : d.customer, customerName: d.customerName, customerPhone: d.customerPhone,
                    customerAddress: d.customerAddress, warrantyRef: d.type === 'warranty' ? d.warrantyRef : '', note: d.note, lead: prefill.lead || '' };
                payload[d.type === 'warranty' ? 'parts' : 'items'] = readLines();
                return AM.api('order.create', payload).done(function (data) {
                    var o = applyOrder(data);
                    state.type = 'all';
                    state.status = 'all';
                    state.selected = o.id;
                    render();
                });
            }
        });
        // Đơn bảo hành chọn linh kiện, không gán sẵn máy của phiếu khiếu nại vào dòng
        if (prefill.product && type !== 'warranty') {
            resolveProduct(prefill.product).done(function (p) {
                var $p = $('#am-generic-form-el [data-lines] tr[data-line]').first().find('.am-picker');
                if (p && $p.length) AM.pickerSet($p, p);
            });
        }
    }

    function resolveProduct(key) {
        if (!key) return $.Deferred().resolve(null).promise();
        if (AM.productCache[key]) return $.Deferred().resolve(AM.productCache[key]).promise();
        return AM.lookup('product-search', { q: key }).then(function (res) {
            var rows = AM.cacheProducts(res.rows || []);
            return rows.filter(function (r) { return r.id === key || r.name === key; })[0] || null;
        });
    }

    // Sự kiện của bảng dòng trong form (form dựng lại mỗi lần mở → bắt sự kiện trên document)
    $(document)
        .on('click', '#am-generic-form-el [data-line-add]', function () {
            var kind = $(this).data('line-add');
            $('#am-generic-form-el [data-lines="' + kind + '"]').append(kind === 'export-parts' ? partRow('', '', 1) : lineRow(kind));
        })
        .on('click', '#am-generic-form-el [data-line-del]', function () {
            var $body = $(this).closest('tbody'), kind = $body.data('lines');
            $(this).closest('tr').remove();
            if (!$body.children().length) $body.append(kind === 'export-parts' ? partRow('', '', 1) : lineRow(kind));
            recalc();
        })
        .on('change', '#am-generic-form-el [data-lines="new"] input[type=hidden][name=line_item]', function () {
            var $r = $(this).closest('tr'), p = this.value ? AM.productCache[this.value] : null;
            $r.attr('data-auto', '1');
            $r.find('[data-line-price]').val(p ? productPrice(p, editor.api ? editor.api.get('customer') : '') : '');
            recalc();
        })
        .on('input', '#am-generic-form-el [data-line-price]', function () {
            $(this).closest('tr').attr('data-auto', '0');
            recalc();
        })
        .on('input', '#am-generic-form-el [data-line-qty]', function () { recalc(); });

    /* ---------------- Form từng bước ---------------- */
    function openApprove(o) {
        var dealer = o.customerType === 'dealer' ? AM.dealer(o.dealerId) : null, isNew = o.type === 'new';
        var plannedDebt = o.debtMode ? o.debt : o.total;
        AM.form.open({
            title: '✓ Duyệt ' + ((TYPES[o.type] || {}).label || 'đơn').toLowerCase() + ' ' + o.id,
            sub: customerLine(o) + (isNew ? ' · ' + AM.money(o.total) : ''),
            width: 520,
            submitLabel: '✓ Duyệt đơn',
            submitColor: '#059669',
            fields: isNew ? [
                { name: 'invoiceNo', label: 'Số hoá đơn', placeholder: 'Để trống để hệ thống tự sinh', hint: 'Tự sinh dạng HD' + new Date().getFullYear() + '00001. Nhập số hoá đơn GTGT nếu đã xuất.' },
                { name: 'invoiceDate', label: 'Ngày hoá đơn', type: 'date' },
                { name: 'note', label: 'Ghi chú duyệt', type: 'textarea', full: true, placeholder: 'Điều kiện thanh toán, lưu ý cho kho...' }
            ] : [
                { name: 'note', label: 'Ghi chú duyệt', type: 'textarea', full: true, placeholder: 'Lưu ý cho kho / bộ phận lắp ráp...' }
            ],
            values: { invoiceDate: AM.isoToday(), note: o.note || '' },
            onChange: function (api) {
                api.preview((dealer && isNew ? creditBox(dealer, plannedDebt) : '') +
                    '<div style="font-size:11px;color:#6b7280;margin-top:8px">Sau khi duyệt, đơn chuyển cho <b>Kế toán công nợ</b> ' + (o.type === 'new' ? 'xuất kho.' : 'xuất linh kiện.') + '</div>');
            },
            validate: function (v) {
                return v.invoiceNo && !/^[A-Za-z0-9\/-]{3,20}$/.test(v.invoiceNo) ? { invoiceNo: 'Số hoá đơn 3–20 ký tự, chỉ gồm chữ, số, / -' } : {};
            },
            onSubmit: function (v) { return run('order.approve', o, v); }
        });
    }

    function stockLine(icon, name, qty, stock) {
        var short = stock !== null && stock < qty;
        return '<div class="am-row-between" style="padding:6px 0;border-bottom:1px solid #f3f4f6;font-size:12px"><span>' + icon + ' ' + AM.esc(name) + ' × ' + qty + '</span>' +
            '<span style="color:' + (short ? '#ef4444' : '#059669') + ';font-weight:700">' + (stock !== null ? 'tồn ' + stock + (short ? ' — thiếu' : '') : '') + '</span></div>';
    }

    function openExport(o) {
        var assembly = o.type === 'assembly';
        var lines = o.type === 'new'
            ? o.items.map(function (i) { var p = AM.productCache[i.productId]; return stockLine('🚜', i.name, i.qty, p ? p.stock : null); }).join('')
            : o.type === 'warranty' ? o.parts.map(function (p) { var part = AM.part(p.id); return stockLine('🔩', p.name, p.qtyTotal, part ? part.stock : null); }).join('') : '';
        AM.form.open({
            title: (o.type === 'new' ? '📤 Xuất kho — ' : '📤 Xuất linh kiện — ') + o.id,
            sub: customerLine(o),
            width: 620,
            submitLabel: '📤 Xuất kho',
            submitColor: '#3b82f6',
            fields: [
                { name: 'exportDate', label: 'Ngày xuất', type: 'date', required: true },
                { name: 'list', type: 'html', html: assembly
                    ? '<div class="am-field am-field-full" data-field="parts"><label>Linh kiện xuất cho đội lắp ráp <span class="am-req">*</span></label>' +
                      '<div style="display:flex;gap:8px;margin-bottom:6px;align-items:center"><button type="button" class="am-btn am-btn-sm" data-auto-parts style="background:#0ea5e9;color:#fff">⚡ Lấy tự động theo định mức</button>' +
                      '<span style="font-size:11px;color:#6b7280">' + o.items.map(function (i) { return AM.esc(i.name) + ' × ' + i.qty; }).join(', ') + '</span></div>' +
                      '<table class="am-lines"><thead><tr><th>Linh kiện</th><th>SL</th><th></th></tr></thead><tbody data-lines="export-parts">' + partRow('', '', 1) + '</tbody></table>' +
                      '<button type="button" class="am-btn am-btn-sm" data-line-add="export-parts" style="margin-top:6px;background:#f1f5f9;color:#0f2027">+ Thêm linh kiện</button>' +
                      '<div class="am-field-error"></div></div>'
                    : '<div style="background:#f8fafc;border-radius:8px;padding:10px 12px"><div class="am-label" style="margin-bottom:4px">HÀNG XUẤT</div>' + lines +
                      (o.type === 'new' ? '<div style="font-size:11px;color:#6b7280;margin-top:6px">Hệ thống tự sinh serial bảo hành cho từng máy.</div>' : '') + '</div>' },
                { name: 'note', label: 'Ghi chú', type: 'textarea', full: true }
            ],
            values: { exportDate: AM.isoToday() },
            validate: function () {
                if (!assembly) return {};
                var rows = readExportParts();
                if (!rows.length) return { parts: 'Bấm "Lấy tự động" hoặc chọn linh kiện' };
                return rows.some(function (r) { return !r.part || !(+r.qty >= 1); }) ? { parts: 'Có dòng chưa chọn linh kiện hoặc số lượng không hợp lệ' } : {};
            },
            onSubmit: function (v) { return run('order.export', o, { exportDate: v.exportDate, note: v.note, parts: assembly ? readExportParts() : [] }); }
        });
        if (assembly && o.autoParts && o.autoParts.length) fillAutoParts(o);
        $('#am-generic-form-el').off('click.auto').on('click.auto', '[data-auto-parts]', function () {
            if (!o.autoParts || !o.autoParts.length) {
                AM.toast('⚠ Sản phẩm chưa khai báo định mức linh kiện — vào Sản phẩm → 🔩 BOM để khai báo', false);
                return;
            }
            fillAutoParts(o);
        });
    }

    function fillAutoParts(o) {
        $('#am-generic-form-el [data-lines="export-parts"]').html(o.autoParts.map(function (p) { return partRow(p.part, p.name, p.qty); }).join(''));
        var short = o.autoParts.filter(function (p) { return p.stock < p.qty; });
        $('#am-generic-form-el [data-preview]').html(short.length
            ? '<div style="font-size:11px;color:#ef4444">⚠ Không đủ tồn: ' + short.map(function (p) { return AM.esc(p.name) + ' (còn ' + p.stock + ', cần ' + p.qty + ')'; }).join('; ') + '</div>'
            : '<div style="font-size:11px;color:#059669">✓ Đã lấy ' + o.autoParts.length + ' loại linh kiện theo định mức — đủ tồn kho</div>');
    }

    function readExportParts() {
        var out = [];
        $('#am-generic-form-el [data-lines="export-parts"] tr[data-line]').each(function () {
            var code = $(this).find('input[type=hidden][name=line_item]').val(), qty = $(this).find('[data-line-qty]').val();
            if (code || $(this).find('.am-picker-input').val()) out.push({ part: code, qty: qty });
        });
        return out;
    }

    function imagesField() {
        return '<div class="am-field am-field-full" data-field="images"><label>Ảnh gửi hàng (không bắt buộc, tối đa ' + (AM.data.shipImageMax || 2) + ' ảnh)</label>' +
            '<div class="am-ship-imgs" data-ship-imgs></div>' +
            '<label class="am-btn am-btn-sm" style="display:inline-block;cursor:pointer;margin-top:6px;background:#f1f5f9;color:#0f2027">📷 Chọn ảnh' +
            '<input type="file" accept="image/jpeg,image/png" data-ship-file multiple style="display:none"></label>' +
            '<div class="am-field-hint">Ảnh được nén tự động, mỗi ảnh lưu tối đa 2MB</div><div class="am-field-error"></div></div>';
    }

    function openShip(o) {
        var drivers = AM.staffByRole('delivery'), isDriver = AM.data.role === 'delivery';
        if (!carriers.length) AM.toast('⚠ Chưa có nhà xe nào — vào mục Nhà xe để thêm', false);
        AM.form.open({
            title: '🚛 Gửi nhà xe — ' + o.id,
            sub: customerLine(o) + (o.customerAddress ? ' · ' + o.customerAddress : ''),
            width: 560,
            submitLabel: '🚛 Xác nhận đã gửi',
            submitColor: '#f97316',
            fields: [
                { name: 'carrierId', label: 'Nhà xe', type: 'select', required: true, full: true, placeholder: '— Chọn nhà xe —',
                  options: carriers.map(function (c) { return [c.id, c.name + (c.phone ? ' · ' + c.phone : '') + (c.address ? ' · ' + c.address : '')]; }) },
                isDriver ? { name: 'deliveryName', label: 'Nhân viên giao hàng', readonly: true }
                         : { name: 'delivery', label: 'Nhân viên giao hàng', type: 'select', required: true, placeholder: '— Chọn —', options: drivers.map(function (n) { return [n, n]; }) },
                { name: 'shipDate', label: 'Ngày gửi', type: 'date', required: true },
                { name: 'imgs', type: 'html', html: imagesField() },
                { name: 'note', label: 'Ghi chú', type: 'textarea', full: true, placeholder: 'Biển số xe, giờ xe chạy, số phiếu gửi...' }
            ],
            values: { shipDate: AM.isoToday(), deliveryName: AM.data.me ? AM.data.me.name : '', delivery: o.delivery || '' },
            onSubmit: function (v) {
                if ($('#am-generic-form-el [data-ship-imgs]').data('uploading')) {
                    AM.toast('⏳ Ảnh đang tải lên, vui lòng đợi', false);
                    return false;
                }
                var images = $('#am-generic-form-el [data-ship-imgs] [data-url]').map(function () { return $(this).attr('data-url'); }).get();
                return run('order.ship', o, { carrierId: v.carrierId, delivery: v.delivery || '', shipDate: v.shipDate, note: v.note, images: images });
            }
        });
    }

    $(document).on('change', '#am-generic-form-el [data-ship-file]', function () {
        var files = Array.prototype.slice.call(this.files || []), $box = $('#am-generic-form-el [data-ship-imgs]');
        var max = AM.data.shipImageMax || 2, room = max - $box.children().length;
        this.value = '';
        if (files.length > room) AM.toast('⚠ Tối đa ' + max + ' ảnh', false);
        files.slice(0, Math.max(0, room)).forEach(function (file) {
            var $item = $('<div class="am-ship-img"><div class="am-ship-img-loading">⏳</div></div>').appendTo($box);
            $box.data('uploading', ($box.data('uploading') || 0) + 1);
            var fd = new FormData();
            fd.append('file', file);
            $.ajax({ url: AM.data.shipUploadUrl, type: 'POST', data: fd, processData: false, contentType: false, dataType: 'json' })
                .done(function (res) {
                    if (res && res.status) {
                        $item.attr('data-url', res.url).html('<img src="' + AM.esc(res.url) + '" alt=""><button type="button" data-ship-img-del title="Xoá ảnh">✕</button>' +
                            '<span>' + (res.size / 1048576).toFixed(1) + 'MB</span>');
                    } else {
                        $item.remove();
                        AM.toast('⚠ ' + ((res && res.message) || 'Upload ảnh lỗi'), false);
                    }
                })
                .fail(function () { $item.remove(); AM.toast('⚠ Upload ảnh lỗi, vui lòng thử lại', false); })
                .always(function () { $box.data('uploading', Math.max(0, ($box.data('uploading') || 1) - 1)); });
        });
    }).on('click', '#am-generic-form-el [data-ship-img-del]', function () {
        $(this).closest('.am-ship-img').remove();
    });

    function openReceive(o) {
        AM.form.open({
            title: '✓ Khách đã nhận hàng — ' + o.id,
            sub: customerLine(o) + (o.carrier ? ' · gửi qua ' + o.carrier : ''),
            width: 520,
            submitLabel: '✓ Hoàn thành đơn',
            submitColor: '#10b981',
            fields: [
                { name: 'receiver', label: 'Người nhận', placeholder: o.customerType === 'retail' ? o.customer : 'VD: Anh Hùng (chủ đại lý)' },
                { name: 'receiverPhone', label: 'SĐT người nhận', type: 'tel' },
                { name: 'receivedDate', label: 'Ngày nhận', type: 'date', required: true },
                { name: 'note', label: 'Ghi chú', placeholder: 'Tình trạng hàng khi nhận...' }
            ],
            values: { receivedDate: AM.isoToday() },
            onChange: function (api) {
                if (o.type !== 'new') { api.preview(''); return; }
                var html = '<div style="background:#f8fafc;border-radius:8px;padding:10px 14px;font-size:12px;color:#374151;line-height:1.8"><div class="am-label" style="margin-bottom:2px">KHI HOÀN THÀNH SẼ GHI NHẬN</div>';
                if (o.customerType === 'dealer') {
                    var dealer = AM.dealer(o.dealerId), debt = !o.debtMode || o.debtMode === 'full' ? o.total : o.debt;
                    html += '💰 Ghi nợ ' + AM.money(debt) + ' (' + AM.esc((AM.data.debtModes || {})[o.debtMode || 'full'] || '') + ')' +
                        (dealer ? ' · công nợ ' + AM.esc(AM.shortDealer(dealer.name)) + ': <b>' + AM.money(dealer.debt) + '</b> → <b style="color:#ef4444">' + AM.money(dealer.debt + debt) + '</b>' : '') + '<br>';
                }
                html += '👔 Hoa hồng Sale ' + AM.esc(o.sale || '') + ' (' + (AM.data.commission || {}).sale + '%) · 🚚 giao hàng ' + AM.esc(o.delivery || '') + ' (' + (AM.data.commission || {}).delivery + '%)</div>';
                api.preview(html);
            },
            validate: function (v) { return v.receiverPhone && !AM.isPhone(v.receiverPhone) ? { receiverPhone: 'Số điện thoại không hợp lệ' } : {}; },
            onSubmit: function (v) { return run('order.receive', o, v); }
        });
    }

    function openCancel(o) {
        AM.form.open({
            title: '✕ Huỷ đơn ' + o.id,
            sub: 'Đơn huỷ không thể khôi phục',
            width: 480,
            submitLabel: '✕ Xác nhận huỷ',
            submitColor: '#ef4444',
            fields: [
                { name: 'reason', label: 'Lý do huỷ', type: 'select', required: true, full: true, placeholder: '— Chọn lý do —', options: CANCEL_REASONS.map(function (r) { return [r, r]; }) },
                { name: 'detail', label: 'Chi tiết', type: 'textarea', full: true, placeholder: 'Bắt buộc khi chọn "Khác"' }
            ],
            validate: function (v) { return v.reason === 'Khác' && !v.detail ? { detail: 'Vui lòng ghi rõ lý do' } : {}; },
            onSubmit: function (v) { return run('order.cancel', o, v); }
        });
    }

    function confirmStep(o, op, title, message, okLabel, color) {
        AM.confirm({ title: title, message: message, okLabel: okLabel, okColor: color, onOk: function () { run(op, o, {}); } });
    }

    var ACTION_HANDLERS = {
        approve: openApprove, export: openExport, ship: openShip, receive: openReceive, cancel: openCancel,
        partsReceive: function (o) { confirmStep(o, 'order.partsReceive', '📥 Nhận linh kiện — ' + o.id, 'Xác nhận đã nhận đủ ' + o.parts.length + ' loại linh kiện từ kho.', 'Đã nhận đủ', '#0891b2'); },
        start: function (o) { confirmStep(o, 'order.start', '▶ Bắt đầu lắp ráp — ' + o.id, 'Hệ thống ghi giờ bắt đầu để CEO theo dõi thời gian lắp ráp.', 'Bắt đầu', '#0ea5e9'); },
        finish: function (o) { confirmStep(o, 'order.finish', '✓ Lắp ráp xong — ' + o.id, 'Ghi giờ hoàn thành và chuyển cho Kế toán công nợ nhập kho.', 'Đã lắp xong', '#8b5cf6'); },
        stock: function (o) {
            confirmStep(o, 'order.stock', '📦 Nhập kho — ' + o.id, 'Nhập ' + o.items.map(function (i) { return AM.esc(i.name) + ' × ' + i.qty; }).join(', ') +
                ' vào kho thành phẩm (giá vốn = chi phí linh kiện ' + AM.money(o.cost) + ').', 'Nhập kho', '#059669');
        }
    };

    function render() {
        renderFilters();
        renderRows();
        renderDetail();
    }

    $(function () {
        if (!$('#am-order-rows').length) return;
        render();

        if (AM.query('new') && canCreate()) {
            openCreateOrder({ dealer: AM.query('dealer'), product: AM.query('product'), type: AM.query('type'), claim: AM.query('claim'), lead: AM.query('lead') });
        }
        var focus = AM.query('order');
        if (focus && findOrder(focus)) {
            state.selected = focus;
            render();
        }

        $('#am-order-filters')
            .on('click', '[data-action="new-order"]', function () { openCreateOrder(); })
            .on('click', '[data-type]', function () { state.type = $(this).data('type'); state.status = 'all'; render(); })
            .on('click', '[data-status]', function () { state.status = $(this).data('status'); render(); })
            .on('input', '#am-order-search', function () {
                state.search = this.value;
                renderRows();
            });

        $('#am-order-rows').on('click', 'tr[data-id]', function () {
            var id = $(this).data('id');
            state.selected = id === state.selected ? null : id;
            render();
        });

        $('#am-order-detail')
            .on('click', '[data-action="close-detail"]', function () { state.selected = null; render(); })
            .on('click', '[data-order-action]', function () {
                var o = findOrder(state.selected), h = ACTION_HANDLERS[$(this).data('order-action')];
                if (o && h) h(o);
            })
            .on('click', '[data-action="edit-debt"]', function () {
                AM.debtForm(findOrder(state.selected), function (data) { applyOrder(data); render(); });
            });
    });
})(jQuery, window.AM);
