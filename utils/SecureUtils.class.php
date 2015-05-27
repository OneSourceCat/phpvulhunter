<?php

require_once CURR_PATH . '/conf/securing.php';
require_once CURR_PATH . '/conf/sinks.php';

/**
 * 
 * @author Exploit
 *
 */
class SecureUtils {
	
    /**
     * 返回通用的安全函数列表
     * @return multitype:
     */
    public static function getCommonSecureList(){
        global $F_SECURING_STRING, $F_SECURING_BOOL ;
        unset($F_SECURING_STRING['__NAME__']) ;
        unset($F_SECURING_BOOL['__NAME__']) ;
        
        return array_merge($F_SECURING_STRING, $F_SECURING_BOOL) ;
    }
    
    
	/**
	 * 根据漏洞的类型寻找对应的净化函数
	 * 'XSS','SQLI','HTTP','CODE','EXEC','LDAP','INCLUDE','FILE','XPATH','FILEAFFECT'
	 * @param string $type
	 */
	public static function getSecureListByType($type){
		global  $F_SECURING_SQL,$F_SECURING_XSS,
				$F_SECURING_PREG,$F_SECURING_SYSTEM,$F_SECURING_LDAP,
				$F_SECURING_XPATH,$F_SECURING_FILE,$F_SECURING_STRING;
		$mappings = array(
			'SQLI' => $F_SECURING_SQL,
			'XSS' => $F_SECURING_XSS,
			'HTTP' => array(),
			'CODE' => $F_SECURING_PREG,
			'EXEC' => $F_SECURING_SYSTEM,
			'LDAP' => $F_SECURING_LDAP,
			'INCLUDE' => $F_SECURING_FILE,
			'XPATH' => $F_SECURING_XPATH,
		    'FILE' => $F_SECURING_FILE,
			'FILEAFFECT' => $F_SECURING_FILE
		) ;
		
		return array_merge($mappings[$type],$F_SECURING_STRING) ;
	}
	
	/**
	 * 找到arr1中的第一个存在于arr2的元素，并返回位置
	 * @param array $arr1
	 * @param array $arr2
	 * @return number
	 */
	private function findFirstPosition($arr1, $arr2){
		for($i=0;$i<count($arr1);$i++){
			if(in_array($arr1[$i], $arr2)){
				return $i ;
			}
		}
		return false ;
	}
	
	
	/**
	 * 根据净化栈和漏洞类型判断是否受到净化
	 * @param string $type  漏洞类型
	 * @param array $sanitiArr  净化栈
	 * @return bool true 表示受到净化   false反之
	 */
	public static function checkSanitiByArr($type, $sanitiArr){
		//获取用户自定义sink上下文
		$userDefSinkContext = UserDefinedSinkContext::getInstance() ;
		
		//判断sanitiArr中是否存在list中
		$userDefSinkSaniti = $userDefSinkContext->getSinksSanitiByType($type) ;
		$confDefSinkSaniti = self::getSecureListByType($type) ;
		$commonDefSinkSaniti = self::getCommonSecureList() ;
	    $combine_list = array_merge($userDefSinkSaniti,$confDefSinkSaniti,$commonDefSinkSaniti) ;
		
		foreach ($sanitiArr as $value){
			if(in_array($value->funcName, $combine_list)){
				return true ;
			}
		}
		
		return false ;
	}
	
	
}






?>