<?php
	
	require('Smarty.class.php');

	class Smarty_setup extends Smarty{
		function __construct(){
			// 五项基本配置
		    $this->left_delimiter = "<!--{"; // 左定界符
		    $this->right_delimiter = "}-->"; // 右定界符
		    $this->template_dir = "views/template"; // 指定html模板地址
		    $this->compile_dir = "views/template_c"; // 指定模板编译生成文件
		    $this->cache_dir = "views/ache"; // 缓存
		    // 以下是开启缓存的两个配置
		    $this->caching = false; // 开启缓存
		    //$this->cache_lifetime = 120; // 缓存保存时间
		}
	}

?>