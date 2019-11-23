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

$itemrows = $this->itemrows;
$rows = $this->rows;
$prices = $this->prices;
$allc = $this->allc;

$vri_app = VikRentItems::getVriApplication();
$vri_app->loadSelect2();

//header
$iditem = $itemrows['id'];
$name = $itemrows['name'];
if (is_file(VRI_ADMIN_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.$itemrows['img']) && getimagesize(VRI_ADMIN_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.$itemrows['img'])) {
	$img = '<img align="middle" class="maxninety" alt="Item Image" src="' . VRI_ADMIN_URI . 'resources/'.$itemrows['img'].'" />';
} else {
	$img = '<img align="middle" alt="Vik Rent Items Logo" src="' . VRI_ADMIN_URI . 'vikrentitems.png' . '" />';
}
$fprice = "<div class=\"vri-fares-tabs\"><div class=\"dailyprices\"><a href=\"index.php?option=com_vikrentitems&task=tariffs&cid[]=".$iditem."\">".JText::_('VRIDAILYFARES')."</a></div><div class=\"hourschargesactive\">".JText::_('VRIHOURSCHARGES')."</div><div class=\"hourlyprices\"><a href=\"index.php?option=com_vikrentitems&task=tariffshours&cid[]=".$iditem."\">".JText::_('VRIHOURLYFARES')."</a></div></div>\n";
if (empty($prices)) {
	$fprice .= "<br/><span class=\"err\"><b>".JText::_('VRMSGONE')." <a href=\"index.php?option=com_vikrentitems&task=newprice\">".JText::_('VRHERE')."</a></b></span>";
} else {
	$colsp = "2";
	$fprice .= "<form name=\"newd\" class=\"vri-fares-frm\" method=\"post\" action=\"index.php?option=com_vikrentitems\" onsubmit=\"javascript: if (!document.newd.hhoursfrom.value.match(/\S/)){alert('".JText::_('VRMSGTWO')."'); return false;} else {return true;}\">\n<div class=\"vri-insertrates-cont\"><span class=\"vri-ratestable-lbl\">".JText::_('VRIHOURS').": </span><table><tr><td><span class=\"vri-fares-from-lbl\">".JText::_('VRDAYSFROM')."</span> <input type=\"number\" name=\"hhoursfrom\" id=\"hhoursfrom\" value=\"".(!is_array($prices) ? '1' : '')."\" min=\"1\" /></td><td>&nbsp;&nbsp;&nbsp; <span class=\"vri-fares-to-lbl\">".JText::_('VRDAYSTO')."</span> <input type=\"number\" name=\"hhoursto\" id=\"hhoursto\" value=\"".(!is_array($prices) ? '30' : '')."\" min=\"1\" max=\"23\" /></td></tr></table>\n";
	$fprice .= "<span class=\"vri-ratestable-lbl\">".JText::_('VRIHOURLYCHARGES').": </span><table>\n";
	$currencysymb = VikRentItems::getCurrencySymb(true);
	foreach ($prices as $pr) {
		$fprice .= "<tr><td>".$pr['name'].": </td><td>".$currencysymb." <input type=\"number\" min=\"0\" step=\"any\" name=\"hprice".$pr['id']."\" value=\"\" style=\"width: 70px !important;\"/></td>";
		if (!empty($pr['attr'])) {
			$colsp = "4";
			$fprice .= "<td>".$pr['attr']."</td><td><input type=\"text\" name=\"hattr".$pr['id']."\" value=\"\" size=\"10\"/></td>";
		}
		$fprice .= "</tr>\n";
	}
	$fprice .= "<tr><td colspan=\"".$colsp."\" align=\"right\"><input type=\"submit\" class=\"vrsubmitfares btn btn-large btn-success\" name=\"newdispcost\" value=\"".JText::_('VRINSERT')."\"/></td></tr></table></div><input type=\"hidden\" name=\"cid[]\" value=\"".$iditem."\"/><input type=\"hidden\" name=\"task\" value=\"hourscharges\"/></form>";
}
$chitemsel = "<select id=\"vri-item-selection\" name=\"cid[]\" onchange=\"javascript: document.vrchitem.submit();\">\n";
foreach ($allc as $cc) {
	$chitemsel .= "<option value=\"".$cc['id']."\"".($cc['id'] == $iditem ? " selected=\"selected\"" : "").">".$cc['name']."</option>\n";
}
$chitemsel .= "</select>\n";
$chitemf = "<form name=\"vrchitem\" method=\"post\" action=\"index.php?option=com_vikrentitems\"><input type=\"hidden\" name=\"task\" value=\"hourscharges\"/>".$chitemsel."</form>";
echo "<table><tr><td colspan=\"2\" valign=\"top\" align=\"left\"><div class=\"vriadminfaresctitle\"><span class=\"vri-uppbold\">".$name." - ".JText::_('VRINSERTFEE')."</span> <span style=\"float: right; text-transform: none;\">".$chitemf."</span></div></td></tr><tr><td valign=\"top\" align=\"left\">".$img."</td><td valign=\"top\" align=\"left\">".$fprice."</td></tr></table><br/>\n";
?>
<script type="text/javascript">
jQuery(document).ready(function(){
	jQuery('#hhoursfrom').change(function() {
		var fnights = parseInt(jQuery(this).val());
		if (!isNaN(fnights)) {
			jQuery('#hhoursto').attr('min', fnights);
			var tnights = jQuery('#hhoursto').val();
			if (!(tnights.length > 0)) {
				jQuery('#hhoursto').val(fnights);
			} else {
				if (parseInt(tnights) < fnights) {
					jQuery('#hhoursto').val(fnights);
				}
			}
		}
	});
	jQuery("#vri-item-selection").select2();
});
</script>

