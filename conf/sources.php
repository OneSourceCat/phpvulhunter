<?php
final class Sources
{	
	//用户输入参数
	public static $V_USERINPUT = array(
		'_GET',
		'_POST',
		'_COOKIE',
		'_REQUEST',
		'_FILES',
		'_SERVER',
		'_ENV',
		'HTTP_GET_VARS',
		'HTTP_POST_VARS',
		'HTTP_COOKIE_VARS',  
		'HTTP_REQUEST_VARS', 
		'HTTP_POST_FILES',
		'HTTP_SERVER_VARS',
		'HTTP_ENV_VARS',
		'HTTP_RAW_POST_DATA',
		'argc',
		'argv'
	);
	
	//Server内容
	public static $V_SERVER_PARAMS = array(
		'HTTP_ACCEPT',
		'HTTP_ACCEPT_LANGUAGE',
		'HTTP_ACCEPT_ENCODING',
		'HTTP_ACCEPT_CHARSET',
		'HTTP_CONNECTION',
		'HTTP_HOST',
		'HTTP_KEEP_ALIVE',
		'HTTP_REFERER',
		'HTTP_USER_AGENT',
		'HTTP_X_FORWARDED_FOR',
		// all HTTP_ headers can be tainted
		'PHP_AUTH_DIGEST',
		'PHP_AUTH_USER',
		'PHP_AUTH_PW',
		'AUTH_TYPE',
		'QUERY_STRING',
		'REQUEST_URI', // partly urlencoded
		'PATH_INFO',
		'ORIG_PATH_INFO',
		'PATH_TRANSLATED',
		'PHP_SELF'
	);
	
	/**
	 * 获取用户的输入sources
	 * @return array()
	 */
	public static function getUserInput(){
		return array_merge(
					self::$V_USERINPUT ,
					self::$V_SERVER_PARAMS
				) ;
	}
	
	
	
	//文件内容当做输入
	public static $F_FILE_INPUT = array(
		'bzread',
		'dio_read',
		'exif_imagetype',
		'exif_read_data',
		'exif_thumbnail',
		'fgets',
		'fgetss',
		'file', 
		'file_get_contents',
		'fread',
		'get_meta_tags',
		'glob',
		'gzread',
		'readdir',
		'read_exif_data',
		'scandir',
		'zip_read'
	);
	
	//数据库查询结果当做输入
	public static $F_DATABASE_INPUT = array(
		'mysql_fetch_array',
		'mysql_fetch_assoc',
		'mysql_fetch_field',
		'mysql_fetch_object',
		'mysql_fetch_row',
		'pg_fetch_all',
		'pg_fetch_array',
		'pg_fetch_assoc',
		'pg_fetch_object',
		'pg_fetch_result',
		'pg_fetch_row',
		'sqlite_fetch_all',
		'sqlite_fetch_array',
		'sqlite_fetch_object',
		'sqlite_fetch_single',
		'sqlite_fetch_string'
	);
	
	
}
	


?>	