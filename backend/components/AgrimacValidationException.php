<?php

namespace backend\components;

use yii\base\UserException;

/** Lỗi nghiệp vụ trả về cho form: errors = [tên ô => thông báo]. */
class AgrimacValidationException extends UserException
{
    public $errors;

    public function __construct(array $errors, $message = 'Dữ liệu chưa hợp lệ')
    {
        $this->errors = $errors;
        parent::__construct($message);
    }
}
