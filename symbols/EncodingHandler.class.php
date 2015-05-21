<?php

/**
 * 该类用于对数据流分析中采取的编码操作进行处理
 * 如：
 * $sql = base64_encode($sql) ;
 * 则$sql进行了base64编码，对此信息进行记录
 * Symbol对象的encoding栈
 * @author exploit
 */
class EncodingHandler {
	/**
	 * 处理summary的编码信息
	 * @param node $node
	 * @param DataFlow $dataFlow
	 * @param block $block
	 * @param fileSummary $fileSummary
	 */
	public static function setEncodeInfo($node, $dataFlow, $block, $fileSummary){
		global $F_ENCODING_STRING ;
		$funcName = NodeUtils::getNodeFunctionName($node) ;
		//发现有编码操作的函数，将编码信息加入至map中
		if(in_array($funcName, $F_ENCODING_STRING)){
		    $dataFlow->getLocation()->addEncoding($funcName) ;
			//向上追踪变量，相同变量的净化信息，全部添加
			$funcParams = NodeUtils::getNodeFuncParams($node);
			//traceback
			$sameVarEncodeInfo = array();
			foreach ($funcParams as $param){
			    $dataFlows = $block->getBlockSummary()->getDataFlowMap();
			    $dataFlows = array_reverse($dataFlows);
			    $ret = self::encodeSameVarMultiBlockHandler($param, $block, $dataFlows);
			    //如果一个参数没有净化，则未净化
			    if(!$ret[0]){
			        $sameVarEncodeInfo = array();
			        break;
			    }
			    $sameVarEncodeInfo = array_merge($sameVarEncodeInfo,$ret['funcs']);
			}
			//加入此变量的净化信息中
			foreach ($sameVarEncodeInfo as $funcName)
			    $dataFlow->getLocation()->addEncoding($funcName) ;

		}
		
		//清除解码
		EncodingHandler::clearEncodeInfo($funcName, $node, $dataFlow) ;
		//print_r($dataFlow);
	}
	
	/**
	 * 相同净化变量的多块回溯
	 * @param 变量名 $varName
	 * @param 当前块 $block
	 * @param 数据流 $dataFlows
	 * @return
	 */
	public static function encodeSameVarMultiBlockHandler($varName, $block, $dataFlows){
	    //print_r("enter sanitiSameVarMultiBlock<br/>");
	    $mulitBlockHandlerUtils = new multiBlockHandlerUtils($block);
	    $blockList = $mulitBlockHandlerUtils->getPathArr();
	
	    //当前块flows没有遍历完
	    if(count($dataFlows) != 0)
	        return self::encodeSameVarTraceback($varName, $block, $dataFlows);
	
	    if($blockList == null || count($blockList) == 0){
	        return  array(false);
	    }
	     
	    if(!is_array($blockList[0])){
	        //如果不是平行结构
	        if(count($dataFlows) == 0){
	            //当前块回溯完毕，回溯上一块
	            $block = $blockList[0];
	            $dataFlows = $block->getBlockSummary()->getDataFlowMap();
	            $dataFlows = array_reverse($dataFlows);
	            return self::encodeSameVarTraceback($varName, $block, $dataFlows);
	        }
	        return self::encodeSameVarTraceback($varName, $block, $dataFlows);
	    }else{
	        //平行结构
	        //向上找相关变量的净化信息，只有平行块间的变量净化信息相同，才保存
	        $retarr = array();
	        foreach ($blockList[0] as $key=>$block){
	            if(count($dataFlows) == 0){
	                //当前块回溯完毕，回溯上一块
	                $dataFlows = $block->getBlockSummary()->getDataFlowMap();
	                $dataFlows = array_reverse($dataFlows);
	                $ret = self::encodeSameVarTraceback($varName, $block, $dataFlows);
	                $dataFlows = array();
	            }else
	                $ret = self::encodeSameVarTraceback($varName, $block, $dataFlows);
	            //得到各个平行结构中的相同函数
	            if ($key == 0){
	                if ($ret[0])
	                    $retarr = $ret['funcs'];
	                else
	                    return array(false);
	            }
	            if ($ret[0]){
	                $temp = array();
	                foreach ($retarr as $function){
	                    foreach ($ret['funcs'] as $otherfunction){
	                        if ($function == $otherfunction){
	                            array_push($temp, $function);
	                        }
	                    }
	                }
	                $retarr = $temp;
	            }
	            else
	                return array(false);
	        }
	        return array(true, 'funcs'=>$retarr);
	         
	    }
	     
	}
	
	/**
	 * 相同变量块内回溯
	 * @param 净化变量 $var
	 * @param 数据流 $dataFlow
	 * @return 是否净化，及净化信息
	 */
	public static function encodeSameVarTraceback($varName, $block, $dataFlows){
	    global $F_ENCODING_STRING;
	    //将块内数据流逆序，从后往前遍历
	    $flows = $dataFlows;
	    foreach($flows as $flow){
	        //需要将遍历过的dataflow删除
	        array_shift($dataFlows);
	        //trace back
	        if($flow->getName() == $varName){
	            //处理净化信息
	            $ret = $flow->getlocation()->getEncoding();
	            if ($ret){
	                //存在净化，return
	                return array(true,'funcs' => $ret);
	            }else{
	                //没净化，继续回溯
	                //$sql = $a . $b ;  =>  array($a,$b)
	                $vars = array();
	                if($flow->getValue() instanceof ConcatSymbol){
	                    $vars = $flow->getValue()->getItems();
	                }else{
	                    $vars = array($flow->getValue()) ;
	                }
	                $retarr = array();
	                foreach($vars as $var){
	                    $varName = NodeUtils::getNodeStringName($var);
	                    $ret = self::encodeSameVarMultiBlockHandler($varName,$block,$dataFlows);
	                    if ($ret[0]){
	                        $retarr = array_merge($retarr,$ret['funcs']);
	                    }else{
	                        return array(false);
	                    }
	                }
	                if($retarr)
	                    return array(true,'funcs'=>$retarr);
	                return array(false);
	            }
	             
	        }
	    }
	    //当前块内不存在,回溯上一块
	    return self::encodeSameVarMultiBlockHandler($varName,$block,$dataFlows);
	}
	
	
	
	
	
	/**
	 * 清除相应的编码效果
	 * 	[+]'rawurldecode', - rawurlencode
	 *	[+]'urldecode', - urlencode
	 *	[+]'base64_decode', - base64_encode
	 * @param string $funcName
	 * @param Node $node
	 * @param DataFlow $dataFlow
	 */
	public static function clearEncodeInfo($funcName, $node,$dataFlow){
		global $F_DECODING_STRING ;
		if(in_array($funcName,$F_DECODING_STRING)){
			switch ($funcName){
				case 'rawurldecode' or 'urldecode':
					//去除净化Map中最近的addslashes净化
					$map = $dataFlow->getLocation()->getEncoding() ;
					$position = array_search('urlencode',$map) ;
					array_splice($map,$position,1) ;
					break ;
					
				case 'base64_decode':
					//去除Map中最近的base64编码操作
					$map = $dataFlow->getLocation()->getEncoding() ;
					$position = array_search('base64_encode',$map) ;
					array_splice($map,$position,1) ;
					break ; 
				case 'html_entity_decode':
				    //去除Map中最近的base64编码操作
				    $map = $dataFlow->getLocation()->getEncoding() ;
				    $position = array_search('html_entity_decode',$map) ;
				    array_splice($map,$position,1) ;
				    break ;
			}
		}
	}
	
}

?>