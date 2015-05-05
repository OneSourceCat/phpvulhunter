<?php

/**
 * 该上下文类用来保存report函数报告的漏洞
 * @author Exploit
 *
 */
class ResultContext {
	//单例
	private static $instance ;  
	
	private $resArr = array() ;
	
	/**
	 * 添加到结果集数组中
	 * @param Result $ele
	 */
	public function addResElement($ele){
		array_push($this->resArr, $ele) ;
	}
	
	
	/**
	 * getter
	 */
	public function getResArr() {
		return $this->resArr;
	}

	//--------------------单例模式---------------------------------
	private function __construct(){}

	private function __clone(){}
	
	public static function getInstance(){
		if(!(self::$instance instanceof self)){
			self::$instance = new self ;
		}
		return self::$instance ;
	}
}


/**
 * 一条漏洞记录
 * array(
 *		'node'=> null, //调用sink的node
 *		'var' => null, //追踪的变量node
 *		'type' => ''   //漏洞类型
 * ) ;
 * 
 * @author Exploit
 *
 */
class Result{
	private $record = array();
	//construct
	public function __construct($type,$node,$var){
		$this->record = array(
				'node'=> $node,
				'var' => $var,
				'type' => $type
		) ;
	}
	
	//getter
	public function getRecord() {
		return $this->record;
	}

	
}












?>