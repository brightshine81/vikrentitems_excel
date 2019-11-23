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

class VikRentItemsViewManageseason extends JViewVikRentItems {
	
	function display($tpl = null) {
		// Set the toolbar
		$this->addToolBar();

		$cid = VikRequest::getVar('cid', array(0));
		if (!empty($cid[0])) {
			$id = $cid[0];
		}

		$row = array();
		$split = array();
		$splitprices = array();
		$dbo = JFactory::getDBO();
		if (!empty($cid[0])) {
			$q = "SELECT * FROM `#__vikrentitems_seasons` WHERE `id`=".(int)$id.";";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() != 1) {
				VikError::raiseWarning('', 'Not found.');
				$mainframe = JFactory::getApplication();
				$mainframe->redirect("index.php?option=com_vikrentitems&task=seasons");
				exit;
			}
			$row = $dbo->loadAssoc();
			$split = explode(",", $row['iditems']);
			$splitprices = explode(",", $row['idprices']);
		}

		$wsel = "";
		$q = "SELECT `id`,`name` FROM `#__vikrentitems_items` ORDER BY `#__vikrentitems_items`.`name` ASC;";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$wsel .= "<select name=\"iditems[]\" multiple=\"multiple\" size=\"5\">\n";
			$data = $dbo->loadAssocList();
			foreach ($data as $d) {
				$wsel .= "<option value=\"".$d['id']."\"".(in_array("-".$d['id']."-", $split) ? " selected=\"selected\"" : "").">".$d['name']."</option>\n";
			}
			$wsel .= "</select>\n";
		}
		$wpricesel = "";
		$q = "SELECT `id`,`name` FROM `#__vikrentitems_prices` ORDER BY `#__vikrentitems_prices`.`name` ASC;";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$wpricesel .= "<select name=\"idprices[]\" multiple=\"multiple\" size=\"5\">\n";
			$data = $dbo->loadAssocList();
			foreach ($data as $d) {
				$wpricesel .= "<option value=\"".$d['id']."\"".(in_array("-".$d['id']."-", $splitprices) ? " selected=\"selected\"" : "").">".$d['name']."</option>\n";
			}
			$wpricesel .= "</select>\n";
		}
		$wlocsel = "<input type=\"hidden\" name=\"idlocation\" value=\"0\"/>";
		$q = "SELECT `id`,`name` FROM `#__vikrentitems_places` ORDER BY `#__vikrentitems_places`.`name` ASC;";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$wlocsel = "<select name=\"idlocation\">\n<option value=\"0\">".JText::_('VRSEASONANY')."</option>";
			$data = $dbo->loadAssocList();
			foreach ($data as $d) {
				$wlocsel .= "<option value=\"".$d['id']."\"".(count($row) && $d['id'] == $row['locations'] ? " selected=\"selected\"" : "").">".$d['name']."</option>\n";
			}
			$wlocsel .= "</select>\n";
		}
		
		$this->row = &$row;
		$this->wsel = &$wsel;
		$this->wpricesel = &$wpricesel;
		$this->wlocsel = &$wlocsel;
		
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
			JToolBarHelper::title(JText::_('VRMAINSEASONTITLEEDIT'), 'vikrentitems');
			if (JFactory::getUser()->authorise('core.edit', 'com_vikrentitems')) {
				JToolBarHelper::save( 'updateseason', JText::_('VRSAVE'));
				JToolBarHelper::spacer();
			}
			JToolBarHelper::cancel( 'cancelseason', JText::_('VRANNULLA'));
			JToolBarHelper::spacer();
		} else {
			//new
			JToolBarHelper::title(JText::_('VRMAINSEASONTITLENEW'), 'vikrentitems');
			if (JFactory::getUser()->authorise('core.create', 'com_vikrentitems')) {
				JToolBarHelper::save( 'createseason', JText::_('VRSAVE'));
				JToolBarHelper::spacer();
			}
			JToolBarHelper::cancel( 'cancelseason', JText::_('VRANNULLA'));
			JToolBarHelper::spacer();
		}
	}

}
