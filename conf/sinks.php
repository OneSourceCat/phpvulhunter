<?php

require 'securing.php';

//跨站脚本的危险函数
//参数为0表示所有的参数都要进行分析
$NAME_XSS = 'Cross-Site Scripting';
$F_XSS = array(
	'__NAME__'						=> 'XSS',
	'echo'							=> array(array(0), $F_SECURING_XSS), 
	'print'							=> array(array(1), $F_SECURING_XSS),
	'print_r'						=> array(array(1), $F_SECURING_XSS),
	'exit'							=> array(array(1), $F_SECURING_XSS),
	'die'							=> array(array(1), $F_SECURING_XSS),
	'printf'						=> array(array(0), $F_SECURING_XSS),
	'vprintf'						=> array(array(0), $F_SECURING_XSS)
);
	
//HTTP头注入
$NAME_HTTP_HEADER = 'HTTP Response Splitting';
$F_HTTP_HEADER = array(
	'__NAME__'						=> 'HTTP',
	'header' 						=> array(array(1), array())
);
	
// code evaluating functions  => (parameters to scan, securing functions)
// example parameter array(1,3) will trace only first and third parameter 
$NAME_CODE = 'Code Execution';
$F_CODE = array(
	'__NAME__'						=> 'CODE',
	'array_diff_uassoc'				=> array(array(3), array()),
	'array_diff_ukey'				=> array(array(3), array()),
	'array_filter'					=> array(array(2), array()),
	'array_intersect_uassoc'		=> array(array(3), array()),
	'array_intersect_ukey'			=> array(array(3), array()),
	'array_map'						=> array(array(1), array()),
	'array_reduce'					=> array(array(2), array()),
	'array_udiff'					=> array(array(3), array()),
	'array_udiff_assoc'				=> array(array(3), array()),
	'array_udiff_uassoc'			=> array(array(3,4), array()),
	'array_uintersect'				=> array(array(3), array()),
	'array_uintersect_assoc'		=> array(array(3), array()),
	'array_uintersect_uassoc'		=> array(array(3,4), array()),		
	'array_walk'					=> array(array(2), array()),
	'array_walk_recursive'			=> array(array(2), array()),
	'assert' 						=> array(array(1), array()),
	'assert_options'				=> array(array(1,2), array()),
	'call_user_func'				=> array(array(1), array()),
	'call_user_func_array'			=> array(array(1), array()),
	'create_function' 				=> array(array(1,2), array()),
	'dotnet_load'					=> array(array(1), array()),
	'forward_static_call'			=> array(array(1), array()),
	'forward_static_call_array'		=> array(array(1), array()),
	'eio_busy'						=> array(array(3), array()),
	'eio_chmod'						=> array(array(4), array()),
	'eio_chown'						=> array(array(5), array()),
	'eio_close'						=> array(array(3), array()),
	'eio_custom'					=> array(array(1,2), array()),
	'eio_dup2'						=> array(array(4), array()),
	'eio_fallocate'					=> array(array(6), array()),
	'eio_fchmod'					=> array(array(4), array()),
	'eio_fchown'					=> array(array(5), array()),
	'eio_fdatasync'					=> array(array(3), array()),
	'eio_fstat'						=> array(array(3), array()),
	'eio_fstatvfs'					=> array(array(3), array()),
	'eval' 							=> array(array(1), array()),
	'event_buffer_new'				=> array(array(2,3,4), array()),		
	'event_set'						=> array(array(4), array()),
	'iterator_apply'				=> array(array(2), array()),
	'mb_ereg_replace'				=> array(array(1,2), $F_SECURING_PREG),
	'mb_eregi_replace'				=> array(array(1,2), $F_SECURING_PREG),
	'ob_start'						=> array(array(1), array()),
	'preg_filter'					=> array(array(1,2), $F_SECURING_PREG),
	'preg_replace'					=> array(array(1,2), $F_SECURING_PREG),
	'preg_replace_callback'			=> array(array(1,2), $F_SECURING_PREG),
	'register_shutdown_function'	=> array(array(1), array()),
	'register_tick_function'		=> array(array(1), array()),
	'runkit_method_add'				=> array(array(1,2,3,4), array()),
	'runkit_method_copy'			=> array(array(1,2,3), array()),
	'runkit_method_redefine'		=> array(array(1,2,3,4), array()),	
	'runkit_method_rename'			=> array(array(1,2,3), array()),
	'runkit_function_add'			=> array(array(1,2,3), array()),
	'runkit_function_copy'			=> array(array(1,2), array()),
	'runkit_function_redefine'		=> array(array(1,2,3), array()),
	'runkit_function_rename'		=> array(array(1,2), array()),
	'session_set_save_handler'		=> array(array(1,2,3,4,5), array()),
	'set_error_handler'				=> array(array(1), array()),
	'set_exception_handler'			=> array(array(1), array()),
	'spl_autoload'					=> array(array(1), array()),	
	'spl_autoload_register'			=> array(array(1), array()),
	'sqlite_create_aggregate'		=> array(array(2,3,4), array()), 
	'sqlite_create_function'		=> array(array(2,3), array()), 
	'stream_wrapper_register'		=> array(array(2), array()), 
	'uasort'						=> array(array(2), array()),
	'uksort'						=> array(array(2), array()),
	'usort'							=> array(array(2), array()),
	'yaml_parse'					=> array(array(4), array()),
	'yaml_parse_file'				=> array(array(4), array()),
	'yaml_parse_url'				=> array(array(4), array())
);
	
