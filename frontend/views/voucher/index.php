<?php

use yii\helpers\Url;
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
            // ['label' => '   Sample Post', 'url' => ['post/edit', 'id' => 1]],
            'Ví voucher ',
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
                <?php for ($i = 0; $i < 20; $i++) { ?>
                    <div class="voucher_item">
                        <div class="voucher_avatar flex-center">
                            <img src="/images/page/img-voucher.png" alt="">
                        </div>
                        <div class="voucher_desc">
                            <span>Giảm 50k</span>
                            <p>Hoàn 10% cho đơn hàng có giá trị trên 1tr vào KHOvoucher</p>
                            <a href="">Dùng ngay</a>
                        </div>
                        <div class="tooltips">
                            <img class="show_detail_voucher" src="/images/icon/i.svg" alt="">
                            <div class="content_tooltips hide">
                                <div class="voucher_item">
                                    <div class="voucher_avatar flex-center">
                                        <img src="/images/page/img-voucher.png" alt="">
                                    </div>
                                    <div class="voucher_desc">
                                        <span>Giảm 50k</span>
                                        <p>Hoàn 10% cho đơn hàng có giá trị trên 1tr vào KHOvoucher</p>
                                        <a href="">Dùng ngay</a>
                                    </div>
                                </div>
                                <div class="info_voucher">
                                    <div class="text_voucher">
                                        <p>Mã</p>
                                        <span>12908739</span>
                                    </div>
                                    <div class="text_voucher">
                                        <p>Hạn sử dụng</p>
                                        <span>20/10/2021</span>
                                    </div>
                                    <div class="text_list">
                                        <p>Điều kiện:</p>
                                        <ul>
                                            <li>Giảm 50k Cho đơn hàng 200k</li>
                                            <li>Áp dụng cho sản phẩm của Máy Cày Office</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            </div>
            <div class="voucher_list" id="used">
                <?php for ($i = 0; $i < 20; $i++) { ?>
                    <div class="voucher_item">
                        <div class="voucher_avatar flex-center">
                            <img src="/images/page/img-voucher.png" alt="">
                        </div>
                        <div class="voucher_desc">
                            <span>Giảm 20k</span>
                            <p>Hoàn 15% cho đơn hàng có giá trị trên 1tr vào KHOvoucher</p>
                            <a href="">Dùng ngay</a>
                        </div>
                        <div class="tooltips">
                            <img class="show_detail_voucher" src="/images/icon/i.svg" alt="">
                            <div class="content_tooltips hide">
                                <div class="voucher_item">
                                    <div class="voucher_avatar flex-center">
                                        <img src="/images/page/img-voucher.png" alt="">
                                    </div>
                                    <div class="voucher_desc">
                                        <span>Giảm 50k</span>
                                        <p>Hoàn 10% cho đơn hàng có giá trị trên 1tr vào KHOvoucher</p>
                                        <a href="">Dùng ngay</a>
                                    </div>
                                </div>
                                <div class="info_voucher">
                                    <div class="text_voucher">
                                        <p>Mã</p>
                                        <span>12908739</span>
                                    </div>
                                    <div class="text_voucher">
                                        <p>Hạn sử dụng</p>
                                        <span>20/10/2021</span>
                                    </div>
                                    <div class="text_list">
                                        <p>Điều kiện:</p>
                                        <ul>
                                            <li>Giảm 50k Cho đơn hàng 200k</li>
                                            <li>Áp dụng cho sản phẩm của Máy Cày Office</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            </div>
        </div>
    </section>
</div>