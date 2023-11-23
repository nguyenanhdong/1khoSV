<?php

namespace backend\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\models\Notify;

/**
 * BannerSearch represents the model behind the search form about `backend\models\Banner`.
 */
class NotifySearch extends Notify
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['title','type','type_notify'], 'safe'],
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
        $query = Notify::find();

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
            'type' => $this->type
        ]);
        if( isset($_GET['tab']) && $_GET['tab'] != '' ){
            switch($_GET['tab']){
                case 'customer':
                    $query->andFilterWhere(['user_notify'=>1]);
                    break;
                case 'staff':
                    $query->andFilterWhere(['user_notify'=>2]);
                    break;
                default:
                    break;
            }
        }else{
            $query->andFilterWhere(['user_notify'=>1]);
        }
        if( !$this->type_notify || $this->type_notify == 1 )
            $query->andFilterWhere(['obj_id'=>0]);
        else
            $query->andFilterWhere(['>','obj_id',0]);
            
        $query->andFilterWhere(['like', 'title', $this->title]);

        return $dataProvider;
    }
}
