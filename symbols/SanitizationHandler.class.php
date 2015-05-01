<?php

use PhpParser\Node;
use PhpParser\Node\Scalar;
require CURR_PATH . "/conf/securing.php" ;


/**
 * 对净化信息进行处理
 * @author xyw55
 *
 */
class SanitizationHandler {
	
	/**
	 * @param Node $node
	 * @param 数据流 $dataFlow
	 */
	public static function setSanitiInfo($node,$dataFlow,$dataFlows){
	    
	    $sanitiInfo = self::SantiniFuncHandler($node);
	    //print_r($sanitiInfo);
	    if($sanitiInfo){
	        //向上追踪变量，相同变量的净化信息，全部添加
	        $funcParams = NodeUtils::getNodeFuncParams($node);
	        //traceback
	        $sameVarSanitiInfo = array();
	        foreach ($funcParams as $param){
	            $ret = self::sanitiSameVarTraceback($param, $dataFlows);
	            //print_r($ret);
	            //如果一个参数没有净化，则未净化
	            if(!$ret[0]){
	                $sameVarSanitiInfo = array();
	                break;
	            }
	            $sameVarSanitiInfo = array_merge($sameVarSanitiInfo,$ret['funcs']);
	        }
	        //加入此变量的净化信息中
	        foreach ($sameVarSanitiInfo as $oneFunction)
	            $dataFlow->getLocation()->addSanitization($oneFunction) ;
	        $dataFlow->getLocation()->addSanitization($sanitiInfo) ;
	    }
	    $funcName = NodeUtils::getNodeFunctionName($node) ;
	    //清除反作用的函数
	    SanitizationHandler::clearSantiInfo($funcName, $node, $dataFlow) ;
	    print_r($dataFlow);
	}
	
	/**
	 * @param 净化变量 $var
	 * @param 数据流 $dataFlow
	 * @return 是否净化，及净化信息
	 */
	public static function sanitiSameVarTraceback($varName, $dataFlows){
	    global $SECURES_TYPE_ALL;
	    //print_r("enter traceback<br/>");
	    echo '<br/>';
	    //将块内数据流逆序，从后往前遍历
	    $flows = array_reverse($dataFlows);
	    foreach($flows as $flow){
	        //需要将遍历过的dataflow删除
	        array_pop($dataFlows);
	        //trace back
	        if($flow->getName() == $varName){
	            //处理净化信息
	            $ret = $flow->getlocation()->getSanitization();
	            if ($ret){
	                //存在净化，return
	                return array(true,'funcs' => $ret);
	            }else 
	               return array(false);
	        }
	    }
	    return array(false);
	}
	
	/**
	 * @param funcNode $node
	 * @return null | array(funcName,type)
	 */
	public static function SantiniFuncHandler($node){
	    global $F_SECURES_ALL ;
	    $funcName = NodeUtils::getNodeFunctionName($node) ;
	    //查看系统净化函数及已查找函数的信息
	    $ret = self::isSecureFunction($funcName);
	    if($ret[0]){
	        $oneFunction = new OneFunction($funcName);
	        $oneFunction->setSanitiType($ret['type']);
	        return $oneFunction;
	    }else{
	        //未查找过函数
	        $context = Context::getInstance() ;
	        global $fileSummary;
	        $require_array = $fileSummary->getIncludeMap();
	        $path = $fileSummary->getPath();
	        
	        $funcBody = $context->getClassMethodBody($funcName, $path, $require_array);
	        if(!$funcBody) return null;
	        $visitor = new SanitiFunctionVisitor();
	        $parser = new PhpParser\Parser(new PhpParser\Lexer\Emulative) ;
	        $traverser = new PhpParser\NodeTraverser ;
	        $traverser->addVisitor($visitor) ;
	        $visitor->funcName = $funcName;
	        $traverser->traverse($funcBody->stmts) ;
	        if($visitor->sanitiInfo[0]){
	            //将净化函数加入净化UserSanitizeFuncContext
	            $oneFunction = new OneFunction($funcName);
	            $oneFunction->setSanitiType($visitor->sanitiInfo['type']);
	            $SanitiFuncContext = UserSanitizeFuncConetxt::getInstance();
	            $SanitiFuncContext->addFunction($oneFunction);
	            return $oneFunction;
	        }else
	            return null;
	    }
	}
	
	
	/**
	 * 检测是否为净化函数
	 * @param 函数名 $funcName
	 * @return array(true|false,type)
	 */
	public static function isSecureFunction($funcName){
	    global $F_SECURES_ARRAY,$F_SECURES_ALL;
	    $nameNum = count($F_SECURES_ARRAY);
	    //查找系统净化函数
	    if (in_array($funcName, $F_SECURES_ALL)){
	        $type = array();
	        for($i = 0;$i < $nameNum; $i++){
	            if(in_array($funcName, $F_SECURES_ARRAY[$i])){
	                array_push($type, $F_SECURES_ARRAY[$i]['__NAME__']);
	            }
	        }
	        if($type)
	            return array(true,'type'=>$type);
	        else
	           return array(false);
	    }else{
	        //已经查找过的用户定义净化函数
	        $sanitiFuncContext = UserSanitizeFuncConetxt::getInstance();
	        $ret = $sanitiFuncContext->getFuncSanitizeInfo($funcName);
	        if($ret){
	            return array(true,'type'=>$ret['type']);
	        }else
	           return array(false);
	    }
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
					foreach ($map as $position => $oneFunction){
					    if ($oneFunction['funcName'] == 'addslashes')
					        array_splice($map,$position,1) ;
					}
					break ;
					
				case 'html_entity_decode':
					//去除htmlentities净化
					$map = $dataFlow->getLocation()->getSanitization() ;
					foreach ($map as $position => $oneFunction){
					    if ($oneFunction['funcName'] == 'htmlentities')
					        array_splice($map,$position,1) ;
					}
					break ;
				
				case 'htmlspecialchars_decode':
					//去除htmlspecialchars净化
					$map = $dataFlow->getLocation()->getSanitization() ;
					foreach ($map as $position => $oneFunction){
					    if ($oneFunction['funcName'] == 'htmlspecialchars')
					        array_splice($map,$position,1) ;
					}
					break ;
					
			}
		}
		
	}
	
}

