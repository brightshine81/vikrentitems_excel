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
$session = JFactory::getSession();
$document = JFactory::getDocument();
if (VikRentItems::loadJquery()) {
	JHtml::_('jquery.framework', true, true);
	JHtml::_('script', VRI_SITE_URI.'resources/jquery-1.12.4.min.js', false, true, false, false);
}
$vri_app = VikRentItems::getVriApplication();
$vrisessioncart = $this->vrisessioncart;
$maxdeliverycost = VikRentItems::getDeliveryMaxCost();
$days = $this->days;
$calcdays = $this->calcdays;
if ((int)$days != (int)$calcdays) {
	$origdays = $days;
	$days = $calcdays;
}
$coupon = $this->coupon;
$first = $this->first;
$second = $this->second;
$ftitle = $this->ftitle;
$place = $this->place;
$returnplace = $this->returnplace;
$payments = $this->payments;
$cfields = $this->cfields;
$customer_details = $this->customer_details;
$countries = $this->countries;
$vri_tn = $this->vri_tn;

$pitemid = VikRequest::getInt('Itemid', '', 'request');

$price = @count($vrisessioncart) ? $vrisessioncart[key($vrisessioncart)][0]['price'] : null;

$relations = array();
if (is_array($vrisessioncart) && count($vrisessioncart) > 0) {
	$relations = VikRentItems::loadRelatedItems(array_keys($vrisessioncart));
	$dailyandhourlyrates = false;
	$dailyrates = false;
	$hourlyrates = false;
	$usedhours = '';
	foreach ($vrisessioncart as $iditem => $itemarrparent) {
		foreach ($itemarrparent as $k => $itemarr) {
			if (array_key_exists('hours', $itemarr['price'])) {
				$hourlyrates = true;
				$usedhours = $itemarr['price']['hours'];
			} else {
				$dailyrates = true;
			}
		}
	}
	if ($dailyrates === true && $hourlyrates === true) {
		$dailyandhourlyrates = true;
	}
}

if (@is_array($cfields)) {
	foreach ($cfields as $cf) {
		if (!empty($cf['poplink'])) {
			$mbox_opts = '{
				"helpers": {
					"overlay": {
						"locked": false
					}
				},
				"width": "70%",
				"height": "75%",
				"autoScale": true,
				"transitionIn": "none",
				"transitionOut": "none",
				"padding": 0,
				"type": "iframe"
			}';
			$vri_app->prepareModalBox('.vrimodal', $mbox_opts);
			break;
		}
	}
}
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
if (VikRentItems::tokenForm()) {
	$vikt = uniqid(rand(17, 1717), true);
	$session->set('vikrtoken', $vikt);
	$tok = "<input type=\"hidden\" name=\"viktoken\" value=\"" . $vikt . "\"/>\n";
} else {
	$tok = "";
}

$imp = 0;
$totdue = 0;
$totdelivery = 0;
$delivery_per_itunit = VikRentItems::isDeliveryPerItemUnit();
$delivery_per_order = VikRentItems::isDeliveryPerOrder();
$wop = array();

if (is_array($vrisessioncart) && count($vrisessioncart) > 0) {
	foreach ($vrisessioncart as $iditem => $itemarrparent) {
		foreach ($itemarrparent as $ind => $itemarr) {
			$imp += VikRentItems::sayCostMinusIva($itemarr['price']['cost'] * $itemarr['units'], $itemarr['price']['idprice']);
			$totdue += VikRentItems::sayCostPlusIva($itemarr['price']['cost'] * $itemarr['units'], $itemarr['price']['idprice']);
			//delivery service
			if (array_key_exists('delivery', $itemarr) && is_array($itemarr['delivery']) && count($itemarr['delivery']) > 0) {
				$nowdelcost = $itemarr['delivery']['vrideliverycost'];
				$overcostperunit = floatval(VikRentItems::getItemParam($itemarr['info']['params'], 'overdelcost'));
				if (!empty($overcostperunit) && $overcostperunit > 0.00) {
					$nowdelcost = $itemarr['delivery']['vrideliverydistance'] * $overcostperunit;
					if ($itemarr['delivery']['vrideliveryroundcost'] == 1) {
						$nowdelcost = round($nowdelcost);
					}
					if (!empty($itemarr['delivery']['vrideliverymaxcost']) && (float)$itemarr['delivery']['vrideliverymaxcost'] > 0 && $nowdelcost > (float)$itemarr['delivery']['vrideliverymaxcost']) {
						$nowdelcost = (float)$itemarr['delivery']['vrideliverymaxcost'];
					}
					// VRI 1.6 - Delivery per Item Unit (Quantity)
					if ($delivery_per_itunit) {
						$nowdelcost = $nowdelcost * $itemarr['units'];
					}
					//
				} elseif ((int)$itemarr['delivery']['vrideliveryelemid'] != (int)$itemarr['info']['id']) {
					$nowdelcost = $itemarr['delivery']['vrideliverydistance'] * $itemarr['delivery']['vrideliveryglobcostperunit'];
					if ($itemarr['delivery']['vrideliveryroundcost'] == 1) {
						$nowdelcost = round($nowdelcost);
					}
					if (!empty($itemarr['delivery']['vrideliverymaxcost']) && (float)$itemarr['delivery']['vrideliverymaxcost'] > 0 && $nowdelcost > (float)$itemarr['delivery']['vrideliverymaxcost']) {
						$nowdelcost = (float)$itemarr['delivery']['vrideliverymaxcost'];
					}
					// VRI 1.6 - Delivery per Item Unit (Quantity)
					if ($delivery_per_itunit) {
						$nowdelcost = $nowdelcost * $itemarr['units'];
					}
					//
				}
				$vrisessioncart[$iditem][$ind]['delivery']['vrideliverysaycost'] = $nowdelcost;
				$totdelivery += $totdelivery > 0 && $delivery_per_order ? 0 : $nowdelcost;
				if (!empty($maxdeliverycost) && (float)$maxdeliverycost > 0 && $totdelivery > (float)$maxdeliverycost) {
					$totdelivery = (float)$maxdeliverycost;
					$vrisessioncart[$iditem][$ind]['delivery']['vrideliverymaxcostreached'] = 1;
				}
			}
			//
			$wopstr = "";
			if (is_array($itemarr['options'])) {
				foreach ($itemarr['options'] as $selo) {
					$wopstr .= $selo['id'] . ":" . $selo['quan'] . (array_key_exists('specintv', $selo) ? '-'.$selo['specintv'] : '') . ";";
					$realcost = (intval($selo['perday']) == 1 ? ($selo['cost'] * $days * $selo['quan']) : ($selo['cost'] * $selo['quan']));
					if (!empty($selo['maxprice']) && $selo['maxprice'] > 0 && $realcost > $selo['maxprice']) {
						$realcost = $selo['maxprice'];
						if (intval($selo['hmany']) == 1 && intval($selo['quan']) > 1) {
							$realcost = $selo['maxprice'] * $selo['quan'];
						}
					}
					$opt_item_units = $selo['onceperitem'] ? 1 : $itemarr['units'];
					$imp += VikRentItems::sayOptionalsMinusIva($realcost * $opt_item_units, $selo['idiva']);
					$totdue += VikRentItems::sayOptionalsPlusIva($realcost * $opt_item_units, $selo['idiva']);
				}
			}
			$wop[$iditem][$ind] = $wopstr;
		}
	}
}

