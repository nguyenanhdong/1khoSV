<?php

/* @var $this yii\web\View */
/* @var $page array trang đầu của danh sách sản phẩm */
/* @var $categories array */
/* @var $suppliers array */
/* @var $parts array */
/* @var $boms array */

use yii\helpers\Url;
use yii\web\View;
use backend\assets\AgrimacAsset;
use backend\components\AgrimacData as D;

$this->registerJsVar('AM_PRODUCTS', [
    'page'        => $page,
    'categories'  => $categories,
    'suppliers'   => $suppliers,
    'parts'       => $parts,
    'boms'        => (object)$boms,
    'sourceTypes' => D::SOURCE_TYPES,
    'uploadUrl'   => Url::to(['agrimac/upload-image']),
    'maxImages'   => 8,
], View::POS_HEAD);
$this->registerJsFile('@web/agrimac/js/am-products.js', ['depends' => AgrimacAsset::class]);

$field = function ($label, $input, $full = false, $hint = '') {
    return '<div class="am-field' . ($full ? ' am-field-full' : '') . '"><label>' . $label . '</label>' . $input
        . ($hint ? '<div class="am-field-hint">' . $hint . '</div>' : '') . '<div class="am-field-error"></div></div>';
};
?>
<div style="display:flex;gap:14px">
    <div style="width:200px;flex-shrink:0">
        <div class="am-card" style="overflow:hidden">
            <div style="padding:12px 14px;border-bottom:1px solid #f3f4f6;font-weight:700;font-size:13px">📂 Danh mục</div>
            <div id="am-cat-list"></div>
            <div style="padding:10px 14px;border-top:1px solid #f3f4f6">
                <button class="am-btn" data-action="new-category" style="width:100%;padding:7px;font-size:11px">+ Thêm danh mục</button>
            </div>
        </div>
    </div>

    <div style="flex:1">
        <div class="am-row-between" style="margin-bottom:12px;gap:10px">
            <input id="am-product-search" class="am-input" placeholder="🔍 Tìm theo tên hoặc mã sản phẩm..." style="max-width:360px">
            <span id="am-product-count" style="font-size:13px;color:#6b7280;margin-left:auto"></span>
            <button class="am-btn" data-action="new-product">+ Thêm sản phẩm</button>
        </div>
        <div id="am-product-grid" class="am-grid-cards"></div>
        <div id="am-product-pager"></div>
    </div>
</div>

<div id="am-product-modal" class="am-modal am-hidden">
    <div class="am-modal-dialog" style="width:620px">
        <div class="am-modal-head am-dark-gradient">
            <div>
                <div id="am-pm-title" style="font-weight:800;font-size:15px;color:#fff">🚜 Thêm sản phẩm mới</div>
                <div id="am-pm-sub" style="font-size:11px;color:rgba(255,255,255,0.5);margin-top:2px">Tồn kho ban đầu = 0, tăng khi tạo phiếu nhập kho hoặc hoàn tất lắp ráp</div>
            </div>
            <button type="button" class="am-modal-close" data-action="close-modal">✕</button>
        </div>
        <form id="am-product-form" class="am-modal-body" novalidate>
            <div class="am-form-grid">
                <?= $field('Tên sản phẩm <span class="am-req">*</span>', '<input name="name" class="am-input" placeholder="VD: Kubota L3408">', true) ?>
                <?= $field('Mã sản phẩm', '<input name="code" class="am-input" readonly style="background:#f8fafc;color:#6b7280">', false, 'Tự sinh khi lưu') ?>
                <?= $field('Danh mục <span class="am-req">*</span>', '<select name="catId" class="am-input"></select>') ?>
                <?= $field('Nguồn hàng <span class="am-req">*</span>', '<select name="sourceType" class="am-input"></select>') ?>
                <?= $field('Nhà cung cấp', '<select name="supId" class="am-input"></select>', false, 'Bắt buộc khi nhập nguyên chiếc') ?>
                <?= $field('Giá bán (đ) <span class="am-req">*</span>', '<input name="price" type="number" min="0" class="am-input" placeholder="VD: 285000000">') ?>
                <?= $field('Giá vốn (đ)', '<input name="cost" type="number" min="0" class="am-input" placeholder="VD: 240000000">', false, 'Tự lắp: có thể để trống, tính theo BOM') ?>
                <?= $field('Công suất (HP)', '<input name="hp" type="number" min="0" class="am-input" placeholder="VD: 34">') ?>
                <?= $field('Hệ dẫn động', '<select name="drive" class="am-input"><option value="">— Chọn —</option><option value="2WD">2WD</option><option value="4WD">4WD</option></select>') ?>
                <?= $field('Trọng lượng (kg)', '<input name="weight" type="number" min="0" class="am-input" placeholder="VD: 1450">') ?>
                <?= $field('Tồn tối thiểu', '<input name="minStock" type="number" min="0" class="am-input" value="3">', false, 'Dưới mức này sẽ cảnh báo sắp hết') ?>
                <div class="am-field am-field-full" data-field="images">
                    <label>Ảnh sản phẩm <span style="font-weight:400;color:#9ca3af">(tối đa 8 ảnh · JPG, PNG, WEBP · ≤ 5MB/ảnh · ảnh đầu tiên là ảnh đại diện)</span></label>
                    <div id="am-pm-images" class="am-img-grid"></div>
                    <input type="file" id="am-pm-file" accept="image/jpeg,image/png,image/webp" multiple class="am-hidden">
                    <div class="am-field-error"></div>
                </div>
                <?= $field('Mô tả', '<textarea name="description" class="am-textarea" style="height:64px" placeholder="Mô tả ngắn về sản phẩm, trang bị đi kèm..."></textarea>', true) ?>
            </div>
            <div id="am-pm-preview" class="am-pm-preview"></div>
        </form>
        <div class="am-modal-foot">
            <button type="button" class="am-cancel" data-action="close-modal">Hủy</button>
            <button type="submit" form="am-product-form" class="am-btn" id="am-pm-submit">💾 Lưu sản phẩm</button>
        </div>
    </div>
</div>

<div id="am-category-modal" class="am-modal am-hidden">
    <div class="am-modal-dialog" style="width:400px">
        <div class="am-modal-head am-dark-gradient">
            <div style="font-weight:800;font-size:15px;color:#fff">📂 Thêm danh mục</div>
            <button type="button" class="am-modal-close" data-action="close-modal">✕</button>
        </div>
        <form id="am-category-form" class="am-modal-body" novalidate>
            <?= $field('Tên danh mục <span class="am-req">*</span>', '<input name="name" class="am-input" placeholder="VD: Máy gặt đập liên hợp">', true) ?>
            <div style="height:12px"></div>
            <?= $field('Thuộc danh mục cha', '<select name="parentId" class="am-input"></select>', true, 'Để trống nếu là danh mục cấp 1') ?>
        </form>
        <div class="am-modal-foot">
            <button type="button" class="am-cancel" data-action="close-modal">Hủy</button>
            <button type="submit" form="am-category-form" class="am-btn">💾 Lưu danh mục</button>
        </div>
    </div>
</div>
