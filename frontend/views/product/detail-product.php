<?php

use yii\helpers\Url;
use yii\web\View;
use backend\models\Config;
use frontend\controllers\HelperController;
use yii\widgets\Breadcrumbs;
// echo '<pre>';
// print_r($product);
// echo '</pre>';die;
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
                            <?php 
                                if($product['product_info']['star'] > 0) 
                                    for($i = 0; $i < $product['product_info']['star'];$i++){
                            ?>
                            <img src="/images/icon/star-product.svg" alt="star">
                            <?php } ?>
                        </div>
                        <div class="rating_num flex-center">
                            <p><?= $product['product_info']['star'] ?> (<?= $product['product_info']['total_rating'] ?>)</p>
                            <p>•</p>
                            <p><?= $product['product_info']['quantity_sold'] ?> Đã bán</p>
                        </div>
                    </div>
                    <div class="product_price flex-item-center">
                        <span class="price_product"><?= HelperController::formatPrice($product['product_info']['price']) ?></span>
                        <p>-<?= $product['product_info']['percent_discount'] ?>%</p>
                    </div>
                </div>
                <?php if(!empty($product['product_info']['classification_group'])){ ?>
                    <div class="product_classification">
                        <?php foreach($product['product_info']['classification_group'] as $row){ ?>
                            <div class="product_choose">
                                <span><?= $row['name'] ?></span>
                                <div class="list_code_product">
                                    <?php foreach($row['childs'] as $child){ ?>
                                        <button dt-class="option_<?= $row['id'] ?>" dt-id="<?= $child['id'] ?>" class="option_product option_<?= $row['id'] ?>"><?= $child['name'] ?></button>
                                    <?php } ?>
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                <?php } ?>
                <div class="product_add_cart">
                    <div class="buy_now">
                        <div class="choose_number flex-center">
                            <button class="update_qty" dt-type="decrease">-</button>
                            <input type="text" class="quantity_product" value="1">
                            <button class="update_qty" dt-type="increase">+</button>
                        </div>
                        <button id="buy_now" class="btn_action flex-center btn_buy_now"><img src="/images/icon/cart-icon.svg" alt="">Mua ngay</button>
                    </div>
                    <div class="add_cart">
                        <button id="add_cart" class="btn_action bg_blue flex-center"><img src="/images/icon/cart.svg" alt="">Thêm vào giỏ</button>
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
                <img src="<?= $product['agent_info']['avatar'] ?>" alt="">
                <div class="text_rating">
                    <a href="<?= Url::to(['/product/shop', 'id' => $product['agent_info']['id']]) ?>"><?= $product['agent_info']['name'] ?></a>
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
    <?= $this->render('/product/template-comment', ['product' => $product]) ?>
    <?php if(!empty($product['product_suggest'])){ ?>
        <section class="product_relate">
            <h2>Sản phẩm gợi ý</h2>
            <div class="product_list">
                <?php foreach($product['product_suggest'] as $row) { ?>
                    <div class="product_item">
                        <a href="<?= Url::to(['/product/detail', 'id' => $row['id']]) ?>">
                            <span class="prod_sale"><?= $row['percent_discount'] ?>% <br> OFF</span>
                            <img class="prod_avatar" src="<?= $row['image'] ?>" alt="">
                            <div class="prod_price_star">
                                <p class="prod_title line_2" title="<?= $row['name'] ?>"><?= $row['name'] ?></p>
                                <div class="des_prod mt-2">
                                    <span><?= HelperController::formatPrice($row['price']) ?></span>
                                    <div class="flex-center">
                                        <img src="/images/icon/star.svg" alt="Star">
                                        <p class="product_star"><?= $row['star'] ?> (<?= $row['total_rate'] ?>)</p>
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
<input type="hidden" id="total_classification" value="<?= !empty($product['product_info']['classification_group']) ? count($product['product_info']['classification_group']) : 0 ?>"> 
<input type="hidden" id="product_id" value="<?= $product['product_info']['id'] ?>">  
<input type="hidden" id="classification_id" value="">  