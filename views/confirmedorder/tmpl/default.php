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

$dbo = JFactory::getDBO();
$ord = $this->ord;
$vricart = $this->vricart;
$payment = $this->payment;
$vri_tn = $this->vri_tn;
//vikrentitems 1.1
$calcdays = $this->calcdays;
if (strlen($calcdays) > 0) {
	$origdays = $ord['days'];
	$ord['days'] = $calcdays;
}
//
$currencysymb = VikRentItems::getCurrencySymb();
$nowdf = VikRentItems::getDateFormat();
if ($nowdf == "%d/%m/%Y") {
	$df = 'd/m/Y';
} elseif ($nowdf == "%m/%d/%Y") {
	$df = 'm/d/Y';
} else {
	$df = 'Y/m/d';
}
$nowtf = VikRentItems::getTimeFormat();

$isdue = 0;
$imp = 0;

foreach ($vricart as $iditem => $itemarrparent) {
	foreach ($itemarrparent as $k => $itemarr) {
		$is_cust_cost = (!empty($itemarr['info']['cust_cost']) && $itemarr['info']['cust_cost'] > 0);
		$isdue += $is_cust_cost ? $itemarr['tar']['cost'] : VikRentItems::sayCostPlusIva($itemarr['tar']['cost'] * $itemarr['itemquant'], $itemarr['tar']['idprice'], $ord);
		$optbought = "";
		if (!empty($itemarr['optionals'])) {
			$stepo = explode(";", $itemarr['optionals']);
			foreach ($stepo as $one) {
				if (!empty($one)) {
					$stept = explode(":", $one);
					$q = "SELECT * FROM `#__vikrentitems_optionals` WHERE `id`='" . intval($stept[0]) . "';";
					$dbo->setQuery($q);
					$dbo->execute();
					if ($dbo->getNumRows() == 1) {
						$actopt = $dbo->loadAssocList();
						$vri_tn->translateContents($actopt, '#__vikrentitems_optionals');
						$specvar = '';
						if (!empty($actopt[0]['specifications']) && strstr($stept[1], '-') != false) {
							$optspeccosts = VikRentItems::getOptionSpecIntervalsCosts($actopt[0]['specifications']);
							$optspecnames = VikRentItems::getOptionSpecIntervalsNames($actopt[0]['specifications']);
							$specstept = explode('-', $stept[1]);
							$stept[1] = $specstept[0];
							$specvar = $specstept[1];
							$actopt[0]['specintv'] = $specvar;
							$actopt[0]['name'] .= ': '.$optspecnames[($specvar - 1)];
							$actopt[0]['quan'] = $stept[1];
							$realcost = (intval($actopt[0]['perday']) == 1 ? (floatval($optspeccosts[($specvar - 1)]) * $ord['days'] * $stept[1]) : (floatval($optspeccosts[($specvar - 1)]) * $stept[1]));
						} else {
							$realcost = (intval($actopt[0]['perday']) == 1 ? ($actopt[0]['cost'] * $ord['days'] * $stept[1]) : ($actopt[0]['cost'] * $stept[1]));
						}
						if (!empty($actopt[0]['maxprice']) && $actopt[0]['maxprice'] > 0 && $realcost > $actopt[0]['maxprice']) {
							$realcost = $actopt[0]['maxprice'];
							if (intval($actopt[0]['hmany']) == 1 && intval($stept[1]) > 1) {
								$realcost = $actopt[0]['maxprice'] * $stept[1];
							}
						}
						$opt_item_units = $actopt[0]['onceperitem'] ? 1 : $itemarr['itemquant'];
						$tmpopr = VikRentItems::sayOptionalsPlusIva($realcost * $opt_item_units, $actopt[0]['idiva'], $ord);
						$isdue += $tmpopr;
						$imp += VikRentItems::sayOptionalsMinusIva($realcost * $opt_item_units, $actopt[0]['idiva'], $ord);
						$optbought .= ($stept[1] > 1 ? $stept[1] . " " : "") . $actopt[0]['name'] . ($tmpopr > 0 ? ": " . $currencysymb . " " . VikRentItems::numberFormat($tmpopr) : '') . "<br/>";
					}
				}
			}
		}
		$vricart[$iditem][$k]['optbought'] = $optbought;
		//custom extra costs
		$vricart[$iditem][$k]['extracosts'] = '';
		if (!empty($itemarr['info']['extracosts'])) {
			$cur_extra_costs = json_decode($itemarr['info']['extracosts'], true);
			foreach ($cur_extra_costs as $eck => $ecv) {
				$ecplustax = !empty($ecv['idtax']) ? VikRentItems::sayOptionalsPlusIva($ecv['cost'], $ecv['idtax'], $ord) : $ecv['cost'];
				$isdue += $ecplustax;
				$imp += !empty($ecv['idtax']) ? VikRentItems::sayOptionalsMinusIva($ecv['cost'], $ecv['idtax'], $ord) : $ecv['cost'];
				$vricart[$iditem][$k]['extracosts'] .= "<div><span class=\"vri-booking-pricename\">".$ecv['name']."</span> <span class=\"vri_currency\">" . $currencysymb . "</span> <span class=\"vri_price\">" . VikRentItems::numberFormat($ecplustax) . "</span></div>";
			}
		}
		//
	}
}
//delivery service
if ($ord['deliverycost'] > 0) {
	$imp += $ord['deliverycost'];
	$isdue += $ord['deliverycost'];
}
//
if (!empty($ord['idplace']) && !empty($ord['idreturnplace'])) {
	$locfee = VikRentItems::getLocFee($ord['idplace'], $ord['idreturnplace']);
	if ($locfee) {
		//VikRentItems 1.1 - Location fees overrides
		if (strlen($locfee['losoverride']) > 0) {
			$arrvaloverrides = array();
			$valovrparts = explode('_', $locfee['losoverride']);
			foreach ($valovrparts as $valovr) {
				if (!empty($valovr)) {
					$ovrinfo = explode(':', $valovr);
					$arrvaloverrides[$ovrinfo[0]] = $ovrinfo[1];
				}
			}
			if (array_key_exists($ord['days'], $arrvaloverrides)) {
				$locfee['cost'] = $arrvaloverrides[$ord['days']];
			}
		}
		//end VikRentItems 1.1 - Location fees overrides
		$locfeecost = intval($locfee['daily']) == 1 ? ($locfee['cost'] * $ord['days']) : $locfee['cost'];
		$locfeewith = VikRentItems::sayLocFeePlusIva($locfeecost, $locfee['idiva'], $ord);
		$isdue += $locfeewith;
	}
}

