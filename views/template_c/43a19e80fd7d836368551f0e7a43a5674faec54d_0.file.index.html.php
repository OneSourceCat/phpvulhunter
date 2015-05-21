<?php /* Smarty version 3.1.23, created on 2015-05-21 05:56:46
         compiled from "views/template/index.html" */ ?>
<?php
/*%%SmartyHeaderCode:17978555d577eb51b07_06498628%%*/
if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '43a19e80fd7d836368551f0e7a43a5674faec54d' => 
    array (
      0 => 'views/template/index.html',
      1 => 1432180450,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '17978555d577eb51b07_06498628',
  'has_nocache_code' => false,
  'version' => '3.1.23',
  'unifunc' => 'content_555d577f20ac69_15883503',
),false);
/*/%%SmartyHeaderCode%%*/
if ($_valid && !is_callable('content_555d577f20ac69_15883503')) {
function content_555d577f20ac69_15883503 ($_smarty_tpl) {
?>
<?php
$_smarty_tpl->properties['nocache_hash'] = '17978555d577eb51b07_06498628';
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