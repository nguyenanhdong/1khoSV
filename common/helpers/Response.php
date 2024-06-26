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
            'product_tab'    => "Tab sản phẩm không được trống",
            'notify_id'      => 'Mã thông báo không được trống',
            'product_id'     => 'Mã sản phẩm không được trống',
            'agent'          => 'Mã đại lý không được trống',
            'voucher_id'     => 'Mã voucher không được trống',
            'product_combination' => 'Danh sách mã sản phẩm không được trống',
            'social_id'      => 'ID mạng xã hội không được trống',
            'order_id'       => 'Mã đơn hàng không được trống',
            'video_image_review'    => 'Video, hình ảnh không được trống',
            'content_review' => 'Nội dung đánh giá không được trống',
            'image_upload'   => 'Hình ảnh không được trống',
            'video_upload'   => 'Video không được trống',
            'type_situation' => 'Tình huống đang gặp không được trống',
            'reason_refund'  => 'Lý do không được trống',
            'media_token'    => 'Token media không được trống',
            'title_adv'      => 'Tiêu đề không được trống',
            'type_adv'       => 'Loại tin rao vặt không được trống',
            'category_adv'   => 'Danh mục không được trống',
            'image_adv'      => 'Ảnh không được trống',
            'type_strain'    => 'Chủng loại không được trống',
            'load_capacity'  => 'Trọng tải không được trống',
            'state_adv'      => 'Tình trạng không được trống',
            'brand_name'     => 'Thương hiệu không được trống',
            'origin_adv'     => 'Xuất xứ không được trống',
            'description_adv'=> 'Loại không được trống',
            'kilometer_used' => 'Số Km sử dụng không được trống',
            'hours_of_use'   => 'Số giờ sử dụng không được trống',
            'production_year'=> 'Năm sản xuất không được trống',
            'fuel_adv'       => 'Nhiên liệu không được trống',
            'price_adv'      => 'Giá không được trống',
        ],
        self::KEY_INVALID    => [
            'phone'          => 'Số điện thoại không hợp lệ',
            'apple_id'       => 'Apple Id không hợp lệ',
            'gg_id'          => 'Token Google không hợp lệ',
            'fb_id'          => 'Token Facebook không hợp lệ',
            'token'          => 'Token không chính xác hoặc đã hết hạn',
            'authorization'  => 'AccessToken không hợp lệ',
            'request_method' => 'Phương thức yêu cầu không hợp lệ',
            'voucher'        => 'Voucher không hợp lệ',
            'order'          => 'Đơn hàng không hợp lệ',
            'social_id'      => 'ID mạng xã hội không hợp lệ',
            'video_image_review' => 'Video, hình ảnh số lượng tối đa là 6',
            'star_review'    => 'Số điểm đánh giá từ 1 đến 5',
            'content_review' => 'Nội dung đánh giá quá dài',
            'image_upload'   => 'Định dạng hình ảnh không hợp lệ. Chỉ chấp nhận hình ảnh có phần mở rộng là: .png, .jpg, .gif',
            'video_upload'   => 'Định dạng video không hợp lệ. Chỉ chấp nhận video có phần mở rộng là: .mp4, .flv, .m4a, .mov',
            'type_situation' => 'Tình huống đang gặp không hợp lệ',
            'order_expire_refund' => 'Lỗi! Đã quá thời gian gửi yêu cầu Trả hàng/Hoàn tiền',
            'media_token'    => 'Token media không hợp lệ',
            'type_adv'       => 'Loại tin rao vặt không hợp lệ',
            'state_adv'      => 'Tình trạng không hợp lệ',
            'category_adv'   => 'Danh mục không hợp lệ',
            'fuel_adv'       => 'Nhiên liệu không hợp lệ',
            'production_year'=> 'Năm sản xuất không hợp lệ',
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
            'notify'         => 'Thông báo không tồn tại',
            'product'        => 'Sản phẩm không tồn tại',
            'delivery_address' => 'Địa chỉ giao hàng không tồn tại',
            'order'          => 'Đơn hàng không tồn tại'
        ],
        self::KEY_EXISTS  => [
            'phone'          => 'Số điện thoại đã được sử dụng',
            'order_refund_pending'   => 'Bạn đã gửi yêu cầu trả hàng/hoàn tiền. Không thể gửi thêm yêu cầu',
            'order_refund_approve'    => 'Yêu cầu trả hàng/hoàn tiền đã được chấp nhận. Không thể gửi thêm yêu cầu',
            'order_refund_reject'    => 'Yêu cầu trả hàng/hoàn tiền đã bị từ chối. Không thể gửi thêm yêu cầu'
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
