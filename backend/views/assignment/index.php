<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use yii\helpers\Json;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $searchModel mdm\admin\models\searchs\Assignment */
/* @var $usernameField string */
/* @var $extraColumns string[] */

$this->title = 'Danh sách nhân viên';
$this->params['breadcrumbs'][] = $this->title;

$userCurrent = Yii::$app->user->identity;


$listRole = [];

// AnimateAsset::register($this);
// YiiAsset::register($this);

$opts = Json::htmlEncode([
    'items' => [],
]);
$this->registerJs("var _opts = {$opts};");
$this->registerJs($this->render('_script.js'));
?>
<style type="text/css">
form .col-lg-3 {
    width: 245px;
    float: none;
    display: inline-block;
    text-align: left !important;
}
form .col-lg-2{display:inline-block}
.table-bordered td:last-child{text-align:center}
.pagination {
    text-align: center;
    width: 100%;
}
.pagination > li{display:inline-block}
.row_role > h3 {
    background-color: #E9E9E9;
    float: left;
    font-size: 18px;
    margin: 0;
    text-indent: 20px;
    width: 100%;
    padding: 10px 0;
    font-weight: 600;
}
.role_access {
    background-color: #fff;
    border: 1px dashed green;
    float: left;
    margin-bottom: 50px;
    padding: 10px 20px 10px;
    width: 100%;
}
.left_role, .right_role {
    float: left;
    width: 45%;
    margin-right: 5%;
}
.role_access h4 {
    border-bottom: 1px solid #ddd;
    font-size: 15px;
    margin: 0 0 10px;
    padding-bottom: 5px;
    font-weight: 600;
}
.list_role {
    max-height: 450px;
    min-height: 300px;
    overflow: auto;
    padding:0;
}
.list_role li:first-child{margin-top:10px}
.list_role li {
    margin-top:5px;
    display: inline-block;
    width: 100%;
}
.label_move {
    display: inline-block;
    font-size: 13px !important;
    width: auto;
    cursor:pointer;
}
.label_move:hover{color:#0096D7}
tr.disabled{opacity:0.7}
</style>
<div class="assignment-index">
    
    
    <?php
      if (Yii::$app->session->hasFlash('message')){
          $msg      = Yii::$app->session->getFlash('message');
          echo '<div class="alert alert-success">
                    ' . $msg . '
                </div>';
          Yii::$app->session->setFlash('message',null);
      }

    ?>
    <p>
        <?= Html::a('Thêm mới' , 'create', ['class' => 'btn btn-success btn_new_account']) ?>
    </p>
    
    <?php Pjax::begin(); ?>
    <?=
    GridView::widget([
        'dataProvider' => $dataProvider,
        // 'filterModel' => $searchModel,
        'summary' => '<p>Tổng <strong>{totalCount}</strong> tài khoản</p>',
        'rowOptions' => function($model){
            if( $model['is_active'] == 1 )
                return ['class'=>'disabled', 'id' => 'tr-user' . $model->id];
            else
                return ['id' => 'tr-user' . $model->id];
        },
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
                [
                    'attribute' => 'id',
                    'label'=>'Mã tài khoản',
                    'contentOptions'=> ['class'=>'text-center'],
                    'headerOptions' => ['class'=>'text-center']
                ],
                [
                    'attribute' => 'username',
                    'label'=>'Tài khoản',
                    // 'contentOptions'=> ['class'=>'text-center'],
                    // 'headerOptions' => ['class'=>'text-center']
                ],
                [
                    'attribute' => 'username',
                    'label'=>'Tên',
                    // 'contentOptions'=> ['class'=>'text-center'],
                    // 'headerOptions' => ['class'=>'text-center']
                ],
                [
                    'label'=>'Nhóm quyền',
                    'value' => function($model) use ($dataRole,$listRole){
                        // var_dump($model);
                        if( isset($model->is_admin) ){
                            if( $model->is_admin == 1 ){
                                return 'All quyền';
                            }
                            else{
                                return isset($dataRole[$model->id]) ? $dataRole[$model->id] : '';
                            }
                        }else if( isset($model->roleid) )
                            return isset($listRole[$model->roleid]) ? $listRole[$model->roleid] : '';
                        else
                            return 'N/A';
                    }
                    // 'contentOptions'=> ['class'=>'text-center'],
                    // 'headerOptions' => ['class'=>'text-center']
                ],
                [
                    'label'=>'Trạng thái tài khoản',
                    'value' => function($model){
                        return $model['is_active'] == 0 ? 'active' : 'inactive';
                    },
                    'contentOptions'=> ['class'=>'text-center status-user'],
                    'headerOptions' => ['class'=>'text-center']
                    // 'contentOptions'=> ['class'=>'text-center'],
                    // 'headerOptions' => ['class'=>'text-center']
                ],
                [
                    'label'=>'Banned/UnBanned',
                    'format' => 'raw',
                    'value' => function($model){
                        return '<input type="checkbox" class="checkbox_banned" value="' . $model['id'] . '" />';
                    },
                    'contentOptions'=> ['class'=>'text-center'],
                    'headerOptions' => ['class'=>'text-center']
                    // 'contentOptions'=> ['class'=>'text-center'],
                    // 'headerOptions' => ['class'=>'text-center']
                ],
                ['class' => 'yii\grid\ActionColumn',
				'template' => '{permission}{view}{update}{delete}',
				'buttons' => [
                    'permission' => function ($model, $url) use ($searchModel) {
                        if( $searchModel->type_account == 1 )
                            return '<a class="permission" href="/assignment/getpermisstion" title="Cấp quyền" name="' . $url->username . '" id="' . $url['id'] . '"><i class="fal fa-list"></i></a>';
                        else
                            return '';
                    },
					'view' => function ($model, $url) use ($userCurrent,$searchModel) {
						return '<a title="Xem chi tiết tài khoản" style="margin:0 0 0 8px" href="/assignment/view?id=' . $url['id'] . '&type=' . $searchModel->type_account . '"><i class="fal fa-search"></i></a>';
                    },
                    'update' => function ($model, $url) use ($userCurrent,$searchModel) {
						return '<a title="Cập nhật tài khoản" style="margin:0 8px" href="/assignment/update?id=' . $url['id'] . '&type=' . $searchModel->type_account . '"><i class="fal fa-pencil"></i></a>';
                    },
                    'delete' => function ($model, $url) use ($userCurrent,$searchModel) {
                        $username = $url->username;

						return '<a class="btn_banned" title="Banned tài khoản" dtname="' . $username . '" dtid="' . $url['id'] . '" href="javascript:;"><i class="fal fa-ban"></i></a>';
					}
				],
				]
        ]
    ]);
    ?>
    <?php Pjax::end(); ?>
    <div class="row_button" style="text-align:right">
        <button class="btn btn-primary btn-change-status">Banned/UnBanned</button>
    </div>
    <div class="row_role" style="display:none;position: relative; float: left; width: 100%;">
        <h3 class="title_form">Cập nhật quyền cho tài khoản <span class="sp_assign"></span></h3>
        <a href="javascript:;" style="position:absolute;top:10px;right:15px" class="fal fa-close hide_role"></a>
        <div class="role_access">
            <div class="left_role">
                <h4>Danh sách nhóm quyền</h4>
                <input class="form-control search" data-target="available" placeholder="<?='Tìm kiếm nhóm quyền'?>">
                <ul class="list_role" data-target="available">
                </ul>
            </div>
            <ul class="right_role">
                <h4>Nhóm quyền được truy cập</h4>
                <input class="form-control search" data-target="assigned" placeholder="<?='Tìm kiếm nhóm quyền'?>">
                <ul class="list_role" data-target="assigned">
                </ul>
            </div>
        </div>
    </div>
</div>
