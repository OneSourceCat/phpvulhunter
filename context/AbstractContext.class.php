<?php
class AbstractContext {
	private static $instance ;   //单例
	
	private function __construct(){
	
	}
	
	public static function getInstance(){
		if(!(self::$instance instanceof self)){
			self::$instance = new self ;
		}
		return self::$instance ;
	}
	
	private function __clone(){
	
	}
}





?>