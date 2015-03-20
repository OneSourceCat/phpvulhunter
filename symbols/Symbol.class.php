<?php

class Symbol {
	
	private $type ;   //类型，默认为string
	private $encoding ;  //symbol的编码信息
	private $sanitization ;   //symbol的净化信息
	
	/**
	 * 初始化三元组:
	 * 		type为string类型
	 * 		encoding为空，默认进行编码操作
	 * 		sanitization为空，默认没有进行净化操作
	 */
	public function __construct(){
		$this->type = 'string' ;
		$this->encoding = array() ;
		$this->sanitization = array() ;
	}
	
	/**
	 * 添加编码操作
	 * 如进行了base64编码，添加'base64'
	 * @param string $encoding
	 */
	public function addEncoding($encoding){
		//如果编码信息是encode，直接添加
		array_push($this->encoding,$encoding) ;
	}
	
	/**
	 * 添加解码信息
	 * @param string $decoding
	 */
	public function addDecoding($decoding){
		//如果编码信息是decode，先查找已有的信息，如果发现同名的encode，就消除
		$key = array_search($decoding, $this->encoding) ;
		array_splice($this->decoding, $key ,1) ;
	}
	
	
	/**
	 * 添加净化操作
	 * 如进行了addslashes，则添加'addslashes'
	 * @param unknown $sanitization
	 */
	public function addSanitization($sanitization){
		array_push($this->sanitization, $sanitization) ;
	}
	
	
	//----------------------getter && setter--------------------------------------
	public function getType() {
		return $this->type;
	}


	public function getEncoding() {
		return $this->encoding;
	}


	public function getSanitization() {
		return $this->sanitization;
	}

	public function setType($type) {
		$this->type = $type;
	}


	public function setEncoding($encoding) {
		$this->encoding = $encoding;
	}


	public function setSanitization($sanitization) {
		$this->sanitization = $sanitization;
	}

	
	
}

?>
