<?php
require './Symbol.class.php';

class ValueSymbol extends Symbol{
	
	private $value ; //Value对应的值
	private $node ;  
	
	
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