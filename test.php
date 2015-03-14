<?php
/**
 * 单元测试类
 * @author Administrator
 *
 */

class TestClass extends PHPUnit_Framework_TestCase{
	function add($a,$b){
		return $a + $b ;
	}

	function testAdd(){
		echo $this->add(1, 2) ;
	}

}


?>