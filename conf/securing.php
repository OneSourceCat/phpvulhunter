<?php
$SECURES_TYPE_ALL = array(
    'BOOL',
    'SQLI',
    'XSS',
    'PREG',
    'FILE',
    'EXEC',
    'XPATH',
    'LDAP',
);
//bool判断的安全函数
$F_SECURING_BOOL = array(
    '__NAME__'  => 'BOOL',
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
    '__NAME__'  => $SECURES_TYPE_ALL,
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

//PHP中的编码函数
$F_ENCODING_STRING = array(
    '__NAME__'  => 'encoding',
	'rawurlencode',
	'urlencode',
	'base64_encode',
	'html_entity_encode'
) ;

//PHP中的解码函数
$F_DECODING_STRING = array(
    '__NAME__'  => 'decoding',
	'rawurldecode',
	'urldecode',
	'base64_decode',
	'html_entity_decode'
) ;


//消除净化标签的函数
$F_INSECURING_STRING = array(
    '__NAME__'  => 'insecuring',
	'rawurldecode',
	'urldecode',
	'base64_decode',
	'html_entity_decode',
	'stripslashes',
	'str_rot13',
	'chr',
	'htmlspecialchars_decode'
);

//XSS的安全标签
$F_SECURING_XSS = array(
    '__NAME__'  => 'XSS',
	'htmlentities',
	'htmlspecialchars'
);	

//SQLI的安全标签
$F_SECURING_SQL = array(
    '__NAME__'  => 'SQLI',
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
    '__NAME__'  => 'PREG',
	'preg_quote'
);

//文件操作的安全函数
$F_SECURING_FILE = array(
    '__NAME__'  => 'FILE',
	'basename',
	'pathinfo'
);

//防止命令注入的安全函数
$F_SECURING_SYSTEM = array(
    '__NAME__'  => 'EXEC',
	'escapeshellarg',
	'escapeshellcmd'
);	

//防止XPath注入的安全函数
$F_SECURING_XPATH = array(
    '__NAME__'  => 'XPATH',
	'addslashes'
);

//防止LDAP注入的安全函数
$F_SECURING_LDAP = array(
    '__NAME__'  => 'LDAP',
);

//全部的安全函数集合
$F_SECURES_ALL = array_merge(
	$F_SECURING_BOOL,
    $F_SECURING_STRING,
    $F_SECURING_XSS,
    $F_SECURING_SQL,
    $F_SECURING_PREG,
    $F_SECURING_FILE,
    $F_SECURING_SYSTEM,
    $F_SECURING_XPATH
);	

$F_SECURES_ARRAY = array(
    $F_SECURING_BOOL,
    $F_SECURING_STRING,
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