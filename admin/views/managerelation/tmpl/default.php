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

$row = $this->row;
$wsel = $this->wsel;
$wseltwo = $this->wseltwo;

if (strlen($wsel) > 0) {
	?>
	<form name="adminForm" id="adminForm" action="index.php" method="post">
		<table class="admintable table">
			<tr><td class="vri-config-param-cell" width="200"> <b><?php echo JText::_('VRIRELATIONNAME'); ?></b> </td><td><input type="text" name="relname" value="<?php echo count($row) ? htmlspecialchars($row['relname']) : ''; ?>" size="30"/></td></tr>
			<tr>
				<td class="vri-config-param-cell" width="200" style="vertical-align: top !important;"> <b><?php echo JText::_('VRINEWRELATIONSEL'); ?></b> </td>
				<td>
					<div style="float: left; margin-right: 20px; min-height: 170px;"><?php echo $wsel; ?></div>
					<div style="float: left; margin-right: 20px;  min-height: 170px; border-right: 1px dotted #cccccc;">&nbsp;</div>
					<div style="float: left; min-height: 170px;"><?php echo $wseltwo; ?></div>
				</td>
			</tr>
		</table>
		<input type="hidden" name="task" value="">
		<input type="hidden" name="option" value="com_vikrentitems" />
	<?php
	if (count($row)) {
		?>
		<input type="hidden" name="where" value="<?php echo $row['id']; ?>">
		<?php
	}
	?>
	</form>
	<?php
} else {
	?>
	<p class="err"><a href="index.php?option=com_vikrentitems&amp;task=newitem"><?php echo JText::_('VRNOITEMSFOUNDSEASONS'); ?></a></p>
	<form action="index.php?option=com_vikrentitems" method="post" name="adminForm" id="adminForm">
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="option" value="com_vikrentitems" />
	</form>
	<?php
}
