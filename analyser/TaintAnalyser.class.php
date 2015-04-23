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
	 * 污点分析中，对当前基本块的探测
	 * @param BasicBlock $block  当前基本块
	 * @param Node $node  当前调用sink的node
	 * @param string $argName  危险参数的名称
	 */
	public function currBlockTaintHandler($block,$node,$argName){
		$summary = $block->getBlockSummary() ;
		$flows = $summary->getDataFlowMap() ;
		$flows = array_reverse($flows); //逆序处理flows
		
		foreach ($flows as $flow){
			//print_r($flow) ;
			if($flow->getName() == $argName){
				//处理净化信息,如果被编码或者净化则返回safe
				if ($flow->getlocation()->getSanitization() || $flow->getLocation()->getEncoding()){
					return "safe";
				}
				
				//获取flow中的右边赋值变量
				//得到flow->getValue()的变量node
				//$sql = $a . $b ;  =>  array($a,$b)
				if($flow->getValue() instanceof ConcatSymbol){
					$vars = $flow->getValue()->getItems();
				}else{
					$vars = array($flow->getValue()) ;
				}
				
				$retarr = array();
				foreach($vars as $var){
					$varName = NodeUtils::getNodeStringName($var) ;
					$ret = $this->currBlockTaintHandler($block, $node, $varName) ;
					//变量经过净化，这不需要跟踪该变量
					if ($ret == "safe"){
						$retarr = array_slice($retarr, array_search($varName,$retarr)) ;
					}else{
						//如果var右边有source项
						$sourcesArr = Sources::getUserInput() ;
						if(in_array($varName, $sourcesArr)){
							//报告漏洞
							$this->report($node, $var) ;
						}
					}
				}

			}
		}
	}
	
	
	/**
	 * 根据sink的类型、危险参数的净化信息列表、编码列表
	 * 判断是否是有效的净化
	 * 返回true or false
	 * @param string $type
	 * @param array $saniArr
	 * @param array $encodingArr
	 */
	public function isSanitization($type,$saniArr,$encodingArr){
		
	}
	
	
	/**
	 * 污点分析的函数
	 * @param BasicBlock $block 当前基本块
	 * @param Node $node 当前的函数调用node
	 * @param $argNameArr 危险参数名的列表
	 */
	public function analysis($block,$node,$argNameArr){
		//获取所有的前驱节点集合
		$this->getPrevBlocks($block) ;
		
		//获取前驱基本块集合并将当前基本量添加至列表
		$block_list = $this->pathArr ;
		array_push($block_list, $block) ;
		
		//首先，在当前基本块中探测变量，如果有source和不完整的santi则报告漏洞
		$ret = $this->currBlockTaintHandler($block, $node, $argNameArr) ;
		
		//遍历每个前驱block
		foreach($block_list as $bitem){
			//不是平行结构
			if(!is_array($bitem)){
				
			}else{
				//是平行结构，比如if-else
				foreach($bitem as $branch){
		
				}
			}
		
		
		}
		return array() ;
	}
	
	
	/**
	 * 报告漏洞的函数
	 * @param Node $node 出现漏洞的node
	 * @param Node $var  出现漏洞的变量node
	 */
	public function report($node,$var){
		echo "<pre>" ;
		echo "有漏洞！！！！<br/>" ;
		echo "漏洞变量：<br/>" ;
		print_r($var) ;
		echo "漏洞节点：<br/>" ;
		print_r($node) ;
	}
	
	
}



?>
