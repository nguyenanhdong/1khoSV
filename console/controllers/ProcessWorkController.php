<?php

namespace console\controllers;
use Yii;
use backend\models\OrderWork;
use backend\models\Notify;
use backend\models\Services;
use backend\models\NotifyUser;
use yii\console\Controller;
use backend\models\Util;
use backend\models\Staff;
use backend\models\StaffOrder;
use DateTime;

class ProcessWorkController extends Controller {
    public $chat_id   = -581513801;
    public $token_bot = '1676133200:AAHU68FSplWBDqQ2p0KlBY28VO2l-6AoFCQ';

    /**
     * Function hủy đơn dịch vụ dọn nhà theo ca: Nếu quá thời gian đặt 2 tiếng mà k có nhân viên nào nhận thì sẽ hủy tự động
     * Ví dụ đơn ngày làm vào 12h 21/9/2023 đặt lúc 6h 20/9/2023. Thì đến 8h 20/9/2023 k có người nhận thì huỷ và bắn noti về cho khách đơn đã bị huỷ do không tìm được nhân viên. Vui lòng gọi lên tổng đài số 0888029029 để được hỗ trợ
     */
    public function actionAutoCancelOrder(){
        date_default_timezone_set('Asia/Saigon');
        set_time_limit(0);
        ini_set('memory_limit', -1);
        $listServiceCheck   = [9];//Dọn nhà theo ca
        $resultOrder        = OrderWork::find()->where(['in','type_order',$listServiceCheck])->andWhere(['status' => 0])->all();
        $total_order        = count($resultOrder);
        $total_cancel       = 0;
        if( !empty($resultOrder) ){
            foreach( $resultOrder as $row ){
                $time_order = strtotime($row->create_at);
                $timeCheck  = strtotime('- 2 hour', time());
                if( $timeCheck >= $time_order ){
                    $row->status = 5;
                    $row->reason = 'Hệ thống huỷ tự động';
                    $row->reason_admin = 'Hệ thống huỷ tự động do đơn chưa được giao nhân viên';
                    $row->save(false);
                    $total_cancel++;

                    //Notify Onesignal
                    $modelService = Services::findOne($row->type_order);
                    if( $modelService && $row->user_id ){
                        
                        $lb_type      = !in_array($modelService->id, [3,4,5,6,8]) ? 'đặt lịch' : 'đơn hàng';
                        $notify_title = "$lb_type của bạn đã bị hủy";
                        $notify_desc  = "Không tìm thấy nhân viên";

                        $modelNotify               = new Notify;
                        $modelNotify->title        = "Đặt lịch dịch vụ " . mb_strtolower($modelService->name, 'UTF-8') . " đã bị huỷ";
                        $modelNotify->description  = $notify_desc;
                        $modelNotify->icon         = '/uploads/images/notify/icon-bell.png';
                        $modelNotify->content      = $notify_desc;
                        $modelNotify->type         = $row->type_order;
                        $modelNotify->obj_id       = $row->id;
                        if( $modelNotify->save(false) ){
                            $notifyUser            = new NotifyUser;
                            $notifyUser->notify_id = $modelNotify->id;
                            $notifyUser->user_id   = $row->user_id;
                            $notifyUser->save(false);
                        }
                        
                        Util::sendOneSignal($row->user_id, $notify_title, $notify_desc);

                    }
                }
            }
        }

        $html_log       = 'Process auto cancel order:' . PHP_EOL;
        $html_log      .= 'Tổng đơn đang chờ: ' . $total_order . PHP_EOL;
        $html_log      .= 'Tổng đơn đã hủy: ' . $total_cancel . PHP_EOL;

        $this->writeLog('log_auto_cancel_order',$html_log);
    }
    
