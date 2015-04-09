<?php
/**
 * 保存基本块中的常量信息
 * @author exploit
 *
 */
class Constants {
	private $name ;  //存放常量名string
	private $value ;  //存放常量的值->node

	public function getName() {
		return $this->name;
	}

	public function getValue() {
		return $this->value;
	}

	public function setName($name) {
		$this->name = $name;
	}

	public function setValue($value) {
		$this->value = $value;
	}

	
	
}

?>