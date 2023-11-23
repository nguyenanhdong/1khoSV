<?php

use yii\helpers\Html;
use yii\grid\GridView;
use mdm\admin\components\RouteRule;
use mdm\admin\components\Configs;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $searchModel mdm\admin\models\searchs\AuthItem */
/* @var $context mdm\admin\components\ItemController */

$context        = $this->context;
$labels         = $context->labels();
$name           = $labels['Items'];
$controller     = Yii::$app->controller->id;
$this->title    = $labels['Items'];
$this->params['breadcrumbs'][] = 'Cấu hình';
$this->params['breadcrumbs'][] = $this->title;
$this->params['breadcrumbs']['description_page'] = 'Quản lý danh sách các nhóm quyền';

?>

<style type="text/css">
.table-bordered td:last-child{text-align:center}
a{color:#337ab7}
</style>
<div class="card mb-g">
    <div class="card-body">
        <p>
            <?= Html::a('Thêm ', ['create'], ['class' => 'btn btn-success']) ?>
        </p>
        <?=
        GridView::widget([
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'columns' => [
                ['class' => 'yii\grid\SerialColumn'],
                [
                    'attribute' => 'name',
                    'label' => 'Tên ' . strtolower($labels['Items']),
                    'format' => 'raw',
                    'value' => function($model) use ($controller){
                        return '<a title="Xem chi tiết" href="/' . $controller . '/view?id=' . str_replace(' ', '+',$model->name) . '">' . $model->name . '</a>';
                    }
                ],

                [
                    'attribute' => 'description',
                    'label' => "Mô tả",
                ],
                ['class' => 'yii\grid\ActionColumn',
                'template' => '{view}{update}{delete}',
                'buttons' => [
                    'view' => function ($model, $url) use ($controller) {
                        return '<a title="Xem chi tiết" href="/' . $controller . '/view?id=' . $url->name . '"><i class="fal fa-search"></i></a>';
                    },
                    'update' => function ($model, $url) use ($controller)  {
                        return '<a title="Cập nhật" style="margin:0 8px" href="/' . $controller . '/update?id=' . $url->name . '"><i class="fal fa-pencil"></i></a>';
                    },
                    'delete' => function ($model, $url) use ($name, $controller){
                        return '<a title="Xoá" onclick="return confirm(\'Bạn có chắc chắn muốn xoá ' . $name . ' này?\')" href="/' . $controller . '/delete?id=' . $url->name . '"><i class="fal fa-trash"></i></a>';
                    }
                ],
                ],
            ],
        ])
        ?>
    </div>
</div>
