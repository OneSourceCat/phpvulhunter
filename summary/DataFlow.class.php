<?php

/**
 * 重要的数据流信息
 * 主要针对赋值语句
 * @author exploit
 */
class DataFlow {

	private $location ; //被赋值的变量  Symbols
	private $value ;   //赋值语句右边的值   Node
	private $name ;   //变量的名字string

	
	
	//--------------------------getter && setter--------------------------------------
	public function getLocation() {
		return $this->location;
	}

	public function getValue() {
		return $this->value;
	}

	public function setLocation($location) {
		$this->location = $location;
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