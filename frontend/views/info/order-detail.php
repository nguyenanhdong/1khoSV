<?php

use yii\helpers\Url;
use yii\web\View;
use backend\models\Config;
use yii\widgets\Breadcrumbs;
use backend\controllers\CommonController;
use frontend\controllers\HelperController;
use yii\helpers\Html;
use backend\models\Order;
use backend\models\OrderRefund;

/* @var $order Order */
/* @var $refund OrderRefund|null */
/* @var $reasons string[] lý do trả hàng (config LIST_REASON_REFUN) */
$ship = $data['shipping_info'] ?: [];
$refundStatus = [OrderRefund::STATUS_PENDING => ['Đang chờ xử lý', '#f59e0b'], OrderRefund::STATUS_APPROVE => ['Đã đồng ý trả hàng/hoàn tiền', '#10b981'], OrderRefund::STATUS_REJECT => ['Bị từ chối', '#ef4444']];
$canReview = (int)$order->status === Order::STATUS_PURCHASED && !(int)$order->is_review;
?>
<div class="container">
    <?php
    echo Breadcrumbs::widget([
        'homeLink' => ['label' => '', 'url' => '/'],
        'links' => [
            ['label' => 'Lịch sử mua hàng', 'url' => ['/info/purchase-history']],
            'Đơn hàng #' . (int)$data['id'],
        ],
    ]);

    ?>

    <section class="voucher">
        <?= $this->render('/layouts/sidebar_info') ?>
        <div class="order_detail_right">
            <div class="status_order">
                <h2><?= Html::encode($data['status_name']) ?> <small class="color-gray">· Đơn #<?= (int)$data['id'] ?></small></h2>
                <?php if ((int)$order->status === Order::STATUS_CANCEL && $order->reason_cancel): ?>
                    <p class="color-gray">Lý do huỷ: <?= Html::encode($order->reason_cancel) ?></p>
                <?php endif; ?>
                <?php if ($refund): $rs = $refundStatus[(int)$refund->status] ?? ['', '#6b7280']; ?>
                    <div class="note_order flex-item-center">
                        <img src="/images/icon/hoan-tien.svg" alt="">
                        <p>Yêu cầu trả hàng/hoàn tiền (<?= Html::encode($refund->reason) ?>): <strong style="color:<?= $rs[1] ?>"><?= $rs[0] ?></strong><?= (int)$refund->status === OrderRefund::STATUS_REJECT && $refund->reason_cancel ? ' — ' . Html::encode($refund->reason_cancel) : '' ?></p>
                    </div>
                <?php endif; ?>
                <?php if(!empty($data['date_refund_expire']) && !$refund && $data['can_refund']){ ?>
                    <div class="note_order flex-item-center">
                        <img src="/images/icon/note-order.svg" alt="">
                        <p>Nếu hàng nhận được có vấn đề, bạn có thể gửi yêu cầu Trả hàng/Hoàn tiền trước <?= $data['date_refund_expire'] ?></p>
                    </div>
                <?php } ?>
                <div class="desc_item">
                    <div class="flex-center avatar_pro">
                        <img src="<?= Html::encode($data['product_info']['image']) ?>" alt="<?= Html::encode($data['product_info']['name']) ?>" onerror="this.style.visibility='hidden'">
                    </div>
                    <div class="text_desc d-flex flex-column">
                        <a href="<?= Url::to(['/product/detail', 'id' => $data['product_info']['id']]) ?>"><?= Html::encode($data['product_info']['name']) ?></a>
                        <div class="flex-item-center">
                            <strong><?= HelperController::formatPrice($data['product_info']['price']) ?></strong>
                            <?php if ($data['product_info']['percent_discount'] > 0): ?><span>-<?= (int)$data['product_info']['percent_discount'] ?>%</span><?php endif; ?>
                        </div>
                        <div class="flex-item-center justify-content-between">
                            <p>Số lượng <?= $data['product_info']['quantity'] ?></p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="detail_order">
                <div class="payment_type d-flex flex-column">
                    <div class="type_text d-flex flex-column">
                        <div class="title_type flex-item-center justify-content-between">
                            <p>Thông tin vận chuyển</p>
                        </div>
                        <div class="type_text_item">
                            <div class="flex-center">
                                <img src="/images/icon/map.svg" alt="">
                            </div>
                            <div>
                                <?php if ($ship): ?>
                                    <p><?= Html::encode(implode(', ', array_filter([$ship['address'] ?? '', $ship['district'] ?? '', $ship['province'] ?? '']))) ?></p>
                                    <span><?= Html::encode($ship['province'] ?? '') ?></span>
                                <?php else: ?>
                                    <p class="color-gray">Địa chỉ giao hàng không còn tồn tại</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="type_text">
                        <div class="title_type flex-item-center justify-content-between">
                            <p>Phương thức thanh toán</p>
                        </div>
                        <?php if($data['type_payment'] == 1){ ?>
                        <div class="type_text_item">
                            <div class="flex-center">
                                <img src="/images/icon/bank.svg" alt="">
                            </div>
                            <div>
                                <p><?= Html::encode($data['bank_payment_info']['ten_bank'] ?? 'Chuyển khoản') ?></p>
                                <span><?= Html::encode(trim(($data['bank_payment_info']['stk'] ?? '') . ' · ' . ($data['bank_payment_info']['ten_tk'] ?? ''), ' ·')) ?> · Nội dung: DH<?= (int)$data['id'] ?></span>
                            </div>
                        </div>
                        <?php }else{ ?>
                            <p>Thanh toán khi nhận hàng</p>
                        <?php } ?>
                    </div>
                </div>
                <div class="price_order">
                    <h2>Thanh toán</h2>
                    <div class="d-flex justify-content-between">
                        <p>Giá</p>
                        <span><?= HelperController::formatPrice($data['pay_info']['price']) ?></span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <p>Phí ship</p>
                        <span><?= HelperController::formatPrice($data['pay_info']['fee_ship']) ?></span>
                    </div>
                    <?php if ($data['pay_info']['price_voucher'] > 0): ?>
                    <div class="d-flex justify-content-between">
                        <p>Giảm voucher</p>
                        <span>-<?= HelperController::formatPrice($data['pay_info']['price_voucher']) ?></span>
                    </div>
                    <?php endif; ?>
                    <div class="d-flex justify-content-between">
                        <p>Tổng</p>
                        <span class="price_final"><?= HelperController::formatPrice($data['pay_info']['total_price']) ?></span>
                    </div>
                    <div class="action_history">
                        <?php if ($data['can_cancel']): ?>
                            <button type="button" class="btn_action btn-blue flex-center js_cancel_order" data-id="<?= (int)$data['id'] ?>">Huỷ đơn hàng</button>
                        <?php endif; ?>
                        <?php if ($data['can_refund'] && !$refund): ?>
                            <button type="button" class="btn_action btn-blue flex-center" data-toggle="modal" data-target="#modalRefund">Trả hàng/Hoàn tiền</button>
                        <?php endif; ?>
                        <?php if ($canReview): ?>
                            <a href="<?= Url::to(['/info/review']) ?>" class="btn_action btn-orange flex-center">Đánh giá</a>
                        <?php endif; ?>
                        <?php if (in_array((int)$order->status, [Order::STATUS_PURCHASED, Order::STATUS_CANCEL, Order::STATUS_REFUND], true)): ?>
                            <a href="<?= Url::to(['/cart/reorder', 'id' => $data['id']]) ?>" class="btn_action btn-orange flex-center">Mua lại</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<?php if ($data['can_refund'] && !$refund): ?>
