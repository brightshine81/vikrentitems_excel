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

$choose = "";
if (count($row) && $row['type'] == "select") {
	$x = explode(";;__;;", $row['choose']);
	if (@count($x) > 0) {
		foreach ($x as $y) {
			if (!empty($y)) {
				$choose .= '<input type="text" name="choose[]" value="'.$y.'" size="40"/><br/>'."\n";
			}
		}
	}
}
?>
<script type="text/javascript">
function setCustomfChoose (val) {
	if (val == "text") {
		document.getElementById('customfchoose').style.display = 'none';
		document.getElementById('vrinominative').style.display = 'table-row';
		document.getElementById('vriphone').style.display = 'table-row';
		document.getElementById('vriemail').style.display = 'table-row';
		document.getElementById('vriaddress').style.display = 'table-row';
		document.getElementById('vricity').style.display = 'table-row';
		document.getElementById('vrizip').style.display = 'table-row';
		document.getElementById('vricompany').style.display = 'table-row';
		document.getElementById('vrivat').style.display = 'table-row';
	}
	if (val == "textarea") {
		document.getElementById('customfchoose').style.display = 'none';
		document.getElementById('vrinominative').style.display = 'none';
		document.getElementById('vriphone').style.display = 'none';
		document.getElementById('vriemail').style.display = 'none';
		document.getElementById('vriaddress').style.display = 'none';
		document.getElementById('vricity').style.display = 'none';
		document.getElementById('vrizip').style.display = 'none';
		document.getElementById('vricompany').style.display = 'none';
		document.getElementById('vrivat').style.display = 'none';
	}
	if (val == "date") {
		document.getElementById('customfchoose').style.display = 'none';
		document.getElementById('vrinominative').style.display = 'none';
		document.getElementById('vriphone').style.display = 'none';
		document.getElementById('vriemail').style.display = 'none';
		document.getElementById('vriaddress').style.display = 'none';
		document.getElementById('vricity').style.display = 'none';
		document.getElementById('vrizip').style.display = 'none';
		document.getElementById('vricompany').style.display = 'none';
		document.getElementById('vrivat').style.display = 'none';
	}
	if (val == "checkbox") {
		document.getElementById('customfchoose').style.display = 'none';
		document.getElementById('vrinominative').style.display = 'none';
		document.getElementById('vriphone').style.display = 'none';
		document.getElementById('vriemail').style.display = 'none';
		document.getElementById('vriaddress').style.display = 'none';
		document.getElementById('vricity').style.display = 'none';
		document.getElementById('vrizip').style.display = 'none';
		document.getElementById('vricompany').style.display = 'none';
		document.getElementById('vrivat').style.display = 'none';
	}
	if (val == "select") {
		document.getElementById('customfchoose').style.display = 'block';
		document.getElementById('vrinominative').style.display = 'none';
		document.getElementById('vriphone').style.display = 'none';
		document.getElementById('vriemail').style.display = 'none';
		document.getElementById('vriaddress').style.display = 'none';
		document.getElementById('vricity').style.display = 'none';
		document.getElementById('vrizip').style.display = 'none';
		document.getElementById('vricompany').style.display = 'none';
		document.getElementById('vrivat').style.display = 'none';
	}
	if (val == "separator") {
		document.getElementById('customfchoose').style.display = 'none';
		document.getElementById('vrinominative').style.display = 'none';
		document.getElementById('vriphone').style.display = 'none';
		document.getElementById('vriemail').style.display = 'none';
		document.getElementById('vriaddress').style.display = 'none';
		document.getElementById('vricity').style.display = 'none';
		document.getElementById('vrizip').style.display = 'none';
		document.getElementById('vricompany').style.display = 'none';
		document.getElementById('vrivat').style.display = 'none';
	}
	if (val == "country") {
		document.getElementById('customfchoose').style.display = 'none';
		document.getElementById('vrinominative').style.display = 'none';
		document.getElementById('vriphone').style.display = 'none';
		document.getElementById('vriemail').style.display = 'none';
		document.getElementById('vriaddress').style.display = 'none';
		document.getElementById('vricity').style.display = 'none';
		document.getElementById('vrizip').style.display = 'none';
		document.getElementById('vricompany').style.display = 'none';
		document.getElementById('vrivat').style.display = 'none';
	}
	return true;
}
function addElement() {
	var ni = document.getElementById('customfchooseadd');
	var numi = document.getElementById('theValue');
	var num = (document.getElementById('theValue').value -1)+ 2;
	numi.value = num;
	var newdiv = document.createElement('div');
	var divIdName = 'my'+num+'Div';
	newdiv.setAttribute('id',divIdName);
	newdiv.innerHTML = '<input type=\'text\' name=\'choose[]\' value=\'\' size=\'40\'/><br/>';
	ni.appendChild(newdiv);
}
</script>
<input type="hidden" value="0" id="theValue" />

