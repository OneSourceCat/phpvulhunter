<?php
require_once './CFGNode.php';

/**
 * 定义基本块信息
 * @author Administrator
 *
 */
class BasicBlock extends CFGNode{
	//基本块中包含的AST node，放入list中
	private $containedNodes ;
	
	public function __construct(){
		$this->containedNodes = array() ;
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
	
	
}







?>






















