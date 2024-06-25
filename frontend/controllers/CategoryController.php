<?php

namespace frontend\controllers;

use Yii;
use backend\controllers\ApiNewController;
use backend\controllers\CommonController;
use backend\models\Category;
use backend\models\Product;
use backend\models\ProductSale;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\web\Controller;

/**
 * Category controller
 */
class CategoryController extends Controller
{
    public function actionIndex($cate_parent_id)
    {
        $category = ApiNewController::CategoryDetail();
        return $this->render('index', [
            'category' => isset($category['data']) ? $category['data'] : []
        ]);
    }
    public function actionGetProductCategory()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $cate_parent_id = Yii::$app->request->post('cate_parent_id', '');
        $cate_child_id      = Yii::$app->request->post('cate_child_id', '');
        $page               = !empty(Yii::$app->request->post('page')) ? Yii::$app->request->post('page') : 0;
        $sort               = Yii::$app->request->post('sort', 'popular');
        $response['data'] = '';
        $response['checkLoadMore'] = false;
        $response['append'] = false;

        if (!empty($page)) {
            $response['append'] = true;
        }
        
        $limit = 10;
        $offset = $page * $limit;

        if (!empty($cate_parent_id)) {
            $listCateChild  = Category::getAllChildByParentId($cate_parent_id);
            $listCateIdProd = [-1];
            if (!empty($listCateChild)) {
                $listCateIdProd = ArrayHelper::map($listCateChild, 'id', 'id');
            }
        }

        if (!empty($cate_child_id)) {
            $listCateIdProd = [$cate_child_id];
        }
  
        $listProduct    = Product::getProductByCategory($listCateIdProd, $sort, $limit, $offset);
        $offsetCheck = $limit + $offset + 1;
        $checkLoadMore = !empty(Product::getProductByCategory($listCateIdProd, $sort, 1, $offsetCheck)) ? true : false;

        $item = '';
        if (!empty($listProduct)) {
            foreach ($listProduct as $row) {
                $url = Url::to(['/product/detail', 'id' => $row['id']]);
                $item .= '<div class="product_item">
                                <a href="' . $url . '">
                                    <span class="prod_sale">' . $row['percent_discount'] . '% <br> OFF</span>
                                    <img class="prod_avatar" src="' . $row['image'] . '" alt="Image product">
                                    <div class="prod_price_star">
                                        <p class="prod_title line_2" title="' . $row['name'] . '">' . $row['name'] . '</p>
                                        <div class="des_prod mt-2">
                                            <span>' . HelperController::formatPrice($row['price']) . '</span>
                                            <div class="flex-center">
                                                <img src="/images/icon/star.svg" alt="">
                                                <p class="product_star">' . $row['star'] . ' (' . $row['total_rate'] . ')</p>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            </div>';
            }
        }
        $response['data'] = $item;
        $response['checkLoadMore'] = $checkLoadMore;

        return $response;
    }
    public function actionGetProductCategoryChild()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $productItem = '';
        if (isset($_POST['catId'])) {
            $catId = $_POST['catId'];
            $products = Product::getProductByCategory([$catId]);
            if (!empty($products)) {
                foreach ($products as $row) {
                    $url = Url::to(['/product/detail', 'id' => $row['id']]);
                    $productItem .= '<div class="product_item">
                                        <a href="' . $url . '">
                                            <span class="prod_sale">' . $row['percent_discount'] . '% <br> OFF</span>
                                            <img class="prod_avatar" src="' . $row['image'] . '" alt="Image product">
                                            <div class="prod_price_star">
                                                <p class="prod_title line_2" title="' . $row['name'] . '">' . $row['name'] . '</p>
                                                <div class="des_prod mt-2">
                                                    <span>' . HelperController::formatPrice($row['price']) . '</span>
                                                    <div class="flex-center">
                                                        <img src="/images/icon/star.svg" alt="">
                                                        <p class="product_star">' . $row['star'] . ' (' . $row['total_rate'] . ')</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </a>
                                    </div>';
                }
            }
        }
        return $productItem;
    }

    public function actionIndexSale()
    {
        $limit = 12;
        $offsetCheck = $limit + 1;
        $productSale = ProductSale::getProductSale([], 0, $limit, 0);
        $checkLoadMore = ProductSale::getProductSale([], 0, 1, $offsetCheck);

        return $this->render('index_sale', [
            'productSale' => $productSale,
            'checkLoadMore' => $checkLoadMore
        ]);
    }

    public function actionGetProductSale()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $response['data'] = '';
        $response['checkLoadMore'] = false;
        if (isset($_POST['page_sale'])) {
            $page_sale = $_POST['page_sale'];
            $limit = 12;
            $offset         = $page_sale * $limit;
            $offsetCheck = $limit + $offset + 1;
            $productSale = ProductSale::getProductSale([], 0, $limit, $offset);
            $checkLoadMore = !empty(ProductSale::getProductSale([], 0, 1, $offsetCheck)) ? true : false;
            $item = '';
            if (!empty($productSale)) {
                foreach ($productSale as $row) {
                    $url = Url::to(['/product/detail', 'id' => $row['id']]);
                    $item .= '<div class="sale_list_item">
                                <a href="' . $url . '">
                                    <span class="num_sale">-' . $row['percent_discount'] . '%</span>
                                    <div class="flex-center flex-column">
                                        <img class="sale_prod_avatar" src="' . $row['image'] . '" alt="Product sale">
                                        <p class="title_prod line_2" title="' . $row['name'] . '">' . $row['name'] . '</p>
                                    </div>
                                    <div class="sale_text">
                                        <div>
                                            <p>' . HelperController::formatPrice($row['price']) . '</p>
                                            <span>' . HelperController::formatPrice($row['price_old']) . '</span>
                                        </div>
                                        <p>Kết thúc sau <strong>' . $row['date_sale_remain'] . ' ngày</strong></p>
                                        <p>Chỉ còn <strong>' . $row['quantity_in_stock'] . ' sản phẩm</strong></p>
                                    </div>
                                </a>
                            </div>';
                }
            }
            $response['data'] = $item;
            $response['checkLoadMore'] = $checkLoadMore;
        }
        return $response;
    }
}
