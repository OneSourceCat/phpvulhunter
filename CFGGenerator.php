<?php

require_once './vendor/autoload.php' ;
ini_set('xdebug.max_nesting_level', 2000);
require_once './BasicBlock.php';


$JUMP_STATEMENT = array('Stmt_If','Stmt_Switch','Stmt_TryCatch','Expr_Ternary','Expr_BinaryOp_LogicalOr') ;
$LOOP_STATEMENT = array('Stmt_For','Stmt_While','Stmt_Foreach','Stmt_Do') ;
$STOP_STATEMENT = array('Stmt_Throw','Stmt_Break','Stmt_Continue') ;
$RETURN_STATEMENT = array('Stmt_Return') ;

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
				if($elseifs){
					foreach($elseifs as $if){
						$if_branch = new Branch($if->cond, $if->stmts) ;
						array_push($branches,$if_branch) ;
					}	
				}
				
				//处理else分支，由stmts组成，没有cond，这里的cond填为"else"
				if($node->else){
					$if_branch = new Branch('else', $node->else) ;
					array_push($branches,$if_branch) ;
				}
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
	 * 处理循环结构，将循环变量添加到基本块
	 * @param unknown $node  AST Node
	 * @param unknown $block  BasicBlock
	 */
	public function addLoopVariable($node,$block){
		switch ($node->getType()){
			case 'Stmt_For':  //for(i=0;i<3;i++) ===> extract var i
				$block->loop_var = $node->init[0] ;
				break ;
			case 'Stmt_While':  //while(cond) ====> extract cond
				$block->loop_var = $node->cond ;
				break ;
			case 'Stmt_Foreach':  //foreach($nodes as $node) ======> extract $nodes
				$block->loop_var = $node->expr ;
				break ;
			case 'Stmt_Do':   //do{}while(cond); =====> extract cond
				$block->loop_var = $node->cond ;
				break ;
		}
	}
	
	/**
	 * 给定AST Nodes集合，返回结束的行号
	 * @param unknown $nodes
	 */
	public function getEndLine($nodes){
		return end($nodes)->getAttribute('endLine') ;
	}
	
	
	
	/**
	 * 由AST节点创建相应的CFG，用于后续分析
	 * 
	 * @param $nodes  传入的PHP file的所有nodes
	 * @param $condition   构建CFGNode时的跳转信息
	 * @param $pEntryBlock   入口基本块
	 * @param $pNextBlock   下一个基本块
	 */
	public function CFGBuilder($nodes,$condition,$pEntryBlock,$pNextBlock,$endLine=0){
		echo "<pre>" ;
		global $JUMP_STATEMENT,$LOOP_STATEMENT,$STOP_STATEMENT,$RETURN_STATEMENT ;
		$currBlock = new BasicBlock() ;
		
		//创建一个CFG节点的边
		if($pEntryBlock){
			$block_edge = new CFGEdge($pEntryBlock, $currBlock,$condition) ;
			$pEntryBlock->addOutEdge($block_edge) ;
			$currBlock->addInEdge($block_edge) ;
		}

		//迭代每个AST node
		foreach($nodes as $node){
			if(!is_object($node))continue ;
			//判断node是否是结束node
			if($node->getAttribute('endLine') == 9){
				$currBlock->is_exit = true ;
			}
			
			//如果节点是跳转类型的语句
			if(in_array($node->getType(), $JUMP_STATEMENT)){
				//生成基本块的摘要
				//simulate(currBlock) ;
				$nextBlock = new BasicBlock() ;
				//对每个分支，建立相应的基本块
				$branches = $this->getBranches($node) ;
				foreach ($branches as $b){
					$this->CFGBuilder($b->nodes, $b->condition, $currBlock, $nextBlock)	;				
				}
				//var_dump($nextBlock) ;
				$currBlock = $nextBlock ;
				
			}elseif(in_array($node->getType(), $LOOP_STATEMENT)){  //如果节点是循环语句
				$this->addLoopVariable($node, $currBlock) ; //加入循环条件
				//simulate($currBlock) ;
				$currBlock->nodes = $node->stmts ;
				$nextBlock = new BasicBlock() ;
				$this->CFGBuilder($node->stmts, NULL, $currBlock, $nextBlock) ;
				$currBlock = $nextBlock ;
				
			}elseif(in_array($node->getType(), $STOP_STATEMENT)){
				$currBlock->is_exit = true ;
				break ;
			}elseif(in_array($node->getType(),$RETURN_STATEMENT)){
				$currBlock->addNode($node) ;
				//simulate($currBlock) ;
				return ;
			}else{
				$currBlock->addNode($node);
			}
		}
		
		
		
		//simulate(currBlock) ;
		print_r($currBlock) ;
		if($pNextBlock && !$currBlock->is_exit){
			$block_edge = new CFGEdge($currBlock, $pNextBlock) ;
			$currBlock->addOutEdge($block_edge) ;
			$pNextBlock->addInEdge($block_edge) ;
		}
		
		//print_r($currBlock) ;
		
	}
	
}



/**
 * 跳转语句的分支结构类
 * @author Administrator
 *
 */
class Branch{
	public $condition ;
	public $nodes ;
	
	/**
	 * 构造函数
	 * @param $cond  跳转的条件
	 * @param $nodes 分支中携带的所有nodes
	 */
	public function __construct($cond,$nodes){
		$this->condition = array($cond) ;
		if(is_array($nodes)){
			$this->nodes = $nodes ;
		}else{
			$this->nodes = array($nodes) ;
		}
		
		
		//将跳转的条件也加入至nodes中
		if(is_array($this->condition)){
			foreach ($this->condition as $cond){
				array_unshift($this->nodes, $cond) ;
			}
		}else{
			array_unshift($this->nodes, $this->condition) ;
		}
	}
	
}

/**
 * 获取PHP File中所有的AST节点的访问者
 * @author Administrator
 *
 */
class MyVisitor extends PhpParser\NodeVisitorAbstract{
	private $nodes = array();
	
	public function beforeTraverse(array $nodes){
		$this->nodes = $nodes ;
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
// $branches = NULL ;
// foreach ($nodes as $node){
// 	if($node instanceof PhpParser\Node\Stmt\If_ ){
// 		$branches = $cfg->getBranches($node) ;
// 	}elseif($node instanceof PhpParser\Node\Stmt\Switch_ ){
// 		$branches = $cfg->getBranches($node) ;
// 	}else{
// 		$branches = $cfg->getBranches($node) ;
// 	}
// }
// echo "<pre>" ;
// print_r($branches) ;

$pEntryBlock = new BasicBlock() ;
$pEntryBlock->is_entry = true ;
$endLine = $cfg->getEndLine($nodes);
$ret = $cfg->CFGBuilder($nodes, NULL, NULL, NULL,$endLine) ;
echo "<pre>" ;
//print_r($pEntryBlock) ;
?>












































