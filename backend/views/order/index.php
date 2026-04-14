<?php

use yii\helpers\Html;
use yii\grid\GridView;
use backend\models\Category;
use backend\models\Customer;
use backend\models\User;
use yii\helpers\ArrayHelper;

$this->title = 'Đơn hàng';
$this->params['breadcrumbs'][] = $this->title;
$this->params['breadcrumbs']['description_page'] = 'Quản lý đơn hàng';
$controller = Yii::$app->controller->id;
$this->params['breadcrumbs']['icon_page'] = 'fa-tag';
?>
<div class="projects-index">

    <?php
    if (Yii::$app->session->hasFlash('message')) {
        $msg      = Yii::$app->session->getFlash('message');
        echo '<div class="alert alert-success">
                    <i class="fal fa-check"></i> ' . $msg . '
                </div>';
        Yii::$app->session->setFlash('message', null);
    }
    ?>

    <?php echo $this->render('_search', ['model' => $searchModel]); ?>
    
    <div class="card mb-g">
        <div class="card-body table-responsive">
            <?= GridView::widget([
                'dataProvider' => $dataProvider,
                'layout'=> "{summary}\n{items}\n<div class='page-navigation'>{pager}</div>",
                'columns' => [
                    ['class' => 'yii\grid\SerialColumn','header' => 'No.','contentOptions' => array('style' => 'width:70px')],

                    [
                        'label' => 'Tên khách hàng',
                        'attribute' => 'user_id',
                        'value' => function ($model) {
                            return Customer::findOne($model->user_id)->fullname ?? '';
                        },
                        'contentOptions' => ['style'=>'width:200px'],
                        'enableSorting' => false,
                    ],
                    [
                        'label' => 'SĐT',
                        'attribute' => 'user_id',
                        'value' => function ($model) {
                            return Customer::findOne($model->user_id)->phone ?? '';
                        },
                        'contentOptions' => ['style'=>'width:200px'],
                        'enableSorting' => false,
                    ],
                    [
                        'label' => 'Tổng giá gốc',
                        'attribute' => 'price',
                        'value' => function ($model) {
                            return number_format($model->price);
                        },
                        'contentOptions' => ['style'=>'width:200px'],
                        'enableSorting' => false,
                    ],
                    [
                        'label' => 'Phí vận chuyển',
                        'attribute' => 'fee_ship',
                        'value' => function ($model) {
                            return number_format($model->fee_ship);
                        },
                        'contentOptions' => ['style'=>'width:200px'],
                        'enableSorting' => false,
                    ],
                    [
                        'label' => 'Tổng tiền đơn hàng',
                        'attribute' => 'total_price',
                         'value' => function ($model) {
                            return number_format($model->total_price);
                        },
                        'contentOptions' => ['style'=>'width:200px'],
                        'enableSorting' => false,
                    ],
                    [
                        'label' => 'Phương thức thanh toán',
                        'attribute' => 'type_payment',
                        'value' => function ($model) {
                            return isset(Yii::$app->params['type_payment'][$model->type_payment]) ? Yii::$app->params['type_payment'][$model->type_payment] : '';
                        },
                        'contentOptions' => array('style' => 'width:150px')
                    ],                    
                    [
                        'label' => 'Trạng thái',
                        'attribute' => 'type_payment',
                        'value' => function ($model) {
                            return isset(Yii::$app->params['status_order'][$model->status]) ? Yii::$app->params['status_order'][$model->status] : '';
                        },
                        'contentOptions' => array('style' => 'width:150px')
                    ],                    
                    // [
                    //     'label' => 'Đại lý',
                    //     'attribute' => 'agent_id',
                    //      'value' => function ($model) {
                    //         return $model->agent_id;
                    //     },
                    //     'contentOptions' => ['style'=>'width:200px'],
                    //     'enableSorting' => false,
                    // ],                    
                    [
                        'label' => 'Ngày tạo',
                        'attribute' => 'create_at',
                        'value' => function ($model) {
                            return date('H:i:s d-m-Y', strtotime($model->create_at));
                        },
                        'contentOptions' => array('style' => 'width:150px')
                    ],
                    ['class' => 'yii\grid\ActionColumn',
                    'header' => 'Hành động',
                        'template' => '{view}',
                        'buttons' => [
                            'view' => function ($model, $url) use ($controller) {
                                return '<a class="btn btn-primary" title="' . Yii::t('app', 'Xem chi tiết') . '" style="margin:0 8px 0 0" href="/' . $controller . '/update?id=' . $url->id . '">' . Yii::t('app', 'Xem chi tiết') . '</a>';
                            },
                            // 'delete' => function ($model, $url) use ($controller) {
                            //     return '<a class="btn btn-danger" title="' . Yii::t('app', 'Xóa') . '" onclick="return confirm(\'' . Yii::t('app', 'Bạn có chắc chắn muốn xóa order?') . '\')" href="/' . $controller . '/delete?id=' . $url->id . '">' . Yii::t('app', 'Xóa') . '</a>';
                            // }
                        ],
                    ],
                ],
            ]); ?>
        </div>
    </div>
</div>
