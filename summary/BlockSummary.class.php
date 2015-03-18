<?php
/**
 * 定义基本块摘要类
 * 在基本块生成的过程中，必须要进行基本块的模拟，也就是抽取关键信息存储在基本块对应的summary中，
 * 以便后续的污染分析和数据流分析使用
 * @author exploit
 */
class BlockSummary {
	private $dataFlowMap = array() ;  //数据流信息
	private $constantsMap = array() ;  //常量信息
	private $globalDefinesMap = array() ;  //全局变量信息
	private $returnValueMap = array() ;   //返回值信息，用于过程内分析
	private $registerGlobalMap = array() ;  //全局变量的注册信息，如extract
	private $isExitBlock = false ;   //是否是exit或者die的基本块
	
	//--------------------------------getter && setter------------------------------------------
	public function getDataFlowMap() {
		return $this->dataFlowMap;
	}

	public function getConstantsMap() {
		return $this->constantsMap;
	}

	public function getGlobalDefinesMap() {
		return $this->globalDefinesMap;
	}

	public function getReturnValueMap() {
		return $this->returnValueMap;
	}

	public function getRegisterGlobalMap() {
		return $this->registerGlobalMap;
	}

	public function getIsExitBlock() {
		return $this->isExitBlock;
	}

	public function setDataFlowMap($dataFlowMap) {
		$this->dataFlowMap = $dataFlowMap;
	}

	public function setConstantsMap($constantsMap) {
		$this->constantsMap = $constantsMap;
	}

	public function setGlobalDefinesMap($globalDefinesMap) {
		$this->globalDefinesMap = $globalDefinesMap;
	}

	public function setReturnValueMap($returnValueMap) {
		$this->returnValueMap = $returnValueMap;
	}

	public function setRegisterGlobalMap($registerGlobalMap) {
		$this->registerGlobalMap = $registerGlobalMap;
	}


	public function setIsExitBlock($isExitBlock) {
		$this->isExitBlock = $isExitBlock;
	}
	
	
	
}

?>