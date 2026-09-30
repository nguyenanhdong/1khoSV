<?php

use yii\helpers\Url;
use yii\web\View;
use backend\models\Config;
use yii\widgets\Breadcrumbs;
use backend\controllers\CommonController;
use backend\models\Agent;
use frontend\controllers\HelperController;
/** "Dùng ngay": voucher của shop → mở shop để chọn sản phẩm; voucher 1Kho → giỏ hàng (chọn voucher ở bước thanh toán) */
$useUrl = function ($row) {
    return !empty($row['agent_id']) && Agent::getInfoAgent($row['agent_id']) ? Url::to(['/product/shop', 'id' => $row['agent_id']]) : Url::to(['/cart/index']);
};
?>
<div class="container">
    <?php
    echo Breadcrumbs::widget([
        'homeLink' => ['label' => '', 'url' => '/'],
        'links' => [
            'Voucher của tôi',
        ],
    ]);

    ?>

    <section class="voucher">
        <?= $this->render('/layouts/sidebar_info') ?>
        <div class="voucher_content">
            <div class="tab_voucher">
                <button dt-tab="unused" class="btn_voucher active">Chưa sử dụng</button>
                <button dt-tab="used" class="btn_voucher">Đã dùng/hết hạn</button>
            </div>
            <div class="voucher_list active" id="unused">
                <?php
                    if(!empty($dataUnused)){
                        foreach($dataUnused as $row){
                ?>
                    <div class="voucher_item">
                        <div class="voucher_avatar flex-center">
                            <img src="<?= $row['image'] ?>" alt="">
                        </div>
                        <div class="voucher_desc">
                            <span><?= $row['name'] ?></span>
                            <p><?= $row['desc'] ?></p>
                            <a href="<?= $useUrl($row) ?>">Dùng ngay</a>
                        </div>
                        <div class="tooltips">
                            <img class="show_detail_voucher" src="/images/icon/i.svg" alt="">
                            <div class="content_tooltips hide">
                                <div class="voucher_item">
                                    <div class="voucher_avatar flex-center">
                                        <img src="<?= $row['image'] ?>" alt="">
                                    </div>
                                    <div class="voucher_desc">
                                        <span><?= $row['name'] ?></span>
                                        <p><?= $row['desc'] ?></p>
                                        <a href="<?= $useUrl($row) ?>">Dùng ngay</a>
                                    </div>
                                </div>
                                <div class="info_voucher">
                                    <!-- <div class="text_voucher">
                                        <p>Mã</p>
                                        <span>12908739</span>
                                    </div> -->
                                    <div class="text_voucher">
                                        <p>Hạn sử dụng</p>
                                        <span><?= $row['expire_time'] ?></span>
                                    </div>
                                    <div class="text_list">
                                        <p>Điều kiện:</p>
                                        <ul>
                                            <li><?= $row['name'] ?> Cho đơn hàng <?= HelperController::formatPrice($row['minimum_order']) ?></li>
                                            <?php if(!empty(Agent::getInfoAgent($row['agent_id']))){ ?>
                                                <li>Áp dụng cho sản phẩm của <a href="<?= Url::to(['/product/shop', 'id' => Agent::getInfoAgent($row['agent_id'])['id']]) ?>"><?= Agent::getInfoAgent($row['agent_id']) ? Agent::getInfoAgent($row['agent_id'])['name'] : ''; ?></a></li>
                                            <?php } ?>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php }} else { ?>
                    <p class="text-center color-gray p-4">Bạn chưa có voucher nào. Voucher sẽ xuất hiện khi 1Kho hoặc shop tặng cho bạn.</p>
                <?php } ?>
            </div>
            <div class="voucher_list" id="used">
            <?php
                if(!empty($dataUsed)){
                    foreach($dataUsed as $row){
                ?>
                    <div class="voucher_item">
                        <div class="voucher_avatar flex-center">
                            <img src="<?= $row['image'] ?>" alt="">
                        </div>
                        <div class="voucher_desc">
                            <span><?= $row['name'] ?></span>
                            <p><?= $row['desc'] ?></p>
                            
                        </div>
                        <div class="tooltips">
                            <img class="show_detail_voucher" src="/images/icon/i.svg" alt="">
                            <div class="content_tooltips hide">
                                <div class="voucher_item">
                                    <div class="voucher_avatar flex-center">
                                        <img src="<?= $row['image'] ?>" alt="">
                                    </div>
                                    <div class="voucher_desc">
                                        <span><?= $row['name'] ?></span>
                                        <p><?= $row['desc'] ?></p>
                                        
                                    </div>
                                </div>
                                <div class="info_voucher">
                                    <!-- <div class="text_voucher">
                                        <p>Mã</p>
                                        <span>12908739</span>
                                    </div> -->
                                    <div class="text_voucher">
                                        <p>Hạn sử dụng</p>
                                        <span><?= $row['expire_time'] ?></span>
                                    </div>
                                    <div class="text_list">
                                        <p>Điều kiện:</p>
                                        <ul>
                                            <li><?= $row['name'] ?> Cho đơn hàng <?= HelperController::formatPrice($row['minimum_order']) ?></li>
                                            <?php if(!empty(Agent::getInfoAgent($row['agent_id']))){ ?>
                                                <li>Áp dụng cho sản phẩm của <a href="<?= Url::to(['/product/shop', 'id' => Agent::getInfoAgent($row['agent_id'])['id']]) ?>"><?= Agent::getInfoAgent($row['agent_id']) ? Agent::getInfoAgent($row['agent_id'])['name'] : ''; ?></a></li>
                                            <?php } ?>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php }} ?>
            </div>
        </div>
    </section>
</div>