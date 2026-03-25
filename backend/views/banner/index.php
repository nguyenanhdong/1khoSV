<?php

use yii\helpers\Html;
use yii\grid\GridView;
use backend\models\Category;
use yii\helpers\ArrayHelper;

$this->title = 'Banner';
$this->params['breadcrumbs'][] = $this->title;
$this->params['breadcrumbs']['description_page'] = 'Quản lý banner';
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
                        'attribute' => 'image',
                        'format' => 'raw',
                        'value' => function ($data) {
                            if(!empty($data->image)){
                                return '<img class="img-grid" src="'.$data->image.'"/>';
                            }
                            return '';
                        },
                        'contentOptions' => ['style'=>'width:200px'],
                        'enableSorting' => false,
                    ],
                    // [
                    //     'label'=>'Image wap',
                    //     'format' => 'raw',
                    //     'value' => function ($data) {
                    //         if(!empty($data->image_path_wap)){
                    //             return '<img class="img-grid" src="'.$data->image_path_wap.'"/>';
                    //         }
                    //         return '';
                    //     },
                    //     'contentOptions' => ['style'=>'width:200px'],
                    //     'enableSorting' => false,
                    // ],
                    [
                        'attribute' => 'name',
                        'format' => 'raw',
                        'value' => function ($model, $url) use ($controller) {
                            if (!empty($data->name)) {
                                return ;
                            }
                            return '<a name="' . $model->name . '" class="child-content" href="/' . $controller . '/update?id=' . $model->id . '">' . $model->name . '</a>';
                        },
                        'contentOptions' => array('style' => 'width:200px;max-width: 200px')
                    ],
                    [
                        'attribute' => 'description',
                        'format' => 'raw',
                        'contentOptions' => array('style' => 'width:200px;max-width: 200px'),
                        'value' => function ($model) {
                            return !empty($model->description) ? '<p class="child-content">'. $model->description .'</p>' : ''; 
                        },
                    ],
                    // [
                    //     'attribute' => 'type',
                    //     'value' => function ($model) {
                    //         if(!empty($model->type))
                    //             return Yii::$app->params['type_banner'][$model->type];
                    //     },
                    // ],
                    // [
                    //     'attribute' => 'is_show_button',
                    //     'value' => function ($model) {
                    //         return $model->is_show_button == 1 ? 'ON' : 'OFF';
                    //     },
                    // ],
                    [
                        'attribute' => 'type',
                        'value' => function ($model) {
                            return isset(Yii::$app->params['banner_type'][$model->type]) ? Yii::$app->params['banner_type'][$model->type] : '';
                        },
                        'contentOptions' => array('style' => 'width:150px')
                    ],                    
                    [
                        'attribute' => 'date_start',
                        'value' => function ($model) {
                            return date('H:i:s d-m-Y', strtotime($model->date_start));
                        },
                        'contentOptions' => array('style' => 'width:150px')
                    ],
                    [
                        'attribute' => 'date_end',
                        'value' => function ($model) {
                            return date('H:i:s d-m-Y', strtotime($model->date_end));
                        },
                        'contentOptions' => array('style' => 'width:150px')
                    ],
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
                        'template' => '{update}{delete}',
                        'buttons' => [
                            'update' => function ($model, $url) use ($controller) {
                                return '<a class="btn btn-primary" title="' . Yii::t('app', 'Cập nhật') . '" style="margin:0 8px 0 0" href="/' . $controller . '/update?id=' . $url->id . '">' . Yii::t('app', 'Cập nhật') . '</a>';
                            },
                            'delete' => function ($model, $url) use ($controller) {
                                return '<a class="btn btn-danger" title="' . Yii::t('app', 'Xóa') . '" onclick="return confirm(\'' . Yii::t('app', 'Bạn có chắc chắn muốn xóa banner?') . '\')" href="/' . $controller . '/delete?id=' . $url->id . '">' . Yii::t('app', 'Xóa') . '</a>';
                            }
                        ],
                    ],
                ],
            ]); ?>
        </div>
    </div>
</div>