//vikrentitems 1.1 coupon
$usedcoupon = false;
$origisdue = $isdue;
if (strlen($ord['coupon']) > 0) {
	$usedcoupon = true;
	$expcoupon = explode(";", $ord['coupon']);
	$isdue = $isdue - $expcoupon[1];
}
//

$wdays_map = array(
	JText::_('VRIJQCALSUN'),
	JText::_('VRIJQCALMON'),
	JText::_('VRIJQCALTUE'),
	JText::_('VRIJQCALWED'),
	JText::_('VRIJQCALTHU'),
	JText::_('VRIJQCALFRI'),
	JText::_('VRIJQCALSAT')
);
$ts_info = getdate($ord['ts']);
$ritiro_info = getdate($ord['ritiro']);
$consegna_info = getdate($ord['consegna']);

$printer = VikRequest::getInt('printer', '', 'request');
if ($printer != 1) {
?>
<div class="vriprintdiv">
	<a href="<?php echo JRoute::_('index.php?option=com_vikrentitems&task=vieworder&sid='.$ord['sid'].'&ts='.$ord['ts'].'&printer=1&tmpl=component'); ?>" target="_blank" title="<?php echo JText::_('VRIPRINTCONFORDER'); ?>">
		<i class="fa fa-print"></i>
	</a>
</div>
<?php
}
?>

<h3 class="vri-ord-details-intro"><?php echo JText::sprintf('VRIYOURORDCONFAT', VikRentItems::getFrontTitle($vri_tn)); ?></h3>

