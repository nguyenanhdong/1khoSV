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
            ['label' => 'Máy cày', 'url' => ['product/delivery']],
            'Máy cày công nghệ mới 2024',
        ],
    ]);

    ?>
    <section class="product_info">
        <div class="product_info_group">
            <div class="product_list_img">
                <div class="card-wrapper">
                    <div class="card">
                        <div class="product-imgs">
                            <div class="img-display">
                                <div class="img-showcase">
                                    <img src="/images/page/product-examp.png" alt="shoe image">
                                    <img src="/images/page/product-examp.png" alt="shoe image">
                                    <img src="/images/page/product-examp.png" alt="shoe image">
                                    <img src="/images/page/product-examp.png" alt="shoe image">
                                    <img src="/images/page/product-examp.png" alt="shoe image">
                                </div>
                            </div>
                            <div class="img-select">
                                <div class="img-item">
                                    <a href="#" data-id="1">
                                        <img src="/images/page/product-examp.png" alt="shoe image">
                                    </a>
                                </div>
                                <div class="img-item">
                                    <a href="#" data-id="2">
                                        <img src="/images/page/product-examp.png" alt="shoe image">
                                    </a>
                                </div>
                                <div class="img-item">
                                    <a href="#" data-id="3">
                                        <img src="/images/page/product-examp.png" alt="shoe image">
                                    </a>
                                </div>
                                <div class="img-item">
                                    <a href="#" data-id="4">
                                        <img src="/images/page/product-examp.png" alt="shoe image">
                                    </a>
                                </div>
                                <div class="img-item">
                                    <a href="#" data-id="3">
                                        <img src="/images/page/product-examp.png" alt="shoe image">
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="product_info_right">
                <div class="product_description">
                    <h1>Máy cày Kubota sử dụng công nghệ mới 2024</h1>
                    <div class="product_rating flex-item-center">
                        <div class="rating_list flex-item-center">
                            <img src="/images/icon/star-product.svg" alt="">
                            <img src="/images/icon/star-product.svg" alt="">
                            <img src="/images/icon/star-product.svg" alt="">
                            <img src="/images/icon/star-product.svg" alt="">
                            <img src="/images/icon/star-product.svg" alt="">
                        </div>
                        <div class="rating_num flex-center">
                            <p>4.0 (200)</p>
                            <p>•</p>
                            <p>500 Đã bán</p>
                        </div>
                    </div>
                    <div class="product_price flex-item-center">
                        <span>200.000</span>
                        <p>-36%</p>
                    </div>
                </div>
                <div class="product_choose">
                    <span>Mã hàng</span>
                    <div class="list_code_product">
                        <button class="choose_btn_code">Mã hàng 1</button>
                        <button class="choose_btn_code">Mã hàng 2</button>
                        <button class="choose_btn_code">Mã hàng 3</button>
                        <button class="choose_btn_code">Mã hàng 4</button>
                        <button class="choose_btn_code">Mã hàng 5</button>
                    </div>
                </div>
                <div class="product_choose">
                    <span>Màu sắc</span>
                    <div class="list_code_product">
                        <button class="choose_btn_color">Màu đen</button>
                        <button class="choose_btn_color">Màu đỏ</button>
                        <button class="choose_btn_color">Màu xanh</button>
                    </div>
                </div>
                <div class="product_add_cart">
                    <div class="buy_now">
                        <div class="choose_number flex-center">
                            <button>-</button>
                            <span>2</span>
                            <button>+</button>
                        </div>
                        <button class="btn_action flex-center btn_buy_now"><img src="/images/icon/cart-icon.svg" alt="">Mua ngay</button>
                    </div>
                    <div class="add_cart">
                        <button class="btn_action bg_blue flex-center"><img src="/images/icon/cart.svg" alt="">Thêm vào giỏ</button>
                        <button class="btn_action bg_blue flex-center">Tư vấn</button>
                    </div>
                </div>
                <div class="category_tag_product">
                    <div class="group_cat_tag">
                        <span>Category: </span>
                        <div>
                            <a href="">Máy cày</a>
                            <a href="">Máy xúc</a>
                        </div>
                    </div>
                    <div class="group_cat_tag">
                        <span>Tag: </span>
                        <div>
                            <a href="">Máy cày</a>
                            <a href="">Công nông</a>
                            <a href="">Máy xúc</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="product_share">
            <span>Chia sẻ</span>
            <div>
                <a href=""><img src="/images/icon/fb.svg" alt=""></a>
                <a href=""><img src="/images/icon/zalo.svg" alt=""></a>
                <a href=""><img src="/images/icon/mess.svg" alt=""></a>
                <a href=""><img src="/images/icon/social.svg" alt=""></a>
            </div>
        </div>
    </section>

    <section class="shop_info">
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
    </section>

    <section class="product_description">
        <h2>Mô tả sản phẩm</h2>
        <div class="product_desc_content">
            <p>Đầu kéo máy cày Kubota M9540 là dòng máy nông nghiệp có công suất mạnh nhất trong thế giới máy cày Kubota hiện tại. Kubota M9540 không chỉ mạnh mẽ mà còn đa dụng, hiệu suất cao và hầu như đáp ứng hoàn hảo mọi yêu cầu canh tác vụ mùa mới. </p>
            <p>Đầu kéo máy cày Kubota M9540 là dòng máy nông nghiệp có công suất mạnh nhất trong thế giới máy cày Kubota hiện tại. Kubota M9540 không chỉ mạnh mẽ mà còn đa dụng, hiệu suất cao và hầu như đáp ứng hoàn hảo mọi yêu cầu canh tác vụ mùa mới. </p>
        </div>
    </section>

    <section class="comment">
        <h2>Đánh giá</h2>
        <div class="comment_list">
            <?php for ($i = 0; $i < 10; $i++) { ?>
                <div class="comment_item">
                    <img class="comment_avatar" src="/images/icon/avatar.png" alt="">
                    <div class="comment_group_right">
                        <div class="user_name flex-item-center">
                            <p>Bùi Thúy Hằng</p>
                            <span>•</span>
                            <span>20-10-2024</span>
                        </div>
                        <p>Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book</p>
                    </div>
                </div>
            <?php } ?>
        </div>
    </section>

    <section class="product_relate">
        <h2>Sản phẩm gợi ý</h2>
        <div class="product_list">
            <?php for($i = 0; $i < 5; $i++) { ?>
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
    </section>
</div>