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

$item = $this->item;
$busy = $this->busy;
$discounts = $this->discounts;
$timeslots = $this->timeslots;
$vri_tn = $this->vri_tn;

$pitemid = VikRequest::getInt('Itemid', '', 'request');

$document = JFactory::getDocument();
$calendartype = VikRentItems::calendarType();
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
$navdecl = '
jQuery(document).ready(function() {
	jQuery(".vrimodal").fancybox({
		"helpers": {
			"overlay": {
				"locked": false
			}
		},"padding": 0
	});
	jQuery(".vrimodalframe").fancybox({
		"helpers": {
			"overlay": {
				"locked": false
			}
		},
		"width": "75%",
		"height": "75%",
	    "autoScale": false,
	    "transitionIn": "none",
		"transitionOut": "none",
		"padding": 0,
		"type": "iframe"
	});
});';
$document->addScriptDeclaration($navdecl);
//

$currencysymb = VikRentItems::getCurrencySymb();
$showpartlyres = VikRentItems::showPartlyReserved();
$numcalendars = VikRentItems::numCalendars();
$item_params = !empty($item['jsparams']) ? json_decode($item['jsparams'], true) : array();
$carats = VikRentItems::getItemCaratOriz($item['idcarat'], $vri_tn);

$session = JFactory::getSession();
$vrisessioncart = $session->get('vriCart', '');
$vrisesspickup = $session->get('vripickupts', '');
$vrisessdropoff = $session->get('vrireturnts', '');
$vrisessdays = $session->get('vridays', '');
$vrisesspickuploc = $session->get('vriplace', '');
$vrisessdropoffloc = $session->get('vrireturnplace', '');

$vridateformat = VikRentItems::getDateFormat();
$nowtf = VikRentItems::getTimeFormat();
if ($vridateformat == "%d/%m/%Y") {
	$df = 'd/m/Y';
} elseif ($vridateformat == "%m/%d/%Y") {
	$df = 'm/d/Y';
} else {
	$df = 'Y/m/d';
}
$nowts = mktime(0, 0, 0, date('n'), date('j'), date('Y'));

?>
<div class="vri-page-content">
<?php
echo VikRentItems::getFullFrontTitle($vri_tn);
?>
	<div class="vri-itemdet-groupblocks">
		<div class="vri-itemdet-groupleft">
			<div class="vri-itemdet-imagesblock">
<?php
if (!empty($item['img'])) {
	?>
				<div class="vri-itemdet-mainimage">
					<img src="<?php echo VRI_ADMIN_URI.'resources/'.$item['img']; ?>" alt="<?php echo $item['name']; ?>"/>
				</div>
	<?php
}
if (strlen($item['moreimgs']) > 0) {
	$moreimages = explode(';;', $item['moreimgs']);
	?>
				<div class="vri-itemdet-extraimages">
	<?php
	foreach ($moreimages as $mimg) {
		if (!empty($mimg)) {
			?>
					<a href="<?php echo VRI_ADMIN_URI; ?>resources/big_<?php echo $mimg; ?>" rel="vrigroup<?php echo $item['id']; ?>" target="_blank" class="vrimodal"><img src="<?php echo VRI_ADMIN_URI; ?>resources/thumb_<?php echo $mimg; ?>" alt="<?php echo substr($mimg, 0, ((int)strpos($mimg, '.') + 1)); ?>"/></a>
			<?php
		}
	}
	?>
				</div>
<?php
}
?>
			</div>
		</div>

		<div class="vri-itemdet-groupright">
			<div class="vri-itemdet-infoblock">
				<div class="vri-itemdet-infocat">
					<span><?php echo VikRentItems::sayCategory($item['idcat'], $vri_tn); ?></span>
				</div>
				<div class="vri-itemdet-infoname">
					<span><?php echo $item['name']; ?></span>
				</div>
			</div>
			<div class="vri-itemdet-descr">
