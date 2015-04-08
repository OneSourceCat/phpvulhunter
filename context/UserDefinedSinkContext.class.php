<?php
class UserDefinedSinkContext {
	
	private static $instance ;  
	
	private $F_XSS = array() ;  //xss
	private $F_HTTP_HEADER = array() ; //http头注入
	private $F_CODE = array() ;   //代码执行
	private $F_FILE_INCLUDE = array() ;  //文件包含
	private $F_FILE_READ = array() ;    //文件漏洞
	private $F_EXEC = array() ;    //命令执行
	private $F_DATABASE = array() ;   //SQL注入
	private $F_XPATH = array() ;    //XPATH注入
	private $F_LDAP = array() ;
	
	/**
	 * 将回溯中获取的用户定义sink函数传入
	 * @param Array $item  array(PDF,array(0,1))
	 * @param string $type=>()
	 */
	public function addByTagName($item,$type){
		if(!in_array($type,array('XSS','SQLI','HTTP','CODE','EXEC','LDAP','INCLUDE','FILE','XPATH'))){
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
		}
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


	
}

?>