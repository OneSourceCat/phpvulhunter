<?php
/**
 * Symbol的工具类
 * @author Administrator
 *
 */
class SymbolUtils {
	
	
	/**
	 * 判断节点是否是Value类型的symbol
	 * 如果是静态String、Integer、Float则返回true
	 * @param AST $node
	 * @return boolean
	 */
	public static function isValue($node){
		$type = $node->getType() ;
		if($type == "Scalar_String" or $type == "Scalar_LNumber" or $type == "Scalar_DNumber"){
			return true ;
		}else{
			return false ;
		}
	}
	
	/**
	 * 判断节点是否是Variable类型的symbol
	 * @param AST $node
	 * @return boolean
	 */
	public static function isVariable($node){
		$type = $node->getType() ;
		if($type == "Expr_Variable"){
			return true ;
		}else{
			return false ;
		}
	}
	
	/**
	 * 判断节点是否是常量类型的symbol
	 * @param AST $node
	 * @return boolean
	 */
	public static function isConstant($node){
		$type = $node->getType() ;
		if($type == "Expr_ConstFetch"){
			return true ;
		}else{
			return false ;
		}
	}
	
	
	/**
	 * 判断节点是否是字符串连接类型symbol
	 * 	(1)Expr_BinaryOp_Concat: 'aaaa' . "bbbbb" . $b ;
 	 * 	(2)Expr_AssignOp_Concat: $sql.= "xxxx" ;
	 * @param AST $node
	 * @return boolean
	 */
	public static function isConcat($node){
		$type = $node->getType() ;
		//Expr_BinaryOp_Concat======> "he" . "llo"
		//Expr_AssignOp_Concat======> $sql .= "xx" ;
		if($type == "Expr_BinaryOp_Concat" or $type == "Expr_AssignOp_Concat"){
			return true ;
		}else{
			return false ;
		}
	}
	
	
	/**
	 * 判断节点是否是数组定义类型symbol
	 * @param AST $node
	 * @return boolean
	 */
	public static function isArrayDimFetch($node){
		$type = $node->getType() ;
		if($type == "Expr_ArrayDimFetch" or $type == "Expr_Array"){
			return true ;
		}else{
			return false ;
		}
	}
	
	/**
	 * 根据AST node获取相应的symbol
	 * @param unknown $node
	 */
	public static function getSymbolByNode($node){
		if($node && SymbolUtils::isValue($node)){
			//在DataFlow加入Location以及name
			$vs = new ValueSymbol() ;
			$vs->setValueByNode($node) ;
			return $vs ;
		}elseif ($node && SymbolUtils::isVariable($node)){
			//加入dataFlow
			$vars = new VariableSymbol() ;
			$vars->setNameByNode($node) ;
			$vars->setValue($node);
			return $vars ;
		}elseif ($node && SymbolUtils::isArrayDimFetch($node)){
			//加入dataFlow
			$arr = new ArrayDimFetchSymbol() ;
			$arr->setValue($node) ;
			return $arr ;
		}elseif ($node && SymbolUtils::isConcat($node)){
			$concat = new ConcatSymbol() ;
			$concat->setItemByNode($node) ;
			return $concat ;
		}else{
			return null ;
		}
	}
	
}

?>















