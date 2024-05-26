<?php

use yii\helpers\Url;
use yii\web\View;
use backend\models\Config;
use yii\widgets\Breadcrumbs;
use backend\controllers\CommonController;

?>
<div class="container">
    <?php
    echo Breadcrumbs::widget([
        'homeLink' => ['label' => '', 'url' => '/'],
        'links' => [
            // ['label' => '   Sample Post', 'url' => ['post/edit', 'id' => 1]],
            'Ví voucher ',
        ],
    ]);

    ?>

    <section class="voucher">
        <?= $this->render('/layouts/sidebar_info') ?>
        <div class="form_right">
            <h2>Thông tin cá nhân</h2>
            <div class="status_acc flex-item-center">
                <p class="color-gray">Trạng thái tài khoản</p>
                <span class="active">Đã xác thực</span>
            </div>
            <form action="">
                <div class="form-group">
                    <label for="">Tên</label>
                    <input type="text" placeholder="">
                </div>
                <div class="grid_50 g-15">
                    <div class="form-group">
                        <label for="">Quận/Huyện</label>
                        <select id="">
                            <option value="">Chọn Quận/Huyện</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="">Thành phố</label>
                        <select id="">
                            <option value="">Thành phố</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label for="">Số điện thoại</label>
                    <input type="text" placeholder="">
                </div>
                <div class="form-group">
                    <label for="">Địa chỉ</label>
                    <input type="text" placeholder="">
                </div>
                <div class="form-group mt-4">
                    <button class="btn_action btn-orange flex-center">Lưu</button>
                </div>
            </form>
        </div>
    </section>
</div>