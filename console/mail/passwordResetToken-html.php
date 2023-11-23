<?php
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $user common\models\User */

$resetLink = 'Link reset';//Yii::$app->urlManager->createAbsoluteUrl(['site/reset-password', 'token' => $user['password_reset_token']]);
?>
<div class="password-reset">
    <p>Hello <?= Html::encode($user['first_name']) ?> <?= Html::encode($user['last_name']) ?>,</p>

    <p>Follow the link below to reset your password:</p>

    <p><?= Html::a(Html::encode($resetLink), $resetLink) ?></p>
</div>
