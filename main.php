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

/**
 * 将结果集转为前端的模式
 * @param ResultContext $resContext
 */
function convertResults($resContext){
    $ret = array() ;
    $resArr = $resContext->getResArr() ;
    foreach($resArr as $record){
        $item = array() ;
        $record = $record->getRecord() ;
        $item['type'] = $record['type'] ;
        $item['node_path'] = $record['node_path'] ;
        $item['var_path'] = $record['var_path'] ;
        
        //整理node代码
        $node = $record['node'] ;
        $node_item = array() ;
        if($node instanceof Symbol){
            $node_start = $node->getValue()->getAttribute('startLine') ;
            $node_end = $node->getValue()->getAttribute('endLine') ;
        }else{
            $node_start = $node->getAttribute('startLine') ;
            $node_end = $node->getAttribute('endLine') ;
        }
        
        $node_item['line'] = $node_start . "|" . $node_end ;
        $node_item['code'] = FileUtils::getCodeByLine($record['node_path'], $node_start, $node_end) ;
        $item['node'] = $node_item ;
        
        //整理var代码
        $var = $record['var'] ;
        $var_item = array() ;
        if($var instanceof Symbol){
            $var_start = $var->getValue()->getAttribute('startLine') ;
            $var_end = $var->getValue()->getAttribute('endLine') ;
        }elseif(is_string($var)){
            $var_start = $node_start ;
            $var_end = $node_end ;
        }else{
            $var_start = $var->getAttribute('startLine') ;
            $var_end = $var->getAttribute('endLine') ;
        }
        $var_item['line'] = $var_start . "|" . $var_end ;
        $var_item['code'] = FileUtils::getCodeByLine($record['var_path'], $var_start, $var_end) ;
        $item['var'] = $var_item ;
        
        array_push($ret, $item) ;
    }
    return $ret ;
}


if(!isset($_POST['path']) || !isset($_POST['type'])){
	$smarty->display('index.html') ;
	exit() ;
}




//1、从web ui中获取并加载项目工程
$project_path = $_POST['prj_path'] ;  //扫描的工程路径
$scan_path = $_POST['path'] ;   //扫描文件路径
$scan_type = $_POST['type'] ;     //扫描的类型
$encoding = $_POST['encoding'] ;  //CMS的编码   UTF-8 或者  GBK

if(CommonUtils::endsWith($project_path, "/")){
    $last = count($project_path) - 2 ;
    $project_path = substr($project_path, 0, $last) ;
}

if(CommonUtils::endsWith($scan_path, "/")){
    $last = count($scan_path) - 2 ;
    $scan_path = substr($scan_path, 0, $last) ;
}

$scan_type = $scanType = strtoupper($scan_type);
$project_path = str_replace(array('\\','//'), '/', $project_path);
$scan_path = str_replace(array('\\','//'), '/', $scan_path);

$fileName = str_replace('/', '_', $scan_path);
$fileName = str_replace(':', '_', $fileName);
$serialPath = CURR_PATH . "/data/resultConetxtSerialData/" . $fileName;

if (!is_file($serialPath)){
    //创建文件
    $fileHandler = fopen($serialPath, 'w');
    fclose($fileHandler);
}
$results = null;
if(($serial_str = file_get_contents($serialPath)) != ''){
    $results = unserialize($serial_str) ;
}else{
    //3、初始化模块
    $allFiles = FileUtils::getPHPfile($project_path);
    $mainlFiles = FileUtils::mainFileFinder($scan_path);
    $initModule = new InitModule() ;
    $initModule->init($project_path, $allFiles) ;
    
    //4、循环每个文件  进行分析工作
    if(is_file($project_path)){
    	load_file($project_path) ;
    }elseif (is_dir($project_path)){
        $path_list = $mainlFiles;
    	foreach ($path_list as $path){
    		try{
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
    
    //5、处理results 序列化
    $results = ResultContext::getInstance() ;
    file_put_contents($serialPath, serialize($results)) ;
    
}




//6、传给template
$template_res = convertResults($results) ;
$smarty->assign('results',$template_res);
$smarty->display('content.html');


?>