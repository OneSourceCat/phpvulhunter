<?php
/**
 * 多个symbol的容器
 * 比如三元运算a?b:c
 * 使用容器存储这些symbol
 * @author exploit
 */
class MutipleSymbol extends Symbol{
	private $symbols = array();  

    public function setSymbols($symbols){
        $this->symbols = $symbols;
    }

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
		    if($node->if->getType() == "Expr_Ternary"){
		        $this->setItemByNode($node->if) ;
		    }
		    if($node->else->getType() == "Expr_Ternary"){
		        $this->setItemByNode($node->else) ;
		    }
			$if_node = SymbolUtils::getSymbolByNode($node->if) ;
			$else_node = SymbolUtils::getSymbolByNode($node->else) ;
		
			$if_node && $this->addSymbol($if_node) ;
			$else_node && $this->addSymbol($else_node) ;
		}else{
		    return ;
		}
		
	}
	
	
	private function addSymbol($symbol){
		array_push($this->symbols, $symbol) ;
	}
	
	public function getSymbols() {
		return $this->symbols;
	}
	
	public function getValue(){
	    return $this->getSymbols() ;
	}

}

?>