<?php

use backend\models\Category;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model backend\models\CategoryTags */
/* @var $form yii\widgets\ActiveForm */

$categoryParent = Category::getListCategoryParent();
if(!$model->isNewRecord){
    unset($categoryParent[$_GET['id']]);
}
?>

<div class="category-tags-form">
    <?php $form = ActiveForm::begin(); ?>
    <div class="row">
        <div class="col-lg-8">
            <div class="input-group-control">
                <?= $form->field($model, 'title')->textInput(['maxlength' => true]) ?>
                <?= $form->field($model, 'description')->textarea(['maxlength' => true]) ?>
                <?= $form->field($model, 'content')->textarea(['maxlength' => true, 'id' => 'editor', 'class' => 'form-control editor', 'style' => 'height: 300px;']) ?>
            </div>
        </div>
        <div class="col-lg-4">
            <?= $form->field($model, 'user_notify')->dropDownList(Yii::$app->params['user_notify'],['prompt' => Yii::t('app', 'Chọn đối tượng'), 'class' => 'form-control select2 select2-hidden']) ?>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-12">
            <div class="form-group text-center" style="margin-top:10px">
                <?= Html::submitButton($model->isNewRecord ? '<i class="fal fa-plus"></i> Thêm' : '<i class="fal fa-edit"></i> Cập nhật', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
                <a class="btn btn-info btn_cancel" href="index">Hủy</a>
            </div>
        </div>
    </div>
    <?php ActiveForm::end(); ?>
</div>
<link rel="stylesheet" href="/css/dropzone.css" />
<link href="/css/cropper.css" rel="stylesheet" />
<script src="/js/dropzone.js"></script>
<script src="/js/cropper.js"></script>
<script src="/assets/global/plugins/ckeditor/ckeditor.js"></script>
<script>
    jQuery(document).ready(function() {
        if ($('#editor').length > 0) {
            CKEDITOR.timestamp = '<?= strtotime('2022-07-14') ?>';
            CKEDITOR.config.removePlugins = 'flash';
            CKEDITOR.config.removeButtons = 'Flash';
            CKEDITOR.config.language = 'en';
            var editor = CKEDITOR.replace('editor', {
                extraPlugins: "image2,wordcount,notification",
                removePlugins: 'image,scayt,wsc,language',
                image2_alignClasses: ['align-left', 'align-center', 'align-right'],
                // filebrowserBrowseUrl: '/assets/global/plugins/ckeditor/ckfinder/ckfinder.html',
                // filebrowserUploadUrl: '/assets/global/plugins/ckeditor/ckfinder/core/connector/php/connector.php?command=QuickUpload&currentFolder=<?= date('Y/m/d') ?>&type=Hình ảnh',
                filebrowserBrowseUrl: '/assets/global/plugins/ckeditor/ckfinder/ckfinder.html?type=Video',
                filebrowserImageBrowseUrl: '/assets/global/plugins/ckeditor/ckfinder/ckfinder.html?type=Images',
                filebrowserUploadUrl: '/assets/global/plugins/ckeditor/ckfinder/core/connector/php/connector.php?command=QuickUpload&currentFolder=<?= date('Y/m/d') ?>&type=Video',
                filebrowserImageUploadUrl: '/assets/global/plugins/ckeditor/ckfinder/core/connector/php/connector.php?command=QuickUpload&currentFolder=<?= date('Y/m/d') ?>&type=Images',
                filebrowserWindowWidth: '1000',
                filebrowserWindowHeight: '700',
                height: '300px',
                allowedContent: {
                    script: true,
                    div: true,
                    $1: {
                        // This will set the default set of elements
                        elements: CKEDITOR.dtd,
                        attributes: true,
                        styles: true,
                        classes: true
                    }
                },
                wordcount: {
                    showWordCount: true,
                    showCharCount: true,
                    filter: new CKEDITOR.htmlParser.filter({
                        elements: {
                            div: function(element) {
                                if (element.attributes.class == 'mediaembed') {
                                    return false;
                                }
                            }
                        }
                    })
                },
                on: {
                    dialogShow: function(evt) {
                        var dialog = evt.data;
                        if (dialog._.name === 'image2' && !dialog._.model.data.src) {
                            evt.data.getContentElement('info', 'hasCaption').setValue(true);
                        } else if (dialog._.name === 'html5video' && !dialog._.model.ready) {
                            evt.data.getContentElement('info', 'responsive').setValue(true);
                            evt.data.getContentElement('info', 'controls').setValue(true);
                        }
                    }
                }
            });
            editor.on('fileUploadRequest', function(evt) {
                var fileLoader = evt.data.fileLoader,
                    xhr = fileLoader.xhr;
                if ($('.box-loading-upload').length <= 0)
                    $('body').append('<div class="box-loading-upload"> <img src="/img/loading-upload.svg" /> Đang tải file lên (<span class="percent_complete">0</span>%) </div>');

                $('.box-loading-upload').show();
                xhr.upload.onprogress = function(e) {
                    if (e.lengthComputable) {
                        var percentComplete = parseInt((e.loaded / e.total) * 100);
                        $('.box-loading-upload .percent_complete').text(percentComplete);
                        if (percentComplete == 100)
                            $('.box-loading-upload').hide();
                    }
                };
            });

            editor.on('fileUploadResponse', function(evt) {
                $('.box-loading-upload').hide();
            });
            editor.on('change', function() {
                $('#is_edit_post').val(1);
            });
            $(window).resize(function() {
                if (screen.width === window.innerWidth) {
                    if (document.exitFullscreen) {
                        document.exitFullscreen();
                    } else if (document.webkitExitFullscreen) {
                        /* Safari */
                        document.webkitExitFullscreen();
                    } else if (document.msExitFullscreen) {
                        /* IE11 */
                        document.msExitFullscreen();
                    }
                }
            })
        }
        if ($('#editor_des').length > 0) {
            CKEDITOR.timestamp = '<?= strtotime('2022-07-14') ?>';
            CKEDITOR.config.removePlugins = 'flash';
            CKEDITOR.config.removeButtons = 'Flash';
            CKEDITOR.config.language = 'en';
            var editor = CKEDITOR.replace('editor_des', {
                extraPlugins: "image2,wordcount,notification",
                removePlugins: 'image,scayt,wsc,language',
                image2_alignClasses: ['align-left', 'align-center', 'align-right'],
                // filebrowserBrowseUrl: '/assets/global/plugins/ckeditor/ckfinder/ckfinder.html',
                // filebrowserUploadUrl: '/assets/global/plugins/ckeditor/ckfinder/core/connector/php/connector.php?command=QuickUpload&currentFolder=<?= date('Y/m/d') ?>&type=Hình ảnh',
                filebrowserBrowseUrl: '/assets/global/plugins/ckeditor/ckfinder/ckfinder.html?type=Video',
                filebrowserImageBrowseUrl: '/assets/global/plugins/ckeditor/ckfinder/ckfinder.html?type=Images',
                filebrowserUploadUrl: '/assets/global/plugins/ckeditor/ckfinder/core/connector/php/connector.php?command=QuickUpload&currentFolder=<?= date('Y/m/d') ?>&type=Video',
                filebrowserImageUploadUrl: '/assets/global/plugins/ckeditor/ckfinder/core/connector/php/connector.php?command=QuickUpload&currentFolder=<?= date('Y/m/d') ?>&type=Images',
                filebrowserWindowWidth: '1000',
                filebrowserWindowHeight: '700',
                height: '500px',
                allowedContent: {
                    script: true,
                    div: true,
                    $1: {
                        // This will set the default set of elements
                        elements: CKEDITOR.dtd,
                        attributes: true,
                        styles: true,
                        classes: true
                    }
                },
                wordcount: {
                    showWordCount: true,
                    showCharCount: true,
                    filter: new CKEDITOR.htmlParser.filter({
                        elements: {
                            div: function(element) {
                                if (element.attributes.class == 'mediaembed') {
                                    return false;
                                }
                            }
                        }
                    })
                },
                on: {
                    dialogShow: function(evt) {
                        var dialog = evt.data;
                        if (dialog._.name === 'image2' && !dialog._.model.data.src) {
                            evt.data.getContentElement('info', 'hasCaption').setValue(true);
                        } else if (dialog._.name === 'html5video' && !dialog._.model.ready) {
                            evt.data.getContentElement('info', 'responsive').setValue(true);
                            evt.data.getContentElement('info', 'controls').setValue(true);
                        }
                    }
                }
            });
            editor.on('fileUploadRequest', function(evt) {
                var fileLoader = evt.data.fileLoader,
                    xhr = fileLoader.xhr;
                if ($('.box-loading-upload').length <= 0)
                    $('body').append('<div class="box-loading-upload"> <img src="/img/loading-upload.svg" /> Đang tải file lên (<span class="percent_complete">0</span>%) </div>');

                $('.box-loading-upload').show();
                xhr.upload.onprogress = function(e) {
                    if (e.lengthComputable) {
                        var percentComplete = parseInt((e.loaded / e.total) * 100);
                        $('.box-loading-upload .percent_complete').text(percentComplete);
                        if (percentComplete == 100)
                            $('.box-loading-upload').hide();
                    }
                };
            });

            editor.on('fileUploadResponse', function(evt) {
                $('.box-loading-upload').hide();
            });
            editor.on('change', function() {
                $('#is_edit_post').val(1);
            });
            $(window).resize(function() {
                if (screen.width === window.innerWidth) {
                    if (document.exitFullscreen) {
                        document.exitFullscreen();
                    } else if (document.webkitExitFullscreen) {
                        /* Safari */
                        document.webkitExitFullscreen();
                    } else if (document.msExitFullscreen) {
                        /* IE11 */
                        document.msExitFullscreen();
                    }
                }
            })
        }

        $('#form_post .btn-success:not([type=submit]),#form_post .btn-primary:not([type=submit])').click(function() {
            toastr.remove();
            if ($('#post_avatar').val() == "")
                toastr['error']('Vui lòng tải ảnh bài viết');
            else {
                $('#form_post').submit();
            }
        });
        var $modal = $('#modal_crop');

        var image = document.getElementById('image_crop');

        var cropper;

        $modal.on('shown.bs.modal', function() {
            cropper = new Cropper(image, {
                // aspectRatio: 1,
                zoomable: false,
                zoomOnWheel: false,
                viewMode: 3,
                autoCropArea: 1,
                preview: '.preview_crop'
            });
            $('[data-toggle="tooltip"]').tooltip();
        }).on('hidden.bs.modal', function() {
            cropper.destroy();
            cropper = null;
        });

        $('.btn-crop').click(function() {
            if (processUpload)
                return;
            var _this = $(this);
            canvas = cropper.getCroppedCanvas({
                minWidth: 256,
                minHeight: 256,
                maxWidth: 4096,
                maxHeight: 4096,
                fillColor: '#fff',
                imageSmoothingEnabled: true,
                imageSmoothingQuality: 'high'
            });
            _this.find('.loading-crop').removeClass('hide');
            canvas.toBlob(function(blob) {
                url = URL.createObjectURL(blob);
                var reader = new FileReader();
                reader.readAsDataURL(blob);
                reader.onloadend = function() {
                    var base64data = reader.result;
                    uploadImage(base64data, $modal, _this);

                };
            });
        });
    });
</script>
<style>
    .control-label {
        width: 100%
    }

    .img-preview {
        /* width: 100%; */
        max-height: 100px;
        /* object-fit: cover; */
        margin: 20px 0 0;
    }
</style>
