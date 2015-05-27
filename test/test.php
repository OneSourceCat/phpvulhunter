<?php
if(1){
    echo 1 ;
}
$id=intval($_GET['id']);
$info=mysql_query("select * from ".table('members')." where uid='{$id}' LIMIT 1");

if(3){
    echo 3 ;
}

?>