<?php

/**
 * analyser中的分析工具类
 * @author Exploit
 *
 */
class AnalyseUtils {
	/**
	 * 对编码数组进行调整
	 * @param unknown $encodingArr
	 */
	public static function initEncodeList(&$encodingArr){
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
	public static function initSaniti(&$saniArr){
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
	public static function check_encoding($encodingArr){
		if(count($encodingArr) == 0){
			return -1 ;
		}
	
		$secu_ins = array('md5','sha1','crc32') ;
		$vul_encode_ins = array('urlencode','base64_encode','html_entity_decode','htmlspecialchars_decode') ;
	
		//如果最后一个编码为secu_ins,则返回false
		$last = array_pop($encodingArr) ;
		if(in_array($last, $secu_ins)){
			return true ;
		}
		
		//如果直接解码操作，则编码不安全
		if(in_array($last, $vul_encode_ins)){
		    return false;
		}
	
	}
}

?>