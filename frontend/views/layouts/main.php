<?php

/* @var $this \yii\web\View */
/* @var $content string */

use yii\helpers\Html;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\widgets\Breadcrumbs;
use frontend\assets\AppAsset;
use yii\helpers\Url;
use backend\models\Category;
use backend\models\Config;

AppAsset::register($this);

?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="en">
    <head>  
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css" />
        <link rel="stylesheet" id="wp-bootstrap-starter-fontawesome-cdn-css" href="/css/fontawesome.min.css" type="text/css" media="all" />
        <link rel="stylesheet" href="/css/all.min.css" crossorigin="anonymous" />
        <link data-optimized="2" rel="stylesheet" href="/css/layout.css">
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
        <link rel="icon" type="image/png" href="/images/icon/logo-fa.svg" sizes="50x50">
        <meta property="og:locale" content="vi_VN" />
        <meta property="og:type" content="website" />
        <link rel="stylesheet" href="/css/azuremediaplayer.min.css" />
        <script src="/resoure/sdk.js" async="" crossorigin="anonymous"></script>
        
        <link href="/css/sweetalert.css" rel="stylesheet">
        <link href="/css/slick-theme.css" rel="stylesheet">
        <link href="/css/slick.css" rel="stylesheet">
        <link href="/js/toastr/toastr.min.css" rel="stylesheet">
        <link href="/css/site.css" rel="stylesheet">
        <link href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" rel="stylesheet">
        <script src="/js/jquery.min.js" defer></script>
        <script src="/js/bootstrap.min.js" defer></script>
        <script src="/js/sweetalert2.js" defer></script>
        <script src="/js/slick.min.js" defer></script>
        <script src="/js/toastr/toastr.min.js" defer></script>
        <script src="/js/script.js" defer></script> 

        <script>
            (function (html) {
                html.className = html.className.replace(/\bno-js\b/, "js");
            })(document.documentElement);
        </script>

        <script type="text/javascript" async="" defer="" src="/js/piwik.js"></script>
        <script>
            window.dataLayer = window.dataLayer || [];
            function gtag() {
                dataLayer.push(arguments);
            }
            gtag("js", new Date());
        </script>
        <link rel="stylesheet" href="/css/styles.css" />
        <!-- <link rel="stylesheet" href="/css/styles.78c05ae853e3fdc5414f.css" /> -->
        <link rel="stylesheet" href="/css/main.css" />
        <!-- <link rel="stylesheet" href="/css/main.ed7f3fbe4b65d1e2777b.css" /> -->
        <?php
        // $this->registerMetaTag(Yii::$app->params['og_title'], 'og_title'); 
        // $this->registerMetaTag(Yii::$app->params['og_description'], 'og_description'); 
        // $this->registerMetaTag(Yii::$app->params['og_fb'], 'og_fb'); ?>

        <!-- Google / Search Engine Tags -->
        <html prefix="og: http://ogp.me/ns#">
        <meta itemprop="name" content="website abe">
        
        <!-- Facebook Meta Tags -->
        <meta property="og:type" content="article">
        <meta property="og:title" content="<?= Html::encode($this->title) ?>">
        <meta property="og:description" content="">
        
        <!-- Twitter Meta Tags -->
        <meta name="twitter:card" content="">
        <meta name="twitter:title" content="<?= Html::encode($this->title) ?>">
        <meta name="twitter:description" content="">
        <meta name="twitter:image" content="/images/icon/logo-fa.svg">


        <title><?= Html::encode($this->title) ?></title>
        <?php $this->
        head() ?>
        <style>
            @import url(https://fonts.googleapis.com/css?family=Lato:600, 500, 400, 300&display=swap);
        </style>

        <div id="fb-root"></div>
        <script async defer crossorigin="anonymous" src="https://connect.facebook.net/vi_VN/sdk.js#xfbml=1&version=v15.0&appId=1438665563271474&autoLogAppEvents=1" nonce="NvknslKE"></script>

        <!--Start of Tawk.to Script New-->
            <!-- <script type="text/javascript">
            var Tawk_API=Tawk_API||{}, Tawk_LoadStart=new Date();
            (function(){
            var s1=document.createElement("script"),s0=document.getElementsByTagName("script")[0];
            s1.async=true;
            s1.src='https://embed.tawk.to/63fa203531ebfa0fe7ef489d/1gq4grko2';
            s1.charset='UTF-8';
            s1.setAttribute('crossorigin','*');
            s0.parentNode.insertBefore(s1,s0);
            })();
            </script> -->
        <!--End of Tawk.to Script-->

        <!-- Messenger Chat Plugin Code --> 
        <!-- <div id="fb-root"></div> <div id="fb-customer-chat" class="fb-customerchat"></div> <script> var chatbox = document.getElementById('fb-customer-chat'); chatbox.setAttribute("page_id", "PAGE-ID"); chatbox.setAttribute("attribution", "biz_inbox"); </script> <script> window.fbAsyncInit = function() { FB.init({ xfbml : true, version : 'API-VERSION' }); }; (function(d, s, id) { var js, fjs = d.getElementsByTagName(s)[0]; if (d.getElementById(id)) return; js = d.createElement(s); js.id = id; js.src = 'https://connect.facebook.net/en_US/sdk/xfbml.customerchat.js'; fjs.parentNode.insertBefore(js, fjs); }(document, 'script', 'facebook-jssdk')); </script>  -->
            
        <!-- Google analytics (gtag.js) -->
        <script async src="https://www.googletagmanager.com/gtag/js?id=G-GNSZR91JYF"></script>
        <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());

        gtag('config', 'G-GNSZR91JYF');
        </script>

        <!-- google console -->
        <meta name="google-site-verification" content="FVlFTqz1IswBaovc_sbB6PEvICkpGxmt35msFsDpvNw" />
        <?php $this->registerCsrfMetaTags() ?>
    </head>
    <?php $this->beginBody() ?>
        <?= $this->render('header') ?>
        <div id="main_content">
            <?= $content ?>
        </div>
        <?= $this->render('footer') ?>
    <?php $this->endBody() ?>
</html>
<?php $this->endPage() ?>
