<?php
    $description_page_h1 = '';
    $icon_page = 'fa-tag';
    if(isset($breadcrumbs['description_page'])){
        $description_page_h1 = $breadcrumbs['description_page'];
        unset($breadcrumbs['description_page']);
    }
    if(isset($breadcrumbs['icon_page'])){
        $icon_page = $breadcrumbs['icon_page'];
        unset($breadcrumbs['icon_page']);
    }
    if( !empty($breadcrumbs) ){
?>
<ol class="breadcrumb page-breadcrumb">
    <li class="breadcrumb-item"><a href="/">Trang chủ</a></li>
    <?php
        $c_breadcrumbs = count($breadcrumbs);
        foreach($breadcrumbs as $key=>$childBreadcrumbs){
    ?>
    <li class="breadcrumb-item <?= $key == ($c_breadcrumbs - 1) ? 'active' : '' ?>">
    <?php
        if( is_array($childBreadcrumbs) ){
            $text_breadcrumb = $childBreadcrumbs['label'];
            if( isset($childBreadcrumbs['url']) ){
                $url = $childBreadcrumbs['url'];
                if( is_array($url) ){
                    $controller_action = strpos($url[0],'/') === false ? '/' .Yii::$app->controller->id . '/' . $url[0] : $url[0]; 
                    unset($url[0]);
                    if( !empty($url) ){
                        $dataParams = [];
                        foreach($url as $params_key=>$params_value){
                            $dataParams[] = $params_key . '=' . $params_value;
                        }
                        $controller_action .= '?' . implode('&',$dataParams);
                    }
                    $url = $controller_action;
                }else{
                    if(strpos($url,'/') === false){
                        $url = '/' . Yii::$app->controller->id . '/' . $url;
                    }
                }
                $text_breadcrumb = '<a href="' . $url . '">' . $text_breadcrumb . '</a>';
            }
        }else{
            $text_breadcrumb = $childBreadcrumbs;
        }
        echo $text_breadcrumb;
    ?>
    </li>
    <?php
        }
    ?>
</ol>
<?php } ?>
<div class="subheader">
<h1 class="subheader-title">
    <i class='subheader-icon fal <?= (Yii::$app->controller->id == 'site' && Yii::$app->controller->action->id == 'index') ? 'fa-chart-area' : $icon_page ?>'></i> <?= $this->title ?>
    <?php if( !empty($description_page_h1) ){ ?>
    <small>
        <?= $description_page_h1 ?>
    </small>
    <?php } ?>
</h1>
</div>