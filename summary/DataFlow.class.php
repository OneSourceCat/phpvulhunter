<?php
class DataFlow {
	private $location ; //被赋值的变量
	private $value ;   //赋值语句的值
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