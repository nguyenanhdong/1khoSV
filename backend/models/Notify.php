<?php

namespace backend\models;

use Yii;

class Notify extends \yii\db\ActiveRecord
{
    const TYPE_CUSTOMER = 1;
    const TYPE_AGENT    = 2;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'notify';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['title'],'required','message'=>'Nhập {attribute}'],
            [['user_notify','content','description','is_show_button','type'], 'safe'],
        ];
    }
    public function beforeSave($insert){
        
        if( !isset($_POST['Notify']['is_show_button']) ){
            $this->is_show_button = 0;
        }
        return parent::beforeSave($insert);
    }
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
        ];
    }

    public static function insertNotify($user_id, $title, $desc, $content, $isShowButton, $category_id, $obj_id, $user_notify){
        $model                  = new Notify;
        $model->title           = $title;
        $model->description     = $desc;
        $model->content         = $content;
        $model->is_show_button  = $isShowButton;
        $model->category_id     = $category_id;
        $model->obj_id          = $obj_id;
        $model->user_notify     = $user_notify;
        if($model->save(false)){
            NotifyUser::insertHistory($user_id, $model->id, $user_notify);
            NotifyUnRead::saveUnread($user_id, 1, $user_notify);
        }
    }
}
