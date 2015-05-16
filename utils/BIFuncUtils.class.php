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
		return  array(
					'urldecode' => 0,
					'urlencode' => 0,
					'intval' => 0,
					'is_numeric' => 0,
					'is_float' => 0,
					'trim' => 0,
					'iconv' => 2
					
				) ;
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
			if($type == "right" && array_key_exists($funcName, $single_func)){
				$position = $single_func[$funcName] ;
				$value = $part->args[$position]->value ;
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