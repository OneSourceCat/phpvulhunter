<?php
use PhpParser\Node;
/**
 * NodeUtils类主要对node节点的辅助
 * @author xyw55
 *
 */
class NodeUtils{
    /**
     * 给定一个节点，返回该节点对应string name
     * @param Node $node
     * @return base node name string
     */
    public static function getNodeStringName($node) {
        if (!$node instanceof Node){
            return null;
        }
        $type = $node->getType();
        switch ($type) {    
            case "Expr_Variable":
            case "Scalar_String":
            case "Scalar_LNumber":
            case "Scalar_DNumber":
                if($node->name){
                    return $node->name ;
                }
                $names = $node->getSubNodeNames();
                foreach ($names as $name){
                    return($node->$name);
                }
                break;
            //负数
            case "Expr_UnaryMinus":
                $names = $node->getSubNodeNames();
                //print_r($node->getSubNodeNames());
                foreach ($names as $name)
                    return ("-".NodeUtils::getNodeStringName($node->$name));
                break;
            //arg name
            case "Arg":
                return NodeUtils::getNodeStringName($node->value);
                break;
            //param name
            case "Param":
                return $node->name;
                break;
            case "Name":
                $names = $node->getSubNodeNames();
                //print_r($node->getSubNodeNames());
                foreach ($names as $name)
                    foreach ($node->$name as $parts)
                        return($parts);
                break;
                       
            //$a[],$[a]$a[]][]    
            case "Expr_ArrayDimFetch":
                //处理GLOBALS
                if($node->var->name == "GLOBALS"){
                    return $node->dim->value; 
                }
            	//不处理_GET _POST等
            	$userInput = Sources::getUserInput() ;
            	if(in_array($node->var->name, $userInput)){
            		return $node->var->name ;
            	}
            	
                $names = $node->getSubNodeNames();
                $temp = "";
                foreach ($names as $name)
                {
                    if ($name == "dim"){
                        if ($node->$name)
                            $temp .= "[".NodeUtils::getNodeStringName($node->$name)."]";
                        else 
                            $temp .= "[]";
                    }
                    else
                        $temp .= NodeUtils::getNodeStringName($node->$name);
                }
                return $temp;
                break;
            //数组dim
            case "Expr_ConstFetch":
                $names = $node->getSubNodeNames();
                //print_r($names);
                foreach ($names as $name)
                    return NodeUtils::getNodeStringName($node->$name);
                break;
            //$this->property对象属性
            case "Expr_PropertyFetch":
                $names = $node->getSubNodeNames();
                $ret = '';
                foreach ($names as $name)
                     $ret .= NodeUtils::getNodeStringName($node->$name) . ":";
                $ret .= $node->name;
                return $ret;
                break;    
            default:
                ;
            break;
        }
        return "";
    }
    
    /**
     * $GLOBALS["first"]["second"]["third"] =>first[second][third]
     * @param Node $node
     * @return GLOBALS注册的变量名
     */
    public static function getNodeGLOBALSNodeName($node){
        if (!$node instanceof Node){
            return null;
        }
        if($node->var->var){
            $ret = NodeUtils::getNodeStringName($node->dim);
            return NodeUtils::getNodeGLOBALSNodeName($node->var)."[".$ret."]";
        }
        return NodeUtils::getNodeStringName($node->dim);
       
    }
    /**
     * 给定一个节点，返回该节点对应function name,如果是类方法调用，返回类名:方法名
     * @param Node $node
     * @return function name
     */
    public static function getNodeFunctionName($node){
        if (!$node instanceof Node){
            return null;
        }
        $type = $node->getType();
        //print_r($type);
        switch ($type) {
            //function a(){},
            case "Stmt_Function":
                return $node->name;
            break;
            //a()
            case "Expr_FuncCall":
                return NodeUtils::getNodeStringName($node->name);
            break;
            //function define in class
            case "Stmt_ClassMethod":
                return $node->name;
                break;
            //class->function()
            case "Expr_MethodCall":           
                $objectName = NodeUtils::getNodeStringName($node->var);
                $methodName = $node->name;
                if(is_object($objectName) || is_object($methodName)){
                    return "";
                }
                return "$objectName:$methodName";
                break;
            //class::static function()
            case "Expr_StaticCall":
                $objectName = NodeUtils::getNodeStringName($node->class);
                $methodName = $node->name;
                return "$objectName:$methodName";
                break;
            //匿名函数
            case "Expr_Closure":
                return "";
                break;
            //echo和print比较特殊，单独处理
            case "Stmt_Echo":
            	return "echo" ;
            	break;
            case "Expr_Print":
            	return "print";
            	break;
        	case 'Expr_Include':
                return "include";
                break;
            case 'Expr_Eval':
                return "eval" ;
                break ;
            case 'Expr_Isset':
                return "isset";
                break ;
            default:
                return "";
                break;
        }
    }
    /**
     * 给定一个节点，返回该节点对应class name,
     * @param Node $node
     * @return class name
     */
    public static function getNodeClassName($node){
        if (!$node instanceof Node){
            return null;
        }
        $type = $node->getType();
        switch ($type) {
            //class define
            case "Stmt_Class":
                return $node->name;
                break;
            //new class
            case "Expr_New":
                return NodeUtils::getNodeStringName($node->class);
                break; 
            //
            default:
                return "";
                break;
        }
    }
    
