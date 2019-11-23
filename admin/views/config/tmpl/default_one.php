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
JHTML::_('behavior.calendar');
$timeopst=VikRentItems::getTimeOpenStore(true);
if (is_array($timeopst) && $timeopst[0]!=$timeopst[1]) {
	$wtos="<input type=\"checkbox\" name=\"timeopenstorealw\" value=\"yes\"/> ".JText::_('VRIONFIGONEONE')."<br/><br/><b>".JText::_('VRIONFIGONETWO')."</b>:<br/><table><tr><td valign=\"top\">".JText::_('VRIONFIGONETHREE')."</td><td><select name=\"timeopenstorefh\">";
	$openat=VikRentItems::getHoursMinutes($timeopst[0]);
	for($i=0; $i <= 23; $i++) {
		if ($i < 10) {
			$in="0".$i;
		} else {
			$in=$i;
		}
		$stat=($openat[0]==$i ? " selected=\"selected\"" : "");
		$wtos.="<option value=\"".$i."\"".$stat.">".$in."</option>\n";
	}
	$wtos.="</select> <select name=\"timeopenstorefm\">";
	for($i=0; $i <= 59; $i++) {
		if ($i < 10) {
			$in="0".$i;
		} else {
			$in=$i;
		}
		$stat=($openat[1]==$i ? " selected=\"selected\"" : "");
		$wtos.="<option value=\"".$i."\"".$stat.">".$in."</option>\n";
	}
	$wtos.="</select></td></tr><tr><td>".JText::_('VRIONFIGONEFOUR')."</td><td><select name=\"timeopenstoreth\">";
	$closeat=VikRentItems::getHoursMinutes($timeopst[1]);
	for($i=0; $i <= 23; $i++) {
		if ($i < 10) {
			$in="0".$i;
		} else {
			$in=$i;
		}
		$stat=($closeat[0]==$i ? " selected=\"selected\"" : "");
		$wtos.="<option value=\"".$i."\"".$stat.">".$in."</option>\n";
	}
	$wtos.="</select> <select name=\"timeopenstoretm\">";
	for($i=0; $i <= 59; $i++) {
		if ($i < 10) {
			$in="0".$i;
		} else {
			$in=$i;
		}
		$stat=($closeat[1]==$i ? " selected=\"selected\"" : "");
		$wtos.="<option value=\"".$i."\"".$stat.">".$in."</option>\n";
	}
	$wtos.="</select></td></tr></table>";
} else {
	$wtos="<input type=\"checkbox\" name=\"timeopenstorealw\" value=\"yes\" checked=\"checked\"/> ".JText::_('VRIONFIGONEONE')."<br/><br/><b>".JText::_('VRIONFIGONETWO')."</b>:<br/><table><tr><td valign=\"top\">".JText::_('VRIONFIGONETHREE')."</td><td><select name=\"timeopenstorefh\">";
	for($i=0; $i <= 23; $i++) {
		if ($i < 10) {
			$in="0".$i;
		} else {
			$in=$i;
		}
		$wtos.="<option value=\"".$i."\">".$in."</option>\n";
	}
	$wtos.="</select> <select name=\"timeopenstorefm\">";
	for($i=0; $i <= 59; $i++) {
		if ($i < 10) {
			$in="0".$i;
		} else {
			$in=$i;
		}
		$wtos.="<option value=\"".$i."\">".$in."</option>\n";
	}
	$wtos.="</select></td></tr><tr><td>".JText::_('VRIONFIGONEFOUR')."</td><td><select name=\"timeopenstoreth\">";
	for($i=0; $i <= 23; $i++) {
		if ($i < 10) {
			$in="0".$i;
		} else {
			$in=$i;
		}
		$wtos.="<option value=\"".$i."\">".$in."</option>\n";
	}
	$wtos.="</select> <select name=\"timeopenstoretm\">";
	for($i=0; $i <= 59; $i++) {
		if ($i < 10) {
			$in="0".$i;
		} else {
			$in=$i;
		}
		$wtos.="<option value=\"".$i."\">".$in."</option>\n";
	}
	$wtos.="</select></td></tr></table>";
}
$calendartype = VikRentItems::calendarType(true);
$aehourschbasp = VikRentItems::applyExtraHoursChargesBasp();
$nowdf = VikRentItems::getDateFormat(true);
$nowtf = VikRentItems::getTimeFormat(true);
$forcedpickdroptimes = VikRentItems::getForcedPickDropTimes(true);
$forcepickupthsel = "<select name=\"forcepickupth\" style=\"float: none;\">\n";
for($i=0; $i <= 23; $i++) {
	$in = $i < 10 ? "0".$i : $i;
	$forcepickupthsel.="<option value=\"".$i."\"".(is_array($forcedpickdroptimes[0]) && count($forcedpickdroptimes[0]) > 0 && intval($forcedpickdroptimes[0][0]) == $i ? ' selected="selected"' : '').">".$in."</option>\n";
}
$forcepickupthsel .= "</select>\n";
$forcepickuptmsel = "<select name=\"forcepickuptm\" style=\"float: none;\">\n";
for($i=0; $i <= 59; $i++) {
	$in = $i < 10 ? "0".$i : $i;
	$forcepickuptmsel.="<option value=\"".$i."\"".(is_array($forcedpickdroptimes[0]) && count($forcedpickdroptimes[0]) > 0 && intval($forcedpickdroptimes[0][1]) == $i ? ' selected="selected"' : '').">".$in."</option>\n";
}
$forcepickuptmsel .= "</select>\n";
$forcedropoffthsel = "<select name=\"forcedropoffth\" style=\"float: none;\">\n";
for($i=0; $i <= 23; $i++) {
	$in = $i < 10 ? "0".$i : $i;
	$forcedropoffthsel.="<option value=\"".$i."\"".(is_array($forcedpickdroptimes[1]) && count($forcedpickdroptimes[1]) > 0 && intval($forcedpickdroptimes[1][0]) == $i ? ' selected="selected"' : '').">".$in."</option>\n";
}
$forcedropoffthsel .= "</select>\n";
$forcedropofftmsel = "<select name=\"forcedropofftm\" style=\"float: none;\">\n";
for($i=0; $i <= 59; $i++) {
	$in = $i < 10 ? "0".$i : $i;
	$forcedropofftmsel.="<option value=\"".$i."\"".(is_array($forcedpickdroptimes[1]) && count($forcedpickdroptimes[1]) > 0 && intval($forcedpickdroptimes[1][1]) == $i ? ' selected="selected"' : '').">".$in."</option>\n";
}
$forcedropofftmsel .= "</select>\n";
$globclosingdays = VikRentItems::getGlobalClosingDays();
$currentglobclosedays = '';
if (is_array($globclosingdays)) {
	if (count($globclosingdays['singleday']) > 0) {
		foreach ($globclosingdays['singleday'] as $kgcs => $gcdsd) {
			$currentglobclosedays .= '<div id="curglobcsday'.$kgcs.'"><span class="vriconfspanclosed">'.date('Y-m-d', $gcdsd).' ('.JText::_('VRICONFIGCLOSESINGLED').')</span><input type="hidden" name="globalclosingdays[]" value="'.date('Y-m-d', $gcdsd).':1"/><img src="'.VRI_ADMIN_URI.'resources/images/remove.png" class="vriconfrmimgsmall" onclick="removeClosingDay(\'curglobcsday'.$kgcs.'\');"/></div>'."\n";
		}
	}
	if (count($globclosingdays['weekly']) > 0) {
		$weekdaysarr = array(0 => JText::_('VRISUNDAY'), 1 => JText::_('VRIMONDAY'), 2 => JText::_('VRITUESDAY'), 3 => JText::_('VRIWEDNESDAY'), 4 => JText::_('VRITHURSDAY'), 5 => JText::_('VRIFRIDAY'), 6 => JText::_('VRISATURDAY'));
		foreach ($globclosingdays['weekly'] as $kgcw => $gcdwd) {
			$moregcdinfo = getdate($gcdwd);
			$currentglobclosedays .= '<div id="curglobcwday'.$kgcw.'"><span class="vriconfspanclosed">'.date('Y-m-d', $gcdwd).' ('.$weekdaysarr[$moregcdinfo['wday']].')</span><input type="hidden" name="globalclosingdays[]" value="'.date('Y-m-d', $gcdwd).':2"/><img src="'.VRI_ADMIN_URI.'resources/images/remove.png" class="vriconfrmimgsmall" onclick="removeClosingDay(\'curglobcwday'.$kgcw.'\');"/></div>'."\n";
		}
	}
}

