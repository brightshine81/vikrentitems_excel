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

$res = $this->res;
$days = $this->days;
$hours = $this->hours;
$pickup = $this->pickup;
$release = $this->release;
$place = $this->place;
$navig = $this->navig;
$timeslot = $this->timeslot;
$vri_tn = $this->vri_tn;

$document = JFactory::getDocument();
//load jQuery lib e jQuery UI
if (VikRentItems::loadJquery()) {
	JHtml::_('jquery.framework', true, true);
	JHtml::_('script', VRI_SITE_URI.'resources/jquery-1.12.4.min.js', false, true, false, false);
}
$document->addStyleSheet(VRI_SITE_URI.'resources/jquery-ui.min.css');
//load jQuery UI
JHtml::_('script', VRI_SITE_URI.'resources/jquery-ui.min.js', false, true, false, false);

$dbo = JFactory::getDBO();
$currencysymb = VikRentItems::getCurrencySymb();
$returnplace = VikRequest::getInt('returnplace', '', 'request');
$pitemid = VikRequest::getInt('Itemid', '', 'request');
$vridateformat = VikRentItems::getDateFormat();
$nowtf = VikRentItems::getTimeFormat();
if ($vridateformat == "%d/%m/%Y") {
	$df = 'd/m/Y';
} elseif ($vridateformat == "%m/%d/%Y") {
	$df = 'm/d/Y';
} else {
	$df = 'Y/m/d';
}

?>
<div class="vri-page-content">
<?php

// itinerary
$pickloc = VikRentItems::getPlaceInfo($place, $vri_tn);
$droploc = VikRentItems::getPlaceInfo($returnplace, $vri_tn);
?>
	<div class="vri-itinerary-summary">
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
				<span class="vri-itinerary-pickup-date-day"><?php echo date($df, $pickup); ?></span>
				<span class="vri-itinerary-pickup-date-time"><?php echo date($nowtf, $pickup); ?></span>
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
				<span class="vri-itinerary-dropoff-date-day"><?php echo date($df, $release); ?></span>
				<span class="vri-itinerary-dropoff-date-time"><?php echo date($nowtf, $release); ?></span>
				<span class="vri-itinerary-duration"><?php echo $hours > 0 ? ($hours . ' ' . strtolower(JText::_(($hours > 1 ? 'VRIHOURS' : 'VRIHOUR')))) : ($days . ' ' . strtolower(JText::_(($days > 1 ? 'VRDAYS' : 'VRDAY')))); ?></span>
			</div>
		</div>
	</div>

	<h3 class="vri-big-header"><?php echo JText::_('VRIARSFND'); ?>: <?php echo $this->tot_res; ?></h3>
	<div class="vri-search-results-container">
