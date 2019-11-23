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

$dbo = JFactory::getDBO();
$vri_app = VikRentItems::getVriApplication();
//load jQuery lib and navigation
$document = JFactory::getDocument();
$document->addStyleSheet(VRI_SITE_URI.'resources/jquery-ui.min.css');
//load jQuery UI
JHtml::_('script', VRI_SITE_URI.'resources/jquery-ui.min.js', false, true, false, false);
$navdecl = '
jQuery.noConflict();
var baseaddrbaselink = "index.php?option=com_vikrentitems&task=validatebaseaddr&tmpl=component";
jQuery(document).ready(function() {
	jQuery(".vrimodal").fancybox();
	jQuery(".vrimodaliframe").fancybox({
		beforeLoad: function() {
			this.href = baseaddrbaselink+"&baseaddress="+jQuery("#deliverybaseaddresscont").val();
		},
		"helpers": {
			"overlay": {
				"locked": false
			}
		},
		"padding": 0,
		"width": "100%",
		"height": "500px",
		"autoScale": false,
		"transitionIn": "none",
		"transitionOut": "none",
		"type": "iframe"
	});
});';
$document->addScriptDeclaration($navdecl);
$tooltipdecl = '
jQuery(document).ready(function() {
	jQuery(".vritooltip").tooltip();
});';
$document->addScriptDeclaration($tooltipdecl);
//
$currencysymb = VikRentItems::getCurrencySymb(true);
$delcalcunit = VikRentItems::getDeliveryCalcUnit(true);