<div class="vri-ord-top-container">

	<div class="vri-ord-details-head vri-ord-details-head-confirmed">
		<h4><i class="fas fa-check-circle"></i> <?php echo JText::_('VRIYOURORDISCONF'); ?></h4>
	</div>

	<div class="vri-ord-mid-container">

		<div class="vri-ord-pickdrop-block">
			<h4 class="vri-medium-header"><?php echo JText::_('VRIORDERDETAILS'); ?></h4>
			<div class="vri-ord-det-cont">
				<span class="vri-ord-det-lbl"><?php echo JText::_('VRIORDERNUMBER'); ?></span>
				<span class="vri-ord-det-val"><?php echo $ord['id']; ?></span>
			</div>
			<div class="vri-ord-det-cont">
				<span class="vri-ord-det-lbl"><?php echo JText::_('VRCONFIRMNUMB'); ?></span>
				<span class="vri-ord-det-val"><?php echo $ord['sid'].'-'.$ord['ts']; ?></span>
			</div>
			<div class="vri-ord-det-cont">
				<span class="vri-ord-det-lbl"><?php echo JText::_('VRORDEREDON'); ?></span>
				<span class="vri-ord-det-val"><?php echo $wdays_map[$ts_info['wday']].', '.date($df.' '.$nowtf, $ord['ts']); ?></span>
			</div>
			<div class="vri-ord-pickleft-block">
				<div class="vri-ord-pickdate-inner">
					<span class="vri-ord-pickupdt-lbl"><?php echo JText::_('VRPICKUP'); ?></span>
					<span class="vri-ord-pickupdt-val"><?php echo $wdays_map[$ritiro_info['wday']].', '.date($df.' '.$nowtf, $ord['ritiro']); ?></span>
				</div>
				<?php if (!empty($ord['idplace'])) { ?>
				<div class="vri-ord-picklocation-inner">
					<span class="vri-ord-pickuploc-lbl"><?php echo JText::_('VRRITIROITEM'); ?></span>
					<span class="vri-ord-pickuploc-val"><?php echo VikRentItems::getPlaceName($ord['idplace'], $vri_tn); ?></span>
				</div>
				<?php } ?>
			</div>
			<div class="vri-ord-dropright-block">
				<div class="vri-ord-dropdate-inner">
					<span class="vri-ord-dropoffdt-lbl"><?php echo JText::_('VRRETURN'); ?></span>
					<span class="vri-ord-dropoffdt-val"><?php echo $wdays_map[$consegna_info['wday']].', '.date($df.' '.$nowtf, $ord['consegna']); ?></span>
				</div>
				<?php if (!empty($ord['idreturnplace'])) { ?>
				<div class="vri-ord-droplocation-inner">
					<span class="vri-ord-dropoffloc-lbl"><?php echo JText::_('VRRETURNITEMORD'); ?></span>
					<span class="vri-ord-dropoffloc-val"><?php echo VikRentItems::getPlaceName($ord['idreturnplace'], $vri_tn); ?></span>
				</div>
				<?php } ?>
			</div>
		<?php
		// VikRentItems 1.1 Download PDF
		if (file_exists(VRI_SITE_PATH . DS . "resources" . DS . "pdfs" . DS . $ord['id'].'_'.$ord['ts'].'.pdf')) {
			?>
			<!--			<div class="vri-ord-det-cont">-->
<!--				<span class="vri-ord-det-lbl"></span>-->
<!--				<div class="vri-ord-det-val">-->
<!--					<div class="vri-ord-downpdf">-->
<!--						<a href="--><?php //echo VRI_SITE_URI; ?><!--resources/pdfs/--><?php //echo $ord['id'].'_'.$ord['ts']; ?><!--.pdf" class="btn" target="_blank"><i class="fas fa-file-pdf"></i> --><?php //echo JText::_('VRIDOWNLOADPDF'); ?><!--</a>-->
<!--					</div>-->
<!--				</div>-->
<!--			</div>-->
			<?php
		}
		?>
		</div>

		<div class="vri-ord-udata">
			<h4 class="vri-medium-header"><?php echo JText::_('VRPERSDETS'); ?></h4>
			<div class="vri-bookingdet-custdata">
			<?php
			$custdata_parts = explode("\n", $ord['custdata']);
			if (count($custdata_parts) > 2 && strpos($custdata_parts[0], ':') !== false && strpos($custdata_parts[1], ':') !== false) {
				//attempt to format labels and values
				foreach ($custdata_parts as $custdet) {
					if (strlen($custdet) < 1) {
						continue;
					}
					$custdet_parts = explode(':', $custdet);
					$custd_lbl = '';
					$custd_val = '';
					if (count($custdet_parts) < 2) {
						$custd_val = $custdet;
					} else {
						$custd_lbl = $custdet_parts[0];
						unset($custdet_parts[0]);
						$custd_val = trim(implode(':', $custdet_parts));
					}
					?>
				<div class="vri-bookingdet-userdetail">
					<?php
					if (strlen($custd_lbl)) {
						?>
					<span class="vri-bookingdet-userdetail-lbl"><?php echo $custd_lbl; ?></span>
						<?php
					}
					if (strlen($custd_val)) {
						?>
					<span class="vri-bookingdet-userdetail-val"><?php echo $custd_val; ?></span>
						<?php
					}
					?>
				</div>
					<?php
				}
			} else {
				echo nl2br($ord['custdata']);
			}
			?>
			</div>
		</div>

	</div>

