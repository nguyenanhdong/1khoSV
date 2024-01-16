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
}
