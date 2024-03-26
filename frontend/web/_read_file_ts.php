<?php
require_once 'MobileDetect.php';
$isFromAdmin = false;
$listDomainAccept = ['https://yogalunathai.com','https://admin.yogalunathai.com'];
$listDomainAccept[] = 'https://' . $_SERVER['SERVER_NAME'];
foreach($listDomainAccept as $domain){
    $listDomainAccept[] = str_replace('https://','http://', $domain);
}
if( isset($_SERVER["HTTP_ORIGIN"]) && in_array($_SERVER["HTTP_ORIGIN"],$listDomainAccept) ){
    header('Access-Control-Allow-Origin: ' . $_SERVER["HTTP_ORIGIN"]); 
    header('Access-Control-Allow-Credentials: true'); 
    
    if( $_SERVER["HTTP_ORIGIN"] === 'https://admin.yogalunathai.com' )
        $isFromAdmin = true;
}

$isAcceptReadFile  = false;
if( isset($_SERVER['HTTP_REFERER']) && !empty($_SERVER['HTTP_REFERER']) ){
    $referer = $_SERVER['HTTP_REFERER'];
    foreach($listDomainAccept as $domain){
        $domain = $domain . '/';
        if( strpos($referer, $domain) === 0 ){
            $isAcceptReadFile = true;
            break;
        }
    }
}
$detect = new MobileDetect;
$isMobile = $detect->isMobile();

session_start();    
if( $isAcceptReadFile && ( (isset($_GET['token']) && !empty($_GET['token'])) || $isMobile ) && !empty($_GET['filename']) && isset($_GET['filename']) && !empty($_GET['course_id']) && isset($_GET['course_id']) ){
    $time_current  = time();
    $time_check    = $time_current - (isset($_SESSION['time_ts']) ? $_SESSION['time_ts'] : 0);
    if( $isFromAdmin || $isMobile || (isset($_SESSION['token_ts_video']) && $_SESSION['token_ts_video'] == $_GET['token'] && $time_check >= 0 && $time_check <= 4 ) ){
        unset($_SESSION['token_ts_video']);
        //OK trả về file ts
        $path_root = $_SERVER['DOCUMENT_ROOT'];
        $course_id = $_GET['course_id'];
        $filename  = $_GET['filename'];
        $path_file = $path_root . '/uploads/video-lesson/' . $course_id . '/' . $filename;
        if( file_exists($path_file) ){
            set_time_limit(0);
            ini_set('memory_limit', '-1');
            ob_clean();
            $file           = $path_file;
            $file_size      = filesize($file);
            $file_pointer   = fopen($file, "rb");
            $data           = fread($file_pointer, $file_size);
            header("Content-type: video/mpegts");
            header("Content-Length: " . $file_size);
            echo $data;
            flush();
        }else
            echo 'File not exits!';
    }else{
        echo 'File not exits!';
    }
}else{
    echo 'File not exits!';
}
die;exit();