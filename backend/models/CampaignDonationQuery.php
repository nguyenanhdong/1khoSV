<?php

namespace backend\models;

/**
 * This is the ActiveQuery class for [[CampaignDonation]].
 *
 * @see CampaignDonation
 */
class CampaignDonationQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return CampaignDonation[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return CampaignDonation|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
