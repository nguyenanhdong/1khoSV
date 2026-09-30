<?php

/* @var $this yii\web\View */
/* @var $name string */
/* @var $message string */
/* @var $exception Exception */

use yii\helpers\Html;
use yii\helpers\Url;

$code = $exception instanceof \yii\web\HttpException ? $exception->statusCode : 500;
$this->title = ($code === 404 ? 'Không tìm thấy trang' : 'Có lỗi xảy ra') . ' - 1Kho';
$text = $code === 404
    ? ($message && $message !== 'Page not found.' ? $message : 'Trang bạn tìm không tồn tại hoặc đã bị gỡ.')
    : ($code < 500 && $message ? $message : 'Hệ thống đang gặp sự cố, vui lòng thử lại sau ít phút.');
?>
<div class="container">
    <section class="page_content text-center" style="margin-top:30px;padding:60px 20px">
        <div style="font-size:64px;font-weight:700;color:#0091ff;line-height:1"><?= (int)$code ?></div>
        <h1 style="margin:16px 0 8px"><?= $code === 404 ? 'Không tìm thấy trang' : 'Có lỗi xảy ra' ?></h1>
        <p class="color-gray"><?= Html::encode($text) ?></p>
        <div class="d-flex justify-content-center flex-wrap" style="gap:10px;margin-top:20px">
            <a class="btn_action btn-orange flex-center" style="padding:0 22px" href="<?= Url::home() ?>">Về trang chủ</a>
            <a class="btn_action btn-blue flex-center" style="padding:0 22px" href="<?= Url::to(['/product/search']) ?>">Xem sản phẩm</a>
            <a class="btn_action btn-blue flex-center" style="padding:0 22px" href="<?= Url::to(['/site/contact']) ?>">Liên hệ hỗ trợ</a>
        </div>
    </section>
</div>
