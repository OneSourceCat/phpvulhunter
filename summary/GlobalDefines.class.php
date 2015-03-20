<?php
/**
 * 保存基本块中的全局变量信息
 * 转换为string类型
 * @author exploit
 */
class GlobalDefines {
	//比如global $a ----->  保存a
	private $name ;  //变量的名字

	public function getName() {
		return $this->name;
	}

	public function setName($name) {
		$this->name = $name;
	}
 
}

?>