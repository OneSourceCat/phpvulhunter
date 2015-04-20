<?php

function goods_del($s,$id)
{
	$a = $id;
	del2($a,$b);
	$d = mysql_query($s);
	
}

function del2($where,$hi)
{
	$where = ' WHERE '.$where;
	$sql = 'DELETE FROM '.$where;
	$sql = jinghua($sql1,$hi);
	$sql = $sql;
	return mysql_query($sql);
}

function jinghua($str,$str1){
    $str1 = addslashes($str1);
    return addslashes($str);
}
$d = 123;
goods_del($id) ;
?>