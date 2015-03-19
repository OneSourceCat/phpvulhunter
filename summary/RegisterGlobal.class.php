<?php
class RegisterGlobal {
	private $name ;   //注册的内容
	private $isUrlOverWrite = true ;
	
	public function getIsUrlOverWrite() {
		return $this->isOverWrite;
	}

	public function setIsUrlOverWrite($isOverWrite) {
		$this->isOverWrite = $isOverWrite;
	}

	public function getName() {
		return $this->name;
	}

	public function setName($name) {
		$this->name = $name;
	}

	
	
}

?>