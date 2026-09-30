<?php
namespace frontend\controllers;

use backend\controllers\ApiNewController;
use backend\models\Agent;
use backend\models\Category;
use backend\models\Product;
use backend\models\ProductReview;
use backend\models\UserFavouriteProduct;
use backend\models\UserViewProduct;
use Yii;
use yii\helpers\Url;
use yii\web\Controller;

/**
 * Product controller
 */
class ProductController extends Controller
{
    const SEARCH_PAGE_SIZE = 20;
    const SEARCH_SORTS = ['popular', 'best-selling', 'new', 'price_asc', 'price_desc'];

    private static function keyword($value)
    {
        return mb_substr(trim((string)$value), 0, 100);
    }

    /** Gợi ý khi gõ ở ô tìm kiếm header (GET q) */
    public function actionSuggest()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $q = self::keyword(Yii::$app->request->get('q'));
        if (mb_strlen($q) < 2) {
            return ['items' => [], 'total' => 0];
        }
        $items = array_map(function ($row) {
            return [
                'id'       => (int)$row['id'],
                'name'     => $row['name'],
                'image'    => $row['image'],
                'price'    => HelperController::formatPrice($row['price']),
                'priceOld' => $row['percent_discount'] > 0 ? HelperController::formatPrice($row['price_old']) : null,
                'url'      => Url::to(['/product/detail', 'id' => $row['id']]),
            ];
        }, Product::searchProducts($q, 'popular', 8));
        return ['items' => $items, 'total' => Product::countSearchProducts($q), 'moreUrl' => Url::to(['/product/search', 'q' => $q])];
    }

    /** Trang kết quả tìm kiếm */
    public function actionSearch($q = '')
    {
        $q = self::keyword($q);
        return $this->render('search', [
            'q'        => $q,
            'total'    => Product::countSearchProducts($q),
            'products' => Product::searchProducts($q, 'popular', self::SEARCH_PAGE_SIZE, 0),
            'pageSize' => self::SEARCH_PAGE_SIZE,
        ]);
    }

    /** Ajax: đổi sắp xếp / Xem thêm trên trang kết quả tìm kiếm */
    public function actionGetProductSearch()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $request = Yii::$app->request;
        $q = self::keyword($request->get('q'));
        $sort = in_array($request->get('sort'), self::SEARCH_SORTS, true) ? $request->get('sort') : 'popular';
        $page = max(0, (int)$request->get('page', 0));
        $offset = $page * self::SEARCH_PAGE_SIZE;
        $products = Product::searchProducts($q, $sort, self::SEARCH_PAGE_SIZE, $offset);
        $html = '';
        foreach ($products as $row) {
            $html .= $this->renderPartial('_item', ['prod' => $row]);
        }
        return ['data' => $html, 'hasMore' => $offset + count($products) < Product::countSearchProducts($q)];
    }

    //Chi tiết sản phẩm
    public function behaviors()
    {
        return [
            'login' => [
                'class' => \frontend\components\LoginRequired::class,
                'only'  => ['toggle-favourite', 'toggle-follow', 'get-product-review', 'get-product-seen', 'get-product-favourite'],
            ],
        ];
    }

    public function actionDetail($id){
        $userId = Yii::$app->user->isGuest ? 0 : (int)Yii::$app->user->id;
        $product = Product::getProductDetail((int)$id, $userId);
        if (!$product) {
            throw new \yii\web\NotFoundHttpException('Sản phẩm không tồn tại hoặc đã ngừng bán');
        }
        if ($userId) {
            UserViewProduct::saveViewProduct($userId, $id);
        }
        $model = Product::findOne((int)$id);
        $categories = [];
        if ($model && $model->category_id) {
            $cat = Category::findOne(['id' => $model->category_id, 'is_delete' => 0, 'status' => 1]);
            $parent = $cat && $cat->parent_id ? Category::findOne(['id' => $cat->parent_id, 'is_delete' => 0, 'status' => 1]) : null;
            if ($parent) {
                $categories[] = ['name' => $parent->name, 'url' => ['/category/index', 'cate_parent_id' => $parent->id]];
                $categories[] = ['name' => $cat->name, 'url' => ['/category/index', 'cate_parent_id' => $parent->id, 'cate_child_id' => $cat->id]];
            } elseif ($cat) {
                $categories[] = ['name' => $cat->name, 'url' => ['/category/index', 'cate_parent_id' => $cat->id]];
            }
        }
        $this->view->title = $product['product_info']['name'] . ' - 1Kho';
        $this->view->registerMetaTag(['property' => 'og:image', 'content' => $product['product_info']['images'][0] ?? '']);
        return $this->render('detail-product',[
            'product'    => $product,
            'categories' => $categories,
        ]);
    }

    /** Thả tim / bỏ thích sản phẩm (ajax) */
    public function actionToggleFavourite()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $productId = (int)Yii::$app->request->post('productId');
        if (!Product::find()->where(['id' => $productId, 'status' => Product::STATUS_ACTIVE])->exists()) {
            return ['status' => false, 'message' => 'Sản phẩm không tồn tại'];
        }
        $liked = UserFavouriteProduct::toggleFavourites(Yii::$app->user->id, $productId);
        return ['status' => true, 'liked' => $liked, 'message' => $liked ? 'Đã thêm vào Sản phẩm yêu thích' : 'Đã bỏ khỏi Sản phẩm yêu thích'];
    }

    /** Theo dõi / bỏ theo dõi shop (ajax) */
    public function actionToggleFollow()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $agent = Agent::findOne((int)Yii::$app->request->post('agentId'));
        if (!$agent) {
            return ['status' => false, 'message' => 'Shop không tồn tại'];
        }
        $following = \backend\models\UserFollowAgent::toggleFollowAgent(Yii::$app->user->id, $agent->id);
        $agent->updateCounters(['follow_count' => $following ? 1 : ($agent->follow_count > 0 ? -1 : 0)]);
        return ['status' => true, 'following' => $following, 'total' => (int)$agent->follow_count];
    }

    //lấy giá sản phẩm khi chọn phân loại
    public function actionGetPrice(){
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $productId = Yii::$app->request->post('productId', '');
        $arrOptionId = Yii::$app->request->post('arrOptionId', '');
        $data['classification_id'] = '';
        $data['price'] = '';
        if(!empty($productId)){
            $product = Product::getProductDetail($productId);
            $data['price'] = HelperController::formatPrice($product['product_info']['price']);
            $classificationData = $product['product_info']['classification_data'];
            if(!empty($classificationData)){
                foreach ($classificationData as $row) {
                    if($row['classification_id'] == $arrOptionId){
                        $data['price'] = HelperController::formatPrice($row['price']);
                        $data['classification_id'] = $row['id'];
                    }
                }
            }
        }
        return $data;
    }

    public function actionGetProductReview(){
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $limit          = 10;
        $page           = Yii::$app->request->post('page', 1);
        $type           = Yii::$app->request->post('type');
        $offset         = $page * $limit;
        $offsetCheck = $limit + $offset;
        
        $response['data'] = '';
        $response['checkLoadMore'] = false;
        $userId = Yii::$app->user->identity->id;

        if($type == 'not-review'){
            $dataNotReview = ProductReview::getListReviewOfUser($userId, 0, $limit, $offset);
            $response['checkLoadMore'] = !empty(ProductReview::getListReviewOfUser($userId, 0, 1, $offsetCheck)) ? true : false;
            if(!empty($dataNotReview)){
                $item = '';
                foreach($dataNotReview as $row){
                    $rating = '';
                    for ($i = 0; $i < 5; $i++) {
                        if ($i < $row['product_star']) { 
                            $rating .= '<img src="/images/icon/star-active.svg" alt="">';
                            } else { 
                                $rating .= '<img src="/images/icon/star-inactive.svg" alt="">';
                            } 
                    }
                    $item .= '<div class="group_item_shop px-0 d-flex flex-column">
                            <div class="item_shop">
                                <div class="item_shop_left d-flex flex-column">
                                    <div class="desc_item">
                                        <div class="flex-center avatar_pro">
                                            <img src=" '. $row['product_img'] .'" alt="">
                                        </div>
                                        <div class="text_desc d-flex flex-column">
                                            <p>'. $row['product_name'] .'</p>
                                            <div class="flex-item-center">
                                                <strong>'. HelperController::formatPrice($row['price']) .'</strong>
                                                <span>-'. $row['percent_discount'] .'%</span>
                                            </div>
                                            <div class="flex-item-center justify-content-between">
                                                <p>Số lượng '. $row['quantity'] .'</p>
                                                <div class="rating_product flex-item-center">
                                                    '. $rating .'
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="item_shop_right d-flex flex-column">
                                    <div class="btn_item">
                                        <div class="action_form">
                                            <button class="btn_action btn-blue flex-center" data-toggle="modal" data-target="#modalReview'. $row['order_id'] .'">Đánh giá</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal fade" id="modalReview'. $row['order_id'] .'" tabindex="-1" role="dialog" aria-labelledby="modaReviewTitle" aria-hidden="true">
                                <div class="modal-dialog" role="document">
                                    <div class="modal-content modal_content_review">
                                        <div class="modal-header">
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <div class="modal-body">
                                            <h2>ĐÁNH GIÁ SẢN PHẨM</h2>
                                            <div class="product_review desc_item">
                                                <div class="flex-center avatar_pro">
                                                    <img src="'. $row['product_img'] .'" alt="">
                                                </div>
                                                <div class="product_review_detail">
                                                    <p>'. $row['product_name'] .'</p>
                                                    <div class="flex-item-center text_desc">
                                                        <strong>'. HelperController::formatPrice($row['price']) .'</strong>
                                                        <span>-'. $row['percent_discount'] .'%</span>
                                                    </div>
                                                    <p>Số lượng '. $row['quantity'] .'</p>
                                                </div>
                                            </div>
                                            <div class="form_review">
                                                <label>Chất lượng sản phẩm <span class="color_red">*</span></label>

                                                <div id="group-stars">
                                                    <div class="rating-group">
                                                        <input  disabled checked class="rating__input rating__input--none rating_'. $row['order_id'] .'" name="rating'. $row['order_id'] .'" id="rating'. $row['order_id'] .'-none" value="0" type="radio">
                                                        <label aria-label="1 star" class="rating__label" for="rating'. $row['order_id'] .'-1"><i class="rating__icon rating__icon--star fa fa-star"></i></label>
                                                        <input  class="rating__input rating_'. $row['order_id'] .'" name="rating'. $row['order_id'] .'" id="rating'. $row['order_id'] .'-1" value="1" type="radio">
                                                        <label aria-label="2 stars" class="rating__label" for="rating'. $row['order_id'] .'-2"><i class="rating__icon rating__icon--star fa fa-star"></i></label>
                                                        <input class="rating__input rating_'. $row['order_id'] .'" name="rating'. $row['order_id'] .'" id="rating'. $row['order_id'] .'-2" value="2" type="radio">
                                                        <label aria-label="3 stars" class="rating__label" for="rating'. $row['order_id'] .'-3"><i class="rating__icon rating__icon--star fa fa-star"></i></label>
                                                        <input  class="rating__input rating_'. $row['order_id'] .'" name="rating'. $row['order_id'] .'" id="rating'. $row['order_id'] .'-3" value="3" type="radio">
                                                        <label aria-label="4 stars" class="rating__label" for="rating'. $row['order_id'] .'-4"><i class="rating__icon rating__icon--star fa fa-star"></i></label>
                                                        <input  class="rating__input rating_'. $row['order_id'] .'" name="rating'. $row['order_id'] .'" id="rating'. $row['order_id'] .'-4" value="4" type="radio">
                                                        <label aria-label="5 stars" class="rating__label" for="rating'. $row['order_id'] .'-5"><i class="rating__icon rating__icon--star fa fa-star"></i></label>
                                                        <input  class="rating__input rating_'. $row['order_id'] .'" name="rating'. $row['order_id'] .'" id="rating'. $row['order_id'] .'-5" value="5" type="radio">
                                                    </div>
                                                </div>

                                            </div>
                                            <div class="form-group">
                                                <div class="box-image flex-center flex-column">
                                                    <div class="form-group field-fileInput">
                                                        <input type="hidden" name="Advertisement[image][]" value=""><input class="fileInput" type="file" id="fileInput_'. $row['order_id'] .'" order-id="'. $row['order_id'] .'" name="Advertisement[image][]" multiple="" accept="image/*,video/*">
                                                        <div class="help-block"></div>
                                                    </div> <img src="/images/icon/icon-img.svg" alt="">
                                                    <label for="">Chọn 3 video ngắn dưới 1 phút + 8 hình ảnh</label>
                                                    <i class="error">Dung lượng ảnh tối đa 1MB, dung lượng video tối đa 20MB</i>
                                                </div>
                                                <div class="preview-box" id="previewBox_'. $row['order_id'] .'"></div>
                                            </div>
                                            <div class="form-group">
                                                <label for="">Đánh giá <span class="color_red">*</span></label>
                                                <textarea rows="5" name="" id="content_'. $row['order_id'] .'"></textarea>
                                            </div>
                                            <div class="form-group">
                                                <button type="button" pro-id="'. $row['product_id'] .'" od-id="'. $row['order_id'] .'" class="btn_action btn_submit_review btn-orange flex-center">Gửi đánh giá</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>';
                }
                $response['data'] = $item;
            }
        }else if($type = 'reviewed'){
            $dataReviewd = ProductReview::getListReviewOfUser($userId, 1, $limit, $offset);
            $response['checkLoadMore'] = !empty(ProductReview::getListReviewOfUser($userId, 1, 1, $offsetCheck)) ? true : false;
            if(!empty($dataReviewd)){
                $item = '';
                foreach($dataReviewd as $row){
                    $avatar = !empty($row['avatar']) ? $row['avatar'] : '/images/icon/user-icon.svg';
                    $elementNav = '';
                    $elementFor = '';
                    if(!empty($row['video_image'])){
                        foreach($row['video_image'] as $link){ 
                            if(strpos($link, 'mp4') !== false){
                                $elementNav .= '<div class="item_slide slide_nav position-relative">
                                                <video class="img_slide_nav" width="640" height="360">
                                                    <source src="'. $link .'" type="video/mp4">
                                                </video>
                                                <div class="icon_play flex-center"><img src="/images/icon/play.svg"></div>
                                            </div>';
                                $elementFor .= '<div class="item_slide item_for">
                                                    <video class="" width="640" height="360" controls>
                                                        <source src="'. $link .'" type="video/mp4">
                                                    </video>
                                                </div>';
                            }else{
                                $elementNav .= '<div class="item_slide slide_nav"><img class="img_slide_nav" src="'. $link .'"></div>';
                                $elementFor .= '<div class="item_slide item_for"><img class="" src="'. $link .'"></div>';
                            }
                        }
                    }
                    $item .= '<div class="comment_item">
                                    <img class="comment_avatar" src="'. $avatar .'" alt="">
                                    <div class="comment_group_right">
                                        <div class="user_name flex-item-center">
                                            <p>'. $row['fullname'] .'</p>
                                            <span>•</span>
                                            <span>'. $row['date_review'] .'</span>
                                        </div>
                                        <p>'. $row['content'] .'</p>
                                        <div class="video_image_comment slider-comment-nav">
                                            '. $elementNav .'
                                        </div>
                                        <div class="video_image_comment slider-comment-for hide">
                                            '. $elementFor .'
                                        </div>
                                    </div>
                                </div>';
                }
                $response['data'] = $item;
            }
        }
      
        return $response;
    }

    public function actionGetProductSeen(){
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $limit          = 10;
        $page           = Yii::$app->request->post('page', 1);
        $offset         = $page * $limit;
        $offsetCheck = $limit + $offset;
        
        $response['data'] = '';
        $userId = Yii::$app->user->identity->id;

        $data = UserViewProduct::getListProductView($userId, $limit, $offset);
        $response['checkLoadMore'] = !empty(UserViewProduct::getListProductView($userId, 1, $offsetCheck)) ? true : false;
        if(!empty($data)){
            $item = '';
            foreach($data as $row){
                $rating = '';
                for ($i = 0; $i < 5; $i++) {
                    if ($i < $row['star']) { 
                        $rating .= '<img src="/images/icon/star-active.svg" alt="">';
                        } else { 
                            $rating .= '<img src="/images/icon/star-inactive.svg" alt="">';
                        } 
                }
                $item .= '<div class="group_item_shop d-flex flex-column">
                            <div class="item_shop">
                                <div class="item_shop_left d-flex flex-column">
                                    <div class="desc_item">
                                        <div class="flex-center avatar_pro">
                                            <img src="'. $row['image'] .'" alt="">
                                        </div>
                                        <div class="text_desc d-flex flex-column">
                                            <p>'. $row['name'] .'</p>
                                            <div class="flex-item-center">
                                                <strong>'. HelperController::formatPrice($row['price']) .'</strong>
                                                <span>-'. $row['percent_discount'] .'%</span>
                                            </div>
                                            <div class="flex-item-center justify-content-between">
                                                <p>Số lượng '. $row['quantity_sold'] .'</p>
                                                <div class="rating_product flex-item-center">
                                                    '. $rating .'
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="item_shop_right d-flex flex-column">
                                    <div class="btn_item">
                                        <div class="action_viewd">
                                            <button type="button" class="btn_favourite btn_favourite_sm '. (UserFavouriteProduct::checkStatusUserFavourites($userId, $row['id']) ? 'active' : '') .'" data-product="'. (int)$row['id'] .'" title="Yêu thích"><img src="/images/icon/'. (UserFavouriteProduct::checkStatusUserFavourites($userId, $row['id']) ? 'heart-active' : 'heart-inactive') .'.svg" alt=""></button>
                                            <a target="_blank" href="'. Url::to(['/product/detail', 'id' => $row['id']]) .'" class="btn_action btn-blue flex-center">Xem chi tiết</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>';
            }
            $response['data'] = $item;
        }
        return $response;
    }
    public function actionGetProductFavourite(){
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $limit          = 10;
        $page           = Yii::$app->request->post('page', 1);
        $offset         = $page * $limit;
        $offsetCheck = $limit + $offset;
        
        $response['data'] = '';
        $userId = Yii::$app->user->identity->id;

        $data = UserFavouriteProduct::getListProductFavourites($userId, $limit, $offset);
        $response['checkLoadMore'] = !empty(UserFavouriteProduct::getListProductFavourites($userId, 1, $offsetCheck)) ? true : false;
        if(!empty($data)){
            $item = '';
            foreach($data as $row){
                $rating = '';
                for ($i = 0; $i < 5; $i++) {
                    if ($i < $row['star']) { 
                        $rating .= '<img src="/images/icon/star-active.svg" alt="">';
                        } else { 
                            $rating .= '<img src="/images/icon/star-inactive.svg" alt="">';
                        } 
                }
                $item .= '<div class="group_item_shop d-flex flex-column">
                            <div class="item_shop">
                                <div class="item_shop_left d-flex flex-column">
                                    <div class="desc_item">
                                        <div class="flex-center avatar_pro">
                                            <img src="'. $row['image'] .'" alt="">
                                        </div>
                                        <div class="text_desc d-flex flex-column">
                                            <p>'. $row['name'] .'</p>
                                            <div class="flex-item-center">
                                                <strong>'. HelperController::formatPrice($row['price']) .'</strong>
                                                <span>-'. $row['percent_discount'] .'%</span>
                                            </div>
                                            <div class="flex-item-center justify-content-between">
                                                <p>Số lượng '. $row['quantity_sold'] .'</p>
                                                <div class="rating_product flex-item-center">
                                                    '. $rating .'
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="item_shop_right d-flex flex-column">
                                    <div class="btn_item">
                                        <div class="action_viewd">
                                            <button type="button" class="btn_favourite btn_favourite_sm active" data-product="'. (int)$row['id'] .'" data-remove-card="1" title="Bỏ yêu thích"><img src="/images/icon/heart-active.svg" alt=""></button>
                                            <a target="_blank" href="'. Url::to(['/product/detail', 'id' => $row['id']]) .'" class="btn_action btn-blue flex-center">Xem chi tiết</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>';
            }
            $response['data'] = $item;
        }
        return $response;
    }

    public function actionViewMoreReview(){
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $product_id     = Yii::$app->request->post('product_id', '');
        $limit          = 3;
        $page           = Yii::$app->request->post('page', 1);
        $offset         = ($page - 1) * $limit;
        $offsetCheck = $limit + $offset;
        $response['data'] = '';
        $response['checkLoadMore'] = false;
        if(!empty($product_id)){
            $dataRes        = ProductReview::getReviewByProductId($product_id, $limit, $offset);
            $response['checkLoadMore'] = !empty(ProductReview::getReviewByProductId($product_id, 1, $offsetCheck)) ? true : false;
            if (!empty($dataRes)) {
                $item = '';
                foreach($dataRes as $row) {
                    $elementNav = '';
                    $elementFor = '';
                    if(!empty($row['video_image'])){
                        foreach($row['video_image'] as $link){ 
                            $elementNav .= '<div class="item_slide slide_nav"><img class="img_slide_nav" src="'. $link .'"></div>';
                            $elementFor .= '<div class="item_slide item_for"><img class="" src="'. $link .'"></div>';
                            if(strpos($link, 'mp4') !== false){
                                $elementNav .= '<div class="item_slide slide_nav position-relative">
                                                <video class="img_slide_nav" width="640" height="360">
                                                    <source src="'. $link .'" type="video/mp4">
                                                </video>
                                                <div class="icon_play flex-center"><img src="/images/icon/play.svg"></div>
                                            </div>';
                                $elementFor .= '<div class="item_slide item_for">
                                                    <video class="" width="640" height="360" controls>
                                                        <source src="'. $link .'" type="video/mp4">
                                                    </video>
                                                </div>';
                            }

                        }
                    }
                    $avatar = !empty($row['avatar']) ? $row['avatar'] : '/images/icon/user-icon.svg';
                    $item .= '<div class="comment_item">
                                <img class="comment_avatar" src="'. $avatar .'" alt="">
                                <div class="comment_group_right">
                                    <div class="user_name flex-item-center">
                                        <p>'. $row['fullname'] .'</p>
                                        <span>•</span>
                                        <span>'. $row['date_review'] .'</span>
                                    </div>
                                    <p>'. $row['content'] .'</p>
                                    <div class="video_image_comment slider-comment-nav">
                                        '. $elementNav .'
                                    </div>
                                    <div class="video_image_comment slider-comment-for hide">
                                        '. $elementFor .'
                                    </div>
                                </div>
                            </div>';
                }
                $response['data'] = $item;
            }
        }
        return $response;
    }

    //Thông tin shop
    public function actionShop($id){
        $agentData = ApiNewController::AgentHome();
        if (empty($agentData['data']['agentInfo'])) {
            throw new \yii\web\NotFoundHttpException('Shop không tồn tại');
        }
        $data = $agentData['data'];
        $userId = Yii::$app->user->isGuest ? 0 : (int)Yii::$app->user->id;
        $this->view->title = $data['agentInfo']['name'] . ' - 1Kho';
        return $this->render('shop',[
            'data'      => $data,
            'shopId'    => (int)$id,
            'following' => $userId ? \backend\models\UserFollowAgent::checkUserFollowAgent($userId, (int)$id) : false,
            'rating'    => Product::agentRating((int)$id),
            'total'     => Product::countSearchProducts('', ['agent_id' => (int)$id]),
            'pageSize'  => 10,
        ]);
    }

    /** Ajax: sản phẩm của shop theo từ khoá / chuyên mục / sắp xếp / trang */
    public function actionGetProductShop()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $request = Yii::$app->request;
        $shopId  = (int)$request->post('shop_id');
        $page    = max(0, (int)$request->post('page', 0));
        $sort    = in_array($request->post('sort'), self::SEARCH_SORTS, true) ? $request->post('sort') : 'popular';
        $q       = self::keyword($request->post('q'));
        $catId   = (int)$request->post('cate_id');
        $filter  = ['agent_id' => $shopId ?: -1];
        if ($catId > 0) {
            $filter['category_ids'] = array_merge([$catId], array_map('intval', array_column(Category::getAllChildByParentId($catId), 'id')));
        }
        $limit   = 10;
        $offset  = $page * $limit;
        $rows    = Product::searchProducts($q, $sort, $limit, $offset, $filter);
        $total   = Product::countSearchProducts($q, $filter);
        $html    = '';
        foreach ($rows as $row) {
            $html .= $this->renderPartial('_item', ['prod' => $row]);
        }
        if ($html === '' && $page === 0) {
            $html = '<div class="search_empty w-100">' . ($q !== '' ? 'Không tìm thấy sản phẩm phù hợp trong shop' : 'Shop chưa có sản phẩm') . '</div>';
        }
        return ['data' => $html, 'append' => $page > 0, 'checkLoadMore' => $offset + count($rows) < $total, 'total' => $total];
    }

}
