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
$formatvals = VikRentItems::getNumberFormatData(true);
$formatparts = explode(':', $formatvals);
?>
<fieldset class="adminform">
	<legend class="adminlegend"><?php echo JText::_('VRICONFIGCURRENCYPART'); ?></legend>
	<table cellspacing="1" class="admintable table">
		<tbody>
			<tr>
				<td width="200" class="vri-config-param-cell"> <b><?php echo JText::_('VRIONFIGTHREECURNAME'); ?></b> </td>
				<td><input type="text" name="currencyname" value="<?php echo VikRentItems::getCurrencyName(); ?>" size="10"/></td>
			</tr>
			<tr>
				<td width="200" class="vri-config-param-cell"> <b><?php echo JText::_('VRIONFIGTHREECURSYMB'); ?></b> </td>
				<td><input type="text" name="currencysymb" value="<?php echo VikRentItems::getCurrencySymb(true); ?>" size="10"/></td>
			</tr>
			<tr>
				<td width="200" class="vri-config-param-cell"> <b><?php echo JText::_('VRIONFIGTHREECURCODEPP'); ?></b> </td>
				<td><input type="text" name="currencycodepp" value="<?php echo VikRentItems::getCurrencyCodePp(); ?>" size="10"/></td>
			</tr>
			<tr>
				<td width="200" class="vri-config-param-cell"> <b><?php echo JText::_('VRCONFIGNUMDECIMALS'); ?></b> </td>
				<td><input type="number" name="numdecimals" value="<?php echo $formatparts[0]; ?>" min="0"/></td>
			</tr>
			<tr>
				<td width="200" class="vri-config-param-cell"> <b><?php echo JText::_('VRCONFIGNUMDECSEPARATOR'); ?></b> </td>
				<td><input type="text" name="decseparator" value="<?php echo $formatparts[1]; ?>" size="2"/></td>
			</tr>
			<tr>
				<td width="200" class="vri-config-param-cell"> <b><?php echo JText::_('VRCONFIGNUMTHOSEPARATOR'); ?></b> </td>
				<td><input type="text" name="thoseparator" value="<?php echo $formatparts[2]; ?>" size="2"/></td>
			</tr>
		</tbody>
	</table>
</fieldset>

<fieldset class="adminform">
	<legend class="adminlegend"><?php echo JText::_('VRICONFIGPAYMPART'); ?></legend>
	<table cellspacing="1" class="admintable table">
		<tbody>
			<tr>
				<td width="200" class="vri-config-param-cell"> <b><?php echo JText::_('VRIONFIGTWOFIVE'); ?></b> </td>
				<td><?php echo $vri_app->printYesNoButtons('ivainclusa', JText::_('VRYES'), JText::_('VRNO'), (VikRentItems::ivaInclusa(true) ? 'yes' : 0), 'yes', 0); ?></td>
			</tr>
			<tr>
				<td width="200" class="vri-config-param-cell"> <b><?php echo JText::_('VRIONFIGTWOTHREE'); ?></b> </td>
				<td><?php echo $vri_app->printYesNoButtons('paytotal', JText::_('VRYES'), JText::_('VRNO'), (VikRentItems::payTotal() ? 'yes' : 0), 'yes', 0); ?></td>
			</tr>
			<tr>
				<td width="200" class="vri-config-param-cell"> <b><?php echo JText::_('VRIONFIGTWOFOUR'); ?></b> </td>
				<td><input type="number" name="payaccpercent" value="<?php echo VikRentItems::getAccPerCent(); ?>" min="0" step="any"/> <select id="typedeposit" name="typedeposit"><option value="pcent">%</option><option value="fixed"<?php echo (VikRentItems::getTypeDeposit(true) == "fixed" ? ' selected="selected"' : ''); ?>><?php echo VikRentItems::getCurrencySymb(); ?></option></select></td>
			</tr>
			<tr>
				<td width="200" class="vri-config-param-cell"> <b><?php echo JText::_('VRIONFIGTWOSIX'); ?></b> </td>
				<td><input type="text" name="paymentname" value="<?php echo VikRentItems::getPaymentName(); ?>" size="25"/></td>
			</tr>
		</tbody>
	</table>
</fieldset>
