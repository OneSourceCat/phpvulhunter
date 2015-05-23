<?php 
$id = $_GET['id'] ;
if($id){
    echo $id ;
}else{
    echo false;
}

$sql = "xxx". $id ;
mysql_query($sql) ;

?>