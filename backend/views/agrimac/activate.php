<?php

/* @var $this yii\web\View */
/* @var $warranty array|null */
/* @var $dealer array|null */
/* @var $errors array */
/* @var $form array */
/* @var $done bool */

use yii\helpers\Html;
use backend\components\AgrimacData as D;

$this->title = 'Kích hoạt bảo hành';
$err = function ($name) use ($errors) {
    return isset($errors[$name]) ? '<div class="am-field-error">' . Html::encode($errors[$name]) . '</div>' : '';
};
$cls = function ($name) use ($errors) {
    return 'am-field' . (isset($errors[$name]) ? ' has-error' : '');
};
?>
<?php if ($warranty === null): ?>
    <div class="am-public-card" style="text-align:center">
        <div style="font-size:44px">⚠️</div>
        <h1 class="am-public-title">Mã QR không hợp lệ</h1>
        <p class="am-public-text">Không tìm thấy máy tương ứng với mã QR này. Vui lòng quét lại tem dán trên máy hoặc gọi hotline
            <a href="tel:<?= preg_replace('/\s/', '', D::HOTLINE) ?>"><b><?= D::HOTLINE ?></b></a> để được hỗ trợ.</p>
    </div>
<?php else: ?>
    <div class="am-public-card">
        <div class="am-label" style="margin-bottom:8px">THÔNG TIN MÁY</div>
        <div style="font-weight:800;font-size:18px;color:#1a2035"><?= Html::encode($warranty['prodName']) ?></div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-top:12px">
            <div class="am-info"><div class="am-info-label">🔢 Serial</div><div class="am-info-value" style="font-family:monospace"><?= Html::encode($warranty['serial']) ?></div></div>
            <div class="am-info"><div class="am-info-label">🏪 Đại lý bán</div><div class="am-info-value"><?= Html::encode($dealer['name'] ?? '—') ?></div></div>
        </div>
    </div>

    <?php if ($warranty['status'] === 'active'): ?>
        <div class="am-public-card" style="text-align:center;border-top:4px solid #10b981">
            <div style="font-size:44px"><?= $done ? '🎉' : '🛡' ?></div>
            <h1 class="am-public-title" style="color:#059669"><?= $done ? 'Kích hoạt thành công!' : 'Máy đang được bảo hành' ?></h1>
            <p class="am-public-text">Chủ máy: <b><?= Html::encode($warranty['custName']) ?></b><br>
                Kích hoạt: <?= Html::encode($warranty['activatedAt']) ?><br>
                Bảo hành đến: <b style="color:#059669;font-size:16px"><?= Html::encode($warranty['expires']) ?></b></p>
            <p class="am-public-text" style="font-size:12px;color:#6b7280">Khi máy gặp sự cố, gọi hotline
                <a href="tel:<?= preg_replace('/\s/', '', D::HOTLINE) ?>"><b><?= D::HOTLINE ?></b></a> và cung cấp số serial phía trên.</p>
        </div>
    <?php else: ?>
        <div class="am-public-card">
            <h1 class="am-public-title">Kích hoạt bảo hành <?= D::WARRANTY_MONTHS ?> tháng</h1>
            <p class="am-public-text" style="margin-bottom:14px">Điền thông tin chủ máy để kích hoạt. Thời hạn bảo hành tính từ hôm nay.</p>
            <?= Html::beginForm(['agrimac/activate', 'token' => $warranty['qr']], 'post', ['novalidate' => true]) ?>
                <div class="<?= $cls('name') ?>" style="margin-bottom:12px">
                    <label>Họ và tên chủ máy <span class="am-req">*</span></label>
                    <input class="am-input am-input-lg" name="Activate[name]" value="<?= Html::encode($form['name']) ?>" placeholder="VD: Nguyễn Văn An" autocomplete="name">
                    <?= $err('name') ?>
                </div>
                <div class="<?= $cls('phone') ?>" style="margin-bottom:12px">
                    <label>Số điện thoại <span class="am-req">*</span></label>
                    <input class="am-input am-input-lg" name="Activate[phone]" type="tel" inputmode="tel" value="<?= Html::encode($form['phone']) ?>" placeholder="VD: 0901 999 111" autocomplete="tel">
                    <?= $err('phone') ?>
                </div>
                <div class="am-field" style="margin-bottom:12px">
                    <label>Địa chỉ</label>
                    <input class="am-input am-input-lg" name="Activate[address]" value="<?= Html::encode($form['address']) ?>" placeholder="Xã/phường, huyện, tỉnh" autocomplete="street-address">
                </div>
                <div class="<?= $cls('agree') ?>" style="margin-bottom:16px">
                    <label style="display:flex;gap:8px;align-items:flex-start;font-weight:400;font-size:12px;color:#374151;cursor:pointer">
                        <input type="checkbox" name="Activate[agree]" value="1" <?= $form['agree'] === '1' ? 'checked' : '' ?> style="margin-top:2px;width:16px;height:16px">
                        Tôi đồng ý với điều khoản bảo hành và cho phép 1Kho liên hệ khi cần hỗ trợ kỹ thuật.
                    </label>
                    <?= $err('agree') ?>
                </div>
                <button type="submit" class="am-btn am-dark-gradient" style="width:100%;padding:13px;font-size:15px">🛡 Kích hoạt bảo hành</button>
            <?= Html::endForm() ?>
        </div>
    <?php endif; ?>
<?php endif; ?>
