<?php

/* @var $code string mã giới thiệu (user.referral_code) */

use yii\helpers\Url;
use yii\helpers\Html;
use yii\web\View;
use backend\models\Config;
use yii\widgets\Breadcrumbs;
use backend\controllers\CommonController;

?>
<div class="container">
    <?php
    echo Breadcrumbs::widget([
        'homeLink' => ['label' => '', 'url' => '/'],
        'links' => [
            'Mời bạn bè',
        ],
    ]);

    ?>

    <section class="voucher">
        <?= $this->render('/layouts/sidebar_info') ?>
        <div class="invite_friend d-flex flex-column">
            <div class="friend_text">
                <div class="text-center">
                    <span>Giới thiệu bạn bè</span>
                    <p>Gửi đường dẫn 1Kho kèm mã giới thiệu cho bạn bè để cùng mua sắm máy móc, vật tư nông nghiệp chính hãng với giá tốt.</p>
                </div>
                <div class="text-center code_friend flex-center flex-column">
                    <span>Mã giới thiệu của bạn</span>
                    <button type="button" class="btn_action btn-blue flex-center js_copy" data-copy="<?= Html::encode($code) ?>" title="Sao chép mã"><?= Html::encode($code) ?> <img src="/images/icon/copy.svg" alt=""></button>
                    <button type="button" class="btn_action bg_blue flex-center js_share" data-url="<?= Html::encode(Url::home(true) . '?ref=' . rawurlencode($code)) ?>" data-title="Mua sắm cùng 1Kho - mã giới thiệu <?= Html::encode($code) ?>">Chia Sẻ</button>
                </div>
            </div>
            <div class="text-center">
                <img class="img_friend" src="/images/page/friend.png" alt="">
            </div>
        </div>
    </section>
</div>