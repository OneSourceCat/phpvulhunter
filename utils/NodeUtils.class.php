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
     * @return string
     */
    public static function getNodeStringName(Node $node) {
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
    
}
?>