<?php
if (!empty($item['info'])) {
	//BEGIN: Joomla Content Plugins Rendering
	JPluginHelper::importPlugin('content');
	$content_instance = JTable::getInstance('content');
	$myItem = &$content_instance;
	$disp_instance = JDispatcher::getInstance();
	$dispatcher = &$disp_instance;
	$myItem->text = $item['info'];
	$dispatcher->trigger('onContentPrepare', array('com_vikrentitems.itemdetails', &$myItem, &$params, 0));
	$item['info'] = $myItem->text;
	//END: Joomla Content Plugins Rendering
	echo $item['info'];
}
?>
			</div>
<?php
if (strlen($carats)) {
	?>
			<div class="vri-itemdet-carats"><?php echo $carats; ?></div>
	<?php
}
if ($item['isgroup'] > 0 && count($this->kit_relations) > 0) {
	?>
			<div class="vri-itemdet-kitrelations">
				<span class="vri-kit-expl"><?php echo JText::_('VRIKITITEMSINCL'); ?></span>
				<table class="vri-kitrelations-tbl">
				<?php
				foreach ($this->kit_relations as $kitrel) {
					?>
					<tr>
						<td><a href="<?php echo JRoute::_('index.php?option=com_vikrentitems&view=itemdetails&elemid='.$kitrel['childid'].(!empty($pitemid) ? '&Itemid='.$pitemid : '')); ?>" target="_blank"><?php echo $kitrel['name']; ?></a></td>
						<td>x<?php echo $kitrel['units']; ?></td>
					</tr>
					<?php
				}
				?>
				</table>
			</div>
	<?php
}
if ($item['cost'] > 0) {
	?>
			<div class="vri-itemdet-priceblock">
				<span class="vri-itemdet-price-startfrom"><?php echo JText::_('VRILISTSFROM'); ?></span>
				<span class="vri-itemdet-price-cost"><?php echo $currencysymb; ?> <?php echo strlen($item['startfrom']) > 0 ? VikRentItems::numberFormat($item['startfrom']) : VikRentItems::numberFormat($item['cost']); ?></span>
				<span class="vri-itemdet-price-fromtext"><?php echo JText::_(VikRentItems::getItemParam($item['params'], 'startfromtext')); ?></span>
			</div>
	<?php
}
?>
		</div>
	</div>
<?php

$pmonth = VikRequest::getInt('month', '', 'request');
$pday = VikRequest::getInt('day', '', 'request');

$viewingdayts = !empty($pday) && $pday >= $nowts ? $pday : $nowts;

$arr = getdate();
$mon = $arr['mon'];
$realmon = ($mon < 10 ? "0".$mon : $mon);
$year = $arr['year'];
$day = $realmon."/01/".$year;
$dayts = strtotime($day);
$validmonth = false;
if ($pmonth > 0 && $pmonth >= $dayts) {
	$validmonth = true;
}
$moptions = "";
for ($i = 0; $i < 24; $i++) {
	$moptions .= "<option value=\"".$dayts."\"".($validmonth && $pmonth == $dayts ? " selected=\"selected\"" : "").">".VikRentItems::sayMonth($arr['mon'])." ".$arr['year']."</option>\n";
	$next = $arr['mon'] + 1;
	$dayts = mktime(0, 0, 0, $next, 1, $arr['year']);
	$arr = getdate($dayts);
}

if ($numcalendars > 0) {
?>
	<div class="vri-itemdet-monthslegend">
		<form action="<?php echo JRoute::_('index.php?option=com_vikrentitems&view=itemdetails&elemid='.$item['id'], false); ?>" method="post" name="vrimonths">
			<select name="month" onchange="javascript: document.vrimonths.submit();" class="vriselectm"><?php echo $moptions; ?></select>
		</form>

		<div class="vrilegendediv">
			<span class="vrilegenda"><div class="vrilegfree">&nbsp;</div> <?php echo JText::_('VRLEGFREE'); ?></span>
		<?php
		if ($showpartlyres) {
			?>
			<span class="vrilegenda"><div class="vrilegwarning">&nbsp;</div> <?php echo JText::_('VRLEGWARNING'); ?></span>
			<?php
		}
		?>
			<span class="vrilegenda"><div class="vrilegbusy">&nbsp;</div> <?php echo JText::_('VRLEGBUSY'); ?></span>
		</div>

	</div>
<?php
}

