<?php

use yii\helpers\Url;
use yii\web\View;
use backend\models\Config;
use frontend\controllers\HelperController;
use yii\widgets\Breadcrumbs;
use yii\helpers\Html;
use frontend\components\SiteInfo;

/* @var $categories array [['name', 'url'], ...] chuyên mục cha → con của sản phẩm */
$info = $product['product_info'];
$agent = $product['agent_info'];
$shareUrl = Url::to(['/product/detail', 'id' => $info['id']], true);
$crumbs = [];
if ($agent) {
    $crumbs[] = ['label' => $agent['name'], 'url' => ['/product/shop', 'id' => $agent['id']]];
} elseif ($categories) {
    $crumbs[] = ['label' => $categories[0]['name'], 'url' => $categories[0]['url']];
}
$crumbs[] = $info['name'];
?>
<div class="container">
    <?php
    echo Breadcrumbs::widget([
        'homeLink' => ['label' => '', 'url' => '/'],
        'links' => $crumbs,
    ]);

    ?>
    <section class="product_info">
        <div class="product_info_group">
            <div class="product_list_img">
                <div class="card-wrapper">
                    <div class="card">
                        <div class="product-imgs">
                            <div class="img-display">
                                <div class="img-showcase">
                                    <?php
                                        if(!empty($product['product_info']['images'])){
                                            foreach($product['product_info']['images'] as $img){
                                    ?>
                                    <img src="<?= Html::encode($img) ?>" alt="<?= Html::encode($info['name']) ?>">
                                    <?php }} ?>
                                </div>
                            </div>
                            <div class="img-select">
                                    <?php
                                    $i = 0;
                                    if(!empty($product['product_info']['images'])){
                                        foreach($product['product_info']['images'] as $img){
                                            $i++;
                                ?>
                                    <div class="img-item">
                                        <a href="javascript:;" data-id="<?= $i ?>">
                                            <img src="<?= Html::encode($img) ?>" alt="">
                                        </a>
                                    </div>
                                <?php }} ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="product_info_right">
                <div class="product_description">
                    <div class="d-flex justify-content-between align-items-start" style="gap:12px">
                        <h1><?= Html::encode($info['name']) ?></h1>
                        <button type="button" class="btn_favourite <?= !empty($info['is_favourites']) ? 'active' : '' ?>" data-product="<?= (int)$info['id'] ?>"
                                title="<?= !empty($info['is_favourites']) ? 'Bỏ yêu thích' : 'Thêm vào yêu thích' ?>" aria-pressed="<?= !empty($info['is_favourites']) ? 'true' : 'false' ?>">
                            <img src="/images/icon/<?= !empty($info['is_favourites']) ? 'heart-active' : 'heart-inactive' ?>.svg" alt="Yêu thích">
                        </button>
                    </div>
                    <div class="product_rating flex-item-center">
                        <div class="rating_list flex-item-center">
                            <?php 
                                if($product['product_info']['star'] > 0) 
                                    for($i = 0; $i < $product['product_info']['star'];$i++){
                            ?>
                            <img src="/images/icon/star-product.svg" alt="star">
                            <?php } ?>
                        </div>
                        <div class="rating_num flex-center">
                            <p><?= $product['product_info']['star'] ?> (<?= $product['product_info']['total_rating'] ?>)</p>
                            <p>•</p>
                            <p><?= $product['product_info']['quantity_sold'] ?> Đã bán</p>
                        </div>
                    </div>
                    <div class="product_price flex-item-center">
                        <span class="price_product"><?= HelperController::formatPrice($info['price']) ?></span>
                        <?php if ($info['percent_discount'] > 0): ?><p>-<?= (int)$info['percent_discount'] ?>%</p><?php endif; ?>
                    </div>
                </div>
                <?php if(!empty($product['product_info']['classification_group'])){ ?>
                    <div class="product_classification">
                        <?php foreach($product['product_info']['classification_group'] as $row){ ?>
                            <div class="product_choose">
                                <span><?= $row['name'] ?></span>
                                <div class="list_code_product">
                                    <?php foreach($row['childs'] as $child){ ?>
                                        <button dt-class="option_<?= $row['id'] ?>" dt-id="<?= $child['id'] ?>" class="option_product option_<?= $row['id'] ?>"><?= $child['name'] ?></button>
                                    <?php } ?>
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                <?php } ?>
                <div class="product_add_cart">
                    <div class="buy_now">
                        <div class="choose_number flex-center">
                            <button class="update_qty" dt-type="decrease">-</button>
                            <input type="text" class="quantity_product" value="1">
                            <button class="update_qty" dt-type="increase">+</button>
                        </div>
                        <button id="buy_now" dt-type="buynow" class="btn_action flex-center btn_buy_now"><img src="/images/icon/cart-icon.svg" alt="">Mua ngay</button>
                    </div>
                    <div class="add_cart">
                        <button id="add_cart" dt-type="add-cart" class="btn_action bg_blue flex-center"><img src="/images/icon/cart.svg" alt="">Thêm vào giỏ</button>
                        <a class="btn_action bg_blue flex-center" href="<?= SiteInfo::phone() !== '' ? SiteInfo::tel() : Url::to(['/site/contact']) ?>" title="<?= SiteInfo::phone() !== '' ? 'Gọi ' . Html::encode(SiteInfo::phone()) . ' để được tư vấn' : 'Liên hệ tư vấn' ?>">Tư vấn</a>
                    </div>
                </div>
                <?php if ($categories): ?>
                <div class="category_tag_product">
                    <div class="group_cat_tag">
                        <span>Chuyên mục: </span>
                        <div>
                            <?php foreach ($categories as $cat): ?>
                                <a href="<?= Url::to($cat['url']) ?>"><?= Html::encode($cat['name']) ?></a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <div class="product_share">
            <span>Chia sẻ</span>
            <div>
                <a target="_blank" rel="noopener" title="Chia sẻ Facebook" href="https://www.facebook.com/sharer/sharer.php?u=<?= rawurlencode($shareUrl) ?>"><img src="/images/icon/fb.svg" alt="Facebook"></a>
                <a target="_blank" rel="noopener" title="Chia sẻ Zalo" href="https://sp.zalo.me/share_inline?url=<?= rawurlencode($shareUrl) ?>"><img src="/images/icon/zalo.svg" alt="Zalo"></a>
                <a href="javascript:;" class="js_share" title="Chia sẻ / sao chép liên kết" data-url="<?= Html::encode($shareUrl) ?>" data-title="<?= Html::encode($info['name']) ?>"><img src="/images/icon/social.svg" alt="Chia sẻ"></a>
            </div>
        </div>
    </section>


    <section class="product_description">
        <h2>Mô tả sản phẩm</h2>
        <div class="product_desc_content">
            <?= $product['product_info']['description'] ?>
        </div>
    </section>
    <?= $this->render('/product/template-comment', ['product' => $product]) ?>
    <?php if(!empty($product['product_suggest'])){ ?>
        <section class="product_relate">
            <h2>Sản phẩm gợi ý</h2>
            <div class="product_list">
                <?php foreach ($product['product_suggest'] as $row) { if ((int)$row['id'] !== (int)$info['id']) echo $this->render('_item', ['prod' => $row]); } ?>
            </div>
        </section>
    <?php } ?>
</div>
<input type="hidden" id="total_classification" value="<?= !empty($product['product_info']['classification_group']) ? count($product['product_info']['classification_group']) : 0 ?>"> 
<input type="hidden" id="product_id" value="<?= $product['product_info']['id'] ?>">  
<input type="hidden" id="classification_id" value="">  