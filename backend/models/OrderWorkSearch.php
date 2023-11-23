<?php

namespace backend\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\models\OrderWork;


/**
 * CoachSectionSearch represents the model behind the search form about `backend\models\CoachSection`.
 */
class OrderWorkSearch extends OrderWork
{
    public $list_id;
    public $staff_id;
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['repeat_every_week','date_end_work','date_start_work','date_start','type_order','date_end','status','staff_id','user_id','fullname','email','phone', 'create_at'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = OrderWork::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort'=>false,
            'pagination' => [ 'pageSize' => 20 ],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }
        if( $this->service_id && !$this->type_order ){
            $listServiceChild = \backend\models\Services::getListServiceChild($this->service_id);
            $listServiceChild = empty($listServiceChild) ? [$this->service_id] : array_keys($listServiceChild);

            $query->andFilterWhere(['in','type_order',$listServiceChild]);
        }
        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'create_at' => $this->create_at,
            'type_order' => $this->type_order,
            'status' => $this->status,
            // 'staff_id' => $this->staff_id,
            'user_id' => $this->user_id,
        ]);
        if( $this->staff_id ){
            if( $this->staff_id == 9999 ){
                $query->andFilterWhere(['total_staff_receiver'=>0]);
            }else if($this->staff_id == 1000){
                $query->andFilterWhere('total_staff_receiver > 0 and total_staff_receiver < total_staff');
            }else{
                $list_id = \yii\helpers\ArrayHelper::map(\backend\models\StaffOrder::find()->where(['staff_id'=>$this->staff_id])->all(),'order_id','order_id');
                if( empty($list_id) )
                    $list_id = [0];
                $query->andFilterWhere(['in','id',$list_id]);
            }
        }

        
        if( !empty($this->list_id) ){
            $query->andFilterWhere(['in','id',$this->list_id]);
        }
        if( $this->date_start )
            $query->andFilterWhere(['>=','create_at',$this->date_start]);
        if( $this->date_end )
            $query->andFilterWhere(['<=','create_at',$this->date_end . ' 23:59:59']);
        if( $this->date_start_work )
            $query->andFilterWhere(['>=','workday',$this->date_start_work]);
        if( $this->date_end_work )
            $query->andFilterWhere(['<=','workday',$this->date_end_work . ' 23:59:59']);
        if( $this->fullname )
            $this->fullname = trim($this->fullname);
        
        if( is_numeric($this->fullname) && $this->fullname > 0 )
            $query->andFilterWhere(['or',['like', 'phone', $this->fullname],['id' => $this->fullname]]);
        else{
            if( $this->service_id == 3 && $this->fullname ){
                $search     = $this->fullname;
                $sql_product_ship = "Select order_id from product_shipper where fullname_receiver like '%$search%' or id_custom like '%$search%' or id_unique like '%$search%'";
                $list_id = \yii\helpers\ArrayHelper::map(Yii::$app->db->CreateCommand($sql_product_ship)->queryAll(),'order_id','order_id');
                if( empty($list_id) )
                    $list_id = [0];
                $query->andFilterWhere(['or',['in', 'id', $list_id],['like', 'fullname', $this->fullname],['like', 'phone', $this->fullname],['like', 'email', $this->fullname]]);
            }else
                $query->andFilterWhere(['or',['like', 'fullname', $this->fullname],['like', 'phone', $this->fullname],['like', 'email', $this->fullname]]);
        }
        
        $query->orderBy(['id' => SORT_DESC]);

        return $dataProvider;
    }
}