<?php
//page content

if (empty($rows)) {
	?>
	<p class="warn"><?php echo JText::_('VRNOTARFOUND'); ?></p>
	<form name="adminForm" id="adminForm" action="index.php" method="post">
	<input type="hidden" name="task" value="">
	<input type="hidden" name="option" value="com_vikrentitems">
	</form>
	<?php
} else {
	$mainframe = JFactory::getApplication();
	$lim = $mainframe->getUserStateFromRequest("com_vikrentitems.limit", 'limit', 15, 'int');
	$lim0 = VikRequest::getVar('limitstart', 0, '', 'int');
	$allpr = array();
	$tottar = array();
	foreach ($rows as $r) {
		if (!array_key_exists($r['idprice'], $allpr)) {
			$allpr[$r['idprice']] = VikRentItems::getPriceAttr($r['idprice']);
		}
		$tottar[$r['ehours']][] = $r;
	}
	$prord = array();
	$prvar = '';
	foreach ($allpr as $kap => $ap) {
		$prord[] = $kap;
		$prvar .= "<th class=\"title center\" width=\"150\">".VikRentItems::getPriceName($kap).(!empty($ap) ? " - ".$ap : "")."</th>\n";
	}
	$totrows = count($tottar);
	$tottar = array_slice($tottar, $lim0, $lim, true);
	?>
<script type="text/javascript">
function vrRateSetTask(event) {
	event.preventDefault();
	document.getElementById('vrtarmod').value = '1';
	document.getElementById('vrtask').value = 'items';
	document.adminForm.submit();
}
</script>
<form action="index.php?option=com_vikrentitems" method="post" name="adminForm" id="adminForm">
<div class="table-responsive">
	<table cellpadding="4" cellspacing="0" border="0" width="100%" class="table table-striped vri-list-table">
		<thead>
		<tr>
			<th class="title left" width="100" style="text-align: left;"><?php echo JText::_( 'VRIPVIEWTARHOURS' ); ?></th>
			<?php echo $prvar; ?>
			<th width="20" class="title right" style="text-align: right;">
				<input type="submit" name="modtarhourscharges" value="<?php echo JText::_( 'VRPVIEWTARTWO' ); ?>" onclick="vrRateSetTask(event);" class="btn" /> &nbsp; <input type="checkbox" onclick="Joomla.checkAll(this)" value="" name="checkall-toggle">
			</th>
		</tr>
		</thead>
	<?php
	$k = 0;
	$i = 0;
	foreach ($tottar as $kt => $vt) {
		?>
		<tr class="row<?php echo $k; ?>">
			<td class="left"><?php echo $kt; ?></td>
		<?php
		$multiid = "";
		foreach ($prord as $ord) {
			$thereis = false;
			foreach ($vt as $kkkt => $vvv) {
				if ($vvv['idprice'] == $ord) {
					$multiid .= $vvv['id'].";";
					echo "<td class=\"center\"><input type=\"number\" min=\"0\" step=\"any\" name=\"cost".$vvv['id']."\" value=\"".$vvv['cost']."\" style=\"width: 70px !important;\"/>".(!empty($vvv['attrdata'])? " - <input type=\"text\" name=\"attr".$vvv['id']."\" value=\"".$vvv['attrdata']."\" size=\"10\"/>" : "")."</td>\n";
					$thereis = true;
					break;
				}
			}
			
			if (!$thereis) {
				echo "<td></td>\n";
			}
			unset($thereis);
			
		}
		?>
		<td class="right" style="text-align: right;"><input type="checkbox" id="cb<?php echo $i;?>" name="cid[]" value="<?php echo $multiid; ?>" onclick="Joomla.isChecked(this.checked);"></td>
		</tr>
		<?php
		unset($multiid);
		$k = 1 - $k;
		$i++;
	}
	?>
	</table>
</div>
	<input type="hidden" name="elemid" value="<?php echo $itemrows['id']; ?>" />
	<input type="hidden" name="cid[]" value="<?php echo $itemrows['id']; ?>" />
	<input type="hidden" name="option" value="com_vikrentitems" />
	<input type="hidden" name="task" id="vrtask" value="hourscharges" />
	<input type="hidden" name="tarmodhourscharges" id="vrtarmod" value="" />
	<input type="hidden" name="boxchecked" value="0" />
	<?php echo JHTML::_( 'form.token' ); ?>
	<?php
	jimport('joomla.html.pagination');
	$pageNav = new JPagination( $totrows, $lim0, $lim );
	$navbut = "<table align=\"center\"><tr><td>".$pageNav->getListFooter()."</td></tr></table>";
	echo $navbut;
	?>
</form>
<?php
}
