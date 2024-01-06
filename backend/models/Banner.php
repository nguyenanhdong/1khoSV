<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "banner".
 *
 * @property integer $id
 * @property string $image
 * @property integer $status
 * @property integer $index_banner
 */
class Banner extends \yii\db\ActiveRecord
{
    const BANNER_CUSTOMER = 1;
    const BANNER_AGENT = 2;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'banner';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'],'required','message'=>'Nhập {attribute}'],
            [['image'], 'string', 'max' => 400],
            [['description','type_show','is_show_button','type','date_start','date_end'], 'safe'],
        ];
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
            'description' => 'Nội dung',
            'category_id' => 'Chuyên mục',
            'date_start' => 'Ngày bắt đầu',
            'date_end' => 'Ngày kết thúc',
            'type' => 'Loại',
        ];
    }

    public static function getListBannerApp($type){
        $domain = Yii::$app->params['urlDomain'];
        $sql_banner = '
            SELECT id, concat("' . $domain . '",image) as image, category_id
            FROM banner where is_delete = 0 and type = :type and (( date_start is null and date_end is null ) or ( date_start <= "' . date('Y-m-d') . '" and date_end >= "' . date('Y-m-d') . '"))
        ';
        return Yii::$app->db->CreateCommand($sql_banner, [':type' => $type])->queryAll();
    }
}
