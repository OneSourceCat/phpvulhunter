<?php
class HeaderAnalyser {
	/**
	 * 判断变量的净化情况
	 * 返回:
	 * 		(1)如果没有有效的净化，返回false
	 * 		(2)如果进行了有效的净化，返回true
	 * 		(3)如果净化数组为null,返回false
	 * @param symbol $var  判断的变量
	 * @param array $saniArr  判断的净化数组
	 * @return bool true
	 */
	private function check_sanitization($var,$saniArr){
		//如果数组为空，说明没有进行任何净化
		if(count($saniArr) == 0){
			return false ;
		}else if (SecureUtils::checkSanitiByArr("HTTP", $saniArr)) {
			//如果判别为真,则说明有效净化过
			return true;
		}else{
			return false ;
		}
	
	}
	
	/**
	 * 分析方法
	 * @param string $func_name 方法名
	 * @param Symbol $var 敏感变量节点
	 * @param array $saniArr 净化信息栈
	 * @param array $encodingArr 编码信息栈
	 * @return boolean
	 */
	public function analyse($var, $saniArr, $encodingArr){
	    if(empty($saniArr) && empty($encodingArr)){
			return false ;
		}
	
		//处理编码
		AnalyseUtils::initSaniti($saniArr) ;
		AnalyseUtils::initEncodeList($encodingArr) ;
	
		//编码和净化的判别
		if(AnalyseUtils::check_encoding($encodingArr) == true){
			//编码正确情况下，净化不够
			if($this->check_sanitization($var, $saniArr) == false){
				return false ;
			}else{
				return true ;
			}
		}else if(AnalyseUtils::check_encoding($encodingArr) == false){
			return false ;
		}
	}
}

?>