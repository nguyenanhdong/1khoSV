<?php

namespace backend\models;

use Yii;
use backend\models\GiftCode;
/**
 * This is the model class for table "banner".
 *
 * @property integer $id
 * @property string $image
 * @property integer $status
 * @property integer $index_banner
 */
class Promotion extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'promotion';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'],'required','message'=>'Nhập {attribute}'],
            [['image'], 'string', 'max' => 400],
            [['code'], 'validateCode'],
            [['code','content','description','is_show_button','type','date_start','date_end'], 'safe'],
        ];
    }

    public function validateCode($attribute, $params) {
        if( $this->code != '' ){
            $modelGiftCode = GiftCode::findOne(['code'=>$this->code]);
            if( !$modelGiftCode )
                $this->addError($attribute, Yii::t('app', 'Mã khuyến mại không tồn tại'));
            else if($this->type && $modelGiftCode->service != '' && $modelGiftCode->service != '0' ){
                $list_service = array_filter(explode(';',$modelGiftCode->service));
                if( !in_array($this->type, $list_service) )
                    $this->addError($attribute, Yii::t('app', 'Mã khuyến mại không dành cho dịch vụ này'));
            }
        }
    }

    public function beforeSave($insert){
        if( $this->date_start != '' ){
            if( strpos($this->date_start,'/') !== false ){
                $date_start = explode('/',$this->date_start);
                $this->date_start = $date_start[2] . '-' . $date_start[1] . '-' . $date_start[0];
            }
        }else
            $this->date_start = NULL;
        if( $this->date_end != '' ){
            if( strpos($this->date_end,'/') !== false ){
                $date_end = explode('/',$this->date_end);
                $this->date_end = $date_end[2] . '-' . $date_end[1] . '-' . $date_end[0];
            }
        }else
            $this->date_end = NULL;
        if( !isset($_POST['Promotion']['is_show_button']) ){
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
            'image' => 'Ảnh',
            'name' => 'Tên',
            'content' => 'Nội dung',
            'description' => 'Mô tả',
            'type' => 'Dịch vụ',
            'date_start' => 'Ngày bắt đầu',
            'date_end' => 'Ngày kết thúc',
            'code'     => 'Mã khuyến mại'
        ];
    }
}
