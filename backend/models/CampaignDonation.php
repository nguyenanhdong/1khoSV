<?php

namespace backend\models;

use Yii;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "campaign_donation".
 *
 * @property int $id
 * @property int $user_id
 * @property string $phone
 * @property int $email
 * @property int $amount
 * @property string $created_at
 * @property string $updated_at
 * @property int $status
 * @property int $campaign_id
 *
 * @property User $user
 * @property Campaign $campaign
 */
class CampaignDonation extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'campaign_donation';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'phone', 'email', 'amount', 'status', 'campaign_id'], 'required'],
            [['user_id', 'email', 'amount', 'status', 'campaign_id'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['phone'], 'string', 'max' => 255],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['user_id' => 'id']],
            [['campaign_id'], 'exist', 'skipOnError' => true, 'targetClass' => Campaign::className(), 'targetAttribute' => ['campaign_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'user_id' => Yii::t('app', 'User ID'),
            'phone' => Yii::t('app', 'Phone'),
            'email' => Yii::t('app', 'Email'),
            'amount' => Yii::t('app', 'Amount'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
            'status' => Yii::t('app', 'Status'),
            'campaign_id' => Yii::t('app', 'Campaign ID'),
        ];
    }

    /**
     * Gets query for [[User]].
     *
     * @return ActiveQuery
	 */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    /**
     * Gets query for [[Campaign]].
     *
     * @return ActiveQuery
	 */
    public function getCampaign()
    {
        return $this->hasOne(Campaign::className(), ['id' => 'campaign_id']);
    }

    /**
     * {@inheritdoc}
     * @return CampaignDonationQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new CampaignDonationQuery(get_called_class());
    }
}
