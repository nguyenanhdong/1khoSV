/* AgriMac – Đơn hàng: danh sách, BOM linh kiện, lợi nhuận, luồng duyệt theo vai trò */
(function ($, AM) {
    'use strict';

    var orders = window.AM_ORDERS || [];
    var products = AM.cacheProducts(window.AM_ORDER_PRODUCTS || []);
    var statuses = AM.data.orderStatus;
    var state = { filter: 'all', selected: null, tab: 'info', search: '', bomDraft: {} };

    var STEPS = [
        { key: 'pending', label: 'Đại lý đặt hàng' },
        { key: 'confirmed', label: 'KT duyệt đơn' },
        { key: 'assembling', label: 'Sale chọn linh kiện' },
        { key: 'assembled', label: 'Lắp ráp hoàn tất' },
        { key: 'delivering', label: 'Đang giao hàng' },
        { key: 'delivered', label: 'Hoàn thành' }
    ];
    var STEP_COLORS = ['#f59e0b', '#3b82f6', '#0ea5e9', '#8b5cf6', '#f97316', '#10b981'];

    var ACTIONS = [
        { roles: ['admin', 'kt_banhang'], from: 'pending', form: 'approve', label: '✓ Duyệt & xuất HĐ', bg: '#059669' },
        { roles: ['admin', 'sale'], from: 'confirmed', tab: 'bom', label: '🔩 Chọn linh kiện', bg: '#0ea5e9' },
        { roles: ['admin', 'sale'], from: 'confirmed', to: 'assembling', needBom: true, label: '→ Gửi lắp ráp', bg: '#0f2027', color: '#fbbf24' },
        { roles: ['admin', 'assembly'], from: 'assembling', to: 'assembled', label: '✓ Lắp ráp xong', bg: '#8b5cf6' },
        { roles: ['admin', 'kt_xuatkho'], from: 'assembled', form: 'export', label: '🚚 Xuất kho & giao', bg: '#f97316' },
        { roles: ['admin', 'delivery'], from: 'delivering', form: 'deliver', label: '✓ Đã giao xong', bg: '#10b981' }
    ];
    var CANCEL = { roles: ['admin', 'kt_banhang', 'sale'], from: ['pending', 'confirmed'] };
    var CANCEL_REASONS = ['Đại lý huỷ đặt hàng', 'Đại lý vượt hạn mức công nợ', 'Hết hàng / không đủ linh kiện', 'Sai thông tin đơn', 'Khác'];
    var RATES = AM.data.commission || { sale: 1, delivery: 0.3 };

    function findOrder(id) {
        return orders.filter(function (o) { return o.id === id; })[0] || null;
    }

    function bomOf(o) {
        return state.bomDraft[o.id] || o.bom || [];
    }

    /** Đơn đã gửi lắp ráp dùng giá vốn chốt lúc chọn BOM; đơn đang chọn BOM dùng giá vốn hiện tại. */
    function unitCostOf(o, item) {
        if (item.unitCost !== undefined && !state.bomDraft[o.id]) return item.unitCost;
        var p = AM.part(item.id);
        return p ? p.cost : 0;
    }

    function bomCost(o) {
        return bomOf(o).reduce(function (sum, item) { return sum + unitCostOf(o, item) * item.qty * o.qty; }, 0);
    }

    function applyOrder(data) {
        var o = data.order, cur = findOrder(o.id);
        if (cur) {
            Object.keys(cur).forEach(function (k) { delete cur[k]; });
            $.extend(cur, o);
        } else {
            orders.unshift(o);
        }
        delete state.bomDraft[o.id];
        if (data.product) {
            AM.productCache[data.product.id] = data.product;
            var cur = products.filter(function (p) { return p.id === data.product.id; })[0];
            if (cur) $.extend(cur, data.product); else products.push(data.product);
        }
        return findOrder(o.id);
    }

    function runAction(op, o, payload) {
        return AM.api(op, $.extend({ id: o.id }, payload || {})).done(function (data) {
            applyOrder(data);
            render();
        });
    }

    function profitOf(o) {
        var cost = bomCost(o);
        var profit = o.total > 0 ? o.total - cost : null;
        var margin = profit && o.total > 0 ? Math.round(profit / o.total * 100) : null;
        return { cost: cost, profit: profit, margin: margin };
    }

    function typeBadge(type) {
        return type === 'warranty' ? AM.badge('BH', '#ef4444', '#fee2e2') : AM.badge('Mới', '#059669', '#d1fae5');
    }

    function statusBadge(status) {
        var s = statuses[status];
        return AM.badge(s.label, s.color, s.bg);
    }

    /** Cập nhật BOM trên giao diện ngay, đồng thời lưu nháp vào DB (đơn đang ở trạng thái Đã duyệt). */
    function setBom(orderId, bom) {
        state.bomDraft[orderId] = bom;
        AM.api('order.bom', { id: orderId, bom: bom.map(function (b) { return { id: b.id, qty: b.qty }; }) })
            .done(function (data) { applyOrder(data); render(); })
            .fail(function () { delete state.bomDraft[orderId]; render(); });
    }


    /* ---------- Danh sách ---------- */
    function renderFilters() {
        var allOn = state.filter === 'all';
        var html = '<button class="am-filter" data-filter="all" style="' +
            (allOn ? 'border-color:#0f2027;background:#0f2027;color:#fbbf24' : '') + '">Tất cả (' + orders.length + ')</button>';
        $.each(statuses, function (key, s) {
            var count = orders.filter(function (o) { return o.status === key; }).length;
            var on = state.filter === key;
            html += '<button class="am-filter" data-filter="' + key + '" style="' +
                (on ? 'border-color:' + s.color + ';background:' + s.bg + ';color:' + s.color : '') + '">' + s.label + ' ' +
                (count > 0 ? '<span class="am-filter-count" style="background:' + s.color + '">' + count + '</span>' : '') + '</button>';
        });
        if (canCreate()) html += '<button class="am-btn" data-action="new-order">➕ Tạo đơn</button>';
        $('#am-order-filters').html(html);
    }

    function renderRows() {
        var list = state.filter === 'all' ? orders : orders.filter(function (o) { return o.status === state.filter; });
        var html = '';
        list.forEach(function (o, i) {
            var dealer = AM.dealer(o.dealerId) || {};
            var pf = profitOf(o);
            var sel = o.id === state.selected;
            html += '<tr class="am-clickable" data-id="' + o.id + '" style="background:' + (sel ? '#fffbeb' : (i % 2 === 0 ? '#fff' : '#fafafa')) +
                ';border-left:3px solid ' + (sel ? '#f59e0b' : 'transparent') + '">' +
                '<td>' + typeBadge(o.type) + '</td>' +
                '<td><span style="font-weight:800;color:#1a2035;font-size:12px">' + o.id + '</span></td>' +
                '<td><div style="font-weight:600;font-size:12px">' + AM.esc(AM.shortDealer(dealer.name)) + '</div>' +
                '<div style="font-size:10px;color:#9ca3af">' + AM.esc(dealer.province) + '</div></td>' +
                '<td style="font-size:12px">' + AM.esc(o.product) + '</td>' +
                '<td style="text-align:center;font-weight:700">' + o.qty + '</td>' +
                '<td><span style="font-weight:700;color:#059669">' + (o.total > 0 ? AM.money(o.total) : '—') + '</span></td>' +
                '<td><span style="font-weight:600;color:#6b7280;font-size:12px">' +
                (pf.cost > 0 ? AM.money(pf.cost) : '<span style="color:#d1d5db">Chưa có BOM</span>') + '</span></td>' +
                '<td>' + (pf.profit !== null
                    ? '<span style="font-weight:800;color:' + (pf.profit > 0 ? '#059669' : '#ef4444') + '">' + AM.money(pf.profit) + '</span>' +
                      (pf.margin !== null ? '<span style="font-size:10px;color:#9ca3af;margin-left:4px">(' + pf.margin + '%)</span>' : '')
                    : '<span style="color:#d1d5db;font-size:11px">—</span>') + '</td>' +
                '<td style="font-size:12px">' + AM.esc(AM.lastWord(o.sale)) + '</td>' +
                '<td>' + statusBadge(o.status) + '</td></tr>';
        });
        $('#am-order-rows').html(html);
    }

    /* ---------- Chi tiết ---------- */
    function renderInfoTab(o) {
        var dealer = AM.dealer(o.dealerId) || {};
        var current = STEPS.map(function (s) { return s.key; }).indexOf(o.status);
        var html = '<div style="display:grid;grid-template-columns:1fr 1fr;gap:7px;margin-bottom:14px">' +
            AM.info('🏪', 'Đại lý', AM.shortDealer(dealer.name)) + AM.info('🚜', 'Sản phẩm', o.product) +
            AM.info('📦', 'Số lượng', o.qty + ' chiếc') + AM.info('💵', 'Doanh thu', o.total > 0 ? AM.money(o.total) : 'BH — miễn phí') +
            '</div>' + renderFacts(o) +
            (o.status === 'cancelled'
                ? '<div style="background:#f8fafc;border:1.5px solid #e5e7eb;border-radius:10px;padding:12px 14px;font-size:12px;color:#374151">' +
                  '<div style="font-weight:800;color:#6b7280;margin-bottom:4px">✕ ĐƠN ĐÃ HUỶ</div>' + AM.esc(o.cancelReason || '') +
                  '<div style="font-size:10px;color:#9ca3af;margin-top:4px">' + AM.esc(o.cancelledAt || '') + '</div></div>'
                : '') +
            '<div style="background:#f8fafc;border-radius:10px;padding:12px 14px' + (o.status === 'cancelled' ? ';display:none' : '') + '">' +
            '<div class="am-label" style="margin-bottom:10px">TIẾN TRÌNH XỬ LÝ</div>';
        STEPS.forEach(function (s, i) {
            var done = i <= current, cur = i === current, c = STEP_COLORS[i];
            html += '<div style="display:flex;align-items:center;gap:9px;margin-bottom:8px">' +
                '<div class="am-step-dot" style="background:' + (done ? c : '#f3f4f6') + ';border:' + (cur ? '2px solid ' + c : 'none') +
                ';color:' + (done ? '#fff' : '#d1d5db') + '">' + (done ? (cur ? '●' : '✓') : '○') + '</div>' +
                '<div style="flex:1;font-size:12px;font-weight:' + (cur ? 700 : done ? 500 : 400) + ';color:' + (cur ? c : done ? '#374151' : '#9ca3af') + '">' + s.label + '</div>' +
                (cur ? '<span style="font-size:9px;background:' + c + '22;color:' + c + ';padding:2px 6px;border-radius:6px;font-weight:700">HIỆN TẠI</span>' : '') +
                '</div>';
        });
        html += '</div><div style="margin-top:12px;display:flex;gap:7px;flex-wrap:wrap">';
        ACTIONS.forEach(function (a, idx) {
            if (a.roles.indexOf(AM.data.role) < 0 || o.status !== a.from) return;
            if (a.needBom && !bomOf(o).length) return;
            html += '<button class="am-btn-block" data-order-action="' + idx + '" style="background:' + a.bg + (a.color ? ';color:' + a.color : '') + '">' + a.label + '</button>';
        });
        if (CANCEL.roles.indexOf(AM.data.role) >= 0 && CANCEL.from.indexOf(o.status) >= 0) {
            html += '<button class="am-btn-block" data-action="cancel-order" style="flex:0 0 100%;background:#fff;color:#ef4444;border:1.5px solid #fecaca">✕ Huỷ đơn</button>';
        }
        return html + '</div>';
    }

    function renderPickList(o) {
        var bom = bomOf(o), q = state.search.toLowerCase();
        return (AM.data.parts || []).filter(function (p) {
            var inBom = bom.some(function (b) { return b.id === p.id; });
            return !inBom && (p.name.toLowerCase().indexOf(q) >= 0 || p.cat.toLowerCase().indexOf(q) >= 0);
        }).map(function (p) {
            return '<div class="am-part-pick" data-add-part="' + p.id + '"><div>' +
                '<div style="font-size:12px;font-weight:600">' + AM.esc(p.name) + '</div>' +
                '<div style="font-size:10px;color:#9ca3af">' + AM.esc(p.cat) + ' · Tồn: ' + p.stock + '</div></div>' +
                '<div style="text-align:right"><div style="font-size:11px;font-weight:700;color:#059669">' + AM.money(p.cost) + '</div>' +
                '<div style="font-size:10px;color:#9ca3af">/' + p.unit + '</div></div></div>';
        }).join('');
    }

    function renderBomTab(o) {
        var bom = bomOf(o), cost = bomCost(o);
        var editable = AM.can('admin', 'sale') && o.status === 'confirmed';
        var assembler = AM.can('admin', 'assembly') && o.status === 'assembling';
        var html = '';
        if (o.type === 'warranty') {
            html += '<div style="background:#fee2e2;border-radius:8px;padding:8px 11px;margin-bottom:12px;font-size:11px;color:#ef4444;font-weight:600">🔧 Đơn bảo hành — chọn linh kiện thay thế gửi cho khách</div>';
        }
        if (bom.length) {
            html += '<div style="margin-bottom:14px"><div class="am-row-between" style="margin-bottom:8px">' +
                '<div style="font-size:11px;font-weight:700;color:#374151">DANH SÁCH LINH KIỆN (' + bom.length + ' loại × ' + o.qty + ' máy)</div>' +
                (editable ? '<button data-bom="default" style="font-size:10px;padding:3px 8px;background:#f1f5f9;border:1px solid #e5e7eb;border-radius:6px;cursor:pointer;color:#374151;font-weight:600">↺ BOM mặc định</button>' : '') +
                '</div>';
            bom.forEach(function (item) {
                var p = AM.part(item.id);
                if (!p) return;
                html += '<div class="am-bom-row"><div style="flex:1"><div style="font-size:12px;font-weight:600;color:#1a2035">' + AM.esc(p.name) + '</div>' +
                    '<div style="font-size:10px;color:#9ca3af">' + AM.esc(p.cat) + ' · ' + AM.money(p.cost) + '/' + p.unit + '</div></div>' +
                    (editable
                        ? '<input type="number" min="1" value="' + item.qty + '" data-bom-qty="' + p.id + '" style="width:44px;padding:3px 5px;border:1.5px solid #e5e7eb;border-radius:6px;font-size:12px;text-align:center;outline:none">'
                        : '<span style="font-size:12px;font-weight:700;background:#f1f5f9;padding:3px 8px;border-radius:6px">×' + item.qty + '</span>') +
                    '<div style="min-width:64px;text-align:right"><div style="font-size:12px;font-weight:700;color:#374151">' + AM.money(unitCostOf(o, item) * item.qty * o.qty) + '</div>' +
                    '<div style="font-size:9px;color:#9ca3af">(' + item.qty + '×' + o.qty + 'máy)</div></div>' +
                    (editable ? '<button data-bom-remove="' + p.id + '" style="background:#fee2e2;border:none;color:#ef4444;border-radius:5px;padding:2px 6px;cursor:pointer;font-size:11px">✕</button>' : '') +
                    (assembler ? '<div title="Đánh dấu đã lấy" style="width:20px;height:20px;border-radius:50%;background:#d1fae5;border:1.5px solid #10b981;display:flex;align-items:center;justify-content:center;font-size:11px;color:#059669">✓</div>' : '') +
                    '</div>';
            });
            html += '<div class="am-row-between" style="padding:10px 0 0;border-top:2px solid #e5e7eb;margin-top:6px">' +
                '<span style="font-size:12px;font-weight:700;color:#374151">Tổng chi phí linh kiện</span>' +
                '<span style="font-size:14px;font-weight:900;color:#0ea5e9">' + AM.money(cost) + '</span></div></div>';
        } else {
            html += '<div class="am-empty" style="padding:20px 0"><div style="font-size:28px">🔩</div>' +
                '<div style="font-size:12px;margin-top:6px">Chưa có linh kiện nào</div>' +
                (editable ? '<button data-bom="default" style="margin-top:10px;padding:7px 14px;background:#0ea5e9;color:#fff;border:none;border-radius:8px;cursor:pointer;font-size:12px;font-weight:700">↺ Tự động theo sản phẩm</button>' : '') +
                '</div>';
        }
        if (editable) {
            html += '<div><div style="font-size:11px;font-weight:700;color:#374151;margin-bottom:7px">THÊM LINH KIỆN</div>' +
                '<input id="am-part-search" class="am-input" value="' + AM.esc(state.search) + '" placeholder="Tìm linh kiện..." style="padding:7px 10px;margin-bottom:8px">' +
                '<div id="am-part-list" style="max-height:180px;overflow:auto;display:flex;flex-direction:column;gap:5px">' + renderPickList(o) + '</div></div>';
        }
        return html;
    }

    function renderProfitTab(o) {
        var bom = bomOf(o), pf = profitOf(o), cost = pf.cost, g = pf.profit;
        if (o.type === 'warranty') {
            return '<div style="background:#fff5f5;border-radius:10px;padding:14px;text-align:center"><div style="font-size:24px">🔧</div>' +
                '<div style="font-size:13px;font-weight:700;color:#ef4444;margin-top:6px">Đơn bảo hành</div>' +
                '<div style="font-size:11px;color:#6b7280;margin-top:4px">Không tính doanh thu — chi phí linh kiện: ' + AM.money(cost) + '</div></div>';
        }
        var html = '<div class="am-dark-gradient" style="border-radius:10px;padding:14px 16px;margin-bottom:14px;color:#fff">' +
            '<div style="font-size:10px;color:rgba(255,255,255,0.5);margin-bottom:4px">LỢI NHUẬN GỘP</div>' +
            '<div style="font-size:26px;font-weight:900;color:' + (g && g > 0 ? '#4ade80' : '#f87171') + '">' + (g !== null ? AM.money(g) : 'Chưa có BOM') + '</div>' +
            (pf.margin !== null ? '<div style="font-size:12px;color:rgba(255,255,255,0.6);margin-top:2px">Margin: ' + pf.margin + '%</div>' : '') + '</div>';
        [['💵 Doanh thu (giá bán)', o.total, '#059669'], ['🔩 Giá vốn linh kiện', cost, '#ef4444']].forEach(function (r) {
            html += '<div style="display:flex;justify-content:space-between;padding:9px 0;border-bottom:1px solid #f3f4f6">' +
                '<span style="font-size:12px;color:#374151">' + r[0] + '</span>' +
                '<span style="font-size:13px;font-weight:800;color:' + r[2] + '">' + (r[1] > 0 ? AM.money(r[1]) : 'Chưa có BOM') + '</span></div>';
        });
        html += '<div style="display:flex;justify-content:space-between;padding:10px 0;margin-top:4px">' +
            '<span style="font-size:13px;font-weight:700">= Lợi nhuận gộp</span>' +
            '<span style="font-size:15px;font-weight:900;color:' + (g && g > 0 ? '#059669' : '#ef4444') + '">' + (g !== null ? AM.money(g) : '—') + '</span></div>';
        if (cost === 0) {
            html += '<div style="background:#fef3c7;border-radius:8px;padding:9px 11px;margin-top:10px;font-size:11px;color:#b45309">⚠ Chưa khai báo linh kiện — sang tab <b>Linh kiện</b> để thêm BOM</div>';
        }
        if (bom.length) {
            html += '<div style="margin-top:14px"><div style="font-size:11px;font-weight:700;color:#6b7280;margin-bottom:8px">CHI TIẾT LINH KIỆN</div>';
            bom.forEach(function (item) {
                var p = AM.part(item.id);
                if (!p) return;
                var line = unitCostOf(o, item) * item.qty * o.qty, pct = cost > 0 ? Math.round(line / cost * 100) : 0;
                html += '<div style="margin-bottom:7px"><div style="display:flex;justify-content:space-between;margin-bottom:3px;font-size:11px">' +
                    '<span style="color:#374151">' + AM.esc(p.name) + ' ×' + (item.qty * o.qty) + '</span>' +
                    '<span style="font-weight:600">' + AM.money(line) + ' (' + pct + '%)</span></div>' +
                    '<div style="background:#f3f4f6;border-radius:3px;height:4px"><div style="width:' + pct + '%;height:100%;background:#0ea5e9;border-radius:3px"></div></div></div>';
            });
            html += '</div>';
        }
        return html;
    }

    function renderDetail() {
        var o = findOrder(state.selected), $d = $('#am-order-detail');
        if (!o) { $d.addClass('am-hidden').empty(); return; }
        var dealer = AM.dealer(o.dealerId) || {};
        var body = state.tab === 'bom' ? renderBomTab(o) : state.tab === 'profit' ? renderProfitTab(o) : renderInfoTab(o);
        var tabs = [['info', '📋 Thông tin'], ['bom', '🔩 Linh kiện'], ['profit', '💹 Lợi nhuận']].map(function (t) {
            return '<button class="am-dtab ' + (state.tab === t[0] ? 'active' : '') + '" data-dtab="' + t[0] + '">' + t[1] + '</button>';
        }).join('');
        $d.html(
            '<div style="padding:14px 16px;border-bottom:1px solid #f3f4f6"><div style="display:flex;justify-content:space-between;align-items:flex-start"><div>' +
            '<div style="font-weight:900;font-size:15px;color:#1a2035">' + o.id + '</div>' +
            '<div style="font-size:11px;color:#9ca3af;margin-top:2px">' + AM.esc(dealer.name) + ' · ' + (o.date.length > 5 ? o.date : o.date + '/2025') + '</div></div>' +
            '<div style="display:flex;gap:4px;align-items:center">' + typeBadge(o.type) + statusBadge(o.status) + '</div></div></div>' +
            '<div class="am-dtabs">' + tabs + '</div>' +
            '<div style="flex:1;overflow:auto;padding:14px 16px">' + body + '</div>'
        ).removeClass('am-hidden');
    }

    /* ---------- Tạo đơn ---------- */
    function canCreate() {
        return AM.can('admin', 'sale', 'kt_banhang');
    }

    function findProduct(key) {
        if (!key) return null;
        if (AM.productCache[key]) return AM.productCache[key];
        return products.filter(function (p) { return p.id === key || p.name === key; })[0] || null;
    }

    /** Tìm sản phẩm theo mã hoặc tên (tham số URL từ trang khác) khi chưa có sẵn trên trang. */
    function resolveProduct(key) {
        var found = findProduct(key);
        if (found || !key) return $.Deferred().resolve(found).promise();
        return AM.lookup('product-search', { q: key }).then(function (res) {
            var rows = AM.cacheProducts(res.rows || []);
            return rows.filter(function (r) { return r.id === key || r.name === key; })[0] || null;
        });
    }

    function creditInfo(dealer, extra) {
        var after = dealer.debt + (extra || 0), pct = dealer.limit > 0 ? Math.round(after / dealer.limit * 100) : 0;
        return { after: after, pct: pct, over: after > dealer.limit };
    }

    function creditBox(dealer, extra) {
        var c = creditInfo(dealer, extra);
        return '<div style="background:' + (c.over ? '#fff5f5' : '#f0fdf4') + ';border:1.5px solid ' + (c.over ? '#fecaca' : '#bbf7d0') +
            ';border-radius:8px;padding:10px 14px;font-size:12px;color:#374151;line-height:1.6">' +
            '<div style="font-weight:700;color:' + (c.over ? '#ef4444' : '#059669') + '">' +
            (c.over ? '⚠ Vượt hạn mức công nợ' : '✓ Trong hạn mức công nợ') + '</div>' +
            'Dư nợ hiện tại <b>' + AM.money(dealer.debt) + '</b> + đơn này <b>' + AM.money(extra || 0) + '</b> = <b>' + AM.money(c.after) + '</b>' +
            ' / hạn mức <b>' + AM.money(dealer.limit) + '</b> (' + c.pct + '%)' +
            (c.over ? '<div style="color:#b45309;margin-top:2px">KT bán hàng cần thu tiền trước khi duyệt đơn.</div>' : '') + '</div>';
    }

    function openCreateOrder(prefill) {
        prefill = prefill || {};
        var dealers = $.map(AM.data.dealers || {}, function (d) { return d; });
        var sales = AM.staffByRole('sale');
        var product = findProduct(prefill.product);
        var type = prefill.type === 'warranty' ? 'warranty' : 'new';
        var lastProduct = product ? product.id : '';

        AM.form.open({
            title: '📋 Tạo đơn hàng mới',
            sub: 'Đơn mới ở trạng thái Chờ duyệt — KT bán hàng duyệt trước khi Sale chọn linh kiện',
            width: 640,
            submitLabel: '💾 Tạo đơn hàng',
            fields: [
                { name: 'code', label: 'Mã đơn', readonly: true },
                { name: 'type', label: 'Loại đơn', type: 'select', required: true, options: [['new', 'Đơn bán mới'], ['warranty', 'Đơn bảo hành (gửi linh kiện thay thế)']] },
                { name: 'dealerId', label: 'Đại lý đặt hàng', type: 'select', required: true, full: true, placeholder: '— Chọn đại lý —',
                    options: dealers.map(function (d) { return [d.id, d.name + ' · ' + d.province]; }) },
                { name: 'product', label: 'Sản phẩm', type: 'picker', source: 'products', required: true, full: true,
                    valueLabel: product ? product.name + ' · ' + product.id : '', placeholder: 'Gõ tên hoặc mã sản phẩm để tìm...' },
                { name: 'qty', label: 'Số lượng (chiếc)', type: 'number', min: 1, required: true },
                { name: 'price', label: 'Đơn giá bán (đ)', type: 'number', min: 0, required: true, hint: 'Tự điền theo giá niêm yết, sửa được nếu có chiết khấu' },
                { name: 'sale', label: 'Sale phụ trách', type: 'select', required: true, placeholder: '— Chọn Sale —', options: sales.map(function (n) { return [n, n]; }) },
                { name: 'warrantyRef', label: 'Mã phiếu khiếu nại', placeholder: 'VD: KC001', hint: 'Chỉ dùng cho đơn bảo hành' },
                { name: 'note', label: 'Ghi chú', type: 'textarea', full: true, placeholder: 'Yêu cầu giao hàng, chiết khấu, điều khoản thanh toán...' }
            ],
            values: {
                code: AM.nextCode(orders, 'DH' + new Date().getFullYear(), 3),
                type: type, dealerId: prefill.dealer || '', product: lastProduct, qty: 1,
                price: type === 'warranty' ? 0 : (product ? product.price : ''),
                sale: prefill.sale || (sales.length ? sales[0] : ''), warrantyRef: prefill.claim || ''
            },
            onChange: function (api, name) {
                var d = api.data(), p = findProduct(d.product), isWarranty = d.type === 'warranty';
                if (name === 'product' && p && !isWarranty) {
                    var prev = findProduct(lastProduct);
                    if (!d.price || (prev && +d.price === prev.price)) api.set('price', p.price);
                    lastProduct = p.id;
                }
                if (name === 'type') api.set('price', isWarranty ? 0 : (p ? p.price : ''));
                api.toggle('warrantyRef', isWarranty);
                d = api.data();
                var total = (+d.qty || 0) * (+d.price || 0), dealer = AM.dealer(d.dealerId), html = '';
                if (p && +d.qty > p.stock && p.stock >= 0) {
                    html += '<div style="font-size:11px;color:#b45309;margin-bottom:8px">⚠ Tồn kho hiện còn ' + p.stock + ' chiếc — phần thiếu cần nhập thêm hoặc lắp ráp.</div>';
                }
                if (total > 0) {
                    html += '<div class="am-row-between" style="background:#f8fafc;border-radius:8px;padding:10px 14px;margin-bottom:8px">' +
                        '<span style="font-size:12px;color:#6b7280">Tổng giá trị đơn</span>' +
                        '<span style="font-weight:900;font-size:15px;color:#059669">' + AM.money(total) + '</span></div>';
                }
                if (dealer && !isWarranty) html += creditBox(dealer, total);
                api.preview(html);
            },
            validate: function (d) {
                var e = {};
                if (!(+d.qty >= 1) || Math.floor(+d.qty) !== +d.qty) e.qty = 'Số lượng phải là số nguyên ≥ 1';
                if (d.type === 'new' && !(+d.price > 0)) e.price = 'Đơn bán mới phải có giá bán lớn hơn 0';
                if (+d.price < 0) e.price = 'Giá không được âm';
                return e;
            },
            onSubmit: function (d) {
                return AM.api('order.create', $.extend({}, d, { lead: prefill.lead || '' })).done(function (data) {
                    var o = applyOrder(data);
                    state.filter = 'all';
                    state.selected = o.id;
                    state.tab = 'info';
                    render();
                });
            }
        });
    }

    function renderFacts(o) {
        var rows = [
            ['🧾 Số hoá đơn', o.invoiceNo], ['📤 Phiếu xuất', o.exportCode], ['🚚 NV giao hàng', o.delivery],
            ['🔢 Serial', (o.serials || []).join(', ')], ['👤 Người nhận', o.receiver ? o.receiver + (o.receiverPhone ? ' · ' + o.receiverPhone : '') : ''],
            ['📅 Ngày giao', o.deliveredAt], ['🛡 Khiếu nại', o.warrantyRef], ['📝 Ghi chú', o.note]
        ].filter(function (r) { return r[1]; });
        if (!rows.length) return '';
        return '<div style="background:#fff;border:1px solid #f3f4f6;border-radius:10px;padding:8px 12px;margin-bottom:12px">' + rows.map(function (r) {
            return '<div style="display:flex;gap:8px;padding:4px 0;font-size:11px"><span style="color:#9ca3af;min-width:92px">' + r[0] + '</span>' +
                '<span style="color:#374151;font-weight:600;word-break:break-word">' + AM.esc(r[1]) + '</span></div>';
        }).join('') + '</div>';
    }

    function openApproveForm(o) {
        var dealer = AM.dealer(o.dealerId), isNew = o.type === 'new';
        var over = dealer && isNew && creditInfo(dealer, o.total).over;
        AM.form.open({
            title: '✓ Duyệt đơn ' + o.id,
            sub: (dealer ? dealer.name + ' · ' : '') + o.product + ' × ' + o.qty + (isNew ? ' · ' + AM.money(o.total) : ' · đơn bảo hành'),
            width: 520,
            submitLabel: over ? '⚠ Vẫn duyệt (vượt hạn mức)' : '✓ Duyệt đơn',
            submitColor: over ? '#ef4444' : '#059669',
            fields: [
                { name: 'invoiceNo', label: 'Số hoá đơn', required: isNew, placeholder: 'VD: 0001234', hint: isNew ? 'Hoá đơn GTGT xuất cho đại lý' : 'Đơn bảo hành không xuất hoá đơn' },
                { name: 'invoiceDate', label: 'Ngày hoá đơn', type: 'date', required: isNew },
                { name: 'note', label: 'Ghi chú duyệt', type: 'textarea', full: true, placeholder: 'Điều kiện thanh toán, lưu ý cho Sale...' }
            ],
            values: { invoiceDate: AM.isoToday(), note: o.note || '' },
            onChange: function (api) { api.toggle('invoiceDate', isNew); api.preview(dealer && isNew ? creditBox(dealer, o.total) : ''); },
            validate: function (v) {
                var e = {};
                if (v.invoiceNo && !/^[A-Za-z0-9\/-]{3,20}$/.test(v.invoiceNo)) e.invoiceNo = 'Số hoá đơn 3–20 ký tự, chỉ gồm chữ, số, / -';
                else if (v.invoiceNo && orders.some(function (x) { return x.id !== o.id && x.invoiceNo === v.invoiceNo; })) e.invoiceNo = 'Số hoá đơn đã dùng cho đơn khác';
                return e;
            },
            onSubmit: function (v) { return runAction('order.approve', o, v); }
        });
    }

    function openExportForm(o) {
        var drivers = AM.staffByRole('delivery'), product = findProduct(o.product), isNew = o.type === 'new';
        var lines = isNew
            ? ['🚜 ' + o.product + ' × ' + o.qty + ' chiếc']
            : bomOf(o).map(function (b) { var p = AM.part(b.id); return '🔩 ' + (p ? p.name : b.id) + ' × ' + b.qty * o.qty; });
        AM.form.open({
            title: '🚚 Xuất kho & giao — ' + o.id,
            sub: 'Tạo phiếu xuất kho' + (isNew ? ' và sinh serial bảo hành cho từng máy' : ' linh kiện bảo hành'),
            width: 560,
            submitLabel: '📤 Xuất kho & giao',
            submitColor: '#f97316',
            fields: [
                { name: 'exportDate', label: 'Ngày xuất', type: 'date', required: true, full: true },
                { name: 'delivery', label: 'Nhân viên giao hàng', type: 'select', required: true, full: true, placeholder: '— Chọn NV giao hàng —', options: drivers.map(function (n) { return [n, n]; }) },
                { name: 'items', type: 'html', html: '<div style="background:#f8fafc;border-radius:8px;padding:10px 12px;font-size:12px;color:#374151;line-height:1.7">' +
                    '<div class="am-label" style="margin-bottom:4px">HÀNG XUẤT</div>' + lines.map(AM.esc).join('<br>') +
                    (isNew ? '<div class="am-label" style="margin:8px 0 4px">SERIAL BẢO HÀNH</div>' +
                        'Hệ thống tự sinh ' + o.qty + ' serial dạng <span style="font-family:monospace">' + AM.esc(product ? product.id : 'SP') + '-XXXX</span> và tạo sổ bảo hành (chưa kích hoạt) cho từng máy' : '') +
                    '</div>' },
                { name: 'note', label: 'Ghi chú giao hàng', type: 'textarea', full: true, placeholder: 'Địa chỉ nhận, giờ hẹn, xe vận chuyển...' }
            ],
            values: { exportDate: AM.isoToday(), delivery: o.delivery || '' },
            onChange: function (api) {
                api.preview(product && isNew && product.stock < o.qty
                    ? '<div style="font-size:11px;color:#ef4444">⚠ Tồn kho ' + o.product + ' chỉ còn ' + product.stock + ' chiếc</div>' : '');
            },
            validate: function (v) {
                return product && isNew && product.stock < o.qty ? { exportDate: 'Không đủ tồn kho để xuất (còn ' + product.stock + ')' } : {};
            },
            onSubmit: function (v) { return runAction('order.export', o, v); }
        });
    }

    function openDeliverForm(o) {
        var dealer = AM.dealer(o.dealerId), isNew = o.type === 'new';
        var comSale = Math.round(o.total * RATES.sale / 100), comDeli = Math.round(o.total * RATES.delivery / 100);
        AM.form.open({
            title: '✓ Xác nhận đã giao — ' + o.id,
            sub: (dealer ? dealer.name + ' · ' : '') + (o.exportCode ? 'Phiếu ' + o.exportCode : ''),
            width: 520,
            submitLabel: '✓ Hoàn thành đơn',
            submitColor: '#10b981',
            fields: [
                { name: 'receiver', label: 'Người nhận hàng', required: true, placeholder: 'VD: Anh Hùng (chủ đại lý)' },
                { name: 'receiverPhone', label: 'SĐT người nhận', type: 'tel' },
                { name: 'deliveredDate', label: 'Ngày giao', type: 'date', required: true },
                { name: 'note', label: 'Ghi chú', placeholder: 'Tình trạng hàng khi giao...' }
            ],
            values: { deliveredDate: AM.isoToday() },
            onChange: function (api) {
                if (!isNew) { api.preview(''); return; }
                api.preview('<div style="background:#f8fafc;border-radius:8px;padding:10px 14px;font-size:12px;color:#374151;line-height:1.8">' +
                    '<div class="am-label" style="margin-bottom:2px">KHI HOÀN THÀNH SẼ GHI NHẬN</div>' +
                    '💰 Công nợ ' + AM.esc(dealer ? AM.shortDealer(dealer.name) : '') + ': <b>' + AM.money(dealer ? dealer.debt : 0) + '</b> → <b style="color:#ef4444">' + AM.money((dealer ? dealer.debt : 0) + o.total) + '</b><br>' +
                    '👔 HH Sale ' + AM.esc(o.sale) + ' (' + RATES.sale + '%): <b style="color:#7c3aed">' + AM.money(comSale) + '</b><br>' +
                    '🚚 HH giao hàng ' + AM.esc(o.delivery || '') + ' (' + RATES.delivery + '%): <b style="color:#0891b2">' + AM.money(comDeli) + '</b></div>');
            },
            validate: function (v) {
                return v.receiverPhone && !AM.isPhone(v.receiverPhone) ? { receiverPhone: 'Số điện thoại không hợp lệ' } : {};
            },
            onSubmit: function (v) { return runAction('order.deliver', o, v); }
        });
    }

    function openCancelForm(o) {
        AM.form.open({
            title: '✕ Huỷ đơn ' + o.id,
            sub: 'Đơn huỷ không thể khôi phục — đại lý cần đặt đơn mới',
            width: 480,
            submitLabel: '✕ Xác nhận huỷ',
            submitColor: '#ef4444',
            fields: [
                { name: 'reason', label: 'Lý do huỷ', type: 'select', required: true, full: true, placeholder: '— Chọn lý do —', options: CANCEL_REASONS.map(function (r) { return [r, r]; }) },
                { name: 'detail', label: 'Chi tiết', type: 'textarea', full: true, placeholder: 'Bắt buộc khi chọn "Khác"' }
            ],
            validate: function (v) { return v.reason === 'Khác' && !v.detail ? { detail: 'Vui lòng ghi rõ lý do' } : {}; },
            onSubmit: function (v) { return runAction('order.cancel', o, v); }
        });
    }

    var FORMS = { approve: openApproveForm, export: openExportForm, deliver: openDeliverForm };

    function render() {
        renderFilters();
        renderRows();
        renderDetail();
    }

    $(function () {
        render();

        $('#am-order-filters').on('click', '[data-action="new-order"]', function () { openCreateOrder(); });

        if (AM.query('new') && canCreate()) {
            resolveProduct(AM.query('product')).always(function (p) {
                openCreateOrder({
                    dealer: AM.query('dealer'), product: p ? p.id : '', type: AM.query('type'),
                    claim: AM.query('claim'), lead: AM.query('lead')
                });
            });
        }

        $('#am-order-filters').on('click', '[data-filter]', function () {
            state.filter = $(this).data('filter');
            render();
        });

        $('#am-order-rows').on('click', 'tr[data-id]', function () {
            var id = $(this).data('id');
            state.selected = id === state.selected ? null : id;
            state.tab = 'info';
            render();
        });

        $('#am-order-detail')
            .on('click', '[data-dtab]', function () {
                state.tab = $(this).data('dtab');
                renderDetail();
            })
            .on('click', '[data-order-action]', function () {
                var a = ACTIONS[$(this).data('order-action')], o = findOrder(state.selected);
                if (a.form) return FORMS[a.form](o);
                if (a.tab) {
                    state.tab = a.tab;
                    return render();
                }
                var $btn = $(this).prop('disabled', true).css('opacity', .6);
                var payload = a.to === 'assembling' ? { bom: bomOf(o).map(function (b) { return { id: b.id, qty: b.qty }; }) } : {};
                runAction(a.to === 'assembling' ? 'order.send' : 'order.assembled', o, payload).fail(function () {
                    $btn.prop('disabled', false).css('opacity', '');
                });
            })
            .on('click', '[data-action="cancel-order"]', function () { openCancelForm(findOrder(state.selected)); })
            .on('click', '[data-bom="default"]', function () {
                var o = findOrder(state.selected), def = AM.data.defaultBoms[o.product];
                if (def) setBom(o.id, def.map(function (b) { return { id: b.id, qty: b.qty }; }));
                render();
            })
            .on('click', '[data-add-part]', function () {
                var o = findOrder(state.selected), id = $(this).data('add-part'), bom = bomOf(o);
                if (!bom.some(function (b) { return b.id === id; })) setBom(o.id, bom.concat([{ id: id, qty: 1 }]));
                render();
            })
            .on('click', '[data-bom-remove]', function () {
                var o = findOrder(state.selected), id = $(this).data('bom-remove');
                setBom(o.id, bomOf(o).filter(function (b) { return b.id !== id; }));
                render();
            })
            .on('change', '[data-bom-qty]', function () {
                var o = findOrder(state.selected), id = $(this).data('bom-qty'), qty = +this.value || 1;
                setBom(o.id, bomOf(o).map(function (b) { return b.id === id ? { id: b.id, qty: qty } : b; }));
                render();
            })
            .on('input', '#am-part-search', function () {
                state.search = this.value;
                $('#am-part-list').html(renderPickList(findOrder(state.selected)));
            });
    });
})(jQuery, window.AM);
