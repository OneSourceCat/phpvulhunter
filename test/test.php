<?php

$key=trim($_GET['key']);
$key = addslashes($key) ;
if (!empty($key)){
    if (strcasecmp(QISHI_DBCHARSET,"utf8")!=0) $key=iconv("utf-8",QISHI_DBCHARSET,$key);
    $result = mysql_query("select * from ".table('category')." where c_alias='QS_street' AND c_name LIKE '%{$key}%' ");
    while($row = $db->fetch_array($result)){
        if ($listtype=="li"){
        	echo "alert" ;
        }
    }
}

mysql_query($key);
?>