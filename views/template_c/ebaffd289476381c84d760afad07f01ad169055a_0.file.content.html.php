<?php /* Smarty version 3.1.23, created on 2015-05-21 06:45:03
         compiled from "views/template/content.html" */ ?>
<?php
/*%%SmartyHeaderCode:3078555d62cff28f17_05937837%%*/
if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    'ebaffd289476381c84d760afad07f01ad169055a' => 
    array (
      0 => 'views/template/content.html',
      1 => 1432182970,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '3078555d62cff28f17_05937837',
  'variables' => 
  array (
    'results' => 0,
    'n' => 0,
  ),
  'has_nocache_code' => false,
  'version' => '3.1.23',
  'unifunc' => 'content_555d62d008eac0_41807088',
),false);
/*/%%SmartyHeaderCode%%*/
if ($_valid && !is_callable('content_555d62d008eac0_41807088')) {
function content_555d62d008eac0_41807088 ($_smarty_tpl) {
?>
<?php
$_smarty_tpl->properties['nocache_hash'] = '3078555d62cff28f17_05937837';
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