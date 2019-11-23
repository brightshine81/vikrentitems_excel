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

$dbo = JFactory::getDBO();
$q = "SELECT * FROM `#__vikrentitems_iva`;";
$dbo->setQuery($q);
$dbo->execute();
if ($dbo->getNumRows() > 0) {
	$ivas = $dbo->loadAssocList();
	$wiva = "<select name=\"praliq\">\n";
	foreach ($ivas as $iv) {
		$wiva .= "<option value=\"".$iv['id']."\"".(count($row) && $iv['id'] == $row['idiva'] ? " selected=\"selected\"" : "").">".(empty($iv['name']) ? $iv['aliq']."%" : $iv['name']."-".$iv['aliq']."%")."</option>\n";
	}
	$wiva .= "</select>\n";
} else {
	$wiva = "<a href=\"index.php?option=com_vikrentitems&task=iva\">".JText::_('NESSUNAIVA')."</a>";
}
?>
<form name="adminForm" id="adminForm" action="index.php" method="post">
	<table class="admintable table">
		<tr><td class="vri-config-param-cell" width="200"> <b><?php echo JText::_('VRNEWPRICEONE'); ?><sup>*</sup></b> </td><td><input type="text" name="price" value="<?php echo count($row) ? htmlspecialchars($row['name']) : ''; ?>" size="40"/></td></tr>
		<tr><td class="vri-config-param-cell" width="200"> <b><?php echo JText::_('VRNEWPRICETWO'); ?></b> <?php echo $vri_app->createPopover(array('title' => JText::_('VRNEWPRICETWO'), 'content' => JText::_('VRIPRATTRHELP'))); ?></td><td><input type="text" name="attr" value="<?php echo count($row) ? $row['attr'] : ''; ?>" size="40"/></td></tr>
		<tr><td class="vri-config-param-cell" width="200"> <b><?php echo JText::_('VRNEWPRICETHREE'); ?></b> </td><td><?php echo $wiva; ?></td></tr>
	</table>
	<input type="hidden" name="task" value="">
<?php
if (count($row)) {
	?>
	<input type="hidden" name="whereup" value="<?php echo $row['id']; ?>">
	<?php
}
?>
	<input type="hidden" name="option" value="com_vikrentitems" />
</form>
