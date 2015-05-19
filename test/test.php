<?php

$id = trim(urldecode(urlencode($_GET['id']))) ;


$sql = "xxx". $id ;
mysql_query($sql) ;

?>