<form name="adminForm" id="adminForm" action="index.php" method="post">
	<table cellspacing="1" class="admintable table">
		<tbody>
			<tr>
				<td width="200" class="vri-config-param-cell"> <b><?php echo JText::_('VRNEWCUSTOMFONE'); ?></b> </td>
				<td><input type="text" name="name" value="<?php echo count($row) ? htmlspecialchars($row['name']) : ''; ?>" size="40"/></td>
			</tr>
			<tr>
				<td width="200" class="vri-config-param-cell" style="vertical-align: top;"> <b><?php echo JText::_('VRNEWCUSTOMFTWO'); ?></b> </td>
				<td valign="top">
					<select id="stype" name="type" onchange="setCustomfChoose(this.value);">
						<option value="text"<?php echo (count($row) && $row['type'] == "text" ? " selected=\"selected\"" : ""); ?>><?php echo JText::_('VRNEWCUSTOMFTHREE'); ?></option><option value="textarea"<?php echo (count($row) && $row['type'] == "textarea" ? " selected=\"selected\"" : ""); ?>><?php echo JText::_('VRNEWCUSTOMFTEN'); ?></option><option value="date"<?php echo (count($row) && $row['type'] == "date" ? " selected=\"selected\"" : ""); ?>><?php echo JText::_('VRNEWCUSTOMFDATETYPE'); ?></option><option value="select"<?php echo (count($row) && $row['type'] == "select" ? " selected=\"selected\"" : ""); ?>><?php echo JText::_('VRNEWCUSTOMFFOUR'); ?></option><option value="checkbox"<?php echo (count($row) && $row['type'] == "checkbox" ? " selected=\"selected\"" : ""); ?>><?php echo JText::_('VRNEWCUSTOMFFIVE'); ?></option><option value="country"<?php echo (count($row) && $row['type'] == "country" ? " selected=\"selected\"" : ""); ?>><?php echo JText::_('VRINEWCUSTOMFCOUNTRY'); ?></option><option value="separator"<?php echo (count($row) && $row['type'] == "separator" ? " selected=\"selected\"" : ""); ?>><?php echo JText::_('VRNEWCUSTOMFSEPARATOR'); ?></option>
					</select>
					<div id="customfchoose" style="display: <?php echo (count($row) && $row['type'] == "select" ? "block" : "none"); ?>;">
					<?php
					if (!count($row) || (count($row) && $row['type'] != "select")) {
					?>
						<br/><input type="text" name="choose[]" value="" size="40"/>
					<?php
					} else {
						echo '<br/>'.$choose;
					}
					?>
						<div id="customfchooseadd" style="display: block;"></div>
						<span><b><a href="javascript: void(0);" onclick="javascript: addElement();"><?php echo JText::_('VRNEWCUSTOMFNINE'); ?></a></b></span>
					</div>
				</td>
			</tr>
			<tr>
				<td width="200" class="vri-config-param-cell"> <b><?php echo JText::_('VRNEWCUSTOMFSIX'); ?></b> </td>
				<td><input type="checkbox" name="required" value="1"<?php echo (count($row) && intval($row['required']) == 1 ? " checked=\"checked\"" : ""); ?>/></td>
			</tr>
			<tr id="vriemail"<?php echo (count($row) && $row['type'] != "text" ? " style=\"display: none;\"" : ""); ?>>
				<td width="200" class="vri-config-param-cell"> <b><?php echo JText::_('VRNEWCUSTOMFSEVEN'); ?></b> </td>
				<td><input type="checkbox" name="isemail" value="1"<?php echo (count($row) && intval($row['isemail']) == 1 ? " checked=\"checked\"" : ""); ?>/></td>
			</tr>
			<tr id="vrinominative"<?php echo (count($row) && $row['type'] != "text" ? " style=\"display: none;\"" : ""); ?>>
				<td width="200" class="vri-config-param-cell"> <b><?php echo JText::_('VRIISNOMINATIVE'); ?></b> </td>
				<td><input type="checkbox" name="isnominative" value="1"<?php echo (count($row) && intval($row['isnominative']) == 1 ? " checked=\"checked\"" : ""); ?>/></td>
			</tr>
			<tr id="vriphone"<?php echo (count($row) && $row['type'] != "text" ? " style=\"display: none;\"" : ""); ?>>
				<td width="200" class="vri-config-param-cell"> <b><?php echo JText::_('VRIISPHONENUMBER'); ?></b> </td>
				<td><input type="checkbox" name="isphone" value="1"<?php echo (count($row) && intval($row['isphone']) == 1 ? " checked=\"checked\"" : ""); ?>/></td>
			</tr>
			<tr id="vriaddress"<?php echo (count($row) && $row['type'] != "text" ? " style=\"display: none;\"" : ""); ?>>
				<td width="200" class="vri-config-param-cell"> <b><?php echo JText::_('VRIISADDRESS'); ?></b> </td>
				<td><input type="checkbox" name="isaddress" value="1"<?php echo (count($row) && stripos($row['flag'], 'address') !== false ? " checked=\"checked\"" : ""); ?>/></td>
			</tr>
			<tr id="vricity"<?php echo (count($row) && $row['type'] != "text" ? " style=\"display: none;\"" : ""); ?>>
				<td width="200" class="vri-config-param-cell"> <b><?php echo JText::_('VRIISCITY'); ?></b> </td>
				<td><input type="checkbox" name="iscity" value="1"<?php echo (count($row) && stripos($row['flag'], 'city') !== false ? " checked=\"checked\"" : ""); ?>/></td>
			</tr>
			<tr id="vrizip"<?php echo (count($row) && $row['type'] != "text" ? " style=\"display: none;\"" : ""); ?>>
				<td width="200" class="vri-config-param-cell"> <b><?php echo JText::_('VRIISZIP'); ?></b> </td>
				<td><input type="checkbox" name="iszip" value="1"<?php echo (count($row) && stripos($row['flag'], 'zip') !== false ? " checked=\"checked\"" : ""); ?>/></td>
			</tr>
			<tr id="vricompany"<?php echo (count($row) && $row['type'] != "text" ? " style=\"display: none;\"" : ""); ?>>
				<td width="200" class="vri-config-param-cell"> <b><?php echo JText::_('VRIISCOMPANY'); ?></b> </td>
				<td><input type="checkbox" name="iscompany" value="1"<?php echo (count($row) && stripos($row['flag'], 'company') !== false ? " checked=\"checked\"" : ""); ?>/></td>
			</tr>
			<tr id="vrivat"<?php echo (count($row) && $row['type'] != "text" ? " style=\"display: none;\"" : ""); ?>>
				<td width="200" class="vri-config-param-cell"> <b><?php echo JText::_('VRIISVAT'); ?></b> </td>
				<td><input type="checkbox" name="isvat" value="1"<?php echo (count($row) && stripos($row['flag'], 'vat') !== false ? " checked=\"checked\"" : ""); ?>/></td>
			</tr>
			<tr>
				<td width="200" class="vri-config-param-cell"> <b><?php echo JText::_('VRNEWCUSTOMFEIGHT'); ?></b> </td>
				<td>
					<input type="text" name="poplink" value="<?php echo count($row) ? $row['poplink'] : ''; ?>" size="40"/>
					<br/>
					<small>Ex. <i>index.php?option=com_content&amp;view=article&amp;id=#JoomlaArticleID#&amp;tmpl=component</i></small>
				</td>
			</tr>
		</tbody>
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
