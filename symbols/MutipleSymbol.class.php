<?php
/**
 * 多个symbol的容器
 * 比如三元运算a?b:c
 * 使用容器存储这些symbol
 * @author exploit
 */
class MutipleSymbol {
	private $symbols = array();  
	
	private function addSymbol($symbol){
		array_push($this->symbols, $symbol) ;
	}
	
	public function getSymbols() {
		return $this->symbols;
	}

}

?>