<?php

namespace backend\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\models\Staff;

/**
 * CoachSectionSearch represents the model behind the search form about `backend\models\CoachSection`.
 */
class StaffSearch extends Staff
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['fullname','email','phone', 'create_at'], 'safe'],
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
        $query = Staff::find();

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
            'create_at' => $this->create_at,
        ]);
        if( isset($_GET['tab']) && $_GET['tab'] != '' ){
            switch($_GET['tab']){
                case 'active':
                    $query->andFilterWhere(['is_active'=>1]);
                    break;
                case 'pending':
                    $query->andFilterWhere(['is_active'=>2]);
                    break;
                case 'banned':
                    $query->andFilterWhere(['is_active'=>0]);
                    break;
                default:
                    break;
            }
        }else{
            $query->andFilterWhere(['is_active'=>1]);
        }
        if( strpos($this->fullname,'ID') !== false )
            $query->andFilterWhere(['id'=>str_replace('ID','',$this->fullname)]);
        else
            $query->andFilterWhere(['or',['like', 'fullname', $this->fullname],['like', 'phone', $this->fullname],['like', 'email', $this->fullname]]);
        $query->orderBy(['id' => SORT_DESC]);

        return $dataProvider;
    }
}
