<?php


class FileSummaryContext{
	/**
	 * 存放所有分析过的文件摘要FileSummary
	 * @var Array
	 */
	private $fileSummaryMap = array() ;
	
	private static $instance ;   //单例
	
	
	//------------------------getter && setter----------------------------
	/**
	 * @return the $fileSummaryMap
	 */
	public function getFileSummaryMap() {
		return $this->fileSummaryMap;
	}

	/**
	 * @param multitype: $fileSummaryMap
	 */
	public function setFileSummaryMap($fileSummaryMap) {
		$this->fileSummaryMap = $fileSummaryMap;
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