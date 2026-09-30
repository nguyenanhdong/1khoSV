/* AgriMac – Quản lý kho: tồn máy, tồn linh kiện, lịch sử nhập/xuất, tạo phiếu nhập */
(function ($, AM) {
    'use strict';

    var inv = window.AM_INV || {};
    var products = [], lowStock = inv.lowStock || [], stockValue = inv.stockValue || 0;
    var parts = inv.parts || [], suppliers = inv.suppliers || [];
    var stock = { q: '', page: 1, pages: 1, total: 0, xhr: null };
    var categories = inv.categories || [], partCategories = inv.partCategories || [], txs = inv.transactions || [];
    var kind = 'product';
    var selected = {
        product: '',
        part: parts.length ? parts[0].id : '',
        supplier: suppliers.length ? suppliers[0].id : ''
    };
    var TOGGLE_TEXT = { product: '+ Tạo sản phẩm mới', part: '+ Tạo linh kiện mới', supplier: '+ Tạo NCC mới' };

    function byId(list, id) {
        return list.filter(function (x) { return x.id === id; })[0] || null;
    }

    function productLow(p) { return p.stock <= (p.minStock || 0); }
    function partLow(p) { return p.stock <= (p.minStock || 0); }

    function stockBadge(stock, low) {
        if (stock === 0) return AM.badge('Hết hàng', '#ef4444', '#fee2e2');
        if (low) return AM.badge('Sắp hết', '#b45309', '#fef3c7');
        return AM.badge('Còn hàng', '#059669', '#d1fae5');
    }

    function stockColor(stock, low) {
        return stock === 0 ? '#ef4444' : low ? '#f59e0b' : '#059669';
    }

    function renderStock() {
        $('#am-stock-rows').html(products.map(function (p) {
            var cat = byId(categories, p.catId), sup = byId(suppliers, p.supId), low = productLow(p);
            return '<tr><td><span style="font-weight:700;font-size:11px;color:#9ca3af">' + p.id + '</span></td>' +
                '<td><span style="font-weight:700">' + AM.esc(p.name) + '</span></td>' +
                '<td style="font-size:12px;color:#6b7280">' + AM.esc(cat ? cat.name.split('(')[0].trim() : '') + '</td>' +
                '<td style="font-size:12px">' + (sup ? AM.esc(sup.name) : '<span style="color:#d1d5db">—</span>') + '</td>' +
                '<td><span style="font-weight:800;color:' + stockColor(p.stock, low) + ';font-size:15px">' + p.stock + '</span><span style="font-size:11px;color:#9ca3af"> chiếc</span></td>' +
                '<td style="font-size:12px">' + AM.money(p.cost) + '</td>' +
                '<td><span style="font-weight:700;color:#059669">' + AM.money(p.stock * p.cost) + '</span></td>' +
                '<td>' + stockBadge(p.stock, low) + '</td></tr>';
        }).join('') || '<tr><td colspan="8" class="am-empty" style="padding:24px">Không có sản phẩm phù hợp</td></tr>');
        $('#am-stock-total').text(AM.money(stockValue));
        $('#am-stock-pager').html(stock.pages > 1 ? AM.pagerHtml(stock.page, stock.pages, stock.total, 'sản phẩm') : '');
    }

    function loadStock(page) {
        if (stock.xhr) stock.xhr.abort();
        stock.xhr = AM.lookup('products', { q: stock.q, page: page || stock.page, perPage: 30 }).done(function (res) {
            AM.replace(products, AM.cacheProducts(res.rows));
            $.extend(stock, { page: res.page, pages: res.pages, total: res.total });
            renderStock();
        });
    }

    var EXPORT_REASONS = {
        assembly_issue: '🔩 Xuất linh kiện cho lắp ráp',
        warranty: '🛡 Xuất linh kiện bảo hành',
        sale: '🚜 Xuất máy giao đại lý',
        adjust: '📋 Điều chỉnh kiểm kê (hao hụt)',
        other: 'Khác'
    };
    var partFilter = '', txFilter = '';

    function renderPartFilter() {
        $('#am-part-filter').html('<option value="">Tất cả nhóm (' + parts.length + ')</option>' + partCategories.map(function (c) {
            var n = parts.filter(function (p) { return p.cat === c; }).length;
            return '<option value="' + AM.esc(c) + '"' + (c === partFilter ? ' selected' : '') + '>' + AM.esc(c) + ' (' + n + ')</option>';
        }).join(''));
    }

    function renderParts() {
        renderPartFilter();
        $('#am-part-rows').html(parts.filter(function (p) { return !partFilter || p.cat === partFilter; }).map(function (p) {
            var low = partLow(p);
            return '<tr><td><span style="font-weight:700;font-size:11px;color:#9ca3af">' + p.id + '</span></td>' +
                '<td><span style="font-weight:700">' + AM.esc(p.name) + '</span></td>' +
                '<td style="font-size:12px;color:#6b7280">' + AM.esc(p.cat) + '</td>' +
                '<td style="font-size:12px">' + AM.esc(p.unit) + '</td>' +
                '<td><span style="font-weight:800;color:' + stockColor(p.stock, low) + ';font-size:15px">' + p.stock + '</span><span style="font-size:11px;color:#9ca3af"> ' + AM.esc(p.unit) + '</span></td>' +
                '<td style="font-size:12px;color:#6b7280">' + (p.minStock || 0) + '</td>' +
                '<td style="font-size:12px">' + AM.money(p.cost) + '</td>' +
                '<td><span style="font-weight:700;color:#0ea5e9">' + AM.money(p.stock * p.cost) + '</span></td>' +
                '<td>' + stockBadge(p.stock, low) + '</td>' +
                '<td><button class="am-btn am-btn-sm" data-edit-part="' + p.id + '" style="background:#f1f5f9;color:#374151">Sửa</button></td></tr>';
        }).join(''));
        $('#am-part-total').text(AM.money(parts.reduce(function (s, p) { return s + p.stock * p.cost; }, 0)));
    }

    function renderTx() {
        $('#am-tx-rows').html(txs.filter(function (t) { return !txFilter || t.type === txFilter; }).map(function (t) {
            var imp = t.type === 'import', isPart = t.itemType === 'part';
            return '<tr><td><span style="font-weight:700;font-size:11px">' + t.id + '</span></td>' +
                '<td>' + (imp ? AM.badge('📥 Nhập kho', '#2563eb', '#dbeafe') : AM.badge('📤 Xuất kho', '#7c3aed', '#ede9fe')) + '</td>' +
                '<td>' + (isPart ? AM.badge('🔩 Linh kiện', '#0369a1', '#e0f2fe') : AM.badge('🚜 Máy', '#047857', '#d1fae5')) + '</td>' +
                '<td style="font-weight:600">' + AM.esc(t.prod) + '</td>' +
                '<td style="text-align:center;font-weight:700">' + (imp ? '+' : '-') + ' ' + t.qty + '</td>' +
                '<td style="font-size:12px;color:#6b7280">' + AM.esc(t.ref) + '</td>' +
                '<td style="font-size:12px">' + AM.money(t.price) + '</td>' +
                '<td><span style="font-weight:700;color:' + (imp ? '#2563eb' : '#7c3aed') + '">' + AM.money(t.qty * t.price) + '</span></td>' +
                '<td style="color:#9ca3af;font-size:12px">' + AM.esc(t.date) + '</td>' +
                '<td style="font-size:11px;color:#6b7280;max-width:180px">' + AM.esc([EXPORT_REASONS[t.reason], t.note].filter(Boolean).join(' · ')) + '</td></tr>';
        }).join(''));
    }

    function openPartForm(part) {
        AM.form.open({
            title: part ? '✏️ Sửa linh kiện ' + part.id : '🔩 Thêm linh kiện',
            sub: part ? 'Tồn kho hiện tại: ' + part.stock + ' ' + part.unit + ' (thay đổi qua phiếu nhập/xuất)' : 'Tồn kho ban đầu = 0, tăng khi tạo phiếu nhập',
            width: 560,
            submitLabel: part ? '💾 Cập nhật' : '💾 Lưu linh kiện',
            fields: [
                { name: 'code', label: 'Mã linh kiện', readonly: true },
                { name: 'unit', label: 'Đơn vị tính', required: true, placeholder: 'cái / bộ' },
                { name: 'name', label: 'Tên linh kiện', required: true, full: true, placeholder: 'VD: Bơm cao áp Kubota' },
                { name: 'cat', label: 'Nhóm linh kiện', type: 'select', placeholder: '— Chọn nhóm —', options: partCategories.map(function (c) { return [c, c]; }) },
                { name: 'newCat', label: 'Hoặc tạo nhóm mới', placeholder: 'VD: Hệ thống phanh', hint: 'Điền nếu nhóm chưa có trong danh sách' },
                { name: 'supId', label: 'Nhà cung cấp mặc định', type: 'select', placeholder: '— Không —', options: suppliers.map(function (x) { return [x.id, x.name]; }) },
                { name: 'cost', label: 'Giá vốn (đ)', type: 'number', min: 0, required: true },
                { name: 'minStock', label: 'Tồn tối thiểu', type: 'number', min: 0, required: true, hint: 'Dưới mức này sẽ cảnh báo' }
            ],
            values: part ? $.extend({ code: part.id }, part) : { code: AM.nextCode(parts, 'LK', 3), unit: 'cái', minStock: 5, cat: partFilter },
            validate: function (v) {
                var e = {}, name = v.name.toLowerCase();
                if (!v.cat && !v.newCat) e.cat = 'Chọn nhóm hoặc tạo nhóm mới';
                if (v.newCat && partCategories.some(function (c) { return c.toLowerCase() === v.newCat.toLowerCase(); })) e.newCat = 'Nhóm đã tồn tại, hãy chọn ở trên';
                if (v.name && parts.some(function (x) { return x.name.toLowerCase() === name && (!part || x.id !== part.id); })) e.name = 'Linh kiện đã tồn tại';
                $.each(['cost', 'minStock'], function (_, k) {
                    if (v[k] !== '' && (+v[k] < 0 || Math.floor(+v[k]) !== +v[k])) e[k] = 'Phải là số nguyên không âm';
                });
                return e;
            },
            onSubmit: function (v) {
                return AM.api('part.save', $.extend({}, v, { id: part ? part.id : '' })).done(function (data) {
                    applyInventory(data);
                    if (!part) selected.part = data.id;
                    renderAll();
                });
            }
        });
    }

    function applyInventory(data) {
        if (data.lowStock) AM.replace(lowStock, data.lowStock);
        if (data.stockValue !== undefined) stockValue = data.stockValue;
        if (data.stockChanged) loadStock();
        if (data.parts) AM.replace(parts, data.parts);
        if (data.partCategories) AM.replace(partCategories, data.partCategories);
        if (data.transactions) AM.replace(txs, data.transactions);
        if (data.suppliers) AM.replace(suppliers, data.suppliers);
    }

    function openExportForm() {
        var code = AM.nextCode(txs.filter(function (t) { return t.type === 'export'; }), 'XK', 3);
        var itemOf = function (k, id) { return k === 'part' ? byId(parts, id) : AM.productCache[id] || null; };
        AM.form.open({
            title: '📤 Tạo phiếu xuất kho',
            sub: 'Xuất theo đơn hàng dùng nút trong trang Đơn hàng; phiếu này cho xuất lẻ / điều chỉnh',
            width: 580,
            submitLabel: '📤 Xuất kho',
            submitColor: '#7c3aed',
            fields: [
                { name: 'code', label: 'Mã phiếu', readonly: true },
                { name: 'reason', label: 'Lý do xuất', type: 'select', required: true, options: $.map(EXPORT_REASONS, function (l, k) { return [[k, l]]; }) },
                { name: 'kind', label: 'Loại hàng', type: 'select', required: true, options: [['part', '🔩 Linh kiện'], ['product', '🚜 Máy nguyên chiếc']] },
                { name: 'qty', label: 'Số lượng', type: 'number', min: 1, required: true },
                { name: 'itemId', label: 'Hàng xuất', type: 'picker', source: 'parts', required: true, full: true, placeholder: 'Gõ tên hoặc mã (chỉ hiện hàng còn tồn)...' },
                { name: 'ref', label: 'Tham chiếu', placeholder: 'VD: DH2025003 / KC001', hint: 'Mã đơn hàng hoặc phiếu khiếu nại' },
                { name: 'date', label: 'Ngày xuất', type: 'date', required: true },
                { name: 'note', label: 'Ghi chú', type: 'textarea', full: true }
            ],
            values: { code: code, reason: 'assembly_issue', kind: 'part', qty: 1, date: AM.isoToday() },
            onChange: function (api, name) {
                if (name === null) api.picker('itemId').data('instock', true);
                if (name === 'reason') {
                    var r = api.get('reason');
                    if (r === 'sale') api.set('kind', 'product');
                    if (r === 'assembly_issue' || r === 'warranty') api.set('kind', 'part');
                    name = 'kind';
                }
                if (name === 'kind') AM.pickerSource(api.picker('itemId'), api.get('kind') === 'part' ? 'parts' : 'products', true);
                var item = itemOf(api.get('kind'), api.get('itemId')), qty = +api.get('qty') || 0;
                api.preview(item
                    ? '<div class="am-row-between" style="background:#f8fafc;border-radius:8px;padding:10px 14px;font-size:12px">' +
                      '<span style="color:#6b7280">Tồn sau xuất · giá trị</span><span style="font-weight:800;color:' + (qty > item.stock ? '#ef4444' : '#7c3aed') + '">' +
                      (item.stock - qty) + ' ' + (item.unit || 'chiếc') + ' · ' + AM.money(qty * item.cost) + '</span></div>'
                    : '');
            },
            validate: function (v) {
                var e = {}, item = itemOf(v.kind, v.itemId);
                if (!(+v.qty >= 1) || Math.floor(+v.qty) !== +v.qty) e.qty = 'Số lượng phải là số nguyên ≥ 1';
                else if (item && +v.qty > item.stock) e.qty = 'Vượt tồn kho (' + item.stock + ')';
                if ((v.reason === 'assembly_issue' || v.reason === 'sale') && !v.ref) e.ref = 'Cần nhập mã đơn hàng';
                if ((v.reason === 'adjust' || v.reason === 'other') && !v.note) e.note = 'Vui lòng ghi rõ lý do';
                return e;
            },
            onSubmit: function (v) {
                return AM.api('stock.export', v).done(function (data) {
                    applyInventory(data);
                    txFilter = '';
                    $('#am-tx-filter').val('');
                    renderAll();
                });
            }
        });
    }

    function optionList(list, selectedId, placeholder, suffix) {
        return '<option value="" disabled>' + placeholder + '</option>' + list.map(function (x) {
            return '<option value="' + x.id + '"' + (x.id === selectedId ? ' selected' : '') + '>' + AM.esc(x.name) +
                (suffix ? suffix(x) : '') + (/X$/.test(x.id) ? ' 🆕' : '') + '</option>';
        }).join('');
    }

    function renderSelects() {
        var $cat = $('[data-npart="cat"]'), cur = $cat.val();
        $cat.html(partCategories.map(function (c) {
            return '<option value="' + AM.esc(c) + '"' + (c === cur ? ' selected' : '') + '>' + AM.esc(c) + '</option>';
        }).join(''));
        $('#am-imp-part').html(optionList(parts, selected.part, '-- Chọn linh kiện --', function (p) {
            return ' (' + p.id + ')';
        }));
        $('#am-imp-supplier').html(optionList(suppliers, selected.supplier, '-- Chọn nhà cung cấp --'));
        var part = byId(parts, selected.part);
        $('#am-imp-part-info').html(part
            ? 'Nhóm: <b>' + AM.esc(part.cat) + '</b> · Tồn: <b style="color:' + stockColor(part.stock, partLow(part)) + '">' + part.stock + ' ' + AM.esc(part.unit) + '</b> · Giá vốn hiện tại: <b>' + AM.money(part.cost) + '</b>'
            : '');
    }

    function renderAlerts() {
        var rows = lowStock.map(function (p) {
            return { name: '🚜 ' + p.name, stock: p.stock, text: p.stock === 0 ? 'Hết hàng' : 'Còn ' + p.stock + ' chiếc' };
        }).concat(parts.filter(partLow).map(function (p) {
            return { name: '🔩 ' + p.name, stock: p.stock, text: p.stock === 0 ? 'Hết hàng' : 'Còn ' + p.stock + '/' + p.minStock + ' ' + p.unit };
        }));
        $('#am-stock-alerts').html(rows.length ? rows.map(function (r) {
            return '<div style="display:flex;justify-content:space-between;padding:6px 0;border-bottom:1px solid #f3f4f6;font-size:12px">' +
                '<span style="color:#374151">' + AM.esc(r.name) + '</span>' +
                '<span style="font-weight:700;color:' + (r.stock === 0 ? '#ef4444' : '#f59e0b') + '">' + AM.esc(r.text) + '</span></div>';
        }).join('') : '<div style="font-size:12px;color:#9ca3af">Tất cả sản phẩm và linh kiện đều đủ hàng ✓</div>');
    }

    function renderTotal() {
        var q = +$('#am-imp-qty').val(), p = +$('#am-imp-price').val();
        $('#am-imp-total').toggleClass('am-hidden', !(q && p)).find('[data-value]').text(AM.money(q * p));
    }

    function setPanel(name, open) {
        ['product', 'part', 'supplier'].forEach(function (key) {
            var on = key === name && open;
            $('#am-new-' + key).toggleClass('am-hidden', !on);
            $('.am-toggle[data-toggle-panel="' + key + '"]').toggleClass('open', on).text(on ? '✕ Đóng' : TOGGLE_TEXT[key]);
        });
    }

    function setKind(next) {
        kind = next;
        $('.am-kind').removeClass('active').filter('[data-kind="' + next + '"]').addClass('active');
        $('#am-imp-product-block').toggleClass('am-hidden', next !== 'product');
        $('#am-imp-part-block').toggleClass('am-hidden', next !== 'part');
        $('#am-imp-price').attr('placeholder', next === 'part' ? 'VD: 45000000' : 'VD: 240000000');
        setPanel(null, false);
    }

    function prefillPrice(item) {
        if (item && !$('#am-imp-price').val()) {
            $('#am-imp-price').val(item.cost || '');
            renderTotal();
        }
    }

    function showTab(tab) {
        $('.am-tabs[data-group="inv"] .am-tab[data-tab="' + tab + '"]').trigger('click');
    }

    function readFields(attr) {
        var f = {};
        $('[' + attr + ']').each(function () { f[$(this).attr(attr)] = $(this).val(); });
        return f;
    }

    function renderAll() {
        renderParts();
        renderTx();
        renderSelects();
        renderAlerts();
    }

    $(function () {
        $('[data-np="catId"]').html(categories.map(function (c) {
            return '<option value="' + c.id + '">' + (c.parent ? '— ' : '') + AM.esc(c.name) + '</option>';
        }).join(''));
        setPanel(null, false);
        if (byId(suppliers, AM.query('supplier'))) selected.supplier = AM.query('supplier');
        renderAll();
        loadStock(1);
        var stockTimer;
        $('#am-stock-search').on('input', function () {
            var q = $.trim(this.value);
            clearTimeout(stockTimer);
            stockTimer = setTimeout(function () { stock.q = q; loadStock(1); }, 300);
        });
        $('#am-stock-pager').on('click', '[data-page]:not(:disabled)', function () { loadStock(+$(this).data('page')); });
        if (AM.query('import')) {
            showTab('import');
            if (AM.query('kind') === 'part') setKind('part');
        }

        $('.am-kind').on('click', function () { setKind($(this).data('kind')); });
        $('#am-part-filter').on('change', function () { partFilter = this.value; renderParts(); });
        $('#am-tx-filter').on('change', function () { txFilter = this.value; renderTx(); });
        $('[data-action="new-part"]').on('click', function () { openPartForm(null); });
        $('#am-part-rows').on('click', '[data-edit-part]', function () { openPartForm(byId(parts, $(this).data('edit-part'))); });
        $('[data-action="new-export"]').on('click', openExportForm);
        $('#am-imp-product').on('change', function () {
            selected.product = this.value;
            prefillPrice(AM.productCache[this.value]);
        });
        $('#am-imp-part').on('change', function () {
            selected.part = this.value;
            renderSelects();
            prefillPrice(byId(parts, selected.part));
        });
        $('#am-imp-supplier').on('change', function () { selected.supplier = this.value; });
        $('#am-imp-qty, #am-imp-price').on('input', renderTotal);

        $(document).on('click', '[data-toggle-panel]', function () {
            var name = $(this).data('toggle-panel');
            var isOpen = !$('#am-new-' + name).hasClass('am-hidden');
            setPanel(name, $(this).data('close') ? false : !isOpen);
        });

        $('[data-action="add-product"]').on('click', function () {
            var f = readFields('data-np');
            if (!$.trim(f.name)) return AM.toast('⚠ Vui lòng nhập tên sản phẩm', false);
            AM.api('product.save', {
                name: f.name, catId: f.catId, price: f.price, cost: f.cost, specs: f.specs,
                supId: selected.supplier, sourceType: selected.supplier ? 'import' : 'assembly'
            }).done(function (data) {
                AM.pickerSet($('#am-imp-product').closest('.am-picker'), data.product);
                $('[data-np]').not('select').val('');
                setPanel(null, false);
                loadStock(1);
            });
        });

        $('[data-action="add-part"]').on('click', function () {
            var f = readFields('data-npart');
            if (!$.trim(f.name)) return AM.toast('⚠ Vui lòng nhập tên linh kiện', false);
            AM.api('part.save', { name: f.name, cat: f.cat, unit: $.trim(f.unit) || 'cái', cost: f.cost || 0, minStock: f.minStock || 0 }).done(function (data) {
                applyInventory(data);
                selected.part = data.id;
                $('[data-npart]').not('select').val('');
                setPanel(null, false);
                renderAll();
                prefillPrice(byId(parts, data.id));
            });
        });

        $('[data-action="add-supplier"]').on('click', function () {
            var f = readFields('data-ns');
            if (!$.trim(f.name)) return AM.toast('⚠ Vui lòng nhập tên nhà cung cấp', false);
            AM.api('supplier.save', { name: f.name, contact: f.contact, address: f.address }).done(function (data) {
                AM.replace(suppliers, data.suppliers);
                selected.supplier = data.id;
                $('[data-ns]').val('');
                setPanel(null, false);
                renderAll();
            });
        });

        $('[data-action="submit-import"]').on('click', function () {
            var $btn = $(this);
            var itemId = kind === 'part' ? selected.part : selected.product;
            var qty = $('#am-imp-qty').val(), price = $('#am-imp-price').val();
            if (!itemId || !qty || !price) return AM.toast('⚠ Vui lòng điền đủ thông tin', false);
            $btn.prop('disabled', true);
            AM.api('stock.import', { kind: kind, itemId: itemId, supplierId: selected.supplier, qty: qty, price: price, note: $('#am-imp-note').val() })
                .done(function (data) {
                    applyInventory(data);
                    $('#am-imp-qty, #am-imp-price, #am-imp-note').val('');
                    renderTotal();
                    renderAll();
                    showTab('tx');
                }).always(function () { $btn.prop('disabled', false); });
        });
    });
})(jQuery, window.AM);
