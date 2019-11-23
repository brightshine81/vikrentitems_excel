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

$row = $this->row;
$cats = $this->cats;
$carats = $this->carats;
$optionals = $this->optionals;
$places = $this->places;
$all_items = $this->all_items;
$grouped_items = $this->grouped_items;

$vri_app = VikRentItems::getVriApplication();
$currencysymb = VikRentItems::getCurrencySymb(true);
$arrcats = array();
$arrcarats = array();
$arropts = array();
if (count($row)) {
	$oldcats = explode(";", $row['idcat']);
	foreach ($oldcats as $oc) {
		if (!empty($oc)) {
			$arrcats[$oc] = $oc;
		}
	}
	$oldcarats = explode(";", $row['idcarat']);
	foreach ($oldcarats as $ocr) {
		if (!empty($ocr)) {
			$arrcarats[$ocr] = $ocr;
		}
	}
	$oldopts = explode(";", $row['idopt']);
	foreach ($oldopts as $oopt) {
		if (!empty($oopt)) {
			$arropts[$oopt] = $oopt;
		}
	}
}
$wcats = "";
if (is_array($cats)) {
	$wcats = "<tr><td class=\"vri-config-param-cell\" width=\"200\"> <b>".JText::_('VRNEWITEMONE')."</b> </td><td>";
	$wcats .= "<select name=\"ccat[]\" multiple=\"multiple\" size=\"".(count($cats) + 1)."\">";
	foreach ($cats as $cat) {
		$wcats .= "<option value=\"".$cat['id']."\"".(array_key_exists($cat['id'], $arrcats) ? " selected=\"selected\"" : "").">".$cat['name']."</option>\n";
	}
	$wcats .= "</select></td></tr>\n";
}
$wplaces = "";
$wretplaces = "";
if (is_array($places)) {
	$wplaces = "<tr><td class=\"vri-config-param-cell\" width=\"200\"> <b>".JText::_('VRNEWITEMTWO')."</b> </td><td>";
	$wretplaces = "<tr><td class=\"vri-config-param-cell\" width=\"200\"> <b>".JText::_('VRNEWITEMDROPLOC')."</b> </td><td>";
	$wplaces .= "<select name=\"cplace[]\" id=\"cplace\" multiple=\"multiple\" size=\"".(count($places) + 1)."\" onchange=\"vriSelDropLocation();\">";
	$wretplaces .= "<select name=\"cretplace[]\" id=\"cretplace\" multiple=\"multiple\" size=\"".(count($places) + 1)."\">";
	$actplac = count($row) ? explode(";", $row['idplace']) : array();
	$actretplac = count($row) ? explode(";", $row['idretplace']) : array();
	foreach ($places as $place) {
		$wplaces .= "<option value=\"".$place['id']."\"".(in_array($place['id'], $actplac) ? " selected=\"selected\"" : "").">".$place['name']."</option>\n";
		$wretplaces .= "<option value=\"".$place['id']."\"".(in_array($place['id'], $actretplac) ? " selected=\"selected\"" : "").">".$place['name']."</option>\n";
	}
	$wplaces .= "</select></td></tr>\n";
	$wretplaces .= "</select></td></tr>\n";
}
if (is_array($carats)) {
	$wcarats = "<tr><td class=\"vri-config-param-cell\" width=\"200\"> <b>".JText::_('VRNEWITEMTHREE')."</b> </td><td>";
	$wcarats .= "<div class=\"vri-itementries-cont\">";
	$nn = 0;
	foreach ($carats as $kcarat => $carat) {
		$wcarats .= "<div class=\"vri-itementry-cont\"><input type=\"checkbox\" name=\"ccarat[]\" id=\"carat".$kcarat."\" value=\"".$carat['id']."\"".(array_key_exists($carat['id'], $arrcarats) ? " checked=\"checked\"" : "")."/> <label for=\"carat".$kcarat."\">".$carat['name']."</label></div>\n";
		$nn++;
		if (($nn % 3) == 0) {
			$wcarats .= "</div>\n<div class=\"vri-itementries-cont\">";
		}
	}
	$wcarats .= "</div>\n";
	$wcarats .= "</td></tr>\n";
} else {
	$wcarats = "";
}
if (is_array($optionals)) {
	$woptionals = "<tr><td class=\"vri-config-param-cell\" width=\"200\"> <b>".JText::_('VRNEWITEMFOUR')."</b> </td><td>";
	$woptionals .= "<div class=\"vri-itementries-cont\">";
	$nn = 0;
	foreach ($optionals as $kopt => $optional) {
		$woptionals .= "<div class=\"vri-itementry-cont\"><input type=\"checkbox\" name=\"coptional[]\" id=\"opt".$kopt."\" value=\"".$optional['id']."\"".(array_key_exists($optional['id'], $arropts) ? " checked=\"checked\"" : "")."/> <label for=\"opt".$kopt."\">".$optional['name']." ".$currencysymb."".$optional['cost']."</label></div>\n";
		$nn++;
		if (($nn % 3) == 0) {
			$woptionals .= "</div>\n<div class=\"vri-itementries-cont\">";
		}
	}
	$woptionals .= "</div>\n";
	$woptionals .= "</td></tr>\n";
} else {
	$woptionals = "";
}
//more images
$morei = count($row) ? explode(';;', $row['moreimgs']) : array();
$actmoreimgs = "";
if (@count($morei) > 0) {
	$notemptymoreim = false;
	foreach ($morei as $ki => $mi) {
		if (!empty($mi)) {
			$notemptymoreim = true;
			$actmoreimgs .= '<div style="float: left; margin-right: 5px;">';
			$actmoreimgs .= '<img src="./components/com_vikrentitems/resources/thumb_'.$mi.'" class="maxfifty"/>';
			$actmoreimgs .= '<a style="margin-left: -20px;width: 30px;z-index: 100;" href="index.php?option=com_vikrentitems&task=removemoreimgs&elemid='.$row['id'].'&imgind='.$ki.'"><img src="./components/com_vikrentitems/resources/images/remove.png" style="border: 0;"/></a>';
			$actmoreimgs .= '</div>';
		}
	}
	if ($notemptymoreim) {
		$actmoreimgs .= '<br clear="all"/>';
	}
}
//end more images
$item_jsparams = count($row) && !empty($row['jsparams']) ? json_decode($row['jsparams'], true) : array('custptitle' => '', 'custptitlew' => '', 'metakeywords' => '', 'metadescription' => '');
$editor = JEditor::getInstance(JFactory::getApplication()->get('editor'));
?>
<script type="text/javascript">
function showResizeSel() {
	if (document.adminForm.autoresize.checked == true) {
		document.getElementById('resizesel').style.display='block';
	} else {
		document.getElementById('resizesel').style.display='none';
	}
	return true;
}
function vriSelDropLocation() {
	var picksel = document.getElementById('cplace');
	var dropsel = document.getElementById('cretplace');
	for(i = 0; i < picksel.length; i++) {
		if (picksel.options[i].selected == false) {
			if (dropsel.options[i].selected == true) {
				dropsel.options[i].selected = false;
			}
		} else {
			if (dropsel.options[i].selected == false) {
				dropsel.options[i].selected = true;
			}
		}
	}
}
function showResizeSelMore() {
	if (document.adminForm.autoresizemore.checked == true) {
		document.getElementById('resizeselmore').style.display='block';
	} else {
		document.getElementById('resizeselmore').style.display='none';
	}
	return true;
}
function addMoreImages() {
	var ni = document.getElementById('myDiv');
	var numi = document.getElementById('moreimagescounter');
	var num = (document.getElementById('moreimagescounter').value -1)+ 2;
	numi.value = num;
	var newdiv = document.createElement('div');
	var divIdName = 'my'+num+'Div';
	newdiv.setAttribute('id',divIdName);
	newdiv.innerHTML = '<input type=\'file\' name=\'cimgmore[]\' size=\'35\'/><br/>';
	ni.appendChild(newdiv);
}
function toggleDeliveryCost() {
	if (document.adminForm.delivery.checked == true) {
		document.getElementById('overdeliverycost').style.display='block';
	} else {
		document.getElementById('overdeliverycost').style.display='none';
	}
	return true;
}
function toggleMinQuantity(status) {
	if (status) {
		document.getElementById("minitemquant").style.display='table-row';
	} else {
		document.getElementById("minitemquant").style.display='none';
	}
}
function toggleGroupItems(active) {
	document.getElementById('itemgroupcont').style.display = (active ? 'block' : 'none');
}
function updateGroupedItems() {
	var grouped = document.getElementById('childids');
	var container = document.getElementById('itemgroup-right');
	for (var i = 0; i < grouped.length; i++) {
		var rel_elem = document.getElementById('itemgroup-rel'+grouped.options[i].value);
		if (grouped.options[i].selected) {
			if (!rel_elem) {
				var itid = grouped.options[i].value;
				var itname = grouped.options[i].dataset.itName;
				var itunits = grouped.options[i].dataset.itUnits;
				//create element to append, because ".innerHTML += '...'" would reset the input values
				var newel = document.createElement("div");
				newel.id = "itemgroup-rel"+itid;
				//
				var spname = document.createElement("span");
				spname.className = "itemgroup-rel-name";
				spname.appendChild(document.createTextNode(itname));
				newel.appendChild(spname);
				//
				var spunits = document.createElement("span");
				spunits.className = "itemgroup-rel-units";
				var spunits_child = document.createElement("span");
				spunits_child.appendChild(document.createTextNode("<?php echo addslashes(JText::_('VRNEWITEMISGROUPUNITS')); ?>"));
				var units_inp = document.createElement("input");
				units_inp.type = "number";
				units_inp.name = "groupunits["+itid+"]";
				units_inp.value = "1";
				units_inp.min = "1";
				units_inp.max = itunits;
				spunits.appendChild(spunits_child);
				spunits.appendChild(document.createTextNode(" "));
				spunits.appendChild(units_inp);
				//
				newel.appendChild(spunits);
				//
				container.appendChild(newel);
				//
			}
		} else {
			if (rel_elem) {
				rel_elem.parentElement.removeChild(rel_elem);
			}
		}
	}
}
</script>
<input type="hidden" value="0" id="moreimagescounter" />

