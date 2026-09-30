<?php

/* @var $this yii\web\View */
/* @var $form yii\widgets\ActiveForm */
/* @var $model \common\models\LoginForm */

use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = 'Đăng nhập';
$field = ['template' => "{label}\n{input}\n{error}", 'options' => ['class' => 'am-field', 'style' => 'margin-bottom:14px'], 'errorOptions' => ['class' => 'am-field-error']];
?>
<h2 style="font-size:20px;font-weight:800;color:#1a2035;margin-bottom:4px">Đăng nhập</h2>
<p style="font-size:12px;color:#6b7280;margin-bottom:20px">Dùng tài khoản nhân viên được quản trị viên cấp</p>

<?php $form = ActiveForm::begin(['id' => 'js-login', 'errorCssClass' => 'has-error']); ?>
<?= $form->field($model, 'username', $field)->label('Tài khoản')
    ->textInput(['class' => 'am-input am-input-lg', 'required' => true, 'placeholder' => 'Tên đăng nhập', 'autocomplete' => 'username', 'autofocus' => true]) ?>
<?= $form->field($model, 'password', $field)->label('Mật khẩu')
    ->passwordInput(['class' => 'am-input am-input-lg', 'required' => true, 'placeholder' => 'Mật khẩu', 'autocomplete' => 'current-password']) ?>
<?= Html::submitButton('Đăng nhập', ['class' => 'am-btn am-dark-gradient', 'style' => 'width:100%;padding:13px;font-size:15px;margin-top:6px', 'name' => 'login-button']) ?>
<?php ActiveForm::end(); ?>
