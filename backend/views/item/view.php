<?php

use mdm\admin\AnimateAsset;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\web\YiiAsset;
use yii\widgets\DetailView;
use backend\models\Assignment;

/* @var $this yii\web\View */
/* @var $model mdm\admin\models\AuthItem */
/* @var $context mdm\admin\components\ItemController */

$context = $this->context;
$labels = $context->labels();
$this->title = $labels['Items'] . ' ' . $model->name;
$this->params['breadcrumbs'][] = 'Cấu hình';
$this->params['breadcrumbs'][] = ['label' => $labels['Items'], 'url' => ['index']];
$this->params['breadcrumbs'][] = $model->name;

$listItems = $model->getItems();
$listControllerAction = [];
$listRole  = [];
if( isset($listItems['available']) ){
    $listControllerAction = array_keys($listItems['available'], $type);
    $listControllerAction = array_fill_keys($listControllerAction, false);
}

if( isset($listItems['assigned']) ){
    $listControllerAssign = array_keys($listItems['assigned'], $type);
    $listControllerAction = array_merge(array_fill_keys($listControllerAssign, true), $listControllerAction);
}

if( $type == 'route' )
    $listControllerAction     = $model->explodeController($listControllerAction);
else{
    $resultRole = (new Assignment(0))->getItems($model->name,true);
    // var_dump($resultRole);die;
    if( isset($resultRole['available']) && !empty($resultRole['available']) ){
        $listRole = array_fill_keys(array_keys($resultRole['available'], 'role'),false);
    }
    
    if( isset($resultRole['assigned']) && !empty($resultRole['assigned']) ){
        $listRoleAssign = array_fill_keys(array_keys($resultRole['assigned'], 'role'),true);
        $listRole = array_merge($listRoleAssign, $listRole);
    }
    if( isset($listRole[$model->name]) )
        unset($listRole[$model->name]);
}

AnimateAsset::register($this);
YiiAsset::register($this);
$opts = Json::htmlEncode([
    'items' => $model->getItems(),
]);
$this->registerJs("var _opts = {$opts};");
$this->registerJs($this->render('_script.js'));
?>
<style type="text/css">
    label{font-weight:400}
    li.li-action {
    display: inline-block;
    width: 49%;
    font-size: 14px;
}
.list-action {
    list-style: none;
    padding:0 0 0 15px;
}
.controller-toggle{
    cursor:pointer;
}
.li-action label {
    padding: 0 5px;
    margin-bottom: 0;
    margin-top: 3px;
}
.li-action .active{background-color:gold}
.list-controler-action h3{font-size: 19px;
    background-color: #eee;
    margin: 0;
    padding: 10px 0;
    text-indent: 10px;
    margin-bottom: 11px;}
.list-controler-action{border: 1px solid #ddd;
    margin-top: 20px;
    padding: 0 0 10px;}
    .rowController {
    padding: 0 10px;
    margin-bottom:6px;
}
.btn-submit,.btn-submit-role {
    position: fixed;
    bottom: 45px;
    left: 50%;
    z-index: 1000;
}
.input_action,.input_action_role {margin-right:3px !important}
.hide{display:none}
</style>
<div class="auth-item-view">
    <!-- <h1><?=Html::encode($this->title);?></h1> -->
    <p style="text-align:right"> 
        <?=Html::a('Thêm', ['create'], ['class' => 'btn btn-success']);?>
        <?=Html::a('Cập nhật', ['update', 'id' => $model->name], ['class' => 'btn btn-primary']);?>
        <?=Html::a('Xoá', ['delete', 'id' => $model->name], [
    'class' => 'btn btn-danger',
    'data-confirm' => 'Bạn có chắc chắn muốn xoá ' . strtolower($labels['Items']) . ' này?',
    'data-method' => 'post',
]);?>

    </p>
    <div class="row">
        <div class="col-sm-12">
            <?=
DetailView::widget([
    'model' => $model,
    'attributes' => [
        'name',
        'description:ntext',
        // 'ruleName',
        // 'data:ntext',
    ],
    'template' => '<tr><th style="width:25%">{label}</th><td>{value}</td></tr>',
]);
?>
        </div>
    </div>
    <div class="row">
        <?php
            if( $type == 'route' ){
        ?>
        <div class="col-sm-12" style="text-align:right">
            <?=Html::a('<span class="glyphicon glyphicon-refresh"></span> Refresh controller list ', ['route/refresh'], [
                        'class' => 'btn btn-default',
                        'id' => 'btn-refresh',
                    ]);?>

        </div>
        <?php } ?>
        <div class="col-sm-12">
            <?php
                if( $type == 'route' )
                    echo $this->render('_routeController',[
                        'listControllerAction' => $listControllerAction
                    ]);
                else
                    echo $this->render('_roleAssign',[
                        'listControllerAction' => $listControllerAction,
                        'listRole' => $listRole
                    ]);    
            ?>
        </div>
    </div>
</div>
