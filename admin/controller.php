<?php
/**
 * @package     VikRentItems
 * @subpackage  com_vikrentitems
 * @author      Alessio Gaggii - e4j - Extensionsforjoomla.com
 * @copyright   Copyright (C) 2018 e4j - Extensionsforjoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 * @link        https://e4j.com
 */

require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Helper\Sample;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

defined('_JEXEC') or die('Restricted access');

// import Joomla controller library
jimport('joomla.application.component.controller');

class VikRentItemsController extends JControllerVikRentItems {

	/**
	 * Default controller's method when no task is defined,
	 * or no method exists for that task. If a View is requested.
	 * attempts to set it, otherwise sets the default View.
	 */
	function display($cachable = false, $urlparams = array()) {

		$view = VikRequest::getVar('view', '');
		$header_val = '';

		if (!empty($view)) {
			VikRequest::setVar('view', $view);
		} else {
			$header_val = '18';
			VikRequest::setVar('view', 'dashboard');
		}

		VikRentItemsHelper::printHeader($header_val);
		
		parent::display();

		if (VikRentItems::showFooter()) {
			VikRentItemsHelper::printFooter();
		}
	}

	function places() {
		VikRentItemsHelper::printHeader("3");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'places'));
	
		parent::display();

		if (VikRentItems::showFooter()) {
			VikRentItemsHelper::printFooter();
		}
	}

	function newplace() {
		VikRentItemsHelper::printHeader("3");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'manageplace'));
	
		parent::display();

		if (VikRentItems::showFooter()) {
			VikRentItemsHelper::printFooter();
		}
	}

	function editplace() {
		VikRentItemsHelper::printHeader("3");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'manageplace'));
	
		parent::display();

		if (VikRentItems::showFooter()) {
			VikRentItemsHelper::printFooter();
		}
	}

	function createplace() {
		$pname = VikRequest::getString('placename', '', 'request');
		$plat = VikRequest::getString('lat', '', 'request');
		$plng = VikRequest::getString('lng', '', 'request');
		$ppraliq = VikRequest::getString('praliq', '', 'request');
		$pdescr = VikRequest::getString('descr', '', 'request', VIKREQUEST_ALLOWHTML);
		$popentimefh = VikRequest::getString('opentimefh', '', 'request');
		$popentimefm = VikRequest::getString('opentimefm', '', 'request');
		$popentimeth = VikRequest::getString('opentimeth', '', 'request');
		$popentimetm = VikRequest::getString('opentimetm', '', 'request');
		$pclosingdays = VikRequest::getString('closingdays', '', 'request');
		$opentime = "";
		if (strlen($popentimefh) > 0 && strlen($popentimefm) > 0 && strlen($popentimeth) > 0 && strlen($popentimetm) > 0) {
			$openingh=$popentimefh * 3600;
			$openingm=$popentimefm * 60;
			$openingts=$openingh + $openingm;
			$closingh=$popentimeth * 3600;
			$closingm=$popentimetm * 60;
			$closingts=$closingh + $closingm;
			if ($closingts > $openingts) {
				$opentime = $openingts."-".$closingts;
			}
		}
		if (!empty($pname)) {
			$dbo = JFactory::getDBO();
			$q = "INSERT INTO `#__vikrentitems_places` (`name`,`lat`,`lng`,`descr`,`opentime`,`closingdays`,`idiva`) VALUES(".$dbo->quote($pname).", ".$dbo->quote($plat).", ".$dbo->quote($plng).", ".$dbo->quote($pdescr).", '".$opentime."', ".$dbo->quote($pclosingdays).", ".(!empty($ppraliq) ? "'".$ppraliq."'" : "null").");";
			$dbo->setQuery($q);
			$dbo->execute();
		}
		$mainframe = JFactory::getApplication();
		$mainframe->redirect('index.php?option=com_vikrentitems&task=places');
	}

	function updateplace() {
		$pname = VikRequest::getString('placename', '', 'request');
		$plat = VikRequest::getString('lat', '', 'request');
		$plng = VikRequest::getString('lng', '', 'request');
		$ppraliq = VikRequest::getString('praliq', '', 'request');
		$pdescr = VikRequest::getString('descr', '', 'request', VIKREQUEST_ALLOWHTML);
		$pwhereup = VikRequest::getString('whereup', '', 'request');
		$popentimefh = VikRequest::getString('opentimefh', '', 'request');
		$popentimefm = VikRequest::getString('opentimefm', '', 'request');
		$popentimeth = VikRequest::getString('opentimeth', '', 'request');
		$popentimetm = VikRequest::getString('opentimetm', '', 'request');
		$pclosingdays = VikRequest::getString('closingdays', '', 'request');
		$opentime = "";
		if (strlen($popentimefh) > 0 && strlen($popentimefm) > 0 && strlen($popentimeth) > 0 && strlen($popentimetm) > 0) {
			$openingh=$popentimefh * 3600;
			$openingm=$popentimefm * 60;
			$openingts=$openingh + $openingm;
			$closingh=$popentimeth * 3600;
			$closingm=$popentimetm * 60;
			$closingts=$closingh + $closingm;
			if ($closingts > $openingts) {
				$opentime = $openingts."-".$closingts;
			}
		}
		if (!empty($pname)) {
			$dbo = JFactory::getDBO();
			$q = "UPDATE `#__vikrentitems_places` SET `name`=".$dbo->quote($pname).",`lat`=".$dbo->quote($plat).",`lng`=".$dbo->quote($plng).",`descr`=".$dbo->quote($pdescr).",`opentime`='".$opentime."',`closingdays`=".$dbo->quote($pclosingdays).",`idiva`=".(!empty($ppraliq) ? "'".$ppraliq."'" : "null")." WHERE `id`=".$dbo->quote($pwhereup).";";
			$dbo->setQuery($q);
			$dbo->execute();
		}
		$mainframe = JFactory::getApplication();
		$mainframe->redirect('index.php?option=com_vikrentitems&task=places');
	}

	function removeplace() {
		$ids = VikRequest::getVar('cid', array(0));
		if (@count($ids)) {
			$dbo = JFactory::getDBO();
			foreach ($ids as $d) {
				$q = "DELETE FROM `#__vikrentitems_places` WHERE `id`=".$dbo->quote($d).";";
				$dbo->setQuery($q);
				$dbo->execute();
			}
		}
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikrentitems&task=places");
	}

	function cancelplace() {
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikrentitems&task=places");
	}

	function iva() {
		VikRentItemsHelper::printHeader("2");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'iva'));
	
		parent::display();

		if (VikRentItems::showFooter()) {
			VikRentItemsHelper::printFooter();
		}
	}

	function newiva() {
		VikRentItemsHelper::printHeader("2");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'manageiva'));
	
		parent::display();

		if (VikRentItems::showFooter()) {
			VikRentItemsHelper::printFooter();
		}
	}

	function editiva() {
		VikRentItemsHelper::printHeader("2");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'manageiva'));
	
		parent::display();

		if (VikRentItems::showFooter()) {
			VikRentItemsHelper::printFooter();
		}
	}

	function createiva() {
		$paliqname = VikRequest::getString('aliqname', '', 'request');
		$paliqperc = VikRequest::getString('aliqperc', '', 'request');
		if (!empty($paliqperc)) {
			$dbo = JFactory::getDBO();
			$q = "INSERT INTO `#__vikrentitems_iva` (`name`,`aliq`) VALUES(".$dbo->quote($paliqname).", ".floatval($paliqperc).");";
			$dbo->setQuery($q);
			$dbo->execute();
		}
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikrentitems&task=iva");
	}

	function updateiva() {
		$paliqname = VikRequest::getString('aliqname', '', 'request');
		$paliqperc = VikRequest::getString('aliqperc', '', 'request');
		$pwhereup = VikRequest::getString('whereup', '', 'request');
		if (!empty($paliqperc)) {
			$dbo = JFactory::getDBO();
			$q = "UPDATE `#__vikrentitems_iva` SET `name`=".$dbo->quote($paliqname).",`aliq`=".floatval($paliqperc)." WHERE `id`=".intval($pwhereup).";";
			$dbo->setQuery($q);
			$dbo->execute();
		}
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikrentitems&task=iva");
	}

	function removeiva() {
		$ids = VikRequest::getVar('cid', array(0));
		if (@count($ids)) {
			$dbo = JFactory::getDBO();
			foreach ($ids as $d) {
				$q = "DELETE FROM `#__vikrentitems_iva` WHERE `id`=".$dbo->quote($d).";";
				$dbo->setQuery($q);
				$dbo->execute();
			}
		}
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikrentitems&task=iva");
	}

	function canceliva() {
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikrentitems&task=iva");
	}

	function prices() {
		VikRentItemsHelper::printHeader("1");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'prices'));
	
		parent::display();

		if (VikRentItems::showFooter()) {
			VikRentItemsHelper::printFooter();
		}
	}

	function newprice() {
		VikRentItemsHelper::printHeader("1");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'manageprice'));
	
		parent::display();

		if (VikRentItems::showFooter()) {
			VikRentItemsHelper::printFooter();
		}
	}

	function editprice() {
		VikRentItemsHelper::printHeader("1");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'manageprice'));
	
		parent::display();

		if (VikRentItems::showFooter()) {
			VikRentItemsHelper::printFooter();
		}
	}

	function createprice() {
		$pprice = VikRequest::getString('price', '', 'request');
		$pattr = VikRequest::getString('attr', '', 'request');
		$ppraliq = VikRequest::getString('praliq', '', 'request');
		if (!empty($pprice)) {
			$dbo = JFactory::getDBO();
			$q = "INSERT INTO `#__vikrentitems_prices` (`name`,`attr`,`idiva`) VALUES(".$dbo->quote($pprice).", ".$dbo->quote($pattr).", ".(!empty($ppraliq) ? intval($ppraliq) : 'NULL').");";
			$dbo->setQuery($q);
			$dbo->execute();
		}
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikrentitems&task=prices");
	}

	function updateprice() {
		$pprice = VikRequest::getString('price', '', 'request');
		$pattr = VikRequest::getString('attr', '', 'request');
		$ppraliq = VikRequest::getString('praliq', '', 'request');
		$pwhereup = VikRequest::getString('whereup', '', 'request');
		if (!empty($pprice)) {
			$dbo = JFactory::getDBO();
			$q = "UPDATE `#__vikrentitems_prices` SET `name`=".$dbo->quote($pprice).",`attr`=".$dbo->quote($pattr).",`idiva`=".(!empty($ppraliq) ? intval($ppraliq) : 'NULL')." WHERE `id`=".$dbo->quote($pwhereup).";";
			$dbo->setQuery($q);
			$dbo->execute();
		}
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikrentitems&task=prices");
	}

	function removeprice() {
		$ids = VikRequest::getVar('cid', array(0));
		if (@count($ids)) {
			$dbo = JFactory::getDBO();
			foreach ($ids as $d) {
				$q = "DELETE FROM `#__vikrentitems_prices` WHERE `id`=".$dbo->quote($d).";";
				$dbo->setQuery($q);
				$dbo->execute();
			}
		}
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikrentitems&task=prices");
	}

	function cancelprice() {
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikrentitems&task=prices");
	}

	function categories() {
		VikRentItemsHelper::printHeader("4");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'categories'));
	
		parent::display();

		if (VikRentItems::showFooter()) {
			VikRentItemsHelper::printFooter();
		}
	}

	function newcat() {
		VikRentItemsHelper::printHeader("4");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'managecat'));
	
		parent::display();

		if (VikRentItems::showFooter()) {
			VikRentItemsHelper::printFooter();
		}
	}

	function editcat() {
		VikRentItemsHelper::printHeader("4");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'managecat'));
	
		parent::display();

		if (VikRentItems::showFooter()) {
			VikRentItemsHelper::printFooter();
		}
	}

	function createcat() {
		$pcatname = VikRequest::getString('catname', '', 'request');
		$pdescr = VikRequest::getString('descr', '', 'request', VIKREQUEST_ALLOWHTML);
		if (!empty($pcatname)) {
			$dbo = JFactory::getDBO();
			$q = "SELECT `ordering` FROM `#__vikrentitems_categories` ORDER BY `#__vikrentitems_categories`.`ordering` DESC LIMIT 1;";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() == 1) {
				$getlast = $dbo->loadResult();
				$newsortnum = $getlast + 1;
			} else {
				$newsortnum = 1;
			}
			$q = "INSERT INTO `#__vikrentitems_categories` (`name`,`descr`,`ordering`) VALUES(".$dbo->quote($pcatname).", ".$dbo->quote($pdescr).", ".(int)$newsortnum.");";
			$dbo->setQuery($q);
			$dbo->execute();
		}
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikrentitems&task=categories");
	}

	function updatecat() {
		$pcatname = VikRequest::getString('catname', '', 'request');
		$pdescr = VikRequest::getString('descr', '', 'request', VIKREQUEST_ALLOWHTML);
		$pwhereup = VikRequest::getString('whereup', '', 'request');
		if (!empty($pcatname)) {
			$dbo = JFactory::getDBO();
			$q = "UPDATE `#__vikrentitems_categories` SET `name`=".$dbo->quote($pcatname).", `descr`=".$dbo->quote($pdescr)." WHERE `id`=".$dbo->quote($pwhereup).";";
			$dbo->setQuery($q);
			$dbo->execute();
		}
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikrentitems&task=categories");
	}

	function removecat() {
		$ids = VikRequest::getVar('cid', array(0));
		if (@count($ids)) {
			$dbo = JFactory::getDBO();
			foreach ($ids as $d) {
				$q = "DELETE FROM `#__vikrentitems_categories` WHERE `id`=".$dbo->quote($d).";";
				$dbo->setQuery($q);
				$dbo->execute();
			}
		}
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikrentitems&task=categories");
	}

	function cancelcat() {
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikrentitems&task=categories");
	}

	function sortcategory() {
		$cid = VikRequest::getVar('cid', array(0));
		$sortid = (int)$cid[0];
		$pmode = VikRequest::getString('mode', '', 'request');
		$dbo = JFactory::getDBO();
		$mainframe = JFactory::getApplication();
		if (!empty($pmode)) {
			$q = "SELECT `id`,`ordering` FROM `#__vikrentitems_categories` ORDER BY `#__vikrentitems_categories`.`ordering` ASC;";
			$dbo->setQuery($q);
			$dbo->execute();
			$totr=$dbo->getNumRows();
			if ($totr > 1) {
				$data = $dbo->loadAssocList();
				if ($pmode == "up") {
					foreach ($data as $v) {
						if ($v['id'] == $sortid) {
							$y = $v['ordering'];
						}
					}
					if ($y && $y > 1) {
						$vik = $y - 1;
						$found = false;
						foreach ($data as $v) {
							if (intval($v['ordering']) == intval($vik)) {
								$found = true;
								$q = "UPDATE `#__vikrentitems_categories` SET `ordering`='".$y."' WHERE `id`='".$v['id']."' LIMIT 1;";
								$dbo->setQuery($q);
								$dbo->execute();
								$q = "UPDATE `#__vikrentitems_categories` SET `ordering`='".$vik."' WHERE `id`='".$sortid."' LIMIT 1;";
								$dbo->setQuery($q);
								$dbo->execute();
								break;
							}
						}
						if (!$found) {
							$q = "UPDATE `#__vikrentitems_categories` SET `ordering`='".$vik."' WHERE `id`='".$sortid."' LIMIT 1;";
							$dbo->setQuery($q);
							$dbo->execute();
						}
					}
				} elseif ($pmode == "down") {
					foreach ($data as $v) {
						if ($v['id'] == $sortid[0]) {
							$y = $v['ordering'];
						}
					}
					if ($y) {
						$vik = $y + 1;
						$found = false;
						foreach ($data as $v) {
							if (intval($v['ordering']) == intval($vik)) {
								$found = true;
								$q = "UPDATE `#__vikrentitems_categories` SET `ordering`='".$y."' WHERE `id`='".$v['id']."' LIMIT 1;";
								$dbo->setQuery($q);
								$dbo->execute();
								$q = "UPDATE `#__vikrentitems_categories` SET `ordering`='".$vik."' WHERE `id`='".$sortid."' LIMIT 1;";
								$dbo->setQuery($q);
								$dbo->execute();
								break;
							}
						}
						if (!$found) {
							$q = "UPDATE `#__vikrentitems_categories` SET `ordering`='".$vik."' WHERE `id`='".$sortid."' LIMIT 1;";
							$dbo->setQuery($q);
							$dbo->execute();
						}
					}
				}
			}
			$mainframe->redirect("index.php?option=com_vikrentitems&task=categories");
		} else {
			$mainframe->redirect("index.php?option=com_vikrentitems");
		}
	}

	function carat() {
		VikRentItemsHelper::printHeader("5");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'carat'));
	
		parent::display();

		if (VikRentItems::showFooter()) {
			VikRentItemsHelper::printFooter();
		}
	}

	function newcarat() {
		VikRentItemsHelper::printHeader("5");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'managecarat'));
	
		parent::display();

		if (VikRentItems::showFooter()) {
			VikRentItemsHelper::printFooter();
		}
	}

	function editcarat() {
		VikRentItemsHelper::printHeader("5");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'managecarat'));
	
		parent::display();

		if (VikRentItems::showFooter()) {
			VikRentItemsHelper::printFooter();
		}
	}

	function createcarat() {
		$pcaratname = VikRequest::getString('caratname', '', 'request');
		$pcaratmix = VikRequest::getString('caratmix', '', 'request');
		$pcarattextimg = VikRequest::getString('carattextimg', '', 'request', VIKREQUEST_ALLOWHTML);
		$pautoresize = VikRequest::getString('autoresize', '', 'request');
		$presizeto = VikRequest::getString('resizeto', '', 'request');
		if (!empty($pcaratname)) {
			if (intval($_FILES['caraticon']['error']) == 0 && VikRentItems::caniWrite(VRI_ADMIN_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR) && trim($_FILES['caraticon']['name'])!="") {
				jimport('joomla.filesystem.file');
				if (@is_uploaded_file($_FILES['caraticon']['tmp_name'])) {
					$safename = JFile::makeSafe(str_replace(" ", "_", strtolower($_FILES['caraticon']['name'])));
					if (file_exists(VRI_ADMIN_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.$safename)) {
						$j = 1;
						while (file_exists(VRI_ADMIN_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.$j.$safename)) {
							$j++;
						}
						$pwhere = VRI_ADMIN_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.$j.$safename;
					} else {
						$j = "";
						$pwhere = VRI_ADMIN_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.$safename;
					}
					VikRentItems::uploadFile($_FILES['caraticon']['tmp_name'], $pwhere);
					if (!getimagesize($pwhere)) {
						@unlink($pwhere);
						$picon = "";
					} else {
						@chmod($pwhere, 0644);
						$picon = $j.$safename;
						if ($pautoresize == "1" && !empty($presizeto)) {
							$eforj = new VriImageResizer();
							$origmod = $eforj->proportionalImage($pwhere, VRI_ADMIN_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'r_'.$j.$safename, $presizeto, $presizeto);
							if ($origmod) {
								@unlink($pwhere);
								$picon = 'r_'.$j.$safename;
							}
						}
					}
				} else {
					$picon = "";
				}
			} else {
				$picon = "";
			}
			$dbo = JFactory::getDBO();
			$q = "SELECT `ordering` FROM `#__vikrentitems_caratteristiche` ORDER BY `#__vikrentitems_caratteristiche`.`ordering` DESC LIMIT 1;";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() == 1) {
				$getlast = $dbo->loadResult();
				$newsortnum = $getlast + 1;
			} else {
				$newsortnum = 1;
			}
			$q = "INSERT INTO `#__vikrentitems_caratteristiche` (`name`,`icon`,`align`,`textimg`,`ordering`) VALUES(".$dbo->quote($pcaratname).", ".$dbo->quote($picon).", ".$dbo->quote($pcaratmix).", ".$dbo->quote($pcarattextimg).", '".$newsortnum."');";
			$dbo->setQuery($q);
			$dbo->execute();
		}
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikrentitems&task=carat");
	}

	function updatecarat() {
		$pcaratname = VikRequest::getString('caratname', '', 'request');
		$pcaratmix = VikRequest::getString('caratmix', '', 'request');
		$pcarattextimg = VikRequest::getString('carattextimg', '', 'request', VIKREQUEST_ALLOWHTML);
		$pwhereup = VikRequest::getString('whereup', '', 'request');
		$pautoresize = VikRequest::getString('autoresize', '', 'request');
		$presizeto = VikRequest::getString('resizeto', '', 'request');
		if (!empty($pcaratname)) {
			if (intval($_FILES['caraticon']['error']) == 0 && VikRentItems::caniWrite(VRI_ADMIN_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR) && trim($_FILES['caraticon']['name'])!="") {
				jimport('joomla.filesystem.file');
				if (@is_uploaded_file($_FILES['caraticon']['tmp_name'])) {
					$safename = JFile::makeSafe(str_replace(" ", "_", strtolower($_FILES['caraticon']['name'])));
					if (file_exists(VRI_ADMIN_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.$safename)) {
						$j = 1;
						while (file_exists(VRI_ADMIN_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.$j.$safename)) {
							$j++;
						}
						$pwhere=VRI_ADMIN_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.$j.$safename;
					} else {
						$j = "";
						$pwhere = VRI_ADMIN_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.$safename;
					}
					VikRentItems::uploadFile($_FILES['caraticon']['tmp_name'], $pwhere);
					if (!getimagesize($pwhere)) {
						@unlink($pwhere);
						$picon = "";
					} else {
						@chmod($pwhere, 0644);
						$picon = $j.$safename;
						if ($pautoresize == "1" && !empty($presizeto)) {
							$eforj = new VriImageResizer();
							$origmod = $eforj->proportionalImage($pwhere, VRI_ADMIN_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'r_'.$j.$safename, $presizeto, $presizeto);
							if ($origmod) {
								@unlink($pwhere);
								$picon = 'r_'.$j.$safename;
							}
						}
					}
				} else {
					$picon = "";
				}
			} else {
				$picon = "";
			}
			$dbo = JFactory::getDBO();
			$q = "UPDATE `#__vikrentitems_caratteristiche` SET `name`=".$dbo->quote($pcaratname).",".(strlen($picon) > 0 ? "`icon`='".$picon."'," : "")."`align`=".$dbo->quote($pcaratmix).",`textimg`=".$dbo->quote($pcarattextimg)." WHERE `id`=".$dbo->quote($pwhereup).";";
			$dbo->setQuery($q);
			$dbo->execute();
		}
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikrentitems&task=carat");
	}

	function removecarat() {
		$ids = VikRequest::getVar('cid', array(0));
		if (@count($ids)) {
			$dbo = JFactory::getDBO();
			foreach ($ids as $d) {
				$q = "SELECT `icon` FROM `#__vikrentitems_caratteristiche` WHERE `id`=".$dbo->quote($d).";";
				$dbo->setQuery($q);
				$dbo->execute();
				if ($dbo->getNumRows() == 1) {
					$rows = $dbo->loadAssocList();
					if (!empty($rows[0]['icon']) && file_exists(VRI_ADMIN_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.$rows[0]['icon'])) {
						@unlink(VRI_ADMIN_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.$rows[0]['icon']);
					}
				}	
				$q = "DELETE FROM `#__vikrentitems_caratteristiche` WHERE `id`=".$dbo->quote($d).";";
				$dbo->setQuery($q);
				$dbo->execute();
			}
		}
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikrentitems&task=carat");
	}

	function cancelcarat() {
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikrentitems&task=carat");
	}

	function optionals() {
		VikRentItemsHelper::printHeader("6");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'optionals'));
	
		parent::display();

		if (VikRentItems::showFooter()) {
			VikRentItemsHelper::printFooter();
		}
	}

	function newoptional() {
		VikRentItemsHelper::printHeader("6");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'manageopt'));
	
		parent::display();

		if (VikRentItems::showFooter()) {
			VikRentItemsHelper::printFooter();
		}
	}

	function editoptional() {
		VikRentItemsHelper::printHeader("6");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'manageopt'));
	
		parent::display();

		if (VikRentItems::showFooter()) {
			VikRentItemsHelper::printFooter();
		}
	}

	function createoptional() {
		$poptname = VikRequest::getString('optname', '', 'request');
		$poptdescr = VikRequest::getString('optdescr', '', 'request', VIKREQUEST_ALLOWHTML);
		$poptcost = VikRequest::getString('optcost', '', 'request');
		$poptperday = VikRequest::getString('optperday', '', 'request');
		$pmaxprice = VikRequest::getString('maxprice', '', 'request');
		$popthmany = VikRequest::getString('opthmany', '', 'request');
		$poptonlyonce = VikRequest::getString('optonlyonce', '', 'request');
		$poptonceperitem = VikRequest::getString('optonceperitem', '', 'request');
		$poptaliq = VikRequest::getString('optaliq', '', 'request');
		$pautoresize = VikRequest::getString('autoresize', '', 'request');
		$presizeto = VikRequest::getString('resizeto', '', 'request');
		$pforcesel = VikRequest::getString('forcesel', '', 'request');
		$pforceval = VikRequest::getString('forceval', '', 'request');
		$pforceifdays = VikRequest::getInt('forceifdays', '', 'request');
		$pforcevalperday = VikRequest::getString('forcevalperday', '', 'request');
		$pforcesel = $pforcesel == "1" ? 1 : 0;
		$pisspecification = VikRequest::getString('isspecification', '', 'request');
		$pisspecification = $pisspecification == "1" ? true : false;
		$pspecname = VikRequest::getVar('specname', array(0));
		$pspeccost = VikRequest::getVar('speccost', array(0));
		if ($pforcesel == 1) {
			$strforceval = intval($pforceval)."-".($pforcevalperday == "1" ? "1" : "0");
		} else {
			$strforceval = "";
		}
		if (!empty($poptname)) {
			if (intval($_FILES['optimg']['error']) == 0 && VikRentItems::caniWrite('./components/com_vikrentitems/resources/') && trim($_FILES['optimg']['name'])!="") {
				jimport('joomla.filesystem.file');
				if (@is_uploaded_file($_FILES['optimg']['tmp_name'])) {
					$safename=JFile::makeSafe(str_replace(" ", "_", strtolower($_FILES['optimg']['name'])));
					if (file_exists('./components/com_vikrentitems/resources/'.$safename)) {
						$j=1;
						while (file_exists('./components/com_vikrentitems/resources/'.$j.$safename)) {
							$j++;
						}
						$pwhere='./components/com_vikrentitems/resources/'.$j.$safename;
					} else {
						$j="";
						$pwhere='./components/com_vikrentitems/resources/'.$safename;
					}
					@move_uploaded_file($_FILES['optimg']['tmp_name'], $pwhere);
					if (!getimagesize($pwhere)){
						@unlink($pwhere);
						$picon="";
					} else {
						@chmod($pwhere, 0644);
						$picon=$j.$safename;
						if ($pautoresize=="1" && !empty($presizeto)) {
							$eforj = new VriImageResizer();
							$origmod = $eforj->proportionalImage($pwhere, './components/com_vikrentitems/resources/r_'.$j.$safename, $presizeto, $presizeto);
							if ($origmod) {
								@unlink($pwhere);
								$picon='r_'.$j.$safename;
							}
						}
					}
				} else {
					$picon="";
				}
			} else {
				$picon="";
			}
			$poptperday=($poptperday=="each" ? "1" : "0");
			($popthmany=="yes" ? $popthmany="1" : $popthmany="0");
			$poptonlyonce = $poptonlyonce == "yes" ? 1 : 0;
			$poptonceperitem = $poptonceperitem == 'yes' ? 1 : 0;
			$specificationstr = '';
			if ($pisspecification == true && count($pspecname) > 0 && count($pspeccost) > 0 && count($pspecname) == count($pspeccost)) {
				foreach ($pspecname as $kspec => $vspec) {
					$sname = str_replace('_', ' ', $vspec);
					$scost = floatval($pspeccost[$kspec]);
					if (strlen($sname) > 0 && strlen($scost) > 0) {
						$specificationstr .= $sname.'_'.$scost.';;';
					}
				}
				$specificationstr = rtrim($specificationstr, ';;');
			}
			$dbo = JFactory::getDBO();
			$q = "SELECT `ordering` FROM `#__vikrentitems_optionals` ORDER BY `#__vikrentitems_optionals`.`ordering` DESC LIMIT 1;";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() == 1) {
				$getlast=$dbo->loadResult();
				$newsortnum=$getlast + 1;
			} else {
				$newsortnum=1;
			}
			$q = "INSERT INTO `#__vikrentitems_optionals` (`name`,`descr`,`cost`,`perday`,`hmany`,`img`,`idiva`,`maxprice`,`forcesel`,`forceval`,`ordering`,`forceifdays`,`specifications`,`onlyonce`,`onceperitem`) VALUES(".$dbo->quote($poptname).", ".$dbo->quote($poptdescr).", ".$dbo->quote($poptcost).", ".$dbo->quote($poptperday).", ".$dbo->quote($popthmany).", '".$picon."', ".$dbo->quote($poptaliq).", ".$dbo->quote($pmaxprice).", '".$pforcesel."', '".$strforceval."', '".$newsortnum."', '".$pforceifdays."', ".$dbo->quote($specificationstr).", ".$dbo->quote($poptonlyonce).", ".$dbo->quote($poptonceperitem).");";
			$dbo->setQuery($q);
			$dbo->execute();
		}
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikrentitems&task=optionals");
	}

	function updateoptional() {
		$poptname = VikRequest::getString('optname', '', 'request');
		$poptdescr = VikRequest::getString('optdescr', '', 'request', VIKREQUEST_ALLOWHTML);
		$poptcost = VikRequest::getString('optcost', '', 'request');
		$poptperday = VikRequest::getString('optperday', '', 'request');
		$pmaxprice = VikRequest::getString('maxprice', '', 'request');
		$popthmany = VikRequest::getString('opthmany', '', 'request');
		$poptonlyonce = VikRequest::getString('optonlyonce', '', 'request');
		$poptonceperitem = VikRequest::getString('optonceperitem', '', 'request');
		$poptaliq = VikRequest::getString('optaliq', '', 'request');
		$pwhereup = VikRequest::getString('whereup', '', 'request');
		$pautoresize = VikRequest::getString('autoresize', '', 'request');
		$presizeto = VikRequest::getString('resizeto', '', 'request');
		$pforcesel = VikRequest::getString('forcesel', '', 'request');
		$pforceval = VikRequest::getString('forceval', '', 'request');
		$pforceifdays = VikRequest::getInt('forceifdays', '', 'request');
		$pforcevalperday = VikRequest::getString('forcevalperday', '', 'request');
		$pforcesel = $pforcesel == "1" ? 1 : 0;
		$pisspecification = VikRequest::getString('isspecification', '', 'request');
		$pisspecification = $pisspecification == "1" ? true : false;
		$pspecname = VikRequest::getVar('specname', array(0));
		$pspeccost = VikRequest::getVar('speccost', array(0));
		if ($pforcesel == 1) {
			$strforceval = intval($pforceval)."-".($pforcevalperday == "1" ? "1" : "0");
		} else {
			$strforceval = "";
		}
		if (!empty($poptname)) {
			if (intval($_FILES['optimg']['error']) == 0 && VikRentItems::caniWrite('./components/com_vikrentitems/resources/') && trim($_FILES['optimg']['name'])!="") {
				jimport('joomla.filesystem.file');
				if (@is_uploaded_file($_FILES['optimg']['tmp_name'])) {
					$safename=JFile::makeSafe(str_replace(" ", "_", strtolower($_FILES['optimg']['name'])));
					if (file_exists('./components/com_vikrentitems/resources/'.$safename)) {
						$j=1;
						while (file_exists('./components/com_vikrentitems/resources/'.$j.$safename)) {
							$j++;
						}
						$pwhere='./components/com_vikrentitems/resources/'.$j.$safename;
					} else {
						$j="";
						$pwhere='./components/com_vikrentitems/resources/'.$safename;
					}
					@move_uploaded_file($_FILES['optimg']['tmp_name'], $pwhere);
					if (!getimagesize($pwhere)){
						@unlink($pwhere);
						$picon="";
					} else {
						@chmod($pwhere, 0644);
						$picon=$j.$safename;
						if ($pautoresize=="1" && !empty($presizeto)) {
							$eforj = new VriImageResizer();
							$origmod = $eforj->proportionalImage($pwhere, './components/com_vikrentitems/resources/r_'.$j.$safename, $presizeto, $presizeto);
							if ($origmod) {
								@unlink($pwhere);
								$picon='r_'.$j.$safename;
							}
						}
					}
				} else {
					$picon="";
				}
			} else {
				$picon="";
			}
			($poptperday=="each" ? $poptperday="1" : $poptperday="0");
			($popthmany=="yes" ? $popthmany="1" : $popthmany="0");
			$poptonlyonce = $poptonlyonce == "yes" ? 1 : 0;
			$poptonceperitem = $poptonceperitem == 'yes' ? 1 : 0;
			$specificationstr = '';
			if ($pisspecification == true && count($pspecname) > 0 && count($pspeccost) > 0 && count($pspecname) == count($pspeccost)) {
				foreach ($pspecname as $kspec => $vspec) {
					$sname = str_replace('_', ' ', $vspec);
					$scost = floatval($pspeccost[$kspec]);
					if (strlen($sname) > 0 && strlen($scost) > 0) {
						$specificationstr .= $sname.'_'.$scost.';;';
					}
				}
				$specificationstr = rtrim($specificationstr, ';;');
			}
			$dbo = JFactory::getDBO();
			$q = "UPDATE `#__vikrentitems_optionals` SET `name`=".$dbo->quote($poptname).",`descr`=".$dbo->quote($poptdescr).",`cost`=".$dbo->quote($poptcost).",`perday`=".$dbo->quote($poptperday).",`hmany`=".$dbo->quote($popthmany).",".(strlen($picon)>0 ? "`img`='".$picon."'," : "")."`idiva`=".$dbo->quote($poptaliq).", `maxprice`=".$dbo->quote($pmaxprice).", `forcesel`='".$pforcesel."', `forceval`='".$strforceval."', `forceifdays`='".$pforceifdays."', `specifications`=".$dbo->quote($specificationstr).",`onlyonce`=".$dbo->quote($poptonlyonce).",`onceperitem`=".$dbo->quote($poptonceperitem)." WHERE `id`=".$dbo->quote($pwhereup).";";
			$dbo->setQuery($q);
			$dbo->execute();
		}
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikrentitems&task=optionals");
	}

	function removeoptionals() {
		$ids = VikRequest::getVar('cid', array(0));
		if (@count($ids)) {
			$dbo = JFactory::getDBO();
			foreach ($ids as $d) {
				$q = "SELECT `img` FROM `#__vikrentitems_optionals` WHERE `id`=".$dbo->quote($d).";";
				$dbo->setQuery($q);
				$dbo->execute();
				if ($dbo->getNumRows() == 1) {
					$rows = $dbo->loadAssocList();
					if (!empty($rows[0]['img']) && file_exists(VRI_ADMIN_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.$rows[0]['img'])) {
						@unlink(VRI_ADMIN_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.$rows[0]['img']);
					}
				}	
				$q = "DELETE FROM `#__vikrentitems_optionals` WHERE `id`=".$dbo->quote($d).";";
				$dbo->setQuery($q);
				$dbo->execute();
			}
		}
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikrentitems&task=optionals");
	}

	function canceloptional() {
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikrentitems&task=optionals");
	}

	function stats() {
		VikRentItemsHelper::printHeader("10");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'stats'));
	
		parent::display();

		if (VikRentItems::showFooter()) {
			VikRentItemsHelper::printFooter();
		}
	}

	function removestats() {
		$ids = VikRequest::getVar('cid', array(0));
		if (@count($ids)) {
			$dbo = JFactory::getDBO();
			foreach ($ids as $d) {
				$q = "DELETE FROM `#__vikrentitems_stats` WHERE `id`=".$dbo->quote($d).";";
				$dbo->setQuery($q);
				$dbo->execute();
			}
		}
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikrentitems&task=stats");
	}

	function cancelstats() {
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikrentitems&task=stats");
	}

	function items() {
		VikRentItemsHelper::printHeader("7");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'items'));
	
		parent::display();

		if (VikRentItems::showFooter()) {
			VikRentItemsHelper::printFooter();
		}
	}

	function newitem() {
		VikRentItemsHelper::printHeader("7");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'manageitem'));
	
		parent::display();

		if (VikRentItems::showFooter()) {
			VikRentItemsHelper::printFooter();
		}
	}

	function edititem() {
		VikRentItemsHelper::printHeader("7");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'manageitem'));
	
		parent::display();

		if (VikRentItems::showFooter()) {
			VikRentItemsHelper::printFooter();
		}
	}

	function createitem() {
		$mainframe = JFactory::getApplication();
		$pcname = VikRequest::getString('cname', '', 'request');
		$pccat = VikRequest::getVar('ccat', array(0));
		$pcdescr = VikRequest::getString('cdescr', '', 'request', VIKREQUEST_ALLOWRAW);
		$pshortdesc = VikRequest::getString('shortdesc', '', 'request', VIKREQUEST_ALLOWHTML);
		$pcplace = VikRequest::getVar('cplace', array(0));
		$pcretplace = VikRequest::getVar('cretplace', array(0));
		$pccarat = VikRequest::getVar('ccarat', array(0));
		$pcoptional = VikRequest::getVar('coptional', array(0));
		$pcavail = VikRequest::getString('cavail', '', 'request');
		$pautoresize = VikRequest::getString('autoresize', '', 'request');
		$presizeto = VikRequest::getString('resizeto', '', 'request');
		$pautoresizemore = VikRequest::getString('autoresizemore', '', 'request');
		$presizetomore = VikRequest::getString('resizetomore', '', 'request');
		$punits = VikRequest::getInt('units', '', 'request');
		$pimages = VikRequest::getVar('cimgmore', null, 'files', 'array');
		$pstartfrom = VikRequest::getString('startfrom', '', 'request');
		$pstartfromtext = VikRequest::getString('startfromtext', '', 'request');
		$pextraemail = VikRequest::getString('extraemail', '', 'request');
		$paskquantity = VikRequest::getString('askquantity', '', 'request');
		$paskquantity = $paskquantity == "yes" ? "1" : "0";
		$pdiscsquantstab = VikRequest::getString('discsquantstab', '', 'request');
		$pdiscsquantstab = $pdiscsquantstab == "yes" ? "1" : "0";
		$phourlycalendar = VikRequest::getString('hourlycalendar', '', 'request');
		$phourlycalendar = $phourlycalendar == "yes" ? "1" : "0";
		$ptimeslots = VikRequest::getString('timeslots', '', 'request');
		$ptimeslots = $ptimeslots == "yes" ? "1" : "0";
		$pdelivery = VikRequest::getString('delivery', '', 'request');
		$pdelivery = $pdelivery == "yes" ? "1" : "0";
		$poverdelcost = VikRequest::getString('overdelcost', '', 'request');
		$poverdelcost = floatval($poverdelcost);
		$pdropdaysplus = VikRequest::getString('dropdaysplus', '', 'request');
		$pdropdaysplus = strlen($pdropdaysplus) > 0 ? intval($pdropdaysplus) : '';
		$paramstr = 'startfromtext:'.$pstartfromtext.';_;hourlycalendar:'.$phourlycalendar.';_;discsquantstab:'.$pdiscsquantstab.';_;timeslots:'.$ptimeslots.';_;dropdaysplus:'.$pdropdaysplus.';_;delivery:'.$pdelivery.';_;overdelcost:'.$poverdelcost.';_;extraemail:'.$pextraemail.';_;';
		$pcustptitle = VikRequest::getString('custptitle', '', 'request');
		$pcustptitlew = VikRequest::getString('custptitlew', '', 'request');
		$pcustptitlew = in_array($pcustptitlew, array('before', 'after', 'replace')) ? $pcustptitlew : 'before';
		$pmetakeywords = VikRequest::getString('metakeywords', '', 'request');
		$pmetadescription = VikRequest::getString('metadescription', '', 'request');
		$psefalias = VikRequest::getString('sefalias', '', 'request');
		$psefalias = empty($psefalias) ? JFilterOutput::stringURLSafe($pcname) : JFilterOutput::stringURLSafe($psefalias);
		$pminquant = VikRequest::getInt('minquant', '', 'request');
		$pminquant = $pminquant < 1 ? 1 : $pminquant;
		//Items Grouping
		$pisgroup = VikRequest::getInt('isgroup', '', 'request');
		$pchildid = VikRequest::getVar('childid', array(0));
		$pgroupunits = VikRequest::getVar('groupunits', array(0));
		//
		jimport('joomla.filesystem.file');
		if (!empty($pcname)) {
			if (intval($_FILES['cimg']['error']) == 0 && VikRentItems::caniWrite(VRI_ADMIN_PATH.DS.'resources'.DS) && trim($_FILES['cimg']['name'])!="") {
				if (@is_uploaded_file($_FILES['cimg']['tmp_name'])) {
					$safename=JFile::makeSafe(str_replace(" ", "_", strtolower($_FILES['cimg']['name'])));
					if (file_exists(VRI_ADMIN_PATH.DS.'resources'.DS.$safename)) {
						$j=1;
						while (file_exists(VRI_ADMIN_PATH.DS.'resources'.DS.$j.$safename)) {
							$j++;
						}
						$pwhere=VRI_ADMIN_PATH.DS.'resources'.DS.$j.$safename;
					} else {
						$j="";
						$pwhere=VRI_ADMIN_PATH.DS.'resources'.DS.$safename;
					}
					VikRentItems::uploadFile($_FILES['cimg']['tmp_name'], $pwhere);
					if (!($mainimginfo = getimagesize($pwhere))){
						@unlink($pwhere);
						$picon="";
					} else {
						@chmod($pwhere, 0644);
						$picon=$j.$safename;
						if ($pautoresize=="1" && !empty($presizeto)) {
							$eforj = new VriImageResizer();
							$origmod = $eforj->proportionalImage($pwhere, VRI_ADMIN_PATH.DS.'resources'.DS.'r_'.$j.$safename, $presizeto, $presizeto);
							if ($origmod) {
								@unlink($pwhere);
								$picon='r_'.$j.$safename;
							}
						}
						$thumbs_width = VikRentItems::getThumbnailsWidth();
						//VikRentItems 1.1 - Thumbnail for better CSS forcing result
						if ($mainimginfo[0] > $thumbs_width) {
							$eforj = new VriImageResizer();
							$eforj->proportionalImage(VRI_ADMIN_PATH.DS.'resources'.DS.$picon, VRI_ADMIN_PATH.DS.'resources'.DS.'vthumb_'.$picon, $thumbs_width, $thumbs_width);
						}
						//end VikRentItems 1.1 - Thumbnail for better CSS forcing result
					}
				} else {
					$picon="";
				}
			} else {
				$picon="";
			}
			//more images
			$creativik = new VriImageResizer();
			$bigsdest = VRI_ADMIN_PATH.DS.'resources'.DS;
			$thumbsdest = VRI_ADMIN_PATH.DS.'resources'.DS;
			$dest = VRI_ADMIN_PATH.DS.'resources'.DS;
			$moreimagestr="";
			foreach ($pimages['name'] as $kk=>$ci) if (!empty($ci)) $arrimgs[]=$kk;
			if (is_array($arrimgs)) {
				foreach ($arrimgs as $imgk){
					if (strlen(trim($pimages['name'][$imgk]))) {
						$filename = JFile::makeSafe(str_replace(" ", "_", strtolower($pimages['name'][$imgk])));
						$src = $pimages['tmp_name'][$imgk];
						$j="";
						if (file_exists($dest.$filename)) {
							$j=rand(171, 1717);
							while (file_exists($dest.$j.$filename)) {
								$j++;
							}
						}
						$finaldest=$dest.$j.$filename;
						$check=getimagesize($pimages['tmp_name'][$imgk]);
						if ($check[2] & imagetypes()) {
							if (VikRentItems::uploadFile($src, $finaldest)) {
								$gimg=$j.$filename;
								//orig img
								$origmod = true;
								if ($pautoresizemore == "1" && !empty($presizetomore)) {
									$origmod = $creativik->proportionalImage($finaldest, $bigsdest.'big_'.$j.$filename, $presizetomore, $presizetomore);
								} else {
									copy($finaldest, $bigsdest.'big_'.$j.$filename);
								}
								//thumb
								$thumb = $creativik->proportionalImage($finaldest, $thumbsdest.'thumb_'.$j.$filename, 70, 70);
								if (!$thumb || !$origmod) {
									if (file_exists($bigsdest.'big_'.$j.$filename)) @unlink($bigsdest.'big_'.$j.$filename);
									if (file_exists($thumbsdest.'thumb_'.$j.$filename)) @unlink($thumbsdest.'thumb_'.$j.$filename);
									VikError::raiseWarning('', 'Error While Uploading the File: '.$pimages['name'][$imgk]);
								} else {
									$moreimagestr.=$j.$filename.";;";
								}
								@unlink($finaldest);
							} else {
								VikError::raiseWarning('', 'Error While Uploading the File: '.$pimages['name'][$imgk]);
							}
						} else {
							VikError::raiseWarning('', 'Error While Uploading the File: '.$pimages['name'][$imgk]);
						}
					}
				}
			}
			//end more images
			if (!empty($pcplace) && @count($pcplace)) {
				$pcplacedef="";
				foreach ($pcplace as $cpla){
					$pcplacedef.=$cpla.";";
				}
			} else {
				$pcplacedef="";
			}
			if (!empty($pcretplace) && @count($pcretplace)) {
				$pcretplacedef="";
				foreach ($pcretplace as $cpla){
					$pcretplacedef.=$cpla.";";
				}
			} else {
				$pcretplacedef="";
			}
			if (!empty($pccat) && @count($pccat)) {
				foreach ($pccat as $ccat){
					$pccatdef.=$ccat.";";
				}
			} else {
				$pccatdef="";
			}
			if (!empty($pccarat) && @count($pccarat)) {
				foreach ($pccarat as $ccarat){
					$pccaratdef.=$ccarat.";";
				}
			} else {
				$pccaratdef="";
			}
			if (!empty($pcoptional) && @count($pcoptional)) {
				foreach ($pcoptional as $coptional){
					$pcoptionaldef.=$coptional.";";
				}
			} else {
				$pcoptionaldef="";
			}
			$pcavaildef=($pcavail=="yes" ? "1" : "0");
			//JSON params
			$item_jsparams = array();
			$item_jsparams['custptitle'] = $pcustptitle;
			$item_jsparams['custptitlew'] = $pcustptitlew;
			$item_jsparams['metakeywords'] = $pmetakeywords;
			$item_jsparams['metadescription'] = $pmetadescription;
			$item_jsparams['minquant'] = $pminquant;
			$dbo = JFactory::getDBO();
			$q = "INSERT INTO `#__vikrentitems_items` (`name`,`img`,`idcat`,`idcarat`,`idopt`,`info`,`idplace`,`avail`,`units`,`idretplace`,`moreimgs`,`startfrom`,`askquantity`,`params`,`shortdesc`,`jsparams`,`alias`,`isgroup`) VALUES(".$dbo->quote($pcname).",".$dbo->quote($picon).",".$dbo->quote($pccatdef).",".$dbo->quote($pccaratdef).",".$dbo->quote($pcoptionaldef).",".$dbo->quote($pcdescr).",".$dbo->quote($pcplacedef).",".$dbo->quote($pcavaildef).",".($punits > 0 ? $dbo->quote($punits) : "'1'").",".$dbo->quote($pcretplacedef).", ".$dbo->quote($moreimagestr).", ".(strlen($pstartfrom) > 0 ? "'".$pstartfrom."'" : "null").", '".$paskquantity."', ".$dbo->quote($paramstr).", ".$dbo->quote($pshortdesc).", ".$dbo->quote(json_encode($item_jsparams)).", ".$dbo->quote($psefalias).", ".($pisgroup > 0 && @count($pchildid) > 0 && !empty($pchildid[0]) ? '1' : '0').");";
			$dbo->setQuery($q);
			$dbo->execute();
			$lid = $dbo->insertid();
			if (!empty($lid)) {
				//check items grouping relations
				if ($pisgroup > 0 && @count($pchildid) > 0 && !empty($pchildid[0])) {
					foreach ($pchildid as $child_id) {
						if (empty($child_id)) {
							continue;
						}
						$set_units = isset($pgroupunits[(int)$child_id]) ? (int)$pgroupunits[(int)$child_id] : 1;
						$set_units = $set_units > 0 ? $set_units : 1;
						$q = "INSERT INTO `#__vikrentitems_groupsrel` (`parentid`,`childid`,`units`) VALUES(".$lid.", ".(int)$child_id.", ".$set_units.");";
						$dbo->setQuery($q);
						$dbo->execute();
					}
				}
				//
				$mainframe->redirect("index.php?option=com_vikrentitems&task=tariffs&cid[]=".$lid);
				exit;
			}
		}
		$mainframe->redirect("index.php?option=com_vikrentitems&task=items");
	}

	function updateitem() {
		$this->do_updateitem();
	}

	function updateitemapply() {
		$this->do_updateitem(true);
	}

	function do_updateitem($stay = false) {
		$mainframe = JFactory::getApplication();
		$pcname = VikRequest::getString('cname', '', 'request');
		$pccat = VikRequest::getVar('ccat', array(0));
		$pcdescr = VikRequest::getString('cdescr', '', 'request', VIKREQUEST_ALLOWRAW);
		$pshortdesc = VikRequest::getString('shortdesc', '', 'request', VIKREQUEST_ALLOWHTML);
		$pcplace = VikRequest::getVar('cplace', array(0));
		$pcretplace = VikRequest::getVar('cretplace', array(0));
		$pccarat = VikRequest::getVar('ccarat', array(0));
		$pcoptional = VikRequest::getVar('coptional', array(0));
		$pcavail = VikRequest::getString('cavail', '', 'request');
		$pwhereup = VikRequest::getString('whereup', '', 'request');
		$pautoresize = VikRequest::getString('autoresize', '', 'request');
		$presizeto = VikRequest::getString('resizeto', '', 'request');
		$pautoresizemore = VikRequest::getString('autoresizemore', '', 'request');
		$presizetomore = VikRequest::getString('resizetomore', '', 'request');
		$punits = VikRequest::getInt('units', '', 'request');
		$pimages = VikRequest::getVar('cimgmore', null, 'files', 'array');
		$pactmoreimgs = VikRequest::getString('actmoreimgs', '', 'request');
		$pstartfrom = VikRequest::getString('startfrom', '', 'request');
		$pstartfromtext = VikRequest::getString('startfromtext', '', 'request');
		$pextraemail = VikRequest::getString('extraemail', '', 'request');
		$paskquantity = VikRequest::getString('askquantity', '', 'request');
		$paskquantity = $paskquantity == "yes" ? "1" : "0";
		$pdiscsquantstab = VikRequest::getString('discsquantstab', '', 'request');
		$pdiscsquantstab = $pdiscsquantstab == "yes" ? "1" : "0";
		$phourlycalendar = VikRequest::getString('hourlycalendar', '', 'request');
		$phourlycalendar = $phourlycalendar == "yes" ? "1" : "0";
		$ptimeslots = VikRequest::getString('timeslots', '', 'request');
		$ptimeslots = $ptimeslots == "yes" ? "1" : "0";
		$pdelivery = VikRequest::getString('delivery', '', 'request');
		$pdelivery = $pdelivery == "yes" ? "1" : "0";
		$poverdelcost = VikRequest::getString('overdelcost', '', 'request');
		$poverdelcost = floatval($poverdelcost);
		$pdropdaysplus = VikRequest::getString('dropdaysplus', '', 'request');
		$pdropdaysplus = strlen($pdropdaysplus) > 0 ? intval($pdropdaysplus) : '';
		$paramstr = 'startfromtext:'.$pstartfromtext.';_;hourlycalendar:'.$phourlycalendar.';_;discsquantstab:'.$pdiscsquantstab.';_;timeslots:'.$ptimeslots.';_;dropdaysplus:'.$pdropdaysplus.';_;delivery:'.$pdelivery.';_;overdelcost:'.$poverdelcost.';_;extraemail:'.$pextraemail.';_;';
		$pcustptitle = VikRequest::getString('custptitle', '', 'request');
		$pcustptitlew = VikRequest::getString('custptitlew', '', 'request');
		$pcustptitlew = in_array($pcustptitlew, array('before', 'after', 'replace')) ? $pcustptitlew : 'before';
		$pmetakeywords = VikRequest::getString('metakeywords', '', 'request');
		$pmetadescription = VikRequest::getString('metadescription', '', 'request');
		$psefalias = VikRequest::getString('sefalias', '', 'request');
		$psefalias = empty($psefalias) ? JFilterOutput::stringURLSafe($pcname) : JFilterOutput::stringURLSafe($psefalias);
		$pminquant = VikRequest::getInt('minquant', '', 'request');
		$pminquant = $pminquant < 1 ? 1 : $pminquant;
		//Items Grouping
		$pisgroup = VikRequest::getInt('isgroup', '', 'request');
		$pchildid = VikRequest::getVar('childid', array(0));
		$pgroupunits = VikRequest::getVar('groupunits', array(0));
		$current_item = VikRentItems::getItemInfo((int)$pwhereup);
		//
		jimport('joomla.filesystem.file');
		if (!empty($pcname)) {
			if (intval($_FILES['cimg']['error']) == 0 && VikRentItems::caniWrite(VRI_ADMIN_PATH.DS.'resources'.DS) && trim($_FILES['cimg']['name'])!="") {
				if (@is_uploaded_file($_FILES['cimg']['tmp_name'])) {
					$safename=JFile::makeSafe(str_replace(" ", "_", strtolower($_FILES['cimg']['name'])));
					if (file_exists(VRI_ADMIN_PATH.DS.'resources'.DS.$safename)) {
						$j=1;
						while (file_exists(VRI_ADMIN_PATH.DS.'resources'.DS.$j.$safename)) {
							$j++;
						}
						$pwhere=VRI_ADMIN_PATH.DS.'resources'.DS.$j.$safename;
					} else {
						$j="";
						$pwhere=VRI_ADMIN_PATH.DS.'resources'.DS.$safename;
					}
					VikRentItems::uploadFile($_FILES['cimg']['tmp_name'], $pwhere);
					if (!($mainimginfo = getimagesize($pwhere))){
						@unlink($pwhere);
						$picon="";
					} else {
						@chmod($pwhere, 0644);
						$picon=$j.$safename;
						if ($pautoresize=="1" && !empty($presizeto)) {
							$eforj = new VriImageResizer();
							$origmod = $eforj->proportionalImage($pwhere, VRI_ADMIN_PATH.DS.'resources'.DS.'r_'.$j.$safename, $presizeto, $presizeto);
							if ($origmod) {
								@unlink($pwhere);
								$picon='r_'.$j.$safename;
							}
						}
						$thumbs_width = VikRentItems::getThumbnailsWidth();
						//VikRentItems 1.1 - Thumbnail for better CSS forcing result
						if ($mainimginfo[0] > $thumbs_width) {
							$eforj = new VriImageResizer();
							$eforj->proportionalImage(VRI_ADMIN_PATH.DS.'resources'.DS.$picon, VRI_ADMIN_PATH.DS.'resources'.DS.'vthumb_'.$picon, $thumbs_width, $thumbs_width);
						}
						//end VikRentItems 1.1 - Thumbnail for better CSS forcing result
					}
				} else {
					$picon="";
				}
			} else {
				$picon="";
			}
			//more images
			$creativik = new VriImageResizer();
			$bigsdest = VRI_ADMIN_PATH.DS.'resources'.DS;
			$thumbsdest = VRI_ADMIN_PATH.DS.'resources'.DS;
			$dest = VRI_ADMIN_PATH.DS.'resources'.DS;
			$moreimagestr=$pactmoreimgs;
			foreach ($pimages['name'] as $kk=>$ci) if (!empty($ci)) $arrimgs[]=$kk;
			if (@count($arrimgs) > 0) {
				foreach ($arrimgs as $imgk){
					if (strlen(trim($pimages['name'][$imgk]))) {
						$filename = JFile::makeSafe(str_replace(" ", "_", strtolower($pimages['name'][$imgk])));
						$src = $pimages['tmp_name'][$imgk];
						$j="";
						if (file_exists($dest.$filename)) {
							$j=rand(171, 1717);
							while (file_exists($dest.$j.$filename)) {
								$j++;
							}
						}
						$finaldest=$dest.$j.$filename;
						$check=getimagesize($pimages['tmp_name'][$imgk]);
						if ($check[2] & imagetypes()) {
							if (VikRentItems::uploadFile($src, $finaldest)) {
								$gimg=$j.$filename;
								//orig img
								$origmod = true;
								if ($pautoresizemore == "1" && !empty($presizetomore)) {
									$origmod = $creativik->proportionalImage($finaldest, $bigsdest.'big_'.$j.$filename, $presizetomore, $presizetomore);
								} else {
									copy($finaldest, $bigsdest.'big_'.$j.$filename);
								}
								//thumb
								$thumb = $creativik->proportionalImage($finaldest, $thumbsdest.'thumb_'.$j.$filename, 70, 70);
								if (!$thumb || !$origmod) {
									if (file_exists($bigsdest.'big_'.$j.$filename)) @unlink($bigsdest.'big_'.$j.$filename);
									if (file_exists($thumbsdest.'thumb_'.$j.$filename)) @unlink($thumbsdest.'thumb_'.$j.$filename);
									VikError::raiseWarning('', 'Error While Uploading the File: '.$pimages['name'][$imgk]);
								} else {
									$moreimagestr.=$j.$filename.";;";
								}
								@unlink($finaldest);
							} else {
								VikError::raiseWarning('', 'Error While Uploading the File: '.$pimages['name'][$imgk]);
							}
						} else {
							VikError::raiseWarning('', 'Error While Uploading the File: '.$pimages['name'][$imgk]);
						}
					}
				}
			}
			//end more images
			if (!empty($pcplace) && @count($pcplace)) {
				$pcplacedef="";
				foreach ($pcplace as $cpla){
					$pcplacedef.=$cpla.";";
				}
			} else {
				$pcplacedef="";
			}
			if (!empty($pcretplace) && @count($pcretplace)) {
				$pcretplacedef="";
				foreach ($pcretplace as $cpla){
					$pcretplacedef.=$cpla.";";
				}
			} else {
				$pcretplacedef="";
			}
			if (!empty($pccat) && @count($pccat)) {
				foreach ($pccat as $ccat){
					$pccatdef.=$ccat.";";
				}
			} else {
				$pccatdef="";
			}
			if (!empty($pccarat) && @count($pccarat)) {
				foreach ($pccarat as $ccarat){
					$pccaratdef.=$ccarat.";";
				}
			} else {
				$pccaratdef="";
			}
			if (!empty($pcoptional) && @count($pcoptional)) {
				foreach ($pcoptional as $coptional){
					$pcoptionaldef.=$coptional.";";
				}
			} else {
				$pcoptionaldef="";
			}
			$pcavaildef=($pcavail=="yes" ? "1" : "0");
			//JSON params
			$item_jsparams = array();
			$item_jsparams['custptitle'] = $pcustptitle;
			$item_jsparams['custptitlew'] = $pcustptitlew;
			$item_jsparams['metakeywords'] = $pmetakeywords;
			$item_jsparams['metadescription'] = $pmetadescription;
			$item_jsparams['minquant'] = $pminquant;
			$dbo = JFactory::getDBO();
			$q = "UPDATE `#__vikrentitems_items` SET `name`=".$dbo->quote($pcname).",".(strlen($picon) > 0 ? "`img`='".$picon."'," : "")."`idcat`=".$dbo->quote($pccatdef).",`idcarat`=".$dbo->quote($pccaratdef).",`idopt`=".$dbo->quote($pcoptionaldef).",`info`=".$dbo->quote($pcdescr).",`idplace`=".$dbo->quote($pcplacedef).",`avail`=".$dbo->quote($pcavaildef).",`units`=".($punits > 0 ? $dbo->quote($punits) : "'1'").",`idretplace`=".$dbo->quote($pcretplacedef).",`moreimgs`=".$dbo->quote($moreimagestr).",`startfrom`=".(strlen($pstartfrom) > 0 ? "'".$pstartfrom."'" : "null").",`askquantity`='".$paskquantity."',`params`=".$dbo->quote($paramstr).",`shortdesc`=".$dbo->quote($pshortdesc).",`jsparams`=".$dbo->quote(json_encode($item_jsparams)).",`alias`=".$dbo->quote($psefalias).",`isgroup`=".($pisgroup > 0 && @count($pchildid) > 0 && !empty($pchildid[0]) ? '1' : '0')." WHERE `id`=".$dbo->quote($pwhereup).";";
			$dbo->setQuery($q);
			$dbo->execute();
			//check items grouping relations
			if ($pisgroup > 0 && @count($pchildid) > 0 && !empty($pchildid[0])) {
				$groups_rel = array();
				foreach ($pchildid as $child_id) {
					if (empty($child_id)) {
						continue;
					}
					$set_units = isset($pgroupunits[(int)$child_id]) ? (int)$pgroupunits[(int)$child_id] : 1;
					$set_units = $set_units > 0 ? $set_units : 1;
					array_push($groups_rel, array(
						'parentid' => $current_item['id'],
						'childid' => (int)$child_id,
						'units' => $set_units
					));
				}
				if ($current_item['isgroup'] > 0) {
					//it was a group also before, check if the relations are different and raise warning
					$q = "SELECT * FROM `#__vikrentitems_groupsrel` WHERE `parentid`=".$current_item['id'].";";
					$dbo->setQuery($q);
					$dbo->execute();
					$prev_rels = $dbo->getNumRows() > 0 ? $dbo->loadAssocList() : array();
					$missing_ids = false;
					foreach ($prev_rels as $prev_rel) {
						$id_found = false;
						foreach ($groups_rel as $rel) {
							if ($rel['childid'] == $prev_rel['childid']) {
								$id_found = true;
								break;
							}
						}
						if (!$id_found) {
							$missing_ids = true;
							break;
						}
					}
					if ($missing_ids || count($prev_rels) != count($groups_rel)) {
						VikError::raiseWarning('', JText::_('VRIUPDITEMDIFFGROUP'));
					}
				}
				//attempt to delete any possible previous relation
				$q = "DELETE FROM `#__vikrentitems_groupsrel` WHERE `parentid`=".$current_item['id'].";";
				$dbo->setQuery($q);
				$dbo->execute();
				//create new relations
				foreach ($groups_rel as $grel) {
					$q = "INSERT INTO `#__vikrentitems_groupsrel` (`parentid`,`childid`,`units`) VALUES(".$grel['parentid'].", ".$grel['childid'].", ".$grel['units'].");";
					$dbo->setQuery($q);
					$dbo->execute();
				}
			} else {
				if ($current_item['isgroup'] > 0) {
					//no more a group, but it used to be. Remove relations and raise warning about the availability
					$q = "DELETE FROM `#__vikrentitems_groupsrel` WHERE `parentid`=".$current_item['id'].";";
					$dbo->setQuery($q);
					$dbo->execute();
					VikError::raiseWarning('', JText::_('VRIUPDITEMNOMOREAGROUP'));
				}
			}
			//
			if ($stay === true) {
				$mainframe->redirect("index.php?option=com_vikrentitems&task=edititem&cid[]=".$pwhereup);
				exit;
			}
		}
		$mainframe->redirect("index.php?option=com_vikrentitems&task=items");
	}

	function removeitem() {
		$ids = VikRequest::getVar('cid', array(0));
		if (@count($ids)) {
			$dbo = JFactory::getDBO();
			foreach ($ids as $d) {
				$q = "DELETE FROM `#__vikrentitems_items` WHERE `id`=".$dbo->quote($d).";";
				$dbo->setQuery($q);
				$dbo->execute();
				$q = "DELETE FROM `#__vikrentitems_dispcost` WHERE `iditem`=".$dbo->quote($d).";";
				$dbo->setQuery($q);
				$dbo->execute();
			}
		}
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikrentitems&task=items");
	}

	function modavail() {
		$cid = VikRequest::getVar('cid', array(0));
		$item = $cid[0];
		if (!empty($item)) {
			$dbo = JFactory::getDBO();
			$q = "SELECT `avail` FROM `#__vikrentitems_items` WHERE `id`=".$dbo->quote($item).";";
			$dbo->setQuery($q);
			$dbo->execute();
			$get = $dbo->loadAssocList();
			$q = "UPDATE `#__vikrentitems_items` SET `avail`='".(intval($get[0]['avail'])==1 ? 0 : 1)."' WHERE `id`=".$dbo->quote($item).";";
			$dbo->setQuery($q);
			$dbo->execute();
		}
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikrentitems&task=items");
	}

	function tariffs() {
		VikRentItemsHelper::printHeader("fares");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'tariffs'));
	
		parent::display();

		if (VikRentItems::showFooter()) {
			VikRentItemsHelper::printFooter();
		}
	}

	function removetariffs() {
		$ids = VikRequest::getVar('cid', array(0));
		$pelemid = VikRequest::getString('elemid', '', 'request');
		if (@count($ids)) {
			$dbo = JFactory::getDBO();
			foreach ($ids as $r) {
				$x = explode(";", $r);
				foreach ($x as $rm) {
					if (!empty($rm)) {
						$q = "DELETE FROM `#__vikrentitems_dispcost` WHERE `id`=".$dbo->quote($rm).";";
						$dbo->setQuery($q);
						$dbo->execute();
					}
				}
			}
		}
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikrentitems&task=tariffs&cid[]=".$pelemid);
	}

	function tariffshours() {
		VikRentItemsHelper::printHeader("fares");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'tariffshours'));
	
		parent::display();

		if (VikRentItems::showFooter()) {
			VikRentItemsHelper::printFooter();
		}
	}

	function removetariffshours() {
		$ids = VikRequest::getVar('cid', array(0));
		$pelemid = VikRequest::getString('elemid', '', 'request');
		if (@count($ids)) {
			$dbo = JFactory::getDBO();
			foreach ($ids as $r) {
				$x = explode(";", $r);
				foreach ($x as $rm) {
					if (!empty($rm)) {
						$q = "DELETE FROM `#__vikrentitems_dispcosthours` WHERE `id`=".$dbo->quote($rm).";";
						$dbo->setQuery($q);
						$dbo->execute();
					}
				}
			}
		}
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikrentitems&task=tariffshours&cid[]=".$pelemid);
	}

	function hourscharges() {
		VikRentItemsHelper::printHeader("fares");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'hourscharges'));
	
		parent::display();

		if (VikRentItems::showFooter()) {
			VikRentItemsHelper::printFooter();
		}
	}

	function removehourscharges() {
		$ids = VikRequest::getVar('cid', array(0));
		$pelemid = VikRequest::getString('elemid', '', 'request');
		if (@count($ids)) {
			$dbo = JFactory::getDBO();
			foreach ($ids as $r) {
				$x = explode(";", $r);
				foreach ($x as $rm) {
					if (!empty($rm)) {
						$q = "DELETE FROM `#__vikrentitems_hourscharges` WHERE `id`=".$dbo->quote($rm).";";
						$dbo->setQuery($q);
						$dbo->execute();
					}
				}
			}
		}
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikrentitems&task=hourscharges&cid[]=".$pelemid);
	}

	function cancel() {
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikrentitems&task=items");
	}

	function calendar() {
		VikRentItemsHelper::printHeader("19");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'calendar'));
	
		parent::display();

		if (VikRentItems::showFooter()) {
			VikRentItemsHelper::printFooter();
		}
	}

	function cancelcalendar() {
		$piditem = VikRequest::getString('iditem', '', 'request');
		$preturn = VikRequest::getString('return', '', 'request');
		$pidorder = VikRequest::getString('idorder', '', 'request');
		$mainframe = JFactory::getApplication();
		if ($preturn == 'order' && !empty($pidorder)) {
			$mainframe->redirect("index.php?option=com_vikrentitems&task=editorder&cid[]=".$pidorder);
		} else {
			$mainframe->redirect("index.php?option=com_vikrentitems&task=calendar&cid[]=".$piditem);
		}
	}

	function goconfig() {
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikrentitems&task=config");
	}

	function config() {
		VikRentItemsHelper::printHeader("11");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'config'));
	
		parent::display();

		if (VikRentItems::showFooter()) {
			VikRentItemsHelper::printFooter();
		}
	}

	function saveconfig() {
		$dbo = JFactory::getDBO();
		$session = JFactory::getSession();
		$pallowrent = VikRequest::getString('allowrent', '', 'request');
		$pdisabledrentmsg = VikRequest::getString('disabledrentmsg', '', 'request', VIKREQUEST_ALLOWHTML);
		$ptimeopenstorealw = VikRequest::getString('timeopenstorealw', '', 'request');
		$ptimeopenstorefh = VikRequest::getString('timeopenstorefh', '', 'request');
		$ptimeopenstorefm = VikRequest::getString('timeopenstorefm', '', 'request');
		$ptimeopenstoreth = VikRequest::getString('timeopenstoreth', '', 'request');
		$ptimeopenstoretm = VikRequest::getString('timeopenstoretm', '', 'request');
		$phoursmorerentback = VikRequest::getString('hoursmorerentback', '', 'request');
		$phoursmoreitemavail = VikRequest::getString('hoursmoreitemavail', '', 'request');
		$pplacesfront = VikRequest::getString('placesfront', '', 'request');
		$pdateformat = VikRequest::getString('dateformat', '', 'request');
		$ptimeformat = VikRequest::getString('timeformat', '', 'request');
		$pshowcategories = VikRequest::getString('showcategories', '', 'request');
		$ptokenform = VikRequest::getString('tokenform', '', 'request');
		$padminemail = VikRequest::getString('adminemail', '', 'request');
		$psenderemail = VikRequest::getString('senderemail', '', 'request');
		$pminuteslock = VikRequest::getString('minuteslock', '', 'request');
		$pfooterordmail = VikRequest::getString('footerordmail', '', 'request', VIKREQUEST_ALLOWHTML);
		$prequirelogin = VikRequest::getString('requirelogin', '', 'request');
		$pusefa = VikRequest::getInt('usefa', '', 'request');
		$pusefa = $pusefa > 0 ? 1 : 0;
		$ploadjquery = VikRequest::getString('loadjquery', '', 'request');
		$ploadjquery = $ploadjquery == "yes" ? "1" : "0";
		$pcalendar = VikRequest::getString('calendar', '', 'request');
		$pcalendar = $pcalendar == "joomla" ? "joomla" : "jqueryui";
		$pehourschbasp = VikRequest::getString('ehourschbasp', '', 'request');
		$pehourschbasp = $pehourschbasp == "1" ? 1 : 0;
		$penablecoupons = VikRequest::getString('enablecoupons', '', 'request');
		$penablecoupons = $penablecoupons == "1" ? 1 : 0;
		$penablepin = VikRequest::getInt('enablepin', 0, 'request');
		$penablepin = $penablepin > 0 ? 1 : 0;
		$ptodaybookings = VikRequest::getInt('todaybookings', '', 'request');
		$ptodaybookings = $ptodaybookings === 1 ? 1 : 0;
		$ppickondrop = VikRequest::getInt('pickondrop', '', 'request');
		$ppickondrop = $ppickondrop === 1 ? 1 : 0;
		$picalkey = VikRequest::getString('icalkey', '', 'request');
		$pforcepickupt = VikRequest::getString('forcepickupt', '', 'request');
		$pforcepickupth = VikRequest::getString('forcepickupth', '', 'request');
		$pforcepickuptm = VikRequest::getString('forcepickuptm', '', 'request');
		$pforcedropofft = VikRequest::getString('forcedropofft', '', 'request');
		$pforcedropoffth = VikRequest::getString('forcedropoffth', '', 'request');
		$pforcedropofftm = VikRequest::getString('forcedropofftm', '', 'request');
		$pglobalclosingdays = VikRequest::getVar('globalclosingdays', array(0));
		$globalclosingdaystr = '';
		if (is_array($pglobalclosingdays) && count($pglobalclosingdays) > 0 && !empty($pglobalclosingdays[0])) {
			foreach ($pglobalclosingdays as $globcday) {
				$cdayparts = explode(':', $globcday);
				$cdaydate = strtotime(trim($cdayparts[0]));
				if ($cdaydate && !empty($cdaydate) && in_array($cdayparts[1], array('1', '2'))) {
					$globalclosingdaystr .= $cdaydate.':'.$cdayparts[1].';';
				}
			}
		}
		$pvrisef = VikRequest::getInt('vrisef', '', 'request');
		$vrisef = file_exists(VRI_SITE_PATH.DS.'router.php');
		if ($pvrisef === 1) {
			if (!$vrisef) {
				rename(VRI_SITE_PATH.DS.'_router.php', VRI_SITE_PATH.DS.'router.php');
			}
		} else {
			if ($vrisef) {
				rename(VRI_SITE_PATH.DS.'router.php', VRI_SITE_PATH.DS.'_router.php');
			}
		}
		$pmultilang = VikRequest::getString('multilang', '', 'request');
		$pmultilang = $pmultilang == "1" ? 1 : 0;
		$psetdropdplus = VikRequest::getString('setdropdplus', '', 'request');
		$psetdropdplus = !empty($psetdropdplus) ? intval($psetdropdplus) : '';
		$pmindaysadvance = VikRequest::getInt('mindaysadvance', '', 'request');
		$pmindaysadvance = $pmindaysadvance < 0 ? 0 : $pmindaysadvance;
		$pmaxdate = VikRequest::getString('maxdate', '', 'request');
		$pmaxdate = intval($pmaxdate) < 1 ? 2 : $pmaxdate;
		$pmaxdateinterval = VikRequest::getString('maxdateinterval', '', 'request');
		$pmaxdateinterval = !in_array($pmaxdateinterval, array('d', 'w', 'm', 'y')) ? 'y' : $pmaxdateinterval;
		$maxdate_str = '+'.$pmaxdate.$pmaxdateinterval;
		$picon="";
		if (intval($_FILES['sitelogo']['error']) == 0 && trim($_FILES['sitelogo']['name'])!="") {
			jimport('joomla.filesystem.file');
			if (@is_uploaded_file($_FILES['sitelogo']['tmp_name'])) {
				$safename=JFile::makeSafe(str_replace(" ", "_", strtolower($_FILES['sitelogo']['name'])));
				if (file_exists(VRI_ADMIN_PATH.DS.'resources'.DS.$safename)) {
					$j=1;
					while (file_exists(VRI_ADMIN_PATH.DS.'resources'.DS.$j.$safename)) {
						$j++;
					}
					$pwhere=VRI_ADMIN_PATH.DS.'resources'.DS.$j.$safename;
				} else {
					$j="";
					$pwhere=VRI_ADMIN_PATH.DS.'resources'.DS.$safename;
				}
				@move_uploaded_file($_FILES['sitelogo']['tmp_name'], $pwhere);
				if (!getimagesize($pwhere)){
					@unlink($pwhere);
					$picon="";
				} else {
					@chmod($pwhere, 0644);
					$picon=$j.$safename;
				}
			}
			if (!empty($picon)) {
				$q = "UPDATE `#__vikrentitems_config` SET `setting`=".$dbo->quote($picon)." WHERE `param`='sitelogo';";
				$dbo->setQuery($q);
				$dbo->execute();
			}
		}
		if (empty($pallowrent) || $pallowrent!="1") {
			$q = "UPDATE `#__vikrentitems_config` SET `setting`='0' WHERE `param`='allowrent';";
		} else {
			$q = "UPDATE `#__vikrentitems_config` SET `setting`='1' WHERE `param`='allowrent';";
		}
		$dbo->setQuery($q);
		$dbo->execute();
		if (empty($pplacesfront) || $pplacesfront!="yes") {
			$q = "UPDATE `#__vikrentitems_config` SET `setting`='0' WHERE `param`='placesfront';";
		} else {
			$q = "UPDATE `#__vikrentitems_config` SET `setting`='1' WHERE `param`='placesfront';";
		}
		$dbo->setQuery($q);
		$dbo->execute();
		$session->set('showPlacesFront', '');
		if (empty($pshowcategories) || $pshowcategories!="yes") {
			$q = "UPDATE `#__vikrentitems_config` SET `setting`='0' WHERE `param`='showcategories';";
		} else {
			$q = "UPDATE `#__vikrentitems_config` SET `setting`='1' WHERE `param`='showcategories';";
		}
		$dbo->setQuery($q);
		$dbo->execute();
		$session->set('showCategoriesFront', '');
		if (empty($ptokenform) || $ptokenform!="yes") {
			$q = "UPDATE `#__vikrentitems_config` SET `setting`='0' WHERE `param`='tokenform';";
		} else {
			$q = "UPDATE `#__vikrentitems_config` SET `setting`='1' WHERE `param`='tokenform';";
		}
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikrentitems_texts` SET `setting`=".$dbo->quote($pfooterordmail)." WHERE `param`='footerordmail';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikrentitems_texts` SET `setting`=".$dbo->quote($pdisabledrentmsg)." WHERE `param`='disabledrentmsg';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikrentitems_config` SET `setting`=".$dbo->quote($padminemail)." WHERE `param`='adminemail';";
		$dbo->setQuery($q);
		$dbo->execute();
		//Sender email address
		$q = "SELECT `setting` FROM `#__vikrentitems_config` WHERE `param`='senderemail' LIMIT 1;";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$q = "UPDATE `#__vikrentitems_config` SET `setting`=".$dbo->quote($psenderemail)." WHERE `param`='senderemail';";
			$dbo->setQuery($q);
			$dbo->execute();
		} else {
			$q = "INSERT INTO `#__vikrentitems_config` (`param`,`setting`) VALUES ('senderemail',".$dbo->quote($psenderemail).");";
			$dbo->setQuery($q);
			$dbo->execute();
		}
		//
		$q = "UPDATE `#__vikrentitems_config` SET `setting`='".$pmultilang."' WHERE `param`='multilang';";
		$dbo->setQuery($q);
		$dbo->execute();
		if (empty($pdateformat)) {
			$pdateformat="%d/%m/%Y";
		}
		$q = "UPDATE `#__vikrentitems_config` SET `setting`=".$dbo->quote($pdateformat)." WHERE `param`='dateformat';";
		$dbo->setQuery($q);
		$dbo->execute();
		$session->set('getDateFormat', '');
		if (empty($ptimeformat)) {
			$ptimeformat="";
		}
		$q = "UPDATE `#__vikrentitems_config` SET `setting`=".$dbo->quote($ptimeformat)." WHERE `param`='timeformat';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikrentitems_config` SET `setting`=".$dbo->quote($pminuteslock)." WHERE `param`='minuteslock';";
		$dbo->setQuery($q);
		$dbo->execute();
		if (!empty($ptimeopenstorealw)) {
			$q = "UPDATE `#__vikrentitems_config` SET `setting`='' WHERE `param`='timeopenstore';";
		} else {
			$openingh=$ptimeopenstorefh * 3600;
			$openingm=$ptimeopenstorefm * 60;
			$openingts=$openingh + $openingm;
			$closingh=$ptimeopenstoreth * 3600;
			$closingm=$ptimeopenstoretm * 60;
			$closingts=$closingh + $closingm;
			if ($closingts <= $openingts) {
				$q = "UPDATE `#__vikrentitems_config` SET `setting`='' WHERE `param`='timeopenstore';";
			} else {
				$q = "UPDATE `#__vikrentitems_config` SET `setting`='".$openingts."-".$closingts."' WHERE `param`='timeopenstore';";
			}
		}
		$dbo->setQuery($q);
		$dbo->execute();
		if (empty($pforcepickupt)) {
			$q = "UPDATE `#__vikrentitems_config` SET `setting`='' WHERE `param`='globpickupt';";
		} else {
			$tforcepick = intval($pforcepickupth) < 10 ? "0".(int)$pforcepickupth : $pforcepickupth;
			$tforcepick .= ':';
			$tforcepick .= intval($pforcepickuptm) < 10 ? "0".(int)$pforcepickuptm : $pforcepickuptm;
			$q = "UPDATE `#__vikrentitems_config` SET `setting`='".$tforcepick."' WHERE `param`='globpickupt';";
		}
		$dbo->setQuery($q);
		$dbo->execute();
		if (empty($pforcedropofft)) {
			$q = "UPDATE `#__vikrentitems_config` SET `setting`='' WHERE `param`='globdropofft';";
		} else {
			$tforcedrop = intval($pforcedropoffth) < 10 ? "0".(int)$pforcedropoffth : $pforcedropoffth;
			$tforcedrop .= ':';
			$tforcedrop .= intval($pforcedropofftm) < 10 ? "0".(int)$pforcedropofftm : $pforcedropofftm;
			$q = "UPDATE `#__vikrentitems_config` SET `setting`='".$tforcedrop."' WHERE `param`='globdropofft';";
		}
		$dbo->setQuery($q);
		$dbo->execute();
		if (!ctype_digit($phoursmorerentback)) {
			$phoursmorerentback="0";
		}
		$q = "UPDATE `#__vikrentitems_config` SET `setting`='".$phoursmorerentback."' WHERE `param`='hoursmorerentback';";
		$dbo->setQuery($q);
		$dbo->execute();
		if (!ctype_digit($phoursmoreitemavail)) {
			$phoursmoreitemavail="0";
		}
		$q = "UPDATE `#__vikrentitems_config` SET `setting`='".$phoursmoreitemavail."' WHERE `param`='hoursmoreitemavail';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikrentitems_config` SET `setting`='".$globalclosingdaystr."' WHERE `param`='globalclosingdays';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikrentitems_config` SET `setting`='".($prequirelogin == "1" ? "1" : "0")."' WHERE `param`='requirelogin';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikrentitems_config` SET `setting`='".(string)$pusefa."' WHERE `param`='usefa';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikrentitems_config` SET `setting`='".$ploadjquery."' WHERE `param`='loadjquery';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikrentitems_config` SET `setting`='".$pcalendar."' WHERE `param`='calendar';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikrentitems_config` SET `setting`='".$pehourschbasp."' WHERE `param`='ehourschbasp';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikrentitems_config` SET `setting`='".$penablecoupons."' WHERE `param`='enablecoupons';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikrentitems_config` SET `setting`='".$penablepin."' WHERE `param`='enablepin';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikrentitems_config` SET `setting`='".(string)$ptodaybookings."' WHERE `param`='todaybookings';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikrentitems_config` SET `setting`='".(string)$ppickondrop."' WHERE `param`='pickondrop';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikrentitems_config` SET `setting`='".$picalkey."' WHERE `param`='icalkey';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikrentitems_config` SET `setting`='".$psetdropdplus."' WHERE `param`='setdropdplus';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikrentitems_config` SET `setting`='".$pmindaysadvance."' WHERE `param`='mindaysadvance';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikrentitems_config` SET `setting`='".$maxdate_str."' WHERE `param`='maxdate';";
		$dbo->setQuery($q);
		$dbo->execute();
		//Google Maps API Key
		$pgmapskey = VikRequest::getString('gmapskey', '', 'request');
		$q = "SELECT * FROM `#__vikrentitems_config` WHERE `param`='gmapskey';";
		$dbo->setQuery($q);
		$dbo->Query($q);
		if ($dbo->getNumRows() > 0) {
			$q = "UPDATE `#__vikrentitems_config` SET `setting`=".$dbo->quote($pgmapskey)." WHERE `param`='gmapskey';";
			$dbo->setQuery($q);
			$dbo->Query($q);
		} else {
			$q = "INSERT INTO `#__vikrentitems_config` (`param`,`setting`) VALUES ('gmapskey', ".$dbo->quote($pgmapskey).");";
			$dbo->setQuery($q);
			$dbo->Query($q);
		}
		//
		$psendemailwhen = VikRequest::getInt('sendemailwhen', '', 'request');
		$psendemailwhen = $psendemailwhen > 1 ? 2 : 1;
		$q = "UPDATE `#__vikrentitems_config` SET `setting`=".$dbo->quote($psendemailwhen)." WHERE `param`='emailsendwhen';";
		$dbo->setQuery($q);
		$dbo->execute();
		$pfronttitle = VikRequest::getString('fronttitle', '', 'request');
		$pfronttitletag = VikRequest::getString('fronttitletag', '', 'request');
		$pfronttitletagclass = VikRequest::getString('fronttitletagclass', '', 'request');
		$psearchbtnval = VikRequest::getString('searchbtnval', '', 'request');
		$psearchbtnclass = VikRequest::getString('searchbtnclass', '', 'request');
		$pshowfooter = VikRequest::getString('showfooter', '', 'request');
		$pintromain = VikRequest::getString('intromain', '', 'request', VIKREQUEST_ALLOWHTML);
		$pclosingmain = VikRequest::getString('closingmain', '', 'request', VIKREQUEST_ALLOWHTML);
		$pcurrencyname = VikRequest::getString('currencyname', '', 'request', VIKREQUEST_ALLOWHTML);
		$pcurrencysymb = VikRequest::getString('currencysymb', '', 'request', VIKREQUEST_ALLOWHTML);
		$pcurrencycodepp = VikRequest::getString('currencycodepp', '', 'request');
		$pnumdecimals = VikRequest::getString('numdecimals', '', 'request');
		$pnumdecimals = intval($pnumdecimals);
		$pdecseparator = VikRequest::getString('decseparator', '', 'request');
		$pdecseparator = empty($pdecseparator) ? '.' : $pdecseparator;
		$pthoseparator = VikRequest::getString('thoseparator', '', 'request');
		$numberformatstr = $pnumdecimals.':'.$pdecseparator.':'.$pthoseparator;
		$pshowpartlyreserved = VikRequest::getString('showpartlyreserved', '', 'request');
		$pshowpartlyreserved = $pshowpartlyreserved == "yes" ? 1 : 0;
		$pnumcalendars = VikRequest::getInt('numcalendars', '', 'request');
		$pnumcalendars = $pnumcalendars > -1 ? $pnumcalendars : 3;
		$pthumbswidth = VikRequest::getInt('thumbswidth', '', 'request');
		$pthumbswidth = $pthumbswidth > 0 ? $pthumbswidth : 200;
		$pfirstwday = VikRequest::getString('firstwday', '', 'request');
		$pfirstwday = intval($pfirstwday) >= 0 && intval($pfirstwday) <= 6 ? $pfirstwday : '0';
		//theme
		$ptheme = VikRequest::getString('theme', '', 'request');
		if (empty($ptheme) || $ptheme == 'default') {
			$ptheme = 'default';
		} else {
			$validtheme = false;
			$themes = glob(VRI_SITE_PATH.DS.'themes'.DS.'*');
			if (count($themes) > 0) {
				$strip = VRI_SITE_PATH.DS.'themes'.DS;
				foreach ($themes as $th) {
					if (is_dir($th)) {
						$tname = str_replace($strip, '', $th);
						if ($tname == $ptheme) {
							$validtheme = true;
							break;
						}
					}
				}
			}
			if ($validtheme == false) {
				$ptheme = 'default';
			}
		}
		$q = "UPDATE `#__vikrentitems_config` SET `setting`=".$dbo->quote($ptheme)." WHERE `param`='theme';";
		$dbo->setQuery($q);
		$dbo->execute();
		//
		$q = "UPDATE `#__vikrentitems_config` SET `setting`=".$dbo->quote($pshowpartlyreserved)." WHERE `param`='showpartlyreserved';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikrentitems_config` SET `setting`=".$dbo->quote($pnumcalendars)." WHERE `param`='numcalendars';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikrentitems_config` SET `setting`=".$dbo->quote($pthumbswidth)." WHERE `param`='thumbswidth';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikrentitems_config` SET `setting`='".$pfirstwday."' WHERE `param`='firstwday';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikrentitems_texts` SET `setting`=".$dbo->quote($pfronttitle)." WHERE `param`='fronttitle';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikrentitems_config` SET `setting`=".$dbo->quote($pfronttitletag)." WHERE `param`='fronttitletag';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikrentitems_config` SET `setting`=".$dbo->quote($pfronttitletagclass)." WHERE `param`='fronttitletagclass';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikrentitems_texts` SET `setting`=".$dbo->quote($psearchbtnval)." WHERE `param`='searchbtnval';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikrentitems_config` SET `setting`=".$dbo->quote($psearchbtnclass)." WHERE `param`='searchbtnclass';";
		$dbo->setQuery($q);
		$dbo->execute();
		if (empty($pshowfooter) || $pshowfooter!="yes") {
			$q = "UPDATE `#__vikrentitems_config` SET `setting`='0' WHERE `param`='showfooter';";
		} else {
			$q = "UPDATE `#__vikrentitems_config` SET `setting`='1' WHERE `param`='showfooter';";
		}
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikrentitems_texts` SET `setting`=".$dbo->quote($pintromain)." WHERE `param`='intromain';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikrentitems_texts` SET `setting`=".$dbo->quote($pclosingmain)." WHERE `param`='closingmain';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikrentitems_config` SET `setting`=".$dbo->quote($pcurrencyname)." WHERE `param`='currencyname';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikrentitems_config` SET `setting`=".$dbo->quote($pcurrencysymb)." WHERE `param`='currencysymb';";
		$dbo->setQuery($q);
		$dbo->execute();
		$session->set('getCurrencySymb', '');
		$q = "UPDATE `#__vikrentitems_config` SET `setting`=".$dbo->quote($pcurrencycodepp)." WHERE `param`='currencycodepp';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikrentitems_config` SET `setting`=".$dbo->quote($numberformatstr)." WHERE `param`='numberformat';";
		$dbo->setQuery($q);
		$dbo->execute();
		
		$pivainclusa = VikRequest::getString('ivainclusa', '', 'request');
		$pccpaypal = VikRequest::getString('ccpaypal', '', 'request');
		$ppaytotal = VikRequest::getString('paytotal', '', 'request');
		$ppayaccpercent = VikRequest::getString('payaccpercent', '', 'request');
		$ptypedeposit = VikRequest::getString('typedeposit', '', 'request');
		$ptypedeposit = $ptypedeposit == 'fixed' ? 'fixed' : 'pcent';
		$ppaymentname = VikRequest::getString('paymentname', '', 'request');
		if (empty($pivainclusa) || $pivainclusa!="yes") {
			$q = "UPDATE `#__vikrentitems_config` SET `setting`='0' WHERE `param`='ivainclusa';";
		} else {
			$q = "UPDATE `#__vikrentitems_config` SET `setting`='1' WHERE `param`='ivainclusa';";
		}
		$dbo->setQuery($q);
		$dbo->execute();
		if (empty($ppaytotal) || $ppaytotal!="yes") {
			$q = "UPDATE `#__vikrentitems_config` SET `setting`='0' WHERE `param`='paytotal';";
		} else {
			$q = "UPDATE `#__vikrentitems_config` SET `setting`='1' WHERE `param`='paytotal';";
		}
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikrentitems_config` SET `setting`=".$dbo->quote($pccpaypal)." WHERE `param`='ccpaypal';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikrentitems_texts` SET `setting`=".$dbo->quote($ppaymentname)." WHERE `param`='paymentname';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikrentitems_config` SET `setting`=".$dbo->quote($ppayaccpercent)." WHERE `param`='payaccpercent';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikrentitems_config` SET `setting`=".$dbo->quote($ptypedeposit)." WHERE `param`='typedeposit';";
		$dbo->setQuery($q);
		$dbo->execute();
		
		$psendjutility = VikRequest::getString('sendjutility', '', 'request');
		$psendpdf = VikRequest::getString('sendpdf', '', 'request');
		$pallowstats = VikRequest::getString('allowstats', '', 'request');
		$psendmailstats = VikRequest::getString('sendmailstats', '', 'request');
		$pdisclaimer = VikRequest::getString('disclaimer', '', 'request', VIKREQUEST_ALLOWHTML);
		if (empty($psendjutility) || $psendjutility!="yes") {
			$q = "UPDATE `#__vikrentitems_config` SET `setting`='0' WHERE `param`='sendjutility';";
		} else {
			$q = "UPDATE `#__vikrentitems_config` SET `setting`='1' WHERE `param`='sendjutility';";
		}
		$dbo->setQuery($q);
		$dbo->execute();
		if (empty($psendpdf) || $psendpdf!="yes") {
			$q = "UPDATE `#__vikrentitems_config` SET `setting`='0' WHERE `param`='sendpdf';";
		} else {
			$q = "UPDATE `#__vikrentitems_config` SET `setting`='1' WHERE `param`='sendpdf';";
		}
		$dbo->setQuery($q);
		$dbo->execute();
		if (empty($pallowstats) || $pallowstats!="yes") {
			$q = "UPDATE `#__vikrentitems_config` SET `setting`='0' WHERE `param`='allowstats';";
		} else {
			$q = "UPDATE `#__vikrentitems_config` SET `setting`='1' WHERE `param`='allowstats';";
		}
		$dbo->setQuery($q);
		$dbo->execute();
		if (empty($psendmailstats) || $psendmailstats!="yes") {
			$q = "UPDATE `#__vikrentitems_config` SET `setting`='0' WHERE `param`='sendmailstats';";
		} else {
			$q = "UPDATE `#__vikrentitems_config` SET `setting`='1' WHERE `param`='sendmailstats';";
		}
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikrentitems_texts` SET `setting`=".$dbo->quote($pdisclaimer)." WHERE `param`='disclaimer';";
		$dbo->setQuery($q);
		$dbo->execute();
		
		$pdeliverybaseaddress = VikRequest::getString('deliverybaseaddress', '', 'request');
		$pdeliverycalcunit = VikRequest::getString('deliverycalcunit', '', 'request');
		$pdeliverycalcunit = $pdeliverycalcunit == 'km' ? 'km' : 'miles';
		$pdeliverycostperunit = VikRequest::getFloat('deliverycostperunit', '', 'request');
		$pdeliverymaxcost = VikRequest::getFloat('deliverymaxcost', '', 'request');
		$pdeliverybaselat = VikRequest::getString('deliverybaselat', '', 'request');
		$pdeliverybaselng = VikRequest::getString('deliverybaselng', '', 'request');
		$pdeliverymaxunitdist = VikRequest::getFloat('deliverymaxunitdist', '', 'request');
		$pdeliveryrounddist = VikRequest::getString('deliveryrounddist', '', 'request');
		$pdeliveryrounddist = $pdeliveryrounddist == "1" ? "1" : "0";
		$pdeliveryroundcost = VikRequest::getString('deliveryroundcost', '', 'request');
		$pdeliveryroundcost = $pdeliveryroundcost == "1" ? "1" : "0";
		$pdeliveryperord = VikRequest::getInt('deliveryperord', 0, 'request');
		$pdeliveryperord = $pdeliveryperord > 0 ? 1 : 0;
		$pdeliveryperitunit = VikRequest::getInt('deliveryperitunit', 0, 'request');
		$pdeliveryperitunit = $pdeliveryperitunit > 0 ? 1 : 0;
		$pdeliverytaxid = VikRequest::getInt('deliverytaxid', 0, 'request');
		$pdeliverymapnotes = VikRequest::getString('deliverymapnotes', '', 'request', VIKREQUEST_ALLOWHTML);
		$q = "UPDATE `#__vikrentitems_config` SET `setting`=".$dbo->quote($pdeliverybaseaddress)." WHERE `param`='deliverybaseaddress';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikrentitems_config` SET `setting`=".$dbo->quote($pdeliverycalcunit)." WHERE `param`='deliverycalcunit';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikrentitems_config` SET `setting`=".$dbo->quote($pdeliverycostperunit)." WHERE `param`='deliverycostperunit';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikrentitems_config` SET `setting`=".$dbo->quote($pdeliverymaxcost)." WHERE `param`='deliverymaxcost';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikrentitems_config` SET `setting`=".$dbo->quote($pdeliverybaselat)." WHERE `param`='deliverybaselat';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikrentitems_config` SET `setting`=".$dbo->quote($pdeliverybaselng)." WHERE `param`='deliverybaselng';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikrentitems_config` SET `setting`=".$dbo->quote($pdeliverymaxunitdist)." WHERE `param`='deliverymaxunitdist';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikrentitems_config` SET `setting`='".$pdeliveryrounddist."' WHERE `param`='deliveryrounddist';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikrentitems_config` SET `setting`='".$pdeliveryroundcost."' WHERE `param`='deliveryroundcost';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikrentitems_config` SET `setting`='".$pdeliveryperord."' WHERE `param`='deliveryperord';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikrentitems_config` SET `setting`='".$pdeliveryperitunit."' WHERE `param`='deliveryperitunit';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikrentitems_config` SET `setting`='".$pdeliverytaxid."' WHERE `param`='deliverytaxid';";
		$dbo->setQuery($q);
		$dbo->execute();
		$q = "UPDATE `#__vikrentitems_config` SET `setting`=".$dbo->quote($pdeliverymapnotes)." WHERE `param`='deliverymapnotes';";
		$dbo->setQuery($q);
		$dbo->execute();
		
		$mainframe = JFactory::getApplication();
		$mainframe->enqueueMessage(JText::_('VRSETTINGSAVED'));
		$mainframe->redirect("index.php?option=com_vikrentitems&task=config");
	}

	function renewsession() {
		$dbo = JFactory::getDBO();
		$q = "TRUNCATE TABLE `#__session`;";
		$dbo->setQuery($q);
		$dbo->execute();
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikrentitems&task=config");
	}

	function locfees() {
		VikRentItemsHelper::printHeader("12");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'locfees'));
	
		parent::display();

		if (VikRentItems::showFooter()) {
			VikRentItemsHelper::printFooter();
		}
	}

	function newlocfee() {
		VikRentItemsHelper::printHeader("12");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'managelocfee'));
	
		parent::display();

		if (VikRentItems::showFooter()) {
			VikRentItemsHelper::printFooter();
		}
	}

	function editlocfee() {
		VikRentItemsHelper::printHeader("12");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'managelocfee'));
	
		parent::display();

		if (VikRentItems::showFooter()) {
			VikRentItemsHelper::printFooter();
		}
	}

	function createlocfee() {
		$dbo = JFactory::getDBO();
		$mainframe = JFactory::getApplication();
		$pfrom = VikRequest::getInt('from', '', 'request');
		$pto = VikRequest::getInt('to', '', 'request');
		$pcost = VikRequest::getString('cost', '', 'request');
		$pdaily = VikRequest::getString('daily', '', 'request');
		$paliq = VikRequest::getInt('aliq', '', 'request');
		$pinvert = VikRequest::getString('invert', '', 'request');
		$pinvert = $pinvert == "1" ? 1 : 0;
		$pnightsoverrides = VikRequest::getVar('nightsoverrides', array());
		$pvaluesoverrides = VikRequest::getVar('valuesoverrides', array());
		if (!empty($pfrom) && !empty($pto)) {
			$losverridestr = "";
			if (count($pnightsoverrides) > 0 && count($pvaluesoverrides) > 0) {
				foreach ($pnightsoverrides as $ko => $no) {
					if (!empty($no) && strlen(trim($pvaluesoverrides[$ko])) > 0) {
						$losverridestr .= $no.':'.trim($pvaluesoverrides[$ko]).'_';
					}
				}
			}
			$q = "INSERT INTO `#__vikrentitems_locfees` (`from`,`to`,`daily`,`cost`,`idiva`,`invert`,`losoverride`) VALUES(".$dbo->quote($pfrom).", ".$dbo->quote($pto).", '".(intval($pdaily) == 1 ? "1" : "0")."', ".$dbo->quote($pcost).", ".$dbo->quote($paliq).", '".$pinvert."', '".$losverridestr."');";
			$dbo->setQuery($q);
			$dbo->execute();
			$mainframe->enqueueMessage(JText::_('VRLOCFEESAVED'));
		}
		$mainframe->redirect("index.php?option=com_vikrentitems&task=locfees");
	}

	function updatelocfee() {
		$dbo = JFactory::getDBO();
		$mainframe = JFactory::getApplication();
		$pwhere = VikRequest::getString('where', '', 'request');
		$pfrom = VikRequest::getInt('from', '', 'request');
		$pto = VikRequest::getInt('to', '', 'request');
		$pcost = VikRequest::getString('cost', '', 'request');
		$pdaily = VikRequest::getString('daily', '', 'request');
		$paliq = VikRequest::getInt('aliq', '', 'request');
		$pinvert = VikRequest::getString('invert', '', 'request');
		$pinvert = $pinvert == "1" ? 1 : 0;
		$pnightsoverrides = VikRequest::getVar('nightsoverrides', array());
		$pvaluesoverrides = VikRequest::getVar('valuesoverrides', array());
		if (!empty($pwhere) && !empty($pfrom) && !empty($pto)) {
			$losverridestr = "";
			if (count($pnightsoverrides) > 0 && count($pvaluesoverrides) > 0) {
				foreach ($pnightsoverrides as $ko => $no) {
					if (!empty($no) && strlen(trim($pvaluesoverrides[$ko])) > 0) {
						$losverridestr .= $no.':'.trim($pvaluesoverrides[$ko]).'_';
					}
				}
			}
			$q = "UPDATE `#__vikrentitems_locfees` SET `from`=".$dbo->quote($pfrom).",`to`=".$dbo->quote($pto).",`daily`='".(intval($pdaily) == 1 ? "1" : "0")."',`cost`=".$dbo->quote($pcost).",`idiva`=".$dbo->quote($paliq).",`invert`='".$pinvert."',`losoverride`='".$losverridestr."' WHERE `id`=".$dbo->quote($pwhere).";";
			$dbo->setQuery($q);
			$dbo->execute();
			$mainframe->enqueueMessage(JText::_('VRLOCFEEUPDATE'));
		}
		$mainframe->redirect("index.php?option=com_vikrentitems&task=locfees");
	}

	function removelocfee() {
		$ids = VikRequest::getVar('cid', array(0));
		if (@count($ids)) {
			$dbo = JFactory::getDBO();
			foreach ($ids as $d) {
				$q = "DELETE FROM `#__vikrentitems_locfees` WHERE `id`=".$dbo->quote($d).";";
				$dbo->setQuery($q);
				$dbo->execute();
			}
		}
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikrentitems&task=locfees");
	}

	function cancellocfee() {
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikrentitems&task=locfees");
	}

	function seasons() {
		VikRentItemsHelper::printHeader("13");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'seasons'));
	
		parent::display();

		if (VikRentItems::showFooter()) {
			VikRentItemsHelper::printFooter();
		}
	}

	function newseason() {
		VikRentItemsHelper::printHeader("13");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'manageseason'));
	
		parent::display();

		if (VikRentItems::showFooter()) {
			VikRentItemsHelper::printFooter();
		}
	}

	function editseason() {
		VikRentItemsHelper::printHeader("13");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'manageseason'));
	
		parent::display();

		if (VikRentItems::showFooter()) {
			VikRentItemsHelper::printFooter();
		}
	}

	function createseason() {
		$mainframe = JFactory::getApplication();
		$pfrom = VikRequest::getString('from', '', 'request');
		$pto = VikRequest::getString('to', '', 'request');
		$ptype = VikRequest::getString('type', '', 'request');
		$pdiffcost = VikRequest::getString('diffcost', '', 'request');
		$pidlocation = VikRequest::getInt('idlocation', '', 'request');
		$piditems = VikRequest::getVar('iditems', array(0));
		$pidprices = VikRequest::getVar('idprices', array(0));
		$pwdays = VikRequest::getVar('wdays', array());
		$pspname = VikRequest::getString('spname', '', 'request');
		$ppickupincl = VikRequest::getString('pickupincl', '', 'request');
		$ppickupincl = $ppickupincl == 1 ? 1 : 0;
		$pkeepfirstdayrate = VikRequest::getString('keepfirstdayrate', '', 'request');
		$pkeepfirstdayrate = $pkeepfirstdayrate == 1 ? 1 : 0;
		$pval_pcent = VikRequest::getString('val_pcent', '', 'request');
		$pval_pcent = $pval_pcent == "1" ? 1 : 2;
		$proundmode = VikRequest::getString('roundmode', '', 'request');
		$proundmode = (!empty($proundmode) && in_array($proundmode, array('PHP_ROUND_HALF_UP', 'PHP_ROUND_HALF_DOWN')) ? $proundmode : '');
		$pyeartied = VikRequest::getString('yeartied', '', 'request');
		$pyeartied = $pyeartied == "1" ? 1 : 0;
		$tieyear = 0;
		$ppromo = VikRequest::getInt('promo', '', 'request');
		$ppromodaysadv = VikRequest::getInt('promodaysadv', '', 'request');
		$ppromotxt = VikRequest::getString('promotxt', '', 'request', VIKREQUEST_ALLOWHTML);
		$pnightsoverrides = VikRequest::getVar('nightsoverrides', array());
		$pvaluesoverrides = VikRequest::getVar('valuesoverrides', array());
		$pandmoreoverride = VikRequest::getVar('andmoreoverride', array());
		$dbo = JFactory::getDBO();
		if ((!empty($pfrom) && !empty($pto)) || count($pwdays) > 0) {
			$skipseason = false;
			if (empty($pfrom) || empty($pto)) {
				$skipseason = true;
			}
			$skipdays = false;
			$wdaystr = null;
			if (count($pwdays) == 0) {
				$skipdays = true;
			} else {
				$wdaystr = "";
				foreach ($pwdays as $wd) {
					$wdaystr .= $wd.';';
				}
			}
			$itemstr="";
			if (@count($piditems) > 0) {
				foreach ($piditems as $it) {
					$itemstr.="-".$it."-,";
				}
			}
			$pricestr="";
			if (@count($pidprices) > 0) {
				foreach ($pidprices as $price) {
					if (empty($price)) {
						continue;
					}
					$pricestr.="-".$price."-,";
				}
			}
			$valid = true;
			$sfrom = null;
			$sto = null;
			if (!$skipseason) {
				$first = VikRentItems::getDateTimestamp($pfrom, 0, 0);
				$second = VikRentItems::getDateTimestamp($pto, 0, 0);
				if ($second > 0 && $second == $first) {
					$second += 86399;
				}
				if ($second > $first) {
					$baseone = getdate($first);
					$basets = mktime(0, 0, 0, 1, 1, $baseone['year']);
					$sfrom = $baseone[0] - $basets;
					$basetwo = getdate($second);
					$basets = mktime(0, 0, 0, 1, 1, $basetwo['year']);
					$sto = $basetwo[0] - $basets;
					//check leap year
					if ($baseone['year'] % 4 == 0 && ($baseone['year'] % 100 != 0 || $baseone['year'] % 400 == 0)) {
						$leapts = mktime(0, 0, 0, 2, 29, $baseone['year']);
						if ($baseone[0] >= $leapts) {
							$sfrom -= 86400;
							$sto -= 86400;
						}
					}
					//end leap year
					//tied to the year
					if ($pyeartied == 1) {
						$tieyear = $baseone['year'];
					}
					//
					//check if seasons dates are valid
					$q = "SELECT `id` FROM `#__vikrentitems_seasons` WHERE `from`<=".$dbo->quote($sfrom)." AND `to`>=".$dbo->quote($sfrom)." AND `iditems`=".$dbo->quote($itemstr)." AND `locations`=".$dbo->quote($pidlocation)."".(!$skipdays ? " AND `wdays`='".$wdaystr."'" : "").($skipdays ? " AND (`from` > 0 OR `to` > 0) AND `wdays`=''" : "").($pyeartied == 1 ? " AND `year`=".$tieyear : " AND `year` IS NULL")." AND `idprices`=".$dbo->quote($pricestr).";";
					$dbo->setQuery($q);
					$dbo->execute();
					$totfirst = $dbo->getNumRows();
					if ($totfirst > 0) {
						$valid=false;
					}
					$q = "SELECT `id` FROM `#__vikrentitems_seasons` WHERE `from`<=".$dbo->quote($sto)." AND `to`>=".$dbo->quote($sto)." AND `iditems`=".$dbo->quote($itemstr)." AND `locations`=".$dbo->quote($pidlocation)."".(!$skipdays ? " AND `wdays`='".$wdaystr."'" : "").($skipdays ? " AND (`from` > 0 OR `to` > 0) AND `wdays`=''" : "").($pyeartied == 1 ? " AND `year`=".$tieyear : " AND `year` IS NULL")." AND `idprices`=".$dbo->quote($pricestr).";";
					$dbo->setQuery($q);
					$dbo->execute();
					$totsecond=@$dbo->getNumRows();
					if ($totsecond > 0) {
						$valid=false;
					}
					$q = "SELECT `id` FROM `#__vikrentitems_seasons` WHERE `from`>=".$dbo->quote($sfrom)." AND `from`<=".$dbo->quote($sto)." AND `to`>=".$dbo->quote($sfrom)." AND `to`<=".$dbo->quote($sto)." AND `iditems`=".$dbo->quote($itemstr)." AND `locations`=".$dbo->quote($pidlocation)."".(!$skipdays ? " AND `wdays`='".$wdaystr."'" : "").($skipdays ? " AND (`from` > 0 OR `to` > 0) AND `wdays`=''" : "").($pyeartied == 1 ? " AND `year`=".$tieyear : " AND `year` IS NULL")." AND `idprices`=".$dbo->quote($pricestr).";";
					$dbo->setQuery($q);
					$dbo->execute();
					$totthird = $dbo->getNumRows();
					if ($totthird > 0) {
						$valid=false;
					}
					//
				} else {
					VikError::raiseWarning('', JText::_('ERRINVDATESEASON'));
					$mainframe->redirect("index.php?option=com_vikrentitems&task=newseason");
				}
			}
			if ($valid) {
				$losverridestr = "";
				if (count($pnightsoverrides) > 0 && count($pvaluesoverrides) > 0) {
					foreach ($pnightsoverrides as $ko => $no) {
						if (!empty($no) && strlen(trim($pvaluesoverrides[$ko])) > 0) {
							$infiniteclause = intval($pandmoreoverride[$ko]) == 1 ? '-i' : '';
							$losverridestr .= intval($no).$infiniteclause.':'.trim($pvaluesoverrides[$ko]).'_';
						}
					}
				}
				$q = "INSERT INTO `#__vikrentitems_seasons` (`type`,`from`,`to`,`diffcost`,`iditems`,`locations`,`spname`,`wdays`,`pickupincl`,`val_pcent`,`losoverride`,`keepfirstdayrate`,`roundmode`,`year`,`idprices`,`promo`,`promodaysadv`,`promotxt`) VALUES('".($ptype == "1" ? "1" : "2")."', ".$dbo->quote($sfrom).", ".$dbo->quote($sto).", ".$dbo->quote($pdiffcost).", ".$dbo->quote($itemstr).", ".$dbo->quote($pidlocation).", ".$dbo->quote($pspname).", ".$dbo->quote($wdaystr).", '".$ppickupincl."', '".$pval_pcent."', ".$dbo->quote($losverridestr).", '".$pkeepfirstdayrate."', ".(!empty($proundmode) ? "'".$proundmode."'" : "null").", ".($pyeartied == 1 ? $tieyear : "NULL").", ".$dbo->quote($pricestr).", ".($ppromo == 1 ? '1' : '0').", ".(!empty($ppromodaysadv) ? $ppromodaysadv : "null").", ".$dbo->quote($ppromotxt).");";
				$dbo->setQuery($q);
				$dbo->execute();
				$mainframe->enqueueMessage(JText::_('VRSEASONSAVED'));
				$mainframe->redirect("index.php?option=com_vikrentitems&task=seasons");
			} else {
				VikError::raiseWarning('', JText::_('ERRINVDATEITEMSLOCSEASON'));
				$mainframe->redirect("index.php?option=com_vikrentitems&task=newseason");
			}
		} else {
			$mainframe->redirect("index.php?option=com_vikrentitems&task=newseason");
		}
	}

	function updateseason() {
		$mainframe = JFactory::getApplication();
		$pwhere = VikRequest::getString('where', '', 'request');
		$pfrom = VikRequest::getString('from', '', 'request');
		$pto = VikRequest::getString('to', '', 'request');
		$ptype = VikRequest::getString('type', '', 'request');
		$pdiffcost = VikRequest::getString('diffcost', '', 'request');
		$pidlocation = VikRequest::getInt('idlocation', '', 'request');
		$piditems = VikRequest::getVar('iditems', array(0));
		$pidprices = VikRequest::getVar('idprices', array(0));
		$pwdays = VikRequest::getVar('wdays', array());
		$pspname = VikRequest::getString('spname', '', 'request');
		$ppickupincl = VikRequest::getString('pickupincl', '', 'request');
		$ppickupincl = $ppickupincl == 1 ? 1 : 0;
		$pkeepfirstdayrate = VikRequest::getString('keepfirstdayrate', '', 'request');
		$pkeepfirstdayrate = $pkeepfirstdayrate == 1 ? 1 : 0;
		$pval_pcent = VikRequest::getString('val_pcent', '', 'request');
		$pval_pcent = $pval_pcent == "1" ? 1 : 2;
		$proundmode = VikRequest::getString('roundmode', '', 'request');
		$proundmode = (!empty($proundmode) && in_array($proundmode, array('PHP_ROUND_HALF_UP', 'PHP_ROUND_HALF_DOWN')) ? $proundmode : '');
		$pyeartied = VikRequest::getString('yeartied', '', 'request');
		$pyeartied = $pyeartied == "1" ? 1 : 0;
		$tieyear = 0;
		$ppromo = VikRequest::getInt('promo', '', 'request');
		$ppromo = $ppromo == 1 ? 1 : 0;
		$ppromodaysadv = VikRequest::getInt('promodaysadv', '', 'request');
		$ppromotxt = VikRequest::getString('promotxt', '', 'request', VIKREQUEST_ALLOWHTML);
		$pnightsoverrides = VikRequest::getVar('nightsoverrides', array());
		$pvaluesoverrides = VikRequest::getVar('valuesoverrides', array());
		$pandmoreoverride = VikRequest::getVar('andmoreoverride', array());
		$dbo = JFactory::getDBO();
		if ((!empty($pfrom) && !empty($pto)) || count($pwdays) > 0) {
			$skipseason = false;
			if (empty($pfrom) || empty($pto)) {
				$skipseason = true;
			}
			$skipdays = false;
			$wdaystr = null;
			if (count($pwdays) == 0) {
				$skipdays = true;
			} else {
				$wdaystr = "";
				foreach ($pwdays as $wd) {
					$wdaystr .= $wd.';';
				}
			}
			$itemstr="";
			if (@count($piditems) > 0) {
				foreach ($piditems as $it) {
					$itemstr.="-".$it."-,";
				}
			}
			$pricestr="";
			if (@count($pidprices) > 0) {
				foreach ($pidprices as $price) {
					if (empty($price)) {
						continue;
					}
					$pricestr.="-".$price."-,";
				}
			}
			$valid = true;
			$sfrom = null;
			$sto = null;
			if (!$skipseason) {
				$first = VikRentItems::getDateTimestamp($pfrom, 0, 0);
				$second = VikRentItems::getDateTimestamp($pto, 0, 0);
				if ($second > 0 && $second == $first) {
					$second += 86399;
				}
				if ($second > $first) {
					$baseone = getdate($first);
					$basets = mktime(0, 0, 0, 1, 1, $baseone['year']);
					$sfrom = $baseone[0] - $basets;
					$basetwo = getdate($second);
					$basets = mktime(0, 0, 0, 1, 1, $basetwo['year']);
					$sto = $basetwo[0] - $basets;
					//check leap year
					if ($baseone['year'] % 4 == 0 && ($baseone['year'] % 100 != 0 || $baseone['year'] % 400 == 0)) {
						$leapts = mktime(0, 0, 0, 2, 29, $baseone['year']);
						if ($baseone[0] >= $leapts) {
							$sfrom -= 86400;
							$sto -= 86400;
						}
					}
					//end leap year
					//tied to the year
					if ($pyeartied == 1) {
						$tieyear = $baseone['year'];
					}
					//
					//check if seasons dates are valid
					$q = "SELECT `id` FROM `#__vikrentitems_seasons` WHERE `from`<=".$dbo->quote($sfrom)." AND `to`>=".$dbo->quote($sfrom)." AND `id`!=".$dbo->quote($pwhere)." AND `iditems`=".$dbo->quote($itemstr)." AND `locations`=".$dbo->quote($pidlocation)."".(!$skipdays ? " AND `wdays`='".$wdaystr."'" : "").($skipdays ? " AND (`from` > 0 OR `to` > 0) AND `wdays`=''" : "").($pyeartied == 1 ? " AND `year`=".$tieyear : " AND `year` IS NULL")." AND `idprices`=".$dbo->quote($pricestr).";";
					$dbo->setQuery($q);
					$dbo->execute();
					$totfirst=@$dbo->getNumRows();
					if ($totfirst > 0) {
						$valid=false;
					}
					$q = "SELECT `id` FROM `#__vikrentitems_seasons` WHERE `from`<=".$dbo->quote($sto)." AND `to`>=".$dbo->quote($sto)." AND `id`!=".$dbo->quote($pwhere)." AND `iditems`=".$dbo->quote($itemstr)." AND `locations`=".$dbo->quote($pidlocation)."".(!$skipdays ? " AND `wdays`='".$wdaystr."'" : "").($skipdays ? " AND (`from` > 0 OR `to` > 0) AND `wdays`=''" : "").($pyeartied == 1 ? " AND `year`=".$tieyear : " AND `year` IS NULL")." AND `idprices`=".$dbo->quote($pricestr).";";
					$dbo->setQuery($q);
					$dbo->execute();
					$totsecond=@$dbo->getNumRows();
					if ($totsecond > 0) {
						$valid=false;
					}
					$q = "SELECT `id` FROM `#__vikrentitems_seasons` WHERE `from`>=".$dbo->quote($sfrom)." AND `from`<=".$dbo->quote($sto)." AND `to`>=".$dbo->quote($sfrom)." AND `to`<=".$dbo->quote($sto)." AND `id`!=".$dbo->quote($pwhere)." AND `iditems`=".$dbo->quote($itemstr)." AND `locations`=".$dbo->quote($pidlocation)."".(!$skipdays ? " AND `wdays`='".$wdaystr."'" : "").($skipdays ? " AND (`from` > 0 OR `to` > 0) AND `wdays`=''" : "").($pyeartied == 1 ? " AND `year`=".$tieyear : " AND `year` IS NULL")." AND `idprices`=".$dbo->quote($pricestr).";";
					$dbo->setQuery($q);
					$dbo->execute();
					$totthird=@$dbo->getNumRows();
					if ($totthird > 0) {
						$valid=false;
					}
					//
				} else {
					VikError::raiseWarning('', JText::_('ERRINVDATESEASON'));
					$mainframe->redirect("index.php?option=com_vikrentitems&task=editseason&cid[]=".$pwhere);
				}
			}
			if ($valid) {
				$losverridestr = "";
				if (count($pnightsoverrides) > 0 && count($pvaluesoverrides) > 0) {
					foreach ($pnightsoverrides as $ko => $no) {
						if (!empty($no) && strlen(trim($pvaluesoverrides[$ko])) > 0) {
							$infiniteclause = intval($pandmoreoverride[$ko]) == 1 ? '-i' : '';
							$losverridestr .= intval($no).$infiniteclause.':'.trim($pvaluesoverrides[$ko]).'_';
						}
					}
				}
				$q = "UPDATE `#__vikrentitems_seasons` SET `type`='".($ptype == "1" ? "1" : "2")."',`from`=".$dbo->quote($sfrom).",`to`=".$dbo->quote($sto).",`diffcost`=".$dbo->quote($pdiffcost).",`iditems`=".$dbo->quote($itemstr).",`locations`=".$dbo->quote($pidlocation).",`spname`=".$dbo->quote($pspname).",`wdays`='".$wdaystr."',`pickupincl`='".$ppickupincl."',`val_pcent`='".$pval_pcent."',`losoverride`=".$dbo->quote($losverridestr).",`keepfirstdayrate`='".$pkeepfirstdayrate."',`roundmode`=".(!empty($proundmode) ? "'".$proundmode."'" : "null").",`year`=".($pyeartied == 1 ? $tieyear : "NULL").",`idprices`=".$dbo->quote($pricestr).",`promo`=".$ppromo.",`promodaysadv`=".(!empty($ppromodaysadv) ? $ppromodaysadv : "null").",`promotxt`=".$dbo->quote($ppromotxt)." WHERE `id`=".$dbo->quote($pwhere).";";
				$dbo->setQuery($q);
				$dbo->execute();
				$mainframe->enqueueMessage(JText::_('VRSEASONUPDATED'));
				$mainframe->redirect("index.php?option=com_vikrentitems&task=seasons");
			} else {
				VikError::raiseWarning('', JText::_('ERRINVDATEITEMSLOCSEASON'));
				$mainframe->redirect("index.php?option=com_vikrentitems&task=editseason&cid[]=".$pwhere);
			}
		} else {
			$mainframe->redirect("index.php?option=com_vikrentitems&task=editseason&cid[]=".$pwhere);
		}
	}

	function removeseasons() {
		$ids = VikRequest::getVar('cid', array(0));
		if (@count($ids)) {
			$dbo = JFactory::getDBO();
			foreach ($ids as $d) {
				$q = "DELETE FROM `#__vikrentitems_seasons` WHERE `id`=".$dbo->quote($d).";";
				$dbo->setQuery($q);
				$dbo->execute();
			}
		}
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikrentitems&task=seasons");
	}

	function cancelseason() {
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikrentitems&task=seasons");
	}

	function payments() {
		VikRentItemsHelper::printHeader("14");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'payments'));
	
		parent::display();

		if (VikRentItems::showFooter()) {
			VikRentItemsHelper::printFooter();
		}
	}

	function newpayment() {
		VikRentItemsHelper::printHeader("14");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'managepayment'));
	
		parent::display();

		if (VikRentItems::showFooter()) {
			VikRentItemsHelper::printFooter();
		}
	}

	function editpayment() {
		VikRentItemsHelper::printHeader("14");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'managepayment'));
	
		parent::display();

		if (VikRentItems::showFooter()) {
			VikRentItemsHelper::printFooter();
		}
	}

	function createpayment() {
		$mainframe = JFactory::getApplication();
		$pname = VikRequest::getString('name', '', 'request');
		$ppayment = VikRequest::getString('payment', '', 'request');
		$ppublished = VikRequest::getString('published', '', 'request');
		$pcharge = VikRequest::getString('charge', '', 'request');
		$psetconfirmed = VikRequest::getString('setconfirmed', '', 'request');
		$pshownotealw = VikRequest::getString('shownotealw', '', 'request');
		$pnote = VikRequest::getString('note', '', 'request', VIKREQUEST_ALLOWHTML);
		$pval_pcent = VikRequest::getString('val_pcent', '', 'request');
		$pval_pcent = !in_array($pval_pcent, array('1', '2')) ? 1 : $pval_pcent;
		$pch_disc = VikRequest::getString('ch_disc', '', 'request');
		$pch_disc = !in_array($pch_disc, array('1', '2')) ? 1 : $pch_disc;
		$vikpaymentparams = VikRequest::getVar('vikpaymentparams', array(0));
		$payparamarr = array();
		$payparamstr = '';
		if (count($vikpaymentparams) > 0) {
			foreach ($vikpaymentparams as $setting => $cont) {
				if (strlen($setting) > 0) {
					$payparamarr[$setting] = $cont;
				}
			}
			if (count($payparamarr) > 0) {
				$payparamstr = json_encode($payparamarr);
			}
		}
		$dbo = JFactory::getDBO();
		if (!empty($pname) && !empty($ppayment)) {
			$setpub = $ppublished == "1" ? 1 : 0;
			$psetconfirmed = $psetconfirmed == "1" ? 1 : 0;
			$pshownotealw = $pshownotealw == "1" ? 1 : 0;
			$q = "SELECT `id` FROM `#__vikrentitems_gpayments` WHERE `file`=".$dbo->quote($ppayment).";";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() >= 0) {
				$q = "INSERT INTO `#__vikrentitems_gpayments` (`name`,`file`,`published`,`note`,`charge`,`setconfirmed`,`shownotealw`,`val_pcent`,`ch_disc`,`params`) VALUES(".$dbo->quote($pname).",".$dbo->quote($ppayment).",".$dbo->quote($setpub).",".$dbo->quote($pnote).",".$dbo->quote($pcharge).",".$dbo->quote($psetconfirmed).",".$dbo->quote($pshownotealw).",'".$pval_pcent."','".$pch_disc."',".$dbo->quote($payparamstr).");";
				$dbo->setQuery($q);
				$dbo->execute();
				$mainframe->enqueueMessage(JText::_('VRPAYMENTSAVED'));
				$mainframe->redirect("index.php?option=com_vikrentitems&task=payments");
			} else {
				VikError::raiseWarning('', JText::_('ERRINVFILEPAYMENT'));
				$mainframe->redirect("index.php?option=com_vikrentitems&task=newpayment");
			}
		} else {
			$mainframe->redirect("index.php?option=com_vikrentitems&task=newpayment");
		}
	}

	function updatepayment() {
		$mainframe = JFactory::getApplication();
		$pwhere = VikRequest::getString('where', '', 'request');
		$pname = VikRequest::getString('name', '', 'request');
		$ppayment = VikRequest::getString('payment', '', 'request');
		$ppublished = VikRequest::getString('published', '', 'request');
		$pcharge = VikRequest::getString('charge', '', 'request');
		$psetconfirmed = VikRequest::getString('setconfirmed', '', 'request');
		$pshownotealw = VikRequest::getString('shownotealw', '', 'request');
		$pnote = VikRequest::getString('note', '', 'request', VIKREQUEST_ALLOWHTML);
		$pval_pcent = VikRequest::getString('val_pcent', '', 'request');
		$pval_pcent = !in_array($pval_pcent, array('1', '2')) ? 1 : $pval_pcent;
		$pch_disc = VikRequest::getString('ch_disc', '', 'request');
		$pch_disc = !in_array($pch_disc, array('1', '2')) ? 1 : $pch_disc;
		$vikpaymentparams = VikRequest::getVar('vikpaymentparams', array(0));
		$payparamarr = array();
		$payparamstr = '';
		if (count($vikpaymentparams) > 0) {
			foreach ($vikpaymentparams as $setting => $cont) {
				if (strlen($setting) > 0) {
					$payparamarr[$setting] = $cont;
				}
			}
			if (count($payparamarr) > 0) {
				$payparamstr = json_encode($payparamarr);
			}
		}
		$dbo = JFactory::getDBO();
		if (!empty($pname) && !empty($ppayment) && !empty($pwhere)) {
			$setpub = $ppublished == "1" ? 1 : 0;
			$psetconfirmed = $psetconfirmed == "1" ? 1 : 0;
			$pshownotealw = $pshownotealw == "1" ? 1 : 0;
			$q = "SELECT `id` FROM `#__vikrentitems_gpayments` WHERE `file`=".$dbo->quote($ppayment)." AND `id`!='".$pwhere."';";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() >= 0) {
				$q = "UPDATE `#__vikrentitems_gpayments` SET `name`=".$dbo->quote($pname).",`file`=".$dbo->quote($ppayment).",`published`=".$dbo->quote($setpub).",`note`=".$dbo->quote($pnote).",`charge`=".$dbo->quote($pcharge).",`setconfirmed`=".$dbo->quote($psetconfirmed).",`shownotealw`=".$dbo->quote($pshownotealw).",`val_pcent`='".$pval_pcent."',`ch_disc`='".$pch_disc."',`params`=".$dbo->quote($payparamstr)." WHERE `id`=".$dbo->quote($pwhere).";";
				$dbo->setQuery($q);
				$dbo->execute();
				$mainframe->enqueueMessage(JText::_('VRPAYMENTUPDATED'));
				$mainframe->redirect("index.php?option=com_vikrentitems&task=payments");
			} else {
				VikError::raiseWarning('', JText::_('ERRINVFILEPAYMENT'));
				$mainframe->redirect("index.php?option=com_vikrentitems&task=editpayment&cid[]=".$pwhere);
			}
		} else {
			$mainframe->redirect("index.php?option=com_vikrentitems&task=editpayment&cid[]=".$pwhere);
		}
	}

	function removepayments() {
		$ids = VikRequest::getVar('cid', array(0));
		if (@count($ids)) {
			$dbo = JFactory::getDBO();
			foreach ($ids as $d) {
				$q = "DELETE FROM `#__vikrentitems_gpayments` WHERE `id`=".$dbo->quote($d).";";
				$dbo->setQuery($q);
				$dbo->execute();
			}
		}
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikrentitems&task=payments");
	}

	function cancelpayment() {
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikrentitems&task=payments");
	}

	function setordconfirmed() {
		$dbo = JFactory::getDBO();
		$mainframe = JFactory::getApplication();
		$cid = VikRequest::getVar('cid', array(0));
		$oid = $cid[0];
		$q = "SELECT * FROM `#__vikrentitems_orders` WHERE `id`=".(int)$oid.";";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() == 1) {
			$order = $dbo->loadAssocList();
			$vri_tn = VikRentItems::getTranslator();
			//check if the language in use is the same as the one used during the checkout
			if (!empty($order[0]['lang'])) {
				$lang = JFactory::getLanguage();
				if ($lang->getTag() != $order[0]['lang']) {
					$lang->load('com_vikrentitems', JPATH_ADMINISTRATOR, $order[0]['lang'], true);
					$vri_tn::$force_tolang = $order[0]['lang'];
				}
			}
			//
			$totdelivery = $order[0]['deliverycost'];
			$checkhourscharges = 0;
			$ppickup = $order[0]['ritiro'];
			$prelease = $order[0]['consegna'];
			$secdiff = $prelease - $ppickup;
			$daysdiff = $secdiff / 86400;
			if (is_int($daysdiff)) {
				if ($daysdiff < 1) {
					$daysdiff = 1;
				}
			} else {
				if ($daysdiff < 1) {
					$daysdiff = 1;
				} else {
					$sum = floor($daysdiff) * 86400;
					$newdiff = $secdiff - $sum;
					$maxhmore = VikRentItems::getHoursMoreRb() * 3600;
					if ($maxhmore >= $newdiff) {
						$daysdiff = floor($daysdiff);
					} else {
						$daysdiff = ceil($daysdiff);
						$ehours = intval(round(($newdiff - $maxhmore) / 3600));
						$checkhourscharges = $ehours;
						if ($checkhourscharges > 0) {
							$aehourschbasp = VikRentItems::applyExtraHoursChargesBasp();
						}
					}
				}
			}
			$realback = VikRentItems::getHoursItemAvail() * 3600;
			$realback += $order[0]['consegna'];
			$isdue = 0;
			$vricart = array();
			$allbook = true;
			$notavail = array();
			$q = "SELECT `oi`.*,`i`.`name`,`i`.`units` FROM `#__vikrentitems_ordersitems` AS `oi`,`#__vikrentitems_items` AS `i` WHERE `oi`.`idorder`='".$order[0]['id']."' AND `oi`.`iditem`=`i`.`id` ORDER BY `oi`.`id` ASC;";
			$dbo->setQuery($q);
			$dbo->execute();
			$orderitems = $dbo->loadAssocList();
			$vri_tn->translateContents($orderitems, '#__vikrentitems_items', array('id' => 'iditem'));
			foreach ($orderitems as $koi => $oi) {
				if (!VikRentItems::itemBookable($oi['iditem'], $oi['units'], $order[0]['ritiro'], $order[0]['consegna'], $oi['itemquant'])) {
					$allbook = false;
					$notavail[] = $oi['name'];
				}
			}
			if (!$allbook) {
				VikError::raiseWarning('', JText::sprintf('ERRCONFORDERITEMNA', implode(", ", $notavail)));
			} else {
				$arrnewbusy = array();
				foreach ($orderitems as $koi => $oi) {
					for ($i = 1; $i <= $oi['itemquant']; $i++) {
						$q = "INSERT INTO `#__vikrentitems_busy` (`iditem`,`ritiro`,`consegna`,`realback`) VALUES('".$oi['iditem']."','".$order[0]['ritiro']."','".$order[0]['consegna']."','".$realback."');";
						$dbo->setQuery($q);
						$dbo->execute();
						$busynow = $dbo->insertid();
						$arrnewbusy[] = $busynow;
					}
					$kit_relations = VikRentItems::getKitRelatedItems($oi['iditem']);
					if (count($kit_relations)) {
						//VRI 1.5 - store busy records for the children or parent items, in case of a kit (Group/Set of Items)
						foreach ($kit_relations as $kit_rel) {
							for ($i = 1; $i <= $kit_rel['units']; $i++) {
								$q = "INSERT INTO `#__vikrentitems_busy` (`iditem`,`ritiro`,`consegna`,`realback`) VALUES(" . $dbo->quote($kit_rel['iditem']) . ", '" . $order[0]['ritiro'] . "', '" . $order[0]['consegna'] . "','" . $realback . "');";
								$dbo->setQuery($q);
								$dbo->execute();
								$busynow = $dbo->insertid();
								$arrnewbusy[] = $busynow;
							}
						}
						//
					}
				}
				foreach ($arrnewbusy as $newbusy) {
					$q = "INSERT INTO `#__vikrentitems_ordersbusy` (`idorder`,`idbusy`) VALUES('".$order[0]['id']."', '".$newbusy."');";
					$dbo->setQuery($q);
					$dbo->execute();
				}
				$q = "UPDATE `#__vikrentitems_orders` SET `status`='confirmed' WHERE `id`='".$order[0]['id']."';";
				$dbo->setQuery($q);
				$dbo->execute();
				$q = "DELETE FROM `#__vikrentitems_tmplock` WHERE `idorder`=".(int)$order[0]['id'].";";
				$dbo->setQuery($q);
				$dbo->execute();
				//send mail
				$ftitle = VikRentItems::getFrontTitle($vri_tn);
				$nowts = $order[0]['ts'];
				$viklink = JURI::root()."index.php?option=com_vikrentitems&task=vieworder&sid=".$order[0]['sid']."&ts=".$order[0]['ts'];
				$ritplace = (!empty($order[0]['idplace']) ? VikRentItems::getPlaceName($order[0]['idplace'], $vri_tn) : "");
				$consegnaplace = (!empty($order[0]['idreturnplace']) ? VikRentItems::getPlaceName($order[0]['idreturnplace'], $vri_tn) : "");
				$maillocfee = "";
				$locfeewithouttax = 0;
				if (!empty($order[0]['idplace']) && !empty($order[0]['idreturnplace'])) {
					$locfee = VikRentItems::getLocFee($order[0]['idplace'], $order[0]['idreturnplace']);
					if ($locfee) {
						//VikRentItems 1.1 - Location fees overrides
						if (strlen($locfee['losoverride']) > 0) {
							$arrvaloverrides = array();
							$valovrparts = explode('_', $locfee['losoverride']);
							foreach ($valovrparts as $valovr) {
								if (!empty($valovr)) {
									$ovrinfo = explode(':', $valovr);
									$arrvaloverrides[$ovrinfo[0]] = $ovrinfo[1];
								}
							}
							if (array_key_exists($order[0]['days'], $arrvaloverrides)) {
								$locfee['cost'] = $arrvaloverrides[$order[0]['days']];
							}
						}
						//end VikRentItems 1.1 - Location fees overrides
						$locfeecost = intval($locfee['daily']) == 1 ? ($locfee['cost'] * $order[0]['days']) : $locfee['cost'];
						$locfeewith = VikRentItems::sayLocFeePlusIva($locfeecost, $locfee['idiva'], $order[0]);
						$isdue += $locfeewith;
						$locfeewithouttax = VikRentItems::sayLocFeeMinusIva($locfeecost, $locfee['idiva'], $order[0]);
						$maillocfee = $locfeewith;
					}
				}
				foreach ($orderitems as $koi => $oi) {
					$tar = array(array(
						'id' => 0,
						'iditem' => $oi['iditem'],
						'days' => $order[0]['days'],
						'idprice' => -1,
						'cost' => 0,
						'attrdata' => '',
					));
					$is_cust_cost = (!empty($oi['cust_cost']) && $oi['cust_cost'] > 0);
					if (!empty($oi['idtar'])) {
						if ($order[0]['hourly'] == 1) {
							$q = "SELECT * FROM `#__vikrentitems_dispcosthours` WHERE `id`=".(int)$oi['idtar'].";";
						} else {
							$q = "SELECT * FROM `#__vikrentitems_dispcost` WHERE `id`=".(int)$oi['idtar'].";";
						}
						$dbo->setQuery($q);
						$dbo->execute();
						if ($dbo->getNumRows() == 0) {
							if ($order[0]['hourly'] == 1) {
								$q = "SELECT * FROM `#__vikrentitems_dispcost` WHERE `id`=".(int)$oi['idtar'].";";
								$dbo->setQuery($q);
								$dbo->execute();
								if ($dbo->getNumRows() == 1) {
									$tar = $dbo->loadAssocList();
								}
							}
						} else {
							$tar = $dbo->loadAssocList();
						}
					} elseif ($is_cust_cost) {
						//Custom Rate
						$tar = array(array(
							'id' => -1,
							'iditem' => $oi['iditem'],
							'days' => $order[0]['days'],
							'idprice' => -1,
							'cost' => $oi['cust_cost'],
							'attrdata' => '',
						));
					}
					if (count($tar) && !empty($tar[0]['id'])) {
						if ($order[0]['hourly'] == 1 && !empty($tar[0]['hours'])) {
							foreach ($tar as $kt => $vt) {
								$tar[$kt]['days'] = 1;
							}
						}
						if ($checkhourscharges > 0 && $aehourschbasp == true) {
							$ret = VikRentItems::applyExtraHoursChargesItem($tar, $oi['iditem'], $checkhourscharges, $daysdiff, false, true, true);
							$tar = $ret['return'];
							$calcdays = $ret['days'];
						}
						if ($checkhourscharges > 0 && $aehourschbasp == false) {
							$tar = VikRentItems::extraHoursSetPreviousFareItem($tar, $oi['iditem'], $checkhourscharges, $daysdiff, true);
							$tar = VikRentItems::applySeasonsItem($tar, $order[0]['ritiro'], $order[0]['consegna'], $order[0]['idplace']);
							$ret = VikRentItems::applyExtraHoursChargesItem($tar, $oi['iditem'], $checkhourscharges, $daysdiff, true, true, true);
							$tar = $ret['return'];
							$calcdays = $ret['days'];
						} else {
							$tar = VikRentItems::applySeasonsItem($tar, $order[0]['ritiro'], $order[0]['consegna'], $order[0]['idplace']);
						}
						$tar = VikRentItems::applyItemDiscounts($tar, $oi['iditem'], $oi['itemquant']);
					}
					$costplusiva = $is_cust_cost ? $tar[0]['cost'] : VikRentItems::sayCostPlusIva($tar[0]['cost'] * $oi['itemquant'], $tar[0]['idprice'], $order[0]);
					$costminusiva = $is_cust_cost ? VikRentItems::sayCustCostMinusIva($tar[0]['cost'], $oi['cust_idiva']) : VikRentItems::sayCostMinusIva($tar[0]['cost'] * $oi['itemquant'], $tar[0]['idprice'], $order[0]);
					$pricestr = ($is_cust_cost ? JText::_('VRIRENTCUSTRATEPLAN').": ".$costplusiva : VikRentItems::getPriceName($tar[0]['idprice'], $vri_tn).": ".$costplusiva.(!empty($tar[0]['attrdata']) ? "\n".VikRentItems::getPriceAttr($tar[0]['idprice'], $vri_tn).": ".$tar[0]['attrdata'] : ""));
					$isdue += $is_cust_cost ? $tar[0]['cost'] : VikRentItems::sayCostPlusIva($tar[0]['cost'] * $oi['itemquant'], $tar[0]['idprice'], $order[0]);
					$optstr = "";
					$optarrtaxnet = array();
					if (!empty($oi['optionals'])) {
						$stepo = explode(";", $oi['optionals']);
						foreach ($stepo as $oo) {
							if (!empty($oo)) {
								$stept = explode(":", $oo);
								$q = "SELECT * FROM `#__vikrentitems_optionals` WHERE `id`='".intval($stept[0])."';";
								$dbo->setQuery($q);
								$dbo->execute();
								if ($dbo->getNumRows() == 1) {
									$actopt = $dbo->loadAssocList();
									$vri_tn->translateContents($actopt, '#__vikrentitems_optionals');
									$specvar = '';
									if (!empty($actopt[0]['specifications']) && strstr($stept[1], '-') != false) {
										$optspeccosts = VikRentItems::getOptionSpecIntervalsCosts($actopt[0]['specifications']);
										$optspecnames = VikRentItems::getOptionSpecIntervalsNames($actopt[0]['specifications']);
										$specstept = explode('-', $stept[1]);
										$stept[1] = $specstept[0];
										$specvar = $specstept[1];
										$actopt[0]['specintv'] = $specvar;
										$actopt[0]['name'] .= ' ('.$optspecnames[($specvar - 1)].')';
										$actopt[0]['quan'] = $stept[1];
										$realcost = (intval($actopt[0]['perday']) == 1 ? (floatval($optspeccosts[($specvar - 1)]) * $order[0]['days'] * $stept[1]) : (floatval($optspeccosts[($specvar - 1)]) * $stept[1]));
									} else {
										$realcost = (intval($actopt[0]['perday'])==1 ? ($actopt[0]['cost'] * $order[0]['days'] * $stept[1]) : ($actopt[0]['cost'] * $stept[1]));
									}
									if (!empty($actopt[0]['maxprice']) && $actopt[0]['maxprice'] > 0 && $realcost > $actopt[0]['maxprice']) {
										$realcost = $actopt[0]['maxprice'];
										if (intval($actopt[0]['hmany']) == 1 && intval($stept[1]) > 1) {
											$realcost = $actopt[0]['maxprice'] * $stept[1];
										}
									}
									$opt_item_units = $actopt[0]['onceperitem'] ? 1 : $oi['itemquant'];
									$tmpopr = VikRentItems::sayOptionalsPlusIva($realcost * $opt_item_units, $actopt[0]['idiva'], $order[0]);
									$isdue += $tmpopr;
									$optnetprice = VikRentItems::sayOptionalsMinusIva($realcost * $opt_item_units, $actopt[0]['idiva'], $order[0]);
									$optarrtaxnet[] = $optnetprice;
									$optstr .= ($stept[1] > 1 ? $stept[1]." " : "").$actopt[0]['name'].": ".$tmpopr."\n";
								}
							}
						}
					}
					// VRI 1.6 - custom extra costs
					if (!empty($oi['extracosts'])) {
						$cur_extra_costs = json_decode($oi['extracosts'], true);
						foreach ($cur_extra_costs as $eck => $ecv) {
							$efee_cost = VikRentItems::sayOptionalsPlusIva($ecv['cost'], $ecv['idtax'], $order[0]);
							$isdue += $efee_cost;
							$efee_cost_without = VikRentItems::sayOptionalsMinusIva($ecv['cost'], $ecv['idtax'], $order[0]);
							$optarrtaxnet[] = $efee_cost_without;
							$optstr .= $ecv['name'].": ".$efee_cost."\n";
						}
					}
					//
					$arrayinfopdf = array('days' => $order[0]['days'], 'tarminusiva' => $costminusiva, 'tartax' => ($costplusiva - $costminusiva), 'opttaxnet' => $optarrtaxnet, 'locfeenet' => $locfeewithouttax);
					$vricart[$oi['iditem']][$koi]['itemquant'] = $oi['itemquant'];
					$vricart[$oi['iditem']][$koi]['info'] = VikRentItems::getItemInfo($oi['iditem'], $vri_tn);
					$vricart[$oi['iditem']][$koi]['pricestr'] = $pricestr;
					$vricart[$oi['iditem']][$koi]['optstr'] = $optstr;
					$vricart[$oi['iditem']][$koi]['optarrtaxnet'] = $optarrtaxnet;
					$vricart[$oi['iditem']][$koi]['infopdf'] = $arrayinfopdf;
					if (!empty($oi['timeslot'])) {
						$vricart[$oi['iditem']][$koi]['timeslot']['name'] = $oi['timeslot'];
					}
					if (!empty($oi['deliveryaddr'])) {
						$vricart[$oi['iditem']][$koi]['delivery']['vrideliveryaddress'] = $oi['deliveryaddr'];
						$vricart[$oi['iditem']][$koi]['delivery']['vrideliverydistance'] = $oi['deliverydist'];
					}
				}
				//delivery service
				if ($totdelivery > 0) {
					$isdue += $totdelivery;
				}
				//
				//vikrentitems 1.1 coupon
				$usedcoupon = false;
				$origisdue = $isdue;
				if (strlen($order[0]['coupon']) > 0) {
					$usedcoupon = true;
					$expcoupon = explode(";", $order[0]['coupon']);
					$isdue = $isdue - $expcoupon[1];
				}
				//
				$mainframe->enqueueMessage(JText::_('VRORDERSETASCONF'));
				VikRentItems::sendCustMailFromBack($order[0]['custmail'], strip_tags($ftitle)." ".JText::_('VRRENTALORD'), $ftitle, $nowts, $order[0]['custdata'], $vricart, $order[0]['ritiro'], $order[0]['consegna'], $isdue, $viklink, JText::_('VRIOMPLETED'), $ritplace, $consegnaplace, $maillocfee, $order[0]['id'], $order[0]['coupon'], true, $totdelivery, "setconfirm");
			}
		}
		$mainframe->redirect("index.php?option=com_vikrentitems&task=editorder&cid[]=".$oid);
	}

    function recreatepdf() {
        $dbo = JFactory::getDBO();
        $mainframe = JFactory::getApplication();
        $cid = VikRequest::getVar('cid', array(0));
        $oid = $cid[0];
        $q = "SELECT * FROM `#__vikrentitems_orders` WHERE `id`=".(int)$oid.";";
        $dbo->setQuery($q);
        $dbo->execute();
        if ($dbo->getNumRows() == 1) {
            $order = $dbo->loadAssocList();
            $vri_tn = VikRentItems::getTranslator();
            //check if the language in use is the same as the one used during the checkout
            if (!empty($order[0]['lang'])) {
                $lang = JFactory::getLanguage();
                if ($lang->getTag() != $order[0]['lang']) {
                    $lang->load('com_vikrentitems', JPATH_ADMINISTRATOR, $order[0]['lang'], true);
                    $vri_tn::$force_tolang = $order[0]['lang'];
                }
            }
            //
            $totdelivery = $order[0]['deliverycost'];
            $checkhourscharges = 0;
            $ppickup = $order[0]['ritiro'];
            $prelease = $order[0]['consegna'];
            $secdiff = $prelease - $ppickup;
            $daysdiff = $secdiff / 86400;
            if (is_int($daysdiff)) {
                if ($daysdiff < 1) {
                    $daysdiff = 1;
                }
            } else {
                if ($daysdiff < 1) {
                    $daysdiff = 1;
                } else {
                    $sum = floor($daysdiff) * 86400;
                    $newdiff = $secdiff - $sum;
                    $maxhmore = VikRentItems::getHoursMoreRb() * 3600;
                    if ($maxhmore >= $newdiff) {
                        $daysdiff = floor($daysdiff);
                    } else {
                        $daysdiff = ceil($daysdiff);
                        $ehours = intval(round(($newdiff - $maxhmore) / 3600));
                        $checkhourscharges = $ehours;
                        if ($checkhourscharges > 0) {
                            $aehourschbasp = VikRentItems::applyExtraHoursChargesBasp();
                        }
                    }
                }
            }
            $realback = VikRentItems::getHoursItemAvail() * 3600;
            $realback += $order[0]['consegna'];
            $isdue = 0;
            $vricart = array();
            $allbook = true;
            $notavail = array();
            $q = "SELECT `oi`.*,`i`.`name`,`i`.`units` FROM `#__vikrentitems_ordersitems` AS `oi`,`#__vikrentitems_items` AS `i` WHERE `oi`.`idorder`='".$order[0]['id']."' AND `oi`.`iditem`=`i`.`id` ORDER BY `oi`.`id` ASC;";
            $dbo->setQuery($q);
            $dbo->execute();
            $orderitems = $dbo->loadAssocList();
            $vri_tn->translateContents($orderitems, '#__vikrentitems_items', array('id' => 'iditem'));
            foreach ($orderitems as $koi => $oi) {
                if (!VikRentItems::itemBookable($oi['iditem'], $oi['units'], $order[0]['ritiro'], $order[0]['consegna'], $oi['itemquant'])) {
                    $allbook = false;
                    $notavail[] = $oi['name'];
                }
            }
            if (!$allbook) {
                VikError::raiseWarning('', JText::sprintf('ERRCONFORDERITEMNA', implode(", ", $notavail)));
            } else {
                $recreate_date = date("h:i:s / d.m.Y");
                $q = "UPDATE `#__vikrentitems_orders` SET `recreate_date`='".$recreate_date."' WHERE `id`='".$order[0]['id']."';";
                $dbo->setQuery($q);
                $dbo->execute();

//                $arrnewbusy = array();
//                foreach ($orderitems as $koi => $oi) {
//                    for ($i = 1; $i <= $oi['itemquant']; $i++) {
//                        $q = "INSERT INTO `#__vikrentitems_busy` (`iditem`,`ritiro`,`consegna`,`realback`) VALUES('".$oi['iditem']."','".$order[0]['ritiro']."','".$order[0]['consegna']."','".$realback."');";
//                        $dbo->setQuery($q);
//                        $dbo->execute();
//                        $busynow = $dbo->insertid();
//                        $arrnewbusy[] = $busynow;
//                    }
//                    $kit_relations = VikRentItems::getKitRelatedItems($oi['iditem']);
//                    if (count($kit_relations)) {
//                        //VRI 1.5 - store busy records for the children or parent items, in case of a kit (Group/Set of Items)
//                        foreach ($kit_relations as $kit_rel) {
//                            for ($i = 1; $i <= $kit_rel['units']; $i++) {
//                                $q = "INSERT INTO `#__vikrentitems_busy` (`iditem`,`ritiro`,`consegna`,`realback`) VALUES(" . $dbo->quote($kit_rel['iditem']) . ", '" . $order[0]['ritiro'] . "', '" . $order[0]['consegna'] . "','" . $realback . "');";
//                                $dbo->setQuery($q);
//                                $dbo->execute();
//                                $busynow = $dbo->insertid();
//                                $arrnewbusy[] = $busynow;
//                            }
//                        }
//                        //
//                    }
//                }
//                foreach ($arrnewbusy as $newbusy) {
//                    $q = "INSERT INTO `#__vikrentitems_ordersbusy` (`idorder`,`idbusy`) VALUES('".$order[0]['id']."', '".$newbusy."');";
//                    $dbo->setQuery($q);
//                    $dbo->execute();
//                }
//                $q = "UPDATE `#__vikrentitems_orders` SET `status`='confirmed' WHERE `id`='".$order[0]['id']."';";
//                $dbo->setQuery($q);
//                $dbo->execute();
//                $q = "DELETE FROM `#__vikrentitems_tmplock` WHERE `idorder`=".(int)$order[0]['id'].";";
//                $dbo->setQuery($q);
//                $dbo->execute();
//                //send mail
                $ftitle = VikRentItems::getFrontTitle($vri_tn);
                $nowts = $order[0]['ts'];
                $viklink = JURI::root()."index.php?option=com_vikrentitems&task=vieworder&sid=".$order[0]['sid']."&ts=".$order[0]['ts'];
                $ritplace = (!empty($order[0]['idplace']) ? VikRentItems::getPlaceName($order[0]['idplace'], $vri_tn) : "");
                $consegnaplace = (!empty($order[0]['idreturnplace']) ? VikRentItems::getPlaceName($order[0]['idreturnplace'], $vri_tn) : "");
                $maillocfee = "";
                $locfeewithouttax = 0;
                if (!empty($order[0]['idplace']) && !empty($order[0]['idreturnplace'])) {
                    $locfee = VikRentItems::getLocFee($order[0]['idplace'], $order[0]['idreturnplace']);
                    if ($locfee) {
                        //VikRentItems 1.1 - Location fees overrides
                        if (strlen($locfee['losoverride']) > 0) {
                            $arrvaloverrides = array();
                            $valovrparts = explode('_', $locfee['losoverride']);
                            foreach ($valovrparts as $valovr) {
                                if (!empty($valovr)) {
                                    $ovrinfo = explode(':', $valovr);
                                    $arrvaloverrides[$ovrinfo[0]] = $ovrinfo[1];
                                }
                            }
                            if (array_key_exists($order[0]['days'], $arrvaloverrides)) {
                                $locfee['cost'] = $arrvaloverrides[$order[0]['days']];
                            }
                        }
                        //end VikRentItems 1.1 - Location fees overrides
                        $locfeecost = intval($locfee['daily']) == 1 ? ($locfee['cost'] * $order[0]['days']) : $locfee['cost'];
                        $locfeewith = VikRentItems::sayLocFeePlusIva($locfeecost, $locfee['idiva'], $order[0]);
                        $isdue += $locfeewith;
                        $locfeewithouttax = VikRentItems::sayLocFeeMinusIva($locfeecost, $locfee['idiva'], $order[0]);
                        $maillocfee = $locfeewith;
                    }
                }
                foreach ($orderitems as $koi => $oi) {
                    $tar = array(array(
                        'id' => 0,
                        'iditem' => $oi['iditem'],
                        'days' => $order[0]['days'],
                        'idprice' => -1,
                        'cost' => 0,
                        'attrdata' => '',
                    ));
                    $is_cust_cost = (!empty($oi['cust_cost']) && $oi['cust_cost'] > 0);
                    if (!empty($oi['idtar'])) {
                        if ($order[0]['hourly'] == 1) {
                            $q = "SELECT * FROM `#__vikrentitems_dispcosthours` WHERE `id`=".(int)$oi['idtar'].";";
                        } else {
                            $q = "SELECT * FROM `#__vikrentitems_dispcost` WHERE `id`=".(int)$oi['idtar'].";";
                        }
                        $dbo->setQuery($q);
                        $dbo->execute();
                        if ($dbo->getNumRows() == 0) {
                            if ($order[0]['hourly'] == 1) {
                                $q = "SELECT * FROM `#__vikrentitems_dispcost` WHERE `id`=".(int)$oi['idtar'].";";
                                $dbo->setQuery($q);
                                $dbo->execute();
                                if ($dbo->getNumRows() == 1) {
                                    $tar = $dbo->loadAssocList();
                                }
                            }
                        } else {
                            $tar = $dbo->loadAssocList();
                        }
                    } elseif ($is_cust_cost) {
                        //Custom Rate
                        $tar = array(array(
                            'id' => -1,
                            'iditem' => $oi['iditem'],
                            'days' => $order[0]['days'],
                            'idprice' => -1,
                            'cost' => $oi['cust_cost'],
                            'attrdata' => '',
                        ));
                    }
                    if (count($tar) && !empty($tar[0]['id'])) {
                        if ($order[0]['hourly'] == 1 && !empty($tar[0]['hours'])) {
                            foreach ($tar as $kt => $vt) {
                                $tar[$kt]['days'] = 1;
                            }
                        }
                        if ($checkhourscharges > 0 && $aehourschbasp == true) {
                            $ret = VikRentItems::applyExtraHoursChargesItem($tar, $oi['iditem'], $checkhourscharges, $daysdiff, false, true, true);
                            $tar = $ret['return'];
                            $calcdays = $ret['days'];
                        }
                        if ($checkhourscharges > 0 && $aehourschbasp == false) {
                            $tar = VikRentItems::extraHoursSetPreviousFareItem($tar, $oi['iditem'], $checkhourscharges, $daysdiff, true);
                            $tar = VikRentItems::applySeasonsItem($tar, $order[0]['ritiro'], $order[0]['consegna'], $order[0]['idplace']);
                            $ret = VikRentItems::applyExtraHoursChargesItem($tar, $oi['iditem'], $checkhourscharges, $daysdiff, true, true, true);
                            $tar = $ret['return'];
                            $calcdays = $ret['days'];
                        } else {
                            $tar = VikRentItems::applySeasonsItem($tar, $order[0]['ritiro'], $order[0]['consegna'], $order[0]['idplace']);
                        }
                        $tar = VikRentItems::applyItemDiscounts($tar, $oi['iditem'], $oi['itemquant']);
                    }
                    $costplusiva = $is_cust_cost ? $tar[0]['cost'] : VikRentItems::sayCostPlusIva($tar[0]['cost'] * $oi['itemquant'], $tar[0]['idprice'], $order[0]);
                    $costminusiva = $is_cust_cost ? VikRentItems::sayCustCostMinusIva($tar[0]['cost'], $oi['cust_idiva']) : VikRentItems::sayCostMinusIva($tar[0]['cost'] * $oi['itemquant'], $tar[0]['idprice'], $order[0]);
                    $pricestr = ($is_cust_cost ? JText::_('VRIRENTCUSTRATEPLAN').": ".$costplusiva : VikRentItems::getPriceName($tar[0]['idprice'], $vri_tn).": ".$costplusiva.(!empty($tar[0]['attrdata']) ? "\n".VikRentItems::getPriceAttr($tar[0]['idprice'], $vri_tn).": ".$tar[0]['attrdata'] : ""));
                    $isdue += $is_cust_cost ? $tar[0]['cost'] : VikRentItems::sayCostPlusIva($tar[0]['cost'] * $oi['itemquant'], $tar[0]['idprice'], $order[0]);
                    $optstr = "";
                    $optarrtaxnet = array();
                    if (!empty($oi['optionals'])) {
                        $stepo = explode(";", $oi['optionals']);
                        foreach ($stepo as $oo) {
                            if (!empty($oo)) {
                                $stept = explode(":", $oo);
                                $q = "SELECT * FROM `#__vikrentitems_optionals` WHERE `id`='".intval($stept[0])."';";
                                $dbo->setQuery($q);
                                $dbo->execute();
                                if ($dbo->getNumRows() == 1) {
                                    $actopt = $dbo->loadAssocList();
                                    $vri_tn->translateContents($actopt, '#__vikrentitems_optionals');
                                    $specvar = '';
                                    if (!empty($actopt[0]['specifications']) && strstr($stept[1], '-') != false) {
                                        $optspeccosts = VikRentItems::getOptionSpecIntervalsCosts($actopt[0]['specifications']);
                                        $optspecnames = VikRentItems::getOptionSpecIntervalsNames($actopt[0]['specifications']);
                                        $specstept = explode('-', $stept[1]);
                                        $stept[1] = $specstept[0];
                                        $specvar = $specstept[1];
                                        $actopt[0]['specintv'] = $specvar;
                                        $actopt[0]['name'] .= ' ('.$optspecnames[($specvar - 1)].')';
                                        $actopt[0]['quan'] = $stept[1];
                                        $realcost = (intval($actopt[0]['perday']) == 1 ? (floatval($optspeccosts[($specvar - 1)]) * $order[0]['days'] * $stept[1]) : (floatval($optspeccosts[($specvar - 1)]) * $stept[1]));
                                    } else {
                                        $realcost = (intval($actopt[0]['perday'])==1 ? ($actopt[0]['cost'] * $order[0]['days'] * $stept[1]) : ($actopt[0]['cost'] * $stept[1]));
                                    }
                                    if (!empty($actopt[0]['maxprice']) && $actopt[0]['maxprice'] > 0 && $realcost > $actopt[0]['maxprice']) {
                                        $realcost = $actopt[0]['maxprice'];
                                        if (intval($actopt[0]['hmany']) == 1 && intval($stept[1]) > 1) {
                                            $realcost = $actopt[0]['maxprice'] * $stept[1];
                                        }
                                    }
                                    $opt_item_units = $actopt[0]['onceperitem'] ? 1 : $oi['itemquant'];
                                    $tmpopr = VikRentItems::sayOptionalsPlusIva($realcost * $opt_item_units, $actopt[0]['idiva'], $order[0]);
                                    $isdue += $tmpopr;
                                    $optnetprice = VikRentItems::sayOptionalsMinusIva($realcost * $opt_item_units, $actopt[0]['idiva'], $order[0]);
                                    $optarrtaxnet[] = $optnetprice;
                                    $optstr .= ($stept[1] > 1 ? $stept[1]." " : "").$actopt[0]['name'].": ".$tmpopr."\n";
                                }
                            }
                        }
                    }
                    // VRI 1.6 - custom extra costs
                    if (!empty($oi['extracosts'])) {
                        $cur_extra_costs = json_decode($oi['extracosts'], true);
                        foreach ($cur_extra_costs as $eck => $ecv) {
                            $efee_cost = VikRentItems::sayOptionalsPlusIva($ecv['cost'], $ecv['idtax'], $order[0]);
                            $isdue += $efee_cost;
                            $efee_cost_without = VikRentItems::sayOptionalsMinusIva($ecv['cost'], $ecv['idtax'], $order[0]);
                            $optarrtaxnet[] = $efee_cost_without;
                            $optstr .= $ecv['name'].": ".$efee_cost."\n";
                        }
                    }
                    //
                    $arrayinfopdf = array('days' => $order[0]['days'], 'tarminusiva' => $costminusiva, 'tartax' => ($costplusiva - $costminusiva), 'opttaxnet' => $optarrtaxnet, 'locfeenet' => $locfeewithouttax);
                    $vricart[$oi['iditem']][$koi]['itemquant'] = $oi['itemquant'];
                    $vricart[$oi['iditem']][$koi]['info'] = VikRentItems::getItemInfo($oi['iditem'], $vri_tn);
                    $vricart[$oi['iditem']][$koi]['pricestr'] = $pricestr;
                    $vricart[$oi['iditem']][$koi]['optstr'] = $optstr;
                    $vricart[$oi['iditem']][$koi]['optarrtaxnet'] = $optarrtaxnet;
                    $vricart[$oi['iditem']][$koi]['infopdf'] = $arrayinfopdf;
                    if (!empty($oi['timeslot'])) {
                        $vricart[$oi['iditem']][$koi]['timeslot']['name'] = $oi['timeslot'];
                    }
                    if (!empty($oi['deliveryaddr'])) {
                        $vricart[$oi['iditem']][$koi]['delivery']['vrideliveryaddress'] = $oi['deliveryaddr'];
                        $vricart[$oi['iditem']][$koi]['delivery']['vrideliverydistance'] = $oi['deliverydist'];
                    }
                }
                //delivery service
                if ($totdelivery > 0) {
                    $isdue += $totdelivery;
                }
                //
                //vikrentitems 1.1 coupon
                $usedcoupon = false;
                $origisdue = $isdue;
                if (strlen($order[0]['coupon']) > 0) {
                    $usedcoupon = true;
                    $expcoupon = explode(";", $order[0]['coupon']);
                    $isdue = $isdue - $expcoupon[1];
                }
                //
                $mainframe->enqueueMessage(JText::_('VRPDFRECREATE'));
                VikRentItems::recreatePDF($order[0]['custmail'], strip_tags($ftitle)." ".JText::_('VRRENTALORD'), $ftitle, $nowts, $order[0]['custdata'], $vricart, $order[0]['ritiro'], $order[0]['consegna'], $isdue, $viklink, JText::_('VRIOMPLETED'), $ritplace, $consegnaplace, $maillocfee, $order[0]['id'], $order[0]['coupon'], true, $totdelivery, "setconfirm");
            }
        }
        $mainframe->redirect("index.php?option=com_vikrentitems&task=editorder&cid[]=".$oid);
    }

	function overv() {
		VikRentItemsHelper::printHeader("15");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'overv'));
	
		parent::display();

		if (VikRentItems::showFooter()) {
			VikRentItemsHelper::printFooter();
		}
	}

	function canceloverv() {
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikrentitems&task=overv");
	}

	function cancelbusy() {
		$pidorder = VikRequest::getString('idorder', '', 'request');
		$pgoto = VikRequest::getString('goto', '', 'request');
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikrentitems&task=editorder&cid[]=".$pidorder.($pgoto == 'overv' ? '&goto=overv' : ''));
	}

	function customf() {
		VikRentItemsHelper::printHeader("16");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'customf'));
	
		parent::display();

		if (VikRentItems::showFooter()) {
			VikRentItemsHelper::printFooter();
		}
	}

	function newcustomf() {
		VikRentItemsHelper::printHeader("16");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'managecustomf'));
	
		parent::display();

		if (VikRentItems::showFooter()) {
			VikRentItemsHelper::printFooter();
		}
	}

	function editcustomf() {
		VikRentItemsHelper::printHeader("16");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'managecustomf'));
	
		parent::display();

		if (VikRentItems::showFooter()) {
			VikRentItemsHelper::printFooter();
		}
	}

	function createcustomf() {
		$pname = VikRequest::getString('name', '', 'request', VIKREQUEST_ALLOWHTML);
		$ptype = VikRequest::getString('type', '', 'request');
		$pchoose = VikRequest::getVar('choose', array(0));
		$prequired = VikRequest::getString('required', '', 'request');
		$prequired = $prequired == "1" ? 1 : 0;
		$pisemail = VikRequest::getString('isemail', '', 'request');
		$pisemail = $pisemail == "1" ? 1 : 0;
		$pisnominative = VikRequest::getString('isnominative', '', 'request');
		$pisnominative = $pisnominative == "1" && $ptype == 'text' ? 1 : 0;
		$pisphone = VikRequest::getString('isphone', '', 'request');
		$pisphone = $pisphone == "1" && $ptype == 'text' ? 1 : 0;
		$pisaddress = VikRequest::getString('isaddress', '', 'request');
		$pisaddress = $pisaddress == "1" && $ptype == 'text' ? 1 : 0;
		$piscity = VikRequest::getString('iscity', '', 'request');
		$piscity = $piscity == "1" && $ptype == 'text' ? 1 : 0;
		$piszip = VikRequest::getString('iszip', '', 'request');
		$piszip = $piszip == "1" && $ptype == 'text' ? 1 : 0;
		$piscompany = VikRequest::getString('iscompany', '', 'request');
		$piscompany = $piscompany == "1" && $ptype == 'text' ? 1 : 0;
		$pisvat = VikRequest::getString('isvat', '', 'request');
		$pisvat = $pisvat == "1" && $ptype == 'text' ? 1 : 0;
		$fieldflag = '';
		if ($pisaddress == 1) {
			$fieldflag = 'address';
		} elseif ($piscity == 1) {
			$fieldflag = 'city';
		} elseif ($piszip == 1) {
			$fieldflag = 'zip';
		} elseif ($piscompany == 1) {
			$fieldflag = 'company';
		} elseif ($pisvat == 1) {
			$fieldflag = 'vat';
		}
		$ppoplink = VikRequest::getString('poplink', '', 'request');
		$choosestr = "";
		if (@count($pchoose) > 0) {
			foreach ($pchoose as $ch) {
				if (!empty($ch)) {
					$choosestr .= $ch.";;__;;";
				}
			}
		}
		$dbo = JFactory::getDBO();
		$q = "SELECT `ordering` FROM `#__vikrentitems_custfields` ORDER BY `#__vikrentitems_custfields`.`ordering` DESC LIMIT 1;";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() == 1) {
			$getlast = $dbo->loadResult();
			$newsortnum = $getlast + 1;
		} else {
			$newsortnum = 1;
		}
		$q = "INSERT INTO `#__vikrentitems_custfields` (`name`,`type`,`choose`,`required`,`ordering`,`isemail`,`poplink`,`isnominative`,`isphone`,`flag`) VALUES(".$dbo->quote($pname).", ".$dbo->quote($ptype).", ".$dbo->quote($choosestr).", ".$dbo->quote($prequired).", ".$dbo->quote($newsortnum).", ".$dbo->quote($pisemail).", ".$dbo->quote($ppoplink).", ".$pisnominative.", ".$pisphone.", ".$dbo->quote($fieldflag).");";
		$dbo->setQuery($q);
		$dbo->execute();
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikrentitems&task=customf");
	}

	function updatecustomf() {
		$pname = VikRequest::getString('name', '', 'request', VIKREQUEST_ALLOWHTML);
		$ptype = VikRequest::getString('type', '', 'request');
		$pchoose = VikRequest::getVar('choose', array(0));
		$prequired = VikRequest::getString('required', '', 'request');
		$prequired = $prequired == "1" ? 1 : 0;
		$pisemail = VikRequest::getString('isemail', '', 'request');
		$pisemail = $pisemail == "1" ? 1 : 0;
		$pisnominative = VikRequest::getString('isnominative', '', 'request');
		$pisnominative = $pisnominative == "1" && $ptype == 'text' ? 1 : 0;
		$pisphone = VikRequest::getString('isphone', '', 'request');
		$pisphone = $pisphone == "1" && $ptype == 'text' ? 1 : 0;
		$pisaddress = VikRequest::getString('isaddress', '', 'request');
		$pisaddress = $pisaddress == "1" && $ptype == 'text' ? 1 : 0;
		$piscity = VikRequest::getString('iscity', '', 'request');
		$piscity = $piscity == "1" && $ptype == 'text' ? 1 : 0;
		$piszip = VikRequest::getString('iszip', '', 'request');
		$piszip = $piszip == "1" && $ptype == 'text' ? 1 : 0;
		$piscompany = VikRequest::getString('iscompany', '', 'request');
		$piscompany = $piscompany == "1" && $ptype == 'text' ? 1 : 0;
		$pisvat = VikRequest::getString('isvat', '', 'request');
		$pisvat = $pisvat == "1" && $ptype == 'text' ? 1 : 0;
		$fieldflag = '';
		if ($pisaddress == 1) {
			$fieldflag = 'address';
		} elseif ($piscity == 1) {
			$fieldflag = 'city';
		} elseif ($piszip == 1) {
			$fieldflag = 'zip';
		} elseif ($piscompany == 1) {
			$fieldflag = 'company';
		} elseif ($pisvat == 1) {
			$fieldflag = 'vat';
		}
		$ppoplink = VikRequest::getString('poplink', '', 'request');
		$pwhere = VikRequest::getInt('where', '', 'request');
		$choosestr = "";
		if (@count($pchoose) > 0) {
			foreach ($pchoose as $ch) {
				if (!empty($ch)) {
					$choosestr .= $ch.";;__;;";
				}
			}
		}
		$dbo = JFactory::getDBO();
		$q = "UPDATE `#__vikrentitems_custfields` SET `name`=".$dbo->quote($pname).",`type`=".$dbo->quote($ptype).",`choose`=".$dbo->quote($choosestr).",`required`=".$dbo->quote($prequired).",`isemail`=".$dbo->quote($pisemail).",`poplink`=".$dbo->quote($ppoplink).",`isnominative`=".$pisnominative.",`isphone`=".$pisphone.",`flag`=".$dbo->quote($fieldflag)." WHERE `id`=".$dbo->quote($pwhere).";";
		$dbo->setQuery($q);
		$dbo->execute();
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikrentitems&task=customf");
	}

	function removecustomf() {
		$ids = VikRequest::getVar('cid', array(0));
		if (@count($ids)) {
			$dbo = JFactory::getDBO();
			foreach ($ids as $d) {
				$q = "DELETE FROM `#__vikrentitems_custfields` WHERE `id`=".$dbo->quote($d).";";
				$dbo->setQuery($q);
				$dbo->execute();
			}
		}
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikrentitems&task=customf");
	}

	function cancelcustomf() {
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikrentitems&task=customf");
	}

	function sortfield() {
		$sortid = VikRequest::getVar('cid', array(0));
		$pmode = VikRequest::getString('mode', '', 'request');
		$dbo = JFactory::getDBO();
		$mainframe = JFactory::getApplication();
		if (!empty($pmode)) {
			$q = "SELECT `id`,`ordering` FROM `#__vikrentitems_custfields` ORDER BY `#__vikrentitems_custfields`.`ordering` ASC;";
			$dbo->setQuery($q);
			$dbo->execute();
			$totr=$dbo->getNumRows();
			if ($totr > 1) {
				$data = $dbo->loadAssocList();
				if ($pmode=="up") {
					foreach ($data as $v){
						if ($v['id']==$sortid[0]) {
							$y=$v['ordering'];
						}
					}
					if ($y && $y > 1) {
						$vik=$y - 1;
						$found=false;
						foreach ($data as $v){
							if (intval($v['ordering'])==intval($vik)) {
								$found=true;
								$q = "UPDATE `#__vikrentitems_custfields` SET `ordering`='".$y."' WHERE `id`='".$v['id']."' LIMIT 1;";
								$dbo->setQuery($q);
								$dbo->execute();
								$q = "UPDATE `#__vikrentitems_custfields` SET `ordering`='".$vik."' WHERE `id`='".$sortid[0]."' LIMIT 1;";
								$dbo->setQuery($q);
								$dbo->execute();
								break;
							}
						}
						if (!$found) {
							$q = "UPDATE `#__vikrentitems_custfields` SET `ordering`='".$vik."' WHERE `id`='".$sortid[0]."' LIMIT 1;";
							$dbo->setQuery($q);
							$dbo->execute();
						}
					}
				} elseif ($pmode=="down") {
					foreach ($data as $v){
						if ($v['id']==$sortid[0]) {
							$y=$v['ordering'];
						}
					}
					if ($y) {
						$vik=$y + 1;
						$found=false;
						foreach ($data as $v){
							if (intval($v['ordering'])==intval($vik)) {
								$found=true;
								$q = "UPDATE `#__vikrentitems_custfields` SET `ordering`='".$y."' WHERE `id`='".$v['id']."' LIMIT 1;";
								$dbo->setQuery($q);
								$dbo->execute();
								$q = "UPDATE `#__vikrentitems_custfields` SET `ordering`='".$vik."' WHERE `id`='".$sortid[0]."' LIMIT 1;";
								$dbo->setQuery($q);
								$dbo->execute();
								break;
							}
						}
						if (!$found) {
							$q = "UPDATE `#__vikrentitems_custfields` SET `ordering`='".$vik."' WHERE `id`='".$sortid[0]."' LIMIT 1;";
							$dbo->setQuery($q);
							$dbo->execute();
						}
					}
				}
			}
			$mainframe->redirect("index.php?option=com_vikrentitems&task=customf");
		} else {
			$mainframe->redirect("index.php?option=com_vikrentitems");
		}
	}

	function removemoreimgs() {
		$mainframe = JFactory::getApplication();
		$dbo = JFactory::getDBO();
		$pelemid = VikRequest::getInt('elemid', '', 'request');
		$pimgind = VikRequest::getInt('imgind', '', 'request');
		if (!empty($pelemid) && strlen($pimgind) > 0) {
			$q = "SELECT `moreimgs` FROM `#__vikrentitems_items` WHERE `id`='".$pelemid."';";
			$dbo->setQuery($q);
			$dbo->execute();
			$actmore=$dbo->loadResult();
			if (strlen($actmore) > 0) {
				$actsplit = explode(';;', $actmore);
				if (array_key_exists($pimgind, $actsplit)) {
					@unlink(VRI_ADMIN_PATH.DS.'resources'.DS.'big_'.$actsplit[$pimgind]);
					@unlink(VRI_ADMIN_PATH.DS.'resources'.DS.'thumb_'.$actsplit[$pimgind]);
					unset($actsplit[$pimgind]);
					$newstr = "";
					foreach ($actsplit as $oi) {
						if (!empty($oi)) {
							$newstr.=$oi.';;';
						}
					}
					$q = "UPDATE `#__vikrentitems_items` SET `moreimgs`=".$dbo->quote($newstr)." WHERE `id`='".$pelemid."';";
					$dbo->setQuery($q);
					$dbo->execute();
				}
			}
			$mainframe->redirect("index.php?option=com_vikrentitems&task=edititem&cid[]=".$pelemid);
		} else {
			$mainframe->redirect("index.php?option=com_vikrentitems");
		}
	}

	function coupons() {
		VikRentItemsHelper::printHeader("17");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'coupons'));
	
		parent::display();

		if (VikRentItems::showFooter()) {
			VikRentItemsHelper::printFooter();
		}
	}

	function newcoupon() {
		VikRentItemsHelper::printHeader("17");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'managecoupon'));
	
		parent::display();

		if (VikRentItems::showFooter()) {
			VikRentItemsHelper::printFooter();
		}
	}

	function editcoupon() {
		VikRentItemsHelper::printHeader("17");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'managecoupon'));
	
		parent::display();

		if (VikRentItems::showFooter()) {
			VikRentItemsHelper::printFooter();
		}
	}

	function createcoupon() {
		$mainframe = JFactory::getApplication();
		$pcode = VikRequest::getString('code', '', 'request');
		$pvalue = VikRequest::getFloat('value', '', 'request');
		$pfrom = VikRequest::getString('from', '', 'request');
		$pto = VikRequest::getString('to', '', 'request');
		$piditems = VikRequest::getVar('iditems', array(0));
		$ptype = VikRequest::getString('type', '', 'request');
		$ptype = $ptype == "1" ? 1 : 2;
		$ppercentot = VikRequest::getString('percentot', '', 'request');
		$ppercentot = $ppercentot == "1" ? 1 : 2;
		$pallvehicles = VikRequest::getString('allvehicles', '', 'request');
		$pallvehicles = $pallvehicles == "1" ? 1 : 0;
		$pmintotord = VikRequest::getString('mintotord', '', 'request');
		$striditems = "";
		if (@count($piditems) > 0 && $pallvehicles != 1) {
			foreach ($piditems as $ch) {
				if (!empty($ch)) {
					$striditems .= ";".$ch.";";
				}
			}
		}
		$strdatevalid = "";
		if (strlen($pfrom) > 0 && strlen($pto) > 0) {
			$first = VikRentItems::getDateTimestamp($pfrom, 0, 0);
			$second = VikRentItems::getDateTimestamp($pto, 0, 0);
			if ($first < $second) {
				$strdatevalid .= $first."-".$second;
			}
		}
		$dbo = JFactory::getDBO();
		$q = "SELECT * FROM `#__vikrentitems_coupons` WHERE `code`=".$dbo->quote($pcode).";";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			VikError::raiseWarning('', JText::_('VRICOUPONEXISTS'));
		} else {
			$mainframe->enqueueMessage(JText::_('VRICOUPONSAVEOK'));
			$q = "INSERT INTO `#__vikrentitems_coupons` (`code`,`type`,`percentot`,`value`,`datevalid`,`allvehicles`,`iditems`,`mintotord`) VALUES(".$dbo->quote($pcode).",'".$ptype."','".$ppercentot."',".$dbo->quote($pvalue).",'".$strdatevalid."','".$pallvehicles."','".$striditems."', ".$dbo->quote($pmintotord).");";
			$dbo->setQuery($q);
			$dbo->execute();
		}
		$mainframe->redirect("index.php?option=com_vikrentitems&task=coupons");
	}

	function updatecoupon() {
		$mainframe = JFactory::getApplication();
		$pcode = VikRequest::getString('code', '', 'request');
		$pvalue = VikRequest::getFloat('value', '', 'request');
		$pfrom = VikRequest::getString('from', '', 'request');
		$pto = VikRequest::getString('to', '', 'request');
		$piditems = VikRequest::getVar('iditems', array(0));
		$pwhere = VikRequest::getString('where', '', 'request');
		$ptype = VikRequest::getString('type', '', 'request');
		$ptype = $ptype == "1" ? 1 : 2;
		$ppercentot = VikRequest::getString('percentot', '', 'request');
		$ppercentot = $ppercentot == "1" ? 1 : 2;
		$pallvehicles = VikRequest::getString('allvehicles', '', 'request');
		$pallvehicles = $pallvehicles == "1" ? 1 : 0;
		$pmintotord = VikRequest::getString('mintotord', '', 'request');
		$striditems = "";
		if (@count($piditems) > 0 && $pallvehicles != 1) {
			foreach ($piditems as $ch) {
				if (!empty($ch)) {
					$striditems .= ";".$ch.";";
				}
			}
		}
		$strdatevalid = "";
		if (strlen($pfrom) > 0 && strlen($pto) > 0) {
			$first = VikRentItems::getDateTimestamp($pfrom, 0, 0);
			$second = VikRentItems::getDateTimestamp($pto, 0, 0);
			if ($first < $second) {
				$strdatevalid .= $first."-".$second;
			}
		}
		$dbo = JFactory::getDBO();
		$q = "SELECT * FROM `#__vikrentitems_coupons` WHERE `code`=".$dbo->quote($pcode)." AND `id`!='".$pwhere."';";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			VikError::raiseWarning('', JText::_('VRICOUPONEXISTS'));
		} else {
			$mainframe->enqueueMessage(JText::_('VRICOUPONSAVEOK'));
			$q = "UPDATE `#__vikrentitems_coupons` SET `code`=".$dbo->quote($pcode).",`type`='".$ptype."',`percentot`='".$ppercentot."',`value`=".$dbo->quote($pvalue).",`datevalid`='".$strdatevalid."',`allvehicles`='".$pallvehicles."',`iditems`='".$striditems."',`mintotord`=".$dbo->quote($pmintotord)." WHERE `id`='".$pwhere."';";
			$dbo->setQuery($q);
			$dbo->execute();
		}
		$mainframe->redirect("index.php?option=com_vikrentitems&task=coupons");
	}

	function removecoupons() {
		$ids = VikRequest::getVar('cid', array(0));
		if (@count($ids)) {
			$dbo = JFactory::getDBO();
			foreach ($ids as $d) {
				$q = "DELETE FROM `#__vikrentitems_coupons` WHERE `id`=".$dbo->quote($d).";";
				$dbo->setQuery($q);
				$dbo->execute();
			}
		}
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikrentitems&task=coupons");
	}

	function cancelcoupon() {
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikrentitems&task=coupons");
	}

	function resendordemail() {
		$cid = VikRequest::getVar('cid', array(0));
		$oid = (int)$cid[0];
		$this->do_resendordemail($oid);
	}

	private function do_resendordemail($oid, $checkdbsendpdf = false) {
		$dbo = JFactory::getDBO();
		$mainframe = JFactory::getApplication();
		$q = "SELECT * FROM `#__vikrentitems_orders` WHERE `id`=".(int)$oid.";";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() == 1) {
			$order = $dbo->loadAssocList();
			$vri_tn = VikRentItems::getTranslator();
			//check if the language in use is the same as the one used during the checkout
			if (!empty($order[0]['lang'])) {
				$lang = JFactory::getLanguage();
				if ($lang->getTag() != $order[0]['lang']) {
					$lang->load('com_vikrentitems', JPATH_ADMINISTRATOR, $order[0]['lang'], true);
					$vri_tn::$force_tolang = $order[0]['lang'];
				}
			}
			//
			$totdelivery = $order[0]['deliverycost'];
			$checkhourscharges = 0;
			$ppickup = $order[0]['ritiro'];
			$prelease = $order[0]['consegna'];
			$secdiff = $prelease - $ppickup;
			$daysdiff = $secdiff / 86400;
			if (is_int($daysdiff)) {
				if ($daysdiff < 1) {
					$daysdiff = 1;
				}
			} else {
				if ($daysdiff < 1) {
					$daysdiff = 1;
				} else {
					$sum = floor($daysdiff) * 86400;
					$newdiff = $secdiff - $sum;
					$maxhmore = VikRentItems::getHoursMoreRb() * 3600;
					if ($maxhmore >= $newdiff) {
						$daysdiff = floor($daysdiff);
					} else {
						$daysdiff = ceil($daysdiff);
						$ehours = intval(round(($newdiff - $maxhmore) / 3600));
						$checkhourscharges = $ehours;
						if ($checkhourscharges > 0) {
							$aehourschbasp = VikRentItems::applyExtraHoursChargesBasp();
						}
					}
				}
			}
			//send mail
			$ftitle = VikRentItems::getFrontTitle($vri_tn);
			$nowts = $order[0]['ts'];
			$viklink = JURI::root()."index.php?option=com_vikrentitems&task=vieworder&sid=".$order[0]['sid']."&ts=".$order[0]['ts'];
			$ritplace = (!empty($order[0]['idplace']) ? VikRentItems::getPlaceName($order[0]['idplace'], $vri_tn) : "");
			$consegnaplace=(!empty($order[0]['idreturnplace']) ? VikRentItems::getPlaceName($order[0]['idreturnplace'], $vri_tn) : "");
			$isdue = 0;
			$vricart = array();
			$q = "SELECT `oi`.*,`i`.`name`,`i`.`units` FROM `#__vikrentitems_ordersitems` AS `oi`,`#__vikrentitems_items` AS `i` WHERE `oi`.`idorder`='".$order[0]['id']."' AND `oi`.`iditem`=`i`.`id` ORDER BY `oi`.`id` ASC;";
			$dbo->setQuery($q);
			$dbo->execute();
			$orderitems = $dbo->loadAssocList();
			$vri_tn->translateContents($orderitems, '#__vikrentitems_items', array('id' => 'iditem'));
			$maillocfee = "";
			$locfeewithouttax = 0;
			if (!empty($order[0]['idplace']) && !empty($order[0]['idreturnplace'])) {
				$locfee = VikRentItems::getLocFee($order[0]['idplace'], $order[0]['idreturnplace']);
				if ($locfee) {
					//VikRentItems 1.1 - Location fees overrides
					if (strlen($locfee['losoverride']) > 0) {
						$arrvaloverrides = array();
						$valovrparts = explode('_', $locfee['losoverride']);
						foreach ($valovrparts as $valovr) {
							if (!empty($valovr)) {
								$ovrinfo = explode(':', $valovr);
								$arrvaloverrides[$ovrinfo[0]] = $ovrinfo[1];
							}
						}
						if (array_key_exists($order[0]['days'], $arrvaloverrides)) {
							$locfee['cost'] = $arrvaloverrides[$order[0]['days']];
						}
					}
					//end VikRentItems 1.1 - Location fees overrides
					$locfeecost = intval($locfee['daily']) == 1 ? ($locfee['cost'] * $order[0]['days']) : $locfee['cost'];
					$locfeewith = VikRentItems::sayLocFeePlusIva($locfeecost, $locfee['idiva'], $order[0]);
					$isdue += $locfeewith;
					$locfeewithouttax = VikRentItems::sayLocFeeMinusIva($locfeecost, $locfee['idiva'], $order[0]);
					$maillocfee = $locfeewith;
				}
			}
			foreach ($orderitems as $koi => $oi) {
				$tar = array(array(
					'id' => 0,
					'iditem' => $oi['iditem'],
					'days' => $order[0]['days'],
					'idprice' => -1,
					'cost' => 0,
					'attrdata' => '',
				));
				$is_cust_cost = (!empty($oi['cust_cost']) && $oi['cust_cost'] > 0);
				if (!empty($oi['idtar'])) {
					if ($order[0]['hourly'] == 1) {
						$q = "SELECT * FROM `#__vikrentitems_dispcosthours` WHERE `id`=".(int)$oi['idtar'].";";
					} else {
						$q = "SELECT * FROM `#__vikrentitems_dispcost` WHERE `id`=".(int)$oi['idtar'].";";
					}
					$dbo->setQuery($q);
					$dbo->execute();
					if ($dbo->getNumRows() == 0) {
						if ($order[0]['hourly'] == 1) {
							$q = "SELECT * FROM `#__vikrentitems_dispcost` WHERE `id`=".(int)$oi['idtar'].";";
							$dbo->setQuery($q);
							$dbo->execute();
							if ($dbo->getNumRows() == 1) {
								$tar = $dbo->loadAssocList();
							}
						}
					} else {
						$tar = $dbo->loadAssocList();
					}
				} elseif ($is_cust_cost) {
					//Custom Rate
					$tar = array(array(
						'id' => -1,
						'iditem' => $oi['iditem'],
						'days' => $order[0]['days'],
						'idprice' => -1,
						'cost' => $oi['cust_cost'],
						'attrdata' => '',
					));
				}
				if (count($tar) && !empty($tar[0]['id'])) {
					if ($order[0]['hourly'] == 1 && !empty($tar[0]['hours'])) {
						foreach ($tar as $kt => $vt) {
							$tar[$kt]['days'] = 1;
						}
					}
					if ($checkhourscharges > 0 && $aehourschbasp == true) {
						$ret = VikRentItems::applyExtraHoursChargesItem($tar, $oi['iditem'], $checkhourscharges, $daysdiff, false, true, true);
						$tar = $ret['return'];
						$calcdays = $ret['days'];
					}
					if ($checkhourscharges > 0 && $aehourschbasp == false) {
						$tar = VikRentItems::extraHoursSetPreviousFareItem($tar, $oi['iditem'], $checkhourscharges, $daysdiff, true);
						$tar = VikRentItems::applySeasonsItem($tar, $order[0]['ritiro'], $order[0]['consegna'], $order[0]['idplace']);
						$ret = VikRentItems::applyExtraHoursChargesItem($tar, $oi['iditem'], $checkhourscharges, $daysdiff, true, true, true);
						$tar = $ret['return'];
						$calcdays = $ret['days'];
					} else {
						$tar = VikRentItems::applySeasonsItem($tar, $order[0]['ritiro'], $order[0]['consegna'], $order[0]['idplace']);
					}
					$tar = VikRentItems::applyItemDiscounts($tar, $oi['iditem'], $oi['itemquant']);
				}
				$costplusiva = $is_cust_cost ? $tar[0]['cost'] : VikRentItems::sayCostPlusIva($tar[0]['cost'] * $oi['itemquant'], $tar[0]['idprice'], $order[0]);
				$costminusiva = $is_cust_cost ? VikRentItems::sayCustCostMinusIva($tar[0]['cost'], $oi['cust_idiva']) : VikRentItems::sayCostMinusIva($tar[0]['cost'] * $oi['itemquant'], $tar[0]['idprice'], $order[0]);
				$pricestr = ($is_cust_cost ? JText::_('VRIRENTCUSTRATEPLAN').": ".$costplusiva : VikRentItems::getPriceName($tar[0]['idprice'], $vri_tn).": ".$costplusiva.(!empty($tar[0]['attrdata']) ? "\n".VikRentItems::getPriceAttr($tar[0]['idprice'], $vri_tn).": ".$tar[0]['attrdata'] : ""));
				$isdue += $is_cust_cost ? $tar[0]['cost'] : VikRentItems::sayCostPlusIva($tar[0]['cost'] * $oi['itemquant'], $tar[0]['idprice'], $order[0]);
				$optstr = "";
				$optarrtaxnet = array();
				if (!empty($oi['optionals'])) {
					$stepo = explode(";", $oi['optionals']);
					foreach ($stepo as $oo){
						if (!empty($oo)) {
							$stept = explode(":", $oo);
							$q = "SELECT * FROM `#__vikrentitems_optionals` WHERE `id`='".intval($stept[0])."';";
							$dbo->setQuery($q);
							$dbo->execute();
							if ($dbo->getNumRows() == 1) {
								$actopt = $dbo->loadAssocList();
								$vri_tn->translateContents($actopt, '#__vikrentitems_optionals');
								$specvar = '';
								if (!empty($actopt[0]['specifications']) && strstr($stept[1], '-') != false) {
									$optspeccosts = VikRentItems::getOptionSpecIntervalsCosts($actopt[0]['specifications']);
									$optspecnames = VikRentItems::getOptionSpecIntervalsNames($actopt[0]['specifications']);
									$specstept = explode('-', $stept[1]);
									$stept[1] = $specstept[0];
									$specvar = $specstept[1];
									$actopt[0]['specintv'] = $specvar;
									$actopt[0]['name'] .= ' ('.$optspecnames[($specvar - 1)].')';
									$actopt[0]['quan'] = $stept[1];
									$realcost = (intval($actopt[0]['perday']) == 1 ? (floatval($optspeccosts[($specvar - 1)]) * $order[0]['days'] * $stept[1]) : (floatval($optspeccosts[($specvar - 1)]) * $stept[1]));
								} else {
									$realcost = (intval($actopt[0]['perday'])==1 ? ($actopt[0]['cost'] * $order[0]['days'] * $stept[1]) : ($actopt[0]['cost'] * $stept[1]));
								}
								if (!empty($actopt[0]['maxprice']) && $actopt[0]['maxprice'] > 0 && $realcost > $actopt[0]['maxprice']) {
									$realcost = $actopt[0]['maxprice'];
									if (intval($actopt[0]['hmany']) == 1 && intval($stept[1]) > 1) {
										$realcost = $actopt[0]['maxprice'] * $stept[1];
									}
								}
								$opt_item_units = $actopt[0]['onceperitem'] ? 1 : $oi['itemquant'];
								$tmpopr = VikRentItems::sayOptionalsPlusIva($realcost * $opt_item_units, $actopt[0]['idiva'], $order[0]);
								$isdue += $tmpopr;
								$optnetprice = VikRentItems::sayOptionalsMinusIva($realcost * $opt_item_units, $actopt[0]['idiva'], $order[0]);
								$optarrtaxnet[] = $optnetprice;
								$optstr .= ($stept[1] > 1 ? $stept[1]." " : "").$actopt[0]['name'].": ".$tmpopr."\n";
							}
						}
					}
				}
				// VRI 1.6 - custom extra costs
				if (!empty($oi['extracosts'])) {
					$cur_extra_costs = json_decode($oi['extracosts'], true);
					foreach ($cur_extra_costs as $eck => $ecv) {
						$efee_cost = VikRentItems::sayOptionalsPlusIva($ecv['cost'], $ecv['idtax'], $order[0]);
						$isdue += $efee_cost;
						$efee_cost_without = VikRentItems::sayOptionalsMinusIva($ecv['cost'], $ecv['idtax'], $order[0]);
						$optarrtaxnet[] = $efee_cost_without;
						$optstr .= $ecv['name'].": ".$efee_cost."\n";
					}
				}
				//
				$arrayinfopdf = array('days' => $order[0]['days'], 'tarminusiva' => $costminusiva, 'tartax' => ($costplusiva - $costminusiva), 'opttaxnet' => $optarrtaxnet, 'locfeenet' => $locfeewithouttax);
				$vricart[$oi['iditem']][$koi]['itemquant'] = $oi['itemquant'];
				$vricart[$oi['iditem']][$koi]['info'] = VikRentItems::getItemInfo($oi['iditem'], $vri_tn);
				$vricart[$oi['iditem']][$koi]['pricestr'] = $pricestr;
				$vricart[$oi['iditem']][$koi]['optstr'] = $optstr;
				$vricart[$oi['iditem']][$koi]['optarrtaxnet'] = $optarrtaxnet;
				$vricart[$oi['iditem']][$koi]['infopdf'] = $arrayinfopdf;
				if (!empty($oi['timeslot'])) {
					$vricart[$oi['iditem']][$koi]['timeslot']['name'] = $oi['timeslot'];
				}
				if (!empty($oi['deliveryaddr'])) {
					$vricart[$oi['iditem']][$koi]['delivery']['vrideliveryaddress'] = $oi['deliveryaddr'];
					$vricart[$oi['iditem']][$koi]['delivery']['vrideliverydistance'] = $oi['deliverydist'];
				}
			}
			//delivery service
			if ($totdelivery > 0) {
				$isdue += $totdelivery;
			}
			//
			$usedcoupon = false;
			$origisdue = $isdue;
			if (strlen($order[0]['coupon']) > 0) {
				$usedcoupon = true;
				$expcoupon = explode(";", $order[0]['coupon']);
				$isdue = $isdue - $expcoupon[1];
			}
			if (!empty($order[0]['custmail'])) {
				$sendpdf = true;
				if (!$checkdbsendpdf) {
					$psendpdf = VikRequest::getString('sendpdf', '', 'request');
					if ($psendpdf != "1") {
						$sendpdf = false;
					}
				}
				$mainframe->enqueueMessage(JText::sprintf('VRORDERMAILRESENT', $order[0]['custmail']));
				$saystatus = $order[0]['status'] == 'confirmed' ? JText::_('VRIOMPLETED') : JText::_('VRSTANDBY');
				VikRentItems::sendCustMailFromBack($order[0]['custmail'], strip_tags($ftitle)." ".JText::_('VRRENTALORD'), $ftitle, $nowts, $order[0]['custdata'], $vricart, $order[0]['ritiro'], $order[0]['consegna'], $isdue, $viklink, $saystatus, $ritplace, $consegnaplace, $maillocfee, $order[0]['id'], $order[0]['coupon'], $sendpdf, $totdelivery);
			} else {
				VikError::raiseWarning('', JText::_('VRORDERMAILRESENTNOREC'));
			}
		}
		$mainframe->redirect("index.php?option=com_vikrentitems&task=editorder&cid[]=".$oid);
	}

	function sortcarat() {
		$sortid = VikRequest::getVar('cid', array(0));
		$pmode = VikRequest::getString('mode', '', 'request');
		$dbo = JFactory::getDBO();
		$mainframe = JFactory::getApplication();
		if (!empty($pmode)) {
			$q = "SELECT `id`,`ordering` FROM `#__vikrentitems_caratteristiche` ORDER BY `#__vikrentitems_caratteristiche`.`ordering` ASC;";
			$dbo->setQuery($q);
			$dbo->execute();
			$totr=$dbo->getNumRows();
			if ($totr > 1) {
				$data = $dbo->loadAssocList();
				if ($pmode=="up") {
					foreach ($data as $v){
						if ($v['id']==$sortid[0]) {
							$y=$v['ordering'];
						}
					}
					if ($y && $y > 1) {
						$vik=$y - 1;
						$found=false;
						foreach ($data as $v){
							if (intval($v['ordering'])==intval($vik)) {
								$found=true;
								$q = "UPDATE `#__vikrentitems_caratteristiche` SET `ordering`='".$y."' WHERE `id`='".$v['id']."' LIMIT 1;";
								$dbo->setQuery($q);
								$dbo->execute();
								$q = "UPDATE `#__vikrentitems_caratteristiche` SET `ordering`='".$vik."' WHERE `id`='".$sortid[0]."' LIMIT 1;";
								$dbo->setQuery($q);
								$dbo->execute();
								break;
							}
						}
						if (!$found) {
							$q = "UPDATE `#__vikrentitems_caratteristiche` SET `ordering`='".$vik."' WHERE `id`='".$sortid[0]."' LIMIT 1;";
							$dbo->setQuery($q);
							$dbo->execute();
						}
					}
				} elseif ($pmode=="down") {
					foreach ($data as $v){
						if ($v['id']==$sortid[0]) {
							$y=$v['ordering'];
						}
					}
					if ($y) {
						$vik=$y + 1;
						$found=false;
						foreach ($data as $v){
							if (intval($v['ordering'])==intval($vik)) {
								$found=true;
								$q = "UPDATE `#__vikrentitems_caratteristiche` SET `ordering`='".$y."' WHERE `id`='".$v['id']."' LIMIT 1;";
								$dbo->setQuery($q);
								$dbo->execute();
								$q = "UPDATE `#__vikrentitems_caratteristiche` SET `ordering`='".$vik."' WHERE `id`='".$sortid[0]."' LIMIT 1;";
								$dbo->setQuery($q);
								$dbo->execute();
								break;
							}
						}
						if (!$found) {
							$q = "UPDATE `#__vikrentitems_caratteristiche` SET `ordering`='".$vik."' WHERE `id`='".$sortid[0]."' LIMIT 1;";
							$dbo->setQuery($q);
							$dbo->execute();
						}
					}
				}
			}
			$mainframe->redirect("index.php?option=com_vikrentitems&task=carat");
		} else {
			$mainframe->redirect("index.php?option=com_vikrentitems");
		}
	}

	function sortoptional() {
		$sortid = VikRequest::getVar('cid', array(0));
		$pmode = VikRequest::getString('mode', '', 'request');
		$dbo = JFactory::getDBO();
		$mainframe = JFactory::getApplication();
		if (!empty($pmode)) {
			$q = "SELECT `id`,`ordering` FROM `#__vikrentitems_optionals` ORDER BY `#__vikrentitems_optionals`.`ordering` ASC;";
			$dbo->setQuery($q);
			$dbo->execute();
			$totr=$dbo->getNumRows();
			if ($totr > 1) {
				$data = $dbo->loadAssocList();
				if ($pmode=="up") {
					foreach ($data as $v){
						if ($v['id']==$sortid[0]) {
							$y=$v['ordering'];
						}
					}
					if ($y && $y > 1) {
						$vik=$y - 1;
						$found=false;
						foreach ($data as $v){
							if (intval($v['ordering'])==intval($vik)) {
								$found=true;
								$q = "UPDATE `#__vikrentitems_optionals` SET `ordering`='".$y."' WHERE `id`='".$v['id']."' LIMIT 1;";
								$dbo->setQuery($q);
								$dbo->execute();
								$q = "UPDATE `#__vikrentitems_optionals` SET `ordering`='".$vik."' WHERE `id`='".$sortid[0]."' LIMIT 1;";
								$dbo->setQuery($q);
								$dbo->execute();
								break;
							}
						}
						if (!$found) {
							$q = "UPDATE `#__vikrentitems_optionals` SET `ordering`='".$vik."' WHERE `id`='".$sortid[0]."' LIMIT 1;";
							$dbo->setQuery($q);
							$dbo->execute();
						}
					}
				} elseif ($pmode=="down") {
					foreach ($data as $v){
						if ($v['id']==$sortid[0]) {
							$y=$v['ordering'];
						}
					}
					if ($y) {
						$vik=$y + 1;
						$found=false;
						foreach ($data as $v){
							if (intval($v['ordering'])==intval($vik)) {
								$found=true;
								$q = "UPDATE `#__vikrentitems_optionals` SET `ordering`='".$y."' WHERE `id`='".$v['id']."' LIMIT 1;";
								$dbo->setQuery($q);
								$dbo->execute();
								$q = "UPDATE `#__vikrentitems_optionals` SET `ordering`='".$vik."' WHERE `id`='".$sortid[0]."' LIMIT 1;";
								$dbo->setQuery($q);
								$dbo->execute();
								break;
							}
						}
						if (!$found) {
							$q = "UPDATE `#__vikrentitems_optionals` SET `ordering`='".$vik."' WHERE `id`='".$sortid[0]."' LIMIT 1;";
							$dbo->setQuery($q);
							$dbo->execute();
						}
					}
				}
			}
			$mainframe->redirect("index.php?option=com_vikrentitems&task=optionals");
		} else {
			$mainframe->redirect("index.php?option=com_vikrentitems");
		}
	}

	function discounts() {
		VikRentItemsHelper::printHeader("discounts");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'discounts'));
	
		parent::display();

		if (VikRentItems::showFooter()) {
			VikRentItemsHelper::printFooter();
		}
	}

	function newdiscount() {
		VikRentItemsHelper::printHeader("discounts");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'managediscount'));
	
		parent::display();

		if (VikRentItems::showFooter()) {
			VikRentItemsHelper::printFooter();
		}
	}

	function editdiscount() {
		VikRentItemsHelper::printHeader("discounts");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'managediscount'));
	
		parent::display();

		if (VikRentItems::showFooter()) {
			VikRentItemsHelper::printFooter();
		}
	}

	function creatediscount() {
		$pdiffcost = VikRequest::getString('diffcost', '', 'request');
		$piditems = VikRequest::getVar('iditems', array(0));
		$pdiscname = VikRequest::getString('discname', '', 'request');
		$pquantity = VikRequest::getInt('quantity', '', 'request');
		$pquantity = $pquantity < 1 ? 1 : $pquantity;
		$pifmorequant = VikRequest::getInt('ifmorequant', '', 'request');
		$pifmorequant = $pifmorequant == 1 ? 1 : 0;
		$pval_pcent = VikRequest::getString('val_pcent', '', 'request');
		$pval_pcent = $pval_pcent == "1" ? 1 : 2;
		$dbo = JFactory::getDBO();
		$mainframe = JFactory::getApplication();
		if (strlen($pdiffcost) > 0) {
			$itemstr="";
			if (@count($piditems) > 0) {
				foreach ($piditems as $item) {
					$itemstr.="-".$item."-,";
				}
			}
			$q = "INSERT INTO `#__vikrentitems_discountsquants` (`discname`,`iditems`,`quantity`,`val_pcent`,`diffcost`,`ifmorequant`) VALUES(".$dbo->quote($pdiscname).", ".$dbo->quote($itemstr).", '".$pquantity."', '".$pval_pcent."', ".$dbo->quote($pdiffcost).", '".$pifmorequant."');";
			$dbo->setQuery($q);
			$dbo->execute();
			$mainframe->enqueueMessage(JText::_('VRIDISCOUNTSAVED'));
			$mainframe->redirect("index.php?option=com_vikrentitems&task=discounts");
		} else {
			$mainframe->redirect("index.php?option=com_vikrentitems&task=newdiscount");
		}
	}

	function updatediscount() {
		$pwhere = VikRequest::getString('where', '', 'request');
		$pdiffcost = VikRequest::getString('diffcost', '', 'request');
		$piditems = VikRequest::getVar('iditems', array(0));
		$pdiscname = VikRequest::getString('discname', '', 'request');
		$pquantity = VikRequest::getInt('quantity', '', 'request');
		$pquantity = $pquantity < 1 ? 1 : $pquantity;
		$pifmorequant = VikRequest::getInt('ifmorequant', '', 'request');
		$pifmorequant = $pifmorequant == 1 ? 1 : 0;
		$pval_pcent = VikRequest::getString('val_pcent', '', 'request');
		$pval_pcent = $pval_pcent == "1" ? 1 : 2;
		$dbo = JFactory::getDBO();
		$mainframe = JFactory::getApplication();
		if (strlen($pdiffcost) > 0) {
			$itemstr="";
			if (@count($piditems) > 0) {
				foreach ($piditems as $item) {
					$itemstr.="-".$item."-,";
				}
			}
			$q = "UPDATE `#__vikrentitems_discountsquants` SET `discname`=".$dbo->quote($pdiscname).",`iditems`=".$dbo->quote($itemstr).",`quantity`='".$pquantity."',`val_pcent`='".$pval_pcent."',`diffcost`=".$dbo->quote($pdiffcost).",`ifmorequant`='".$pifmorequant."' WHERE `id`='".$pwhere."';";
			$dbo->setQuery($q);
			$dbo->execute();
			$mainframe->enqueueMessage(JText::_('VRIDISCOUNTUPDATED'));
			$mainframe->redirect("index.php?option=com_vikrentitems&task=discounts");
		} else {
			$mainframe->redirect("index.php?option=com_vikrentitems&task=editdiscount&cid[]=".$pwhere);
		}
	}

	function removediscounts() {
		$ids = VikRequest::getVar('cid', array(0));
		if (@count($ids)) {
			$dbo = JFactory::getDBO();
			foreach ($ids as $d){
				$q = "DELETE FROM `#__vikrentitems_discountsquants` WHERE `id`=".intval($d).";";
				$dbo->setQuery($q);
				$dbo->execute();
			}
		}
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikrentitems&task=discounts");
	}

	function canceldiscount() {
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikrentitems&task=discounts");
	}

	function timeslots() {
		VikRentItemsHelper::printHeader("timeslots");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'timeslots'));
	
		parent::display();

		if (VikRentItems::showFooter()) {
			VikRentItemsHelper::printFooter();
		}
	}

	function newtimeslot() {
		VikRentItemsHelper::printHeader("timeslots");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'managetimeslot'));
	
		parent::display();

		if (VikRentItems::showFooter()) {
			VikRentItemsHelper::printFooter();
		}
	}

	function edittimeslot() {
		VikRentItemsHelper::printHeader("timeslots");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'managetimeslot'));
	
		parent::display();

		if (VikRentItems::showFooter()) {
			VikRentItemsHelper::printFooter();
		}
	}

	function createtimeslot() {
		$piditems = VikRequest::getVar('iditems', array(0));
		$ptname = VikRequest::getString('tname', '', 'request');
		$pfromh = VikRequest::getString('fromh', '', 'request');
		$pfromm = VikRequest::getString('fromm', '', 'request');
		$ptoh = VikRequest::getString('toh', '', 'request');
		$ptom = VikRequest::getString('tom', '', 'request');
		$pglobal = VikRequest::getString('global', '', 'request');
		$pglobal = $pglobal == 1 ? 1 : 0;
		$pdays = VikRequest::getString('days', '', 'request');
		$pdays = intval($pdays) < 0 ? 0 : intval($pdays);
		$dbo = JFactory::getDBO();
		$mainframe = JFactory::getApplication();
		if (strlen($ptname) > 0 && strlen($pfromh) > 0 && strlen($ptoh) > 0) {
			$itemstr="";
			if (@count($piditems) > 0) {
				foreach ($piditems as $item) {
					$itemstr.="-".$item."-,";
				}
			}
			$q = "INSERT INTO `#__vikrentitems_timeslots` (`tname`,`fromh`,`fromm`,`toh`,`tom`,`iditems`,`global`,`days`) VALUES(".$dbo->quote($ptname).", '".$pfromh."', '".$pfromm."', '".$ptoh."', '".$ptom."', ".$dbo->quote($itemstr).", '".$pglobal."', ".$pdays.");";
			$dbo->setQuery($q);
			$dbo->execute();
			$mainframe->enqueueMessage(JText::_('VRITIMESLOTSAVED'));
			$mainframe->redirect("index.php?option=com_vikrentitems&task=timeslots");
		} else {
			$mainframe->redirect("index.php?option=com_vikrentitems&task=newtimeslot");
		}
	}

	function updatetimeslot() {
		$piditems = VikRequest::getVar('iditems', array(0));
		$ptname = VikRequest::getString('tname', '', 'request');
		$pfromh = VikRequest::getString('fromh', '', 'request');
		$pfromm = VikRequest::getString('fromm', '', 'request');
		$ptoh = VikRequest::getString('toh', '', 'request');
		$ptom = VikRequest::getString('tom', '', 'request');
		$pwhere = VikRequest::getString('where', '', 'request');
		$pglobal = VikRequest::getString('global', '', 'request');
		$pglobal = $pglobal == 1 ? 1 : 0;
		$pdays = VikRequest::getString('days', '', 'request');
		$pdays = intval($pdays) < 0 ? 0 : intval($pdays);
		$dbo = JFactory::getDBO();
		$mainframe = JFactory::getApplication();
		if (strlen($ptname) > 0 && strlen($pfromh) > 0 && strlen($ptoh) > 0) {
			$itemstr="";
			if (@count($piditems) > 0) {
				foreach ($piditems as $item) {
					$itemstr.="-".$item."-,";
				}
			}
			$q = "UPDATE `#__vikrentitems_timeslots` SET `tname`=".$dbo->quote($ptname).",`fromh`='".$pfromh."',`fromm`='".$pfromm."',`toh`='".$ptoh."',`tom`='".$ptom."',`iditems`=".$dbo->quote($itemstr).",`global`='".$pglobal."',`days`=".$pdays." WHERE id='".$pwhere."';";
			$dbo->setQuery($q);
			$dbo->execute();
			$mainframe->enqueueMessage(JText::_('VRITIMESLOTUPDATED'));
			$mainframe->redirect("index.php?option=com_vikrentitems&task=timeslots");
		} else {
			$mainframe->redirect("index.php?option=com_vikrentitems&task=edittimeslot&cid[]=".$pwhere);
		}
	}

	function removeTimeslots() {
		$ids = VikRequest::getVar('cid', array(0));
		if (@count($ids)) {
			$dbo = JFactory::getDBO();
			foreach ($ids as $d){
				$q = "DELETE FROM `#__vikrentitems_timeslots` WHERE `id`=".intval($d).";";
				$dbo->setQuery($q);
				$dbo->execute();
			}
		}
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikrentitems&task=timeslots");
	}

	function canceltimeslot() {
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikrentitems&task=timeslots");
	}

	function relations() {
		VikRentItemsHelper::printHeader("relations");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'relations'));
	
		parent::display();

		if (VikRentItems::showFooter()) {
			VikRentItemsHelper::printFooter();
		}
	}

	function newrelation() {
		VikRentItemsHelper::printHeader("relations");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'managerelation'));
	
		parent::display();

		if (VikRentItems::showFooter()) {
			VikRentItemsHelper::printFooter();
		}
	}

	function editrelation() {
		VikRentItemsHelper::printHeader("relations");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'managerelation'));
	
		parent::display();

		if (VikRentItems::showFooter()) {
			VikRentItemsHelper::printFooter();
		}
	}

	function createrelation() {
		$piditems = VikRequest::getVar('iditems', array());
		$piditemstwo = VikRequest::getVar('iditemstwo', array(0));
		$prelname = VikRequest::getString('relname', '', 'request');
		$dbo = JFactory::getDBO();
		$mainframe = JFactory::getApplication();
		if (strlen($prelname) > 0 && count($piditems) > 0) {
			$itemstr="";
			$itemstrtwo="";
			if (@count($piditems) > 0) {
				foreach ($piditems as $item) {
					$itemstr.="-".$item."-;";
				}
			}
			if (@count($piditemstwo) > 0) {
				foreach ($piditemstwo as $item) {
					$itemstrtwo.="-".$item."-;";
				}
			}
			$q = "INSERT INTO `#__vikrentitems_relations` (`relname`,`relone`,`reltwo`) VALUES(".$dbo->quote($prelname).", '".$itemstr."', '".$itemstrtwo."');";
			$dbo->setQuery($q);
			$dbo->execute();
			$mainframe->enqueueMessage(JText::_('VRIRELATIONSAVED'));
			$mainframe->redirect("index.php?option=com_vikrentitems&task=relations");
		} else {
			$mainframe->redirect("index.php?option=com_vikrentitems&task=newrelation");
		}
	}

	function updaterelation() {
		$piditems = VikRequest::getVar('iditems', array());
		$piditemstwo = VikRequest::getVar('iditemstwo', array(0));
		$prelname = VikRequest::getString('relname', '', 'request');
		$dbo = JFactory::getDBO();
		$mainframe = JFactory::getApplication();
		$pwhere = VikRequest::getString('where', '', 'request');
		if (strlen($prelname) > 0 && count($piditems) > 0) {
			$itemstr="";
			$itemstrtwo="";
			if (@count($piditems) > 0) {
				foreach ($piditems as $item) {
					$itemstr.="-".$item."-;";
				}
			}
			if (@count($piditemstwo) > 0) {
				foreach ($piditemstwo as $item) {
					$itemstrtwo.="-".$item."-;";
				}
			}
			$q = "UPDATE `#__vikrentitems_relations` SET `relname`=".$dbo->quote($prelname).",`relone`='".$itemstr."',`reltwo`='".$itemstrtwo."' WHERE `id`='".(int)$pwhere."';";
			$dbo->setQuery($q);
			$dbo->execute();
			$mainframe->enqueueMessage(JText::_('VRIRELATIONUPDATED'));
			$mainframe->redirect("index.php?option=com_vikrentitems&task=relations");
		} else {
			$mainframe->redirect("index.php?option=com_vikrentitems&task=editrelation&cid[]=".$pwhere);
		}
	}

	function removerelations() {
		$ids = VikRequest::getVar('cid', array(0));
		if (@count($ids)) {
			$dbo = JFactory::getDBO();
			foreach ($ids as $d){
				$q = "DELETE FROM `#__vikrentitems_relations` WHERE `id`=".intval($d).";";
				$dbo->setQuery($q);
				$dbo->execute();
			}
		}
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikrentitems&task=relations");
	}

	function cancelrelation() {
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikrentitems&task=relations");
	}

	function validatebaseaddr() {
		VikRequest::setVar('view', VikRequest::getCmd('view', 'validatebaseaddr'));
	
		parent::display();
	}

	function export() {
		VikRentItemsHelper::printHeader("8");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'export'));
	
		parent::display();

		if (VikRentItems::showFooter()) {
			VikRentItemsHelper::printFooter();
		}
	}

	function doexport() {
		$dbo = JFactory::getDBO();
		$mainframe = JFactory::getApplication();
		$oids = VikRequest::getVar('cid', array(0));
		$oids = count($oids) > 0 && intval($oids[key($oids)]) > 0 ? $oids : array();
		$pfrom = VikRequest::getString('from', '', 'request');
		$pto = VikRequest::getString('to', '', 'request');
		$pdatetype = VikRequest::getString('datetype', '', 'request');
		$pdatetype = $pdatetype == 'ts' ? 'ts' : 'ritiro';
		$plocation = VikRequest::getString('location', '', 'request');
		$ptype = VikRequest::getString('type', '', 'request');
		// $ptype = $ptype == "csv" ? "csv" : "ics";
		$pstatus = VikRequest::getString('status', '', 'request');
		$pdateformat = VikRequest::getString('dateformat', '', 'request');
		$nowdf = VikRentItems::getDateFormat(true);
		$nowtf = VikRentItems::getTimeFormat(true);
		$pdateformat .= ' '.$nowtf;
		$tf = $nowtf;
		if ($nowdf == "%d/%m/%Y") {
			$df = 'd/m/Y';
		} elseif ($nowdf == "%m/%d/%Y") {
			$df = 'm/d/Y';
		} else {
			$df = 'Y/m/d';
		}
		$clauses = array();
		if (count($oids) > 0) {
			$clauses[] = "`o`.`id` IN(".implode(',', $oids).")";
		}
		if ($pstatus == "C") {
			$clauses[] = "`o`.`status`='confirmed'";
		}
		if (!empty($pfrom) && VikRentItems::dateIsValid($pfrom)) {
			$fromts = VikRentItems::getDateTimestamp($pfrom, '0', '0');
			$clauses[] = "`o`.`".$pdatetype."`>=".$fromts;
		}
		if (!empty($pto) && VikRentItems::dateIsValid($pto)) {
			$tots = VikRentItems::getDateTimestamp($pto, '23', '59');
			$clauses[] = "`o`.`".$pdatetype."`<=".$tots;
		}
		if (!empty($plocation)) {
			$clauses[] = "(`o`.`idplace`=".intval($plocation)." OR `o`.`idreturnplace`=".intval($plocation).")";
		}
		$q = "SELECT `o`.* FROM `#__vikrentitems_orders` AS `o`".(count($clauses) > 0 ? " WHERE ".implode(' AND ', $clauses) : "")." ORDER BY `o`.`id` ASC;";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$rows = $dbo->loadAssocList();
			if ($ptype == "csv") {
				//init csv creation
				$csvlines = array();
				$csvlines[] = array('ID', JText::_('VRIEXPCSVPICK'), JText::_('VRIEXPCSVDROP'), JText::_('VRIEXPCSVITEMS'), JText::_('VRIEXPCSVPICKLOC'), JText::_('VRIEXPCSVDROPLOC'), JText::_('VRIEXPCSVCUSTINFO'), JText::_('VRIEXPCSVPAYMETH'), JText::_('VRIEXPCSVORDSTATUS'), JText::_('VRIEXPCSVTOT'), JText::_('VRIEXPCSVTOTPAID'));
				foreach ($rows as $r) {
					$pickdate = $pdatetype == 'ts' ? $r['ritiro'] : date($pdateformat, $r['ritiro']);
					$dropdate = $pdatetype == 'ts' ? $r['consegna'] : date($pdateformat, $r['consegna']);
					$nowitems = array();
					$q = "SELECT `oi`.`itemquant`,`i`.`name` FROM `#__vikrentitems_ordersitems` AS `oi` LEFT JOIN `#__vikrentitems_items` `i` ON `i`.`id`=`oi`.`iditem` WHERE `oi`.`idorder`=".$r['id'].";";
					$dbo->setQuery($q);
					$dbo->execute();
					if ($dbo->getNumRows() > 0) {
						$allitems = $dbo->loadAssocList();
						foreach ($allitems as $it) {
							$nowitems[] = ($it['itemquant'] > 1 ? 'x'.$it['itemquant'].' ' : '').$it['name'];
						}
					}
					$pickloc = VikRentItems::getPlaceName($r['idplace']);
					$droploc = VikRentItems::getPlaceName($r['idreturnplace']);
					$custdata = preg_replace('/\s+/', ' ', trim($r['custdata']));
					$payment = VikRentItems::getPayment($r['idpayment']);
					$saystatus = ($r['status']=="confirmed" ? JText::_('VRIONFIRMED') : JText::_('VRSTANDBY'));
					$csvlines[] = array($r['id'], $pickdate, $dropdate, implode(', ', $nowitems), $pickloc, $droploc, $custdata, $payment['name'], $saystatus, VikRentItems::numberFormat($r['order_total']), VikRentItems::numberFormat($r['totpaid']));
				}
				//end csv creation
			} else if($ptype == "ics") {
				$icslines = array();
				$icscontent = "BEGIN:VCALENDAR\n";
				$icscontent .= "VERSION:2.0\n";
				$icscontent .= "PRODID:-//e4j//VikRentItems//EN\n";
				$icscontent .= "CALSCALE:GREGORIAN\n";
				$icscontent .= "X-WR-TIMEZONE:".date_default_timezone_get()."\n";
				$str = "";
				foreach ($rows as $r) {
					$uri = JURI::root().'index.php?option=com_vikrentitems&task=vieworder&sid='.$r['sid'].'&ts='.$r['ts'];
					$nowitems = array();
					$q = "SELECT `oi`.`itemquant`,`i`.`name` FROM `#__vikrentitems_ordersitems` AS `oi` LEFT JOIN `#__vikrentitems_items` `i` ON `i`.`id`=`oi`.`iditem` WHERE `oi`.`idorder`=".$r['id'].";";
					$dbo->setQuery($q);
					$dbo->execute();
					if ($dbo->getNumRows() > 0) {
						$allitems = $dbo->loadAssocList();
						foreach ($allitems as $it) {
							$nowitems[] = ($it['itemquant'] > 1 ? 'x'.$it['itemquant'].' ' : '').$it['name'];
						}
					}
					$pickloc = VikRentItems::getPlaceName($r['idplace']);
					//$custdata = preg_replace('/\s+/', ' ', trim($r['custdata']));
					$description = implode(', ', $nowitems)."\\n".str_replace("\n", "\\n", trim($r['custdata']));
					$str .= "BEGIN:VEVENT\n";
					//End of the Event set as Drop Off Date, decomment line below to have it on Pickup Date
					//$str .= "DTEND:".date('Ymd\THis\Z', $r['ritiro'])."\n";
					$str .= "DTEND;TZID=".date_default_timezone_get().":".date('Ymd\THis', $r['consegna'])."\n";
					//
					$str .= "UID:".uniqid()."\n";
					//Date format for DTSTAMP is with Timezone info (\Z)
					$str .= "DTSTAMP:".date('Ymd\THis\Z', time())."\n";
					$str .= "LOCATION:".preg_replace('/([\,;])/','\\\$1', $pickloc)."\n";
					$str .= ((strlen($description) > 0 ) ? "DESCRIPTION:".preg_replace('/([\,;])/','\\\$1', $description)."\n" : "");
					$str .= "URL;VALUE=URI:".preg_replace('/([\,;])/','\\\$1', $uri)."\n";
					$str .= "SUMMARY:".JText::sprintf('VRIICSEXPSUMMARY', date($tf, $r['ritiro']))."\n";
					$str .= "DTSTART;TZID=".date_default_timezone_get().":".date('Ymd\THis', $r['ritiro'])."\n";
					$str .= "END:VEVENT\n";
				}
				$icscontent .= $str;
				$icscontent .= "END:VCALENDAR\n";
			} else if($ptype == "xlsx"){
                // Create new Spreadsheet object
                $spreadsheet = new Spreadsheet();

                // Set document properties
                $spreadsheet->getProperties()->setCreator('BrightShine81')
                    ->setLastModifiedBy('BrightShine81')
                    ->setTitle('Office 2007 XLSX Document')
                    ->setSubject('Office 2007 XLSX Document')
                    ->setDescription('Powered by fivestarsmobi.com')
                    ->setKeywords('office 2007 openxml php')
                    ->setCategory('auto-generated');

                // columns
                $csvlines[] = array('ID', JText::_('VRIEXPCSVPICK'), JText::_('VRIEXPCSVDROP'), JText::_('VRIEXPCSVITEMS'), JText::_('VRIEXPCSVPICKLOC'), JText::_('VRIEXPCSVDROPLOC'),
                    "Nom", "Prénom", "e-Mail", "Téléphone", "Code postal", "Ville", JText::_('VRIEXPCSVPAYMETH'), JText::_('VRIEXPCSVORDSTATUS'), JText::_('VRIEXPCSVTOT'), JText::_('VRIEXPCSVTOTPAID'));

                foreach ($rows as $r) {
                    $pickdate = $pdatetype == 'ts' ? $r['ritiro'] : date($pdateformat, $r['ritiro']);
                    $dropdate = $pdatetype == 'ts' ? $r['consegna'] : date($pdateformat, $r['consegna']);
                    $nowitems = array();
                    $q = "SELECT `oi`.`itemquant`,`i`.`name` FROM `#__vikrentitems_ordersitems` AS `oi` LEFT JOIN `#__vikrentitems_items` `i` ON `i`.`id`=`oi`.`iditem` WHERE `oi`.`idorder`=".$r['id'].";";
                    $dbo->setQuery($q);
                    $dbo->execute();
                    if ($dbo->getNumRows() > 0) {
                        $allitems = $dbo->loadAssocList();
                        foreach ($allitems as $it) {
                            $nowitems[] = ($it['itemquant'] > 1 ? 'x'.$it['itemquant'].' ' : '').$it['name'];
                        }
                    }
                    $pickloc = VikRentItems::getPlaceName($r['idplace']);
                    $droploc = VikRentItems::getPlaceName($r['idreturnplace']);
                    $custdata = trim($r['custdata']);
                    $custlines = explode("\n", $custdata);

                    $custfields = [];
                    foreach ($custlines as $custline) {
                        $segments = explode(":", $custline);

                        if(count($segments) == 2) {
                            $key = trim($segments[0]);
                            $value = trim($segments[1]);

                            $custfields[$key] = $value;
                        }
                    }

                    $payment = VikRentItems::getPayment($r['idpayment']);
                    $saystatus = $r['status']; // ($r['status']=="confirmed" ? JText::_('VRIONFIRMED') : JText::_('VRSTANDBY'));
                    $csvlines[] = array($r['id'], $pickdate, $dropdate, implode(', ', $nowitems), $pickloc, $droploc,
                        $custfields["Nom"], $custfields["Prénom"], $custfields["e-Mail"], $custfields["Téléphone"], $custfields["Code postal"], $custfields["Ville"],
                        $payment['name'], $saystatus, VikRentItems::numberFormat($r['order_total']), VikRentItems::numberFormat($r['totpaid']));
                }

                $spreadsheet->getActiveSheet()
                    ->fromArray(
                        $csvlines,  // The data to set
                        NULL,        // Array values with this value will not be set
                        'A1'         // Top left coordinate of the worksheet range where
                    //    we want to set these values (default is A1)
                    );

                $spreadsheet->getActiveSheet()->getColumnDimension('A')->setWidth(8);
                $spreadsheet->getActiveSheet()->getColumnDimension('B')->setWidth(14);
                $spreadsheet->getActiveSheet()->getColumnDimension('C')->setWidth(14);
                $spreadsheet->getActiveSheet()->getColumnDimension('D')->setWidth(15);
                $spreadsheet->getActiveSheet()->getColumnDimension('E')->setWidth(15);
                $spreadsheet->getActiveSheet()->getColumnDimension('F')->setWidth(15);
                $spreadsheet->getActiveSheet()->getColumnDimension('G')->setWidth(12);
                $spreadsheet->getActiveSheet()->getColumnDimension('H')->setWidth(12);
                $spreadsheet->getActiveSheet()->getColumnDimension('I')->setWidth(25);
                $spreadsheet->getActiveSheet()->getColumnDimension('J')->setWidth(15);
                $spreadsheet->getActiveSheet()->getColumnDimension('K')->setWidth(12);
                $spreadsheet->getActiveSheet()->getColumnDimension('L')->setWidth(15);
                $spreadsheet->getActiveSheet()->getColumnDimension('M')->setWidth(25);
                $spreadsheet->getActiveSheet()->getColumnDimension('N')->setWidth(10);
                $spreadsheet->getActiveSheet()->getColumnDimension('O')->setWidth(15);
                $spreadsheet->getActiveSheet()->getColumnDimension('P')->setWidth(15);


                $spreadsheet->getActiveSheet()->getStyle('A1:P1')->getFont()->setBold(true);
                $spreadsheet->getActiveSheet()->getStyle('A1:P1')->getFont()->getColor()->setARGB(Color::COLOR_WHITE);
                $spreadsheet->getActiveSheet()->getStyle('A1:P1')->applyFromArray(
                    ['fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'color' => ['argb' => 'FF70AD47'],
                    ],
                    ]
                );

                $line_count = count($csvlines);

                $spreadsheet->getActiveSheet()->getStyle("O2:P$line_count")->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_00);
                $spreadsheet->getActiveSheet()->getStyle("O2:P$line_count")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $spreadsheet->getActiveSheet()->getStyle("J2:J$line_count")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);


                // Rename worksheet
                $spreadsheet->getActiveSheet()->setTitle('Orders');

                // Set active sheet index to the first sheet, so Excel opens this as the first sheet
                $spreadsheet->setActiveSheetIndex(0);

            }
			//download file from buffer
			$dfilename = 'export_'.date('Y-m-d_H_i').'.'.$ptype;
			if ($ptype == "csv") {
				header("Content-type: text/csv");
				header("Cache-Control: no-store, no-cache");
				header('Content-Disposition: attachment; filename="'.$dfilename.'"');
				$outstream = fopen("php://output", 'w');
				foreach ($csvlines as $csvline) {
					fputcsv($outstream, $csvline);
				}
				fclose($outstream);
				exit;
			} else if($ptype == "ics") {
				header("Content-Type: application/octet-stream; ");
				header("Cache-Control: no-store, no-cache");
				header("Content-Disposition: attachment; filename=\"".$dfilename."\"");
				$f = fopen('php://output', "w");
				fwrite($f, $icscontent);
				fclose($f);
				exit;
			} else if($ptype == "xlsx") {

			    // Redirect output to a client’s web browser (Xlsx)
                header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                header('Content-Disposition: attachment;filename="'.$dfilename.'"');
                header('Cache-Control: max-age=0');

                // If you're serving to IE 9, then the following may be needed
                header('Cache-Control: max-age=1');

                // If you're serving to IE over SSL, then the following may be needed
                header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
                header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
                header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
                header('Pragma: public'); // HTTP/1.0

                $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
                $writer->save('php://output');

                exit;
            }
		} else {
			VikError::raiseWarning('', JText::_('VRIEXPORTERRNOREC'));
			$mainframe->redirect("index.php?option=com_vikrentitems&task=orders");
		}
	}

	function loadpaymentparams() {
		$html = '---------';
		$phpfile = VikRequest::getString('phpfile', '', 'request');
		if (!empty($phpfile)) {
			$html = VikRentItems::displayPaymentParameters($phpfile);
		}
		echo $html;
		exit;
	}

	function translations() {
		VikRentItemsHelper::printHeader("20");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'translations'));
	
		parent::display();

		if (VikRentItems::showFooter()) {
			VikRentItemsHelper::printFooter();
		}
	}

	function savetranslation() {
		$this->do_savetranslation();
	}

	function savetranslationstay() {
		$this->do_savetranslation(true);
	}

	private function do_savetranslation($stay = false) {
		$dbo = JFactory::getDBO();
		$mainframe = JFactory::getApplication();
		$vri_tn = VikRentItems::getTranslator();
		$table = VikRequest::getString('vri_table', '', 'request');
		$cur_langtab = VikRequest::getString('vri_lang', '', 'request');
		$langs = $vri_tn->getLanguagesList();
		$xml_tables = $vri_tn->getTranslationTables();
		if (!empty($table) && array_key_exists($table, $xml_tables)) {
			$tn = VikRequest::getVar('tn', array(), 'request', 'array', VIKREQUEST_ALLOWRAW);
			$tn_saved = 0;
			$table_cols = $vri_tn->getTableColumns($table);
			$table = $vri_tn->replacePrefix($table);
			foreach ($langs as $ltag => $lang) {
				if ($ltag == $vri_tn->default_lang) {
					continue;
				}
				if (array_key_exists($ltag, $tn) && count($tn[$ltag]) > 0) {
					foreach ($tn[$ltag] as $reference_id => $translation) {
						$lang_translation = array();
						foreach ($table_cols as $field => $fdetails) {
							if (!array_key_exists($field, $translation)) {
								continue;
							}
							$ftype = $fdetails['type'];
							if ($ftype == 'skip') {
								continue;
							}
							if ($ftype == 'json') {
								$translation[$field] = json_encode($translation[$field]);
							}
							$lang_translation[$field] = $translation[$field];
						}
						if (count($lang_translation) > 0) {
							$q = "SELECT `id` FROM `#__vikrentitems_translations` WHERE `table`=".$dbo->quote($table)." AND `lang`=".$dbo->quote($ltag)." AND `reference_id`=".$dbo->quote((int)$reference_id).";";
							$dbo->setQuery($q);
							$dbo->execute();
							if ($dbo->getNumRows() > 0) {
								$last_id = $dbo->loadResult();
								$q = "UPDATE `#__vikrentitems_translations` SET `content`=".$dbo->quote(json_encode($lang_translation))." WHERE `id`=".(int)$last_id.";";
							} else {
								$q = "INSERT INTO `#__vikrentitems_translations` (`table`,`lang`,`reference_id`,`content`) VALUES (".$dbo->quote($table).", ".$dbo->quote($ltag).", ".$dbo->quote((int)$reference_id).", ".$dbo->quote(json_encode($lang_translation)).");";
							}
							$dbo->setQuery($q);
							$dbo->execute();
							$tn_saved++;
						}
					}
				}
			}
			if ($tn_saved > 0) {
				$mainframe->enqueueMessage(JText::_('VRITRANSLSAVEDOK'));
			}
		} else {
			VikError::raiseWarning('', JText::_('VRITRANSLATIONERRINVTABLE'));
		}
		$mainframe->redirect("index.php?option=com_vikrentitems".($stay ? '&task=translations&vri_table='.$vri_tn->replacePrefix($table).'&vri_lang='.$cur_langtab : ''));
	}

	function edittmplfile() {
		//modal box, so we do not set menu or footer

		VikRequest::setVar('view', VikRequest::getCmd('view', 'edittmplfile'));
	
		parent::display();
	}

	function savetmplfile() {
		$fpath = VikRequest::getString('path', '', 'request', VIKREQUEST_ALLOWRAW);
		$pcont = VikRequest::getString('cont', '', 'request', VIKREQUEST_ALLOWRAW);
		$mainframe = JFactory::getApplication();
		$exists = file_exists($fpath) ? true : false;
		if (!$exists) {
			$fpath = urldecode($fpath);
		}
		$fpath = file_exists($fpath) ? $fpath : '';
		if (!empty($fpath)) {
			$fp = fopen($fpath, 'wb');
			$byt = (int)fwrite($fp, $pcont);
			fclose($fp);
			if ($byt > 0) {
				$mainframe->enqueueMessage(JText::_('VRIUPDTMPLFILEOK'));
			} else {
				VikError::raiseWarning('', JText::_('VRIUPDTMPLFILENOBYTES'));
			}
		} else {
			VikError::raiseWarning('', JText::_('VRIUPDTMPLFILEERR'));
		}
		$mainframe->redirect("index.php?option=com_vikrentitems&task=edittmplfile&path=".$fpath."&tmpl=component");
		exit;
	}

	function unlockrecords() {
		$ids = VikRequest::getVar('cid', array(0));
		if (@count($ids)) {
			$dbo = JFactory::getDBO();
			foreach ($ids as $d){
				$q = "DELETE FROM `#__vikrentitems_tmplock` WHERE `id`=".$dbo->quote($d).";";
				$dbo->setQuery($q);
				$dbo->execute();
			}
		}
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikrentitems");
	}

	function checkversion() {
		$params = new stdClass;
		$params->version 	= E4J_SOFTWARE_VERSION;
		$params->alias 		= 'com_vikrentitems';

		JPluginHelper::importPlugin('e4j');
		$dispatcher = JEventDispatcher::getInstance();

		$result = $dispatcher->trigger('checkVersion', array(&$params));

		if (!count($result)) {
			$result = new stdClass;
			$result->status = 0;
		} else {
			$result = $result[0];
		}

		echo json_encode($result);
		exit;
	}

	function updateprogram() {
		$params = new stdClass;
		$params->version 	= E4J_SOFTWARE_VERSION;
		$params->alias 		= 'com_vikrentitems';

		JPluginHelper::importPlugin('e4j');
		$dispatcher = JEventDispatcher::getInstance();
		
		$result = $dispatcher->trigger('getVersionContents', array(&$params));

		if (!count($result) || !$result[0]) {
			$result = $dispatcher->trigger('checkVersion', array(&$params));
		}

		if (!count($result) || !$result[0]->status || !$result[0]->response->status) {
			exit('Error, plugin disabled');
		}

		JToolbarHelper::title(JText::_('VRMAINTITLEUPDATEPROGRAM'));

		VikRentItemsHelper::pUpdateProgram($result[0]->response);
	}

	function updateprogramlaunch() {
		$params = new stdClass;
		$params->version 	= E4J_SOFTWARE_VERSION;
		$params->alias 		= 'com_vikrentitems';

		JPluginHelper::importPlugin('e4j');
		$dispatcher = JEventDispatcher::getInstance();

		$json = new stdClass;
		$json->status = false;

		try {

			$result = $dispatcher->trigger('doUpdate', array(&$params));

			if ( count($result) ) {
				$json->status = (bool) $result[0];
			} else {
				$json->error = 'plugin disabled.';
			}

		} catch(Exception $e) {

			$json->status = false;
			$json->error = $e->getMessage();

		}

		echo json_encode($json);
		exit;
	}

	function cloneitem() {
		$ids = VikRequest::getVar('cid', array(0));
		$itid = isset($ids[0]) ? $ids[0] : 0;
		$dbo = JFactory::getDBO();
		$mainframe = JFactory::getApplication();
		if (empty($itid)) {
			VikError::raiseWarning('', 'Empty Item ID for cloning');
			$mainframe->redirect('index.php?option=com_vikrentitems&task=items');
			exit;
		}
		$q = "SELECT * FROM `#__vikrentitems_items` WHERE `id`=".(int)$itid.";";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$parentitem = $dbo->loadAssoc();
			//change some values from the parent Item
			unset($parentitem['id']);
			$parentitem['name'] .= ' '.JText::_('VRICLONEITEMCOPY');
			$parentitem['alias'] .= '-copy'.date('njgi');
			//
			$itemcols = array();
			$itemvals = array();
			foreach ($parentitem as $col => $val) {
				array_push($itemcols, '`'.$col.'`');
				if ($val == null) {
					array_push($itemvals, 'NULL');
				} else {
					array_push($itemvals, $dbo->quote($val));
				}
			}
			$q = "INSERT INTO `#__vikrentitems_items` (".implode(', ', $itemcols).") VALUES(".implode(', ', $itemvals).");";
			$dbo->setQuery($q);
			$dbo->execute();
			$newid = $dbo->insertid();
			//check discounts per quantity
			$q = "SELECT `id`,`iditems` FROM `#__vikrentitems_discountsquants` WHERE `iditems` LIKE '%-".(int)$itid."-%';";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() > 0) {
				$discounts = $dbo->loadAssocList();
				foreach ($discounts as $disc) {
					$q = "UPDATE `#__vikrentitems_discountsquants` SET `iditems`=".$dbo->quote($disc['iditems'].'-'.$newid.'-,')." WHERE `id`=".$disc['id'].";";
					$dbo->setQuery($q);
					$dbo->execute();
				}
			}
			//
			//check daily fares
			$q = "SELECT * FROM `#__vikrentitems_dispcost` WHERE `iditem`=".(int)$itid.";";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() > 0) {
				$fares = $dbo->loadAssocList();
				foreach ($fares as $fare) {
					unset($fare['id']);
					$fare['iditem'] = $newid;
					$cols = array();
					$vals = array();
					foreach ($fare as $fk => $fv) {
						array_push($cols, '`'.$fk.'`');
						if ($fv == null) {
							array_push($vals, 'NULL');
						} else {
							array_push($vals, $dbo->quote($fv));
						}
					}
					$q = "INSERT INTO `#__vikrentitems_dispcost` (".implode(', ', $cols).") VALUES(".implode(', ', $vals).");";
					$dbo->setQuery($q);
					$dbo->execute();
				}
			}
			//
			//check hourly fares
			$q = "SELECT * FROM `#__vikrentitems_dispcosthours` WHERE `iditem`=".(int)$itid.";";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() > 0) {
				$fares = $dbo->loadAssocList();
				foreach ($fares as $fare) {
					unset($fare['id']);
					$fare['iditem'] = $newid;
					$cols = array();
					$vals = array();
					foreach ($fare as $fk => $fv) {
						array_push($cols, '`'.$fk.'`');
						if ($fv == null) {
							array_push($vals, 'NULL');
						} else {
							array_push($vals, $dbo->quote($fv));
						}
					}
					$q = "INSERT INTO `#__vikrentitems_dispcosthours` (".implode(', ', $cols).") VALUES(".implode(', ', $vals).");";
					$dbo->setQuery($q);
					$dbo->execute();
				}
			}
			//
			//check extra hours charges
			$q = "SELECT * FROM `#__vikrentitems_hourscharges` WHERE `iditem`=".(int)$itid.";";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() > 0) {
				$fares = $dbo->loadAssocList();
				foreach ($fares as $fare) {
					unset($fare['id']);
					$fare['iditem'] = $newid;
					$cols = array();
					$vals = array();
					foreach ($fare as $fk => $fv) {
						array_push($cols, '`'.$fk.'`');
						if ($fv == null) {
							array_push($vals, 'NULL');
						} else {
							array_push($vals, $dbo->quote($fv));
						}
					}
					$q = "INSERT INTO `#__vikrentitems_hourscharges` (".implode(', ', $cols).") VALUES(".implode(', ', $vals).");";
					$dbo->setQuery($q);
					$dbo->execute();
				}
			}
			//
			//check special prices
			$q = "SELECT `id`,`iditems` FROM `#__vikrentitems_seasons` WHERE `iditems` LIKE '%-".(int)$itid."-%';";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() > 0) {
				$sprices = $dbo->loadAssocList();
				foreach ($sprices as $sprice) {
					$q = "UPDATE `#__vikrentitems_seasons` SET `iditems`=".$dbo->quote($sprice['iditems'].'-'.$newid.'-,')." WHERE `id`=".$sprice['id'].";";
					$dbo->setQuery($q);
					$dbo->execute();
				}
			}
			//
			//check time slots
			$q = "SELECT `id`,`iditems` FROM `#__vikrentitems_timeslots` WHERE `iditems` LIKE '%-".(int)$itid."-%';";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() > 0) {
				$tslots = $dbo->loadAssocList();
				foreach ($tslots as $tslot) {
					$q = "UPDATE `#__vikrentitems_timeslots` SET `iditems`=".$dbo->quote($tslot['iditems'].'-'.$newid.'-,')." WHERE `id`=".$tslot['id'].";";
					$dbo->setQuery($q);
					$dbo->execute();
				}
			}
			//
			$mainframe->enqueueMessage(JText::_('VRICLONEITEMOK'));
			$mainframe->redirect('index.php?option=com_vikrentitems&task=edititem&cid[]='.$newid);
			exit;
		} else {
			VikError::raiseWarning('', 'Invalid Item ID for cloning');
			$mainframe->redirect('index.php?option=com_vikrentitems&task=items');
			exit;
		}
	}

	function customers() {
		VikRentItemsHelper::printHeader("customers");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'customers'));
	
		parent::display();

		if (VikRentItems::showFooter()) {
			VikRentItemsHelper::printFooter();
		}
	}

	function newcustomer() {
		VikRentItemsHelper::printHeader("customers");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'managecustomer'));
	
		parent::display();

		if (VikRentItems::showFooter()) {
			VikRentItemsHelper::printFooter();
		}
	}

	function editcustomer() {
		VikRentItemsHelper::printHeader("customers");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'managecustomer'));
	
		parent::display();

		if (VikRentItems::showFooter()) {
			VikRentItemsHelper::printFooter();
		}
	}

	function removecustomers() {
		$ids = VikRequest::getVar('cid', array(0));
		if (@count($ids)) {
			$dbo = JFactory::getDBO();
			$cpin = VikRentItems::getCPinIstance();
			foreach ($ids as $d) {
				$cpin->pluginCustomerSync($d, 'delete');
				$q = "DELETE FROM `#__vikrentitems_customers` WHERE `id`=".(int)$d.";";
				$dbo->setQuery($q);
				$dbo->execute();
			}
		}
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikrentitems&task=customers");
	}

	function savecustomer() {
		$dbo = JFactory::getDBO();
		$mainframe = JFactory::getApplication();
		$pfirst_name = VikRequest::getString('first_name', '', 'request');
		$plast_name = VikRequest::getString('last_name', '', 'request');
		$pcompany = VikRequest::getString('company', '', 'request');
		$pvat = VikRequest::getString('vat', '', 'request');
		$pemail = VikRequest::getString('email', '', 'request');
		$pphone = VikRequest::getString('phone', '', 'request');
		$pcountry = VikRequest::getString('country', '', 'request');
		$ppin = VikRequest::getString('pin', '', 'request');
		$pujid = VikRequest::getInt('ujid', '', 'request');
		$paddress = VikRequest::getString('address', '', 'request');
		$pcity = VikRequest::getString('city', '', 'request');
		$pzip = VikRequest::getString('zip', '', 'request');
		$pgender = VikRequest::getString('gender', '', 'request');
		$pgender = in_array($pgender, array('F', 'M')) ? $pgender : '';
		$pbdate = VikRequest::getString('bdate', '', 'request');
		$ppbirth = VikRequest::getString('pbirth', '', 'request');
		$pdoctype = VikRequest::getString('doctype', '', 'request');
		$pdocnum = VikRequest::getString('docnum', '', 'request');
		$pnotes = VikRequest::getString('notes', '', 'request');
		$pscandocimg = VikRequest::getString('scandocimg', '', 'request');
		$pischannel = VikRequest::getInt('ischannel', '', 'request');
		$pcommission = VikRequest::getFloat('commission', '', 'request');
		$pcalccmmon = VikRequest::getInt('calccmmon', '', 'request');
		$papplycmmon = VikRequest::getInt('applycmmon', '', 'request');
		$pchname = VikRequest::getString('chname', '', 'request');
		$pchcolor = VikRequest::getString('chcolor', '', 'request');
		$ptmpl = VikRequest::getString('tmpl', '', 'request');
		$pbid = VikRequest::getInt('bid', '', 'request');
		if (!empty($pfirst_name) && !empty($plast_name)) {
			$cpin = VikRentItems::getCPinIstance();
			$q = "SELECT * FROM `#__vikrentitems_customers` WHERE `email`=".$dbo->quote($pemail)." LIMIT 1;";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() == 0) {
				if (empty($ppin)) {
					$ppin = $cpin->generateUniquePin();
				} elseif ($cpin->pinExists($ppin)) {
					$ppin = $cpin->generateUniquePin();
				}
				//file upload
				$pimg = VikRequest::getVar('docimg', null, 'files', 'array');
				jimport('joomla.filesystem.file');
				$gimg = "";
				if (isset($pimg) && strlen(trim($pimg['name']))) {
					$filename = JFile::makeSafe(rand(100, 9999).str_replace(" ", "_", strtolower($pimg['name'])));
					$src = $pimg['tmp_name'];
					$dest = VRI_ADMIN_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'idscans'.DIRECTORY_SEPARATOR;
					$j = "";
					if (file_exists($dest.$filename)) {
						$j = rand(171, 1717);
						while (file_exists($dest.$j.$filename)) {
							$j++;
						}
					}
					$finaldest = $dest.$j.$filename;
					$check = getimagesize($pimg['tmp_name']);
					if ($check[2] & imagetypes()) {
						if (VikRentItems::uploadFile($src, $finaldest)) {
							$gimg = $j.$filename;
						} else {
							VikError::raiseWarning('', 'Error while uploading image');
						}
					} else {
						VikError::raiseWarning('', 'Uploaded file is not an Image');
					}
				} elseif (!empty($pscandocimg)) {
					$gimg = $pscandocimg;
				}
				//
				$q = "INSERT INTO `#__vikrentitems_customers` (`first_name`,`last_name`,`email`,`phone`,`country`,`pin`,`ujid`,`address`,`city`,`zip`,`doctype`,`docnum`,`docimg`,`notes`,`company`,`vat`,`gender`,`bdate`,`pbirth`) VALUES(".$dbo->quote($pfirst_name).", ".$dbo->quote($plast_name).", ".$dbo->quote($pemail).", ".$dbo->quote($pphone).", ".$dbo->quote($pcountry).", ".$dbo->quote($ppin).", ".$dbo->quote($pujid).", ".$dbo->quote($paddress).", ".$dbo->quote($pcity).", ".$dbo->quote($pzip).", ".$dbo->quote($pdoctype).", ".$dbo->quote($pdocnum).", ".$dbo->quote($gimg).", ".$dbo->quote($pnotes).", ".$dbo->quote($pcompany).", ".$dbo->quote($pvat).", ".$dbo->quote($pgender).", ".$dbo->quote($pbdate).", ".$dbo->quote($ppbirth).");";
				$dbo->setQuery($q);
				$dbo->execute();
				$lid = $dbo->insertid();
				$cpin->pluginCustomerSync($lid, 'insert');
				if (!empty($lid)) {
					$mainframe->enqueueMessage(JText::_('VRCUSTOMERSAVED'));
				}
			} else {
				//email already exists
				$ex_customer = $dbo->loadAssoc();
				VikError::raiseWarning('', JText::_('VRERRCUSTOMEREMAILEXISTS').'<br/><a href="index.php?option=com_vikrentitems&task=editcustomer&cid[]='.$ex_customer['id'].'" target="_blank">'.$ex_customer['first_name'].' '.$ex_customer['last_name'].'</a>');
			}
		}
		$mainframe->redirect("index.php?option=com_vikrentitems&task=customers");
	}

	function updatecustomer() {
		$this->do_updatecustomer();
	}

	function updatecustomerstay() {
		$this->do_updatecustomer(true);
	}

	private function do_updatecustomer($stay = false) {
		$dbo = JFactory::getDBO();
		$mainframe = JFactory::getApplication();
		$pfirst_name = VikRequest::getString('first_name', '', 'request');
		$plast_name = VikRequest::getString('last_name', '', 'request');
		$pcompany = VikRequest::getString('company', '', 'request');
		$pvat = VikRequest::getString('vat', '', 'request');
		$pemail = VikRequest::getString('email', '', 'request');
		$pphone = VikRequest::getString('phone', '', 'request');
		$pcountry = VikRequest::getString('country', '', 'request');
		$ppin = VikRequest::getString('pin', '', 'request');
		$pujid = VikRequest::getInt('ujid', '', 'request');
		$paddress = VikRequest::getString('address', '', 'request');
		$pcity = VikRequest::getString('city', '', 'request');
		$pzip = VikRequest::getString('zip', '', 'request');
		$pgender = VikRequest::getString('gender', '', 'request');
		$pgender = in_array($pgender, array('F', 'M')) ? $pgender : '';
		$pbdate = VikRequest::getString('bdate', '', 'request');
		$ppbirth = VikRequest::getString('pbirth', '', 'request');
		$pdoctype = VikRequest::getString('doctype', '', 'request');
		$pdocnum = VikRequest::getString('docnum', '', 'request');
		$pnotes = VikRequest::getString('notes', '', 'request');
		$pscandocimg = VikRequest::getString('scandocimg', '', 'request');
		$pischannel = VikRequest::getInt('ischannel', '', 'request');
		$pcommission = VikRequest::getFloat('commission', '', 'request');
		$pcalccmmon = VikRequest::getInt('calccmmon', '', 'request');
		$papplycmmon = VikRequest::getInt('applycmmon', '', 'request');
		$pchname = VikRequest::getString('chname', '', 'request');
		$pchcolor = VikRequest::getString('chcolor', '', 'request');
		$pwhere = VikRequest::getInt('where', '', 'request');
		$ptmpl = VikRequest::getString('tmpl', '', 'request');
		$pbid = VikRequest::getInt('bid', '', 'request');
		if (!empty($pwhere) && !empty($pfirst_name) && !empty($plast_name)) {
			$q = "SELECT * FROM `#__vikrentitems_customers` WHERE `id`=".(int)$pwhere." LIMIT 1;";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() == 1) {
				$customer = $dbo->loadAssoc();
			} else {
				$mainframe->redirect("index.php?option=com_vikrentitems&task=customers");
				exit;
			}
			$q = "SELECT * FROM `#__vikrentitems_customers` WHERE `email`=".$dbo->quote($pemail)." AND `id`!=".(int)$pwhere." LIMIT 1;";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() == 0) {
				$cpin = VikRentItems::getCPinIstance();
				if (empty($ppin)) {
					$ppin = $customer['pin'];
				} elseif ($cpin->pinExists($ppin, $customer['pin'])) {
					$ppin = $cpin->generateUniquePin();
				}
				//file upload
				$pimg = VikRequest::getVar('docimg', null, 'files', 'array');
				jimport('joomla.filesystem.file');
				$gimg = "";
				if (isset($pimg) && strlen(trim($pimg['name']))) {
					$filename = JFile::makeSafe(rand(100, 9999).str_replace(" ", "_", strtolower($pimg['name'])));
					$src = $pimg['tmp_name'];
					$dest = VRI_ADMIN_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'idscans'.DIRECTORY_SEPARATOR;
					$j = "";
					if (file_exists($dest.$filename)) {
						$j = rand(171, 1717);
						while (file_exists($dest.$j.$filename)) {
							$j++;
						}
					}
					$finaldest = $dest.$j.$filename;
					$check = getimagesize($pimg['tmp_name']);
					if ($check[2] & imagetypes()) {
						if (VikRentItems::uploadFile($src, $finaldest)) {
							$gimg = $j.$filename;
						} else {
							VikError::raiseWarning('', 'Error while uploading image');
						}
					} else {
						VikError::raiseWarning('', 'Uploaded file is not an Image');
					}
				} elseif (!empty($pscandocimg)) {
					$gimg = $pscandocimg;
				}
				//
				$q = "UPDATE `#__vikrentitems_customers` SET `first_name`=".$dbo->quote($pfirst_name).",`last_name`=".$dbo->quote($plast_name).",`email`=".$dbo->quote($pemail).",`phone`=".$dbo->quote($pphone).",`country`=".$dbo->quote($pcountry).",`pin`=".$dbo->quote($ppin).",`ujid`=".$dbo->quote($pujid).",`address`=".$dbo->quote($paddress).",`city`=".$dbo->quote($pcity).",`zip`=".$dbo->quote($pzip).",`doctype`=".$dbo->quote($pdoctype).",`docnum`=".$dbo->quote($pdocnum).(!empty($gimg) ? ",`docimg`=".$dbo->quote($gimg) : "").",`notes`=".$dbo->quote($pnotes).",`company`=".$dbo->quote($pcompany).",`vat`=".$dbo->quote($pvat).",`gender`=".$dbo->quote($pgender).",`bdate`=".$dbo->quote($pbdate).",`pbirth`=".$dbo->quote($ppbirth)." WHERE `id`=".(int)$pwhere.";";
				$dbo->setQuery($q);
				$dbo->execute();
				$cpin->pluginCustomerSync($pwhere, 'update');
				$mainframe->enqueueMessage(JText::_('VRCUSTOMERSAVED'));
			} else {
				//email already exists
				$ex_customer = $dbo->loadAssoc();
				VikError::raiseWarning('', JText::_('VRERRCUSTOMEREMAILEXISTS').'<br/><a href="index.php?option=com_vikrentitems&task=editcustomer&cid[]='.$ex_customer['id'].'" target="_blank">'.$ex_customer['first_name'].' '.$ex_customer['last_name'].'</a>');
					$mainframe->redirect("index.php?option=com_vikrentitems&task=editcustomer&cid[]=".$pwhere);
					exit;
			}
		}
		if ($stay) {
			$mainframe->redirect("index.php?option=com_vikrentitems&task=editcustomer&cid[]=".$pwhere);
		} else {
			$mainframe->redirect("index.php?option=com_vikrentitems&task=customers");
		}
	}

	function cancelcustomer() {
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikrentitems&task=customers");
	}

	function searchcustomer() {
		//to be called via ajax
		$kw = VikRequest::getString('kw', '', 'request');
		$nopin = VikRequest::getInt('nopin', '', 'request');
		$cstring = '';
		if (strlen($kw) > 0) {
			$dbo = JFactory::getDBO();
			if ($nopin > 0) {
				//page all bookings
				$q = "SELECT * FROM `#__vikrentitems_customers` WHERE CONCAT_WS(' ', `first_name`, `last_name`) LIKE ".$dbo->quote("%".$kw."%")." OR `email` LIKE ".$dbo->quote("%".$kw."%")." ORDER BY `first_name` ASC LIMIT 30;";
			} else {
				//page calendar
				$q = "SELECT * FROM `#__vikrentitems_customers` WHERE CONCAT_WS(' ', `first_name`, `last_name`) LIKE ".$dbo->quote("%".$kw."%")." OR `email` LIKE ".$dbo->quote("%".$kw."%")." OR `pin` LIKE ".$dbo->quote("%".$kw."%")." ORDER BY `first_name` ASC;";
			}
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() > 0) {
				$customers = $dbo->loadAssocList();
				$cust_old_fields = array();
				$cstring_search = '';
				foreach ($customers as $k => $v) {
					$cstring_search .= '<div class="vri-custsearchres-entry" data-custid="'.$v['id'].'" data-email="'.$v['email'].'" data-phone="'.addslashes($v['phone']).'" data-country="'.$v['country'].'" data-pin="'.$v['pin'].'" data-firstname="'.addslashes($v['first_name']).'" data-lastname="'.addslashes($v['last_name']).'">'."\n";
					$cstring_search .= '<span class="vri-custsearchres-name hasTooltip" title="'.$v['email'].'">'.$v['first_name'].' '.$v['last_name'].'</span>'."\n";
					if (!($nopin > 0)) {
						$cstring_search .= '<span class="vri-custsearchres-pin">'.$v['pin'].'</span>'."\n";
					}
					if (!empty($v['country'])) {
						if (file_exists(VRI_ADMIN_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'countries'.DIRECTORY_SEPARATOR.$v['country'].'.png')) {
							$cstring_search .= '<span class="vri-custsearchres-cflag"><img src="'.VRI_ADMIN_URI.'resources/countries/'.$v['country'].'.png'.'" title="'.$v['country'].'" class="vri-country-flag"/></span>'."\n";
						}
					}
					$cstring_search .= '</div>'."\n";
					if (!empty($v['cfields'])) {
						$oldfields = json_decode($v['cfields'], true);
						if (is_array($oldfields) && count($oldfields)) {
							$cust_old_fields[$v['id']] = $oldfields;
						}
					}
				}
				$cstring = json_encode(array(($nopin > 0 ? '' : $cust_old_fields), $cstring_search));
			}
		}
		echo $cstring;
		exit;
	}

	function exportcustomers() {
		//we do not set the menu for this view
	
		VikRequest::setVar('view', VikRequest::getCmd('view', 'exportcustomers'));
	
		parent::display();

		if (VikRentItems::showFooter()) {
			VikRentItemsHelper::printFooter();
		}
	}

	function exportcustomerslaunch() {
		$cid = VikRequest::getVar('cid', array(0));
		$dbo = JFactory::getDBO();
		$pnotes = VikRequest::getInt('notes', '', 'request');
		$pscanimg = VikRequest::getInt('scanimg', '', 'request');
		$ppin = VikRequest::getInt('pin', '', 'request');
		$pcountry = VikRequest::getString('country', '', 'request');
		$pfromdate = VikRequest::getString('fromdate', '', 'request');
		$ptodate = VikRequest::getString('todate', '', 'request');
		$pdatefilt = VikRequest::getInt('datefilt', '', 'request');
		$clauses = array();
		if (count($cid) > 0 && !empty($cid[0])) {
			$clauses[] = "`c`.`id` IN (".implode(', ', $cid).")";
		}
		if (!empty($pcountry)) {
			$clauses[] = "`c`.`country`=".$dbo->quote($pcountry);
		}
		$datescol = '`bk`.`ts`';
		if ($pdatefilt > 0) {
			if ($pdatefilt == 1) {
				$datescol = '`bk`.`ts`';
			} elseif ($pdatefilt == 2) {
				$datescol = '`bk`.`ritiro`';
			} elseif ($pdatefilt == 3) {
				$datescol = '`bk`.`consegna`';
			}
		}
		if (!empty($pfromdate)) {
			$from_ts = VikRentItems::getDateTimestamp($pfromdate, 0, 0);
			$clauses[] = $datescol.">=".$from_ts;
		}
		if (!empty($ptodate)) {
			$to_ts = VikRentItems::getDateTimestamp($ptodate, 23, 59);
			$clauses[] = $datescol."<=".$to_ts;
		}
		//this query below is safe with the error #1055 when sql_mode=only_full_group_by
		$q = "SELECT `c`.`id`,`c`.`first_name`,`c`.`last_name`,`c`.`email`,`c`.`phone`,`c`.`country`,`c`.`cfields`,`c`.`pin`,`c`.`ujid`,`c`.`address`,`c`.`city`,`c`.`zip`,`c`.`doctype`,`c`.`docnum`,`c`.`docimg`,`c`.`notes`,`c`.`company`,`c`.`vat`,`c`.`gender`,`c`.`bdate`,`c`.`pbirth`,".
			"(SELECT COUNT(*) FROM `#__vikrentitems_customers_orders` AS `co` WHERE `co`.`idcustomer`=`c`.`id`) AS `tot_bookings`,".
			"`cy`.`country_3_code`,`cy`.`country_name` ".
			"FROM `#__vikrentitems_customers` AS `c` LEFT JOIN `#__vikrentitems_countries` `cy` ON `cy`.`country_3_code`=`c`.`country` ".
			"LEFT JOIN `#__vikrentitems_customers_orders` `co` ON `co`.`idcustomer`=`c`.`id` ".
			"LEFT JOIN `#__vikrentitems_orders` `bk` ON `bk`.`id`=`co`.`idorder`".
			(count($clauses) > 0 ? " WHERE ".implode(' AND ', $clauses) : "")." 
			GROUP BY `c`.`id`,`c`.`first_name`,`c`.`last_name`,`c`.`email`,`c`.`phone`,`c`.`country`,`c`.`cfields`,`c`.`pin`,`c`.`ujid`,`c`.`address`,`c`.`city`,`c`.`zip`,`c`.`doctype`,`c`.`docnum`,`c`.`docimg`,`c`.`notes`,`c`.`company`,`c`.`vat`,`c`.`gender`,`c`.`bdate`,`c`.`pbirth`,`cy`.`country_3_code`,`cy`.`country_name` ".
			"ORDER BY `c`.`last_name` ASC;";
		$dbo->setQuery($q);
		$dbo->execute();
		if (!($dbo->getNumRows() > 0)) {
			VikError::raiseWarning('', JText::_('VRINORECORDSCSVCUSTOMERS'));
			$mainframe = JFactory::getApplication();
			$mainframe->redirect("index.php?option=com_vikrentitems&task=customers");
			exit;
		}
		$customers = $dbo->loadAssocList();
		$csvlines = array();
		$csvheadline = array('ID', JText::_('VRCUSTOMERLASTNAME'), JText::_('VRCUSTOMERFIRSTNAME'), JText::_('VRCUSTOMEREMAIL'), JText::_('VRCUSTOMERPHONE'), JText::_('VRCUSTOMERADDRESS'), JText::_('VRCUSTOMERCITY'), JText::_('VRCUSTOMERZIP'), JText::_('VRCUSTOMERCOUNTRY'), JText::_('VRCUSTOMERTOTBOOKINGS'));
		if ($ppin > 0) {
			$csvheadline[] = JText::_('VRCUSTOMERPIN');
		}
		if ($pscanimg > 0) {
			$csvheadline[] = JText::_('VRCUSTOMERDOCTYPE');
			$csvheadline[] = JText::_('VRCUSTOMERDOCNUM');
			$csvheadline[] = JText::_('VRCUSTOMERDOCIMG');
		}
		if ($pnotes > 0) {
			$csvheadline[] = JText::_('VRCUSTOMERNOTES');
		}
		$csvlines[] = $csvheadline;
		foreach ($customers as $customer) {
			$csvcustomerline = array($customer['id'], $customer['last_name'], $customer['first_name'], $customer['email'], $customer['phone'], $customer['address'], $customer['city'], $customer['zip'], $customer['country_name'], $customer['tot_bookings']);
			if ($ppin > 0) {
				$csvcustomerline[] = $customer['pin'];
			}
			if ($pscanimg > 0) {
				$csvcustomerline[] = $customer['doctype'];
				$csvcustomerline[] = $customer['docnum'];
				$csvcustomerline[] = (!empty($customer['docimg']) ? VRI_ADMIN_URI.'resources/idscans/'.$customer['docimg'] : '');
			}
			if ($pnotes > 0) {
				$csvcustomerline[] = $customer['notes'];
			}	
			$csvlines[] = $csvcustomerline;
		}
		header("Content-type: text/csv");
		header("Cache-Control: no-store, no-cache");
		header('Content-Disposition: attachment; filename="customers_export_'.(!empty($pcountry) ? strtolower($pcountry).'_' : '').date('Y-m-d').'.csv"');
		$outstream = fopen("php://output", 'w');
		foreach ($csvlines as $csvline) {
			fputcsv($outstream, $csvline);
		}
		fclose($outstream);
		exit;
	}

	function sendcustomemail() {
		$dbo = JFactory::getDBO();
		$mainframe = JFactory::getApplication();
		$vri_tn = VikRentItems::getTranslator();
		$pbid = VikRequest::getInt('bid', '', 'request');
		$pemailsubj = VikRequest::getString('emailsubj', '', 'request');
		$pemail = VikRequest::getString('email', '', 'request');
		$pemailcont = VikRequest::getString('emailcont', '', 'request', VIKREQUEST_ALLOWRAW);
		$pemailfrom = VikRequest::getString('emailfrom', '', 'request');
		$pgoto = VikRequest::getString('goto', '', 'request');
		$pgoto = !empty($pgoto) ? urldecode($pgoto) : 'index.php?option=com_vikrentitems';
		if (!empty($pemail) && !empty($pemailcont)) {
			$email_attach = null;
			jimport('joomla.filesystem.file');
			$pemailattch = VikRequest::getVar('emailattch', null, 'files', 'array');
			if (isset($pemailattch) && strlen(trim($pemailattch['name']))) {
				$filename = JFile::makeSafe(str_replace(" ", "_", strtolower($pemailattch['name'])));
				$src = $pemailattch['tmp_name'];
				$dest = VRI_ADMIN_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR;
				$j = "";
				if (file_exists($dest.$filename)) {
					$j = rand(171, 1717);
					while (file_exists($dest.$j.$filename)) {
						$j++;
					}
				}
				$finaldest = $dest.$j.$filename;
				if (VikRentItems::uploadFile($src, $finaldest)) {
					$email_attach = $finaldest;
				} else {
					VikError::raiseWarning('', 'Error uploading the attachment. Email not sent.');
					$mainframe->redirect($pgoto);
					exit;
				}
			}
			//VRI 1.6 - special tags for the custom email template files and messages
			$orig_mail_cont = $pemailcont;
			if (strpos($pemailcont, '{') !== false && strpos($pemailcont, '}') !== false) {
				$order = array();
				$q = "SELECT `o`.*,`co`.`idcustomer`,CONCAT_WS(' ',`c`.`first_name`,`c`.`last_name`) AS `customer_name`,`c`.`pin` AS `customer_pin`,`nat`.`country_name` FROM `#__vikrentitems_orders` AS `o` LEFT JOIN `#__vikrentitems_customers_orders` `co` ON `co`.`idorder`=`o`.`id` AND `co`.`idorder`=".(int)$pbid." LEFT JOIN `#__vikrentitems_customers` `c` ON `c`.`id`=`co`.`idcustomer` LEFT JOIN `#__vikrentitems_countries` `nat` ON `nat`.`country_3_code`=`o`.`country` WHERE `o`.`id`=".(int)$pbid.";";
				$dbo->setQuery($q);
				$dbo->execute();
				if ($dbo->getNumRows() > 0) {
					$order = $dbo->loadAssoc();
					// get order items
					$q = "SELECT `oi`.*,`i`.`name` AS `item_name` FROM `#__vikrentitems_ordersitems` AS `oi` LEFT JOIN `#__vikrentitems_items` `i` ON `oi`.`iditem`=`i`.`id` WHERE `oi`.`idorder`=".$order['id'].";";
					$dbo->setQuery($q);
					$dbo->execute();
					if ($dbo->getNumRows() > 0) {
						$order['items'] = $dbo->loadAssocList();
					}
				}
				// parse the special tokens to build the message
				$pemailcont = VikRentItems::parseSpecialTokens($order, $pemailcont);
			}
			//
			$is_html = (strpos($pemailcont, '<') !== false && strpos($pemailcont, '>') !== false);
			$pemailcont = $is_html ? nl2br($pemailcont) : $pemailcont;
			$vri_app = VikRentItems::getVriApplication();
			$vri_app->sendMail($pemailfrom, $pemailfrom, $pemail, $pemailfrom, $pemailsubj, $pemailcont, $is_html, 'base64', $email_attach);
			$mainframe->enqueueMessage(JText::_('VRSENDEMAILOK'));
			if ($email_attach !== null) {
				@unlink($email_attach);
			}
			//Save email template for future sending
			$config_rec_exists = false;
			$emtpl = array(
				'emailsubj' => $pemailsubj,
				'emailcont' => $orig_mail_cont,
				'emailfrom' => $pemailfrom
			);
			$cur_emtpl = array();
			$q = "SELECT `setting` FROM `#__vikrentitems_config` WHERE `param`='customemailtpls';";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() > 0) {
				$config_rec_exists = true;
				$cur_emtpl = $dbo->loadResult();
				$cur_emtpl = empty($cur_emtpl) ? array() : json_decode($cur_emtpl, true);
				$cur_emtpl = is_array($cur_emtpl) ? $cur_emtpl : array();
			}
			if (count($cur_emtpl) > 0) {
				$existing_subj = false;
				foreach ($cur_emtpl as $emk => $emv) {
					if (array_key_exists('emailsubj', $emv) && $emv['emailsubj'] == $emtpl['emailsubj']) {
						$cur_emtpl[$emk] = $emtpl;
						$existing_subj = true;
						break;
					}
				}
				if ($existing_subj === false) {
					$cur_emtpl[] = $emtpl;
				}
			} else {
				$cur_emtpl[] = $emtpl;
			}
			if (count($cur_emtpl) > 10) {
				//Max 10 templates to avoid problems with the size of the field and truncated json strings
				$exceed = count($cur_emtpl) - 10;
				for ($tl=0; $tl < $exceed; $tl++) { 
					unset($cur_emtpl[$tl]);
				}
				$cur_emtpl = array_values($cur_emtpl);
			}
			if ($config_rec_exists === true) {
				$q = "UPDATE `#__vikrentitems_config` SET `setting`=".$dbo->quote(json_encode($cur_emtpl))." WHERE `param`='customemailtpls';";
				$dbo->setQuery($q);
				$dbo->execute();
			} else {
				$q = "INSERT INTO `#__vikrentitems_config` (`param`,`setting`) VALUES ('customemailtpls', ".$dbo->quote(json_encode($cur_emtpl)).");";
				$dbo->setQuery($q);
				$dbo->execute();
			}
			//
		} else {
			VikError::raiseWarning('', JText::_('VRSENDEMAILERRMISSDATA'));
		}
		$mainframe->redirect($pgoto);
	}

	function rmcustomemailtpl() {
		$cid = VikRequest::getVar('cid', array(0));
		$oid = $cid[0];
		$dbo = JFactory::getDBO();
		$mainframe = JFactory::getApplication();
		$tplind = VikRequest::getInt('tplind', '', 'request');
		if (empty($oid) || !(strlen($tplind) > 0)) {
			VikError::raiseWarning('', 'Missing Data.');
			$mainframe->redirect('index.php?option=com_vikrentitems');
			exit;
		}
		$cur_emtpl = array();
		$q = "SELECT `setting` FROM `#__vikrentitems_config` WHERE `param`='customemailtpls';";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$cur_emtpl = $dbo->loadResult();
			$cur_emtpl = empty($cur_emtpl) ? array() : json_decode($cur_emtpl, true);
			$cur_emtpl = is_array($cur_emtpl) ? $cur_emtpl : array();
		} else {
			VikError::raiseWarning('', 'Missing Templates Record.');
			$mainframe->redirect('index.php?option=com_vikrentitems');
			exit;
		}
		if (array_key_exists($tplind, $cur_emtpl)) {
			unset($cur_emtpl[$tplind]);
			$cur_emtpl = count($cur_emtpl) > 0 ? array_values($cur_emtpl) : array();
			$q = "UPDATE `#__vikrentitems_config` SET `setting`=".$dbo->quote(json_encode($cur_emtpl))." WHERE `param`='customemailtpls';";
			$dbo->setQuery($q);
			$dbo->execute();
		}
		$mainframe->redirect('index.php?option=com_vikrentitems&task=editorder&cid[]='.$oid.'&customemail=1');
		exit;
	}

	function choosebusy() {
		VikRentItemsHelper::printHeader("8");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'choosebusy'));
	
		parent::display();

		if (VikRentItems::showFooter()) {
			VikRentItemsHelper::printFooter();
		}
	}

	function orders() {
		VikRentItemsHelper::printHeader("8");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'orders'));
	
		parent::display();

		if (VikRentItems::showFooter()) {
			VikRentItemsHelper::printFooter();
		}
	}

	function removeorders() {
		$mainframe = JFactory::getApplication();
		$ids = VikRequest::getVar('cid', array(0));
		if (@count($ids)) {
			$dbo = JFactory::getDBO();
			foreach ($ids as $d) {
				$q = "SELECT * FROM `#__vikrentitems_orders` WHERE `id`=".intval($d).";";
				$dbo->setQuery($q);
				$dbo->execute();
				if ($dbo->getNumRows() == 1) {
					$rows = $dbo->loadAssocList();
					$q = "SELECT * FROM `#__vikrentitems_ordersbusy` WHERE `idorder`=".$rows[0]['id'].";";
					$dbo->setQuery($q);
					$dbo->execute();
					if ($dbo->getNumRows() > 0) {
						$ordbusy = $dbo->loadAssocList();
						foreach ($ordbusy as $ob) {
							$q = "DELETE FROM `#__vikrentitems_busy` WHERE `id`=".(int)$ob['idbusy'].";";
							$dbo->setQuery($q);
							$dbo->execute();
						}
					}
					$q = "DELETE FROM `#__vikrentitems_ordersbusy` WHERE `idorder`=".$rows[0]['id'].";";
					$dbo->setQuery($q);
					$dbo->execute();
					$q = "DELETE FROM `#__vikrentitems_tmplock` WHERE `idorder`=" . intval($rows[0]['id']) . ";";
					$dbo->setQuery($q);
					$dbo->execute();
					if ($rows[0]['status'] == 'cancelled') {
						$q = "DELETE FROM `#__vikrentitems_customers_orders` WHERE `idorder`=".$rows[0]['id'].";";
						$dbo->setQuery($q);
						$dbo->execute();
						$q = "DELETE FROM `#__vikrentitems_ordersitems` WHERE `idorder`=".$rows[0]['id'].";";
						$dbo->setQuery($q);
						$dbo->execute();
						$q = "DELETE FROM `#__vikrentitems_orders` WHERE `id`=".$rows[0]['id'].";";
						$dbo->setQuery($q);
						$dbo->execute();
					} else {
						$q = "UPDATE `#__vikrentitems_orders` SET `status`='cancelled' WHERE `id`=".$rows[0]['id'].";";
						$dbo->setQuery($q);
						$dbo->execute();
					}
				}
			}
			$mainframe->enqueueMessage(JText::_('VRMESSDELBUSY'));
		}
		$mainframe->redirect("index.php?option=com_vikrentitems&task=orders");
	}

	function canceledorder() {
		$pgoto = VikRequest::getString('goto', '', 'request');
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikrentitems&task=".($pgoto == 'overv' ? 'overv' : 'orders'));
	}

	function removebusy() {
		$dbo = JFactory::getDBO();
		$pidorder = VikRequest::getString('idorder', '', 'request');
		$pgoto = VikRequest::getString('goto', '', 'request');
		$q = "SELECT * FROM `#__vikrentitems_orders` WHERE `id`=".$dbo->quote($pidorder).";";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() == 1) {
			$rows = $dbo->loadAssocList();
			if ($rows[0]['status'] != 'cancelled') {
				$q = "UPDATE `#__vikrentitems_orders` SET `status`='cancelled' WHERE `id`=".(int)$rows[0]['id'].";";
				$dbo->setQuery($q);
				$dbo->execute();
				$q = "DELETE FROM `#__vikrentitems_tmplock` WHERE `idorder`=" . intval($rows[0]['id']) . ";";
				$dbo->setQuery($q);
				$dbo->execute();
			}
			$q = "SELECT * FROM `#__vikrentitems_ordersbusy` WHERE `idorder`=".(int)$rows[0]['id'].";";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() > 0) {
				$ordbusy = $dbo->loadAssocList();
				foreach ($ordbusy as $ob) {
					$q = "DELETE FROM `#__vikrentitems_busy` WHERE `id`='".$ob['idbusy']."';";
					$dbo->setQuery($q);
					$dbo->execute();
				}
			}
			$q = "DELETE FROM `#__vikrentitems_ordersbusy` WHERE `idorder`=".(int)$rows[0]['id'].";";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($rows[0]['status'] == 'cancelled') {
				$q = "DELETE FROM `#__vikrentitems_customers_orders` WHERE `idorder`=" . intval($rows[0]['id']) . ";";
				$dbo->setQuery($q);
				$dbo->execute();
				$q = "DELETE FROM `#__vikrentitems_ordersitems` WHERE `idorder`=".(int)$rows[0]['id'].";";
				$dbo->setQuery($q);
				$dbo->execute();
				$q = "DELETE FROM `#__vikrentitems_orders` WHERE `id`=".(int)$rows[0]['id'].";";
				$dbo->setQuery($q);
				$dbo->execute();
			}
			$app = JFactory::getApplication();
			$app->enqueueMessage(JText::_('VRMESSDELBUSY'));
		}
		$mainframe = JFactory::getApplication();
		$mainframe->redirect("index.php?option=com_vikrentitems&task=".($pgoto == 'overv' ? 'overv' : 'orders'));
	}

	function editorder() {
		VikRentItemsHelper::printHeader("8");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'editorder'));
	
		parent::display();

		if (VikRentItems::showFooter()) {
			VikRentItemsHelper::printFooter();
		}
	}

	function editbusy() {
		VikRentItemsHelper::printHeader("8");

		VikRequest::setVar('view', VikRequest::getCmd('view', 'editbusy'));
	
		parent::display();

		if (VikRentItems::showFooter()) {
			VikRentItemsHelper::printFooter();
		}
	}

	function updatebusy() {
		$pidorder = VikRequest::getString('idorder', '', 'request');
		$pritiro = VikRequest::getString('ritiro', '', 'request');
		$pconsegna = VikRequest::getString('consegna', '', 'request');
		$ppickuph = VikRequest::getString('pickuph', '', 'request');
		$ppickupm = VikRequest::getString('pickupm', '', 'request');
		$pdropoffh = VikRequest::getString('dropoffh', '', 'request');
		$pdropoffm = VikRequest::getString('dropoffm', '', 'request');
		$pcustdata = VikRequest::getString('custdata', '', 'request');
		$pidplace = VikRequest::getInt('idplace', '', 'request');
		$pidreturnplace = VikRequest::getInt('idreturnplace', '', 'request');
		$pdeliverycost = VikRequest::getFloat('deliverycost', 0, 'request');
		$pareprices = VikRequest::getString('areprices', '', 'request');
		$ptotpaid = VikRequest::getString('totpaid', '', 'request');
		$pgoto = VikRequest::getString('goto', '', 'request');
		$pextracn = VikRequest::getVar('extracn', array());
		$pextracc = VikRequest::getVar('extracc', array());
		$pextractx = VikRequest::getVar('extractx', array());
		$dbo = JFactory::getDBO();
		$mainframe = JFactory::getApplication();
		$actnow = time();
		$nowdf = VikRentItems::getDateFormat(true);
		$nowtf = VikRentItems::getTimeFormat(true);
		if ($nowdf == "%d/%m/%Y") {
			$df = 'd/m/Y';
		} elseif ($nowdf == "%m/%d/%Y") {
			$df = 'm/d/Y';
		} else {
			$df = 'Y/m/d';
		}
		$q = "SELECT * FROM `#__vikrentitems_orders` WHERE `id`=".(int)$pidorder.";";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() == 1) {
			$ord = $dbo->loadAssocList();
			$q = "SELECT `oi`.*,`i`.`name`,`i`.`idopt`,`i`.`units`,`i`.`params` FROM `#__vikrentitems_ordersitems` AS `oi`,`#__vikrentitems_items` AS `i` WHERE `oi`.`idorder`=".$ord[0]['id']." AND `oi`.`iditem`=`i`.`id` ORDER BY `oi`.`id` ASC;";
			$dbo->setQuery($q);
			$dbo->execute();
			$orderitems = $dbo->loadAssocList();
			// Custom rate
			$is_cust_cost = false;
			foreach ($orderitems as $koi => $oi) {
				if (!empty($oi['cust_cost']) && $oi['cust_cost'] > 0.00) {
					$is_cust_cost = true;
					break;
				}
			}
			//
			//VRI 1.6 item switching
			$toswitch = array();
			$idbooked = array();
			$items_units = array();
			$q = "SELECT `id`,`name`,`units` FROM `#__vikrentitems_items`;";
			$dbo->setQuery($q);
			$dbo->execute();
			$all_items = $dbo->loadAssocList();
			foreach ($all_items as $rr) {
				$items_units[$rr['id']]['name'] = $rr['name'];
				$items_units[$rr['id']]['units'] = $rr['units'];
			}
			foreach ($orderitems as $ind => $oi) {
				$switch_command = VikRequest::getString('switch_'.$oi['id'], '', 'request');
				$book_item_units = VikRequest::getInt('itemquant'.$ind, 1, 'request');
				$book_item_units = $book_item_units < 1 ? 1 : $book_item_units;
				if (!empty($switch_command) && intval($switch_command) != $oi['iditem'] && array_key_exists(intval($switch_command), $items_units)) {
					$idbooked[$oi['iditem']]++;
					$orkey = count($toswitch);
					$toswitch[$orkey]['from'] = $oi['iditem'];
					$toswitch[$orkey]['to'] = intval($switch_command);
					$toswitch[$orkey]['newquantity'] = $book_item_units;
					$toswitch[$orkey]['record'] = $oi;
				}
			}
			if (count($toswitch) > 0 && (!empty($orderitems[0]['idtar']) || $is_cust_cost)) {
				foreach ($toswitch as $ksw => $rsw) {
					$plusunit = array_key_exists($rsw['to'], $idbooked) ? $idbooked[$rsw['to']] : 0;
					if (!VikRentItems::itemBookable($rsw['to'], ($items_units[$rsw['to']]['units'] + $plusunit), $ord[0]['ritiro'], $ord[0]['consegna'], $rsw['newquantity'])) {
						unset($toswitch[$ksw]);
						VikError::raiseWarning('', JText::sprintf('VRISWITCHITERR', $rsw['newquantity'], $rsw['record']['name'], $items_units[$rsw['to']]['name']));
					}
				}
				if (count($toswitch) > 0) {
					//reset first record rate
					reset($orderitems);
					$q = "UPDATE `#__vikrentitems_ordersitems` SET `idtar`=NULL WHERE `id`=".$orderitems[0]['id'].";";
					$dbo->setQuery($q);
					$dbo->execute();
					//
					$app = JFactory::getApplication();
					foreach ($toswitch as $ksw => $rsw) {
						$q = "UPDATE `#__vikrentitems_ordersitems` SET `iditem`=".$rsw['to'].",`idtar`=NULL WHERE `id`=".$rsw['record']['id'].";";
						$dbo->setQuery($q);
						$dbo->execute();
						$app->enqueueMessage(JText::sprintf('VRISWITCHITOK', $rsw['record']['name'], $items_units[$rsw['to']]['name']));
						//update Notes field for this booking to keep track of the previous item that was assigned
						$prev_room_name = array_key_exists($rsw['from'], $items_units) ? $items_units[$rsw['from']]['name'] : '';
						if (!empty($prev_room_name)) {
							$new_notes = JText::sprintf('VRIPREVITEMMOVED', $prev_room_name, date($df.' '.$nowtf))."\n".$ord[0]['adminnotes'];
							$q = "UPDATE `#__vikrentitems_orders` SET `adminnotes`=".$dbo->quote($new_notes)." WHERE `id`=".(int)$ord[0]['id'].";";
							$dbo->setQuery($q);
							$dbo->execute();
						}
						//
						if ($ord[0]['status'] == 'confirmed') {
							//update record in _busy
							$q = "SELECT `b`.`id`,`b`.`iditem`,`ob`.`idorder` FROM `#__vikrentitems_busy` AS `b`,`#__vikrentitems_ordersbusy` AS `ob` WHERE `b`.`iditem`=" . $rsw['from'] . " AND `b`.`id`=`ob`.`idbusy` AND `ob`.`idorder`=".$ord[0]['id']." LIMIT 1;";
							$dbo->setQuery($q);
							$dbo->execute();
							if ($dbo->getNumRows() == 1) {
								$cur_busy = $dbo->loadAssocList();
								$q = "UPDATE `#__vikrentitems_busy` SET `iditem`=".$rsw['to']." WHERE `id`=".$cur_busy[0]['id']." AND `iditem`=".$cur_busy[0]['iditem']." LIMIT 1;";
								$dbo->setQuery($q);
								$dbo->execute();
								// old kit relations
								$kit_relations = VikRentItems::getKitRelatedItems($rsw['from']);
								if (count($kit_relations)) {
									// switched item was a kit: delete all busy records for children items
									$q = "SELECT * FROM `#__vikrentitems_ordersbusy` WHERE `idorder`=".$ord[0]['id'].";";
									$dbo->setQuery($q);
									$dbo->execute();
									$old_obs = $dbo->getNumRows() > 0 ? $dbo->loadAssocList() : array();
									foreach ($kit_relations as $kit_rel) {
										for ($i = 1; $i <= $kit_rel['units']; $i++) {
											foreach ($old_obs as $old_ob) {
												$q = "DELETE FROM `#__vikrentitems_busy` WHERE `id`=".$old_ob['idbusy']." AND `iditem`=" . $dbo->quote($kit_rel['iditem']) . " LIMIT 1;";
												$dbo->setQuery($q);
												$dbo->execute();
												if ($dbo->getAffectedRows() > 0) {
													$q = "DELETE FROM `#__vikrentitems_ordersbusy` WHERE `id`=".$old_ob['id']." LIMIT 1;";
													$dbo->setQuery($q);
													$dbo->execute();
												}
											}
										}
									}
									//
								}
								// new kit relations
								$kit_relations = VikRentItems::getKitRelatedItems($rsw['to']);
								if (count($kit_relations)) {
									// newly switched item is a kit
									foreach ($kit_relations as $kit_rel) {
										for ($i = 1; $i <= $kit_rel['units']; $i++) {
											$q = "INSERT INTO `#__vikrentitems_busy` (`iditem`,`ritiro`,`consegna`,`realback`) VALUES(" . $dbo->quote($kit_rel['iditem']) . ", '" . $ord[0]['ritiro'] . "', '" . $ord[0]['consegna'] . "','" . $ord[0]['consegna'] . "');";
											$dbo->setQuery($q);
											$dbo->execute();
											$newbusyid = $dbo->insertid();
											$q = "INSERT INTO `#__vikrentitems_ordersbusy` (`idorder`,`idbusy`) VALUES(".$ord[0]['id'].", ".(int)$newbusyid.");";
											$dbo->setQuery($q);
											$dbo->execute();
										}
									}
									//
								}
								//
							}
						} elseif ($ord[0]['status'] == 'standby') {
							//remove record in _tmplock
							$q = "DELETE FROM `#__vikrentitems_tmplock` WHERE `idorder`=" . intval($ord[0]['id']) . ";";
							$dbo->setQuery($q);
							$dbo->execute();
						}
					}
					$app->redirect("index.php?option=com_vikrentitems&task=editbusy&cid[]=".$ord[0]['id'].($pgoto == 'overv' ? "&goto=overv" : ""));
					exit;
				}
			}
			//
			$first = VikRentItems::getDateTimestamp($pritiro, $ppickuph, $ppickupm);
			$second = VikRentItems::getDateTimestamp($pconsegna, $pdropoffh, $pdropoffm);
			if ($second > $first) {
				$checkhourly = false;
				$checkhourscharges = 0;
				$hoursdiff = 0;
				$secdiff = $second - $first;
				$daysdiff = $secdiff / 86400;
				if (is_int($daysdiff)) {
					if ($daysdiff < 1) {
						$daysdiff = 1;
					}
				} else {
					if ($daysdiff < 1) {
						$daysdiff = 1;
						$checkhourly = true;
						$ophours = $secdiff / 3600;
						$hoursdiff = intval(round($ophours));
						if ($hoursdiff < 1) {
							$hoursdiff = 1;
						}
					} else {
						$sum = floor($daysdiff) * 86400;
						$newdiff = $secdiff - $sum;
						$maxhmore = VikRentItems::getHoursMoreRb() * 3600;
						if ($maxhmore >= $newdiff) {
							$daysdiff = floor($daysdiff);
						} else {
							$daysdiff = ceil($daysdiff);
							$ehours = intval(round(($newdiff - $maxhmore) / 3600));
							$checkhourscharges = $ehours;
							if ($checkhourscharges > 0) {
								$aehourschbasp = VikRentItems::applyExtraHoursChargesBasp();
							}
						}
					}
				}
				$groupdays = VikRentItems::getGroupDays($first, $second, $daysdiff);
				// VRI 1.6 - Allow pick ups on drop offs
				$picksondrops = VikRentItems::allowPickOnDrop();
				//
				$opertwounits = true;
				$notbookable = array();
				$units_counter = array();
				foreach ($orderitems as $ind => $oi) {
					$pitemquant = VikRequest::getInt('itemquant'.$ind, 1, 'request');
					$pitemquant = $pitemquant < 1 ? 1 : $pitemquant;
					$orderitems[$ind]['itemquant'] = $pitemquant;
					if (!isset($units_counter[$oi['iditem']])) {
						$units_counter[$oi['iditem']] = -1;
					}
					$units_counter[$oi['iditem']]++;
				}
				foreach ($orderitems as $ind => $oi) {
					$num = $ind + 1;
					$check = "SELECT `b`.`id`,`b`.`ritiro`,`b`.`realback`,`ob`.`idorder` FROM `#__vikrentitems_busy` AS `b`,`#__vikrentitems_ordersbusy` AS `ob` WHERE `b`.`iditem`='" . $oi['iditem'] . "' AND `b`.`id`=`ob`.`idbusy` AND `ob`.`idorder`!='".$ord[0]['id']."';";
					$dbo->setQuery($check);
					$dbo->execute();
					if ($dbo->getNumRows() > 0) {
						$busy = $dbo->loadAssocList();
						foreach ($groupdays as $gday) {
							$bfound = 0;
							foreach ($busy as $bu) {
								if ($gday >= $bu['ritiro'] && $gday <= $bu['realback']) {
									if ($picksondrops && !($gday > $bu['ritiro'] && $gday < $bu['realback'])) {
										// VRI 1.6 - pick ups on drop offs allowed
										continue;
									}
									$bfound++;
								} elseif (count($groupdays) == 2 && $gday == $groupdays[0]) {
									if ($groupdays[0] < $bu['ritiro'] && $groupdays[0] < $bu['realback'] && $groupdays[1] > $bu['ritiro'] && $groupdays[1] > $bu['realback']) {
										$bfound++;
									}
								}
							}
							if (($bfound + $oi['itemquant']) > $oi['units'] || !VikRentItems::itemNotLocked($oi['iditem'], $oi['units'], $first, $second, $oi['itemquant'])) {
								$notbookable[] = $oi['name'].($oi['itemquant'] > 1 ? " x".$oi['itemquant'] : "");
								$opertwounits = false;
							}
						}
					}
				}
				if ($opertwounits === true) {
					//update dates, customer information, amount paid and busy records before checking the rates
					$realback = VikRentItems::getHoursItemAvail() * 3600;
					$realback += $second;
					$newtotalpaid = strlen($ptotpaid) > 0 ? floatval($ptotpaid) : "";
					//Vik Rent Items 1.6 - Add Room to existing booking
					$item_added = false;
					$padd_item_id = VikRequest::getInt('add_item_id', '', 'request');
					$padd_item_quantity = VikRequest::getInt('add_item_quantity', 1, 'request');
					$padd_item_quantity = $padd_item_quantity < 1 ? 1 : $padd_item_quantity;
					$padd_item_price = VikRequest::getFloat('add_item_price', 0, 'request');
					$paliq_add_item = VikRequest::getInt('aliq_add_item', 0, 'request');
					if ($padd_item_id > 0) {
						//no need to re-validate the availability for this new item, as it was made via JS in the View.
						//insert the new item record
						$q = "INSERT INTO `#__vikrentitems_ordersitems` (`idorder`,`iditem`,`itemquant`,`cust_cost`,`cust_idiva`) VALUES(".$ord[0]['id'].", ".$padd_item_id.", ".$padd_item_quantity.", ".($padd_item_price > 0 ? $dbo->quote($padd_item_price) : 'NULL').", ".($padd_item_price > 0 && !empty($paliq_add_item) ? $dbo->quote($paliq_add_item) : 'NULL').");";
						$dbo->setQuery($q);
						$dbo->execute();
						$item_added = true;
					}
					//Vik Rent Items 1.6 - Remove Room from existing booking
					$item_removed = false;
					$prm_item_oid = VikRequest::getInt('rm_item_oid', '', 'request');
					if ($prm_item_oid > 0 && count($orderitems) > 1) {
						//check if the requested item record exists for removal
						$q = "SELECT * FROM `#__vikrentitems_ordersitems` WHERE `id`=".$prm_item_oid." AND `idorder`=".$ord[0]['id'].";";
						$dbo->setQuery($q);
						$dbo->execute();
						if ($dbo->getNumRows() == 1) {
							$item_before_rm = $dbo->loadAssoc();
							//remove the requested item record
							$q = "DELETE FROM `#__vikrentitems_ordersitems` WHERE `id`=".$prm_item_oid." AND `idorder`=".$ord[0]['id']." LIMIT 1;";
							$dbo->setQuery($q);
							$dbo->execute();
							$item_removed = $item_before_rm['iditem'];
						}
					}
					//
					//update booking's basic information (customer data, dates, tot paid, locations, delivery cost)
					$q = "UPDATE `#__vikrentitems_orders` SET `custdata`=".$dbo->quote($pcustdata).", `days`='".$daysdiff."', `ritiro`='".$first."', `consegna`='".$second."', `idplace`=".(!empty($pidplace) ? $pidplace : 'NULL').", `idreturnplace`=".(!empty($pidreturnplace) ? $pidreturnplace : 'NULL').(strlen($newtotalpaid) > 0 ? ", `totpaid`='".$newtotalpaid."'" : "").", `deliverycost`=".$dbo->quote($pdeliverycost)." WHERE `id`='".$ord[0]['id']."';";
					$dbo->setQuery($q);
					$dbo->execute();
					// update the order array information about the locations and delivery
					$ord[0]['idplace'] = $pidplace;
					$ord[0]['idreturnplace'] = $pidreturnplace;
					$ord[0]['deliverycost'] = $pdeliverycost;
					//
					if ($ord[0]['status'] == 'confirmed') {
						$q = "SELECT `b`.`id`,`b`.`iditem` FROM `#__vikrentitems_busy` AS `b`,`#__vikrentitems_ordersbusy` AS `ob` WHERE `b`.`id`=`ob`.`idbusy` AND `ob`.`idorder`='".$ord[0]['id']."';";
						$dbo->setQuery($q);
						$dbo->execute();
						$allbusy = $dbo->loadAssocList();
						foreach ($allbusy as $bb) {
							$q = "UPDATE `#__vikrentitems_busy` SET `ritiro`='".$first."', `consegna`='".$second."', `realback`='".$realback."' WHERE `id`='".$bb['id']."';";
							$dbo->setQuery($q);
							$dbo->execute();
						}
						//Vik Rent Items 1.6 - Add item to existing (Confirmed) booking
						if ($item_added === true) {
							//add busy record for the new item unit
							$q = "INSERT INTO `#__vikrentitems_busy` (`iditem`,`ritiro`,`consegna`,`realback`) VALUES(".$padd_item_id.", ".$dbo->quote($first).", ".$dbo->quote($second).", ".$dbo->quote($realback).");";
							$dbo->setQuery($q);
							$dbo->execute();
							$newbusyid = $dbo->insertid();
							$q = "INSERT INTO `#__vikrentitems_ordersbusy` (`idorder`,`idbusy`) VALUES(".$ord[0]['id'].", ".(int)$newbusyid.");";
							$dbo->setQuery($q);
							$dbo->execute();
							// Kit relations
							$kit_relations = VikRentItems::getKitRelatedItems($padd_item_id);
							if (count($kit_relations)) {
								//VRI 1.5 - store busy records for the children or parent items, in case of a kit (Group/Set of Items)
								foreach ($kit_relations as $kit_rel) {
									for ($i = 1; $i <= $kit_rel['units']; $i++) {
										$q = "INSERT INTO `#__vikrentitems_busy` (`iditem`,`ritiro`,`consegna`,`realback`) VALUES(" . $dbo->quote($kit_rel['iditem']) . ", '" . $first . "', '" . $second . "','" . $realback . "');";
										$dbo->setQuery($q);
										$dbo->execute();
										$newbusyid = $dbo->insertid();
										$q = "INSERT INTO `#__vikrentitems_ordersbusy` (`idorder`,`idbusy`) VALUES(".$ord[0]['id'].", ".(int)$newbusyid.");";
										$dbo->setQuery($q);
										$dbo->execute();
									}
								}
								//
							}
							//
						}
						//Vik Rent Items 1.6 - Remove item from existing (Confirmed) booking
						if ($item_removed !== false) {
							//remove busy record for the removed item
							foreach ($allbusy as $bb) {
								if ($bb['iditem'] == $item_removed) {
									//remove the first item with this ID that was booked
									$q = "DELETE FROM `#__vikrentitems_busy` WHERE `id`=".$bb['id']." AND `iditem`=".$item_removed.";";
									$dbo->setQuery($q);
									$dbo->execute();
									$q = "DELETE FROM `#__vikrentitems_ordersbusy` WHERE `idorder`=".$ord[0]['id']." AND `idbusy`=".$bb['id'].";";
									$dbo->setQuery($q);
									$dbo->execute();
									break;
								}
							}
							// Kit relations
							$kit_relations = VikRentItems::getKitRelatedItems($item_removed);
							if (count($kit_relations)) {
								// removed item was a kit: free up busy records
								$q = "SELECT * FROM `#__vikrentitems_ordersbusy` WHERE `idorder`=".$ord[0]['id'].";";
								$dbo->setQuery($q);
								$dbo->execute();
								$old_obs = $dbo->getNumRows() > 0 ? $dbo->loadAssocList() : array();
								foreach ($kit_relations as $kit_rel) {
									for ($i = 1; $i <= $kit_rel['units']; $i++) {
										foreach ($old_obs as $old_ob) {
											$q = "DELETE FROM `#__vikrentitems_busy` WHERE `id`=".$old_ob['idbusy']." AND `iditem`=" . $dbo->quote($kit_rel['iditem']) . " LIMIT 1;";
											$dbo->setQuery($q);
											$dbo->execute();
											if ($dbo->getAffectedRows() > 0) {
												$q = "DELETE FROM `#__vikrentitems_ordersbusy` WHERE `id`=".$old_ob['id']." LIMIT 1;";
												$dbo->setQuery($q);
												$dbo->execute();
											}
										}
									}
								}
								//
							}
							//
						}
						//
					}
					$upd_esit = JText::_('RESUPDATED');
					//
					$isdue = 0;
					$isdue += $ord[0]['deliverycost'];
					$doup = true;
					$notar = array();
					$tars = array();
					$cust_costs = array();
					$items_costs_map = array();
					foreach ($orderitems as $koi => $oi) {
						//Vik Rent Items 1.6 - Remove from existing booking
						if ($item_removed !== false) {
							if ($oi['id'] == $prm_item_oid) {
								//do not consider this item for the calculation of the new total amount
								unset($orderitems[$koi]);
								continue;
							}
						}
						//
						$num = $koi + 1;
						$ppriceid = VikRequest::getString('priceid'.$num, '', 'request');
						$pcust_cost = VikRequest::getString('cust_cost'.$num, '', 'request');
						$paliq = VikRequest::getString('aliq'.$num, '', 'request');
						if (empty($ppriceid) && !empty($pcust_cost) && floatval($pcust_cost) > 0) {
							$cust_costs[$num] = array('cust_cost' => $pcust_cost, 'aliq' => $paliq);
							$isdue += (float)$pcust_cost;
							continue;
						}
						$tar = array();
						if ($checkhourly) {
							$q = "SELECT * FROM `#__vikrentitems_dispcosthours` WHERE `iditem`=".(int)$oi['iditem']." AND `hours`=".(int)$hoursdiff." AND `idprice`=".(int)$ppriceid.";";
						} else {
							$q = "SELECT * FROM `#__vikrentitems_dispcost` WHERE `iditem`=".(int)$oi['iditem']." AND `days`=".(int)$daysdiff." AND `idprice`=".(int)$ppriceid.";";
						}
						$dbo->setQuery($q);
						$dbo->execute();
						if ($dbo->getNumRows() == 1) {
							$tar = $dbo->loadAssocList();
							if ($checkhourly) {
								// set the order to be hourly
								$ord[0]['hourly'] = 1;
								//
								foreach ($tar as $kt => $vt) {
									$tar[$kt]['days'] = 1;
								}
							}
						} else {
							//there are no hourly prices
							if ($checkhourly) {
								$q = "SELECT * FROM `#__vikrentitems_dispcost` WHERE `iditem`=".(int)$oi['iditem']." AND `days`=".(int)$daysdiff." AND `idprice`=".(int)$ppriceid.";";
								$dbo->setQuery($q);
								$dbo->execute();
								if ($dbo->getNumRows() == 1) {
									$tar = $dbo->loadAssocList();
								}
							}
						}
						if (count($tar) == 0) {
							$doup = false;
							$notar[] = $oi['name'];
							break;
						}
						if ($checkhourscharges > 0 && $aehourschbasp == true) {
							$ret = VikRentItems::applyExtraHoursChargesItem($tar, $oi['iditem'], $checkhourscharges, $daysdiff, false, false, true);
							$tar = $ret['return'];
							$calcdays = $ret['days'];
						}
						if ($checkhourscharges > 0 && $aehourschbasp == false) {
							$tar = VikRentItems::extraHoursSetPreviousFareItem($tar, $oi['iditem'], $checkhourscharges, $daysdiff, false);
							$tar = VikRentItems::applySeasonsItem($tar, $first, $second, $ord[0]['idplace']);
							$ret = VikRentItems::applyExtraHoursChargesItem($tar, $oi['iditem'], $checkhourscharges, $daysdiff, true, false, true);
							$tar = $ret['return'];
							$calcdays = $ret['days'];
						} else {
							$tar = VikRentItems::applySeasonsItem($tar, $first, $second, $ord[0]['idplace']);
						}
						$tar = VikRentItems::applyItemDiscounts($tar, $oi['iditem'], $oi['itemquant']);
						if ($oi['itemquant'] > 0) {
							$isdue += VikRentItems::sayCostPlusIva($tar[0]['cost'] * $oi['itemquant'], $tar[0]['idprice'], $ord[0]);
						}
						//when editing the reservation and hours charges, a different fare can be chosen so the ID must be updated in $tar
						if ($checkhourscharges > 0 && !empty($calcdays) && $calcdays > 0 && (int)$daysdiff != (int)$calcdays) {
							foreach ($tar as $kt => $tt) {
								$q = "SELECT `id` FROM `#__vikrentitems_dispcost` WHERE `iditem`=".(int)$tt['iditem']." AND `days`=".(int)$tt['days']." AND `idprice`=".(int)$tt['idprice'].";";
								$dbo->setQuery($q);
								$dbo->execute();
								$validdaytarid = $dbo->loadResult();
								if (strlen($validdaytarid) > 0) {
									$tar[$kt]['id'] = $validdaytarid;
								}
							}
						}
						//
						$orderitems[$koi]['tar'] = $tar;
						$tars[$num] = $tar;
						$items_costs_map[$num] = $tar[0]['cost'];
					}
					if ($doup === true) {
						if (isset($calcdays) && $calcdays > 0 && (int)$daysdiff != (int)$calcdays) {
							$daysdiff = $calcdays;
						}
						if ($item_added === true) {
							//Vik Rent Items 1.6 - Add item to existing booking may require to increase the total amount
							$padd_item_price = VikRequest::getFloat('add_item_price', 0, 'request');
							$paliq_add_item = VikRequest::getInt('aliq_add_item', 0, 'request');
							if (!empty($padd_item_price) && floatval($padd_item_price) > 0) {
								$isdue += (float)$padd_item_price;
							}
							//
						}
						if (!empty($ord[0]['idplace']) && !empty($ord[0]['idreturnplace'])) {
							$locfee = VikRentItems::getLocFee($ord[0]['idplace'], $ord[0]['idreturnplace']);
							if ($locfee) {
								//VikRentItems 1.1 - Location fees overrides
								if (strlen($locfee['losoverride']) > 0) {
									$arrvaloverrides = array();
									$valovrparts = explode('_', $locfee['losoverride']);
									foreach ($valovrparts as $valovr) {
										if (!empty($valovr)) {
											$ovrinfo = explode(':', $valovr);
											$arrvaloverrides[$ovrinfo[0]] = $ovrinfo[1];
										}
									}
									if (array_key_exists($daysdiff, $arrvaloverrides)) {
										$locfee['cost'] = $arrvaloverrides[$daysdiff];
									}
								}
								//end VikRentItems 1.1 - Location fees overrides
								$locfeecost = intval($locfee['daily']) == 1 ? ($locfee['cost'] * $daysdiff) : $locfee['cost'];
								$locfeewith = VikRentItems::sayLocFeePlusIva($locfeecost, $locfee['idiva'], $ord[0]);
								$isdue += $locfeewith;
							}
						}
						$toptionals = '';
						$q = "SELECT * FROM `#__vikrentitems_optionals` ORDER BY `#__vikrentitems_optionals`.`ordering` ASC;";
						$dbo->setQuery($q);
						$dbo->execute();
						if ($dbo->getNumRows() > 0) {
							$toptionals = $dbo->loadAssocList();
						}
						foreach ($orderitems as $koi => $oi) {
							$num = $koi + 1;
							$wop = "";
							if (is_array($toptionals)) {
								foreach ($toptionals as $opt) {
									$tmpvar = VikRequest::getString('optid'.$num.$opt['id'], '', 'request');
									if (!empty($tmpvar)) {
										if (!empty($opt['specifications'])) {
											$optspeccosts = VikRentItems::getOptionSpecIntervalsCosts($opt['specifications']);
											$optspecnames = VikRentItems::getOptionSpecIntervalsNames($opt['specifications']);
											$opt['quan'] = 1;
											$opt['cost'] = $optspeccosts[($tmpvar - 1)];
											$opt['name'] .= ': '.$optspecnames[($tmpvar - 1)];
											$opt['specintv'] = $tmpvar;
											$wop .= $opt['id'].":".$opt['quan']."-".$tmpvar.";";
											$realcost = (intval($opt['perday']) == 1 ? ($opt['cost'] * $daysdiff * $opt['quan']) : ($opt['cost'] * $opt['quan']));
										} else {
											$wop .= $opt['id'].":".$tmpvar.";";
											$realcost = (intval($opt['perday']) == 1 ? ($opt['cost'] * $daysdiff * $tmpvar) : ($opt['cost'] * $tmpvar));
										}
										if (!empty($opt['maxprice']) && $opt['maxprice'] > 0 && $realcost > $opt['maxprice']) {
											$realcost = $opt['maxprice'];
											if (intval($opt['hmany']) == 1 && intval($tmpvar) > 1) {
												$realcost = $opt['maxprice'] * $tmpvar;
											}
										}
										$opt_item_units = $opt['onceperitem'] ? 1 : $oi['itemquant'];
										$tmpopr = VikRentItems::sayOptionalsPlusIva($realcost * $opt_item_units, $opt['idiva'], $ord[0]);
										$isdue += $tmpopr;
									}
								}
							}
							$upd_fields = array();
							if (array_key_exists($num, $tars)) {
								//type of price
								$upd_fields[] = "`idtar`='".$tars[$num][0]['id']."'";
								$upd_fields[] = "`cust_cost`=NULL";
								$upd_fields[] = "`cust_idiva`=NULL";
							} elseif (array_key_exists($num, $cust_costs) && array_key_exists('cust_cost', $cust_costs[$num])) {
								//custom rate + custom tax rate
								$upd_fields[] = "`idtar`=NULL";
								$upd_fields[] = "`cust_cost`='".$cust_costs[$num]['cust_cost']."'";
								$upd_fields[] = "`cust_idiva`='".$cust_costs[$num]['aliq']."'";
							}
							if (is_array($toptionals)) {
								$upd_fields[] = "`optionals`='".$wop."'";
							}
							// quantity and delivery address
							$pdeliveryaddr = VikRequest::getString('deliveryaddr'.$oi['id'], '', 'request');
							$pdeliverydist = VikRequest::getFloat('deliverydist'.$oi['id'], 0, 'request');
							$upd_fields[] = "`itemquant`=".$oi['itemquant'];
							$upd_fields[] = "`deliveryaddr`=".$dbo->quote($pdeliveryaddr);
							$upd_fields[] = "`deliverydist`=".$dbo->quote($pdeliverydist);
							//calculate the extra costs and increase isdue
							$extracosts_arr = array();
							if (count($pextracn) > 0 && count($pextracn[$num]) > 0) {
								foreach ($pextracn[$num] as $eck => $ecn) {
									if (strlen($ecn) > 0 && array_key_exists($eck, $pextracc[$num]) && is_numeric($pextracc[$num][$eck])) {
										$ecidtax = array_key_exists($eck, $pextractx[$num]) && intval($pextractx[$num][$eck]) > 0 ? (int)$pextractx[$num][$eck] : '';
										$extracosts_arr[] = array('name' => $ecn, 'cost' => (float)$pextracc[$num][$eck], 'idtax' => $ecidtax);
										$ecplustax = !empty($ecidtax) ? VikRentItems::sayOptionalsPlusIva((float)$pextracc[$num][$eck], $ecidtax, $ord[0]) : (float)$pextracc[$num][$eck];
										$isdue += $ecplustax;
									}
								}
							}
							if (count($extracosts_arr) > 0) {
								$upd_fields[] = "`extracosts`=".$dbo->quote(json_encode($extracosts_arr));
							} else {
								$upd_fields[] = "`extracosts`=NULL";
							}
							//end extra costs
							if (count($upd_fields) > 0) {
								$q = "UPDATE `#__vikrentitems_ordersitems` SET ".implode(', ', $upd_fields)." WHERE `idorder`=".(int)$ord[0]['id']." AND `iditem`=".(int)$oi['iditem']." AND `id`=".(int)$oi['id'].";";
								$dbo->setQuery($q);
								$dbo->execute();
							}
						}
						$q = "UPDATE `#__vikrentitems_orders` SET `hourly`=".(int)$ord[0]['hourly'].", `order_total`='".$isdue."' WHERE `id`=".(int)$ord[0]['id'].";";
						$dbo->setQuery($q);
						$dbo->execute();
						$upd_esit = JText::_('VRIRESRATESUPDATED');
					} else {
						VikError::raiseWarning('', JText::sprintf('VRIERRNOTAR', implode(", ", $notar)));
					}
					$mainframe->enqueueMessage($upd_esit);
				} else {
					VikError::raiseWarning('', JText::_('VRIARNOTRIT')." ".date($df.' H:i', $first)." ".JText::_('VRIARNOTCONSTO')." ".date($df.' H:i', $second).'<br/>'.implode(", ", $notbookable));
				}
			} else {
				VikError::raiseWarning('', JText::_('ERRPREV'));
			}
                        $result = $this->updatebusy_pdf($pidorder);
			$mainframe->redirect("index.php?option=com_vikrentitems&task=editbusy&cid[]=".$ord[0]['id'].($pgoto == 'overv' ? "&goto=overv" : ""));
		} else {
			$mainframe->redirect("index.php?option=com_vikrentitems&task=rooms");
		}
	}
        function updatebusy_pdf($oid){
$dbo = JFactory::getDBO();
		$mainframe = JFactory::getApplication();
		$q = "SELECT * FROM `#__vikrentitems_orders` WHERE `id`=".(int)$oid.";";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() == 1) {
			$order = $dbo->loadAssocList();
			$vri_tn = VikRentItems::getTranslator();
			//check if the language in use is the same as the one used during the checkout
			if (!empty($order[0]['lang'])) {
				$lang = JFactory::getLanguage();
				if ($lang->getTag() != $order[0]['lang']) {
					$lang->load('com_vikrentitems', JPATH_ADMINISTRATOR, $order[0]['lang'], true);
					$vri_tn::$force_tolang = $order[0]['lang'];
				}
			}
			//
			$totdelivery = $order[0]['deliverycost'];
			$checkhourscharges = 0;
			$ppickup = $order[0]['ritiro'];
			$prelease = $order[0]['consegna'];
			$secdiff = $prelease - $ppickup;
			$daysdiff = $secdiff / 86400;
			if (is_int($daysdiff)) {
				if ($daysdiff < 1) {
					$daysdiff = 1;
				}
			} else {
				if ($daysdiff < 1) {
					$daysdiff = 1;
				} else {
					$sum = floor($daysdiff) * 86400;
					$newdiff = $secdiff - $sum;
					$maxhmore = VikRentItems::getHoursMoreRb() * 3600;
					if ($maxhmore >= $newdiff) {
						$daysdiff = floor($daysdiff);
					} else {
						$daysdiff = ceil($daysdiff);
						$ehours = intval(round(($newdiff - $maxhmore) / 3600));
						$checkhourscharges = $ehours;
						if ($checkhourscharges > 0) {
							$aehourschbasp = VikRentItems::applyExtraHoursChargesBasp();
						}
					}
				}
			}
			//send mail
			$ftitle = VikRentItems::getFrontTitle($vri_tn);
			$nowts = $order[0]['ts'];
			$viklink = JURI::root()."index.php?option=com_vikrentitems&task=vieworder&sid=".$order[0]['sid']."&ts=".$order[0]['ts'];
			$ritplace = (!empty($order[0]['idplace']) ? VikRentItems::getPlaceName($order[0]['idplace'], $vri_tn) : "");
			$consegnaplace=(!empty($order[0]['idreturnplace']) ? VikRentItems::getPlaceName($order[0]['idreturnplace'], $vri_tn) : "");
			$isdue = 0;
			$vricart = array();
			$q = "SELECT `oi`.*,`i`.`name`,`i`.`units` FROM `#__vikrentitems_ordersitems` AS `oi`,`#__vikrentitems_items` AS `i` WHERE `oi`.`idorder`='".$order[0]['id']."' AND `oi`.`iditem`=`i`.`id` ORDER BY `oi`.`id` ASC;";
			$dbo->setQuery($q);
			$dbo->execute();
			$orderitems = $dbo->loadAssocList();
			$vri_tn->translateContents($orderitems, '#__vikrentitems_items', array('id' => 'iditem'));
			$maillocfee = "";
			$locfeewithouttax = 0;
			if (!empty($order[0]['idplace']) && !empty($order[0]['idreturnplace'])) {
				$locfee = VikRentItems::getLocFee($order[0]['idplace'], $order[0]['idreturnplace']);
				if ($locfee) {
					//VikRentItems 1.1 - Location fees overrides
					if (strlen($locfee['losoverride']) > 0) {
						$arrvaloverrides = array();
						$valovrparts = explode('_', $locfee['losoverride']);
						foreach ($valovrparts as $valovr) {
							if (!empty($valovr)) {
								$ovrinfo = explode(':', $valovr);
								$arrvaloverrides[$ovrinfo[0]] = $ovrinfo[1];
							}
						}
						if (array_key_exists($order[0]['days'], $arrvaloverrides)) {
							$locfee['cost'] = $arrvaloverrides[$order[0]['days']];
						}
					}
					//end VikRentItems 1.1 - Location fees overrides
					$locfeecost = intval($locfee['daily']) == 1 ? ($locfee['cost'] * $order[0]['days']) : $locfee['cost'];
					$locfeewith = VikRentItems::sayLocFeePlusIva($locfeecost, $locfee['idiva'], $order[0]);
					$isdue += $locfeewith;
					$locfeewithouttax = VikRentItems::sayLocFeeMinusIva($locfeecost, $locfee['idiva'], $order[0]);
					$maillocfee = $locfeewith;
				}
			}
			foreach ($orderitems as $koi => $oi) {
				$tar = array(array(
					'id' => 0,
					'iditem' => $oi['iditem'],
					'days' => $order[0]['days'],
					'idprice' => -1,
					'cost' => 0,
					'attrdata' => '',
				));
				$is_cust_cost = (!empty($oi['cust_cost']) && $oi['cust_cost'] > 0);
				if (!empty($oi['idtar'])) {
					if ($order[0]['hourly'] == 1) {
						$q = "SELECT * FROM `#__vikrentitems_dispcosthours` WHERE `id`=".(int)$oi['idtar'].";";
					} else {
						$q = "SELECT * FROM `#__vikrentitems_dispcost` WHERE `id`=".(int)$oi['idtar'].";";
					}
					$dbo->setQuery($q);
					$dbo->execute();
					if ($dbo->getNumRows() == 0) {
						if ($order[0]['hourly'] == 1) {
							$q = "SELECT * FROM `#__vikrentitems_dispcost` WHERE `id`=".(int)$oi['idtar'].";";
							$dbo->setQuery($q);
							$dbo->execute();
							if ($dbo->getNumRows() == 1) {
								$tar = $dbo->loadAssocList();
							}
						}
					} else {
						$tar = $dbo->loadAssocList();
					}
				} elseif ($is_cust_cost) {
					//Custom Rate
					$tar = array(array(
						'id' => -1,
						'iditem' => $oi['iditem'],
						'days' => $order[0]['days'],
						'idprice' => -1,
						'cost' => $oi['cust_cost'],
						'attrdata' => '',
					));
				}
				if (count($tar) && !empty($tar[0]['id'])) {
					if ($order[0]['hourly'] == 1 && !empty($tar[0]['hours'])) {
						foreach ($tar as $kt => $vt) {
							$tar[$kt]['days'] = 1;
						}
					}
					if ($checkhourscharges > 0 && $aehourschbasp == true) {
						$ret = VikRentItems::applyExtraHoursChargesItem($tar, $oi['iditem'], $checkhourscharges, $daysdiff, false, true, true);
						$tar = $ret['return'];
						$calcdays = $ret['days'];
					}
					if ($checkhourscharges > 0 && $aehourschbasp == false) {
						$tar = VikRentItems::extraHoursSetPreviousFareItem($tar, $oi['iditem'], $checkhourscharges, $daysdiff, true);
						$tar = VikRentItems::applySeasonsItem($tar, $order[0]['ritiro'], $order[0]['consegna'], $order[0]['idplace']);
						$ret = VikRentItems::applyExtraHoursChargesItem($tar, $oi['iditem'], $checkhourscharges, $daysdiff, true, true, true);
						$tar = $ret['return'];
						$calcdays = $ret['days'];
					} else {
						$tar = VikRentItems::applySeasonsItem($tar, $order[0]['ritiro'], $order[0]['consegna'], $order[0]['idplace']);
					}
					$tar = VikRentItems::applyItemDiscounts($tar, $oi['iditem'], $oi['itemquant']);
				}
				$costplusiva = $is_cust_cost ? $tar[0]['cost'] : VikRentItems::sayCostPlusIva($tar[0]['cost'] * $oi['itemquant'], $tar[0]['idprice'], $order[0]);
				$costminusiva = $is_cust_cost ? VikRentItems::sayCustCostMinusIva($tar[0]['cost'], $oi['cust_idiva']) : VikRentItems::sayCostMinusIva($tar[0]['cost'] * $oi['itemquant'], $tar[0]['idprice'], $order[0]);
				$pricestr = ($is_cust_cost ? JText::_('VRIRENTCUSTRATEPLAN').": ".$costplusiva : VikRentItems::getPriceName($tar[0]['idprice'], $vri_tn).": ".$costplusiva.(!empty($tar[0]['attrdata']) ? "\n".VikRentItems::getPriceAttr($tar[0]['idprice'], $vri_tn).": ".$tar[0]['attrdata'] : ""));
				$isdue += $is_cust_cost ? $tar[0]['cost'] : VikRentItems::sayCostPlusIva($tar[0]['cost'] * $oi['itemquant'], $tar[0]['idprice'], $order[0]);
				$optstr = "";
				$optarrtaxnet = array();
				if (!empty($oi['optionals'])) {
					$stepo = explode(";", $oi['optionals']);
					foreach ($stepo as $oo){
						if (!empty($oo)) {
							$stept = explode(":", $oo);
							$q = "SELECT * FROM `#__vikrentitems_optionals` WHERE `id`='".intval($stept[0])."';";
							$dbo->setQuery($q);
							$dbo->execute();
							if ($dbo->getNumRows() == 1) {
								$actopt = $dbo->loadAssocList();
								$vri_tn->translateContents($actopt, '#__vikrentitems_optionals');
								$specvar = '';
								if (!empty($actopt[0]['specifications']) && strstr($stept[1], '-') != false) {
									$optspeccosts = VikRentItems::getOptionSpecIntervalsCosts($actopt[0]['specifications']);
									$optspecnames = VikRentItems::getOptionSpecIntervalsNames($actopt[0]['specifications']);
									$specstept = explode('-', $stept[1]);
									$stept[1] = $specstept[0];
									$specvar = $specstept[1];
									$actopt[0]['specintv'] = $specvar;
									$actopt[0]['name'] .= ' ('.$optspecnames[($specvar - 1)].')';
									$actopt[0]['quan'] = $stept[1];
									$realcost = (intval($actopt[0]['perday']) == 1 ? (floatval($optspeccosts[($specvar - 1)]) * $order[0]['days'] * $stept[1]) : (floatval($optspeccosts[($specvar - 1)]) * $stept[1]));
								} else {
									$realcost = (intval($actopt[0]['perday'])==1 ? ($actopt[0]['cost'] * $order[0]['days'] * $stept[1]) : ($actopt[0]['cost'] * $stept[1]));
								}
								if (!empty($actopt[0]['maxprice']) && $actopt[0]['maxprice'] > 0 && $realcost > $actopt[0]['maxprice']) {
									$realcost = $actopt[0]['maxprice'];
									if (intval($actopt[0]['hmany']) == 1 && intval($stept[1]) > 1) {
										$realcost = $actopt[0]['maxprice'] * $stept[1];
									}
								}
								$opt_item_units = $actopt[0]['onceperitem'] ? 1 : $oi['itemquant'];
								$tmpopr = VikRentItems::sayOptionalsPlusIva($realcost * $opt_item_units, $actopt[0]['idiva'], $order[0]);
								$isdue += $tmpopr;
								$optnetprice = VikRentItems::sayOptionalsMinusIva($realcost * $opt_item_units, $actopt[0]['idiva'], $order[0]);
								$optarrtaxnet[] = $optnetprice;
								$optstr .= ($stept[1] > 1 ? $stept[1]." " : "").$actopt[0]['name'].": ".$tmpopr."\n";
							}
						}
					}
				}

				// VRI 1.6 - custom extra costs
				if (!empty($oi['extracosts'])) {
					$cur_extra_costs = json_decode($oi['extracosts'], true);
					foreach ($cur_extra_costs as $eck => $ecv) {
						$efee_cost = VikRentItems::sayOptionalsPlusIva($ecv['cost'], $ecv['idtax'], $order[0]);
						$isdue += $efee_cost;
						$efee_cost_without = VikRentItems::sayOptionalsMinusIva($ecv['cost'], $ecv['idtax'], $order[0]);
						$optarrtaxnet[] = $efee_cost_without;
						$optstr .= $ecv['name'].": ".$efee_cost."\n";
					}
				}
				//
				$arrayinfopdf = array('days' => $order[0]['days'], 'tarminusiva' => $costminusiva, 'tartax' => ($costplusiva - $costminusiva), 'opttaxnet' => $optarrtaxnet, 'locfeenet' => $locfeewithouttax);
				$vricart[$oi['iditem']][$koi]['itemquant'] = $oi['itemquant'];
				$vricart[$oi['iditem']][$koi]['info'] = VikRentItems::getItemInfo($oi['iditem'], $vri_tn);
				$vricart[$oi['iditem']][$koi]['pricestr'] = $pricestr;
				$vricart[$oi['iditem']][$koi]['optstr'] = $optstr;
				$vricart[$oi['iditem']][$koi]['optarrtaxnet'] = $optarrtaxnet;
				$vricart[$oi['iditem']][$koi]['infopdf'] = $arrayinfopdf;
				if (!empty($oi['timeslot'])) {
					$vricart[$oi['iditem']][$koi]['timeslot']['name'] = $oi['timeslot'];
				}
				if (!empty($oi['deliveryaddr'])) {
					$vricart[$oi['iditem']][$koi]['delivery']['vrideliveryaddress'] = $oi['deliveryaddr'];
					$vricart[$oi['iditem']][$koi]['delivery']['vrideliverydistance'] = $oi['deliverydist'];
				}
			}
			//delivery service
			if ($totdelivery > 0) {
				$isdue += $totdelivery;
			}
			//
			$usedcoupon = false;
			$origisdue = $isdue;
			if (strlen($order[0]['coupon']) > 0) {
				$usedcoupon = true;
				$expcoupon = explode(";", $order[0]['coupon']);
				$isdue = $isdue - $expcoupon[1];
			}
			
			if (!empty($order[0]['custmail'])) {
				$sendpdf = true;
				if (!$checkdbsendpdf) {
					$psendpdf = VikRequest::getString('sendpdf', '', 'request');
					if ($psendpdf != "1") {
						$sendpdf = false;
					}
				}

				$mainframe->enqueueMessage(JText::sprintf('VRORDERMAILRESENT', $order[0]['custmail']));
				$saystatus = $order[0]['status'] == 'confirmed' ? JText::_('VRIOMPLETED') : JText::_('VRSTANDBY');
				// print_r($saystatus);
				// print_r($order[0]['status']);exit();
				VikRentItems::updatebusy_pdf_lib($order[0]['custmail'], strip_tags($ftitle)." ".JText::_('VRRENTALORD'), $ftitle, $nowts, $order[0]['custdata'], $vricart, $order[0]['ritiro'], $order[0]['consegna'], $isdue, $viklink, $saystatus, $ritplace, $consegnaplace, $maillocfee, $order[0]['id'], $order[0]['coupon'], true, $totdelivery);
			} else {
				VikError::raiseWarning('', JText::_('VRORDERMAILRESENTNOREC'));
			}
		}
		return true;
        }
	function isitembookable() {
		//to be called via ajax
		$dbo = JFactory::getDBO();
		$res = array(
			'status' => 0,
			'err' => ''
		);
		$pitid = VikRequest::getInt('itid', 0, 'request');
		$pfdate = VikRequest::getString('fdate', '', 'request');
		$pfh = VikRequest::getInt('fh', 0, 'request');
		$pfm = VikRequest::getInt('fm', 0, 'request');
		$ptdate = VikRequest::getString('tdate', '', 'request');
		$pth = VikRequest::getInt('th', 0, 'request');
		$ptm = VikRequest::getInt('tm', 0, 'request');
		$item_info = array();
		$q = "SELECT * FROM `#__vikrentitems_items` WHERE `id`=".(int)$pitid.";";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$item_info = $dbo->loadAssoc();
		}
		$from_ts = VikRentItems::getDateTimestamp($pfdate, $pfh, $pfm);
		$to_ts = VikRentItems::getDateTimestamp($ptdate, $pth, $ptm);
		if (
			count($item_info) > 0 && 
			(!empty($pfdate) && !empty($ptdate) && !empty($from_ts) && !empty($to_ts)) && 
			VikRentItems::itemBookable($item_info['id'], $item_info['units'], $from_ts, $to_ts)) 
		{
			$res['status'] = 1;
		} else {
			if (!(count($item_info) > 0)) {
				$res['err'] = 'Item not found';
			} elseif (empty($pfdate) || empty($ptdate) || empty($from_ts) || empty($to_ts)) {
				$res['err'] = 'Invalid dates';
			} else {
				//not available
				$res['err'] = JText::sprintf('VRIBOOKADDITEMERR', $item_info['name'], $pfdate, $ptdate);
			}
		}

		echo json_encode($res);
		exit;
	}

}