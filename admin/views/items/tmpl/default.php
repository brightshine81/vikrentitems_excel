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
$cat_filter = $this->cat_filter;

$dbo = JFactory::getDBO();
$filtni = VikRequest::getString('filtni', '', 'request');
$filtcateg = VikRequest::getInt('filtcateg', '', 'request');
?>
<div class="btn-toolbar" id="filter-bar">
	<form action="index.php?option=com_vikrentitems&amp;task=items" id="filtni-form" method="post">
		<input type="hidden" name="task" value="items"/>
		<div class="btn-group pull-left">
			<input type="text" size="35" id="filtni" name="filtni" value="<?php echo $filtni; ?>" placeholder="<?php echo JText::_('VRIFILTINAME'); ?>"/>
		</div>
		<div class="btn-group pull-left">
			<button class="btn" type="submit"><i class="icon-search"></i></button>
			<button class="btn" type="button" onclick="jQuery('#filtni').val('');jQuery('#filtni-form').submit();"><i class="icon-remove"></i></button>
		</div>
	</form>
	<form action="index.php?option=com_vikrentitems&amp;task=items" method="post">
		<input type="hidden" name="task" value="items"/>
		<div class="btn-group pull-right">
			<button class="btn" type="submit"><?php echo JText::_('VRIFILTCATEGORY'); ?></button>
		</div>
		<div class="btn-group pull-right">
			<?php echo $cat_filter; ?>
		</div>
	</form>
