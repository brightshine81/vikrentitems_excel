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

$vri_app = VikRentItems::getVriApplication();
$editor = JEditor::getInstance(JFactory::getApplication()->get('editor'));
JHTML::_('behavior.modal');
$sitelogo = VikRentItems::getSiteLogo();
$sendemailwhen = VikRentItems::getSendEmailWhen();
?>
<fieldset class="adminform">
	<legend class="adminlegend"><?php echo JText::_('VRPANELFOUR'); ?></legend>
	<table cellspacing="1" class="admintable table">
		<tbody>
			<tr>
				<td width="200" class="vri-config-param-cell"> <b><?php echo JText::_('VRIONFIGFOURLOGO'); ?></b> </td>
				<td><input type="file" name="sitelogo" size="35"/> <?php echo (strlen($sitelogo) > 0 ? '&nbsp;&nbsp;<a href="'.VRI_ADMIN_URI.'resources/'.$sitelogo.'" class="modal" target="_blank">'.$sitelogo.'</a>' : ''); ?></td>
			</tr>
			<tr>
				<td width="200" class="vri-config-param-cell"> <b><?php echo JText::_('VRICONFIGSENDEMAILWHEN'); ?></b> </td>
				<td><select name="sendemailwhen"><option value="1"><?php echo JText::_('VRICONFIGSMSSENDWHENCONFPEND'); ?></option><option value="2"<?php echo $sendemailwhen > 1 ? ' selected="selected"' : ''; ?>><?php echo JText::_('VRICONFIGSMSSENDWHENCONF'); ?></option></select></td>
			</tr>
			<tr>
				<td width="200" class="vri-config-param-cell"> <b><?php echo JText::_('VRUSEJUTILITY'); ?></b> </td>
				<td><?php echo $vri_app->printYesNoButtons('sendjutility', JText::_('VRYES'), JText::_('VRNO'), (VikRentItems::sendJutility() ? 'yes' : 0), 'yes', 0); ?></td>
			</tr>
			<tr>
				<td width="200" class="vri-config-param-cell"> <b><?php echo JText::_('VRISENDPDF'); ?></b> </td>
				<td><?php echo $vri_app->printYesNoButtons('sendpdf', JText::_('VRYES'), JText::_('VRNO'), (VikRentItems::sendPDF() ? 'yes' : 0), 'yes', 0); ?></td>
			</tr>
			<tr>
				<td width="200" class="vri-config-param-cell"> <b><?php echo JText::_('VRIONFIGFOURTWO'); ?></b> </td>
				<td><?php echo $vri_app->printYesNoButtons('allowstats', JText::_('VRYES'), JText::_('VRNO'), (VikRentItems::allowStats() ? 'yes' : 0), 'yes', 0); ?></td>
			</tr>
			<tr>
				<td width="200" class="vri-config-param-cell"> <b><?php echo JText::_('VRIONFIGFOURTHREE'); ?></b> </td>
				<td><?php echo $vri_app->printYesNoButtons('sendmailstats', JText::_('VRYES'), JText::_('VRNO'), (VikRentItems::sendMailStats() ? 'yes' : 0), 'yes', 0); ?></td>
			</tr>
			<tr>
				<td width="200" class="vri-config-param-cell"> <b><?php echo JText::_('VRIONFIGFOURORDMAILFOOTER'); ?></b> </td>
				<td><?php echo $editor->display( "footerordmail", VikRentItems::getFooterOrdMail(), 500, 350, 70, 20 ); ?></td>
			</tr>
			<tr>
				<td width="200" class="vri-config-param-cell"> <b><?php echo JText::_('VRIONFIGFOURFOUR'); ?></b> </td>
				<td><textarea name="disclaimer" rows="7" cols="50"><?php echo VikRentItems::getDisclaimer(); ?></textarea></td>
			</tr>
		</tbody>
	</table>
</fieldset>
