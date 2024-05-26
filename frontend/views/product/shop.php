<?php

use yii\helpers\Url;
use yii\web\View;
use backend\models\Config;
use yii\widgets\Breadcrumbs;

?>
<div class="container">
    <section class="shop">
        <img src="/images/page/banner-shop.png" alt="" class="banner_shop w-100">
        <div class="shop_info">
            <div class="shop_info_group">
                <div class="info_desc flex-item-center">
                    <img src="/images/icon/shop.png" alt="">
                    <div class="text_rating">
                        <a href="">Shop Máy Cày</a>
                        <div class="rating_box flex-item-center">
                            <div>
                                <img src="/images/icon/star.svg" alt="">
                                <img src="/images/icon/star.svg" alt="">
                                <img src="/images/icon/star.svg" alt="">
                                <img src="/images/icon/star.svg" alt="">
                                <img src="/images/icon/star.svg" alt="">
                            </div>
                            <span>4.5/5.0 (200)</span>
                            <span>•</span>
                            <span>1245 Người theo dõi</span>
                        </div>
                    </div>
                </div>
                <button class="btn_follow btn_action">Theo dõi</button>
            </div>
            <div class="search_shop">
                <div class="form-group">
                    <input type="text" placeholder="Tìm trong shop này">
                </div>
            </div>
        </div>
    </section>

    <section class="sale_index">
        <h2>Săn sale cùng 1KHO</h2>
        <div class="sale_list slide_sale slick_global">
            <?php for ($i = 0; $i < 10; $i++) { ?>
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

    <section class="cat_list_index">
        <div class="cat_list_index_title d-flex d-lg-none">
            <p>Danh mục sản phẩm</p>
            <a href="">Tất cả <i class="fal fa-long-arrow-right"></i></a>
        </div>
        <div class="cat_list_group">
            <a href="" class="cat_list_item">
                <div class="flex-center">
                    <img src="/images/page/may-no.png" alt="">
                </div>
                <p>Máy nổ, <br> Động cơ</p>
            </a>
            <a href="" class="cat_list_item">
                <div class="flex-center">
                    <img src="/images/page/may-no.png" alt="">
                </div>
                <p>Máy nổ, <br> Động cơ</p>
            </a>
            <a href="" class="cat_list_item">
                <div class="flex-center">
                    <img src="/images/page/may-no.png" alt="">
                </div>
                <p>Máy nổ, <br> Động cơ</p>
            </a>
            <a href="" class="cat_list_item">
                <div class="flex-center">
                    <img src="/images/page/may-no.png" alt="">
                </div>
                <p>Máy nổ, <br> Động cơ</p>
            </a>
            <a href="" class="cat_list_item">
                <div class="flex-center">
                    <img src="/images/page/may-no.png" alt="">
                </div>
                <p>Máy nổ, <br> Động cơ</p>
            </a>
            <a href="" class="cat_list_item">
                <div class="flex-center">
                    <img src="/images/page/may-no.png" alt="">
                </div>
                <p>Máy nổ, <br> Động cơ</p>
            </a>
            <a href="" class="cat_list_item">
                <div class="flex-center">
                    <img src="/images/page/may-no.png" alt="">
                </div>
                <p>Máy nổ, <br> Động cơ</p>
            </a>
            <a href="" class="cat_list_item">
                <div class="flex-center">
                    <img src="/images/page/may-no.png" alt="">
                </div>
                <p>Máy nổ, <br> Động cơ</p>
            </a>
        </div>
    </section>

    <section class="list_product">
        <div class="sort_product">
            <p class="d-none d-lg-block">Sản phẩm của shop</p>
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