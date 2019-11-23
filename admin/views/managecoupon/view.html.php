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

class VikRentItemsViewManagecoupon extends JViewVikRentItems {
	
	function display($tpl = null) {
		// Set the toolbar
		$this->addToolBar();

		$cid = VikRequest::getVar('cid', array(0));
		if (!empty($cid[0])) {
			$id = $cid[0];
		}

		$row = array();
		$dbo = JFactory::getDBO();
		if (!empty($cid[0])) {
			$q = "SELECT * FROM `#__vikrentitems_coupons` WHERE `id`=".(int)$id.";";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() != 1) {
				VikError::raiseWarning('', 'Not found.');
				$mainframe = JFactory::getApplication();
				$mainframe->redirect("index.php?option=com_vikrentitems&task=coupons");
				exit;
			}
			$row = $dbo->loadAssoc();
		}
		$wselitems = "";
		$q = "SELECT `id`,`name` FROM `#__vikrentitems_items` ORDER BY `#__vikrentitems_items`.`name` ASC;";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$items = $dbo->loadAssocList();
			$filteritemr = array();
			if (count($row) && strlen($row['iditems']) > 0) {
				$cparts = explode(";", $row['iditems']);
				foreach ($cparts as $fc) {
					if (!empty($fc)) {
						$filteritemr[] = $fc;
					}
				}
			}
			$wselitems = "<select name=\"iditems[]\" multiple=\"multiple\" size=\"5\">\n";
			foreach ($items as $c) {
				$wselitems .= "<option value=\"".$c['id']."\"".(in_array($c['id'], $filteritemr) ? " selected=\"selected\"" : "").">".$c['name']."</option>\n";
			}
			$wselitems .= "</select>\n";
		}
		
		$this->row = &$row;
		$this->wselitems = &$wselitems;
		
		// Display the template
		parent::display($tpl);
	}

	/**
	 * Sets the toolbar
	 */
	protected function addToolBar() {
		$cid = VikRequest::getVar('cid', array(0));
		
		if (!empty($cid[0])) {
			//edit
			JToolBarHelper::title(JText::_('VRMAINCOUPONTITLE'), 'vikrentitems');
			if (JFactory::getUser()->authorise('core.edit', 'com_vikrentitems')) {
				JToolBarHelper::save( 'updatecoupon', JText::_('VRSAVE'));
				JToolBarHelper::spacer();
			}
			JToolBarHelper::cancel( 'cancelcoupon', JText::_('VRANNULLA'));
			JToolBarHelper::spacer();
		} else {
			//new
			JToolBarHelper::title(JText::_('VRMAINCOUPONTITLE'), 'vikrentitems');
			if (JFactory::getUser()->authorise('core.create', 'com_vikrentitems')) {
				JToolBarHelper::save( 'createcoupon', JText::_('VRSAVE'));
				JToolBarHelper::spacer();
			}
			JToolBarHelper::cancel( 'cancelcoupon', JText::_('VRANNULLA'));
			JToolBarHelper::spacer();
		}
	}

}
