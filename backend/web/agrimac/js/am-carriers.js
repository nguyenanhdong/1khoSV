/* 1Kho CMS – Nhà xe: thêm / sửa / xoá nhà xe để nhân viên giao hàng chọn khi gửi hàng */
(function ($, AM) {
    'use strict';

    var carriers = window.AM_CARRIER_LIST || [];
    var state = { search: '' };

    function byId(id) {
        return carriers.filter(function (c) { return c.id === id; })[0] || null;
    }

    function render() {
        var q = state.search.toLowerCase();
        var list = carriers.filter(function (c) {
            return !q || (c.name + ' ' + c.phone + ' ' + c.address + ' ' + c.note).toLowerCase().indexOf(q) >= 0;
        });
        $('#am-carrier-count').text(carriers.length + ' nhà xe');
        if (!list.length) {
            $('#am-carrier-rows').html('<tr><td colspan="7" class="am-empty" style="padding:30px">' +
                (carriers.length ? 'Không tìm thấy nhà xe phù hợp' : 'Chưa có nhà xe nào — bấm "+ Thêm nhà xe"') + '</td></tr>');
            return;
        }
        $('#am-carrier-rows').html(list.map(function (c) {
            return '<tr><td><b>' + AM.esc(c.name) + '</b></td>' +
                '<td>' + (c.phone ? '<a href="tel:' + AM.esc(c.phone.replace(/[^0-9+]/g, '')) + '">' + AM.esc(c.phone) + '</a>' : '—') + '</td>' +
                '<td style="font-size:12px">' + AM.esc(c.address || '—') + '</td>' +
                '<td style="font-size:12px;color:#6b7280;max-width:240px">' + AM.esc(c.note || '') + '</td>' +
                '<td style="text-align:center;font-weight:700">' + c.orders + '</td>' +
                '<td style="font-size:12px;color:#6b7280">' + AM.esc(c.lastShipped || '—') + '</td>' +
                '<td style="white-space:nowrap"><button class="am-btn am-btn-sm" data-edit="' + c.id + '">✎ Sửa</button> ' +
                '<button class="am-btn am-btn-sm" data-delete="' + c.id + '" style="background:#fee2e2;color:#ef4444">Xoá</button></td></tr>';
        }).join(''));
    }

    function openForm(c) {
        AM.form.open({
            title: c ? '✎ Sửa nhà xe' : '🚛 Thêm nhà xe',
            width: 520,
            submitLabel: '💾 Lưu',
            fields: [
                { name: 'name', label: 'Tên nhà xe', required: true, full: true, placeholder: 'VD: Nhà xe Phương Trang – tuyến Cần Thơ' },
                { name: 'phone', label: 'Số điện thoại', type: 'tel', placeholder: 'VD: 0901234567' },
                { name: 'address', label: 'Địa chỉ bến / tuyến', placeholder: 'VD: Bến xe Miền Tây' },
                { name: 'note', label: 'Ghi chú', type: 'textarea', full: true, placeholder: 'Giờ xe chạy, nhận hàng cồng kềnh không...' }
            ],
            values: c || {},
            validate: function (v) { return v.phone && !AM.isPhone(v.phone) ? { phone: 'Số điện thoại không hợp lệ' } : {}; },
            onSubmit: function (v) {
                return AM.api('carrier.save', $.extend({ id: c ? c.id : 0 }, v)).done(function (data) {
                    carriers = data.carriers || carriers;
                    render();
                });
            }
        });
    }

    $(function () {
        if (!$('#am-carrier-rows').length) return;
        render();
        $('[data-action="new-carrier"]').on('click', function () { openForm(null); });
        $('#am-carrier-search').on('input', function () { state.search = this.value; render(); });
        $('#am-carrier-rows')
            .on('click', '[data-edit]', function () { openForm(byId(+$(this).data('edit'))); })
            .on('click', '[data-delete]', function () {
                var c = byId(+$(this).data('delete'));
                AM.confirm({
                    title: 'Xoá nhà xe "' + c.name + '"?',
                    message: 'Nhà xe sẽ không còn trong danh sách chọn khi gửi hàng. Các đơn đã gửi vẫn giữ tên nhà xe.',
                    okLabel: 'Xoá', okColor: '#ef4444',
                    onOk: function () {
                        AM.api('carrier.delete', { id: c.id }).done(function (data) { carriers = data.carriers || carriers; render(); });
                    }
                });
            });
    });
})(jQuery, window.AM);
