<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model backend\models\Course */

$this->title = 'Tạo voucher';
$this->params['breadcrumbs'][] = ['label' => 'Voucher', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="course-create">
    <div class="card mb-g">
        <div class="card-body">
            <?= $this->render('_form', [
                'model' => $model,
            ]) ?>
        </div>
    </div>
</div>
