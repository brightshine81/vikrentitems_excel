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
$document = JFactory::getDocument();
$document->addStyleSheet(VRI_SITE_URI.'resources/jquery.fancybox.css');
JHtml::_('script', VRI_SITE_URI.'resources/jquery.fancybox.js', false, true, false, false);
$themesel = '<select name="theme">';
$themesel .= '<option value="default">default</option>';
$themes = glob(VRI_SITE_PATH.DS.'themes'.DS.'*');
$acttheme = VikRentItems::getTheme();
if (count($themes) > 0) {
	$strip = VRI_SITE_PATH.DS.'themes'.DS;
	foreach ($themes as $th) {
		if (is_dir($th)) {
			$tname = str_replace($strip, '', $th);
			if ($tname != 'default') {
				$themesel .= '<option value="'.$tname.'"'.($tname == $acttheme ? ' selected="selected"' : '').'>'.$tname.'</option>';
			}
		}
	}
}
$themesel .= '</select>';
$firstwday = VikRentItems::getFirstWeekDay(true);
?>
<fieldset class="adminform">
	<legend class="adminlegend"><?php echo JText::_('VRICONFIGPAYMPART'); ?></legend>
	<table cellspacing="1" class="admintable table">
		<tbody>
			<tr>
				<td width="200" class="vri-config-param-cell"> <b><?php echo JText::_('VRCONFIGFIRSTWDAY'); ?></b> </td>
				<td><select name="firstwday" style="float: none;"><option value="0"<?php echo $firstwday == '0' ? ' selected="selected"' : ''; ?>><?php echo JText::_('VRISUNDAY'); ?></option><option value="1"<?php echo $firstwday == '1' ? ' selected="selected"' : ''; ?>><?php echo JText::_('VRIMONDAY'); ?></option><option value="2"<?php echo $firstwday == '2' ? ' selected="selected"' : ''; ?>><?php echo JText::_('VRITUESDAY'); ?></option><option value="3"<?php echo $firstwday == '3' ? ' selected="selected"' : ''; ?>><?php echo JText::_('VRIWEDNESDAY'); ?></option><option value="4"<?php echo $firstwday == '4' ? ' selected="selected"' : ''; ?>><?php echo JText::_('VRITHURSDAY'); ?></option><option value="5"<?php echo $firstwday == '5' ? ' selected="selected"' : ''; ?>><?php echo JText::_('VRIFRIDAY'); ?></option><option value="6"<?php echo $firstwday == '6' ? ' selected="selected"' : ''; ?>><?php echo JText::_('VRISATURDAY'); ?></option></select></td>
			</tr>
			<tr>
				<td width="200" class="vri-config-param-cell"> <b><?php echo JText::_('VRIONFIGTHREETEN'); ?></b> </td>
				<td><input type="number" name="numcalendars" value="<?php echo VikRentItems::numCalendars(); ?>" min="0"/></td>
			</tr>
			<tr>
				<td width="200" class="vri-config-param-cell"> <b><?php echo JText::_('VRCONFIGTHUMBSIZE'); ?></b> </td>
				<td><input type="number" step="any" name="thumbswidth" value="<?php echo VikRentItems::getThumbnailsWidth(); ?>" min="0"/> px</td>
			</tr>
			<tr>
				<td width="200" class="vri-config-param-cell"> <b><?php echo JText::_('VRIONFIGTHREENINE'); ?></b> </td>
				<td><?php echo $vri_app->printYesNoButtons('showpartlyreserved', JText::_('VRYES'), JText::_('VRNO'), (VikRentItems::showPartlyReserved() ? 'yes' : 0), 'yes', 0); ?></td>
			</tr>
			<tr>
				<td width="200" class="vri-config-param-cell"> <b><?php echo JText::_('VRCONFIGEMAILTEMPLATE'); ?></b> </td>
				<td><button type="button" class="btn vri-edit-tmpl" data-tmpl-path="<?php echo urlencode(VRI_SITE_PATH.DS.'helpers'.DS.'email_tmpl.php'); ?>"><i class="icon-edit"></i> <?php echo JText::_('VRCONFIGEDITTMPLFILE'); ?></button></td>
			</tr>
			<tr>
				<td width="200" class="vri-config-param-cell"> <b><?php echo JText::_('VRCONFIGPDFTEMPLATE'); ?></b> </td>
				<td><button type="button" class="btn vri-edit-tmpl" data-tmpl-path="<?php echo urlencode(VRI_SITE_PATH.DS.'helpers'.DS.'pdf_tmpl.php'); ?>"><i class="icon-edit"></i> <?php echo JText::_('VRCONFIGEDITTMPLFILE'); ?></button></td>
			</tr>
			<tr>
				<td width="200" class="vri-config-param-cell"> <b>Custom CSS Overrides</b> </td>
				<td><button type="button" class="btn vri-edit-tmpl" data-tmpl-path="<?php echo urlencode(VRI_SITE_PATH.DS.'vikrentitems_custom.css'); ?>"><i class="icon-edit"></i> <?php echo JText::_('VRCONFIGEDITTMPLFILE'); ?></button></td>
			</tr>
			<tr>
				<td width="200" class="vri-config-param-cell"> <b><?php echo JText::_('VRIONFIGTHREEONE'); ?></b> </td>
				<td><input type="text" name="fronttitle" value="<?php echo VikRentItems::getFrontTitle(); ?>" size="10"/></td>
			</tr>
			<tr>
				<td width="200" class="vri-config-param-cell"> <b><?php echo JText::_('VRIONFIGTHREETWO'); ?></b> </td>
				<td><input type="text" name="fronttitletag" value="<?php echo VikRentItems::getFrontTitleTag(); ?>" size="10"/></td>
			</tr>
			<tr>
				<td width="200" class="vri-config-param-cell"> <b><?php echo JText::_('VRIONFIGTHREETHREE'); ?></b> </td>
				<td><input type="text" name="fronttitletagclass" value="<?php echo VikRentItems::getFrontTitleTagClass(); ?>" size="10"/></td>
			</tr>
			<tr>
				<td width="200" class="vri-config-param-cell"> <b><?php echo JText::_('VRIONFIGTHREEFOUR'); ?></b> </td>
				<td><input type="text" name="searchbtnval" value="<?php echo VikRentItems::getSubmitName(true); ?>" size="10"/></td>
			</tr>
			<tr>
				<td width="200" class="vri-config-param-cell"> <b><?php echo JText::_('VRIONFIGTHREEFIVE'); ?></b> </td>
				<td><input type="text" name="searchbtnclass" value="<?php echo VikRentItems::getSubmitClass(true); ?>" size="10"/></td>
			</tr>
			<tr>
				<td width="200" class="vri-config-param-cell"> <b><?php echo JText::_('VRIONFIGTHREESIX'); ?></b> </td>
				<td><?php echo $vri_app->printYesNoButtons('showfooter', JText::_('VRYES'), JText::_('VRNO'), (VikRentItems::showFooter() ? 'yes' : 0), 'yes', 0); ?></td>
			</tr>
			<tr>
				<td width="200" class="vri-config-param-cell"> <b><?php echo JText::_('VRIONFIGTHEME'); ?></b> </td>
				<td><?php echo $themesel; ?></td>
			</tr>
			<tr>
				<td width="200" class="vri-config-param-cell"> <b><?php echo JText::_('VRIONFIGTHREESEVEN'); ?></b> </td>
				<td><?php echo $editor->display( "intromain", VikRentItems::getIntroMain(), 500, 350, 70, 20 ); ?></td>
			</tr>
			<tr>
				<td width="200" class="vri-config-param-cell"> <b><?php echo JText::_('VRIONFIGTHREEEIGHT'); ?></b> </td>
				<td><textarea name="closingmain" rows="5" cols="50"><?php echo VikRentItems::getClosingMain(); ?></textarea></td>
			</tr>


		</tbody>
	</table>
</fieldset>

<script type="text/javascript">
jQuery(document).ready(function() {
	jQuery(".vri-edit-tmpl").click(function() {
		var vri_tmpl_path = jQuery(this).attr("data-tmpl-path");
		jQuery.fancybox({
			"helpers": {
				"overlay": {
					"locked": false
				}
			},
			"href": "index.php?option=com_vikrentitems&task=edittmplfile&path="+vri_tmpl_path+"&tmpl=component",
			"width": "75%",
			"height": "75%",
			"autoScale": false,
			"transitionIn": "none",
			"transitionOut": "none",
			//"padding": 0,
			"type": "iframe"
		});
	});
});
</script>