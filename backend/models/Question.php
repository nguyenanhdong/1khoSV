<?php

namespace backend\models;

use Yii;

class Question extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'staff_question';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['question','title'],'required','message'=>'Nhập {attribute}'],
            [['status'],'required','message'=>'Chọn {attribute}'],
            [['question','answer','status'],'safe']
        ];
    }

    
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'        => 'ID',
            'question'     => 'Nội dung câu hỏi',
            'title'    => 'Tiêu đề',
            'status'    => 'Trạng thái câu hỏi'
        ];
    }
}
