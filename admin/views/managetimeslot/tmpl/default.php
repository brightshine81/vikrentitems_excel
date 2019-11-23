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
if (strlen($wsel) > 0) {
	$wfromh = "<select name=\"fromh\">\n";
	for ($i = 0; $i < 24; $i++) {
		$sayv = $i < 10 ? '0'.$i : $i;
		$wfromh .= "<option value=\"".$i."\"".(count($row) && $i == $row['fromh'] ? " selected=\"selected\"" : "").">".$sayv."</option>\n";
	}
	$wfromh .= "</select>\n";
	$wfromm = "<select name=\"fromm\">\n";
	for ($i = 0; $i < 60; $i+=15) {
		$sayv = $i < 10 ? '0'.$i : $i;
		$wfromm .= "<option value=\"".$i."\"".(count($row) && $i == $row['fromm'] ? " selected=\"selected\"" : "").">".$sayv."</option>\n";
	}
	$wfromm .= "</select>\n";
	$wtoh = "<select name=\"toh\">\n";
	for ($i = 0; $i < 24; $i++) {
		$sayv = $i < 10 ? '0'.$i : $i;
		$wtoh .= "<option value=\"".$i."\"".(count($row) && $i == $row['toh'] ? " selected=\"selected\"" : "").">".$sayv."</option>\n";
	}
	$wtoh .= "</select>\n";
	$wtom = "<select name=\"tom\">\n";
	for ($i = 0; $i < 60; $i+=15) {
		$sayv = $i < 10 ? '0'.$i : $i;
		$wtom .= "<option value=\"".$i."\"".(count($row) && $i == $row['tom'] ? " selected=\"selected\"" : "").">".$sayv."</option>\n";
	}
	$wtom .= "</select>\n";
	?>
	<form name="adminForm" id="adminForm" action="index.php" method="post">
		<table class="admintable table">
			<tr><td class="vri-config-param-cell" width="200"> <b><?php echo JText::_('VRITIMESLOTNAME'); ?></b> </td><td><input type="text" name="tname" value="<?php echo count($row) ? htmlspecialchars($row['tname']) : ''; ?>" size="30"/></td></tr>
			<tr><td class="vri-config-param-cell" width="200"> <b><?php echo JText::_('VRINEWTIMESLOTFROM'); ?></b> </td><td><?php echo $wfromh.' : '.$wfromm; ?> &nbsp;<?php echo $vri_app->createPopover(array('title' => JText::_('VRITIMESLOTSTITLETXT'), 'content' => JText::_('VRITIMESLOTSHELP'))); ?></td></tr>
			<tr><td class="vri-config-param-cell" width="200"> <b><?php echo JText::_('VRINEWTIMESLOTTO'); ?></b> </td><td><?php echo $wtoh.' : '.$wtom; ?></td></tr>
			<tr><td class="vri-config-param-cell" width="200"> <b><?php echo JText::_('VRITIMESLOTDAYS'); ?></b> </td><td><input type="number" min="0" name="days" value="<?php echo count($row) ? $row['days'] : '0'; ?>" /></td></tr>
			<tr><td class="vri-config-param-cell" width="200" style="vertical-align: top;"> <b><?php echo JText::_('VRINEWTIMESLOTITEMS'); ?></b> </td><td><?php echo $wsel; ?></td></tr>
			<tr><td class="vri-config-param-cell" width="200"> <b><?php echo JText::_('VRINEWTIMESLOTGLOBAL'); ?></b> </td><td><input type="checkbox" name="global" value="1"<?php echo (count($row) && $row['global'] == 1 ? " checked=\"checked\"" : ""); ?>/></td></tr>
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
