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
$totres = $this->totres;
$pts = $this->pts;

$dbo = JFactory::getDbo();

if (is_file(VRI_ADMIN_PATH.DS.'resources'.DS.$rows[0]['img']) && getimagesize(VRI_ADMIN_PATH.DS.'resources'.DS.$rows[0]['img'])) {
	$img = '<img align="middle" class="maxninety" alt="Item Image" src="' . VRI_ADMIN_URI . 'resources/'.$rows[0]['img'].'" />';
} else {
	$img = '<img align="middle" alt="Vik Rent Items logo" src="' . VRI_ADMIN_URI . 'vikrentitems.png' . '" />';
}
$unitsdisp = $rows[0]['units'] - $totres;
$unitsdisp = ($unitsdisp < 0 ? "0" : $unitsdisp);
$pgoto = VikRequest::getString('goto', '', 'request');
?>
<table>
	<tr>
		<td><div class="vri-item-title"><?php echo JText::_('VRMAINCHOOSEBUSY'); ?> <?php echo $rows[0]['name']; ?></div></td>
	</tr>
	<tr>
		<td><?php echo $img; ?></td>
	</tr>
</table>

<br/>
<button class="btn btn-primary" type="button">
		<?php echo JText::_('VRPCHOOSEBUSYCAVAIL'); ?>: <span class="badge"><?php echo $unitsdisp; ?>/<?php echo $rows[0]['units']; ?></span>
</button>
<br/>

<form action="index.php?option=com_vikrentitems" method="post" name="adminForm" id="adminForm">
<table cellpadding="4" cellspacing="0" border="0" width="100%" class="table table-striped">
	<thead>
	<tr>
		<th class="title center" width="50"><?php echo JText::_( 'VRIORDERNUMBER' ); ?></th>
		<th class="title left" width="250"><?php echo JText::_( 'VRPVIEWORDERSTWO' ); ?></th>
		<th class="title left" width="150"><?php echo JText::_( 'VRPVIEWORDERSFOUR' ); ?></th>
		<th class="title left" width="150"><?php echo JText::_( 'VRPVIEWORDERSFIVE' ); ?></th>
		<th class="title center" width="100"><?php echo JText::_( 'VRPCHOOSEBUSYITEMQUANT' ); ?></th>
		<th class="title left" width="150"><?php echo JText::_( 'VRPCHOOSEBUSYORDATE' ); ?></th>
	</tr>
	</thead>
	<?php
	$nowdf = VikRentItems::getDateFormat(true);
	$nowtf = VikRentItems::getTimeFormat(true);
	if ($nowdf == "%d/%m/%Y") {
		$df = 'd/m/Y';
	} elseif ($nowdf == "%m/%d/%Y") {
		$df = 'm/d/Y';
	} else {
		$df = 'Y/m/d';
	}
	$k = 0;
	$i = 0;
	for ($i = 0, $n = count($rows); $i < $n; $i++) {
		$row = $rows[$i];
		//Customer Details
		$custdata = $row['custdata'];
		$custdata_parts = explode("\n", $row['custdata']);
		if (count($custdata_parts) > 2 && strpos($custdata_parts[0], ':') !== false && strpos($custdata_parts[1], ':') !== false) {
			//get the first two fields
			$custvalues = array();
			foreach ($custdata_parts as $custdet) {
				if (strlen($custdet) < 1) {
					continue;
				}
				$custdet_parts = explode(':', $custdet);
				if (count($custdet_parts) >= 2) {
					unset($custdet_parts[0]);
					array_push($custvalues, trim(implode(':', $custdet_parts)));
				}
				if (count($custvalues) > 1) {
					break;
				}
			}
			if (count($custvalues) > 1) {
				$custdata = implode(' ', $custvalues);
			}
		}
		if (strlen($custdata) > 45) {
			$custdata = substr($custdata, 0, 45)." ...";
		}
		$q = "SELECT `c`.*,`co`.`idorder` FROM `#__vikrentitems_customers` AS `c` LEFT JOIN `#__vikrentitems_customers_orders` `co` ON `c`.`id`=`co`.`idcustomer` WHERE `co`.`idorder`=".$row['id'].";";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$cust_country = $dbo->loadAssocList();
			$cust_country = $cust_country[0];
			if (!empty($cust_country['first_name'])) {
				$custdata = $cust_country['first_name'].' '.$cust_country['last_name'];
				if (!empty($cust_country['country'])) {
					if (file_exists(VRI_ADMIN_PATH.DS.'resources'.DS.'countries'.DS.$row['country'].'.png')) {
						$custdata .= '<img src="'.VRI_ADMIN_URI.'resources/countries/'.$row['country'].'.png'.'" title="'.$row['country'].'" class="vri-country-flag vri-country-flag-left"/>';
					}
				}
			}
		} elseif (!empty($row['nominative'])) {
			$custdata = $row['nominative'];
			if (!empty($row['country'])) {
				if (file_exists(VRI_ADMIN_PATH.DS.'resources'.DS.'countries'.DS.$row['country'].'.png')) {
					$custdata .= '<img src="'.VRI_ADMIN_URI.'resources/countries/'.$row['country'].'.png'.'" title="'.$row['country'].'" class="vri-country-flag vri-country-flag-left"/>';
				}
			}
		}
		//
		?>
		<tr class="row<?php echo $k; ?>">
			<td class="center"><a class="vri-orderid" href="index.php?option=com_vikrentitems&amp;task=editbusy<?php echo (!empty($pgoto) ? '&amp;goto='.$pgoto : ''); ?>&amp;cid[]=<?php echo $row['idorder']; ?>"><?php echo $row['idorder']; ?></a></td>
			<td><?php echo $custdata; ?></td>
			<td><a href="index.php?option=com_vikrentitems&amp;task=editbusy<?php echo (!empty($pgoto) ? '&amp;goto='.$pgoto : ''); ?>&amp;cid[]=<?php echo $row['idorder']; ?>"><?php echo date($df.' '.$nowtf, $row['ritiro']); ?></a></td>
			<td><span<?php echo $row['consegna'] != $row['realback'] ? ' title="'.date($df.' '.$nowtf, $row['realback']).'"' : ''; ?>><?php echo date($df.' '.$nowtf, $row['consegna']); ?></span></td>
			<td class="center"><?php echo $row['totquant']; ?></td>
			<td><?php echo date($df.' '.$nowtf, $row['ts']); ?></td>
		</tr>
		<?php
		$k = 1 - $k;
	}
	?>
	
	</table>
	<input type="hidden" name="iditem" value="<?php echo $rows[0]['iditem']; ?>" />
	<input type="hidden" name="ts" value="<?php echo $pts; ?>" />
	<input type="hidden" name="option" value="com_vikrentitems" />
	<input type="hidden" name="task" value="choosebusy" />
	<input type="hidden" name="boxchecked" value="0" />
	<?php echo JHTML::_( 'form.token' ); ?>
	<?php echo $navbut; ?>
</form>
