<?php
/**
 * 进行sql注入的漏洞分析
 * @author Exploit
 *
 */
class SqliAnalyser {
	
	/**
	 * 对编码数组进行调整
	 * @param unknown $encodingArr
	 */
	private function initEncodeList(&$encodingArr){
		global $F_ENCODING_STRING;
		$len = count($encodingArr) ;
		if($len == 0) return ;
		//调整
		for($i=0;$i<$len;$i++){
			if(in_array($encodingArr[$i], $F_ENCODING_STRING)){
				//处理url编码
				switch ($encodingArr[$i]){
					case "rawurlencode":
					case "urlencode":
						//向后找decoding看是否可以抵消
						for($j=0;$j<$i;$j++){
							if($encodingArr[$i] == "urlencode" && $encodingArr[$j] == "urldecode"){
								array_slice($encodingArr, $i) ;
								array_slice($encodingArr, $j) ;
							}else if($encodingArr[$i] == "rawurlencode" && $encodingArr[$j] == "rawurldecode"){
								array_slice($encodingArr, $i) ;
								array_slice($encodingArr, $j) ;
							}
						}
						break ;
					case "base64_encode":
						//向后找decoding看是否可以抵消
						for($j=0;$j<$i;$j++){
							if($encodingArr[$j] == "base64_decode"){
								array_slice($encodingArr, $i) ;
								array_slice($encodingArr, $j) ;
							}
						}
						break ;
					case "html_entity_encode":
						//向后找decoding看是否可以抵消
						for($j=0;$j<$i;$j++){
							if($encodingArr[$j] == "html_entity_encode"){
								array_slice($encodingArr, $i) ;
								array_slice($encodingArr, $j) ;
							}
						}
						break ;
				}
			}
			
			
		}
	}
	
	/**
	 * 对净化数组进行调整
	 * @param array $saniArr
	 */
	private function initSaniti(&$saniArr){
		$len = count($saniArr) ;
		if($len == 0) return ;
		for($i=0;$i<$len;$i++){
			//处理反转义
			if($saniArr[$i] == "addslashes"){
				//向后找stripslashes看是否可以抵消
				for($j=0;$j<$i;$j++){
					if($saniArr[$j] == "stripcslashes"){
						array_slice($saniArr, $i) ;
						array_slice($saniArr, $j) ;
					}
				}
			}
		}
	}
	
	
	/**
	 * 判断变量的编码情况
	 * 如果只对变量进行解码操作，则不安全
	 * 如果对变量进行了md5或者sha运算，则安全
	 * 返回:
	 * 	(1)true 	=> 编码安全
	 * 	(2)false 	=> 编码不安全
	 * 	(3)-1 		=> 无编码
	 * @param array $encodingArr
	 * @return bool 
	 */
	private function check_encoding($encodingArr){
		if(count($encodingArr) == 0){
			return -1 ;
		}
		
		$secu_ins = array('md5','sha1') ;
		$vul_encode_ins = array('urlencode','base64_encode') ;
		
		//如果最后一个编码为secu_ins,则返回false
		$last = array_pop($encodingArr) ;
		if(in_array($last, $secu_ins) || in_array($last, $vul_encode_ins)){
			return true ;
		}
		
	}
	
	/**
	 * 判断变量的净化情况
	 * 返回:
	 * 		(1)如果没有有效的净化，返回false
	 * 		(2)如果进行了有效的净化，返回true
	 * 		(3)如果净化数组为null,返回false
	 * @param symbol $var  判断的变量
	 * @param array $saniArr  判断的净化数组
	 * @return bool
	 */
	private function check_sanitization($var,$saniArr){
		//如果数组为空，说明没有进行任何净化
		if(count($saniArr) == 0){
			return false ;
		}
		//数值型注入，转义无效
		if($var->getType() == "int" && in_array("addslashes", $saniArr)){
			return false ;
		}
		
		return true ;
	}
	
	
	/**
	 * 根据变量的净化栈和编码栈判断是否是有效净化和编码
	 * 返回:
	 * 		(1)true 	=> 有效净化
	 * 		(2)false 	=> 无效净化
	 * @param array $saniArr
	 * @param array $encodingArr
	 * @return bool
	 */
	public function analyse($var,$saniArr,$encodingArr){
		//处理编码
		$this->initSaniti($saniArr) ;
		$this->initEncodeList($encodingArr) ;
		
		//编码和净化的判别
		if($this->check_encoding($encodingArr) == true){
			//编码正确情况下，净化不够
			if($this->check_sanitization($var, $saniArr) == false){
				return false ;
			}else{
				return true ;
			}
		}else if($this->check_encoding($encodingArr) == false){
			return false ;
		}
		
	}
	
	
}









?>