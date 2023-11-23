<?php
namespace backend\components;
use Yii;
use yii\web\ForbiddenHttpException;
use backend\models\AuthItem;

class UAccess extends \yii\base\Behavior
{
	public $actionAllowed = ['/site/login', '/site/logout', '/site/test', '/site/error','/api/*','/uploads/*','/img/*'];
    public function events(){
        return [
            \yii\base\Module::EVENT_BEFORE_ACTION => 'checkAccess'
        ];
    }
	
    public function checkAccess($event){
        
        $module         = Yii::$app->controller->module->id;
        $controller 	= $event->action->controller->id;
		$action     	= $event->action->id;
		$roleAccessAll  = '/' . $controller . '/*';
        $roleAction     = '/' . $controller . '/' . $action;
        
        $listActionAllow= $this->actionAllowed;

        if( !Yii::$app->user->isGuest ){
            if( Yii::$app->user->identity->is_admin != 1 ){
                if( Yii::$app->session->hasFlash('actionsAllow') ){
                    $listActionAllow= json_decode(Yii::$app->session->getFlash('actionsAllow'),true);
                }else{
                    $listRoleOfUser = Yii::$app->authManager->getRolesByUser(Yii::$app->user->getId());
                    
                    foreach($listRoleOfUser as $roleName=>$value){
                        $resultRole = Yii::$app->authManager->getRole($roleName);
                        $AuthItem  = new AuthItem($resultRole);
                        $listItems = $AuthItem->getItems();
                        if( isset($listItems['assigned']) && !empty($listItems['assigned']) ){
                            foreach($listItems['assigned'] as $taskName=>$type){
                                $resultPermission   = Yii::$app->authManager->getPermission($taskName);
                                $AuthItemTask       = new AuthItem($resultPermission);
                                $listItemsTask      = $AuthItemTask->getItems();
                                if( isset($listItemsTask['assigned']) && !empty($listItemsTask['assigned']) ){
                                    $listActionAllow= array_merge($listActionAllow, array_keys($listItemsTask['assigned']));
                                }
                            }
                        }
                    }
                }
            }else
                $listActionAllow[] = 'All';
            Yii::$app->session->setFlash('actionsAllow', json_encode($listActionAllow));

            if( (!Yii::$app->user->isGuest && Yii::$app->user->identity->is_admin == 1) ||  in_array( $roleAction, $listActionAllow ) || in_array( $roleAccessAll, $listActionAllow ) ){
                          
                return true;
            }
            else{
                return true;
                // throw new \yii\web\HttpException(403, 'Rất tiếc! Bạn không đủ quyền thực hiện thao tác này.');
            }
        }else{
            if( $roleAction != '/site/login' && strpos($roleAction,'/api/') === false && strpos($roleAction,'/process-work/') === false ){
                if (Yii::$app->request->url != "/")
                    Yii::$app->user->loginUrl = ['site/login', 'return' => Yii::$app->request->url];
                else
                    Yii::$app->user->loginUrl = ['site/login'];

                Yii::$app->getResponse()->redirect(Yii::$app->user->loginUrl)->send();
                die(); exit();
            }   
        }   
    }
}
