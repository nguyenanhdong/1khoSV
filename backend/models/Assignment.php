<?php

namespace backend\models;

use mdm\admin\components\Configs;
use mdm\admin\components\Helper;
use Yii;
use yii\base\Object;
use backend\models\AuthItemChild;
use yii\helpers\ArrayHelper;

/**
 * Description of Assignment
 *
 * @author Misbahul D Munir <misbahuldmunir@gmail.com>
 * @since 2.5
 */
class Assignment extends Object
{
    /**
     * @var integer User id
     */
    public static $id;
    /**
     * @var \yii\web\IdentityInterface User
     */
    public static $user;

    /**
     * @inheritdoc
     */
    public function __construct($id, $user = null, $config = array())
    {
        self::$id = $id;
        self::$user = $user;
        parent::__construct($config);
    }

    /**
     * Grands a roles from a user.
     * @param array $items
     * @return integer number of successful grand
     */
    public function assign($items)
    {
        $manager = Configs::authManager();
        $success = 0;
        foreach ($items as $name) {
            try {
                $item = $manager->getRole($name);

                $item = $item ?: $manager->getPermission($name);
                $manager->assign($item, self::$id);
                $success++;
            } catch (\Exception $exc) {
                Yii::error($exc->getMessage(), __METHOD__);
            }
        }
        Helper::invalidate();
        return $success;
    }

    /**
     * Revokes a roles from a user.
     * @param array $items
     * @return integer number of successful revoke
     */
    public function revoke($items)
    {
        $manager = Configs::authManager();
        $success = 0;
        foreach ($items as $name) {
            try {
                $item = $manager->getRole($name);
                $item = $item ?: $manager->getPermission($name);
                $manager->revoke($item, self::$id);
                $success++;
            } catch (\Exception $exc) {
                Yii::error($exc->getMessage(), __METHOD__);
            }
        }
        Helper::invalidate();
        return $success;
    }

    /**
     * Get all available and assigned roles/permission
     * @return array
     */
    public function getItems($id = 0, $getRoleOnly=false)
    {
        $manager = Configs::authManager();
        $available = [];
        foreach (array_keys($manager->getRoles()) as $name) {
            $available[$name] = 'role';
        }
        if( !$getRoleOnly ){
            foreach (array_keys($manager->getPermissions()) as $name) {
                if ($name[0] != '/') {
                    $available[$name] = 'permission';
                }
            }
        }
        
        $assigned = [];
        if( $getRoleOnly ){
            $resultRoleAssign = ArrayHelper::map(AuthItemChild::find()->where(['parent'=>$id,'type_assign'=>2])->all(),'child','child');
            if( !empty($resultRoleAssign) ){
                foreach($resultRoleAssign as $roleName){
                    $assigned[$roleName] = $available[$roleName];
                    unset($available[$roleName]);
                }
            }
        }else{
            if( $id == 0 )
                $id = self::$id;
            foreach ($manager->getAssignments($id) as $item) {
                $assigned[$item->roleName] = $available[$item->roleName];
                unset($available[$item->roleName]);
            }
        }
        
        return [
            'available' => $available,
            'assigned' => $assigned,
        ];
    }

    /**
     * @inheritdoc
     */
    public function __get($name)
    {
        if (self::$user) {
            return self::$user->$name;
        }
    }

    public function removeRoleAssign($parent){
        Yii::$app->db->CreateCommand('DELETE FROM auth_item_child where parent = "' . $parent . '" and type_assign = 2')->execute();
    }
    public function addRoleAssign($parent,$listRoleAssign){
        if( !empty($listRoleAssign) ){
            foreach($listRoleAssign as $roleName){
                $model = new AuthItemChild;
                $model->parent = $parent;
                $model->child  = $roleName;
                $model->type_assign = 2;
                $model->save(false);
            }
        }
    }
    public function getAllRoleAssign($listRoleOfUser){
        $resultRoleAssign = ArrayHelper::map(AuthItemChild::find()->where(['type_assign'=>2])->andWhere(['in','parent',$listRoleOfUser])->all(),'child','child');
        if( !empty($resultRoleAssign) ){
            foreach($resultRoleAssign as $roleName){
                $listRoleOfUser[] = $roleName;
            }
        }
        return array_unique($listRoleOfUser);
    }
}
