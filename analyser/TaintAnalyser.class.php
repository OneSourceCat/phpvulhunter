<?php


/**
 * 用于污点分析的类
 * 污点分析的任务：
 * 	（1）从各个基本块摘要中找出危险参数的变化
 * 	（2）评估危险参数是否受到有效净化
 * 	（3）根据评估结果报告漏洞
 * @author Exploit
 *
 */
class TaintAnalyser {
	//方法getPrevBlocks返回的参数
	private $pathArr = array() ;
	
	public function getPathArr() {
		return $this->pathArr;
	}

	/**
	 * 获取当前基本块的所有前驱基本块
	 * @param BasicBlock $block
	 * @return Array 返回前驱基本块集合
	 */
	public function getPrevBlocks($currBlock){
		if($currBlock != null){
			$blocks = array() ;
			$edges = $currBlock->getInEdges();
			
			//如果到达了第一个基本块则返回
			if(!$edges) return $this->pathArr;
			
			foreach ($edges as $edge){
				array_push($blocks, $edge->getSource()) ;
			}
			
			if(count($blocks) == 1){
				//前驱的节点只有一个
				if(!in_array($blocks[0],$this->pathArr)){
					array_push($this->pathArr,$blocks[0]) ;
				} 
			}else{
				//前驱节点有多个
				if(!in_array($blocks,$this->pathArr)){
					array_push($this->pathArr,$blocks) ;
				} 
			}
		
			//递归
			foreach($blocks as $bitem){
				if(!is_array($bitem)){
					$this->getPrevBlocks($bitem);
				}else{
					$this->getPrevBlocks($bitem[0]) ;
				}
				
			}
		
		}
	}
	
	/**
	 * 污点分析的函数
	 * @param BasicBlock $block
	 */
	public function analysis($block,$node,$argName){
		//获取所有的前驱节点集合
		$block_list = $this->getPrevBlocks($block) ;
		
		//遍历每个前驱block
		foreach($block_list as $bitem){
			//不是平行结构
			if(!is_array($bitem)){
				$summary = $bitem->getBlockSummary();
				$flows = $summary->getDataFlowMap();
				
			}else{
				//是平行结构，比如if-else
				foreach($bitem as $branch){
		
				}
			}
		
		
		}
		return array() ;
	}
	
}



?>
