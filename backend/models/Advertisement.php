<?php

namespace backend\models;

use Yii;

class Advertisement extends \yii\db\ActiveRecord
{
    const TYPE_BUY = 1;
    const TYPE_SELL= 2;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'advertisement';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            
        ];
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

    public static function getAdvertisementHome( $type = null, $limit = null, $offset = null, $sortBy = 'NEWS' ){

    }
}
