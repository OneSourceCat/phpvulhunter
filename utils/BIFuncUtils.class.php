<?php

/**
 * 用来解决一些内置PHP函数的模拟
 * @author Exploit
 *
 */
class BIFuncUtils {
	
	/**
	 * 返回无影响函数
	 * @return multitype:number
	 */
	public static function getSingleFuncs(){
	    global $F_SECURES_ALL ;
	    $ret = array(
					'trim' => 0,
					'iconv' => 2,
					'explode' => 1,
					'preg_match' => 1,
				) ;

		return  $ret ;
	}
	
	
	/**
	 * 处理赋值性的一些内置函数
	 * 比如:
	 * 		$id = urlencode($_GET['id']) ;
	 * @param unknown $part
	 * @param unknown $type
	 * @param unknown $dataFlow
	 */
	public static function assignFuncHandler($part, $type, $dataFlow, $funcName){
			$single_func = self::getSingleFuncs() ;
			$encoding_convert = array('iconv') ;
			if($type == "right" && array_key_exists($funcName, $single_func)){
			    //首先搜索不安全字符的转换函数
			    if(in_array($funcName, $encoding_convert)){
			        $oneFunction = new OneFunction($funcName);
			        $dataFlow->getLocation()->addSanitization($oneFunction) ;
			    }
			    
				$position = $single_func[$funcName] ;
				$value = $part->args[$position]->value ;
                
				//解决trim(urlencode($id))的方法嵌套问题
				if($value->getType() == 'Expr_FuncCall'){
					$new_name = NodeUtils::getNodeFunctionName($value) ;
					self::assignFuncHandler($value, $type, $dataFlow, $new_name) ;
				}
				
				if($dataFlow->getValue() != null){
					return ;
				}
				
				$vars = SymbolUtils::getSymbolByNode($value) ;
				$dataFlow->setValue($vars) ;
			}
	}

	
	/**
	 * 处理赋值语句右边的三元表达式
	 * @param unknown $part
	 * @param unknown $dataFlow
	 */
	public static function ternaryHandler($type, $part, $dataFlow){
		$ter_symbol = new MutipleSymbol() ;
		$ter_symbol->setItemByNode($part) ;
		if($type == 'right'){
			$dataFlow->setValue($ter_symbol) ;
		}
	}

	
}







?>