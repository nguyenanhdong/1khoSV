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

    /* ---------- Ô nhập tiền: hiển thị 285.000.000, .val() vẫn trả "285000000" ---------- */
    AM.moneyInput = function (v) {
        var digits = String(v == null ? '' : v).replace(/\D/g, '').replace(/^0+(?=\d)/, '');
        return digits.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    };
    var textHook = $.valHooks.text;
    $.valHooks.text = {
        get: function (el) {
            if ($(el).hasClass('am-money')) return el.value.replace(/\D/g, '');
            return textHook && textHook.get ? textHook.get(el) : undefined;
        },
        set: function (el, v) {
            if ($(el).hasClass('am-money')) { el.value = AM.moneyInput(v); return true; }
            return textHook && textHook.set ? textHook.set(el, v) : undefined;
        }
    };
    $(document).on('input', 'input.am-money', function () {
        // Định dạng lại khi gõ, giữ con trỏ sau đúng số chữ số đã gõ
        var el = this, pos = el.selectionStart || 0, before = el.value.slice(0, pos).replace(/\D/g, '').length;
        var out = AM.moneyInput(el.value);
        if (out === el.value) return;
        el.value = out;
        var i = 0, seen = 0;
        while (i < out.length && seen < before) { if (/\d/.test(out[i])) seen++; i++; }
        try { el.setSelectionRange(i, i); } catch (e) {}
    });
    /** HTML thuộc tính cho ô tiền */
    AM.MONEY_ATTRS = 'type="text" inputmode="numeric" autocomplete="off"';

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

    /* ---------- Bảng nhiều dòng dùng chung: phiếu nhập / phiếu xuất kho / đơn lắp ráp ----------
     * Dòng thường (máy hoặc linh kiện) + nhóm "⚙ LK theo máy": chọn máy + số máy → linh kiện theo định mức tự thêm thành các dòng con.
     * opts: kinds ['product','part'] loại dòng thường; bom: cho phép nhóm theo máy; machinesOnly: mọi dòng chính là máy (đơn lắp ráp);
     *       price: có cột đơn giá (phiếu nhập); priceOf(kind, item); instock: chỉ gợi ý hàng còn tồn; stockCheck: cảnh báo thiếu tồn; onChange()
     */
    AM.bomCache = {};
    AM.loadBom = function (code) {
        if (AM.bomCache[code]) return $.Deferred().resolve(AM.bomCache[code]).promise();
        return AM.lookup('product-bom', { code: code }).then(function (res) { return (AM.bomCache[code] = res.rows || []); });
    };

    /** Link mở form sửa sản phẩm (tab mới) để khai báo linh kiện cấu thành */
    AM.bomLink = function (code) {
        return AM.can('admin', 'sale', 'kt_congno')
            ? ' · <a href="' + AM.esc(AM.url('products', { edit: code })) + '" target="_blank" rel="noopener" style="color:#2563eb;font-weight:700">Khai báo ngay ↗</a>'
            : ' (Sản phẩm → Sửa → Linh kiện cấu thành)';
    };

    var LX_KINDS = { product: '🚜 Máy', part: '🔩 Linh kiện', bom: '⚙ LK theo máy' };

    function lxOpts($root) { return $root.data('lx') || {}; }
    function lxShowKind(o) { return !o.machinesOnly && ((o.kinds || []).length > 1 || o.bom); }

    function lxPicker(kind, code, label, o, placeholder) {
        var html = AM.pickerHtml('lx_item', kind === 'part' ? 'parts' : 'products', code || '', label || '',
            placeholder || (kind === 'part' ? 'Gõ tên / mã linh kiện...' : 'Gõ tên / mã máy...'));
        return o.instock && kind !== 'bom' ? html.replace('class="am-picker"', 'class="am-picker" data-instock="true"') : html;
    }

    function lxKindSelect(o, kind) {
        var kinds = (o.kinds || []).concat(o.bom ? ['bom'] : []);
        return '<td class="lx-k"><select class="am-input" data-lx-kind>' + kinds.map(function (k) {
            return '<option value="' + k + '"' + (k === kind ? ' selected' : '') + '>' + LX_KINDS[k] + '</option>';
        }).join('') + '</select></td>';
    }

    function lxPriceCells(o) {
        return o.price ? '<td class="lx-p"><input ' + AM.MONEY_ATTRS + ' class="am-input am-money" data-lx-price placeholder="Đơn giá"></td><td class="lx-t" data-lx-total>—</td>' : '';
    }

    function lxItemRow(o, kind) {
        return '<tr data-lx="item" data-kind="' + kind + '">' + (lxShowKind(o) ? lxKindSelect(o, kind) : '') +
            '<td>' + lxPicker(kind, '', '', o) + '</td>' +
            '<td class="lx-q"><input type="number" class="am-input" data-lx-qty min="1" value="1"></td>' + lxPriceCells(o) +
            '<td class="lx-a"><button type="button" class="am-line-del" data-lx-del title="Xoá dòng">✕</button></td></tr>';
    }

    function lxGroupRow(o) {
        return '<tr data-lx="group" class="lx-group">' + (lxShowKind(o) ? lxKindSelect(o, 'bom') : '') +
            '<td>' + lxPicker('product', '', '', o, o.machinesOnly ? 'Gõ tên / mã máy cần lắp...' : 'Chọn máy để lấy linh kiện theo định mức...') +
            '<div class="lx-gnote" data-lx-gnote></div></td>' +
            '<td class="lx-q"><input type="number" class="am-input" data-lx-qty min="1" value="1" title="Số máy"></td>' + (o.price ? '<td></td><td></td>' : '') +
            '<td class="lx-a"><button type="button" class="am-line-add" data-lx-sub-add title="Thêm linh kiện cho máy này">+LK</button>' +
            '<button type="button" class="am-line-del" data-lx-del title="Xoá máy và linh kiện của máy">✕</button></td></tr>';
    }

    function lxSubRow(o, part, per, qty, auto) {
        return '<tr data-lx="sub" class="lx-sub" data-auto="' + (auto ? 1 : 0) + '" data-per="' + (per || 0) + '">' + (lxShowKind(o) ? '<td class="lx-k lx-arrow">↳</td>' : '') +
            '<td>' + (lxShowKind(o) ? '' : '<span class="lx-arrow">↳</span>') + lxPicker('part', part ? part.id : '', part ? part.name + ' (' + part.id + ')' : '', o) + '</td>' +
            '<td class="lx-q"><input type="number" class="am-input" data-lx-qty min="1" value="' + (qty || 1) + '"></td>' + lxPriceCells(o) +
            '<td class="lx-a"><button type="button" class="am-line-del" data-lx-del title="Xoá dòng">✕</button></td></tr>';
    }

    function lxSubs($group) { return $group.nextUntil('tr[data-lx="group"], tr[data-lx="item"]'); }

    function lxCode($tr) { return $tr.find('input[type=hidden][name=lx_item]').val(); }

    function lxFillGroup($root, $group) {
        var o = lxOpts($root), code = lxCode($group), $note = $group.find('[data-lx-gnote]');
        lxSubs($group).remove();
        if (!code) { $note.text(''); return lxChanged($root); }
        $note.text('⏳ Đang lấy định mức...');
        AM.loadBom(code).done(function (bom) {
            if (lxCode($group) !== code) return;
            var mq = +$group.find('[data-lx-qty]').val() || 1;
            lxSubs($group).remove();
            $group.after(bom.map(function (b) {
                return lxSubRow(o, AM.part(b.id) || { id: b.id, name: b.name }, b.qty, b.qty * mq, true);
            }).join(''));
            $note.html(bom.length ? bom.length + ' linh kiện theo định mức / máy'
                : '<span style="color:#b45309">⚠ Máy chưa khai báo linh kiện cấu thành' + AM.bomLink(code) + ' — hoặc bấm +LK để thêm tay</span>');
            lxChanged($root);
        });
    }

    function lxStock(kind, code) {
        if (kind === 'part') { var p = AM.part(code); return p ? p.stock : null; }
        var pr = AM.productCache[code]; return pr ? pr.stock : null;
    }

    function lxChanged($root) {
        var o = lxOpts($root), total = 0, need = {};
        $root.find('tbody tr[data-lx="item"], tbody tr[data-lx="sub"]').each(function () {
            var $r = $(this), kind = $r.data('lx') === 'sub' ? 'part' : $r.attr('data-kind'), code = lxCode($r), qty = +$r.find('[data-lx-qty]').val() || 0;
            if (o.price) {
                var price = +$r.find('[data-lx-price]').val() || 0;
                $r.find('[data-lx-total]').text(qty && price ? AM.money(qty * price) : '—');
                total += qty * price;
            }
            if (code && qty) {
                var k = kind + ':' + code;
                need[k] = need[k] || { kind: kind, code: code, qty: 0, name: $r.find('.am-picker-input').val() };
                need[k].qty += qty;
            }
        });
        var html = '';
        if (o.price && total) html += '<div class="am-row-between lx-total"><span>Tổng giá trị phiếu</span><b>' + AM.money(total) + '</b></div>';
        if (o.stockCheck) {
            var keys = Object.keys(need), short = keys.map(function (k) { return need[k]; }).filter(function (n) {
                var st = lxStock(n.kind, n.code); return st !== null && n.qty > st;
            });
            if (short.length) {
                html += '<div class="am-bom-warn">⚠ Không đủ tồn kho: ' + short.map(function (n) {
                    return AM.esc(n.name) + ' (còn ' + lxStock(n.kind, n.code) + ', cần ' + n.qty + ')';
                }).join('; ') + '</div>';
            } else if (keys.length) {
                html += '<div class="am-bom-ok">✓ Đủ tồn kho cho ' + keys.length + ' mặt hàng</div>';
            }
        }
        $root.find('.am-lx-sum').html(html);
        if (o.onChange) o.onChange();
    }

    AM.lx = {
        html: function (opts) {
            var o = opts || {}, kindCol = lxShowKind(o);
            var head = (kindCol ? '<th>Loại</th>' : '') + '<th>' + (o.machinesOnly ? 'Máy / linh kiện' : 'Hàng') + '</th><th>' + (o.machinesOnly ? 'SL' : 'SL') + '</th>' +
                (o.price ? '<th>Đơn giá (đ)</th><th style="text-align:right">Thành tiền</th>' : '') + '<th></th>';
            return '<div class="am-lx"><table class="am-lines am-lx-table"><thead><tr>' + head + '</tr></thead><tbody></tbody></table>' +
                '<div class="am-lx-bar"><button type="button" class="am-btn am-btn-sm am-lx-btn" data-lx-add>+ ' + (o.machinesOnly ? 'Thêm máy' : 'Thêm dòng') + '</button>' +
                (o.bom ? '<button type="button" class="am-btn am-btn-sm am-lx-btn" data-lx-add-bom>⚙ + Linh kiện theo máy</button>' : '') +
                '<span class="am-lx-hint">' + (o.machinesOnly ? 'Chọn máy → linh kiện theo định mức tự thêm bên dưới (sửa / xoá / +LK được)' : 'Chọn xong dòng cuối sẽ tự thêm dòng mới') + '</span></div>' +
                '<div class="am-lx-sum"></div></div>';
        },
        /** Khởi tạo trên $root (.am-lx); preset: [{kind, item: {id, name}, qty, price}] */
        init: function ($root, opts, preset) {
            $root.data('lx', opts || {});
            var o = lxOpts($root), $body = $root.find('tbody').empty();
            (preset || []).forEach(function (p) { AM.lx.add($root, p.kind, p.item, p.qty, p.price); });
            $body.append(o.machinesOnly ? lxGroupRow(o) : lxItemRow(o, o.defaultKind || o.kinds[0]));
            lxChanged($root);
        },
        /** Thêm một dòng hàng đã chọn (điền vào dòng trống cuối nếu có) */
        add: function ($root, kind, item, qty, price) {
            var o = lxOpts($root), $body = $root.find('tbody'), $last = $body.children('tr[data-lx="item"]').last();
            var $r = $last.length && !lxCode($last) && !$last.find('.am-picker-input').val() && $last.is(':last-child') ? $last : null;
            if ($r && $r.attr('data-kind') !== kind) { $r.replaceWith(lxItemRow(o, kind)); $r = null; }
            if (!$r) {
                $r = $(lxItemRow(o, kind));
                var $tail = $body.children('tr[data-lx="item"]').last();
                $tail.length && !lxCode($tail) && $tail.is(':last-child') ? $tail.before($r) : $body.append($r);
            }
            if (qty) $r.find('[data-lx-qty]').val(qty);
            if (price) $r.find('[data-lx-price]').val(price);
            AM.pickerSet($r.find('.am-picker'), item);
        },
        /** → { lines: [{kind, item, qty, price}], groups: [{product, qty}], error } */
        read: function ($root) {
            var out = { lines: [], groups: [], error: '' };
            $root.find('tbody tr').each(function () {
                var $r = $(this), type = $r.data('lx'), code = lxCode($r), qty = $.trim($r.find('[data-lx-qty]').val()), typed = $r.find('.am-picker-input').val();
                if (!code) { if (typed) out.error = 'Có dòng chưa chọn hàng từ danh sách gợi ý'; return; }
                if (!(+qty >= 1) || Math.floor(+qty) !== +qty) out.error = 'Số lượng phải là số nguyên ≥ 1';
                if (type === 'group') { out.groups.push({ product: code, qty: qty }); return; }
                var line = { kind: type === 'sub' ? 'part' : $r.attr('data-kind'), item: code, qty: qty };
                if (lxOpts($root).price) {
                    line.price = $r.find('[data-lx-price]').val();
                    if (!(+line.price > 0)) out.error = 'Đơn giá phải lớn hơn 0';
                }
                out.lines.push(line);
            });
            return out;
        },
        refresh: function ($root) { lxChanged($root); }
    };

    $(document)
        .on('change', '.am-lx [data-lx-kind]', function () {
            var $root = $(this).closest('.am-lx'), o = lxOpts($root), $r = $(this).closest('tr'), kind = this.value;
            if ($r.data('lx') === 'group') lxSubs($r).remove();
            $r.replaceWith(kind === 'bom' ? lxGroupRow(o) : lxItemRow(o, kind));
            lxChanged($root);
        })
        .on('change', '.am-lx input[type=hidden][name=lx_item]', function () {
            var $root = $(this).closest('.am-lx'), o = lxOpts($root), $r = $(this).closest('tr'), type = $r.data('lx'), code = this.value;
            if (type === 'group') {
                lxFillGroup($root, $r);
                // Đơn lắp ráp: chọn máy ở nhóm cuối → tự thêm nhóm máy trống
                if (o.machinesOnly && code && !$r.nextAll('tr[data-lx="group"]').length) $root.find('tbody').append(lxGroupRow(o));
                return;
            }
            if (o.price && code && $r.attr('data-pauto') !== '0') {
                var kind = type === 'sub' ? 'part' : $r.attr('data-kind'), item = kind === 'part' ? AM.part(code) : AM.productCache[code];
                if (item && o.priceOf) $r.find('[data-lx-price]').val(o.priceOf(kind, item) || '');
            }
            if (type === 'item' && code && $r.is(':last-child')) $r.after(lxItemRow(o, $r.attr('data-kind')));
            lxChanged($root);
        })
        .on('input', '.am-lx [data-lx-qty]', function () {
            var $root = $(this).closest('.am-lx'), $r = $(this).closest('tr');
            if ($r.data('lx') === 'group') {
                var mq = +this.value || 0;
                lxSubs($r).filter('[data-auto="1"]').each(function () { $(this).find('[data-lx-qty]').val(+$(this).data('per') * mq || ''); });
            } else if ($r.data('lx') === 'sub') {
                $r.attr('data-auto', '0');
            }
            lxChanged($root);
        })
        .on('input', '.am-lx [data-lx-price]', function () {
            $(this).closest('tr').attr('data-pauto', '0');
            lxChanged($(this).closest('.am-lx'));
        })
        .on('click', '.am-lx [data-lx-del]', function () {
            var $root = $(this).closest('.am-lx'), o = lxOpts($root), $r = $(this).closest('tr');
            if ($r.data('lx') === 'group') lxSubs($r).remove();
            $r.remove();
            var $body = $root.find('tbody');
            if (!$body.children().length) $body.append(o.machinesOnly ? lxGroupRow(o) : lxItemRow(o, o.defaultKind || o.kinds[0]));
            lxChanged($root);
        })
        .on('click', '.am-lx [data-lx-add]', function () {
            var $root = $(this).closest('.am-lx'), o = lxOpts($root);
            $root.find('tbody').append(o.machinesOnly ? lxGroupRow(o) : lxItemRow(o, o.defaultKind || o.kinds[0]));
        })
        .on('click', '.am-lx [data-lx-add-bom]', function () {
            var $root = $(this).closest('.am-lx');
            $root.find('tbody').append(lxGroupRow(lxOpts($root)));
        })
        .on('click', '.am-lx [data-lx-sub-add]', function () {
            var $root = $(this).closest('.am-lx'), $g = $(this).closest('tr'), $subs = lxSubs($g);
            ($subs.length ? $subs.last() : $g).after(lxSubRow(lxOpts($root), null, 0, 1, false));
        });

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
        } else if (f.type === 'money') {
            input = '<input class="am-input am-money" ' + AM.MONEY_ATTRS + ' value="' + AM.esc(AM.moneyInput(v)) + '"' + attrs +
                (f.placeholder ? ' placeholder="' + AM.esc(f.placeholder) + '"' : '') +
                (f.readonly ? ' style="background:#f8fafc;color:#6b7280"' : '') + '>';
        } else {
            input = '<input class="am-input" type="' + (f.type || 'text') + '" value="' + AM.esc(v) + '"' + attrs +
                (f.placeholder ? ' placeholder="' + AM.esc(f.placeholder) + '"' : '') +
                (f.readonly ? ' style="background:#f8fafc;color:#6b7280"' : '') + '>';
        }
        return '<div class="am-field' + (f.full ? ' am-field-full' : '') + '" data-field="' + f.name + '">' +
            '<label>' + AM.esc(f.label) + (f.required ? ' <span class="am-req">*</span>' : '') + '</label>' + input +
            '<div class="am-field-hint">' + (f.hintHtml || AM.esc(f.hint || '')) + '</div><div class="am-field-error"></div></div>';
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
                // Ô tiền là type=text nên tự kiểm tra min/max
                fields.forEach(function (f) {
                    if (f.type !== 'money' || errors[f.name] || data[f.name] === '' || $form.find('[data-field="' + f.name + '"]').hasClass('am-hidden')) return;
                    var n = +data[f.name];
                    if (f.min !== undefined && n < +f.min) errors[f.name] = 'Tối thiểu ' + AM.moneyInput(f.min) + 'đ';
                    else if (f.max !== undefined && n > +f.max) errors[f.name] = 'Tối đa ' + AM.moneyInput(f.max) + 'đ';
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

    /** 95 → "1 giờ 35 phút" */
    AM.formatMinutes = function (m) {
        m = Math.max(0, Math.round(+m || 0));
        if (m < 60) return m + ' phút';
        var h = Math.floor(m / 60);
        return h >= 24 ? Math.floor(h / 24) + ' ngày ' + (h % 24) + ' giờ' : h + ' giờ ' + (m % 60) + ' phút';
    };

    /** Nhãn ngắn trạng thái thanh toán của đơn: {label, color, bg} */
    AM.debtBadge = function (o) {
        // Công nợ chỉ áp dụng cho đơn bán của đại lý
        if (o.type !== 'new' || o.customerType === 'retail' || o.status === 'cancelled') return null;
        // Theo phiếu thu: chưa trả / còn nợ / đã thanh toán đủ
        var paid = o.paid || 0, debt = Math.max(0, (o.total || 0) - paid);
        if (debt <= 0) return { label: 'Đã thanh toán', color: '#059669', bg: '#d1fae5' };
        if (paid > 0) return { label: 'Còn nợ ' + AM.money(debt), color: '#b45309', bg: '#fef3c7' };
        return { label: 'Chưa trả', color: '#ef4444', bg: '#fee2e2' };
    };

    /* ---------- Phiếu thu theo đơn: mỗi lần đại lý trả = 1 phiếu, không sửa/xoá, chỉ huỷ có lý do ---------- */
    AM.canPay = function () { return AM.can('admin', 'sale', 'kt_congno'); };
    AM.canVoidPay = function () { return AM.can('admin', 'kt_congno'); };

    /** Ghi nhận một lần trả: nhập số tiền trả lần này, hệ thống tự tính còn nợ */
    AM.payForm = function (o, onSaved) {
        var methods = AM.data.paidMethods || [], dealer = AM.dealer(o.dealerId), delivered = o.status === 'delivered';
        var paid = o.paid || 0, debt = Math.max(0, o.total - paid);
        var times = (o.payments || []).filter(function (p) { return !p.voided; }).length;
        AM.form.open({
            title: '💰 Ghi nhận thanh toán — ' + o.id,
            sub: (dealer ? dealer.name + ' · ' : '') + 'Giá trị đơn ' + AM.money(o.total) + ' · còn nợ ' + AM.money(debt),
            width: 520,
            submitLabel: '💾 Ghi phiếu thu',
            submitColor: '#059669',
            fields: [
                { name: 'amount', label: 'Số tiền trả lần này (đ)', type: 'money', min: 1, max: debt, required: true,
                  hintHtml: '<a href="javascript:;" data-pay-all style="color:#2563eb;font-weight:700">Trả hết ' + AM.esc(AM.money(debt)) + '</a>' },
                { name: 'date', label: 'Ngày trả', type: 'date', required: true },
                { name: 'method', label: 'Hình thức', type: 'select', required: true, placeholder: '— Chọn —', options: methods.map(function (m) { return [m, m]; }) },
                { name: 'note', label: 'Ghi chú', placeholder: 'Số UNC, người nộp, hẹn trả phần còn lại...' }
            ],
            values: { date: AM.isoToday(), method: methods[0] || '' },
            onChange: function (api) {
                var a = Math.min(+api.get('amount') || 0, debt), left = debt - a, cur = dealer ? dealer.debt : 0;
                var row = function (l, v, c) { return '<div style="display:flex;justify-content:space-between"><span>' + l + '</span><b style="color:' + (c || '#1a2035') + '">' + v + '</b></div>'; };
                api.preview('<div style="background:#f8fafc;border-radius:8px;padding:10px 14px;font-size:12px;color:#374151;line-height:1.8">' +
                    row('Giá trị đơn', AM.money(o.total)) +
                    row('Đã trả trước đó' + (times ? ' (' + times + ' lần)' : ''), AM.money(paid), '#059669') +
                    row('Trả lần này', a ? AM.money(a) : '—', '#2563eb') +
                    row('Còn nợ sau lần này', AM.money(left), left > 0 ? '#ef4444' : '#059669') +
                    (dealer ? '<div style="border-top:1px dashed #e5e7eb;margin-top:6px;padding-top:6px;color:#6b7280">' + (delivered
                        ? 'Dư nợ ' + AM.esc(AM.shortDealer(dealer.name)) + ': <b style="color:#1a2035">' + AM.money(cur) + '</b> → <b style="color:#1a2035">' + AM.money(cur - a) + '</b>'
                        : 'Đơn chưa giao: khi giao xong chỉ ghi nợ phần còn lại vào công nợ đại lý.') + '</div>' : '') + '</div>');
            },
            validate: function (v) {
                var e = {};
                if (!(+v.amount > 0)) e.amount = 'Vui lòng nhập số tiền trả';
                else if (+v.amount > debt) e.amount = 'Vượt số còn nợ của đơn (' + AM.money(debt) + ')';
                return e;
            },
            onSubmit: function (v) {
                return AM.api('order.pay', { id: o.id, amount: v.amount, date: v.date, method: v.method, note: v.note }).done(function (data) {
                    if (onSaved) onSaved(data);
                });
            }
        }).$form.on('click', '[data-pay-all]', function () {
            $(this).closest('form').find('[name="amount"]').val(debt).trigger('input');
        });
    };

    AM.payVoidForm = function (o, pay, onSaved) {
        AM.form.open({
            title: '⊘ Huỷ phiếu thu ' + pay.code,
            sub: o.id + ' · ' + AM.money(pay.amount) + ' · ' + pay.method + ' · ngày ' + pay.date,
            width: 480,
            submitLabel: '⊘ Huỷ phiếu thu',
            submitColor: '#dc2626',
            fields: [
                { name: 'reason', label: 'Lý do huỷ', type: 'textarea', required: true, full: true, placeholder: 'VD: Nhập nhầm số tiền / ghi nhầm đơn / hoàn tiền cho đại lý...' },
                { name: 'info', type: 'html', html: '<div style="font-size:11px;color:#6b7280;line-height:1.6">Phiếu không bị xoá mà chuyển sang trạng thái <b>đã huỷ</b> (giữ lại lịch sử). ' +
                    'Số tiền này được cộng lại vào số còn nợ của đơn' + (o.status === 'delivered' ? ' và dư nợ đại lý' : '') + '. Nhập sai thì huỷ rồi ghi phiếu mới.</div>' }
            ],
            onSubmit: function (v) {
                return AM.api('order.payVoid', { code: pay.code, reason: v.reason }).done(function (data) {
                    if (onSaved) onSaved(data);
                });
            }
        });
    };

    /** Lịch sử phiếu thu của đơn (phiếu đã huỷ gạch ngang, có lý do) */
    AM.paymentsHtml = function (o) {
        var list = o.payments || [];
        if (!list.length) return '<div style="font-size:11px;color:#9ca3af;padding:4px 0">Chưa có lần trả nào</div>';
        return list.map(function (p) {
            return '<div class="am-pay' + (p.voided ? ' voided' : '') + '">' +
                '<div class="am-pay-main"><b>' + AM.money(p.amount) + '</b><span>' + AM.esc(p.date) + ' · ' + AM.esc(p.method) + '</span>' +
                (!p.voided && AM.canVoidPay() && o.status !== 'cancelled' ? '<button type="button" class="am-pay-void" data-pay-void="' + AM.esc(p.code) + '" title="Huỷ phiếu thu">⊘</button>' : '') + '</div>' +
                '<div class="am-pay-sub">' + AM.esc(p.code) + ' · ghi bởi ' + AM.esc(p.by || '—') + (p.note ? ' · ' + AM.esc(p.note) : '') + '</div>' +
                (p.voided ? '<div class="am-pay-void-note">Đã huỷ ' + AM.esc(p.voidedAt || '') + ' bởi ' + AM.esc(p.voidedBy || '—') + ': ' + AM.esc(p.voidReason || '') + '</div>' : '') +
                '</div>';
        }).join('');
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
