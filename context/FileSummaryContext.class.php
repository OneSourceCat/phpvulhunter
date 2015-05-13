<?php


class FileSummaryContext{
	/**
	 * 存放所有分析过的文件摘要FileSummary
	 * @var array
	 */
	private $fileSummaryMap = array() ;
	
	//单例
	private static $instance ;   
	
	/**
	 * 添加一条FileSummary至Context中
	 * @param FileSummary $sumary
	 */
	public function addSummaryToMap($summary){
		if($summary instanceof FileSummary){
			array_push($this->fileSummaryMap, $summary) ;
		}else{
			return ;
		}
	}
	
	/**
	 * 根据文件的路径查询相关的summary
	 * @param string $path
	 * @return FileSummary 返回查找到的摘要，如果没有找到，返回null
	 */
	public function findSummaryByPath($path){
		foreach ($this->fileSummaryMap as $item){
			if($item->getPath() === $path){
				return $item ;
			}
		}
		return null ;
	}
	
	
	//------------------------getter && setter----------------------------
	public function getFileSummaryMap() {
		return $this->fileSummaryMap;
	}
	
	public function setFileSummaryMap($fileSummaryMap) {
	    $this->fileSummaryMap = $fileSummaryMap;
	}

	//单例模式代码
	private function __construct(){}
	
	public static function getInstance(){
		if(!(self::$instance instanceof self)){
			self::$instance = new self ;
		}
		return self::$instance ;
	}
	
	private function __clone(){}
	
}


?>