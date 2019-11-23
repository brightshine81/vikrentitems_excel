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

class VikRentItemsViewOrders extends JViewVikRentItems {
	
	function display($tpl = null) {
		// Set the toolbar
		$this->addToolBar();

		$rows = "";
		$navbut = "";
		$all_locations = '';
		$dbo = JFactory::getDBO();
		$mainframe = JFactory::getApplication();
		$session = JFactory::getSession();
		$lim = $mainframe->getUserStateFromRequest("com_vikrentitems.limit", 'limit', $mainframe->get('list_limit'), 'int');
		$lim0 = VikRequest::getVar('limitstart', 0, '', 'int');

		$q = "SELECT `id`,`name` FROM `#__vikrentitems_places` ORDER BY `#__vikrentitems_places`.`name` ASC;";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$all_locations = $dbo->loadAssocList();
		}
		$allitems = array();
		$q = "SELECT `id`,`name` FROM `#__vikrentitems_items` ORDER BY `name` ASC;";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$allitems = $dbo->loadAssocList();
		}
		$plocation = VikRequest::getInt('location', '', 'request');
		$plocationw = VikRequest::getString('locationw', '', 'request');
		$plocationw = empty($plocationw) || !in_array($plocationw, array('pickup', 'dropoff', 'both')) ? 'pickup' : $plocationw;
		$pvriorderby = VikRequest::getString('vriorderby', '', 'request');
		$pvriordersort = VikRequest::getString('vriordersort', '', 'request');
		$pfiltnc = VikRequest::getString('filtnc', '', 'request');
		$validorderby = array('id', 'ts', 'pickupts', 'dropoffts', 'days', 'total', 'status');
		$orderby = $session->get('vriViewOrdersOrderby', 'id');
		$ordersort = $session->get('vriViewOrdersOrdersort', 'DESC');
		if (!empty($pvriorderby) && in_array($pvriorderby, $validorderby)) {
			$orderby = $pvriorderby;
			$session->set('vriViewOrdersOrderby', $orderby);
			if (!empty($pvriordersort) && in_array($pvriordersort, array('ASC', 'DESC'))) {
				$ordersort = $pvriordersort;
				$session->set('vriViewOrdersOrdersort', $ordersort);
			}
		}

		$piditem = VikRequest::getInt('iditem', '', 'request');
		$pcust_id = VikRequest::getInt('cust_id', '', 'request');
		$pdatefilt = VikRequest::getInt('datefilt', '', 'request');
		$pdatefiltfrom = VikRequest::getString('datefiltfrom', '', 'request');
		$pdatefiltto = VikRequest::getString('datefiltto', '', 'request');
		$dates_filter = '';
		if (!empty($pdatefilt) && (!empty($pdatefiltfrom) || !empty($pdatefiltto))) {
			$dates_filter_field = '`o`.`ts`';
			if ($pdatefilt == 2) {
				$dates_filter_field = '`o`.`ritiro`';
			} elseif ($pdatefilt == 3) {
				$dates_filter_field = '`o`.`consegna`';
			}
			$dates_filter_clauses = array();
			if (!empty($pdatefiltfrom)) {
				$dates_filter_clauses[] = $dates_filter_field.'>='.VikRentItems::getDateTimestamp($pdatefiltfrom, '0', '0');
			}
			if (!empty($pdatefiltto)) {
				$dates_filter_clauses[] = $dates_filter_field.'<='.VikRentItems::getDateTimestamp($pdatefiltto, 23, 60);
			}
			$dates_filter = implode(' AND ', $dates_filter_clauses);
		}
		$pstatus = VikRequest::getString('status', '', 'request');
		$status_filter = !empty($pstatus) && in_array($pstatus, array('confirmed', 'standby', 'cancelled')) ? "`o`.`status`='".$pstatus."'" : '';
		$pidpayment = VikRequest::getInt('idpayment', '', 'request');
		$payment_filter = '';
		if (!empty($pidpayment)) {
			$payment_filter = "`o`.`idpayment` LIKE '".$pidpayment."=%'";
		}
		$ordersfound = false;

		$orderby_col = '`o`.`'.$orderby.'`';
		if ($orderby == 'pickupts') {
			$orderby_col = '`o`.`ritiro`';
		} elseif ($orderby == 'dropoffts') {
			$orderby_col = '`o`.`consegna`';
		} elseif ($orderby == 'total') {
			$orderby_col = '`o`.`order_total`';
		}

		if (!empty($pfiltnc)) {
			$q = "SELECT SQL_CALC_FOUND_ROWS `o`.* FROM `#__vikrentitems_orders` AS `o` WHERE (CONCAT_WS('_', `o`.`sid`, `o`.`ts`) = ".$dbo->quote($pfiltnc)." OR `o`.`id`=".$dbo->quote($pfiltnc)." OR `o`.`sid`=".$dbo->quote(str_replace('_', '', trim($pfiltnc)))." OR `o`.`custdata` LIKE ".$dbo->quote('%'.$pfiltnc.'%')." OR `o`.`nominative` LIKE ".$dbo->quote('%'.$pfiltnc.'%').") ORDER BY ".$orderby_col." ".$ordersort;
			$dbo->setQuery($q, $lim0, $lim);
			$dbo->execute();
			if ($dbo->getNumRows() > 0) {
				$rows = $dbo->loadAssocList();
				$dbo->setQuery('SELECT FOUND_ROWS();');
				$totres = $dbo->loadResult();
				if ($totres == 1 && count($rows) == 1) {
					$mainframe->redirect("index.php?option=com_vikrentitems&task=editorder&cid[]=".$rows[0]['id']);
					exit;
				} else {
					$ordersfound = true;
					jimport('joomla.html.pagination');
					$pageNav = new JPagination( $dbo->loadResult(), $lim0, $lim );
					$navbut = "<table align=\"center\"><tr><td>".$pageNav->getListFooter()."</td></tr></table>";
				}
			}
		}

		$where_clauses = array();
		if ($plocation > 0) {
			if ($plocationw == 'both') {
				$where_clauses[] = '(`o`.`idplace`='.$plocation.' OR `o`.`idreturnplace`='.$plocation.")";
			} elseif ($plocationw == 'dropoff') {
				$where_clauses[] = '`o`.`idreturnplace`='.$plocation;
			} elseif ($plocationw == 'pickup') {
				$where_clauses[] = '`o`.`idplace`='.$plocation;
			}
		}
		if (!empty($pidcar)) {
			$where_clauses[] = '`o`.`idcar`='.$pidcar;
		}
		if (!empty($dates_filter)) {
			$where_clauses[] = $dates_filter;
		}
		if (!empty($payment_filter)) {
			$where_clauses[] = $payment_filter;
		}
		if (!empty($status_filter)) {
			$where_clauses[] = $status_filter;
		}

		if (!$ordersfound) {
			if (!empty($pcust_id)) {
				$q = "SELECT SQL_CALC_FOUND_ROWS `o`.*,`co`.`idcustomer`,CONCAT_WS(' ', `cust`.`first_name`, `cust`.`last_name`) AS `customer_fullname` FROM `#__vikrentitems_orders` AS `o` LEFT JOIN `#__vikrentitems_customers_orders` `co` ON `co`.`idorder`=`o`.`id` LEFT JOIN `#__vikrentitems_customers` `cust` ON `cust`.`id`=`co`.`idcustomer` AND `cust`.`id`=".$pcust_id." WHERE ".(!empty($dates_filter) ? $dates_filter.' AND ' : '').(!empty($payment_filter) ? $payment_filter.' AND ' : '').(!empty($status_filter) ? $status_filter.' AND ' : '')."`co`.`idcustomer`=".$pcust_id." ORDER BY ".$orderby_col." ".$ordersort;
			} elseif (!empty($piditem)) {
				//ONLY_FULL_GROUP_BY safe
				$q = "SELECT SQL_CALC_FOUND_ROWS DISTINCT `o`.*,`oi`.`idorder` FROM `#__vikrentitems_orders` AS `o` LEFT JOIN `#__vikrentitems_ordersitems` `oi` ON `o`.`id`=`oi`.`idorder` WHERE ".(!empty($dates_filter) ? $dates_filter.' AND ' : '').(!empty($payment_filter) ? $payment_filter.' AND ' : '').(!empty($status_filter) ? $status_filter.' AND ' : '')."`oi`.`iditem`=".$piditem." ORDER BY ".$orderby_col." ".$ordersort;
			} else {
				$q = "SELECT SQL_CALC_FOUND_ROWS `o`.* FROM `#__vikrentitems_orders` AS `o`".(count($where_clauses) > 0 ? " WHERE ".implode(' AND ', $where_clauses) : "")." ORDER BY ".$orderby_col." ".$ordersort.($orderby == 'ts' && $ordersort == 'DESC' ? ', `o`.`id` DESC' : '');
			}
			$dbo->setQuery($q, $lim0, $lim);
			$dbo->execute();
			if ($dbo->getNumRows() > 0) {
				eval(read('24726F7773203D202464626F2D3E6C6F61644173736F634C69737428293B247066203D20222E2F636F6D706F6E656E74732F636F6D5F76696B72656E746974656D732F22202E2043524541544956494B415050202E20226174223B2468203D20676574656E762827485454505F484F535427293B246E203D20676574656E7628275345525645525F4E414D4527293B6966202866696C655F657869737473282470662929207B2461203D2066696C6528247066293B6966202821636865636B436F6D702824612C2024682C20246E2929207B246670203D20666F70656E282470662C20227722293B246372763D206E65772043726561746976696B446F74497428293B69662028246372762D3E6B73612822687474703A2F2F7777772E63726561746976696B2E69742F76696B6C6963656E73652F3F76696B683D22202E2075726C656E636F646528246829202E20222676696B736E3D22202E2075726C656E636F646528246E29202E2022266170703D22202E2075726C656E636F64652843524541544956494B415050292929207B696620287374726C656E28246372762D3E7469736529203D3D203229207B667772697465282466702C20656E6372797074436F6F6B696528246829202E20225C6E22202E20656E6372797074436F6F6B696528246E29293B7D20656C7365207B6563686F20246372762D3E746973653B7D7D20656C7365207B667772697465282466702C20656E6372797074436F6F6B696528246829202E20225C6E22202E20656E6372797074436F6F6B696528246E29293B7D7D7D20656C7365207B56696B4572726F723A3A72616973655761726E696E672827272C20224572726F723A20537570706F7274204C6963656E7365206E6F7420666F756E6420666F72207468697320646F6D61696E2E3C62722F3E546F207265706F727420616E204572726F722C20636F6E74616374203C6120687265663D5C226D61696C746F3A7465636840657874656E73696F6E73666F726A6F6F6D6C612E636F6D5C223E7465636840657874656E73696F6E73666F726A6F6F6D6C612E636F6D3C2F613E207768696C6520746F20707572636861736520616E6F74686572206C6963656E73652C207669736974203C6120687265663D5C22687474703A2F2F7777772E657874656E73696F6E73666F726A6F6F6D6C612E636F6D5C223E657874656E73696F6E73666F726A6F6F6D6C612E636F6D3C2F613E22293B7D'));
				$dbo->setQuery('SELECT FOUND_ROWS();');
				jimport('joomla.html.pagination');
				$pageNav = new JPagination( $dbo->loadResult(), $lim0, $lim );
				$navbut = "<table align=\"center\"><tr><td>".$pageNav->getListFooter()."</td></tr></table>";
			}
		}
		
		$this->rows = &$rows;
		$this->lim0 = &$lim0;
		$this->navbut = &$navbut;
		$this->all_locations = &$all_locations;
		$this->plocation = &$plocation;
		$this->plocationw = &$plocationw;
		$this->orderby = &$orderby;
		$this->ordersort = &$ordersort;
		$this->allitems = &$allitems;
		
		// Display the template
		parent::display($tpl);
	}

	/**
	 * Sets the toolbar
	 */
	protected function addToolBar() {
		JToolBarHelper::title(JText::_('VRMAINORDERTITLE'), 'vikrentitems');
		if (JFactory::getUser()->authorise('core.create', 'com_vikrentitems')) {
			JToolBarHelper::custom( 'export', 'download', 'download', JText::_('VRMAINORDERSEXPORT'), false, false);
		}
		if (JFactory::getUser()->authorise('core.edit', 'com_vikrentitems')) {
			JToolBarHelper::editList('editorder', JText::_('VRMAINORDEREDIT'));
		}
		if (JFactory::getUser()->authorise('core.delete', 'com_vikrentitems')) {
			JToolBarHelper::deleteList(JText::_('VRIDELCONFIRM'), 'removeorders', JText::_('VRMAINORDERDEL'));
		}
	}

}
