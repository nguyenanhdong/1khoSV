<?php

/* @var $this yii\web\View */
/* @var $rows backend\models\Config[] theo key */

use yii\helpers\Html;
use yii\helpers\Url;
use backend\controllers\SiteContentController as C;

$this->title = 'Nội dung & liên hệ';
$this->params['breadcrumbs'][] = $this->title;
$this->params['breadcrumbs']['description_page'] = 'Hiển thị trên web và app 1Kho';
$groups = ['Thông tin chung' => ['contact', 'bank', 'lines'], 'Trang nội dung (footer web)' => ['html']];
?>
<?php foreach ($groups as $title => $kinds): ?>
<div class="card mb-g">
    <div class="card-header"><h2 class="card-title"><?= $title ?></h2></div>
    <div class="card-body table-responsive">
        <table class="table">
            <thead><tr><th>Mục</th><th>Tình trạng</th><th style="width:140px"></th></tr></thead>
            <tbody>
            <?php foreach (C::ITEMS as $key => [$label, $kind]): if (!in_array($kind, $kinds, true)) continue; ?>
                <?php $value = isset($rows[$key]) ? trim((string)$rows[$key]->value) : ''; ?>
                <tr>
                    <td><strong><?= Html::encode($label) ?></strong><br><small class="text-muted"><?= $key ?></small></td>
                    <td>
                        <?php if ($value === '' || $value === '[]' || $value === '{}'): ?>
                            <span class="badge badge-warning">Chưa có nội dung<?= $kind === 'html' ? ' · đang ẩn khỏi footer' : '' ?></span>
                        <?php elseif ($kind === 'html'): ?>
                            <span class="badge badge-success">Đang hiển thị</span>
                            <small class="text-muted"><?= Html::encode(mb_substr(trim(preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags($value), ENT_QUOTES, 'UTF-8'))), 0, 110)) ?>…</small>
                        <?php else: ?>
                            <?php $data = json_decode($value, true) ?: []; ?>
                            <small><?= Html::encode(implode(' · ', array_filter(array_map('strval', array_values($data))))) ?></small>
                        <?php endif; ?>
                    </td>
                    <td class="text-right">
                        <?php if ($kind === 'html' && $value !== '' && !empty(Yii::$app->params['frontendUrl'])): ?>
                            <a class="btn btn-sm btn-default" target="_blank" rel="noopener" href="<?= Html::encode(rtrim(Yii::$app->params['frontendUrl'], '/') . '/' . C::PAGE_SLUGS[$key]) ?>">Xem</a>
                        <?php endif; ?>
                        <a class="btn btn-sm btn-primary" href="<?= Url::to(['update', 'key' => $key]) ?>">Sửa</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endforeach; ?>
