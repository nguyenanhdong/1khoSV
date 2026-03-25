<?php

use backend\models\Category;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model backend\models\CategoryTags */
/* @var $form yii\widgets\ActiveForm */

$categoryParent = Category::getListCategoryParent();
if(!$model->isNewRecord){
    unset($categoryParent[$_GET['id']]);
}
?>

<div class="category-tags-form">
    <?php $form = ActiveForm::begin(); ?>
    <div class="row">
        <div class="col-lg-8">
            <div class="input-group-control">
                <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>
                <?= $form->field($model, 'show_in_header')->checkbox(['uncheck' => 0, 'value' => 1]); ?>
                <?= $form->field($model, 'show_in_home')->checkbox(['uncheck' => 0, 'value' => 1]); ?>
                <?= $form->field($model, 'sort_order')->textInput(['type' => 'number','maxlength' => true]) ?>
                <?= $form->field($model, 'home_position')->textInput(['type' => 'number','maxlength' => true]) ?>
            </div>
        </div>
        <div class="col-lg-4">
            <?= $form->field($model, 'parent_id')->dropDownList($categoryParent,['prompt' => Yii::t('app', 'Chọn chuyên mục cha'), 'class' => 'form-control select2 select2-hidden']) ?>
            <div id="gallery">
                <label class="control-label" style="width:100%; position: relative;">
                    <div style="width: 86%;"><?= Yii::t('app', 'Ảnh') ?> <br> <i><?= Yii::t('app', 'Chọn định dạng ảnh image/jpeg, image/png, image/jpg, image/svg') ?></i></div>
                    <span class="blue fileinput-button" style="color: #337ab7;float:right;float: right; position: relative; bottom:20px;" id="fileinput">
                        <input class="gallery-photo file-upload-ajax" data-folder="images/product" accept="image/jpeg, image/png, image/jpg, image/svg" style="width:auto" type="file" id="gallery-photo-add-mobile">
                    </span>
                    <?= $form->field($model, 'image')->hiddenInput(['id' => 'post_avatar_wap', 'class' => 'input-hidden-value'])->label(false) ?>
                    <div class="meter" style="display:none;margin:10px 0">
                        <span style="width:0"></span>
                        <i></i>
                    </div>
                    <div style="position: relative" class="box_preview" class="box_preview">
                        <img id="preview-img" class="img-preview" src="<?= !empty($model->image) ? $model->image : '/img/upload.png' ?>" style="<?= $model->image != '' ? 'width: auto; max-width: 100%; max-height: 200px; left: 0; top: 10px' : 'width: auto; max-width: 100%; max-height: 200px' ?>" />
                        <div style="top: -10px" class="group_preview <?= empty($model->image) ? 'd-none' : '' ?>">
                            <i class="fal fa-times-circle remove_img" aria-hidden="true"></i>
                        </div>
                    </div>
                </label>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-12">
            <div class="form-group text-center" style="margin-top:10px">
                <?= Html::submitButton($model->isNewRecord ? '<i class="fal fa-plus"></i> Thêm' : '<i class="fal fa-edit"></i> Cập nhật', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
                <a class="btn btn-info btn_cancel" href="index">Hủy</a>
            </div>
        </div>
    </div>
    <?php ActiveForm::end(); ?>
</div>
