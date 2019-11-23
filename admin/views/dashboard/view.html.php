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

// import Joomla view library
jimport('joomla.application.component.view');

class VikRentItemsViewDashboard extends JViewVikRentItems {
	
	function display($tpl = null) {
		// Set the toolbar
		$this->addToolBar();
		
		//VRI 1.5 - Joomla Updates (>= 3.2.0) - Extra Fields Handler
		$jvobj = new JVersion;
		$jv = $jvobj->getShortVersion();
		if (version_compare($jv, '3.2.0', '>=')) {
			//With this method we populate the extra fields for this extension. We need to store the domain name encoded in base64 for the download of commercial updates.
			//Without the record stored this way, our Update Servers will reject the download request.
			require_once(JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_vikrentitems'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'urihandler.php');
			$update = new UriUpdateHandler('com_vikrentitems');
			$domain = JFactory::getApplication()->input->server->getString('HTTP_HOST');
			$update->addExtraField('domain', base64_encode($domain));
			$ord_num = JFactory::getApplication()->input->getString('order_number');
			if (!empty($ord_num)) {
				$update->addExtraField('order_number', $ord_num);
			}
			$update->checkSchema(E4J_SOFTWARE_VERSION);
			$update->register();
			//
		}
		//
		$pidplace = VikRequest::getInt('idplace', '', 'request');
		$dbo = JFactory::getDBO();
		$q = "SELECT COUNT(*) FROM `#__vikrentitems_prices`;";
		$dbo->setQuery($q);
		$dbo->execute();
		$totprices = $dbo->loadResult();
		$q = "SELECT `id`,`name` FROM `#__vikrentitems_places` ORDER BY `#__vikrentitems_places`.`name` ASC;";
		$dbo->setQuery($q);
		$dbo->execute();
		$totlocations = $dbo->getNumRows();
		if ($totlocations > 0) {
			$allplaces = $dbo->loadAssocList();
		} else {
			$allplaces = "";
		}
		$q = "SELECT COUNT(*) FROM `#__vikrentitems_categories`;";
		$dbo->setQuery($q);
		$dbo->execute();
		$totcategories = $dbo->loadResult();
		$q = "SELECT COUNT(*) FROM `#__vikrentitems_items`;";
		$dbo->setQuery($q);
		$dbo->execute();
		$totitems = $dbo->loadResult();
		$q = "SELECT COUNT(*) FROM `#__vikrentitems_dispcost`;";
		$dbo->setQuery($q);
		$dbo->execute();
		$totdailyfares = $dbo->loadResult();
		$arrayfirst = array('totprices' => $totprices, 'totlocations' => $totlocations, 'totcategories' => $totcategories, 'totitems' => $totitems, 'totdailyfares' => $totdailyfares);
		$nextrentals = "";
		$totnextrentconf = 0;
		$totnextrentpend = 0;
		$today_start_ts = mktime(0, 0, 0, date("n"), date("j"), date("Y"));
		$today_end_ts = mktime(23, 59, 59, date("n"), date("j"), date("Y"));
		$pickup_today = array();
		$dropoff_today = array();
		$items_locked = array();
		if ($totprices > 0 && $totitems > 0) {
			$q = "SELECT `o`.`id`,`o`.`custdata`,`o`.`status`,`o`.`ritiro`,`o`.`consegna`,`o`.`idplace`,`o`.`idreturnplace`,`o`.`country`,`o`.`nominative`,
					(SELECT SUM(`oi`.`itemquant`) FROM `#__vikrentitems_ordersitems` AS `oi` WHERE `oi`.`idorder`=`o`.`id`) AS `totitems`, 
					(SELECT GROUP_CONCAT(`i`.`name` SEPARATOR ',') FROM `#__vikrentitems_items` AS `i` LEFT JOIN `#__vikrentitems_ordersitems` `oi` ON `oi`.`iditem`=`i`.`id` WHERE `oi`.`idorder`=`o`.`id`) AS `item_names` 
				FROM `#__vikrentitems_orders` AS `o` WHERE `o`.`ritiro`>".$today_end_ts." ".($pidplace > 0 ? "AND `o`.`idplace`='".$pidplace."' " : "")."ORDER BY `o`.`ritiro` ASC LIMIT 10;";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() > 0) {
				$nextrentals = $dbo->loadAssocList();
			}
			$q = "SELECT `o`.`id`,`o`.`custdata`,`o`.`status`,`o`.`ritiro`,`o`.`consegna`,`o`.`idplace`,`o`.`idreturnplace`,`o`.`country`,`o`.`nominative`,
					(SELECT SUM(`oi`.`itemquant`) FROM `#__vikrentitems_ordersitems` AS `oi` WHERE `oi`.`idorder`=`o`.`id`) AS `totitems`, 
					(SELECT GROUP_CONCAT(`i`.`name` SEPARATOR ',') FROM `#__vikrentitems_items` AS `i` LEFT JOIN `#__vikrentitems_ordersitems` `oi` ON `oi`.`iditem`=`i`.`id` WHERE `oi`.`idorder`=`o`.`id`) AS `item_names` 
				FROM `#__vikrentitems_orders` AS `o` WHERE `o`.`ritiro`>=".$today_start_ts." AND `o`.`ritiro`<=".$today_end_ts." ORDER BY `o`.`ritiro` ASC;";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() > 0) {
				$pickup_today = $dbo->loadAssocList();
			}
			$q = "SELECT `o`.`id`,`o`.`custdata`,`o`.`status`,`o`.`ritiro`,`o`.`consegna`,`o`.`idplace`,`o`.`idreturnplace`,`o`.`country`,`o`.`nominative`,
					(SELECT SUM(`oi`.`itemquant`) FROM `#__vikrentitems_ordersitems` AS `oi` WHERE `oi`.`idorder`=`o`.`id`) AS `totitems`, 
					(SELECT GROUP_CONCAT(`i`.`name` SEPARATOR ',') FROM `#__vikrentitems_items` AS `i` LEFT JOIN `#__vikrentitems_ordersitems` `oi` ON `oi`.`iditem`=`i`.`id` WHERE `oi`.`idorder`=`o`.`id`) AS `item_names` 
				FROM `#__vikrentitems_orders` AS `o` WHERE `o`.`consegna`>=".$today_start_ts." AND `o`.`consegna`<=".$today_end_ts." ORDER BY `o`.`consegna` ASC;";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() > 0) {
				$dropoff_today = $dbo->loadAssocList();
			}
			$q = "DELETE FROM `#__vikrentitems_tmplock` WHERE `until`<" . time() . ";";
			$dbo->setQuery($q);
			$dbo->execute();
			$q = "SELECT `lock`.*,`i`.`name` AS `item_name`,`o`.`custdata`,`o`.`country`,`o`.`nominative` FROM `#__vikrentitems_tmplock` AS `lock` LEFT JOIN `#__vikrentitems_orders` `o` ON `lock`.`idorder`=`o`.`id` LEFT JOIN `#__vikrentitems_items` `i` ON `lock`.`iditem`=`i`.`id` WHERE `lock`.`until`>".time()." ORDER BY `lock`.`id` DESC;";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() > 0) {
				$items_locked = $dbo->loadAssocList();
			}
			$q = "SELECT COUNT(*) FROM `#__vikrentitems_orders` WHERE `ritiro`>".time()." AND `status`='confirmed';";
			$dbo->setQuery($q);
			$dbo->execute();
			$totnextrentconf = $dbo->loadResult();
			$q = "SELECT COUNT(*) FROM `#__vikrentitems_orders` WHERE `ritiro`>".time()." AND `status`='standby';";
			$dbo->setQuery($q);
			$dbo->execute();
			$totnextrentpend = $dbo->loadResult();
		}

		$this->pidplace = &$pidplace;
		$this->arrayfirst = &$arrayfirst;
		$this->allplaces = &$allplaces;
		$this->nextrentals = &$nextrentals;
		$this->pickup_today = &$pickup_today;
		$this->dropoff_today = &$dropoff_today;
		$this->items_locked = &$items_locked;
		$this->totnextrentconf = &$totnextrentconf;
		$this->totnextrentpend = &$totnextrentpend;
		
		// Display the template
		parent::display($tpl);
	}

	/**
	 * Sets the toolbar
	 */
	protected function addToolBar() {
		JToolBarHelper::title(JText::_('VRMAINDASHBOARDTITLE'), 'vikrentitems');
		if (JFactory::getUser()->authorise('core.admin', 'com_vikrentitems')) {
			JToolBarHelper::preferences('com_vikrentitems');
		}
	}

}
