<?php

namespace backend\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\models\GiftCode;

/**
 * MedicalSearch represents the model behind the search form about `backend\models\Medical`.
 */
class GiftCodeSearch extends GiftCode
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            
            [['code','type_price','service'], 'safe'],
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
        $query = GiftCode::find();

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
            'type_price' => $this->type_price,
            'type' => 1
            // 'service'    => $this->service
        ]);
        
        if( !$this->service && isset($_GET['service']) && !empty($_GET['service']) ){
            $query->andFilterWhere(['or',
                ['service'=>0],
                ['like','service',';' . $_GET['service'] . ';']
            ]);
        }else if($this->service){
            $query->andFilterWhere(['like','service',';' . $_GET['service'] . ';']);
        }
        $query->andFilterWhere(['like', 'code', $this->code])->orderBy(['id' => SORT_DESC]);

        return $dataProvider;
    }
}