//文件包含函数  => (要扫描的参数, 安全函数)
$NAME_FILE_INCLUDE = 'File Inclusion';
$F_FILE_INCLUDE = array(
	'__NAME__'						=> 'INCLUDE',
	'include' 						=> array(array(1), $F_SECURING_FILE),
	'include_once' 					=> array(array(1), $F_SECURING_FILE),
	'parsekit_compile_file'			=> array(array(1), $F_SECURING_FILE),
	'php_check_syntax' 				=> array(array(1), $F_SECURING_FILE),	
	'require' 						=> array(array(1), $F_SECURING_FILE),
	'require_once' 					=> array(array(1), $F_SECURING_FILE),
	'runkit_import'					=> array(array(1), $F_SECURING_FILE),
	'set_include_path' 				=> array(array(1), $F_SECURING_FILE),
	'virtual' 						=> array(array(1), $F_SECURING_FILE)		
);

// file affecting functions  => (parameters to scan, securing functions)
// file handler functions like fopen() are added as parameter 
// for functions that use them like fread() and fwrite()
$NAME_FILE_READ = 'File Disclosure';
$F_FILE_READ = array(
	'__NAME__'						=> 'FILE',
	'bzread'						=> array(array(1), $F_SECURING_FILE), 
	'bzflush'						=> array(array(1), $F_SECURING_FILE), 
	'dio_read'						=> array(array(1), $F_SECURING_FILE),   
	'eio_readdir'					=> array(array(1), $F_SECURING_FILE),  
	'fdf_open'						=> array(array(1), $F_SECURING_FILE), 
	'file'							=> array(array(1), $F_SECURING_FILE), 
	'file_get_contents'				=> array(array(1), $F_SECURING_FILE),  
	'finfo_file'					=> array(array(1,2), array()), 
	'fflush'						=> array(array(1), $F_SECURING_FILE),
	'fgetc'							=> array(array(1), $F_SECURING_FILE),
	'fgetcsv'						=> array(array(1), $F_SECURING_FILE),
	'fgets'							=> array(array(1), $F_SECURING_FILE),
	'fgetss'						=> array(array(1), $F_SECURING_FILE),
	'fread'							=> array(array(1), $F_SECURING_FILE), 
	'fpassthru'						=> array(array(1,2), array()), 
	'fscanf'						=> array(array(1), $F_SECURING_FILE), 
	'ftok'							=> array(array(1), $F_SECURING_FILE),
	'get_meta_tags'					=> array(array(1), $F_SECURING_FILE), 
	'glob'							=> array(array(1), array()), 
	'gzfile'						=> array(array(1), $F_SECURING_FILE), 
	'gzgetc'						=> array(array(1), $F_SECURING_FILE),
	'gzgets'						=> array(array(1), $F_SECURING_FILE), 
	'gzgetss'						=> array(array(1), $F_SECURING_FILE), 
	'gzread'						=> array(array(1), $F_SECURING_FILE),  
	'gzpassthru'					=> array(array(1), $F_SECURING_FILE), 
	'highlight_file'				=> array(array(1), $F_SECURING_FILE),  
	'imagecreatefrompng'			=> array(array(1), $F_SECURING_FILE), 
	'imagecreatefromjpg'			=> array(array(1), $F_SECURING_FILE), 
	'imagecreatefromgif'			=> array(array(1), $F_SECURING_FILE), 
	'imagecreatefromgd2'			=> array(array(1), $F_SECURING_FILE), 
	'imagecreatefromgd2part'		=> array(array(1), $F_SECURING_FILE), 
	'imagecreatefromgd'				=> array(array(1), $F_SECURING_FILE),  
	'opendir'						=> array(array(1), $F_SECURING_FILE),  
	'parse_ini_file' 				=> array(array(1), $F_SECURING_FILE),	
	'php_strip_whitespace'			=> array(array(1), $F_SECURING_FILE),	
	'readfile'						=> array(array(1), $F_SECURING_FILE), 
	'readgzfile'					=> array(array(1), $F_SECURING_FILE), 
	'readlink'						=> array(array(1), $F_SECURING_FILE),		
	'scandir'						=> array(array(1), $F_SECURING_FILE),
	'show_source'					=> array(array(1), $F_SECURING_FILE),
	'simplexml_load_file'			=> array(array(1), $F_SECURING_FILE),
	'stream_get_contents'			=> array(array(1), $F_SECURING_FILE),
	'stream_get_line'				=> array(array(1), $F_SECURING_FILE),
	'xdiff_file_bdiff'				=> array(array(1,2), $F_SECURING_FILE),
	'xdiff_file_bpatch'				=> array(array(1,2), $F_SECURING_FILE),
	'xdiff_file_diff_binary'		=> array(array(1,2), $F_SECURING_FILE),
	'xdiff_file_diff'				=> array(array(1,2), $F_SECURING_FILE),
	'xdiff_file_merge3'				=> array(array(1,2,3), $F_SECURING_FILE),
	'xdiff_file_patch_binary'		=> array(array(1,2), $F_SECURING_FILE),
	'xdiff_file_patch'				=> array(array(1,2), $F_SECURING_FILE),
	'xdiff_file_rabdiff'			=> array(array(1,2), $F_SECURING_FILE),
	'yaml_parse_file'				=> array(array(1), $F_SECURING_FILE),
	'zip_open'						=> array(array(1), $F_SECURING_FILE)
);
	
