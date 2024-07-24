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
            $category['info']['name'],
        ],
    ]);

    ?>
    <section class="list_product">
        <div class="product_top_title justify-content-start">
            <h2 class="mr-2"><?= $category['info']['name'] ?? '' ?></h2>
            <!-- <span class="color-gray">0 sản phẩm</span> -->
        </div>
        <div class="list_product_cat">
            <?php 
                if(!empty($category['cate_child'])){
                    foreach($category['cate_child'] as $row){
            ?>
                <a class="tab_cat_child" cat-id="<?= $row['id'] ?>" href="javascript:;">
                    <img src="<?= $row['image'] ?>" alt="<?= $row['name'] ?>">
                    <p class="text-center"><?= $row['name'] ?></p>
                </a>
            <?php }} ?>
        </div>
        <div class="sort_product">
            <p class="d-none d-lg-block">Sắp xếp theo</p>
            <div class="sort_list">
                <button sort="popular" class="btn_sort active">Phổ biến</button>
                <button sort="best-selling" class="btn_sort">Bán chạy</button>
                <button sort="new" class="btn_sort">Hàng mới</button>
                <button sort="price_desc" class="btn_sort sort_product_wap d-block d-lg-none">Giá <img src="/images/icon/icon-sort.svg" alt=""></button>
                <button sort="price_asc" class="btn_sort d-none d-lg-block">Giá tăng</button>
                <button sort="price_desc" class="btn_sort d-none d-lg-block">Giá giảm</button>
            </div>
        </div>
        <div class="product_list">
            <?php 
                if(!empty($category['product'])){
                    foreach($category['product'] as $prod){
            ?>
                <div class="product_item">
                        <a href="<?= Url::to(['/product/detail', 'id' => $prod['id']]) ?>">
                            <span class="prod_sale"><?= $prod['percent_discount'] ?>% <br> OFF</span>
                            <img class="prod_avatar" src="<?= $prod['image'] ?>" alt="">
                            <div class="prod_price_star">
                                <p class="prod_title line_2" title="<?= $prod['name'] ?>"><?= $prod['name'] ?></p>
                                <div class="des_prod mt-2">
                                    <span><?= HelperController::formatPrice($prod['price']) ?></span>
                                    <div class="flex-center">
                                        <img src="/images/icon/star.svg" alt="Star">
                                        <p class="product_star"><?= $prod['star'] ?> (<?= $prod['total_rate'] ?>)</p>
                                    </div>
                                </div>
                            </div>
                        </a>
                </div>
            <?php }} ?>
        </div>
        <?php if(count($category['product']) >= 10) { ?>
            <div class="see_more_product">
                <button cate-parent-id="<?= $_GET['cate_parent_id'] ?>" class="see_more_btn see_more_product_cat">Xem thêm</button>
            </div>
        <?php } ?>
    </section>
</div>