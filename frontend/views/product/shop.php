<?php

use yii\helpers\Url;
use yii\web\View;
use backend\models\Config;
use frontend\controllers\HelperController;
use yii\widgets\Breadcrumbs;
use yii\helpers\Html;

/* @var $shopId int */
/* @var $following bool */
/* @var $rating array ['star', 'total'] */
/* @var $total int */
/* @var $pageSize int */
$agent = $data['agentInfo'];
?>
<div class="container">
    <section class="shop">
        <img src="<?= Html::encode($agent['cover']) ?>" alt="" class="banner_shop w-100" onerror="this.style.display='none'">
        <div class="shop_info">
            <div class="shop_info_group">
                <div class="info_desc flex-item-center">
                    <img src="<?= Html::encode($agent['avatar']) ?>" alt="" onerror="this.onerror=null;this.src='/images/icon/shop.png'">
                    <div class="text_rating">
                        <h1 class="shop_name"><?= Html::encode($agent['name']) ?></h1>
                        <div class="rating_box flex-item-center">
                            <?php if ($rating['total'] > 0): ?>
                            <div>
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <img src="/images/icon/<?= $i <= round($rating['star']) ? 'star' : 'star-inactive' ?>.svg" alt="">
                                <?php endfor; ?>
                            </div>
                            <span><?= number_format($rating['star'], 1) ?>/5.0 (<?= number_format($rating['total'], 0, ',', '.') ?>)</span>
                            <span>•</span>
                            <?php endif; ?>
                            <span><span class="follow_count"><?= (int)$agent['total_follow'] ?></span> Người theo dõi</span>
                        </div>
                    </div>
                </div>
                <button type="button" class="btn_follow btn_action <?= $following ? 'following' : '' ?>" data-agent="<?= $shopId ?>"><?= $following ? 'Đang theo dõi' : 'Theo dõi' ?></button>
            </div>
            <div class="search_shop">
                <div class="form-group position-relative">
                    <div class="icon_search_shop flex-center">
                        <img class="" src="/images/icon/k.svg" alt="">
                    </div>
                    <input type="search" id="shop_search" placeholder="Tìm trong shop này" maxlength="100" autocomplete="off">
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
                <a href="javascript:;" class="shop_cat_filter" data-cat="0">Tất cả <i class="fal fa-long-arrow-right"></i></a>
            </div>
            <div class="cat_list_group">
                <?php foreach ($data['category'] as $cat) { ?>
                    <a href="javascript:;" class="cat_list_item shop_cat_filter" data-cat="<?= (int)$cat['id'] ?>">
                        <div class="flex-center">
                            <img src="<?= Html::encode($cat['image']) ?>" alt="">
                        </div>
                        <p><?= Html::encode($cat['name']) ?></p>
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
        <div class="product_top_title justify-content-start"><span class="color-gray" id="shop_total"><?= number_format($total, 0, ',', '.') ?> sản phẩm</span></div>
        <div class="product_list" id="shop_products">
            <?php foreach ($data['productTab'] ?? [] as $prod) echo $this->render('_item', ['prod' => $prod]); ?>
            <?php if (empty($data['productTab'])): ?><div class="search_empty w-100">Shop chưa có sản phẩm</div><?php endif; ?>
        </div>
        <div class="see_more_product" style="<?= $total > count($data['productTab'] ?? []) ? '' : 'display:none' ?>">
            <button shop-id="<?= $shopId ?>" class="see_more_btn see_more_shop">Xem thêm</button>
        </div>
    </section>
</div>