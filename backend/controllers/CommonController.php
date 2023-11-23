<?php

namespace backend\controllers;

use Yii;
use backend\models\District;
use yii\base\Exception;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;

require_once realpath(dirname(__FILE__) . '/../../vendor/PHPExcel/Classes/PHPExcel/IOFactory.php');
require_once realpath(dirname(__FILE__) . '/../../vendor/PHPExcel/Classes/PHPExcel.php');
require_once realpath(dirname(__FILE__) . '/../../vendor/PHPExcel/Classes/PHPExcel/Cell.php');

use PHPExcel;
use PHPExcel_IOFactory;
use PHPExcel_Cell;

class CommonController extends Controller
{

    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'remove-file' => ['POST'],
                    'upload-file' => ['POST'],
                ],
            ],
            // 'corsFilter' => [
            //     'class' => \yii\filters\Cors::className(),
            //     'cors' => [
            //         // restrict access to
            //         'Origin' => ['*'],
            //         'Access-Control-Request-Method' => ['POST', 'GET'],
            //         // Allow only POST and PUT methods
            //         'Access-Control-Request-Headers' => ['*'],
            //         // Allow only headers 'X-Wsse'
            //         'Access-Control-Allow-Credentials' => true,
            //         // Allow OPTIONS caching
            //         'Access-Control-Max-Age' => 3600,
            //         // Allow the X-Pagination-Current-Page header to be exposed to the browser.
            //         'Access-Control-Expose-Headers' => ['X-Pagination-Current-Page'],
            //     ],

            // ],

        ];
    }
    public static function syncFrontEnd($folder, $name_file){
        if (!file_exists(Url::to('@frontend/web/uploads/' . $folder . '/'))) {
            mkdir(Url::to('@frontend/web/uploads/' . $folder . '/'), 0777, true);
        }

        file_put_contents(Url::to('@frontend/web/uploads/' . $folder . '/' . $name_file),file_get_contents('https://admin.viecnhanh.com.vn/uploads/' . $folder . '/' . $name_file));
    }
    public static function uploadFile($file = null, $folder)
    {
        if (!empty($file)) {
            $_FILES["file"] = $file;
        }
        if ($_FILES["file"]["error"]) {
            return (array("message" => 'Có lỗi khi tải file. Vui lòng tải lại file khác', "status" => false));
        }

        // $image_type = mime_content_type($_FILES['file']['tmp_name']);

        $target_dir = $_SERVER['DOCUMENT_ROOT'];

        if (!file_exists($target_dir . "/uploads/" . $folder)) {
            mkdir($target_dir . "/uploads/" . $folder, 0777, true);
        }
        if( isset(Yii::$app->params['root_foder_upload'])){
            $target_dir_sync = Yii::$app->params['root_foder_upload'];
            if (!file_exists($target_dir_sync . "/uploads/" . $folder)) {
                mkdir($target_dir_sync . "/uploads/" . $folder, 0775, true);
            }
        }

        $method = $_SERVER['REQUEST_METHOD'];
        if ($method != 'POST') {
            return (array("message" => 'Only method post', "status" => false));
        }

        $target_file    = $target_dir . basename($_FILES["file"]["name"]);
        $uploadOk       = 1;
        $extFileType    = pathinfo($target_file, PATHINFO_EXTENSION);
        $file_name      = explode('.' . $extFileType,basename($_FILES["file"]["name"]));
        $checkImage     = true;
        $extFileType    = strtolower($extFileType);
        $file_n         = time() . '-' . md5(CommonController::LocDau($file_name[0])) . '.' . $extFileType;
        $name_file      = "/uploads/" . $folder . "/" . $file_n;

        $target_file    = $target_dir . $name_file;
        
        if ($uploadOk == 0) {
            return (array("message" => $message, "status" => false));
        } else {
            if (move_uploaded_file($_FILES["file"]["tmp_name"], $target_file)) {
                // $r = self::syncFrontEnd($folder,$file_n);
                $message = "The file " . basename($_FILES["file"]["name"]) . " has been uploaded.";
                $url = $name_file;
                return (array("message" => $message, "status" => true,'target_file'=>$target_file, 'url' => $url));
            } else {
                $message = "Sorry, there was an error uploading your file.";
                return (array("message" => $message, "status" => false));
            }
        }
    }
    
    private function compressImage($source, $destination, $quality){
        if( file_exists($source) ){
            $info = getimagesize($source);
            $imageSize = filesize($source);
            if( $imageSize >= 500000 ){
                if( $imageSize >= 1500000 )
                    $quality = 60;
                if ($info['mime'] == 'image/jpeg'){
                    $image = imagecreatefromjpeg($source);
                }
            
                elseif ($info['mime'] == 'image/gif') 
                    $image = imagecreatefromgif($source);
            
                elseif ($info['mime'] == 'image/png') 
                    $image = imagecreatefrompng($source);
            
                imagejpeg($image, $destination, $quality);
            }
        }
        return $destination;
    }
    public static function removeFile($file)
    {
        $target_dir = $_SERVER['DOCUMENT_ROOT'];
        if( file_exists($target_dir . $file) ){
            unlink($target_dir . $file);
        }
        if( isset(Yii::$app->params['root_foder_upload']) ){
            $target_dir = Yii::$app->params['root_foder_upload'];
            if( file_exists($target_dir . $file) ){
                unlink($target_dir . $file);
            }
        }
    }

    public function actionUploadFile()
    {
        $request = Yii::$app->request;
        $post = Yii::$app->request->post();
        if (($request->isPost) && !empty($post['folder'])) {
            $res = $this->uploadFile($_FILES["file"], $post['folder']);
            echo json_encode($res);
            die;
        } else {
            echo json_encode(array("status" => false, "message" => 'sorry. only method post'));
            die;
        }
    }

    public function actionRemoveFile()
    {
        $post = Yii::$app->request->post();
        if (!empty($post['file'])) {
            $res = $this->removeFile($_SERVER['DOCUMENT_ROOT'] . $post['file']);
            echo json_encode($res);
            die;
        } else {
            echo json_encode(array("status" => false, "message" => 'Đã có lỗi xảy ra'));
            die;
        }
    }

    public function upload($file, $folder)
    {
        # code...
        if (!empty($file) && !empty($folder)) {
            $res = CommonController::uploadFile($file, $folder);
            return $res;
        } else {
            return array("status" => false, "message" => 'sorry. only method post');
        }
    }

    public static function LocDau($str)
    {
        $str = trim($str);
        $str = preg_replace("/(à|á|ạ|ả|ã|â|ầ|ấ|ậ|ẩ|ẫ|ă|ằ|�� �|ặ|ẳ|ẵ|ắ)/", 'a', $str);
        $str = preg_replace("/(è|é|ẹ|ẻ|ẽ|ê|ề|ế|ệ|ể|ễ)/", 'e', $str);
        $str = preg_replace("/(ì|í|ị|ỉ|ĩ)/", 'i', $str);
        $str = preg_replace("/(ò|ó|ọ|ỏ|õ|ô|ồ|ố|ộ|ổ|ỗ|ơ|ờ|ớ|ợ|ở|ỡ)/", 'o', $str);
        $str = preg_replace("/(ù|ú|ụ|ủ|ũ|ư|ừ|ứ|ự|ử|ữ)/", 'u', $str);
        $str = preg_replace("/(ỳ|ý|ỵ|ỷ|ỹ)/", 'y', $str);
        $str = preg_replace("/(đ)/", 'd', $str);
        $str = preg_replace("/(À|Á|Ạ|Ả|Ã|Â|Ầ|Ấ|Ậ|Ẩ|Ẫ|Ă|Ằ|�� �|Ặ|Ẳ|Ẵ)/", 'A', $str);
        $str = preg_replace("/(È|É|Ẹ|Ẻ|Ẽ|Ê|Ề|Ế|Ệ|Ể|Ễ)/", 'E', $str);
        $str = preg_replace("/(Ì|Í|Ị|Ỉ|Ĩ)/", 'I', $str);
        $str = preg_replace("/(Ò|Ó|Ọ|Ỏ|Õ|Ô|Ồ|Ố|Ộ|Ổ|Ỗ|Ơ|Ờ|Ợ|Ở|Ớ|Ỡ)/", 'O', $str);
        $str = preg_replace("/(Ù|Ú|Ụ|Ủ|Ũ|Ư|Ừ|Ứ|Ự|Ử|Ữ)/", 'U', $str);
        $str = preg_replace("/(Ỳ|Ý|Ỵ|Ỷ|Ỹ)/", 'Y', $str);
        $str = preg_replace("/(Đ)/", 'D', $str);
        $str = preg_replace("/( |'|,|\||\.|\"|\?|\/|\%|–|!|’|‘)/", '-', $str);
        $str = preg_replace("/(\()/", '-', $str);
        $str = preg_replace("/(\))/", '-', $str);
        $str = preg_replace("/(&)/", '', $str);
        $str = preg_replace("/“/", '', $str);
        $str = preg_replace("/”/", '', $str);
        $str = preg_replace("/;/", '', $str);
        $str = preg_replace("/:/", '', $str);
        $str = preg_replace("/(?)/", '', $str);
        $str = array_filter(explode('-', strtolower($str)));

        return implode('-', $str);
    }

    public function readFileExcel($file)
    {
        $excel      = new PHPExcel();
        $objFile    = PHPExcel_IOFactory::identify($file);
        $objData    = PHPExcel_IOFactory::createReader($objFile);
        $objData->setReadDataOnly(true); //Chỉ đọc dữ liệu
        $objPHPExcel = $objData->load($file); // Load dữ liệu sang dạng đối tượng
        // Lấy Ra tên trang sử dụng getSheetNames(), Lấy ra số trang sử dụng phương thức getSheetCount()
        $sheet = $objPHPExcel->setActiveSheetIndex(0); //Chọn trang cần truy xuất
        $Totalrow = $sheet->getHighestRow(); //Lấy ra số dòng cuối cùng
        $LastColumn = $sheet->getHighestColumn();//Lấy ra tên cột cuối cùng
        $TotalCol = PHPExcel_Cell::columnIndexFromString($LastColumn);//Chuyển đổi tên cột đó về vị trí thứ, VD: C là 3,D là 4
        
        $data   = [];
        $count  = 0;
        //Tiến hành lặp qua từng ô dữ liệu
        for ($i = 2; $i <= $Totalrow; $i++) {   //---Lặp dòng, Vì dòng đầu là tiêu đề cột nên chúng ta sẽ lặp giá trị từ dòng 2
            for ($j = 0; $j < $TotalCol; $j++) { //----Lặp cột
                $data[$i - 2][$j] = $sheet->getCellByColumnAndRow($j, $i)->getValue(); // Tiến hành lấy giá trị của từng ô đổ vào mảng
            }
        }
        return $data;
    }

}