<form name="adminForm" id="adminForm" action="index.php" method="post" enctype="multipart/form-data">
	<table class="admintable table">
		<tr>
			<td class="vri-config-param-cell" width="200"> <b><?php echo JText::_('VRNEWITEMFIVE'); ?></b> </td>
			<td><input type="text" name="cname" value="<?php echo count($row) ? htmlspecialchars($row['name']) : ''; ?>" size="40"/></td>
		</tr>
		<tr>
			<td class="vri-config-param-cell" width="200"> <b><?php echo JText::_('VRNEWITEMNINE'); ?></b> </td>
			<td><input type="number" name="units" value="<?php echo count($row) ? $row['units'] : '1'; ?>" min="1" style="width: 50px !important;"/></td>
		</tr>
		<tr>
			<td class="vri-config-param-cell" width="200"> <b><?php echo JText::_('VRNEWITEMEIGHT'); ?></b> </td>
			<td><?php echo $vri_app->printYesNoButtons('cavail', JText::_('VRYES'), JText::_('VRNO'), ((count($row) && intval($row['avail']) == 1) || !count($row) ? 'yes' : 0), 'yes', 0); ?></td>
		</tr>
		<tr>
			<td class="vri-config-param-cell" width="200" style="vertical-align: top !important;"> <b><?php echo JText::_('VRNEWITEMSIX'); ?></b> </td>
			<td><?php echo (count($row) && !empty($row['img']) && file_exists(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_vikrentitems'.DS.'resources'.DS.$row['img']) ? "<img src=\"./components/com_vikrentitems/resources/".$row['img']."\" class=\"maxfifty\"/> &nbsp;" : ""); ?><input type="file" name="cimg" size="35"/><br/><label style="display: inline;" for="autoresize"><?php echo JText::_('VRNEWOPTNINE'); ?></label> <input type="checkbox" id="autoresize" name="autoresize" value="1" onclick="showResizeSel();"/> <span id="resizesel" style="display: none;">&nbsp;<?php echo JText::_('VRNEWOPTTEN'); ?>: <input type="text" name="resizeto" value="500" size="3"/> px</span></td>
		</tr>
		<tr>
			<td class="vri-config-param-cell" width="200" style="vertical-align: top !important;"> <b><?php echo JText::_('VRMOREIMAGES'); ?></b><br/>&nbsp;&nbsp;<a href="javascript: void(0);" onclick="addMoreImages();"><?php echo JText::_('VRADDIMAGES'); ?></a></td>
			<td><?php echo $actmoreimgs; ?><input type="file" name="cimgmore[]" size="35"/><div id="myDiv" style="display: block;"></div><label style="display: inline;" for="autoresizemore"><?php echo JText::_('VRRESIZEIMAGES'); ?></label> <input type="checkbox" id="autoresizemore" name="autoresizemore" value="1" onclick="showResizeSelMore();"/> <span id="resizeselmore" style="display: none;">&nbsp;<?php echo JText::_('VRNEWOPTTEN'); ?>: <input type="text" name="resizetomore" value="600" size="3"/> px</span></td>
		</tr>
		<?php echo $wcats; ?>
		<tr>
			<td class="vri-config-param-cell" width="200"> <b><?php echo JText::_('VRITEMSHORTDESCR'); ?></b> </td>
			<td><textarea name="shortdesc" rows="5" cols="40"><?php echo count($row) ? $row['shortdesc'] : ''; ?></textarea></td>
		</tr>
		<tr>
			<td class="vri-config-param-cell" width="200"> <b><?php echo JText::_('VRNEWITEMSEVEN'); ?></b> </td>
			<td><?php echo $editor->display( "cdescr", (count($row) ? $row['info'] : ''), 400, 200, 70, 20 ); ?></td>
		</tr>
		<?php echo $wplaces; ?>
		<?php echo $wretplaces; ?>
		<?php echo $wcarats; ?>
		<?php echo $woptionals; ?>
		<?php
		if (count($all_items) > 0) {
			?>
		<tr>
			<td class="vri-config-param-cell" width="200" style="vertical-align: top;"> <b><?php echo JText::_('VRNEWITEMISGROUP'); ?></b> </td>
			<td>
				<input type="checkbox" name="isgroup" value="1"<?php echo count($row) && $row['isgroup'] > 0 ? " checked=\"checked\"" : ""; ?> onchange="toggleGroupItems(this.checked);"/>
				<div id="itemgroupcont" class="itemgroupcont" style="display: <?php echo count($row) && $row['isgroup'] > 0 ? "block" : "none"; ?>;">
					<p><?php echo JText::_('VRNEWITEMISGROUPSEL'); ?></p>
					<div class="itemgroup-left">
						<select name="childid[]" id="childids" multiple="multiple" size="<?php echo count($all_items) > 8 ? 8 : count($all_items); ?>" onchange="updateGroupedItems();">
						<?php
						foreach ($all_items as $it) {
							$childgrouped = false;
							foreach ($grouped_items as $git) {
								if ($git['childid'] == $it['id']) {
									$childgrouped = true;
									break;
								}
							}
							?>
							<option value="<?php echo $it['id']; ?>" data-it-name="<?php echo $it['name']; ?>" data-it-units="<?php echo $it['units']; ?>"<?php echo $childgrouped ? ' selected="selected"' : ''; ?>><?php echo $it['name']; ?></option>
							<?php
						}
						?>
						</select>
					</div>
					<div class="itemgroup-right" id="itemgroup-right">
					<?php
					foreach ($grouped_items as $git) {
						?>
						<div id="itemgroup-rel<?php echo $git['childid']; ?>">
							<span class="itemgroup-rel-name"><?php echo $git['name']; ?></span>
							<span class="itemgroup-rel-units"><span><?php echo JText::_('VRNEWITEMISGROUPUNITS'); ?></span> <input type="number" name="groupunits[<?php echo $git['childid']; ?>]" value="<?php echo $git['units']; ?>" min="1" max="<?php echo $git['maxunits']; ?>" /></span>
						</div>
						<?php
					}
					?>
					</div>
				</div>
			</td>
		</tr>
			<?php
		} else {
			echo '<input type="hidden" name="isgroup" value="0" />';
		}
		?>
		<tr>
			<td class="vri-config-param-cell" width="200" style="vertical-align: top !important;"> <b><?php echo JText::_('VRNEWITEMPARAMETERS'); ?></b> </td>
			<td>
				<table>
				<tr><td width="200"><?php echo JText::_('VRIUSTSTARTINGFROM'); ?>: </td><td><?php echo $currencysymb; ?> &nbsp; <input type="number" step="any" name="startfrom" value="<?php echo count($row) ? $row['startfrom'] : ''; ?>"/> &nbsp;&nbsp; <?php echo $vri_app->createPopover(array('title' => JText::_('VRIUSTSTARTINGFROM'), 'content' => JText::_('VRIUSTSTARTINGFROMHELP'))); ?></td></tr>
				<tr><td width="200"><?php echo JText::_('VRICUSTSTARTINGFROMTEXT'); ?>: </td><td><input type="text" name="startfromtext" value="<?php echo count($row) ? VikRentItems::getItemParam($row['params'], 'startfromtext') : 'VRI_PERHOUR'; ?>" size="20"/></td></tr>
				<tr><td width="200"><?php echo JText::_('VRIASKQUANTITY'); ?>: </td><td><input type="checkbox" name="askquantity" value="yes"<?php echo ((count($row) && intval($row['askquantity']) == 1) || !count($row) ? " checked=\"checked\"" : ""); ?> onclick="toggleMinQuantity(this.checked);"/></td></tr>
				<tr><td width="200"><?php echo JText::_('VRIPARAMITEMSHOWDISCQUANTAB'); ?>: </td><td><input type="checkbox" name="discsquantstab" value="yes"<?php echo ((count($row) && intval(VikRentItems::getItemParam($row['params'], 'discsquantstab')) == 1) || !count($row) ? " checked=\"checked\"" : ""); ?>/></td></tr>
				<tr><td width="200"><?php echo JText::_('VRIHOURLYCALENDAR'); ?>: </td><td><input type="checkbox" name="hourlycalendar" value="yes"<?php echo ((count($row) && intval(VikRentItems::getItemParam($row['params'], 'hourlycalendar')) == 1) || !count($row) ? " checked=\"checked\"" : ""); ?>/></td></tr>
				<tr><td width="200"><?php echo JText::_('VRIUSETIMESLOTS'); ?>: </td><td><input type="checkbox" name="timeslots" value="yes"<?php echo ((count($row) && intval(VikRentItems::getItemParam($row['params'], 'timeslots')) == 1) || !count($row) ? " checked=\"checked\"" : ""); ?>/> &nbsp;&nbsp; <?php echo $vri_app->createPopover(array('title' => JText::_('VRIUSETIMESLOTS'), 'content' => JText::_('VRIUSETIMESLOTSHELP'))); ?></td></tr>
				<tr><td width="200"><?php echo JText::_('VRIUSEDELIVERY'); ?>: </td><td><input type="checkbox" name="delivery" value="yes"<?php echo (count($row) && intval(VikRentItems::getItemParam($row['params'], 'delivery')) == 1 ? " checked=\"checked\"" : ""); ?> onclick="toggleDeliveryCost();"/> <div id="overdeliverycost" style="display: <?php echo (count($row) && intval(VikRentItems::getItemParam($row['params'], 'delivery')) == 1 ? "block" : "none"); ?>;"><?php echo JText::_('VRIOVERDELIVERY'); ?> <input type="number" step="any" name="overdelcost" value="<?php echo count($row) ? VikRentItems::getItemParam($row['params'], 'overdelcost') : '0.00'; ?>"/> <?php echo $currencysymb; ?></div></td></tr>
				<tr id="minitemquant" style="display: <?php echo (count($row) && intval($row['askquantity']) == 1 ? "table-row" : "none"); ?>;"><td width="200"><?php echo JText::_('VRIMINITEMQUANTITY'); ?>: </td><td><input type="number" name="minquant" value="<?php echo ((count($row) && intval($item_jsparams['minquant']) < 1) || !count($row) ? '1' : (int)$item_jsparams['minquant']); ?>" min="1" style="width: 50px !important;"/></td></tr>
				<tr><td width="200"><?php echo JText::_('VRIAUTOSETDROPDAY'); ?>: </td><td><input type="number" name="dropdaysplus" min="0" value="<?php echo count($row) ? VikRentItems::getItemParam($row['params'], 'dropdaysplus') : '0'; ?>" style="width: 50px !important;"/> <?php echo JText::_('VRIDAYSAFTERPICKUP'); ?></td></tr>
				<tr><td width="200"><?php echo JText::_('VRIEXTRAEMAILITEM'); ?>: </td><td><input type="text" name="extraemail" size="30" value="<?php echo count($row) ? VikRentItems::getItemParam($row['params'], 'extraemail') : ''; ?>" /></td></tr>
				</table>
			</td>
		</tr>
		<tr>
			<td class="vri-config-param-cell" width="200" style="vertical-align: top !important;"> <b><?php echo JText::_('VRNEWITEMSEFPARAMETERS'); ?></b> </td>
			<td>
				<p class="vriitemparamp">
					<label for="custptitle"><?php echo JText::_('VRIPARAMPAGETITLE'); ?></label>
					<input type="text" id="custptitle" name="custptitle" value="<?php echo $item_jsparams['custptitle']; ?>"/>
					<span>
						<select name="custptitlew">
							<option value="before"<?php echo $item_jsparams['custptitlew'] == 'before' ? ' selected="selected"' : ''; ?>><?php echo JText::_('VRIPARAMPAGETITLEBEFORECUR'); ?></option>
							<option value="after"<?php echo $item_jsparams['custptitlew'] == 'after' ? ' selected="selected"' : ''; ?>><?php echo JText::_('VRIPARAMPAGETITLEAFTERCUR'); ?></option>
							<option value="replace"<?php echo $item_jsparams['custptitlew'] == 'replace' ? ' selected="selected"' : ''; ?>><?php echo JText::_('VRIPARAMPAGETITLEREPLACECUR'); ?></option>
						</select>
					</span>
				</p>
				<p class="vriitemparamp">
					<label for="metakeywords"><?php echo JText::_('VRIPARAMKEYWORDSMETATAG'); ?></label> 
					<textarea name="metakeywords" id="metakeywords" rows="3" cols="40"><?php echo $item_jsparams['metakeywords']; ?></textarea>
				</p>
				<p class="vriitemparamp">
					<label for="metadescription"><?php echo JText::_('VRIPARAMDESCRIPTIONMETATAG'); ?></label> 
					<textarea name="metadescription" id="metadescription" rows="4" cols="40"><?php echo $item_jsparams['metadescription']; ?></textarea>
				</p>
				<p class="vriitemparamp">
					<label for="sefalias"><?php echo JText::_('VRIARSEFALIAS'); ?></label> 
					<input type="text" id="sefalias" name="sefalias" value="<?php echo count($row) ? $row['alias'] : ''; ?>" placeholder="item-name"/>
				</p>
			</td>
		</tr>
	</table>
	<input type="hidden" name="task" value="">
<?php
if (count($row)) {
	?>
	<input type="hidden" name="whereup" value="<?php echo $row['id']; ?>">
	<input type="hidden" name="actmoreimgs" value="<?php echo $row['moreimgs']; ?>">
	<?php
}
?>
	<input type="hidden" name="option" value="com_vikrentitems">
</form>
