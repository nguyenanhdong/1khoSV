<?php

use yii\helpers\Url;
use yii\web\View;
use backend\models\Config;
use yii\widgets\Breadcrumbs;

?>
<div class="container">
    <?php
    echo Breadcrumbs::widget([
        'homeLink' => ['label' => '', 'url' => '/'],
        'links' => [
            ['label' => 'Máy cày', 'url' => ['product/delivery']],
            'Đăng tin',
        ],
    ]);

    ?>

    <section class="post_delivery">
        <form action="">
            <h2>Đăng tin</h2>
            <div class="form-group">
                <div class="box-image flex-center flex-column">
                    <input type="file" class="hide_file">
                    <img src="/images/icon/icon-img.svg" alt="">
                    <label for="">Chọn 3 video ngắn dưới 1 phút + 8 hình ảnh</label>
                </div>
            </div>
            <div class="form-group">
                <label for="">Danh mục</label>
                <select id="">
                    <option value="">Chọn danh mục</option>
                    <option value="">Chọn danh mục</option>
                </select>
            </div>
            <div class="form-group">
                <label for="">Thương hiệu</label>
                <input type="text" placeholder="VD: Huyendai, Komatsu, Hitachi,...">
            </div>
            <div class="form-group">
                <label for="">Chủng loại</label>
                <input type="text" placeholder="VD: Pc 200 - 8, Hd500,...">
            </div>
            <div class="grid_50">
                <div class="form-group">
                    <label for="">Năm sản xuất</label>
                    <select id="">
                        <option value="">Chọn năm sản xuất</option>
                        <option value="">Chọn năm sản xuất</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="">Xuất xứ</label>
                    <select id="">
                        <option value="">Xuất xứ</option>
                    </select>
                </div>
            </div>
            <div class="grid_50">
                <div class="form-group">
                    <label for="">Nhiên liệu</label>
                    <input type="text" placeholder="Nhập nhiên liệu">
                </div>
                <div class="form-group">
                    <label for="">Số km sử dụng</label>
                    <input type="text" placeholder="Số km sử dụng">
                </div>
            </div>
            <div class="grid_50">
                <div class="form-group">
                    <label for="">Số giờ sử dụng</label>
                    <input type="text" placeholder="Nhập số giờ sử dụng">
                </div>
                <div class="form-group">
                    <label for="">Giá</label>
                    <input type="text" placeholder="Nhập giá">
                </div>
            </div>
            <div class="grid_50">
                <div class="form-group">
                    <label for="">Mô tả chi tiết</label>
                    <input type="text" placeholder="Mô tả chi tiết">
                </div>
                <div class="form-group">
                    <label for="">Vị trí sản phẩm</label>
                    <input type="text" placeholder="Vị trí sản phẩm">
                </div>
            </div>
            <div class="action_form">
                <button class="btn_action btn-blue flex-center">XEM TRƯỚC</button>
                <button class="btn_action btn-orange flex-center">ĐĂNG TIN</button>
            </div>
        </form>
    </section>
</div>