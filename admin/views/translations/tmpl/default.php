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

$vri_tn = $this->vri_tn;

$editor = JEditor::getInstance(JFactory::getApplication()->get('editor'));
$langs = $vri_tn->getLanguagesList();
$xml_tables = $vri_tn->getTranslationTables();
$active_table = '';
$active_table_key = '';
if (!(count($langs) > 1)) {
	//Error: only one language is published. Translations are useless
	?>
	<p class="err"><?php echo JText::_('VRITRANSLATIONERRONELANG'); ?></p>
	<form name="adminForm" id="adminForm" action="index.php" method="post">
		<input type="hidden" name="task" value="">
		<input type="hidden" name="option" value="com_vikrentitems">
	</form>
	<?php
} elseif (!(count($xml_tables) > 0) || strlen($vri_tn->getError())) {
	//Error: XML file not readable or errors occurred
	?>
	<p class="err"><?php echo $vri_tn->getError(); ?></p>
	<form name="adminForm" id="adminForm" action="index.php" method="post">
		<input type="hidden" name="task" value="">
		<input type="hidden" name="option" value="com_vikrentitems">
	</form>
	<?php
} else {
	$cur_langtab = VikRequest::getString('vri_lang', '', 'request');
	$table = VikRequest::getString('vri_table', '', 'request');
	if (!empty($table)) {
		$table = $vri_tn->replacePrefix($table);
	}
?>
<script type="text/Javascript">
var vri_tn_changes = false;
jQuery(document).ready(function() {
	jQuery('#adminForm input[type=text], #adminForm textarea').change(function() {
		vri_tn_changes = true;
	});
});
function vriCheckChanges() {
	if (!vri_tn_changes) {
		return true;
	}
	return confirm("<?php echo addslashes(JText::_('VRITANSLATIONSCHANGESCONF')); ?>");
}
</script>
<form action="index.php?option=com_vikrentitems&amp;task=translations" method="post" onsubmit="return vriCheckChanges();">
	<div style="width: 100%; display: inline-block;" class="btn-toolbar" id="filter-bar">
		<div class="btn-group pull-right">
			<button class="btn" type="submit"><?php echo JText::_('VRIGETTRANSLATIONS'); ?></button>
		</div>
		<div class="btn-group pull-right">
			<select name="vri_table">
				<option value="">-----------</option>
			<?php
			foreach ($xml_tables as $key => $value) {
				$active_table = $vri_tn->replacePrefix($key) == $table ? $value : $active_table;
				$active_table_key = $vri_tn->replacePrefix($key) == $table ? $key : $active_table_key;
				?>
				<option value="<?php echo $key; ?>"<?php echo $vri_tn->replacePrefix($key) == $table ? ' selected="selected"' : ''; ?>><?php echo $value; ?></option>
				<?php
			}
			?>
			</select>
		</div>
	</div>
	<input type="hidden" name="vri_lang" class="vri_lang" value="<?php echo $vri_tn->default_lang; ?>">
	<input type="hidden" name="option" value="com_vikrentitems" />
	<input type="hidden" name="task" value="translations" />
</form>
<form name="adminForm" id="adminForm" action="index.php" method="post">
	<div class="vri-translation-langtabs">
<?php
foreach ($langs as $ltag => $lang) {
	$is_def = ($ltag == $vri_tn->default_lang);
	$lcountry = substr($ltag, 0, 2);
	$flag = file_exists(JPATH_SITE.DS.'media'.DS.'mod_languages'.DS.'images'.DS.$lcountry.'.gif') ? '<img src="'.JURI::root().'media/mod_languages/images/'.$lcountry.'.gif"/>' : '';
		?>
		<div class="vri-translation-tab<?php echo $is_def ? ' vri-translation-tab-default' : ''; ?>" data-vrilang="<?php echo $ltag; ?>">
		<?php
		if (!empty($flag)) {
			?>
			<span class="vri-translation-flag"><?php echo $flag; ?></span>
			<?php
		}
		?>
			<span class="vri-translation-langname"><?php echo $lang['name']; ?></span>
		</div>
	<?php
}
?>
		<div class="vri-translation-tab vri-translation-tab-ini" data-vrilang="">
			<span class="vri-translation-iniflag">.INI</span>
			<span class="vri-translation-langname"><?php echo JText::_('VRITRANSLATIONINISTATUS'); ?></span>
		</div>
	</div>
	<div class="vri-translation-tabscontents">
<?php
$table_cols = !empty($active_table_key) ? $vri_tn->getTableColumns($active_table_key) : array();
$table_def_dbvals = !empty($active_table_key) ? $vri_tn->getTableDefaultDbValues($active_table_key, array_keys($table_cols)) : array();
if (!empty($active_table_key)) {
	echo '<input type="hidden" name="vri_table" value="'.$active_table_key.'"/>'."\n";
}
foreach ($langs as $ltag => $lang) {
	$is_def = ($ltag == $vri_tn->default_lang);
	?>
		<div class="vri-translation-langcontent" style="display: <?php echo $is_def ? 'block' : 'none'; ?>;" id="vri_langcontent_<?php echo $ltag; ?>">
	<?php
	if (empty($active_table_key)) {
		?>
			<p class="warn"><?php echo JText::_('VRITRANSLATIONSELTABLEMESS'); ?></p>
		<?php
	} elseif (strlen($vri_tn->getError()) > 0) {
		?>
			<p class="err"><?php echo $vri_tn->getError(); ?></p>
		<?php
	} else {
		?>
			<fieldset class="adminform">
				<legend class="adminlegend"><?php echo $active_table; ?> - <?php echo $lang['name'].($is_def ? ' - '.JText::_('VRITRANSLATIONDEFLANG') : ''); ?></legend>
				<table cellspacing="1" class="admintable table">
					<tbody>
		<?php
		if ($is_def) {
			//Values of Default Language to be translated
			foreach ($table_def_dbvals as $reference_id => $values) {
				?>
						<tr data-reference="<?php echo $ltag.'-'.$reference_id; ?>">
							<td class="vri-translate-reference-cell" colspan="2"><?php echo $vri_tn->getRecordReferenceName($table_cols, $values); ?></td>
						</tr>
				<?php
				foreach ($values as $field => $def_value) {
					$title = $table_cols[$field]['jlang'];
					$type = $table_cols[$field]['type'];
					if ($type == 'html') {
						$def_value = strip_tags($def_value);
					}
					?>
						<tr data-reference="<?php echo $ltag.'-'.$reference_id; ?>">
							<td width="200" class="vri-translate-column-cell"> <b><?php echo $title; ?></b> </td>
							<td><?php echo $type != 'json' ? $def_value : ''; ?></td>
						</tr>
					<?php
					if ($type == 'json') {
						$tn_keys = $table_cols[$field]['keys'];
						$keys = !empty($tn_keys) ? explode(',', $tn_keys) : array();
						$json_def_values = json_decode($def_value, true);
						if (count($json_def_values) > 0) {
							foreach ($json_def_values as $jkey => $jval) {
								if ((!in_array($jkey, $keys) && count($keys) > 0) || empty($jval)) {
									continue;
								}
								?>
						<tr data-reference="<?php echo $ltag.'-'.$reference_id; ?>">
							<td width="200" class="vri-translate-column-cell"><?php echo !is_numeric($jkey) ? ucwords($jkey) : '&nbsp;'; ?></td>
							<td><?php echo $jval; ?></td>
						</tr>
								<?php
							}
						}
					}
					?>
					<?php
				}
			}
		} else {
			//Translation Fields for this language
			$lang_record_tn = $vri_tn->getTranslatedTable($active_table_key, $ltag);
			foreach ($table_def_dbvals as $reference_id => $values) {
				?>
						<tr data-reference="<?php echo $ltag.'-'.$reference_id; ?>">
							<td class="vri-translate-reference-cell" colspan="2"><?php echo $vri_tn->getRecordReferenceName($table_cols, $values); ?></td>
						</tr>
				<?php
				foreach ($values as $field => $def_value) {
					$title = $table_cols[$field]['jlang'];
					$type = $table_cols[$field]['type'];
					if ($type == 'skip') {
						continue;
					}
					$tn_value = '';
					$tn_class = ' vri-missing-translation';
					if (array_key_exists($reference_id, $lang_record_tn) && array_key_exists($field, $lang_record_tn[$reference_id]['content']) && strlen($lang_record_tn[$reference_id]['content'][$field])) {
						if (in_array($type, array('text', 'textarea', 'html'))) {
							$tn_class = ' vri-field-translated';
						} else {
							$tn_class = '';
						}
					}
					?>
						<tr data-reference="<?php echo $ltag.'-'.$reference_id; ?>">
							<td width="200" class="vri-translate-column-cell<?php echo $tn_class; ?>"<?php echo in_array($type, array('textarea', 'html')) ? ' style="vertical-align: top !important;"' : ''; ?>> <b><?php echo $title; ?></b> </td>
							<td>
					<?php
					if ($type == 'text') {
						if (array_key_exists($reference_id, $lang_record_tn) && array_key_exists($field, $lang_record_tn[$reference_id]['content'])) {
							$tn_value = $lang_record_tn[$reference_id]['content'][$field];
						}
						?>
								<input type="text" name="tn[<?php echo $ltag; ?>][<?php echo $reference_id; ?>][<?php echo $field; ?>]" value="<?php echo $tn_value; ?>" size="40" placeholder="<?php echo $def_value; ?>"/>
						<?php
					} elseif ($type == 'textarea') {
						if (array_key_exists($reference_id, $lang_record_tn) && array_key_exists($field, $lang_record_tn[$reference_id]['content'])) {
							$tn_value = $lang_record_tn[$reference_id]['content'][$field];
						}
						?>
								<textarea name="tn[<?php echo $ltag; ?>][<?php echo $reference_id; ?>][<?php echo $field; ?>]" rows="5" cols="40"><?php echo $tn_value; ?></textarea>
						<?php
					} elseif ($type == 'html') {
						if (array_key_exists($reference_id, $lang_record_tn) && array_key_exists($field, $lang_record_tn[$reference_id]['content'])) {
							$tn_value = $lang_record_tn[$reference_id]['content'][$field];
						}
						echo $editor->display( "tn[".$ltag."][".$reference_id."][".$field."]", $tn_value, 500, 350, 70, 20, true, "tn_".$ltag."_".$reference_id."_".$field );
					}
					?>
							</td>
						</tr>
					<?php
					if ($type == 'json') {
						$tn_keys = $table_cols[$field]['keys'];
						$keys = !empty($tn_keys) ? explode(',', $tn_keys) : array();
						$json_def_values = json_decode($def_value, true);
						if (count($json_def_values) > 0) {
							$tn_json_value = array();
							if (array_key_exists($reference_id, $lang_record_tn) && array_key_exists($field, $lang_record_tn[$reference_id]['content'])) {
								$tn_json_value = json_decode($lang_record_tn[$reference_id]['content'][$field], true);
							}
							foreach ($json_def_values as $jkey => $jval) {
								if ((!in_array($jkey, $keys) && count($keys) > 0) || empty($jval)) {
									continue;
								}
								?>
						<tr data-reference="<?php echo $ltag.'-'.$reference_id; ?>">
							<td width="200" class="vri-translate-column-cell"><?php echo !is_numeric($jkey) ? ucwords($jkey) : '&nbsp;'; ?></td>
							<td><input type="text" name="tn[<?php echo $ltag; ?>][<?php echo $reference_id; ?>][<?php echo $field; ?>][<?php echo $jkey; ?>]" value="<?php echo $tn_json_value[$jkey]; ?>" size="40"/></td>
						</tr>
								<?php
							}
						}
					}
				}
			}
		}
		?>
					</tbody>
				</table>
			</fieldset>
		<?php
		//echo '<pre>'.print_r($table_def_dbvals, true).'</pre><br/>';
		//echo '<pre>'.print_r($table_cols, true).'</pre><br/>';
	}
	?>
		</div>
	<?php
}
//ini files status
$all_inis = $vri_tn->getIniFiles();
?>
		<div class="vri-translation-langcontent" style="display: none;" id="vri_langcontent_ini">
			<fieldset class="adminform">
				<legend class="adminlegend">.INI <?php echo JText::_('VRITRANSLATIONINISTATUS'); ?></legend>
				<table cellspacing="1" class="admintable table">
					<tbody>
					<?php
					foreach ($all_inis as $initype => $inidet) {
						$inipath = $inidet['path'];
						?>
						<tr>
							<td class="vri-translate-reference-cell" colspan="2"><?php echo JText::_('VRIINIEXPL'.strtoupper($initype)); ?></td>
						</tr>
						<?php
						foreach ($langs as $ltag => $lang) {
							$t_file_exists = file_exists(str_replace('en-GB', $ltag, $inipath));
							$t_parsed_ini = $t_file_exists ? parse_ini_file(str_replace('en-GB', $ltag, $inipath)) : false;
							?>
						<tr>
							<td width="200" class="vri-translate-column-cell <?php echo $t_file_exists ? 'vri-field-translated' : 'vri-missing-translation'; ?>"> <b><?php echo ($ltag == 'en-GB' ? 'Native ' : '').$lang['name']; ?></b> </td>
							<td>
								<span class="vri-inifile-totrows <?php echo $t_file_exists ? 'vri-inifile-exists' : 'vri-inifile-notfound'; ?>"><?php echo $t_file_exists && $t_parsed_ini !== false ? JText::_('VRIINIDEFINITIONS').': '.count($t_parsed_ini) : JText::_('VRIINIMISSINGFILE'); ?></span>
								<span class="vri-inifile-path <?php echo $t_file_exists ? 'vri-inifile-exists' : 'vri-inifile-notfound'; ?>"><?php echo JText::_('VRIINIPATH').': '.str_replace('en-GB', $ltag, $inipath); ?></span>
							</td>
						</tr>
							<?php
						}
					}
					?>
					</tbody>
				</table>
			</fieldset>
		</div>
	<?php
	//end ini files status
	?>
	</div>
	<input type="hidden" name="vri_lang" class="vri_lang" value="<?php echo $vri_tn->default_lang; ?>">
	<input type="hidden" name="task" value="translations">
	<input type="hidden" name="option" value="com_vikrentitems" />
	<br/>
	<table align="center">
		<tr>
			<td align="center"><?php echo $vri_tn->getPagination(); ?></td>
		</tr>
		<tr>
			<td align="center">
				<select name="limit" onchange="document.adminForm.limitstart.value = '0'; document.adminForm.submit();">
					<option value="2"<?php echo $vri_tn->lim == 2 ? ' selected="selected"' : ''; ?>>2</option>
					<option value="5"<?php echo $vri_tn->lim == 5 ? ' selected="selected"' : ''; ?>>5</option>
					<option value="10"<?php echo $vri_tn->lim == 10 ? ' selected="selected"' : ''; ?>>10</option>
					<option value="20"<?php echo $vri_tn->lim == 20 ? ' selected="selected"' : ''; ?>>20</option>
				</select>
			</td>
		</tr>
	</table>
</form>
<script type="text/Javascript">
jQuery(document).ready(function() {
	jQuery('.vri-translation-tab').click(function() {
		var langtag = jQuery(this).attr('data-vrilang');
		if (jQuery('#vri_langcontent_'+langtag).length) {
			jQuery('.vri_lang').val(langtag);
			jQuery('.vri-translation-tab').removeClass('vri-translation-tab-default');
			jQuery(this).addClass('vri-translation-tab-default');
			jQuery('.vri-translation-langcontent').hide();
			jQuery('#vri_langcontent_'+langtag).fadeIn();
		} else {
			jQuery('.vri-translation-tab').removeClass('vri-translation-tab-default');
			jQuery(this).addClass('vri-translation-tab-default');
			jQuery('.vri-translation-langcontent').hide();
			jQuery('#vri_langcontent_ini').fadeIn();
		}
	});
<?php
if (!empty($cur_langtab)) {
	?>
	jQuery('.vri-translation-tab').each(function() {
		var langtag = jQuery(this).attr('data-vrilang');
		if (langtag != '<?php echo $cur_langtab; ?>') {
			return true;
		}
		if (jQuery('#vri_langcontent_'+langtag).length) {
			jQuery('.vri_lang').val(langtag);
			jQuery('.vri-translation-tab').removeClass('vri-translation-tab-default');
			jQuery(this).addClass('vri-translation-tab-default');
			jQuery('.vri-translation-langcontent').hide();
			jQuery('#vri_langcontent_'+langtag).fadeIn();
		}
	});
	<?php
}
?>
});
</script>
<?php
}
