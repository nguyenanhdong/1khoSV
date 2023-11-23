<?php

/* @var $this yii\web\View */
/* @var $user common\models\User */

$verifyLink = Yii::$app->urlManager->createAbsoluteUrl(['site/verify-email', 'token' => $user->activation_key]);
?>
Hello <?= $user->first_name ?> <?= $user->last_name ?>,

Your information:
- Email: <?= $user->email ?>

Follow the link below to verify your email:

<?= $verifyLink ?>