//delivery service
if ($totdelivery > 0) {
	$imp += VikRentItems::sayDeliveryMinusIva($totdelivery);
	$totdue += $totdelivery;
}
//
?>
<h2 class="vri-rental-summary-title"><?php echo JText::_('VRRIEPILOGOORD'); ?></h2>

<?php
// itinerary
$pickloc = VikRentItems::getPlaceInfo($place, $vri_tn);
$droploc = VikRentItems::getPlaceInfo($returnplace, $vri_tn);
?>

<div class="vri-summary-itinerary">
	<div class="vrirentforlocs">
		<div class="vrirentalfor">
		<?php
		if (is_array($price) && array_key_exists('hours', $price)) {
			?>
			<h3 class="vrirentalforone"><?php echo JText::_('VRIRENTALFOR'); ?> <?php echo (intval($price['hours']) == 1 ? "1 ".JText::_('VRIHOUR') : $price['hours']." ".JText::_('VRIHOURS')); ?></h3>
			<?php
		} else {
			?>
			<h3 class="vrirentalforone"><?php echo JText::_('VRIRENTALFOR'); ?> <?php echo (intval($days) == 1 ? "1 ".JText::_('VRDAY') : $days." ".JText::_('VRDAYS')); ?></h3>
			<?php
		}
		?>
		</div>

		<div class="vri-itinerary-confirmation">
			<div class="vri-itinerary-pickup">
				<h4><?php echo JText::_('VRPICKUP'); ?></h4>
			<?php
			if (count($pickloc)) {
				?>
				<div class="vri-itinerary-pickup-location">
					<i class="fa fa-location-arrow"></i>
					<div class="vri-itinerary-pickup-locdet">
						<span class="vri-itinerary-pickup-locname"><?php echo $pickloc['name']; ?></span>
					</div>
				</div>
				<?php
			}
			?>
				<div class="vri-itinerary-pickup-date">
					<i class="fas fa-calendar-alt"></i>
					<span class="vri-itinerary-pickup-date-day"><?php echo date($df, $first); ?></span>
					<span class="vri-itinerary-pickup-date-time"><?php echo date($nowtf, $first); ?></span>
				</div>
			</div>
			<div class="vri-itinerary-dropoff">
				<h4><?php echo JText::_('VRRETURN'); ?></h4>
			<?php
			if (count($droploc)) {
				?>
				<div class="vri-itinerary-dropoff-location">
					<i class="fa fa-location-arrow"></i>
					<div class="vri-itinerary-dropfff-locdet">
						<span class="vri-itinerary-dropoff-locname"><?php echo $droploc['name']; ?></span>
					</div>
				</div>
				<?php
			}
			?>
				<div class="vri-itinerary-dropoff-date">
					<i class="fas fa-calendar-alt"></i>
					<span class="vri-itinerary-dropoff-date-day"><?php echo !is_array($price) || !array_key_exists('hours', $price) ? date($df, $second) : ''; ?></span>
					<span class="vri-itinerary-dropoff-date-time"><?php echo date($nowtf, $second); ?></span>
				</div>
			</div>
		</div>
		
	</div>

</div>

