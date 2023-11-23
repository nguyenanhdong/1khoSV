<?php
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $user common\models\User */

$verifyLink = Yii::$app->urlManager->createAbsoluteUrl(['site/verify-email', 'token' => $user->activation_key]);
?>
<div class="password-reset">
    <p>Hello <?= Html::encode($user->first_name) ?> <?= Html::encode($user->last_name) ?>,</p>

    <p>Your information:</p>
    <p>- Email: <?= Html::encode($user->email) ?></p>

    <p>Follow the link below to verify your email:</p>

    <p><?= Html::a(Html::encode($verifyLink), $verifyLink) ?></p>
</div>
