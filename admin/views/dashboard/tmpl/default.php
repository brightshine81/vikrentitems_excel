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

$pidplace = $this->pidplace;
$arrayfirst = $this->arrayfirst;
$allplaces = $this->allplaces;
$nextrentals = $this->nextrentals;
$pickup_today = $this->pickup_today;
$dropoff_today = $this->dropoff_today;
$items_locked = $this->items_locked;
$totnextrentconf = $this->totnextrentconf;
$totnextrentpend = $this->totnextrentpend;

$nowdf = VikRentItems::getDateFormat(true);
if ($nowdf == "%d/%m/%Y") {
	$df = 'd/m/Y';
} elseif ($nowdf == "%m/%d/%Y") {
	$df = 'm/d/Y';
} else {
	$df = 'Y/m/d';
}
$nowtf = VikRentItems::getTimeFormat(true);
$selplace = "";
if (is_array($allplaces)) {
	$selplace = "<form action=\"index.php?option=com_vikrentitems\" method=\"post\" name=\"vridashform\" style=\"display: inline; margin: 0;\"> <label style=\"display: inline-block; mrgin-right: 5px;\">".JText::_('VRIDASHPICKUPLOC')."</label> <select name=\"idplace\" onchange=\"javascript: document.vridashform.submit();\">\n<option value=\"0\">".JText::_('VRIDASHALLPLACES')."</option>\n";
	foreach ($allplaces as $place) {
		$selplace .= "<option value=\"".$place['id']."\"".($place['id'] == $pidplace ? " selected=\"selected\"" : "").">".$place['name']."</option>\n";
	}
	$selplace .= "</select></form>\n";
}
//Todays Pick Up
?>
<div class="vri-dashboard-today-bookings">
	<div class="vri-dashboard-today-pickup-wrapper">
		<h4><i class="fas fa-sign-in-alt"></i> <?php echo JText::_('VRIDASHTODAYPICKUP'); ?></h4>
		<div class="vri-dashboard-today-pickup table-responsive">
			<table class="table">
				<tr class="vri-dashboard-today-pickup-firstrow">
					<td align="center"><?php echo JText::_('VRIDASHUPRESONE'); ?></td>
					<td align="center"><?php echo JText::_('VRIDASHUPRESTWO'); ?></td>
					<td align="center"><?php echo JText::_('VRPVIEWORDERSTWO'); ?></td>
					<td align="center"><?php echo JText::_('VRIDASHUPRESTHREE'); ?></td>
					<td align="center"><?php echo JText::_('VRIDASHUPRESFOUR'); ?></td>
					<td><?php echo JText::_('VRIDASHUPRESFIVE'); ?></td>
				</tr>
			<?php
			foreach ($pickup_today as $next) {
				$nominative = strlen($next['nominative']) > 1 ? $next['nominative'] : VikRentItems::getFirstCustDataField($next['custdata']);
				$country_flag = '';
				if (file_exists(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_vikrentitems'.DS.'resources'.DS.'countries'.DS.$next['country'].'.png')) {
					$country_flag = '<img src="'.JURI::root().'administrator/components/com_vikrentitems/resources/countries/'.$next['country'].'.png'.'" title="'.$next['country'].'" class="vri-country-flag vri-country-flag-left"/>';
				}
				$num_items = $next['totitems'];
				if (!empty($next['item_names'])) {
					$oinames = explode(',', $next['item_names']);
					if (count($oinames) === 1 && !empty($oinames[0])) {
						if ($next['totitems'] > 1) {
							$num_items = $oinames[0].' x'.$next['totitems'];
						} else {
							$num_items = $oinames[0];
						}
					} elseif (count($oinames) > 1) {
						$num_items = '<span class="hasTooltip" title="'.htmlentities(implode(', ', $oinames)).'">'.$next['totitems'].'</span>';
					}
				}
				?>
				<tr class="vri-dashboard-today-pickup-rows">
					<td align="center"><a href="index.php?option=com_vikrentitems&amp;task=editorder&amp;cid[]=<?php echo $next['id']; ?>"><?php echo $next['id']; ?></a></td>
					<td align="center"><?php echo $num_items; ?></td>
					<td align="center"><?php echo $country_flag.$nominative; ?></td>
					<td align="center"><?php echo (!empty($next['idplace']) && empty($pidplace) ? VikRentItems::getPlaceName($next['idplace'])." " : "").date('H:i', $next['ritiro']); ?></td>
					<td align="center"><?php echo (!empty($next['idreturnplace']) ? VikRentItems::getPlaceName($next['idreturnplace'])." " : "").date($df.' H:i', $next['consegna']); ?></td>
					<td align="center"><?php echo ($next['status'] == 'confirmed' ? '<span style="font-weight: bold; color: green;">'.strtoupper(JText::_('VRIONFIRMED')).'</span>' : '<span style="font-weight: bold; color: red;">'.strtoupper(JText::_('VRSTANDBY')).'</span>'); ?></td>
				</tr>
				<?php
			}
			?>
			</table>
		</div>
	</div>
	<?php
	//Todays Drop Off
	?>
	<div class="vri-dashboard-today-dropoff-wrapper">
		<h4><i class="fas fa-sign-out-alt"></i> <?php echo JText::_('VRIDASHTODAYDROPOFF'); ?></h4>
		<div class="vri-dashboard-today-dropoff table-responsive">
			<table class="table">
				<tr class="vri-dashboard-today-dropoff-firstrow">
					<td align="center"><?php echo JText::_('VRIDASHUPRESONE'); ?></td>
					<td align="center"><?php echo JText::_('VRIDASHUPRESTWO'); ?></td>
					<td align="center"><?php echo JText::_('VRPVIEWORDERSTWO'); ?></td>
					<td align="center"><?php echo JText::_('VRIDASHUPRESTHREE'); ?></td>
					<td align="center"><?php echo JText::_('VRIDASHUPRESFOUR'); ?></td>
					<td><?php echo JText::_('VRIDASHUPRESFIVE'); ?></td>
				</tr>
			<?php
			foreach ($dropoff_today as $next) {
				$nominative = strlen($next['nominative']) > 1 ? $next['nominative'] : VikRentItems::getFirstCustDataField($next['custdata']);
				$country_flag = '';
				if (file_exists(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_vikrentitems'.DS.'resources'.DS.'countries'.DS.$next['country'].'.png')) {
					$country_flag = '<img src="'.JURI::root().'administrator/components/com_vikrentitems/resources/countries/'.$next['country'].'.png'.'" title="'.$next['country'].'" class="vri-country-flag vri-country-flag-left"/>';
				}
				$num_items = $next['totitems'];
				if (!empty($next['item_names'])) {
					$oinames = explode(',', $next['item_names']);
					if (count($oinames) === 1 && !empty($oinames[0])) {
						if ($next['totitems'] > 1) {
							$num_items = $oinames[0].' x'.$next['totitems'];
						} else {
							$num_items = $oinames[0];
						}
					} elseif (count($oinames) > 1) {
						$num_items = '<span class="hasTooltip" title="'.htmlentities(implode(', ', $oinames)).'">'.$next['totitems'].'</span>';
					}
				}
				?>
				<tr class="vri-dashboard-today-pickup-rows">
					<td align="center"><a href="index.php?option=com_vikrentitems&amp;task=editorder&amp;cid[]=<?php echo $next['id']; ?>"><?php echo $next['id']; ?></a></td>
					<td align="center"><?php echo $num_items; ?></td>
					<td align="center"><?php echo $country_flag.$nominative; ?></td>
					<td align="center"><?php echo (!empty($next['idplace']) && empty($pidplace) ? VikRentItems::getPlaceName($next['idplace'])." " : "").date($df.' H:i', $next['ritiro']); ?></td>
					<td align="center"><?php echo (!empty($next['idreturnplace']) ? VikRentItems::getPlaceName($next['idreturnplace'])." " : "").date('H:i', $next['consegna']); ?></td>
					<td align="center"><?php echo ($next['status'] == 'confirmed' ? '<span style="font-weight: bold; color: green;">'.strtoupper(JText::_('VRIONFIRMED')).'</span>' : '<span style="font-weight: bold; color: red;">'.strtoupper(JText::_('VRSTANDBY')).'</span>'); ?></td>
				</tr>
				<?php
			}
			?>
			</table>
		</div>
	</div>
</div>

<br clear="all" /><br clear="all">

<div class="vri-dashboard-next-bookings-block">
	<div class="vri-dashboard-next-bookings table-responsive">
		<h4><i class="fas fa-calendar-alt"></i> <?php echo JText::_('VRIDASHUPCRES'); ?></h4>
		<div style="float: right; margin: 13px;"><?php echo $selplace; ?></div>
<?php
if (is_array($nextrentals)) {
	?>
		<table class="table">
			<tr class="vri-dashboard-today-dropoff-firstrow">
				<td align="center"><?php echo JText::_('VRIDASHUPRESONE'); ?></td>
				<td align="center"><?php echo JText::_('VRIDASHUPRESTWO'); ?></td>
				<td align="center"><?php echo JText::_('VRPVIEWORDERSTWO'); ?></td>
				<td align="center"><?php echo JText::_('VRIDASHUPRESTHREE'); ?></td>
				<td align="center"><?php echo JText::_('VRIDASHUPRESFOUR'); ?></td>
				<td align="center"><?php echo JText::_('VRIDASHUPRESFIVE'); ?></td>
			</tr>
	<?php
	foreach ($nextrentals as $next) {
		$nominative = strlen($next['nominative']) > 1 ? $next['nominative'] : VikRentItems::getFirstCustDataField($next['custdata']);
		$country_flag = '';
		if (file_exists(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_vikrentitems'.DS.'resources'.DS.'countries'.DS.$next['country'].'.png')) {
			$country_flag = '<img src="'.JURI::root().'administrator/components/com_vikrentitems/resources/countries/'.$next['country'].'.png'.'" title="'.$next['country'].'" class="vri-country-flag vri-country-flag-left"/>';
		}
		$num_items = $next['totitems'];
		if (!empty($next['item_names'])) {
			$oinames = explode(',', $next['item_names']);
			if (count($oinames) === 1 && !empty($oinames[0])) {
				if ($next['totitems'] > 1) {
					$num_items = $oinames[0].' x'.$next['totitems'];
				} else {
					$num_items = $oinames[0];
				}
			} elseif (count($oinames) > 1) {
				$num_items = '<span class="hasTooltip" title="'.htmlentities(implode(', ', $oinames)).'">'.$next['totitems'].'</span>';
			}
		}
		?>
			<tr class="vri-dashboard-today-dropoff-rows">
				<td align="center"><a class="vri-orderid" href="index.php?option=com_vikrentitems&amp;task=editorder&amp;cid[]=<?php echo $next['id']; ?>"><?php echo $next['id']; ?></a></td>
				<td align="center"><?php echo $num_items; ?></td>
				<td align="center"><?php echo $country_flag.$nominative; ?></td>
				<td align="center"><?php echo (!empty($next['idplace']) && empty($pidplace) ? VikRentItems::getPlaceName($next['idplace'])." " : "").date($df.' H:i', $next['ritiro']); ?></td>
				<td align="center"><?php echo (!empty($next['idreturnplace']) ? VikRentItems::getPlaceName($next['idreturnplace'])." " : "").date($df.' H:i', $next['consegna']); ?></td>
				<td align="center"><?php echo ($next['status'] == 'confirmed' ? '<span style="font-weight: bold; color: green;">'.strtoupper(JText::_('VRIONFIRMED')).'</span>' : '<span style="font-weight: bold; color: red;">'.strtoupper(JText::_('VRSTANDBY')).'</span>'); ?></td>
			</tr>
		<?php
	}
	?>
		</table>
	<?php
}
?>
	</div>
</div>

<br clear="all" />

<?php
//Items Locked
if (count($items_locked)) {
	?>
<div class="vri-dashboard-items-locked-block">
	<div class="vri-dashboard-items-locked table-responsive">
		<h4 id="vri-dashboard-items-locked-toggle"><?php echo JText::_('VRIDASHITEMSLOCKED'); ?><span>(<?php echo count($items_locked); ?>)</span></h4>
		<table class="table" style="display: none;">
			<tr class="vri-dashboard-items-locked-firstrow">
				<td align="center"><?php echo JText::_('VRIDASHUPRESTWO'); ?></td>
				<td align="center"><?php echo JText::_('VRPVIEWORDERSTWO'); ?></td>
				<td align="center"><?php echo JText::_('VRIDASHLOCKUNTIL'); ?></td>
				<td align="center"><?php echo JText::_('VRIDASHUPRESONE'); ?></td>
				<td align="center">&nbsp;</td>
			</tr>
		<?php
		foreach ($items_locked as $lock) {
			$country_flag = '';
			if (file_exists(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_vikrentitems'.DS.'resources'.DS.'countries'.DS.$lock['country'].'.png')) {
				$country_flag = '<img src="'.JURI::root().'administrator/components/com_vikrentitems/resources/countries/'.$lock['country'].'.png'.'" title="'.$lock['country'].'" class="vri-country-flag vri-country-flag-left"/>';
			}
			?>
			<tr class="vri-dashboard-items-locked-rows">
				<td align="center"><?php echo $lock['item_name']; ?></td>
				<td align="center"><?php echo $country_flag.$lock['nominative']; ?></td>
				<td align="center"><?php echo date($df.' H:i', $lock['until']); ?></td>
				<td align="center"><a href="index.php?option=com_vikrentitems&amp;task=editorder&amp;cid[]=<?php echo $lock['idorder']; ?>" target="_blank"><?php echo $lock['idorder']; ?></a></td>
				<td align="center"><button type="button" class="btn btn-danger" onclick="if (confirm('<?php echo addslashes(JText::_('VRIDELCONFIRM')); ?>')) location.href='index.php?option=com_vikrentitems&amp;task=unlockrecords&amp;cid[]=<?php echo $lock['id']; ?>';"><?php echo JText::_('VRIDASHUNLOCK'); ?></button></td>
			</tr>
			<?php
		}
		?>
		</table>
	</div>
</div>
<script type="text/JavaScript">
if (jQuery.isFunction(jQuery.fn.tooltip)) {
	jQuery(".hasTooltip").tooltip();
} else {
	jQuery.fn.tooltip = function() {};
}
jQuery(document).ready(function() {
	jQuery("#vri-dashboard-items-locked-toggle").click(function() {
		jQuery(this).next("table").fadeToggle();
	});
});
</script>
	<?php
}
?>

<div class="vridashdivright">
	<h3 class="vridashdivrighthead"><?php echo JText::_('VRIDASHSTATS'); ?></h3>
	<p class="vridashparag"></p>
<?php
if ($arrayfirst['totprices'] < 1) {
	?>
	<p class="vridashparagred"><?php echo JText::_('VRIDASHNOPRICES'); ?>: 0</p>
	<?php
}
if ($arrayfirst['totlocations'] < 1) {
	?>
	<p class="vridashparagred"><?php echo JText::_('VRIDASHNOLOCATIONS'); ?>: 0</p>
	<?php
} else {
	?>
	<p class="vridashparag"><?php echo JText::_('VRIDASHNOLOCATIONS').': '.$arrayfirst['totlocations']; ?></p>
	<?php
}
if ($arrayfirst['totcategories'] < 1) {
	?>
	<p class="vridashparagred"><?php echo JText::_('VRIDASHNOCATEGORIES'); ?>: 0</p>
	<?php
} else {
	?>
	<p class="vridashparag"><?php echo JText::_('VRIDASHNOCATEGORIES').': '.$arrayfirst['totcategories']; ?></p>
	<?php
}
if ($arrayfirst['totitems'] < 1) {
	?>
	<p class="vridashparagred"><?php echo JText::_('VRIDASHNOITEMS'); ?>: 0</p>
	<?php
} else {
	?>
	<p class="vridashparag"><?php echo JText::_('VRIDASHNOITEMS').': '.$arrayfirst['totitems']; ?></p>
	<?php
}
if ($arrayfirst['totdailyfares'] < 1) {
	?>
	<p class="vridashparagred"><?php echo JText::_('VRIDASHNODAILYFARES'); ?>: 0</p>
	<?php
}
?>
	<p class="vridashparag"><?php echo JText::_('VRIDASHTOTRESCONF').': '.$totnextrentconf; ?></p>
	<p class="vridashparag"><?php echo JText::_('VRIDASHTOTRESPEND').': '.$totnextrentpend; ?></p>
</div>
