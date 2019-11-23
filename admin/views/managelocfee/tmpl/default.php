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

$vri_app = VikRentItems::getVriApplication();
$currencysymb = VikRentItems::getCurrencySymb(true);
if (strlen($wsel) > 0) {
	$dbo = JFactory::getDBO();
	$q = "SELECT * FROM `#__vikrentitems_iva`;";
	$dbo->setQuery($q);
	$dbo->execute();
	if ($dbo->getNumRows() > 0) {
		$ivas = $dbo->loadAssocList();
		$wiva = "<select name=\"aliq\">\n";
		foreach ($ivas as $iv) {
			$wiva .= "<option value=\"".$iv['id']."\"".(count($row) && $row['idiva'] == $iv['id'] ? " selected=\"selected\"" : "").">".(empty($iv['name']) ? $iv['aliq']."%" : $iv['name']."-".$iv['aliq']."%")."</option>\n";
		}
		$wiva .= "</select>\n";
	} else {
		$wiva = "<a href=\"index.php?option=com_vikrentitems&task=iva\">".JText::_('VRNOIVAFOUND')."</a>";
	}
	
	$actvalueoverrides = '';
	if (count($row) && strlen($row['losoverride']) > 0) {
		$losoverrides = explode('_', $row['losoverride']);
		foreach ($losoverrides as $loso) {
			if (!empty($loso)) {
				$losoparts = explode(':', $loso);
				$actvalueoverrides .= '<p>'.JText::_('VRLOCFEECOSTOVERRIDEDAYS').' <input type="text" name="nightsoverrides[]" value="'.$losoparts[0].'" size="4"/> - '.JText::_('VRLOCFEECOSTOVERRIDECOST').' <input type="text" name="valuesoverrides[]" value="'.$losoparts[1].'" size="5"/> '.$currencysymb.'</p>';
			}
		}
	}
	?>
	<script type="text/javascript">
	function addMoreOverrides() {
		var ni = document.getElementById('myDiv');
		var numi = document.getElementById('morevalueoverrides');
		var num = (document.getElementById('morevalueoverrides').value -1)+ 2;
		numi.value = num;
		var newdiv = document.createElement('div');
		var divIdName = 'my'+num+'Div';
		newdiv.setAttribute('id',divIdName);
		newdiv.innerHTML = '<p><?php echo addslashes(JText::_('VRLOCFEECOSTOVERRIDEDAYS')); ?> <input type=\'text\' name=\'nightsoverrides[]\' value=\'\' size=\'4\'/> - <?php echo addslashes(JText::_('VRLOCFEECOSTOVERRIDECOST')); ?> <input type=\'text\' name=\'valuesoverrides[]\' value=\'\' size=\'5\'/> <?php echo addslashes($currencysymb); ?></p>';
		ni.appendChild(newdiv);
	}
	</script>
	<input type="hidden" value="0" id="morevalueoverrides" />
	
	<form name="adminForm" id="adminForm" action="index.php" method="post">
		<table class="admintable table">
			<tr><td class="vri-config-param-cell" width="200"> <b><?php echo JText::_('VRNEWLOCFEEONE'); ?></b> </td><td><?php echo $wsel; ?></td></tr>
			<tr><td class="vri-config-param-cell" width="200"> <b><?php echo JText::_('VRNEWLOCFEETWO'); ?></b> </td><td><?php echo $wseltwo; ?></td></tr>
			<tr><td class="vri-config-param-cell" width="200"> <b><?php echo JText::_('VRLOCFEEINVERT'); ?></b> </td><td><input type="checkbox" name="invert" value="1"<?php echo (count($row) && intval($row['invert']) == 1 ? " checked=\"checked\"" : ""); ?>/></td></tr>
			<tr><td class="vri-config-param-cell" width="200"> <b><?php echo JText::_('VRNEWLOCFEETHREE'); ?></b> </td><td><?php echo $currencysymb; ?> <input type="number" step="any" name="cost" value="<?php echo count($row) ? $row['cost'] : ''; ?>" size="3"/></td></tr>
			<tr><td class="vri-config-param-cell" width="200"> <b><?php echo JText::_('VRNEWLOCFEEFOUR'); ?></b> </td><td><input type="checkbox" name="daily" value="1"<?php echo (count($row) && intval($row['daily']) == 1 ? " checked=\"checked\"" : ""); ?>/></td></tr>
			<tr><td class="vri-config-param-cell" width="150" valign="top"> <b><?php echo JText::_('VRLOCFEECOSTOVERRIDE'); ?></b> <?php echo $vri_app->createPopover(array('title' => JText::_('VRLOCFEECOSTOVERRIDE'), 'content' => JText::_('VRLOCFEECOSTOVERRIDEHELP'))); ?></td><td><div id="myDiv" style="display: block;"><?php echo $actvalueoverrides; ?></div><a href="javascript: void(0);" onclick="addMoreOverrides();"><?php echo JText::_('VRLOCFEECOSTOVERRIDEADD'); ?></a></td></tr>
			<tr><td class="vri-config-param-cell" width="200"> <b><?php echo JText::_('VRNEWLOCFEEFIVE'); ?></b> </td><td><?php echo $wiva; ?></td></tr>
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
	<p class="warn"><a href="index.php?option=com_vikrentitems&amp;task=newplace"><?php echo JText::_('VRNOPLACESFOUND'); ?></a></p>
	<form action="index.php?option=com_vikrentitems" method="post" name="adminForm" id="adminForm">
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="option" value="com_vikrentitems" />
	</form>
	<?php
}
