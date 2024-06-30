<?php

use yii\helpers\Url;
use yii\web\View;
use backend\models\Config;
use backend\controllers\CommonController;
use frontend\controllers\HelperController;

?>

<div class="container">
    <section class="banner">
        <div class="banner_index">
            <?php 
                if(!empty($dataHome['banner'])){
                    foreach($dataHome['banner'] as $row){
            ?>
            <div class="banner_item">
                <a href="<?= Url::to(['category/index', 'cate_parent_id' => $row['category_id']]) ?>">
                    <img src="<?= $row['image'] ?>" alt="banner home">
                </a>
            </div>
            <?php }} ?>
        </div>
    </section>
    <section class="cat_list_index">
        <div class="cat_list_index_title d-flex d-lg-none">
            <p>Danh mục sản phẩm</p>
            <a href="">Tất cả <i class="fal fa-long-arrow-right"></i></a>
        </div>
        <div class="cat_list_group">
            <?php 
                if(!empty($dataHome['category'])){
                 foreach($dataHome['category'] as $row){
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

    <section class="product_top">
        <div class="product_top_title">
            <h2>Tin Rao vặt</h2>
            <a href="">Tất cả <i class="far fa-angle-right"></i></a>
        </div>
        <div class="product_top_tab">
            <p class="tab_advertis active" dt-tab="news">Tin mới</p>
            <p>•</p>
            <p class="tab_advertis" dt-tab="buy">Tin đăng mua</p>
            <p>•</p>
            <p class="tab_advertis" dt-tab="sell">Bán nhanh có trả phí</p>
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
        <?php
            if(!empty($dataHome['advertisement'])){
                foreach($dataHome['advertisement'] as $tab => $rows){
        ?> 
            <div id="<?= $tab ?>" class="tab_adver_content <?= $tab == 'news' ? '' : 'hide' ?>">
                <div class="product_list product_slide slick_global">
                    <?php
                        if(!empty($rows)){
                            foreach($rows as $row){
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
            </div>
        <?php }} ?>
    </section>

    <section class="sale_index">
        <h2>Săn sale cùng 1KHO</h2>
        <div class="sale_list slide_sale slick_global">
            <?php 
                if(!empty($dataHome['productSale'])){
                    foreach($dataHome['productSale'] as $row){
            ?>
                <div class="sale_list_item">
                    <a href="<?= Url::to(['/product/detail', 'id' => $row['id']]) ?>">
                        <span class="num_sale">-<?= $row['percent_discount'] ?>%</span>
                        <div class="flex-center flex-column">
                            <img class="sale_prod_avatar" src="<?= $row['image'] ?>" alt="Product sale">
                            <p class="title_prod line_2" title="<?= $row['name'] ?>"><?= $row['name'] ?></p>
                        </div>
                        <div class="sale_text">
                            <div>
                                <p><?= HelperController::formatPrice($row['price']) ?></p>
                                <span><?= HelperController::formatPrice($row['price_old']) ?></span>
                            </div>
                            <p>Kết thúc sau <strong><?= $row['date_sale_remain'] ?> ngày</strong></p>
                            <p>Chỉ còn <strong><?= $row['quantity_in_stock'] ?> sản phẩm</strong></p>
                        </div>
                    </a>
                </div>
            <?php }} ?>
        </div>
        <div class="sale_see_more flex-center">
            <a target="_blank" href="<?= Url::to(['/category/index-sale']) ?>">Xem tất cả</a>
        </div>
    </section>

    <?php 
        if(!empty($dataHome['categoryProduct'])){
            foreach($dataHome['categoryProduct'] as $row){
    ?>
        <section class="list_product">
            <div class="product_top_title">
                <h2><?= $row['name'] ?></h2>
                <a href="<?= Url::to(['category/index', 'cate_parent_id' => $row['id']]) ?>">Tất cả <i class="far fa-angle-right"></i></a>
            </div>
            <div class="list_product_cat">
                <?php 
                    if($row['cate_child']){ 
                        $i = 0;
                        foreach($row['cate_child'] as $catChild){
                            $i++;
                            if($i <= 6){
                ?>
                    <a class="category_child" cat-id="<?= $catChild['id'] ?>" href="javascript:;">
                        <img src="<?= $catChild['image'] ?>" alt="category child">
                        <p class="line_2 text-center"><?= $catChild['name'] ?></p>
                    </a>
                <?php }}} ?>
            </div>
            <div class="product_list product_slide slick_global">
                <?php 
                    if(!empty($row['product'])){
                        foreach($row['product'] as $prod){
                ?> 
                    <div class="product_item">
                            <a href="<?= Url::to(['/product/detail', 'id' => $prod['id']]) ?>">
                                <span class="prod_sale"><?= $prod['percent_discount'] ?> % <br> OFF</span>
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
        </section>
    <?php }} ?>
</div>