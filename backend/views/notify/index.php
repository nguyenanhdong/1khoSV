<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use yii\grid\GridView;
/* @var $this yii\web\View */
/* @var $searchModel backend\models\CategoryTagsSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Thông báo';
$this->params['breadcrumbs'][] = $this->title;
$this->params['breadcrumbs']['description_page'] = 'Quản lý Thông báo';
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
                        'attribute' => 'title',
                        'format' => 'raw',
                        'value' => function ($model) use ($controller) {
                            return '<a class="child-content" title="' . $model->title . '" href="/' . $controller . '/update?id=' . $model->id . '">' . $model->title . '</a>';
                        },
                        'contentOptions' => array('style' => 'width:200px')
                    ],
                    [
                        'attribute' => 'description',
                        'format' => 'raw',
                        'value' => function ($model) use ($controller) {
                            return '<div class="child-content" title="' . $model->description . '">' . $model->description . '</div>';
                        },
                        'contentOptions' => array('style' => 'width:300px')
                    ],
                    [
                        'attribute' => 'user_notify',
                        'format' => 'raw',
                        'value' => function ($model) {
                            return isset(Yii::$app->params['user_notify'][$model->user_notify]) ? Yii::$app->params['user_notify'][$model->user_notify] : '';
                        },
                        'contentOptions' => array('style' => 'width:200px')
                    ],
                    [
                        'label' => 'Ngày tạo',
                        'attribute' => 'create_at',
                        'value' => function ($model) {
                            return date('H:i:s d-m-Y', strtotime($model->create_at));
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