$check=false;
if (@is_array($busy)) {
	$check=true;
}
if ($validmonth) {
	$arr = getdate($pmonth);
	$mon = $arr['mon'];
	$realmon = ($mon < 10 ? "0".$mon : $mon);
	$year = $arr['year'];
	$day = $realmon."/01/".$year;
	$dayts = strtotime($day);
	$newarr = getdate($dayts);
} else {
	$arr = getdate();
	$mon = $arr['mon'];
	$realmon = ($mon < 10 ? "0".$mon : $mon);
	$year = $arr['year'];
	$day = $realmon."/01/".$year;
	$dayts = strtotime($day);
	$newarr = getdate($dayts);
}

$firstwday = (int)VikRentItems::getFirstWeekDay();
$days_labels = array(
	JText::_('VRSUN'),
	JText::_('VRMON'),
	JText::_('VRTUE'),
	JText::_('VRWED'),
	JText::_('VRTHU'),
	JText::_('VRFRI'),
	JText::_('VRSAT')
);
$days_indexes = array();
for ($i = 0; $i < 7; $i++) {
	$days_indexes[$i] = (6-($firstwday-$i)+1)%7;
}

?>
	<div class="vri-avcals-container">
<?php
$mindaysadv = VikRentItems::getMinDaysAdvance();
$lim_mindays = $mindaysadv > 0 ? ($mindaysadv > 1 ? strtotime("+$mindaysadv days", $nowts) : strtotime("+1 day", $nowts)) : $nowts;
for ($jj = 1; $jj <= $numcalendars; $jj++) {
	$d_count = 0;
	$cal = "";
	?>
		<div class="vricaldivcont">
			<table class="vrical">
				<tr><td colspan="7" align="center" class="vriitdetmonthnam"><strong><?php echo VikRentItems::sayMonth($newarr['mon'])." ".$newarr['year']; ?></strong></td></tr>
				<tr class="vricaldays">
	<?php
	for ($i = 0; $i < 7; $i++) {
		$d_ind = ($i + $firstwday) < 7 ? ($i + $firstwday) : ($i + $firstwday - 7);
		echo '<td>'.$days_labels[$d_ind].'</td>';
	}
	?>
				</tr>
				<tr>
	<?php
	for ($i=0, $n = $days_indexes[$newarr['wday']]; $i < $n; $i++, $d_count++) {
		$cal .= "<td align=\"center\">&nbsp;</td>";
	}
	while ($newarr['mon']==$mon) {
		if ($d_count > 6) {
			$d_count = 0;
			$cal .= "</tr>\n<tr>";
		}
		$dclass = "vritdfree";
		$dalt = "";
		$bid = "";
		$totfound = 0;
		if ($check) {
			foreach ($busy as $b) {
				$tmpone = getdate($b['ritiro']);
				$rit = ($tmpone['mon'] < 10 ? "0".$tmpone['mon'] : $tmpone['mon'])."/".($tmpone['mday'] < 10 ? "0".$tmpone['mday'] : $tmpone['mday'])."/".$tmpone['year'];
				$ritts = strtotime($rit);
				$tmptwo = getdate($b['consegna']);
				$con = ($tmptwo['mon'] < 10 ? "0".$tmptwo['mon'] : $tmptwo['mon'])."/".($tmptwo['mday'] < 10 ? "0".$tmptwo['mday'] : $tmptwo['mday'])."/".$tmptwo['year'];
				$conts = strtotime($con);
				if ($newarr[0] >= $ritts && $newarr[0] <= $conts) {
					$totfound++;
				}
			}
			if ($totfound >= $item['units']) {
				$dclass = "vritdbusy";
			} elseif ($totfound > 0) {
				if ($showpartlyres) {
					$dclass = "vritdwarning";
				}
			}
		}
		$useday = ($newarr['mday'] < 10 ? "0".$newarr['mday'] : $newarr['mday']);
		//link for opening the hourly availability of the day
		if ($newarr[0] >= $nowts && $newarr[0] >= $lim_mindays) {

			//'<a href="'.JRoute::_('index.php?option=com_vikrentitems&view=itemdetails&elemid='.$item['id'].'&day='.$newarr[0].(!empty($pmonth) && $validmonth ? '&month='.$pmonth : '')).'">'.'</a>'
			$useday = $useday;
		} else {
			$useday = '<span class="vri-avcal-spday">'.$useday.'</span>';
		}
		//
		if ($totfound == 1) {
			$cal .= "<td align=\"center\" data-fulldate=\"".date('Y-n-j', $newarr[0])."\" data-weekday=\"".$newarr['wday']."\" class=\"".$dclass."\">".$useday."</td>\n";
		} elseif ($totfound > 1) {
			$cal .= "<td align=\"center\" data-fulldate=\"".date('Y-n-j', $newarr[0])."\" data-weekday=\"".$newarr['wday']."\" class=\"".$dclass."\">".$useday."</td>\n";
		} else {
			$cal .= "<td align=\"center\" data-fulldate=\"".date('Y-n-j', $newarr[0])."\" data-weekday=\"".$newarr['wday']."\" class=\"".$dclass."\">".$useday."</td>\n";
		}
		$next = $newarr['mday'] + 1;
		$dayts = mktime(0, 0, 0, $newarr['mon'], $next, $newarr['year']);
		$newarr = getdate($dayts);
		$d_count++;
	}

	for ($i=$d_count; $i <= 6; $i++) {
		$cal .= "<td align=\"center\">&nbsp;</td>";
	}
	
	echo $cal;
	?>
				</tr>
			</table>
		</div>
	<?php
	if ($mon == 12) {
		$mon = 1;
		$year += 1;
		$dayts = mktime(0, 0, 0, $mon, 1, $year);
	} else {
		$mon += 1;
		$dayts = mktime(0, 0, 0, $mon, 1, $year);
	}
	$newarr = getdate($dayts);
	
	if (($jj % 3) == 0) {
		echo "";
	}
}
?>
	</div>

