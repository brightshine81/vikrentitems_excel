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

$rows = $this->rows;
$lim0 = $this->lim0;
$navbut = $this->navbut;
$arrbusy = $this->arrbusy;
$wmonthsel = $this->wmonthsel;
$tsstart = $this->tsstart;
$all_locations = $this->all_locations;
$plocation = $this->plocation;
$plocationw = $this->plocationw;

$nowtf = VikRentItems::getTimeFormat(true);
$wdays_map = array(
	JText::_('VRSUN'),
	JText::_('VRMON'),
	JText::_('VRTUE'),
	JText::_('VRWED'),
	JText::_('VRTHU'),
	JText::_('VRFRI'),
	JText::_('VRSAT')
);

$session = JFactory::getSession();
$show_type = $session->get('vriUnitsShowType', '');
$cookie = JFactory::getApplication()->input->cookie;
$cookie_uleft = $cookie->get('vriAovwUleft', '', 'string');
$mnum = $session->get('vriOvwMnum', '1');
$mnum = intval($mnum);
?>
<script type="text/javascript">
function vriUnitsLeftOrBooked() {
	var set_to = jQuery('#uleftorbooked').val();
	if (jQuery('.vri-overview-redday').length) {
		jQuery('.vri-overview-redday').each(function() {
			jQuery(this).text(jQuery(this).attr('data-'+set_to));
		});
	}
	var nd = new Date();
	nd.setTime(nd.getTime() + (365*24*60*60*1000));
	document.cookie = "vriAovwUleft="+set_to+"; expires=" + nd.toUTCString() + "; path=/";
}
</script>
<form class="vri-avov-form" action="index.php?option=com_vikrentitems&amp;task=overv" method="post" name="vroverview">
	<div class="btn-toolbar vri-avov-toolbar" id="filter-bar" style="width: 100%; display: inline-block;">
		<div class="btn-group pull-left">
			<?php echo $wmonthsel; ?>
		</div>
		<div class="btn-group pull-left">
			<select name="mnum" onchange="document.vroverview.submit();">
			<?php
			for ($i = 1; $i <= 12; $i++) { 
				?>
				<option value="<?php echo $i; ?>"<?php echo $i == $mnum ? ' selected="selected"' : ''; ?>><?php echo JText::_('VRCONFIGMAXDATEMONTHS').': '.$i; ?></option>
				<?php
			}
			?>
			</select>
		</div>
		<div class="btn-group pull-right">
			<select name="units_show_type" id="uleftorbooked" onchange="vriUnitsLeftOrBooked();">
				<option value="units-booked"<?php echo (!empty($cookie_uleft) && $cookie_uleft == 'units-booked' ? ' selected="selected"' : ''); ?>><?php echo JText::_('VRISHOWUNITSBOOKED'); ?></option>
				<option value="units-left"<?php echo $show_type == 'units-left' || (!empty($cookie_uleft) && $cookie_uleft == 'units-left') ? ' selected="selected"' : ''; ?>><?php echo JText::_('VRISHOWUNITSLEFT'); ?></option>
			</select>
		</div>
	<?php
	if (is_array($all_locations)) {
		$loc_options = '<option value="">'.JText::_('VRIORDERSLOCFILTERANY').'</option>'."\n";
		foreach ($all_locations as $location) {
			$loc_options .= '<option value="'.$location['id'].'"'.($location['id'] == $plocation ? ' selected="selected"' : '').'>'.$location['name'].'</option>'."\n";
		}
		?>
		<div class="btn-group pull-right">
			<button type="submit" class="btn btn-secondary"><?php echo JText::_('VRIORDERSLOCFILTERBTN'); ?></button>
		</div>
		<div class="btn-group pull-right">
			<select name="locationw" id="locwfilter">
				<option value="pickup"><?php echo JText::_('VRIORDERSLOCFILTERPICK'); ?></option>
				<option value="dropoff"<?php echo $plocationw == 'dropoff' ? ' selected="selected"' : ''; ?>><?php echo JText::_('VRIORDERSLOCFILTERDROP'); ?></option>
				<option value="both"<?php echo $plocationw == 'both' ? ' selected="selected"' : ''; ?>><?php echo JText::_('VRIORDERSLOCFILTERPICKDROP'); ?></option>
			</select>
		</div>
		<div class="btn-group pull-right">
			<label for="locfilter" style="display: inline-block; margin-right: 5px;"><?php echo JText::_('VRIORDERSLOCFILTER'); ?></label>
			<select name="location" id="locfilter"><?php echo $loc_options; ?></select>
		</div>
		<?php
	}
	?>
	</div>
</form>

