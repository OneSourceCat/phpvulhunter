<?php

require_once CURR_PATH . '/conf/sink.php';

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
		if(in_array($funcName, $F_SINK_ALL)){
			foreach ($F_SINK_ARRAY as $value){
				if(in_array($funcName, $value)){
					return $value['__NAME__'] ;
				}
			}
		}
		//用户sink
		if(in_array($funcName, $U_SINK_ALL)){
			foreach ($userDefinedSink->getAllSinkArray() as $value){
				if(in_array($funcName, $value)){
					return $value['__NAME__'] ;
				}
			}
		}
		
		return NULL ;	
	}
}

?>