<div class="table-responsive">
	<table class="table vritableorder">
		<tr class="vritableorderfrow"><td>&nbsp;</td><td align="center"><?php echo (is_array($price) && array_key_exists('hours', $price) ? JText::_('VRIHOURS') : JText::_('ORDDD')); ?></td><td align="center"><?php echo JText::_('ORDNOTAX'); ?></td><td align="center" ><span id="tax_col"><?php echo JText::_('ORDTAX'); ?></span></td><td align="center"><?php echo JText::_('ORDWITHTAX'); ?></td></tr>
	<?php
	$sf = 2;
	if (is_array($vrisessioncart) && count($vrisessioncart) > 0) {
		foreach ($vrisessioncart as $iditem => $itemarrparent) {
			foreach ($itemarrparent as $k => $itemarr) {
				$saywithout = VikRentItems::sayCostMinusIva($itemarr['price']['cost'] * $itemarr['units'], $itemarr['price']['idprice']);
				$saywith = VikRentItems::sayCostPlusIva($itemarr['price']['cost'] * $itemarr['units'], $itemarr['price']['idprice']);
			?>
		<tr>
			<td align="left">
				<div class="vriremoveitemdiv">
					<a href="<?php echo JRoute::_('index.php?option=com_vikrentitems&task=rmcartitem&elem='.$iditem.';'.$k.'&place='.$place.'&returnplace='.$returnplace.'&days='.$days.'&pickup='.$first.'&release='.$second); ?>" onclick="return confirm('<?php echo addslashes(JText::_('VRICARTCONFRMITEM')); ?>');"><i class="far fa-trash-alt"></i></a>
				</div>
				<strong><?php echo $itemarr['info']['name']; ?></strong> <?php echo ($itemarr['units'] > 1 ? '(x '.$itemarr['units'].')' : ''); ?><br/><?php echo VikRentItems::getPriceName($itemarr['price']['idprice'], $vri_tn).(!empty($itemarr['price']['attrdata']) ? "<br/>".VikRentItems::getPriceAttr($itemarr['price']['idprice'], $vri_tn).": ".$itemarr['price']['attrdata'] : ""); ?>
			</td>
			<td align="center"><?php echo (array_key_exists('timeslot', $itemarr) ? $itemarr['timeslot']['name'] : (array_key_exists('hours', $itemarr['price']) ? $itemarr['price']['hours'].($dailyandhourlyrates === true ? " (".JText::_('VRIHOURS').")" : "") : $days.($dailyandhourlyrates === true ? " (".JText::_('VRDAY').")" : ""))); ?></td>
			<td align="center"><span class="vricurrency"><?php echo $currencysymb; ?></span> <span class="vriprice"><?php echo VikRentItems::numberFormat($saywithout); ?></span></td>

			
			<td align="center">
				<?php
				$tax_amt = $saywith - $saywithout;

				if($tax_amt > 0 ):

				?>
				<span class="vricurrency"><?php echo $currencysymb; ?></span> 
				<span class="vriprice">

					<?php echo VikRentItems::numberFormat($saywith - $saywithout); ?>
					

				</span>
				<?php

				endif;

				?>
			</td>
			<?php
//}
			?>
			<td align="center"><span class="vricurrency"><?php echo $currencysymb; ?></span> <span class="vriprice"><?php echo VikRentItems::numberFormat($saywith); ?></span></td>
		</tr>
			<?php
				if (is_array($itemarr['options'])) {
					foreach ($itemarr['options'] as $aop) {
						if (intval($aop['perday']) == 1) {
							$thisoptcost = ($aop['cost'] * $aop['quan']) * $days;
						} else {
							$thisoptcost = $aop['cost'] * $aop['quan'];
						}
						if (!empty($aop['maxprice']) && $aop['maxprice'] > 0 && $thisoptcost > $aop['maxprice']) {
							$thisoptcost = $aop['maxprice'];
							if (intval($aop['hmany']) == 1 && intval($aop['quan']) > 1) {
								$thisoptcost = $aop['maxprice'] * $aop['quan'];
							}
						}
						$opt_item_units = $aop['onceperitem'] ? 1 : $itemarr['units'];
						$optwithout = VikRentItems::sayOptionalsMinusIva($thisoptcost * $opt_item_units, $aop['idiva']);
						$optwith = VikRentItems::sayOptionalsPlusIva($thisoptcost * $opt_item_units, $aop['idiva']);
						$opttax = VikRentItems::numberFormat($optwith - $optwithout);
						$aop['quan'] = $opt_item_units > 1 ? ($aop['quan'] * $opt_item_units) : $aop['quan'];
						?>
			<tr<?php echo (($sf % 2) == 0 ? " class=\"vriordrowtwo\"" : " class=\"vriordrowone\""); ?>>
				<td><?php echo $aop['name'].($aop['quan'] > 1 ? " <small>(x ".$aop['quan'].")</small>" : ""); ?></td>
				<td align="center"><?php echo (array_key_exists('timeslot', $itemarr) ? $itemarr['timeslot']['name'] : (array_key_exists('hours', $itemarr['price']) ? $itemarr['price']['hours'].($dailyandhourlyrates === true ? " (".JText::_('VRIHOURS').")" : "") : $days.($dailyandhourlyrates === true ? " (".JText::_('VRDAY').")" : ""))); ?></td>
				<td align="center"><span class="vricurrency"><?php echo $currencysymb; ?></span> <span class="vriprice"><?php echo VikRentItems::numberFormat($optwithout); ?></span></td>
				<td align="center">

					<!-- <span class="vricurrency"><?php echo $currencysymb; ?></span> <span class="vriprice"><?php echo VikRentItems::numberFormat($opttax); ?></span> -->

					<?php

				if($opttax > 0 ):

				?>
				<span class="vricurrency"><?php echo $currencysymb; ?></span> 
				<span class="vriprice">

					<?php echo VikRentItems::numberFormat($opttax); ?>
					

				</span>
				<?php

				endif;

				?>




				</td>
				<td align="center"><span class="vricurrency"><?php echo $currencysymb; ?></span> <span class="vriprice"><?php echo VikRentItems::numberFormat($optwith); ?></span></td>
			</tr>
						<?php
						$sf++;
					}
				}
				//delivery service
				if (array_key_exists('delivery', $itemarr) && is_array($itemarr['delivery']) && count($itemarr['delivery']) > 0) {
					$delnetcost = VikRentItems::sayDeliveryMinusIva($itemarr['delivery']['vrideliverysaycost']);
					$deltotcost = $itemarr['delivery']['vrideliverysaycost'];
					$deltottax = $deltotcost - $delnetcost;
					$deliverystrokeclass = (array_key_exists('vrideliverymaxcostreached', $itemarr['delivery']) && $itemarr['delivery']['vrideliverymaxcostreached'] == 1 ? ' vripricestroke' : '');
					?>
			<tr<?php echo (($sf % 2) == 0 ? " class=\"vriordrowtwo\"" : " class=\"vriordrowone\""); ?>>
				<td colspan="2"><span class="vrisummarydeliveryto"><?php echo JText::_('VRISUMMARYDELIVERYTO').' '.$itemarr['delivery']['vrideliveryaddress']; ?></span></td>
					<?php
					if (!$delivery_per_order || count($vrisessioncart) < 2) {
					?>
				<td align="center"><span class="vricurrency"><?php echo $currencysymb; ?></span> <span class="vriprice<?php echo $deliverystrokeclass; ?>"><?php echo VikRentItems::numberFormat($delnetcost); ?></span></td>
				<td align="center"><span class="vricurrency"><?php echo $currencysymb; ?></span> <span class="vriprice<?php echo $deliverystrokeclass; ?>"><?php echo VikRentItems::numberFormat($deltottax); ?></span></td>
				<td align="center"><span class="vricurrency"><?php echo $currencysymb; ?></span> <span class="vriprice<?php echo $deliverystrokeclass; ?>"><?php echo VikRentItems::numberFormat($deltotcost); ?></span></td>
					<?php
					} else {
					?>
				<td align="center">&nbsp;</td>
				<td align="center">&nbsp;</td>
				<td align="center">&nbsp;</td>
					<?php
					}
					?>
			</tr>
					<?php
				}
				//end delivery service
			}
		}
	}

	if (!empty($place) && !empty($returnplace) && is_array($vrisessioncart) && count($vrisessioncart) > 0) {
		$locfee = VikRentItems::getLocFee($place, $returnplace);
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
				if (array_key_exists($days, $arrvaloverrides)) {
					$locfee['cost'] = $arrvaloverrides[$days];
				}
			}
			//end VikRentItems 1.1 - Location fees overrides
			$locfeecost = intval($locfee['daily']) == 1 ? ($locfee['cost'] * $days) : $locfee['cost'];
			$locfeewithout = VikRentItems::sayLocFeeMinusIva($locfeecost, $locfee['idiva']);
			$locfeewith = VikRentItems::sayLocFeePlusIva($locfeecost, $locfee['idiva']);
			$locfeetax = VikRentItems::numberFormat($locfeewith - $locfeewithout);
			$imp += $locfeewithout;
			$totdue += $locfeewith;
			?>
		<tr<?php echo (($sf % 2) == 0 ? " class=\"vriordrowtwo\"" : " class=\"vriordrowone\"");?>>
			<td><?php echo JText::_('VRLOCFEETOPAY'); ?></td>
			<td align="center"><?php echo (is_array($price) && array_key_exists('hours', $price) ? $price['hours'] : $days); ?></td>
			<td align="center"><span class="vricurrency"><?php echo $currencysymb; ?></span> <span class="vriprice"><?php echo VikRentItems::numberFormat($locfeewithout); ?></span></td>
			<td align="center"><span class="vricurrency"><?php echo $currencysymb; ?></span> <span class="vriprice"><?php echo VikRentItems::numberFormat($locfeetax); ?></span></td>
			<td align="center"><span class="vricurrency"><?php echo $currencysymb; ?></span> <span class="vriprice"><?php echo VikRentItems::numberFormat($locfeewith); ?></span></td>
		</tr>
			<?php

		}
	}

	//store Order Total in session for modules
	$session->set('vikrentitems_ordertotal', $totdue);
	//

	//vikrentitems 1.1
	$origtotdue = $totdue;
	$usedcoupon = false;
	if (is_array($coupon)) {
		//check min tot ord
		$coupontotok = true;
		if (strlen($coupon['mintotord']) > 0) {
			if ($totdue < $coupon['mintotord']) {
				$coupontotok = false;
			}
		}
		if ($coupontotok == true) {
			$usedcoupon = true;
			if ($coupon['percentot'] == 1) {
				//percent value
				$minuscoupon = 100 - $coupon['value'];
				$couponsave = $totdue * $coupon['value'] / 100;
				$totdue = $totdue * $minuscoupon / 100;
			} else {
				//total value
				$couponsave = $coupon['value'];
				$totdue = $totdue - $coupon['value'];
			}
		} else {
			VikError::raiseWarning('', JText::_('VRICOUPONINVMINTOTORD'));
		}
	}
	//
	?>
		<tr height="20px"><td colspan="5" height="20px">&nbsp;</td></tr>	
			<?php
			if ($totdelivery > 0) {
				$totdeliverynet = VikRentItems::sayDeliveryMinusIva($totdelivery);
				$totdeliverytax = $totdelivery - $totdeliverynet;
				?>
		<tr class="vriordrowdelivery">
			<td colspan="2"><?php echo JText::_('VRISUMMARYDELIVERYSERVICE'); ?></td>
			<td align="center"><span class="vricurrency"><?php echo $currencysymb; ?></span> <span class="vriprice"><?php echo VikRentItems::numberFormat($totdeliverynet); ?></span></td>
			<td align="center"><span class="vricurrency"><?php echo $currencysymb; ?></span> <span class="vriprice"><?php echo VikRentItems::numberFormat($totdeliverytax); ?></span></td>
			<td align="center" class="vritotaldelivery"><span class="vricurrency"><?php echo $currencysymb; ?></span> <span class="vriprice"><?php echo VikRentItems::numberFormat($totdelivery); ?></span></td>
		</tr>
		<tr height="10px"><td colspan="5" height="10px">&nbsp;</td></tr>
				<?php
			}
			?>
		<tr class="vriordrowtotal">
			<td><?php echo JText::_('VRTOTAL'); ?></td>
			<td align="center">&nbsp;</td>
			<td align="center"><span class="vricurrency"><?php echo $currencysymb; ?></span> <span class="vriprice"><?php echo VikRentItems::numberFormat($imp); ?></span></td>
			<td align="center">
				<?php

				$tot_amt = $origtotdue - $imp;
				if($tot_amt>0)
				{

				?>

				<span class="vricurrency"><?php echo $currencysymb; ?></span> 
				<span class="vriprice"><?php echo VikRentItems::numberFormat(($origtotdue - $imp)); ?></span>
				<?php
			}
			else
			{

				?>
				<style>
					#tax_col{
						display: none;
					}
				</style>

				<?php
			}
				?>
			</td>
			<td align="center" class="vritotalord"><span class="vricurrency"><?php echo $currencysymb; ?></span> <span class="vriprice"><?php echo VikRentItems::numberFormat($origtotdue); ?></span></td>
		</tr>
			<?php
			if ($usedcoupon == true) {
				?>
		<tr class="vriordrowtotal">
			<td><?php echo JText::_('VRICOUPON'); ?> <?php echo $coupon['code']; ?></td>
			<td align="center">&nbsp;</td>
			<td align="center">&nbsp;</td>
			<td align="center">&nbsp;</td>
			<td align="center" class="vritotalord">
				<span class="vricurrency">- <?php echo $currencysymb; ?></span> 
				<span class="vriprice"><?php echo VikRentItems::numberFormat($couponsave); ?></span>
			</td>
		</tr>
		<tr class="vriordrowtotal">
			<td><?php echo JText::_('VRINEWTOTAL'); ?></td>
			<td align="center">&nbsp;</td>
			<td align="center">&nbsp;</td>
			<td align="center">&nbsp;</td>
			<td align="center" class="vritotalord"><span class="vricurrency"><?php echo $currencysymb; ?></span> <span class="vriprice"><?php echo VikRentItems::numberFormat($totdue); ?></span></td>
		</tr>
				<?php
			}
			?>
	</table>