    /**
     * 获取concat中的所有变量名
     * @param Node $node
     */
    private static function getConcatParams($node){
    	$retArr = array() ;
    	if($node->getType() != "Expr_BinaryOp_Concat"){
    		return $retArr ;
    	}
    	$symbol = new ConcatSymbol() ;
    	$symbol->setItemByNode($node) ;
    	$items = $symbol->getItems() ;

    	foreach ($items as $item){
    		if($item instanceof ValueSymbol){
    			continue ;
    		}
    		array_push($retArr, NodeUtils::getNodeStringName($item->getValue())) ;
    	}
    	return $retArr ;
    }
    
    
    /**
     * 获取concat中的所有变量名
     * @param Node $node
     */
    private static function getConcatParamsNode($node){
        $retArr = array() ;
        if($node->getType() != "Expr_BinaryOp_Concat"){
            return $retArr ;
        }
        $symbol = new ConcatSymbol() ;
        $symbol->setItemByNode($node) ;
        $items = $symbol->getItems() ;
    
        foreach ($items as $item){
            if($item instanceof ValueSymbol){
                continue ;
            }
            array_push($retArr, $item->getValue()) ;
        }
        return $retArr ;
    }
    
    /**
     * 获取函数的参数名称
     * @param Node $node 函数调用的node
     * @return array(arg1[,arg2,arg3,...])
     */
    public static function getFuncParamsNode($node){
        if (!$node instanceof Node){
            return null;
        }
        //支持echo和print
        $funcName = self::getNodeFunctionName($node) ;
         
        //处理其他的函数
        $argsArr = array();
        if ($node->args){
            foreach ($node->args as $arg){
                //如果为concat类型
                if($arg->value->getType() == "Expr_BinaryOp_Concat"){
                    $concatArr = self::getConcatParamsNode($arg->value) ;
                    $argsArr = array_merge($argsArr, $concatArr);
                }else{
                    array_push($argsArr, $arg->value);
                }
            }
        }elseif($node->params){
            foreach ($node->params as $arg){
                if($arg->getType() == "Expr_BinaryOp_Concat"){
                    $concatArr = self::getConcatParamsNode($arg->value) ;
                    $argsArr = array_merge($argsArr, $concatArr);
                }else{
                    array_push($argsArr, $arg->value);
                }
            }
        }
        return $argsArr;
    }
    
    
    
    
    /**
     * 获取函数的参数名称
     * @param Node $node 函数调用的node
     * @return array(arg1[,arg2,arg3,...])
     */
    public static function getNodeFuncParams($node){
    	if (!$node instanceof Node){
    		return null;
    	}
		
    	//支持echo和print
    	$funcName = self::getNodeFunctionName($node) ;
    	if($funcName == "echo"){
    		$ret = array() ;
    		if($node->exprs[0]->getType() == "Expr_BinaryOp_Concat"){
    			$ret = self::getConcatParams($node->exprs[0]) ;
    		}else{
    			if(SymbolUtils::isValue($node->exprs[0])){
    				return array() ;
    			}else{
    				$res = self::getNodeStringName($node->exprs[0]) ;
    				if(is_array($res)){
    					array_merge($ret,$res) ;
    				}else{
    					array_push($ret, $res) ;
    				}
    			}
    		}

    		return $ret ;
    	}else if($funcName == 'include'){
    	   $ret = array() ;
    		if($node->expr->getType() == "Expr_BinaryOp_Concat"){
    			$ret = self::getConcatParams($node->expr) ;
    		}elseif ($node->expr->getType() == "Scalar_Encapsed"){
    		    $args = $node->expr->parts;
    		    foreach ($args as $arg){
     		        if (SymbolUtils::isValue($arg)){
    		            continue;
    		        }else{
                        array_push($ret, NodeUtils::getNodeStringName($arg));
    		        }
    		    }
    		}else{
    			if(SymbolUtils::isValue($node->expr)){
    				return array() ;
    			}else{
    				$ret = self::getNodeStringName($node->expr) ;
    			}
    		}
    		return $ret ;
        }else if($funcName == "print"){
    		$ret = array() ;
    		if($node->expr->getType() == "Expr_BinaryOp_Concat"){
    			$ret = self::getConcatParams($node->expr) ;
    		}else{
    			if(SymbolUtils::isValue($node->expr)){
    				return array() ;
    			}else{
    				$ret = self::getNodeStringName($node->expr) ;
    			}
    		}
    		return $ret ;
    	}else if($funcName == "eval"){
    	    $ret = array() ;
    	    if($node->expr->getType() == "Expr_BinaryOp_Concat"){
    	        $ret = self::getConcatParams($node->expr) ;
    	    }else{
    	        if(SymbolUtils::isValue($node->expr)){
    	        	return array() ;
    	        }else{
    	        	$ret = self::getNodeStringName($node->expr) ;
    	        }
    	    }
    	    return $ret ;
    	}
    	
    	//处理其他的函数
    	$argsArr = array();
    	if ($node->args){
    	    foreach ($node->args as $arg){
    	    	//如果为concat类型
    	    	if($arg->value->getType() == "Expr_BinaryOp_Concat"){
    	    		$concatArr = self::getConcatParams($arg->value) ;
    	    		array_push($argsArr, $concatArr);
    	    	}else{
    	    		array_push($argsArr, NodeUtils::getNodeStringName($arg));
    	    	}  
    	    }
    	}elseif($node->params){
    	    foreach ($node->params as $arg){
    	    	if($arg->getType() == "Expr_BinaryOp_Concat"){
    	    		$concatArr = self::getConcatParams($arg->value) ;
    	    		array_push($argsArr, $concatArr);
    	    	}else{
    	    		array_push($argsArr, NodeUtils::getNodeStringName($arg));
    	    	}
    	    }
    	}
    	return $argsArr;
    }
    
