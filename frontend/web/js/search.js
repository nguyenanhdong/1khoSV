/* Tìm kiếm sản phẩm: gợi ý khi gõ ở header (desktop + mobile) và trang kết quả /product/search */
(function ($) {
    'use strict';

    var MIN_LENGTH = 2, DELAY = 250;

    function esc(s) {
        return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
        });
    }

    /** Bỏ dấu từng ký tự (giữ nguyên độ dài chuỗi để vị trí khớp trên bản bỏ dấu dùng được cho tên gốc). */
    function fold(s) {
        return Array.from(String(s)).map(function (c) {
            var f = c.normalize('NFD').replace(/[̀-ͯ]/g, '').replace(/[đĐ]/g, 'd').toLowerCase();
            return f.length === 1 ? f : c.toLowerCase();
        }).join('');
    }

    /** Tô đậm các từ khoá trong tên, không phân biệt hoa thường và dấu (khớp cách tìm phía server). */
    function highlight(name, q) {
        var chars = Array.from(String(name)), folded = fold(name), marks = new Array(chars.length);
        fold(q).split(/\s+/).filter(function (w) { return w.length > 0; }).forEach(function (w) {
            var from = 0, i;
            while ((i = folded.indexOf(w, from)) >= 0) {
                for (var k = i; k < i + w.length; k++) marks[k] = true;
                from = i + w.length;
            }
        });
        var out = '', open = false;
        chars.forEach(function (c, i) {
            if (marks[i] && !open) { out += '<b>'; open = true; }
            if (!marks[i] && open) { out += '</b>'; open = false; }
            out += esc(c);
        });
        return out + (open ? '</b>' : '');
    }

    function initBox($form) {
        var $input = $form.find('input[name=q]'), $box = $form.find('.search_suggest');
        var timer = null, xhr = null, cache = {}, active = -1, lastQ = '';

        function close() { $box.attr('hidden', true).empty(); active = -1; }

        function render(q, res) {
            if (!res.items.length) {
                $box.html('<div class="search_suggest_empty">Không tìm thấy sản phẩm cho "' + esc(q) + '"</div>').removeAttr('hidden');
                return;
            }
            var html = res.items.map(function (it) {
                return '<a class="search_suggest_item" href="' + esc(it.url) + '">' +
                    '<img src="' + esc(it.image || '/images/icon/logo.svg') + '" alt="" loading="lazy" onerror="this.onerror=null;this.src=\'/images/icon/logo.svg\'">' +
                    '<span class="search_suggest_info"><span class="search_suggest_name">' + highlight(it.name, q) + '</span>' +
                    '<span class="search_suggest_price">' + esc(it.price) + 'đ' + (it.priceOld ? ' <s>' + esc(it.priceOld) + 'đ</s>' : '') + '</span></span></a>';
            }).join('');
            if (res.total > res.items.length) {
                html += '<a class="search_suggest_more" href="' + esc(res.moreUrl) + '">Xem tất cả ' + res.total.toLocaleString('vi-VN') + ' kết quả cho "' + esc(q) + '"</a>';
            }
            $box.html(html).removeAttr('hidden');
            active = -1;
        }

        function lookup() {
            var q = $.trim($input.val());
            lastQ = q;
            if (q.length < MIN_LENGTH) { close(); return; }
            if (cache[q]) { render(q, cache[q]); return; }
            if (xhr) xhr.abort();
            $box.html('<div class="search_suggest_empty">Đang tìm...</div>').removeAttr('hidden');
            xhr = $.getJSON('/product/suggest', { q: q })
                .done(function (res) {
                    cache[q] = res;
                    if (q === lastQ) render(q, res);
                })
                .fail(function (x) { if (x.statusText !== 'abort') close(); })
                .always(function () { xhr = null; });
        }

        function move(step) {
            var $items = $box.find('a');
            if (!$items.length) return;
            active = (active + step + $items.length) % $items.length;
            $items.removeClass('active').eq(active).addClass('active');
        }

        $input.on('input', function () {
            clearTimeout(timer);
            timer = setTimeout(lookup, DELAY);
        }).on('focus', function () {
            if ($.trim(this.value).length >= MIN_LENGTH) lookup();
        }).on('keydown', function (e) {
            if ($box.is('[hidden]')) return;
            if (e.key === 'ArrowDown') { e.preventDefault(); move(1); }
            else if (e.key === 'ArrowUp') { e.preventDefault(); move(-1); }
            else if (e.key === 'Escape') { close(); }
            else if (e.key === 'Enter' && active >= 0) {
                e.preventDefault();
                window.location.href = $box.find('a').eq(active).attr('href');
            }
        });

        $form.on('submit', function (e) {
            if (!$.trim($input.val())) { e.preventDefault(); $input.trigger('focus'); }
        });

        $(document).on('mousedown touchstart', function (e) {
            if (!$form[0].contains(e.target)) close();
        });
    }

    /* ---------- Trang kết quả: đổi sắp xếp và "Xem thêm" bằng ajax ---------- */
    function initResultPage() {
        var $page = $('#search_result');
        if (!$page.length) return;
        var q = $page.data('q'), sort = 'popular', page = 0, loading = false;
        var $list = $('#search_product_list'), $more = $('#search_more');

        function load(append) {
            if (loading) return;
            loading = true;
            var $btn = $more.find('button').prop('disabled', true);
            if (append) $btn.append(' <i class="spinner-border spinner-border-sm"></i>');
            else $list.css('opacity', 0.5);
            $.getJSON('/product/get-product-search', { q: q, sort: sort, page: page })
                .done(function (res) {
                    if (append) $list.append(res.data); else $list.html(res.data);
                    $more.toggle(!!res.hasMore);
                })
                .fail(function () {
                    if (append) page--;
                    if (window.toastr) toastr.error('Không tải được sản phẩm, vui lòng thử lại');
                })
                .always(function () {
                    loading = false;
                    $list.css('opacity', '');
                    $btn.prop('disabled', false).find('.spinner-border').remove();
                });
        }

        $page.on('click', '.btn_sort_search', function () {
            if (loading || $(this).hasClass('active')) return;
            $page.find('.btn_sort_search').removeClass('active');
            $(this).addClass('active');
            sort = $(this).attr('sort');
            page = 0;
            load(false);
        });
        $more.on('click', 'button', function () {
            page++;
            load(true);
        });
    }

    $(function () {
        $('.js_search').each(function () { initBox($(this)); });
        initResultPage();
    });
})(jQuery);