</div>

<script type="text/javascript">
function vriConfirmEmptyCart() {
	document.getElementById('vriemptycartconfirmbox').style.display = 'block';
	document.getElementById('vrichangedatesconfirmbox').style.display = 'none';
}
function vriCancelEmptyCart() {
	document.getElementById('vriemptycartconfirmbox').style.display = 'none';
	document.getElementById('vrichangedatesconfirmbox').style.display = 'none';
}
function vriConfirmChangeDates() {
	document.getElementById('vrichangedatesconfirmbox').style.display = 'block';
	document.getElementById('vriemptycartconfirmbox').style.display = 'none';
}
function vriCancelChangeDates() {
	document.getElementById('vrichangedatesconfirmbox').style.display = 'none';
	document.getElementById('vriemptycartconfirmbox').style.display = 'none';
}
function vriGotoConfirmForm() {
	jQuery.noConflict();
	jQuery('html,body').animate({ scrollTop: (jQuery("#vriconfordformanchor").offset().top - 2) }, { duration: 'slow' });
}
</script>
<div class="vrioconfirmbuttonsdiv">
	<div class="vriemptycartdivcontainer">
		<div class="vriemptycartdiv">
			<a href="javascript: void(0);" onclick="vriConfirmEmptyCart();"><?php echo JText::_('VRIEMPTYCART'); ?></a>
		</div>
		<div class="vriemptycartconfirmbox" id="vriemptycartconfirmbox">
			<span><?php echo JText::_('VRIEMPTYCARTCONFIRM'); ?></span>
			<a href="<?php echo JRoute::_('index.php?option=com_vikrentitems&task=emptycart&place='.$place.'&returnplace='.$returnplace.'&days='.$days.'&pickup='.$first.'&release='.$second); ?>" class="vri-summary-emptybut vri-summary-yes"><?php echo JText::_('VRIYES'); ?></a>
			<a href="javascript: void(0);" class="vri-summary-emptybut vri-summary-no" onclick="vriCancelEmptyCart();"><?php echo JText::_('VRINO'); ?></a>
		</div>
	</div>
	<div class="vrichangedatesdivcontainer">
		<div class="vrichangedatesdiv">
			<a href="javascript: void(0);" onclick="vriConfirmChangeDates();"><?php echo JText::_('VRICHANGEDATES'); ?></a>
		</div>
		<div class="vrichangedatesconfirmbox" id="vrichangedatesconfirmbox">
			<span><?php echo JText::_('VRICHANGEDATESCONFIRM'); ?></span>
			<a href="<?php echo JRoute::_('index.php?option=com_vikrentitems&task=emptycart&place='.$place.'&returnplace='.$returnplace.'&days='.$days.'&pickup='.$first.'&release='.$second); ?>" class="vri-summary-chdbut vri-summary-yes"><?php echo JText::_('VRIYES'); ?></a>
			<a href="javascript: void(0);" class="vri-summary-chdbut vri-summary-no" onclick="vriCancelChangeDates();"><?php echo JText::_('VRINO'); ?></a>
		</div>
	</div>
	<?php
	if (is_array($vrisessioncart) && count($vrisessioncart) > 0) {
	?>
	<div class="vricompleteorderdiv">
		<a href="javascript: void(0);" onclick="vriGotoConfirmForm();" class="btn"><?php echo JText::_('VRICOMPLETEYOURORDER'); ?></a>
	</div>
	<?php
	}
	?>
