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

$tars = $this->tars;
$item = $this->item;
$pickup = $this->pickup;
$release = $this->release;
$place = $this->place;
$itemquant = $this->itemquant;
$timeslot = $this->timeslot;
$lastdelivery = $this->lastdelivery;
$vri_tn = $this->vri_tn;

$pitemid = VikRequest::getInt('Itemid', '', 'request');

//load jQuery lib and navigation
$document = JFactory::getDocument();
if (VikRentItems::loadJquery()) {
	JHtml::_('jquery.framework', true, true);
	JHtml::_('script', VRI_SITE_URI.'resources/jquery-1.12.4.min.js', false, true, false, false);
}
$document->addStyleSheet(VRI_SITE_URI.'resources/jquery-ui.min.css');
//load jQuery UI
JHtml::_('script', VRI_SITE_URI.'resources/jquery-ui.min.js', false, true, false, false);
$document->addStyleSheet(VRI_SITE_URI.'resources/jquery.fancybox.css');
JHtml::_('script', VRI_SITE_URI.'resources/jquery.fancybox.js', false, true, false, false);
$navdecl = '
jQuery.noConflict();
jQuery(document).ready(function() {
	jQuery(".vrimodal").fancybox({
		"helpers": {
			"overlay": {
				"locked": false
			}
		},"padding": 0
	});
});';
$document->addScriptDeclaration($navdecl);
//
?>
<div class="vri-page-content">
<?php

$preturnplace = VikRequest::getString('returnplace', '', 'request');
$carats = VikRentItems::getItemCaratOriz($item['idcarat'], $vri_tn);
$currencysymb = VikRentItems::getCurrencySymb();
$optionals = "";
if (!empty($item['idopt'])) {
	$optionals = VikRentItems::getItemOptionals($item['idopt'], $vri_tn);
}
$discl = VikRentItems::getDisclaimer($vri_tn);