    //Function xử lý cập nhật trạng thái đơn hàng
    public function actionUpdateStatusWork() {
        die;
        $time_current   = time();
        $time_today     = strtotime(date('Y-m-d'));
        $time_end_today = strtotime(date('Y-m-d 23:59:59'));
        $result         = OrderWork::find()->where(['in','status',[0,1]])->all();
        $total_cancel   = 0;
        $total_active   = 0;
        $total_complete = 0;
        if( !empty($result) ){
            foreach($result as $row){
                $time_date_work = strtotime($row->workday);
                $time_end_work  = 0;
                $staff_id       = 0;
                $modelStaffOrder = StaffOrder::find()->where((['order_id' => $row->id]))->andWhere(['<>', 'status', 0])->one();
                if( $modelStaffOrder )
                    $staff_id   = $modelStaffOrder->staff_id;
                if( $staff_id == 0 ){//Chưa giao: Check nếu ngày làm < hôm này hoặc đến giờ làm -> Huỷ đơn
                    if( ($time_date_work < $time_today) || ($time_current >= $time_date_work && $time_date_work <= $time_end_today) ){
                        if( $row->status == 0 && $staff_id == 0 ){
                            $row->status = 5;
                            $row->reason = 'Hệ thống huỷ tự động';
                            $row->reason_admin = 'Hệ thống huỷ tự động do đơn chưa được giao nhân viên';
                            $row->save(false);
                            $total_cancel++;

                            //Notify Onesignal
                            $modelService = Services::findOne($row->type_order);
                            if( $modelService ){
                                
                                $lb_type      = !in_array($modelService->id, [3,4,5,6,8]) ? 'đặt lịch' : 'đơn hàng';
                                $notify_title = "$lb_type của bạn đã bị hủy";
                                $notify_desc  = "Không tìm thấy nhân viên";

                                $modelNotify               = new Notify;
                                $modelNotify->title        = "Đơn hàng đặt dịch vụ " . mb_strtolower($modelService->name, 'UTF-8') . " đã bị huỷ";
                                $modelNotify->description  = $notify_desc;
                                $modelNotify->icon         = '/uploads/images/notify/icon-bell.png';
                                $modelNotify->content      = $notify_desc;
                                $modelNotify->type         = $row->type_order;
                                $modelNotify->obj_id       = $row->id;
                                if( $modelNotify->save(false) ){
                                    $notifyUser            = new NotifyUser;
                                    $notifyUser->notify_id = $modelNotify->id;
                                    $notifyUser->user_id   = $row->user_id;
                                    $notifyUser->save(false);
                                }
                                
                                Util::sendOneSignal($row->user_id, $notify_title, $notify_desc);

                            }
                        }
                    }
                }else{

                    //Kiểm tra đơn đang ở trạng thái Đang chờ: Nếu đến giờ làm thì set đang hoạt động
                    if( $row->status == 0 ){
                        if($time_current >= $time_date_work && $time_date_work <= $time_end_today){
                            $row->status = 1;//Đang diễn ra
                            $row->save(false);
                            $total_active++;
                        }
                    }else{
                        //Nếu là đơn có giờ làm -> Cộng số giờ lên -> Check nếu qua giờ thì set hoàn thành
                        //Ngược lại thì set hoàn thành vào ngày hôm sau
                        if( in_array($row->type_order,[1]) ){
                            $time_date_work = strtotime('+' . $row->working_time . ' hours', $time_date_work);
                        }else{
                            $time_date_work = strtotime('+ 1 days', $time_date_work);
                        }
                        if( $time_current >= $time_date_work ){
                            $row->status = 2;//Đã hoàn thành
                            $row->save(false);
                            $total_complete++;
                        }
                    }
                }
                
            }
        }
        $html_log       = 'Process update status:' . PHP_EOL;
        $html_log      .= 'Tổng đơn hủy: ' . $total_cancel . PHP_EOL;
        $html_log      .= 'Tổng đơn active: ' . $total_active . PHP_EOL;
        $html_log      .= 'Tổng đơn hoàn thành: ' . $total_complete . PHP_EOL;

        // $this->_sendNotifyTelegram($html_log);
        $this->writeLog('log_update_status',$html_log);
    }

