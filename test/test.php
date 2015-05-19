<?php
	$cat_name=$_POST['cat_name'];
	$parent_id=intval($_POST['parent_id']);
	if (empty($cat_name))
	{
		sys_message('分类名字不能为空',$referer_url);
	}
	$cat_desc=$_POST['cat_desc'];
	$list_sort=$_POST['list_sort'];
	$url_type=intval($_POST['url_type']);
	//对自定义url处理
	if ($url_type==2)
	{
		$url_type=$_POST['url'];
	}

	$sql='INSERT INTO '.table('category').
	"  (`cat_id` ,`cat_name` ,`cat_desc` ,`parent_id` ,`listorder`,`url_type` ) VALUES (".
	"Null, '".$cat_name."', '".$cat_desc."', '".$parent_id."', '".$list_sort."','".$url_type."')";
	if (mysql_query($sql))
	{
		sys_message('添加分类成功','admin.php?act=cat_list');
	}
	else
	{
		sys_message('添加分类失败，请重新返回添加','admin.php?act=add_cat');
	}
?>