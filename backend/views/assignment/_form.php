<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;

/* @var $this yii\web\View */
/* @var $model backend\models\Employee */
/* @var $form yii\widgets\ActiveForm */
list(, $url) = Yii::$app->assetManager->publish('@mdm/admin/assets');

$this->registerCssFile($url . '/multiple-select.css?v=' . time());
$this->registerJsFile($url . '/multiple-select.js', ['depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJS('(function($){
    $(".select_multiple").multipleSelect({
        width:"100%",
        placeholder : "Chọn nhóm quyền",
        allSelected : "Đã chọn toàn bộ nhóm quyền",
        countSelected : "Đã chọn <strong>#</strong> trong tổng <strong>%</strong> nhóm quyền"
    });
})(jQuery);
');
?>
<style>
.form-group {
    margin-bottom: 15px;
    float: left;
    width: 100%;
}
</style>
<div class="employee-form">

    <?php $form = ActiveForm::begin([
    'id' => "some_form",
    // 'action' => [''],
    'options' => ['class' => 'edit_form'],
    'enableAjaxValidation' => true,
    'enableClientValidation' => true,
    'fieldConfig' => [
        'template' => "{label}\n<div class=\"col-lg-5\">{input}</div>\n<div class=\"col-lg-4 div_err\">{error}</div>",
        'labelOptions' => ['class' => 'col-lg-3 control-label'],
]
]); ?>

    <?= $form->field($model, 'username')->textInput(['maxlength' => true,'autocomplete'=>'off'])->label('Tài khoản') ?>

    <?php if($model->isNewRecord)
        echo $form->field($model, 'password')->passwordInput(['maxlength' => true,'autocomplete'=>'off'])->label('Mật khẩu');
    ?>

    <?= $form->field($model, 'fullname')->textInput(['maxlength' => true])->label('Họ tên') ?>
    
    <?= $form->field($model, 'email')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'phone')->textInput(['maxlength' => true])->label('Điện thoại') ?>
    <?= $form->field($model, 'receive_data_auto')->checkbox(['label' => null])->label('Nhận khách hệ thống giao tự động?') ?>
    <?= $form->field($model, 'is_active')->checkbox(['label' => null])->label('Active tài khoản?') ?>
    <?php if( Yii::$app->user->identity->is_admin ): ?>
    <?= $form->field($model, 'is_admin')->checkbox(['label' => null])->label('Là Admin?') ?>
    <?php 
        endif;
    ?>
    <div class="form-group field-user-role">
        <label class="col-lg-3 control-label" for="">Nhóm quyền</label>
        <div class="col-lg-5">
            <select multiple="multiple" name="roles[]" class="select_multiple">
                <?php
                    foreach($model->userRole as $role=>$typechecked){
                ?>
                <option <?= ($typechecked == 'checked' ? 'selected="true"' : '') ?> value="<?= $role ?>"><?= $role ?></option>
                <?php
                    }
                ?>
                
            </select>
        </div>
    </div>
    
    <div class="form-group text-center" style="width:85%">
        <?= Html::submitButton($model->isNewRecord ? 'Thêm' : 'Cập nhật', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