<?php
foreach ($res as $k => $r) {
	$getitem = VikRentItems::getItemInfo($k, $vri_tn);
	$item_params = !empty($getitem['jsparams']) ? json_decode($getitem['jsparams'], true) : array();
	$carats = VikRentItems::getItemCaratOriz($getitem['idcarat'], $vri_tn);
	$imgpath = file_exists(VRI_ADMIN_PATH.DS.'resources'.DS.'vthumb_'.$getitem['img']) ? VRI_ADMIN_URI.'resources/vthumb_'.$getitem['img'] : VRI_ADMIN_URI.'resources/'.$getitem['img'];
	$vcategory = VikRentItems::sayCategory($getitem['idcat'], $vri_tn);
	$discounts = array();
	if ($getitem['askquantity'] == 1 && intval(VikRentItems::getItemParam($getitem['params'], 'discsquantstab')) == 1) {
		$q = "SELECT * FROM `#__vikrentitems_discountsquants` WHERE `iditems` LIKE '%-".$getitem['id']."-%' ORDER BY `#__vikrentitems_discountsquants`.`quantity` ASC;";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$discounts = $dbo->loadAssocList();
		}
	}
	?>
		<div class="vri-search-result-block<?php echo $getitem['isgroup'] > 0 ? ' vri-result-item-kit' : ''; ?>">
			<form action="<?php echo JRoute::_('index.php?option=com_vikrentitems'); ?>" method="get">
				<input type="hidden" name="option" value="com_vikrentitems"/>
				<input type="hidden" name="itemopt" value="<?php echo $k; ?>"/>
				<input type="hidden" name="days" value="<?php echo $days; ?>"/>
				<input type="hidden" name="pickup" value="<?php echo $pickup; ?>"/>
				<input type="hidden" name="release" value="<?php echo $release; ?>"/>
				<input type="hidden" name="place" value="<?php echo $place; ?>"/>
				<input type="hidden" name="returnplace" value="<?php echo $returnplace; ?>"/>
				<input type="hidden" name="task" value="showprc"/>
			<?php
			if (is_array($timeslot) && count($timeslot) > 0) {
				?>
				<input type="hidden" name="timeslot" value="<?php echo $timeslot['id']; ?>"/>
				<?php
			}
			?>
				<div class="vri-result-item-inner">
				<?php
				if (!empty($getitem['img'])) {
				?>
					<div class="vri-result-item-img">
						<img src="<?php echo $imgpath; ?>" alt="<?php $getitem['name']; ?>"/>
					</div>
				<?php
				}
				?>
					<div class="vri-result-item-descr">
						<span class="vrilistitemname"><?php echo $getitem['name']; ?></span>
					<?php
					if (strlen($vcategory) > 0) {
						?>
						<span class="vrilistitemcat"><?php echo $vcategory; ?></span>
						<?php
					}
					?>
						<div class="vri-result-itemdescr"><?php echo $getitem['shortdesc']; ?></div>
					</div>
				</div>
				<div class="vri-result-item-cont">
					<div class="vri-result-costdivcont">
						<div class="vri-result-divcost">
							<span class="vriliststartfrom"><?php echo JText::_('VRSTARTFROM'); ?></span>
							<span class="item_cost"><?php echo $currencysymb; ?> <?php echo VikRentItems::numberFormat(VikRentItems::sayCostPlusIva($r[0]['cost'], $r[0]['idprice'])); ?></span>
						</div>
					<?php
					if ($getitem['askquantity'] == 1) {
						?>
						<div class="vri-search-selectquantity">
							<label for="itemquant-<?php echo $k; ?>"><?php echo JText::_('VRIQUANTITYX'); ?></label>
							<input type="number" name="itemquant" id="itemquant-<?php echo $k; ?>" value="<?php echo (!array_key_exists('minquant', $item_params) || empty($item_params['minquant']) ? '1' : (int)$item_params['minquant']); ?>" min="<?php echo (!array_key_exists('minquant', $item_params) || empty($item_params['minquant']) ? '1' : (int)$item_params['minquant']); ?>" max="<?php echo $getitem['units']; ?>" class="vri-numbinput"/>
						</div>
						<?php
					}
					?>
						<div class="vri-search-subdiv"><input type="submit" name="goon" value="<?php echo JText::_('VRPROSEGUI'); ?>" class="vricontinue"/></div>
					</div>
				</div>
				<div class="vri-result-itembottom<?php echo ($getitem['askquantity'] == 1 && count($discounts) > 0 ? ' vri-result-itembottom-double' : ''); ?>">
				<?php
				if (!empty($carats)) {
					?>
					<div class="vri-result-itemcarats"><?php echo $carats; ?></div>
					<?php
				}
				if ($getitem['askquantity'] == 1 && intval(VikRentItems::getItemParam($getitem['params'], 'discsquantstab')) == 1 && count($discounts) > 0) {
					?>
					<div class="vri-result-itemdiscquants-container">
						<div class="vri-result-itemdiscquants-inner">
							<div class="vri-result-itemdiscquants-firstrow">
								<div class="vri-result-itemdiscquants-firstrow-left"><?php echo JText::_('VRIDISCSQUANTSQ'); ?></div>
								<div class="vri-result-itemdiscquants-firstrow-right"><?php echo JText::_('VRIDISCSQUANTSSAVE'); ?></div>
							</div>
							<div class="vri-result-itemdiscquants-listrows">
						<?php
						foreach ($discounts as $kd => $disc) {
							$discval = substr($disc['diffcost'], -2) == '00' ? number_format($disc['diffcost'], 0) : VikRentItems::numberFormat($disc['diffcost']);
							$savedisc = $disc['val_pcent'] == 1 ? $currencysymb.' '.$discval : $discval.'%';
							$disc_keys = array_keys($discounts);
							?>
								<div class="vri-result-itemdiscquants-row">
									<div class="vri-result-itemdiscquants-row-left"><?php echo $disc['quantity'].(end($disc_keys) == $kd && $disc['ifmorequant'] == 1 ? ' '.JText::_('VRIDISCSQUANTSORMORE') : ''); ?></div>
									<div class="vri-result-itemdiscquants-row-right"><?php echo $savedisc; ?></div>
								</div>
							<?php
						}
						?>
							</div>
						</div>
					</div>
				<?php
				}
				?>
				</div>
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
?>
	</div>
<?php
//pagination
if (strlen($navig) > 0) {
	?>
	<div class="vri-pagination"><?php echo $navig; ?></div>
	<?php
}
?>
</div>
