<?php

use backend\controllers\ApiNewController;
use backend\models\NotifyUser;
use yii\helpers\Url;

$controller = Yii::$app->controller->id;
$action = Yii::$app->controller->action->id;
$show_menu = true;
$show_search = true;
$not_show_search = [
    'voucher/index',
    'category/index',
    'product/index',
    'product/shop',
    'cart/index',
    'info/profile',
    'info/purchase-history',
    'info/confirmed',
    'info/await-confirmed',
    'info/delivering',
    'info/review',
    'info/favourite',
    'info/return',
    'info/acc-points',
    'site/return-policy',
    'site/guarantee',
    'site/privacy-policy',
    'category/index-sale',
];
$not_show_menu = [
    'voucher/index',
    'category/index',
];
if (in_array($controller . '/' . $action, $not_show_menu)) {
    $show_menu = false;
}
if (in_array($controller . '/' . $action, $not_show_search)) {
    $show_search = false;
}

$allCategory = ApiNewController::AllCategory();

$isGuest = Yii::$app->user->isGuest;

if(!$isGuest)
    $listNotify = NotifyUser::getListNotifyByUser(Yii::$app->user->identity->id);

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
                    <a href="<?= Url::to(['/product/delivery']) ?>">
                        <img src="/images/icon/gv-icon.svg" alt="">
                        <p>Rao vặt</p>
                    </a>
                    <div class="noti_gr position-relative">
                        <a class="toggle_noti" href="<?= $isGuest ? Url::to(['/site/login']) : 'javascript:;' ?>">
                            <img src="/images/icon/noti-icon.svg" alt="">
                            <p>Thông báo</p>
                        </a>
                        <div class="dropdown-menu notification-ui_dd" aria-labelledby="navbarDropdown">
                            <div class="notification-ui_dd-content">
                                <?php
                                    if(!empty($listNotify)){
                                        foreach($listNotify as $row){
                                            $orderDetail = [];
                                            $href = 'javascript:;';
                                            if($row['id_order'] != 0){
                                                $href = Url::to(['/info/order-detail', 'id' => $row['id_order']]);
                                            }else{
                                                $orderDetail = NotifyUser::getDetailNotify($row['id'], Yii::$app->user->identity->id);
                                            }
                                ?>
                                    <a href="<?= $href ?>" class="notification-list notification-list--unread" data-detail="<?= $row['id'] ?>">
                                        <div class="notification-list_img">
                                            <img src="/images/icon/noti.svg" alt="user">
                                        </div>
                                        <div class="notification-list_detail">
                                            <p class="title_noti"><b><?= $row['title'] ?></b></p>
                                            <p><?= $row['desc'] ?> </p>
                                            <span><?= $row['date'] ?></span>
                                        </div>
                                        <div class="notification-list_feature-img flex-item-center">
                                            <i class="fas fa-chevron-right"></i>
                                        </div>
                                    </a>
                                    <?php if(!empty($orderDetail)) { ?>
                                        <div class="notification_detail" id="noti_detail_<?= $row['id'] ?>">
                                            <div class="btn_action_noti_detail d-flex justify-content-between">
                                                <i class="far fa-arrow-left hide_noti_detail"></i>
                                                <i class="fal fa-trash-alt remove_noti"></i>
                                            </div>
                                            <div class="content_noti_detail">
                                                <h4><?= $orderDetail['title'] ?></h4>
                                                <?= $orderDetail['content'] ?>
                                            </div>
                                        </div>
                                    <?php } ?>
                                <?php }}?>
                            </div>
                        </div>
                    </div>
                    <a href="<?= Url::to(['/voucher/index']) ?>">
                        <img src="/images/icon/vi-icon.svg" alt="">
                        <p>Ví</p>
                    </a>
                    <?php if($isGuest){ ?>
                        <a href="<?= Url::to(['/site/login']) ?>">
                            <img src="/images/icon/user-icon.svg" alt="">
                            <p>Đăng Nhập</p>
                        </a>
                    <?php }else{ ?>
                      <a href="<?= Url::to(['/info/acc-info']) ?>">
                        <img src="<?= Yii::$app->user->identity->avatar ? Yii::$app->user->identity->avatar : '/images/icon/user-icon.svg' ?>" alt="">
                        <p><?= Yii::$app->user->identity->fullname ? Yii::$app->user->identity->fullname : 'Tài khoản' ?></p>
                    </a>
                    <?php } ?>
                    <a href="<?= Url::to(['/cart/index']) ?>">
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
                <a href="<?= Url::to(['/voucher/index']) ?>">
                    <img src="/images/icon/vi-icon.svg" alt="">
                </a>
                <div class="noti_gr position-relative">
                    <a class="toggle_noti" href="javascript:;">
                        <img src="/images/icon/noti-icon.svg" alt="">
                    </a>
                    <div class="dropdown-menu notification-ui_dd" aria-labelledby="navbarDropdown">
                        <div class="notification-ui_dd-content">
                            <?php for ($i = 0; $i < 10; $i++) { ?>
                                <div class="notification-list notification-list--unread" data-detail="<?= $i ?>">
                                    <div class="notification-list_img">
                                        <img src="/images/icon/noti.svg" alt="user">
                                    </div>
                                    <div class="notification-list_detail">
                                        <p class="title_noti"><b>Thông báo 1</b></p>
                                        <p>Reference site about Lorem Ipsum Ipsum Ipsum </p>
                                        <span>14:00 - 20/08/2023</span>
                                    </div>
                                    <div class="notification-list_feature-img flex-item-center">
                                        <i class="fas fa-chevron-right"></i>
                                    </div>
                                </div>
                                <div class="notification_detail" id="noti_detail_mb_<?= $i ?>">
                                    <div class="btn_action_noti_detail d-flex justify-content-between">
                                        <i class="far fa-arrow-left hide_noti_detail"></i>
                                        <i class="fal fa-trash-alt remove_noti"></i>
                                    </div>
                                    <div class="content_noti_detail">
                                        <h4>What is Lorem Ipsum?</h4>
                                        <p>Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting Remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset.</p>
                                        <p>Remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.</p>
                                        <p>Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting
                                        </p>
                                    </div>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="header_toggle_mobi">
            <button class="close_sidebar"><img src="/images/icon/icon-x.svg" alt=""></button>
            <a href="/">
                <img class="logo" src="/images/icon/logo-menu-mobi.svg" alt="">
            </a>
            <ul class="menu_list_mobi">
                <?php 
                    if(!empty($allCategory['data'])){
                        foreach($allCategory['data'] as $cat){
                ?>
                <li>
                    <a href="<?= Url::to(['/category/index', 'cate_parent_id' => $cat['id']]) ?>"><?= $cat['name'] ?></a>
                </li>
                <?php }} ?>
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
                    <a href="<?= Url::to(['/cart/index']) ?>">
                        <img src="/images/icon/cart-icon.svg" alt="">
                    </a>
                </div>
            </div>
        </div>
    <?php } ?>
    <?php if ($show_menu) { ?>
        <div class="container d-none d-lg-block">
            <div class="header_menu">
                <ul>
                <?php 
                    if(!empty($allCategory['data'])){
                        foreach($allCategory['data'] as $cat){
                ?>
                <li>
                    <a href="<?= Url::to(['category/index', 'cate_parent_id' => $cat['id']]) ?>">
                        <?= $cat['name'] ?>
                        <i class="far fa-angle-down"></i>
                    </a>
                </li>
                <?php }} ?>
                </ul>
            </div>
        </div>
    <?php } ?>
</div>
<div class="sidebar_bot d-block d-lg-none">
    <div class="list_item_bot">
        <div class="item_bot">
            <a class="text_bot home_item <?= $controller == 'site' && $action == 'index' ? 'active' : '' ?>" href="<?= Url::to(['/site/index']) ?>">
                Trang chủ
            </a>
        </div>
        <div class="item_bot">
            <a class="text_bot product_item <?= $controller . '/' . $action == 'category/index' ? 'active' : '' ?>" href="<?= Url::to(['/category/index']) ?>">
                Sản phẩm
            </a>
        </div>
        <div class="item_bot">
            <a class="text_bot cart_item <?= $controller . '/' . $action == 'product/delivery' ? 'active' : '' ?>" href="<?= Url::to(['/product/delivery']) ?>">
                Rao vặt
            </a>
        </div>
        <div class="item_bot">
            <a class="text_bot profile_item <?= $controller . '/' . $action == 'info/profile' ? 'active' : '' ?>" href="<?= Url::to(['/info/profile']) ?>">
                Tài khoản
            </a>
        </div>
    </div>
</div>