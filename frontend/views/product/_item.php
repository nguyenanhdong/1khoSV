<?php

/* @var $prod array sản phẩm từ Product::getItemApp() */

use yii\helpers\Html;
use yii\helpers\Url;
use frontend\controllers\HelperController;
?>
<div class="product_item">
    <a href="<?= Url::to(['/product/detail', 'id' => $prod['id']]) ?>">
        <?php if ($prod['percent_discount'] > 0): ?>
            <span class="prod_sale"><?= (int)$prod['percent_discount'] ?>% <br> OFF</span>
        <?php endif; ?>
        <img class="prod_avatar" src="<?= Html::encode($prod['image']) ?>" alt="<?= Html::encode($prod['name']) ?>" loading="lazy">
        <div class="prod_price_star">
            <p class="prod_title line_2" title="<?= Html::encode($prod['name']) ?>"><?= Html::encode($prod['name']) ?></p>
            <div class="des_prod mt-2">
                <span><?= HelperController::formatPrice($prod['price']) ?></span>
                <div class="flex-center">
                    <img src="/images/icon/star.svg" alt="Star">
                    <p class="product_star"><?= Html::encode($prod['star']) ?> (<?= (int)$prod['total_rate'] ?>)</p>
                </div>
            </div>
        </div>
    </a>
</div>
