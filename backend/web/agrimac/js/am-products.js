/* AgriMac – Sản phẩm: danh mục, lưới sản phẩm, form tạo/sửa */
(function ($, AM) {
    'use strict';

    var cfg = window.AM_PRODUCTS || {};
    var firstPage = cfg.page || { rows: [], total: 0, page: 1, pages: 1 };
    var products = firstPage.rows.slice(), categories = cfg.categories || [], suppliers = cfg.suppliers || [];
    var sourceTypes = cfg.sourceTypes || {};
    var parts = cfg.parts || [], boms = cfg.boms || {};
    var MAX_IMAGES = cfg.maxImages || 8, MAX_BYTES = 5 * 1024 * 1024;
    var IMAGE_TYPES = ['image/jpeg', 'image/png', 'image/webp'];
    var state = { cat: 'all', q: '', page: firstPage.page, pages: firstPage.pages, total: firstPage.total, editingId: null, images: [], uploads: [], xhr: null };

    function byId(list, id) {
        return list.filter(function (x) { return x.id === id; })[0] || null;
    }

    function specsOf(p) {
        var parts = [];
        if (p.hp) parts.push(p.hp + 'HP');
        if (p.drive) parts.push(p.drive);
        if (p.weight) parts.push(p.weight + 'kg');
        return parts.length ? parts.join(' · ') : (p.specs || '—');
    }

    /* ---------- Danh mục & lưới ---------- */
    /** Cây chuyên mục: mục cha luôn hiện, mục con chỉ hiện khi đang mở mục cha đó (hoặc một mục con của nó). */
    function renderCategories() {
        var active = byId(categories, state.cat), openParent = active ? (active.parent || active.id) : null;
        var total = categories.reduce(function (n, c) { return n + (c.parent ? 0 : c.count); }, 0);
        var html = '<div class="am-cat-item ' + (state.cat === 'all' ? 'active' : '') + '" data-cat="all">' +
            '<span class="am-cat-name">Tất cả sản phẩm</span><span class="am-count">' + total.toLocaleString('vi-VN') + '</span></div>';
        categories.forEach(function (c) {
            if (c.parent && c.parent !== openParent) return;
            var hasKids = !c.parent && categories.some(function (k) { return k.parent === c.id; });
            html += '<div class="am-cat-item ' + (c.parent ? 'am-cat-child ' : '') + (state.cat === c.id ? 'active' : '') + '" data-cat="' + c.id + '">' +
                '<span class="am-cat-name">' + (hasKids ? (openParent === c.id ? '▾ ' : '▸ ') : '') + AM.esc(c.name) + '</span>' +
                '<span class="am-count">' + c.count.toLocaleString('vi-VN') + '</span></div>';
        });
        $('#am-cat-list').html(html);
    }

    function loadPage(page) {
        if (state.xhr) state.xhr.abort();
        $('#am-product-grid').css('opacity', .5);
        state.xhr = AM.lookup('products', { q: state.q, cat: state.cat === 'all' ? '' : state.cat, page: page || 1, perPage: 24 })
            .done(function (res) {
                AM.replace(products, res.rows);
                $.extend(state, { page: res.page, pages: res.pages, total: res.total });
                renderGrid();
            })
            .always(function () { $('#am-product-grid').css('opacity', ''); });
        return state.xhr;
    }

    function renderGrid() {
        var list = products;
        $('#am-product-count').text(state.total.toLocaleString('vi-VN') + ' sản phẩm');
        $('#am-product-pager').html(state.pages > 1 ? AM.pagerHtml(state.page, state.pages, state.total, 'sản phẩm') : '');
        if (!list.length) {
            $('#am-product-grid').html('<div class="am-empty" style="grid-column:1/-1;padding:40px;font-size:13px">Không có sản phẩm phù hợp</div>');
            return;
        }
        $('#am-product-grid').html(list.map(function (p) {
            var out = p.stock === 0, cat = byId(categories, p.catId), sup = byId(suppliers, p.supId);
            var stockText = out ? '⚠ Hết hàng' : 'Còn ' + p.stock + ' chiếc', cover = (p.images || [])[0];
            return '<div class="am-card" style="overflow:hidden;border:1.5px solid ' + (out ? '#fecaca' : 'transparent') + '">' +
                (cover
                    ? '<div style="position:relative"><img class="am-product-thumb" src="' + AM.esc(cover) + '" alt="' + AM.esc(p.name) + '" loading="lazy" onerror="this.onerror=null;this.src=AM.NO_IMAGE">' +
                      '<span style="position:absolute;left:8px;top:8px;background:' + (out ? '#fee2e2' : 'rgba(15,32,39,.8)') + ';color:' + (out ? '#ef4444' : '#fbbf24') +
                      ';font-size:10px;font-weight:700;padding:2px 7px;border-radius:6px">' + stockText + '</span>' +
                      (p.images.length > 1 ? '<span style="position:absolute;right:8px;bottom:8px;background:rgba(15,32,39,.7);color:#fff;font-size:10px;padding:2px 6px;border-radius:6px">🖼 ' + p.images.length + '</span>' : '') + '</div>'
                    : '<div style="background:' + (out ? '#fef2f2' : 'linear-gradient(135deg,#0f2027,#1a3a4a)') + ';padding:18px 14px;text-align:center">' +
                      '<div style="font-size:34px">🚜</div>' +
                      '<div style="font-size:11px;color:' + (out ? '#ef4444' : '#fbbf24') + ';margin-top:4px;font-weight:700">' + stockText + '</div></div>') +
                '<div style="padding:12px 14px">' +
                '<div style="font-weight:800;font-size:12px;margin-bottom:3px;line-height:1.3">' + AM.esc(p.name) + '</div>' +
                '<div style="font-size:10px;color:#9ca3af;margin-bottom:4px">#' + AM.esc(p.id) + ' · ' + AM.esc(cat ? cat.name.split('(')[0].trim() : '—') + '</div>' +
                '<div style="font-size:10px;color:#6b7280;margin-bottom:6px">' + AM.esc(specsOf(p)) + '</div>' +
                '<div style="font-size:13px;font-weight:800;color:#059669;margin-bottom:3px">' + AM.money(p.price) + '</div>' +
                '<div style="font-size:10px;color:#9ca3af;margin-bottom:' + (canAssemble(p) ? 3 : 8) + 'px">Vốn: ' + AM.money(p.cost) + ' · NCC: ' + AM.esc(sup ? sup.name.split(' ')[0] : '—') + '</div>' +
                (canAssemble(p) ? '<div style="font-size:10px;margin-bottom:8px;color:' + ((boms[p.name] || []).length ? '#0369a1' : '#f59e0b') + '">🔩 ' +
                    ((boms[p.name] || []).length ? 'BOM ' + boms[p.name].length + ' linh kiện · ' + AM.money(bomCost(boms[p.name])) + '/máy' : 'Chưa khai báo BOM') + '</div>' : '') +
                '<div class="am-card-actions">' +
                '<button data-edit="' + AM.esc(p.id) + '" style="flex:1;padding:5px;background:#f1f5f9;border:none;border-radius:6px;cursor:pointer;font-size:10px;font-weight:600">Sửa</button>' +
                (canAssemble(p) ? '<button data-bom-edit="' + AM.esc(p.id) + '" style="flex:1;padding:5px;background:#e0f2fe;border:none;border-radius:6px;cursor:pointer;font-size:10px;font-weight:700;color:#0369a1">🔩 BOM</button>' : '') +
                (AM.canOpen('orders') ? '<button data-order="' + AM.esc(p.id) + '" style="flex:1;padding:5px;background:#0f2027;border:none;border-radius:6px;cursor:pointer;font-size:10px;font-weight:700;color:#fbbf24">Đặt hàng</button>' : '') +
                '</div></div></div>';
        }).join(''));
    }

    function canAssemble(p) {
        return p.sourceType === 'assembly' || p.sourceType === 'both';
    }

    function partById(id) {
        return parts.filter(function (x) { return x.id === id; })[0] || null;
    }

    function bomCost(bom) {
        return (bom || []).reduce(function (s, b) { var x = partById(b.id); return s + (x ? x.cost * b.qty : 0); }, 0);
    }

    /* ---------- BOM mặc định ---------- */
    var bomDraft = [];

    function renderBomEditor(api, product) {
        var total = bomCost(bomDraft);
        var rows = bomDraft.map(function (b) {
            var x = partById(b.id);
            if (!x) return '';
            return '<tr><td style="padding:6px 8px"><div style="font-weight:600;font-size:12px">' + AM.esc(x.name) + '</div>' +
                '<div style="font-size:10px;color:#9ca3af">' + AM.esc(x.id + ' · ' + x.cat + ' · ' + AM.money(x.cost) + '/' + x.unit) + '</div></td>' +
                '<td style="padding:6px 8px;width:70px"><input type="number" min="1" value="' + b.qty + '" data-bom-qty="' + x.id + '" class="am-input" style="padding:4px 6px;text-align:center"></td>' +
                '<td style="padding:6px 8px;text-align:right;font-weight:700;font-size:12px;white-space:nowrap">' + AM.money(x.cost * b.qty) + '</td>' +
                '<td style="padding:6px 4px;width:30px"><button type="button" data-bom-del="' + x.id + '" style="background:#fee2e2;border:none;color:#ef4444;border-radius:5px;padding:2px 7px;cursor:pointer">✕</button></td></tr>';
        }).join('');
        var avail = parts.filter(function (x) { return !bomDraft.some(function (b) { return b.id === x.id; }); });
        api.html('bom',
            '<label style="display:block;font-size:12px;font-weight:700;color:#374151;margin-bottom:5px">Danh sách linh kiện / 1 máy</label>' +
            '<div style="border:1.5px solid #e5e7eb;border-radius:8px;overflow:hidden">' +
            (rows ? '<table style="width:100%;border-collapse:collapse">' + rows + '</table>'
                  : '<div class="am-empty" style="padding:18px;font-size:12px">Chưa có linh kiện — thêm bên dưới</div>') +
            '<div style="display:flex;gap:6px;padding:8px;background:#f8fafc;border-top:1px solid #f3f4f6">' +
            '<select data-bom-pick class="am-input" style="flex:1"><option value="">— Chọn linh kiện để thêm —</option>' + avail.map(function (x) {
                return '<option value="' + x.id + '">' + AM.esc(x.cat + ' · ' + x.name + ' (' + AM.money(x.cost) + ')') + '</option>';
            }).join('') + '</select>' +
            '<input type="number" min="1" value="1" data-bom-pick-qty class="am-input" style="width:64px;text-align:center">' +
            '<button type="button" data-bom-add class="am-btn" style="padding:6px 12px">+ Thêm</button></div></div>'
        );
        var diff = total - product.cost;
        api.preview(bomDraft.length
            ? '<div style="background:#f0f9ff;border:1.5px solid #bae6fd;border-radius:8px;padding:10px 14px;font-size:12px;color:#374151;line-height:1.7">' +
              '<div class="am-row-between"><span>Giá vốn theo BOM / máy</span><b style="font-size:15px;color:#0369a1">' + AM.money(total) + '</b></div>' +
              '<div class="am-row-between"><span>Giá vốn đang khai báo</span><b>' + AM.money(product.cost) + '</b></div>' +
              (product.cost && diff !== 0 ? '<div style="color:' + (diff > 0 ? '#ef4444' : '#059669') + '">Chênh lệch ' + (diff > 0 ? '+' : '−') + AM.money(Math.abs(diff)) + '</div>' : '') +
              '<div class="am-row-between"><span>Lãi gộp / máy theo BOM</span><b style="color:' + (product.price - total > 0 ? '#059669' : '#ef4444') + '">' + AM.money(product.price - total) + '</b></div></div>'
            : '');
    }

    function openBomForm(product) {
        bomDraft = (boms[product.name] || []).map(function (b) { return { id: b.id, qty: b.qty }; });
        var api = AM.form.open({
            title: '🔩 BOM mặc định — ' + product.name,
            sub: 'Sale dùng BOM này khi bấm "↺ BOM mặc định" trong đơn hàng; có thể sửa riêng cho từng đơn',
            width: 640,
            submitLabel: '💾 Lưu BOM',
            submitColor: '#0ea5e9',
            fields: [
                { name: 'bom', type: 'html' },
                { name: 'updateCost', label: 'Giá vốn sản phẩm', type: 'select', full: true,
                  options: [['1', 'Cập nhật giá vốn = tổng BOM'], ['0', 'Giữ nguyên giá vốn đang khai báo']] }
            ],
            values: { updateCost: product.cost ? '0' : '1' },
            onChange: function (a, name) { if (name === null) renderBomEditor(a, product); },
            validate: function () { return bomDraft.length ? {} : { updateCost: 'BOM cần ít nhất 1 linh kiện' }; },
            onSubmit: function (v) {
                return AM.api('bom.save', { productId: product.id, items: bomDraft, updateCost: v.updateCost === '1' ? 1 : 0 }).done(function (data) {
                    $.each(boms, function (k) { delete boms[k]; });
                    $.extend(boms, data.boms);
                    $.extend(product, data.product);
                    render();
                });
            }
        });
        var $f = api.$form;
        $f.on('click', '[data-bom-add]', function () {
            var id = $f.find('[data-bom-pick]').val(), qty = Math.max(1, Math.floor(+$f.find('[data-bom-pick-qty]').val() || 1));
            if (!id) return;
            bomDraft.push({ id: id, qty: qty });
            renderBomEditor(api, product);
        }).on('click', '[data-bom-del]', function () {
            var id = $(this).data('bom-del');
            bomDraft = bomDraft.filter(function (b) { return b.id !== id; });
            renderBomEditor(api, product);
        }).on('change', '[data-bom-qty]', function () {
            var id = $(this).data('bom-qty'), qty = Math.max(1, Math.floor(+this.value || 1));
            bomDraft.forEach(function (b) { if (b.id === id) b.qty = qty; });
            renderBomEditor(api, product);
        });
    }

    function render() {
        renderCategories();
        renderGrid();
    }

    /* ---------- Popup ---------- */
    function openModal(id) { $(id).removeClass('am-hidden').find('input:not([readonly]),select').first().trigger('focus'); }
    var closeModals = AM.closeModals;

    function categoryOptions(parentsOnly) {
        return categories.filter(function (c) { return !parentsOnly || !c.parent; }).map(function (c) {
            return '<option value="' + c.id + '">' + (c.parent ? '— ' : '') + AM.esc(c.name) + '</option>';
        }).join('');
    }

    function fillOptions() {
        var $f = $('#am-product-form');
        $f.find('[name="catId"]').html('<option value="">— Chọn danh mục —</option>' + categoryOptions());
        $f.find('[name="sourceType"]').html($.map(sourceTypes, function (label, key) {
            return '<option value="' + key + '">' + AM.esc(label) + '</option>';
        }).join(''));
        $f.find('[name="supId"]').html('<option value="">— Không có —</option>' + suppliers.map(function (s) {
            return '<option value="' + s.id + '">' + AM.esc(s.name) + '</option>';
        }).join(''));
    }

    function clearErrors($form) {
        $form.find('.am-field').removeClass('has-error').find('.am-field-error').text('');
    }

    function setError($form, name, msg) {
        $form.find('[name="' + name + '"]').closest('.am-field').addClass('has-error').find('.am-field-error').text(msg);
    }

    function readForm($form) {
        var data = {};
        $.each($form.serializeArray(), function (_, f) { data[f.name] = $.trim(f.value); });
        return data;
    }

    /* ---------- Ảnh sản phẩm ---------- */
    function uploading() {
        return state.images.filter(function (img) { return img.uploading; }).length;
    }

    function renderImages() {
        var html = state.images.map(function (img, i) {
            return '<div class="am-img-tile' + (i === 0 ? ' cover' : '') + '" data-key="' + img.key + '">' +
                '<img src="' + AM.esc(img.preview || img.url) + '" alt="">' +
                (i === 0 ? '<span class="am-img-cover">ẢNH ĐẠI DIỆN</span>' : '') +
                (img.uploading
                    ? '<div class="am-img-progress">Đang tải ' + img.progress + '%<div class="am-bar"><div style="width:' + img.progress + '%"></div></div></div>'
                    : '<div class="am-img-actions">' + (i > 0 ? '<button type="button" data-img-cover="' + img.key + '">★ Đại diện</button>' : '<span></span>') +
                      '<button type="button" data-img-remove="' + img.key + '" title="Xoá ảnh">✕</button></div>') +
                '</div>';
        }).join('');
        if (state.images.length < MAX_IMAGES) {
            html += '<button type="button" class="am-img-add" data-img-add><span>＋</span>' +
                (state.images.length ? 'Thêm ảnh' : 'Chọn hoặc kéo thả ảnh') + '<em style="font-style:normal;font-weight:400">' + state.images.length + '/' + MAX_IMAGES + '</em></button>';
        }
        $('#am-pm-images').html(html);
    }

    function imageError(msg) {
        $('[data-field="images"]').addClass('has-error').find('.am-field-error').text(msg);
    }

    function uploadImage(file) {
        var img = { key: 'img' + Date.now() + Math.random().toString(36).slice(2, 7), preview: URL.createObjectURL(file), uploading: true, progress: 0 };
        state.images.push(img);
        var data = new FormData();
        data.append('file', file);
        var xhr = $.ajax({
            url: cfg.uploadUrl, type: 'POST', data: data, processData: false, contentType: false, dataType: 'json',
            xhr: function () {
                var x = new window.XMLHttpRequest();
                x.upload.addEventListener('progress', function (e) {
                    if (!e.lengthComputable) return;
                    img.progress = Math.round(e.loaded / e.total * 100);
                    $('.am-img-tile[data-key="' + img.key + '"] .am-img-progress').html('Đang tải ' + img.progress + '%<div class="am-bar"><div style="width:' + img.progress + '%"></div></div>');
                });
                return x;
            }
        }).done(function (res) {
            if (res && res.status) {
                img.url = res.url;
                img.uploading = false;
            } else {
                dropImage(img.key);
                imageError(file.name + ': ' + ((res && res.message) || 'tải ảnh thất bại'));
            }
        }).fail(function (x, status) {
            dropImage(img.key);
            if (status !== 'abort') imageError(file.name + ': tải ảnh thất bại (' + (x.status || 'mất kết nối') + ')');
        }).always(function () {
            URL.revokeObjectURL(img.preview);
            delete img.preview;
            state.uploads = state.uploads.filter(function (u) { return u !== xhr; });
            renderImages();
        });
        state.uploads.push(xhr);
    }

    function dropImage(key) {
        state.images = state.images.filter(function (img) { return img.key !== key; });
    }

    function addFiles(files) {
        $('[data-field="images"]').removeClass('has-error').find('.am-field-error').text('');
        var rejected = [];
        $.each(files, function (_, file) {
            if (IMAGE_TYPES.indexOf(file.type) < 0) return rejected.push(file.name + ': không phải JPG/PNG/WEBP');
            if (file.size > MAX_BYTES) return rejected.push(file.name + ': vượt 5MB');
            if (state.images.length >= MAX_IMAGES) return rejected.push(file.name + ': đã đủ ' + MAX_IMAGES + ' ảnh');
            uploadImage(file);
        });
        renderImages();
        if (rejected.length) imageError(rejected.join(' · '));
    }

    function openProductForm(id) {
        var $f = $('#am-product-form'), p = id ? byId(products, id) : null;
        state.editingId = id || null;
        state.uploads.forEach(function (x) { x.abort(); });
        state.images = ((p && p.images) || []).map(function (url, i) { return { key: 'saved' + i, url: url }; });
        renderImages();
        fillOptions();
        $f[0].reset();
        clearErrors($f);
        $('#am-pm-title').text(p ? '✏️ Sửa sản phẩm ' + p.id : '🚜 Thêm sản phẩm mới');
        $('#am-pm-sub').text(p ? 'Tồn kho hiện tại: ' + p.stock + ' chiếc (thay đổi qua phiếu kho)' : 'Tồn kho ban đầu = 0, tăng khi tạo phiếu nhập kho hoặc hoàn tất lắp ráp');
        $('#am-pm-submit').text(p ? '💾 Cập nhật' : '💾 Lưu sản phẩm');
        if (p) {
            $.each(['name', 'catId', 'sourceType', 'supId', 'price', 'cost', 'hp', 'drive', 'weight', 'minStock', 'description'], function (_, k) {
                $f.find('[name="' + k + '"]').val(p[k] === undefined || p[k] === null ? '' : p[k]);
            });
            $f.find('[name="code"]').val(p.id);
        } else {
            $f.find('[name="code"]').val('');
            $f.find('[name="catId"]').val(state.cat !== 'all' ? state.cat : '');
            $f.find('[name="minStock"]').val(3);
        }
        renderPreview();
        openModal('#am-product-modal');
    }

    function renderPreview() {
        var d = readForm($('#am-product-form')), price = +d.price || 0, cost = +d.cost || 0, html = '';
        var specs = specsOf({ hp: d.hp, drive: d.drive, weight: d.weight });
        if (specs !== '—') {
            html += '<div style="font-size:11px;color:#6b7280;margin-bottom:6px">Thông số hiển thị: <b style="color:#374151">' + AM.esc(specs) + '</b></div>';
        }
        if (price > 0 && cost > 0) {
            var profit = price - cost, pct = Math.round(profit / price * 100), ok = profit > 0;
            html += '<div style="background:' + (ok ? '#f0fdf4' : '#fff5f5') + ';border:1.5px solid ' + (ok ? '#bbf7d0' : '#fecaca') +
                ';border-radius:8px;padding:10px 14px;display:flex;justify-content:space-between;align-items:center">' +
                '<span style="font-size:12px;color:#6b7280">' + (ok ? '💹 Lãi gộp dự kiến / máy' : '⚠ Giá vốn đang cao hơn giá bán') + '</span>' +
                '<span style="font-weight:900;font-size:15px;color:' + (ok ? '#059669' : '#ef4444') + '">' + (ok ? AM.money(profit) : '-' + AM.money(-profit)) + ' (' + pct + '%)</span></div>';
        }
        $('#am-pm-preview').html(html);
    }

    function validateProduct(d) {
        var errors = {}, name = d.name.toLowerCase();
        if (!d.name) errors.name = 'Vui lòng nhập tên sản phẩm';
        else if (d.name.length > 255) errors.name = 'Tên tối đa 255 ký tự';
        else if (products.some(function (p) { return p.id !== state.editingId && p.name.toLowerCase() === name; })) errors.name = 'Tên sản phẩm đã tồn tại';
        if (!d.catId) errors.catId = 'Vui lòng chọn danh mục';
        if (!(+d.price > 0)) errors.price = 'Giá bán phải lớn hơn 0';
        if (d.sourceType === 'import' && !d.supId) errors.supId = 'Hàng nhập nguyên chiếc cần chọn nhà cung cấp';
        $.each(['cost', 'hp', 'weight', 'minStock'], function (_, k) {
            if (d[k] !== '' && (isNaN(+d[k]) || +d[k] < 0 || Math.floor(+d[k]) !== +d[k])) errors[k] = 'Phải là số nguyên không âm';
        });
        return errors;
    }

    function saveProduct() {
        var $f = $('#am-product-form'), d = readForm($f), errors = validateProduct(d);
        clearErrors($f);
        if (uploading()) errors.images = 'Đang tải ' + uploading() + ' ảnh, vui lòng chờ tải xong rồi lưu';
        if (!$.isEmptyObject(errors)) {
            $.each(errors, function (k, msg) { k === 'images' ? imageError(msg) : setError($f, k, msg); });
            $f.find('.has-error').first().find('input,select').trigger('focus');
            return;
        }
        var $btn = $('#am-pm-submit'), label = $btn.text();
        $btn.prop('disabled', true).text('⏳ Đang lưu...');
        AM.api('product.save', $.extend({}, d, {
            id: state.editingId || '', images: state.images.map(function (img) { return img.url; })
        })).done(function (data) {
            var p = byId(products, data.product.id);
            if (p) {
                $.extend(p, data.product);
            } else {
                products.unshift(data.product);
                state.total += 1;
            }
            AM.replace(categories, data.categories);
            closeModals();
            render();
        }).fail(function (err) {
            err.handled = !$.isEmptyObject(err.errors);
            $.each(err.errors, function (k, msg) { k === 'images' ? imageError(msg) : setError($f, k, msg); });
        }).always(function () {
            $btn.prop('disabled', false).text(label);
        });
    }

    function saveCategory() {
        var $f = $('#am-category-form'), name = $.trim($f.find('[name="name"]').val());
        clearErrors($f);
        if (!name) return setError($f, 'name', 'Vui lòng nhập tên danh mục');
        AM.api('category.save', { name: name, parentId: $f.find('[name="parentId"]').val() }).done(function (data) {
            AM.replace(categories, data.categories);
            state.cat = data.id;
            closeModals();
            render();
            loadPage(1);
        }).fail(function (err) {
            err.handled = !$.isEmptyObject(err.errors);
            $.each(err.errors, function (k, msg) { setError($f, k, msg); });
        });
    }

    $(function () {
        render();

        $('#am-cat-list').on('click', '.am-cat-item', function () {
            state.cat = String($(this).data('cat'));
            renderCategories();
            loadPage(1);
        });
        $('#am-product-pager').on('click', '[data-page]:not(:disabled)', function () {
            loadPage(+$(this).data('page'));
            $('.am-content').scrollTop(0);
        });
        var searchTimer;
        $('#am-product-search').on('input', function () {
            var q = $.trim(this.value);
            clearTimeout(searchTimer);
            searchTimer = setTimeout(function () { state.q = q; loadPage(1); }, 300);
        });
        $('[data-action="new-product"]').on('click', function () { openProductForm(null); });
        $('#am-product-grid').on('click', '[data-edit]', function () { openProductForm($(this).data('edit')); })
            .on('click', '[data-bom-edit]', function () { openBomForm(byId(products, $(this).data('bom-edit'))); });
        $('[data-action="new-category"]').on('click', function () {
            var $f = $('#am-category-form');
            $f[0].reset();
            clearErrors($f);
            var active = byId(categories, state.cat);
            $f.find('[name="parentId"]').html('<option value="">— Danh mục cấp 1 —</option>' + categoryOptions(true))
                .val(active ? (active.parent || active.id) : '');
            openModal('#am-category-modal');
        });

        $('#am-product-form').on('submit', function (e) { e.preventDefault(); saveProduct(); })
            .on('input change', 'input:not([type=file]),select', function () {
                $(this).closest('.am-field').removeClass('has-error').find('.am-field-error').text('');
                renderPreview();
            });
        $('#am-pm-images')
            .on('click', '[data-img-add]', function () { $('#am-pm-file').val('').trigger('click'); })
            .on('click', '[data-img-remove]', function () { dropImage($(this).data('img-remove')); renderImages(); })
            .on('click', '[data-img-cover]', function () {
                var key = $(this).data('img-cover'), img = state.images.filter(function (x) { return x.key === key; })[0];
                dropImage(key);
                state.images.unshift(img);
                renderImages();
            })
            .on('dragover dragenter', function (e) { e.preventDefault(); $(this).addClass('drag'); })
            .on('dragleave drop', function (e) { e.preventDefault(); $(this).removeClass('drag'); })
            .on('drop', function (e) { addFiles(e.originalEvent.dataTransfer.files); });
        $('#am-pm-file').on('change', function () { addFiles(this.files); });
        $('#am-category-form').on('submit', function (e) { e.preventDefault(); saveCategory(); });
        $('#am-product-grid').on('click', '[data-order]', function () {
            window.location.href = AM.url('orders', { new: 1, product: $(this).data('order') });
        });
    });
})(jQuery, window.AM);