<?php
if (intval(VikRentItems::getItemParam($item['params'], 'hourlycalendar')) == 1) {
	// VRI 1.6 - Allow pick ups on drop offs
	$picksondrops = VikRentItems::allowPickOnDrop();
	//
?>
	<div class="vri-hourlycal-container">
		<h4 class="vri-medium-header"><?php echo JText::sprintf('VRIAVAILSINGLEDAY', date($df, $viewingdayts)); ?></h4>
		<div class="table-responsive">
			<table class="vrical table">
				<tr>
					<td style="text-align: center;"><?php echo JText::_('VRILEGH'); ?></td>
<?php
for ($h = 0; $h <= 23; $h++) {
	if ($nowtf == 'H:i') {
		$sayh = $h < 10 ? "0".$h : $h;
	} else {
		$ampm = $h < 12 ? ' am' : ' pm';
		$ampmh = $h > 12 ? ($h - 12) : $h;
		$sayh = $ampmh < 10 ? "0".$ampmh.$ampm : $ampmh.$ampm;
	}
	?>
					<td style="text-align: center;"><?php echo $sayh; ?></td>
	<?php
}
?>
				</tr>
				<tr>
					<td style="text-align: center;"><?php echo JText::_('VRILEGU'); ?></td>
<?php
for ($h = 0; $h <= 23; $h++) {
	$checkhourts = ($viewingdayts + ($h * 3600));
	$dclass = "vritdfree";
	$dalt = "";
	$bid = "";
	if ($check) {
		$totfound = 0;
		foreach ($busy as $b) {
			$tmpone = getdate($b['ritiro']);
			$rit = ($tmpone['mon'] < 10 ? "0".$tmpone['mon'] : $tmpone['mon'])."/".($tmpone['mday'] < 10 ? "0".$tmpone['mday'] : $tmpone['mday'])."/".$tmpone['year'];
			$ritts = strtotime($rit);
			$tmptwo = getdate($b['consegna']);
			$con = ($tmptwo['mon'] < 10 ? "0".$tmptwo['mon'] : $tmptwo['mon'])."/".($tmptwo['mday'] < 10 ? "0".$tmptwo['mday'] : $tmptwo['mday'])."/".$tmptwo['year'];
			$conts = strtotime($con);
			if ($viewingdayts >= $ritts && $viewingdayts <= $conts) {
				if ($checkhourts >= $b['ritiro'] && $checkhourts <= $b['consegna']) {
					if ($picksondrops && !($checkhourts > $b['ritiro'] && $checkhourts < $b['consegna']) && $checkhourts == $b['consegna']) {
						// VRI 1.6 - pick ups on drop offs allowed
						continue;
					}
					$totfound++;
				}
			}
		}
		if ($totfound >= $item['units']) {
			$dclass = "vritdbusy";
		} elseif ($totfound > 0) {
			if ($showpartlyres) {
				$dclass = "vritdwarning";
			}
		}
		$hourlydisp = $item['units'] - $totfound;
		$hourlydisp = $hourlydisp < 0 ? 0 : $hourlydisp;
	} else {
		$hourlydisp = $item['units'];
	}
	?>
					<td style="text-align: center;" class="<?php echo $dclass; ?>"><?php echo $hourlydisp; ?></td>
	<?php
}
?>
				</tr>
			</table>
		</div>
	</div>
<?php
}
?>
	<div class="vri-bookform-container">
		<h4 class="vri-medium-header"><?php echo JText::_('VRISELECTPDDATES'); ?></h4>