$maxdatefuture = VikRentItems::getMaxDateFuture(true);
$maxdate_val = intval(substr($maxdatefuture, 1, (strlen($maxdatefuture) - 1)));
$maxdate_interval = substr($maxdatefuture, -1, 1);

$vrisef = file_exists(VRI_SITE_PATH.DS.'router.php');
?>
<script type="text/javascript">
var _DAYS = new Array();
_DAYS[0] = '<?php echo addslashes(JText::_('VRISUNDAY')); ?>';
_DAYS[1] = '<?php echo addslashes(JText::_('VRIMONDAY')); ?>';
_DAYS[2] = '<?php echo addslashes(JText::_('VRITUESDAY')); ?>';
_DAYS[3] = '<?php echo addslashes(JText::_('VRIWEDNESDAY')); ?>';
_DAYS[4] = '<?php echo addslashes(JText::_('VRITHURSDAY')); ?>';
_DAYS[5] = '<?php echo addslashes(JText::_('VRIFRIDAY')); ?>';
_DAYS[6] = '<?php echo addslashes(JText::_('VRISATURDAY')); ?>';
var daysindxcount = 0;
function addClosingDay() {
	var dayadd = document.getElementById('globdayclose').value;
	var frequency = document.getElementById('vrifrequencyclose').value;
	var freqexpl = '';
	if ( dayadd.length > 0 ) {
		if ( parseInt(frequency) == 1 ) {
			freqexpl = '<?php echo addslashes(JText::_('VRICONFIGCLOSESINGLED')); ?>';
		} else {
			var dateparts = dayadd.split("-");
			var anlzdate = new Date( dateparts[0], (dateparts[1] - 1), dateparts[2] );
			freqexpl = _DAYS[anlzdate.getDay()];
		}
		addHiddenClosingDay(dayadd, frequency, freqexpl);
	}
}
function addHiddenClosingDay(cday, cfreq, cfreqexpl) {
	var ni = document.getElementById('vriglobclosedaysdiv');
	var num = (daysindxcount -1)+ 2;
	daysindxcount = num;
	var newdiv = document.createElement('div');
	var divIdName = 'cday'+num+'Div';
	newdiv.setAttribute('id',divIdName);
	newdiv.innerHTML = '<span class=\'vriconfspanclosed\'>'+cday+' ('+cfreqexpl+')</span><input type=\'hidden\' name=\'globalclosingdays[]\' value=\''+cday+':'+cfreq+'\'/><img src=\'<?php echo VRI_ADMIN_URI; ?>resources/images/remove.png\' class=\'vriconfrmimgsmall\' onclick=\'removeClosingDay("'+divIdName+'");\'/>';
	ni.appendChild(newdiv);
}
function removeClosingDay(idtorm) {
	return (elem=document.getElementById(idtorm)).parentNode.removeChild(elem);
}
function toggleForcePickup() {
	if (document.getElementById('forcepickupt').checked) {
		document.getElementById('forcepickuptdiv').style.display = 'block';
	} else {
		document.getElementById('forcepickuptdiv').style.display = 'none';
	}
}
function toggleForceDropoff() {
	if (document.getElementById('forcedropofft').checked) {
		document.getElementById('forcedropofftdiv').style.display = 'block';
	} else {
		document.getElementById('forcedropofftdiv').style.display = 'none';
	}
}
</script>

