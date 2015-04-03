<?php

require_once 'AbstractContext.class.php';

class FileSummaryContext extends AbstractContext{
	/**
	 * 存放所有分析过的文件摘要FileSummary
	 * @var Array
	 */
	private $fileSummaryMap = array() ;
	
	
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

	
	
	
}


?>