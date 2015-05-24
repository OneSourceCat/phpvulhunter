<?php /* Smarty version 3.1.23, created on 2015-05-23 05:29:08
         compiled from "views/template/index.html" */ ?>
<?php
/*%%SmartyHeaderCode:116865560102431b930_16439713%%*/
if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    'b56f577d13f6fa8856ee9b4e579f7ff91c6d44be' => 
    array (
      0 => 'views/template/index.html',
      1 => 1432358946,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '116865560102431b930_16439713',
  'has_nocache_code' => false,
  'version' => '3.1.23',
  'unifunc' => 'content_55601024350929_99554591',
),false);
/*/%%SmartyHeaderCode%%*/
if ($_valid && !is_callable('content_55601024350929_99554591')) {
function content_55601024350929_99554591 ($_smarty_tpl) {
?>
<?php
$_smarty_tpl->properties['nocache_hash'] = '116865560102431b930_16439713';
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
			<?php echo $_smarty_tpl->getSubTemplate ('content.html', $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), 0);
?>

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