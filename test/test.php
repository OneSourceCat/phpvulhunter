<?php

$content = $where . $cookie ;
$id = $content;
if($id){
	echo $id ;
}else if($id>0){
	echo false;
}else{
	if(1){
		echo "xxxx" ;
	}
}

$sql = "xxx". $id ;
$sql = addslashes($sql) ;
mysql_query($sql) ;

?>