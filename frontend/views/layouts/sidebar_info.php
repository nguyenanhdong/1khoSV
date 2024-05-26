<?php

use yii\helpers\Url;

$controller = Yii::$app->controller->id;
$action = Yii::$app->controller->action->id;

?>

<div class="voucher_sidebar">
    <div class="shop_info_bar">
        <div class="shop_avatar">
            <img class="w-100" src="/images/page/lazada.png" alt="">
        </div>
        <div class="shop_des">
            <p>Shop Máy Cày <img src="/images/icon/badge.svg" alt=""></p>
            <span>Ví tích điểm <strong>1.000</strong> xu</span>
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
                    <a href="">
                        <div class="flex-center">
                            <img src="/images/icon/bag-search.svg" alt="">
                        </div>
                        <span>Chờ xác nhận</span>
                    </a>
                    <a href="">
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
            <a class="<?= $action == 'acc-points' ? 'active' : '' ?>" href="<?= Url::to(['/info/acc-points']) ?>">Quản lý giao vặt <i class="far fa-angle-right"></i></a>
        </div>
        <div class="sidebar_item">
            <a class="" href="<?= Url::to(['/site/guarantee']) ?>"> Chính sách bảo hành <i class="far fa-angle-right"></i></a>
        </div>
        <div class="sidebar_item">
            <a class="" href="<?= Url::to(['/site/return-policy']) ?>"> Chính sách đổi trả hàng hóa <i class="far fa-angle-right"></i></a>
        </div>
        <div class="sidebar_item">
            <a class="" href="<?= Url::to(['/site/privacy-policy']) ?>"> Chính sách bảo mật <i class="far fa-angle-right"></i></a>
        </div>


        <div class="sidebar_item">
            <a class="<?= $action == 'acc-points' ? 'active' : '' ?>" href="<?= Url::to(['/info/acc-points']) ?>">Ví tích điểm <i class="far fa-angle-right"></i></a>
        </div>
        <div class="sidebar_item">
            <a href="javascript:;">Chia sẻ ứng dụng <i class="far fa-angle-right"></i></a>
        </div>
        <div class="sidebar_item">
            <a class="<?= $action == 'review' ? 'active' : '' ?>" href="<?= Url::to(['/info/review']) ?>">Đánh giá ứng dụng <i class="far fa-angle-right"></i></a>
        </div>
        <div class="sidebar_item">
            <a class="" href="<?= Url::to(['/info/introduce']) ?>">Liên hệ <i class="far fa-angle-right"></i></a>
        </div>
        <div class="sidebar_item">
            <a class="<?= $action == 'introduce' ? 'active' : '' ?>" href="<?= Url::to(['/info/introduce']) ?>">Giới thiệu <i class="far fa-angle-right"></i></a>
        </div>
        <div class="sidebar_item">
            <a class="<?= $action == 'invite-friend' ? 'active' : '' ?>" href="<?= Url::to(['/info/invite-friend']) ?>">Mời bạn bè <i class="far fa-angle-right"></i></a>
        </div>
        <div class="sidebar_item">
            <a class="" href="">Bán hàng cùng sàn <i class="far fa-angle-right"></i></a>
        </div>
        <div class="sidebar_item">
            <a class="" href="">Xoá tài khoản <i class="far fa-angle-right"></i></a>
        </div>
        <button class="log_out"><img src="/images/icon/logout.svg" alt="">Đăng Xuất</button>
    </div>
</div>