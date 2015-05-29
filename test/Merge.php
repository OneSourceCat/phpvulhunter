<?php

$PRO = "D:/MySoftware/wamp/www/code/phpvulhunter" ;
$OUT = $PRO . "/data/resultConetxtSerialData/outfile.txt" ;

require_once $PRO . '/context/ResultContext.class.php';

function getAllFiles($path){
    $ret = array() ;
    if(!is_dir($path)){
        array_push($ret, $path) ;
        return $ret ;
    }
    if(($handle = opendir($path)) == false){
        return $ret ;
    }
    while(($file = readdir($handle))!=false){
        if($file == "." || $file == ".."){
            continue ;
        }
        if(is_file($path . "/" . $file)){
            $item = $path . "/" . $file ;
            $in_charset = mb_detect_encoding($item) ;
            $item = iconv($in_charset, "UTF-8", $item) ;
            array_push($ret, $item) ;
        }else{
            continue ;
        }
    }
    closedir($handle) ;
    return $ret ;
}

$resContext_all = ResultContext::getInstance() ;
$path = $PRO . "/data/resultConetxtSerialData/" ;
$file_list = getAllFiles($path) ;

if(count($file_list) == 0){
    echo "No result files";
    exit() ;
}else{
    echo "Just one file";
    exit() ;
}

foreach ($file_list as $file){
    $content = file_get_contents($file) ;
    $resContext = unserialize($content) ;
    $resArr = $resContext->getResArr() ;
    foreach ($resArr as $value){
        $resContext_all->mergeResultArray($value) ;
    }
}

echo "完成！";

?>























