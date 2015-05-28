<?php
define('IN_QISHI', true);
require_once(dirname(__FILE__).'/../data/config.php');
require_once(dirname(__FILE__).'/include/admin_common.inc.php');
require_once(ADMIN_ROOT_PATH.'include/admin_article_fun.php');
require_once(ADMIN_ROOT_PATH.'include/upload.php');

$article = get_news($offset, $perpage,$joinsql.$wheresql.$oederbysql);


?>