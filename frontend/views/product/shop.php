<?php

use yii\helpers\Url;
use yii\web\View;
use backend\models\Config;
use frontend\controllers\HelperController;
use yii\widgets\Breadcrumbs;

?>
<div class="container">
    <section class="shop">
        <img src="<?= $data['agentInfo']['cover'] ?>" alt="" class="banner_shop w-100">
        <div class="shop_info">
            <div class="shop_info_group">
                <div class="info_desc flex-item-center">
                    <img src="<?= $data['agentInfo']['avatar'] ?>" alt="">
                    <div class="text_rating">
                        <a href="javascript:;"><?= $data['agentInfo']['name'] ?></a>
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
                            <span><?= $data['agentInfo']['total_follow'] ?> Người theo dõi</span>
                        </div>
                    </div>
                </div>
                <button class="btn_follow btn_action">Theo dõi</button>
            </div>
            <div class="search_shop">
                <div class="form-group position-relative">
                    <div class="icon_search_shop flex-center">
                        <img class="" src="/images/icon/k.svg" alt="">
                    </div>
                    <input type="text" placeholder="Tìm trong shop này">
                </div>
            </div>
        </div>
    </section>

    <?php if (!empty($data['productSale'])) { ?>
        <section class="sale_index">
            <h2>Săn sale cùng 1KHO</h2>
            <div class="sale_list slide_sale slick_global">
                <?php
                foreach ($data['productSale'] as $row) {
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
                <?php } ?>
            </div>
            <div class="sale_see_more flex-center">
                <a target="_blank" href="<?= Url::to(['/category/index-sale']) ?>">Xem tất cả</a>
            </div>
        </section>
    <?php } ?>

    <?php if (!empty($data['category'])) { ?>
        <section class="cat_list_index">
            <div class="cat_list_index_title d-flex d-lg-none">
                <p>Danh mục sản phẩm</p>
                <a href="">Tất cả <i class="fal fa-long-arrow-right"></i></a>
            </div>
            <div class="cat_list_group">
                <?php foreach ($data['category'] as $cat) { ?>
                    <a href="<?= Url::to(['/category/index', 'cate_parent_id' => $cat['id']]) ?>" class="cat_list_item">
                        <div class="flex-center">
                            <img src="<?= $cat['image'] ?>" alt="">
                        </div>
                        <p><?= $cat['name'] ?></p>
                    </a>
                <?php } ?>
            </div>
        </section>
    <?php } ?>

    <section class="list_product">
        <div class="sort_product">
            <p class="d-none d-lg-block">Sắp xếp theo</p>
            <div class="sort_list">
                <button sort="popular" class="btn_sort_shop active">Phổ biến</button>
                <button sort="best-selling" class="btn_sort_shop">Bán chạy</button>
                <button sort="new" class="btn_sort_shop">Hàng mới</button>
                <button sort="price_desc" class="btn_sort_shop sort_product_wap d-block d-lg-none">Giá <img src="/images/icon/icon-sort.svg" alt=""></button>
                <button sort="price_asc" class="btn_sort_shop d-none d-lg-block">Giá tăng</button>
                <button sort="price_desc" class="btn_sort_shop d-none d-lg-block">Giá giảm</button>
            </div>
        </div>
        <div class="product_list">
            <?php
            if (!empty($data['productTab'])) {
                foreach ($data['productTab'] as $prod) {
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
            <?php } 
            } ?>
        </div>
        <?php if (count($data['productTab']) >= 10) { ?>
            <div class="see_more_product">
                <button shop-id="<?= $_GET['id'] ?>" class="see_more_shop">Xem thêm</button>
            </div>
        <?php } ?>
    </section>
</div>