</div>
		
<div class="vrivordcosts">
<?php
foreach ($vricart as $iditem => $itemarrparent) {
	foreach ($itemarrparent as $k => $itemarr) {
		$is_cust_cost = (!empty($itemarr['info']['cust_cost']) && $itemarr['info']['cust_cost'] > 0);
		?>
	<div class="vri-ord-item-block">
		<div class="vri-ord-item-entry-main">
			<div class="vri-ord-item-entry-name">
				<span><?php echo $itemarr['info']['name'].($itemarr['itemquant'] > 1 ? " x".$itemarr['itemquant'] : ""); ?></span>
			</div>
		<?php
		if (!empty($itemarr['info']['img'])) {
			?>
			<div class="vri-ord-item-entry-img">
				<img src="<?php echo VRI_ADMIN_URI; ?>resources/vthumb_<?php echo $itemarr['info']['img']; ?>" alt="<?php echo $itemarr['info']['name']; ?>"/>
			</div>
			<?php
		}
		?>
		</div>
		<?php
		if (is_array($itemarr['tar'])) {
			?>
		<div class="vri-ord-item-entry-tariff">
			<span><?php echo $is_cust_cost ? JText::_('VRIRENTCUSTRATEPLAN') : VikRentItems::getPriceName($itemarr['tar']['idprice'], $vri_tn); ?>:</span> <span><?php echo $currencysymb; ?> <?php echo VikRentItems::numberFormat(($is_cust_cost ? $itemarr['tar']['cost'] : VikRentItems::sayCostPlusIva($itemarr['tar']['cost'] * $itemarr['itemquant'], $itemarr['tar']['idprice'], $ord))); ?></span>
		</div>
			<?php
		}
		if (strlen($itemarr['optbought'])) {
			?>
		<div class="vri-ord-item-entry-options">
			<span class="vrivordcoststitle"><?php echo JText::_('VROPTS'); ?></span>
			<div class="vrivordcostsoptionals"><?php echo $itemarr['optbought']; ?></div>
		</div>
			<?php
		}
		if (isset($locfeewith)) {
			?>
		<div class="vri-ord-item-entry-options vri-ord-item-entry-locfee">
			<span class="vrivordcoststitle"><?php echo JText::_('VRLOCFEETOPAY'); ?></span> <span><?php echo $currencysymb; ?> <?php echo $locfeewith; ?></span>
		</div>
			<?php
		}
		if (array_key_exists('delivery', $itemarr)) {
			?>
		<div class="vri-ord-item-entry-options vri-ord-item-entry-delivery">
			<span class="vrivordcoststitle"><?php echo JText::_('VRISUMMARYDELIVERYTO'); ?></span> <span><?php echo $itemarr['delivery']['addr']; ?></span>
		</div>
			<?php
		}
		if (strlen($itemarr['extracosts'])) {
			?>
		<div class="vri-ord-item-entry-options vri-ord-item-entry-extracosts">
			<span class="vrivordcoststitle"><?php echo JText::_('VRIEXTRASERVICES'); ?></span>
			<div class="vbvordextraservices"><?php echo $itemarr['extracosts']; ?></div>
		</div>
			<?php
		}
	?>
	</div>
	<?php
	}
}
?>
	<div class="vri-ord-coststot-container">
	<?php
	if ($ord['deliverycost'] > 0) {
		?>
		<div class="vrivordcostsdelivery">
			<span class="vrivordcoststitle"><?php echo JText::_('VRISUMMARYDELIVERYSERVICE'); ?></span>
			<span class="vri-ord-tot-cost vri-ord-delivery-cost"><?php echo $currencysymb; ?> <?php echo VikRentItems::numberFormat($ord['deliverycost']); ?></span>
		</div>
		<?php
	}
	if ($usedcoupon) {
		?>
		<div class="vri-ord-coupon">
			<span class="vrivordcoststitle"><?php echo JText::_('VRICOUPON').' '.$expcoupon[2]; ?></span>
			<span class="vri-ord-tot-cost vri-ord-coupon-disc">- <?php echo $currencysymb; ?> <?php echo VikRentItems::numberFormat($expcoupon[1]); ?></span>
		</div>
		<?php
	}
	?>
		<div class="vrivordcoststot">
			<span class="vrivordcoststitle"><?php echo JText::_('VRTOTAL'); ?></span>
			<span class="vri-ord-tot-cost"><?php echo $currencysymb; ?> <?php echo VikRentItems::numberFormat($ord['order_total']); ?></span>
		</div>
	<?php
	if ($ord['totpaid'] > 0 && $ord['totpaid'] < $ord['order_total']) {
		?>
		<div class="vri-ord-totpaid">
			<span class="vrivordcoststitle"><?php echo JText::_('VRIAMOUNTPAID'); ?></span>
			<span class="vri-ord-tot-cost vri-ord-tot-cost-paid"><?php echo $currencysymb; ?> <?php echo VikRentItems::numberFormat($ord['totpaid']); ?></span>
		</div>
		<div class="vri-ord-totremaining">
			<span class="vrivordcoststitle"><?php echo JText::_('VRITOTALREMAINING'); ?></span>
			<span class="vri-ord-tot-cost vri-ord-tot-cost-remaining"><?php echo $currencysymb; ?> <?php echo VikRentItems::numberFormat(($ord['order_total'] - $ord['totpaid'])); ?></span>
		</div>
		<?php
	}
	?>
	</div>
