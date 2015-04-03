<?php

class FileSummary{
	/**
	 * 用于存放本文件中的基本块摘要
	 * @var Array
	 */
	private $summaryMap = array() ;
	/**
	 * 用于记录该文件摘要对应的文件路径
	 * @var string
	*/
	private $path = '' ;
	
	/**
	 * 将一个summary添加至map中
	 * @param BlockSummary $summary
	 */
	public function addSummaryToMap($summary){
		if($summary){
			array_push($this->summaryMap,$summary) ;
		}
	}
	
	//--------------------------------getter && setter-------------------------------------------
	/**
	 * @return the $sstack
	 */
	public function getSstack() {
		return $this->sstack;
	}

	/**
	 * @return the $path
	 */
	public function getPath() {
		return $this->path;
	}

	/**
	 * @param multitype: $sstack
	 */
	public function setSstack($sstack) {
		$this->sstack = $sstack;
	}

	/**
	 * @param string $path
	 */
	public function setPath($path) {
		$this->path = $path;
	}

	
	
	
}


?>