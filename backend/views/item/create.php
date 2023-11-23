<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model mdm\admin\models\AuthItem */
/* @var $context mdm\admin\components\ItemController */

$context = $this->context;
$labels = $context->labels();
$this->params['breadcrumbs'][] = 'Cấu hình';
$this->title = 'Thêm ' . strtolower($labels['Items']);
$this->params['breadcrumbs'][] = ['label' => $labels['Items'], 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
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
