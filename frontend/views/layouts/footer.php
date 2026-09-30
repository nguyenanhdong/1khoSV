<?php

use yii\helpers\Html;
use yii\helpers\Url;
use frontend\components\SiteInfo;
use frontend\controllers\SiteController;

$pageLink = function ($slug, $label) {
    return SiteController::pageContent($slug) !== ''
        ? '<li><a href="' . Url::to(['/site/page', 'slug' => $slug]) . '">' . $label . '</a></li>'
        : '';
};
$email = SiteInfo::get('email');
$phone = SiteInfo::phone();
$appAndroid = SiteInfo::get('app_android');
$appIos = SiteInfo::get('app_ios');
?>
<div id="footer">
    <div class="container">
        <div class="footer_gr">
            <div class="footer_item">
                <a href="<?= Url::home() ?>">
                    <img class="logo" src="/images/icon/logo.svg" alt="1Kho">
                </a>
                <h4>Liên hệ</h4>
                <?php if ($email !== ''): ?>
                <div>
                    <img src="/images/icon/email.svg" alt="">
                    <a href="mailto:<?= Html::encode($email) ?>">Email <br> <?= Html::encode($email) ?></a>
                </div>
                <?php endif; ?>
                <?php if ($phone !== ''): ?>
                <div>
                    <img src="/images/icon/phone.svg" alt="">
                    <a href="<?= SiteInfo::tel() ?>">Tổng đài hỗ trợ <br> <?= Html::encode($phone) ?></a>
                </div>
                <?php endif; ?>
                <?php if ($appAndroid !== '' || $appIos !== ''): ?>
                    <h4>Tải ứng dụng</h4>
                    <a href="<?= Html::encode($appAndroid ?: $appIos) ?>" target="_blank" rel="noopener">
                        <img class="download_app w-100" src="/images/icon/down-app.png" alt="Tải ứng dụng 1Kho">
                    </a>
                <?php endif; ?>
            </div>
            <div class="footer_item">
                <h2>CHĂM SÓC KHÁCH HÀNG</h2>
                <ul>
                    <?= $pageLink('huong-dan-mua-hang', 'Hướng Dẫn Mua Hàng') ?>
                    <?= $pageLink('thanh-toan', 'Thanh Toán') ?>
                    <?= $pageLink('van-chuyen', 'Vận Chuyển') ?>
                    <?= $pageLink('chinh-sach-doi-tra', 'Trả Hàng &amp; Hoàn Tiền') ?>
                    <?= $pageLink('chinh-sach-bao-hanh', 'Chính Sách Bảo Hành') ?>
                    <li><a href="<?= Url::to(['/site/contact']) ?>">Liên Hệ</a></li>
                </ul>
            </div>
            <div class="footer_item">
                <h2>VỀ 1KHO</h2>
                <ul>
                    <?= $pageLink('gioi-thieu', 'Giới Thiệu 1Kho') ?>
                    <?= $pageLink('tuyen-dung', 'Tuyển Dụng') ?>
                    <?= $pageLink('dieu-khoan', 'Điều Khoản') ?>
                    <?= $pageLink('chinh-sach-bao-mat', 'Chính Sách Bảo Mật') ?>
                </ul>
            </div>
        </div>
        <div class="footer_bottom">
            <p>© <?= date('Y') ?> <?= Html::encode(SiteInfo::get('name', '1Kho')) ?>. All rights reserved.</p>
        </div>
    </div>
</div>
