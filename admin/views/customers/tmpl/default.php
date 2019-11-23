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
$orderby = $this->orderby;
$ordersort = $this->ordersort;

$pfiltercustomer = VikRequest::getString('filtercustomer', '', 'request');
?>
<form action="index.php?option=com_vikrentitems&amp;task=customers" method="post" name="customersform">
	<div style="width: 100%; display: inline-block;" class="btn-toolbar" id="filter-bar">
		<div class="btn-group pull-left input-append">
			<input type="text" name="filtercustomer" id="filtercustomer" value="<?php echo $pfiltercustomer; ?>" size="40" placeholder="<?php echo JText::_('VRCUSTOMERFIRSTNAME').', '.JText::_('VRCUSTOMERLASTNAME').', '.JText::_('VRCUSTOMEREMAIL').', '.JText::_('VRCUSTOMERPIN'); ?>"/>
			<button type="button" class="btn btn-secondary" onclick="document.customersform.submit();"><i class="icon-search"></i></button>
		</div>
		<div class="btn-group pull-left">
			<button type="button" class="btn btn-secondary" onclick="document.getElementById('filtercustomer').value='';document.customersform.submit();"><?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?></button>
		</div>
		
	</div>
	<input type="hidden" name="task" value="customers" />
	<input type="hidden" name="option" value="com_vikrentitems" />
