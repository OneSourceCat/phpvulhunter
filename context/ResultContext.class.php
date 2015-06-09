<?php

/**
 * 该上下文类用来保存report函数报告的漏洞
 * @author Exploit
 *
 */
class ResultContext {
	//单例
	private static $instance ;  
	
	//结果集数组
	private $resArr = array() ;
	
	/**
	 * 添加到结果集数组中
	 * @param Result $ele
	 */
	public function addResElement($ele){
		if($ele instanceof  Result){
			array_push($this->resArr, $ele) ;
		}
	}
	
	/**
	 * 将结果集数组归并
	 * @param unknown $arr
	 */
	public function mergeResultArray($arr){
	    foreach ($arr as $value){
	        if($this->isRecordExists($value) == false){
	            array_push($this->resArr, $value) ;
	        }
	    }
	}
	
	/**
	 * 查看一条记录是否在结果集中存在
	 * @param Result $record
	 * @return boolean
	 */
    public function isRecordExists($record){
		foreach ($this->resArr as $value){
		    $value_record = $value->getRecord() ; 
			if($value_record == $record->getRecord()){
				return true ;
			}
		}
		return false ;
	}
	
	/**
	 * 获取结果集数据的条数
	 * @return number
	 */
	public function getCount(){
		return count($this->resArr) ;	
	}
	
	//getter
	public function getResArr() {
		return $this->resArr;
	}

	
	/**
     * @param multitype: $resArr
     */
    public function setResArr($resArr)
    {
        $this->resArr = $resArr;
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
 * 		'path' => $path,  //漏洞的页面路径
 *		'node'=> null, //调用sink的node
 *		'var' => null, //追踪的变量node
 *		'type' => 'SQLI'   //漏洞类型
 * ) ;
 * 
 * @author Exploit
 *
 */
class Result{
	private $record = array();
	//construct
	public function __construct($node_path, $var_path, $type, $node, $var){
		$this->record = array(
				'node_path' => $node_path,
				'node'=> $node,
                'var_path' => $var_path,
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