</div>
<br clear="both" />
<?php
if (empty($rows)) {
	?>
	<p class="err"><?php echo JText::_('VRNOITEMSFOUND'); ?></p>
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
	if (pressbutton == 'removeitem') {
		if (confirm('<?php echo JText::_('VRJSDELITEM'); ?>?')) {
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
				<th class="title center" width="30"><a href="index.php?option=com_vikrentitems&amp;task=items&amp;vriorderby=id&amp;vriordersort=<?php echo ($orderby == "id" && $ordersort == "ASC" ? "DESC" : "ASC"); ?>" class="<?php echo ($orderby == "id" && $ordersort == "ASC" ? "vrisortasc" : ($orderby == "id" ? "vrisortdesc" : "")); ?>"><?php echo JText::_( 'VRIDASHUPRESONE' ); ?></a></th>
				<th class="title left" width="150"><a href="index.php?option=com_vikrentitems&amp;task=items&amp;vriorderby=name&amp;vriordersort=<?php echo ($orderby == "name" && $ordersort == "ASC" ? "DESC" : "ASC"); ?>" class="<?php echo ($orderby == "name" && $ordersort == "ASC" ? "vrisortasc" : ($orderby == "name" ? "vrisortdesc" : "")); ?>"><?php echo JText::_( 'VRPVIEWITEMONE' ); ?></a></th>
				<th class="title left" width="150"><?php echo JText::_( 'VRPVIEWITEMTWO' ); ?></th>
				<th class="title center" align="center" width="150"><?php echo JText::_( 'VRPVIEWITEMTHREE' ); ?></th>
				<th class="title center" align="center" width="150"><?php echo JText::_( 'VRPVIEWITEMFOUR' ); ?></th>
				<th class="title left" width="150"><?php echo JText::_( 'VRPVIEWITEMFIVE' ); ?></th>
				<th class="title center" align="center" width="100"><a href="index.php?option=com_vikrentitems&amp;task=items&amp;vriorderby=units&amp;vriordersort=<?php echo ($orderby == "units" && $ordersort == "ASC" ? "DESC" : "ASC"); ?>" class="<?php echo ($orderby == "units" && $ordersort == "ASC" ? "vrisortasc" : ($orderby == "units" ? "vrisortdesc" : "")); ?>"><?php echo JText::_( 'VRPVIEWITEMSEVEN' ); ?></a></th>
				<th class="title center" align="center" width="100"><a href="index.php?option=com_vikrentitems&amp;task=items&amp;vriorderby=avail&amp;vriordersort=<?php echo ($orderby == "avail" && $ordersort == "ASC" ? "DESC" : "ASC"); ?>" class="<?php echo ($orderby == "avail" && $ordersort == "ASC" ? "vrisortasc" : ($orderby == "avail" ? "vrisortdesc" : "")); ?>"><?php echo JText::_( 'VRPVIEWITEMSIX' ); ?></a></th>
			</tr>
		</thead>
	<?php
	$kk = 0;
	$i = 0;
	for ($i = 0, $n = count($rows); $i < $n; $i++) {
		$row = $rows[$i];
		$q = "SELECT COUNT(*) AS `totdisp` FROM `#__vikrentitems_dispcost` WHERE `iditem`='".$row['id']."' ORDER BY `#__vikrentitems_dispcost`.`days`;";
		$dbo->setQuery($q);
		$dbo->execute();
		$lines = $dbo->loadAssocList();
		$tot = $lines[0]['totdisp'];
		$categories = "";
		if (!empty($row['idcat'])) {
			$cat = explode(";", $row['idcat']);
			$catids = array();
			foreach ($cat as $k => $cc) {
				if (!empty($cc)) {
					$catids[] = (int)$cc;
				}
			}
			if (count($catids)) {
				$q = "SELECT `name` FROM `#__vikrentitems_categories` WHERE `id` IN (".implode(', ', $catids).");";
				$dbo->setQuery($q);
				$dbo->execute();
				if ($dbo->getNumRows() > 0) {
					$lines = $dbo->loadAssocList();
					$catnames = array();
					foreach ($lines as $ll) {
						$catnames[] = $ll['name'];
					}
					$categories = implode(", ", $catnames);
				}
			}
		}
		
		$caratteristiche = "";
		if (!empty($row['idcarat'])) {
			$tmpcarat = explode(";", $row['idcarat']);
			$caratteristiche = VikRentItems::totElements($tmpcarat);
		}
		
		$optionals = "";
		if (!empty($row['idopt'])) {
			$tmpopt = explode(";", $row['idopt']);
			$optionals = VikRentItems::totElements($tmpopt);
		}
		
		$luogo = "";
		if (!empty($row['idplace'])) {
			$explace = explode(";", $row['idplace']);
			$q = "SELECT `id`,`name` FROM `#__vikrentitems_places` WHERE `id`=".$dbo->quote($explace[0]).";";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() > 0) {
				$lines = $dbo->loadAssoc();
				$luogo = $lines['name'];
				if (@count($explace) > 2) {
					$luogo .= " ...";
				}
			}
		}
		
		?>
		<tr class="row<?php echo $kk; ?>">
			<td><input type="checkbox" id="cb<?php echo $i;?>" name="cid[]" value="<?php echo $row['id']; ?>" onclick="Joomla.isChecked(this.checked);"></td>
			<td class="center"><a href="index.php?option=com_vikrentitems&amp;task=edititem&amp;cid[]=<?php echo $row['id']; ?>"><?php echo $row['id']; ?></a></td>
			<td><?php echo $row['isgroup'] > 0 ? '<i class="vriicn-stack" title="'.JText::_('VRITEMISAGROUP').'"></i> ' : ''; ?><a href="index.php?option=com_vikrentitems&amp;task=edititem&amp;cid[]=<?php echo $row['id']; ?>"><?php echo $row['name']; ?></a></td>
			<td><?php echo $categories; ?></td>
			<td class="center"><?php echo $caratteristiche; ?></td>
			<td class="center"><?php echo $optionals; ?></td>
			<td><?php echo $luogo; ?></td>
			<td class="center"><?php echo $row['units']; ?></td>
			<td class="center"><a href="index.php?option=com_vikrentitems&amp;task=modavail&amp;cid[]=<?php echo $row['id']; ?>"><?php echo (intval($row['avail'])=="1" ? "<i class=\"fa fa-check vri-icn-img\" style=\"color: #099909;\" title=\"".JText::_('VRMAKENOTAVAIL')."\"></i>" : "<i class=\"fa fa-times-circle vri-icn-img\" style=\"color: #ff0000;\" title=\"".JText::_('VRMAKEAVAIL')."\"></i>"); ?></a></td>
		</tr>
		<?php
		$kk = 1 - $kk;
		unset($categories);
	}
	?>
	</table>
	<input type="hidden" name="option" value="com_vikrentitems" />
	<?php
	if (!empty($filtni)) {
		echo '<input type="hidden" name="filtni" value="'.$filtni.'" />';
	} elseif (!empty($filtcateg)) {
		echo '<input type="hidden" name="filtcateg" value="'.$filtcateg.'" />';
	}
	?>
	<input type="hidden" name="task" value="items" />
	<input type="hidden" name="boxchecked" value="0" />
	<?php echo JHTML::_( 'form.token' ); ?>
	<?php echo $navbut; ?>
</form>
<?php
}
