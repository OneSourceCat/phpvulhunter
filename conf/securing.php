<?php

//bool判断的安全函数
$F_SECURING_BOOL = array(
	'is_double',
	'is_float',
	'is_real',
	'is_long',
	'is_int',
	'is_integer',
	'is_numeric',
	'ctype_alnum',
	'ctype_alpha',
	'ctype_cntrl',
	'ctype_digit',
	'ctype_xdigit',
	'ctype_upper',
	'ctype_lower',
	'ctype_space',
	'in_array',
	'preg_match',
	'preg_match_all',
	'fnmatch',
	'ereg',
	'eregi'
);

//可以防止所有漏洞的函数
$F_SECURING_STRING = array(
	'intval',
	'floatval',
	'doubleval',
	'filter_input',
	'urlencode',
	'rawurlencode',
	'round',
	'floor',
	'strlen',
	'hexdec',
	'strrpos',
	'strpos',
	'md5',
	'sha1',
	'crypt',
	'crc32',
	'hash',
	'base64_encode',
	'ord',
	'sizeof',
	'count',
	'bin2hex',
	'levenshtein',
	'abs',
	'bindec',
	'decbin',
	'hexdec',
	'rand',
	'max',
	'min'
);

//消除净化标签的函数
$F_INSECURING_STRING = array(
	'rawurldecode',
	'urldecode',
	'base64_decode',
	'html_entity_decode',
	'stripslashes',
	'str_rot13',
	'chr'
);

//XSS的安全标签
$F_SECURING_XSS = array(
	'htmlentities',
	'htmlspecialchars'
);	

//SQLI的安全标签
$F_SECURING_SQL = array(
	'addslashes',
	'dbx_escape_string',
	'db2_escape_string',
	'ingres_escape_string',
	'maxdb_escape_string',
	'maxdb_real_escape_string',
	'mysql_escape_string',
	'mysql_real_escape_string',
	'mysqli_escape_string',
	'mysqli_real_escape_string',
	'pg_escape_string',	
	'pg_escape_bytea',
	'sqlite_escape_string',
	'sqlite_udf_encode_binary'
);	

//使用正则过滤RCE
$F_SECURING_PREG = array(
	'preg_quote'
);

//文件操作的安全函数
$F_SECURING_FILE = array(
	'basename',
	'pathinfo'
);

//防止命令注入的安全函数
$F_SECURING_SYSTEM = array(
	'escapeshellarg',
	'escapeshellcmd'
);	

//防止XPath注入的安全函数
$F_SECURING_XPATH = array(
	'addslashes'
);

//防止LDAP注入的安全函数
$F_SECURING_LDAP = array(
);

//全部的安全函数集合
$F_SECURES_ALL = array_merge(
	$F_SECURING_XSS, 
	$F_SECURING_SQL,
	$F_SECURING_PREG,
	$F_SECURING_FILE,
	$F_SECURING_SYSTEM,
	$F_SECURING_XPATH
);	

// securing functions that work only when embedded in quotes
$F_QUOTE_ANALYSIS = $F_SECURING_SQL;
		
?>	