<?php
/**
 * @package     VikRentItems
 * @subpackage  com_vikrentitems
 * @author      Alessio Gaggii - e4j - Extensionsforjoomla.com
 * @copyright   Copyright (C) 2018 e4j - Extensionsforjoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 * @link        https://e4j.com
 */

defined('_JEXEC') or die('Restricted access');

$rows = $this->rows;
$lim0 = $this->lim0;
$navbut = $this->navbut;

if (empty($rows)) {
	?>
	<p class="err"><?php echo JText::_('VRNOFIELDSFOUND'); ?></p>
	<form action="index.php?option=com_vikrentitems" method="post" name="adminForm" id="adminForm">
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="option" value="com_vikrentitems" />
	</form>
	<?php
} else {
	?>
<form action="index.php?option=com_vikrentitems" method="post" name="adminForm" id="adminForm">
<table cellpadding="4" cellspacing="0" border="0" width="100%" class="table table-striped">
	<thead>
	<tr>
		<th width="20">
			<input type="checkbox" onclick="Joomla.checkAll(this)" value="" name="checkall-toggle">
		</th>
		<th class="title center" width="50" align="center">ID</th>
		<th class="title left" width="200"><?php echo JText::_( 'VRPVIEWCUSTOMFONE' ); ?></th>
		<th class="title left" width="200"><?php echo JText::_( 'VRPVIEWCUSTOMFTWO' ); ?></th>
		<th class="title center" width="100" align="center"><?php echo JText::_( 'VRPVIEWCUSTOMFTHREE' ); ?></th>
		<th class="title center" width="100" align="center"><?php echo JText::_( 'VRPVIEWCUSTOMFFOUR' ); ?></th>
		<th class="title center" width="100" align="center"><?php echo JText::_( 'VRPVIEWCUSTOMFFIVE' ); ?></th>
	</tr>
	</thead>
	<?php

	$k = 0;
	$i = 0;
	for ($i = 0, $n = count($rows); $i < $n; $i++) {
		$row = $rows[$i];
		?>
		<tr class="row<?php echo $k; ?>">
			<td><input type="checkbox" id="cb<?php echo $i;?>" name="cid[]" value="<?php echo $row['id']; ?>" onclick="Joomla.isChecked(this.checked);"></td>
			<td class="center"><?php echo $row['id']; ?></td>
			<td><a href="index.php?option=com_vikrentitems&amp;task=editcustomf&amp;cid[]=<?php echo $row['id']; ?>"><?php echo JText::_($row['name']); ?></a></td>
			<td><?php echo ucwords($row['type']).($row['isnominative'] == 1 ? ' <span class="badge">'.JText::_('VRIISNOMINATIVE').'</span>' : '').($row['isphone'] == 1 ? ' <span class="badge">'.JText::_('VRIISPHONENUMBER').'</span>' : '').(!empty($row['flag']) ? ' <span class="badge">'.JText::_('VRIIS'.strtoupper($row['flag'])).'</span>' : ''); ?></td>
			<td class="center"><?php echo intval($row['required']) == 1 ? "<i class=\"fa fa-check vri-icn-img\" style=\"color: #099909;\"></i>" : "<i class=\"fa fa-times-circle vri-icn-img\" style=\"color: #ff0000;\"></i>"; ?></td>
			<td class="center"><a href="index.php?option=com_vikrentitems&amp;task=sortfield&amp;cid[]=<?php echo $row['id']; ?>&amp;mode=up"><i class="fa fa-arrow-up vri-icn-img"></i></a> <a href="index.php?option=com_vikrentitems&amp;task=sortfield&amp;cid[]=<?php echo $row['id']; ?>&amp;mode=down"><i class="fa fa-arrow-down vri-icn-img"></i></a></td>
			<td class="center"><?php echo intval($row['isemail']) == 1 ? "<i class=\"fa fa-check vri-icn-img\" style=\"color: #099909;\"></i>" : "<i class=\"fa fa-times-circle vri-icn-img\" style=\"color: #ff0000;\"></i>"; ?></td>
		</tr>
		<?php
		$k = 1 - $k;
	}
	?>
	
	</table>
	<input type="hidden" name="option" value="com_vikrentitems" />
	<input type="hidden" name="task" value="customf" />
	<input type="hidden" name="boxchecked" value="0" />
	<?php echo JHTML::_( 'form.token' ); ?>
	<?php echo $navbut; ?>
</form>
<?php
}
