<?php
/**
 * 保存基本块中的常量信息
 * @author exploit
 *
 */
class Constants {
	private $name ;
	private $value ;

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