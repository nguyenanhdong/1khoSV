<?php
namespace common\models;
use Yii;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;
use yii\web\IdentityInterface;


class Province extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'province';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['province_name'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'province_name' => 'Tên thành phố',
        ];
    }

    public static function getProvince(){
        $province = ArrayHelper::map(Province::find()->all(), 'province_name', 'province_name');
        return $province;
    }
}
