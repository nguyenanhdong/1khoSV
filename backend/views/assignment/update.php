<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model backend\models\Employee */

$this->title = 'Cập nhật tài khoản ' . $model->fullname;
$this->params['breadcrumbs'][] = ['label' => 'Tài khoản', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="employee-update">
    <?php
      if (Yii::$app->session->hasFlash('message')){
          $msg      = Yii::$app->session->getFlash('message');
          echo '<div class="alert alert-success">
                    ' . $msg . '
                </div>';
          Yii::$app->session->setFlash('message',null);
      }

    ?>
    <!-- <h1><?= Html::encode($this->title) ?></h1> -->

    <?= $this->render('_form', [
        'model' => $model
    ]) ?>

</div>
