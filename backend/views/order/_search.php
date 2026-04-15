<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use backend\models\Category;

?>

<div class="panel">
    <div class="panel-hdr">
        <h2>
            Lọc và tìm kiếm
        </h2>
    </div>
    <div class="panel-container show">
        <div class="panel-content">
            <?php $form = ActiveForm::begin([
                'action' => ['index'],
                'method' => 'get',
            ]); ?>
            <div class="row">
                <div class="col-lg-3">
                    <label class="lab_select" for=""><?= Yii::t('app', 'Trạng thái') ?></label>
                    <?= $form->field($model, 'status')->dropDownList(Yii::$app->params['status_order'],['prompt'=>'Tất cả', 'class' => 'form-control select2 select2-hidden'])->label(false) ?>
                </div>      
                <div class="col-lg-3">
                    <label class="lab_select" for=""><?= Yii::t('app', 'Phương thức thanh toán') ?></label>
                    <?= $form->field($model, 'type_payment')->dropDownList(Yii::$app->params['type_payment'],['prompt'=>'Tất cả', 'class' => 'form-control select2 select2-hidden'])->label(false) ?>
                </div>      
                <div class="col-lg-3">
                    <div class="form-group">
                        <?= Html::submitButton('<i class="fal fa-search"></i> Tìm kiếm', ['class' => 'btn btn-primary']) ?>
                        <?= Html::a('<i class="fal fa-plus"></i> Thêm', ['create'], ['class' => 'btn btn-success']) ?>
                    </div>
                </div>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>
<style>
    .form-parent .select2{
        width:100% !important;
    }
    .img-grid{
        max-width: 200px;
        max-height: 100px;
    }
</style>
