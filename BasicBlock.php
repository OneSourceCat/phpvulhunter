<?php
require CURR_PATH . '/CFGNode.php';
require CURR_PATH . '/summary/BlockSummary.class.php';
/**
 * 定义基本块信息
 * @author exploit
 *
 */
class BasicBlock extends CFGNode{
	//基本块中包含的AST node，放入list中
	private $containedNodes ;
	private $blockSummary;
	//array('function_name'=>'xxx',array(0,1))
    //public $function = array(); 
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
	
	
}




?>