<?php

require_once './vendor/autoload.php' ;
ini_set('xdebug.max_nesting_level', 2000);

@define("JUMP_STATEMENT", array('Stmt_If','Stmt_Switch','Stmt_TryCatch','Expr_Ternary','Expr_BinaryOp_LogicalOr')) ;
@define("LOOP_STATEMENT",array('Stmt_For','Stmt_While','Stmt_Foreach','Stmt_Do')) ;
@define("STOP_STATEMENT",array('Stmt_Throw','Stmt_Break','Stmt_Continue')) ;
@define("RETURN_STATEMENT",array('Stmt_Return')) ;

use PhpParser\Node ;

class CFGGenerator{
	
	private $parser ;
	private $traverser ;
	
	public function __construct(){
		$this->parser = new PhpParser\Parser(new PhpParser\Lexer\Emulative) ;
		$this->traverser = new PhpParser\NodeTraverser ;
	}	
	
	/**
	 * 给定一个JUMP类型的Statement，获取分支node
	 * @param unknown $node
	 */
	public function getBranches($node){
		$type = $node->getType();
		$branches = array() ;
		switch ($type){
			case 'Stmt_If':
				//处理if-else结构中的if语句，包括条件和语句
				$if_branch = new Branch($node->cond, $node->stmts) ;
				array_push($branches,$if_branch) ;
				
				//处理elseifs,elseifs为索引数组,由cond和stmts构成
				$elseifs = $node->elseifs ;
				foreach($elseifs as $if){
					$if_branch = new Branch($if->cond, $if->stmts) ;
					array_push($branches,$if_branch) ;
				}
				
				//处理else分支，由stmts组成，没有cond，这里的cond填为"else"
				$if_branch = new Branch('else', $node->stmts) ;
				array_push($branches,$if_branch) ;
				
				break ;
				
			case 'Stmt_Switch':
				//switch语句中的判断条件
				$cases = $node->cases ;
				foreach($cases as $case){
					//switch+case的condition
					$cond_arr = array($node->cond) ;
					array_push($cond_arr,$case->cond) ;
					
					//创建分支
					$case_branch = new Branch($cond_arr, $case->stmts) ;
					array_push($branches,$case_branch) ;
				}
				
				break ;
			
			case 'Stmt_TryCatch':
				//try分支
				$try_branch = new Branch(NULL, $node->stmts) ;
				
				//catch分支
				$catches = $node->catches ;
				foreach ($catches as $catch){
					$catch_branch = new Branch($catch->type, $catch->stmts) ;
					array_push($branches, $catch_branch) ;
				}
				
				break ;
			
			case 'Expr_Ternary':
				//三元运算 A?B:C
				$if_branch = new Branch($node->cond, $node->if) ;
				array_push($branches, $if_branch) ;
				$else_branch = new Branch('else', $node->else) ;
				array_push($branches,$else_branch) ;
				break ;
			
			case 'Expr_BinaryOp_LogicalOr':
				//A or B的逻辑或运算
				$visitor = new BranchVisitor() ;
				$this->traverser->addVisitor($visitor) ;
				$this->traverser->traverse(array($node)) ;
				$branches = $visitor->branches ;
				break ;
				
		}
		
		return $branches ;
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
			if(in_array($node->getType(), JUMP_STATEMENT)){
				$nextBlock = new BasicBlock() ;
				//对每个分支，建立相应的基本块
				
			}
		}
		
	}
	
}


/**
 * 跳转语句的分支结构类
 * @author Administrator
 *
 */
class Branch{
	private $condition ;
	private $nodes = array() ;
	
	/**
	 * 构造函数
	 * @param $cond  跳转的条件
	 * @param $nodes 分支中携带的所有nodes
	 */
	public function __construct($cond,$nodes){
		$this->condition = $cond ;
		$this->nodes = $nodes ;
		
		//将跳转的条件也加入至nodes中
		if(is_array($this->condition)){
			foreach ($this->condition as $cond){
				array_unshift($this->nodes, $cond) ;
			}
		}else{
			array_unshift($this->nodes, $this->condition) ;
		}
	}
	
	/**
	 * getter for $condition
	 */
	public function getCondition(){
		return $this->condition	 ;
	}	
	
	/**
	 * getter for $nodes
	 * @return multitype:
	 */
	public function getNodes(){
		return $this->nodes ;	
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

/**
 * 用来遍历LogicalOr节点，并将所有的分支分离出来
 * @author Administrator
 *
 */
class BranchVisitor extends PhpParser\NodeVisitorAbstract{
	public $branches = array() ;
	/**
	 * 将or表达式的分支分离成分支数组
	 * @param $node  LogicalOr节点
	 * @return $branches 分支数组
	 */
	public function leaveNode(Node $node) {
		if($node instanceof PhpParser\Node\Expr\BinaryOp\LogicalOr){
			if(!($node->left instanceof PhpParser\Node\Expr\BinaryOp\LogicalOr) && !($node->right instanceof PhpParser\Node\Expr\BinaryOp\LogicalOr)){
				array_push($this->branches,$node->left) ;
				array_push($this->branches,$node->right) ;
			}else{
				if(!($node->left instanceof PhpParser\Node\Expr\BinaryOp\LogicalOr)){
					array_push($this->branches,$node->left) ;
				}elseif(!($node->right instanceof PhpParser\Node\Expr\BinaryOp\LogicalOr)){
					array_push($this->branches,$node->right) ;
				}
			}
		}
	}
	
}


$cfg = new CFGGenerator() ;
$visitor = new MyVisitor() ;
$parser = new PhpParser\Parser(new PhpParser\Lexer\Emulative) ;
$traverser = new PhpParser\NodeTraverser ;
$code = file_get_contents('./test/simple_demo.php') ;
$stmts = $parser->parse($code) ;
$traverser->addVisitor($visitor) ;
$traverser->traverse($stmts) ;
$nodes = $visitor->getNodes() ;
$branches = NULL ;
foreach ($nodes as $node){
	if($node instanceof PhpParser\Node\Stmt\If_ ){
		$branches = $cfg->getBranches($node) ;
	}elseif($node instanceof PhpParser\Node\Stmt\Switch_ ){
		$branches = $cfg->getBranches($node) ;
	}else{
		$branches = $cfg->getBranches($node) ;
	}
}
echo "<pre>" ;
print_r($branches) ;

?>












































