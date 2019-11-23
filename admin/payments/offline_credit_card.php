<?php
/**
 * @package     VikRentItems
 * @subpackage  com_vikrentitems
 * @author      Alessio Gaggii - e4j - Extensionsforjoomla.com
 * @copyright   Copyright (C) 2018 e4j - Extensionsforjoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 * @link        https://e4j.com
 */

defined('_JEXEC') OR die('Restricted Area');

class vikRentItemsPayment {
	
	private $order_info;
	private $params;
	private $validation;
	private $sslvalidation;
	private $sslcapturepage;
	
	/**
	 * Do not edit this function unless you know what you are doing
	 * it is just meant to define the parameters of the payment method
	 */
	static function getAdminParameters () {
		return array(
				'newstatus' => array('type' => 'select', 'label' => 'Set Order Status to://in case you want to manually verify that the credit card information is valid, set this to Pending', 'options' => array('CONFIRMED', 'PENDING')),
				'sslvalidation' => array('type' => 'select', 'label' => 'Force HTTPS Validation://If enabled the validation page will be in HTTPS', 'options' => array('OFF', 'ON')),
				'sslcapturepage' => array('type' => 'select', 'label' => 'Force HTTPS Capture://If enabled the page where the credit card information are captured will be in HTTPS', 'options' => array('OFF', 'ON'))
		);
	}
	
	public function __construct ($order, $params = array()) {
		$this->order_info = $order;
		$this->params = $params;
		$this->validation = 0;
		$this->sslvalidation = count($params) && $params['sslvalidation'] == 'ON' ? 1 : 0;
		$this->sslcapturepage = count($params) && $params['sslcapturepage'] == 'ON' ? 1 : 0;
	}
	
	public function showPayment () {
		$depositmess = "";
		$actionurl = $this->order_info['notify_url'];
		//enable ssl in the payment validation page
		if ($this->sslvalidation == 1) {
			$actionurl = str_replace('http:', 'https:', $actionurl);
		}
		//enable ssl in the credit card info capture page
		if ($this->sslcapturepage == 1) {
			if ($_SERVER['HTTPS'] != "on") {
				$url = $this->order_info['return_url'];
				$mainframe = JFactory::getApplication();
				$mainframe->redirect(str_replace('http:', 'https:', $url));
				exit;
			}
		}
		//
		$form = "<br clear=\"all\"/><p>".JText::_('VRICCOFFLINECCMESSAGE')."</p><form action=\"".$actionurl."\" method=\"post\" name=\"offlineccpaymform\">\n";
		$form .= "<div class=\"vri-offline-cc-container\">\n";
		$form .= "<div class=\"vri-offline-cc-entry\"><label for=\"credit_card_number\">".JText::_('VRICCCREDITCARDNUMBER')."</label><input type=\"text\" id=\"credit_card_number\" name=\"credit_card_number\" size=\"20\" value=\"\"/></div>\n";
		$form .= '<div class="vri-offline-cc-entry"><label for="expire_month">'.JText::_('VRICCVALIDTHROUGH').'</label><select name="expire_month" id="expire_month">
				<option value="01">January</option>
				<option value="02">February</option>
				<option value="03">March</option>
				<option value="04">April</option>
				<option value="05">May</option>
				<option value="06">June</option>
				<option value="07">July</option>
				<option value="08">August</option>
				<option value="09">September</option>
				<option value="10">October</option>
				<option value="11">November</option>
				<option value="12">December</option>
				</select> ';
		$maxyear = date("Y");
		$form .= '<select name="expire_year">';
		for ($i = $maxyear; $i <= ($maxyear + 10); $i++) {
			$form .= '<option value="'.substr($i, -2, 2).'">'.$i.'</option>';
		}
		$form .= '</select></div>'."\n";
		$form .= "<div class=\"vri-offline-cc-entry\"><label for=\"credit_card_cvv\">".JText::_('VRICCCVV')."</label><input type=\"text\" id=\"credit_card_cvv\" name=\"credit_card_cvv\" size=\"5\" value=\"\"/></div>\n";
		$form .= "<div class=\"vri-offline-cc-entry\"><label for=\"business_first_name\">".JText::_('VRICCFIRSTNAME')."</label><input type=\"text\" id=\"business_first_name\" name=\"business_first_name\" size=\"20\" value=\"\"/></div>\n";
		$form .= "<div class=\"vri-offline-cc-entry\"><label for=\"business_last_name\">".JText::_('VRICCLASTNAME')."</label><input type=\"text\" id=\"business_last_name\" name=\"business_last_name\" size=\"20\" value=\"\"/></div>\n";
		$form .= "<div class=\"vri-offline-cc-entry vri-offline-cc-entry-sbmt\"><input type=\"submit\" id=\"offlineccsubmit\" name=\"offlineccsubmit\" class=\"button\" value=\"".JText::_('VRIOFFLINECCSEND')."\" onclick=\"javascript: event.preventDefault(); this.disabled = true; this.value = '".addslashes(JText::_('VRIOFFLINECCSENT'))."'; document.offlineccpaymform.submit(); return true;\"/></div>\n";
		$form .= "</div>\n";
		$form .= "<input type=\"hidden\" name=\"total\" value=\"".number_format($this->order_info['total_to_pay'], 2)."\"/>\n";
		$form .= "<input type=\"hidden\" name=\"description\" value=\"".$this->order_info['transaction_name']."\"/>\n";
		$form .= "</form>\n";
		
		
		if ($this->order_info['leave_deposit']) {
			$depositmess = "<br/><p><strong>".JText::_('VRLEAVEDEPOSIT')." ".$this->order_info['currency_symb']." ".VikRentItems::numberFormat($this->order_info['total_to_pay'])."</strong></p><br/>";
		}
		//output
		echo $depositmess;
		echo $this->order_info['payment_info']['note'];
		echo $form;
		
		return true;
	}
	