// file or file system affecting functions
$NAME_FILE_AFFECT = 'File Manipulation';
$F_FILE_AFFECT = array(
	'__NAME__'						=> 'FILEAFFECT',
	'bzwrite'						=> array(array(2), array()),
	'chmod'							=> array(array(1), $F_SECURING_FILE),
	'chgrp'							=> array(array(1), $F_SECURING_FILE),
	'chown'							=> array(array(1), $F_SECURING_FILE),
	'copy'							=> array(array(1), array()),
	'dio_write'						=> array(array(1,2), array()),	
	'eio_chmod'						=> array(array(1), $F_SECURING_FILE),
	'eio_chown'						=> array(array(1), $F_SECURING_FILE),
	'eio_mkdir'						=> array(array(1), $F_SECURING_FILE),
	'eio_mknod'						=> array(array(1), $F_SECURING_FILE),
	'eio_rmdir'						=> array(array(1), $F_SECURING_FILE),
	'eio_write'						=> array(array(1,2), array()),
	'eio_unlink'					=> array(array(1), $F_SECURING_FILE),
	'error_log'						=> array(array(3), $F_SECURING_FILE),
	'event_buffer_write'			=> array(array(2), array()),
	'file_put_contents'				=> array(array(1,2), $F_SECURING_FILE),
	'fputcsv'						=> array(array(1,2), $F_SECURING_FILE),
	'fputs'							=> array(array(1,2), $F_SECURING_FILE),	
	'fprintf'						=> array(array(0), array()),	
	'ftruncate'						=> array(array(1), $F_SECURING_FILE),
	'fwrite'						=> array(array(1,2), $F_SECURING_FILE),		
	'gzwrite'						=> array(array(1,2), array()),
	'gzputs'						=> array(array(1,2), array()),
	'loadXML'						=> array(array(1), array()),
	'mkdir'							=> array(array(1), array()),
	'move_uploaded_file'			=> array(array(1,2), $F_SECURING_FILE),	
	'posix_mknod'					=> array(array(1), $F_SECURING_FILE),
	'recode_file'					=> array(array(2,3), $F_SECURING_FILE),	
	'rename'						=> array(array(1,2), $F_SECURING_FILE),
	'rmdir'							=> array(array(1), $F_SECURING_FILE),	
	'shmop_write'					=> array(array(2), array()),
	'touch'							=> array(array(1), $F_SECURING_FILE),
	'unlink'						=> array(array(1), $F_SECURING_FILE),
	'vfprintf'						=> array(array(0), array()),	
	'xdiff_file_bdiff'				=> array(array(3), $F_SECURING_FILE),
	'xdiff_file_bpatch'				=> array(array(3), $F_SECURING_FILE),
	'xdiff_file_diff_binary'		=> array(array(3), $F_SECURING_FILE),
	'xdiff_file_diff'				=> array(array(3), $F_SECURING_FILE),	
	'xdiff_file_merge3'				=> array(array(4), $F_SECURING_FILE),
	'xdiff_file_patch_binary'		=> array(array(3), $F_SECURING_FILE),
	'xdiff_file_patch'				=> array(array(3), $F_SECURING_FILE),
	'xdiff_file_rabdiff'			=> array(array(3), $F_SECURING_FILE),
	'yaml_emit_file'				=> array(array(1,2), $F_SECURING_FILE),
);

