<?php

function goods_del($s,$id)
{
	$a = $id;
	del2($a);
	
}



function del2($where,$hi)
{
	
	$where = ' WHERE '.$where;
	$sql   = 'DELETE FROM '.$where;
	return mysql_query($sql);
}



$d = 123;
goods_del($id) ;



?>












