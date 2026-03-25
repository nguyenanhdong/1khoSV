<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use yii\grid\GridView;
/* @var $this yii\web\View */
/* @var $searchModel backend\models\CategoryTagsSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Sản phẩm';
$this->params['breadcrumbs'][] = $this->title;
$this->params['breadcrumbs']['description_page'] = 'Quản lý Sản phẩm';
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
                'columns' => array(
                    [
                        'class' => 'yii\grid\SerialColumn',
                        'header' => Yii::t('app', 'No.'),
                        'contentOptions' => array('style' => 'width:70px')
                    ],
                    [
                        'attribute' => 'image',
                        'format' => 'raw',
                        'value' => function ($model) {
                            return '<img class="img-grid" src="' . $model->image . '"/>';
                        },
                        'contentOptions' => array('style' => 'width:200px')
                    ],
                    [
                        'attribute' => 'name',
                        'format' => 'raw',
                        'value' => function ($model) {
                            return $model->name;
                        },
                        // 'contentOptions' => array('style' => 'width:150px')
                    ],
                    [
                        'attribute' => 'status',
                        'format' => 'raw',
                        'value' => function ($model) {
                            return $model->status == 1 ? 'Hiển thị' : 'Ẩn';
                        },
                        'contentOptions' => array('style' => 'width:250px')
                    ],
                    [
                        'attribute' => 'price',
                        'format' => 'raw',
                        'value' => function ($model) {
                            return number_format($model->price);
                        },
                    ],                    
                    [
                        'attribute' => 'quantity_in_stock',
                        'format' => 'raw',
                        'value' => function ($model) {
                            return number_format($model->quantity_in_stock);
                        },
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
                        'contentOptions' => ['style' => 'width:230px;max-width:230px'],
                    ],
                )
            ]); ?>
        </div>
    </div>
</div>
