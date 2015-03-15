<?php
define('CURR_PATH',str_replace("\\", "/", dirname(__FILE__))) ;
require "Symbol.class.php" ;
require_once CURR_PATH . "/../vendor/autoload.php" ;
require CURR_PATH . '/../utils/SymbolUtils.class.php';
ini_set('xdebug.max_nesting_level', 2000);

class ConcatSymbol extends Symbol{
	private $value ; //Value对应的值
	private $items = array() ; //concat的组成部门
	
	/**
	 * 获取concat中的各个成员
	 * 形如：
	 *  （1） "aaaa" . $b . "bbbbbb" ;
	 *  	使用时，只需要将最大的一个concat传递过来当做参数$node即可
	 * @param AST $node
	 */
	public function setItemByNode($node){
		$type = $node->getType() ;
		if($type == "Expr_BinaryOp_Concat"){
			$parser = new PhpParser\Parser(new PhpParser\Lexer\Emulative) ;
			$traverser = new PhpParser\NodeTraverser;
			$visitor = new BinaryOpConcatVisitor() ;
			$traverser->addVisitor($visitor) ;
			$traverser->traverse(array($node)) ;
			$this->items = $visitor->getItems() ;
			
		}
		
	}
	
	
	public function getItems(){
		return $this->items ;
	}
	
	/**
	 * @return the $value
	 */
	public function getValue() {
		return $this->value;
	}
	
	/**
	 * @param field_type $value
	 */
	public function setValue($value) {
		$this->value = $value;
	}
}

/**
 * 辅助类
 * 用于收集concat的各个组成部分
 * 需要遍历的node集合是最外层的concat节点
 * 
 * public function leaveNode(Node $node){
 *		$type = $node->getType() ;
 *		if($type == "Expr_BinaryOp_Concat"){
 *			$this->concat = $node ;
 *			return ;
 *		}
 *		//print_r($node) ;
 *	}
 * 
 */
use PhpParser\Node ;
class BinaryOpConcatVisitor extends PhpParser\NodeVisitorAbstract{
	private $items =array() ;   //concat字符串的各个部分
	public function leaveNode(Node $node){
		$type = $node->getType() ;
		if($type == "Expr_BinaryOp_Concat"){
			if($node->right){
				
				//如果不是常量   则收入至items中
				if(!SymbolUtils::isValue($node->right)){
					array_push($this->items,$node->right) ;
				}
				
			}
			if($node->left->getType() != "Expr_BinaryOp_Concat"){
				if(!SymbolUtils::isValue($node->right)){
					array_push($this->items,$node->left) ;
				}
				
			}
		}
	}
	
	public function getItems(){
		return $this->items ;
	}
	
}


?>