/**
 * 寻找函数体的return语句，判断函数返回值是否净化
 * @author xyw55
 *  
 */
class SanitiFunctionVisitor extends PhpParser\NodeVisitorAbstract{
    public $sanitiInfo = null;
    public $funcName = null;
    
    public function leaveNode(Node $node){
        global $SECURES_TYPE_ALL;
        if (!$node instanceof Node){
            return null;
        }
        
        if ($node->getType() == 'Stmt_Return'){
            $part = $node->expr;
            if(SymbolUtils::isValue($part)){
                //return value
                $this->sanitiInfo = array(true,'type'=>$SECURES_TYPE_ALL);
            }elseif (SymbolUtils::isVariable($part)){
                //return variable
                $context = Context::getInstance() ;
                $funcBody = $context->getFunctionBody($this->funcName);
                if(!$funcBody) break;
                $nodes = $funcBody->stmts;
                $cfg = new CFGGenerator() ;
                $block = $cfg->CFGBuilder($nodes, NULL, NULL, NULL) ;
                $dataFlows = $block->getBlockSummary()->getDataFlowMap();
                $ret = $this->sanitiTraceback($node->expr, $dataFlows);
                //print_r($ret);
                if ($ret[0])
                    $this->sanitiInfo = array(true,'type'=>$ret['type']);
                else 
                    $this->sanitiInfo = null;
            }elseif (SymbolUtils::isConstant($part)){
                //return constant
                $this->sanitiInfo = array(true,'type'=>$SECURES_TYPE_ALL);
            }elseif (SymbolUtils::isArrayDimFetch($part)){
                //return array
            }elseif (SymbolUtils::isConcat($part)){
                //return concat
            }else{
                if (($part->getType() == 'Expr_FuncCall') || ($part->getType() == 'Expr_MethodCall') ){
                    $ret = SanitizationHandler::SantiniFuncHandler($part);
                    if($ret)
                        $this->sanitiInfo = array(true,'type'=>$ret['type']);
                }
            }

        }else 
            return null;
    }
    
    /**
     * 进行回溯
     * @param string $argName
     * @param BasicBlock $block
     * @param flowNum 遍历过的flow数量
     * @return array(),返回是否净化，净化类型是什么
     */
    public function sanitiTraceback($arg,$dataFlows){
        global $SECURES_TYPE_ALL;
        //print_r("enter traceback<br/>");
        
        $argName = NodeUtils::getNodeStringName($arg);
        
        //将块内数据流逆序，从后往前遍历
        $flows = array_reverse($dataFlows);
        foreach($flows as $flow){
            //需要将遍历过的dataflow删除
            array_pop($dataFlows);
            //trace back
            if($flow->getName() == $argName){
                //处理净化信息
                $ret = $flow->getlocation()->getSanitization();
                if ($ret){
                    $type = array();
                    foreach ($ret as $oneFunction)
                        $type = array_merge($type, $oneFunction->getSanitiType());
                    return array(true,'type'=>$type);
                }
                //得到flow->getValue()的变量node
                //$sql = $a . $b ;  =>  array($a,$b)
                if($flow->getValue() instanceof ConcatSymbol){
                    $vars = $flow->getValue()->getItems();
                }else{
                    $vars = array($flow->getValue()) ;
                }
                $retarr = array();
                foreach($vars as $var){
                    $ret = $this->sanitiTraceback($var,$dataFlows);
                    //有一个变量值未净化，就是整个未净化
                    if (!$ret[0]) return array(false);
                    array_push($retarr, $ret['type']);
                }
                //统计变量值的净化信息，所有相关值被净化，且净化类型相同，则变量净化了该类型
                $varsNum = count($vars);
                $retNum = count($retarr);
                if ($varsNum != $retNum) return array(false);
                $ret = $SECURES_TYPE_ALL;
                foreach ($retarr as $arr){
                    $ret = array_intersect($ret, $arr);
                }
                return array(true,'type' => $ret);
            }
        }
        if ($arg instanceof ValueSymbol){
            return array(true,'type'=>$SECURES_TYPE_ALL);
        }elseif ($arg instanceof Scalar){
            return array(true, 'type'=>$SECURES_TYPE_ALL);
        }
        return array(false);
    }
    
}
?>