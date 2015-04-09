<?php

/**
 * 存放用户自定义sink的上下文结构
 * 规定一个方法放入的格式：
 * [方法名称，敏感参数位置]
 * 
 * @author Exploit
 *
 */

class UserDefinedSinkContext {
	
	private static $instance ;  //单例
	
	private $F_XSS = array('__NAME__'=>'XSS') ;  //xss
	private $F_HTTP_HEADER = array('__NAME__'=>'HTTP') ; //http头注入
	private $F_CODE = array('__NAME__'=>'CODE') ;   //代码执行
	private $F_FILE_INCLUDE = array('__NAME__'=>'INCLUDE') ;  //文件包含
	private $F_FILE_READ = array('__NAME__'=>'FILE_READ') ;    //文件漏洞
	private $F_EXEC = array('__NAME__'=>'EXEC') ;    //命令执行
	private $F_DATABASE = array('__NAME__'=>'SQLI') ;   //SQL注入
	private $F_XPATH = array('__NAME__'=>'XPATH') ;    //XPATH注入
	private $F_LDAP = array('__NAME__'=>'LDAP') ;    //LDAP注入
	private $F_FILE_AFFECT = array('__NAME__'=>'FILEAFFECT') ;   //文件相关操作



	/**
	 * 将回溯中获取的用户定义sink函数传入
	 * @param Array $item  array(PDF,array(0,1))
	 * @param string $type=>()
	 */
	public function addByTagName($item,$type){
		if(!in_array($type,array('XSS','SQLI','HTTP','CODE','EXEC','LDAP','INCLUDE','FILE','XPATH','FILEAFFECT'))){
			return ;
		}
		
		switch ($type) {
			case 'XSS':
				foreach ($item as $k => $v){
					$this->F_XSS[$k] = $v ;
				}
				break;
			case 'SQLI':
				foreach ($item as $k => $v){
					$this->F_SQLI[$k] = $v ;
				}
				break;
			case 'HTTP':
				foreach ($item as $k => $v){
					$this->F_HTTP_HEADER[$k] = $v ;
				}
				break ;
			case 'CODE':
				foreach ($item as $k => $v){
					$this->F_CODE[$k] = $v ;
				}
				break;
			case 'EXEC':
				foreach ($item as $k => $v){
					$this->F_EXEC[$k] = $v ;
				}
				break;
			case 'LDAP':
				foreach ($item as $k => $v){
					$this->F_LDAP[$k] = $v ;
				}
				break;
			case 'INCLUDE':
				foreach ($item as $k => $v){
					$this->F_FILE_INCLUDE[$k] = $v ;
				}
				break;
			case 'FILE':
				foreach ($item as $k => $v){
					$this->F_FILE_READ[$k] = $v ;
				}
				break;
			case 'XPATH':
				foreach ($item as $k => $v){
					$this->F_XPATH[$k] = $v ;
				}
				break;
			case 'FILEAFFECT':
				foreach ($item as $k => $v){
					$this->F_FILE_AFFECT[$k] = $v ;
				}
				break;
		}
	}
	
	/**
	 * 获取所有的用户自定义sink
	 * @return multitype:
	 */
	public function getAllSinks(){
		return array_merge(
				$this->$F_XSS,
				$this->F_CODE,
				$this->F_DATABASE,
				$this->F_EXEC,
				$this->F_FILE_INCLUDE,
				$this->F_FILE_READ,
				$this->F_HTTP_HEADER,
				$this->F_LDAP,
				$this->F_XPATH		
		) ;	
	}
	
	/**
	 * 获取所有的sink——数组形式
	 * @return multitype:NULL
	 */
	public function getAllSinkArray(){
		return array(
				$this->$F_XSS,
				$this->F_CODE,
				$this->F_DATABASE,
				$this->F_EXEC,
				$this->F_FILE_INCLUDE,
				$this->F_FILE_READ,
				$this->F_HTTP_HEADER,
				$this->F_LDAP,
				$this->F_XPATH
		) ;
	}
	
	//--------------------单例模式---------------------------------
	private function __construct(){
		$this->records = array() ;
	}
	
	
	public static function getInstance(){
		if(!(self::$instance instanceof self)){
			self::$instance = new self ;
		}
		return self::$instance ;
	}
	
	private function __clone(){
	
	}

	//--------------------getter方法-------------------------------------
	public function getF_XSS() {
		return $this->F_XSS;
	}


	public function getF_HTTP_HEADER() {
		return $this->F_HTTP_HEADER;
	}


	public function getF_CODE() {
		return $this->F_CODE;
	}

	public function getF_FILE_INCLUDE() {
		return $this->F_FILE_INCLUDE;
	}


	public function getF_FILE_READ() {
		return $this->F_FILE_READ;
	}


	public function getF_EXEC() {
		return $this->F_EXEC;
	}


	public function getF_DATABASE() {
		return $this->F_DATABASE;
	}


	public function getF_XPATH() {
		return $this->F_XPATH;
	}

	public function getF_LDAP() {
		return $this->F_LDAP;
	}

	public function getF_FILE_AFFECT() {
		return $this->F_FILE_AFFECT;
	}

	
}

?>