</form>
<?php
if (empty($rows)) {
	?>
	<p class="warn"><?php echo JText::_('VRNOCUSTOMERS'); ?></p>
	<form action="index.php?option=com_vikrentitems" method="post" name="adminForm" id="adminForm">
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="option" value="com_vikrentitems" />
	</form>
	<?php
} else {
?>
<form action="index.php?option=com_vikrentitems" method="post" name="adminForm" id="adminForm">
<div class="table-responsive">
	<table cellpadding="4" cellspacing="0" border="0" width="100%" class="table table-striped vri-list-table">
		<thead>
		<tr>
			<th width="20">
				<input type="checkbox" onclick="Joomla.checkAll(this)" value="" name="checkall-toggle">
			</th>
			<th class="title left" width="75">
				<a href="index.php?option=com_vikrentitems&amp;task=customers&amp;vrorderby=id&amp;vrordersort=<?php echo ($orderby == "id" && $ordersort == "ASC" ? "DESC" : "ASC"); ?>" class="<?php echo ($orderby == "id" && $ordersort == "ASC" ? "vri-list-activesort" : ($orderby == "id" ? "vri-list-activesort" : "")); ?>">
					ID<?php echo ($orderby == "id" && $ordersort == "ASC" ? '<i class="fa fa-sort-asc"></i>' : ($orderby == "id" ? '<i class="fa fa-sort-desc"></i>' : '<i class="fa fa-sort"></i>')); ?>
				</a>
			</th>
			<th class="title left" width="75">
				<a href="index.php?option=com_vikrentitems&amp;task=customers&amp;vrorderby=first_name&amp;vrordersort=<?php echo ($orderby == "first_name" && $ordersort == "ASC" ? "DESC" : "ASC"); ?>" class="<?php echo ($orderby == "first_name" && $ordersort == "ASC" ? "vri-list-activesort" : ($orderby == "first_name" ? "vri-list-activesort" : "")); ?>">
					<?php echo JText::_('VRCUSTOMERFIRSTNAME').($orderby == "first_name" && $ordersort == "ASC" ? '<i class="fa fa-sort-asc"></i>' : ($orderby == "first_name" ? '<i class="fa fa-sort-desc"></i>' : '<i class="fa fa-sort"></i>')); ?>
				</a>
			</th>
			<th class="title left" width="75">
				<a href="index.php?option=com_vikrentitems&amp;task=customers&amp;vrorderby=last_name&amp;vrordersort=<?php echo ($orderby == "last_name" && $ordersort == "ASC" ? "DESC" : "ASC"); ?>" class="<?php echo ($orderby == "last_name" && $ordersort == "ASC" ? "vri-list-activesort" : ($orderby == "last_name" ? "vri-list-activesort" : "")); ?>">
					<?php echo JText::_('VRCUSTOMERLASTNAME').($orderby == "last_name" && $ordersort == "ASC" ? '<i class="fa fa-sort-asc"></i>' : ($orderby == "last_name" ? '<i class="fa fa-sort-desc"></i>' : '<i class="fa fa-sort"></i>')); ?>
				</a>
			</th>
			<th class="title left" width="75">
				<a href="index.php?option=com_vikrentitems&amp;task=customers&amp;vrorderby=email&amp;vrordersort=<?php echo ($orderby == "email" && $ordersort == "ASC" ? "DESC" : "ASC"); ?>" class="<?php echo ($orderby == "email" && $ordersort == "ASC" ? "vri-list-activesort" : ($orderby == "email" ? "vri-list-activesort" : "")); ?>">
					<?php echo JText::_('VRCUSTOMEREMAIL').($orderby == "email" && $ordersort == "ASC" ? '<i class="fa fa-sort-asc"></i>' : ($orderby == "email" ? '<i class="fa fa-sort-desc"></i>' : '<i class="fa fa-sort"></i>')); ?>
				</a>
			</th>
			<th class="title left" width="75">
				<a href="index.php?option=com_vikrentitems&amp;task=customers&amp;vrorderby=phone&amp;vrordersort=<?php echo ($orderby == "phone" && $ordersort == "ASC" ? "DESC" : "ASC"); ?>" class="<?php echo ($orderby == "phone" && $ordersort == "ASC" ? "vri-list-activesort" : ($orderby == "phone" ? "vri-list-activesort" : "")); ?>">
					<?php echo JText::_('VRCUSTOMERPHONE').($orderby == "phone" && $ordersort == "ASC" ? '<i class="fa fa-sort-asc"></i>' : ($orderby == "phone" ? '<i class="fa fa-sort-desc"></i>' : '<i class="fa fa-sort"></i>')); ?>
				</a>
			</th>
			<th class="title center" width="75">
				<a href="index.php?option=com_vikrentitems&amp;task=customers&amp;vrorderby=country&amp;vrordersort=<?php echo ($orderby == "country" && $ordersort == "ASC" ? "DESC" : "ASC"); ?>" class="<?php echo ($orderby == "country" && $ordersort == "ASC" ? "vri-list-activesort" : ($orderby == "country" ? "vri-list-activesort" : "")); ?>">
					<?php echo JText::_('VRCUSTOMERCOUNTRY').($orderby == "country" && $ordersort == "ASC" ? '<i class="fa fa-sort-asc"></i>' : ($orderby == "country" ? '<i class="fa fa-sort-desc"></i>' : '<i class="fa fa-sort"></i>')); ?>
				</a>
			</th>
			<th class="title center" width="75">
				<a href="index.php?option=com_vikrentitems&amp;task=customers&amp;vrorderby=pin&amp;vrordersort=<?php echo ($orderby == "pin" && $ordersort == "ASC" ? "DESC" : "ASC"); ?>" class="<?php echo ($orderby == "pin" && $ordersort == "ASC" ? "vri-list-activesort" : ($orderby == "pin" ? "vri-list-activesort" : "")); ?>">
					<?php echo JText::_('VRCUSTOMERPIN').($orderby == "pin" && $ordersort == "ASC" ? '<i class="fa fa-sort-asc"></i>' : ($orderby == "pin" ? '<i class="fa fa-sort-desc"></i>' : '<i class="fa fa-sort"></i>')); ?>
				</a>
			</th>
			<th class="title center" width="75">
				<a href="index.php?option=com_vikrentitems&amp;task=customers&amp;vrorderby=tot_bookings&amp;vrordersort=<?php echo ($orderby == "tot_bookings" && $ordersort == "ASC" ? "DESC" : "ASC"); ?>" class="<?php echo ($orderby == "tot_bookings" && $ordersort == "ASC" ? "vri-list-activesort" : ($orderby == "tot_bookings" ? "vri-list-activesort" : "")); ?>">
					<?php echo JText::_('VRCUSTOMERTOTBOOKINGS').($orderby == "tot_bookings" && $ordersort == "ASC" ? '<i class="fa fa-sort-asc"></i>' : ($orderby == "tot_bookings" ? '<i class="fa fa-sort-desc"></i>' : '<i class="fa fa-sort"></i>')); ?>
				</a>
			</th>
			<th class="title center" width="75">&nbsp;</th>
		</tr>
		</thead>
	<?php
	$kk = 0;
	$i = 0;
	for ($i = 0, $n = count($rows); $i < $n; $i++) {
		$row = $rows[$i];
		$country_flag = '';
		if (!empty($row['country']) && !empty($row['country_full_name'])) {
			if (file_exists(VRI_ADMIN_PATH.DS.'resources'.DS.'countries'.DS.$row['country'].'.png')) {
				$country_flag = '<img src="'.VRI_ADMIN_URI.'resources/countries/'.$row['country'].'.png'.'" title="'.$row['country_full_name'].'" class="vri-country-flag"/>';
			}
		}
		?>
		<tr class="row<?php echo $kk; ?>">
			<td><input type="checkbox" id="cb<?php echo $i;?>" name="cid[]" value="<?php echo $row['id']; ?>" onclick="Joomla.isChecked(this.checked);"></td>
			<td><a href="index.php?option=com_vikrentitems&amp;task=editcustomer&amp;cid[]=<?php echo $row['id']; ?>"><?php echo $row['id']; ?></a></td>
			<td><a href="index.php?option=com_vikrentitems&amp;task=editcustomer&amp;cid[]=<?php echo $row['id']; ?>"><?php echo $row['first_name']; ?></a></td>
			<td><?php echo $row['last_name']; ?></td>
			<td><?php echo $row['email']; ?></td>
			<td><?php echo $row['phone']; ?></td>
			<td class="center"><?php echo empty($country_flag) ? $row['country'] : $country_flag; ?></td>
			<td class="center"><?php echo $row['pin']; ?></td>
			<td class="center"><?php echo $row['tot_bookings']; ?></td>
			<td class="center"><?php echo ($row['tot_bookings'] > 0 ? '<a href="index.php?option=com_vikrentitems&task=orders&cust_id='.$row['id'].'" class="btn hasTooltip" title="'.JText::_('VRMENUSEVEN').'"><i class="icon-eye"></i></a>' : ''); ?></td>
		 </tr>
		  <?php
		$kk = 1 - $kk;
	}
	?>
	</table>
</div>
	<input type="hidden" name="option" value="com_vikrentitems" />
	<input type="hidden" name="task" value="customers" />
	<input type="hidden" name="boxchecked" value="0" />
	<?php echo JHTML::_( 'form.token' ); ?>
	<?php echo $navbut; ?>
</form>

<script type="text/javascript">
if (jQuery.isFunction(jQuery.fn.tooltip)) {
	jQuery(".hasTooltip").tooltip();
}
</script>
<?php
}