	public function validatePayment () {
		$array_result = array();
		$array_result['verified'] = 0;
		
		//post data
		$creditcard = VikRequest::getString('credit_card_number', '', 'request');
		$expire_month = VikRequest::getString('expire_month', '', 'request');
		$expire_year = VikRequest::getString('expire_year', '', 'request');
		$cvv = VikRequest::getString('credit_card_cvv', '', 'request');
		$total = VikRequest::getString('total', '', 'request');
		$business_first_name = VikRequest::getString('business_first_name', '', 'request');
		$business_last_name = VikRequest::getString('business_last_name', '', 'request');
		//end post data
		
		//post data validation
		$error_redirect_url = 'index.php?option=com_vikrentitems&task=vieworder&sid='.$this->order_info['sid'].'&ts='.$this->order_info['ts'];
		$valid_data = true;
		$current_month = date("m");
		$current_year = date("y");
		if ((int)$expire_year < (int)$current_year) {
			$valid_data = false;
		} else { 
			if ((int)$expire_year == (int)$current_year) {
				if ((int)$expire_month < (int)$current_month) {
					$valid_data = false;
				}
			}
		}
		if (empty($creditcard) || empty($cvv) || empty($business_first_name) || empty($business_last_name)) {
			$valid_data = false;
		}
		if (!$valid_data) {
			VikError::raiseWarning('', JText::_('VRIOFFCCINVCC'));
			$mainframe = JFactory::getApplication();
			$mainframe->redirect($error_redirect_url);
			exit;
		}
		//end post data validation
		
		//Credit Card Information Received
		
		$this->validation = 1;
		$array_result['skip_email'] = 1;
		if (empty($this->params['newstatus']) || $this->params['newstatus'] == 'CONFIRMED') {
			$array_result['verified'] = 1;
			$array_result['skip_email'] = 0;
		}
		
		//Send Credit Card Info via eMail to the Administrator
		$admail = VikRentItems::getAdminMail();
		$currencyname = VikRentItems::getCurrencyName();

		$replacement = '*';
		for ($i = 1; $i <= strlen($creditcard); $i++) {
			$replacement .= '*';
		}
		
		$log = JText::_('VRICCCREDITCARDNUMBER').": ".$creditcard."\n";
		$log .= JText::_('VRICCVALIDTHROUGH')." (mm/yy): ".$expire_month."/".$expire_year."\n";
		$log .= JText::_('VRICCCVV').": *** (".JText::_('VRCSENTVIAMAIL').")"."\n";
		$log .= JText::_('VRICCFIRSTNAME').": ".$business_first_name."\n";
		$log .= JText::_('VRICCLASTNAME').": ".$business_last_name."\n";
		$array_result['log'] = $log;

		$mess = "Order ID: ".$this->order_info['id']."\n\n";
		$mess .= JText::_('VRICCCREDITCARDNUMBER').": ".substr_replace($creditcard, $replacement, 1, -1)."\n";
		$mess .= JText::_('VRICCVALIDTHROUGH')." (mm/yy): ".$expire_month."/".$expire_year."\n";
		$mess .= JText::_('VRICCCVV').": ".$cvv."\n";
		$mess .= JText::_('VRICCFIRSTNAME').": ".$business_first_name."\n";
		$mess .= JText::_('VRICCLASTNAME').": ".$business_last_name."\n\n";
		$mess .= JText::_('VRIOFFCCTOTALTOPAY').": ".$currencyname." ".VikRentItems::numberFormat($total)."\n\n\n";
		$mess .= JURI::root().'index.php?option=com_vikrentcar&task=vieworder&sid='.$this->order_info['sid'].'&ts='.$this->order_info['ts'];

		$vrc_app = VikRentItems::getVriApplication();
		$vrc_app->sendMail($admail, $admail, $admail, $admail, JText::_('VRIOFFCCMAILSUBJECT'), $mess, false);
		
		return $array_result;
	}
	
	//this function is called after the payment has been validated for redirect actions
	//When this method is called, the class is invoked at the same time as validatePayment()
	public function afterValidation ($esit = 0) {
		$redirect_url = 'index.php?option=com_vikrentitems&task=vieworder&sid='.$this->order_info['sid'].'&ts='.$this->order_info['ts'];
		$esit = $this->validation;
		if ($esit < 1) {
			VikError::raiseWarning('', JText::_('VRIOFFCCINVPAY'));
		} else {
			$app = JFactory::getApplication();
			$app->enqueueMessage(JText::_('VRIOFFCCTHANKS'));
		}
		
		$mainframe = JFactory::getApplication();
		$mainframe->redirect($redirect_url);
		exit;
		//
	}
	
}
