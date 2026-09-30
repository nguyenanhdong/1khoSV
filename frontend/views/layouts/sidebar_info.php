<?php

use yii\helpers\Url;
use yii\helpers\Html;

$controller = Yii::$app->controller->id;
$action = Yii::$app->controller->action->id;
$user = Yii::$app->user->identity;
?>

<div class="voucher_sidebar">
    <div class="shop_info_bar">
        <div class="shop_avatar">
            <img class="w-100" src="<?= Html::encode($user->avatar ?: '/images/icon/user-icon.svg') ?>" alt="" onerror="this.onerror=null;this.src='/images/icon/user-icon.svg'">
        </div>
        <div class="shop_des">
            <p><?= Html::encode($user->fullname ?: 'Khách hàng 1Kho') ?> <img src="/images/icon/badge.svg" alt=""></p>
            <span><?= Html::encode($user->phone ?: '') ?></span>
        </div>
    </div>
    <div class="list_item_sidebar">
        <div class="sidebar_item mt-4">
            <a class="<?= $action == 'acc-info' ? 'active' : '' ?>" href="<?= Url::to(['/info/acc-info']) ?>">Thông tin cá nhân <i class="far fa-angle-right"></i></a>
        </div>
        <div class="content_sidebar">
            <div class="sidebar_item_action">
                <p>Lịch sử mua hàng</p>
                <div class="group_item_action">
                    <a class="<?= $action == 'confirmed' ? 'active' : '' ?>" href="<?= Url::to(['/info/confirmed']) ?>">
                        <div class="flex-center">
                            <img src="/images/icon/list.svg" alt="">
                        </div>
                        <span>Đã xác nhận</span>
                    </a>
                    <a class="<?= $action == 'await-confirmed' ? 'active' : '' ?>" href="<?= Url::to(['/info/await-confirmed']) ?>">
                        <div class="flex-center">
                            <img src="/images/icon/bag-search.svg" alt="">
                        </div>
                        <span>Chờ xác nhận</span>
                    </a>
                    <a class="<?= $action == 'delivering' ? 'active' : '' ?>" href="<?= Url::to(['/info/delivering']) ?>">
                        <div class="flex-center">
                            <img src="/images/icon/car.svg" alt="">
                        </div>
                        <span>Đang giao</span>
                    </a>
                    <a class="<?= $action == 'purchase-history' ? 'active' : '' ?>" href="<?= Url::to(['/info/purchase-history']) ?>">
                        <div class="flex-center">
                            <img src="/images/icon/package-sended.svg" alt="">
                        </div>
                        <span>Đã mua</span>
                    </a>
                </div>

            </div>
            <div class="sidebar_item_action mt-4 action_color">
                <p>Quan tâm</p>
                <div class="group_item_action group_qt">
                    <a class="<?= $action == 'review' ? 'active' : '' ?>" href="<?= Url::to(['/info/review']) ?>">
                        <div class="flex-center">
                            <img src="/images/icon/danh-gia.svg" alt="">
                        </div>
                        <span>Đánh giá</span>
                    </a>
                    <a class="<?= $action == 'seen' ? 'active' : '' ?>" href="<?= Url::to(['/info/seen']) ?>">
                        <div class="flex-center">
                            <img src="/images/icon/da-xem.svg" alt="">
                        </div>
                        <span>Sản phẩm đã xem</span>
                    </a>
                    <a class="<?= $action == 'favourite' ? 'active' : '' ?>" href="<?= Url::to(['/info/favourite']) ?>">
                        <div class="flex-center">
                            <img src="/images/icon/heart.svg" alt="">
                        </div>
                        <span>Sản phẩm yêu thích</span>
                    </a>
                    <a class="<?= $action == 'return' ? 'active' : '' ?>" href="<?= Url::to(['/info/return']) ?>">
                        <div class="flex-center">
                            <img src="/images/icon/hoan-tien.svg" alt="">
                        </div>
                        <span>Trả hàng, hoàn tiền</span>
                    </a>
                </div>
            </div>
        </div>

        <div class="sidebar_item">
            <a class="<?= $controller == 'voucher' ? 'active' : '' ?>" href="<?= Url::to(['/voucher/index']) ?>">Voucher của tôi <i class="far fa-angle-right"></i></a>
        </div>
        <?php foreach (['chinh-sach-bao-hanh' => 'Chính sách bảo hành', 'chinh-sach-doi-tra' => 'Chính sách đổi trả hàng hóa', 'chinh-sach-bao-mat' => 'Chính sách bảo mật', 'gioi-thieu' => 'Giới thiệu 1Kho'] as $slug => $label): ?>
        <div class="sidebar_item">
            <a href="<?= Url::to(['/site/page', 'slug' => $slug]) ?>"><?= $label ?> <i class="far fa-angle-right"></i></a>
        </div>
        <?php endforeach; ?>
        <div class="sidebar_item">
            <a class="<?= $action == 'invite-friend' ? 'active' : '' ?>" href="<?= Url::to(['/info/invite-friend']) ?>">Mời bạn bè <i class="far fa-angle-right"></i></a>
        </div>
        <div class="sidebar_item">
            <a href="javascript:;" class="js_share" data-url="<?= Url::home(true) ?>" data-title="1Kho - Sàn máy nông nghiệp">Chia sẻ 1Kho <i class="far fa-angle-right"></i></a>
        </div>
        <div class="sidebar_item">
            <a href="<?= Url::to(['/site/contact']) ?>">Liên hệ <i class="far fa-angle-right"></i></a>
        </div>
        <div class="sidebar_item">
            <a href="<?= Url::to(['/site/contact', 'topic' => 'seller']) ?>">Bán hàng cùng sàn <i class="far fa-angle-right"></i></a>
        </div>
        <div class="sidebar_item">
            <a href="javascript:;" class="js_delete_account">Xoá tài khoản <i class="far fa-angle-right"></i></a>
        </div>
        <a href="<?= Url::to(['/site/logout']) ?>" class="log_out"><img src="/images/icon/logout.svg" alt="">Đăng Xuất</a>
    </div>
</div>