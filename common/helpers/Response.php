<?php
namespace common\helpers;

use Yii;
class Response
{
    const RESPONSE_CODE_SUCC = 0;
    const RESPONSE_CODE_ERR  = 1;
    const PHONE_MIN_LENGTH   = 9;
    const PHONE_MAX_LENGTH   = 12;
    const KEY_REQUIRED       = 'required';
    const KEY_INVALID        = 'invalid';
    const KEY_FORBIDDEN      = 'forbidden';
    const KEY_NOT_FOUND      = 'not_found';
    const KEY_SYS_ERR        = 'sys_err';
    const KEY_EXISTS         = 'exists';
    public static $listErrorByField = array(
        self::KEY_REQUIRED   => [
            'user_id'        => 'Mã tài khoản không được trống',
            'fullname'       => 'Họ tên không được trống',
            'phone'          => 'Số điện thoại không được trống',
            'address'        => 'Địa chỉ không được trống',
            'district'       => 'Quận/Huyện không được trống',
            'province'       => 'Thành Phố không được trống',
            'apple_id'       => 'Apple Id không được trống',
            'gg_id'          => 'Google Id không được trống',
            'fb_id'          => 'Facebook Id không được trống',
            'token'          => 'Token không được trống',
            'did'            => 'Mã thiết bị không được trống',
            'clientver'      => 'Phiên bản App không được trống',
            'cate_parent_id' => 'Mã danh mục cha không được trống',
            'product_tab'    => "Tab sản phẩm không được trống"
        ],
        self::KEY_INVALID    => [
            'phone'          => 'Số điện thoại không hợp lệ',
            'apple_id'       => 'Apple Id không hợp lệ',
            'gg_id'          => 'Token Google không hợp lệ',
            'fb_id'          => 'Token Facebook không hợp lệ',
            'token'          => 'Token không chính xác hoặc đã hết hạn',
            'authorization'  => 'AccessToken không hợp lệ',
            'request_method' => 'Phương thức yêu cầu không hợp lệ'
        ],
        self::KEY_FORBIDDEN   => [
            'banned'         => 'Tài khoản đang bị khoá',
            'info'           => 'Xác thực không thành công',
        ],
        self::KEY_NOT_FOUND  => [
            'account'        => 'Thông tin khách hàng không tồn tại',
            'category'       => 'Danh mục sản phẩm không tồn tại',
            'voucher'        => 'Voucher không tồn tại',
            'agent'          => 'Thông tin đại lý không tồn tại',
        ],
        self::KEY_EXISTS  => [
            'phone'          => 'Số điện thoại đã được sử dụng',
        ],
        self::KEY_SYS_ERR    => [
            'sys'            => 'Lỗi! Vui lòng thử lại sau',
        ],
    );

    public static function getErrorMessage($field, $keyError){
        $listErr = self::$listErrorByField;
        
        if( isset($listErr[$keyError]) && isset($listErr[$keyError][$field]) )
            return $listErr[$keyError][$field];

        return 'Lỗi! Không xác định';
    }

    public static function returnResponse($code, $data = [], $errorList = []){
        return [
            'errorCode' => $code,
            'errorMessage' => $code == self::RESPONSE_CODE_SUCC ? 'Thành công' : 'Lỗi',
            'errorList' => array_values($errorList),
            'data' => $data
        ];
    }
}
