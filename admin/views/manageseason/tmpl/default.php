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
$wpricesel = $this->wpricesel;
$wlocsel = $this->wlocsel;

$vri_app = VikRentItems::getVriApplication();
$editor = JEditor::getInstance(JFactory::getApplication()->get('editor'));
if (strlen($wsel) > 0) {
	JHTML::_('behavior.calendar');
	$caldf = VikRentItems::getDateFormat(true);
	$currencysymb = VikRentItems::getCurrencySymb(true);
	if ($caldf == "%d/%m/%Y") {
		$df = 'd/m/Y';
	} elseif ($caldf == "%m/%d/%Y") {
		$df = 'm/d/Y';
	} else {
		$df = 'Y/m/d';
	}
	if (count($row) && ($row['from'] > 0 || $row['to'] > 0)) {
		$nowyear = !empty($row['year']) ? $row['year'] : date('Y');
		$frombase = mktime(0, 0, 0, 1, 1, $nowyear);
		$fromdate = date($df, ($frombase + $row['from']));
		if ($row['to'] < $row['from']) {
			$nowyear = $nowyear + 1;
			$frombase = mktime(0, 0, 0, 1, 1, $nowyear);
		}
		$todate = date($df, ($frombase + $row['to']));
		//leap years
		$checkly = !empty($row['year']) ? $row['year'] : date('Y');
		if ($checkly % 4 == 0 && ($checkly % 100 != 0 || $checkly % 400 == 0)) {
			$frombase = mktime(0, 0, 0, 1, 1, $checkly);
			$infoseason = getdate($frombase + $row['from']);
			$leapts = mktime(0, 0, 0, 2, 29, $infoseason['year']);
			if ($infoseason[0] >= $leapts) {
				$fromdate = date($df, ($frombase + $row['from'] + 86400));
				$frombase = mktime(0, 0, 0, 1, 1, $nowyear);
				$todate = date($df, ($frombase + $row['to'] + 86400));
			}
		}
		//
	} else {
		$fromdate = '';
		$todate = '';
	}
	$actweekdays = count($row) ? explode(";", $row['wdays']) : array();
	
	$actvalueoverrides = '';
	if (count($row) && strlen($row['losoverride']) > 0) {
		$losoverrides = explode('_', $row['losoverride']);
		foreach ($losoverrides as $loso) {
			if (!empty($loso)) {
				$losoparts = explode(':', $loso);
				$losoparts[2] = strstr($losoparts[0], '-i') != false ? 1 : 0;
				$losoparts[0] = str_replace('-i', '', $losoparts[0]);
				$actvalueoverrides .= '<p>'.JText::_('VRNEWSEASONNIGHTSOVR').' <input type="text" name="nightsoverrides[]" value="'.$losoparts[0].'" size="4"/> <select name="andmoreoverride[]"><option value="0">-------</option><option value="1"'.($losoparts[2] == 1 ? ' selected="selected"' : '').'>'.JText::_('VRNEWSEASONVALUESOVREMORE').'</option></select> - '.JText::_('VRNEWSEASONVALUESOVR').' <input type="text" name="valuesoverrides[]" value="'.$losoparts[1].'" size="5"/> '.(intval($row['val_pcent']) == 2 ? '%' : $currencysymb).'</p>';
			}
		}
	}
	
	?>
	<script type="text/javascript">
	function addMoreOverrides() {
		var sel = document.getElementById('val_pcent');
		var curpcent = sel.options[sel.selectedIndex].text;
		var ni = document.getElementById('myDiv');
		var numi = document.getElementById('morevalueoverrides');
		var num = (document.getElementById('morevalueoverrides').value -1)+ 2;
		numi.value = num;
		var newdiv = document.createElement('div');
		var divIdName = 'my'+num+'Div';
		newdiv.setAttribute('id',divIdName);
		newdiv.innerHTML = '<p><?php echo addslashes(JText::_('VRNEWSEASONNIGHTSOVR')); ?> <input type=\'text\' name=\'nightsoverrides[]\' value=\'\' size=\'4\'/> <select name=\'andmoreoverride[]\'><option value=\'0\'>-------</option><option value=\'1\'><?php echo addslashes(JText::_('VRNEWSEASONVALUESOVREMORE')); ?></option></select> - <?php echo addslashes(JText::_('VRNEWSEASONVALUESOVR')); ?> <input type=\'text\' name=\'valuesoverrides[]\' value=\'\' size=\'5\'/> '+curpcent+'</p>';
		ni.appendChild(newdiv);
	}
	jQuery(document).ready(function() {
		jQuery(".vri-select-all").click(function() {
			jQuery(this).next("select").find("option").prop('selected', true);
		});
	});
	function togglePromotion() {
		var promo_on = document.getElementById('promo').checked;
		if (promo_on === true) {
			jQuery('.promotr').fadeIn();
			var cur_startd = jQuery('#from').val();
			jQuery('#promovalidity span').text('');
			if (cur_startd.length) {
				jQuery('#promovalidity span').text(' ('+cur_startd+')');
			}
		} else {
			jQuery('.promotr').fadeOut();
		}
	}
	</script>
	<input type="hidden" value="0" id="morevalueoverrides" />
	
	<form name="adminForm" id="adminForm" action="index.php" method="post">
		<fieldset class="adminform fieldset-left">
			<legend class="adminlegend"><?php echo JText::_('VRISEASON'); ?> &nbsp;&nbsp;<?php echo $vri_app->createPopover(array('title' => JText::_('VRISPRICESHELPTITLE'), 'content' => JText::_('VRISPRICESHELP'))); ?></legend>
			<table cellspacing="1" class="admintable table">
				<tbody>
					<tr>
						<td width="200" class="vri-config-param-cell"> <b><?php echo JText::_('VRNEWSEASONONE'); ?></b> </td>
						<td><?php echo JHTML::_('calendar', $fromdate, 'from', 'from', $caldf, array('class'=>'', 'size'=>'10',  'maxlength'=>'19', 'todayBtn' => 'true')); ?></td>
					</tr>
					<tr>
						<td width="200" class="vri-config-param-cell"> <b><?php echo JText::_('VRNEWSEASONTWO'); ?></b> </td>
						<td><?php echo JHTML::_('calendar', $todate, 'to', 'to', $caldf, array('class'=>'', 'size'=>'10',  'maxlength'=>'19', 'todayBtn' => 'true')); ?></td>
					</tr>
					<tr>
						<td width="200" class="vri-config-param-cell"> <b><?php echo JText::_('VRISPONLYPICKINCL'); ?></b> </td>
						<td><?php echo $vri_app->printYesNoButtons('pickupincl', JText::_('VRYES'), JText::_('VRNO'), (count($row) ? (int)$row['pickupincl'] : 0), 1, 0); ?></td>
					</tr>
					<tr>
						<td width="200" class="vri-config-param-cell"> <b><?php echo JText::_('VRISPKEEPFIRSTDAYRATE'); ?></b> &nbsp;&nbsp;<?php echo $vri_app->createPopover(array('title' => JText::_('VRISPKEEPFIRSTDAYRATE'), 'content' => JText::_('VRISPKEEPFIRSTDAYRATEHELP'))); ?></td>
						<td><?php echo $vri_app->printYesNoButtons('keepfirstdayrate', JText::_('VRYES'), JText::_('VRNO'), (count($row) ? (int)$row['keepfirstdayrate'] : 0), 1, 0); ?></td>
					</tr>
					<tr>
						<td width="200" class="vri-config-param-cell"> <b><?php echo JText::_('VRISPYEARTIED'); ?></b> </td>
						<td><?php echo $vri_app->printYesNoButtons('yeartied', JText::_('VRYES'), JText::_('VRNO'), (count($row) && !empty($row['year']) ? 1 : 0), 1, 0); ?></td>
					</tr>
				</tbody>
			</table>
		</fieldset>

		<fieldset class="adminform fieldset-left">
			<legend class="adminlegend"><?php echo JText::_('VRIWEEKDAYS'); ?></legend>
			<table cellspacing="1" class="admintable table">
				<tbody>
					<tr>
						<td width="200" class="vri-config-param-cell" style="vertical-align: top;"> <b><?php echo JText::_('VRISEASONDAYS'); ?></b> </td>
						<td><select multiple="multiple" size="7" name="wdays[]"><option value="0"<?php echo (in_array("0", $actweekdays) ? " selected=\"selected\"" : ""); ?>><?php echo JText::_('VRISUNDAY'); ?></option><option value="1"<?php echo (in_array("1", $actweekdays) ? " selected=\"selected\"" : ""); ?>><?php echo JText::_('VRIMONDAY'); ?></option><option value="2"<?php echo (in_array("2", $actweekdays) ? " selected=\"selected\"" : ""); ?>><?php echo JText::_('VRITUESDAY'); ?></option><option value="3"<?php echo (in_array("3", $actweekdays) ? " selected=\"selected\"" : ""); ?>><?php echo JText::_('VRIWEDNESDAY'); ?></option><option value="4"<?php echo (in_array("4", $actweekdays) ? " selected=\"selected\"" : ""); ?>><?php echo JText::_('VRITHURSDAY'); ?></option><option value="5"<?php echo (in_array("5", $actweekdays) ? " selected=\"selected\"" : ""); ?>><?php echo JText::_('VRIFRIDAY'); ?></option><option value="6"<?php echo (in_array("6", $actweekdays) ? " selected=\"selected\"" : ""); ?>><?php echo JText::_('VRISATURDAY'); ?></option></select></td>
					</tr>
				</tbody>
			</table>
		</fieldset>

		<br clear="all" />

		<fieldset class="adminform">
			<legend class="adminlegend"><?php echo JText::_('VRISEASONPRICING'); ?></legend>
			<table cellspacing="1" class="admintable table">
				<tbody>
					<tr>
						<td width="200" class="vri-config-param-cell"> <b><?php echo JText::_('VRISPNAME'); ?></b> </td>
						<td><input type="text" name="spname" value="<?php echo count($row) ? htmlspecialchars($row['spname']) : ''; ?>" size="30"/></td>
					</tr>
					<tr>
						<td width="200" class="vri-config-param-cell"> <b><?php echo JText::_('VRNEWSEASONTHREE'); ?></b> </td>
						<td><select name="type"><option value="1"<?php echo (count($row) && intval($row['type']) == 1 ? " selected=\"selected\"" : ""); ?>><?php echo JText::_('VRNEWSEASONSIX'); ?></option><option value="2"<?php echo (count($row) && intval($row['type']) == 2 ? " selected=\"selected\"" : ""); ?>><?php echo JText::_('VRNEWSEASONSEVEN'); ?></option></select></td>
					</tr>
					<tr>
						<td width="200" class="vri-config-param-cell"> <b><?php echo JText::_('VRNEWSEASONFOUR'); ?></b> </td>
						<td><input type="number" step="any" name="diffcost" value="<?php echo count($row) ? $row['diffcost'] : ''; ?>" size="5"/> <select name="val_pcent" id="val_pcent"><option value="2"<?php echo (count($row) && intval($row['val_pcent']) == 2 ? " selected=\"selected\"" : ""); ?>>%</option><option value="1"<?php echo (count($row) && intval($row['val_pcent']) == 1 ? " selected=\"selected\"" : ""); ?>><?php echo $currencysymb; ?></option></select> &nbsp;<?php echo $vri_app->createPopover(array('title' => JText::_('VRNEWSEASONFOUR'), 'content' => JText::_('VRSPECIALPRICEVALHELP'))); ?></td>
					</tr>
					<tr>
						<td width="200" class="vri-config-param-cell"> <b><?php echo JText::_('VRNEWSEASONVALUEOVERRIDE'); ?></b> <?php echo $vri_app->createPopover(array('title' => JText::_('VRNEWSEASONVALUEOVERRIDE'), 'content' => JText::_('VRNEWSEASONVALUEOVERRIDEHELP'))); ?> </td>
						<td><div id="myDiv" style="display: block;"><?php echo $actvalueoverrides; ?></div><a href="javascript: void(0);" onclick="addMoreOverrides();"><?php echo JText::_('VRNEWSEASONADDOVERRIDE'); ?></a></td>
					</tr>
					<tr>
						<td width="200" class="vri-config-param-cell"> <b><?php echo JText::_('VRNEWSEASONROUNDCOST'); ?></b> </td>
						<td><select name="roundmode"><option value=""><?php echo JText::_('VRNEWSEASONROUNDCOSTNO'); ?></option><option value="PHP_ROUND_HALF_UP"<?php echo (count($row) && $row['roundmode'] == 'PHP_ROUND_HALF_UP' ? ' selected="selected"' : ''); ?>><?php echo JText::_('VRNEWSEASONROUNDCOSTUP'); ?></option><option value="PHP_ROUND_HALF_DOWN"<?php echo (count($row) && $row['roundmode'] == 'PHP_ROUND_HALF_DOWN' ? ' selected="selected"' : ''); ?>><?php echo JText::_('VRNEWSEASONROUNDCOSTDOWN'); ?></option></select></td>
					</tr>
					<tr>
						<td width="200" class="vri-config-param-cell"> <b><?php echo JText::_('VRNEWSEASONFIVE'); ?></b> </td>
						<td><span class="vri-select-all"><?php echo JText::_('VRISELECTALL'); ?></span><?php echo $wsel; ?></td>
					</tr>
					<tr>
						<td width="200" class="vri-config-param-cell"> <b><?php echo JText::_('VRISPTYPESPRICE'); ?></b> </td>
						<td><span class="vri-select-all"><?php echo JText::_('VRISELECTALL'); ?></span><?php echo $wpricesel; ?></td>
					</tr>
					<tr>
						<td width="200" class="vri-config-param-cell"> <b><?php echo JText::_('VRNEWSEASONEIGHT'); ?></b> </td>
						<td><?php echo $wlocsel; ?></td>
					</tr>
				</tbody>
			</table>
		</fieldset>

		<fieldset class="adminform">
			<legend class="adminlegend"><?php echo JText::_('VRISPPROMOTIONLABEL'); ?></legend>
			<table cellspacing="1" class="admintable table">
				<tbody>
					<tr>
						<td width="200" class="vri-config-param-cell"> <b><?php echo JText::_('VRIISPROMOTION'); ?></b> </td>
						<td><input type="checkbox" id="promo" name="promo" value="1" onclick="togglePromotion();" <?php echo count($row) && $row['promo'] == 1 ? "checked=\"checked\"" : ""; ?>/></td>
					</tr>
					<tr class="promotr">
						<td width="200" class="vri-config-param-cell"> <b><?php echo JText::_('VRIPROMOVALIDITY'); ?></b> </td>
						<td><input type="text" name="promodaysadv" value="<?php echo !count($row) || empty($row['promodaysadv']) ? '0' : $row['promodaysadv']; ?>" size="5"/><span id="promovalidity"><?php echo JText::_('VRIPROMOVALIDITYDAYSADV'); ?><span></span></span></td>
					</tr>
					<tr class="promotr">
						<td width="200" class="vri-config-param-cell"> <b><?php echo JText::_('VRIPROMOTEXT'); ?></b> </td>
						<td><?php echo $editor->display( "promotxt", (count($row) ? $row['promotxt'] : ''), 400, 200, 70, 20 ); ?></td>
					</tr>
				</tbody>
			</table>
		</fieldset>

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
	<script type="text/javascript">
	jQuery(document).ready(function() {
		jQuery('#from').val('<?php echo $fromdate; ?>').attr('data-alt-value', '<?php echo $fromdate; ?>');
		jQuery('#to').val('<?php echo $todate; ?>').attr('data-alt-value', '<?php echo $todate; ?>');
	});
	togglePromotion();
	</script>
	<?php
} else {
	?>
	<p class="err"><a href="index.php?option=com_vikrentitems&amp;task=newitem"><?php echo JText::_('VRNOITEMSFOUNDSEASONS'); ?></a></p>
	<form action="index.php?option=com_vikrentitems" method="post" name="adminForm" id="adminForm">
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="option" value="com_vikrentitems" />
	</form>
	<?php
}