</div>

<div class="vri-oconfirm-middlep">
<?php
// coupon code
if (VikRentItems::couponsEnabled() && !is_array($coupon)) {
	?>
	<div class="vri-coupon-outer">
		<form action="<?php echo JRoute::_('index.php?option=com_vikrentitems'); ?>" method="post">
			<div class="vrientercoupon">
			<span class="vrihaveacoupon"><?php echo JText::_('VRIHAVEACOUPON'); ?></span><input type="text" name="couponcode" value="" size="20" class="vriinputcoupon"/><input type="submit" class="btn vrisubmitcoupon" name="applyacoupon" value="<?php echo JText::_('VRISUBMITCOUPON'); ?>"/>
			</div>
			<input type="hidden" name="place" value="<?php echo $place; ?>"/>
			<input type="hidden" name="returnplace" value="<?php echo $returnplace; ?>"/>
			<input type="hidden" name="days" value="<?php echo $days; ?>"/>
			<input type="hidden" name="pickup" value="<?php echo $first; ?>"/>
			<input type="hidden" name="release" value="<?php echo $second; ?>"/>
			<input type="hidden" name="task" value="oconfirm"/>
		</form>
	</div>
	<?php
}

// Customers PIN
if (VikRentItems::customersPinEnabled() && !VikRentItems::userIsLogged() && !(count($customer_details) > 0)) {
	?>
	<div class="vri-enterpin-block">
		<div class="vri-enterpin-top">
			<span><span><?php echo JText::_('VRRETURNINGCUSTOMER'); ?></span><?php echo JText::_('VRENTERPINCODE'); ?></span>
			<input type="text" id="vri-pincode-inp" value="" size="6"/>
			<button type="button" class="btn vri-pincode-sbmt"><?php echo JText::_('VRAPPLYPINCODE'); ?></button>
		</div>
		<div class="vri-enterpin-response"></div>
	</div>
	<script>
	jQuery(document).ready(function() {
		jQuery(".vri-pincode-sbmt").click(function() {
			var pin_code = jQuery("#vri-pincode-inp").val();
			jQuery(this).prop('disabled', true);
			jQuery(".vri-enterpin-response").hide();
			jQuery.ajax({
				type: "POST",
				url: "<?php echo JRoute::_('index.php?option=com_vikrentitems&task=validatepin&tmpl=component'.(!empty($pitemid) ? '&Itemid='.$pitemid : ''), false); ?>",
				data: { pin: pin_code }
			}).done(function(res) {
				var pinobj = jQuery.parseJSON(res);
				if (pinobj.hasOwnProperty('success')) {
					jQuery(".vri-enterpin-top").hide();
					jQuery(".vri-enterpin-response").removeClass("vri-enterpin-error").addClass("vri-enterpin-success").html("<span class=\"vri-enterpin-welcome\"><?php echo addslashes(JText::_('VRWELCOMEBACK')); ?></span><span class=\"vri-enterpin-customer\">"+pinobj.first_name+" "+pinobj.last_name+"</span>").fadeIn();
					jQuery.each(pinobj.cfields, function(k, v) {
						if (jQuery("#vrif-inp"+k).length) {
							jQuery("#vrif-inp"+k).val(v);
						}						
					});
					var user_country = pinobj.country;
					if (jQuery(".vrif-countryinp").length && user_country.length) {
						jQuery(".vrif-countryinp option").each(function(i){
							var opt_country = jQuery(this).val();
							if (opt_country.substring(0, 3) == user_country) {
								jQuery(this).prop("selected", true);
								return false;
							}
						});
					}
				} else {
					jQuery(".vri-enterpin-response").addClass("vri-enterpin-error").html("<p><?php echo addslashes(JText::_('VRINVALIDPINCODE')); ?></p>").fadeIn();
					jQuery(".vri-pincode-sbmt").prop('disabled', false);
				}
			}).fail(function(){
				alert('Error validating the PIN. Request failed.');
				jQuery(".vri-pincode-sbmt").prop('disabled', false);
			});
		});
	});
	</script>
	<?php
}
?>
</div>

<?php
//Continue Renting Items
	?>
