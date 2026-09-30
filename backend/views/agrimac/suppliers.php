<?php

/* @var $this yii\web\View */
/* @var $suppliers array */
/* @var $products array */
/* @var $parts array */

use yii\web\View;
use backend\assets\AgrimacAsset;

$this->registerJsVar('AM_SUPPLIER_PAGE', ['suppliers' => $suppliers, 'products' => $products, 'parts' => $parts], View::POS_HEAD);
$this->registerJsFile('@web/agrimac/js/am-suppliers.js', ['depends' => AgrimacAsset::class]);
?>
<div>
    <div class="am-toolbar-end"><button class="am-btn" data-action="new-supplier">+ Thêm nhà cung cấp</button></div>
    <div id="am-supplier-grid" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:14px"></div>
</div>
