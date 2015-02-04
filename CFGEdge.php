<?php

class CFGEdge{
	private $source ; //边的来源
	private $destination ;  //边的目的
	private $condition ;  //边上携带的条件
	private $sanitization_info ;  //边上携带的sanitization信息
	private $encoding_info ;  //边上携带的编码信息
	
	//构造函数
	public function __construct($source,$destination){
		$this->sanitization_info = array() ;
		$this->encoding_info = array() ;
		$this->source = $source ;
		$this->destination = $destination ;
	}
	
	/**
	 * 添加新的sanitization信息
	 * @param unknown $info
	 */
	public function addSanitinazionInfo($info){
		if($info){
			array_push($this->sanitization_info, $info) ;
		}else{
			return ;
		}
	}
	
	/**
	 * 添加新的encoding信息
	 * @param unknown $info
	 */
	public function addEncodingInfo($info){
		if($info){
			array_push($this->encoding_info, $info) ;
		}else{
			return ;
		}
	}
	
	
	/**
	 * @return the $source
	 */
	public function getSource() {
		return $this->source;
	}

	/**
	 * @return the $destination
	 */
	public function getDestination() {
		return $this->destination;
	}

	/**
	 * @return the $condition
	 */
	public function getCondition() {
		return $this->condition;
	}

	/**
	 * @return the $sanitization_info
	 */
	public function getSanitization_info() {
		return $this->sanitization_info;
	}

	/**
	 * @return the $encoding_info
	 */
	public function getEncoding_info() {
		return $this->encoding_info;
	}

	/**
	 * @param field_type $source
	 */
	public function setSource($source) {
		$this->source = $source;
	}

	/**
	 * @param field_type $destination
	 */
	public function setDestination($destination) {
		$this->destination = $destination;
	}

	/**
	 * @param field_type $condition
	 */
	public function setCondition($condition) {
		$this->condition = $condition;
	}

	/**
	 * @param multitype: $sanitization_info
	 */
	public function setSanitization_info($sanitization_info) {
		$this->sanitization_info = $sanitization_info;
	}

	/**
	 * @param multitype: $encoding_info
	 */
	public function setEncoding_info($encoding_info) {
		$this->encoding_info = $encoding_info;
	}

}

?>















