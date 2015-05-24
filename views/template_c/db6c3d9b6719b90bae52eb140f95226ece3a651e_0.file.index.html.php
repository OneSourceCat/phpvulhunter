<?php /* Smarty version 3.1.23, created on 2015-05-20 15:28:32
         compiled from "views/template/index.html" */ ?>
<?php
/*%%SmartyHeaderCode:32738555ca8209080a5_21203164%%*/
if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    'db6c3d9b6719b90bae52eb140f95226ece3a651e' => 
    array (
      0 => 'views/template/index.html',
      1 => 1432128628,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '32738555ca8209080a5_21203164',
  'has_nocache_code' => false,
  'version' => '3.1.23',
  'unifunc' => 'content_555ca820b88fe9_58468525',
),false);
/*/%%SmartyHeaderCode%%*/
if ($_valid && !is_callable('content_555ca820b88fe9_58468525')) {
function content_555ca820b88fe9_58468525 ($_smarty_tpl) {
?>
<?php
$_smarty_tpl->properties['nocache_hash'] = '32738555ca8209080a5_21203164';
?>
<!doctype html>
<html lang="en">
<?php echo $_smarty_tpl->getSubTemplate ('header.html', $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), 0);
?>

<body>
	<?php echo $_smarty_tpl->getSubTemplate ('navigation.html', $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), 0);
?>


	<div class="content">
		<div id="err_cont">
			<a href="javascript:;">OK</a>
		</div>

		<div class="content-panel">
		</div>
	</div>
	<div class="waiting">
		<div class="loading"></div>
		<div class="load-font">
			<div class="wait-font">
				Scanning
			</div>
			<div class="jumppoint">
				<span id="p1">.</span><span id="p2">.</span><span id="p3">.</span>
			</div>
		</div>
	</div>
	<?php echo $_smarty_tpl->getSubTemplate ('footer.html', $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), 0);
?>

</body>
</html><?php }
}
?>