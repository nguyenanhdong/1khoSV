<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model \common\models\LoginForm */

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

$this->title = 'Login';
$this->params['breadcrumbs'][] = $this->title;
?>
<?php $form = ActiveForm::begin(['id' => 'js-login']); ?>

<?= $form->field($model, 'username', [
        'template' => "{label}\n{input}\n{error}",
        'labelOptions' => [ 'class' => 'form-label']
      ])->textInput(array('class' => 'form-control form-control-lg','required'=>true,'placeholder'=>'Tài khoản'));
?>
<?= $form->field($model, 'password',[
        'template' => "{label}\n{input}\n{error}",
        'labelOptions' => [ 'class' => 'form-label']
    ])->passwordInput(array('class' => 'form-control form-control-lg','required'=>true,'placeholder'=>'Mật khẩu')) ?>

<div class="form-group">
    <?= Html::submitButton('Đăng nhập', ['class' => 'btn btn-info btn-block btn-lg waves-effect waves-themed','id'=>'js-login-btn', 'name' => 'login-button']) ?>
</div>

<?php ActiveForm::end(); ?>
