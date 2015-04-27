<?php

require_once CURR_PATH . '/CFGEdge.php';
require CURR_PATH . '/summary/BlockSummary.class.php';
/**
 * 定义基本块信息
 * @author exploit
 *
 */
class BasicBlock{
	//基本块中包含的AST node，放入list中
	private $containedNodes ;
	private $blockSummary;
	
	public $is_entry = false ;
	public $is_exit = false ;
	public  $loop_var = NULL;
	//CFG节点的进入边
	private $inEdges = array() ;
	//CFG节点的出边
	private $outEdges = array() ;
	
	
	public function __construct(){
		$this->containedNodes = array() ;
		$this->blockSummary =  new BlockSummary() ;
	}
	
	
	/**
	 * 给定一个node，将其加入到containedNodes中
	 * @param unknown $node
	 */
	public function addNode($node){
		if($node){
			array_push($this->containedNodes, $node) ;
		}else{
			return ;
		}
	}
	
	/**
	 * 获取基本块中所有的AST节点
	 */
	public function getContainedNodes(){
		return $this->containedNodes ;
	}
	
	public function getBlockSummary() {
		return $this->blockSummary;
	}
	
	public function setBlockSummary($blockSummary) {
		$this->blockSummary = $blockSummary;
	}
	
	/**
	 * 为CFG中的节点添加入入边
	 * @param unknown $inEdge
	 */
	public function addInEdge($inEdge){
		if($inEdge){
			array_push($this->inEdges, $inEdge) ;
		}else{
			return ;
		}
	}
	
	/**
	 * 为CFG中的节点加入出边
	 * @param unknown $outEdge
	 */
	public  function addOutEdge($outEdge){
		if($outEdge){
			array_push($this->outEdges, $outEdge) ;
		}else{
			return ;
		}
	}
	
	//--------------------------Getter && Setter---------------------------------------------
	public function getInEdges() {
		return $this->inEdges;
	}

	public function getOutEdges() {
		return $this->outEdges;
	}

	public function setInEdges($inEdges) {
		$this->inEdges = $inEdges;
	}

	public function setOutEdges($outEdges) {
		$this->outEdges = $outEdges;
	}
}




?>