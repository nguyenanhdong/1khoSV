<?php

use yii\helpers\Url;
use yii\web\View;
use backend\models\Config;
use yii\widgets\Breadcrumbs;
use yii\widgets\ActiveForm;
use yii\helpers\Html;

$arrYear = [];
$currentYear = date("Y");
for ($year = $currentYear; $year >= 1900; $year--) {
    $arrYear[$year] = $year;
}

?>
<div class="container">
    <?php
    echo Breadcrumbs::widget([
        'homeLink' => ['label' => '', 'url' => '/'],
        'links' => [
            ['label' => 'Máy cày', 'url' => ['product/delivery']],
            'Đăng tin',
        ],
    ]);

    ?>

    <section class="post_delivery">
        <?php if ($model->hasErrors() && !empty($model->getErrors('image'))): ?>
            <div class="alert alert-danger">
                <?php foreach ($model->getErrors('image') as $error): ?>
                    <p><?= Html::encode($error) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <?php $form = ActiveForm::begin([
            'id' => 'post_delivery'
        ]); ?>
            <h2>Đăng tin</h2>
            <div class="form-group">
                <div class="box-image flex-center flex-column">
                    <!-- <input type="file" id="fileInput" class="hide_file" name="files[]" multiple accept="image/*,video/*"> -->
                    <?= $form->field($model, 'image[]')->fileInput(['multiple' => true, 'id' => 'fileInput', 'accept' => 'image/*,video/*']) ?>
                    <img src="/images/icon/icon-img.svg" alt="">
                    <label for="">Chọn 3 video ngắn dưới 1 phút + 8 hình ảnh</label>
                    <i class="error">Dung lượng ảnh tối đa 1MB, dung lượng video tối đa 20MB</i>
                </div>
                <div class="preview-box" id="previewBox"></div>
            </div>
            <?= $form->field($model, 'category_id')->dropDownList($formInfo['data']['list_category'], [
                'prompt' => 'Chọn chuyên mục', 
                'options' => [
                    $model->category_id => ['Selected' => true]
                ]
            ]) ?>
            <?= $form->field($model, 'brand_name')->textInput(['maxlength' => true, 'placeholder' => 'Nhập thương hiệu']) ?>
            <?= $form->field($model, 'type_strain')->textInput(['maxlength' => true, 'placeholder' => 'VD: Pc 200 - 8, Hd500,...']) ?>
            <div class="grid_50">
                <?= $form->field($model, 'production_year')->dropDownList($arrYear, [
                    'prompt' => 'Chọn năm sản xuất'
                ]) ?>
                <?= $form->field($model, 'origin')->textInput(['maxlength' => true, 'placeholder' => 'Nhập xuất xứ']) ?>
            </div>
            <div class="grid_50">
                <?= $form->field($model, 'fuel_id')->textInput(['maxlength' => true, 'placeholder' => 'Nhập nhiên liệu']) ?>
                <?= $form->field($model, 'kilometer_used')->textInput(['maxlength' => true, 'placeholder' => 'Nhập số km sử dụng']) ?>
            </div>
            <div class="grid_50">
                <?= $form->field($model, 'hours_of_use')->textInput(['maxlength' => true, 'placeholder' => 'Nhập số giờ sử dụng']) ?>
                <?= $form->field($model, 'price')->textInput(['maxlength' => true, 'placeholder' => 'Nhập giá']) ?>
            </div>
            <?= $form->field($model, 'title')->textInput(['maxlength' => true, 'placeholder' => 'Nhập tiêu đề']) ?>
            <?= $form->field($model, 'description')->textarea(['maxlength' => true, 'placeholder' => 'Nhập mô tả']) ?>
            <div class="action_form">
                <button class="btn_action btn-blue flex-center">XEM TRƯỚC</button>
                <?= Html::submitButton('ĐĂNG TIN', ['class' => 'btn_action btn-orange flex-center']) ?>
            </div>
        <?php ActiveForm::end(); ?>
    </section>
</div>

<script>
    document.getElementById('fileInput').addEventListener('change', function(event) {
    const previewBox = document.getElementById('previewBox');
    previewBox.innerHTML = ''; // Clear the current previews

    Array.from(event.target.files).forEach(file => {
        const reader = new FileReader();
        reader.onload = function(e) {
            const previewItem = document.createElement('div');
            previewItem.classList.add('preview-item');

            if (file.type.startsWith('image/')) {
                const img = document.createElement('img');
                img.src = e.target.result;
                previewItem.appendChild(img);
            } else if (file.type.startsWith('video/')) {
                const video = document.createElement('video');
                video.src = e.target.result;
                video.controls = true;
                previewItem.appendChild(video);
            }

            previewBox.appendChild(previewItem);
        };
        reader.readAsDataURL(file);
    });
    });
</script>