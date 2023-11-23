<?php

namespace backend\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\models\CaundryProduct;

/**
 * MedicalSearch represents the model behind the search form about `backend\models\Medical`.
 */
class CaundryProductSearch extends CaundryProduct
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            
            [['id','name','category_id','status'], 'safe'],
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
        $query = CaundryProduct::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
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
            'is_delete'=> 0,
            'status' => $this->status,
            'category_id' => $this->category_id
        ]);

        $query->andFilterWhere(['like', 'name', $this->name])->orderBy(['id' => SORT_DESC]);

        return $dataProvider;
    }
}
