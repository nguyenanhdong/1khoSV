<?php

use yii\helpers\Url;

$controller = Yii::$app->controller->id;
$action = Yii::$app->controller->action->id;
$show_menu = true;
$show_search = true;
$not_show_search = [
    'voucher/index',
    'category/index',
];
$not_show_menu = [
    'voucher/index',
    'category/index',
];
if (in_array($controller.'/'.$action, $not_show_menu)) {
    $show_menu = false;
}
if (in_array($controller.'/'.$action, $not_show_search)) {
    $show_search = false;
}
?>

<div id="header">
    <div class="header_top d-none d-lg-block">
        <div class="container">
            <div class="header_top_group">
                <div class="header_logo">
                    <a href="/">
                        <img src="/images/icon/logo.svg" alt="">
                    </a>
                </div>
                <div class="header_search">
                    <input type="text" placeholder="Tìm kiếm sản phẩm">
                    <button>
                        <img src="/images/icon/search.svg" alt="">
                    </button>
                </div>
                <div class="header_action">
                    <a href="">
                        <img src="/images/icon/gv-icon.svg" alt="">
                        <p>Rao vặt</p>
                    </a>
                    <a href="">
                        <img src="/images/icon/noti-icon.svg" alt="">
                        <p>Thông báo</p>
                    </a>
                    <a href="">
                        <img src="/images/icon/vi-icon.svg" alt="">
                        <p>Ví</p>
                    </a>
                    <a href="">
                        <img src="/images/icon/user-icon.svg" alt="">
                        <p>Đăng Nhập</p>
                    </a>
                    <a href="">
                        <img src="/images/icon/cart-icon.svg" alt="">
                        <p>Giỏ hàng</p>
                    </a>
                </div>
            </div>
        </div>
    </div>
    <div class="header_top_mobi d-block d-lg-none position-relative">
        <div class="header_gr_mobi">
            <div class="toogle_icon">
                <i id="btn_toggle_menu" class="fal fa-bars"></i>
            </div>
            <a href="/">
                <img class="logo" src="/images/icon/logo.svg" alt="">
            </a>
            <div class="header_mobi_icon">
                <a href="">
                    <img src="/images/icon/vi-icon.svg" alt="">
                </a>
                <a href="">
                    <img src="/images/icon/noti-icon.svg" alt="">
                </a>
            </div>
        </div>
        <div class="header_toggle_mobi">
            <button class="close_sidebar"><img src="/images/icon/icon-x.svg" alt=""></button>
            <a href="/">
                <img class="logo" src="/images/icon/logo-menu-mobi.svg" alt="">
            </a>
            <ul class="menu_list_mobi">
                <li>
                    <a href="">Máy nổ, động cơ</a>
                </li>
                <li>
                    <a href="">Máy cày</a>
                </li>
                <li>
                    <a href="">Máy phát điện</a>
                </li>
                <li>
                    <a href="">Ô tô tải</a>
                </li>
                <li>
                    <a href="">Công nông, ba bánh </a>
                </li>
                <li>
                    <a href="">Vòng bi, bạn đạn</a>
                </li>
                <li>
                    <a href="">Dầu</a>
                </li>
                <li>
                    <a href="">Mỡ công nghiệp</a>
                </li>
            </ul>
            <div class="hotline">
                <a href="">
                    <img src="/images/icon/phone-menu.svg" alt="">
                    0888.333.215
                </a>
            </div>
        </div>
        <div class="overlay"></div>
    </div>
    <?php if ($show_search) { ?>
        <div class="header_search_mobi d-block d-lg-none">
            <div class="header_search_gr">
                <div class="position-relative">
                    <div class="icon_search_mb">
                        <img src="/images/icon/k.svg" alt="">
                    </div>
                    <input type="text" placeholder="Tìm kiếm sản phẩm...">
                </div>
                <div class="cart_mobi flex-center">
                    <img src="/images/icon/cart-icon.svg" alt="">
                </div>
            </div>
        </div>
    <?php } ?>
    <?php if ($show_menu) { ?>
        <div class="container d-none d-lg-block">
            <div class="header_menu">
                <ul>
                    <?php for($i = 0; $i < 7; $i++) { ?>
                    <li>
                        <a href="<?= Url::to(['category/index']) ?>">
                            Máy nổ, động cơ
                            <i class="far fa-angle-down"></i>
                        </a>
                    </li>
                    <?php } ?>
                </ul>
            </div>
        </div>
    <?php } ?>
</div>