<?php

namespace backend\models;

use Yii;
use backend\models\GiftCode;
/**
 * This is the model class for table "medical".
 *
 * @property integer $id
 * @property string $image
 * @property string $des
 * @property integer $status
 * @property string $content
 */
class Package extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'package';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['package_name','price','price_promotion'],'required','message'=>'Nhập {attribute}'],
            // [['image'],'required','message'=>'Chọn {attribute}'],
            [['code'], 'validateCode'],
            [['type_package','code','description','date_start','date_end'],'safe']
        ];
    }

    public function validateCode($attribute, $params) {
        if( $this->code != '' ){
            $modelGiftCode = GiftCode::findOne(['code'=>$this->code]);
            if( !$modelGiftCode )
                $this->addError($attribute, Yii::t('app', 'Mã khuyến mại không tồn tại'));
            else if($this->type_package && $modelGiftCode->service != '' && $modelGiftCode->service != '0' ){
                $list_service = array_filter(explode(';',$modelGiftCode->service));
                if( !in_array($this->type_package, $list_service) )
                    $this->addError($attribute, Yii::t('app', 'Mã khuyến mại không dành cho dịch vụ này'));
            }
        }
    }
    public function beforeSave($insert){
        if( $this->price )
            $this->price = str_replace([',','.'],'',$this->price);
        if( $this->price_promotion )
            $this->price_promotion = str_replace([',','.'],'',$this->price_promotion);
        return parent::beforeSave($insert);
    }
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'price' => 'Giá gốc',
            'price_promotion' => 'Giá khuyến mại',
            'description' => 'Mô tả',
            'date_start' => 'Ngày bắt đầu',
            'date_end' => 'Ngày kết thúc',
            'type_package' => 'Dịch vụ',
            'package_name' => 'Tên gói',
            'code' => 'Mã khuyến mại'
        ];
    }
}