<div class="vricontinuerentdiv">
	<h4><?php echo JText::_('VRICONTINUERENTING'); ?></h4>
	<form action="<?php echo JRoute::_('index.php?option=com_vikrentitems'); ?>" method="post">
	<?php
	if (VikRentItems::showCategoriesFront()) {
		$q = "SELECT * FROM `#__vikrentitems_categories` ORDER BY `#__vikrentitems_categories`.`name` ASC;";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$categories = $dbo->loadAssocList();
			$vri_tn->translateContents($categories, '#__vikrentitems_categories');
			$catform = "<select name=\"categories\" id=\"continuecategories\">\n";
			$catform .= "<option value=\"all\">" . JText::_('VRALLCAT') . "</option>\n";
			foreach ($categories as $cat) {
				$catform .= "<option value=\"" . $cat['id'] . "\">" . $cat['name'] . "</option>\n";
			}
			$catform .= "</select>\n";
		?>
		<div class="vricontinuecategory">
			<label for="continuecategories"><?php echo JText::_('VRICONTINUECATEGSEARCH'); ?></label>
			<?php echo $catform; ?>
		</div>
		<?php
		}
	}
	$continuetimeslot = '';
	if (is_array($vrisessioncart) && count($vrisessioncart) > 0) {
		foreach ($vrisessioncart as $iditem => $itemarrparent) {
			foreach ($itemarrparent as $k => $itemarr) {
				if (array_key_exists('timeslot', $itemarr)) {
					$continuetimeslot = '<input type="hidden" name="timeslot" value="'.$itemarr['timeslot']['id'].'"/>'."\n";
					break;
				}
			}
		}
	}
	echo $continuetimeslot;
	?>
		<input type="hidden" name="place" value="<?php echo $place; ?>"/>
		<input type="hidden" name="returnplace" value="<?php echo $returnplace; ?>"/>
		<input type="hidden" name="pickupdate" value="<?php echo date($df, $first); ?>"/>
		<input type="hidden" name="pickuph" value="<?php echo date('H', $first); ?>"/>
		<input type="hidden" name="pickupm" value="<?php echo date('i', $first); ?>"/>
		<input type="hidden" name="releasedate" value="<?php echo date($df, $second); ?>"/>
		<input type="hidden" name="releaseh" value="<?php echo date('H', $second); ?>"/>
		<input type="hidden" name="releasem" value="<?php echo date('i', $second); ?>"/>
		<input type="hidden" name="task" value="search"/>
		<input type="hidden" name="option" value="com_vikrentitems"/>
		<input type="submit" name="searchmore" value="<?php echo JText::_('VRICONTINUESEARCH'); ?>" class="btn booknow"/>
		<?php
		if (!empty($pitemid)) {
		?>
			<input type="hidden" name="Itemid" value="<?php echo $pitemid; ?>"/>
			<?php
		}
		?>
	</form>
</div>
	<?php
//

//Related Items
if (is_array($relations) && count($relations) > 0) {
	shuffle($relations);
	?>
<div class="vri-summary-interested">
	<h4 class="vriyoumightintp"><?php echo JText::_('VRIMIGHTINTEREST'); ?></h4>
	
	<div class="vrirelateditemsdivscroll">
		<ul class="vrirelateditems" id="vrirelateditemsulscroll">
		<?php
		foreach ($relations as $rel) {
			$item_params = !empty($rel['jsparams']) ? json_decode($rel['jsparams'], true) : array();
			$imgpath = file_exists(VRI_ADMIN_PATH.DS.'resources'.DS.'vthumb_'.$rel['img']) ? VRI_ADMIN_URI.'resources/vthumb_'.$rel['img'] : VRI_ADMIN_URI.'resources/'.$rel['img'];
			?>
			<li class="vrirelitemdiv">
				<form action="<?php echo JRoute::_('index.php?option=com_vikrentitems'); ?>" method="post">
					<input type="hidden" name="option" value="com_vikrentitems"/>
		  			<input type="hidden" name="itemopt" value="<?php echo $rel['id']; ?>"/>
		  			<input type="hidden" name="days" value="<?php echo $days; ?>"/>
		  			<input type="hidden" name="pickup" value="<?php echo $first; ?>"/>
		  			<input type="hidden" name="release" value="<?php echo $second; ?>"/>
		  			<input type="hidden" name="place" value="<?php echo $place; ?>"/>
		  			<input type="hidden" name="returnplace" value="<?php echo $returnplace; ?>"/>
		  			<input type="hidden" name="task" value="showprc"/>
		  			<div class="vrirelitemimgdiv"><img class="vrirelitemimg" alt="<?php echo $rel['name']; ?>" src="<?php echo $imgpath; ?>"/></div>
		  			<span class="vrirelitemname"><?php echo $rel['name']; ?></span>
		  			<?php
					if ($rel['askquantity'] == 1) {
						if (intval(VikRentItems::getItemParam($rel['params'], 'discsquantstab')) == 1) {
							$q = "SELECT * FROM `#__vikrentitems_discountsquants` WHERE `iditems` LIKE '%-".$rel['id']."-%' ORDER BY `#__vikrentitems_discountsquants`.`quantity` ASC;";
							$dbo->setQuery($q);
							$dbo->execute();
							if ($dbo->getNumRows() > 0) {
								$discounts = $dbo->loadAssocList();
								?>
								<div class="vridiscsquantsdivsearchrel">
									<table class="vridiscsquantstablesearchrel">
										<tr class="vridiscsquantstrfirstsearchrel"><td><?php echo JText::_('VRIDISCSQUANTSQ'); ?></td><td><?php echo JText::_('VRIDISCSQUANTSSAVE'); ?></td></tr>
										<?php
										foreach ($discounts as $kd => $disc) {
											$discval = substr($disc['diffcost'], -2) == '00' ? number_format($disc['diffcost'], 0) : VikRentItems::numberFormat($disc['diffcost']);
											$savedisc = $disc['val_pcent'] == 1 ? $currencysymb.' '.$discval : $discval.'%';
											$disc_keys = array_keys($discounts);
											?>
										<tr class="vridiscsquantstrentrysearchrel">
											<td><?php echo $disc['quantity'].(end($disc_keys) == $kd && $disc['ifmorequant'] == 1 ? ' '.JText::_('VRIDISCSQUANTSORMORE') : ''); ?></td>
											<td><?php echo $savedisc; ?></td>
										</tr>	
											<?php
										}
										?>
									</table>
								</div>
								<?php
							}
						}
					?>
					<div class="vriselectquantitydivrelmain">
						<div class="vriselectquantitydivrel">
							<label for="itemquant-<?php echo $rel['id']; ?>"><?php echo JText::_('VRIQUANTITYX'); ?></label>
							<input type="number" name="itemquant" id="itemquant-<?php echo $rel['id']; ?>" value="<?php echo (!array_key_exists('minquant', $item_params) || empty($item_params['minquant']) ? '1' : (int)$item_params['minquant']); ?>" min="<?php echo (!array_key_exists('minquant', $item_params) || empty($item_params['minquant']) ? '1' : (int)$item_params['minquant']); ?>" max="<?php echo $rel['units']; ?>" class="vrismallinput vri-numbinput"/>
						</div>
					<?php
					} else {
						?>
					<div class="vriselectquantitydivrelmain">	
						<?php
					}
					?>
						<div class="vrirelitemsubmitdiv"><input type="submit" name="goon" value="<?php echo JText::_('VRIMIGHTINTERESTBOOK'); ?>" class="btn vrirelitemsubmit"/></div>
					</div>
				</form>
			</li>
			<?php
		}
		?>
		</ul>
	</div>
</div>

<script type="text/javascript">
document.getElementById('vrirelateditemsulscroll').style.width = '<?php echo ((count($relations) * 220) + 100); ?>px';
</script>

<?php
}
//End Related Items

