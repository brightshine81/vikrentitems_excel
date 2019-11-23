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
</script>
<form name="adminForm" id="adminForm" action="index.php" method="post" enctype="multipart/form-data">
	<table class="admintable table">
		<tr><td class="vri-config-param-cell" width="200"> <b><?php echo JText::_('VRNEWCARATONE'); ?></b> </td><td><input type="text" name="caratname" value="<?php echo count($row) ? htmlspecialchars($row['name']) : ''; ?>" size="40"/></td></tr>
		<tr><td class="vri-config-param-cell" width="200"> <b><?php echo JText::_('VRNEWCARATTWO'); ?></b> </td><td><?php echo (count($row) && !empty($row['icon']) && is_file(VRI_ADMIN_PATH.DS.'resources'.DS.$row['icon']) ? "<img src=\"".VRI_ADMIN_URI."resources/".$row['icon']."\"/>&nbsp; " : ""); ?><input type="file" name="caraticon" size="35"/><br/><label style="display: inline;" for="autoresize"><?php echo JText::_('VRNEWOPTNINE'); ?></label> <input type="checkbox" id="autoresize" name="autoresize" value="1" onclick="showResizeSel();"/> <span id="resizesel" style="display: none;">&nbsp;<?php echo JText::_('VRNEWOPTTEN'); ?>: <input type="text" name="resizeto" value="50" size="3"/> px</span></td></tr>
		<tr><td class="vri-config-param-cell" width="200"> <b><?php echo JText::_('VRNEWCARATTHREE'); ?></b> </td><td><input type="text" name="carattextimg" value="<?php echo count($row) ? htmlspecialchars($row['textimg']) : ''; ?>" size="40"/></td></tr>
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