$sayquantity = $itemquant > 1 ? '(x '.$itemquant.')' : '';
?>
<!-- 	<div class="vri-showprc-groupblocks">
	<?php
	if (is_array($timeslot) && count($timeslot) > 0) {
		?>
		<h4 class="vri-medium-header vri-header-attract"><?php echo JText::_('VRRENTAL'); ?> <?php echo $item['name']; ?> <?php echo $sayquantity; ?> <?php echo JText::_('VRFOR'); ?> <?php echo $timeslot['tname']; ?></h4>
		<?php
	} else {
		if (array_key_exists('hours', $tars[0])) {
		?>
		<h4 class="vri-medium-header vri-header-attract"><?php echo JText::_('VRRENTAL'); ?> <?php echo $item['name']; ?> <?php echo $sayquantity; ?> <?php echo JText::_('VRFOR'); ?> <?php echo (intval($tars[0]['hours']) == 1 ? "1 ".JText::_('VRIHOUR') : $tars[0]['hours']." ".JText::_('VRIHOURS')); ?></h4>
		<?php
		} else {
		?>
		<h4 class="vri-medium-header vri-header-attract"><?php echo JText::_('VRRENTAL'); ?> <?php echo $item['name']; ?> <?php echo $sayquantity; ?> <?php echo JText::_('VRFOR'); ?> <?php echo (intval($tars[0]['days']) == 1 ? "1 ".JText::_('VRDAY') : $tars[0]['days']." ".JText::_('VRDAYS')); ?></h4>
		<?php
		}
	}
	?>
		<div class="vri-showprc-groupleft">
			<div class="vri-showprc-imagesblock">
				<div class="vri-showprc-mainimage">
					<img src="<?php echo VRI_ADMIN_URI; ?>resources/<?php echo $item['img']; ?>" alt="<?php echo $item['name']; ?>"/>
				</div>
			<?php
			if (strlen($item['moreimgs']) > 0) {
				$moreimages = explode(';;', $item['moreimgs']);
				?>
				<div class="vri-showprc-extraimages">
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


		<div class="vri-showprc-groupright">
			<div class="vri-showprc-descr">
<?php
if (!empty($item['info'])) {
	//BEGIN: Joomla Content Plugins Rendering
	JPluginHelper::importPlugin('content');
	$cont_instance = JTable::getInstance('content');
	$myItem = &$cont_instance;
	$disp_instance = JDispatcher::getInstance();
	$dispatcher = &$disp_instance;
	$myItem->text = $item['info'];
	$dispatcher->trigger('onContentPrepare', array('com_vikrentitems.showprc', &$myItem, &$params, 0));
	$item['info'] = $myItem->text;
	//END: Joomla Content Plugins Rendering
	echo $item['info'];
}
?>
			</div>
<?php
if (strlen($carats)) {
	?>
			<div class="vri-showprc-carats"><?php echo $carats; ?></div>
	<?php
}
if ($item['isgroup'] > 0 && count($this->kit_relations) > 0) {
	?>
			<div class="vri-showprc-kitrelations">
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
?>
		</div>

	</div> -->
		
	<form action="<?php echo JRoute::_('index.php?option=com_vikrentitems'); ?>" method="post">
		<div class="item_prices table-responsive">
			<h4 class="vri-medium-header"><?php echo JText::_('VRPRICE'); ?></h4>
			<table class="table">
			<?php
			foreach ($tars as $k => $t) {
				// VRI 1.6 - if location fees and multiple units, the price must be adjusted as it's not "each unit"
				$locfee_info = '';
				if (isset($t['locfee']) && $t['locfee'] > 0 && $itemquant > 1) {
					$t['cost'] = $t['cost'] - $t['locfee'];
					$locfee_info = '<br/>'.JText::_('VRLOCFEETOPAY').' '.$currencysymb.' '.VikRentItems::numberFormat($t['locfee']);
				}
				//
				?>
				<tr><td><label for="pid<?php echo $t['idprice']; ?>"><strong><?php echo VikRentItems::getPriceName($t['idprice'], $vri_tn).":</strong> <strong>".$currencysymb." ".VikRentItems::numberFormat(VikRentItems::sayCostPlusIva($t['cost'], $t['idprice']))."</strong>".($itemquant > 1 ? ' '.JText::_('VRIEACHUNIT') : '').(strlen($t['attrdata']) ? "<br/>".VikRentItems::getPriceAttr($t['idprice'], $vri_tn).": ".$t['attrdata'] : "").$locfee_info; ?></label></td><td><input type="radio" name="priceid" id="pid<?php echo $t['idprice']; ?>" value="<?php echo $t['idprice']; ?>"<?php echo ($k == 0 ? " checked=\"checked\"" : ""); ?>/></td></tr>
			<?php
			}
			?>
			</table>
			<?php
			//BEGIN: Item Specifications
			if (!empty($item['idopt']) && is_array($optionals)) {
				list($optionals, $specifications) = VikRentItems::loadOptionSpecifications($optionals);
				if (is_array($specifications) && count($specifications) > 0) {
					?>
				<div class="vrispecifications">
					<?php
					foreach ($specifications as $specification) {
						$specselect = '<select name="optid'.$specification['id'].'">'."\n";
						$intervals = explode(';;', $specification['specifications']);
						foreach ($intervals as $kintv => $intv) {
							if (empty($intv)) continue;
							$intvparts = explode('_', $intv);
							$intvparts[1] = intval($specification['perday']) == 1 ? ($intvparts[1] * $tars[0]['days']) : $intvparts[1];
							if (!empty($specification['maxprice']) && $specification['maxprice'] > 0 && $intvparts[1] > $specification['maxprice']) {
								$intvparts[1] = $specification['maxprice'];
							}
							$intvparts[1] = VikRentItems::sayOptionalsPlusIva($intvparts[1], $specification['idiva']);
							$pricestr = floatval($intvparts[1]) >= 0 ? '+ '.$currencysymb.''.VikRentItems::numberFormat($intvparts[1]) : '- '.$currencysymb.''.VikRentItems::numberFormat($intvparts[1]);
							$specselect .= '<option value="'.($kintv + 1).'">'.$intvparts[0].(VikRentItems::numberFormat(($intvparts[1] * 1)) != '0.00' ? ' ('.$pricestr.')' : '').'</option>'."\n";
						}
						$specselect .= '</select>'."\n";
					?>
					<div class="vrispecificationopt">
						<?php
						if (strlen($specification['descr']) > 0) {
							echo $specification['descr'];
						}
						?>
						<span class="vrispecificationoptname"><?php echo $specification['name']; ?></span>
						<span class="vrispecificationoptselect"><?php echo $specselect; ?></span>
					</div>
					<?php
					}
					?>
				</div>
					<?php
				}
			}
			//END: Item Specifications
			?>
		</div>
		
		<?php
		//check options to be applied only-once
		if (count($this->vrisessioncart) && !empty($item['idopt']) && is_array($optionals)) {
			foreach ($optionals as $k => $o) {
				if ($o['onlyonce'] > 0) {
					//check if already in the cart
					foreach ($this->vrisessioncart as $items) {
						foreach ($items as $cartitem) {
							if (isset($cartitem['options']) && is_array($cartitem['options'])) {
								foreach ($cartitem['options'] as $cartitemopt) {
									if ($cartitemopt['id'] == $o['id']) {
										unset($optionals[$k]);
										break 3;
									}
								}
							}
						}
					}
				}
			}
			if (!count($optionals)) {
				$optionals = "";
			}
		}
		//
		if (!empty($item['idopt']) && is_array($optionals)) {
		?>
		<div class="item_options table-responsive">
			<h4 class="vri-medium-header"><?php echo JText::_('VRACCOPZ'); ?></h4>
			<table class="table">
			<?php
			foreach ($optionals as $k => $o) {
				$optcost = intval($o['perday']) == 1 ? ($o['cost'] * $tars[0]['days']) : $o['cost'];
				if (!empty($o['maxprice']) && $o['maxprice'] > 0 && $optcost > $o['maxprice']) {
					$optcost = $o['maxprice'];
				}
				$optcost = $optcost * 1;
				//vikrentitems 1.1
				if (intval($o['forcesel']) == 1) {
					//VRI 1.1 Rev.2
					if ((int)$tars[0]['days'] > (int)$o['forceifdays']) {
						$forcedquan = 1;
						$forceperday = false;
						if (strlen($o['forceval']) > 0) {
							$forceparts = explode("-", $o['forceval']);
							$forcedquan = intval($forceparts[0]);
							$forceperday = intval($forceparts[1]) == 1 ? true : false;
						}
						$setoptquan = $forceperday == true ? $forcedquan * $tars[0]['days'] : $forcedquan;
						if (intval($o['hmany']) == 1) {
							$optquaninp = "<input type=\"hidden\" name=\"optid".$o['id']."\" value=\"".$setoptquan."\"/><span class=\"vrioptionforcequant\"><small>x</small> ".$setoptquan."</span>";
						} else {
							$optquaninp = "<input type=\"hidden\" name=\"optid".$o['id']."\" value=\"".$setoptquan."\"/><span class=\"vrioptionforcequant\"><small>x</small> ".$setoptquan."</span>";
						}
					} else {
						continue;
					}
					//
				} else {
					if (intval($o['hmany']) == 1) {
						$optquaninp = "<input type=\"number\" min=\"0\" name=\"optid".$o['id']."\" value=\"0\" size=\"3\" class=\"vri-inp-numb\"/>";
					} else {
						$optquaninp = "<input type=\"checkbox\" name=\"optid".$o['id']."\" value=\"1\"/>";
					}
				}
				//
				?>
				<tr height="30px"><td><?php echo (!empty($o['img']) ? "<img class=\"maxthirty\" src=\"".VRI_ADMIN_URI."resources/".$o['img']."\" align=\"middle\" />" : "") ?></td><td><strong><?php echo $o['name']; ?></strong></td><td><strong><?php echo $currencysymb; ?> <?php echo VikRentItems::numberFormat(VikRentItems::sayOptionalsPlusIva($optcost, $o['idiva'])); ?></strong> <?php echo ($itemquant > 1 && !$o['onceperitem'] ? JText::_('VRIEACHUNIT') : ''); ?></td><td align="center"><?php echo $optquaninp; ?></td></tr>
				<?php
				if (strlen(strip_tags(trim($o['descr'])))) {
					?>
					<tr><td colspan="4"><div class="vrioptionaldescr"><?php echo $o['descr']; ?></div></td></tr>
					<?php
				}
			}
			?>
			</table>
		</div>
		<?php
		}
		//VikRent Items 1.2 - Delivery Service
		$baseaddress = VikRentItems::getDeliveryBaseAddress();
		$prevdelivery = is_array($lastdelivery) && count($lastdelivery) > 0 ? true : false;
		if (intval(VikRentItems::getItemParam($item['params'], 'delivery')) == 1 && !empty($baseaddress)) {
			$currentUser = JFactory::getUser();
			$previousdata = VikRentItems::loadPreviousUserData($currentUser->id);
			$overcostperunit = floatval(VikRentItems::getItemParam($item['params'], 'overdelcost'));
			if ($prevdelivery === true && !empty($overcostperunit) && $overcostperunit > 0.00) {
				$lastdelivery['vrideliverycost'] = $lastdelivery['vrideliverydistance'] * $overcostperunit;
				if ($lastdelivery['vrideliveryroundcost'] == 1) {
					$lastdelivery['vrideliverycost'] = round($lastdelivery['vrideliverycost']);
				}
				if (!empty($lastdelivery['vrideliverymaxcost']) && (float)$lastdelivery['vrideliverymaxcost'] > 0 && $lastdelivery['vrideliverycost'] > (float)$lastdelivery['vrideliverymaxcost']) {
					$lastdelivery['vrideliverycost'] = (float)$lastdelivery['vrideliverymaxcost'];
				}
				// VRI 1.6 - Delivery per Item Unit (Quantity)
				if (VikRentItems::isDeliveryPerItemUnit()) {
					$lastdelivery['vrideliverycost'] = $lastdelivery['vrideliverycost'] * $itemquant;
				}
				//
			} elseif ($prevdelivery === true && (int)$lastdelivery['vrideliveryelemid'] != (int)$item['id']) {
				$lastdelivery['vrideliverycost'] = $lastdelivery['vrideliverydistance'] * $lastdelivery['vrideliveryglobcostperunit'];
				if ($lastdelivery['vrideliveryroundcost'] == 1) {
					$lastdelivery['vrideliverycost'] = round($lastdelivery['vrideliverycost']);
				}
				if (!empty($lastdelivery['vrideliverymaxcost']) && (float)$lastdelivery['vrideliverymaxcost'] > 0 && $lastdelivery['vrideliverycost'] > (float)$lastdelivery['vrideliverymaxcost']) {
					$lastdelivery['vrideliverycost'] = (float)$lastdelivery['vrideliverymaxcost'];
				}
				// VRI 1.6 - Delivery per Item Unit (Quantity)
				if (VikRentItems::isDeliveryPerItemUnit()) {
					$lastdelivery['vrideliverycost'] = $lastdelivery['vrideliverycost'] * $itemquant;
				}
				//
			}
			?>
		<div class="item_delivery">
			<h4 class="vri-medium-header"><?php echo JText::_('VRIDELIVERYSERVICETITLE'); ?></h4>
			<div class="vrideliveryidlike">
				<label for="vridelidlk"><?php echo JText::_('VRIDELIVERYIDLIKE'); ?></label> <input type="checkbox" name="delivery" id="vridelidlk" value="1"/>
			</div>
			<div class="vrideliverycont" id="vrideliverycont">
				<div>
					<label><?php echo JText::_('VRIDELIVERYADDRESS'); ?></label>
					<span id="vrideliveryaddress"><?php echo ($prevdelivery === true ? $lastdelivery['vrideliveryaddress'] : ''); ?></span>
				<?php
				if (!$prevdelivery || ($prevdelivery && !VikRentItems::isDeliveryPerOrder())) {
					// VRI 1.6 - Change address only allowed if delivery set and cost is per item, not per order
				?>
					<a class="btn vrichangedeliveryaddress" id="vrichangedeliveryaddress"><i class="fas fa-map-marker"></i> <?php echo JText::_('VRIDELIVERYADDRESSCHANGE'); ?></a>
				<?php
				}
				?>
				</div>
				<div>
					<label><?php echo JText::_('VRIDELIVERYDISTANCE'); ?></label>
					<span id="vrideliverydistance"><?php echo ($prevdelivery === true ? $lastdelivery['vrideliverydistance'].' '.$lastdelivery['vrideliverydistanceunit'] : ''); ?></span>
				</div>
				<?php
				if (!$prevdelivery || ($prevdelivery && !VikRentItems::isDeliveryPerOrder())) {
					// VRI 1.6 - Show the delivery cost only if no items with delivery already in the cart when charge once per order
				?>
				<div>
					<label><?php echo JText::_('VRIDELIVERYCOST'); ?></label>
					<span id="vrideliverycost"><?php echo ($prevdelivery === true ? $currencysymb.' '.$lastdelivery['vrideliverycost'] : ''); ?></span>
				</div>
				<?php
				}
				?>
			</div>
		</div>
		<input type="hidden" name="deliveryaddress" id="deliveryaddressinp" value="<?php echo ($prevdelivery === true ? $lastdelivery['vrideliveryaddress'] : (count($previousdata) > 0 && array_key_exists('delivery', $previousdata) ? $previousdata['delivery']['vrideliveryaddress'] : '')); ?>"/>
		<input type="hidden" name="deliverydistance" id="deliverydistanceinp" value="<?php echo ($prevdelivery === true ? $lastdelivery['vrideliverydistance'] : ''); ?>"/>
		<input type="hidden" name="deliverysessionval" id="deliverysessionval" value="<?php echo ($prevdelivery === true ? $lastdelivery['vrideliverysessid'] : ''); ?>"/>
		<script type="text/javascript">
		var actdelivaddr = jQuery("#deliveryaddressinp").val();
		function vriShowDeliveryMap() {
			var baseaddrmaplink = "<?php echo JRoute::_('index.php?option=com_vikrentitems&task=deliverymap&elemid='.$item['id'].'&tmpl=component', false); ?>";
			jQuery.fancybox({
				beforeLoad: function() {
					this.href = baseaddrmaplink+(baseaddrmaplink.indexOf('?') > 0 ? '&' : '?')+"delto="+jQuery("#deliveryaddressinp").val()+"&itemquant="+jQuery("#itemquant").val();
				},
				"helpers": {
					"overlay": {
						"locked": false
					}
				},
				"width": "100%",
				"height": "75%",
				"autoScale": false,
				"transitionIn": "none",
				"transitionOut": "none",
				"padding": 0,
				"type": "iframe" 
			});
		}
		jQuery(document).ready(function() {
			jQuery("#vridelidlk").change(function() {
				if (jQuery(this).is(":checked")) {
					if (jQuery("#deliveryaddressinp").val().length > 0 && jQuery("#deliverysessionval").val().length > 0) {
						jQuery(".vrideliverycont").fadeIn();
					} else {
						vriShowDeliveryMap();
					}
				} else {
					jQuery(".vrideliverycont").hide();
				}
			});
			jQuery("#vrichangedeliveryaddress").click(function() {
				vriShowDeliveryMap();
			});
		});
		</script>
			<?php
		}
		//VikRent Items 1.2 - Delivery Service
		?>
		<input type="hidden" name="place" value="<?php echo $place; ?>"/>
		<input type="hidden" name="returnplace" value="<?php echo $preturnplace; ?>"/>
		<input type="hidden" name="elemid" value="<?php echo $item['id']; ?>"/>
  		<input type="hidden" name="days" value="<?php echo $tars[0]['days']; ?>"/>
  		<input type="hidden" name="pickup" value="<?php echo $pickup; ?>"/>
  		<input type="hidden" name="release" value="<?php echo $release; ?>"/>
  		<input type="hidden" name="itemquant" id="itemquant" value="<?php echo $itemquant; ?>"/>
  		<input type="hidden" name="task" value="oconfirm"/>
  		<?php
		if (is_array($timeslot) && count($timeslot) > 0) {
			?>
			<input type="hidden" name="timeslot" value="<?php echo $timeslot['id']; ?>"/>
			<?php
		}
		if (!empty($pitemid)) {
			?>
			<input type="hidden" name="Itemid" value="<?php echo $pitemid; ?>"/>
			<?php
		}
		?>
		<br clear="all">
  		<?php
  		if (strlen($discl)) { 
			?>	
			<div class="item_disclaimer"><?php echo $discl; ?></div>
			<?php
  		}
		?>
		
		<div class="item_buttons_box">
			<input type="submit" name="goon" value="<?php echo JText::_('VRBOOKNOW'); ?>" class="booknow"/>
			<div class="goback">
				<a href="javascript: void(0);" onclick="javascript: window.history.back();"><?php echo JText::_('VRBACK'); ?></a>
			</div>
		</div>
		
	</form>
</div>