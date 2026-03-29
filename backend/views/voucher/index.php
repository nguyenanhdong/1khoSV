<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use yii\grid\GridView;
/* @var $this yii\web\View */
/* @var $searchModel backend\models\CategoryTagsSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Voucher';
$this->params['breadcrumbs'][] = $this->title;
$this->params['breadcrumbs']['description_page'] = 'Quản lý voucher';
$controller = Yii::$app->controller->id;
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
                'layout' => "{summary}\n{items}\n<div class='page-navigation'>{pager}</div>",
                // [['','','','','max_price_by_percent','minimum_order','total_maximum_use','maximum_use_by_user','date_start','date_end','agent_id','product_id'], 'safe'],
                'columns' => array(
                    [
                        'class' => 'yii\grid\SerialColumn',
                        'header' => Yii::t('app', 'No.'),
                        'contentOptions' => array('style' => 'width:70px')
                    ],
                    [
                        'attribute' => 'name',
                        'format' => 'raw',
                        'value' => function ($model) use ($controller) {
                            return '<a class="child-content" title="' . $model->name . '" href="/' . $controller . '/update?id=' . $model->id . '">' . $model->name . '</a>';
                        },
                        'contentOptions' => array('style' => 'width:200px')
                    ],
                    [
                        'attribute' => 'type_voucher',
                        'format' => 'raw',
                        'value' => function ($model) {
                            return isset(Yii::$app->params['type_voucher'][$model->type_voucher]) ? Yii::$app->params['type_voucher'][$model->type_voucher] : '';
                        },
                        'contentOptions' => array('style' => 'width:150px')
                    ],
                    [
                        'attribute' => 'type_price',
                        'format' => 'raw',
                        'value' => function ($model) {
                            return isset(Yii::$app->params['type_price'][$model->type_price]) ? Yii::$app->params['type_price'][$model->type_price] : '';
                        },
                        'contentOptions' => array('style' => 'width:150px')
                    ],
                    [
                        'attribute' => 'price',
                        'format' => 'raw',
                        'value' => function ($model) {
                            return number_format($model->price);
                        },
                        'contentOptions' => array('style' => 'width:200px')
                    ],
                    [
                        'attribute' => 'date_start',
                        'value' => function ($model) {
                            return date('d-m-Y', strtotime($model->date_start));
                        },
                        'contentOptions' => array('style' => 'width:150px')
                    ],                    
                    [
                        'attribute' => 'date_end',
                        'value' => function ($model) {
                            return date('d-m-Y', strtotime($model->date_end));
                        },
                        'contentOptions' => array('style' => 'width:150px')
                    ],                    
                    [
                        'header' => 'Hành động',
                        'class' => 'yii\grid\ActionColumn',
                        'template' => '{update}{delete}',
                        'buttons' => [
                            'update' => function ($model, $url) use ($controller) {
                                return '<a class="btn btn-primary" title="' . Yii::t('app', 'Cập nhật') . '" style="margin:0 8px 0 0" href="/' . $controller . '/update?id=' . $url->id . '">' . Yii::t('app', 'Cập nhật') . '</a>';
                            },
                            'delete' => function ($model, $url) use ($controller) {
                                return '<a class="btn btn-danger" title="' . Yii::t('app', 'Xóa') . '" onclick="return confirm(\'' . Yii::t('app', 'Bạn có chắc chắn muốn xóa chuyên mục?') . '\')" href="/' . $controller . '/delete?id=' . $url->id . '">' . Yii::t('app', 'Xóa') . '</a>';
                            }
                        ],
                        'contentOptions' => ['style' => 'width:170px;max-width:170px'],
                    ],
                )
            ]); ?>
        </div>
    </div>
</div>