    /**
     * 根据参数的位置，返回参数的名称
     * @param Node $node
     * @param array $argsPos
     * @return array
     */
    public static function getFuncParamsByPos($node,$argsPos){
    	if ((!$node instanceof Node) || !$argsPos){
    		return null;
    	}
    	$argsNameArr = self::getNodeFuncParams($node) ;	
        
    	if($node->getType() == "Expr_Include" || $node->getType() == "Expr_Eval"){
    	    return array($argsNameArr) ;
    	}
    	
    	$retArr = array() ;
    	$argNum = count($argsNameArr);
    	if($argNum > 0){
	        //类方法判别时，argsPos[0]为array
	        if(is_array($argsPos[0])){
	            foreach ($argsPos[0] as $value){
	                if ($value > $argNum)
	                    continue ;
    				//sink是从索引1开始的
    				//如果参数位置为0，如echo，则不做处理
    				if($value != 0){
    				    $value -= 1 ;
    				}
    				array_push($retArr,$argsNameArr[$value]) ;
	            }
	        }else{
	            //普通方法判断时，argsPos为array
	            foreach ($argsPos as $value){
	                if ($value > $argNum)
	                    continue ;
    				//sink是从索引1开始的
    				//如果参数位置为0，如echo，则不做处理
    				if($value != 0){
    				    $value -= 1 ;
    				}
    				if ($value == 0){
    				    if (!isset($argsNameArr[$value]) || $argsNameArr[$value] == ''){
    				        continue;
    				    }
    				}
    				array_push($retArr,$argsNameArr[$value]) ;
	            }
	        }
    		
    	}
    	return $retArr ;
    }
    
    
    
