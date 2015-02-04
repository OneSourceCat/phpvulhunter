<?php 

require_once './CFGEdge.php';

class CFGNode{
	public $is_entry = false ;
	public $is_exit = false ;
	public  $loop_var = NULL;
	//CFG节点的进入边
	private $inEdges = array() ;
	//CFG节点的出边
	private $outEdges = array() ;

	
	/**
	 * 构造函数
	 */
	public function __construct(){
	
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
	
	/**
	 * @return the $inEdges
	 */
	public function getInEdges() {
		return $this->inEdges;
	}

	/**
	 * @return the $outEdges
	 */
	public function getOutEdges() {
		return $this->outEdges;
	}



	/**
	 * @param multitype: $inEdges
	 */
	public function setInEdges($inEdges) {
		$this->inEdges = $inEdges;
	}

	/**
	 * @param field_type $outEdges
	 */
	public function setOutEdges($outEdges) {
		$this->outEdges = $outEdges;
	}

	
}


?>