    //Function xử lý tạo đơn lặp lại hàng tuần + Cảnh báo trước 2 tiếng cho admin nếu chưa giao
    public function actionCreateOrderRepeat(){
        $result         = OrderWork::find()->where(['status'=>2,'is_create_repeat'=>0,'repeat_every_week'=>1])->all();
        if( !empty($result) ){
            $listDays       = array('Sunday' => 8, 'Monday' => 2, 'Tuesday' => 3, 'Wednesday' => 4,'Thursday' => 5,'Friday' => 6, 'Saturday' => 7);
            $time_today     = strtotime(date('Y-m-d'));
            $time_current   = time();
            $number_day_current = $listDays[date('l')];
            $time_next       = strtotime('+ 1 days', $time_current);
            $number_day_next = $listDays[date('l',$time_next)];
            foreach($result as $row){
                $time_date_work = strtotime($row->workday);
                $day_name   = date('l',$time_date_work);
                $number_day = $listDays[$day_name];
                $date       = new DateTime($row->workday);
                $date->modify('Sunday this week');
                $last_day_of_week = $date->format('Y-m-d');
                $time_last_day_of_week    = strtotime($last_day_of_week . ' 23:59:59');
                $flagCreateRepeat = false;
                $workday_new      = '';
                if( $time_current > $time_last_day_of_week ){
                    if( $number_day_current == $row->day_repeat || $number_day_next == $row->day_repeat ){
                        $flagCreateRepeat = true;
                        $workday_new      = $number_day_current == $row->day_repeat ? date('Y-m-d') : date('Y-m-d',$time_next);
                        if( date('H',$time_date_work) > 0 )
                            $workday_new .= ' ' . date('H:00:00',$time_date_work);
                    }
                }

                if( $flagCreateRepeat ){
                    $row->is_create_repeat = 1;
                    $row->save(false);
                    //Tạo đơn lặp
                    $productOrder               = new OrderWork;
                    $productOrder->user_id      = $row->user_id;
                    $productOrder->fullname     = $row->fullname;
                    $productOrder->email        = $row->email;
                    $productOrder->phone        = $row->phone;
                    $productOrder->house_address= $row->house_address;
                    $productOrder->house_number = $row->house_number;
                    $productOrder->workday      = $workday_new;
                    $productOrder->working_time = $row->working_time;
                    $productOrder->note         = $row->note;
                    $productOrder->type_payment = $row->type_payment;
                    $productOrder->type_order   = $row->type_order;
                    $productOrder->service_other= $row->service_other;
                    $productOrder->repeat_every_week        = $row->repeat_every_week;
                    $productOrder->day_repeat               = $row->day_repeat;
                    
                    $productOrder->price                    = $row->price;
                    $productOrder->price_promotion          = 0;
                    $productOrder->source                   = $productOrder->source;
                    if($productOrder->save(false)){
                        
                        $list_service               = Yii::$app->params['list_service'];
                        $list_icon_service_notify   = Yii::$app->params['list_icon_service_notify'];

                        $title_notify               = 'Tạo đơn lặp lại dịch vụ ' . mb_strtolower($list_service[$type_order], 'UTF-8') . ' thành công';
                        $icon_notify                = isset($list_icon_service_notify[$type_order]) ? $list_icon_service_notify[$type_order] : '/uploads/images/notify/icon-bell.png';
                        $des_notify                 = '';
                        $content                    = '';
                        $type_order_name            = '';
                        $type_order                 = $row->type_order;
                        if( $row->house_number )
                            $des_notify             = $row->house_number;
                        if( $row->house_address )
                            $des_notify                .= ' ' . $row->house_address;
                        $des_notify                 = $des_notify != '' ? 'Tại ' . trim($des_notify) : '';
                        
                        $content                   = 'FastJob đã ' . mb_strtolower(str_replace('thành công','',$title_notify), 'UTF-8') . ' cho bạn';
                        //Tạo notify
                        $notifyOrder               = new Notify;
                        $notifyOrder->title        = $title_notify;
                        $notifyOrder->description  = $des_notify;
                        $notifyOrder->icon         = $icon_notify;
                        $notifyOrder->content      = $content;
                        $notifyOrder->type         = $type_order;
                        $notifyOrder->obj_id       = $productOrder->id;
                        if( $notifyOrder->save(false) ){
                            $notifyUser            = new NotifyUser;
                            $notifyUser->notify_id = $notifyOrder->id;
                            $notifyUser->user_id   = $row->user_id;
                            $notifyUser->save(false);
                        }

                        //Bắn đơn qua telegram
                        $message_tele  = '--------------Lặp Đơn hàng-------------' .  PHP_EOL;
                        $message_tele .= str_replace(' thành công','',$title_notify) .  PHP_EOL;
                        $message_tele .= '- Mã đơn: ' . $productOrder->id .  PHP_EOL;
                        $message_tele .= '- Thông tin khách hàng' .  PHP_EOL;
                        $message_tele .= '  ' . $row->fullname . ' - ' . $row->phone .  PHP_EOL;
                        
                        $message_tele .= '- Thông tin đặt hàng' .  PHP_EOL;
                        if( $type_order == 1 )   
                            $message_tele .= '  ' . date( 'H' , strtotime($workday_new) ) . 'h Ngày ' . date( 'd/m/Y' , strtotime($workday_new) ) .  PHP_EOL;
                        else
                            $message_tele .= '  Ngày ' . date( 'd/m/Y' , strtotime($workday_new) ) .  PHP_EOL;
                        $message_tele .= '  Tại ' . ($row->house_number ? $row->house_number . ' ' : '') . $row->house_address . PHP_EOL;
                        $message_tele .= '--------------------------------------';

                        $this->_sendNotifyTelegram($message_tele);

                        //Gửi thông báo đến tất cả nhân viên đăng ký dịch vụ
                        $this->_notifyOrderToStaff($productOrder);

                    }
                }
                // echo $number_day_current . ' - ' . $number_day_next . ' - ' . $row->day_repeat;
                // if( $time_date_work < $time_today ){
                //     $date = new DateTime($row->workday);
                // }
            }
        }
    }
    public function writeLog($typeLog, $stringlog){
        try {
            $dir = __DIR__;
            $path = "logs/" . $typeLog;
            if (!file_exists($path)) {
                mkdir($path, 0777, true);
            }
            $timeWrite = date("Y_m_d");
            $fileName = $path . "/log." . $timeWrite . ".log";
            $fh = fopen($fileName, 'a+') or die("Can't create file");
            fwrite($fh, date('Y-m-d H:i:s', time()) . ": " . $stringlog . "\n");
            fclose($fh);
        } catch (Exception $e) {

        }
    }
    public function _notifyOrderToStaff($model){
        $type_order = $model->type_order;
        $resultStaff= Staff::find()->where(['like', 'list_service', $type_order])->all();
        if( !empty($resultStaff) ){
            $list_service_order         = Yii::$app->params['list_service'];
            foreach($resultStaff as $row){
                $list_service = explode(',',$row['list_service']);
                if( in_array($type_order,$list_service) ){
                    $title_notify = 'Đơn hàng mới';
                    $des_notify   = 'Dịch vụ ' . mb_strtolower($list_service_order[$type_order], 'UTF-8') . ' tại ' . $model->house_address . '. XEM NGAY 👉';
                    Util::sendOneSignal($row['id'],$title_notify,$des_notify,'staff');
                }
            }
        }
    }
    public function _sendNotifyTelegram($message){
        $urlApiSendMessageTele = 'https://api.telegram.org/bot' . $this->token_bot . '/sendMessage';
        $data = [
            'text' => $message,
            'chat_id' => $this->chat_id
        ];
        file_get_contents($urlApiSendMessageTele . "?" . http_build_query($data) );
    }
}
