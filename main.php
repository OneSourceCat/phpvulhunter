<?php

require_once 'global.php';

header("Content-type:text/html;charset=utf-8") ;

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
	$endLine = $cfg->getEndLine($nodes);
	$ret = $cfg->CFGBuilder($nodes, NULL, NULL, NULL,$endLine) ;
}


//1、从web ui中获取并加载项目工程
$project_path = $_POST['path'] ;
$scan_type = $_POST['type'] ;


//2、循环每个文件  进行分析工作
if(is_file($project_path)){
	load_file($project_path) ;
}elseif (is_dir($project_path)){
	$dirs = FileUtils::getDir($project_path) ;
	foreach ($dirs as $dir){
		$path_list = FileUtils::getPHPfile($dir) ;
		foreach ($path_list as $path){
			load_file($path) ;
		}
	}
}


//3、获取ResultContext  传给template
$results = ResultContext::getInstance() ;




?>