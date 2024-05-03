<?php

use yii\helpers\Url;
use yii\web\View;
use backend\models\Config;
use backend\controllers\CommonController;


?>

<div class="container">
    <section class="banner">
        <div class="banner_index">
            <div class="banner_item">
                <a href="">
                    <img src="/images/banner/banner.png" alt="">
                </a>
            </div>
            <div class="banner_item">
                <a href="">
                    <img src="/images/banner/banner.png" alt="">
                </a>
            </div>
            <div class="banner_item">
                <a href="">
                    <img src="/images/banner/banner.png" alt="">
                </a>
            </div>
        </div>
    </section>
    <section class="cat_list_index">
        <div class="cat_list_index_title d-flex d-lg-none">
            <p>Danh mục sản phẩm</p>
            <a href="">Tất cả <i class="fal fa-long-arrow-right"></i></a>
        </div>
        <div class="cat_list_group">
            <?php for ($i = 0; $i < 8; $i++) { ?>
                <a href="" class="cat_list_item">
                    <div class="flex-center">
                        <img src="/images/page/may-no.png" alt="">
                    </div>
                    <p>Máy nổ, <br> Động cơ</p>
                </a>
            <?php } ?>
        </div>
    </section>

    <section class="product_top">
        <div class="product_top_title">
            <h2>Tin Rao vặt</h2>
            <a href="">Tất cả <i class="far fa-angle-right"></i></a>
        </div>
        <div class="product_top_tab">
            <p>Tin mới • Tin đăng mua • Bán nhanh có trả phí</p>
        </div>
        <div class="product_top_cat">
            <div class="product_top_cat_item">
                <div>
                    <a class="flex-center" href="">Máy cày</a>
                    <span>UP to 80% OFF</span>
                </div>
                <div>
                    <img src="/images/page/may-cay.png" alt="">
                </div>
            </div>
            <div class="product_top_cat_item">
                <div>
                    <a class="flex-center" href="">Máy cày</a>
                    <span>UP to 80% OFF</span>
                </div>
                <div>
                    <img src="/images/page/may-cay.png" alt="">
                </div>
            </div>
            <div class="product_top_cat_item">
                <div>
                    <a class="flex-center" href="">Máy cày</a>
                    <span>UP to 80% OFF</span>
                </div>
                <div>
                    <img src="/images/page/may-cay.png" alt="">
                </div>
            </div>
        </div>
        <div class="product_list product_slide slick_global">
            <?php for ($i = 0; $i < 10; $i++) { ?> 
                <div class="product_item">
                        <a href="">
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
    </section>

    <section class="sale_index">
        <h2>Săn sale cùng 1KHO</h2>
        <div class="sale_list slick_global">
            <?php for($i = 0; $i < 10; $i++) { ?>
                <div class="sale_list_item">
                    <a href="">
                        <span class="num_sale">-36%</span>
                        <div class="flex-center flex-column">
                            <img class="sale_prod_avatar" src="/images/page/may-cay.png" alt="">
                            <p class="title_prod">Máy cày Kubota sử dụng công nghệ mới</p>
                        </div>
                        <div class="sale_text">
                            <div>
                                <p>200.000</p>
                                <span>699.000</span>
                            </div>
                            <p>Kết thúc sau <strong>4 ngày</strong></p>
                            <p>Chỉ còn <strong>15 sản phẩm</strong></p>
                        </div>
                    </a>
                </div>
            <?php } ?>
        </div>
        <div class="sale_see_more flex-center">
            <button>Xem tất cả</button>
        </div>
    </section>

    <?php for($j = 0; $j < 4; $j++) { ?>
        <section class="list_product">
            <div class="product_top_title">
                <h2>Máy nổ, động cơ</h2>
                <a href="">Tất cả <i class="far fa-angle-right"></i></a>
            </div>
            <div class="list_product_cat">
                <?php for($i = 0;$i < 6; $i++) { ?>
                    <a href="">
                        <img src="/images/page/may-cay.png" alt="">
                        <p>Danh mục con</p>
                    </a>
                <?php } ?>
            </div>
            <div class="product_list product_slide slick_global">
                <?php for ($i = 0; $i < 10; $i++) { ?> 
                    <div class="product_item">
                            <a href="">
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
        </section>
    <?php } ?>
</div>