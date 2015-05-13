<?php


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
	
	
	///---------------------getter && setter-------------------------------
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
 * Notice:ConcatSymbol只收集变量，不收集常量
 * 
 * public function leaveNode(Node $node){
 *		$type = $node->getType() ;
 *		if($type == "Expr_BinaryOp_Concat"){
 *			$this->concat = $node ;
 *			return ;
 *		}
 *	}
 * 
 */
use PhpParser\Node ;
class BinaryOpConcatVisitor extends PhpParser\NodeVisitorAbstract{
	//concat字符串的各个部分
	private $items = array() ;   
	
	public function leaveNode(Node $node){
		$type = $node->getType() ;
		if($type == "Expr_BinaryOp_Concat"){
			if($node->right){
				//转为symbol
				$right_symbol = SymbolUtils::getSymbolByNode($node->right) ;
				($right_symbol != null) && array_push($this->items,$right_symbol) ;
			}
			if($node->left->getType() != "Expr_BinaryOp_Concat"){
				$left_symbol = SymbolUtils::getSymbolByNode($node->left) ;
				($left_symbol != null) && array_push($this->items,$left_symbol) ;
			}
		}else if($type == "Scalar_Encapsed"){
			foreach ($node->parts as $item){
				if(!is_object($item)){
					$valueSymbol = new ValueSymbol() ;
					$valueSymbol->setValue($item) ;
					($valueSymbol != null) && array_push($this->items, $valueSymbol) ;
				}else{
					$setItem = SymbolUtils::getSymbolByNode($item) ;
					($setItem != null) && array_push($this->items, $setItem) ;
				}
			}
		}
		
	}
	
	public function getItems(){
		return $this->items ;
	}
	
}


?>





