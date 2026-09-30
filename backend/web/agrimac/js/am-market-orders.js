/* AgriMac – Đơn hàng sàn 1kho (bảng `order`): danh sách phân trang, chi tiết đầy đủ, cập nhật trạng thái */
(function ($, AM) {
    'use strict';

    var cfg = window.AM_MARKET || {};
    var STATUS = cfg.statuses || {}, PAYMENT = cfg.payments || {};
    var state = { scope: 'dealer', status: '', payment: '', q: '', page: 1, selected: null, detail: null, counts: cfg.counts || {}, loaded: false };
    var listReq = null, detailReq = null, searchTimer = null;

    function statusBadge(s) {
        var st = STATUS[s];
        return st ? AM.badge(st.label, st.color, st.bg) : AM.badge('Không rõ (' + s + ')', '#6b7280', '#f1f5f9');
    }

    function paymentShort(p) {
        return +p === 2 ? 'COD' : +p === 1 ? 'Chuyển khoản' : '—';
    }

    function vnd(n) {
        return (+n || 0).toLocaleString('vi-VN') + 'đ';
    }

    /* ---------- Chuyển tab Đơn đại lý / Đơn sàn ---------- */
    function setScope(scope) {
        state.scope = scope;
        $('#am-order-scope [data-scope]').each(function () { $(this).toggleClass('active', $(this).data('scope') === scope); });
        $('#am-scope-dealer').css('display', scope === 'dealer' ? 'flex' : 'none');
        $('#am-scope-market').css('display', scope === 'market' ? 'flex' : 'none');
        var url = new URL(window.location.href);
        if (scope === 'market') url.searchParams.set('scope', 'market'); else url.searchParams.delete('scope');
        if (scope !== 'market' || !state.selected) url.searchParams.delete('id');
        window.history.replaceState(null, '', url.toString());
        if (scope === 'market' && !state.loaded) load(1);
    }

    /* ---------- Danh sách ---------- */
    function renderFilters() {
        var total = 0;
        $.each(state.counts, function (k, n) { total += +n || 0; });
        $('#am-market-total').text(total);
        var allOn = state.status === '';
        var html = '<button class="am-filter" data-mstatus="" style="' +
            (allOn ? 'border-color:#0f2027;background:#0f2027;color:#fbbf24' : '') + '">Tất cả (' + total + ')</button>';
        $.each(STATUS, function (key, s) {
            var n = +state.counts[key] || 0, on = state.status === String(key);
            html += '<button class="am-filter" data-mstatus="' + key + '" style="' +
                (on ? 'border-color:' + s.color + ';background:' + s.bg + ';color:' + s.color : '') + '">' + AM.esc(s.label) + ' ' +
                (n > 0 ? '<span class="am-filter-count" style="background:' + s.color + '">' + n + '</span>' : '') + '</button>';
        });
        $('#am-market-filters').html(html);
    }

    function renderRows(res) {
        var rows = res.rows || [];
        if (!rows.length) {
            $('#am-market-rows').html('<tr><td colspan="9" class="am-empty" style="padding:30px">Không có đơn hàng phù hợp</td></tr>');
        } else {
            $('#am-market-rows').html(rows.map(function (o, i) {
                var sel = o.id === state.selected;
                return '<tr class="am-clickable" data-mid="' + o.id + '" style="background:' + (sel ? '#fffbeb' : (i % 2 === 0 ? '#fff' : '#fafafa')) +
                    ';border-left:3px solid ' + (sel ? '#f59e0b' : 'transparent') + '">' +
                    '<td><span style="font-weight:800;font-size:12px">' + AM.esc(o.code) + '</span></td>' +
                    '<td><div style="font-weight:600;font-size:12px">' + AM.esc(o.customer || '—') + '</div>' +
                    '<div style="font-size:10px;color:#9ca3af">' + AM.esc(o.phone) + '</div></td>' +
                    '<td style="font-size:12px;max-width:220px"><div style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap" title="' + AM.esc(o.product) + '">' + AM.esc(o.product || '—') + '</div>' +
                    (o.lines > 1 ? '<div style="font-size:10px;color:#9ca3af">+' + (o.lines - 1) + ' sản phẩm khác</div>' : '') + '</td>' +
                    '<td style="text-align:center;font-weight:700">' + o.qty + '</td>' +
                    '<td><span style="font-weight:700;color:#059669">' + vnd(o.total) + '</span></td>' +
                    '<td class="am-mcol-opt" style="font-size:11px;white-space:nowrap">' + AM.esc(paymentShort(o.payment)) + '</td>' +
                    '<td class="am-mcol-opt" style="font-size:12px;max-width:130px"><div style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap" title="' + AM.esc(o.agent) + '">' + AM.esc(o.agent) + '</div></td>' +
                    '<td style="font-size:11px;color:#6b7280;line-height:1.3">' + AM.esc(o.date || '').replace(' ', '<br>') + '</td>' +
                    '<td style="white-space:nowrap">' + statusBadge(o.status) + '</td></tr>';
            }).join(''));
        }
        $('#am-market-pager').html(res.pages > 1 || res.total > 0 ? AM.pagerHtml(res.page, res.pages, res.total, 'đơn hàng') : '');
    }

    function load(page) {
        if (listReq) listReq.abort();
        state.page = page || 1;
        listReq = AM.lookup('market-orders', { q: state.q, status: state.status, payment: state.payment, page: state.page })
            .done(function (res) {
                state.loaded = true;
                state.page = res.page;
                if (res.counts) state.counts = res.counts;
                renderFilters();
                renderRows(res);
            })
            .always(function () { listReq = null; });
    }

    /* ---------- Chi tiết ---------- */
    function section(title, body) {
        return '<div style="margin-bottom:14px"><div class="am-label" style="margin-bottom:7px">' + title + '</div>' + body + '</div>';
    }

    function facts(rows) {
        rows = rows.filter(function (r) { return r[1] !== null && r[1] !== undefined && r[1] !== ''; });
        if (!rows.length) return '<div style="font-size:12px;color:#9ca3af">—</div>';
        return '<div style="background:#fff;border:1px solid #f3f4f6;border-radius:10px;padding:8px 12px">' + rows.map(function (r) {
            return '<div style="display:flex;gap:8px;padding:4px 0;font-size:12px;border-bottom:1px dashed #f3f4f6">' +
                '<span style="color:#6b7280;min-width:118px;flex-shrink:0">' + AM.esc(r[0]) + '</span>' +
                '<span style="font-weight:600;color:' + (r[2] || '#1a2035') + ';word-break:break-word">' + (r[3] ? r[1] : AM.esc(r[1])) + '</span></div>';
        }).join('') + '</div>';
    }

    function itemsHtml(items) {
        if (!items.length) return '<div style="font-size:12px;color:#9ca3af">Không có sản phẩm</div>';
        return items.map(function (it) {
            var discount = it.priceOrigin > it.price;
            return '<div style="display:flex;gap:10px;padding:8px;background:#f8fafc;border-radius:10px;margin-bottom:6px">' +
                (it.image
                    ? '<img src="' + AM.esc(it.image) + '" alt="" style="width:46px;height:46px;border-radius:8px;object-fit:cover;flex-shrink:0" onerror="this.onerror=null;this.src=AM.NO_IMAGE">'
                    : '<div style="width:46px;height:46px;border-radius:8px;background:#0f2027;display:flex;align-items:center;justify-content:center;flex-shrink:0">🚜</div>') +
                '<div style="flex:1;min-width:0">' +
                '<div style="font-weight:700;font-size:12px;line-height:1.3">' + AM.esc(it.name) + (it.deleted ? ' <span style="color:#ef4444;font-size:10px">(đã xoá)</span>' : '') + '</div>' +
                '<div style="font-size:10px;color:#9ca3af">' + AM.esc(it.code || ('#' + it.productId)) + (it.classificationId ? ' · phân loại #' + it.classificationId : '') + '</div>' +
                '<div style="font-size:11px;margin-top:2px">' + vnd(it.price) +
                (discount ? ' <s style="color:#9ca3af;font-size:10px">' + vnd(it.priceOrigin) + '</s>' : '') +
                ' × ' + it.qty + ' = <b style="color:#059669">' + vnd(it.total) + '</b></div></div></div>';
        }).join('');
    }

    function refundsHtml(refunds) {
        return refunds.map(function (r) {
            var color = r.status === 1 ? '#059669' : r.status === 2 ? '#ef4444' : '#f59e0b';
            var actions = r.status === 0
                ? '<div style="display:flex;gap:6px;margin-top:6px"><button type="button" class="am-btn-block" data-refund="approve" data-rid="' + r.id + '" style="background:#059669">✓ Đồng ý hoàn tiền</button>' +
                  '<button type="button" class="am-btn-block" data-refund="reject" data-rid="' + r.id + '" style="background:#fff;color:#ef4444;border:1.5px solid #fecaca">✕ Từ chối</button></div>'
                : '';
            return facts([
                ['Yêu cầu #' + r.id, r.statusLabel, color],
                ['Số tiền hoàn', vnd(r.amount), '#8b5cf6'],
                ['Tình huống', r.situation], ['Lý do', r.reason], ['Ghi chú', r.note],
                ['Lý do từ chối', r.rejectReason, '#ef4444'],
                ['Thời gian yêu cầu', r.createdAt], ['Thời gian xử lý', r.processedAt]
            ]) + actions;
        }).join('<div style="height:6px"></div>');
    }

    function renderDetail() {
        var o = state.detail, $d = $('#am-market-detail');
        $('#am-market-table').toggleClass('am-market-compact', !!state.selected);
        if (!state.selected) { $d.addClass('am-hidden').empty(); return; }
        if (!o) {
            $d.html('<div class="am-empty" style="padding:40px;font-size:13px">Đang tải đơn hàng...</div>').removeClass('am-hidden');
            return;
        }
        var m = o.money, ship = o.shipping;
        var html =
            section('👤 KHÁCH HÀNG', facts([
                ['Họ tên', o.customer.name], ['Số điện thoại', o.customer.phone], ['Địa chỉ tài khoản', o.customer.address],
                ['Điểm ví hiện có', o.customer.walletPoint ? o.customer.walletPoint.toLocaleString('vi-VN') + ' điểm' : '0']
            ])) +
            section('🚚 ĐỊA CHỈ GIAO HÀNG', ship ? facts([['Người nhận', ship.name], ['Số điện thoại', ship.phone], ['Địa chỉ', ship.address]])
                : facts([['Địa chỉ', o.deliveryAddressId ? 'Địa chỉ #' + o.deliveryAddressId + ' (đã bị xoá)' : 'Không có', '#9ca3af']])) +
            section('📦 SẢN PHẨM (' + o.items.length + ')', itemsHtml(o.items)) +
            section('💵 THANH TOÁN', facts([
                ['Tổng giá gốc', vnd(m.price)],
                ['Phí vận chuyển', vnd(m.feeShip)],
                ['Giảm voucher', m.voucher ? '−' + vnd(m.voucher) : '0đ', m.voucher ? '#ef4444' : null],
                ['Thanh toán bằng ví', (o.useWallet ? 'Có' : 'Không') + (m.wallet ? ' · −' + m.wallet.toLocaleString('vi-VN') + ' điểm' : ''), m.wallet ? '#ef4444' : null],
                ['Tổng tiền đơn hàng', vnd(m.total), '#059669'],
                ['Phương thức', PAYMENT[o.payment] || '—'],
                ['Thời gian thanh toán', o.paidAt]
            ])) +
            section('🎟 VOUCHER', o.voucher ? facts([
                ['Voucher', o.voucher.name + (o.voucher.deleted ? ' (đã xoá)' : '')],
                ['Mã voucher', '#' + o.voucher.id],
                ['Xu hoàn khi giao xong', o.voucher.pointRefundable ? o.voucher.pointRefundable.toLocaleString('vi-VN') + ' xu' : '0']
            ]) : '<div style="font-size:12px;color:#9ca3af">Không dùng voucher</div>') +
            section('🏪 ĐẠI LÝ BÁN', o.agent ? facts([['Đại lý', o.agent.name], ['Số điện thoại', o.agent.phone], ['Địa chỉ', o.agent.address]])
                : '<div style="font-size:12px;color:#6b7280;font-weight:600">1KHO bán trực tiếp</div>') +
            (o.cancel ? section('✕ HỦY ĐƠN', facts([['Người hủy', o.cancel.by], ['Lý do', o.cancel.reason], ['Thời gian hủy', o.cancel.at]])) : '') +
            (o.refunds.length ? section('↩ YÊU CẦU TRẢ HÀNG / HOÀN TIỀN (' + o.refunds.length + ')', refundsHtml(o.refunds)) : '') +
            section('🕒 KHÁC', facts([
                ['Ngày tạo', o.createdAt], ['Cập nhật lần cuối', o.updatedAt],
                ['Khách đã đánh giá', o.reviewed ? 'Đã đánh giá' : 'Chưa'],
                ['Ghi chú', o.cancel ? '' : o.note]
            ]));

        $d.html(
            '<div style="padding:14px 16px;border-bottom:1px solid #f3f4f6"><div style="display:flex;justify-content:space-between;align-items:flex-start;gap:8px"><div>' +
            '<div style="font-weight:900;font-size:15px;color:#1a2035">Đơn ' + AM.esc(o.code) + '</div>' +
            '<div style="font-size:11px;color:#9ca3af;margin-top:2px">' + AM.esc(o.customer.name || 'Khách') + ' · ' + AM.esc(o.createdAt || '') + '</div></div>' +
            '<div style="display:flex;gap:4px;align-items:center">' + statusBadge(o.status) +
            '<button type="button" class="am-btn am-btn-sm" data-mclose style="background:#f1f5f9;color:#374151">✕</button></div></div></div>' +
            '<div style="flex:1;overflow:auto;padding:14px 16px">' + html + '</div>' +
            '<div style="padding:12px 16px;border-top:1px solid #f3f4f6"><button type="button" class="am-btn-block" data-mstatus-edit style="width:100%;background:#0f2027;color:#fbbf24">✎ Cập nhật trạng thái</button></div>'
        ).removeClass('am-hidden');
    }

    function select(id) {
        state.selected = id;
        state.detail = null;
        $('#am-market-rows tr[data-mid]').each(function () {
            var on = +$(this).data('mid') === id;
            $(this).css({ background: on ? '#fffbeb' : '', borderLeft: '3px solid ' + (on ? '#f59e0b' : 'transparent') });
        });
        var url = new URL(window.location.href);
        if (id) url.searchParams.set('id', id); else url.searchParams.delete('id');
        window.history.replaceState(null, '', url.toString());
        renderDetail();
        if (!id) return;
        if (detailReq) detailReq.abort();
        detailReq = AM.lookup('market-order', { id: id }).done(function (res) {
            if (state.selected !== id) return;
            if (!res.row) { AM.toast(res.message || 'Đơn hàng không tồn tại', false); state.selected = null; }
            state.detail = res.row || null;
            renderDetail();
        }).always(function () { detailReq = null; });
    }

    function applyResult(data) {
        state.detail = data.marketOrder;
        if (data.counts) state.counts = data.counts;
        renderDetail();
        load(state.page);
    }

    function processRefund(rid, decision) {
        var o = state.detail;
        if (decision === 'approve') {
            AM.confirm({
                title: '✓ Đồng ý trả hàng / hoàn tiền?',
                message: 'Đơn ' + AM.esc(o.code) + ' sẽ chuyển sang trạng thái "Hoàn tiền". Nhớ chuyển khoản hoàn tiền cho khách.',
                okLabel: 'Đồng ý hoàn tiền',
                okColor: '#059669',
                onOk: function () { AM.api('market.refund', { refundId: rid, decision: 'approve' }).done(applyResult); }
            });
            return;
        }
        AM.form.open({
            title: '✕ Từ chối yêu cầu hoàn tiền',
            sub: 'Đơn ' + o.code,
            width: 440,
            submitLabel: 'Từ chối',
            fields: [{ name: 'note', label: 'Lý do từ chối (khách sẽ thấy)', type: 'textarea', required: true, full: true }],
            onSubmit: function (v) { return AM.api('market.refund', { refundId: rid, decision: 'reject', note: v.note }).done(applyResult); }
        });
    }

    function openStatusForm(o) {
        AM.form.open({
            title: '✎ Cập nhật trạng thái đơn ' + o.code,
            sub: 'Trạng thái hiện tại: ' + (STATUS[o.status] ? STATUS[o.status].label : o.status),
            width: 460,
            submitLabel: '💾 Cập nhật',
            fields: [
                { name: 'status', label: 'Trạng thái', type: 'select', required: true, full: true, placeholder: '— Chọn trạng thái —',
                  options: $.map(STATUS, function (s, k) { return [[k, s.label]]; }) },
                { name: 'note', label: 'Ghi chú / lý do hủy', type: 'text', full: true, placeholder: 'Bắt buộc khi hủy đơn',
                  hint: 'Chọn "Đã mua hàng" sẽ ghi thời gian thanh toán (tính hạn trả hàng 10 ngày). Chọn "Hủy" ghi người hủy là 1Kho.' }
            ],
            values: { status: String(o.status), note: o.note || '' },
            validate: function (v) {
                return String(v.status) === '5' && !$.trim(v.note) ? { note: 'Vui lòng nhập lý do hủy' } : {};
            },
            onSubmit: function (v) {
                return AM.api('market.status', { id: o.id, status: v.status, note: v.note }).done(function (data) {
                    state.detail = data.marketOrder;
                    if (data.counts) state.counts = data.counts;
                    renderDetail();
                    load(state.page);
                });
            }
        });
    }

    $(function () {
        if (!$('#am-scope-market').length) return;
        renderFilters();

        $('#am-order-scope').on('click', '[data-scope]', function () { setScope($(this).data('scope')); });

        $('#am-market-filters').on('click', '[data-mstatus]', function () {
            state.status = String($(this).data('mstatus'));
            renderFilters();
            load(1);
        });
        $('#am-market-payment').on('change', function () { state.payment = this.value; load(1); });
        $('#am-market-search').on('input', function () {
            var q = $.trim(this.value);
            clearTimeout(searchTimer);
            searchTimer = setTimeout(function () { state.q = q; load(1); }, 300);
        });
        $('#am-market-pager').on('click', '[data-page]', function () {
            if (!$(this).prop('disabled')) load(+$(this).data('page'));
        });
        $('#am-market-rows').on('click', 'tr[data-mid]', function () {
            var id = +$(this).data('mid');
            select(id === state.selected ? null : id);
        });
        $('#am-market-detail')
            .on('click', '[data-mclose]', function () { select(null); })
            .on('click', '[data-mstatus-edit]', function () { if (state.detail) openStatusForm(state.detail); })
            .on('click', '[data-refund]', function () { if (state.detail) processRefund(+$(this).data('rid'), $(this).data('refund')); });

        if (AM.query('scope') === 'market') {
            var id = parseInt(AM.query('id'), 10);
            setScope('market');
            if (id > 0) select(id);
        }
    });
})(jQuery, window.AM);
