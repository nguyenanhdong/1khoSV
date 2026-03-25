<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use yii\grid\GridView;
/* @var $this yii\web\View */
/* @var $searchModel backend\models\CategoryTagsSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Chuyên mục';
$this->params['breadcrumbs'][] = $this->title;
$this->params['breadcrumbs']['description_page'] = 'Quản lý chuyên mục';
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
                        'attribute' => 'show_in_header',
                        'format' => 'raw',
                        'value' => function ($model) {
                            return $model->show_in_header == 1 ? 'Hiển thị' : 'Ẩn';
                        },
                        'contentOptions' => array('style' => 'width:250px')
                    ],
                    [
                        'attribute' => 'show_in_home',
                        'format' => 'raw',
                        'value' => function ($model) {
                            return $model->show_in_home == 1 ? 'Hiển thị' : 'Ẩn';
                        },
                        'contentOptions' => array('style' => 'width:250px')
                    ],
                    [
                        'attribute' => 'sort_order',
                        'format' => 'raw',
                        'value' => function ($model) {
                            return !empty($model->sort_order) ? $model->sort_order : '';
                        },
                        'contentOptions' => array('style' => 'width:250px')
                    ],
                    [
                        'attribute' => 'home_position',
                        'format' => 'raw',
                        'value' => function ($model) {
                            return !empty($model->home_position) ? $model->home_position : '';
                        },
                        'contentOptions' => array('style' => 'width:250px')
                    ],
                    [
                        'header' => 'Action',
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
                ),
            ]); ?>
        </div>
    </div>
</div>
