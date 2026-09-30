<?php

/* Khung AgriMac cho các trang quản trị sàn 1kho (banner, voucher, đơn hàng sàn...).
 * Giữ nguyên CSS/JS gốc (Bootstrap/SmartAdmin, select2, datepicker, TinyMCE) để chức năng không đổi;
 * agrimac-legacy.css đổi giao diện các thành phần sang phong cách AgriMac. */

/* @var $this \yii\web\View */
/* @var $content string */

use yii\helpers\Html;
use backend\assets\AppAsset;
use backend\components\AgrimacAuth;
use backend\components\AgrimacData as D;

AppAsset::register($this);
$this->registerCssFile('@web/agrimac/css/agrimac.css', ['depends' => AppAsset::class]);
$this->registerCssFile('@web/agrimac/css/agrimac-legacy.css', ['depends' => AppAsset::class]);

$role = AgrimacAuth::role() ?: 'admin';
$controller = Yii::$app->controller->id;
$menu = D::LEGACY_MENU[$controller] ?? ['icon' => '⚙️', 'label' => $this->title ?: 'Quản trị'];
$roleInfo = D::ROLES[$role];
$breadcrumbs = $this->params['breadcrumbs'] ?? [];
$subtitle = $breadcrumbs['description_page'] ?? null;
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title><?= Html::encode(($this->title ?: $menu['label']) . ' — 1Kho CMS') ?></title>
    <link rel="stylesheet" media="screen, print" href="/css/vendors.bundle.css">
    <link rel="stylesheet" media="screen, print" href="/css/app.bundle.css">
    <link rel="stylesheet" href="/assets/global/plugins/bootstrap-daterangepicker/daterangepicker-bs3.css">
    <link rel="stylesheet" href="/assets/global/plugins/select2/select2.css">
    <link rel="stylesheet" href="/assets/global/css/plugins-md.css">
    <link rel="stylesheet" href="/css/fa-regular.css">
    <link rel="stylesheet" href="/css/fa-solid.css">
    <?php $this->head() ?>
</head>
<body class="am-body am-legacy-body">
<?php $this->beginBody() ?>
<div class="am-app">
    <?= $this->render('agrimac/_sidebar', ['role' => $role, 'page' => null, 'legacy' => $controller, 'canSwitch' => AgrimacAuth::isSuperAdmin()]) ?>

    <div class="am-main">
        <header class="am-topbar">
            <div>
                <div class="am-topbar-title"><?= $menu['icon'] . ' ' . Html::encode($this->title ?: $menu['label']) ?></div>
            </div>
            <div class="am-topbar-right">
                <?php if ($subtitle): ?><span class="am-pill" style="background:#f1f5f9;color:#475569"><?= Html::encode($subtitle) ?></span><?php endif; ?>
                <span class="am-pill" style="background:#e0f2fe;color:#0369a1">Sàn 1kho</span>
                <span class="am-pill" style="background:<?= $roleInfo['color'] ?>22;color:<?= $roleInfo['color'] ?>"><?= $roleInfo['icon'] . ' ' . Html::encode($roleInfo['label']) ?></span>
            </div>
        </header>
        <main id="js-page-content" class="am-content am-legacy">
            <?= $this->render('partial/bread_crumb', ['breadcrumbs' => $breadcrumbs]) ?>
            <?php foreach (['success' => 'alert-success', 'error' => 'alert-danger'] as $key => $class): ?>
                <?php if (Yii::$app->session->hasFlash($key)): ?>
                    <div class="alert <?= $class ?>"><?= Yii::$app->session->getFlash($key) ?></div>
                    <?php Yii::$app->session->setFlash($key, null); ?>
                <?php endif; ?>
            <?php endforeach; ?>
            <?= $content ?>
        </main>
    </div>
</div>

<?php /* app.bundle.js của SmartAdmin đọc bảng màu theme từ phần tử này khi khởi động */ ?>
<p id="js-color-profile" class="d-none">
    <?php foreach (['primary', 'success', 'info', 'warning', 'danger', 'fusion'] as $group): ?>
        <?php foreach ([50, 100, 200, 300, 400, 500, 600, 700, 800, 900] as $shade): ?><span class="color-<?= $group ?>-<?= $shade ?>"></span><?php endforeach; ?>
    <?php endforeach; ?>
</p>
<script src="/js/vendors.bundle.js"></script>
<script src="/js/app.bundle.js"></script>
<script src="/assets/global/plugins/select2/select2.min.js"></script>
<script src="/js/formplugins/bootstrap-datepicker/bootstrap-datepicker.js"></script>
<script src="https://cdn.tiny.cloud/1/j8mi27n4mciy3k3mjx8jsc0rmc0yjb68sv7hvxuxejxnxfz3/tinymce/5/tinymce.min.js" referrerpolicy="origin"></script>
<script>
    function addCommas(str) {
        return str.toString().replace(/,/g, "").replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    }
    jQuery(function ($) {
        $('#am-role-select').on('change', function () {
            window.location.href = $(this).data('url') + '?role=' + encodeURIComponent(this.value);
        });
        if ($('.editor').length) {
            tinymce.init({
                selector: '.editor',
                plugins: 'preview paste importcss searchreplace autolink autosave directionality code visualblocks visualchars fullscreen image link media codesample table charmap hr pagebreak nonbreaking anchor insertdatetime advlist lists wordcount imagetools textpattern noneditable help quickbars emoticons',
                menubar: 'file edit view insert format tools table help',
                toolbar: 'undo redo | bold italic underline | fontselect fontsizeselect formatselect | image | alignleft aligncenter alignright alignjustify | outdent indent | numlist bullist | forecolor backcolor | fullscreen preview | link media codesample',
                toolbar_sticky: true,
                image_advtab: true,
                image_caption: true,
                toolbar_mode: 'sliding',
                height: 510,
                entity_encoding: 'raw',
                images_upload_handler: function (blobInfo, success, failure) {
                    var formData = new FormData();
                    formData.append('file', blobInfo.blob(), blobInfo.filename());
                    formData.append('folder', 'images/news');
                    $.ajax({ url: '/common/upload-file', type: 'POST', data: formData, processData: false, contentType: false })
                        .done(function (res) {
                            var json = typeof res === 'string' ? JSON.parse(res) : res;
                            json && typeof json.url === 'string' ? success(json.url) : failure('Upload lỗi');
                        })
                        .fail(function (x) { failure('HTTP Error: ' + x.status); });
                }
            });
        }
        if ($('.input_date').length) {
            $('.input_date').datepicker({ todayBtn: 'linked', clearBtn: true, todayHighlight: true });
        }
    });
</script>
<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