//系统命令执行函数  => (parameters to scan, securing functions)
$NAME_EXEC = 'Command Execution';
$F_EXEC = array(
	'__NAME__'						=> 'EXEC',
	'backticks'						=> array(array(1), $F_SECURING_SYSTEM), 
	'exec'							=> array(array(1), $F_SECURING_SYSTEM),
	'expect_popen'					=> array(array(1), $F_SECURING_SYSTEM),
	'passthru'						=> array(array(1), $F_SECURING_SYSTEM),
	'pcntl_exec'					=> array(array(1), $F_SECURING_SYSTEM),
	'popen'							=> array(array(1), $F_SECURING_SYSTEM),
	'proc_open'						=> array(array(1), $F_SECURING_SYSTEM),
	'shell_exec'					=> array(array(1), $F_SECURING_SYSTEM),
	'system'						=> array(array(1), $F_SECURING_SYSTEM),
	'mail'							=> array(array(5), array()), 
	'mb_send_mail'					=> array(array(5), array()),
	'w32api_invoke_function'		=> array(array(1), array()),
	'w32api_register_function'		=> array(array(2), array()),
);

//SQL语句执行函数 => (parameters to scan, securing functions)
$NAME_DATABASE = 'SQL Injection';
$F_DATABASE = array(
	'__NAME__'						=> 'SQLI',
	'dba_open'						=> array(array(1), array()),
	'dba_popen'						=> array(array(1), array()), 
	'dba_insert'					=> array(array(1,2), array()),
	'dba_fetch'						=> array(array(1), array()), 
	'dba_delete'					=> array(array(1), array()), 
	'dbx_query'						=> array(array(2), $F_SECURING_SQL), 
	'odbc_do'						=> array(array(2), $F_SECURING_SQL),
	'odbc_exec'						=> array(array(2), $F_SECURING_SQL),
	'odbc_execute'					=> array(array(2), $F_SECURING_SQL),
	'db2_exec' 						=> array(array(2), $F_SECURING_SQL),
	'db2_execute'					=> array(array(2), $F_SECURING_SQL),
	'fbsql_db_query'				=> array(array(2), $F_SECURING_SQL),
	'fbsql_query'					=> array(array(1), $F_SECURING_SQL), 
	'ibase_query'					=> array(array(2), $F_SECURING_SQL), 
	'ibase_execute'					=> array(array(1), $F_SECURING_SQL), 
	'ifx_query'						=> array(array(1), $F_SECURING_SQL), 
	'ifx_do'						=> array(array(1), $F_SECURING_SQL),
	'ingres_query'					=> array(array(2), $F_SECURING_SQL),
	'ingres_execute'				=> array(array(2), $F_SECURING_SQL),
	'ingres_unbuffered_query'		=> array(array(2), $F_SECURING_SQL),
	'msql_db_query'					=> array(array(2), $F_SECURING_SQL), 
	'msql_query'					=> array(array(1), $F_SECURING_SQL),
	'msql'							=> array(array(2), $F_SECURING_SQL), 
	'mssql_query'					=> array(array(1), $F_SECURING_SQL), 
	'mssql_execute'					=> array(array(1), $F_SECURING_SQL),
	'mysql_db_query'				=> array(array(2), $F_SECURING_SQL),  
	'mysql_query'					=> array(array(1), $F_SECURING_SQL), 
	'mysql_unbuffered_query'		=> array(array(1), $F_SECURING_SQL), 
	'mysqli_stmt_execute'			=> array(array(1), $F_SECURING_SQL),
	'mysqli_query'					=> array(array(2), $F_SECURING_SQL),
	'mysqli_real_query'				=> array(array(1), $F_SECURING_SQL),
	'mysqli_master_query'			=> array(array(2), $F_SECURING_SQL),
	'oci_execute'					=> array(array(1), array()),
	'ociexecute'					=> array(array(1), array()),
	'ovrimos_exec'					=> array(array(2), $F_SECURING_SQL),
	'ovrimos_execute'				=> array(array(2), $F_SECURING_SQL),
	'ora_do'						=> array(array(2), array()), 
	'ora_exec'						=> array(array(1), array()), 
	'pg_query'						=> array(array(2), $F_SECURING_SQL),
	'pg_send_query'					=> array(array(2), $F_SECURING_SQL),
	'pg_send_query_params'			=> array(array(2), $F_SECURING_SQL),
	'pg_send_prepare'				=> array(array(3), $F_SECURING_SQL),
	'pg_prepare'					=> array(array(3), $F_SECURING_SQL),
	'sqlite_open'					=> array(array(1), $F_SECURING_SQL),
	'sqlite_popen'					=> array(array(1), $F_SECURING_SQL),
	'sqlite_array_query'			=> array(array(1,2), $F_SECURING_SQL),
	'arrayQuery'					=> array(array(1,2), $F_SECURING_SQL),
	'singleQuery'					=> array(array(1), $F_SECURING_SQL),
	'sqlite_query'					=> array(array(1,2), $F_SECURING_SQL),
	'sqlite_exec'					=> array(array(1,2), $F_SECURING_SQL),
	'sqlite_single_query'			=> array(array(2), $F_SECURING_SQL),
	'sqlite_unbuffered_query'		=> array(array(1,2), $F_SECURING_SQL),
	'sybase_query'					=> array(array(1), $F_SECURING_SQL), 
	'sybase_unbuffered_query'		=> array(array(1), $F_SECURING_SQL)
);
	
