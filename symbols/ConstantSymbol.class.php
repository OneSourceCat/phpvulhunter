<?php
require './Symbol.class.php' ;

class ConstantSymbol extends Symbol{
	private $value ; //Value对应的值
	
	/**
	 * 通过AST node分离出concat的各个值
	 * @param unknown $node
	 */
	public function setValueByNode($node){
		$this->value = $node->name ;
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

?>