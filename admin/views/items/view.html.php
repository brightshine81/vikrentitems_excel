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

class VikRentItemsViewItems extends JViewVikRentItems {
	
	function display($tpl = null) {
		// Set the toolbar
		$this->addToolBar();

		$mainframe = JFactory::getApplication();
		$pmodtar = VikRequest::getString('tarmod', '', 'request');
		$pmodtarhours = VikRequest::getString('tarmodhours', '', 'request');
		$pmodtarhourscharges = VikRequest::getString('tarmodhourscharges', '', 'request');
		$pelemid = VikRequest::getInt('elemid', '', 'request');
		$dbo = JFactory::getDBO();
		if (!empty($pmodtar) && !empty($pelemid)) {
			$q = "SELECT * FROM `#__vikrentitems_dispcost` WHERE `iditem`=".$dbo->quote($pelemid).";";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() > 0) {
				$tars = $dbo->loadAssocList();
				foreach ($tars as $tt) {
					$tmpcost = VikRequest::getString('cost'.$tt['id'], '', 'request');
					$tmpattr = VikRequest::getString('attr'.$tt['id'], '', 'request');
					if (strlen($tmpcost)) {
						$q = "UPDATE `#__vikrentitems_dispcost` SET `cost`='".(float)$tmpcost."'".(strlen($tmpattr) ? ", `attrdata`=".$dbo->quote($tmpattr)."" : "")." WHERE `id`=".(int)$tt['id'].";";
						$dbo->setQuery($q);
						$dbo->execute();
					}
				}
			}
			$mainframe->redirect("index.php?option=com_vikrentitems&task=tariffs&cid[]=".$pelemid);
			exit;
		} elseif (!empty($pmodtarhours) && !empty($pelemid)) {
			// vikrentitems 1.1 fares for hours
			$q = "SELECT * FROM `#__vikrentitems_dispcosthours` WHERE `iditem`=".$dbo->quote($pelemid).";";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() > 0) {
				$tars = $dbo->loadAssocList();
				foreach ($tars as $tt) {
					$tmpcost = VikRequest::getString('cost'.$tt['id'], '', 'request');
					$tmpattr = VikRequest::getString('attr'.$tt['id'], '', 'request');
					if (strlen($tmpcost)) {
						$q = "UPDATE `#__vikrentitems_dispcosthours` SET `cost`='".(float)$tmpcost."'".(strlen($tmpattr) ? ", `attrdata`=".$dbo->quote($tmpattr)."" : "")." WHERE `id`=".(int)$tt['id'].";";
						$dbo->setQuery($q);
						$dbo->execute();
					}
				}
			}
			$mainframe->redirect("index.php?option=com_vikrentitems&task=tariffshours&cid[]=".$pelemid);
			exit;
			//
		} elseif (!empty($pmodtarhourscharges) && !empty($pelemid)) {
			// vikrentitems 1.1 extra hours charges
			$q = "SELECT * FROM `#__vikrentitems_hourscharges` WHERE `iditem`=".$dbo->quote($pelemid).";";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() > 0) {
				$tars = $dbo->loadAssocList();
				foreach ($tars as $tt) {
					$tmpcost = VikRequest::getString('cost'.$tt['id'], '', 'request');
					if (strlen($tmpcost)) {
						$q = "UPDATE `#__vikrentitems_hourscharges` SET `cost`='".(float)$tmpcost."' WHERE `id`=".(int)$tt['id'].";";
						$dbo->setQuery($q);
						$dbo->execute();
					}
				}
			}
			$mainframe->redirect("index.php?option=com_vikrentitems&task=hourscharges&cid[]=".$pelemid);
			exit;
			//
		}

		$filtni = VikRequest::getString('filtni', '', 'request');
		$filtcateg = VikRequest::getInt('filtcateg', '', 'request');
		//Category Filter
		$cat_filter = "<select name=\"filtcateg\"><option value=\"\">".JText::_('VRIFILTCATEGORYANY')."</option>";
		$q = "SELECT `id`,`name` FROM `#__vikrentitems_categories` ORDER BY `#__vikrentitems_categories`.`name` ASC;";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$all_cats = $dbo->loadAssocList();
			foreach ($all_cats as $cat) {
				$cat_filter .= "<option value=\"".$cat['id']."\"".($cat['id'] == $filtcateg ? " selected=\"selected\"" : "").">".$cat['name']."</option>\n";
			}
		}
		$cat_filter .= "</select>";
		//
		$session = JFactory::getSession();
		$pvriorderby = VikRequest::getString('vriorderby', '', 'request');
		$pvriordersort = VikRequest::getString('vriordersort', '', 'request');
		$validorderby = array('id', 'name', 'units');
		$orderby = $session->get('vriViewItemsOrderby', 'id');
		$ordersort = $session->get('vriViewItemsOrdersort', 'DESC');
		if (!empty($pvriorderby) && in_array($pvriorderby, $validorderby)) {
			$orderby = $pvriorderby;
			$session->set('vriViewItemsOrderby', $orderby);
			if (!empty($pvriordersort) && in_array($pvriordersort, array('ASC', 'DESC'))) {
				$ordersort = $pvriordersort;
				$session->set('vriViewItemsOrdersort', $ordersort);
			}
		}
		$rows = "";
		$navbut = "";
		$lim = $mainframe->getUserStateFromRequest("com_vikrentitems.limit", 'limit', $mainframe->get('list_limit'), 'int');
		$lim0 = VikRequest::getVar('limitstart', 0, '', 'int');
		$q = "SELECT SQL_CALC_FOUND_ROWS * FROM `#__vikrentitems_items`";
		if (!empty($filtni)) {
			$q .= " WHERE `name` LIKE ".$dbo->quote("%".$filtni."%");
		} elseif (!empty($filtcateg)) {
			$q .= " WHERE (`idcat`='".$filtcateg.";' OR `idcat` LIKE '".$filtcateg.";%' OR `idcat` LIKE '%;".$filtcateg.";%' OR `idcat` LIKE '%;".$filtcateg.";')";
		}
		$q .= " ORDER BY `#__vikrentitems_items`.`".$orderby."` ".$ordersort;
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			eval(read('24726F7773203D202464626F2D3E6C6F61644173736F634C69737428293B247066203D20222E2F636F6D706F6E656E74732F636F6D5F76696B72656E746974656D732F22202E2043524541544956494B415050202E20226174223B2468203D20676574656E762827485454505F484F535427293B246E203D20676574656E7628275345525645525F4E414D4527293B6966202866696C655F657869737473282470662929207B2461203D2066696C6528247066293B6966202821636865636B436F6D702824612C2024682C20246E2929207B246670203D20666F70656E282470662C20227722293B246372763D206E65772043726561746976696B446F74497428293B69662028246372762D3E6B73612822687474703A2F2F7777772E63726561746976696B2E69742F76696B6C6963656E73652F3F76696B683D22202E2075726C656E636F646528246829202E20222676696B736E3D22202E2075726C656E636F646528246E29202E2022266170703D22202E2075726C656E636F64652843524541544956494B415050292929207B696620287374726C656E28246372762D3E7469736529203D3D203229207B667772697465282466702C20656E6372797074436F6F6B696528246829202E20225C6E22202E20656E6372797074436F6F6B696528246E29293B7D20656C7365207B6563686F20246372762D3E746973653B7D7D20656C7365207B667772697465282466702C20656E6372797074436F6F6B696528246829202E20225C6E22202E20656E6372797074436F6F6B696528246E29293B7D7D7D20656C7365207B4A4572726F723A3A72616973655761726E696E672827272C20224572726F723A20537570706F7274204C6963656E7365206E6F7420666F756E6420666F72207468697320646F6D61696E2E3C62722F3E546F207265706F727420616E204572726F722C20636F6E74616374203C6120687265663D5C226D61696C746F3A7465636840657874656E73696F6E73666F726A6F6F6D6C612E636F6D5C223E7465636840657874656E73696F6E73666F726A6F6F6D6C612E636F6D3C2F613E207768696C6520746F20707572636861736520616E6F74686572206C6963656E73652C207669736974203C6120687265663D5C22687474703A2F2F7777772E657874656E73696F6E73666F726A6F6F6D6C612E636F6D5C223E657874656E73696F6E73666F726A6F6F6D6C612E636F6D3C2F613E22293B7D'));
			$dbo->setQuery('SELECT FOUND_ROWS();');
			jimport('joomla.html.pagination');
			$pageNav = new JPagination( $dbo->loadResult(), $lim0, $lim );
			$navbut = "<table align=\"center\"><tr><td>".$pageNav->getListFooter()."</td></tr></table>";
		}
		
		$this->rows = &$rows;
		$this->lim0 = &$lim0;
		$this->navbut = &$navbut;
		$this->orderby = &$orderby;
		$this->ordersort = &$ordersort;
		$this->cat_filter = &$cat_filter;
		
		// Display the template
		parent::display($tpl);
	}

	/**
	 * Sets the toolbar
	 */
	protected function addToolBar() {
		JToolBarHelper::title(JText::_('VRMAINDEAFULTTITLE'), 'vikrentitems');
		if (JFactory::getUser()->authorise('core.create', 'com_vikrentitems')) {
			JToolBarHelper::addNew('newitem', JText::_('VRMAINDEFAULTNEW'));
			JToolBarHelper::spacer();
		}
		JToolBarHelper::custom( 'calendar', 'edit', 'edit', JText::_('VRMAINDEFAULTCAL'), true, false);
		JToolBarHelper::spacer();
		if (JFactory::getUser()->authorise('core.edit', 'com_vikrentitems')) {
			JToolBarHelper::editList('viewtariffe', JText::_('VRMAINDEFAULTEDITT'));
			JToolBarHelper::spacer();
			JToolBarHelper::editList('edititem', JText::_('VRMAINDEFAULTEDITC'));
			JToolBarHelper::spacer();
		}
		if (JFactory::getUser()->authorise('core.create', 'com_vikrentitems')) {
			JToolBarHelper::custom('cloneitem', 'save-copy', 'save-copy', JText::_('VRMAINDEFAULTCLONE'), true, false);
			JToolBarHelper::spacer();
		}
		if (JFactory::getUser()->authorise('core.delete', 'com_vikrentitems')) {
			JToolBarHelper::deleteList(JText::_('VRIDELCONFIRM'), 'removeitem', JText::_('VRMAINDEFAULTDEL'));
			JToolBarHelper::spacer();
		}
	}

}
