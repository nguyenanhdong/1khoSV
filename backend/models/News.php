<?php

namespace backend\models;

use Yii;

class News extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'news';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['title','description','content'],'required','message'=>'Nhập {attribute}'],
            [['image'],'required','message'=>'Chọn {attribute}'],
            [['slug','date_publish','status'],'safe']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => 'Tiêu đề bài viết',
            'description'    => 'Mô tả ngắn gọn',
            'content'   => 'Nội dung',
            'image' => 'Ảnh đại diện',
            'status' => 'Trạng thái',
            'create_at' => 'Ngày đăng',
            'date_publish' => 'Ngày xuất bản'
        ];
    }
}
