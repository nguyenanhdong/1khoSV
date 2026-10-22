/* 1Kho CMS – Báo cáo quản trị: biểu đồ doanh thu / lợi nhuận và cơ cấu kênh (SVG thuần, có tooltip) */
(function ($, AM) {
    'use strict';

    var R = window.AM_REPORT || {};
    // Bảng màu phân loại đã kiểm định (thứ tự cố định): 1 xanh dương, 2 cam, 3 xanh ngọc
    var SERIES = [
        { key: 'revenue', label: 'Doanh thu', color: '#2a78d6' },
        { key: 'profit', label: 'Lợi nhuận gộp', color: '#eb6834' }
    ];
    var NS = 'http://www.w3.org/2000/svg';

    function el(tag, attrs, parent) {
        var n = document.createElementNS(NS, tag);
        Object.keys(attrs || {}).forEach(function (k) { n.setAttribute(k, attrs[k]); });
        if (parent) parent.appendChild(n);
        return n;
    }

    /** Bước trục "đẹp": 1, 2, 2.5, 5 × 10^n */
    function niceMax(v) {
        if (v <= 0) return 1;
        var p = Math.pow(10, Math.floor(Math.log10(v))), n = v / p;
        return (n <= 1 ? 1 : n <= 2 ? 2 : n <= 2.5 ? 2.5 : n <= 5 ? 5 : 10) * p;
    }

    function trendChart() {
        var data = R.trend || [], $box = $('#am-rp-trend');
        if (!$box.length) return;
        var hasData = data.some(function (d) { return d.revenue || d.profit; });
        if (!hasData) {
            $box.html('<div class="am-empty" style="padding:50px 0;font-size:13px">Chưa có đơn bán hoàn thành trong kỳ</div>');
            return;
        }
        $box.empty();
        var W = $box.width() || 600, H = 260, pad = { l: 64, r: 96, t: 14, b: 30 };
        var iw = W - pad.l - pad.r, ih = H - pad.t - pad.b;
        var vals = [];
        data.forEach(function (d) { vals.push(d.revenue, d.profit); });
        var min = Math.min(0, Math.min.apply(null, vals)), max = niceMax(Math.max.apply(null, vals));
        var x = function (i) { return pad.l + (data.length === 1 ? iw / 2 : i * iw / (data.length - 1)); };
        var y = function (v) { return pad.t + ih - (v - min) / (max - min) * ih; };

        var svg = el('svg', { width: W, height: H, viewBox: '0 0 ' + W + ' ' + H, role: 'img', 'aria-label': 'Doanh thu và lợi nhuận gộp theo thời gian' });
        $box[0].appendChild(svg);
        // lưới & trục y (nhạt)
        for (var t = 0; t <= 4; t++) {
            var v = min + (max - min) * t / 4, yy = y(v);
            el('line', { x1: pad.l, x2: pad.l + iw, y1: yy, y2: yy, stroke: '#eef0f3', 'stroke-width': 1 }, svg);
            el('text', { x: pad.l - 8, y: yy + 4, 'text-anchor': 'end', 'font-size': 10, fill: '#6b7280' }, svg).textContent = AM.money(v);
        }
        // nhãn trục x (thưa)
        var every = Math.max(1, Math.ceil(data.length / 8));
        data.forEach(function (d, i) {
            if (i % every && i !== data.length - 1) return;
            el('text', { x: x(i), y: H - 8, 'text-anchor': 'middle', 'font-size': 10, fill: '#6b7280' }, svg).textContent = d.label;
        });
        // đường dữ liệu 2px + nhãn trực tiếp ở điểm cuối
        SERIES.forEach(function (s) {
            var dPath = data.map(function (d, i) { return (i ? 'L' : 'M') + x(i).toFixed(1) + ' ' + y(d[s.key]).toFixed(1); }).join(' ');
            el('path', { d: dPath, fill: 'none', stroke: s.color, 'stroke-width': 2, 'stroke-linejoin': 'round', 'stroke-linecap': 'round' }, svg);
            var last = data[data.length - 1];
            el('circle', { cx: x(data.length - 1), cy: y(last[s.key]), r: 4, fill: s.color, stroke: '#fff', 'stroke-width': 2 }, svg);
        });
        var lastIdx = data.length - 1, labelYs = SERIES.map(function (s) { return y(data[lastIdx][s.key]); });
        if (Math.abs(labelYs[0] - labelYs[1]) < 14) labelYs[1] = labelYs[0] + 14;
        SERIES.forEach(function (s, i) {
            el('text', { x: x(lastIdx) + 8, y: labelYs[i] + 4, 'font-size': 11, 'font-weight': 700, fill: '#374151' }, svg).textContent = s.label;
        });

        // lớp hover: đường dóng + tooltip
        var cross = el('line', { y1: pad.t, y2: pad.t + ih, stroke: '#9ca3af', 'stroke-width': 1, 'stroke-dasharray': '3 3', visibility: 'hidden' }, svg);
        var dots = SERIES.map(function (s) { return el('circle', { r: 5, fill: s.color, stroke: '#fff', 'stroke-width': 2, visibility: 'hidden' }, svg); });
        var $tip = $('<div class="am-rp-tip"></div>').appendTo($box);
        var hit = el('rect', { x: pad.l, y: pad.t, width: iw, height: ih, fill: 'transparent' }, svg);
        hit.addEventListener('mousemove', function (e) {
            var rect = svg.getBoundingClientRect(), mx = e.clientX - rect.left;
            var i = data.length === 1 ? 0 : Math.round((mx - pad.l) / iw * (data.length - 1));
            i = Math.max(0, Math.min(data.length - 1, i));
            var d = data[i], cx = x(i);
            cross.setAttribute('x1', cx); cross.setAttribute('x2', cx); cross.setAttribute('visibility', 'visible');
            SERIES.forEach(function (s, k) { dots[k].setAttribute('cx', cx); dots[k].setAttribute('cy', y(d[s.key])); dots[k].setAttribute('visibility', 'visible'); });
            $tip.html('<div class="h">' + AM.esc(d.label) + '</div>' + SERIES.map(function (s) {
                return '<div><i style="background:' + s.color + '"></i>' + s.label + ': <b>' + AM.money(d[s.key]) + '</b></div>';
            }).join('')).css({ left: Math.min(cx + 12, W - 190), top: 10 }).show();
        });
        hit.addEventListener('mouseleave', function () {
            cross.setAttribute('visibility', 'hidden');
            dots.forEach(function (d) { d.setAttribute('visibility', 'hidden'); });
            $tip.hide();
        });
        // chú thích (luôn có khi ≥ 2 đường)
        $box.append('<div class="am-rp-legend">' + SERIES.map(function (s) {
            return '<span><i style="background:' + s.color + '"></i>' + s.label + '</span>';
        }).join('') + '</div>');
    }

    function channelBar() {
        var c = R.channels || {}, $box = $('#am-rp-channels');
        var parts = [
            { label: 'Đại lý', value: c.dealer || 0, color: '#2a78d6' },
            { label: 'Khách lẻ', value: c.retail || 0, color: '#eb6834' },
            { label: 'Sàn 1kho', value: c.market || 0, color: '#1baf7a' }
        ];
        var total = parts.reduce(function (s, p) { return s + p.value; }, 0);
        if (!total) {
            $box.html('<div class="am-empty" style="padding:20px 0;font-size:12px">Chưa có doanh thu trong kỳ</div>');
            return;
        }
        var bar = '<div class="am-rp-stack">' + parts.filter(function (p) { return p.value > 0; }).map(function (p) {
            var pct = p.value / total * 100;
            return '<span style="width:' + pct + '%;background:' + p.color + '" title="' + AM.esc(p.label + ': ' + AM.money(p.value) + ' (' + pct.toFixed(1) + '%)') + '"></span>';
        }).join('') + '</div>';
        var rows = parts.map(function (p) {
            return '<div class="am-rp-chrow"><span><i style="background:' + p.color + '"></i>' + p.label + '</span><b>' + AM.money(p.value) + '</b><em>' +
                (total ? Math.round(p.value / total * 100) : 0) + '%</em></div>';
        }).join('');
        $box.html(bar + rows + '<div class="am-rp-chrow tot"><span>Tổng</span><b>' + AM.money(total) + '</b><em></em></div>');
    }

    $(function () {
        trendChart();
        channelBar();
        var timer;
        $(window).on('resize', function () { clearTimeout(timer); timer = setTimeout(trendChart, 200); });
    });
})(jQuery, window.AM);
