<?php

if( !isset($_POST['sinkPath']) || !isset($_POST['argPath'])){
	$data = array("flag"=>false, "msg"=>"文件不存在！");
	$data = json_encode($data);
	echo $data;
	exit() ;
}

$sink_path = $_POST['sinkPath'];
$arg_path = $_POST['argPath'];  


if( $sink_path == $arg_path ){
	$sink_fp = file_get_contents( $sink_path );
	$arg_fp = false;
}
else{
	$sink_fp = file_get_contents( $sink_path ); 
	$arg_fp = file_get_contents( $arg_path ); 
}

// $fp = htmlspecialchars($fp); js按特定的方式处理子串，不需要html编码。
// $in_charset = mb_detect_encoding($sink_fp) ;
// $sink_fp = iconv($in_charset, "UTF-8", $sink_fp) ;

// if ($arg_fp){
//     $in_charset = mb_detect_encoding($arg_fp) ;
//     $arg_fp = iconv($in_charset, "UTF-8", $arg_fp) ;
// }
$data = array("flag"=>true, "msg_sink"=>$sink_fp, "msg_arg"=>$arg_fp);

// echo $sink_fp;

//$data = json_encode($data);

$data = '{"flag":true,"msg_sink":"'.$sink_fp.'","msg_arg":"'.$arg_fp.'"}';

echo $data;

?>