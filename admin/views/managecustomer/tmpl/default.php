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

$customer = $this->customer;
$wselcountries = $this->wselcountries;

//JHtmlList::users(string $name, string $active, integer $nouser, string $javascript = null, string $order = 'name')
if (!class_exists('JHtmlList')) {
	jimport( 'joomla.html.html.list' );
}
$df = VikRentItems::getDateFormat(true);
if ($df == "%d/%m/%Y") {
	$usedf = 'd/m/Y';
} elseif ($df == "%m/%d/%Y") {
	$usedf = 'm/d/Y';
} else {
	$usedf = 'Y/m/d';
}
$vri_app = VikRentItems::getVriApplication();
$document = JFactory::getDocument();
$document->addStyleSheet(VRI_SITE_URI.'resources/jquery.fancybox.css');
JHtml::_('script', VRI_SITE_URI.'resources/jquery.fancybox.js', false, true, false, false);
$ptmpl = VikRequest::getString('tmpl', '', 'request');
$pcheckin = VikRequest::getInt('checkin', '', 'request');
$pbid = VikRequest::getInt('bid', '', 'request');
?>
<script type="text/Javascript">
function getRandomPin(min, max) {
	return Math.floor(Math.random() * (max - min)) + min;
}
function generatePin() {
	var pin = getRandomPin(10999, 99999);
	document.getElementById('pin').value = pin;
}
jQuery(document).ready(function() {
	jQuery(document.body).on("click", ".vri-cur-idscan a", function(e) {
		e.preventDefault();
		var imgsrc = jQuery(this).attr("href");
		jQuery.fancybox({
			"helpers": {
				"overlay": {
					"locked": false
				}
			},
			"href": imgsrc,
			"autoScale": false,
			"transitionIn": "none",
			"transitionOut": "none",
			"padding": 0,
			"type": "image"
		});
	});
<?php
if (count($customer) && !empty($customer['bdate'])) {
	?>
	jQuery("#bdate").val("<?php echo $customer['bdate']; ?>").attr('data-alt-value', "<?php echo $customer['bdate']; ?>");
	<?php
}
?>
});
</script>
<form name="adminForm" id="adminForm" action="index.php" method="post" enctype="multipart/form-data">
	<div style="width: 49%; float: left;">
		<fieldset class="adminform">
			<legend class="adminlegend"><?php echo JText::_('VRICUSTOMERDETAILS'); ?></legend>
			<table cellspacing="1" class="admintable table">
				<tbody>
					<tr>
						<td width="200" class="vri-config-param-cell"> <b><?php echo JText::_('VRCUSTOMERFIRSTNAME'); ?> <sup>*</sup></b> </td>
						<td><input type="text" name="first_name" value="<?php echo count($customer) ? $customer['first_name'] : ''; ?>" size="30"/></td>
					</tr>
					<tr>
						<td width="200" class="vri-config-param-cell"> <b><?php echo JText::_('VRCUSTOMERLASTNAME'); ?> <sup>*</sup></b> </td>
						<td><input type="text" name="last_name" value="<?php echo count($customer) ? $customer['last_name'] : ''; ?>" size="30"/></td>
					</tr>
					<tr>
						<td width="200" class="vri-config-param-cell"> <b><?php echo JText::_('VRCUSTOMERCOMPANY'); ?></b> </td>
						<td><input type="text" name="company" value="<?php echo count($customer) ? $customer['company'] : ''; ?>" size="30"/></td>
					</tr>
					<tr>
						<td width="200" class="vri-config-param-cell"> <b><?php echo JText::_('VRCUSTOMERCOMPANYVAT'); ?></b> </td>
						<td><input type="text" name="vat" value="<?php echo count($customer) ? $customer['vat'] : ''; ?>" size="30"/></td>
					</tr>
					<tr>
						<td width="200" class="vri-config-param-cell"> <b><?php echo JText::_('VRCUSTOMEREMAIL'); ?> <sup>*</sup></b> </td>
						<td><input type="text" name="email" value="<?php echo count($customer) ? $customer['email'] : ''; ?>" size="30"/></td>
					</tr>
					<tr>
						<td width="200" class="vri-config-param-cell"> <b><?php echo JText::_('VRCUSTOMERPHONE'); ?> <sup>*</sup></b> </td>
						<td><input type="text" name="phone" value="<?php echo count($customer) ? $customer['phone'] : ''; ?>" size="30"/></td>
					</tr>
					<tr>
						<td width="200" class="vri-config-param-cell"> <b><?php echo JText::_('VRCUSTOMERADDRESS'); ?></b> </td>
						<td><input type="text" name="address" value="<?php echo count($customer) ? $customer['address'] : ''; ?>" size="30"/></td>
					</tr>
					<tr>
						<td width="200" class="vri-config-param-cell"> <b><?php echo JText::_('VRCUSTOMERCITY'); ?></b> </td>
						<td><input type="text" name="city" value="<?php echo count($customer) ? $customer['city'] : ''; ?>" size="30"/></td>
					</tr>
					<tr>
						<td width="200" class="vri-config-param-cell"> <b><?php echo JText::_('VRCUSTOMERZIP'); ?></b> </td>
						<td><input type="text" name="zip" value="<?php echo count($customer) ? $customer['zip'] : ''; ?>" size="6"/></td>
					</tr>
					<tr>
						<td width="200" class="vri-config-param-cell"> <b><?php echo JText::_('VRCUSTOMERCOUNTRY'); ?> <sup>*</sup></b> </td>
						<td><?php echo $wselcountries; ?></td>
					</tr>
					<tr>
						<td width="200" class="vri-config-param-cell<?php echo !empty($pcheckin) && !empty($pbid) && empty($customer['gender']) ? ' vri-config-param-cell-warn' : ''; ?>"> <b><?php echo JText::_('VRCUSTOMERGENDER'); ?></b> </td>
						<td>
							<select name="gender">
								<option value=""></option>
								<option value="M"<?php echo count($customer) && $customer['gender'] == 'M' ? ' selected="selected"' : ''; ?>><?php echo JText::_('VRCUSTOMERGENDERM'); ?></option>
								<option value="F"<?php echo count($customer) && $customer['gender'] == 'F' ? ' selected="selected"' : ''; ?>><?php echo JText::_('VRCUSTOMERGENDERF'); ?></option>
							</select>
						</td>
					</tr>
					<tr>
						<td width="200" class="vri-config-param-cell<?php echo count($customer) && !empty($pcheckin) && !empty($pbid) && empty($customer['bdate']) ? ' vri-config-param-cell-warn' : ''; ?>"> <b><?php echo JText::_('VRCUSTOMERBDATE'); ?></b> </td>
						<td><?php echo $vri_app->getCalendar('', 'bdate', 'bdate', $df, array('class'=>'', 'size'=>'10', 'maxlength'=>'19', 'todayBtn' => 'true')); ?></td>
					</tr>
					<tr>
						<td width="200" class="vri-config-param-cell<?php echo count($customer) && !empty($pcheckin) && !empty($pbid) && empty($customer['pbirth']) ? ' vri-config-param-cell-warn' : ''; ?>"> <b><?php echo JText::_('VRCUSTOMERPBIRTH'); ?></b> </td>
						<td><input type="text" name="pbirth" value="<?php echo count($customer) ? $customer['pbirth'] : ''; ?>" size="30"/></td>
					</tr>
					<tr>
						<td width="200" class="vri-config-param-cell"> <b><?php echo JText::_('VRCUSTOMERDOCTYPE'); ?></b> </td>
						<td><input type="text" name="doctype" value="<?php echo count($customer) ? $customer['doctype'] : ''; ?>" size="30"/></td>
					</tr>
					<tr>
						<td width="200" class="vri-config-param-cell"> <b><?php echo JText::_('VRCUSTOMERDOCNUM'); ?></b> </td>
						<td><input type="text" name="docnum" value="<?php echo count($customer) ? $customer['docnum'] : ''; ?>" size="15"/></td>
					</tr>
					<tr>
						<td width="200" class="vri-config-param-cell"> <b><?php echo JText::_('VRCUSTOMERDOCIMG'); ?></b> </td>
						<td>
							<input type="file" name="docimg" id="docimg" size="30" />
							<input type="hidden" name="scandocimg" id="scandocimg" value="" />
							<div class="vri-cur-idscan">
						<?php
						if (count($customer) && !empty($customer['docimg'])) {
							?>
							<i class="vriicn-eye"></i><a href="<?php echo VRI_ADMIN_URI.'resources/idscans/'.$customer['docimg']; ?>" target="_blank"><?php echo $customer['docimg']; ?></a>
							<?php
						}
						?>
							</div>
						</td>
					</tr>
					<tr>
						<td width="200" class="vri-config-param-cell"> <b><?php echo JText::_('VRCUSTOMERPIN'); ?></b> </td>
						<td><input type="text" name="pin" id="pin" value="<?php echo count($customer) ? $customer['pin'] : ''; ?>" size="6" placeholder="54321" /> &nbsp;&nbsp; <button type="button" class="btn" onclick="generatePin();" style="vertical-align: top;"><?php echo JText::_('VRCUSTOMERGENERATEPIN'); ?></button></td>
					</tr>
					<tr>
						<td width="200" class="vri-config-param-cell"> <b>Joomla User</b> </td>
						<td><?php echo JHtmlList::users('ujid', (count($customer) ? $customer['ujid'] : ''), 1); ?></td>
					</tr>
					<tr>
						<td width="200" class="vri-config-param-cell" style="vertical-align: top;"> <b><?php echo JText::_('VRCUSTOMERNOTES'); ?></b> </td>
						<td><textarea cols="80" rows="5" name="notes" style="width: 400px; height: 130px;"><?php echo count($customer) ? htmlspecialchars($customer['notes']) : ''; ?></textarea></td>
					</tr>
				</tbody>
			</table>
		</fieldset>
	</div>
	<?php
if ($ptmpl == 'component') {
	?>
	<input type="hidden" name="tmpl" value="<?php echo $ptmpl; ?>">
	<?php
}
if (!empty($pcheckin) && !empty($pbid)) {
	?>
	<input type="hidden" name="checkin" value="<?php echo $pcheckin; ?>">
	<input type="hidden" name="bid" value="<?php echo $pbid; ?>">
	<?php
}
if (count($customer)) {
	?>
	<input type="hidden" name="where" value="<?php echo $customer['id']; ?>">
	<?php
}
?>
	<input type="hidden" name="task" value="<?php echo count($customer) ? 'updatecustomer' : 'savecustomer'; ?>">
	<input type="hidden" name="option" value="com_vikrentitems">
</form>
