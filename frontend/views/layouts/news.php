<?php

/* @var $this \yii\web\View */
/* @var $content string */

use yii\helpers\Html;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\widgets\Breadcrumbs;
use frontend\assets\AppAsset;
use yii\helpers\Url;

AppAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="en">
   <head>
      <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1">
      <meta http-equiv="X-UA-Compatible" content="IE=edge">
      <link rel="profile" href="http://gmpg.org/xfn/11">
      <link rel="stylesheet" href="/css/all.min.css" crossorigin="anonymous">
      <title><?= Html::encode($this->title) ?></title>
      <meta name="description" content="Dù tuổi đời vẫn còn non trẻ nhưng TopClass Việt Nam rất vui khi trong thời gian qua đã đón nhận khá nhiều lời bình tích cực từ các bạn học viên.">
      <meta name="robots" content="index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1">
      <link rel="canonical" href="https://www.topclass.com.vn/articles/trai-nghiem-cua-nguoi-dung-tren-topclass/">
      <meta property="og:locale" content="vi_VN">
      <?php
		$this->registerMetaTag(Yii::$app->params['og_title'], 'og_title');
		$this->registerMetaTag(Yii::$app->params['og_description'], 'og_description');
		$this->registerMetaTag(Yii::$app->params['og_image'], 'og_image');
	  ?>
      <meta property="og:url" content="https://www.topclass.com.vn/articles/trai-nghiem-cua-nguoi-dung-tren-topclass/">
      <meta property="og:site_name" content="TopClass">
      <meta property="article:published_time" content="2021-04-07T11:27:07+00:00">
      <meta property="article:modified_time" content="2021-08-30T06:52:08+00:00">
      <meta property="og:image" content="https://www.topclass.com.vn/articles/wp-content/uploads/2021/04/3-student-1-e1624946714512.jpg">
      <meta property="og:image:width" content="960">
      <meta property="og:image:height" content="540">
      <meta name="twitter:card" content="summary_large_image">
      <meta name="twitter:label1" value="Written by">
      <meta name="twitter:data1" value="admin">
      <meta name="twitter:label2" value="Est. reading time">
      <meta name="twitter:data2" value="7 minutes">
      <script type="application/ld+json" class="yoast-schema-graph">{"@context":"https://schema.org","@graph":[{"@type":"WebSite","@id":"https://www.topclass.com.vn/articles/#website","url":"https://www.topclass.com.vn/articles/","name":"TopClass","description":"Tin T\u1ee9c, B\u00e0i Vi\u1ebft T\u1eeb C\u00e1c Chuy\u00ean Gia H\u00e0ng \u0110\u1ea7u","potentialAction":[{"@type":"SearchAction","target":"https://www.topclass.com.vn/articles/?s={search_term_string}","query-input":"required name=search_term_string"}],"inLanguage":"en-US"},{"@type":"ImageObject","@id":"https://www.topclass.com.vn/articles/trai-nghiem-cua-nguoi-dung-tren-topclass/#primaryimage","inLanguage":"en-US","url":"https://www.topclass.com.vn/articles/wp-content/uploads/2021/04/3-student-1-e1624946714512.jpg","width":960,"height":540},{"@type":"WebPage","@id":"https://www.topclass.com.vn/articles/trai-nghiem-cua-nguoi-dung-tren-topclass/#webpage","url":"https://www.topclass.com.vn/articles/trai-nghiem-cua-nguoi-dung-tren-topclass/","name":"C\u00f3 N\u00ean H\u1ecdc TopClass? T\u00ecm Hi\u1ec3u Tr\u1ea3i Nghi\u1ec7m C\u1ee7a Ng\u01b0\u1eddi D\u00f9ng Tr\u00ean TopClass","isPartOf":{"@id":"https://www.topclass.com.vn/articles/#website"},"primaryImageOfPage":{"@id":"https://www.topclass.com.vn/articles/trai-nghiem-cua-nguoi-dung-tren-topclass/#primaryimage"},"datePublished":"2021-04-07T11:27:07+00:00","dateModified":"2021-08-30T06:52:08+00:00","author":{"@id":"https://www.topclass.com.vn/articles/#/schema/person/35b4b05fec49886b723bb1881b246304"},"description":"D\u00f9 tu\u1ed5i \u0111\u1eddi v\u1eabn c\u00f2n non tr\u1ebb nh\u01b0ng TopClass Vi\u1ec7t Nam r\u1ea5t vui khi trong th\u1eddi gian qua \u0111\u00e3 \u0111\u00f3n nh\u1eadn kh\u00e1 nhi\u1ec1u l\u1eddi b\u00ecnh t\u00edch c\u1ef1c t\u1eeb c\u00e1c b\u1ea1n h\u1ecdc vi\u00ean.","inLanguage":"en-US","potentialAction":[{"@type":"ReadAction","target":["https://www.topclass.com.vn/articles/trai-nghiem-cua-nguoi-dung-tren-topclass/"]}]},{"@type":"Person","@id":"https://www.topclass.com.vn/articles/#/schema/person/35b4b05fec49886b723bb1881b246304","name":"admin","image":{"@type":"ImageObject","@id":"https://www.topclass.com.vn/articles/#personlogo","inLanguage":"en-US","url":"https://secure.gravatar.com/avatar/10e4e61b734674c0455aa57d075a3ef1?s=96&d=mm&r=g","caption":"admin"},"sameAs":["https://articles.topclass.com.vn"]}]}</script>
      <!-- / Yoast SEO plugin. -->
      <link rel="dns-prefetch" href="https://code.jquery.com/">
      <link rel="dns-prefetch" href="https://www.topclass.com.vn/">
      <link rel="dns-prefetch" href="https://fonts.googleapis.com/">
      <link rel="dns-prefetch" href="https://s.w.org/">
      <link rel="alternate" type="application/rss+xml" title="TopClass » Feed" href="https://www.topclass.com.vn/articles/feed/">
      <link rel="alternate" type="application/rss+xml" title="TopClass » Comments Feed" href="https://www.topclass.com.vn/articles/comments/feed/">
      <link rel="alternate" type="application/rss+xml" title="TopClass » Có Nên Học TopClass? Tìm Hiểu Trải Nghiệm Của Người Dùng Trên TopClass Comments Feed" href="https://www.topclass.com.vn/articles/trai-nghiem-cua-nguoi-dung-tren-topclass/feed/">
      <?php $this->head() ?>
      <link rel="stylesheet" id="wp-block-library-css" href="/css/style.min.css" type="text/css" media="all">
      <link rel="stylesheet" id="wp-bootstrap-starter-bootstrap-css-css" href="/css/bootstrap.min.css" type="text/css" media="all">
      <link rel="stylesheet" id="wp-bootstrap-starter-fontawesome-cdn-css" href="/css/fontawesome.min.css" type="text/css" media="all">
      <link rel="stylesheet" id="slick-css-css" href="/css/slick.css" type="text/css" media="all">
      <link rel="stylesheet" id="slick-theme-css" href="/css/slick-theme.css" type="text/css" media="all">
      <link rel="stylesheet" id="wp-bootstrap-starter-style-css" href="/css/style.css" type="text/css" media="all">
      <link rel="stylesheet" id="wp-bootstrap-starter-topclass-css" href="/css/topclass.css" type="text/css" media="all">
      <link rel="stylesheet" id="wp-bootstrap-starter-nunito-font-css" href="/css/css" type="text/css" media="all">
      <link rel="stylesheet" id="wp-bootstrap-starter-topclass-origin-css" href="/css/topclass-origin.css" type="text/css" media="all">
      <link rel="https://api.w.org/" href="https://www.topclass.com.vn/articles/wp-json/">
      <link rel="alternate" type="application/json" href="https://www.topclass.com.vn/articles/wp-json/wp/v2/posts/449">
      <link rel="EditURI" type="application/rsd+xml" title="RSD" href="https://www.topclass.com.vn/articles/xmlrpc.php?rsd">
      <link rel="wlwmanifest" type="application/wlwmanifest+xml" href="https://www.topclass.com.vn/articles/wp-includes/wlwmanifest.xml">
      <meta name="generator" content="WordPress 5.5.1">
      <link rel="shortlink" href="https://www.topclass.com.vn/articles/?p=449">
      <link rel="alternate" type="application/json+oembed" href="https://www.topclass.com.vn/articles/wp-json/oembed/1.0/embed?url=https%3A%2F%2Fwww.topclass.com.vn%2Farticles%2Ftrai-nghiem-cua-nguoi-dung-tren-topclass%2F">
      <link rel="alternate" type="text/xml+oembed" href="https://www.topclass.com.vn/articles/wp-json/oembed/1.0/embed?url=https%3A%2F%2Fwww.topclass.com.vn%2Farticles%2Ftrai-nghiem-cua-nguoi-dung-tren-topclass%2F&amp;format=xml">
      <link rel="pingback" href="https://www.topclass.com.vn/articles/xmlrpc.php">
      <style type="text/css">
         #page-sub-header { background: #0a0a0a; }
      </style>
      <link rel="icon" href="https://www.topclass.com.vn/articles/wp-content/uploads/2020/09/favicon.png" sizes="32x32">
      <link rel="icon" href="https://www.topclass.com.vn/articles/wp-content/uploads/2020/09/favicon.png" sizes="192x192">
      <link rel="apple-touch-icon" href="https://www.topclass.com.vn/articles/wp-content/uploads/2020/09/favicon.png">
      <meta name="msapplication-TileImage" content="https://www.topclass.com.vn/articles/wp-content/uploads/2020/09/favicon.png">
   </head>
   
   <body class="post-template-default single single-post postid-449 single-format-standard theme-preset-active">
    <?php $this->beginBody() ?>
      <div id="page" class="site">
         <header id="masthead" class="site-header navbar-static-top navbar-dark bg-primary" role="banner">
            <div class="container">
               <nav class="navbar navbar-expand-xl p-0">
                  <div class="navbar-brand">
                     <a href="<?= Url::to(['news/index']);?>">
                     <img src="/images/logo.jpg" alt="TopClass">
                     </a>
                  </div>
                  <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#main-nav" aria-controls="" aria-expanded="false" aria-label="Toggle navigation">
                  <span class="navbar-toggler-icon"></span>
                  </button>
                  <div id="main-nav" class="collapse navbar-collapse justify-content-end">
                     <ul id="menu-main-menu" class="navbar-nav">
                        <li itemscope="itemscope" itemtype="https://www.schema.org/SiteNavigationElement" id="menu-item-29" class="menu-item menu-item-type-custom menu-item-object-custom menu-item-29 nav-item"><a title="Về trang học" href="/" class="nav-link">Về trang học</a></li>
                     </ul>
                  </div>
               </nav>
            </div>
         </header>
         <!-- #masthead -->
         <div id="content" class="site-content <?= isset(Yii::$app->params['hasSlide']) ? 'no-padding' : '' ?>">
            <?= $content ?>
            <!-- .container -->
         </div>
         <!-- #content -->
         <footer id="colophon" class="site-footer  navbar-dark bg-primary" role="contentinfo">
            <div class="container pb-5">
            </div>
            <div id="footer-widget" class="row m-0 ">
               <div class="container">
                  <div class="row">
                     <div class="col-12 col-md-7">
                        <section id="custom_html-3" class="widget_text widget widget_custom_html">
                           <div class="textwidget custom-html-widget">
                              <div class="main-footer">
                                 <div class="text-muted mb-3"><b>© 2020 CÔNG TY TNHH VIỆT NAM MASTERS
                                    </b>
                                 </div>
                                 <div class="row">
                                    <div class="company-info col-lg-6">
                                       <div>Giấy chứng nhận đăng ký doanh nghiệp số: 0316390525</div>
                                       <div>Cấp ngày: 16/07/2020.</div>
                                       <div>Nơi cấp: Sở Kế hoạch và Đầu tư TP.Hồ Chí Minh.</div>
                                       <div class="registered-badge mb-4"><a target="_blank" rel="noopener noreferrer" href="http://online.gov.vn/Home/WebDetails/70582"><img width="120" alt="Đã thông báo với Bộ Công thương" title="" src="/images/bo_cong_thuong.png"></a></div>
                                    </div>
                                    <div class="company-contact col-lg-6">
                                       <div class="mb-2 justify-content-center justify-content-lg-start company-info"><i class="fal fa-map-marker-alt"></i> <span>Lầu 2, Tòa nhà ITAXA, 19 Võ Văn Tần, P.6, Q.3, Thành phố Hồ Chí Minh</span></div>
                                       <div class="mb-2 justify-content-center justify-content-lg-start company-info"><i class="fal fa-phone"></i> <span>028 38220814</span></div>
                                       <div class="mb-2 justify-content-center justify-content-lg-start company-info"><i class="fal fa-envelope"></i> <span>support@topclass.com.vn</span></div>
                                       <ul class="social-link no-gutters mb-4 d-inline-block mt-3">
                                          <li><a href="https://www.facebook.com/TopClassVietnam" rel="noopener noreferrer" target="_window"><i class="fab fa-facebook"></i></a></li>
                                          <li><a href="https://instagram.com/topclassvn?igshid=um1qp3dt5liv" rel="noopener noreferrer" target="_window"><i class="fab fa-instagram"></i></a></li>
                                          <li><a href="https://www.youtube.com/channel/UCbE1VdRA268UAyDeXhSihsw" rel="noopener noreferrer" target="_window"><i class="fab fa-youtube"></i></a></li>
                                       </ul>
                                    </div>
                                 </div>
                              </div>
                           </div>
                        </section>
                     </div>
                     <div class="col-12 col-md-3">
                        <section id="custom_html-4" class="widget_text widget widget_custom_html">
                           <div class="textwidget custom-html-widget">
                              <div class="main-footer">
                                 <h3 class="text-muted">Menu</h3>
                                 <ul class="footer-link">
                                    <li><a href="https://www.topclass.com.vn/">Trang chủ</a></li>
                                    <li><a href="https://www.topclass.com.vn/">Tất cả khóa học</a></li>
                                    <li><a href="https://www.topclass.com.vn/contact">Liên hệ</a></li>
                                    <li><a href="https://www.topclass.com.vn/terms">Điều khoản sử dụng dịch vụ</a></li>
                                    <li><a href="https://www.topclass.com.vn/privacy">Chính sách bảo mật</a></li>
                                    <li><a href="https://www.topclass.com.vn/refund">Chính sách đổi trả và hoàn tiền</a></li>
                                 </ul>
                              </div>
                           </div>
                        </section>
                     </div>
                     <div class="col-12 col-md-2">
                        <section id="custom_html-5" class="widget_text widget widget_custom_html">
                           <div class="textwidget custom-html-widget">
                              <div class="main-footer">
                                 <h3 class="text-muted">Tải về</h3>
                                 <ul class="footer-link">
                                    <li class="mb-3"><a><img width="88" src="/images/appstore.png" alt="Download on the App Store"></a></li>
                                    <li><a><img width="88" src="/images/playstore.png" alt="Get it on Google Play"></a></li>
                                 </ul>
                              </div>
                           </div>
                        </section>
                     </div>
                     <div class="col-12">
                        <section id="custom_html-6" class="widget_text widget widget_custom_html">
                           <div class="textwidget custom-html-widget">
                              <div class="copyright align-items-center text-center text-lg-left row">
                                 <div class="company-info col-lg-9">
                                    <div class="text-muted">© 2020 CÔNG TY TNHH VIỆT NAM MASTERS</div>
                                 </div>
                              </div>
                           </div>
                        </section>
                     </div>
                  </div>
               </div>
            </div>
            <div class="container pt-3 pb-3">
               <!-- <div class="site-info">
                  &copy; 2021 <a href="https://www.topclass.com.vn/articles">TopClass</a>                <span class="sep"> | </span>
                  <a class="credits" href="https://afterimagedesigns.com/wp-bootstrap-starter/" target="_blank" title="WordPress Technical Support" alt="Bootstrap WordPress Theme">Bootstrap WordPress Theme</a>
                  
                  </div> -->
               <!-- close .site-info -->
            </div>
         </footer>
         <!-- #colophon -->
      </div>
      <!-- #page -->
      <script>
         $(document).ready(function(){
            $('.variable-width').slick({
                arrows: false,
                dots: true,
                infinite: true,
                speed: 300,
                slidesToShow: 1,
                centerMode: true,
                variableWidth: true,
                autoplay: true,
                autoplaySpeed: 3000,
                draggable: false,
            });
        });
      </script>
      <?php $this->endBody() ?>
   </body>
   
   <style id="igor_ext_nofollow">a[rel~='nofollow'],a[rel~='sponsored'],a[rel~='ugc']{outline:.14em dotted red !important;outline-offset:.2em;}a[rel~='nofollow'] img,a[rel~='sponsored'] img,a[rel~='ugc'] img{outline:2px dotted red !important;outline-offset:.2em;}</style>
</html>
<?php $this->endPage() ?>