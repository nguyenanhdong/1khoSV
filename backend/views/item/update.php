<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model mdm\admin\models\AuthItem */
/* @var $context mdm\admin\components\ItemController */

$context = $this->context;
$labels = $context->labels();
$this->title = 'Cập nhật ' . strtolower($labels['Items']);
$this->params['breadcrumbs'][] = 'Cấu hình';
$this->params['breadcrumbs'][] = ['label' => $labels['Items'], 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->name]];
$this->params['breadcrumbs'][] = 'Cập nhật';
?>

<div class="card mb-g">
    <div class="card-body">
    <?=
    $this->render('_form', [
        'model' => $model,
    ]);
    ?>
    </div>
</div>
