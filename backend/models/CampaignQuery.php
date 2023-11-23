<?php

namespace backend\models;

/**
 * This is the ActiveQuery class for [[Campaign]].
 *
 * @see Campaign
 */
class CampaignQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return Campaign[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return Campaign|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
