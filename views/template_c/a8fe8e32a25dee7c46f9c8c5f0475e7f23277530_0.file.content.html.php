<?php /* Smarty version 3.1.23, created on 2015-05-20 15:28:44
         compiled from "views/template/content.html" */ ?>
<?php
/*%%SmartyHeaderCode:2729555ca82c758594_52755062%%*/
if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    'a8fe8e32a25dee7c46f9c8c5f0475e7f23277530' => 
    array (
      0 => 'views/template/content.html',
      1 => 1432119098,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '2729555ca82c758594_52755062',
  'variables' => 
  array (
    'results' => 0,
    'n' => 0,
  ),
  'has_nocache_code' => false,
  'version' => '3.1.23',
  'unifunc' => 'content_555ca82c8e7f08_02341523',
),false);
/*/%%SmartyHeaderCode%%*/
if ($_valid && !is_callable('content_555ca82c8e7f08_02341523')) {
function content_555ca82c8e7f08_02341523 ($_smarty_tpl) {
?>
<?php
$_smarty_tpl->properties['nocache_hash'] = '2729555ca82c758594_52755062';
?>

	
		<ul>
		<?php if (isset($_smarty_tpl->tpl_vars['results']->value)) {?>
		<?php
$_from = $_smarty_tpl->tpl_vars['results']->value;
if (!is_array($_from) && !is_object($_from)) {
settype($_from, 'array');
}
$_smarty_tpl->tpl_vars['n'] = new Smarty_Variable;
$_smarty_tpl->tpl_vars['n']->_loop = false;
foreach ($_from as $_smarty_tpl->tpl_vars['n']->value) {
$_smarty_tpl->tpl_vars['n']->_loop = true;
$foreachItemSav = $_smarty_tpl->tpl_vars['n'];
?>
			<li>
				<h2>Find a vulnerability !</h2>
				<div>file path : <?php echo $_smarty_tpl->tpl_vars['n']->value['path'];?>
</div>
				<div>vlun type : <?php echo $_smarty_tpl->tpl_vars['n']->value['type'];?>
</div>
				<div>node : <?php echo $_smarty_tpl->tpl_vars['n']->value['node'];?>
</div>
				<div>var : <?php echo $_smarty_tpl->tpl_vars['n']->value['var'];?>
</div>				
			</li>
		<?php
$_smarty_tpl->tpl_vars['n'] = $foreachItemSav;
}
?>
		<?php }?>
		</ul>

<?php }
}
?>