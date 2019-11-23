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
	<p class="warn"><?php echo JText::_('VRNOSTATSFOUND'); ?></p>
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
	<th class="title left" width="150"><?php echo JText::_( 'VRPVIEWSTATSONE' ); ?></th>
	<th class="title left" width="150"><?php echo JText::_( 'VRPVIEWSTATSTWO' ); ?></th>
	<th class="title left" width="150"><?php echo JText::_( 'VRPVIEWSTATSTHREE' ); ?></th>
	<th class="title left" width="150"><?php echo JText::_( 'VRPVIEWSTATSFOUR' ); ?></th>
	<th class="title left" width="150"><?php echo JText::_( 'VRPVIEWSTATSFIVE' ); ?></th>
	<th class="title left" width="150"><?php echo JText::_( 'VRPVIEWSTATSSIX' ); ?></th>
	<th class="title left" width="150"><?php echo JText::_( 'VRPVIEWSTATSSEVEN' ); ?></th>
</tr>
</thead>
<?php
$nowdf = VikRentItems::getDateFormat(true);
if ($nowdf == "%d/%m/%Y") {
	$df = 'd/m/Y';
} elseif ($nowdf == "%m/%d/%Y") {
	$df = 'm/d/Y';
} else {
	$df = 'Y/m/d';
}
$nowtf = VikRentItems::getTimeFormat(true);
$kk = 0;
$i = 0;
for ($i = 0, $n = count($rows); $i < $n; $i++) {
	$row = $rows[$i];
	if (!empty($row['place'])) {
		$exp = explode(";", $row['place']);
		$place = VikRentItems::getPlaceName($exp[0]).(!empty($exp[1]) && $exp[0]!=$exp[1] ? " - ".VikRentItems::getPlaceName($exp[1]) : "");
	} else {
		$place = "";
	}
	$cat = JText::_('VRANYTHING');
	if (!empty($row['cat'])) {
		$cat = ($row['cat'] == "all" ? JText::_('VRANYTHING') : VikRentItems::getCategoryName($row['cat']));
	}
	?>
	<tr class="row<?php echo $kk; ?>">
		<td><input type="checkbox" id="cb<?php echo $i;?>" name="cid[]" value="<?php echo $row['id']; ?>" onclick="Joomla.isChecked(this.checked);"></td>
		<td><?php echo date($df.' '.$nowtf, $row['ts']); ?></td>
		<td><?php echo $row['ip']; ?></td>
		<td><?php echo date($df.' '.$nowtf, $row['ritiro']); ?></td>
		<td><?php echo date($df.' '.$nowtf, $row['consegna']); ?></td>
		<td><?php echo $place; ?></td>
		<td><?php echo $cat; ?></td>
		<td><?php echo intval($row['res']); ?></td>
	</tr>
	<?php
	$kk = 1 - $kk;
}
?>

</table>
<input type="hidden" name="option" value="com_vikrentitems" />
<input type="hidden" name="task" value="stats" />
<input type="hidden" name="boxchecked" value="0" />
<?php echo JHTML::_( 'form.token' ); ?>
<?php echo $navbut; ?>
</form>
<?php
}
