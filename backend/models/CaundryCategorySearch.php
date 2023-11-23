<?php

namespace backend\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\models\CaundryCategory;

/**
 * MedicalSearch represents the model behind the search form about `backend\models\Medical`.
 */
class CaundryCategorySearch extends CaundryCategory
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            
            [['id','name','service_id','status'], 'safe'],
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
        $query = CaundryCategory::find();

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
            'service_id' => $this->service_id
        ]);

        $query->andFilterWhere(['like', 'name', $this->name])->orderBy(['id' => SORT_DESC]);

        return $dataProvider;
    }
}
