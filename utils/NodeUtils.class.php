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
                $a = "";
                foreach ($names as $name)
                {
                    if ($name == "dim"){
                        if ($node->$name)
                            $a =$a. "[".NodeUtils::getNodeStringName($node->$name)."]";
                        else 
                            $a =$a. "[]";
                    }
                    else
                        $a =$a. NodeUtils::getNodeStringName($node->$name);
                }
                return $a;
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
     * 给定一个节点，返回该节点对应function name,如果是类方法调用，返回类名:方法名
     * @param Node $node
     * @return function name
     */
    public static function getNodeFunctionName($node){
        if (!$node instanceof Node){
            return null;
        }
        $type = $node->getType();
        print_r($type);
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
    
}
?>