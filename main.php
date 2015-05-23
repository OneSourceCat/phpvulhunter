<?php

require_once 'global.php';


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
//print_r('<pre>');
//1、从web ui中获取并加载项目工程
$project_path = $_POST['path'] ;  //扫描的工程路径
$scan_type = $_POST['type'] ;     //扫描的类型
$encoding = $_POST['encoding'] ;  //CMS的编码   UTF-8 或者  GBK
$scan_type = $scanType = strtoupper($scan_type);

$project_path = str_replace('\\', '/', $project_path);

$fileName = str_replace('/', '_', $project_path);
$fileName = str_replace(':', '_', $fileName);
$serialPath = CURR_PATH . "/data/resultConetxtSerialData/" . $fileName;
if (!is_file($serialPath)){
    //创建文件
    $fileHandler = fopen($serialPath, 'w');
    fclose($fileHandler);
}

if(($serial_str = file_get_contents($serialPath)) != ''){
    $results = unserialize($serial_str) ;
    print_r('<pre>');
    print_r($results);
    exit();
}

//2、初始化模块
$allFiles = FileUtils::getPHPfile($project_path);
$mainlFiles = FileUtils::mainFileFinder($project_path);
$initModule = new InitModule() ;
$initModule->init($project_path, $allFiles) ;


//3、循环每个文件  进行分析工作
if(is_file($project_path)){
	load_file($project_path) ;
}elseif (is_dir($project_path)){
	$path_list = $mainlFiles;
    //$path_list = array('C:/users/xyw55/Desktop/test/simple-log_v1.3.1/upload/admin/admin.php');
    //$path_list = array('C:/Users/xyw55/Desktop/test/dvwa/external/phpids/0.6/tests/allTests.php');
// 	$path_list = array(    
// 	            'C:/Users/xyw55/Desktop/test/dvwa/external/phpids/0.6/tests/allTests.php',
//     'C:/Users/xyw55/Desktop/test/dvwa/external/phpids/0.6/tests/IDS/CachingTest.php',
//     'C:/Users/xyw55/Desktop/test/dvwa/external/phpids/0.6/tests/IDS/EventTest.php',
//     'C:/Users/xyw55/Desktop/test/dvwa/external/phpids/0.6/tests/IDS/ExceptionTest.php',
//     'C:/Users/xyw55/Desktop/test/dvwa/external/phpids/0.6/tests/IDS/FilterTest.php',
//     'C:/Users/xyw55/Desktop/test/dvwa/external/phpids/0.6/tests/IDS/InitTest.php',
//     'C:/Users/xyw55/Desktop/test/dvwa/external/phpids/0.6/tests/IDS/MonitorTest.php',
//     'C:/Users/xyw55/Desktop/test/dvwa/external/phpids/0.6/tests/IDS/ReportTest.php'
// 	);
	foreach ($path_list as $path){
		try{
		    print_r($path.'<br/>');
			load_file($path) ;
		}catch(Exception $e){
			continue ;
		}	
	}
}else{
	//请求不合法
	echo "工程不存在!" ;
	exit() ;
}

//4、获取ResultContext  序列化
$results = ResultContext::getInstance() ;


file_put_contents($serialPath, serialize($results)) ;
print_r($results);

$t_end = time();
$t = $t_end - $t_start;
print_r($t);

//5、处理results 传给template
$tempRes = array();
foreach ($results->getResArr() as $result){
    $record = $result->getRecord();
    array_push($tempRes, $record);
}

$results = $tempRes;


$smarty->assign('results',$results);
$smarty->display('content.html');


?>