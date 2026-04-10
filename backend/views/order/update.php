<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
/* @var $this yii\web\View */
/* @var $model backend\models\CategoryTags */

$this->title = 'Xem chi tiết đơn hàng';
$this->params['breadcrumbs'][] = ['label' => 'Đơn hàng', 'url' => ['index']];
$this->params['breadcrumbs'][] = Yii::t('app', 'Xem chi tiết đơn hàng');
?>
<div class="utility-update">
    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
