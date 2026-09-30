<?php

/* @var $this yii\web\View */
/* @var $q string */
/* @var $total int */
/* @var $products array */
/* @var $pageSize int */

use yii\helpers\Html;
use yii\widgets\Breadcrumbs;

$this->title = $q !== '' ? 'Tìm kiếm: ' . $q : 'Tất cả sản phẩm';
?>
<div class="container">
    <?= Breadcrumbs::widget([
        'homeLink' => ['label' => '', 'url' => '/'],
        'links' => [$q !== '' ? 'Tìm kiếm' : 'Sản phẩm'],
    ]) ?>
    <section class="list_product" id="search_result" data-q="<?= Html::encode($q) ?>">
        <div class="product_top_title justify-content-start">
            <h2 class="mr-2">
                <?= $q !== '' ? 'Kết quả cho "' . Html::encode($q) . '"' : 'Tất cả sản phẩm' ?>
            </h2>
            <span class="color-gray"><?= number_format($total, 0, ',', '.') ?> sản phẩm</span>
        </div>
        <?php if ($products): ?>
            <div class="sort_product">
                <p class="d-none d-lg-block">Sắp xếp theo</p>
                <div class="sort_list">
                    <button sort="popular" class="btn_sort_search active">Phổ biến</button>
                    <button sort="best-selling" class="btn_sort_search">Bán chạy</button>
                    <button sort="new" class="btn_sort_search">Hàng mới</button>
                    <button sort="price_asc" class="btn_sort_search">Giá tăng</button>
                    <button sort="price_desc" class="btn_sort_search">Giá giảm</button>
                </div>
            </div>
            <div class="product_list" id="search_product_list">
                <?php foreach ($products as $prod) echo $this->render('_item', ['prod' => $prod]); ?>
            </div>
            <div class="see_more_product" id="search_more" style="<?= $total > $pageSize ? '' : 'display:none' ?>">
                <button class="see_more_btn">Xem thêm</button>
            </div>
        <?php else: ?>
            <div class="search_empty">
                <img src="/images/icon/search.svg" alt="">
                <p><?= $q !== '' ? 'Không tìm thấy sản phẩm phù hợp. Hãy thử từ khoá khác, ví dụ "máy cày" hoặc "động cơ".' : 'Chưa có sản phẩm nào đang bán.' ?></p>
            </div>
        <?php endif; ?>
    </section>
</div>
