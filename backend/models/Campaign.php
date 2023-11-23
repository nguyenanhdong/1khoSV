<?php

namespace backend\models;

use backend\models\Employee;
use backend\models\User;
use Yii;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "campaign".
 *
 * @property int $id
 * @property string $name
 * @property string $description
 * @property int $created_by
 * @property string $thumbnail
 * @property string $created_at
 * @property string $update_at
 * @property string|null $start_at
 * @property string|null $end_at
 *
 * @property User $createdBy
 */
class Campaign extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'campaign';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'name', 'description', 'created_by', 'thumbnail', 'created_at', 'update_at'], 'required'],
            [['id', 'created_by'], 'integer'],
            [['description'], 'string'],
            [['created_at', 'update_at', 'start_at', 'end_at'], 'safe'],
            [['name', 'thumbnail'], 'string', 'max' => 255],
            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['created_by' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'name' => Yii::t('app', 'Name'),
            'description' => Yii::t('app', 'Description'),
            'created_by' => Yii::t('app', 'Created By'),
            'thumbnail' => Yii::t('app', 'Thumbnail'),
            'created_at' => Yii::t('app', 'Created At'),
            'update_at' => Yii::t('app', 'Update At'),
            'start_at' => Yii::t('app', 'Start At'),
            'end_at' => Yii::t('app', 'End At'),
        ];
    }

    /**
     * Gets query for [[CreatedBy]].
     *
     * @return ActiveQuery
	 */
    public function getCreatedBy()
    {
        return $this->hasOne(Employee::className(), ['id' => 'created_by']);
    }

    /**
     * {@inheritdoc}
     * @return CampaignQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new CampaignQuery(get_called_class());
    }
}