// tax rates
$delivery_tax_id = VikRentItems::getDeliveryTaxId(true);
$ivas = array();
$q = "SELECT * FROM `#__vikrentitems_iva`;";
$dbo->setQuery($q);
$dbo->execute();
if ($dbo->getNumRows() > 0) {
	$ivas = $dbo->loadAssocList();
}
$wiva = "<select name=\"deliverytaxid\">\n<option value=\"\"></option>\n";
foreach ($ivas as $iv) {
	$wiva .= "<option value=\"".$iv['id']."\"".($delivery_tax_id == $iv['id'] ? " selected=\"selected\"" : "").">".(empty($iv['name']) ? $iv['aliq']."%" : $iv['name']."-".$iv['aliq']."%")."</option>\n";
}
$wiva .= "</select>\n";
//
?>
<fieldset class="adminform">
	<legend class="adminlegend"><?php echo JText::_('VRPANELFIVE'); ?></legend>
	<table cellspacing="1" class="admintable table">
		<tbody>
			<tr>
				<td width="200" class="vri-config-param-cell"> <b><?php echo JText::_('VRICONFDELBASEADDR'); ?></b> <?php echo $vri_app->createPopover(array('title' => JText::_('VRICONFDELBASEADDR'), 'content' => JText::_('VRICONFDELBASEADDREXP'))); ?></td>
				<td style="vertical-align: middle;">
					<input type="text" name="deliverybaseaddress" id="deliverybaseaddresscont" value="<?php echo VikRentItems::getDeliveryBaseAddress(true); ?>" size="40"/>
					<span class="vrimodaliframebtn"><a href="index.php?option=com_vikrentitems&amp;task=validatebaseaddr&amp;tmpl=component" target="_blank" id="vrivalidatebaseaddr" class="vrimodaliframe"><i class="fas fa-location-arrow"></i> <?php echo JText::_('VRICONFDELBASEADDRVALIDATE'); ?></a></span>
				</td>
			</tr>
			<tr>
				<td width="200" class="vri-config-param-cell"> <b><?php echo JText::_('VRICONFDELBASELAT'); ?></b> </td>
				<td><input type="text" name="deliverybaselat" id="deliverybaselat" value="<?php echo VikRentItems::getDeliveryBaseLatitude(true); ?>" readonly/></td>
			</tr>
			<tr>
				<td width="200" class="vri-config-param-cell"> <b><?php echo JText::_('VRICONFDELBASELNG'); ?></b> </td>
				<td><input type="text" name="deliverybaselng" id="deliverybaselng" value="<?php echo VikRentItems::getDeliveryBaseLongitude(true); ?>" readonly/></td>
			</tr>
			<tr>
				<td width="200" class="vri-config-param-cell"> <b><?php echo JText::_('VRICONFDELCALCUNIT'); ?></b> </td>
				<td>
					<select name="deliverycalcunit">
						<option value="km"<?php echo ($delcalcunit == "km" ? " selected=\"selected\"" : ""); ?>><?php echo JText::_('VRICONFDELCALCUNITKM'); ?></option>
						<option value="miles"<?php echo ($delcalcunit == "miles" ? " selected=\"selected\"" : ""); ?>><?php echo JText::_('VRICONFDELCALCUNITMILES'); ?></option>
					</select>
				</td>
			</tr>
			<tr>
				<td width="200" class="vri-config-param-cell"> <b><?php echo JText::_('VRICONFDELROUNDDIST'); ?></b> </td>
				<td><?php echo $vri_app->printYesNoButtons('deliveryrounddist', JText::_('VRYES'), JText::_('VRNO'), (int)VikRentItems::getDeliveryRoundDistance(true), 1, 0); ?></td>
			</tr>
			<tr>
				<td width="200" class="vri-config-param-cell"> <b><?php echo JText::_('VRICONFDELCOSTPERUNIT'); ?></b> <?php echo $vri_app->createPopover(array('title' => JText::_('VRICONFDELCOSTPERUNIT'), 'content' => JText::_('VRICONFDELCOSTPERUNITHELP'))); ?></td>
				<td><input type="number" name="deliverycostperunit" value="<?php echo VikRentItems::getDeliveryCostPerUnit(true); ?>" min="0" step="any"/> <?php echo $currencysymb; ?> &nbsp;&nbsp; <?php echo $wiva; ?></td>
			</tr>
			<tr>
				<td width="200" class="vri-config-param-cell"> <b><?php echo JText::_('VRICONFDELROUNDCOST'); ?></b> </td>
				<td><?php echo $vri_app->printYesNoButtons('deliveryroundcost', JText::_('VRYES'), JText::_('VRNO'), (int)VikRentItems::getDeliveryRoundCost(true), 1, 0); ?></td>
			</tr>
			<tr>
				<td width="200" class="vri-config-param-cell"> <b><?php echo JText::_('VRICONFDELIVPERORD'); ?></b> <?php echo $vri_app->createPopover(array('title' => JText::_('VRICONFDELIVPERORD'), 'content' => JText::_('VRICONFDELIVPERORDHELP'))); ?></td>
				<td><?php echo $vri_app->printYesNoButtons('deliveryperord', JText::_('VRYES'), JText::_('VRNO'), (int)VikRentItems::isDeliveryPerOrder(true), 1, 0); ?></td>
			</tr>
			<tr>
				<td width="200" class="vri-config-param-cell"> <b><?php echo JText::_('VRICONFDELIVPERITUNIT'); ?></b> <?php echo $vri_app->createPopover(array('title' => JText::_('VRICONFDELIVPERITUNIT'), 'content' => JText::_('VRICONFDELIVPERITUNITHELP'))); ?></td>
				<td><?php echo $vri_app->printYesNoButtons('deliveryperitunit', JText::_('VRYES'), JText::_('VRNO'), (int)VikRentItems::isDeliveryPerItemUnit(true), 1, 0); ?></td>
			</tr>
			<tr>
				<td width="200" class="vri-config-param-cell"> <b><?php echo JText::_('VRICONFDELMAXDIST'); ?></b> </td>
				<td><input type="number" name="deliverymaxunitdist" value="<?php echo VikRentItems::getDeliveryMaxDistance(true); ?>" min="0" step="any"/></td>
			</tr>
			<tr>
				<td width="200" class="vri-config-param-cell"> <b><?php echo JText::_('VRICONFDELMAXCOST'); ?></b> </td>
				<td><input type="number" name="deliverymaxcost" value="<?php echo VikRentItems::getDeliveryMaxCost(true); ?>" min="0" step="any"/> <?php echo $currencysymb; ?></td>
			</tr>
			<tr>
				<td width="200" class="vri-config-param-cell"> <b><?php echo JText::_('VRICONFDELIVERYNOTES'); ?></b> </td>
				<td><textarea name="deliverymapnotes" rows="6" cols="40"><?php echo VikRentItems::getDeliveryMapNotes(); ?></textarea></td>
			</tr>
		</tbody>
	</table>
</fieldset>
