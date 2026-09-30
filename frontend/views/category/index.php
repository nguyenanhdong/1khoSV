<?php

use yii\helpers\Url;
use yii\helpers\Html;
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
            <h2 class="mr-2"><?= Html::encode($category['info']['name'] ?? '') ?></h2>
            <span class="color-gray" id="category_total"><?= number_format($total, 0, ',', '.') ?> sản phẩm</span>
        </div>
        <div class="list_product_cat">
            <?php 
                if(!empty($category['cate_child'])){
                    foreach($category['cate_child'] as $row){
            ?>
                <a class="tab_cat_child <?= (int)$row['id'] === $activeChild ? 'active' : '' ?>" cat-id="<?= $row['id'] ?>" href="javascript:;">
                    <img src="<?= Html::encode($row['image']) ?>" alt="<?= Html::encode($row['name']) ?>" onerror="this.style.visibility='hidden'">
                    <p class="text-center"><?= Html::encode($row['name']) ?></p>
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
            <?php foreach ($category['product'] as $prod) echo $this->render('@frontend/views/product/_item', ['prod' => $prod]); ?>
            <?php if (empty($category['product'])): ?><div class="search_empty w-100">Chưa có sản phẩm trong chuyên mục này</div><?php endif; ?>
        </div>
            <div class="see_more_product" style="<?= $total > count($category['product']) ? '' : 'display:none' ?>">
                <button cate-parent-id="<?= (int)$category['info']['id'] ?>" <?= $activeChild ? 'cate-child-id="' . $activeChild . '"' : '' ?> class="see_more_btn see_more_product_cat">Xem thêm</button>
            </div>
        
    </section>
</div>