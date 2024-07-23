<?php

use yii\helpers\Url;
use yii\web\View;
use backend\models\Config;
use frontend\controllers\HelperController;
use yii\widgets\Breadcrumbs;

?>
<div class="container">

    <section class="type_delivery">
        <div class="item_type">
            <a class="flex-center" href="">
                <div class="flex-center">
                    <img src="/images/icon/search.svg" alt="">
                </div>
                <p>Tìm Mua</p>
            </a>
        </div>
        <div class="item_type">
            <a class="flex-center" href="">
                <div class="flex-center">
                    <img src="/images/icon/cart-deli.svg" alt="">
                </div>
                <p>Tin Bán</p>
            </a>
        </div>
        <div class="item_type">
            <a class="flex-center" href="<?= Url::to(['/product/post-delivery']) ?>">
                <div class="flex-center">
                    <img src="/images/icon/pen.svg" alt="">
                </div>
                <p>Đăng tin</p>
            </a>
        </div>
    </section>

    <section class="list_product">
        <div class="sort_product">
            <p class="title_hot"><img src="/images/icon/hot.svg" alt=""> Tin hot dành cho bạn</p>
        </div>
        <div class="product_list product_slide slick_global">
            <?php 
            if(!empty($deliveryHot)){
                foreach($deliveryHot as $row){
            ?> 
                <div class="product_item">
                    <a href="<?= Url::to(['/product/detail', 'id' => $row['id']]) ?>">
                        <span class="prod_sale"><?= $row['percent_discount'] ?>% <br> OFF</span>
                        <img class="prod_avatar" src="<?= $row['image'] ?>" alt="">
                        <div class="prod_price_star">
                            <p class="prod_title line_2" title="<?= $row['name'] ?>"><?= $row['name'] ?></p>
                            <div class="des_prod mt-2">
                                <span><?= HelperController::formatPrice($row['price']) ?></span>
                            </div>
                        </div>
                    </a>
                </div>
            <?php }} ?>
        </div>
    </section>

    <section class="cat_list_index bg_cat_unset">
        <h2>Khám phá danh mục</h2>
        <div class="cat_list_group">
            <?php 
                if(!empty($listCategory)){
                 foreach($listCategory as $row){
            ?>
                <a href="<?= Url::to(['/category/index', 'cate_parent_id' => $row['id']]) ?>" class="cat_list_item">
                    <div class="flex-center">
                        <img src="<?= $row['image'] ?>" alt="">
                    </div>
                    <p><?= $row['name'] ?></p>
                </a>
            <?php }} ?>
        </div>
    </section>

    <section class="list_product">
        <div class="sort_product">
            <p class="d-none d-lg-block">Tất cả sản phẩm</p>
            <div class="sort_list">
                <button class="btn_sort active">Cần bán</button>
                <button class="btn_sort">Cần mua</button>
            </div>
        </div>
        <div class="product_list">
            <?php for ($i = 0; $i < 20; $i++) { ?>
                <div class="product_item">
                    <a href="<?= Url::to(['/product/detail-delivery']) ?>">
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