<?php

use backend\models\Category;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\grid\GridView;
/* @var $this yii\web\View */
/* @var $searchModel backend\models\CategorySearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Chuyên mục';
$this->params['breadcrumbs'][] = $this->title;
$this->params['breadcrumbs']['description_page'] = 'Quản lý chuyên mục';
$controller = Yii::$app->controller->id;
$parents = Category::getListCategoryParent();
?>

<div class="projects-index">
    <?php
    if (Yii::$app->session->hasFlash('message')) {
        $msg      = Yii::$app->session->getFlash('message');
        echo '<div class="alert alert-success">
                    <i class="fal fa-check"></i> ' . $msg . '
                </div>';
        Yii::$app->session->setFlash('message', null);
    }
    ?>
    <?php echo $this->render('_search', ['model' => $searchModel]); ?>
    <div class="card mb-g">
        <div class="card-body table-responsive">
            <?= GridView::widget([
                'dataProvider' => $dataProvider,
                'layout' => "{summary}\n{items}\n<div class='page-navigation'>{pager}</div>",
                'columns' => array(
                    [
                        'class' => 'yii\grid\SerialColumn',
                        'header' => Yii::t('app', 'No.'),
                        'contentOptions' => array('style' => 'width:70px')
                    ],
                    [
                        'attribute' => 'image',
                        'format' => 'raw',
                        'value' => function ($model) {
                            return $model->image ? '<img class="img-grid" src="' . Html::encode($model->image) . '" onerror="this.style.display=\'none\'"/>' : '';
                        },
                        'contentOptions' => array('style' => 'width:160px')
                    ],
                    [
                        'attribute' => 'name',
                        'format' => 'raw',
                        'value' => function ($model) {
                            return Html::encode($model->name);
                        },
                    ],
                    [
                        'header' => 'Chuyên mục cha',
                        'format' => 'raw',
                        'value' => function ($model) use ($parents) {
                            return $model->parent_id ? Html::encode($parents[$model->parent_id] ?? '#' . $model->parent_id) : '<span style="color:#9ca3af">— Cấp 1 —</span>';
                        },
                    ],
                    [
                        'attribute' => 'show_in_header',
                        'format' => 'raw',
                        'value' => function ($model) {
                            return $model->show_in_header == 1 ? 'Hiển thị' : 'Ẩn';
                        },
                        'contentOptions' => array('style' => 'width:170px')
                    ],
                    [
                        'attribute' => 'show_in_home',
                        'format' => 'raw',
                        'value' => function ($model) {
                            return $model->show_in_home == 1 ? 'Hiển thị' : 'Ẩn';
                        },
                        'contentOptions' => array('style' => 'width:150px')
                    ],
                    [
                        'attribute' => 'sort_order',
                        'format' => 'raw',
                        'value' => function ($model) {
                            return !empty($model->sort_order) ? $model->sort_order : '';
                        },
                        'contentOptions' => array('style' => 'width:170px')
                    ],
                    [
                        'attribute' => 'home_position',
                        'format' => 'raw',
                        'value' => function ($model) {
                            return !empty($model->home_position) ? $model->home_position : '';
                        },
                        'contentOptions' => array('style' => 'width:170px')
                    ],
                    [
                        'header' => 'Action',
                        'class' => 'yii\grid\ActionColumn',
                        'template' => '{update}{delete}',
                        'buttons' => [
                            'update' => function ($url, $model) {
                                $data = $model->getAttributes(['id', 'name', 'image', 'parent_id', 'sort_order', 'home_position', 'show_in_header', 'show_in_home']);
                                return '<button type="button" class="btn btn-primary" style="margin:0 8px 0 0" data-cat-open="' . Html::encode(Json::encode($data)) . '">Cập nhật</button>';
                            },
                            'delete' => function ($url, $model) use ($controller) {
                                return '<a class="btn btn-danger" title="Xóa" onclick="return confirm(\'Bạn có chắc chắn muốn xóa chuyên mục?\')" href="/' . $controller . '/delete?id=' . $model->id . '">Xóa</a>';
                            }
                        ],
                        'contentOptions' => ['style' => 'width:200px;max-width:200px;white-space:nowrap'],
                    ],
                ),
            ]); ?>
        </div>
    </div>
</div>

<div id="am-cat-modal" class="am-modal" style="display:none">
    <div class="am-modal-dialog" style="width:620px">
        <div class="am-modal-head am-dark-gradient">
            <div style="font-weight:800;font-size:14px;color:#fff" id="am-cat-modal-title">🗂 Thêm chuyên mục</div>
            <button type="button" class="am-modal-close" data-cat-close>✕</button>
        </div>
        <form id="am-cat-form" class="am-modal-body" novalidate>
            <div class="am-form-grid">
                <div class="am-field am-field-full" data-f="name">
                    <label>Tên chuyên mục <span class="am-req">*</span></label>
                    <input class="am-input" name="name" maxlength="255" placeholder="VD: Máy cày">
                    <div class="am-field-error"></div>
                </div>
                <div class="am-field am-field-full" data-f="parent_id">
                    <label>Chuyên mục cha</label>
                    <select class="am-input" name="parent_id">
                        <option value="0">— Không có (chuyên mục cấp 1) —</option>
                        <?php foreach ($parents as $id => $name): ?>
                            <option value="<?= (int)$id ?>"><?= Html::encode($name) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <div class="am-field-hint">Chỉ chọn được chuyên mục cấp 1. Chuyên mục đang có con phải giữ cấp 1.</div>
                    <div class="am-field-error"></div>
                </div>
                <div class="am-field" data-f="home_position">
                    <label>Thứ tự hiển thị chuyên mục cha</label>
                    <input class="am-input" name="home_position" type="number" min="0">
                    <div class="am-field-error"></div>
                </div>
                <div class="am-field" data-f="sort_order">
                    <label>Thứ tự hiển thị chuyên mục con</label>
                    <input class="am-input" name="sort_order" type="number" min="0">
                    <div class="am-field-error"></div>
                </div>
                <div class="am-field" data-f="show_in_header">
                    <label class="am-cat-check"><input type="checkbox" name="show_in_header" value="1"> Hiển thị menu header</label>
                    <div class="am-field-error"></div>
                </div>
                <div class="am-field" data-f="show_in_home">
                    <label class="am-cat-check"><input type="checkbox" name="show_in_home" value="1"> Hiển thị trang chủ</label>
                    <div class="am-field-error"></div>
                </div>
                <div class="am-field am-field-full" data-f="image">
                    <label>Ảnh</label>
                    <input type="hidden" name="image">
                    <div class="am-cat-img">
                        <img id="am-cat-img-preview" alt="" style="display:none">
                        <div style="display:flex;flex-direction:column;gap:6px">
                            <label class="am-btn am-btn-sm" style="margin:0;display:inline-block;cursor:pointer">📷 Chọn ảnh
                                <input type="file" id="am-cat-img-file" accept="image/jpeg,image/png,image/webp" style="display:none">
                            </label>
                            <button type="button" class="am-btn am-btn-sm am-btn-ghost" id="am-cat-img-remove" style="display:none">Xóa ảnh</button>
                            <div class="am-field-hint">JPG, PNG hoặc WEBP, tối đa 5MB</div>
                        </div>
                    </div>
                    <div class="am-field-error"></div>
                </div>
            </div>
        </form>
        <div class="am-modal-foot">
            <button type="button" class="am-btn am-btn-ghost" data-cat-close>Hủy</button>
            <button type="submit" form="am-cat-form" class="am-btn" id="am-cat-submit">💾 Lưu chuyên mục</button>
        </div>
    </div>
</div>

<?php
$saveUrl = Json::htmlEncode(Url::to(['/' . $controller . '/save']));
$uploadUrl = Json::htmlEncode(Url::to(['/agrimac/upload-image']));
$this->registerJs(<<<JS
(function ($) {
    var \$modal = $('#am-cat-modal').appendTo('body'), \$form = $('#am-cat-form'), editId = null, uploading = false;

    function setImage(url) {
        \$form.find('[name=image]').val(url || '');
        $('#am-cat-img-preview').attr('src', url || '').toggle(!!url);
        $('#am-cat-img-remove').toggle(!!url);
    }
    function clearErrors() {
        \$form.find('.am-field').removeClass('has-error').find('.am-field-error').text('');
    }
    function open(data) {
        data = data || {};
        editId = data.id || null;
        clearErrors();
        \$form[0].reset();
        \$form.find('[name=name]').val(data.name || '');
        var parent = \$form.find('[name=parent_id]');
        parent.find('option').prop('disabled', false);
        if (editId) parent.find('option[value="' + editId + '"]').prop('disabled', true);
        parent.val(String(data.parent_id || 0));
        \$form.find('[name=sort_order]').val(data.sort_order == null ? '' : data.sort_order);
        \$form.find('[name=home_position]').val(data.home_position == null ? '' : data.home_position);
        \$form.find('[name=show_in_header]').prop('checked', data.show_in_header == 1);
        \$form.find('[name=show_in_home]').prop('checked', data.show_in_home == 1);
        setImage(data.image);
        $('#am-cat-modal-title').text(editId ? '✏️ Sửa chuyên mục: ' + data.name : '🗂 Thêm chuyên mục');
        $('#am-cat-submit').text(editId ? '💾 Cập nhật' : '💾 Thêm chuyên mục');
        \$modal.css('display', 'flex');
        setTimeout(function () { \$form.find('[name=name]').trigger('focus'); }, 50);
    }
    function close() { \$modal.hide(); }

    $(document).on('click', '[data-cat-open]', function () {
        var raw = $(this).attr('data-cat-open');
        open(raw ? JSON.parse(raw) : null);
    });
    \$modal.on('click', '[data-cat-close]', close);
    \$modal.on('mousedown', function (e) { if (e.target === this) close(); });
    $(document).on('keydown', function (e) { if (e.key === 'Escape' && \$modal.is(':visible')) close(); });

    $('#am-cat-img-file').on('change', function () {
        var file = this.files[0];
        this.value = '';
        if (!file) return;
        var fd = new FormData();
        fd.append('file', file);
        uploading = true;
        $('#am-cat-submit').prop('disabled', true);
        \$form.find('[data-f=image] .am-field-error').text('Đang tải ảnh...');
        $.ajax({ url: {$uploadUrl}, type: 'POST', data: fd, processData: false, contentType: false, dataType: 'json' })
            .done(function (res) {
                if (res && res.status) { setImage(res.url); \$form.find('[data-f=image] .am-field-error').text(''); }
                else \$form.find('[data-f=image] .am-field-error').text((res && res.message) || 'Upload lỗi');
            })
            .fail(function (x) { \$form.find('[data-f=image] .am-field-error').text('Upload lỗi (HTTP ' + x.status + ')'); })
            .always(function () { uploading = false; $('#am-cat-submit').prop('disabled', false); });
    });
    $('#am-cat-img-remove').on('click', function () { setImage(''); });

    \$form.on('submit', function (e) {
        e.preventDefault();
        if (uploading) return;
        clearErrors();
        var data = {
            name: $.trim(\$form.find('[name=name]').val()),
            parent_id: \$form.find('[name=parent_id]').val(),
            sort_order: \$form.find('[name=sort_order]').val(),
            home_position: \$form.find('[name=home_position]').val(),
            show_in_header: \$form.find('[name=show_in_header]').is(':checked') ? 1 : 0,
            show_in_home: \$form.find('[name=show_in_home]').is(':checked') ? 1 : 0,
            image: \$form.find('[name=image]').val()
        };
        if (!data.name) {
            \$form.find('[data-f=name]').addClass('has-error').find('.am-field-error').text('Nhập tên chuyên mục');
            return;
        }
        var \$btn = $('#am-cat-submit').prop('disabled', true);
        $.ajax({ url: {$saveUrl} + (editId ? '?id=' + encodeURIComponent(editId) : ''), type: 'POST', data: data, dataType: 'json' })
            .done(function (res) {
                if (res && res.status) { window.location.reload(); return; }
                $.each((res && res.errors) || {}, function (field, msg) {
                    \$form.find('[data-f="' + field + '"]').addClass('has-error').find('.am-field-error').text(msg);
                });
                if (!res || !res.errors) alert((res && res.message) || 'Không lưu được chuyên mục');
                \$btn.prop('disabled', false);
            })
            .fail(function (x) { alert('Lỗi máy chủ (HTTP ' + x.status + ')'); \$btn.prop('disabled', false); });
    });
})(jQuery);
JS
);
