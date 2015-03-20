<?php

/**
 * 针对静态的string和integer以及float来生成symbol
 * Notice：这里没有继承Symbol基类是因为静态string和数值没有相关的三元组信息
 * @author exploit
 *
 */
class ValueSymbol {
	
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