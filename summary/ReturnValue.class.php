<?php
/**
 * 记录一个基本块的返回值，每个基本块只有一个返回值，
 * 这个返回值取决于当前基本块的最后一个语句的return
 * @author exploit
 */
class ReturnValue {
	private $value ; //返回值
	
	public function getValue() {
		return $this->value;
	}

	public function setValue($value) {
		$this->value = $value;
	}
	
}

?>