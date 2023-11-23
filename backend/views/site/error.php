<?php

/* @var $this yii\web\View */
/* @var $name string */
/* @var $message string */
/* @var $exception Exception */

use yii\helpers\Html;

$this->title = $name;
?>
<div class="site-error">

<h1>Trang không tồn tại( #404)</h1>

    <div class="alert alert-danger">
        <?= nl2br(Html::encode($message)) ?>
    </div>

    <p>
        Trang bạn yêu cầu không tồn tại
    </p>
    <p>
        Vui lòng liên hệ lại với chúng tôi. Xin cảm ơn!
    </p>

</div>
