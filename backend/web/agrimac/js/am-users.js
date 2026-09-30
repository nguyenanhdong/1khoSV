/* AgriMac – Tài khoản & phân quyền */
(function ($, AM) {
    'use strict';

    var staff = AM.data.staff;
    var ROLES = window.AM_ROLES || {};
    var APP_ROLES = ['sale', 'delivery'];

    function byId(id) {
        return staff.filter(function (u) { return u.id === id; })[0] || null;
    }

    function roleOptions() {
        return $.map(ROLES, function (r, key) { return [[key, r.icon + ' ' + r.label]]; });
    }

    function renderRows() {
        $('#am-user-rows').html(staff.map(function (u, i) {
            var r = ROLES[u.role] || { color: '#6b7280', icon: '', label: u.role };
            return '<tr style="background:' + (i % 2 === 0 ? '#fff' : '#fafafa') + '"><td><div style="display:flex;align-items:center;gap:9px">' +
                '<div style="width:30px;height:30px;border-radius:50%;background:' + r.color + '22;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:12px;color:' + r.color + ';flex-shrink:0">' +
                AM.esc(AM.lastWord(u.name).charAt(0)) + '</div><div>' +
                '<div style="font-weight:700;font-size:12px">' + AM.esc(u.name) + '</div>' +
                '<div style="font-size:10px;color:#9ca3af">' + AM.esc(u.id) + (u.username ? ' · @' + AM.esc(u.username) : '') + '</div></div></div></td>' +
                '<td><span style="font-size:11px;background:' + r.color + '18;color:' + r.color + ';padding:3px 8px;border-radius:6px;font-weight:700">' + r.icon + ' ' + AM.esc(r.label) + '</span></td>' +
                '<td style="font-size:12px;color:#6b7280">' + AM.esc(u.email) + '</td>' +
                '<td>' + (u.active ? AM.badge('✓ Có quyền', '#059669', '#d1fae5') : AM.badge('✕ Bị chặn', '#9ca3af', '#f1f5f9')) + '</td>' +
                '<td>' + (APP_ROLES.indexOf(u.role) >= 0 ? AM.badge('✓ App', '#3b82f6', '#dbeafe') : '<span style="color:#d1d5db;font-size:11px">—</span>') + '</td>' +
                '<td style="font-size:11px;color:#6b7280">' + AM.esc(u.login || '—') + '</td>' +
                '<td>' + (u.active ? AM.badge('Đang hoạt động', '#059669', '#d1fae5') : AM.badge('Đã khóa', '#ef4444', '#fee2e2')) + '</td>' +
                '<td><button class="am-btn am-btn-sm" data-perm="' + u.id + '">Phân quyền</button></td></tr>';
        }).join(''));
    }

    function roleHint(role) {
        var pages = window.AM_ROLE_PAGES ? window.AM_ROLE_PAGES[role] : null;
        return pages ? 'Truy cập: ' + pages.join(', ') + (APP_ROLES.indexOf(role) >= 0 ? ' · có dùng App' : '') : '';
    }

    function openCreateForm() {
        AM.form.open({
            title: '👥 Tạo tài khoản nhân viên',
            sub: 'Vai trò quyết định menu và thao tác được phép — xem ma trận phân quyền bên phải',
            width: 580,
            submitLabel: '💾 Tạo tài khoản',
            fields: [
                { name: 'role', label: 'Vai trò', type: 'select', required: true, full: true, placeholder: '— Chọn vai trò —', options: roleOptions() },
                { name: 'name', label: 'Họ và tên', required: true, full: true, placeholder: 'VD: Nguyễn Văn Hải' },
                { name: 'username', label: 'Tên đăng nhập', required: true, placeholder: 'VD: hai.nv', hint: 'Chữ thường, số, dấu . hoặc _' },
                { name: 'email', label: 'Email', type: 'email', required: true, placeholder: 'VD: hai@agrimac.vn' },
                { name: 'phone', label: 'Số điện thoại', type: 'tel' },
                { name: 'active', label: 'Trạng thái', type: 'select', required: true, options: [['1', 'Đang hoạt động'], ['0', 'Khóa']] },
                { name: 'password', label: 'Mật khẩu', type: 'password', required: true, hint: 'Tối thiểu 8 ký tự, có chữ và số' },
                { name: 'password2', label: 'Xác nhận mật khẩu', type: 'password', required: true }
            ],
            values: { active: '1' },
            onChange: function (api, name) {
                if (name === 'role' || name === null) api.hint('role', roleHint(api.get('role')));
                if (name === 'email' && !api.get('username')) {
                    var guess = api.get('email').split('@')[0].toLowerCase().replace(/[^a-z0-9._]/g, '');
                    if (guess) api.set('username', guess);
                }
            },
            validate: function (v) {
                var e = {};
                if (v.username && !/^[a-z0-9._]{3,30}$/.test(v.username)) e.username = 'Chỉ gồm chữ thường, số, . _ (3–30 ký tự)';
                else if (staff.some(function (u) { return u.username === v.username; })) e.username = 'Tên đăng nhập đã tồn tại';
                if (v.email && !AM.isEmail(v.email)) e.email = 'Email không hợp lệ';
                else if (staff.some(function (u) { return u.email.toLowerCase() === v.email.toLowerCase(); })) e.email = 'Email đã được dùng';
                if (v.phone && !AM.isPhone(v.phone)) e.phone = 'Số điện thoại không hợp lệ';
                if (v.password && !(v.password.length >= 8 && /[a-zA-Z]/.test(v.password) && /\d/.test(v.password))) e.password = 'Tối thiểu 8 ký tự, gồm cả chữ và số';
                if (v.password2 && v.password2 !== v.password) e.password2 = 'Mật khẩu nhập lại không khớp';
                return e;
            },
            onSubmit: function (v) {
                return AM.api('user.create', v).done(renderRows);
            }
        });
    }

    function openPermissionForm(u) {
        AM.form.open({
            title: '🔑 Phân quyền — ' + u.name,
            sub: u.email,
            width: 480,
            submitLabel: '💾 Lưu phân quyền',
            fields: [
                { name: 'role', label: 'Vai trò', type: 'select', required: true, full: true,
                  options: u.superAdmin ? [['admin', '👑 Admin / CEO (super admin hệ thống)']] : roleOptions() },
                { name: 'active', label: 'Trạng thái tài khoản', type: 'select', required: true, full: true, options: [['1', 'Đang hoạt động'], ['0', 'Khóa (không đăng nhập được)']] }
            ],
            values: { role: u.role, active: u.active ? '1' : '0' },
            onChange: function (api) {
                api.hint('role', u.superAdmin ? 'Tài khoản super admin của hệ thống luôn có toàn quyền; chỉ đổi được trạng thái' : roleHint(api.get('role')));
                api.preview(api.get('active') === '0'
                    ? '<div style="font-size:11px;color:#ef4444">⚠ Tài khoản bị khóa sẽ đăng xuất khỏi CMS và App ngay.</div>' : '');
            },
            onSubmit: function (v) {
                return AM.api('user.permission', $.extend({}, v, { userId: u.dbId })).done(renderRows);
            }
        });
    }

    $(function () {
        renderRows();
        $('[data-action="new-user"]').on('click', openCreateForm);
        $('#am-user-rows').on('click', '[data-perm]', function () { openPermissionForm(byId($(this).data('perm'))); });
    });
})(jQuery, window.AM);
