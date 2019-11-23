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
$wselitems = $this->wselitems;

JHTML::_('behavior.calendar');
$vri_app = VikRentItems::getVriApplication();
$currencysymb = VikRentItems::getCurrencySymb(true);
$df = VikRentItems::getDateFormat(true);
$fromdate = "";
$todate = "";
if (count($row) && strlen($row['datevalid']) > 0) {
	$dateparts = explode("-", $row['datevalid']);
	if ($df == "%d/%m/%Y") {
		$udf = 'd/m/Y';
	} elseif ($df == "%m/%d/%Y") {
		$udf = 'm/d/Y';
	} else {
		$udf = 'Y/m/d';
	}
	$fromdate = date($udf, $dateparts[0]);
	$todate = date($udf, $dateparts[1]);
}
?>
<script type="text/javascript">
function setVehiclesList() {
	if (document.adminForm.allvehicles.checked == true) {
		document.getElementById('vrivlist').style.display='none';
	} else {
		document.getElementById('vrivlist').style.display='block';
	}
	return true;
}
</script>
<form name="adminForm" action="index.php" method="post" id="adminForm">
	<table class="admintable table">
		<tr><td class="vri-config-param-cell" width="200"> <b><?php echo JText::_('VRINEWCOUPONONE'); ?></b> </td><td><input type="text" name="code" value="<?php echo count($row) ? htmlspecialchars($row['code']) : ''; ?>" size="30"/></td></tr>
		<tr><td class="vri-config-param-cell" width="200"> <b><?php echo JText::_('VRINEWCOUPONTWO'); ?></b> </td><td><select name="type"><option value="1"<?php echo (count($row) && $row['type'] == 1 ? " selected=\"selected\"" : ""); ?>><?php echo JText::_('VRICOUPONTYPEPERMANENT'); ?></option><option value="2"<?php echo (count($row) && $row['type'] == 2 ? " selected=\"selected\"" : ""); ?>><?php echo JText::_('VRICOUPONTYPEGIFT'); ?></option></select></td></tr>
		<tr><td class="vri-config-param-cell" width="200"> <b><?php echo JText::_('VRINEWCOUPONTHREE'); ?></b> </td><td><select name="percentot"><option value="1"<?php echo (count($row) && $row['percentot'] == 1 ? " selected=\"selected\"" : ""); ?>>%</option><option value="2"<?php echo (count($row) && $row['percentot'] == 2 ? " selected=\"selected\"" : ""); ?>><?php echo $currencysymb; ?></option></select></td></tr>
		<tr><td class="vri-config-param-cell" width="200"> <b><?php echo JText::_('VRINEWCOUPONFOUR'); ?></b> </td><td><input type="number" name="value" step="any" value="<?php echo count($row) ? $row['value'] : ''; ?>"/></td></tr>
		<tr><td class="vri-config-param-cell" width="200" valign="top"> <b><?php echo JText::_('VRINEWCOUPONFIVE'); ?></b> </td><td><input type="checkbox" name="allvehicles" value="1"<?php echo (!count($row) || (count($row) && $row['allvehicles'] == 1) ? " checked=\"checked\"" : ""); ?> onclick="javascript: setVehiclesList();"/> <?php echo JText::_('VRINEWCOUPONEIGHT'); ?><span id="vrivlist" style="display: <?php echo (!count($row) || (count($row) && $row['allvehicles'] == 1) ? "none" : "block"); ?>;"><br/><?php echo $wselitems; ?></span></td></tr>
		<tr><td class="vri-config-param-cell" width="200"> <b><?php echo JText::_('VRINEWCOUPONSIX'); ?></b> <?php echo $vri_app->createPopover(array('title' => JText::_('VRINEWCOUPONSIX'), 'content' => JText::_('VRINEWCOUPONNINE'))); ?></td><td><?php echo JHTML::_('calendar', '', 'from', 'from', $df, array('class'=>'', 'size'=>'10',  'maxlength'=>'19', 'todayBtn' => 'true')); ?> - <?php echo JHTML::_('calendar', '', 'to', 'to', $df, array('class'=>'', 'size'=>'10',  'maxlength'=>'19', 'todayBtn' => 'true')); ?></td></tr>
		<tr><td class="vri-config-param-cell" width="200"> <b><?php echo JText::_('VRINEWCOUPONSEVEN'); ?></b> </td><td><input type="number" step="any" name="mintotord" value="<?php echo count($row) ? $row['mintotord'] : ''; ?>" size="4"/></td></tr>
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
if (strlen($fromdate) > 0 && strlen($todate) > 0) {
?>
<script type="text/javascript">
jQuery(document).ready(function() {
	jQuery('#from').val('<?php echo $fromdate; ?>').attr('data-alt-value', '<?php echo $fromdate; ?>');
	jQuery('#to').val('<?php echo $todate; ?>').attr('data-alt-value', '<?php echo $todate; ?>');
});
</script>
<?php
}
