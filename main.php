<?php

require_once 'global.php';

header("Content-type:text/html;charset=utf-8") ;

$smarty = new Smarty_setup();

/**
 * 处理单个文件的静态检测
 * 输入PHP文件
 * @param string $path
 */
function load_file($path){
	$cfg = new CFGGenerator() ;
	$cfg->getFileSummary()->setPath($path);
	
	$visitor = new MyVisitor() ;
	$parser = new PhpParser\Parser(new PhpParser\Lexer\Emulative) ;
	$traverser = new PhpParser\NodeTraverser ;

	$code = file_get_contents($path);
	$stmts = $parser->parse($code) ;
	$traverser->addVisitor($visitor) ;
	$traverser->traverse($stmts) ;
	$nodes = $visitor->getNodes() ;
	
	$pEntryBlock = new BasicBlock() ;
	$pEntryBlock->is_entry = true ;
	
	//开始分析
	$cfg->CFGBuilder($nodes, NULL, NULL, NULL) ;
}


if(!isset($_POST['path']) || !isset($_POST['type'])){
	$smarty->display('index.html') ;
	exit() ;
}

$t_start = time();

//1、从web ui中获取并加载项目工程
$project_path = $_POST['path'] ;  //扫描的工程路径
$scan_type = $_POST['type'] ;     //扫描的类型
$encoding = $_POST['encoding'] ;  //CMS的编码   UTF-8 或者  GBK


// $scan_type = 'ALL';
// $project_path = 'C:/users/xyw55/Desktop/test/simple-log_v1.3.1/upload';
// print_r('<pre>');


//2、初始化模块
// $allFiles = FileUtils::getPHPfile($project_path);
// $mainlFiles = FileUtils::mainFileFinder($project_path);
// $initModule = new InitModule() ;
// $initModule->init($project_path, $allFiles) ;


//3、循环每个文件  进行分析工作
// if(is_file($project_path)){
// 	load_file($project_path) ;
// }elseif (is_dir($project_path)){
// 	$path_list = $mainlFiles;
// 	foreach ($path_list as $path){
// 		try{
// 		    //print_r($path.'<br/>');
// 			load_file($path) ;
// 			$count ++ ;
// 			//传给templates
// 		}catch(Exception $e){
// 			continue ;
// 		}	

// 	}
// }else{
//     echo "<script>alert('工程不存在！');</script>" ;
//     exit() ;
// }



//4、获取ResultContext  传给template
// $results = ResultContext::getInstance() ;
// 测试使用的 results
$results = array(
				array(
			 		'path'	=>	'E:\\demo\\demo1.php',  //漏洞的页面路径
					'node'	=> 	'null', //调用sink的node
			 		'var' 	=> 	'null', //追踪的变量node
			 		'type'	=> 	'SQLI'   //漏洞类型
				),
				array(
			 		'path'	=>	'E:\\demo\\demo2.php',  //漏洞的页面路径
					'node'	=> 	'null', //调用sink的node
			 		'var' 	=> 	'null', //追踪的变量node
			 		'type'	=> 	'SQLI'   //漏洞类型
				),
				array(
			 		'path'	=>	'E:\\demo\\demo3.php',  //漏洞的页面路径
					'node'	=> 	'null', //调用sink的node
			 		'var' 	=> 	'null', //追踪的变量node
			 		'type'	=> 	'SQLI'   //漏洞类型
				),
				array(
			 		'path'	=>	'E:\\demo\\demo4.php',  //漏洞的页面路径
					'node'	=> 	'null', //调用sink的node
			 		'var' 	=> 	'null', //追踪的变量node
			 		'type'	=> 	'SQLI'   //漏洞类型
				)
			);

$smarty->assign('results',$results);
$smarty->display('content.html');

//5、序列化
// $serialPath = CURR_PATH . '/data/resultConetxtSerialData';
// file_put_contents($serialPath, serialize($results)) ;
// if(($serial_str = file_get_contents($serialPath))!=''){
//     $results = unserialize($serial_str) ;
//     print_r($results);
// }

// $t_end = time();
// $t = $t_end - $t_start;
// print_r($t);

?>