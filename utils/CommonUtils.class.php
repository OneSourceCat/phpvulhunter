<?php
/**
 * 放置公用工具方法
 * @author Exploit
 *
 */
class CommonUtils{
    /**
     * 判断字符串needle是否是str的开头
     * @param string $str
     * @param string $needle
     * @return boolean
     */
    public static function startWith($str, $needle) {
        if(strpos($str, $needle) === 0){
            return true ;
        }else{
            return false ;
        }
    }
    
    /**
     * 判断target是否是source的结尾
     * @param string $source
     * @param string $target
     * @return boolean
     */
    public static function endsWith($source, $target){
        if(strrchr($source,$target) == $target){
            return true ;
        }else{
            return false ;
        }
    }
}

?>