<fieldset class="adminform">
	<legend class="adminlegend"><?php echo JText::_('VRICONFIGBOOKINGPART'); ?></legend>
	<table cellspacing="1" class="admintable table">
		<tbody>
			<tr>
				<td width="200" class="vri-config-param-cell"> <b><?php echo JText::_('VRIONFIGONEFIVE'); ?></b> </td>
				<td><?php echo $vri_app->printYesNoButtons('allowrent', JText::_('VRYES'), JText::_('VRNO'), (int)VikRentItems::allowRent(), 1, 0); ?></td>
			</tr>
			<tr>
				<td width="200" class="vri-config-param-cell"> <b><?php echo JText::_('VRIONFIGONESIX'); ?></b> </td>
				<td><textarea name="disabledrentmsg" rows="5" cols="50"><?php echo VikRentItems::getDisabledRentMsg(); ?></textarea></td>
			</tr>
			<tr>
				<td width="200" class="vri-config-param-cell"> <b><?php echo JText::_('VRIONFIGONETENSIX'); ?></b> </td>
				<td><input type="text" name="adminemail" value="<?php echo VikRentItems::getAdminMail(); ?>" size="30"/></td>
			</tr>
			<tr>
				<td width="200" class="vri-config-param-cell"> <b><?php echo JText::_('VRISENDEREMAIL'); ?></b> </td>
				<td><input type="text" name="senderemail" value="<?php echo VikRentItems::getSenderMail(); ?>" size="30"/></td>
			</tr>
			<tr>
				<td width="200" class="vri-config-param-cell"> <b><?php echo JText::_('VRIONFIGONESEVEN'); ?></b> </td>
				<td><?php echo $wtos; ?></td>
			</tr>
			<tr>
				<td width="200" class="vri-config-param-cell"> <b><?php echo JText::_('VRCONFIGFORCEPICKUP'); ?></b> </td>
				<td><input type="checkbox" name="forcepickupt" id="forcepickupt" value="1" onclick="toggleForcePickup();" <?php echo (is_array($forcedpickdroptimes[0]) && count($forcedpickdroptimes[0]) > 0 ? ' checked="checked"' : ''); ?>/><div id="forcepickuptdiv" style="display: <?php echo (is_array($forcedpickdroptimes[0]) && count($forcedpickdroptimes[0]) > 0 ? 'block' : 'none'); ?>;"><?php echo $forcepickupthsel.' : '.$forcepickuptmsel; ?></div></td>
			</tr>
			<tr>
				<td width="200" class="vri-config-param-cell"> <b><?php echo JText::_('VRCONFIGFORCEDROPOFF'); ?></b> </td>
				<td><input type="checkbox" name="forcedropofft" id="forcedropofft" value="1" onclick="toggleForceDropoff();" <?php echo (is_array($forcedpickdroptimes[1]) && count($forcedpickdroptimes[1]) > 0 ? ' checked="checked"' : ''); ?>/><div id="forcedropofftdiv" style="display: <?php echo (is_array($forcedpickdroptimes[1]) && count($forcedpickdroptimes[1]) > 0 ? 'block' : 'none'); ?>;"><?php echo $forcedropoffthsel.' : '.$forcedropofftmsel; ?></div></td>
			</tr>
			<tr>
				<td width="200" class="vri-config-param-cell"> <b><?php echo JText::_('VRIONFIGONEELEVEN'); ?></b> </td>
				<td>
					<select name="dateformat">
						<option value="%d/%m/%Y"<?php echo ($nowdf=="%d/%m/%Y" ? " selected=\"selected\"" : ""); ?>><?php echo JText::_('VRIONFIGONETWELVE'); ?></option>
						<option value="%Y/%m/%d"<?php echo ($nowdf=="%Y/%m/%d" ? " selected=\"selected\"" : ""); ?>><?php echo JText::_('VRIONFIGONETENTHREE'); ?></option>
						<option value="%m/%d/%Y"<?php echo ($nowdf=="%m/%d/%Y" ? " selected=\"selected\"" : ""); ?>><?php echo JText::_('VRIONFIGUSDATEFORMAT'); ?></option>
					</select>
				</td>
			</tr>
			<tr>
				<td width="200" class="vri-config-param-cell"> <b><?php echo JText::_('VRICONFIGTIMEFORMAT'); ?></b> </td>
				<td>
					<select name="timeformat">
						<option value="h:i A"<?php echo ($nowtf=="h:i A" ? " selected=\"selected\"" : ""); ?>><?php echo JText::_('VRICONFIGTIMEFUSA'); ?></option>
						<option value="H:i"<?php echo ($nowtf=="H:i" ? " selected=\"selected\"" : ""); ?>><?php echo JText::_('VRICONFIGTIMEFEUR'); ?></option>
						<option value=""<?php echo (empty($nowtf) ? " selected=\"selected\"" : ""); ?>><?php echo JText::_('VRICONFIGTIMEFNONE'); ?></option>
					</select>
				</td>
			</tr>
			<tr>
				<td width="200" class="vri-config-param-cell"> <b><?php echo JText::_('VRIONFIGONEEIGHT'); ?></b> </td>
				<td><input type="number" step="any" name="hoursmorerentback" value="<?php echo VikRentItems::getHoursMoreRb(); ?>" min="0"/></td>
			</tr>
			<tr>
				<td width="200" class="vri-config-param-cell"> <b><?php echo JText::_('VRIONFIGEHOURSBASP'); ?></b> </td>
				<td>
					<select name="ehourschbasp">
						<option value="1"<?php echo ($aehourschbasp == true ? " selected=\"selected\"" : ""); ?>><?php echo JText::_('VRIONFIGEHOURSBEFORESP'); ?></option>
						<option value="0"<?php echo ($aehourschbasp == false ? " selected=\"selected\"" : ""); ?>><?php echo JText::_('VRIONFIGEHOURSAFTERSP'); ?></option>
					</select>
				</td>
			</tr>
			<tr>
				<td width="200" class="vri-config-param-cell"> <b><?php echo JText::_('VRIONFIGONENINE'); ?></b> </td>
				<td><input type="number" name="hoursmoreitemavail" value="<?php echo VikRentItems::getHoursItemAvail(); ?>" min="0"/> <?php echo JText::_('VRIONFIGONETENEIGHT'); ?></td>
			</tr>
			<tr>
				<td width="200" class="vri-config-param-cell"> <b><?php echo JText::_('VRIPICKONDROP'); ?></b> <?php echo $vri_app->createPopover(array('title' => JText::_('VRIPICKONDROP'), 'content' => JText::_('VRIPICKONDROPHELP'))); ?></td>
				<td><?php echo $vri_app->printYesNoButtons('pickondrop', JText::_('VRYES'), JText::_('VRNO'), (int)VikRentItems::allowPickOnDrop(true), 1, 0); ?></td>
			</tr>
			<tr>
				<td width="200" class="vri-config-param-cell"> <b><?php echo JText::_('VRITODAYBOOKINGS'); ?></b> </td>
				<td><?php echo $vri_app->printYesNoButtons('todaybookings', JText::_('VRYES'), JText::_('VRNO'), (int)VikRentItems::todayBookings(), 1, 0); ?></td>
			</tr>
			<tr>
				<td width="200" class="vri-config-param-cell"> <b><?php echo JText::_('VRIONFIGONECOUPONS'); ?></b> </td>
				<td><?php echo $vri_app->printYesNoButtons('enablecoupons', JText::_('VRYES'), JText::_('VRNO'), (int)VikRentItems::couponsEnabled(), 1, 0); ?></td>
			</tr>
			<tr>
				<td width="200" class="vri-config-param-cell"> <b><?php echo JText::_('VRCONFIGENABLECUSTOMERPIN'); ?></b> </td>
				<td><?php echo $vri_app->printYesNoButtons('enablepin', JText::_('VRYES'), JText::_('VRNO'), (int)VikRentItems::customersPinEnabled(), 1, 0); ?></td>
			</tr>
			<tr>
				<td width="200" class="vri-config-param-cell"> <b><?php echo JText::_('VRIONFIGONETENFIVE'); ?></b> </td>
				<td><?php echo $vri_app->printYesNoButtons('tokenform', JText::_('VRYES'), JText::_('VRNO'), (VikRentItems::tokenForm() ? 'yes' : 0), 'yes', 0); ?></td>
			</tr>
			<tr>
				<td width="200" class="vri-config-param-cell"> <b><?php echo JText::_('VRIONFIGREQUIRELOGIN'); ?></b> </td>
				<td><?php echo $vri_app->printYesNoButtons('requirelogin', JText::_('VRYES'), JText::_('VRNO'), (int)VikRentItems::requireLogin(), 1, 0); ?></td>
			</tr>
			<tr>
				<td width="200" class="vri-config-param-cell"> <b><?php echo JText::_('VRIICALKEY'); ?></b> </td>
				<td><input type="text" name="icalkey" value="<?php echo VikRentItems::getIcalSecretKey(); ?>" size="10"/></td>
			</tr>
			<tr>
				<td width="200" class="vri-config-param-cell"> <b><?php echo JText::_('VRIONFIGONETENSEVEN'); ?></b> </td>
				<td><input type="number" name="minuteslock" value="<?php echo VikRentItems::getMinutesLock(); ?>" min="0"/></td>
			</tr>
		</tbody>
	</table>
