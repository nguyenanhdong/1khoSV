<?php

namespace backend\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\models\Customer;

/**
 * CoachSectionSearch represents the model behind the search form about `backend\models\CoachSection`.
 */
class CustomerSearch extends Customer
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['date_start','date_end','fullname','email','phone', 'create_at'], 'safe'],
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
        $query = Customer::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort'=>false,
            'pagination' => [ 'pageSize' => 15 ],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
        ]);
        if( $this->date_start )
            $query->andFilterWhere(['>=','create_at',$this->date_start]);
        if( $this->date_end )
            $query->andFilterWhere(['<=','create_at',$this->date_end . ' 23:59:59']);
        if( $this->fullname )
            $this->fullname = trim($this->fullname);
        if( is_numeric($this->fullname) && $this->fullname > 0 )
            $query->andFilterWhere(['or',['like', 'phone', $this->fullname],['id' => $this->fullname]]);
        else
            $query->andFilterWhere(['or',['like', 'fullname', $this->fullname],['like', 'phone', $this->fullname],['like', 'email', $this->fullname]]);
        $query->orderBy(['id' => SORT_DESC]);

        return $dataProvider;
    }
}
