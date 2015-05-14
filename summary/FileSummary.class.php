<?php

class FileSummary{
	/**
	 * 用于存放本文件中的基本块摘要
	 * @var Array
	 */
	private $flowsMap = array() ;
	
	/**
	 * 用于记录该文件摘要对应的文件路径
	 * @var string
	*/
	private $path = '' ;

	public function setPath($path) {
	    $this->path = $path ;
	}
	/**
	 * 用于存放require信息
	 * @var Array
	 */
	private $includeMap = array() ;
	
	
	/**
	 * 将一个summary添加至map中
	 * @param BlockSummary $summary
	 */
	public function addSummaryToMap($summary){
		if($summary){
			array_push($this->summaryMap,$summary) ;
		}
	}
	
	/**
	 * 将一个dataFlow添加至flowMap中
	 * @param dataflow $dataFlow
	 */
	public function addDataFlow($dataFlow){
	    if ($dataFlow){
	        array_push($this->flowsMap, $dataFlow);
	    }
	}
	
	/**
	 * 将一个include信息添加至map中
	 * @param string $summary
	 */
	public function addIncludeToMap($include){
		if($include){
			array_push($this->includeMap,$include) ;
			$this->includeMap = array_unique($this->includeMap);
		}
	}
	
	//--------------------------------getter && setter-------------------------------------------

	public function getSstack() {
		return $this->summaryMap ;
	}

	public function getPath() {
		return $this->path ;
	}
	
	public function getIncludeMap(){
		return $this->includeMap ;
	}
	
	public function getFlowsMap() {
		return $this->flowsMap;
	}
	
}


?>