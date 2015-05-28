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
	public static function setSanitiInfo($node, $dataFlow, $block, $fileSummary){

	    $dataFlows = $block->getBlockSummary()->getDataFlowMap();
	    $sanitiInfo = self::SantiniFuncHandler($node, $fileSummary);
	    $sanitiInfo=null;
	    if($sanitiInfo){
	        $args = NodeUtils::getFuncParamsNode($node);
	        if (count($args) > 0){
	            if (!$dataFlow->getValue()){
	                $arg = SymbolUtils::getSymbolByNode($args[0]);
	                $dataFlow->setValue($arg);
	            }
	        }
	        
	        //向上追踪变量，相同变量的净化信息，全部添加
	        $funcParams = NodeUtils::getNodeFuncParams($node);
	        //traceback
	        $sameVarSanitiInfo = array();
	        foreach ($funcParams as $param){
	            $dataFlows = $block->getBlockSummary()->getDataFlowMap();
	            $dataFlows = array_reverse($dataFlows);
                $ret = self::sanitiSameVarMultiBlockHandler($param, $block, $dataFlows, $fileSummary);
	            //如果一个参数没有净化，则未净化
	            if(!$ret[0]){
	                $sameVarSanitiInfo = array();
	                break;
	            }
	            $sameVarSanitiInfo = array_merge($sameVarSanitiInfo,$ret['funcs']);
	        }
	        
	        //加入此变量的净化信息中
	        foreach ($sameVarSanitiInfo as $oneFunction){
	            $dataFlow->getLocation()->addSanitization($oneFunction) ;
	        }
	           
	        $dataFlow->getLocation()->addSanitization($sanitiInfo) ;
	    }
	    $funcName = NodeUtils::getNodeFunctionName($node) ;

	    //清除反作用的函数
	    SanitizationHandler::clearSantiInfo($funcName, $node, $dataFlow) ;
	}

	private static function sanitiSameVarMultiFileHandler($varName, $block, $dataFlows, $fileSummary){
	    $includeFileSummaryMap = FileSummaryGenerator::getIncludeFilesDataFlows($fileSummary);
	    if (!$includeFileSummaryMap){
	        return array(false);
	    }
	    $includeFileSummaryMap = array_reverse($includeFileSummaryMap);
	    foreach ($includeFileSummaryMap as $includeFileSummary){
	        if(!($includeFileSummary instanceof FileSummary)){
	            continue;
	        }
	        $fileDataFlows = $includeFileSummary->getFlowsMap();
	        $fileDataFlows = array_reverse($fileDataFlows);
	        foreach ($fileDataFlows as $flow){
	            if ($flow->getName() == $varName){
	                if ($flow->getlocation()){
	                    $ret = $flow->getlocation()->getSanitization();
	                    if ($ret){
	                        return array(true,'funcs'=>$ret);
	                    }else {
	                        return array(false);
	                    }
	                }
	            }
	        }
	    }
	}

	/**
	 * 相同净化变量的多块回溯
	 * @param 变量名 $varName
	 * @param 当前块 $block
	 * @param 数据流 $dataFlows
	 * @return 
	 */

	public static function sanitiSameVarMultiBlockHandler($varName, $block, $dataFlows, $fileSummary){
	    //print_r("enter sanitiSameVarMultiBlock<br/>");
        $mulitBlockHandlerUtils = new multiBlockHandlerUtils($block);
        $blockList = $mulitBlockHandlerUtils->getPathArr();
        
        //当前块flows没有遍历完
        if(count($dataFlows) != 0)
            return self::sanitiSameVarTraceback($varName, $block, $dataFlows, $fileSummary);
            
	    if($blockList == null || count($blockList) == 0){
	        //加入多文件分析
	        self::sanitiSameVarMultiFileHandler($varName, $block, $dataFlows, $fileSummary);
            return  array(false);
	    }
	    
	    if(!is_array($blockList[0])){
	        //如果不是平行结构
	        if(count($dataFlows) == 0){
	            //当前块回溯完毕，回溯上一块
	            $block = $blockList[0];
	            $dataFlows = $block->getBlockSummary()->getDataFlowMap();
	            $dataFlows = array_reverse($dataFlows);
	            return self::sanitiSameVarTraceback($varName, $block, $dataFlows, $fileSummary);
	        }
	        return self::sanitiSameVarTraceback($varName, $block, $dataFlows, $fileSummary);
	    }else{
	        //平行结构
	        //向上找相关变量的净化信息，只有平行块间的变量净化信息相同，才保存
            $retarr = array();
	        foreach ($blockList[0] as $key=>$block){
	            if(count($dataFlows) == 0){
	                //当前块回溯完毕，回溯上一块
	                $dataFlows = $block->getBlockSummary()->getDataFlowMap();
	                $dataFlows = array_reverse($dataFlows);
	                $ret = self::sanitiSameVarTraceback($varName, $block, $dataFlows, $fileSummary);
	                $dataFlows = array();
	            }else
                    $ret = self::sanitiSameVarTraceback($varName, $block, $dataFlows, $fileSummary);
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
	public static function sanitiSameVarTraceback($varName, $block, $dataFlows, $fileSummary){
	    global $SECURES_TYPE_ALL;
	    //将块内数据流逆序，从后往前遍历
	    $flows = $dataFlows;
	    foreach($flows as $flow){
	        //需要将遍历过的dataflow删除
	        array_shift($dataFlows);
	        //trace back
	        if($flow->getName() == $varName){
	            //处理净化信息
	            $ret = $flow->getlocation()->getSanitization();
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
                        $ret = self::sanitiSameVarMultiBlockHandler($varName, $block, $dataFlows, $fileSummary);
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
	    return self::sanitiSameVarMultiBlockHandler($varName, $block, $dataFlows, $fileSummary);
	}
	
	
	
	
	/**
	 * 净化函数处理函数
	 * @param funcNode $node
	 * @return null | array(funcName,type)
	 */
	public static function SantiniFuncHandler($node, $fileSummary){
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
	        $require_array = $fileSummary->getIncludeMap();
	        $path = $fileSummary->getPath();
	        
	        $funcBody = $context->getClassMethodBody($funcName, $path, $require_array);
	        if(!$funcBody) return null;

	        $visitor = new SanitiFunctionVisitor();
	        $parser = new PhpParser\Parser(new PhpParser\Lexer\Emulative) ;
	        $traverser = new PhpParser\NodeTraverser ;
	        $traverser->addVisitor($visitor) ;
	        $visitor->funcName = $funcName;
	        $visitor->fileSummary = $fileSummary;
	        $traverser->traverse($funcBody->stmts) ;
	        
	        if($visitor->sanitiInfo[0]){
	            //将净化函数加入净化UserSanitizeFuncContext
	            $oneFunction = new OneFunction($funcName);
	            $oneFunction->setSanitiType($visitor->sanitiInfo['type']);
	            $SanitiFuncContext = UserSanitizeFuncConetxt::getInstance();
	            $SanitiFuncContext->addFunction($oneFunction);
	            return $oneFunction;
	        }else{
	            return null;
	        }
	            
	    }
	}
	
	
	/**
	 * 检测是否为系统净化函数或已处理的净化函数
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
	            return array(true,'type'=>$ret->getSanitiType());
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
 * 多个return值，取其净化信息交集
 * @author xyw55
 *  
 */
class SanitiFunctionVisitor extends PhpParser\NodeVisitorAbstract{
    //函数净化信息
    public $sanitiInfo = null;
    public $funcName = null;
    public $fileSummary = null;
    public function beforeTraverse(array $nodes){
        global $SECURES_TYPE_ALL;
        $this->sanitiInfo = array(false,'type'=>$SECURES_TYPE_ALL);
    }
    
    /*
     * 查找return语句，判断return值得净化信息
     */
    public function leaveNode(Node $node){
        global $SECURES_TYPE_ALL;
        if (!$node instanceof Node){
            return null;
        }
        if ($node->getType() == 'Stmt_Return'){
            $part = $node->expr;
            if(SymbolUtils::isValue($part)){
                //return value
                if(!is_array($this->sanitiInfo['type'])){
                    $this->sanitiInfo['type'] = array($this->sanitiInfo['type']) ;
                }
                $type = array_intersect($this->sanitiInfo['type'], $SECURES_TYPE_ALL);
                $this->sanitiInfo = array(true,'type'=>$type);
            }elseif (SymbolUtils::isVariable($part)){
                //return variable
                $context = Context::getInstance() ;
                $funcBody = $context->getFunctionBody($this->funcName);
                if(!$funcBody) return null;

                $nodes = $funcBody->stmts;
                $cfg = new CFGGenerator() ;
                $block = $cfg->CFGBuilder($nodes, NULL, NULL, NULL) ;
                
                $ret = $this->sanitiMultiBlockHandler($node->expr,$block);

                if ($ret[0]){
                    $type = array_intersect($this->sanitiInfo['type'], $ret['type']);
                    $this->sanitiInfo = array(true,'type'=>$type);
                }else 
                    $this->sanitiInfo = null;
            }elseif (SymbolUtils::isConstant($part)){
                //return constant
                $type = array_intersect($this->sanitiInfo['type'], $SECURES_TYPE_ALL);
                $this->sanitiInfo = array(true,'type'=>$type);
            }elseif (SymbolUtils::isArrayDimFetch($part)){
                //return array
                $context = Context::getInstance() ;
                $funcBody = $context->getFunctionBody($this->funcName);
                if(!$funcBody) return null;
                $nodes = $funcBody->stmts;
                $cfg = new CFGGenerator() ;
                $block = $cfg->CFGBuilder($nodes, NULL, NULL, NULL) ;
                
                $ret = $this->sanitiMultiBlockHandler($node->expr,$block);
                if ($ret[0]){
                    $type = array_intersect($this->sanitiInfo['type'], $ret['type']);
                    $this->sanitiInfo = array(true,'type'=>$type);
                }else 
                    $this->sanitiInfo = null;
            }elseif (SymbolUtils::isConcat($part)){
                //return concat
                $concat = new ConcatSymbol() ;
                $concat->setItemByNode($part) ;
                $items = $concat->getItems();
                if(!$items) return null;
                
                $context = Context::getInstance() ;
                $funcBody = $context->getFunctionBody($this->funcName);
                if(!$funcBody) return null;
                $nodes = $funcBody->stmts;
                $cfg = new CFGGenerator() ;
                $block = $cfg->CFGBuilder($nodes, NULL, NULL, NULL) ;
                
                $retarr = $SECURES_TYPE_ALL;
                foreach ($items as $item){
                    $ret = $this->sanitiMultiBlockHandler($item,$block);
                    if ($ret[0]){
                        $retarr = array_intersect($retarr, $ret['type']);
                    }else{
                        $this->sanitiInfo = array(false);
                        break;
                    }
                }
                $this->sanitiInfo = array(true, 'type'=>$retarr);
            }else{
                //处理函数调用
                if (($part->getType() == 'Expr_FuncCall') || 
                    ($part->getType() == 'Expr_MethodCall') || 
                    ($part->getType() == 'Expr_StaticCall') ){
                    $ret = SanitizationHandler::SantiniFuncHandler($part, $this->fileSummary);
                    
                    if($ret){
                        $saniType = $ret->getSanitiType();
                        if (is_array($saniType[0])){
                            $saniType = $saniType[0];
                        }
                        $type = array_intersect($this->sanitiInfo['type'], $saniType);
                        $this->sanitiInfo = array(true,'type'=>$type);
                    }
                        
                }
            }

        }else 
            return null;
        
    }
    
    
    
    /**
     * return变量多块回溯
     * @param 变量对象 $arg
     * @param 当前块 $block
     * @param 数据流已处理数量 $flowsNum
     * @return void|净化信息
     */
    public function sanitiMultiBlockHandler($arg, $block, $flowsNum=0){
        $mulitBlockHandlerUtils = new multiBlockHandlerUtils($block);
        $blockList = $mulitBlockHandlerUtils->getPathArr();
        
        
        $flows = $block->getBlockSummary()->getDataFlowMap();
        //当前块flows没有遍历完
        if(count($flows) != $flowsNum){
            return $this->sanitiTracebackBlock($arg, $block, $flowsNum);
        }else {
            $flowsNum = 0;
        }
        if($blockList == null || count($blockList) == 0){
            return  ;
        }
        
        if(!is_array($blockList[0])){
            //如果不是平行结构
            $flows = $block->getBlockSummary()->getDataFlowMap();
            if(!$flowsNum){
                $block = $blockList[0];
                $ret = $this->sanitiTracebackBlock($arg, $block, 0);
                return $ret;
            }
            $ret = $this->sanitiTracebackBlock($arg, $block, $flowsNum);
            return $ret;
        }else{
            
            //平行结构
            //分别遍历每一个平行基本块及其以上，对得到的净化信息，合并共有的，返回
            global $SECURES_TYPE_ALL;
            $retarr = $SECURES_TYPE_ALL;
            $isFind = false;
            foreach ($blockList[0] as $block){

                $flows = $block->getBlockSummary()->getDataFlowMap();
                if(!$flowsNum){
                    $ret = null;
                    $ret = $this->sanitiTracebackBlock($arg, $block, 0);
                    if ($ret[0]){
                        $retarr = array_intersect($ret, $retarr);
                        $isFind = true;
                    }
                }else{
                    $ret = $this->sanitiTracebackBlock($arg, $block, $flowsNum);
                    if ($ret[0]){
                        $retarr = array_intersect($ret['type'], $retarr);
                        $isFind = true;
                    }
                }
            }
            if ($isFind){
                return array(true,'type'=>$retarr);
            }else{
                return array(false);
            }
        }
        
    }
    
    
    /**
     * return变量块内回溯
     * @param 变量对象 $arg
     * @param 当前块 $block
     * @param flowNum 遍历过的flow数量
     * @return array(),返回是否净化，净化类型是什么
     */
    public function sanitiTracebackBlock($arg,$block,$flowsNum=0){
        global $SECURES_TYPE_ALL;
        $flows = $block->getBlockSummary()->getDataFlowMap();
        $argName = NodeUtils::getNodeStringName($arg);
        
        // 去掉分析过的$flow
        $temp = $flowsNum ;
        while ($temp > 0){
            array_pop($flows) ;
            $temp -- ;
        }
        //将块内数据流逆序，从后往前遍历
        $flows = array_reverse($flows);
        foreach($flows as $flow){
            $flowsNum ++ ;
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
                    $ret = $this->sanitiMultiBlockHandler($var,$block,$flowsNum);
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
        return $this->sanitiMultiBlockHandler($arg, $block, $flowsNum);
    }
    
    
}



?>