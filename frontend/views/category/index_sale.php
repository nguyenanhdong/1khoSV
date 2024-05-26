<?php

use yii\helpers\Url;
use yii\web\View;
use backend\models\Config;
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

<section class="sale_index">
    <h2>Săn sale cùng 1KHO</h2>
    <div class="sale_list sale_list_grid">
        <?php for($i = 0; $i < 12; $i++) { ?>
            <div class="sale_list_item">
                <a href="<?= Url::to(['/product/detail']) ?>">
                    <span class="num_sale">-36%</span>
                    <div class="flex-center flex-column">
                        <img class="sale_prod_avatar" src="/images/page/may-cay.png" alt="">
                        <p class="title_prod">Máy cày Kubota sử dụng công nghệ mới</p>
                    </div>
                    <div class="sale_text">
                        <div>
                            <p>200.000</p>
                            <span>699.000</span>
                        </div>
                        <p>Kết thúc sau <strong>4 ngày</strong></p>
                        <p>Chỉ còn <strong>15 sản phẩm</strong></p>
                    </div>
                </a>
            </div>
        <?php } ?>
    </div>
</section>
    
</div>