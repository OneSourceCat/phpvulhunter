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
        //print_r($node);
        $type = $node->getType();
        //print_r($type);
        //echo "<br/>";
        switch ($type) {    
            case "Expr_Variable":
            case "Scalar_String":
            case "Scalar_LNumber":
            case "Scalar_DNumber":
                $names = $node->getSubNodeNames();
                //print_r($node->getSubNodeNames());
                foreach ($names as $name)
                    return($node->$name);
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
    
    
    public static function getNodeFuncParams($node){
    	if (!$node instanceof Node){
    		return null;
    	}
    	$argsArr = array();
    	if ($node->args){
    	    foreach ($node->args as $arg){
    	        array_push($argsArr, NodeUtils::getNodeStringName($arg));
    	    }
    	}else{
    	    foreach ($node->params as $arg){
    	        array_push($argsArr, NodeUtils::getNodeStringName($arg));
    	    }
    	}
    	
    	return $argsArr;
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




