?>
<script type="text/javascript">
function checkvriFields(){
	var vrvar = document.vri;
	<?php
if (@is_array($cfields)) {
	foreach ($cfields as $cf) {
		if (intval($cf['required']) == 1) {
			if ($cf['type'] == "text" || $cf['type'] == "textarea" || $cf['type'] == "date" || $cf['type'] == "country") {
			?>
	if (!vrvar.vrif<?php echo $cf['id']; ?>.value.match(/\S/)) {
		document.getElementById('vrif<?php echo $cf['id']; ?>').style.color='#ff0000';
		return false;
	} else {
		document.getElementById('vrif<?php echo $cf['id']; ?>').style.color='';
	}
			<?php

			} elseif ($cf['type'] == "select") {
			?>
	if (!vrvar.vrif<?php echo $cf['id']; ?>.value.match(/\S/)) {
		document.getElementById('vrif<?php echo $cf['id']; ?>').style.color='#ff0000';
		return false;
	} else {
		document.getElementById('vrif<?php echo $cf['id']; ?>').style.color='';
	}
			<?php
			} elseif ($cf['type'] == "checkbox") {
				//checkbox
			?>
	if (vrvar.vrif<?php echo $cf['id']; ?>.checked) {
		document.getElementById('vrif<?php echo $cf['id']; ?>').style.color='';
	} else {
		document.getElementById('vrif<?php echo $cf['id']; ?>').style.color='#ff0000';
		return false;
	}
			<?php
			}
		}
	}
}
?>
	return true;
}
</script>

