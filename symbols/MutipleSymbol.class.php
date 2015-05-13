<?php
/**
 * 多个symbol的容器
 * 比如三元运算a?b:c
 * 使用容器存储这些symbol
 * @author exploit
 */
class MutipleSymbol extends Symbol{
	private $symbols = array();  
	
	/**
	 * 分离isset结构中的类型
	 * isset($id) ? $_GET['id'] : 2;
	 * 转为:
	 * 		array($_GET[id],2)
	 * @param unknown $node
	 */
	public function setItemByNode($node){
		//处理三元表达式
		if($node->getType() == "Expr_Ternary"){
			$if_node = SymbolUtils::getSymbolByNode($node->if) ;
			$else_node = SymbolUtils::getSymbolByNode($node->else) ;
			$this->addSymbol($if_node) ;
			$this->addSymbol($else_node) ;
		}
		
	}
	
	private function addSymbol($symbol){
		array_push($this->symbols, $symbol) ;
	}
	
	public function getSymbols() {
		return $this->symbols;
	}

}

?>