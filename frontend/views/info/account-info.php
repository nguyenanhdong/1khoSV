<?php

use yii\helpers\Url;
use yii\web\View;
use backend\models\Config;
use yii\widgets\Breadcrumbs;
use backend\controllers\CommonController;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

?>
<div class="container">
    <?php
    echo Breadcrumbs::widget([
        'homeLink' => ['label' => '', 'url' => '/'],
        'links' => [
            // ['label' => '   Sample Post', 'url' => ['post/edit', 'id' => 1]],
            'Ví voucher ',
        ],
    ]);

    ?>

    <section class="voucher">
        <?= $this->render('/layouts/sidebar_info') ?>
        <div class="form_right">
            <?php if (Yii::$app->session->hasFlash('success')): ?>
                <div class="alert alert-success">
                    <?= Yii::$app->session->getFlash('success') ?>
                </div>
            <?php endif; ?>
            <h2>Thông tin cá nhân</h2>
            <div class="status_acc flex-item-center">
                <p class="color-gray">Trạng thái tài khoản</p>
                <span class="active"><?= $model->is_verify_account == 1 ? 'Đã xác thực' : 'Chưa xác thực' ?></span>
            </div>
            <!-- <form action=""> -->
            <?php $form = ActiveForm::begin(); ?>
                <?= $form->field($model, 'fullname')->textInput(['maxlength' => true]) ?>
                <?= $form->field($model, 'phone')->textInput(['maxlength' => true]) ?>
                <div class="grid_50 g-15">
                    <?= $form->field($model, 'province')->dropDownList($province, [
                        'prompt' => 'Chọn tỉnh thành', 
                        'options' => [
                            $model->province => ['Selected' => true]
                        ]
                    ]) ?>
                    <?= $form->field($model, 'district')->dropDownList($district, [
                        'prompt' => 'Chọn quận huyện', 
                        'options' => [
                            $model->province => ['Selected' => true]
                        ]
                    ]) ?>
                    <!-- <div class="form-group">
                        <label for="">Quận/Huyện</label>
                        <select id="">
                            <option value="">Chọn Quận/Huyện</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="">Thành phố</label>
                        <select id="">
                            <option value="">Thành phố</option>
                        </select>
                    </div> -->
                </div>
                <?= $form->field($model, 'address')->textInput(['maxlength' => true]) ?>
                <div class="form-group">
                    <?= Html::submitButton('Lưu', ['class' => 'btn_action btn-orange flex-center']) ?>
                </div>
            <!-- </form> -->
             <?php ActiveForm::end(); ?>
        </div>
    </section>
</div>