<?php

namespace frontend\components;

use backend\models\Config;

/** Thông tin liên hệ / thanh toán của 1Kho, lấy từ bảng config (sửa trong CMS → Cấu hình nội dung). */
class SiteInfo
{
    private static $contact;

    public static function contact()
    {
        if (self::$contact === null) {
            $value = Config::getConfigApp('CONTACT');
            self::$contact = array_merge(
                ['name' => '1Kho', 'address' => '', 'email' => '', 'phone' => '', 'facebook' => '', 'shopee' => '', 'zalo' => '', 'app_android' => '', 'app_ios' => ''],
                is_array($value) ? $value : []
            );
        }
        return self::$contact;
    }

    public static function get($key, $default = '')
    {
        $value = self::contact()[$key] ?? '';
        return $value !== '' && $value !== null ? $value : $default;
    }

    /** Số hotline hiển thị (VD 0912.345.567) và dạng tel: để bấm gọi. */
    public static function phone()
    {
        return (string)self::get('phone');
    }

    public static function tel()
    {
        return 'tel:' . preg_replace('/[^0-9+]/', '', self::phone());
    }

    public static function bank()
    {
        $bank = Config::getConfigApp('BANK_PAYMENT');
        return is_array($bank) ? array_merge(['stk' => '', 'ten_tk' => '', 'ten_bank' => ''], $bank) : null;
    }
}
