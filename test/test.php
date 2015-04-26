<?php

foreach ($_GET['id'] as $key => $value){
	$sql = $value ;	
}

mysql_query($sql) ;

?>