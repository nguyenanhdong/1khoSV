/* 1Kho CMS – tiện ích dùng chung cho các trang (jQuery). */
(function (window, $) {
    'use strict';

    var AM = window.AM = window.AM || {};
    AM.data = window.AM_DATA || {};

    AM.money = function (t) {
        t = +t || 0;
        if (t >= 1e9) return (t / 1e9).toFixed(2) + 'tỷ';
        if (t >= 1e6) return Math.round(t / 1e6) + 'tr';
        return t.toLocaleString('vi-VN') + 'đ';
    };

    AM.NO_IMAGE = 'data:image/svg+xml,' + encodeURIComponent(
        '<svg xmlns="http://www.w3.org/2000/svg" width="320" height="200"><defs><linearGradient id="g" x2="1" y2="1">' +
        '<stop offset="0" stop-color="#0f2027"/><stop offset="1" stop-color="#1a3a4a"/></linearGradient></defs>' +
        '<rect width="320" height="200" fill="url(#g)"/><text x="160" y="105" font-size="44" text-anchor="middle">🚜</text>' +
        '<text x="160" y="145" font-size="12" fill="#9ca3af" text-anchor="middle" font-family="sans-serif">Không tải được ảnh</text></svg>');

    AM.esc = function (s) {
        return String(s == null ? '' : s)
            .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;').replace(/'/g, '&#39;');
    };

    AM.badge = function (label, color, bg, size) {
        return '<span class="am-badge" style="background:' + bg + ';color:' + color + (size ? ';font-size:' + size + 'px' : '') + '">' + AM.esc(label) + '</span>';
    };

    AM.info = function (icon, label, val) {
        return '<div class="am-info"><div class="am-info-label">' + icon + ' ' + AM.esc(label) + '</div>' +
            '<div class="am-info-value">' + AM.esc(val || '—') + '</div></div>';
    };

    AM.shortDealer = function (name) {
        return String(name || '').split(' ').slice(2).join(' ');
    };

    AM.lastWord = function (name) {
        return String(name || '').split(' ').pop();
    };

    AM.dealer = function (id) {
        return (AM.data.dealers || {})[id] || null;
    };

    AM.part = function (id) {
        var parts = AM.data.parts || [];
        for (var i = 0; i < parts.length; i++) if (parts[i].id === id) return parts[i];
        return null;
    };

    AM.can = function () {
        var role = AM.data.role;
        for (var i = 0; i < arguments.length; i++) if (arguments[i] === role) return true;
        return false;
    };

    var toastTimer = null;
    AM.toast = function (msg, ok) {
        if (ok === undefined) ok = true;
        $('.am-toast').remove();
        clearTimeout(toastTimer);
        var $t = $('<div class="am-toast"></div>').text(msg).css('background', ok ? '#059669' : '#ef4444');
        $('body').append($t);
        toastTimer = setTimeout(function () { $t.remove(); }, 2800);
    };

    AM.pad = function (n, len) {
        var s = String(n);
        while (s.length < (len || 0)) s = '0' + s;
        return s;
    };

    /** Mã kế tiếp theo tiền tố: nextCode([{id:'DL004'}], 'DL', 3) → 'DL005'. */
    AM.nextCode = function (list, prefix, len) {
        var max = 0;
        (list || []).forEach(function (x) {
            var id = String(x.id || '');
            if (id.indexOf(prefix) !== 0) return;
            var m = id.slice(prefix.length).match(/\d+/);
            if (m) max = Math.max(max, +m[0]);
        });
        return prefix + AM.pad(max + 1, len);
    };

    AM.today = function (withYear) {
        var d = new Date(), s = AM.pad(d.getDate(), 2) + '/' + AM.pad(d.getMonth() + 1, 2);
        return withYear ? s + '/' + d.getFullYear() : s;
    };

    AM.isoToday = function () {
        var d = new Date();
        return d.getFullYear() + '-' + AM.pad(d.getMonth() + 1, 2) + '-' + AM.pad(d.getDate(), 2);
    };

    AM.isoToVn = function (iso) {
        var p = String(iso || '').split('-');
        return p.length === 3 ? p[2] + '/' + p[1] + '/' + p[0] : iso;
    };

    AM.isPhone = function (s) { return /^0\d{9,10}$/.test(String(s || '').replace(/[.\s-]/g, '')); };
    AM.isEmail = function (s) { return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(String(s || '')); };

    AM.canOpen = function (page) { return (AM.data.pages || []).indexOf(page) >= 0; };

    AM.url = function (page, params) {
        var base = window.location.pathname.replace(/\/agrimac(\/.*)?$/, '');
        var q = $.param(params || {});
        return base + '/agrimac/' + page + (q ? '?' + q : '');
    };

    AM.query = function (name) { return new URL(window.location.href).searchParams.get(name); };

    /** Thay nội dung mảng tại chỗ (các trang giữ tham chiếu tới mảng dữ liệu). */
    AM.replace = function (target, source) {
        target.length = 0;
        Array.prototype.push.apply(target, source || []);
        return target;
    };

    /**
     * Gọi API ghi dữ liệu. Resolve(data, message) khi thành công;
     * reject({ errors, message }) khi lỗi — lỗi không gắn với ô nào sẽ hiện toast.
     */
    AM.api = function (op, data) {
        var d = $.Deferred();
        $.ajax({ url: AM.data.apiUrl, type: 'POST', dataType: 'json', data: { op: op, data: data || {} } })
            .done(function (res) {
                if (res && res.status) {
                    if (res.message) AM.toast(res.message);
                    if (res.data && res.data.parts) AM.replace(AM.data.parts, res.data.parts);
                    if (res.data && res.data.dealer) AM.data.dealers[res.data.dealer.id] = res.data.dealer;
                    if (res.data && res.data.staff) AM.replace(AM.data.staff, res.data.staff);
                    d.resolve(res.data || {}, res.message);
                } else {
                    d.reject({ errors: {}, message: (res && res.message) || 'Lỗi không xác định' });
                }
            })
            .fail(function (xhr) {
                var res = xhr.responseJSON || {};
                d.reject({ errors: res.errors || {}, message: res.message || ('Lỗi kết nối (' + (xhr.status || 'mất mạng') + ')') });
            });
        return d.promise().fail(function (err) {
            setTimeout(function () {
                if (err.handled) return;
                var first = $.map(err.errors || {}, function (m) { return m; })[0];
                AM.toast('⚠ ' + (first || err.message), false);
            }, 0);
        });
    };

    /** Đọc dữ liệu phân trang / gợi ý (GET agrimac/lookup). */
    AM.lookup = function (type, params) {
        return $.ajax({ url: AM.data.lookupUrl, dataType: 'json', data: $.extend({ type: type }, params || {}) })
            .fail(function (x) { if (x.statusText !== 'abort') AM.toast('⚠ Không tải được dữ liệu (' + (x.status || 'mất mạng') + ')', false); });
    };

    /* ---------- Ô chọn có tìm kiếm (sản phẩm từ server / linh kiện tại chỗ) ----------
     * <div class="am-picker" data-source="products|parts"><input type=hidden name=..><input class="am-picker-input"><div class="am-picker-list"></div></div>
     * Chọn xong: hidden nhận mã, phát sự kiện change; AM.productCache[mã] giữ dữ liệu sản phẩm đã chọn. */
    AM.productCache = {};
    AM.cacheProducts = function (rows) {
        (rows || []).forEach(function (p) { AM.productCache[p.id] = p; });
        return rows;
    };

    AM.pickerHtml = function (name, source, value, label, placeholder) {
        return '<div class="am-picker" data-source="' + (source || 'products') + '">' +
            '<input type="hidden" name="' + name + '" value="' + AM.esc(value || '') + '">' +
            '<input type="text" class="am-input am-picker-input" autocomplete="off" value="' + AM.esc(label || '') + '" placeholder="' +
            AM.esc(placeholder || 'Gõ tên hoặc mã để tìm...') + '"><div class="am-picker-list am-hidden"></div></div>';
    };

    function pickerLabel(row, source) {
        return source === 'parts' ? row.name + ' (' + row.id + ')' : row.name + ' · ' + row.id;
    }

    function pickerItems($picker, rows) {
        var source = $picker.data('source'), $list = $picker.find('.am-picker-list');
        $picker.data('rows', rows);
        $list.html(rows.length ? rows.map(function (r, i) {
            var meta = source === 'parts'
                ? r.cat + ' · tồn ' + r.stock + ' ' + r.unit + ' · ' + AM.money(r.cost)
                : r.id + ' · ' + AM.money(r.price) + ' · tồn ' + r.stock;
            return '<div class="am-picker-item" data-i="' + i + '"><div class="am-picker-name">' + AM.esc(r.name) + '</div>' +
                '<div class="am-picker-meta">' + AM.esc(meta) + '</div></div>';
        }).join('') : '<div class="am-picker-empty">Không tìm thấy</div>').removeClass('am-hidden');
    }

    function pickerSearch($picker) {
        var q = $.trim($picker.find('.am-picker-input').val()).toLowerCase(), source = $picker.data('source');
        if (source === 'parts') {
            var onlyStock = $picker.data('instock');
            pickerItems($picker, (AM.data.parts || []).filter(function (p) {
                return (!onlyStock || p.stock > 0) && (!q || p.name.toLowerCase().indexOf(q) >= 0 || p.id.toLowerCase().indexOf(q) >= 0 || p.cat.toLowerCase().indexOf(q) >= 0);
            }).slice(0, 30));
            return;
        }
        var xhr = $picker.data('xhr');
        if (xhr) xhr.abort();
        $picker.data('xhr', AM.lookup('product-search', { q: q }).done(function (res) {
            var rows = AM.cacheProducts(res.rows || []);
            if ($picker.data('instock')) rows = rows.filter(function (r) { return r.stock > 0; });
            pickerItems($picker, rows);
        }));
    }

    AM.pickerSet = function ($picker, row) {
        var source = $picker.data('source');
        if (row && source !== 'parts') AM.productCache[row.id] = row;
        $picker.find('input[type=hidden]').val(row ? row.id : '').trigger('change');
        $picker.find('.am-picker-input').val(row ? pickerLabel(row, source) : '');
        $picker.find('.am-picker-list').addClass('am-hidden');
    };

    AM.pickerSource = function ($picker, source, onlyStock) {
        $picker.data('source', source).attr('data-source', source).data('instock', !!onlyStock);
        AM.pickerSet($picker, null);
    };

    $(document)
        .on('focus', '.am-picker-input', function () { pickerSearch($(this).closest('.am-picker')); })
        .on('input', '.am-picker-input', function () {
            var $picker = $(this).closest('.am-picker');
            $picker.find('input[type=hidden]').val('').trigger('change');
            clearTimeout($picker.data('timer'));
            $picker.data('timer', setTimeout(function () { pickerSearch($picker); }, 250));
        })
        .on('keydown', '.am-picker-input', function (e) {
            if (e.key !== 'Enter') return;
            e.preventDefault();
            var $picker = $(this).closest('.am-picker'), rows = $picker.data('rows') || [];
            if (rows.length) AM.pickerSet($picker, rows[0]);
        })
        .on('mousedown', '.am-picker-item', function (e) {
            e.preventDefault();
            var $picker = $(this).closest('.am-picker');
            AM.pickerSet($picker, ($picker.data('rows') || [])[$(this).data('i')]);
        })
        .on('blur', '.am-picker-input', function () {
            var $list = $(this).closest('.am-picker').find('.am-picker-list');
            setTimeout(function () { $list.addClass('am-hidden'); }, 150);
        });

    /** Thanh phân trang: nút có data-page. */
    AM.pagerHtml = function (page, pages, total, unit) {
        var btns = [], from = Math.max(1, page - 2), to = Math.min(pages, page + 2);
        btns.push('<button type="button" data-page="' + (page - 1) + '"' + (page <= 1 ? ' disabled' : '') + '>‹</button>');
        if (from > 1) btns.push('<button type="button" data-page="1">1</button>' + (from > 2 ? '<span>…</span>' : ''));
        for (var i = from; i <= to; i++) btns.push('<button type="button" data-page="' + i + '"' + (i === page ? ' class="active"' : '') + '>' + i + '</button>');
        if (to < pages) btns.push((to < pages - 1 ? '<span>…</span>' : '') + '<button type="button" data-page="' + pages + '">' + pages + '</button>');
        btns.push('<button type="button" data-page="' + (page + 1) + '"' + (page >= pages ? ' disabled' : '') + '>›</button>');
        return '<div class="am-pager"><span>' + total.toLocaleString('vi-VN') + ' ' + (unit || 'bản ghi') + ' · trang ' + page + '/' + pages + '</span>' +
            '<div class="am-pager-btns">' + btns.join('') + '</div></div>';
    };

    AM.staffByRole = function (role) {
        return (AM.data.staff || []).filter(function (u) { return u.role === role && u.active; })
            .map(function (u) { return u.name; });
    };

    /* ---------- Form popup dùng chung ----------
     * AM.form.open({
     *   title, sub, width, submitLabel,
     *   fields: [{ name, label, type: text|number|email|tel|password|date|select|textarea|html,
     *              options: [[value, label]], placeholder, required, readonly, full, hint, min, max }],
     *   values: { name: value },
     *   onChange(api, changedName), validate(data, api) → { name: message }, onSubmit(data, api) → false để giữ popup
     * })
     */
    function optionsHtml(options, value) {
        return (options || []).map(function (o) {
            var v = Array.isArray(o) ? o[0] : o.value, l = Array.isArray(o) ? o[1] : o.label;
            return '<option value="' + AM.esc(v) + '"' + (String(v) === String(value) ? ' selected' : '') + '>' + AM.esc(l) + '</option>';
        }).join('');
    }

    function selectOptions(f, value) {
        return (f.placeholder !== undefined ? '<option value="">' + AM.esc(f.placeholder) + '</option>' : '') + optionsHtml(f.options, value);
    }

    function fieldHtml(f, value) {
        if (f.type === 'html') {
            return '<div class="am-field am-field-full" data-html="' + f.name + '">' + (f.html || '') + '</div>';
        }
        var v = value === undefined || value === null ? '' : value;
        var attrs = ' name="' + f.name + '"' + (f.readonly ? ' readonly' : '') +
            (f.min !== undefined ? ' min="' + f.min + '"' : '') + (f.max !== undefined ? ' max="' + f.max + '"' : '');
        var input;
        if (f.type === 'picker') {
            input = AM.pickerHtml(f.name, f.source, v, f.valueLabel, f.placeholder);
        } else if (f.type === 'select') {
            input = '<select class="am-input"' + attrs + '>' + selectOptions(f, v) + '</select>';
        } else if (f.type === 'textarea') {
            input = '<textarea class="am-textarea" style="height:' + (f.height || 64) + 'px"' + attrs +
                (f.placeholder ? ' placeholder="' + AM.esc(f.placeholder) + '"' : '') + '>' + AM.esc(v) + '</textarea>';
        } else {
            input = '<input class="am-input" type="' + (f.type || 'text') + '" value="' + AM.esc(v) + '"' + attrs +
                (f.placeholder ? ' placeholder="' + AM.esc(f.placeholder) + '"' : '') +
                (f.readonly ? ' style="background:#f8fafc;color:#6b7280"' : '') + '>';
        }
        return '<div class="am-field' + (f.full ? ' am-field-full' : '') + '" data-field="' + f.name + '">' +
            '<label>' + AM.esc(f.label) + (f.required ? ' <span class="am-req">*</span>' : '') + '</label>' + input +
            '<div class="am-field-hint">' + AM.esc(f.hint || '') + '</div><div class="am-field-error"></div></div>';
    }

    function ensureModal(id) {
        var $m = $('#' + id);
        if (!$m.length) {
            $m = $('<div class="am-modal am-hidden" id="' + id + '"><div class="am-modal-dialog"></div></div>').appendTo('body');
        }
        return $m;
    }

    AM.closeModals = function () { $('.am-modal').addClass('am-hidden'); };

    AM.form = {
        open: function (opts) {
            var $m = ensureModal('am-generic-form'), fields = opts.fields || [], values = opts.values || {};
            $m.find('.am-modal-dialog').css('width', (opts.width || 560) + 'px').html(
                '<div class="am-modal-head am-dark-gradient"><div>' +
                '<div style="font-weight:800;font-size:15px;color:#fff">' + AM.esc(opts.title) + '</div>' +
                (opts.sub ? '<div style="font-size:11px;color:rgba(255,255,255,0.5);margin-top:2px">' + AM.esc(opts.sub) + '</div>' : '') +
                '</div><button type="button" class="am-modal-close" data-action="close-modal">✕</button></div>' +
                '<form id="am-generic-form-el" class="am-modal-body" novalidate><div class="am-form-grid">' +
                fields.map(function (f) { return fieldHtml(f, values[f.name]); }).join('') +
                '</div><div class="am-pm-preview" data-preview></div></form>' +
                '<div class="am-modal-foot"><button type="button" class="am-cancel" data-action="close-modal">Hủy</button>' +
                '<button type="submit" form="am-generic-form-el" class="am-btn"' + (opts.submitColor ? ' style="background:' + opts.submitColor + ';color:#fff"' : '') + '>' +
                AM.esc(opts.submitLabel || '💾 Lưu') + '</button></div>'
            );
            var $form = $m.find('form');
            var api = {
                $form: $form,
                data: function () {
                    var d = {};
                    $.each($form.serializeArray(), function (_, f) { d[f.name] = $.trim(f.value); });
                    return d;
                },
                get: function (name) { return $.trim($form.find('[name="' + name + '"]').val() || ''); },
                set: function (name, value) {
                    $form.find('[name="' + name + '"]').val(value)
                        .closest('.am-field').removeClass('has-error').find('.am-field-error').text('');
                },
                options: function (name, options, placeholder) {
                    var $s = $form.find('[name="' + name + '"]'), cur = $s.val();
                    $s.html(selectOptions({ options: options, placeholder: placeholder }, cur));
                },
                hint: function (name, text) { $form.find('[data-field="' + name + '"] .am-field-hint').text(text || ''); },
                picker: function (name) { return $form.find('input[type=hidden][name="' + name + '"]').closest('.am-picker'); },
                toggle: function (name, show) { $form.find('[data-field="' + name + '"]').toggleClass('am-hidden', !show); },
                html: function (name, html) { $form.find('[data-html="' + name + '"]').html(html); },
                preview: function (html) { $form.find('[data-preview]').html(html || ''); },
                error: function (name, msg) {
                    $form.find('[data-field="' + name + '"]').addClass('has-error').find('.am-field-error').text(msg);
                }
            };
            var changed = function (name) { if (opts.onChange) opts.onChange(api, name); };

            $form.on('input change', 'input,select,textarea', function () {
                $(this).closest('.am-field').removeClass('has-error').find('.am-field-error').text('');
                changed(this.name);
            }).on('submit', function (e) {
                e.preventDefault();
                $form.find('.am-field').removeClass('has-error').find('.am-field-error').text('');
                var data = api.data(), errors = {};
                fields.forEach(function (f) {
                    if (f.required && data[f.name] === '' && !$form.find('[data-field="' + f.name + '"]').hasClass('am-hidden')) {
                        errors[f.name] = (f.type === 'select' ? 'Vui lòng chọn ' : 'Vui lòng nhập ') + f.label.toLowerCase();
                    }
                });
                if (opts.validate) {
                    $.each(opts.validate(data, api) || {}, function (k, msg) { if (!errors[k]) errors[k] = msg; });
                }
                if (!$.isEmptyObject(errors)) {
                    $.each(errors, function (k, msg) { api.error(k, msg); });
                    $form.find('.has-error').first().find('input,select,textarea').trigger('focus');
                    return;
                }
                var result = opts.onSubmit(data, api);
                if (result && typeof result.then === 'function') {
                    var $btn = $m.find('button[form="am-generic-form-el"]'), label = $btn.text();
                    $btn.prop('disabled', true).css('opacity', .6).text('⏳ Đang lưu...');
                    result.then(function () {
                        AM.closeModals();
                    }, function (err) {
                        if (err && !$.isEmptyObject(err.errors || {})) err.handled = true;
                        $.each((err && err.errors) || {}, function (k, msg) {
                            if ($form.find('[data-field="' + k + '"]').length) api.error(k, msg);
                            else AM.toast('⚠ ' + msg, false);
                        });
                    }).always(function () {
                        $btn.prop('disabled', false).css('opacity', '').text(label);
                    });
                } else if (result !== false) {
                    AM.closeModals();
                }
            });

            changed(null);
            $m.removeClass('am-hidden');
            $form.find('input:not([readonly]),select,textarea').first().trigger('focus');
            return api;
        }
    };

    /* ---------- Thanh toán / công nợ của đơn đại lý (dùng ở trang Đơn hàng và Đại lý) ---------- */
    AM.canEditDebt = function () { return AM.can('admin', 'kt_banhang', 'kt_congno'); };

    /** Nhãn ngắn trạng thái thanh toán của đơn: {label, color, bg} */
    AM.debtBadge = function (o) {
        if (o.type === 'warranty' || o.status === 'cancelled') return null;
        if (!o.debtMode) return { label: 'Chưa chọn TT', color: '#6b7280', bg: '#f1f5f9' };
        if (o.debtMode === 'paid') return { label: 'Đã thanh toán', color: '#059669', bg: '#d1fae5' };
        return { label: 'Nợ ' + AM.money(o.debt), color: '#ef4444', bg: '#fee2e2' };
    };

    AM.debtForm = function (o, onSaved) {
        var modes = AM.data.debtModes || {}, methods = AM.data.paidMethods || [];
        var dealer = AM.dealer(o.dealerId);
        var delivered = o.status === 'delivered';
        AM.form.open({
            title: '💰 Thanh toán / công nợ — ' + o.id,
            sub: (dealer ? dealer.name + ' · ' : '') + 'Giá trị đơn ' + AM.money(o.total),
            width: 520,
            submitLabel: '💾 Lưu công nợ',
            fields: [
                { name: 'mode', label: 'Hình thức thanh toán', type: 'select', required: true, full: true, placeholder: '— Chọn —',
                  options: $.map(modes, function (label, key) { return [[key, label]]; }) },
                { name: 'debtAmount', label: 'Số tiền còn nợ (đ)', type: 'number', min: 1, required: true, hint: 'Phần còn lại coi như đại lý đã trả' },
                { name: 'paidMethod', label: 'Đã trả bằng', type: 'select', required: true, placeholder: '— Chọn —', options: methods.map(function (m) { return [m, m]; }) },
                { name: 'note', label: 'Ghi chú', type: 'textarea', full: true, placeholder: 'VD: Đại lý trả trước 200tr, hẹn trả phần còn lại cuối tháng' }
            ],
            values: { mode: o.debtMode || 'full', debtAmount: o.debtMode === 'partial' ? o.debt : '', paidMethod: o.paidMethod || '', note: o.debtNote || '' },
            onChange: function (api) {
                var mode = api.get('mode');
                api.toggle('debtAmount', mode === 'partial');
                api.toggle('paidMethod', mode === 'partial' || mode === 'paid');
                var debt = mode === 'full' ? o.total : mode === 'paid' ? 0 : Math.max(0, +api.get('debtAmount') || 0);
                var paid = Math.max(0, o.total - debt);
                var delta = delivered ? debt - (o.debtRecorded || 0) : 0;
                var cur = dealer ? dealer.debt : 0;
                var html = '<div style="background:#f8fafc;border-radius:8px;padding:10px 14px;font-size:12px;color:#374151;line-height:1.8">' +
                    '<div style="display:flex;justify-content:space-between"><span>Giá trị đơn</span><b>' + AM.money(o.total) + '</b></div>' +
                    '<div style="display:flex;justify-content:space-between"><span>Đại lý đã trả</span><b style="color:#059669">' + AM.money(paid) + '</b></div>' +
                    '<div style="display:flex;justify-content:space-between"><span>Ghi nợ</span><b style="color:#ef4444">' + AM.money(debt) + '</b></div>';
                if (dealer) {
                    html += delivered
                        ? '<div style="border-top:1px dashed #e5e7eb;margin-top:6px;padding-top:6px">Dư nợ ' + AM.esc(AM.shortDealer(dealer.name)) + ': <b>' + AM.money(cur) + '</b> → <b style="color:' + (cur + delta > dealer.limit ? '#ef4444' : '#1a2035') + '">' + AM.money(cur + delta) + '</b>' +
                          (delta ? ' (' + (delta > 0 ? '+' : '−') + AM.money(Math.abs(delta)) + ')' : ' (không đổi)') + '</div>'
                        : '<div style="border-top:1px dashed #e5e7eb;margin-top:6px;padding-top:6px;color:#6b7280">Đơn chưa giao: số nợ sẽ được ghi vào công nợ đại lý khi giao xong.</div>';
                }
                api.preview(html + '</div>');
            },
            validate: function (v) {
                var e = {};
                if (v.mode === 'partial') {
                    var amt = +v.debtAmount;
                    if (!(amt > 0)) e.debtAmount = 'Vui lòng nhập số tiền còn nợ';
                    else if (amt >= o.total) e.debtAmount = 'Số nợ phải nhỏ hơn giá trị đơn. Nợ toàn bộ thì chọn "Ghi nợ cả đơn"';
                }
                return e;
            },
            onSubmit: function (v) {
                return AM.api('order.debt', { id: o.id, mode: v.mode, debtAmount: v.debtAmount, paidMethod: v.paidMethod, note: v.note }).done(function (data) {
                    if (onSaved) onSaved(data);
                });
            }
        });
    };

    AM.confirm = function (opts) {
        var $m = ensureModal('am-confirm');
        $m.find('.am-modal-dialog').css('width', (opts.width || 440) + 'px').html(
            '<div class="am-modal-head am-dark-gradient"><div style="font-weight:800;font-size:15px;color:#fff">' + AM.esc(opts.title) + '</div>' +
            '<button type="button" class="am-modal-close" data-action="close-modal">✕</button></div>' +
            '<div class="am-modal-body" style="font-size:13px;color:#374151;line-height:1.6">' + opts.message + '</div>' +
            '<div class="am-modal-foot"><button type="button" class="am-cancel" data-action="close-modal">Hủy</button>' +
            '<button type="button" class="am-btn" data-action="confirm-ok" style="background:' + (opts.okColor || '#0f2027') + ';color:' + (opts.okColor ? '#fff' : '#fbbf24') + '">' +
            AM.esc(opts.okLabel || 'Đồng ý') + '</button></div>'
        );
        $m.find('[data-action="confirm-ok"]').one('click', function () {
            AM.closeModals();
            opts.onOk();
        });
        $m.removeClass('am-hidden');
    };

    $(function () {
        $(document).on('click', '[data-action="close-modal"]', AM.closeModals)
            .on('mousedown', '.am-modal', function (e) { if (e.target === this) AM.closeModals(); })
            .on('keydown', function (e) { if (e.key === 'Escape') AM.closeModals(); });

        $('#am-role-select').on('change', function () {
            var url = new URL(window.location.href);
            url.searchParams.set('role', this.value);
            window.location.href = url.toString();
        });

        $(document).on('click', '.am-tabs .am-tab[data-tab]', function () {
            var $btn = $(this), group = $btn.closest('.am-tabs').data('group');
            $btn.addClass('active').siblings().removeClass('active');
            $('[data-tab-group="' + group + '"]').addClass('am-hidden')
                .filter('[data-tab-pane="' + $btn.data('tab') + '"]').removeClass('am-hidden');
        });
    });
})(window, jQuery);
