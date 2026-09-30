<?php

/* @var $this yii\web\View */
/* @var $contact array thông tin CONTACT trong config */
/* @var $seller bool mở từ "Bán hàng cùng sàn" */

use yii\helpers\Html;
use yii\widgets\Breadcrumbs;
use frontend\components\SiteInfo;

$rows = array_filter([
    ['map', 'Địa chỉ', Html::encode($contact['address'])],
    ['phone', 'Hotline', $contact['phone'] !== '' ? '<a href="' . SiteInfo::tel() . '">' . Html::encode($contact['phone']) . '</a>' : ''],
    ['email', 'Email', $contact['email'] !== '' ? Html::mailto(Html::encode($contact['email']), $contact['email']) : ''],
    ['fb', 'Facebook', Html::encode($contact['facebook'])],
    ['zalo', 'Zalo', Html::encode($contact['zalo'])],
    ['cart', 'Shopee', Html::encode($contact['shopee'])],
], function ($r) { return $r[2] !== ''; });
?>
<div class="container">
    <?= Breadcrumbs::widget(['homeLink' => ['label' => '', 'url' => '/'], 'links' => [$seller ? 'Bán hàng cùng 1Kho' : 'Liên hệ']]) ?>
    <section class="section_text contact_page">
        <h1><?= Html::encode($contact['name']) ?></h1>
        <?php if ($seller): ?>
            <p><strong>Bán hàng cùng 1Kho:</strong> để mở gian hàng trên sàn, vui lòng liên hệ bộ phận phát triển đối tác qua hotline hoặc email bên dưới. Chuẩn bị giúp chúng tôi tên cửa hàng, ngành hàng, địa chỉ kho và giấy phép kinh doanh (nếu có).</p>
        <?php else: ?>
            <p class="color-gray">Liên hệ với 1Kho để được tư vấn sản phẩm, hỗ trợ đơn hàng hoặc hợp tác bán hàng cùng sàn.</p>
        <?php endif; ?>
        <ul class="contact_list">
            <?php foreach ($rows as [$icon, $label, $value]): ?>
                <li><img src="/images/icon/<?= $icon ?>.svg" alt=""><span class="contact_label"><?= $label ?></span><span><?= $value ?></span></li>
            <?php endforeach; ?>
        </ul>
    </section>
</div>