<?php
$nowts = getdate($tsstart);
$curts = $nowts;
for ($mind = 1; $mind <= $mnum; $mind++) {
?>
<div class="table-responsive">
	<table class="table vrioverviewtable">
		<tr class="vrioverviewtablerow">
			<td class="bluedays vrioverviewtdone"><strong><?php echo VikRentItems::sayMonth($curts['mon'])." ".$curts['year']; ?></strong></td>
		<?php
		$moncurts = $curts;
		$mon = $moncurts['mon'];
		while ($moncurts['mon'] == $mon) {
			echo '<td align="center" class="bluedays"><span class="vri-overv-mday">'.$moncurts['mday'].'</span><span class="vri-overv-wday">'.$wdays_map[$moncurts['wday']].'</td>';
			$moncurts = getdate(mktime(0, 0, 0, $moncurts['mon'], ($moncurts['mday'] + 1), $moncurts['year']));
		}
		?>
		</tr>
		<?php
		foreach ($rows as $item) {
			$moncurts = $curts;
			$mon = $moncurts['mon'];
			echo '<tr class="vrioverviewtablerow">';
			echo '<td class="itemname"><span class="vri-overview-itemname">'.$item['name'].'</span> <span class="vri-overview-itemunits">'.$item['units'].'</span></td>';
			while ($moncurts['mon'] == $mon) {
				$dclass = "notbusy";
				$dalt = "";
				$bid = "";
				$totfound = 0;
				if (@is_array($arrbusy[$item['id']])) {
					foreach ($arrbusy[$item['id']] as $b) {
						$tmpone = getdate($b['ritiro']);
						$rit = ($tmpone['mon'] < 10 ? "0".$tmpone['mon'] : $tmpone['mon'])."/".($tmpone['mday'] < 10 ? "0".$tmpone['mday'] : $tmpone['mday'])."/".$tmpone['year'];
						$ritts = strtotime($rit);
						$tmptwo = getdate($b['consegna']);
						$con = ($tmptwo['mon'] < 10 ? "0".$tmptwo['mon'] : $tmptwo['mon'])."/".($tmptwo['mday'] < 10 ? "0".$tmptwo['mday'] : $tmptwo['mday'])."/".$tmptwo['year'];
						$conts = strtotime($con);
						if ($moncurts[0] >= $ritts && $moncurts[0] <= $conts) {
							$dclass = "busy";
							$bid = $b['idorder'];
							if ($moncurts[0] == $ritts) {
								$dalt = JText::_('VRPICKUPAT')." ".date($nowtf, $b['ritiro']);
							} elseif ($moncurts[0] == $conts) {
								$dalt = JText::_('VRRELEASEAT')." ".date($nowtf, $b['consegna']);
							}
							$totfound++;
						}
					}
				}
				$useday = ($moncurts['mday'] < 10 ? "0".$moncurts['mday'] : $moncurts['mday']);
				$write_units = $show_type == 'units-left' || (!empty($cookie_uleft) && $cookie_uleft == 'units-left') ? ($item['units'] - $totfound) : $totfound;
				if ($totfound == 1) {
					$dlnk = "<a href=\"index.php?option=com_vikrentitems&task=editbusy&goto=overv&cid[]=".$bid."\" class=\"vri-overview-redday\" style=\"color: #ffffff;\" data-units-booked=\"".$totfound."\" data-units-left=\"".($item['units'] - $totfound)."\">".$write_units."</a>";
					$cal = "<td align=\"center\" class=\"".$dclass."\"".(!empty($dalt) ? " title=\"".$dalt."\"" : "").">".$dlnk."</td>\n";
				} elseif ($totfound > 1) {
					$dlnk = "<a href=\"index.php?option=com_vikrentitems&task=choosebusy&goto=overv&iditem=".$item['id']."&ts=".$moncurts[0]."\" class=\"vri-overview-redday\" style=\"color: #ffffff;\" data-units-booked=\"".$totfound."\" data-units-left=\"".($item['units'] - $totfound)."\">".$write_units."</a>";
					$cal = "<td align=\"center\" class=\"".$dclass."\">".$dlnk."</td>\n";
				} else {
					$dlnk = $useday;
					$cal = "<td align=\"center\" class=\"".$dclass."\">&nbsp;</td>\n";
				}
				echo $cal;
				$moncurts = getdate(mktime(0, 0, 0, $moncurts['mon'], ($moncurts['mday'] + 1), $moncurts['year']));
			}
			echo '</tr>';
		}
		?>
	</table>
</div>
<?php echo ($mind + 1) <= $mnum ? '<br/>' : ''; ?>
<?php
	$curts = getdate(mktime(0, 0, 0, ($nowts['mon'] + $mind), $nowts['mday'], $nowts['year']));
}
?>

<form action="index.php?option=com_vikrentitems" method="post" name="adminForm" id="adminForm">
	<input type="hidden" name="option" value="com_vikrentitems" />
	<input type="hidden" name="task" value="overv" />
	<input type="hidden" name="month" value="<?php echo $tsstart; ?>" />
	<input type="hidden" name="mnum" value="<?php echo $mnum; ?>" />
	<?php echo '<br/>'.$navbut; ?>
</form>
