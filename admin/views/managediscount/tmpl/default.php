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

$vri_app = VikRentItems::getVriApplication();
$currencysymb = VikRentItems::getCurrencySymb(true);
if (strlen($wsel) > 0) {
	?>
	<form name="adminForm" id="adminForm" action="index.php" method="post">
		<table class="admintable table">
			<tr><td class="vri-config-param-cell" width="200"> <b><?php echo JText::_('VRIDISCNAME'); ?></b> </td><td><input type="text" name="discname" value="<?php echo count($row) ? htmlspecialchars($row['discname']) : ''; ?>" size="30"/></td></tr>
			<tr><td class="vri-config-param-cell" width="200"> <b><?php echo JText::_('VRINEWDISCQUANT'); ?></b> </td><td><input type="number" min="1" name="quantity" value="<?php echo count($row) ? $row['quantity'] : '2'; ?>"/></td></tr>
			<tr><td class="vri-config-param-cell" width="200"> <b><?php echo JText::_('VRNEWDISCVALUE'); ?></b> </td><td><input type="number" step="any" name="diffcost" value="<?php echo count($row) ? $row['diffcost'] : ''; ?>"/> <select name="val_pcent" id="val_pcent"><option value="2"<?php echo count($row) && $row['val_pcent'] == 2 ? " selected=\"selected\"" : ""; ?>>%</option><option value="1"<?php echo count($row) && $row['val_pcent'] == 1 ? " selected=\"selected\"" : ""; ?>><?php echo $currencysymb; ?></option></select> &nbsp;<?php echo $vri_app->createPopover(array('title' => JText::_('VRIDISCOUNTSTITLETXT'), 'content' => JText::_('VRIDISCOUNTSHELP'))); ?></td></tr>
			<tr><td class="vri-config-param-cell" width="200"> <b><?php echo JText::_('VRIDISCIFGREATQUANT'); ?></b> </td><td><input type="checkbox" name="ifmorequant" value="1"<?php echo count($row) && $row['ifmorequant'] == 1 ? " checked=\"checked\"" : ""; ?>/></td></tr>
			<tr><td class="vri-config-param-cell" width="200" style="vertical-align: top;"> <b><?php echo JText::_('VRINEWDISCITEMS'); ?></b> </td><td><?php echo $wsel; ?></td></tr>
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
