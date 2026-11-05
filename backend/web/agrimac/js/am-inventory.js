/* AgriMac – Quản lý kho: tồn máy, tồn linh kiện, lịch sử nhập/xuất, tạo phiếu nhập */
(function ($, AM) {
    'use strict';

    var inv = window.AM_INV || {};
    var products = [], lowStock = inv.lowStock || [], stockValue = inv.stockValue || 0;
    var parts = inv.parts || [], suppliers = inv.suppliers || [];
    var stock = { q: '', page: 1, pages: 1, total: 0, xhr: null };
    var categories = inv.categories || [], partCategories = inv.partCategories || [], txs = inv.transactions || [];
    var selected = { supplier: suppliers.length ? suppliers[0].id : '' };
    /** Bảng dòng của phiếu nhập: máy + linh kiện, có đơn giá (gợi ý = giá vốn hiện tại) */
    var IMPORT_LINES = { kinds: ['product', 'part'], price: true, priceOf: function (kind, item) { return item.cost || ''; } };
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

    var NEW_CAT = '__new__';
    var UNITS = AM.data.productUnits || ['Cái', 'Bộ'];

    /** Dữ liệu cũ lưu "cái"/"bộ" → khớp đúng lựa chọn "Cái"/"Bộ" */
    function normUnit(u) {
        var l = String(u || '').toLowerCase();
        return UNITS.filter(function (x) { return x.toLowerCase() === l; })[0] || UNITS[0];
    }

    function openPartForm(part) {
        AM.form.open({
            title: part ? '✏️ Sửa linh kiện ' + part.id : '🔩 Thêm linh kiện',
            sub: part ? 'Tồn kho hiện tại: ' + part.stock + ' ' + part.unit + ' (thay đổi qua phiếu nhập/xuất)' : 'Tồn kho ban đầu = 0, tăng khi tạo phiếu nhập',
            width: 560,
            submitLabel: part ? '💾 Cập nhật' : '💾 Lưu linh kiện',
            fields: [
                { name: 'code', label: 'Mã linh kiện', readonly: true },
                { name: 'unit', label: 'Đơn vị tính', type: 'select', required: true, options: UNITS.map(function (u) { return [u, u]; }) },
                { name: 'name', label: 'Tên linh kiện', required: true, full: true, placeholder: 'VD: Bơm cao áp Kubota' },
                { name: 'cat', label: 'Nhóm linh kiện', type: 'select', required: true, placeholder: '— Chọn nhóm —',
                  options: partCategories.map(function (c) { return [c, c]; }).concat([[NEW_CAT, '➕ Tạo nhóm mới...']]) },
                { name: 'newCat', label: 'Tên nhóm mới', required: true, placeholder: 'VD: Động cơ, Hộp số, Hệ thống phanh' },
                { name: 'supId', label: 'Nhà cung cấp mặc định', type: 'select', placeholder: '— Không —', options: suppliers.map(function (x) { return [x.id, x.name]; }) },
                { name: 'cost', label: 'Giá vốn (đ)', type: 'money', min: 0, required: true },
                { name: 'minStock', label: 'Tồn tối thiểu', type: 'number', min: 0, required: true, hint: 'Dưới mức này sẽ cảnh báo' }
            ],
            values: part ? $.extend({ code: part.id }, part, { unit: normUnit(part.unit) })
                : { code: AM.nextCode(parts, 'LK', 3), unit: UNITS[0], minStock: 5, cat: partCategories.length ? (partCategories.indexOf(partFilter) >= 0 ? partFilter : '') : NEW_CAT },
            onChange: function (api) { api.toggle('newCat', api.get('cat') === NEW_CAT); },
            validate: function (v) {
                var e = {}, name = v.name.toLowerCase();
                if (v.cat === NEW_CAT && v.newCat && partCategories.some(function (c) { return c.toLowerCase() === v.newCat.toLowerCase(); })) e.newCat = 'Nhóm đã tồn tại, hãy chọn ở trên';
                if (v.name && parts.some(function (x) { return x.name.toLowerCase() === name && (!part || x.id !== part.id); })) e.name = 'Linh kiện đã tồn tại';
                $.each(['cost', 'minStock'], function (_, k) {
                    if (v[k] !== '' && (+v[k] < 0 || Math.floor(+v[k]) !== +v[k])) e[k] = 'Phải là số nguyên không âm';
                });
                return e;
            },
            onSubmit: function (v) {
                var isNew = v.cat === NEW_CAT;
                return AM.api('part.save', $.extend({}, v, { id: part ? part.id : '', cat: isNew ? '' : v.cat, newCat: isNew ? v.newCat : '' })).done(function (data) {
                    applyInventory(data);
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

    /** Phiếu xuất lẻ / điều chỉnh: nhiều dòng (máy, linh kiện, "⚙ LK theo máy" tự thêm linh kiện theo định mức) */
    function openExportForm() {
        var code = AM.nextCode(txs.filter(function (t) { return t.type === 'export'; }), 'XK', 3);
        var opts = { kinds: ['part', 'product'], bom: true, instock: true, stockCheck: true };
        var api = AM.form.open({
            title: '📤 Tạo phiếu xuất kho',
            sub: 'Xuất theo đơn hàng dùng nút trong trang Đơn hàng; phiếu này cho xuất lẻ / điều chỉnh',
            width: 820,
            submitLabel: '📤 Xuất kho',
            submitColor: '#7c3aed',
            fields: [
                { name: 'code', label: 'Mã phiếu', readonly: true },
                { name: 'reason', label: 'Lý do xuất', type: 'select', required: true, options: $.map(EXPORT_REASONS, function (l, k) { return [[k, l]]; }) },
                { name: 'lines', type: 'html', html: '<div class="am-field am-field-full" data-field="lines"><label>Hàng xuất <span class="am-req">*</span></label>' +
                    AM.lx.html(opts) + '<div class="am-field-error"></div></div>' },
                { name: 'ref', label: 'Tham chiếu', placeholder: 'VD: DH2025003 / KC001', hint: 'Mã đơn hàng hoặc phiếu khiếu nại' },
                { name: 'date', label: 'Ngày xuất', type: 'date', required: true },
                { name: 'note', label: 'Ghi chú', type: 'textarea', full: true }
            ],
            values: { code: code, reason: 'assembly_issue', date: AM.isoToday() },
            onChange: function (a, name) {
                // Lý do bán hàng → dòng mới mặc định là máy; còn lại là linh kiện
                if (name === 'reason') lxOpts().defaultKind = a.get('reason') === 'sale' ? 'product' : 'part';
            },
            validate: function (v) {
                var e = {}, r = AM.lx.read($lx());
                if (r.error) e.lines = r.error;
                else if (!r.lines.length) e.lines = 'Vui lòng thêm ít nhất một dòng hàng';
                if ((v.reason === 'assembly_issue' || v.reason === 'sale') && !v.ref) e.ref = 'Cần nhập mã đơn hàng';
                if ((v.reason === 'adjust' || v.reason === 'other') && !v.note) e.note = 'Vui lòng ghi rõ lý do';
                return e;
            },
            onSubmit: function (v) {
                return AM.api('stock.export', { reason: v.reason, ref: v.ref, date: v.date, note: v.note, lines: AM.lx.read($lx()).lines }).done(function (data) {
                    applyInventory(data);
                    txFilter = '';
                    $('#am-tx-filter').val('');
                    renderAll();
                });
            }
        });
        function $lx() { return api.$form.find('.am-lx'); }
        function lxOpts() { return $lx().data('lx'); }
        AM.lx.init($lx(), $.extend({ defaultKind: 'part' }, opts));
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
        $('#am-imp-supplier').html(optionList(suppliers, selected.supplier, '-- Chọn nhà cung cấp --'));
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

    function setPanel(name, open) {
        ['product', 'part', 'supplier'].forEach(function (key) {
            var on = key === name && open;
            $('#am-new-' + key).toggleClass('am-hidden', !on);
            $('.am-toggle[data-toggle-panel="' + key + '"]').toggleClass('open', on).text(on ? '✕ Đóng' : TOGGLE_TEXT[key]);
        });
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
        var $impLines = $('#am-imp-lines').html(AM.lx.html(IMPORT_LINES)).find('.am-lx');
        AM.lx.init($impLines, $.extend({ defaultKind: AM.query('kind') === 'part' ? 'part' : 'product' }, IMPORT_LINES));
        $('#am-imp-date').val(AM.isoToday());
        if (AM.query('import')) showTab('import');
        $('#am-part-filter').on('change', function () { partFilter = this.value; renderParts(); });
        $('#am-tx-filter').on('change', function () { txFilter = this.value; renderTx(); });
        $('[data-action="new-part"]').on('click', function () { openPartForm(null); });
        $('#am-part-rows').on('click', '[data-edit-part]', function () { openPartForm(byId(parts, $(this).data('edit-part'))); });
        $('[data-action="new-export"]').on('click', openExportForm);
        $('#am-imp-supplier').on('change', function () { selected.supplier = this.value; });

        $(document).on('click', '[data-toggle-panel]', function () {
            var name = $(this).data('toggle-panel');
            var isOpen = !$('#am-new-' + name).hasClass('am-hidden');
            setPanel(name, $(this).data('close') ? false : !isOpen);
        });

        $('[data-action="add-product"]').on('click', function () {
            var f = readFields('data-np');
            if (!$.trim(f.name)) return AM.toast('⚠ Vui lòng nhập tên sản phẩm', false);
            AM.api('product.save', {
                name: f.name, catId: f.catId, price: f.price, cost: f.cost, specs: f.specs, unit: f.unit || UNITS[0],
                supId: selected.supplier, sourceType: selected.supplier ? 'import' : 'assembly'
            }).done(function (data) {
                AM.lx.add($impLines, 'product', data.product, 1, data.product.cost || '');
                $('[data-np]').not('select').val('');
                setPanel(null, false);
                loadStock(1);
            });
        });

        $('[data-action="add-part"]').on('click', function () {
            var f = readFields('data-npart');
            if (!$.trim(f.name)) return AM.toast('⚠ Vui lòng nhập tên linh kiện', false);
            AM.api('part.save', { name: f.name, cat: f.cat, unit: f.unit || UNITS[0], cost: f.cost || 0, minStock: f.minStock || 0 }).done(function (data) {
                applyInventory(data);
                $('[data-npart]').not('select').val('');
                setPanel(null, false);
                renderAll();
                var part = byId(parts, data.id);
                if (part) AM.lx.add($impLines, 'part', part, 1, part.cost || '');
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
            var $btn = $(this), r = AM.lx.read($impLines), $field = $('#am-imp-lines').closest('[data-field="lines"]');
            $field.removeClass('has-error').find('.am-field-error').text('');
            var err = r.error || (!r.lines.length ? 'Vui lòng thêm ít nhất một dòng hàng' : '');
            if (err) return $field.addClass('has-error').find('.am-field-error').text(err);
            $btn.prop('disabled', true);
            AM.api('stock.import', { lines: r.lines, supplierId: selected.supplier, date: $('#am-imp-date').val(), note: $('#am-imp-note').val() })
                .done(function (data) {
                    applyInventory(data);
                    $('#am-imp-note').val('');
                    AM.lx.init($impLines, IMPORT_LINES);
                    renderAll();
                    showTab('tx');
                }).fail(function (err) {
                    if (err.errors && err.errors.lines) {
                        err.handled = true;
                        $field.addClass('has-error').find('.am-field-error').text(err.errors.lines);
                    }
                }).always(function () { $btn.prop('disabled', false); });
        });
    });
})(jQuery, window.AM);
