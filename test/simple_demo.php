<?php

function goods_del($id)
{
	$a = $id;
	del2($a);


}


function del2($where)
{
	$where = ' WHERE '.$where;
	$sql   = 'DELETE FROM '.$where;
	return mysql_query($sql);
}


$id = $_POST['id'] .$a ."b" ;
goods_del($id) ;

?>












