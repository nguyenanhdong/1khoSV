/* AgriMac – CRM / Pipeline */
(function ($, AM) {
    'use strict';

    var leads = window.AM_LEADS || [];
    var stages = AM.data.leadStages;
    var selectedId = null;

    function findLead(id) {
        return leads.filter(function (l) { return l.id === id; })[0] || null;
    }

    function renderBoard() {
        var html = '';
        $.each(stages, function (stage, st) {
            var list = leads.filter(function (l) { return l.stage === stage; });
            var sum = list.reduce(function (s, l) { return s + l.value; }, 0);
            html += '<div class="am-kanban-col">' +
                '<div class="am-kanban-head" style="background:' + st.bg + ';border:1.5px solid ' + st.color + '50">' +
                '<div style="font-weight:700;color:' + st.color + ';font-size:12px">' + AM.esc(stage) + '</div>' +
                '<div style="font-size:10px;color:#9ca3af;margin-top:2px">' + list.length + ' khách · ' + AM.money(sum) + '</div></div>' +
                '<div class="am-kanban-body" style="border:1.5px solid ' + st.color + '20">';
            list.forEach(function (l) {
                var sel = l.id === selectedId;
                html += '<div class="am-lead-card" data-id="' + l.id + '" style="' +
                    (sel ? 'border-color:' + st.color + ';box-shadow:0 3px 10px ' + st.color + '25' : '') + '">' +
                    '<div style="font-weight:700;font-size:13px;margin-bottom:2px">' + AM.esc(l.name) + '</div>' +
                    '<div style="font-size:11px;color:#6b7280;margin-bottom:2px">' + AM.esc(l.phone) + '</div>' +
                    '<div style="font-size:11px;color:#9ca3af;margin-bottom:6px">🚜 ' + AM.esc(l.product) + '</div>' +
                    '<div class="am-row-between"><span style="font-size:12px;font-weight:700;color:#059669">' + AM.money(l.value) + '</span>' +
                    '<span style="font-size:10px;color:#9ca3af">' + AM.esc(AM.lastWord(l.sale)) + '</span></div>' +
                    '<div style="font-size:10px;color:#9ca3af;margin-top:4px">📝 ' + l.notes.length + ' ghi chú</div></div>';
            });
            if (!list.length) {
                html += '<div class="am-empty" style="font-size:11px;padding:16px">Trống</div>';
            }
            html += '</div></div>';
        });
        html += '<div style="flex-shrink:0"><button data-action="new-lead" title="Thêm khách tiềm năng" style="width:38px;height:38px;border-radius:10px;background:#fff;border:2px dashed #e5e7eb;cursor:pointer;color:#9ca3af;font-size:18px">+</button></div>';
        $('#am-crm-board').html(html);
    }

    function renderDetail() {
        var l = findLead(selectedId), $d = $('#am-crm-detail');
        if (!l) { $d.addClass('am-hidden').empty(); return; }
        var dealer = l.dealer ? AM.dealer(l.dealer) : null;
        var html = '<div class="am-row-between" style="align-items:flex-start;margin-bottom:14px"><div>' +
            '<div style="font-weight:800;font-size:15px">' + AM.esc(l.name) + '</div>' +
            '<div style="font-size:12px;color:#6b7280">' + AM.esc(l.phone) + '</div></div>' +
            '<button class="am-close" data-action="close">✕</button></div>' +
            '<div style="display:grid;grid-template-columns:1fr 1fr;gap:7px;margin-bottom:14px">' +
            AM.info('🚜', 'Sản phẩm', l.product) + AM.info('💰', 'Giá trị', AM.money(l.value)) +
            AM.info('👔', 'Sale', AM.lastWord(l.sale)) + AM.info('🏪', 'Đại lý', dealer ? AM.shortDealer(dealer.name) : 'Direct') +
            '</div><div style="margin-bottom:12px"><div class="am-label" style="margin-bottom:7px">GIAI ĐOẠN</div>' +
            '<div style="display:flex;flex-wrap:wrap;gap:5px">';
        $.each(stages, function (stage, st) {
            var on = stage === l.stage;
            html += '<button class="am-stage-btn" data-stage="' + AM.esc(stage) + '" style="' +
                (on ? 'border-color:' + st.color + ';background:' + st.bg + ';color:' + st.color : '') + '">' + AM.esc(stage) + '</button>';
        });
        html += '</div></div><div style="margin-bottom:12px"><div class="am-label" style="margin-bottom:7px">LỊCH SỬ TRAO ĐỔI</div>' +
            '<div style="display:flex;flex-direction:column;gap:7px">';
        l.notes.forEach(function (n) {
            html += '<div class="am-note"><div style="font-size:10px;color:#9ca3af;margin-bottom:2px">' + AM.esc(n.d) + '</div>' +
                '<div style="font-size:12px;color:#374151;line-height:1.4">' + AM.esc(n.t) + '</div></div>';
        });
        html += '</div></div>' +
            '<textarea id="am-crm-note" class="am-textarea" placeholder="Thêm ghi chú trao đổi..." style="height:64px;padding:7px 9px;margin-bottom:8px"></textarea>' +
            '<div style="display:flex;gap:7px"><button class="am-btn" data-action="save-note">💾 Lưu ghi chú</button>' +
            (AM.canOpen('orders') ? '<button class="am-btn am-btn-green" data-action="create-order">📋 Tạo đơn hàng</button>' : '') + '</div>';
        $d.html(html).removeClass('am-hidden');
    }

    function render() { renderBoard(); renderDetail(); }

    function productOf(code) {
        return AM.productCache[code] || null;
    }

    function openLeadForm() {
        var dealers = $.map(AM.data.dealers || {}, function (d) { return d; });
        var sales = AM.data.leadSale ? [AM.data.leadSale] : AM.staffByRole('sale');
        var lastProduct = '';
        AM.form.open({
            title: '💬 Thêm khách tiềm năng',
            sub: 'Khách mới vào cột giai đoạn đã chọn trên pipeline',
            width: 600,
            submitLabel: '💾 Lưu khách hàng',
            fields: [
                { name: 'name', label: 'Tên khách / tổ chức', required: true, full: true, placeholder: 'VD: Ông Nguyễn Văn Nam / HTX Đông Bình' },
                { name: 'phone', label: 'Số điện thoại', type: 'tel', required: true, placeholder: 'VD: 0901.111.222' },
                { name: 'dealer', label: 'Đại lý giới thiệu', type: 'select', placeholder: 'Khách trực tiếp (không qua đại lý)',
                    options: dealers.map(function (d) { return [d.id, d.name]; }) },
                { name: 'product', label: 'Sản phẩm quan tâm', type: 'picker', source: 'products', required: true, full: true, placeholder: 'Gõ tên hoặc mã sản phẩm...' },
                { name: 'value', label: 'Giá trị dự kiến (đ)', type: 'number', min: 0, required: true, hint: 'Tự điền theo giá sản phẩm, sửa nếu mua nhiều máy' },
                { name: 'sale', label: 'Sale phụ trách', type: 'select', required: true, placeholder: '— Chọn Sale —', options: sales.map(function (n) { return [n, n]; }) },
                { name: 'stage', label: 'Giai đoạn', type: 'select', required: true, options: Object.keys(stages).map(function (k) { return [k, k]; }) },
                { name: 'note', label: 'Ghi chú lần tiếp cận đầu', type: 'textarea', full: true, placeholder: 'Nhu cầu, diện tích canh tác, thời điểm cần máy...' }
            ],
            values: { sale: sales[0] || '', stage: Object.keys(stages)[0] },
            onChange: function (api, name) {
                if (name !== 'product') return;
                var p = productOf(api.get('product')), prev = productOf(lastProduct), cur = +api.get('value');
                if (p && (!cur || (prev && cur === prev.price))) api.set('value', p.price);
                lastProduct = p ? p.id : '';
            },
            validate: function (d) {
                var e = {};
                if (d.phone && !AM.isPhone(d.phone)) e.phone = 'Số điện thoại không hợp lệ (10–11 số, bắt đầu bằng 0)';
                else if (leads.some(function (l) { return l.phone.replace(/\D/g, '') === d.phone.replace(/\D/g, ''); })) e.phone = 'Số điện thoại đã có trong pipeline';
                if (+d.value < 0) e.value = 'Giá trị không được âm';
                return e;
            },
            onSubmit: function (d) {
                return AM.api('lead.save', d).done(function (data) {
                    leads.push(data.lead);
                    selectedId = data.lead.id;
                    render();
                });
            }
        });
    }

    $(function () {
        render();

        $('#am-crm-board').on('click', '[data-action="new-lead"]', openLeadForm);

        $('#am-crm-board').on('click', '.am-lead-card', function () {
            var id = $(this).data('id');
            selectedId = id === selectedId ? null : id;
            render();
        });

        $('#am-crm-detail')
            .on('click', '[data-action="close"]', function () { selectedId = null; render(); })
            .on('click', '.am-stage-btn', function () {
                var stage = $(this).data('stage');
                if (stage === findLead(selectedId).stage) return;
                AM.api('lead.stage', { id: selectedId, stage: stage }).done(function (data) {
                    $.extend(findLead(selectedId), data.lead);
                    render();
                });
            })
            .on('click', '[data-action="create-order"]', function () {
                var l = findLead(selectedId);
                window.location.href = AM.url('orders', { new: 1, lead: l.id, dealer: l.dealer || '', product: l.product });
            })
            .on('click', '[data-action="save-note"]', function () {
                var text = $.trim($('#am-crm-note').val());
                if (!text) return;
                AM.api('lead.note', { id: selectedId, text: text }).done(function (data) {
                    $.extend(findLead(selectedId), data.lead);
                    render();
                });
            });
    });
})(jQuery, window.AM);
