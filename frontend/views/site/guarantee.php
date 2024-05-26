<?php

use yii\helpers\Url;
use yii\web\View;
use backend\models\Config;
use yii\widgets\Breadcrumbs;
use backend\controllers\CommonController;

?>
<div class="container">
    <?php
    echo Breadcrumbs::widget([
        'homeLink' => ['label' => '', 'url' => '/'],
        'links' => [
            'Chính sách bảo hành',
        ],
    ]);

    ?>

    <section class="section_text">
        <h1>Chính sách bảo hành</h1>
        <p>Tại Tiki, chúng tôi trân trọng sự tin tưởng của khách hàng khi đặt mua sản phẩm. Chính sách hậu mãi ở Tiki được xây dựng dựa trên cam kết bảo vệ quyền lợi người tiêu dùng để quý khách có thể yên tâm mua sắm và trải nghiệm dịch vụ.
            Tiki đảm bảo sản phẩm được bán tại Tiki là sản phẩm mới và 100% chính hãng. Trong trường hợp hiếm hoi sản phẩm quý khách nhận được có khiếm khuyết, hư hỏng hoặc không như mô tả, Tiki cam kết bảo vệ khách hàng bằng chính sách đổi trả và bảo hành.


        </p>
        <p>Các sản phẩm không thỏa điều kiện đổi trả như trên: áp dụng bảo hành chính hãng (nếu có).(*) Chỉ áp dụng khi sản phẩm đáp ứng đủ điều kiện theo chính sách.
        </p>
        <p>(**) Áp dụng cho các đơn hàng từ 16/12/2022.</p>
    </section>
</div>