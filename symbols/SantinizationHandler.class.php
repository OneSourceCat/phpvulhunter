<?php

require CURR_PATH . "/conf/securing.php" ;
/**
 * 该类用于对数据流分析中采取的数据净化操作进行处理
 * 如：
 * $sql = addslashes($sql) ;
 * 则$sql进行了净化
 * @author exploit
 */
class SantinizationHandler {
	/**
	 * 处理symbol的净化信息
	 * $F_SECURES_ALL安全函数的集合
	 * @param Node $node
	 * @param DataFlow $dataFlow
	 */
	public static function setSantiInfo($node,$dataFlow){
		global $F_SECURES_ALL ;
		$funcName = NodeUtils::getNodeFunctionName($node) ;
		$funcInfo = explode(":", $funcName);
		$className= "";
		if (count($funcInfo) == 2){
		    $className = $funcInfo[0];
		    $funcName = $funcInfo[1];
		}
		//查看sqli的净化信息
		if(in_array($funcName, $F_SECURES_ALL)){
			//设置净化函数
			$dataFlow->getLocation()->addSanitization($funcName) ;
		}else{
		    $userSanitizeFuncConetxt = UserSanitizeFuncConetxt::getInstance() ;
		    $funcSanitizeInfo = $userSanitizeFuncConetxt->getFuncSanitizeInfo($className, $funcName);
		    if($funcSanitizeInfo)
		        $dataFlow->getLocation()->addSanitization($className . ":" .$funcName) ;
		}
// 		print_r($dataFlow);
		//清除反作用的函数
		SantinizationHandler::clearSantiInfo($funcName, $node, $dataFlow) ;
		
	}	
	
	/**
	 * 查看净化栈中是否有可以抵消的元素
	 *	[+]'html_entity_decode',
	 *	[+]'stripslashes',
	 * @param string $funcName
	 * @param Node $node
	 * @param DataFlow $dataFlow
	 */
	public static function clearSantiInfo($funcName, $node,$dataFlow){
		global $F_INSECURING_STRING ;
		//判断$funcName相反的函数是否在净化Map中
		//比如调用stripslashes($funcName=stripslashes)
		if(in_array($funcName,$F_INSECURING_STRING)){
			switch ($funcName){
				case 'stripslashes':
					//去除净化Map中最近的addslashes净化
					$map = $dataFlow->getLocation()->getSanitization() ;
					$position = array_search('addslashes',$map) ;
					array_splice($map,$position,1) ;
					break ;
					
				case 'html_entity_decode':
					//去除htmlentities净化
					$map = $dataFlow->getLocation()->getSanitization() ;
					$position = array_search('htmlentities',$map) ;
					array_splice($map,$position,1) ;
					break ;
				
				case 'htmlspecialchars_decode':
					//去除htmlspecialchars净化
					$map = $dataFlow->getLocation()->getSanitization() ;
					$position = array_search('htmlspecialchars',$map) ;
					array_splice($map,$position,1) ;
					break ;

			}
		}
		
	}
	
}


?>