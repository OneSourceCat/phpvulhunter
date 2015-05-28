<?php
/**
 * 多块处理的工具类
 * 获取前驱基本块
 * @author xyw55
 *
 */
class multiBlockHandlerUtils{
    private $pathArr = array() ;
    public function getPathArr() {
        return $this->pathArr;
    }

    public function __construct($block){
        $this->getPrevBlocks($block);
    }

    /**
	 * 获取当前基本块的所有前驱基本块
	 * @param BasicBlock $block
	 * @return Array 返回前驱基本块集合$this->pathArr
	 * 使用该方法时，需要对类属性$this->pathArr进行初始化
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
				if(!in_array($blocks[0],$this->pathArr,true)){
					array_push($this->pathArr,$blocks[0]) ;
				}
			}else{
				//前驱节点有多个
				if(!in_array($blocks,$this->pathArr,true)){
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

}
?>