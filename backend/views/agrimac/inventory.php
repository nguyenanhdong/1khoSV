<?php

/* @var $this yii\web\View */
/* @var $lowStock array */
/* @var $stockValue int */
/* @var $parts array */
/* @var $partCategories array */
/* @var $suppliers array */
/* @var $categories array */
/* @var $transactions array */

use yii\web\View;
use backend\assets\AgrimacAsset;

$this->registerJsVar('AM_INV', [
    'lowStock'       => $lowStock,
    'stockValue'     => $stockValue,
    'parts'          => $parts,
    'partCategories' => $partCategories,
    'suppliers'      => $suppliers,
    'categories'     => $categories,
    'transactions'   => $transactions,
], View::POS_HEAD);
$this->registerJsFile('@web/agrimac/js/am-inventory.js', ['depends' => AgrimacAsset::class]);

$label = function ($text, $color = '#374151', $size = 12, $weight = 700, $mb = 5) {
    return '<div style="font-size:' . $size . 'px;font-weight:' . $weight . ';color:' . $color . ';margin-bottom:' . $mb . 'px">' . $text . '</div>';
};
?>
<div style="position:relative">
    <div class="am-tabs" data-group="inv">
        <button class="am-tab active" data-tab="stock">📦 Tồn kho máy</button>
        <button class="am-tab" data-tab="parts">🔩 Tồn linh kiện</button>
        <button class="am-tab" data-tab="tx">📝 Lịch sử nhập/xuất</button>
        <button class="am-tab" data-tab="import">📥 Nhập kho mới</button>
    </div>

    <div data-tab-group="inv" data-tab-pane="stock">
        <div class="am-toolbar-end" style="justify-content:flex-start">
            <input id="am-stock-search" class="am-input" placeholder="🔍 Tìm theo tên hoặc mã sản phẩm..." style="max-width:360px">
        </div>
        <div class="am-card" style="overflow:hidden">
        <table class="am-table">
            <thead><tr><th>Mã SP</th><th>Tên sản phẩm</th><th>Danh mục</th><th>Nhà cung cấp</th><th>Tồn kho</th><th>Đơn giá vốn</th><th>Giá trị tồn</th><th>Trạng thái</th></tr></thead>
            <tbody id="am-stock-rows"></tbody>
            <tfoot><tr>
                <td colspan="6" style="font-weight:700;font-size:13px">Tổng giá trị tồn kho (toàn bộ sản phẩm)</td>
                <td id="am-stock-total" style="font-weight:900;font-size:15px;color:#059669"></td>
                <td></td>
            </tr></tfoot>
        </table>
        </div>
        <div id="am-stock-pager"></div>
    </div>

    <div data-tab-group="inv" data-tab-pane="parts" class="am-hidden">
        <div class="am-toolbar-end" style="gap:8px">
            <select id="am-part-filter" class="am-input" style="width:200px"></select>
            <button class="am-btn" data-action="new-part">+ Thêm linh kiện</button>
        </div>
        <div class="am-card" style="overflow:hidden">
        <table class="am-table">
            <thead><tr><th>Mã LK</th><th>Tên linh kiện</th><th>Nhóm</th><th>Đơn vị</th><th>Tồn kho</th><th>Tồn tối thiểu</th><th>Đơn giá vốn</th><th>Giá trị tồn</th><th>Trạng thái</th><th></th></tr></thead>
            <tbody id="am-part-rows"></tbody>
            <tfoot><tr>
                <td colspan="7" style="font-weight:700;font-size:13px">Tổng giá trị tồn linh kiện</td>
                <td id="am-part-total" style="font-weight:900;font-size:15px;color:#0ea5e9"></td>
                <td colspan="2"></td>
            </tr></tfoot>
        </table>
        </div>
    </div>

    <div data-tab-group="inv" data-tab-pane="tx" class="am-hidden">
        <div class="am-toolbar-end" style="gap:8px">
            <select id="am-tx-filter" class="am-input" style="width:180px">
                <option value="">Tất cả phiếu</option><option value="import">📥 Chỉ phiếu nhập</option><option value="export">📤 Chỉ phiếu xuất</option>
            </select>
            <button class="am-btn" data-action="new-export" style="background:#7c3aed;color:#fff">📤 Tạo phiếu xuất</button>
        </div>
        <div class="am-card" style="overflow:hidden">
        <table class="am-table">
            <thead><tr><th>Mã phiếu</th><th>Loại</th><th>Hàng</th><th>Tên hàng</th><th>Số lượng</th><th>Tham chiếu</th><th>Đơn giá</th><th>Thành tiền</th><th>Ngày</th><th>Lý do / ghi chú</th></tr></thead>
            <tbody id="am-tx-rows"></tbody>
        </table>
        </div>
    </div>

    <div data-tab-group="inv" data-tab-pane="import" class="am-hidden" style="display:flex;gap:16px;align-items:flex-start">
        <div class="am-card-lg" style="padding:24px;width:480px;flex-shrink:0">
            <div class="am-section-title">📥 Tạo phiếu nhập kho</div>

            <div style="margin-bottom:14px">
                <?= $label('Loại hàng nhập', '#374151', 12, 700, 6) ?>
                <div style="display:flex;gap:8px">
                    <button type="button" class="am-kind active" data-kind="product">🚜 Máy nguyên chiếc</button>
                    <button type="button" class="am-kind" data-kind="part">🔩 Linh kiện lắp ráp</button>
                </div>
            </div>

            <div id="am-imp-product-block" style="margin-bottom:14px">
                <div class="am-row-between" style="margin-bottom:6px">
                    <span style="font-size:12px;font-weight:700;color:#374151">Sản phẩm</span>
                    <button type="button" class="am-toggle" data-toggle-panel="product"></button>
                </div>
                <div class="am-picker" data-source="products">
                    <input type="hidden" id="am-imp-product" name="impProduct" value="">
                    <input type="text" class="am-input am-picker-input" autocomplete="off" placeholder="Gõ tên hoặc mã sản phẩm để tìm...">
                    <div class="am-picker-list am-hidden"></div>
                </div>
                <div id="am-new-product" class="am-hidden" style="margin-top:10px;background:#eff6ff;border:1.5px solid #bfdbfe;border-radius:10px;padding:14px 16px">
                    <div style="display:flex;align-items:center;gap:6px;margin-bottom:11px">
                        <div class="am-plus" style="background:#3b82f6">+</div>
                        <span style="font-weight:700;font-size:13px;color:#1d4ed8">Tạo nhanh sản phẩm mới</span>
                    </div>
                    <?php foreach ([
                        ['name', 'Tên sản phẩm *', 'text', 'VD: Kubota GL240'],
                        ['specs', 'Specs / Thông số', 'text', 'VD: 24HP · 4WD'],
                        ['price', 'Giá bán (đ)', 'number', 'VD: 250000000'],
                        ['cost', 'Giá vốn (đ)', 'number', 'VD: 210000000'],
                    ] as [$field, $text, $type, $ph]): ?>
                        <div style="margin-bottom:8px">
                            <?= $label($text, '#1d4ed8', 11, 600, 3) ?>
                            <input type="<?= $type ?>" data-np="<?= $field ?>" placeholder="<?= $ph ?>" class="am-input am-input-blue">
                        </div>
                    <?php endforeach; ?>
                    <div style="margin-bottom:10px">
                        <?= $label('Danh mục', '#1d4ed8', 11, 600, 3) ?>
                        <select data-np="catId" class="am-input am-input-blue"></select>
                    </div>
                    <div style="display:flex;gap:8px">
                        <button type="button" data-action="add-product" class="am-add-btn" style="background:#2563eb">✓ Thêm &amp; chọn ngay</button>
                        <button type="button" data-toggle-panel="product" data-close="1" class="am-cancel">Hủy</button>
                    </div>
                </div>
            </div>

            <div id="am-imp-part-block" class="am-hidden" style="margin-bottom:14px">
                <div class="am-row-between" style="margin-bottom:6px">
                    <span style="font-size:12px;font-weight:700;color:#374151">Linh kiện</span>
                    <button type="button" class="am-toggle" data-toggle-panel="part"></button>
                </div>
                <select id="am-imp-part" class="am-input"></select>
                <div id="am-imp-part-info" style="font-size:11px;color:#6b7280;margin-top:5px"></div>
                <div id="am-new-part" class="am-hidden" style="margin-top:10px;background:#f0f9ff;border:1.5px solid #7dd3fc;border-radius:10px;padding:14px 16px">
                    <div style="display:flex;align-items:center;gap:6px;margin-bottom:11px">
                        <div class="am-plus" style="background:#0ea5e9">+</div>
                        <span style="font-weight:700;font-size:13px;color:#0369a1">Tạo nhanh linh kiện mới</span>
                    </div>
                    <?php foreach ([
                        ['name', 'Tên linh kiện *', 'text', 'VD: Bơm cao áp Kubota'],
                        ['unit', 'Đơn vị tính', 'text', 'VD: cái / bộ'],
                        ['cost', 'Giá vốn (đ)', 'number', 'VD: 4500000'],
                        ['minStock', 'Tồn tối thiểu', 'number', 'VD: 5'],
                    ] as [$field, $text, $type, $ph]): ?>
                        <div style="margin-bottom:8px">
                            <?= $label($text, '#0369a1', 11, 600, 3) ?>
                            <input type="<?= $type ?>" data-npart="<?= $field ?>" placeholder="<?= $ph ?>" class="am-input am-input-sky">
                        </div>
                    <?php endforeach; ?>
                    <div style="margin-bottom:10px">
                        <?= $label('Nhóm linh kiện', '#0369a1', 11, 600, 3) ?>
                        <select data-npart="cat" class="am-input am-input-sky"></select>
                    </div>
                    <div style="display:flex;gap:8px">
                        <button type="button" data-action="add-part" class="am-add-btn" style="background:#0ea5e9">✓ Thêm &amp; chọn ngay</button>
                        <button type="button" data-toggle-panel="part" data-close="1" class="am-cancel">Hủy</button>
                    </div>
                </div>
            </div>

            <div style="margin-bottom:14px">
                <div class="am-row-between" style="margin-bottom:6px">
                    <span style="font-size:12px;font-weight:700;color:#374151">Nhà cung cấp</span>
                    <button type="button" class="am-toggle" data-toggle-panel="supplier"></button>
                </div>
                <select id="am-imp-supplier" class="am-input"></select>
                <div id="am-new-supplier" class="am-hidden" style="margin-top:10px;background:#fffbeb;border:1.5px solid #fcd34d;border-radius:10px;padding:14px 16px">
                    <div style="display:flex;align-items:center;gap:6px;margin-bottom:11px">
                        <div class="am-plus" style="background:#f59e0b">+</div>
                        <span style="font-weight:700;font-size:13px;color:#b45309">Tạo nhanh nhà cung cấp</span>
                    </div>
                    <?php foreach ([
                        ['name', 'Tên nhà cung cấp *', 'VD: Shibaura Vietnam'],
                        ['contact', 'Số điện thoại / Email', 'VD: 028.1234.5678'],
                        ['address', 'Địa chỉ', 'VD: Q.1, TP.HCM'],
                    ] as [$field, $text, $ph]): ?>
                        <div style="margin-bottom:8px">
                            <?= $label($text, '#b45309', 11, 600, 3) ?>
                            <input type="text" data-ns="<?= $field ?>" placeholder="<?= $ph ?>" class="am-input" style="border-color:#fcd34d;background:#fefce8">
                        </div>
                    <?php endforeach; ?>
                    <div style="display:flex;gap:8px">
                        <button type="button" data-action="add-supplier" class="am-add-btn" style="background:#f59e0b">✓ Thêm &amp; chọn ngay</button>
                        <button type="button" data-toggle-panel="supplier" data-close="1" class="am-cancel">Hủy</button>
                    </div>
                </div>
            </div>

            <div style="margin-bottom:12px">
                <?= $label('Số lượng nhập') ?>
                <input id="am-imp-qty" type="number" placeholder="VD: 5" class="am-input">
            </div>
            <div style="margin-bottom:12px">
                <?= $label('Đơn giá vốn (đ)') ?>
                <input id="am-imp-price" type="number" placeholder="VD: 240000000" class="am-input">
            </div>
            <div id="am-imp-total" class="am-hidden" style="background:#f0fdf4;border:1.5px solid #bbf7d0;border-radius:8px;padding:10px 14px;margin-bottom:14px;display:flex;justify-content:space-between;align-items:center">
                <span style="font-size:12px;color:#6b7280">Tổng giá trị nhập:</span>
                <span data-value style="font-weight:900;font-size:15px;color:#059669"></span>
            </div>
            <div style="margin-bottom:14px">
                <?= $label('Ghi chú') ?>
                <textarea id="am-imp-note" class="am-textarea" placeholder="Ghi chú thêm về lô hàng..." style="height:56px"></textarea>
            </div>
            <button type="button" data-action="submit-import" class="am-dark-gradient" style="width:100%;padding:11px;color:#fbbf24;border:none;border-radius:10px;cursor:pointer;font-weight:800;font-size:14px">💾 Tạo phiếu nhập kho</button>
        </div>

        <div style="flex:1;display:flex;flex-direction:column;gap:12px">
            <div class="am-card" style="padding:16px">
                <div style="font-weight:700;font-size:13px;margin-bottom:10px;color:#374151">💡 Hướng dẫn nhập kho nhanh</div>
                <?php foreach ([
                    ['Nhập máy hay linh kiện?', 'Chọn [🚜 Máy nguyên chiếc] khi mua máy về bán, [🔩 Linh kiện lắp ráp] khi nhập linh kiện cho xưởng tự lắp. Giá vốn linh kiện được cập nhật theo phiếu nhập gần nhất.', '#e0f2fe', '#0369a1'],
                    ['Thiếu sản phẩm / linh kiện?', 'Bấm [+ Tạo ... mới] ngay cạnh dropdown — điền tên, giá, danh mục rồi thêm. Hàng mới sẽ được chọn tự động.', '#dbeafe', '#2563eb'],
                    ['Thiếu nhà cung cấp?', 'Bấm [+ Tạo NCC mới] — nhập tên, SĐT, địa chỉ. NCC mới sẽ được chọn tự động vào phiếu.', '#fef3c7', '#b45309'],
                    ['Xem tồn kho?', 'Sau khi lưu phiếu, chuyển qua tab Tồn kho máy / Tồn linh kiện để xem số lượng đã được cộng dồn.', '#d1fae5', '#059669'],
                ] as [$title, $text, $bg, $color]): ?>
                    <div style="background:<?= $bg ?>;border-radius:8px;padding:10px 12px;margin-bottom:8px">
                        <div style="font-weight:700;font-size:12px;color:<?= $color ?>;margin-bottom:3px"><?= $title ?></div>
                        <div style="font-size:11px;color:#374151;line-height:1.5"><?= $text ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="am-card" style="padding:16px">
                <div style="font-weight:700;font-size:13px;margin-bottom:10px">⚠ Cảnh báo tồn kho</div>
                <div id="am-stock-alerts"></div>
            </div>
        </div>
    </div>
</div>
