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
	<p class="warn"><?php echo JText::_('VRNOLOCFEES'); ?></p>
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
		<th class="title left" width="150"><?php echo JText::_( 'VRPVIEWPLOCFEEONE' ); ?></th>
		<th class="title left" width="150"><?php echo JText::_( 'VRPVIEWPLOCFEETWO' ); ?></th>
		<th class="title left" width="100"><?php echo JText::_( 'VRPVIEWPLOCFEETHREE' ); ?></th>
		<th class="title left" width="100"><?php echo JText::_( 'VRPVIEWPLOCFEEFOUR' ); ?></th>
	</tr>
	</thead>
	<?php
	$currencysymb = VikRentItems::getCurrencySymb(true);
	$k = 0;
	$i = 0;
	for ($i = 0, $n = count($rows); $i < $n; $i++) {
		$row = $rows[$i];
		?>
		<tr class="row<?php echo $k; ?>">
			<td><input type="checkbox" id="cb<?php echo $i;?>" name="cid[]" value="<?php echo $row['id']; ?>" onclick="Joomla.isChecked(this.checked);"></td>
			<td><a href="index.php?option=com_vikrentitems&amp;task=editlocfee&amp;cid[]=<?php echo $row['id']; ?>"><?php echo VikRentItems::getPlaceName($row['from']); ?></a></td>
			<td><?php echo VikRentItems::getPlaceName($row['to']); ?></td>
			<td><?php echo $currencysymb.' '.$row['cost']; ?></td>
			<td><?php echo (intval($row['daily']) == 1 ? JText::_('VRYES') : JText::_('VRNO')); ?></td>
		</tr>
		<?php
		$k = 1 - $k;
	}
	?>
	
	</table>
	<input type="hidden" name="option" value="com_vikrentitems" />
	<input type="hidden" name="task" value="locfees" />
	<input type="hidden" name="boxchecked" value="0" />
	<?php echo JHTML::_( 'form.token' ); ?>
	<?php echo $navbut; ?>
</form>
<?php
}
