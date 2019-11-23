<?php
/**
 * @package     VikRentItems
 * @subpackage  com_vikrentitems
 * @author      Alessio Gaggii - e4j - Extensionsforjoomla.com
 * @copyright   Copyright (C) 2018 e4j - Extensionsforjoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 * @link        https://e4j.com
 */

defined('_JEXEC') OR die('Restricted Area');

$vri_tn = VikRentItems::getTranslator();
if (VikRentItems::allowRent()) {
	$session = JFactory::getSession();
	$svriplace = $session->get('vriplace', '');
	$indvriplace = 0;
	$svrireturnplace = $session->get('vrireturnplace', '');
	$indvrireturnplace = 0;
	$dbo = JFactory::getDBO();
	$timeslots = VikRentItems::loadGlobalTimeSlots($vri_tn);
	//vikrentitems 1.1
	$calendartype = VikRentItems::calendarType();
	$document = JFactory::getDocument();
	//load jQuery lib e jQuery UI
	if (VikRentItems::loadJquery()) {
		JHtml::_('jquery.framework', true, true);
		JHtml::_('script', VRI_SITE_URI.'resources/jquery-1.12.4.min.js', false, true, false, false);
	}
	if ($calendartype == "jqueryui") {
		$document->addStyleSheet(VRI_SITE_URI.'resources/jquery-ui.min.css');
		//load jQuery UI
		JHtml::_('script', VRI_SITE_URI.'resources/jquery-ui.min.js', false, true, false, false);
	}
	$document->addStyleSheet(VRI_SITE_URI.'resources/jquery.fancybox.css');
	JHtml::_('script', VRI_SITE_URI.'resources/jquery.fancybox.js', false, true, false, false);
	//
	$ppickup = VikRequest::getInt('pickup', '', 'request');
	$preturn = VikRequest::getInt('return', '', 'request');
	$pitemid = VikRequest::getInt('Itemid', '', 'request');
	$pval = "";
	$rval = "";
	$vridateformat = VikRentItems::getDateFormat();
	$nowtf = VikRentItems::getTimeFormat();
	if ($vridateformat == "%d/%m/%Y") {
		$df = 'd/m/Y';
	} elseif ($vridateformat == "%m/%d/%Y") {
		$df = 'm/d/Y';
	} else {
		$df = 'Y/m/d';
	}
	if (!empty($ppickup)) {
		$dp = date($df, $ppickup);
		if (VikRentItems::dateIsValid($dp)) {
			$pval = $dp;
		}
	}
	if (!empty($preturn)) {
		$dr = date($df, $preturn);
		if (VikRentItems::dateIsValid($dr)) {
			$rval = $dr;
		}
	}
	$coordsplaces = array();
	$selform = "<div class=\"vridivsearch vri-main-search-form\"><form action=\"".JRoute::_('index.php?option=com_vikrentitems')."\" method=\"get\"><div class=\"vricalform\">\n";
	$selform .= "<input type=\"hidden\" name=\"option\" value=\"com_vikrentitems\"/>\n";
	$selform .= "<input type=\"hidden\" name=\"task\" value=\"search\"/>\n";
	$diffopentime = false;
	$closingdays = array();
	$declclosingdays = '';
	$declglobclosingdays = '';
	$globalclosingdays = VikRentItems::getGlobalClosingDays();
	if (is_array($globalclosingdays)) {
		if (count($globalclosingdays['singleday']) > 0) {
			$gscdarr = array();
			foreach ($globalclosingdays['singleday'] as $kgcs => $gcdsd) {
				$gscdarr[] = '"'.date('Y-n-j', $gcdsd).'"';
			}
			$gscdarr = array_unique($gscdarr);
			$declglobclosingdays .= 'var vriglobclosingsdays = ['.implode(", ", $gscdarr).'];'."\n";
		} else {
			$declglobclosingdays .= 'var vriglobclosingsdays = ["-1"];'."\n";
		}
		if (count($globalclosingdays['weekly']) > 0) {
			$gwcdarr = array();
			foreach ($globalclosingdays['weekly'] as $kgcw => $gcdwd) {
				$moregcdinfo = getdate($gcdwd);
				$gwcdarr[] = '"'.$moregcdinfo['wday'].'"';
			}
			$gwcdarr = array_unique($gwcdarr);
			$declglobclosingdays .= 'var vriglobclosingwdays = ['.implode(", ", $gwcdarr).'];'."\n";
		} else {
			$declglobclosingdays .= 'var vriglobclosingwdays = ["-1"];'."\n";
		}
		$declglobclosingdays .= '
jQuery.noConflict();
function vriGlobalClosingDays(date) {
	var gdmy = date.getFullYear() + "-" + (date.getMonth() + 1) + "-" + date.getDate();
	var gwd = date.getDay();
	gwd = gwd.toString();
	var checksdarr = window["vriglobclosingsdays"];
	var checkwdarr = window["vriglobclosingwdays"];
	if (jQuery.inArray(gdmy, checksdarr) == -1 && jQuery.inArray(gwd, checkwdarr) == -1) {
		return [true, ""];
	} else {
		return [false, "", "'.addslashes(JText::_('VRIGLOBDAYCLOSED')).'"];
	}
}';
		$document->addScriptDeclaration($declglobclosingdays);
	}

	$vrisessioncart = $session->get('vriCart', '');
	$vrisesspickup = $session->get('vripickupts', '');
	$vrisessdropoff = $session->get('vrireturnts', '');
	$vrisessdays = $session->get('vridays', '');
	$vrisesspickuploc = $session->get('vriplace', '');
	$vrisessdropoffloc = $session->get('vrireturnplace', '');

	if (is_array($vrisessioncart) && count($vrisessioncart) > 0) {
		$selform .= "<div class=\"vrisfentry vri-search-sessvals\"><label class=\"vripickdroplab\">" . JText::_('VRPICKUPITEM') . "</label><span class=\"vridtsp\"><input type=\"hidden\" name=\"pickupdate\" value=\"".date($df, $vrisesspickup)."\"/>".date($df, $vrisesspickup)." " . JText::_('VRALLE') . " <input type=\"hidden\" name=\"pickuph\" value=\"".date('H', $vrisesspickup)."\"/>".date('H', $vrisesspickup).":<input type=\"hidden\" name=\"pickupm\" value=\"".date('i', $vrisesspickup)."\"/>".date('i', $vrisesspickup)."</span></div>\n";
		$selform .= "<div class=\"vrisfentry vri-search-sessvals\"><label class=\"vripickdroplab\">" . JText::_('VRRETURNITEM') . "</label><span class=\"vridtsp\"><input type=\"hidden\" name=\"releasedate\" value=\"".date($df, $vrisessdropoff)."\"/>".date($df, $vrisessdropoff)." " . JText::_('VRALLE') . " <input type=\"hidden\" name=\"releaseh\" value=\"".date('H', $vrisessdropoff)."\"/>".date('H', $vrisessdropoff).":<input type=\"hidden\" name=\"releasem\" value=\"".date('i', $vrisessdropoff)."\"/>".date('i', $vrisessdropoff)."</span></div>";
		$selform .= "<div class=\"vrisearchemptycartdiv\"><a href=\"".JRoute::_('index.php?option=com_vikrentitems&task=emptycart&search=1')."\" class=\"btn\">".JText::_('VRIEMPTYCARTCHANGEDATES')."</a></div>\n";
	}

	if (VikRentItems::showPlacesFront()) {
		$q = "SELECT * FROM `#__vikrentitems_places` ORDER BY `#__vikrentitems_places`.`name` ASC;";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$places = $dbo->loadAssocList();
			$vri_tn->translateContents($places, '#__vikrentitems_places');
			//check if some place has a different opening time (1.1)
			foreach ($places as $kpla=>$pla) {
				if (!empty($pla['opentime'])) {
					$diffopentime = true;
				}
				//check if some place has closing days
				if (!empty($pla['closingdays'])) {
					$closingdays[$pla['id']] = $pla['closingdays'];
				}
				if (!empty($svriplace) && !empty($svrireturnplace)) {
					if ($pla['id'] == $svriplace) {
						$indvriplace = $kpla;
					}
					if ($pla['id'] == $svrireturnplace) {
						$indvrireturnplace = $kpla;
					}
				}
			}
			//locations closing days (1.1)
			if (count($closingdays) > 0) {
				foreach ($closingdays as $idpla => $clostr) {
					$jsclosingdstr = VikRentItems::formatLocationClosingDays($clostr);
					if (count($jsclosingdstr) > 0) {
						$declclosingdays .= 'var loc'.$idpla.'closingdays = ['.implode(", ", $jsclosingdstr).'];'."\n";
					}
				}
			}
			$onchangeplaces = $diffopentime == true ? " onchange=\"javascript: vriSetLocOpenTime(this.value, 'pickup');\"" : "";
			$onchangeplacesdrop = $diffopentime == true ? " onchange=\"javascript: vriSetLocOpenTime(this.value, 'dropoff');\"" : "";
			if ($diffopentime == true) {
				$onchangedecl = '
jQuery.noConflict();
function vriSetLocOpenTime(loc, where) {
	jQuery.ajax({
		type: "POST",
		url: "'.JRoute::_('index.php?option=com_vikrentitems&task=ajaxlocopentime&tmpl=component').'",
		data: { idloc: loc, pickdrop: where }
	}).done(function(res) {
		var vriobj = jQuery.parseJSON(res);
		if (where == "pickup") {
			jQuery("#vricomselph").html(vriobj.hours);
			jQuery("#vricomselpm").html(vriobj.minutes);
		} else {
			jQuery("#vricomseldh").html(vriobj.hours);
			jQuery("#vricomseldm").html(vriobj.minutes);
		}
	});
}';
				$document->addScriptDeclaration($onchangedecl);
			}
			//end check if some place has a different opningtime (1.1)
			$selform .= "<div class=\"vrisfentry\"><label for=\"place\">" . JText::_('VRPPLACE') . "</label><span class=\"vriplacesp\"><select name=\"place\" id=\"place\"".$onchangeplaces.">";
			foreach ($places as $pla) {
				$selform .= "<option value=\"" . $pla['id'] . "\" id=\"place".$pla['id']."\"".(!empty($svriplace) && $svriplace == $pla['id'] ? " selected=\"selected\"" : "").">" . $pla['name'] . "</option>\n";
				if (!empty($pla['lat']) && !empty($pla['lng'])) {
					$coordsplaces[] = $pla;
				}
			}
			$selform .= "</select></span></div>\n";
		}
	}
	
	if ($diffopentime == true && is_array($places) && strlen($places[$indvriplace]['opentime']) > 0) {
		$parts = explode("-", $places[$indvriplace]['opentime']);
		if (is_array($parts) && $parts[0] != $parts[1]) {
			$opent = VikRentItems::getHoursMinutes($parts[0]);
			$closet = VikRentItems::getHoursMinutes($parts[1]);
			$i = $opent[0];
			$imin = $opent[1];
			$j = $closet[0];
		} else {
			$i = 0;
			$imin = 0;
			$j = 23;
		}
		//change dates drop off location opening time (1.1)
		$iret = $i;
		$iminret = $imin;
		$jret = $j;
		if ($indvriplace != $indvrireturnplace) {
			if (strlen($places[$indvrireturnplace]['opentime']) > 0) {
				//different opening time for drop off location
				$parts = explode("-", $places[$indvrireturnplace]['opentime']);
				if (is_array($parts) && $parts[0] != $parts[1]) {
					$opent = VikRentItems::getHoursMinutes($parts[0]);
					$closet = VikRentItems::getHoursMinutes($parts[1]);
					$iret = $opent[0];
					$iminret = $opent[1];
					$jret = $closet[0];
				} else {
					$iret = 0;
					$iminret = 0;
					$jret = 23;
				}
			} else {
				//global opening time
				$timeopst = VikRentItems::getTimeOpenStore();
				if (is_array($timeopst) && $timeopst[0] != $timeopst[1]) {
					$opent = VikRentItems::getHoursMinutes($timeopst[0]);
					$closet = VikRentItems::getHoursMinutes($timeopst[1]);
					$iret = $opent[0];
					$iminret = $opent[1];
					$jret = $closet[0];
				} else {
					$iret = 0;
					$iminret = 0;
					$jret = 23;
				}
			}
		}
		//
	} else {
		$timeopst = VikRentItems::getTimeOpenStore();
		if (is_array($timeopst) && $timeopst[0] != $timeopst[1]) {
			$opent = VikRentItems::getHoursMinutes($timeopst[0]);
			$closet = VikRentItems::getHoursMinutes($timeopst[1]);
			$i = $opent[0];
			$imin = $opent[1];
			$j = $closet[0];
		} else {
			$i = 0;
			$imin = 0;
			$j = 23;
		}
		$iret = $i;
		$iminret = $imin;
		$jret = $j;
	}
	$hours = "";
	while ($i <= $j) {
		//VRI 1.3
		$sayi = $i < 10 ? "0".$i : $i;
		if ($nowtf != 'H:i') {
			$ampm = $i < 12 ? ' am' : ' pm';
			$ampmh = $i > 12 ? ($i - 12) : $i;
			$sayh = $ampmh < 10 ? "0".$ampmh.$ampm : $ampmh.$ampm;
		} else {
			$sayh = $i;
		}
		$hours .= "<option value=\"" . $sayi . "\">" . $sayh . "</option>\n";
		//
		$i++;
	}
	$hoursret = "";
	while ($iret <= $jret) {
		//VRI 1.3
		$sayiret = $iret < 10 ? "0".$iret : $iret;
		if ($nowtf != 'H:i') {
			$ampm = $iret < 12 ? ' am' : ' pm';
			$ampmh = $iret > 12 ? ($iret - 12) : $iret;
			$sayh = $ampmh < 10 ? "0".$ampmh.$ampm : $ampmh.$ampm;
		} else {
			$sayh = $iret;
		}
		$hoursret .= "<option value=\"" . $sayiret . "\">" . $sayh . "</option>\n";
		//
		$iret++;
	}
	$minutes = "";
	for ($i = 0; $i < 60; $i += 15) {
		if ($i < 10) {
			$i = "0" . $i;
		}
		$minutes .= "<option value=\"" . $i . "\"".((int)$i == $imin ? " selected=\"selected\"" : "").">" . $i . "</option>\n";
	}
	$minutesret = "";
	for ($iret = 0; $iret < 60; $iret += 15) {
		if ($iret < 10) {
			$iret = "0" . $iret;
		}
		$minutesret .= "<option value=\"" . $iret . "\"".((int)$iret == $iminret ? " selected=\"selected\"" : "").">" . $iret . "</option>\n";
	}
	//vikrentitems 1.2
	$forcedpickdroptimes = VikRentItems::getForcedPickDropTimes();
	if ($calendartype == "jqueryui") {
		if ($vridateformat == "%d/%m/%Y") {
			$juidf = 'dd/mm/yy';
		} elseif ($vridateformat == "%m/%d/%Y") {
			$juidf = 'mm/dd/yy';
		} else {
			$juidf = 'yy/mm/dd';
		}
		//lang for jQuery UI Calendar
		$ldecl = '
jQuery(function($){'."\n".'
	$.datepicker.regional["vikrentitems"] = {'."\n".'
		closeText: "'.JText::_('VRIJQCALDONE').'",'."\n".'
		prevText: "'.JText::_('VRIJQCALPREV').'",'."\n".'
		nextText: "'.JText::_('VRIJQCALNEXT').'",'."\n".'
		currentText: "'.JText::_('VRIJQCALTODAY').'",'."\n".'
		monthNames: ["'.JText::_('VRMONTHONE').'","'.JText::_('VRMONTHTWO').'","'.JText::_('VRMONTHTHREE').'","'.JText::_('VRMONTHFOUR').'","'.JText::_('VRMONTHFIVE').'","'.JText::_('VRMONTHSIX').'","'.JText::_('VRMONTHSEVEN').'","'.JText::_('VRMONTHEIGHT').'","'.JText::_('VRMONTHNINE').'","'.JText::_('VRMONTHTEN').'","'.JText::_('VRMONTHELEVEN').'","'.JText::_('VRMONTHTWELVE').'"],'."\n".'
		monthNamesShort: ["'.mb_substr(JText::_('VRMONTHONE'), 0, 3, 'UTF-8').'","'.mb_substr(JText::_('VRMONTHTWO'), 0, 3, 'UTF-8').'","'.mb_substr(JText::_('VRMONTHTHREE'), 0, 3, 'UTF-8').'","'.mb_substr(JText::_('VRMONTHFOUR'), 0, 3, 'UTF-8').'","'.mb_substr(JText::_('VRMONTHFIVE'), 0, 3, 'UTF-8').'","'.mb_substr(JText::_('VRMONTHSIX'), 0, 3, 'UTF-8').'","'.mb_substr(JText::_('VRMONTHSEVEN'), 0, 3, 'UTF-8').'","'.mb_substr(JText::_('VRMONTHEIGHT'), 0, 3, 'UTF-8').'","'.mb_substr(JText::_('VRMONTHNINE'), 0, 3, 'UTF-8').'","'.mb_substr(JText::_('VRMONTHTEN'), 0, 3, 'UTF-8').'","'.mb_substr(JText::_('VRMONTHELEVEN'), 0, 3, 'UTF-8').'","'.mb_substr(JText::_('VRMONTHTWELVE'), 0, 3, 'UTF-8').'"],'."\n".'
		dayNames: ["'.JText::_('VRIJQCALSUN').'", "'.JText::_('VRIJQCALMON').'", "'.JText::_('VRIJQCALTUE').'", "'.JText::_('VRIJQCALWED').'", "'.JText::_('VRIJQCALTHU').'", "'.JText::_('VRIJQCALFRI').'", "'.JText::_('VRIJQCALSAT').'"],'."\n".'
		dayNamesShort: ["'.mb_substr(JText::_('VRIJQCALSUN'), 0, 3, 'UTF-8').'", "'.mb_substr(JText::_('VRIJQCALMON'), 0, 3, 'UTF-8').'", "'.mb_substr(JText::_('VRIJQCALTUE'), 0, 3, 'UTF-8').'", "'.mb_substr(JText::_('VRIJQCALWED'), 0, 3, 'UTF-8').'", "'.mb_substr(JText::_('VRIJQCALTHU'), 0, 3, 'UTF-8').'", "'.mb_substr(JText::_('VRIJQCALFRI'), 0, 3, 'UTF-8').'", "'.mb_substr(JText::_('VRIJQCALSAT'), 0, 3, 'UTF-8').'"],'."\n".'
		dayNamesMin: ["'.mb_substr(JText::_('VRIJQCALSUN'), 0, 2, 'UTF-8').'", "'.mb_substr(JText::_('VRIJQCALMON'), 0, 2, 'UTF-8').'", "'.mb_substr(JText::_('VRIJQCALTUE'), 0, 2, 'UTF-8').'", "'.mb_substr(JText::_('VRIJQCALWED'), 0, 2, 'UTF-8').'", "'.mb_substr(JText::_('VRIJQCALTHU'), 0, 2, 'UTF-8').'", "'.mb_substr(JText::_('VRIJQCALFRI'), 0, 2, 'UTF-8').'", "'.mb_substr(JText::_('VRIJQCALSAT'), 0, 2, 'UTF-8').'"],'."\n".'
		weekHeader: "'.JText::_('VRIJQCALWKHEADER').'",'."\n".'
		dateFormat: "'.$juidf.'",'."\n".'
		firstDay: '.VikRentItems::getFirstWeekDay().','."\n".'
		isRTL: false,'."\n".'
		showMonthAfterYear: false,'."\n".'
		yearSuffix: ""'."\n".'
	};'."\n".'
	$.datepicker.setDefaults($.datepicker.regional["vikrentitems"]);'."\n".'
});';
		$document->addScriptDeclaration($ldecl);
		//
		//locations closing days (1.1)
		if (strlen($declclosingdays) > 0) {
			$declclosingdays .= '
jQuery.noConflict();
function pickupClosingDays(date) {
	dmy = date.getFullYear() + "-" + (date.getMonth() + 1) + "-" + date.getDate();
	var arrlocclosd = jQuery("#place").val();
	var checklocarr = window["loc"+arrlocclosd+"closingdays"];
	if (jQuery.inArray(dmy, checklocarr) == -1) {
		return [true, ""];
	} else {
		return [false, "", "'.addslashes(JText::_('VRILOCDAYCLOSED')).'"];
	}
}
function dropoffClosingDays(date) {
	dmy = date.getFullYear() + "-" + (date.getMonth() + 1) + "-" + date.getDate();
	var arrlocclosd = jQuery("#returnplace").val();
	var checklocarr = window["loc"+arrlocclosd+"closingdays"];
	if (jQuery.inArray(dmy, checklocarr) == -1) {
		return [true, ""];
	} else {
		return [false, "", "'.addslashes(JText::_('VRILOCDAYCLOSED')).'"];
	}
}';
			$document->addScriptDeclaration($declclosingdays);
		}
		//
		//Minimum Num of Days of Rental (VRI 1.4)
		$dropdayplus = VikRentItems::setDropDatePlus();
		$forcedropday = "jQuery('#releasedate').datepicker( 'option', 'minDate', selectedDate );";
		if (strlen($dropdayplus) > 0 && intval($dropdayplus) > 0) {
			$forcedropday = "
var vridate = jQuery(this).datepicker('getDate');
if (vridate) {
	vridate.setDate(vridate.getDate() + ".$dropdayplus.");
	jQuery('#releasedate').datepicker( 'option', 'minDate', vridate );
	jQuery('#releasedate').val(jQuery.datepicker.formatDate('".$juidf."', vridate));
}";
		}
		//
		$sdecl = "
jQuery.noConflict();
jQuery(function(){
	jQuery.datepicker.setDefaults( jQuery.datepicker.regional[ '' ] );
	jQuery('#pickupdate').datepicker({
		showOn: 'focus',
		onSelect: function( selectedDate ) {
			".$forcedropday."
		}".(strlen($declclosingdays) > 0 ? ", beforeShowDay: pickupClosingDays" : (strlen($declglobclosingdays) > 0 ? ", beforeShowDay: vriGlobalClosingDays" : ""))."
	});
	jQuery('#pickupdate').datepicker( 'option', 'dateFormat', '".$juidf."');
	jQuery('#pickupdate').datepicker( 'option', 'minDate', '".VikRentItems::getMinDaysAdvance()."d');
	jQuery('#pickupdate').datepicker( 'option', 'maxDate', '".VikRentItems::getMaxDateFuture()."');
	jQuery('#releasedate').datepicker({
		showOn: 'focus',
		onSelect: function( selectedDate ) {
			jQuery('#pickupdate').datepicker( 'option', 'maxDate', selectedDate );
		}".(strlen($declclosingdays) > 0 ? ", beforeShowDay: dropoffClosingDays" : (strlen($declglobclosingdays) > 0 ? ", beforeShowDay: vriGlobalClosingDays" : ""))."
	});
	jQuery('#releasedate').datepicker( 'option', 'dateFormat', '".$juidf."');
	jQuery('#releasedate').datepicker( 'option', 'minDate', '".VikRentItems::getMinDaysAdvance()."d');
	jQuery('#releasedate').datepicker( 'option', 'maxDate', '".VikRentItems::getMaxDateFuture()."');
	jQuery('#pickupdate').datepicker( 'option', jQuery.datepicker.regional[ 'vikrentitems' ] );
	jQuery('#releasedate').datepicker( 'option', jQuery.datepicker.regional[ 'vikrentitems' ] );
	jQuery('.vri-cal-img').click(function(){
		var jdp = jQuery(this).prev('input.hasDatepicker');
		if (jdp.length) {
			jdp.focus();
		}
	});
});";
		if (!is_array($vrisessioncart) || !count($vrisessioncart)) {
			$document->addScriptDeclaration($sdecl);
			$selform .= "<div class=\"vrisfentry\"><label class=\"vripickdroplab\" for=\"pickupdate\">" . JText::_('VRPICKUPITEM') . "</label><span><input type=\"text\" name=\"pickupdate\" id=\"pickupdate\" size=\"10\" autocomplete=\"off\" onfocus=\"this.blur();\" readonly/><span class=\"vri-cal-img\"><i class=\"fas fa-calendar-alt\"></i></span></span>";
			if (is_array($timeslots) && count($timeslots) > 0) {
				$selform .= "<div class=\"vrisfentrytimeslot\"><label for=\"vri-timeslot\">".JText::_('VRIFOR') . "</label>";
				$wseltimeslots = "<span><select name=\"timeslot\" id=\"vri-timeslot\">\n";
				foreach ($timeslots as $times) {
					$wseltimeslots .= "<option value=\"".$times['id']."\">".$times['tname']."</option>\n";
				}
				$wseltimeslots .= "</select></span></div>\n";
				$selform .= $wseltimeslots . "</div>\n";
			} else {
				$selpickh = is_array($forcedpickdroptimes[0]) && count($forcedpickdroptimes[0]) > 0 ? '<input type="hidden" name="pickuph" value="'.$forcedpickdroptimes[0][0].'"/><span class="vriforcetime">'.$forcedpickdroptimes[0][0].'</span>' : '<select name="pickuph" id="pickuph">' . $hours . '</select>';
				$selpickm = is_array($forcedpickdroptimes[0]) && count($forcedpickdroptimes[0]) > 0 ? '<input type="hidden" name="pickupm" value="'.$forcedpickdroptimes[0][1].'"/><span class="vriforcetime">'.$forcedpickdroptimes[0][1].'</span>' : '<select name="pickupm">' . $minutes . '</select>';
				$selform .= "<div class=\"vrisfentrytime\"><label for=\"pickuph\">".JText::_('VRALLE') . "</label><span id=\"vricomselph\">".$selpickh."</span><label class=\"vritimedots\">:</label><span id=\"vricomselpm\">".$selpickm."</span></div></div>\n";
				$seldroph = is_array($forcedpickdroptimes[1]) && count($forcedpickdroptimes[1]) > 0 ? '<input type="hidden" name="releaseh" value="'.$forcedpickdroptimes[1][0].'"/><span class="vriforcetime">'.$forcedpickdroptimes[1][0].'</span>' : '<select name="releaseh" id="releaseh">' . $hoursret . '</select>';
				$seldropm = is_array($forcedpickdroptimes[1]) && count($forcedpickdroptimes[1]) > 0 ? '<input type="hidden" name="releasem" value="'.$forcedpickdroptimes[1][1].'"/><span class="vriforcetime">'.$forcedpickdroptimes[1][1].'</span>' : '<select name="releasem">' . $minutesret . '</select>';
				$selform .= "<div class=\"vrisfentry\"><label class=\"vripickdroplab\" for=\"releasedate\">" . JText::_('VRRETURNITEM') . "</label><span><input type=\"text\" name=\"releasedate\" id=\"releasedate\" size=\"10\" autocomplete=\"off\" onfocus=\"this.blur();\" readonly/><span class=\"vri-cal-img\"><i class=\"fas fa-calendar-alt\"></i></span></span><div class=\"vrisfentrytime\"><label for=\"releaseh\">" . JText::_('VRALLE') . "</label><span id=\"vricomseldh\">".$seldroph."</span><label class=\"vritimedots\">:</label><span id=\"vricomseldm\">".$seldropm."</span></div></div>\n";
			}
		}
	} else {
		// default Joomla Calendar
		/**
		 * Deprecation Notice: Joomla Calendar is no longer supported. Only the jQuery UI is supported.
		 * 
		 * @since 	1.6
		 */
	}
	//
	if (@is_array($places)) {
		$selform .= "<div class=\"vrisfentry\"><label for=\"returnplace\">" . JText::_('VRRETURNITEMORD') . "</label><span class=\"vriplacesp\"><select name=\"returnplace\" id=\"returnplace\"".(strlen($onchangeplacesdrop) > 0 ? $onchangeplacesdrop : "").">";
		foreach ($places as $pla) {
			$selform .= "<option value=\"" . $pla['id'] . "\" id=\"returnplace".$pla['id']."\"".(!empty($svrireturnplace) && $svrireturnplace == $pla['id'] ? " selected=\"selected\"" : "").">" . $pla['name'] . "</option>\n";
		}
		$selform .= "</select></span></div>\n";
	}
	if (VikRentItems::showCategoriesFront()) {
		$q = "SELECT * FROM `#__vikrentitems_categories` ORDER BY `#__vikrentitems_categories`.`name` ASC;";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$categories = $dbo->loadAssocList();
			$vri_tn->translateContents($categories, '#__vikrentitems_categories');
			$selform .= "<div class=\"vrisfentry\"><label for=\"vri-categories\">" . JText::_('VRIARCAT') . "</label><span class=\"vriplacesp\"><select name=\"categories\" id=\"vri-categories\">";
			$selform .= "<option value=\"all\">" . JText::_('VRALLCAT') . "</option>\n";
			foreach ($categories as $cat) {
				$selform .= "<option value=\"" . $cat['id'] . "\">" . $cat['name'] . "</option>\n";
			}
			$selform .= "</select></span></div>\n";
		}
	}
	$selform .= "<div class=\"vrisfentrysubmit\"><input type=\"submit\" name=\"search\" value=\"" . VikRentItems::getSubmitName() . "\"" . (strlen(VikRentItems::getSubmitClass()) ? " class=\"" . VikRentItems::getSubmitClass() . "\"" : "") . "/></div>\n";
	$selform .= "</div>\n";
	//locations on google map
	if (count($coordsplaces) > 0) {
		$selform .= '<div class="vrilocationsbox"><div class="vrilocationsmapdiv"><a href="'.JRoute::_('index.php?option=com_vikrentitems&view=locationsmap&tmpl=component').'" class="vrimodal" target="_blank"><img src="'.VRI_SITE_URI.'resources/images/mapslocations-small.png"/><span>'.JText::_('VRILOCATIONSMAP').'</span></a></div></div>';
		?>
		<script type="text/javascript">
		jQuery(document).ready(function() {
			jQuery(".vrimodal").fancybox({
				"helpers": {
					"overlay": {
						"locked": false
					}
				},
				'width': '75%',
				'height': '75%',
			    'autoScale': false,
			    'transitionIn': 'none',
				'transitionOut': 'none',
				"padding": 0,
				'type': 'iframe'
			});
		});
		</script>
		<?php
	}
	//
	$selform .= (!empty($pitemid) ? "<input type=\"hidden\" name=\"Itemid\" value=\"" . $pitemid . "\"/>" : "") . "</form></div>";
	echo VikRentItems::getFullFrontTitle($vri_tn);
	echo VikRentItems::getIntroMain($vri_tn);
	
	echo $selform;
	
	if (is_array($vrisessioncart) && count($vrisessioncart) > 0) {
		?>
		<div class="vrisearchgosummarydiv">
			<a href="<?php echo JRoute::_('index.php?option=com_vikrentitems&task=oconfirm&place='.$vrisesspickuploc.'&returnplace='.$vrisessdropoffloc.'&days='.$vrisessdays.'&pickup='.$vrisesspickup.'&release='.$vrisessdropoff); ?>" class="btn"><?php echo JText::_('VRIGOTOSUMMARY'); ?></a>
		</div>
		<?php
	}
	
	echo '<div class="vri-search-closingtext">'.VikRentItems::getClosingMain($vri_tn).'</div>';
	//echo javascript to fill the date values
	if (!empty($pval) && !empty($rval)) {
		if ($calendartype == "jqueryui") {
			?>
			<script type="text/javascript">
			jQuery.noConflict();
			jQuery(function(){
				jQuery('#pickupdate').val('<?php echo $pval; ?>');
				jQuery('#releasedate').val('<?php echo $rval; ?>');
			});
			</script>
			<?php
		} else {
			?>
			<script type="text/javascript">
			document.getElementById('pickupdate').value='<?php echo $pval; ?>';
			document.getElementById('releasedate').value='<?php echo $rval; ?>';
			</script>
			<?php
		}
	}
	//
} else {
	echo VikRentItems::getDisabledRentMsg($vri_tn);
}
