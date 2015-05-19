<?php

require_once CURR_PATH . '/conf/sinks.php';
require_once CURR_PATH . '/context/UserDefinedSinkContext.class.php' ;

/**
 * 用于获取sink所属的漏洞类型
 * @author Exploit
 *
 */
class TypeUtils {
	
	/**
	 * 根据方法名查询sink所属的漏洞类别
	 * @param string $funcName
	 * @return string
	 */
	public static function getTypeByFuncName($funcName){
		//系统内置sink
		global $F_SINK_ALL,$F_SINK_ARRAY ;
		//用户自定义sink
		$userDefinedSink = UserDefinedSinkContext::getInstance() ;
		$U_SINK_ALL = $userDefinedSink->getAllSinks() ;
        
		//系统sink
		if(key_exists($funcName, $F_SINK_ALL)){
			foreach ($F_SINK_ARRAY as $value){
				if(key_exists($funcName, $value)){
					return $value['__NAME__'] ;
				}
			}
		}
		//用户sink
		if(key_exists($funcName, $U_SINK_ALL)){
			foreach ($userDefinedSink->getAllSinkArray() as $value){
				if(key_exists($funcName, $value)){
					return $value['__NAME__'] ;
				}
			}
		}
		
		return NULL ;	
	}
}


?>











