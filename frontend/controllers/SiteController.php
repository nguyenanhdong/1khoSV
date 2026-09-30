<?php
namespace frontend\controllers;

use backend\controllers\ApiNewController;
use backend\models\Config;
use common\models\Users;
use frontend\components\SiteInfo;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * Site controller
 */
class SiteController extends Controller
{
    /**
     * Trang nội dung: slug URL => [khoá config, tiêu đề]. Nội dung sửa trong CMS → Cấu hình nội dung
     * (dùng chung với app mobile). Trang chưa có nội dung sẽ báo "đang cập nhật" và bị ẩn khỏi footer.
     */
    const PAGES = [
        'gioi-thieu'          => ['INTRODUCTION', 'Giới thiệu 1Kho'],
        'huong-dan-mua-hang'  => ['BUY_GUIDE', 'Hướng dẫn mua hàng'],
        'thanh-toan'          => ['PAYMENT_GUIDE', 'Hình thức thanh toán'],
        'van-chuyen'          => ['SHIPPING_POLICY', 'Chính sách vận chuyển'],
        'chinh-sach-doi-tra'  => ['RETURN_POLICY', 'Chính sách trả hàng & hoàn tiền'],
        'chinh-sach-bao-hanh' => ['WARRANTY_POLICY', 'Chính sách bảo hành'],
        'chinh-sach-bao-mat'  => ['PRIVACY_POLICY', 'Chính sách bảo mật'],
        'dieu-khoan'          => ['TERMS_OF_USE', 'Điều khoản sử dụng'],
        'tuyen-dung'          => ['RECRUITMENT', 'Tuyển dụng'],
    ];

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only' => ['logout'],
                'rules' => [
                    ['actions' => ['logout'], 'allow' => true, 'roles' => ['@']],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => ['logout' => ['post', 'get']],
            ],
        ];
    }

    public function actions()
    {
        return [
            'error' => ['class' => 'yii\web\ErrorAction'],
        ];
    }

    public function actionIndex()
    {
        $dataHome = ApiNewController::Home();
        return $this->render('index', [
            'dataHome' => isset($dataHome['data']) ? $dataHome['data'] : []
        ]);
    }

    /* ---------------- Trang nội dung ---------------- */

    /** Nội dung HTML của trang (rỗng nếu chưa nhập trong CMS). */
    public static function pageContent($slug)
    {
        if (!isset(self::PAGES[$slug])) {
            return '';
        }
        return trim((string)Config::getConfigApp(self::PAGES[$slug][0]));
    }

    public function actionPage($slug)
    {
        if (!isset(self::PAGES[$slug])) {
            throw new NotFoundHttpException('Trang không tồn tại');
        }
        [$key, $title] = self::PAGES[$slug];
        $this->view->title = $title . ' - 1Kho';
        $this->view->registerMetaTag(['name' => 'description', 'content' => $title . ' tại sàn thương mại 1Kho']);
        return $this->render('page', [
            'slug'    => $slug,
            'title'   => $title,
            'content' => self::pageContent($slug),
        ]);
    }

    /* Đường dẫn cũ → trang nội dung mới */
    public function actionAbout()         { return $this->redirect(['/site/page', 'slug' => 'gioi-thieu'], 301); }
    public function actionReturnPolicy()  { return $this->redirect(['/site/page', 'slug' => 'chinh-sach-doi-tra'], 301); }
    public function actionPrivacyPolicy() { return $this->redirect(['/site/page', 'slug' => 'chinh-sach-bao-mat'], 301); }
    public function actionGuarantee()     { return $this->redirect(['/site/page', 'slug' => 'chinh-sach-bao-hanh'], 301); }

    public function actionContact($topic = '')
    {
        $this->view->title = ($topic === 'seller' ? 'Bán hàng cùng 1Kho' : 'Liên hệ') . ' - 1Kho';
        return $this->render('contact', ['contact' => SiteInfo::contact(), 'seller' => $topic === 'seller']);
    }

    /* ---------------- Đăng nhập (Firebase: OTP số điện thoại, Google, Facebook) ---------------- */

    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }
        if (!Yii::$app->request->isPost) {
            return $this->render('login');
        }
        Yii::$app->response->format = Response::FORMAT_JSON;
        $type  = (string)Yii::$app->request->post('type', '');
        $token = (string)Yii::$app->request->post('token', '');
        if ($type !== 'idToken' || $token === '') {
            return ['status' => false, 'message' => 'Thông tin đăng nhập không hợp lệ'];
        }
        $data = self::_validateIdTokenFireBase($token);
        if (!$data['status']) {
            return ['status' => false, 'message' => 'Xác thực thất bại, vui lòng thử lại'];
        }
        $info = $data['data'];

        if ($info['phone'] !== '') {
            $user = Users::findOne(['phone' => $info['phone']]) ?: Users::findOne(['phone' => '+84' . substr($info['phone'], 1)]);
        } elseif ($info['provider'] === 'google.com') {
            $user = Users::findOne(['gg_id' => $info['uid']]);
        } elseif ($info['provider'] === 'facebook.com') {
            $user = Users::findOne(['fb_id' => $info['uid']]);
        } else {
            return ['status' => false, 'message' => 'Phương thức đăng nhập không được hỗ trợ'];
        }

        if ($user && (int)$user->status !== Users::STATUS_ACTIVE) {
            return ['status' => false, 'message' => 'Tài khoản đã bị khoá. Vui lòng liên hệ ' . (SiteInfo::phone() ?: '1Kho') . ' để được hỗ trợ'];
        }
        if (!$user) {
            $user = new Users();
            $user->phone             = $info['phone'] !== '' ? $info['phone'] : null;
            $user->gg_id             = $info['provider'] === 'google.com' ? $info['uid'] : null;
            $user->fb_id             = $info['provider'] === 'facebook.com' ? $info['uid'] : null;
            $user->fullname          = $info['name'] !== '' ? $info['name'] : null;
            $user->avatar            = $info['avatar'] !== '' ? $info['avatar'] : null;
            $user->status            = Users::STATUS_ACTIVE;
            $user->is_verify_account = Users::ACCOUNT_VERIFYED;
            $user->create_at         = date('Y-m-d H:i:s');
            if (!$user->save(false)) {
                return ['status' => false, 'message' => 'Không tạo được tài khoản, vui lòng thử lại'];
            }
        }
        $user->last_login = date('Y-m-d H:i:s');
        $user->save(false);
        if (!Yii::$app->user->login($user, 3600 * 24 * 30)) {
            return ['status' => false, 'message' => 'Đăng nhập thất bại, vui lòng thử lại'];
        }
        return ['status' => true, 'message' => 'Đăng nhập thành công', 'redirect' => Yii::$app->user->getReturnUrl('/')];
    }

    /** Số Firebase dạng +84xxxxxxxxx → 0xxxxxxxxx (định dạng lưu trong bảng user). */
    public static function normalizePhone($phone)
    {
        $phone = preg_replace('/[^0-9+]/', '', (string)$phone);
        if (strpos($phone, '+84') === 0) {
            return '0' . substr($phone, 3);
        }
        return ltrim($phone, '+');
    }

    /**
     * Xác thực idToken Firebase qua REST API. Trả về phone (đã chuẩn hoá), provider (phone / google.com / facebook.com),
     * uid Firebase (khớp gg_id / fb_id mà app mobile đang lưu), tên và ảnh đại diện.
     */
    public static function _validateIdTokenFireBase($idToken)
    {
        $fail = ['status' => false, 'message' => 'Error! Authentication failed'];
        $apiKey = Yii::$app->params['fireBase']['login']['apiKey'] ?? '';
        if ($apiKey === '') {
            return $fail;
        }
        $ch = curl_init('https://identitytoolkit.googleapis.com/v1/accounts:lookup?key=' . $apiKey);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_POSTFIELDS     => json_encode(['idToken' => $idToken]),
        ]);
        $result = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($status !== 200) {
            return $fail;
        }
        $data = json_decode($result, true);
        $info = $data['users'][0] ?? null;
        if (!is_array($info) || empty($info['localId'])) {
            return $fail;
        }
        $provider = 'phone';
        foreach ($info['providerUserInfo'] ?? [] as $p) {
            if (in_array($p['providerId'] ?? '', ['google.com', 'facebook.com'], true)) {
                $provider = $p['providerId'];
                break;
            }
        }
        $phone = !empty($info['phoneNumber']) ? self::normalizePhone($info['phoneNumber']) : '';
        if ($phone === '' && $provider === 'phone') {
            return $fail;
        }
        return ['status' => true, 'data' => [
            'uid'      => (string)$info['localId'],
            'provider' => $phone !== '' ? 'phone' : $provider,
            'phone'    => $phone,
            'name'     => trim((string)($info['displayName'] ?? '')),
            'avatar'   => (string)($info['photoUrl'] ?? ''),
        ]];
    }

    public function actionLogout()
    {
        Yii::$app->user->logout();
        return $this->goHome();
    }
}
