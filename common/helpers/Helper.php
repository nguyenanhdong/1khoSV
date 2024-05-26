<?php
namespace common\helpers;

use Yii;
class Helper
{
    public static function generateRandomString($length = 10)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return strtoupper($randomString);
    }

    public static function generateReferralCode($id, $maxLength = 6){
        $referral_code      = $id;
        $characters         = '0123456789';
        $charactersLength   = strlen($characters);
        $length             = $maxLength - strlen($id);
        for ($i = 0; $i < $length; $i++) {
            $referral_code .= $characters[rand(0, $charactersLength - 1)];
        }
        return $referral_code;
    }

    public static function uploadFile($name){
        $params = $_POST;
        $file = $_FILES;
        if( !isset($file[$name]) || empty($file[$name]) ){
            return [
                'status' => false,
                'message'=> Response::getErrorMessage($name . "_upload", Response::KEY_REQUIRED)
            ];
        }
        $fileUpload = $file[$name];
        $file_name  = time() . '_' . preg_replace('/[^a-zA-Z0-9-.]+/', '_', $fileUpload['name']);
        $file_type  = $fileUpload['type'];
        $file_size  = $fileUpload['size'];
        $maxSizeImage = 1048576;//1MB
        $maxSizeVideo = 20971520;//20MB
        if( $name == 'image' ){
            if( $file_size > $maxSizeImage ){
                return [
                    'status' => false,
                    'message'=> "Dung lượng ảnh quá lớn. Tối đa 1 MB"
                ];
            }
        }else if( $name == 'video' ){
            if( $file_size > $maxSizeVideo ){
                return [
                    'status' => false,
                    'message'=> "Dung lượng video quá lớn. Tối đa 20 MB"
                ];
            }
        }
        $extFileType    =pathinfo($fileUpload['name'], PATHINFO_EXTENSION);

        $allowed    = $name == 'image' ? array("jpg", "jpeg", "gif", "png") : [];
        if( $name == 'video' ){
            $allowed    = array("mp4", "flv", "m4a", "mov");
        }
        if(!in_array($extFileType, $allowed)) {
            return [
                'status' => false,
                'message'=> Response::getErrorMessage($name . '_upload', Response::KEY_INVALID)
            ];
        }

        $type       = isset($params['type']) && !empty($params['type']) ? preg_replace('/[^a-zA-Z0-9]+/', '_', $params['type']) : 'file';
        $product_id = isset($params['product_id']) && !empty($params['product_id']) ? $params['product_id'] : 0;

        $target_dir = $_SERVER['DOCUMENT_ROOT'];
        $path_folder= DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . $type;
        if( !is_dir($target_dir . $path_folder) ){
            mkdir($target_dir . $path_folder, 0777, true);
        }
        $path_file  = $path_folder . DIRECTORY_SEPARATOR . $file_name;

        $orgpath    = $target_dir . $path_file;
        
        if (move_uploaded_file($fileUpload['tmp_name'], $orgpath)) {
            return [
                'status' => true,
                'data'   => [
                    'path' => $path_file,
                    'path_full' => Yii::$app->params['urlDomain'] . $path_file
                ]
            ];
        }

        return [
            'status' => false,
            'message'=> 'Có lỗi xảy ra! Vui lòng thử lại sau'
        ];
    }

    public static function removeFile($path){
        $target_dir = $_SERVER['DOCUMENT_ROOT'];
        $full_path  = $target_dir . $path;
        if( file_exists($full_path) ){
            unlink($full_path);
        }
    }
}