	/**
	 * 从传入节点中提取出包含的PHP文件名称
	 * @param Node $node
	 * @return string
	 */
    public static function getNodeIncludeInfo($node){
        if (!$node instanceof Node){
            return null;
        }
		if($node->getType() == "Expr_Include"){
			$parser = new PhpParser\Parser(new PhpParser\Lexer\Emulative) ;
			$traverser = new PhpParser\NodeTraverser;
			$visitor = new IncludeVisitor() ;
			$traverser->addVisitor($visitor) ;
			$traverser->traverse(array($node)) ;
			foreach ($visitor->strings as $v){
				if(preg_match("/.+?\.php/i", $v)){
					return $v ;
				}
			}
		}else{
			return null;
		}
    	
    }
    
    
	/**
	 * 根据函数名称，检测是否为sink函数
	 * @param string $funcName
	 * @return array(is_sink,args_position)
	 */
    public static function isSinkFunction($funcName, $scanType){
    	global $F_SINK_ALL, $F_SINK_ARRAY ;
    	$nameNum = count($F_SINK_ARRAY);
    	$userDefinedSink = UserDefinedSinkContext::getInstance() ;
    	$U_SINK_ALL = $userDefinedSink->getAllSinks() ;

    	//根据scanType,查找sink函数
    	switch ($scanType){
    	    case 'ALL':
            	//如果是系统的sink
            	if(key_exists($funcName, $F_SINK_ALL)){
            		for($i = 0;$i < $nameNum; $i++){
        		    	if(key_exists($funcName, $F_SINK_ARRAY[$i])){
        		    		return array(true,$F_SINK_ARRAY[$i][$funcName][0]);
        		    	}
        	    	}
            		return array(false);
            	}
            	
            	//如果是用户的sink
            	if(key_exists($funcName, $U_SINK_ALL)){
            		foreach ($userDefinedSink->getAllSinkArray() as $value){
            			if(key_exists($funcName, $value)){
            				return array(true,$U_SINK_ALL[$funcName]) ;
            			}
            		}
            		return array(false) ;
            	}
            	break;
            case 'SERVER':
        	    global $F__SINK_SERVER;
            	//如果是系统的sink
    	       if (key_exists($funcName, $F__SINK_SERVER)){
                    return array(true, $F__SINK_SERVER[$funcName][0]);
                }
        	
            	//如果是用户的sink
            	if(key_exists($funcName, $U_SINK_ALL)){
            		foreach ($userDefinedSink->getServerSinkArray() as $value){
            			if(key_exists($funcName, $value)){
            				return array(true,$U_SINK_ALL[$funcName]) ;
            			}
            		}
            		return array(false) ;
            	}
        	    break;
            case 'CLIENT':
                global $F_SINK_CLIENT;
                //如果是系统的sink
                if (key_exists($funcName, $F_SINK_CLIENT)){
                    return array(true, $F_SINK_CLIENT[$funcName][0]);
                }
                //如果是用户的sink
            	if(key_exists($funcName, $U_SINK_ALL)){
            		foreach ($userDefinedSink->getClientSinkArray() as $value){
            			if(key_exists($funcName, $value)){
            				return array(true,$U_SINK_ALL[$funcName]) ;
            			}
            		}
            		return array(false) ;
            	}
                break;
	        case 'XSS':
	            global $F_XSS;
	            //如果是系统的sink
	            if (key_exists($funcName, $F_XSS)){
	                return array(true, $F_XSS[$funcName][0]);
	            }
	            //如果是用户的sink
	            if (key_exists($funcName, $userDefinedSink->getF_XSS())){
	                return array(true,$U_SINK_ALL[$funcName]) ;
	            }
	            return array(false) ;
	            break;
            case 'SQLI':
                global $F_DATABASE;
                //如果是系统的sink
                if (key_exists($funcName, $F_DATABASE)){
                    return array(true, $F_DATABASE[$funcName][0]);
                }
                //如果是用户的sink
                if (key_exists($funcName, $userDefinedSink->getF_DATABASE())){
                    return array(true,$U_SINK_ALL[$funcName]) ;
                }
                return array(false) ;
                break;
            case 'HTTPHEADER':
                global $F_HTTP_HEADER;
                //如果是系统的sink
                if (key_exists($funcName, $F_HTTP_HEADER)){
                    return array(true, $F_HTTP_HEADER[$funcName][0]);
                }
                //如果是用户的sink
                if (key_exists($funcName, $userDefinedSink->getF_HTTP_HEADER())){
                    return array(true,$U_SINK_ALL[$funcName]) ;
                }
                return array(false) ;
                break;
            case 'CODE':
                global $F_CODE;
                //如果是系统的sink
                if (key_exists($funcName, $F_CODE)){
                    return array(true, $F_CODE[$funcName][0]);
                }
                //如果是用户的sink
                if (key_exists($funcName, $userDefinedSink->getF_CODE())){
                    return array(true,$U_SINK_ALL[$funcName]) ;
                }
                return array(false) ;
                break;
            case 'EXEC':
                global $F_EXEC;
                //如果是系统的sink
                if (key_exists($funcName, $F_EXEC)){
                    return array(true, $F_EXEC[$funcName][0]);
                }
                //如果是用户的sink
                if (key_exists($funcName, $userDefinedSink->getF_EXEC())){
                    return array(true,$U_SINK_ALL[$funcName]) ;
                }
                return array(false) ;
                break;
            case 'LDAP':
                global $F_LDAP;
                //如果是系统的sink
                if (key_exists($funcName, $F_LDAP)){
                    return array(true, $F_LDAP[$funcName][0]);
                }
                //如果是用户的sink
                if (key_exists($funcName, $userDefinedSink->getF_LDAP())){
                    return array(true,$U_SINK_ALL[$funcName]) ;
                }
                return array(false) ;
                break;
            case 'FILE_INCLUDE':
                global $F_FILE_INCLUDE;
                //如果是系统的sink
                if (key_exists($funcName, $F_FILE_INCLUDE)){
                    return array(true, $F_FILE_INCLUDE[$funcName][0]);
                }
                //如果是用户的sink
                if (key_exists($funcName, $userDefinedSink->getF_FILE_INCLUDE())){
                    return array(true,$U_SINK_ALL[$funcName]) ;
                }
                return array(false) ;
                break;
            case 'FILE_READ':
                global $F_FILE_READ;
                //如果是系统的sink
                if (key_exists($funcName, $F_FILE_READ)){
                    return array(true, $F_FILE_READ[$funcName][0]);
                }
                //如果是用户的sink
                if (key_exists($funcName, $userDefinedSink->getF_FILE_READ())){
                    return array(true,$U_SINK_ALL[$funcName]) ;
                }
                return array(false) ;
                break;
            case 'XPATH':
                global $F_XPATH;
                //如果是系统的sink
                if (key_exists($funcName, $F_XPATH)){
                    return array(true, $F_XPATH[$funcName][0]);
                }
                //如果是用户的sink
                if (key_exists($funcName, $userDefinedSink->getF_XPATH())){
                    return array(true,$U_SINK_ALL[$funcName]) ;
                }
                return array(false) ;
                break;
            case 'FILE_AFFECT':
                global $F_FILE_AFFECT;
                //如果是系统的sink
                if (key_exists($funcName, $F_FILE_AFFECT)){
                    return array(true, $F_FILE_AFFECT[$funcName][0]);
                }
                //如果是用户的sink
                if (key_exists($funcName, $userDefinedSink->getF_FILE_AFFECT())){
                    return array(true,$U_SINK_ALL[$funcName]) ;
                }
                return array(false) ;
                break;
            
    	}
    	return array(false);
    	
    }
    
