<?php
namespace common\models;

use Yii;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use \yii\db\Query;
/**
 * Order model
 *
 * @property integer $id
 * @property string $userid
 * @property string $link 
 * @property integer $status : 1 chờ xác nhận, 2 đã xác nhận, 3 đã huỷ 
 * @property integer $created_at
 * @property integer $updated_at
 
 */
class Order extends \yii\db\ActiveRecord
{
    
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%order}}';
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            ['status', 'default', 'value' => 1],
            ['link', 'required','message'=>'Link sản phẩm không được để trống'],
        ];
    }
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'username' => 'Khách hàng',
            'link' => 'Link ảnh',
            'created_at' => 'Thời gian tạo',
            'updated_at' => 'Cập nhật lần cuối',
            'status' => 'Trạng thái',
        ];
    }
    public function order(){
        $this->userid = Yii::$app->user->identity->id;
        if($this->save())
            return true;
        return false;
    }
    public function getOrder($type){
        $query = new Query;
        if($type=='all')
            $query	->select([
                'order.link','order.created_at','order.updated_at','order.status','order.id',
                'user.username','user.phone'])  
                ->from('order')
                ->join('INNER JOIN', 'user',
                    'user.id =order.userid')->orderby(['id'=>SORT_DESC])	;	
        if( $type=='waitting')    
            $query	->select([
                'order.link','order.created_at','order.updated_at','order.status','order.id',
                'user.username','user.phone'])  
                ->from('order')
                ->join('INNER JOIN', 'user','user.id =order.userid')
                ->where('order.status=1')
                ->orderby(['id'=>SORT_DESC])	;	 
        if($type == 'processing')
            $query	->select([
                'order.link','order.created_at','order.updated_at','order.status','order.id',
                'user.username','user.phone'])  
                ->from('order')
                ->join('INNER JOIN', 'user','user.id =order.userid')
                ->where('order.status=2')
                ->orderby(['id'=>SORT_DESC])	;
        if($type == 'delete')
            $query	->select([
                'order.link','order.created_at','order.updated_at','order.status','order.id',
                'user.username','user.phone'])  
                ->from('order')
                ->join('INNER JOIN', 'user','user.id =order.userid')
                ->where('order.status=0')
                ->orderby(['id'=>SORT_DESC])	;
        $command = $query->createCommand();
        return $data = $command->queryAll();
    }
    public function getHistoryOrder($id){
        
    }
    
}
