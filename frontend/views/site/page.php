<?php

/* @var $this yii\web\View */
/* @var $slug string */
/* @var $title string */
/* @var $content string HTML nhập trong CMS (Cấu hình nội dung) */

use yii\helpers\Html;
use frontend\components\HtmlSafe;
use yii\helpers\Url;
use yii\widgets\Breadcrumbs;
use frontend\components\SiteInfo;
?>
<div class="container">
    <?= Breadcrumbs::widget(['homeLink' => ['label' => '', 'url' => '/'], 'links' => [$title]]) ?>
    <section class="section_text page_content">
        <h1><?= Html::encode($title) ?></h1>
        <?php if ($content !== ''): ?>
            <?= HtmlSafe::clean($content) ?>
        <?php else: ?>
            <p class="color-gray">Nội dung đang được cập nhật. Cần hỗ trợ vui lòng
                <a href="<?= Url::to(['/site/contact']) ?>">liên hệ 1Kho</a><?= SiteInfo::phone() !== '' ? ' hoặc gọi <a href="' . SiteInfo::tel() . '">' . Html::encode(SiteInfo::phone()) . '</a>' : '' ?>.</p>
        <?php endif; ?>
    </section>
</div>