<?php
if (is_array($vrisessioncart) && count($vrisessioncart) > 0) {
	?>	
<a name="vriconfordformanchor" id="vriconfordformanchor"></a>

<div class="vri-oconfirm-mainf-cont">
	<form action="<?php echo JRoute::_('index.php?option=com_vikrentitems'); ?>" name="vri" method="post" onsubmit="javascript: return checkvriFields();">
	<?php
if (@is_array($cfields)) {
	?>
		<div class="vricustomfields">
	<?php
	$currentUser = JFactory::getUser();
	$useremail = !empty($currentUser->email) ? $currentUser->email : "";
	$useremail = array_key_exists('email', $customer_details) ? $customer_details['email'] : $useremail;
	$previousdata = VikRentItems::loadPreviousUserData($currentUser->id);
	$nominatives = array();
	if (count($customer_details) > 0) {
		$nominatives[] = $customer_details['first_name'];
		$nominatives[] = $customer_details['last_name'];
	}
	foreach ($cfields as $cf) {
		if (intval($cf['required']) == 1) {
			$isreq = "<span class=\"vrirequired\"><sup>*</sup></span> ";
		} else {
			$isreq = "";
		}
		if (!empty($cf['poplink'])) {
			$fname = "<a href=\"" . $cf['poplink'] . "\" id=\"vrif" . $cf['id'] . "\" rel=\"{handler: 'iframe', size: {x: 750, y: 600}}\" target=\"_blank\" class=\"vrimodal\">" . JText::_($cf['name']) . "</a>";
		} else {
			$fname = "<label id=\"vrif" . $cf['id'] . "\" for=\"vrif-inp" . $cf['id'] . "\">" . JText::_($cf['name']) . "</label>";
		}
		if ($cf['type'] == "text") {
			$textmailval = intval($cf['isemail']) == 1 ? $useremail : "";
			$textmailval = array_key_exists($cf['id'], $previousdata['customfields']) ? $previousdata['customfields'][$cf['id']] : $textmailval;
			if ($cf['isphone'] == 1) {
				if (array_key_exists('phone', $customer_details)) {
					$textmailval = $customer_details['phone'];
				}
			} elseif ($cf['isnominative'] == 1) {
				if (count($nominatives) > 0) {
					$textmailval = array_shift($nominatives);
				}
			} elseif (array_key_exists('cfields', $customer_details)) {
				if (array_key_exists($cf['id'], $customer_details['cfields'])) {
					$textmailval = $customer_details['cfields'][$cf['id']];
				}
			}
		?>
			<div class="vridivcustomfield">
				<div class="vri-customfield-label">
					<?php echo $isreq; ?><?php echo $fname; ?>
				</div>
				<div class="vri-customfield-input">
					<input type="text" name="vrif<?php echo $cf['id']; ?>" id="vrif-inp<?php echo $cf['id']; ?>" value="<?php echo $textmailval; ?>" size="40" class="vriinput"/>
				</div>
			</div>
		<?php
		} elseif ($cf['type'] == "textarea") {
			$defaultval = array_key_exists($cf['id'], $previousdata['customfields']) ? $previousdata['customfields'][$cf['id']] : '';
			if (isset($customer_details['cfields']) && array_key_exists($cf['id'], $customer_details['cfields'])) {
				$defaultval = $customer_details['cfields'][$cf['id']];
			}
		?>
			<div class="vridivcustomfield">
				<div class="vri-customfield-label">
					<?php echo $isreq; ?><?php echo $fname; ?>
				</div>
				<div class="vri-customfield-input">
					<textarea name="vrif<?php echo $cf['id']; ?>" id="vrif-inp<?php echo $cf['id']; ?>" rows="5" cols="30" class="vritextarea"><?php echo $defaultval; ?></textarea>
				</div>
			</div>
		<?php
		} elseif ($cf['type'] == "date") {
			$defaultval = array_key_exists($cf['id'], $previousdata['customfields']) ? $previousdata['customfields'][$cf['id']] : '';
			?>
			<div class="vridivcustomfield">
				<div class="vri-customfield-label">
					<?php echo $isreq; ?><?php echo $fname; ?>
				</div>
				<div class="vri-customfield-input">
					<?php echo $vri_app->getCalendar('', 'vrif'.$cf['id'], 'vrif-inp'.$cf['id'], $nowdf, array('class'=>'vriinput', 'size'=>'10', 'value'=>$defaultval, 'maxlength'=>'19')); ?>
				</div>
			</div>
			<?php
			if (!empty($defaultval)) {
			?>
			<script type="text/javascript">
			jQuery(document).ready(function() {
				jQuery('#vrif<?php echo $cf['id']; ?>date').val('<?php echo addslashes($defaultval); ?>');
			});
			</script>
			<?php
			}
			?>
		<?php
		} elseif ($cf['type'] == "country" && is_array($countries)) {
			$defaultval = array_key_exists($cf['id'], $previousdata['customfields']) ? $previousdata['customfields'][$cf['id']] : '';
			if (array_key_exists('country', $customer_details)) {
				$defaultval = !empty($customer_details['country']) ? substr($customer_details['country'], 0, 3) : '';
			}
			$countries_sel = '<select name="vrif'.$cf['id'].'" class="vrif-countryinp"><option value=""></option>'."\n";
			foreach ($countries as $country) {
				$countries_sel .= '<option value="'.$country['country_3_code'].'::'.$country['country_name'].'"'.($defaultval == $country['country_3_code'] ? ' selected="selected"' : '').'>'.$country['country_name'].'</option>'."\n";
			}
			$countries_sel .= '</select>';
			?>
			<div class="vridivcustomfield">
				<div class="vri-customfield-label">
					<?php echo $isreq; ?><?php echo $fname; ?>
				</div>
				<div class="vri-customfield-input">
					<?php echo $countries_sel; ?>
				</div>
			</div>
		<?php
		} elseif ($cf['type'] == "select") {
			$defaultval = array_key_exists($cf['id'], $previousdata['customfields']) ? $previousdata['customfields'][$cf['id']] : '';
			$answ = explode(";;__;;", $cf['choose']);
			$wcfsel = "<select name=\"vrif" . $cf['id'] . "\">\n";
			foreach ($answ as $aw) {
				if (!empty($aw)) {
					$wcfsel .= "<option value=\"" . JText::_($aw) . "\"".($defaultval == JText::_($aw) ? ' selected="selected"' : '').">" . JText::_($aw) . "</option>\n";
				}
			}
			$wcfsel .= "</select>\n";
		?>
			<div class="vridivcustomfield">
				<div class="vri-customfield-label">
					<?php echo $isreq; ?><?php echo $fname; ?>
				</div>
				<div class="vri-customfield-input">
					<?php echo $wcfsel; ?>
				</div>
			</div>
		<?php
		} elseif ($cf['type'] == "separator") {
			$cfsepclass = strlen(JText::_($cf['name'])) > 30 ? "vriseparatorcflong" : "vriseparatorcf";
		?>
			<div class="vridivcustomfield vricustomfldinfo">
				<div class="<?php echo $cfsepclass; ?>"><?php echo JText::_($cf['name']); ?></div>
			</div>
		<?php
		} else {
		?>
			<div class="vridivcustomfield">
				<div class="vri-customfield-label">
					<?php echo $isreq; ?><?php echo $fname; ?>
				</div>
				<div class="vri-customfield-input">
					<input type="checkbox" name="vrif<?php echo $cf['id']; ?>" id="vrif-inp<?php echo $cf['id']; ?>" value="<?php echo JText::_('VRYES'); ?>"/>
				</div>
			</div>
		<?php

		}
	}
	?>
		</div>
		<?php
}
?>
		<input type="hidden" name="days" value="<?php echo $days; ?>"/>
	<?php
	//vikrentitems 1.1
	if (isset($origdays)) {
		?>
		<input type="hidden" name="origdays" value="<?php echo $origdays; ?>"/>
		<?php
	}
	//
	?>
		<input type="hidden" name="pickup" value="<?php echo $first; ?>"/>
		<input type="hidden" name="release" value="<?php echo $second; ?>"/>
		<input type="hidden" name="totdue" value="<?php echo $totdue; ?>"/>
		<input type="hidden" name="place" value="<?php echo $place; ?>"/>
		<input type="hidden" name="returnplace" value="<?php echo $returnplace; ?>"/>
	<?php
  	if (is_array($vrisessioncart) && count($vrisessioncart) > 0) {
		foreach ($vrisessioncart as $iditem => $itemarrparent) {
			foreach ($itemarrparent as $ind => $itemarr) {
				echo '<input type="hidden" name="item[]" value="'.$iditem.'"/>'."\n";
				echo '<input type="hidden" name="itemquant[]" value="'.$itemarr['units'].'"/>'."\n";
				echo '<input type="hidden" name="prtar[]" value="'.$itemarr['price']['id'].'"/>'."\n";
				echo '<input type="hidden" name="priceid[]" value="'.$itemarr['price']['idprice'].'"/>'."\n";
				echo '<input type="hidden" name="optionals[]" value="'.$wop[$iditem][$ind].'"/>'."\n";
				if (array_key_exists('timeslot', $itemarr)) {
					echo '<input type="hidden" name="timeslot[]" value="'.$itemarr['timeslot']['id'].'"/>'."\n";
				} else {
					echo '<input type="hidden" name="timeslot[]" value=""/>'."\n";
				}
				if (array_key_exists('delivery', $itemarr) && is_array($itemarr['delivery']) && count($itemarr['delivery']) > 0) {
					echo '<input type="hidden" name="delivery[]" value="'.$itemarr['delivery']['vrideliverysessid'].'"/>'."\n";
				} else {
					echo '<input type="hidden" name="delivery[]" value=""/>'."\n";
				}
			}
		}
  	}
	if ((is_array($price) && array_key_exists('hours', $price)) || $hourlyrates === true) {
		?>
		<input type="hidden" name="hourly" value="<?php echo $usedhours; ?>"/>	
		<?php
	}
	if ($usedcoupon == true && is_array($coupon)) {
		?>
		<input type="hidden" name="couponcode" value="<?php echo $coupon['code']; ?>"/>
		<?php
	}
	?>
		<?php echo $tok; ?>
		<input type="hidden" name="task" value="saveorder"/>
	<?php
	if (@is_array($payments)) {
	?>
		<div class="vri-oconfirm-paym-block">
			<h4 class="vri-medium-header"><?php echo JText::_('VRIHOOSEPAYMENT'); ?></h4>
			<ul class="vri-noliststyletype">
		<?php
		foreach ($payments as $pk => $pay) {
			$rcheck = $pk == 0 ? " checked=\"checked\"" : "";
			$saypcharge = "";
			if ($pay['charge'] > 0.00) {
				$decimals = $pay['charge'] - (int)$pay['charge'];
				if ($decimals > 0.00) {
					$okchargedisc = VikRentItems::numberFormat($pay['charge']);
				} else {
					$okchargedisc = number_format($pay['charge'], 0);
				}
				$saypcharge .= " (".($pay['ch_disc'] == 1 ? "+" : "-");
				$saypcharge .= "<span class=\"vriprice\">" . $okchargedisc . "</span> <span class=\"vricurrency\">" . ($pay['val_pcent'] == 1 ? $currencysymb : "%") . "</span>";
				$saypcharge .= ")";
			}
			?>
				<li><input type="radio" name="gpayid" value="<?php echo $pay['id']; ?>" id="gpay<?php echo $pay['id']; ?>"<?php echo $rcheck; ?>/> <label for="gpay<?php echo $pay['id']; ?>"><?php echo $pay['name'].$saypcharge; ?></label></li>
			<?php
		}
		?>
			</ul>
		</div>
		<?php
	}
	?>
		<input type="submit" name="saveorder" value="<?php echo JText::_('VRORDCONFIRM'); ?>" class="btn btn-large booknow"/>
	<?php
	if (!empty($pitemid)) {
		?>
		<input type="hidden" name="Itemid" value="<?php echo $pitemid; ?>"/>
		<?php
	}
	?>
	</form>
</div>
<?php
}
