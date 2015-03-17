<?php
require './Symbol.class.php';

class ValueSymbol extends Symbol{
	
	private $value ; //Value对应的值
	
	/**
	 * 通过AST node来设置Value符号的值
	 * @param AST $node
	 */
	public function setValueByNode($node){
		$type = $node->getType() ;
		$this->value = $node->value ;
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