<?php

class ArrayDimFetchSymbol extends Symbol{
	private $value ; //Value对应的值
	private $name ;  //数组名
	
	/**
	 * 通过node获取变量名称
	 * @param AST $node
	 */
	public function setNameByNode($node){
	    $this->name = NodeUtils::getNodeStringName($node) ;
	}
	
	
	public function getName(){
	    return $this->name ;
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