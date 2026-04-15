<?php

use backend\models\Category;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\datetime\DateTimePicker;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model backend\models\CategoryTags */
/* @var $form yii\widgets\ActiveForm */

$optionStatus = '';
foreach (Yii::$app->params['status_order'] as $key => $value) {
    $selected = '';
    if ($model->status == $key) $selected = 'selected';
    $optionStatus .= '<option ' . $selected . ' value="' . $key . '">' . $value . '</option>';
}
?>

<div class="category-tags-form">
    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            [
                'label' => 'Tổng giá gốc của đơn hàng',
                'value' => !empty($model->price) ? $model->price : '-'
            ],
            [
                'label' => 'Số tiền được giảm khi dùng voucher',
                'value' => !empty($model->price_voucher) ? $model->price_voucher : '-'
            ],
            [
                'label' => 'Số điểm trong ví dùng để thanh toán',
                'value' => !empty($model->price_wallet) ? $model->price_wallet : '-'
            ],
            [
                'label' => 'Phí ship',
                'value' => !empty($model->fee_ship) ? $model->fee_ship : '-'
            ],
            [
                'label' => 'Tổng tiên đơn hàng',
                'value' => !empty($model->total_price) ? $model->total_price : '-'
            ],
            [
                'label' => 'Voucher được áp dụng',
                'value' => !empty($model->voucher_id) ? $model->voucher_id : '-'
            ],
            [
                'label' => 'Voucher được áp dụng',
                'value' => !empty($model->voucher_point_refundable) ? $model->voucher_point_refundable : '-'
            ],
            [
                'label' => 'Địa chỉ giao hàng',
                'value' => !empty($model->delivery_address_id) ? $model->delivery_address_id : '-'
            ],
            [
                'label' => 'Phương thức thanh toán',
                'value' => !empty(Yii::$app->params['type_payment'][$model->type_payment]) ? Yii::$app->params['type_payment'][$model->type_payment] : '-'
            ],
            [
                'label' => 'Trạng thái',
                'value' => !empty(Yii::$app->params['status_order'][$model->status]) ? Yii::$app->params['status_order'][$model->status] : '-'
            ],
            [
                'label' => 'Đại lý',
                'value' => !empty($model->agent_id) ?  $model->agent_id : '-'
            ],
            [
                'label' => 'Thời gian thanh toán',
                'value' => !empty($model->time_payment) ? date('d-m-Y H:i:s', strtotime($model->time_payment)) : '-'
            ],
            [
                'label' => 'Lý do hủy đơn',
                'value' => !empty($model->reason_cancel) ? $model->reason_cancel : '-'
            ],
            [
                'label' => 'Thời gian hủy đơn',
                'value' => !empty($model->time_cancel) ? date('d-m-Y H:i:s', strtotime($model->time_cancel)) : '-'
            ],
            [
                'label' => 'Trạng thái',
                'format' => 'raw',
                'value' => '
                    <div class="w-40">
                        <div class="form-group">
                        <select class="form-control select2" id="status" name="status">
                            <option value="">Chọn trạng thái</option>
                            ' . $optionStatus . '
                        </select>
                        <span class="help-block error-message none" id="status_error_message">Vui lòng chọn trạng thái</span>
                        </div>
                    </div>
                '
            ],
            [
                'label' => 'Ghi chú',
                'format' => 'raw',
                'value' => '
                    <div class="w-40">
                        <div class="form-group">
                            <input type="text" class="form-control" id="reason" name="reason" value="' . (!empty($model->reason_cancel) ? $model->reason_cancel : '') . '">
                            <span class="help-block error-message none" id="reason_error_message">Vui lòng nhập lý do</span>
                        </div>
                    </div>
                '
            ],
            // [
            //         'label' => Yii::t('app', 'Reason'),
            //         'format' => 'raw',
            //         'contentOptions' => ['id' => 'reason_box_reject'],
            //         'value' => '
            //             <div id="reason_box_reject" style="margin-top: 10px;">
            //                 <div>
            //                 '.
            //                 Html::textInput('reject_reason', $model->reject_reason, [
            //                     'class' => 'form-control',
            //                     'id' => 'dealer-order-cancel-reason',
            //                     'maxlength' => 255,
            //                     'disabled' => $isDisabled,
            //                 ])
            //                 .'
            //                     <span id="reason-error" class="help-block error-message none">' . Yii::t('app', 'Please enter a reason') . '</span>
            //                 </div>
            //             </div>
            //         '
            //     ],                    
            [
                'label' => Yii::t('app', 'Date create'),
                'value' => date('d-m-Y H:i:s', strtotime($model->create_at))
            ],
            // [
            //     'label' => Yii::t('app', 'Date update'),
            //     'value' => date('d-m-Y H:i:s', strtotime($model->updated_at))
            // ],
        ],
    ]) ?>

    <div class="text-center">
        <a href="index" class="btn btn-primary"><i style="margin-right:5px" class="fal fa-long-arrow-left"></i><?= Yii::t('app', 'Back') ?></a>
        <?= Html::submitButton('<i class="fal fa-edit"></i> ' . Yii::t('app', 'Update') . '', ['class' => 'btn btn-success', 'id' => 'save_update_status_order', 'disabled' => false]) ?>
    </div>
</div>
<input type="hidden" id="order_id" value="<?= $_GET['id'] ?>">
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
    .none{display: none;}
    th {
        width: 300px;
    }

    tr td {
        white-space: normal !important;
    }

    .img-thumbnail {
        width: 200px;
        margin-right: 10px;
    }
    .disable-button__save{
        pointer-events: none;
        opacity: 0.7;
    }

    .change_status_sim {
        gap: 10px;
        display: grid;
        /*grid-template-columns: 200px 200px 200px 100px 200px 100px;*/
        align-items: center;
    }

    .change_status_sim p,
    .change_status_sim label {
        margin: 0;
    }

    .error-input {
        border: 1px solid red !important;
    }

    .error-message {
        color: red;
        font-size: 12px;
        margin-top: 5px;
        display: block;
    }

    .none {
        display: none;
    }

    .change_approve_status_sim .select2 {
        width: 200px!important;
    }
    .change_status_sim_text {
        display: grid;
        grid-template-columns: 200px 200px 200px;
        gap: 10px;
    }
    .change_status_sim_action {
        display: grid;
        grid-template-columns: 200px 200px 200px;
        gap: 10px;
    }
    .change_status_sim_action button {
        width: fit-content;
    }

    .w-40 {
        width: 40% !important;
    }

    #save_update_sim_card_btn:disabled {
        cursor: not-allowed;
    }
</style>
