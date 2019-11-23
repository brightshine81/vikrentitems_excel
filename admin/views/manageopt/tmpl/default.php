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
$dbo = JFactory::getDBO();
$q = "SELECT * FROM `#__vikrentitems_iva`;";
$dbo->setQuery($q);
$dbo->execute();
if ($dbo->getNumRows() > 0) {
	$ivas = $dbo->loadAssocList();
	$wiva = "<select name=\"optaliq\">\n<option value=\"\"></option>\n";
	foreach ($ivas as $iv) {
		$wiva .= "<option value=\"".$iv['id']."\"".(count($row) && $row['idiva'] == $iv['id'] ? " selected=\"selected\"" : "").">".(empty($iv['name']) ? $iv['aliq']."%" : $iv['name']."-".$iv['aliq']."%")."</option>\n";
	}
	$wiva .= "</select>\n";
} else {
	$wiva = "<a href=\"index.php?option=com_vikrentitems&task=viewiva\">".JText::_('VRNOIVAFOUND')."</a>";
}
$currencysymb = VikRentItems::getCurrencySymb(true);
//vikrentitems 1.1
if (count($row) && strlen($row['forceval']) > 0) {
	$forceparts = explode("-", $row['forceval']);
	$forcedq = $forceparts[0];
	$forcedqperday = intval($forceparts[1]) == 1 ? true : false;
} else {
	$forcedq = "1";
	$forcedqperday = false;
}
//
$usespecifications = false;
$oldspecifications = '';
if (count($row) && !empty($row['specifications'])) {
	$usespecifications = true;
	$specparts = explode(';;', $row['specifications']);
	foreach ($specparts as $ks => $spec) {
		if (empty($spec)) {
			continue;
		}
		$interval = explode('_', $spec);
		$oldspecifications .= '<p id="old'.$ks.'spec">'.JText::_('VRNEWSPECNAME').': <input type="text" name="specname[]" size="20" value="'.$interval[0].'"/> '.JText::_('VRNEWSPECCOST').': <input type="number" step="any" name="speccost[]" value="'.$interval[1].'"/> '.$currencysymb.' <img src="'.JURI::root().'administrator/components/com_vikrentitems/resources/images/remove.png" onclick="removeSpecification(\'old'.$ks.'spec\');" style="cursor: pointer; vertical-align: middle;"/></p>'."\n";
	}
}
?>
<script type="text/javascript">
function showResizeSel() {
	if (document.adminForm.autoresize.checked == true) {
		document.getElementById('resizesel').style.display='block';
	} else {
		document.getElementById('resizesel').style.display='none';
	}
	return true;
}
function showForceSel() {
	if (document.adminForm.forcesel.checked == true) {
		document.getElementById('forcevalspan').style.display='block';
	} else {
		document.getElementById('forcevalspan').style.display='none';
	}
	return true;
}
function showSpecifications() {
	if (document.adminForm.isspecification.checked == true) {
		document.getElementById('specificationextra').style.display='block';
		if (document.getElementById('myDiv').getElementsByTagName('div').length > 0 || document.getElementById('myDiv').getElementsByTagName('p').length > 0) {
			document.getElementById('opthmanytr').style.display='none';
			document.getElementById('optonlyoncetr').style.display='none';
			document.getElementById('optonceperitemtr').style.display='none';
			document.getElementById('forceseltr').style.display='none';
		}
	} else {
		document.getElementById('specificationextra').style.display='none';
		if (document.getElementById('opthmanytr').style.display == 'none') {
			document.getElementById('opthmanytr').style.display='';
			document.getElementById('optonlyoncetr').style.display='';
			document.getElementById('optonceperitemtr').style.display='';
			document.getElementById('forceseltr').style.display='';
		}
	}
	return true;
}
function addSpecification() {
	var ni = document.getElementById('myDiv');
	var numi = document.getElementById('morespecifications');
	var num = (document.getElementById('morespecifications').value -1)+ 2;
	numi.value = num;
	var newdiv = document.createElement('div');
	var divIdName = 'my'+num+'Div';
	newdiv.setAttribute('id',divIdName);
	var defaultcost = document.adminForm.optcost.value;
	newdiv.innerHTML = '<p><?php echo addslashes(JText::_('VRNEWSPECNAME')); ?>: <input type=\'text\' name=\'specname[]\' size=\'20\'/> <?php echo addslashes(JText::_('VRNEWSPECCOST')); ?>: <input type=\'number\' step=\'any\' name=\'speccost[]\' value=\''+defaultcost+'\'/> <?php echo addslashes($currencysymb); ?> <img src=\'<?php echo JURI::root(); ?>administrator/components/com_vikrentitems/resources/images/remove.png\' onclick=\'removeSpecification("my'+num+'Div");\' style=\'cursor: pointer; vertical-align: middle;\'/></p>';
	ni.appendChild(newdiv);
	if (document.getElementById('opthmanytr').style.display != 'none') {
		document.getElementById('opthmanytr').style.display = 'none';
		document.getElementById('optonlyoncetr').style.display = 'none';
		document.getElementById('optonceperitemtr').style.display = 'none';
		document.getElementById('forceseltr').style.display = 'none';
	}
}
function removeSpecification(el) {
	return (elem=document.getElementById(el)).parentNode.removeChild(elem);
}
</script>
<input type="hidden" value="0" id="morespecifications" />

