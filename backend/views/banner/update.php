<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
/* @var $this yii\web\View */
/* @var $model backend\models\CategoryTags */

$this->title = 'Cập nhật';
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Banner'), 'url' => ['index']];
$this->params['breadcrumbs'][] = Yii::t('app', 'Cập nhật banner');
?>
<div class="utility-update">
    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
