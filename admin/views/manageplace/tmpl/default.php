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
JHTML::_('behavior.calendar');
$difftime = false;
if (count($row) && !empty($row['opentime'])) {
	$difftime = true;
	$parts = explode("-", $row['opentime']);
	$openat = VikRentItems::getHoursMinutes($parts[0]);
	$closeat = VikRentItems::getHoursMinutes($parts[1]);
}
$hours = "<option value=\"\"> </option>\n";
for ($i=0; $i <= 23; $i++) {
	$in = $i < 10 ? "0".$i : $i;
	$stat = ($difftime == true && (int)$openat[0] == $i ? " selected=\"selected\"" : "");
	$hours.="<option value=\"".$i."\"".$stat.">".$in."</option>\n";
}
$minutes = "<option value=\"\"> </option>\n";
for ($i=0; $i <= 59; $i++) {
	$in = $i < 10 ? "0".$i : $i;
	$stat = ($difftime == true && (int)$openat[1] == $i ? " selected=\"selected\"" : "");
	$minutes.="<option value=\"".$i."\"".$stat.">".$in."</option>\n";
}
$hoursto = "<option value=\"\"> </option>\n";
for ($i=0; $i <= 23; $i++) {
	$in = $i < 10 ? "0".$i : $i;
	$stat = ($difftime == true && (int)$closeat[0] == $i ? " selected=\"selected\"" : "");
	$hoursto.="<option value=\"".$i."\"".$stat.">".$in."</option>\n";
}
$minutesto = "<option value=\"\"> </option>\n";
for ($i=0; $i <= 59; $i++) {
	$in = $i < 10 ? "0".$i : $i;
	$stat = ($difftime == true && (int)$closeat[1] == $i ? " selected=\"selected\"" : "");
	$minutesto.="<option value=\"".$i."\"".$stat.">".$in."</option>\n";
}
$dbo = JFactory::getDBO();
$wiva = "<select name=\"praliq\">\n";
$wiva .= "<option value=\"\"> ------ </option>\n";
$q = "SELECT * FROM `#__vikrentitems_iva`;";
$dbo->setQuery($q);
$dbo->execute();
if ($dbo->getNumRows() > 0) {
	$ivas = $dbo->loadAssocList();
	foreach ($ivas as $iv) {
		$wiva .= "<option value=\"".$iv['id']."\"".(count($row) && $row['idiva'] == $iv['id'] ? " selected=\"selected\"" : "").">".(empty($iv['name']) ? $iv['aliq']."%" : $iv['name']."-".$iv['aliq']."%")."</option>\n";
	}
}
$wiva .= "</select>\n";
?>
<script type="text/javascript">
function vriAddClosingDate() {
	var closingdadd = document.getElementById('insertclosingdate').value;
	if (closingdadd.length > 0) {
		document.getElementById('closingdays').value += closingdadd + ',';
		document.getElementById('insertclosingdate').value = '';
	}
}
</script>

<form name="adminForm" id="adminForm" action="index.php" method="post">
	<table class="admintable table">
		<tr><td class="vri-config-param-cell" width="200"> <b><?php echo JText::_('VREDITPLACEONE'); ?></b> </td><td><input type="text" name="placename" value="<?php echo count($row) ? htmlspecialchars($row['name']) : ''; ?>" size="40"/></td></tr>
		<tr><td class="vri-config-param-cell" width="150"> <b><?php echo JText::_('VRIPLACELAT'); ?></b> </td><td><input type="text" name="lat" value="<?php echo count($row) ? $row['lat'] : ''; ?>" size="30"/></td></tr>
		<tr><td class="vri-config-param-cell" width="150"> <b><?php echo JText::_('VRIPLACELNG'); ?></b> </td><td><input type="text" name="lng" value="<?php echo count($row) ? $row['lng'] : ''; ?>" size="30"/></td></tr>
		<tr><td class="vri-config-param-cell" width="150"> <b><?php echo JText::_('VRIPLACEOVERRIDETAX'); ?>:</b> <?php echo $vri_app->createPopover(array('title' => JText::_('VRIPLACEOVERRIDETAX'), 'content' => JText::_('VRIPLACEOVERRIDETAXTXT'))); ?></td><td><?php echo $wiva; ?></td></tr>
		<tr><td class="vri-config-param-cell" width="150" valign="top"> <b><?php echo JText::_('VRIPLACEOPENTIME'); ?>:</b> <?php echo $vri_app->createPopover(array('title' => JText::_('VRIPLACEOPENTIME'), 'content' => JText::_('VRIPLACEOPENTIMETXT'))); ?></td><td><p><?php echo JText::_('VRIPLACEOPENTIMEFROM'); ?>: <select name="opentimefh"><?php echo $hours; ?></select> : <select name="opentimefm"><?php echo $minutes; ?></select></p><p><?php echo JText::_('VRIPLACEOPENTIMETO'); ?>: <select name="opentimeth"><?php echo $hoursto; ?></select> : <select name="opentimetm"><?php echo $minutesto; ?></select></p></td></tr>
		<tr><td class="vri-config-param-cell" width="150"> <b><?php echo JText::_('VRIPLACEDESCR'); ?></b> </td><td><?php echo $editor->display("descr", (count($row) ? $row['descr'] : ''), 400, 200, 70, 20); ?></td></tr>
		<tr><td class="vri-config-param-cell" width="150" valign="top"> <b><?php echo JText::_('VRNEWPLACECLOSINGDAYS'); ?>:</b> <?php echo $vri_app->createPopover(array('title' => JText::_('VRNEWPLACECLOSINGDAYS'), 'content' => JText::_('VRNEWPLACECLOSINGDAYSHELP'))); ?></td><td><?php echo JHTML::_('calendar', '', 'insertclosingdate', 'insertclosingdate', '%Y-%m-%d', array('class'=>'', 'size'=>'10',  'maxlength'=>'19', 'todayBtn' => 'true')); ?> <span class="vrispdateadd" onclick="javascript: vriAddClosingDate();"><?php echo JText::_('VRNEWPLACECLOSINGDAYSADD'); ?></span><br/><textarea name="closingdays" id="closingdays" rows="5" cols="44"><?php echo count($row) ? $row['closingdays'] : ''; ?></textarea></td></tr>
	</table>
	<input type="hidden" name="task" value="">
<?php
if (count($row)) {
?>
	<input type="hidden" name="whereup" value="<?php echo $row['id']; ?>">
<?php
}
?>
	<input type="hidden" name="option" value="com_vikrentitems">
</form>