</div>
<?php

if (@is_array($payment) && intval($payment['shownotealw']) == 1) {
	if (strlen($payment['note']) > 0) {
		?>
<div class="vrivordpaynote">
	<?php echo $payment['note']; ?>
</div>
		<?php
	}
}

if ($printer == 1) {
	?>
<script type="text/javascript">
window.print();
</script>
	<?php
} else {
	//VikRentItems 1.1 Cancellation Request
	?>
<script type="text/javascript">
function vriOpenCancOrdForm() {
	document.getElementById('vriopencancform').style.display = 'none';
	document.getElementById('vriordcancformbox').style.display = 'block';
}
function vriValidateCancForm() {
	var vrvar = document.vricanc;
	if (!document.getElementById('vricancemail').value.match(/\S/)) {
		document.getElementById('vriformcancemail').style.color='#ff0000';
		return false;
	} else {
		document.getElementById('vriformcancemail').style.color='';
	}
	if (!document.getElementById('vricancreason').value.match(/\S/)) {
		document.getElementById('vriformcancreason').style.color='#ff0000';
		return false;
	} else {
		document.getElementById('vriformcancreason').style.color='';
	}
	return true;
}
</script>
<div class="vriordcancbox">
	<h3><?php echo JText::_('VRIREQUESTCANCMOD'); ?></h3>
	<a href="javascript: void(0);" id="vriopencancform" onclick="javascript: vriOpenCancOrdForm();"><?php echo JText::_('VRIREQUESTCANCMODOPENTEXT'); ?></a>
	<div class="vriordcancformbox" id="vriordcancformbox">
		<form action="<?php echo JRoute::_('index.php?option=com_vikrentitems'); ?>" name="vricanc" method="post" onsubmit="javascript: return vriValidateCancForm();">
			<div class="vri-ord-reqcanc-container">
				<div class="vri-ord-reqcanc-entry">
					<label id="vriformcancemail" for="vricancemail"><?php echo JText::_('VRIREQUESTCANCMODEMAIL'); ?></label>
					<input type="text" class="vriinput" name="email" id="vricancemail" value="<?php echo $ord['custmail']; ?>"/>
				</div>
				<div class="vri-ord-reqcanc-entry vri-ord-reqcanc-entry-reason">
					<label id="vriformcancreason" for="vricancreason"><?php echo JText::_('VRIREQUESTCANCMODREASON'); ?></label>
					<textarea name="reason" id="vricancreason" rows="7" cols="30" class="vritextarea"></textarea>
				</div>
				<div class="vri-ord-reqcanc-entry vri-ord-reqcanc-entry-sbmt">
					<input type="submit" name="sendrequest" value="<?php echo JText::_('VRIREQUESTCANCMODSUBMIT'); ?>" class="btn"/>
				</div>
			</div>
			<input type="hidden" name="sid" value="<?php echo $ord['sid']; ?>"/>
			<input type="hidden" name="idorder" value="<?php echo $ord['id']; ?>"/>
			<input type="hidden" name="option" value="com_vikrentitems"/>
			<input type="hidden" name="task" value="cancelrequest"/>
		</form>
	</div>
</div>
	<?php
}