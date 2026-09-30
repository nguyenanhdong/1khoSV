<?php

namespace backend\controllers;

use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use backend\models\Config;

/**
 * Nội dung & liên hệ của web/app 1Kho (bảng config): thông tin liên hệ, tài khoản nhận chuyển khoản,
 * lý do trả hàng và các trang nội dung hiển thị ở footer web.
 */
class SiteContentController extends Controller
{
    /** key => [nhãn, loại] (html / contact / bank / lines) */
    const ITEMS = [
        'CONTACT'           => ['Thông tin liên hệ', 'contact'],
        'BANK_PAYMENT'      => ['Tài khoản nhận chuyển khoản', 'bank'],
        'LIST_REASON_REFUN' => ['Lý do trả hàng / hoàn tiền', 'lines'],
        'INTRODUCTION'      => ['Giới thiệu 1Kho', 'html'],
        'BUY_GUIDE'         => ['Hướng dẫn mua hàng', 'html'],
        'PAYMENT_GUIDE'     => ['Hình thức thanh toán', 'html'],
        'SHIPPING_POLICY'   => ['Chính sách vận chuyển', 'html'],
        'RETURN_POLICY'     => ['Chính sách trả hàng & hoàn tiền', 'html'],
        'WARRANTY_POLICY'   => ['Chính sách bảo hành', 'html'],
        'PRIVACY_POLICY'    => ['Chính sách bảo mật', 'html'],
        'TERMS_OF_USE'      => ['Điều khoản sử dụng', 'html'],
        'RECRUITMENT'       => ['Tuyển dụng', 'html'],
    ];

    /** key trang nội dung => slug URL trên web (khớp frontend SiteController::PAGES) */
    const PAGE_SLUGS = [
        'INTRODUCTION' => 'gioi-thieu', 'BUY_GUIDE' => 'huong-dan-mua-hang', 'PAYMENT_GUIDE' => 'thanh-toan',
        'SHIPPING_POLICY' => 'van-chuyen', 'RETURN_POLICY' => 'chinh-sach-doi-tra', 'WARRANTY_POLICY' => 'chinh-sach-bao-hanh',
        'PRIVACY_POLICY' => 'chinh-sach-bao-mat', 'TERMS_OF_USE' => 'dieu-khoan', 'RECRUITMENT' => 'tuyen-dung',
    ];

    const CONTACT_FIELDS = [
        'name' => 'Tên công ty', 'address' => 'Địa chỉ', 'phone' => 'Hotline', 'email' => 'Email',
        'facebook' => 'Facebook', 'zalo' => 'Zalo', 'shopee' => 'Shopee',
        'app_android' => 'Link tải app Android', 'app_ios' => 'Link tải app iOS',
    ];
    const BANK_FIELDS = ['ten_bank' => 'Ngân hàng', 'stk' => 'Số tài khoản', 'ten_tk' => 'Chủ tài khoản'];

    public function actionIndex()
    {
        $rows = Config::find()->where(['key' => array_keys(self::ITEMS)])->indexBy('key')->all();
        return $this->render('index', ['rows' => $rows]);
    }

    public function actionUpdate($key)
    {
        if (!isset(self::ITEMS[$key])) {
            throw new NotFoundHttpException('Mục cấu hình không tồn tại');
        }
        [$label, $kind] = self::ITEMS[$key];
        $model = Config::findOne(['key' => $key]);
        if (!$model) {
            $model = new Config(['key' => $key, 'name' => $label, 'type' => $kind === 'html' ? Config::TYPE_HTML : Config::TYPE_JSON, 'value' => '']);
            $model->description = $label;
        }
        $current = $kind === 'html' ? (string)$model->value : (json_decode((string)$model->value, true) ?: []);
        $errors = [];

        if (Yii::$app->request->isPost) {
            $post = Yii::$app->request->post('v', []);
            if ($kind === 'html') {
                $value = trim((string)($post['html'] ?? ''));
                if (mb_strlen($value) > 60000) {
                    $errors['html'] = 'Nội dung quá dài (tối đa 60.000 ký tự)';
                }
            } elseif ($kind === 'lines') {
                $lines = array_values(array_filter(array_map('trim', preg_split('/\R/u', (string)($post['lines'] ?? '')))));
                if (!$lines) {
                    $errors['lines'] = 'Cần ít nhất một lý do';
                }
                $value = json_encode(array_combine(range(1, max(1, count($lines))), $lines ?: ['']), JSON_UNESCAPED_UNICODE);
            } else {
                $fields = $kind === 'contact' ? self::CONTACT_FIELDS : self::BANK_FIELDS;
                $data = [];
                foreach ($fields as $f => $l) {
                    $data[$f] = mb_substr(trim((string)($post[$f] ?? '')), 0, 300);
                }
                if ($kind === 'contact' && $data['email'] !== '' && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                    $errors['email'] = 'Email không hợp lệ';
                }
                if ($kind === 'contact' && $data['phone'] !== '' && !preg_match('/^[0-9 .+()-]{8,20}$/', $data['phone'])) {
                    $errors['phone'] = 'Số điện thoại không hợp lệ';
                }
                foreach (['app_android', 'app_ios'] as $f) {
                    if (!empty($data[$f]) && !preg_match('#^https?://#i', $data[$f])) {
                        $errors[$f] = 'Link phải bắt đầu bằng http:// hoặc https://';
                    }
                }
                $value = json_encode($data, JSON_UNESCAPED_UNICODE);
                $current = $data;
            }
            if (!$errors) {
                $model->value = $value;
                $model->save(false);
                Yii::$app->session->setFlash('success', 'Đã lưu "' . $label . '"');
                return $this->redirect(['index']);
            }
            if ($kind === 'html') {
                $current = $value;
            } elseif ($kind === 'lines') {
                $current = $lines;
            }
        }

        return $this->render('update', [
            'key' => $key, 'label' => $label, 'kind' => $kind, 'current' => $current, 'errors' => $errors,
        ]);
    }
}
