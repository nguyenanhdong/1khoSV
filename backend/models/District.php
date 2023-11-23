<?php

namespace backend\models;

use Yii;
/**
 * This is the model class for table "medical".
 *
 * @property integer $id
 * @property string $image
 * @property string $des
 * @property integer $status
 * @property string $content
 */
class District extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'district';
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
            'id' => 'ID'
        ];
    }
}
