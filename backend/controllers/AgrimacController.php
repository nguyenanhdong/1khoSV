<?php

namespace backend\controllers;

use Yii;
use yii\db\Query;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\FileHelper;
use yii\web\Response;
use yii\web\UploadedFile;
use yii\web\Controller;
use backend\components\AgrimacData as D;
use backend\components\AgrimacRepo as R;
use backend\components\AgrimacAuth;
use backend\components\AgrimacService;
use backend\components\AgrimacValidationException;

class AgrimacController extends Controller
{
    public $layout = 'agrimac';
    public $defaultAction = 'dashboard';

    const IMAGE_MAX_BYTES = 5 * 1024 * 1024;
    const IMAGE_TYPES = [IMAGETYPE_JPEG => 'jpg', IMAGETYPE_PNG => 'png', IMAGETYPE_WEBP => 'webp'];
    const PRODUCT_IMAGE_DIR = 'uploads/images/product';

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    ['allow' => true, 'actions' => ['activate'], 'roles' => ['?', '@']],
                    ['allow' => true, 'roles' => ['@']],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => ['upload-image' => ['POST'], 'api' => ['POST'], 'activate' => ['GET', 'POST']],
            ],
        ];
    }

    public function beforeAction($action)
    {
        if (!parent::beforeAction($action)) {
            return false;
        }
        if ($action->id === 'activate') {
            $this->layout = 'agrimac-public';
            return true;
        }

        $role = AgrimacAuth::role();
        if ($role === null) {
            Yii::$app->response->statusCode = 403;
            if ($action->id === 'api') {
                Yii::$app->response->format = Response::FORMAT_JSON;
                Yii::$app->response->data = ['status' => false, 'message' => 'Tài khoản chưa được gán vai trò trong 1Kho CMS'];
                return false;
            }
            $this->layout = 'agrimac-public';
            Yii::$app->response->content = $this->render('no-role');
            return false;
        }

        $actionPages = ['accounting-export' => 'accounting', 'upload-image' => 'products', 'api' => 'dashboard', 'lookup' => 'dashboard'];
        $page = $actionPages[$action->id] ?? $action->id;
        if (!D::canAccess($role, $page)) {
            $this->redirect(['dashboard']);
            return false;
        }

        $this->view->params['amRole'] = $role;
        $this->view->params['amCanSwitchRole'] = AgrimacAuth::isSuperAdmin();
        $this->view->params['amPage'] = $page;
        return true;
    }

    /** Endpoint ghi dữ liệu duy nhất: POST op + data (JSON). */
    public function actionApi()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $request = Yii::$app->request;
        $op = (string)$request->post('op', '');
        $data = $request->post('data', []);
        $service = new AgrimacService(Yii::$app->user->id, $this->view->params['amRole']);
        if (!isset(AgrimacService::OPS[$op])) {
            Yii::$app->response->statusCode = 400;
            return ['status' => false, 'message' => 'Thao tác không hợp lệ'];
        }
        if (!$service->can($op)) {
            Yii::$app->response->statusCode = 403;
            return ['status' => false, 'message' => 'Vai trò ' . D::ROLES[$this->view->params['amRole']]['label'] . ' không được thực hiện thao tác này'];
        }
        try {
            $result = $service->run($op, is_array($data) ? $data : []);
        } catch (AgrimacValidationException $e) {
            Yii::$app->response->statusCode = 422;
            return ['status' => false, 'message' => $e->getMessage(), 'errors' => $e->errors];
        } catch (\Throwable $e) {
            Yii::error($e, __METHOD__);
            Yii::$app->response->statusCode = 500;
            return ['status' => false, 'message' => 'Lỗi máy chủ, thao tác chưa được lưu. Vui lòng thử lại.'];
        }
        return ['status' => true, 'message' => $result['message'], 'data' => $result['data']];
    }

    /** Đọc dữ liệu có phân trang / gợi ý tìm kiếm (GET, JSON). */
    public function actionLookup($type)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $request = Yii::$app->request;
        switch ($type) {
            case 'products':
                return R::productList($request->get('q', ''), $request->get('cat', ''), (int)$request->get('page', 1), (int)$request->get('perPage', 24));
            case 'product-search':
                return ['rows' => R::productSearch($request->get('q', ''), 20)];
            case 'product':
                return ['row' => R::product($request->get('code', ''))];
            case 'market-orders':
            case 'market-order':
                if (!D::canMarketOrders($this->view->params['amRole'])) {
                    Yii::$app->response->statusCode = 403;
                    return ['status' => false, 'message' => 'Chỉ Admin được xem đơn hàng sàn'];
                }
                if ($type === 'market-order') {
                    $order = R::marketOrder((int)$request->get('id'));
                    return $order ? ['row' => $order] : ['status' => false, 'message' => 'Đơn hàng không tồn tại'];
                }
                return R::marketOrderList($request->get('q', ''), $request->get('status', ''), $request->get('payment', ''), (int)$request->get('page', 1), 20);
        }
        Yii::$app->response->statusCode = 400;
        return ['status' => false, 'message' => 'Loại dữ liệu không hợp lệ'];
    }

    public function actionDashboard()
    {
        return $this->render('dashboard', [
            'orders'     => R::orders(),
            'dealers'    => D::indexBy(R::dealers()),
            'leads'      => R::leads(AgrimacAuth::leadSaleScope()),
            'warranties' => R::warranties(),
            'claims'     => R::claims(),
            'products'   => R::lowStockProducts(),
            'lowStockCount' => R::lowStockCount(),
        ]);
    }

    public function actionCrm()
    {
        return $this->render('crm', ['leads' => R::leads(AgrimacAuth::leadSaleScope())]);
    }

    public function actionOrders()
    {
        $orders = R::orders();
        $market = D::canMarketOrders($this->view->params['amRole']) ? [
            'counts'   => R::marketOrderCounts(),
            'statuses' => D::MARKET_ORDER_STATUS,
            'payments' => D::MARKET_PAYMENT,
        ] : null;
        return $this->render('orders', [
            'orders'   => $orders,
            'products' => R::productsByCodes(array_column($orders, 'productId')),
            'market'   => $market,
        ]);
    }

    public function actionAssembly()
    {
        $orders = array_values(array_filter(R::orders(), function ($o) {
            return in_array($o['status'], ['assembling', 'assembled'], true);
        }));
        return $this->render('assembly', ['orders' => $orders]);
    }

    public function actionProducts()
    {
        return $this->render('products', [
            'page'       => R::productList('', '', 1),
            'categories' => R::categories(),
            'suppliers'  => R::suppliers(),
            'parts'      => R::parts(),
            'boms'       => R::defaultBoms(),
        ]);
    }

    /** Upload ảnh sản phẩm, trả về đường dẫn dạng /uploads/images/product/xxx.jpg (định dạng cột product.image). */
    public function actionUploadImage()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $file = UploadedFile::getInstanceByName('file');
        if ($file !== null && in_array($file->error, [UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE], true)) {
            return ['status' => false, 'message' => 'Ảnh vượt quá dung lượng cho phép (' . ini_get('upload_max_filesize') . ')'];
        }
        if ($file === null || $file->error !== UPLOAD_ERR_OK) {
            return ['status' => false, 'message' => 'Không nhận được file, vui lòng thử lại'];
        }
        if ($file->size > self::IMAGE_MAX_BYTES) {
            return ['status' => false, 'message' => 'Ảnh vượt quá 5MB'];
        }
        $info = @getimagesize($file->tempName);
        if ($info === false || !isset(self::IMAGE_TYPES[$info[2]])) {
            return ['status' => false, 'message' => 'Chỉ chấp nhận ảnh JPG, PNG hoặc WEBP'];
        }

        $dir = Yii::getAlias('@webroot/' . self::PRODUCT_IMAGE_DIR);
        FileHelper::createDirectory($dir);
        $name = time() . '-' . Yii::$app->security->generateRandomString(16) . '.' . self::IMAGE_TYPES[$info[2]];
        if (!$file->saveAs($dir . '/' . $name)) {
            return ['status' => false, 'message' => 'Không lưu được ảnh trên máy chủ'];
        }
        return [
            'status' => true,
            'url'    => Yii::getAlias('@web/' . self::PRODUCT_IMAGE_DIR . '/' . $name),
            'width'  => $info[0],
            'height' => $info[1],
        ];
    }

    /** Trang public khách quét QR trên máy để kích hoạt bảo hành (không cần đăng nhập). */
    public function actionActivate($token = '')
    {
        $warranty = R::warrantyByQr($token);
        if ($warranty === null) {
            return $this->render('activate', ['warranty' => null, 'errors' => [], 'form' => [], 'done' => false]);
        }
        $form = ['name' => '', 'phone' => '', 'address' => '', 'agree' => ''];
        $errors = [];
        $done = false;
        if (Yii::$app->request->isPost && $warranty['status'] === 'unactivated') {
            $form = array_merge($form, array_map('trim', (array)Yii::$app->request->post('Activate', [])));
            if (mb_strlen($form['name']) < 2) {
                $errors['name'] = 'Vui lòng nhập họ tên';
            }
            if (!preg_match('/^0\d{9,10}$/', preg_replace('/[.\s-]/', '', $form['phone']))) {
                $errors['phone'] = 'Số điện thoại không hợp lệ (10–11 số, bắt đầu bằng 0)';
            }
            if ($form['agree'] !== '1') {
                $errors['agree'] = 'Vui lòng đồng ý điều khoản bảo hành';
            }
            if (!$errors) {
                $row = (new Query())->from('warranty')->where(['qr_token' => (string)$token])->one();
                try {
                    (new AgrimacService(0, null))->activate($row, $form['name'], $form['phone'], date('Y-m-d'), $form['address']);
                    $done = true;
                    $warranty = R::warrantyByQr($token);
                } catch (AgrimacValidationException $e) {
                    $errors = ['name' => $e->errors['custName'] ?? null, 'phone' => $e->errors['phone'] ?? null];
                    $errors = array_filter($errors);
                }
            }
        }
        return $this->render('activate', [
            'warranty' => $warranty,
            'dealer'   => D::indexBy(R::dealers())[$warranty['dealer']] ?? null,
            'errors'   => $errors,
            'form'     => $form,
            'done'     => $done,
        ]);
    }

    public function actionInventory()
    {
        return $this->render('inventory', [
            'lowStock'       => R::lowStockProducts(),
            'stockValue'     => R::stockValue(),
            'parts'          => R::parts(),
            'partCategories' => R::partCategories(),
            'suppliers'      => R::suppliers(),
            'categories'     => R::categories(),
            'transactions'   => R::stockTransactions(),
        ]);
    }

    public function actionDealers()
    {
        return $this->render('dealers', ['dealers' => R::dealers(), 'provinces' => $this->provinces()]);
    }

    public function actionWarranty()
    {
        return $this->render('warranty', [
            'warranties' => R::warranties(),
            'claims'     => R::claims(),
        ]);
    }

    public function actionSuppliers()
    {
        return $this->render('suppliers', [
            'suppliers' => R::suppliers(),
            'products'  => R::suppliedProducts(),
            'parts'     => R::parts(),
        ]);
    }

    private function provinces()
    {
        return (new Query())->select('province_name')->from('province')->orderBy('province_name')->column();
    }

    public function actionAccounting()
    {
        return $this->render('accounting', $this->accountingData());
    }

    public function actionAccountingExport()
    {
        $data = $this->accountingData();
        $fh = fopen('php://temp', 'r+');
        fwrite($fh, "\xEF\xBB\xBF");
        fputcsv($fh, ['Mã đơn', 'Đại lý', 'Doanh thu', 'HH Sale', 'HH Giao hàng', 'Thực thu', 'Ngày']);
        foreach ($data['rows'] as $o) {
            fputcsv($fh, [
                $o['id'],
                $data['dealers'][$o['dealerId']]['name'] ?? '',
                $o['total'], $o['comSale'], $o['comDeli'],
                $o['total'] - $o['comSale'] - $o['comDeli'],
                $o['date'],
            ]);
        }
        rewind($fh);
        $csv = stream_get_contents($fh);
        fclose($fh);
        return Yii::$app->response->sendContentAsFile($csv, 'so-thu-chi-agrimac-' . date('Ymd') . '.csv', ['mimeType' => 'text/csv']);
    }

    private function accountingData()
    {
        $rows = array_values(array_filter(R::orders(), function ($o) {
            return $o['status'] === 'delivered' && $o['type'] === 'new';
        }));
        $dealers = D::indexBy(R::dealers());
        return [
            'commissions' => R::commissions(),
            'rows'        => $rows,
            'dealers'     => $dealers,
            'revenue'     => array_sum(array_column($rows, 'total')),
            'comSale'     => array_sum(array_column($rows, 'comSale')),
            'comDeli'     => array_sum(array_column($rows, 'comDeli')),
            'debt'        => array_sum(array_column($dealers, 'debt')),
            'overLimit'   => count(array_filter($dealers, function ($d) {
                return $d['debt'] > $d['limit'];
            })),
        ];
    }

    public function actionUsers()
    {
        return $this->render('users');
    }
}
