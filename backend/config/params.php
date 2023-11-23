<?php
return [
    'adminEmail' => 'admin@example.com',
    'hour_warning_assign' => 2,
    'list_service' => [
        1 => 'Dọn nhà theo giờ',
        2 => 'Vận chuyển tháo lắp',
        3 => 'Sửa chữa nội thất',
        4 => 'Sửa chữa điện nước',
        5 => 'Dọn dẹp văn phòng',
        6 => 'Trông trẻ ngoài giờ',
        7 => 'Tìm kiếm đối tác',
        8 => 'Tìm kênh phân phối',
        9 => 'Sinh viên làm thêm',
        10=> 'Gia sư dạy kèm'
    ],
    'list_icon_service_notify' => [
        1 => '/uploads/images/notify/giup_viec_tai_nha.png',//'Dọn nhà theo giờ',
        2 => '/uploads/images/notify/van_chuyen_thao_lap.png',//'Vận chuyển tháo lắp',
        3 => '/uploads/images/notify/sua_chua_noi_that.png',//'Sửa chữa nội thất',
        4 => '/uploads/images/notify/sua_chua_dien_nuoc.png',//'Sửa chữa điện nước',
        5 => '/uploads/images/notify/don_dep_van_phong.png',//'Dọn dẹp văn phòng',
        6 => '/uploads/images/notify/trong_tre_ngoai_gio.png',//'Trông trẻ ngoài giờ',
        7 => '/uploads/images/notify/tim_kiem_doi_tac.png',//'Tìm kiếm đối tác',
        8 => '/uploads/images/notify/kenh_phan_phoi.png',//'Tìm kênh phân phối',
        9 => '/uploads/images/notify/sinh_vien_lam_them.png',//'Sinh viên làm thêm',
        10=> '/uploads/images/notify/gia_su_day_kem.png',//'Gia sư dạy kèm'
    ],
    'ward_address' => [
        'Phố Mới',
        'Duyên Hải',
        'Cốc Lếu',
        'Kim Tân',
        'Pom Hán',
        'Bắc Lệnh',
        'Thống Nhất',
        'Xuân Tăng',
        'Bắc Cường',
        'Nam Cường',
        'Bình Minh',
        'Vạn Hoà',
        'Đồng Tuyển',
        'Cam Đường',
        'Tả Phời',
        'Hợp Thành',
        'Cốc San',
    ],
    'num_staff_suggest' => [
        1 => '01 nhân viên ~ 30-60m2',
        2 => '02 nhân viên ~ 60-150m2',
        3 => '03 nhân viên ~ 150-250m2',
        4 => '04 nhân viên ~ 250-350m2',
        5 => '05 nhân viên ~ 350-450m2'
    ],
    'status_order' => [
        0 => 'Đang xử lý',
        1 => 'Đang chờ',
        6 => 'Đang diễn ra',
        2 => 'Hoàn thành',
        3 => 'Khách hàng huỷ',
        4 => 'Admin huỷ',
        5 => 'Hệ thống huỷ',
    ],
    'ca_lam' => [
        'ca_sang' => 'Ca sáng',
        'ca_chieu'=> 'Ca chiều',
        'ca_ngay' => 'Cả ngày'
    ],
    'status_by_service' => [
        3 => [
            0 => 'Đang thu gom',
            1 => 'Đã tìm thấy tài xế',
            2 => 'Đang giao hàng',
            3 => 'Đã giao hàng',
            4 => 'Đã Thanh toán',
            6 => 'Khách hàng huỷ',
            7 => 'Admin huỷ'
        ],
        7 => [
            0 => 'Thông tin',
            1 => 'Xác nhận',
            4 => 'Đã Thanh toán',
            6 => 'Khách hàng huỷ',
            7 => 'Admin huỷ'
        ],
        9 => [
            0 => 'Đang tìm nhân viên',
            1 => 'Đã tìm được NV',
            2 => 'Đang trong ca làm',
            3 => 'Ca làm hoàn tất',
            4 => 'Đã Thanh toán',
            5 => 'Không tìm đủ nhân viên',
            6 => 'Khách hàng huỷ',
            7 => 'Admin huỷ'
        ],
        10 => [
            0 => 'Đang tìm nhân viên',
            1 => 'Đã tìm được NV',
            2 => 'Đang trong ca làm',
            3 => 'Ca làm hoàn tất',
            4 => 'Đã Thanh toán',
            5 => 'Không tìm đủ nhân viên',
            6 => 'Khách hàng huỷ',
            7 => 'Admin huỷ'
        ],
        11 => [
            0 => 'Thông tin',
            1 => 'Xác nhận',
            4 => 'Khảo sát',
            6 => 'Khách hàng huỷ',
            7 => 'Admin huỷ'
        ],
        12 => [
            0 => 'Đang xác nhận',
            1 => 'Đã xác nhận',
            2 => 'Đang giao hàng',
            3 => 'Đã giao hàng',
            4 => 'Đã Thanh toán',
            6 => 'Khách hàng huỷ',
            7 => 'Admin huỷ'
        ],
        13 => [
            0 => 'Đang xác nhận',
            1 => 'Xác nhận',
            2 => 'Đang giặt',
            3 => 'Đã giặt',
            4 => 'Đã Thanh toán',
            6 => 'Khách hàng huỷ',
            7 => 'Admin huỷ'
        ],
        14 => [
            0 => 'Đang xác nhận',
            1 => 'Xác nhận',
            2 => 'Đã giao đồ',
            3 => 'Đang giặt',
            4 => 'Đang giao trả khách',
            5 => 'Thanh toán',
            6 => 'Khách hàng huỷ',
            7 => 'Admin huỷ'
        ]
    ]
];
