<?php

namespace backend\models;

use Yii;

class Notify extends \yii\db\ActiveRecord
{
    public $type_notify;
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
            $this->type = 0;
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
            'title' => 'Tiêu đề',
            'description'  => 'Mô tả ngắn',
            'content'  => 'Nội dung',
            'is_show_button'  => 'Hiển thị nút đặt hàng',
            'type'  => 'Dịch vụ',
            'user_notify' => 'Đối tượng thông báo'
        ];
    }
}