<?php

if (count($discounts) > 0) {
	?>
		<div class="vridiscsquantsdiv">
			<table class="vridiscsquantstable">
				<tr class="vridiscsquantstrfirst"><td><?php echo JText::_('VRIDISCSQUANTSQ'); ?></td><td><?php echo JText::_('VRIDISCSQUANTSSAVE'); ?></td></tr>
				<?php
				foreach ($discounts as $kd => $disc) {
					$discval = substr($disc['diffcost'], -2) == '00' ? number_format($disc['diffcost'], 0) : VikRentItems::numberFormat($disc['diffcost']);
					$savedisc = $disc['val_pcent'] == 1 ? $discval.' '.$currencysymb : $discval.'%';
					?>
				<tr class="vridiscsquantstrentry">
					<td><?php echo $disc['quantity'].(end(array_keys($discounts)) == $kd && $disc['ifmorequant'] == 1 ? ' '.JText::_('VRIDISCSQUANTSORMORE') : ''); ?></td>
					<td><?php echo $savedisc; ?></td>
				</tr>	
					<?php
				}
				?>
			</table>
		</div>
	<?php
}

if (VikRentItems::allowRent()) {
	$dbo = JFactory::getDBO();
	
	$deliveryservicetext = '';
	if (intval(VikRentItems::getItemParam($item['params'], 'delivery')) == 1) {
		$deliveryservicetext = '<span class="vrideliveryservicespan">'.JText::_('VRIDELIVERYSERVICEAVLB').'</span>';
	}
	
	$coordsplaces = array();
	$selform = "<div class=\"vridivsearch\">".$deliveryservicetext."<form action=\"".JRoute::_('index.php?option=com_vikrentitems')."\" method=\"get\"><div class=\"vricalform\">\n";
	$selform .= "<input type=\"hidden\" name=\"option\" value=\"com_vikrentitems\"/>\n";
	$selform .= "<input type=\"hidden\" name=\"task\" value=\"search\"/>\n";
	$selform .= "<input type=\"hidden\" name=\"itemdetail\" value=\"".$item['id']."\"/>\n";
	$diffopentime = false;
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
jQuery(document).ready(function() {
	var gscdlen = vriglobclosingsdays.length;
	var gwcdlen = vriglobclosingwdays.length;
	for (var l = 0; l < gscdlen; l++) {
		var tdcheck = jQuery("td[data-fulldate=\'"+vriglobclosingsdays[l]+"\']");
		if (tdcheck.length) {
			tdcheck.addClass("vritdclosedday").attr("title", "'.addslashes(JText::_('VRIGLOBDAYCLOSED')).'");
			if (tdcheck.find("a").length) {
				tdcheck.find("a").attr("href", "Javascript: void(0);");
			}
		}
	}
	for (var l = 0; l < gwcdlen; l++) {
		var tdcheck = jQuery("td[data-weekday=\'"+vriglobclosingwdays[l]+"\']");
		if (tdcheck.length) {
			tdcheck.addClass("vritdclosedday").attr("title", "'.addslashes(JText::_('VRIGLOBDAYCLOSED')).'");
			tdcheck.each(function() {
				if (jQuery(this).find("a").length) {
					jQuery(this).find("a").attr("href", "Javascript: void(0);");
				}
			});
		}
	}
});
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

	if (is_array($vrisessioncart) && count($vrisessioncart) > 0) {
		$selform .= "<div class=\"vrisfentry vri-search-sessvals\"><label class=\"vripickdroplab\">" . JText::_('VRPICKUPITEM') . "</label><span class=\"vridtsp\"><input type=\"hidden\" name=\"pickupdate\" value=\"".date($df, $vrisesspickup)."\"/>".date($df, $vrisesspickup)." " . (!empty($nowtf) ? JText::_('VRALLE') : '') . " <input type=\"hidden\" name=\"pickuph\" value=\"".date('H', $vrisesspickup)."\"/>".(!empty($nowtf) ? date('H', $vrisesspickup).":" : '')."<input type=\"hidden\" name=\"pickupm\" value=\"".date('i', $vrisesspickup)."\"/>".(!empty($nowtf) ? date('i', $vrisesspickup) : '')."</span></div>\n";
		$selform .= "<div class=\"vrisfentry vri-search-sessvals\"><label class=\"vripickdroplab\">" . JText::_('VRRETURNITEM') . "</label><span class=\"vridtsp\"><input type=\"hidden\" name=\"releasedate\" value=\"".date($df, $vrisessdropoff)."\"/>".date($df, $vrisessdropoff)." " . (!empty($nowtf) ? JText::_('VRALLE') : '') . " <input type=\"hidden\" name=\"releaseh\" value=\"".date('H', $vrisessdropoff)."\"/>".(!empty($nowtf) ? date('H', $vrisessdropoff).":" : '')."<input type=\"hidden\" name=\"releasem\" value=\"".date('i', $vrisessdropoff)."\"/>".(!empty($nowtf) ? date('i', $vrisessdropoff) : '')."</span></div>";
	}

	if (VikRentItems::showPlacesFront()) {
		$actlocs = explode(";", $item['idplace']);
		$actretlocs = explode(";", $item['idretplace']);
		$actlocsall = array_merge($actlocs, $actretlocs);
		$actlocsall = array_unique($actlocsall);
		$clauselocs = array();
		foreach ($actlocsall as $ala) {
			if (!empty($ala)) {
				$clauselocs[] = $ala;
			}
		}
		if (count($clauselocs)) {
			$q = "SELECT * FROM `#__vikrentitems_places` WHERE `id` IN (".implode(",", $clauselocs).") ORDER BY `#__vikrentitems_places`.`name` ASC;";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() > 0) {
				$places = $dbo->loadAssocList();
				$vri_tn->translateContents($places, '#__vikrentitems_places');
				//check if some place has a different opening time (1.1)
				foreach ($places as $pla) {
					if (!empty($pla['opentime'])) {
						$diffopentime = true;
						break;
					}
				}
				$onchangeplaces = $diffopentime == true ? " onchange=\"javascript: vriSetLocOpenTime(this.value, 'pickup');\"" : " onchange=\"javascript: vriSetSameDropLoc(this.value);\"";
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
				} else {
					$onchangedecl = '
jQuery.noConflict();
function vriSetSameDropLoc(loc) {
	var droplocsel = document.getElementById("returnplace");
	for (var i = 0; i < droplocsel.length; i++) {
		if (parseInt(droplocsel.options[i].value) == parseInt(loc)) {
			droplocsel.options[i].selected = true;
			break;
		}
	}
}';
					$document->addScriptDeclaration($onchangedecl);
				}
				//end check if some place has a different opningtime (1.1)
				$selform .= "<div class=\"vrisfentry\"><label for=\"place\">" . JText::_('VRPPLACE') . "</label><span class=\"vriplacesp\"><select name=\"place\" id=\"place\"".$onchangeplaces.">";
				foreach ($places as $pla) {
					if (in_array($pla['id'], $actlocs)) {
						$selform .= "<option value=\"" . $pla['id'] . "\" id=\"place".$pla['id']."\">" . $pla['name'] . "</option>\n";
						if (!empty($pla['lat']) && !empty($pla['lng'])) {
							$coordsplaces[] = $pla;
						}
					}
				}
				$selform .= "</select></span></div>\n";
			}
		}
	}
	
	if ($diffopentime == true && is_array($places) && strlen($places[0]['opentime']) > 0) {
		$parts = explode("-", $places[0]['opentime']);
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
	$minutes = "";
	for ($i = 0; $i < 60; $i += 15) {
		if ($i < 10) {
			$i = "0" . $i;
		} else {
			$i = $i;
		}
		$minutes .= "<option value=\"" . $i . "\"".((int)$i == $imin ? " selected=\"selected\"" : "").">" . $i . "</option>\n";
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
jQuery(function($) {'."\n".'
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
		$forcedropday = "jQuery('#releasedate').datepicker( 'option', 'minDate', selectedDate );";
		$dropdayplus = VikRentItems::getItemParam($item['params'], 'dropdaysplus');
		if (strlen($dropdayplus) > 0 && intval($dropdayplus) >= 0) {
			$forcedropday = "
var vridate = jQuery(this).datepicker('getDate');
if (vridate) {
	vridate.setDate(vridate.getDate() + ".$dropdayplus.");
	jQuery('#releasedate').datepicker( 'option', 'minDate', vridate );
	jQuery('#releasedate').val(jQuery.datepicker.formatDate('".$juidf."', vridate));
}";
		}
		
		$sdecl = "
jQuery.noConflict();
jQuery(function() {
	jQuery.datepicker.setDefaults( jQuery.datepicker.regional[ '' ] );
	jQuery('#pickupdate').datepicker({
		showOn: 'focus',
		onSelect: function( selectedDate ) {
			".$forcedropday."
		}".(strlen($declglobclosingdays) > 0 ? ", beforeShowDay: vriGlobalClosingDays" : "")."
	});
	jQuery('#pickupdate').datepicker( 'option', 'dateFormat', '".$juidf."');
	jQuery('#pickupdate').datepicker( 'option', 'minDate', '".VikRentItems::getMinDaysAdvance()."d');
	jQuery('#pickupdate').datepicker( 'option', 'maxDate', '".VikRentItems::getMaxDateFuture()."');
	jQuery('#releasedate').datepicker({
		showOn: 'focus',
		onSelect: function( selectedDate ) {
			jQuery('#pickupdate').datepicker( 'option', 'maxDate', selectedDate );
		}".(strlen($declglobclosingdays) > 0 ? ", beforeShowDay: vriGlobalClosingDays" : "")."
	});
	jQuery('#releasedate').datepicker( 'option', 'dateFormat', '".$juidf."');
	jQuery('#releasedate').datepicker( 'option', 'minDate', '".VikRentItems::getMinDaysAdvance()."d');
	jQuery('#releasedate').datepicker( 'option', 'maxDate', '".VikRentItems::getMaxDateFuture()."');
	jQuery('#pickupdate').datepicker( 'option', jQuery.datepicker.regional[ 'vikrentitems' ] );
	jQuery('#releasedate').datepicker( 'option', jQuery.datepicker.regional[ 'vikrentitems' ] );
	jQuery('.vri-cal-img').click(function() {
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
				$seldroph = is_array($forcedpickdroptimes[1]) && count($forcedpickdroptimes[1]) > 0 ? '<input type="hidden" name="releaseh" value="'.$forcedpickdroptimes[1][0].'"/><span class="vriforcetime">'.$forcedpickdroptimes[1][0].'</span>' : '<select name="releaseh" id="releaseh">' . $hours . '</select>';
				$seldropm = is_array($forcedpickdroptimes[1]) && count($forcedpickdroptimes[1]) > 0 ? '<input type="hidden" name="releasem" value="'.$forcedpickdroptimes[1][1].'"/><span class="vriforcetime">'.$forcedpickdroptimes[1][1].'</span>' : '<select name="releasem">' . $minutes . '</select>';
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
	if(@ is_array($places)) {
		$selform .= "<div class=\"vrisfentry\"><label for=\"returnplace\">" . JText::_('VRRETURNITEMORD') . "</label><span class=\"vriplacesp\"><select name=\"returnplace\" id=\"returnplace\"".(strlen($onchangeplacesdrop) > 0 ? $onchangeplacesdrop : "").">";
		foreach ($places as $pla) {
			if (in_array($pla['id'], $actretlocs)) {
				$selform .= "<option value=\"" . $pla['id'] . "\" id=\"returnplace".$pla['id']."\">" . $pla['name'] . "</option>\n";
			}
		}
		$selform .= "</select></span></div>\n";
	}
	if ((int)$item['askquantity'] == 1) {
		$selform .= "<div class=\"vrisfentry\"><label for=\"itemquant\">".JText::_('VRIQUANTITYITEM')."</label><span><input type=\"number\" name=\"itemquant\" id=\"itemquant\" value=\"".(!array_key_exists('minquant', $item_params) || empty($item_params['minquant']) ? '1' : (int)$item_params['minquant'])."\" min=\"".(!array_key_exists('minquant', $item_params) || empty($item_params['minquant']) ? '1' : (int)$item_params['minquant'])."\" class=\"vri-numbinput\"/></span></div>\n";
	}
	$selform .= "<div class=\"vrisfentrysubmit\"><input type=\"submit\" name=\"search\" value=\"" . JText::_('VRIBOOKTHISITEM') . "\" class=\"vridetbooksubmit\"/></div>\n";
	$selform .= "</div>\n";
	$selform .= (!empty($pitemid) ? "<input type=\"hidden\" name=\"Itemid\" value=\"" . $pitemid . "\"/>" : "") . "</form></div>";
	//locations on google map
	if (count($coordsplaces) > 0) {
		$selform = '<div class="vrilocationsbox"><div class="vrilocationsmapdiv"><a href="'.JRoute::_('index.php?option=com_vikrentitems&view=locationsmap&elemid='.$item['id'].'&tmpl=component').'" class="vrimodalframe" target="_blank"><img src="'.VRI_SITE_URI.'resources/images/mapslocations-small.png" /><span>'.JText::_('VRILOCATIONSMAP').'</span></a></div></div>'.$selform;
	}
	//
	echo $selform;
	?>
	</div>
	<?php
	if (!empty($viewingdayts) && !empty($pday) && $viewingdayts >= $nowts) {
		?>
		<script type="text/javascript">
		jQuery(document).ready(function() {
			document.getElementById('pickupdate').value='<?php echo date($df, $viewingdayts); ?>';
			if (jQuery(".vri-hourlycal-container").length) {
				jQuery('html,body').animate({ scrollTop: (jQuery(".vri-hourlycal-container").offset().top - 5) }, { duration: 'slow' });	
			}
		});
		</script>
		<?php
	}
} else {
	echo VikRentItems::getDisabledRentMsg($vri_tn);
}

?>
</div>
