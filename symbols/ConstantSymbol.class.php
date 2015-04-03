<?php

/**
 * 收集常量信息的symbol
 * define('ROOT','root') ;
 * 保存ROOT至summary中
 * @author exploit
 *
 */
class ConstantSymbol extends Symbol{
	private $value ; //Value对应的值
	private $name ; //对应的字符串


	/**
	 * 通过AST node分离出concat的各个值
	 * @param unknown $node
	 */
	public function setValueByNode($node){
		$this->value = $node->name ;
	}
	
	//-------------------------getter && setter-------------------------------
	public function getValue() {
		return $this->value;
	}

	public function setValue($value) {
		$this->value = $value;
	}

	public function getName() {
		return $this->name;
	}

	public function setName($name) {
		$this->name = $name;
	}
}

?>