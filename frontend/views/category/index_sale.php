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
            // ['label' => '   Sample Post', 'url' => ['post/edit', 'id' => 1]],
            'Săn sale cùng 1KHO',
        ],
    ]);

    ?>

    <section class="sale_index bg_sale d-inline-block">
        <h2>Săn sale cùng 1KHO</h2>
        <div class="sale_list sale_list_grid">
            <?php
            if (!empty($productSale)) {
                foreach ($productSale as $row) {
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
            <?php }
            } ?>
        </div>
        <?php if (!empty($checkLoadMore)) { ?>
            <div class="sale_see_more flex-center">
                <button class="load_more_product_sale" type="button">Xem thêm</button>
            </div>
        <?php } ?>
    </section>
    <div class="noti_prod flex-center mb-5 hide">
        <p>Bạn đã xem hết sản phẩm</p>
    </div>

</div>