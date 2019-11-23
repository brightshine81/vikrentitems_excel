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

$vri_app = VikRentItems::getVriApplication();
$editor = JEditor::getInstance(JFactory::getApplication()->get('editor'));
$allf = glob(VRI_ADMIN_PATH.DS.'payments'.DS.'*.php');
$psel = "";
if (@count($allf) > 0) {
	$classfiles = array();
	foreach ($allf as $af) {
		$classfiles[] = str_replace(VRI_ADMIN_PATH.DS.'payments'.DS, '', $af);
	}
	sort($classfiles);
	$psel="<select name=\"payment\" onchange=\"vikLoadPaymentParameters(this.value);\">\n<option value=\"\"></option>\n";
	foreach ($classfiles as $cf) {
		$psel.="<option value=\"".$cf."\"".(count($row) && $cf==$row['file'] ? " selected=\"selected\"" : "").">".$cf."</option>\n";
	}
	$psel.="</select>";
}
$currencysymb = VikRentItems::getCurrencySymb(true);
$payparams = count($row) ? VikRentItems::displayPaymentParameters($row['file'], $row['params']) : '';
?>
<script type="text/javascript">
function vikLoadPaymentParameters(pfile) {
	if (pfile.length > 0) {
		jQuery("#vikparameters").html('<?php echo addslashes(JTEXT::_('VIKLOADING')); ?>');
		jQuery.ajax({
			type: "POST",
			url: "index.php?option=com_vikrentitems&task=loadpaymentparams&tmpl=component",
			data: { phpfile: pfile }
		}).done(function(res) {
			jQuery("#vikparameters").html(res);
		});
	} else {
		jQuery("#vikparameters").html('--------');
	}
}
</script>

<form name="adminForm" id="adminForm" action="index.php" method="post">
	<table class="admintable table">
		<tr><td class="vri-config-param-cell" width="170"> <b><?php echo JText::_('VRNEWPAYMENTONE'); ?></b> </td><td><input type="text" name="name" value="<?php echo count($row) ? htmlspecialchars($row['name']) : ''; ?>" size="30"/></td></tr>
		<tr><td class="vri-config-param-cell" width="170"> <b><?php echo JText::_('VRNEWPAYMENTTWO'); ?></b> </td><td><?php echo $psel; ?></td></tr>
		<tr><td class="vri-config-param-cell" width="170" style="vertical-align: top;"> <b><?php echo JText::_('VRIPAYMENTPARAMETERS'); ?></b> </td><td id="vikparameters"><?php echo $payparams; ?></td></tr>
		<tr><td class="vri-config-param-cell" width="170"> <b><?php echo JText::_('VRNEWPAYMENTTHREE'); ?></b> </td><td><select name="published"><option value="1"<?php echo count($row) && intval($row['published']) == 1 ? " selected=\"selected\"" : ""; ?>><?php echo JText::_('VRNEWPAYMENTSIX'); ?></option><option value="0"<?php echo count($row) && intval($row['published']) != 1 ? " selected=\"selected\"" : ""; ?>><?php echo JText::_('VRNEWPAYMENTSEVEN'); ?></option></select></td></tr>
		<tr><td class="vri-config-param-cell" width="170"> <b><?php echo JText::_('VRNEWPAYMENTCHARGEORDISC'); ?></b> </td><td><select name="ch_disc"><option value="1"<?php echo (count($row) && $row['ch_disc'] == 1 ? " selected=\"selected\"" : ""); ?>><?php echo JText::_('VRNEWPAYMENTCHARGEPLUS'); ?></option><option value="2"<?php echo (count($row) && $row['ch_disc'] == 2 ? " selected=\"selected\"" : ""); ?>><?php echo JText::_('VRNEWPAYMENTDISCMINUS'); ?></option></select> <input type="number" step="any" name="charge" value="<?php echo count($row) ? $row['charge'] : ''; ?>" size="5"/> <select name="val_pcent"><option value="1"<?php echo (count($row) && $row['val_pcent'] == 1 ? " selected=\"selected\"" : ""); ?>><?php echo $currencysymb; ?></option><option value="2"<?php echo (count($row) && $row['val_pcent'] == 2 ? " selected=\"selected\"" : ""); ?>>%</option></select></td></tr>
		<tr><td class="vri-config-param-cell" width="170"> <b><?php echo JText::_('VRNEWPAYMENTEIGHT'); ?></b> </td><td><select name="setconfirmed"><option value="1"<?php echo count($row) && intval($row['setconfirmed']) == 1 ? " selected=\"selected\"" : ""; ?>><?php echo JText::_('VRNEWPAYMENTSIX'); ?></option><option value="0"<?php echo (count($row) && intval($row['setconfirmed']) != 1) || !count($row) ? " selected=\"selected\"" : ""; ?>><?php echo JText::_('VRNEWPAYMENTSEVEN'); ?></option></select> &nbsp; <?php echo $vri_app->createPopover(array('title' => JText::_('VRIPAYMENTSHELPCONFIRMTXT'), 'content' => JText::_('VRIPAYMENTSHELPCONFIRM'))); ?></td></tr>
		<tr><td class="vri-config-param-cell" width="170"> <b><?php echo JText::_('VRNEWPAYMENTNINE'); ?></b> </td><td><select name="shownotealw"><option value="1"<?php echo count($row) && intval($row['shownotealw']) == 1 ? " selected=\"selected\"" : ""; ?>><?php echo JText::_('VRNEWPAYMENTSIX'); ?></option><option value="0"<?php echo count($row) && intval($row['shownotealw']) != 1 ? " selected=\"selected\"" : ""; ?>><?php echo JText::_('VRNEWPAYMENTSEVEN'); ?></option></select></td></tr>
		<tr><td class="vri-config-param-cell" width="170" valign="top"> <b><?php echo JText::_('VRNEWPAYMENTFIVE'); ?></b> </td><td><?php echo $editor->display( "note", (count($row) ? $row['note'] : ''), 400, 200, 70, 20 ); ?></td></tr>
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
