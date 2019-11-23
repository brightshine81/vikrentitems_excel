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

$oids = $this->oids;
$locations = $this->locations;

JHTML::_('behavior.calendar');
$nowdf = VikRentItems::getDateFormat(true);
if ($nowdf=="%d/%m/%Y") {
	$df = 'd/m/Y';
} elseif ($nowdf=="%m/%d/%Y") {
	$df = 'm/d/Y';
} else {
	$df = 'Y/m/d';
}
$optlocations = '';
if (is_array($locations) && count($locations) > 0) {
	foreach ($locations as $loc) {
		$optlocations .= '<option value="'.$loc['id'].'">'.$loc['name'].'</option>';
	}
}
?>
<script type="text/javascript">
function vriExportSetType(val) {
	if (val == 'csv') {
		document.getElementById('vriexpdateftr').style.display = '';
	} else {
		jQuery('#vriexpdateftr').fadeOut();
	}
}

</script>
<form name="adminForm" id="adminForm" action="index.php" method="post">
<table class="admintable table">
<?php
if (!(count($oids) > 0)) {
?>
<tr><td class="vri-config-param-cell" width="170"> <b><?php echo JText::_('VREXPORTDATETYPE'); ?></b> </td><td><select name="datetype"><option value="ritiro"><?php echo JText::_('VREXPORTDATETYPEPICK'); ?></option><option value="ts"><?php echo JText::_('VREXPORTDATETYPETS'); ?></option></select></td></tr>
<tr><td class="vri-config-param-cell" width="170"> <b><?php echo JText::_('VREXPORTONE'); ?></b> </td><td><?php echo JHTML::_('calendar', '', 'from', 'from', $nowdf, array('class'=>'', 'size'=>'10',  'maxlength'=>'19', 'todayBtn' => 'true')); ?></td></tr>
<tr><td class="vri-config-param-cell" width="170"> <b><?php echo JText::_('VREXPORTTWO'); ?></b> </td><td><?php echo JHTML::_('calendar', '', 'to', 'to', $nowdf, array('class'=>'', 'size'=>'10',  'maxlength'=>'19', 'todayBtn' => 'true')); ?></td></tr>
<tr><td class="vri-config-param-cell" width="170"> <b><?php echo JText::_('VREXPORTELEVEN'); ?></b> </td><td><select name="location"><option value="">--------</option><?php echo $optlocations; ?></select></td></tr>
<?php
} else {
	foreach ($oids as $oid) {
		echo '<input type="hidden" name="cid[]" value="'.$oid.'"/>'."\n";
	}
	?>
<tr><td width="170" colspan="2"> <b><?php echo JText::sprintf('VREXPORTNUMORDS', count($oids)); ?></b></td></tr>
	<?php
}
?>
<tr><td class="vri-config-param-cell" width="170"> <b><?php echo JText::_('VREXPORTTHREE'); ?></b> </td><td><select name="type" id="vritype" onchange="vriExportSetType(this.value);"><option value="csv"><?php echo JText::_('VREXPORTFOUR'); ?></option><option value="ics"><?php echo JText::_('VREXPORTFIVE'); ?></option><option value="xlsx" selected="selected"><?php echo JText::_('VREXPORTEXCEL'); ?></option></select></td></tr>
<tr id="vriexpdateftr" style="display:none;"><td class="vri-config-param-cell" width="170"> <b><?php echo JText::_('VREXPORTTEN'); ?></b> </td><td><select name="dateformat"><option value="Y/m/d"<?php echo $df == 'Y/m/d' ? " selected=\"selected\"" : ""; ?>>Y/m/d</option><option value="m/d/Y"<?php echo $df == 'm/d/Y' ? " selected=\"selected\"" : ""; ?>>m/d/Y</option><option value="d/m/Y"<?php echo $df == 'd/m/Y' ? " selected=\"selected\"" : ""; ?>>d/m/Y</option><option value="Y-m-d">Y-m-d</option><option value="m-d-Y">m-d-Y</option><option value="d-m-Y">d-m-Y</option><option value="ts">Unix Timestamp</option></select></td></tr>
<tr><td class="vri-config-param-cell" width="170"> <b><?php echo JText::_('VREXPORTSIX'); ?></b> </td><td><select name="status"><option value="C"><?php echo JText::_('VREXPORTSEVEN'); ?></option><option value="CP"><?php echo JText::_('VREXPORTEIGHT'); ?></option><option value="CP" selected="selected"><?php echo JText::_('VREXPORTALL'); ?></option></select></td></tr>
<tr><td width="170">&nbsp;</td><td><button type="submit" class="btn"><i class="vriicn-cloud-download"></i> <?php echo JText::_('VREXPORTNINE'); ?></button></td></tr>
</table>
<input type="hidden" name="task" value="doexport">
<input type="hidden" name="option" value="com_vikrentitems" />
</form>
