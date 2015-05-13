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
	 * 根据漏洞的类型寻找对应的净化函数
	 * 'XSS','SQLI','HTTP','CODE','EXEC','LDAP','INCLUDE','FILE','XPATH','FILEAFFECT'
	 * @param string $type
	 */
	public static function getSecureListByType($type){
		global  $F_SECURING_SQL,$F_SECURING_XSS,
				$F_SECURING_PREG,$F_SECURING_SYSTEM,$F_SECURING_LDAP,
				$F_SECURING_XPATH,$F_SECURING_FILE;
		$mappings = array(
			'SQLI' => $F_SECURING_SQL,
			'XSS' => $F_SECURING_XSS,
			'HTTP' => array(),
			'CODE' => $F_SECURING_PREG,
			'EXEC' => $F_SECURING_SYSTEM,
			'LDAP' => $F_SECURING_LDAP,
			'INCLUDE' => $F_SECURING_FILE,
			'XPATH' => $F_SECURING_XPATH,
			'FILEAFFECT' => $F_SECURING_FILE
		) ;
		
		return $mappings[$type] ;
	}
	
	
	/**
	 * 根据净化栈和漏洞类型判断是否受到净化
	 * @param string $type  漏洞类型
	 * @param array $sanitiArr  净化栈
	 * @return bool true 表示受到净化   false反之
	 */
	public static function checkSanitiByArr($type, $sanitiArr){
		$userDefSinkContext = UserDefinedSinkContext::getInstance() ;
		
		//判断sanitiArr中是否存在list中
		$userDefSinkSaniti = $userDefSinkContext->getSinksSanitiByType($type) ;
		$confDefSinkSaniti = self::getSecureListByType($type) ;
		$combine_list = array_merge($userDefSinkSaniti,$confDefSinkSaniti) ;
		
		foreach ($sanitiArr as $value){
			if(in_array($value, $combine_list)){
				return true ;
			}
		}
		
		return false ;
	}
	
	
}






?>