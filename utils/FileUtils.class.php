<?php
/**
 * 文件操作类
 * 与文件相关的操作放入至本类中
 * @author exploit
 *
 */
class FileUtil{

	public function __construct(){

	}

	/*
		获取文件
		@param @path 文件夹路径
		@return 文件夹下的所有文件的数组（递归）
	*/
	public function getDir($path){
		static $ret = array() ;
		if(!is_dir($path)){
			array_push($ret, $path) ;
			return $ret ;
		}
		if(($handle = opendir($path)) == false){
			return $ret ;
		}
		while(($file = readdir($handle))!=false){
			if($file == "." || $file == ".."){
				continue ;
			}
			if(is_dir($path . "/" . $file)){
				$this->getDir($path ."/".$file) ;
			}else{
				//只返回php后缀的文件
				if(strchr($path ."/" .$file,".php") == ".php"){
					array_push($ret,$path ."/".$file) ;	
				}
				
			}
		}	
		closedir($handle) ;
		return $ret ;
	}

}

?>