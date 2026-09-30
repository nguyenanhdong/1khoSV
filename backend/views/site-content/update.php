<?php

/* @var $this yii\web\View */
/* @var $key string */
/* @var $label string */
/* @var $kind string html|contact|bank|lines */
/* @var $current mixed */
/* @var $errors array */

use yii\helpers\Html;
use yii\helpers\Url;
use backend\controllers\SiteContentController as C;

$this->title = $label;
$this->params['breadcrumbs'][] = ['label' => 'Nội dung & liên hệ', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
$err = function ($f) use ($errors) {
    return isset($errors[$f]) ? '<div class="help-block">' . Html::encode($errors[$f]) . '</div>' : '';
};
?>
<div class="card mb-g">
    <div class="card-body">
        <?= Html::beginForm(['update', 'key' => $key], 'post', ['id' => 'site-content-form']) ?>
        <?php if ($kind === 'html'): ?>
            <div class="form-group <?= isset($errors['html']) ? 'has-error' : '' ?>">
                <label class="control-label">Nội dung</label>
                <textarea name="v[html]" id="content-editor" class="form-control" rows="20"><?= Html::encode($current) ?></textarea>
                <?= $err('html') ?>
                <small class="text-muted">Để trống thì trang sẽ báo "đang cập nhật" và link ở footer web tự ẩn.</small>
            </div>
        <?php elseif ($kind === 'lines'): ?>
            <div class="form-group <?= isset($errors['lines']) ? 'has-error' : '' ?>">
                <label class="control-label">Mỗi dòng một lý do</label>
                <textarea name="v[lines]" class="form-control" rows="8"><?= Html::encode(implode("\n", array_values((array)$current))) ?></textarea>
                <?= $err('lines') ?>
            </div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($kind === 'contact' ? C::CONTACT_FIELDS : C::BANK_FIELDS as $f => $l): ?>
                    <div class="col-lg-6 form-group <?= isset($errors[$f]) ? 'has-error' : '' ?>">
                        <label class="control-label"><?= $l ?></label>
                        <input type="text" name="v[<?= $f ?>]" class="form-control" maxlength="300" value="<?= Html::encode($current[$f] ?? '') ?>">
                        <?= $err($f) ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <div class="form-group text-center mt-3">
            <button type="submit" class="btn btn-primary">Lưu</button>
            <a class="btn btn-default" href="<?= Url::to(['index']) ?>">Huỷ</a>
        </div>
        <?= Html::endForm() ?>
    </div>
</div>
<?php if ($kind === 'html'): ?>
<?php
$uploadUrl = json_encode(Url::to(['/agrimac/upload-image']));
$this->registerJs(<<<JS
tinymce.init({
    selector: '#content-editor',
    plugins: 'autolink lists link image table code paste',
    toolbar: 'undo redo | formatselect | bold italic underline | alignleft aligncenter alignright | bullist numlist | link image table | code',
    menubar: false, height: 560, entity_encoding: 'raw', convert_urls: false,
    images_upload_handler: function (blobInfo, success, failure) {
        var fd = new FormData();
        fd.append('file', blobInfo.blob(), blobInfo.filename());
        $.ajax({ url: {$uploadUrl}, type: 'POST', data: fd, processData: false, contentType: false, dataType: 'json' })
            .done(function (res) { res && res.status ? success(res.url) : failure((res && res.message) || 'Upload lỗi'); })
            .fail(function (x) { failure('Upload lỗi (HTTP ' + x.status + ')'); });
    }
});
JS
);
?>
<?php endif; ?>
