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

if (empty($rows)) {
	?>
	<p class="warn"><?php echo JText::_('VRNOOPTIONALSFOUND'); ?></p>
	<form action="index.php?option=com_vikrentitems" method="post" name="adminForm" id="adminForm">
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="option" value="com_vikrentitems" />
	</form>
	<?php
} else {
	
	?>
<script type="text/javascript">
function submitbutton(pressbutton) {
	var form = document.adminForm;
	if (pressbutton == 'removeplace') {
		if (confirm('<?php echo JText::_('VRJSDELOPTIONALS'); ?> ?')) {
			submitform( pressbutton );
			return;
		} else{
			return false;
		}
	}

	// do field validation
	try {
		document.adminForm.onsubmit();
	}
	catch(e) {}
	submitform( pressbutton );
}
</script>

<form action="index.php?option=com_vikrentitems" method="post" name="adminForm" id="adminForm">
<table cellpadding="4" cellspacing="0" border="0" width="100%" class="table table-striped">
	<thead>
	<tr>
		<th width="20">
			<input type="checkbox" onclick="Joomla.checkAll(this)" value="" name="checkall-toggle">
		</th>
		<th class="title left" width="150"><?php echo JText::_( 'VRPVIEWOPTIONALSONE' ); ?></th>
		<th class="title left" width="150"><?php echo JText::_( 'VRPVIEWOPTIONALSTWO' ); ?></th>
		<th class="title center" width="75"><?php echo JText::_( 'VRPVIEWOPTIONALSTHREE' ); ?></th>
		<th class="title center" width="75"><?php echo JText::_( 'VRPVIEWOPTIONALSFOUR' ); ?></th>
		<th class="title center" width="75"><?php echo JText::_( 'VRPVIEWOPTIONALSEIGHT' ); ?></th>
		<th class="title center" width="150"><?php echo JText::_( 'VRPVIEWOPTIONALSFIVE' ); ?></th>
		<th class="title center" width="150"><?php echo JText::_( 'VRPVIEWOPTIONALSSIX' ); ?></th>
		<th class="title left" width="150"><?php echo JText::_( 'VRPVIEWOPTIONALSSEVEN' ); ?></th>
		<th class="title center" width="60"><?php echo JText::_( 'VRIORDERING' ); ?></th>
	</tr>
	</thead>
	<?php

	$k = 0;
	$i = 0;
	for ($i = 0, $n = count($rows); $i < $n; $i++) {
		$row = $rows[$i];
		?>
		<tr class="row<?php echo $k; ?>">
			<td><input type="checkbox" id="cb<?php echo $i;?>" name="cid[]" value="<?php echo $row['id']; ?>" onclick="Joomla.isChecked(this.checked);"></td>
			<td><a href="index.php?option=com_vikrentitems&amp;task=editoptional&amp;cid[]=<?php echo $row['id']; ?>"><?php echo $row['name']; ?></a></td>
			<td><?php echo (strlen($row['descr'])>150 ? substr($row['descr'], 0, 150) : $row['descr']); ?></td>
			<td class="center"><?php echo $row['cost']; ?></td>
			<td class="center"><?php echo VikRentItems::getAliq($row['idiva']); ?>%</td>
			<td class="center"><?php echo $row['maxprice']; ?></td>
			<td class="center"><?php echo (intval($row['perday'])==1 ? "Y" : "N"); ?></td>
			<td class="center"><?php echo (intval($row['hmany'])==1 ? "&gt; 1" : "1"); ?></td>
			<td><?php echo (!empty($row['img']) && file_exists(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_vikrentitems'.DS.'resources'.DS.$row['img']) ? "<span>".$row['img']." &nbsp;&nbsp;<img align=\"middle\" class=\"maxfifty\" src=\"./components/com_vikrentitems/resources/".$row['img']."\"/></span>" : ""); ?></td>
			<td class="center"><a href="index.php?option=com_vikrentitems&amp;task=sortoptional&amp;cid[]=<?php echo $row['id']; ?>&amp;mode=up"><i class="fa fa-arrow-up vri-icn-img"></i></a> <a href="index.php?option=com_vikrentitems&amp;task=sortoptional&amp;cid[]=<?php echo $row['id']; ?>&amp;mode=down"><i class="fa fa-arrow-down vri-icn-img"></i></a></td>
		</tr>
		<?php
		$k = 1 - $k;
	}
	?>
	
	</table>
	<input type="hidden" name="option" value="com_vikrentitems" />
	<input type="hidden" name="task" value="optionals" />
	<input type="hidden" name="boxchecked" value="0" />
	<?php echo JHTML::_( 'form.token' ); ?>
	<?php echo $navbut; ?>
</form>
<?php
}