<form name="adminForm" id="adminForm" action="index.php" method="post" enctype="multipart/form-data">
	<table class="admintable table">
		<tr>
			<td class="vri-config-param-cell" width="200"> <b><?php echo JText::_('VRNEWOPTONE'); ?></b> </td>
			<td><input type="text" name="optname" value="<?php echo count($row) ? htmlspecialchars($row['name']) : ''; ?>" size="40"/></td>
		</tr>
		<tr>
			<td class="vri-config-param-cell" width="200"> <b><?php echo JText::_('VRNEWOPTTWO'); ?></b> </td>
			<td><?php echo $editor->display( "optdescr", (count($row) ? $row['descr'] : ''), 400, 200, 70, 20 ); ?></td>
		</tr>
		<tr>
			<td class="vri-config-param-cell" width="200"> <b><?php echo JText::_('VRNEWOPTTHREE'); ?></b> </td>
			<td><?php echo $currencysymb; ?> <input type="number" step="any" min="0" name="optcost" value="<?php echo count($row) ? $row['cost'] : ''; ?>" /></td>
		</tr>
		<tr>
			<td class="vri-config-param-cell" width="200"> <b><?php echo JText::_('VRNEWOPTFOUR'); ?></b> </td>
			<td><?php echo $wiva; ?></td>
		</tr>
		<tr>
			<td class="vri-config-param-cell" width="200"> <b><?php echo JText::_('VRNEWOPTFIVE'); ?></b> </td>
			<td><input type="checkbox" name="optperday" value="each"<?php echo (count($row) && intval($row['perday']) == 1 ? " checked=\"checked\"" : ""); ?>/></td>
		</tr>
		<tr>
			<td class="vri-config-param-cell" width="200"> <b><?php echo JText::_('VRNEWOPTEIGHT'); ?></b> </td>
			<td><?php echo $currencysymb; ?> <input type="number" step="any" name="maxprice" value="<?php echo count($row) ? $row['maxprice'] : ''; ?>" /></td>
		</tr>
		<tr>
			<td class="vri-config-param-cell" width="200" style="vertical-align: top;"> <b><?php echo JText::_('VRNEWOPTSISSPECIFICATION'); ?></b> </td>
			<td>
				<input type="checkbox" name="isspecification" value="1" onclick="showSpecifications();"<?php echo (strlen($usespecifications) > 0 ? " checked=\"checked\"" : ""); ?>/>
				<div id="specificationextra" style="display: <?php echo ($usespecifications === true && strlen($usespecifications) > 0 ? "block" : "none"); ?>;">
					<p style="display: block; font-weight: bold;"><?php echo JText::_('VRNEWOPTSISSPECIFICATIONEXPL'); ?></p>
					<div id="myDiv" style="display: block;"><?php echo $oldspecifications; ?></div>
					<a href="javascript: void(0);" onclick="addSpecification();"><?php echo JText::_('VRADDSPECIFICATION'); ?></a>
				</div>
			</td>
		</tr>
		<tr id="opthmanytr">
			<td class="vri-config-param-cell" width="200"> <b><?php echo JText::_('VRNEWOPTSIX'); ?></b> </td>
			<td><input type="checkbox" name="opthmany" value="yes"<?php echo (count($row) && intval($row['hmany']) == 1 ? " checked=\"checked\"" : ""); ?>/></td>
		</tr>
		<tr id="optonlyoncetr">
			<td class="vri-config-param-cell" width="200"> <b><?php echo JText::_('VRNEWOPTONLYONCE'); ?></b> </td>
			<td><input type="checkbox" name="optonlyonce" value="yes"<?php echo (count($row) && intval($row['onlyonce']) == 1 ? " checked=\"checked\"" : ""); ?>/></td>
		</tr>
		<tr id="optonceperitemtr">
			<td class="vri-config-param-cell" width="200"> <b><?php echo JText::_('VRNEWOPTONCEPERUNIT'); ?></b> <?php echo $vri_app->createPopover(array('title' => JText::_('VRNEWOPTONCEPERUNIT'), 'content' => JText::_('VRNEWOPTONCEPERUNITHELP'))); ?></td>
			<td><input type="checkbox" name="optonceperitem" value="yes"<?php echo (count($row) && intval($row['onceperitem']) == 1 ? " checked=\"checked\"" : ""); ?>/></td>
		</tr>
		<tr>
			<td class="vri-config-param-cell" width="200"> <b><?php echo JText::_('VRNEWOPTSEVEN'); ?></b> </td>
			<td>
				<?php echo (count($row) && !empty($row['img']) && file_exists(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_vikrentitems'.DS.'resources'.DS.$row['img']) ? "<img src=\"./components/com_vikrentitems/resources/".$row['img']."\" class=\"maxfifty\"/> &nbsp;" : ""); ?>
				<input type="file" name="optimg" size="35"/><br/>
				<label style="display: inline;" for="autoresize"><?php echo JText::_('VRNEWOPTNINE'); ?></label> 
				<input type="checkbox" id="autoresize" name="autoresize" value="1" onclick="showResizeSel();"/> 
				<span id="resizesel" style="display: none;">&nbsp;<?php echo JText::_('VRNEWOPTTEN'); ?>: <input type="text" name="resizeto" value="50" size="3"/> px</span>
			</td>
		</tr>
		<tr id="forceseltr">
			<td class="vri-config-param-cell" width="200" valign="top"> <b><?php echo JText::_('VRINEWOPTFORCESEL'); ?></b> </td>
			<td>
				<input type="checkbox" name="forcesel" value="1" onclick="showForceSel();"<?php echo (count($row) && intval($row['forcesel']) == 1 ? " checked=\"checked\"" : ""); ?>/> 
				<span id="forcevalspan" style="display: <?php echo (count($row) && intval($row['forcesel']) == 1 ? "block" : "none"); ?>;">
					<?php echo JText::_('VRINEWOPTFORCEVALT'); ?> 
					<input type="text" name="forceval" value="<?php echo $forcedq; ?>" size="2"/><br/>
					<?php echo JText::_('VRINEWOPTFORCEVALTPDAY'); ?> 
					<input type="checkbox" name="forcevalperday" value="1"<?php echo ($forcedqperday == true ? " checked=\"checked\"" : ""); ?>/><br/>
					<?php echo JText::_('VRINEWOPTFORCEVALIFDAYS'); ?> 
					<input type="text" name="forceifdays" value="<?php echo count($row) ? $row['forceifdays'] : ''; ?>" size="2"/>
				</span>
			</td>
		</tr>
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