<div class="modal fade" id="modalRefund" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Yêu cầu trả hàng / hoàn tiền</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Đóng"><span aria-hidden="true">&times;</span></button>
            </div>
            <form class="modal-body" id="form_refund" data-id="<?= (int)$data['id'] ?>">
                <p class="mb-2"><strong>Tình huống bạn đang gặp</strong></p>
                <label class="d-flex align-items-start mb-2" style="gap:8px"><input type="radio" name="situation" value="1"> Tôi đã nhận hàng nhưng hàng có vấn đề (bể vỡ, sai mẫu, hàng lỗi…)</label>
                <label class="d-flex align-items-start mb-3" style="gap:8px"><input type="radio" name="situation" value="2"> Tôi chưa nhận được hàng / nhận thiếu hàng</label>
                <div class="form-group">
                    <label for="refund_reason"><strong>Lý do</strong></label>
                    <select id="refund_reason" name="reason" class="form-control">
                        <option value="">— Chọn lý do —</option>
                        <?php foreach ($reasons as $reason): ?><option value="<?= Html::encode($reason) ?>"><?= Html::encode($reason) ?></option><?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="refund_note"><strong>Mô tả thêm</strong> (không bắt buộc)</label>
                    <textarea id="refund_note" name="note" class="form-control" rows="3" maxlength="400" placeholder="Mô tả tình trạng sản phẩm, thời điểm nhận hàng…"></textarea>
                </div>
                <p class="color-gray" style="font-size:13px">Số tiền hoàn dự kiến: <strong><?= HelperController::formatPrice($data['pay_info']['total_price'] - $data['pay_info']['fee_ship']) ?>đ</strong> (không gồm phí ship). Voucher đã dùng có thể không được hoàn lại.</p>
                <button type="submit" class="btn_action btn-orange flex-center w-100">Gửi yêu cầu</button>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>
