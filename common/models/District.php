<?php
namespace common\models;
use Yii;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;
use yii\web\IdentityInterface;


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
            [['district_name','province_id'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'district_name' => 'Tên quận huyện',
            'province_id' => 'ID Tỉnh/ Thành phố',
        ];
    }

    public static function getDistrict($province_name){
        $province = Province::findOne(['province_name' =>$province_name]);
        $district = ArrayHelper::map(District::find()->where(['province_id' => $province->id])->all(), 'district_name', 'district_name');
        return $district;
    }
}