</fieldset>

<fieldset class="adminform">
	<legend class="adminlegend"><?php echo JText::_('VRICONFIGSEARCHPART'); ?></legend>
	<table cellspacing="1" class="admintable table">
		<tbody>
			<tr>
				<td width="200" class="vri-config-param-cell"> <b><?php echo JText::_('VRCONFIGONEDROPDPLUS'); ?></b> </td>
				<td><input type="number" name="setdropdplus" value="<?php echo VikRentItems::setDropDatePlus(true); ?>" min="0"/></td>
			</tr>
			<tr>
				<td width="200" class="vri-config-param-cell"> <b><?php echo JText::_('VRCONFIGMINDAYSADVANCE'); ?></b> </td>
				<td><input type="number" name="mindaysadvance" value="<?php echo VikRentItems::getMinDaysAdvance(true); ?>" min="0"/></td>
			</tr>
			<tr>
				<td width="200" class="vri-config-param-cell"> <b><?php echo JText::_('VRCONFIGMAXDATEFUTURE'); ?></b> </td>
				<td><input type="number" name="maxdate" value="<?php echo $maxdate_val; ?>" min="0" style="float: none; vertical-align: top; max-width: 50px;"/> <select name="maxdateinterval" style="float: none; margin-bottom: 0;"><option value="d"<?php echo $maxdate_interval == 'd' ? ' selected="selected"' : ''; ?>><?php echo JText::_('VRCONFIGMAXDATEDAYS'); ?></option><option value="w"<?php echo $maxdate_interval == 'w' ? ' selected="selected"' : ''; ?>><?php echo JText::_('VRCONFIGMAXDATEWEEKS'); ?></option><option value="m"<?php echo $maxdate_interval == 'm' ? ' selected="selected"' : ''; ?>><?php echo JText::_('VRCONFIGMAXDATEMONTHS'); ?></option><option value="y"<?php echo $maxdate_interval == 'y' ? ' selected="selected"' : ''; ?>><?php echo JText::_('VRCONFIGMAXDATEYEARS'); ?></option></select></td>
			</tr>
			<tr>
				<td width="200" class="vri-config-param-cell"> <b><?php echo JText::_('VRICONFIGGLOBCLOSEDAYS'); ?></b> </td>
				<td><?php echo JHTML::_('calendar', '', 'globdayclose', 'globdayclose', '%Y-%m-%d', array('class'=>'', 'size'=>'8',  'maxlength'=>'8', 'todayBtn' => 'true')); ?> <select style="float: none;" id="vrifrequencyclose"><option value="1"><?php echo JText::_('VRICONFIGCLOSESINGLED'); ?></option><option value="2"><?php echo JText::_('VRICONFIGCLOSEWEEKLY'); ?></option></select> <button type="button" class="btn" onclick="addClosingDay();" style="margin-bottom: 9px;"><?php echo JText::_('VRICONFIGADDCLOSEDAY'); ?></button><div id="vriglobclosedaysdiv" style="display: block;"><?php echo $currentglobclosedays; ?></div></td>
			</tr>
			<tr>
				<td width="200" class="vri-config-param-cell"> <b><?php echo JText::_('VRIONFIGONETEN'); ?></b> </td>
				<td><?php echo $vri_app->printYesNoButtons('placesfront', JText::_('VRYES'), JText::_('VRNO'), (VikRentItems::showPlacesFront(true) ? 'yes' : 0), 'yes', 0); ?></td>
			</tr>
			<tr>
				<td width="200" class="vri-config-param-cell"> <b><?php echo JText::_('VRIONFIGONETENFOUR'); ?></b> </td>
				<td><?php echo $vri_app->printYesNoButtons('showcategories', JText::_('VRYES'), JText::_('VRNO'), (VikRentItems::showCategoriesFront(true) ? 'yes' : 0), 'yes', 0); ?></td>
			</tr>
		</tbody>
	</table>
