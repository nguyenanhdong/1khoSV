<?php

use yii\helpers\Url;
use yii\web\View;
use backend\models\Config;
use frontend\controllers\HelperController;
use yii\bootstrap\ActiveForm;
use yii\widgets\Breadcrumbs;
use yii\helpers\Html;

?>
<div class="container">
    <?php
    echo Breadcrumbs::widget([
        'homeLink' => ['label' => '', 'url' => '/'],
        'links' => [
            'Giỏ hàng',
        ],
    ]);

    ?>
    <?php if(!empty($data)){ ?>
        <section class="cart">
            <div class="cart_group_product">
                <div class="th_cart">
                    <input type="checkbox" id="check_all_product">
                    <label for="check_all_product" class="d-block d-lg-none m-0">Chọn tất cả sản phẩm</label>
                    <p class="d-none d-lg-block">Sản phẩm</p>
                    <p class="d-none d-lg-block">Đơn giá</p>
                    <p class="d-none d-lg-block">Số lượng</p>
                    <p class="d-none d-lg-block">Thành tiền</p>
                </div>
                <div class="cart_list_product">
                    <?php 
                        $total = 0;
                            foreach($data as $row){
                            $total += $row['price']; 
                    ?>
                        <div class="cart_item_product">
                            <div class="choose_product">
                                <input type="checkbox" class="input_choose_product" value="<?= $row['product_id'] ?>">
                                <div class="img_product flex-center">
                                    <img src="<?= $row['images'] ?>" alt="">
                                </div>
                            </div>
                            <div class="desc_product">
                                <div class="d-flex flex-column">
                                    <a href=""><?= $row['name'] ?></a>
                                    <span class="discount">-<?= $row['percent_discount'] ?>%</span>
                                </div>
                                <div>
                                    <p><?= $row['price_format'] ?></p>
                                </div>
                                <div class="number_product">
                                    <button dt-type="decrease" prod-id="<?= $row['product_id'] ?>" class="update_qty_product_cart btn-smale flex-center"><img src="/images/icon/arrow-down.svg" alt=""></button>
                                    <input type="text" class="quantity_product" value="<?= $row['qty'] ?>">
                                    <button dt-type="increase" prod-id="<?= $row['product_id'] ?>" class="update_qty_product_cart btn-smale flex-center"><img src="/images/icon/arrow-up.svg" alt=""></button>
                                </div>
                                <span class="price_cart"><?= HelperController::formatPrice($row['price'] * $row['qty']) ?></span>
                                <button prod-id="<?= $row['product_id'] ?>" class="btn-smale flex-center remove_product_cart"><img src="/images/icon/delete.svg" alt=""></button>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </section>

        <section class="payment">
            <div class="payment_left">
                <div class="payment_type d-flex flex-column">
                    <div class="type_text d-flex flex-column">
                        <div class="title_type flex-item-center justify-content-between">
                            <p>Địa chỉ giao hàng</p>
                            <a class="d-flex" href="javascript:;" class="btn btn-primary" data-toggle="modal" data-target="#modalAddress">Thay đổi <img src="/images/icon/ar-right.svg" alt=""></a>
                        </div>
                        <div class="type_text_item">
                            <div class="flex-center">
                                <img src="/images/icon/map.svg" alt="">
                            </div>
                            <?php if(!empty($deliveryAddress->province)){ ?>
                                <div>
                                    <p><?= $deliveryAddress->address . ',' . $deliveryAddress->district . ', ' .$deliveryAddress->province  ?></p>
                                    <span><?= $deliveryAddress->province ?></span>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                    <div class="type_text">
                        <div class="title_type flex-item-center justify-content-between">
                            <p>Phương thức thanh toán</p>
                            <a href="javascript:;" data-toggle="modal" data-target="#modalTypePayment">Thay đổi <img src="/images/icon/ar-right.svg" alt=""></a>
                        </div>
                        <div class="type_text_item">
                            <div class="flex-center">
                                <img src="/images/icon/bank.svg" alt="">
                            </div>
                            <div>
                                <p>MBBank</p>
                                <span>**** 5647</span>
                            </div>
                        </div>
                    </div>
                    <div class="type_text">
                        <div class="title_type flex-item-center justify-content-between">
                            <p>Chọn voucher</p>
                            <a href="javascript:;" data-toggle="modal" data-target="#modalVoucherPayment">Chọn <img src="/images/icon/ar-right.svg" alt=""></a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="payment_right">
                <h2>Thanh toán</h2>
                <div class="info_payment d-flex flex-column">
                    <div>
                        <p>Giá</p>
                        <span class="price_order">0</span>
                    </div>
                    <div>
                        <p>Phí ship</p>
                        <span class="fee_ship">0</span>
                    </div>
                    <div>
                        <p><img src="/images/icon/vi.svg" alt=""> Sử dụng ví: <span class="wallet_point"><?= $user->wallet_point ?></span></p>
                        <div class="center">
                            <input id="payment_point" class="on_off" type="checkbox" value="1" />
                        </div>
                    </div>
                    <div>
                        <p>Tổng</p>
                        <span class="total_price_order">0</span>
                    </div>
                    <button id="submit_order" class="btn_action btn-orange btn_order flex-center">ĐẶT HÀNG</button>
                </div>
            </div>
        </section>
    <?php }else{ ?>
        <div class="text_empty_cart flex-center">
            <p>Chưa có sản phẩm nào trong giỏ hàng!</p>
        </div>
    <?php } ?>
</div>


<!-- Modal address -->
<div class="modal fade" id="modalAddress" tabindex="-1" role="dialog" aria-labelledby="modalAddressTitle" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <h2>Địa chỉ giao hàng</h2>
                <?php 
                    $form = ActiveForm::begin([
                        'id' => 'ajax-form-delivery',
                        'options' => ['class' => 'form-horizontal'],
                        'enableAjaxValidation' => true,
                    ]); 
                ?>
                    <?= $form->field($deliveryAddress, 'fullname')->textInput(['maxlength' => true]) ?>
                    <div class="grid_50 g-15">
                        <?= $form->field($deliveryAddress, 'province')->dropDownList($province, [
                            'prompt' => 'Chọn tỉnh thành', 
                            'options' => [
                                $deliveryAddress->province => ['Selected' => true]
                            ]
                        ]) ?>
                        <?= $form->field($deliveryAddress, 'district')->dropDownList($district, [
                            'prompt' => 'Chọn quận huyện', 
                            'options' => [
                                $deliveryAddress->province => ['Selected' => true]
                            ]
                        ]) ?>
                    </div>
                    <?= $form->field($deliveryAddress, 'phone')->textInput(['maxlength' => true]) ?>
                    <?= $form->field($deliveryAddress, 'address')->textInput(['maxlength' => true]) ?>
                    <div class="form-group mt-4">
                        <?= Html::submitButton('Lưu', ['class' => 'btn_action btn-orange flex-center', 'id' => 'ajax-submit-delivery']) ?>
                    </div>
                <?php ActiveForm::end(); ?>
            </div>
        </div>
    </div>
</div>

<!-- Modal type payment -->
<div class="modal fade" id="modalTypePayment" tabindex="-1" role="dialog" aria-labelledby="modalTypePaymentTitle" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <h2>Thanh toán</h2>
                <div class="type_payment">
                    <input class="option-input radio type_payment" type="radio" name="type-payment" value="1">
                    <img src="/images/icon/bank.svg" alt="">
                    <p>Chuyển khoản qua ngân hàng</p>
                </div>
                <div class="type_payment">
                    <input class="option-input radio type_payment" type="radio" name="type-payment" value="2">
                    <img src="/images/icon/car-ship.svg" alt="">
                    <p>Thanh toán khi nhận hàng (COD)</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal voucher payment -->
<div class="modal fade" id="modalVoucherPayment" tabindex="-1" role="dialog" aria-labelledby="modalVoucherPaymentTitle" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="voucher_list_cart" id="unused">
                    <?php
                        if(!empty($dataVoucher)){
                            foreach($dataVoucher as $row){
                    ?>
                        <div class="voucher_item voucher_item_cart">
                            <div class="voucher_avatar flex-center">
                                <img src="<?= $row['image'] ?>" alt="">
                            </div>
                            <div class="voucher_desc">
                                <span><?= $row['name'] ?></span>
                                <p><?= $row['desc'] ?></p>
                            </div>
                            <div class="use_voucher flex-center">
                                <input class="option-input radio input_voucher" type="radio" name="vouche-payment" value="<?= $row['id'] ?>">
                            </div>
                        </div>
                    <?php }} ?>
                </div>
            </div>
        </div>
    </div>
</div>
<input type="hidden" id="idDeliveryAddress" value="<?= isset($deliveryAddress->id) ? $deliveryAddress->id : 0 ?>">