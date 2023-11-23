<?php

use mdm\admin\AnimateAsset;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\web\YiiAsset;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model mdm\admin\models\Assignment */
/* @var $fullnameField string */

$userName = $model->{$usernameField};
if (!empty($fullnameField)) {
    $userName .= ' (' . ArrayHelper::getValue($model, $fullnameField) . ')';
}
$userName = Html::encode($userName);

$this->title = 'Thông tin tài khoản';

$this->params['breadcrumbs'][] = ['label' => 'Tài khoản', 'url' => ['index']];
$this->params['breadcrumbs'][] = 'Tài khoản ' . $userName;

?>
<div class="assignment-index">
    <h1><?=$this->title;?></h1>
    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            [                      // the owner name of the model
                'label' => 'Mã tài khoản',
                'value' => $model->user->id,
            ],
            [                      // the owner name of the model
                'label' => 'Tài khoản',
                'value' => $model->user->username,
            ],
            [                      // the owner name of the model
                'label' => 'Điện thoại',
                'value' => $model->user->phone,
            ],
            [                      // the owner name of the model
                'label' => 'Email',
                'value' => $model->user->email,
            ],
            // [                      // the owner name of the model
            //     'label' => 'Thời gian tạo',
            //     'value' => $model->user->created_at,
            // ],
            [                      // the owner name of the model
                'label' => 'Trạng thái tài khoản',
                'value' => ($model->user->is_active == 0 ) ? 'active' : 'inactive',
            ],
            [                      // the owner name of the model
                'label' => 'Nhóm quyền',
                'value' => ($model->user->is_admin == 0 ) ? $model->user->userRole : 'All quyền',
            ]
        ],
    ]) ?>
    <div class="text-center">
        <a href="/assignment/update?id=<?= $model->user->id ?>" class="btn btn-primary">Cập nhật</a>
    </div>
   
</div>