//XPath注入函数
$NAME_XPATH = 'XPath Injection';
$F_XPATH = array(
	'__NAME__'						=> 'XPATH',
	'xpath_eval'					=> array(array(2), $F_SECURING_XPATH),	
	'xpath_eval_expression'			=> array(array(2), $F_SECURING_XPATH),		
	'xptr_eval'						=> array(array(2), $F_SECURING_XPATH)
);

//LDAP注入
$NAME_LDAP = 'LDAP Injection';
$F_LDAP = array(
	'__NAME__'						=> 'LDAP',
	'ldap_add'						=> array(array(2,3), $F_SECURING_LDAP),
	'ldap_delete'					=> array(array(2), $F_SECURING_LDAP),
	'ldap_list'						=> array(array(3), $F_SECURING_LDAP),
	'ldap_read'						=> array(array(3), $F_SECURING_LDAP),
	'ldap_search'					=> array(array(3), $F_SECURING_LDAP)
);	
		

	
// property oriented programming with unserialize
$NAME_POP = 'Unserialize';
$F_POP = array(
    '__NAME__'						=> 'UNSERIALIZE',
	'unserialize'					=> array(array(1), array()), // calls __destruct
	'is_a'							=> array(array(1), array())	 // calls __autoload in php 5.3.7, 5.3.8
);
	
$F_SINK_ALL = array_merge(
		$F_XSS,
		$F_LDAP,
		$F_XPATH,
		$F_DATABASE,
		$F_EXEC,
		$F_FILE_AFFECT,
		$F_FILE_READ,
		$F_FILE_INCLUDE,
		$F_CODE,
		$F_HTTP_HEADER,
        $F_POP
) ;

$F_SINK_SERVER = array_merge(
    $F_LDAP,
    $F_XPATH,
    $F_DATABASE,
    $F_EXEC,
    $F_FILE_AFFECT,
    $F_FILE_READ,
    $F_FILE_INCLUDE,
    $F_CODE,
    $F_POP
) ;

$F_SINK_CLIENT = array_merge(
    $F_XSS,
    $F_HTTP_HEADER
) ;

$F_SINK_ARRAY = array(
		$F_XSS,
		$F_LDAP,
		$F_XPATH,
		$F_DATABASE,
		$F_EXEC,
		$F_FILE_AFFECT,
		$F_FILE_READ,
		$F_FILE_INCLUDE,
		$F_CODE,
		$F_HTTP_HEADER,
        $F_POP
);

?>	