</fieldset>

<fieldset class="adminform">
	<legend class="adminlegend"><?php echo JText::_('VRICONFIGSYSTEMPART'); ?></legend>
	<table cellspacing="1" class="admintable table">
		<tbody>
			<tr>
				<td width="200" class="vri-config-param-cell"> <b><?php echo JText::_('VRICONFENMULTILANG'); ?></b> </td>
				<td><?php echo $vri_app->printYesNoButtons('multilang', JText::_('VRYES'), JText::_('VRNO'), (int)VikRentItems::allowMultiLanguage(), 1, 0); ?></td>
			</tr>
			<tr>
				<td width="200" class="vri-config-param-cell"> <b><?php echo JText::_('VRICONFSEFROUTER'); ?></b> </td>
				<td><?php echo $vri_app->printYesNoButtons('vrisef', JText::_('VRYES'), JText::_('VRNO'), (int)$vrisef, 1, 0); ?></td>
			</tr>
			<tr>
				<td width="200" class="vri-config-param-cell"> <b><?php echo JText::_('VRILOADFA'); ?></b> </td>
				<td><?php echo $vri_app->printYesNoButtons('usefa', JText::_('VRYES'), JText::_('VRNO'), (int)VikRentItems::isFontAwesomeEnabled(true), 1, 0); ?></td>
			</tr>
			<tr>
				<td width="200" class="vri-config-param-cell"> <b><?php echo JText::_('VRIONFIGONEJQUERY'); ?></b> </td>
				<td><?php echo $vri_app->printYesNoButtons('loadjquery', JText::_('VRYES'), JText::_('VRNO'), (VikRentItems::loadJquery(true) ? 'yes' : 0), 'yes', 0); ?></td>
			</tr>
			<tr>
				<td width="200" class="vri-config-param-cell"> <b><?php echo JText::_('VRIONFIGONECALENDAR'); ?></b> </td>
				<td>
					<select name="calendar">
						<option value="jqueryui"<?php echo ($calendartype == "jqueryui" ? " selected=\"selected\"" : ""); ?>>jQuery UI</option>
						<option value="joomla"<?php echo ($calendartype == "joomla" ? " selected=\"selected\"" : ""); ?>>Joomla</option>
					</select>
				</td>
			</tr>
			<tr>
				<td width="200" class="vri-config-param-cell"> <b>Google Maps API Key</b> </td>
				<td><input type="text" name="gmapskey" value="<?php echo VikRentItems::getGoogleMapsKey(); ?>" size="30" /></td>
			</tr>
		</tbody>
	</table>
</fieldset>
