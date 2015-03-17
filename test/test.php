<?php
/**
 * 单元测试类
 * @author Administrator
 *
 */
define('ROOT_PATH',str_replace("\\", "/", dirname(__FILE__))) ;
require ROOT_PATH . '/vendor/autoload.php' ;
ini_set('xdebug.max_nesting_level', 2000);

class TestClass extends PHPUnit_Framework_TestCase{
	
	function testParser(){
		$parser = new PhpParser\Parser(new PhpParser\Lexer\Emulative) ;
		$traverser = new PhpParser\NodeTraverser ;
		
		//$code = file_get_contents('source.class2.php') ;
		//$code = file_get_contents('./test/sub.php') ;
		$code = file_get_contents('D:/MySoftware/wamp-php-5.3/www/code/phpvulhunter/test/simple_demo.php') ;
		
		$stmts = $parser->parse($code) ;
		echo "<pre>" ;
		//print_r($stmts) ;
		
		$traverser->addVisitor(new MyVisitor) ;
		$traverser->traverse($stmts) ;
	}
	
	/**
	 * 测试concat符号生成
	 */
	public function testConcatSymbol(){
		require ROOT_PATH . '/symbols/ConcatSymbol.class.php';
		$parser = new PhpParser\Parser(new PhpParser\Lexer\Emulative) ;
		$traverser = new PhpParser\NodeTraverser ;
		$code = file_get_contents(ROOT_PATH . '/test/simple_demo.php') ;
		$stmts = $parser->parse($code) ;
		$visitor = new MyVisitor() ;
		$traverser->addVisitor($visitor) ;
		$traverser->traverse($stmts) ;
		$symbol = new ConcatSymbol() ;
		$symbol->setItemByNode($visitor->concat) ;
		print_r($symbol->getItems()) ;
	}

}


use PhpParser\Node ;
class MyVisitor extends PhpParser\NodeVisitorAbstract{
	public $concat ;
	public function leaveNode(Node $node){
		$type = $node->getType() ;
		echo  $type;
		if($type == "Expr_BinaryOp_Concat"){
			$this->concat = $node ;
			return ;
		}
		echo "\n" ;
		//print_r($node) ;
	}
	
}




?>








