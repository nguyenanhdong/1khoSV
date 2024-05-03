<?php
namespace frontend\components;
use Yii;
use yii\web\ForbiddenHttpException;
use backend\models\UserLogin;
use common\models\Users;
class UAccess extends \yii\base\Behavior
{
    public function events(){
        return [
            \yii\base\Module::EVENT_BEFORE_ACTION => 'checkAccess'
        ];
    }
	
    public function checkAccess($event){
        return;
        if( !Yii::$app->user->isGuest ){
            $user           = Yii::$app->user->identity;
            $model          = Users::findOne($user->id);
            if( $model->status == 3 ){
                Yii::$app->user->logout();
                return true;
            }

            $browser        = get_browser(null, true);
            $ip_address     = $_SERVER['REMOTE_ADDR'];
            $user_agent     = $_SERVER['HTTP_USER_AGENT'];
            $os             = $browser['platform'];
            $browser_name   = $browser['browser'];
            $version        = str_replace('.0','',$browser['version']);
            $device         = $browser['device_type'] == 'Desktop' ? 'PC' : 'Mobile';
            $condition      = [];

            if (Yii::$app->getRequest()->getCookies()->has('tklgusf'))
                $condition  = ['is_revoke'=>0,'user_id'=>$user->id, 'token' => Yii::$app->getRequest()->getCookies()->getValue('tklgusf')];
            else
                $condition  = ['is_revoke'=>0,'user_id'=>$user->id,'user_agent'=>$user_agent, 'ip_address' => $ip_address];
            $checkIpExits   = UserLogin::find()->where($condition)->orderBy(['status'=>SORT_DESC])->one();
            
            if( $checkIpExits ){
                if( $checkIpExits->status == 0 ){
                    return true;
                }else if( $checkIpExits->is_revoke == 0 ){
                    $checkIpExits->is_revoke = 1;
                    $checkIpExits->save(false);
                    Yii::$app->user->logout();
                }
            }else{
                Yii::$app->user->logout();
            }

        }else{
            return true;
        }   
    }
}
