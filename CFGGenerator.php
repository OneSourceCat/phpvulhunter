<?php

require_once './vendor/autoload.php' ;
ini_set('xdebug.max_nesting_level', 2000);

define('JUMP_STATEMENT', array('Stmt_If','Stmt_Switch','Stmt_TryCatch','Expr_Ternary','Expr_BinaryOp_BooleanOr')) ;
define('LOOP_STATEMENT',array('Stmt_For','Stmt_While','Stmt_Foreach','Stmt_Do')) ;
define('STOP_STATEMENT',array('Stmt_Throw','Stmt_Break','Stmt_Continue')) ;
define('RETURN_STATEMENT',array('Stmt_Return')) ;

use PhpParser\Node ;

class CFGGenerator{
	
	private $parser ;
	private $traverser ;
	
	public function __construct(){
		$this->parser = new PhpParser\Parser(new PhpParser\Lexer\Emulative) ;
		$this->traverser = new PhpParser\NodeTraverser ;
	}	
	
	/**
	 * 由AST节点创建相应的CFG，用于后续分析
	 * 
	 * @param unknown $nodes  传入的PHP file的所有nodes
	 * @param unknown $condition   构建CFGNode时的跳转信息
	 * @param unknown $pEntryBlock   入口基本块
	 * @param unknown $pNextBlock   下一个基本块
	 */
	public function CFGBuilder($nodes,$condition,$pEntryBlock,$pNextBlock){
		$currBlock = new BasicBlock() ;
		
		//创建一个CFG节点的边
		if($pEntryBlock){
			$block_edge = new CFGEdge($pEntryBlock, $pNextBlock,$condition) ;
		}
		
		//迭代每个AST node
		foreach($nodes as $node){
			
		}
		
	}
	
}


class MyVisitor extends PhpParser\NodeVisitorAbstract{
	private $nodes = array();
	
	public function leaveNode(Node $node) {
		$this->addNode($node) ;
	}

	public function addNode($node){
		array_push($this->nodes, $node) ;
	}
	
	public function getNodes(){
		return $this->nodes ;
	}
	
}

$visitor = new MyVisitor() ;
$parser = new PhpParser\Parser(new PhpParser\Lexer\Emulative) ;
$traverser = new PhpParser\NodeTraverser ;
$code = file_get_contents('./test/simple_demo.php') ;
$stmts = $parser->parse($code) ;
$traverser->addVisitor($visitor) ;
$traverser->traverse($stmts) ;


?>












































