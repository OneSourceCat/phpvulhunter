<?php

define('CURR_PATH',str_replace("\\", "/", dirname(__FILE__))) ;
require_once CURR_PATH . '/vendor/autoload.php' ;
require_once CURR_PATH . '/BasicBlock.php';
require_once CURR_PATH . '/symbols/Symbol.class.php' ;
require_once CURR_PATH . '/utils/SymbolUtils.class.php';
require_once CURR_PATH . '/symbols/ValueSymbol.class.php';
require_once CURR_PATH . '/symbols/VariableSymbol.class.php';
require_once CURR_PATH . '/symbols/MutipleSymbol.class.php';
require_once CURR_PATH . '/symbols/ArrayDimFetchSymbol.class.php';
require_once CURR_PATH . '/symbols/ConcatSymbol.class.php';
require_once CURR_PATH . '/symbols/ConstantSymbol.class.php';
require_once CURR_PATH . '/utils/NodeUtils.class.php';
ini_set('xdebug.max_nesting_level', 2000);


use PhpParser\Node ;
class MyVisitor extends PhpParser\NodeVisitorAbstract{
	public function leaveNode(Node $node){
		echo $node->getType() . "<br/>";
		echo NodeUtils::getNodeStringName($node) ;
	}
}


$parser = new PhpParser\Parser(new PhpParser\Lexer\Emulative) ;
$traverser = new PhpParser\NodeTraverser ;

$code = file_get_contents('./test/simple_demo.php') ;

$stmts = $parser->parse($code) ;
echo "<pre>" ;
//print_r($stmts) ;
$visitor = new MyVisitor() ;
$traverser->addVisitor($visitor) ;
$traverser->traverse($stmts) ;

print_r($stmts) ;


function test($nodes){
	foreach($nodes as $node){
		if(node == 'function_call' && is_sink($node)){
			//找到了mysql_query
			$pos = senstivePostion($node,$block) ;  //array(where,id)
			$del_arg_pos = getPosition($del) ;  //array(id,where)
			$posArr = array();  //返回
			foreach($del_arg_pos as $k => $v){
				if(in_array($pos,$v)){
					posArr.add($k) ;
				}
			}
			return $posArr ;
		}
	}
}

test($stmts) ;

?>




























