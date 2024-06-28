<?php

use yii\helpers\Url;
use yii\web\View;
use backend\models\Config;
use frontend\controllers\HelperController;
use yii\widgets\Breadcrumbs;

?>
<div class="container">
    <?php
    echo Breadcrumbs::widget([
        'homeLink' => ['label' => '', 'url' => '/'],
        'links' => [
            ['label' => $product['agent_info']['name'], 'url' => ['category/index', 'cate_parent_id' => $product['agent_info']['id']]],
            $product['product_info']['name'],
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
                                    <?php
                                        if(!empty($product['product_info']['images'])){
                                            foreach($product['product_info']['images'] as $img){
                                    ?>
                                    <img src="<?= $img ?>" alt="shoe image">
                                    <?php }} ?>
                                </div>
                            </div>
                            <div class="img-select">
                                    <?php
                                    $i = 0;
                                    if(!empty($product['product_info']['images'])){
                                        foreach($product['product_info']['images'] as $img){
                                            $i++;
                                ?>
                                    <div class="img-item">
                                        <a href="#" data-id="<?= $i ?>">
                                            <img src="<?= $img ?>" alt="product image">
                                        </a>
                                    </div>
                                <?php }} ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="product_info_right">
                <div class="product_description">
                    <h1><?= $product['product_info']['name'] ?></h1>
                    <div class="product_rating flex-item-center">
                        <div class="rating_list flex-item-center">
                            <img src="/images/icon/star-product.svg" alt="star">
                            <img src="/images/icon/star-product.svg" alt="star">
                            <img src="/images/icon/star-product.svg" alt="star">
                            <img src="/images/icon/star-product.svg" alt="star">
                            <img src="/images/icon/star-product.svg" alt="star">
                        </div>
                        <div class="rating_num flex-center">
                            <p><?= $product['product_info']['star'] ?> (<?= $product['product_info']['total_rating'] ?>)</p>
                            <p>•</p>
                            <p><?= $product['product_info']['quantity_sold'] ?> Đã bán</p>
                        </div>
                    </div>
                    <div class="product_price flex-item-center">
                        <span><?= HelperController::formatPrice($product['product_info']['price']) ?></span>
                        <p>-<?= $product['product_info']['percent_discount'] ?>%</p>
                    </div>
                </div>
                <?php 
                    if(!empty($product['product_info']['classification_group'])){
                        foreach($product['product_info']['classification_group'] as $row){
                ?>
                            <div class="product_choose">
                                <span><?= $row['name'] ?></span>
                                <div class="list_code_product">
                                    <?php foreach($row['childs'] as $child){ ?>
                                        <button dt-class="option_<?= $row['id'] ?>" dt-id="<?= $child['id'] ?>" class="choose_btn_code option_<?= $row['id'] ?>"><?= $child['name'] ?></button>
                                    <?php } ?>
                                </div>
                            </div>
                <?php }} ?>
                <div class="product_add_cart">
                    <div class="buy_now">
                        <div class="choose_number flex-center">
                            <button class="update_qty" dt-type="decrease">-</button>
                            <input type="text" class="quantity_product" value="1">
                            <button class="update_qty" dt-type="increase">+</button>
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
                <a target="_blank" href="https://www.facebook.com/sharer/sharer.php?u=<?= HelperController::getCurrentUrl() ?>"><img src="/images/icon/fb.svg" alt=""></a>
                <a target="_blank" href="https://zalo.me/share?url=<?= HelperController::getCurrentUrl() ?>"><img src="/images/icon/zalo.svg" alt=""></a>
                <!-- <a target="_blank" href=""><img src="/images/icon/mess.svg" alt=""></a> -->
                <!-- <a target="_blank" href=""><img src="/images/icon/social.svg" alt=""></a> -->
            </div>
        </div>
    </section>

    <section class="shop_info">
        <div class="shop_info_group">
            <div class="info_desc flex-item-center">
                <img src="/images/icon/shop.png" alt="">
                <div class="text_rating">
                    <a href=""><?= $product['agent_info']['name'] ?></a>
                    <div class="rating_box flex-item-center">
                        <!-- <div>
                            <img src="/images/icon/star.svg" alt="">
                            <img src="/images/icon/star.svg" alt="">
                            <img src="/images/icon/star.svg" alt="">
                            <img src="/images/icon/star.svg" alt="">
                            <img src="/images/icon/star.svg" alt="">
                        </div>
                        <span>4.5/5.0 (200)</span>
                        <span>•</span> -->
                        <span><?= $product['agent_info']['total_follow'] ?> Người theo dõi</span>
                    </div>
                </div>
            </div>
            <button class="btn_follow btn_action">Theo dõi</button>
        </div>
    </section>

    <section class="product_description">
        <h2>Mô tả sản phẩm</h2>
        <div class="product_desc_content">
            <?= $product['product_info']['description'] ?>
        </div>
    </section>
    <?php if(!empty($product['review_info'])){ ?>
        <section class="comment">
            <h2>Đánh giá</h2>
            <div class="comment_list">
                <?php foreach($product['review_info'] as $row) { ?>
                    <div class="comment_item">
                        <img class="comment_avatar" src="<?= !empty($row['avatar']) ? $row['avatar'] : '/images/icon/user-icon.svg' ?>" alt="">
                        <div class="comment_group_right">
                            <div class="user_name flex-item-center">
                                <p><?= $row['fullname'] ?></p>
                                <span>•</span>
                                <span><?= $row['date_review'] ?></span>
                            </div>
                            <p><?= $row['content'] ?></p>
                            <?php if(!empty($row['video_image'])){  ?>
                                <div class="video_image_comment slider-comment-nav">
                                    <?php 
                                        foreach($row['video_image'] as $link){ 
                                            $is_video = false;
                                            $element = '<div class="item_slide slide_nav"><img class="img_slide_nav" src="'. $link .'"></div>';
                                            if(strpos($link, 'mp4') !== false)
                                                $element = '<div class="item_slide slide_nav position-relative">
                                                                <video class="img_slide_nav" width="640" height="360">
                                                                    <source src="'. $link .'" type="video/mp4">
                                                                    Your browser does not support the video tag.
                                                                </video>
                                                                <div class="icon_play flex-center"><img src="/images/icon/play.svg"></div>
                                                            </div>';
                                    ?>
                                        <?= $element ?>
                                    <?php } ?>
                                    <?php 
                                        foreach($row['video_image'] as $link){ 
                                            $is_video = false;
                                            $element = '<div class="item_slide slide_nav"><img class="img_slide_nav" src="'. $link .'"></div>';
                                            if(strpos($link, 'mp4') !== false)
                                                $element = '<div class="item_slide slide_nav position-relative">
                                                                <video class="img_slide_nav" width="640" height="360">
                                                                    <source src="'. $link .'" type="video/mp4">
                                                                    Your browser does not support the video tag.
                                                                </video>
                                                                <div class="icon_play flex-center"><img src="/images/icon/play.svg"></div>
                                                            </div>';
                                    ?>
                                        <?= $element ?>
                                    <?php } ?>
                                </div>
                                 <div class="video_image_comment slider-comment-for hide">
                                    <?php 
                                        foreach($row['video_image'] as $link){ 
                                            $is_video = false;
                                            $element = '<div class="item_slide item_for"><img class="" src="'. $link .'"></div>';
                                            if(strpos($link, 'mp4') !== false)
                                                $element = '<div class="item_slide item_for">
                                                                <video class="" width="640" height="360" controls>
                                                                    <source src="'. $link .'" type="video/mp4">
                                                                    Your browser does not support the video tag.
                                                                </video>
                                                            </div>';
                                    ?>
                                        <?= $element ?>
                                    <?php } ?>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                <?php } ?>
            </div>
        </section>
    <?php } ?>

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