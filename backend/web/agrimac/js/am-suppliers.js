/* AgriMac – Nhà cung cấp: danh sách, thêm/sửa, đặt nhập hàng, yêu cầu trả hàng */
(function ($, AM) {
    'use strict';

    var cfg = window.AM_SUPPLIER_PAGE || {};
    var suppliers = cfg.suppliers || [], products = cfg.products || [], parts = cfg.parts || [];
    var RETURN_STATUS = {
        pending: ['Chờ gửi', '#b45309', '#fef3c7'],
        sent: ['Đã gửi NCC', '#2563eb', '#dbeafe'],
        done: ['NCC đã nhận', '#059669', '#d1fae5'],
        rejected: ['Bị từ chối', '#ef4444', '#fee2e2']
    };
    var NEXT_STATUS = { pending: ['sent', '✈ Đã gửi'], sent: ['done', '✓ NCC nhận'] };

    function byId(list, id) {
        return list.filter(function (x) { return x.id === id; })[0] || null;
    }

    function itemOf(type, id) {
        return type === 'part' ? byId(parts, id) : (AM.productCache[id] || byId(products, id));
    }

    function renderGrid() {
        $('#am-supplier-grid').html(suppliers.map(function (s) {
            var supplied = products.filter(function (p) { return p.supId === s.id; });
            var returns = s.returns || [], pending = returns.filter(function (r) { return r.status === 'pending'; }).length;
            return '<div class="am-card-lg" style="overflow:hidden">' +
                '<div class="am-dark-gradient" style="padding:16px 18px;display:flex;justify-content:space-between;align-items:flex-start"><div>' +
                '<div style="font-weight:800;color:#fff;font-size:15px">' + AM.esc(s.name) + '</div>' +
                '<div style="font-size:11px;color:rgba(255,255,255,0.5);margin-top:3px">' + AM.esc(s.id) + (s.taxCode ? ' · MST ' + AM.esc(s.taxCode) : '') + '</div></div>' +
                '<button data-edit="' + s.id + '" title="Sửa" style="background:rgba(255,255,255,0.12);border:none;color:#fff;border-radius:6px;padding:4px 8px;cursor:pointer;font-size:11px">✏️</button></div>' +
                '<div style="padding:14px 16px">' +
                '<div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:12px">' +
                AM.info('📞', 'Liên hệ', s.contact) + AM.info('📍', 'Địa chỉ', s.address) + '</div>' +
                (s.email ? '<div style="font-size:11px;color:#6b7280;margin:-4px 0 10px">✉ ' + AM.esc(s.email) + '</div>' : '') +
                '<div style="font-size:11px;font-weight:700;color:#6b7280;margin-bottom:7px">SẢN PHẨM CUNG CẤP</div>' +
                '<div style="display:flex;flex-wrap:wrap;gap:5px;margin-bottom:12px">' +
                (supplied.length ? supplied.map(function (p) {
                    return '<span style="background:#f1f5f9;color:#374151;padding:3px 8px;border-radius:6px;font-size:11px;font-weight:600">' + AM.esc(p.name) + '</span>';
                }).join('') : '<span style="font-size:11px;color:#9ca3af">Chưa có sản phẩm</span>') + '</div>' +
                (pending ? '<div style="background:#fef3c7;border-radius:8px;padding:8px 10px;margin-bottom:8px;font-size:12px;color:#b45309">⚠ ' + pending + ' yêu cầu gửi trả hàng đang chờ</div>' : '') +
                (returns.length ? '<div style="margin-bottom:10px">' + returns.map(function (r) {
                    var st = RETURN_STATUS[r.status], next = NEXT_STATUS[r.status];
                    return '<div style="padding:7px 0;border-bottom:1px solid #f3f4f6;font-size:11px">' +
                        '<div style="font-weight:600;color:#374151">' + (r.itemType === 'part' ? '🔩 ' : '🚜 ') + AM.esc(r.item) + ' × ' + r.qty + '</div>' +
                        '<div style="color:#9ca3af;margin:2px 0 5px">' + AM.esc(r.id) + ' · ' + AM.esc(r.date) + ' · ' + AM.esc(r.reason) + '</div>' +
                        '<div style="display:flex;align-items:center;justify-content:space-between;gap:6px">' + AM.badge(st[0], st[1], st[2], 10) +
                        (next ? '<button data-return-next="' + r.id + '" style="background:#f1f5f9;border:1px solid #e5e7eb;border-radius:6px;padding:3px 8px;cursor:pointer;font-size:10px;font-weight:700;white-space:nowrap">' + next[1] + '</button>' : '') +
                        '</div></div>';
                }).join('') + '</div>' : '') +
                '<div style="display:flex;gap:7px">' +
                (AM.canOpen('inventory') ? '<button class="am-btn" data-import="' + s.id + '" style="flex:1;padding:8px 6px;white-space:nowrap">📦 Đặt nhập hàng</button>' : '') +
                '<button class="am-btn" data-return="' + s.id + '" style="flex:1;padding:8px 6px;white-space:nowrap;background:#f59e0b;color:#0f2027">↩ Yêu cầu trả hàng</button>' +
                '</div></div></div>';
        }).join(''));
    }

    function openSupplierForm(s) {
        AM.form.open({
            title: s ? '✏️ Sửa nhà cung cấp ' + s.id : '🏭 Thêm nhà cung cấp',
            width: 560,
            submitLabel: s ? '💾 Cập nhật' : '💾 Lưu nhà cung cấp',
            fields: [
                { name: 'code', label: 'Mã NCC', readonly: true },
                { name: 'taxCode', label: 'Mã số thuế', placeholder: '10 số' },
                { name: 'name', label: 'Tên nhà cung cấp', required: true, full: true, placeholder: 'VD: Shibaura Vietnam' },
                { name: 'contact', label: 'Số điện thoại', type: 'tel', required: true, placeholder: 'VD: 028.1234.5678' },
                { name: 'email', label: 'Email', type: 'email', placeholder: 'VD: sales@shibaura.vn' },
                { name: 'address', label: 'Địa chỉ', full: true, placeholder: 'VD: Q.1, TP.HCM' }
            ],
            values: s ? $.extend({ code: s.id }, s) : { code: AM.nextCode(suppliers, 'NCC', 2) },
            validate: function (v) {
                var e = {}, name = v.name.toLowerCase();
                if (v.name && suppliers.some(function (x) { return x.name.toLowerCase() === name && (!s || x.id !== s.id); })) e.name = 'Nhà cung cấp đã tồn tại';
                if (v.contact && !/^[\d.\s-]{8,15}$/.test(v.contact)) e.contact = 'Số điện thoại không hợp lệ';
                if (v.email && !AM.isEmail(v.email)) e.email = 'Email không hợp lệ';
                if (v.taxCode && !/^\d{10}(-\d{3})?$/.test(v.taxCode)) e.taxCode = 'Mã số thuế gồm 10 số (hoặc 10 số-3 số)';
                return e;
            },
            onSubmit: function (v) {
                return AM.api('supplier.save', $.extend({}, v, { id: s ? s.id : '' })).done(function (data) {
                    AM.replace(suppliers, data.suppliers);
                    renderGrid();
                });
            }
        });
    }

    function openReturnForm(s) {
        AM.form.open({
            title: '↩ Yêu cầu trả hàng — ' + s.name,
            sub: 'Yêu cầu ở trạng thái Chờ gửi; khi chuyển sang "Đã gửi" hệ thống tự xuất kho',
            width: 540,
            submitLabel: '💾 Tạo yêu cầu',
            submitColor: '#f59e0b',
            fields: [
                { name: 'itemType', label: 'Loại hàng', type: 'select', required: true, options: [['product', '🚜 Máy nguyên chiếc'], ['part', '🔩 Linh kiện']] },
                { name: 'qty', label: 'Số lượng trả', type: 'number', min: 1, required: true },
                { name: 'itemId', label: 'Hàng trả', type: 'picker', source: 'products', required: true, full: true, placeholder: 'Gõ tên hoặc mã hàng cần trả...' },
                { name: 'reason', label: 'Lý do trả hàng', type: 'textarea', required: true, full: true, placeholder: 'VD: Lỗi kỹ thuật từ nhà sản xuất, giao sai model...' }
            ],
            values: { itemType: 'product', qty: 1 },
            onChange: function (api, name) {
                if (name === null) api.picker('itemId').data('instock', true);
                if (name === 'itemType') AM.pickerSource(api.picker('itemId'), api.get('itemType') === 'part' ? 'parts' : 'products', true);
                var item = itemOf(api.get('itemType'), api.get('itemId'));
                api.preview(item && +api.get('qty') > item.stock
                    ? '<div style="font-size:11px;color:#ef4444">⚠ Chỉ còn ' + item.stock + ' trong kho</div>' : '');
            },
            validate: function (v) {
                var e = {}, item = itemOf(v.itemType, v.itemId);
                if (!(+v.qty >= 1) || Math.floor(+v.qty) !== +v.qty) e.qty = 'Số lượng phải là số nguyên ≥ 1';
                else if (item && +v.qty > item.stock) e.qty = 'Vượt tồn kho (' + item.stock + ')';
                return e;
            },
            onSubmit: function (v) {
                return AM.api('supplier.return', $.extend({}, v, { supplierId: s.id })).done(function (data) {
                    AM.replace(suppliers, data.suppliers);
                    renderGrid();
                });
            }
        });
    }

    $(function () {
        renderGrid();

        $('[data-action="new-supplier"]').on('click', function () { openSupplierForm(null); });
        $('#am-supplier-grid')
            .on('click', '[data-edit]', function () { openSupplierForm(byId(suppliers, $(this).data('edit'))); })
            .on('click', '[data-return]', function () { openReturnForm(byId(suppliers, $(this).data('return'))); })
            .on('click', '[data-import]', function () {
                window.location.href = AM.url('inventory', { import: 1, supplier: $(this).data('import') });
            })
            .on('click', '[data-return-next]', function () {
                var $btn = $(this).prop('disabled', true);
                AM.api('supplier.returnNext', { id: $btn.data('return-next') }).done(function (data) {
                    AM.replace(suppliers, data.suppliers);
                    renderGrid();
                }).always(function () { $btn.prop('disabled', false); });
            });
    });
})(jQuery, window.AM);