    /**
     * 判断方法是否是编码或者安全函数
     * @param unknown $funcName
     */
    public static function isEncodeOrSecureFunction($funcName){
        global $F_SECURES_ALL, $F_ENCODING_STRING, $F_DECODING_STRING;
        $list = array_merge($F_SECURES_ALL, $F_ENCODING_STRING, $F_DECODING_STRING) ;
        foreach($list as $value){
            if($funcName){
                return true ;
            }
        }
        return false ;
    }
    
    
	/**
	 * 根据sink方法的名称获取危险参数的位置
	 * 比如提交mysql_query的调用node，返回危险参数位置array(0)
	 * 如果找不到，默认返回array()
	 * @param Node $node
	 */
    public static function getVulArgs($node){
    	global $F_SINK_ALL,$F_SINK_ARRAY ;
    	$funcName = NodeUtils::getNodeFunctionName($node) ;
    	$nameNum = count($F_SINK_ARRAY);
    	
    	//从上下文中获取用户定义sink
    	$userDefinedSink = UserDefinedSinkContext::getInstance() ;
    	$U_SINK_ALL = $userDefinedSink->getAllSinks() ;
    	
    	//如果是系统的sink
    	if(key_exists($funcName, $F_SINK_ALL)){
    		for($i = 0;$i < $nameNum; $i++){
    			if(key_exists($funcName, $F_SINK_ARRAY[$i])){
    				return $F_SINK_ARRAY[$i][$funcName][0];
    			}
    		}
    		return array();
    	}
    	 
    	//如果是用户的sink
    	if(key_exists($funcName, $U_SINK_ALL)){
    		foreach ($userDefinedSink->getAllSinkArray() as $value){
    			if(key_exists($funcName, $value)){
    				return $U_SINK_ALL[$funcName][0] ;
    			}
    		}
    	
    		return array() ;
    	}
    }
    
    
}



/**
 * 用来遍历包含节点的辅助类
 * @author Exploit
 */
class IncludeVisitor extends  PhpParser\NodeVisitorAbstract{
	public $strings = array() ;
	public function leaveNode(Node $node){
		array_push($this->strings, NodeUtils::getNodeStringName($node)) ;
	}
}



?>




















