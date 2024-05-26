<?php

use yii\helpers\Url;
use yii\web\View;
use backend\models\Config;
use yii\widgets\Breadcrumbs;

?>
<div class="container">
    <?php
    echo Breadcrumbs::widget([
        'homeLink' => ['label' => '', 'url' => '/'],
        'links' => [
            // ['label' => '   Sample Post', 'url' => ['post/edit', 'id' => 1]],
            'Máy nổ, động cơ ',
        ],
    ]);

    ?>
    <section class="list_product">
        <div class="product_top_title justify-content-start">
            <h2 class="mr-2">Máy nổ, động cơ</h2>
            <span class="color-gray">183 sản phẩm</span>
        </div>
        <div class="list_product_cat">
            <?php for ($i = 0; $i < 6; $i++) { ?>
                <a href="">
                    <img src="/images/page/may-cay.png" alt="">
                    <p>Danh mục con</p>
                </a>
            <?php } ?>
        </div>
        <div class="sort_product">
            <p class="d-none d-lg-block">Sắp xếp theo</p>
            <div class="sort_list">
                <button class="btn_sort active">Phổ biến</button>
                <button class="btn_sort">Bán chạy</button>
                <button class="btn_sort">Hàng mới</button>
                <button class="btn_sort d-block d-lg-none">Giá <img src="/images/icon/icon-sort.svg" alt=""></button>
                <button class="btn_sort d-none d-lg-block">Giá tăng</button>
                <button class="btn_sort d-none d-lg-block">Giá giảm</button>
            </div>
        </div>
        <div class="product_list">
            <?php for ($i = 0; $i < 20; $i++) { ?>
                <div class="product_item">
                    <a href="<?= Url::to(['/product/detail']) ?>">
                        <span class="prod_sale">56% <br> OFF</span>
                        <img class="prod_avatar" src="/images/page/product-maycay.png" alt="">
                        <div class="prod_price_star">
                            <p class="prod_title">Máy cày Kubota sử dụng công nghệ mới</p>
                            <div class="des_prod mt-2">
                                <span>200.000</span>
                                <div class="flex-center">
                                    <img src="/images/icon/star.svg" alt="">
                                    <p class="product_star">4.0 (200)</p>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            <?php } ?>
        </div>
        <div class="see_more_product">
            <button class="see_more_btn">Xem thêm</button>
        </div>
    </section>
</div>