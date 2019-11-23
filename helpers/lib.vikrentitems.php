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

if (!defined('VRI_ADMIN_URI')) {
	//this library could be loaded by modules, so we need to load at least the Defines Adapter file.
	include(dirname(__FILE__) . DIRECTORY_SEPARATOR . "adapter" . DIRECTORY_SEPARATOR . "defines.php");
}

if (!function_exists('showSelectVRI')) {
	function showSelectVRI($err) {
		include(VRI_SITE_PATH.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'error_form.php');
	}
}

class VikRentItems {
	
	public static function addJoomlaUser($name, $username, $email, $password) {
		//new method
		jimport('joomla.application.component.helper');
		$params = JComponentHelper::getParams('com_users');
		$user = new JUser;
		$data = array();
		//Get the default new user group, Registered if not specified.
		$system = $params->get('new_usertype', 2);
		$data['groups'] = array();
		$data['groups'][] = $system;
		$data['name']=$name;
		$data['username']=$username;
		$data['email'] = JStringPunycode::emailToPunycode($email);
		$data['password']=$password;
		$data['password2']=$password;
		$data['sendEmail'] = 0; //should the user receive system mails?
		//$data['block'] = 0;
		if (!$user->bind($data)) {
			VikError::raiseWarning('', JText::_($user->getError()));
			return false;
		}
		if (!$user->save()) {
			VikError::raiseWarning('', JText::_($user->getError()));
			return false;
		}
		return $user->id;
	}
	
	public static function userIsLogged() {
		$user = JFactory::getUser();
		if ($user->guest) {
			return false;
		} else {
			return true;
		}
	}

	public static function prepareViewContent() {
		$menu = JFactory::getApplication()->getMenu()->getActive();
		//Joomla 3.7.x - property params is now protected, before it was public
		$menuParams = null;
		if (method_exists($menu, 'getParams')) {
			$menuParams = $menu->getParams();
		} elseif (isset($menu->params)) {
			//Until Joomla 3.6.5
			$menuParams = $menu->params;
		}
		//
		if ($menuParams !== null) {
			$document = JFactory::getDocument();
			if (intval($menu->params->get('show_page_heading')) == 1 && strlen($menu->params->get('page_heading'))) {
				echo '<div class="page-header'.(strlen($clazz = $menu->params->get('pageclass_sfx')) ? ' '.$clazz : '' ).'"><h1>'.$menu->params->get('page_heading').'</h1></div>';
			}
			if (strlen($menu->params->get('menu-meta_description'))) {
				$document->setDescription($menu->params->get('menu-meta_description'));
			}
			if (strlen($menu->params->get('menu-meta_keywords'))) {
				$document->setMetadata('keywords', $menu->params->get('menu-meta_keywords'));
			}
			if (strlen($menu->params->get('robots'))) {
				$document->setMetadata('robots', $menu->params->get('robots'));
			}
		}
	}

	public static function isFontAwesomeEnabled($skipsession = false) {
		if (!$skipsession) {
			$session = JFactory::getSession();
			$s = $session->get('vrifaw', '');
			if (strlen($s)) {
				return ((int)$s == 1);
			}
		}
		$dbo = JFactory::getDBO();
		$q = "SELECT `setting` FROM `#__vikrentitems_config` WHERE `param`='usefa';";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$s = $dbo->loadResult();
			if (!$skipsession) {
				$session->set('vrifaw', $s);
			}
			return ((int)$s == 1);
		}
		$q = "INSERT INTO `#__vikrentitems_config` (`param`,`setting`) VALUES ('usefa', '1');";
		$dbo->setQuery($q);
		$dbo->execute();
		if (!$skipsession) {
			$session->set('vrifaw', '1');
		}
		return true;
	}

	public static function loadFontAwesome($force_load = false) {
		if (!self::isFontAwesomeEnabled() && !$force_load) {
			return false;
		}
		$document = JFactory::getDocument();
		$document->addStyleSheet(VRI_ADMIN_URI.'resources/fontawesome-all.min.css');

		return true;
	}

	/**
	 * If enabled, pick ups at equal times (seconds) as drop offs
	 * will be allowed. Rather than using >= for checking the units
	 * booked, just > will be used for comparing the timestamps.
	 * 
	 * @param 	boolean 	$skipsession 	whether to use the Session.
	 *
	 * @return 	boolean 	True if enabled, false otherwise.
	 *
	 * @since 	1.6
	 */
	public static function allowPickOnDrop($skipsession = false) {
		if (!$skipsession) {
			$session = JFactory::getSession();
			$s = $session->get('vriPkonDp', '');
			if (strlen($s)) {
				return ((int)$s == 1);
			}
		}
		$dbo = JFactory::getDBO();
		$q = "SELECT `setting` FROM `#__vikrentitems_config` WHERE `param`='pickondrop';";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$s = $dbo->loadResult();
			if (!$skipsession) {
				$session->set('vriPkonDp', $s);
			}
			return ((int)$s == 1);
		}
		$q = "INSERT INTO `#__vikrentitems_config` (`param`,`setting`) VALUES ('pickondrop', '0');";
		$dbo->setQuery($q);
		$dbo->execute();
		if (!$skipsession) {
			$session->set('vriPkonDp', '0');
		}
		return true;
	}

	public static function allowMultiLanguage($skipsession = false) {
		if ($skipsession) {
			$dbo = JFactory::getDBO();
			$q = "SELECT `setting` FROM `#__vikrentitems_config` WHERE `param`='multilang';";
			$dbo->setQuery($q);
			$dbo->execute();
			$s = $dbo->loadAssocList();
			return intval($s[0]['setting']) == 1 ? true : false;
		} else {
			$session = JFactory::getSession();
			$sval = $session->get('vriMultiLang', '');
			if (!empty($sval)) {
				return intval($sval) == 1 ? true : false;
			} else {
				$dbo = JFactory::getDBO();
				$q = "SELECT `setting` FROM `#__vikrentitems_config` WHERE `param`='multilang';";
				$dbo->setQuery($q);
				$dbo->execute();
				$s = $dbo->loadAssocList();
				$session->set('vriMultiLang', $s[0]['setting']);
				return intval($s[0]['setting']) == 1 ? true : false;
			}
		}
	}

	public static function getTranslator() {
		if (!class_exists('VikRentItemsTranslator')) {
			require_once(VRI_SITE_PATH . DS . "helpers" . DS . "translator.php");
		}
		return new VikRentItemsTranslator();
	}

	public static function getCPinIstance() {
		if (!class_exists('VikRentItemsCustomersPin')) {
			require_once(VRI_SITE_PATH . DS . "helpers" . DS . "cpin.php");
		}
		return new VikRentItemsCustomersPin();
	}

	public static function getFirstCustDataField($custdata) {
		$first_field = '----';
		if (empty($custdata))
			return $first_field;
		$parts = explode("\n", $custdata);
		foreach ($parts as $part) {
			if (!empty($part)) {
				$field = explode(':', trim($part));
				if (!empty($field[1])) {
					return trim($field[1]);
				}
			}
		}
		return $first_field;
	}
	
	public static function getTheme() {
		$dbo = JFactory::getDBO();
		$q = "SELECT `setting` FROM `#__vikrentitems_config` WHERE `param`='theme';";
		$dbo->setQuery($q);
		$dbo->execute();
		$s=$dbo->loadAssocList();
		return $s[0]['setting'];
	}
	
	public static function getItemParam($params, $what) {
		$retparam = '';
		$parts = explode(';_;', $params);
		foreach ($parts as $p) {
			if (substr(trim($p), 0, (strlen($what) + 1)) == $what.':') {
				$pfound = explode(':', trim($p));
				unset($pfound[0]);
				$retparam = implode(':', $pfound);
				break;
			}
		}
		return $retparam;
	}
	
	public static function loadItemTimeSlots($iditem, $vri_tn = null) {
		$dbo = JFactory::getDBO();
		$q = "SELECT * FROM `#__vikrentitems_timeslots` WHERE `iditems` LIKE '%-".(int)$iditem."-%' ORDER BY `#__vikrentitems_timeslots`.`tname` ASC;";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$fetched = $dbo->loadAssocList();
			if (is_object($vri_tn)) {
				$vri_tn->translateContents($fetched, '#__vikrentitems_timeslots');
			}
			return $fetched;
		}
		return array();
	}
	
	public static function loadTimeSlot($idts, $vri_tn = null) {
		$dbo = JFactory::getDBO();
		$q = "SELECT * FROM `#__vikrentitems_timeslots` WHERE `id`='".intval($idts)."';";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$fetched = $dbo->loadAssocList();
			if (is_object($vri_tn)) {
				$vri_tn->translateContents($fetched, '#__vikrentitems_timeslots');
			}
			return $fetched[0];
		}
		return '';
	}
	
	public static function loadGlobalTimeSlots($vri_tn = null) {
		$dbo = JFactory::getDBO();
		$q = "SELECT * FROM `#__vikrentitems_timeslots` WHERE `global`='1' ORDER BY `#__vikrentitems_timeslots`.`tname` ASC;";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$fetched = $dbo->loadAssocList();
			if (is_object($vri_tn)) {
				$vri_tn->translateContents($fetched, '#__vikrentitems_timeslots');
			}
			return $fetched;
		}
		return array();
	}
	
	public static function loadRelatedItems($ids, $vri_tn = null) {
		$related = array();
		$clause = array();
		foreach ($ids as $idi) {
			$clause []= "`relone` LIKE '%-".$idi."-%'";
		}
		if (count($clause) > 0) {
			$dbo = JFactory::getDBO();
			$q = "SELECT `id`,`relname`,`reltwo` FROM `#__vikrentitems_relations` WHERE ".implode(" OR ", $clause).";";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() > 0) {
				$fetched = $dbo->loadAssocList();
				$validitems = array();
				foreach ($fetched as $f) {
					$parts = explode(';', $f['reltwo']);
					foreach ($parts as $p) {
						$comp = str_replace('-', '', $p);
						if (!in_array($comp, $ids) && !empty($p)) {
							$validitems[] = $comp;
						}
					}
				}
				if (count($validitems) > 0) {
					$validitems = array_unique($validitems);
					$q = "SELECT `id`,`name`,`img`,`units`,`startfrom`,`askquantity`,`params`,`jsparams` FROM `#__vikrentitems_items` WHERE `id` IN (".implode(", ", $validitems).") AND `avail`='1';";
					$dbo->setQuery($q);
					$dbo->execute();
					if ($dbo->getNumRows() > 0) {
						$related = $dbo->loadAssocList();
						if (is_object($vri_tn)) {
							$vri_tn->translateContents($related, '#__vikrentitems_items');
						}
					}
				}
			}
		}
		return $related;
	}
	
	public static function getFooterOrdMail($vri_tn = null) {
		$dbo = JFactory::getDBO();
		$q = "SELECT `id`,`setting` FROM `#__vikrentitems_texts` WHERE `param`='footerordmail';";
		$dbo->setQuery($q);
		$dbo->execute();
		$ft = $dbo->loadAssocList();
		if (is_object($vri_tn)) {
			$vri_tn->translateContents($ft, '#__vikrentitems_texts');
		}
		return $ft[0]['setting'];
	}
	
	public static function requireLogin() {
		$dbo = JFactory::getDBO();
		$q = "SELECT `setting` FROM `#__vikrentitems_config` WHERE `param`='requirelogin';";
		$dbo->setQuery($q);
		$dbo->execute();
		$s = $dbo->loadAssocList();
		return intval($s[0]['setting']) == 1 ? true : false;
	}

	public static function todayBookings() {
		$dbo = JFactory::getDBO();
		$q = "SELECT `setting` FROM `#__vikrentitems_config` WHERE `param`='todaybookings';";
		$dbo->setQuery($q);
		$dbo->execute();
		$s = $dbo->loadAssocList();
		return intval($s[0]['setting']) == 1 ? true : false;
	}
	
	public static function couponsEnabled() {
		$dbo = JFactory::getDBO();
		$q = "SELECT `setting` FROM `#__vikrentitems_config` WHERE `param`='enablecoupons';";
		$dbo->setQuery($q);
		$dbo->execute();
		$s = $dbo->loadAssocList();
		return intval($s[0]['setting']) == 1 ? true : false;
	}

	public static function customersPinEnabled() {
		$dbo = JFactory::getDBO();
		$q = "SELECT `setting` FROM `#__vikrentitems_config` WHERE `param`='enablepin';";
		$dbo->setQuery($q);
		$dbo->execute();
		$s = $dbo->loadAssocList();
		return intval($s[0]['setting']) == 1 ? true : false;
	}
	
	public static function applyExtraHoursChargesBasp() {
		$dbo = JFactory::getDBO();
		$q = "SELECT `setting` FROM `#__vikrentitems_config` WHERE `param`='ehourschbasp';";
		$dbo->setQuery($q);
		$dbo->execute();
		$s = $dbo->loadAssocList();
		//true is before special prices, false is after
		return intval($s[0]['setting']) == 1 ? true : false;
	}
	
	public static function loadJquery($skipsession = false) {
		if ($skipsession) {
			$dbo = JFactory::getDBO();
			$q = "SELECT `setting` FROM `#__vikrentitems_config` WHERE `param`='loadjquery';";
			$dbo->setQuery($q);
			$dbo->execute();
			$s = $dbo->loadAssocList();
			return intval($s[0]['setting']) == 1 ? true : false;
		} else {
			$session = JFactory::getSession();
			$sval = $session->get('loadJquery', '');
			if (!empty($sval)) {
				return intval($sval) == 1 ? true : false;
			} else {
				$dbo = JFactory::getDBO();
				$q = "SELECT `setting` FROM `#__vikrentitems_config` WHERE `param`='loadjquery';";
				$dbo->setQuery($q);
				$dbo->execute();
				$s = $dbo->loadAssocList();
				$session->set('loadJquery', $s[0]['setting']);
				return intval($s[0]['setting']) == 1 ? true : false;
			}
		}
	}
	
	public static function calendarType($skipsession = false) {
		if ($skipsession) {
			$dbo = JFactory::getDBO();
			$q = "SELECT `setting` FROM `#__vikrentitems_config` WHERE `param`='calendar';";
			$dbo->setQuery($q);
			$dbo->execute();
			$s = $dbo->loadAssocList();
			return $s[0]['setting'];
		} else {
			$session = JFactory::getSession();
			$sval = $session->get('calendarType', '');
			if (!empty($sval)) {
				return $sval;
			} else {
				$dbo = JFactory::getDBO();
				$q = "SELECT `setting` FROM `#__vikrentitems_config` WHERE `param`='calendar';";
				$dbo->setQuery($q);
				$dbo->execute();
				$s = $dbo->loadAssocList();
				$session->set('calendarType', $s[0]['setting']);
				return $s[0]['setting'];
			}
		}
	}
	
	public static function getSiteLogo() {
		$dbo = JFactory::getDBO();
		$q = "SELECT `setting` FROM `#__vikrentitems_config` WHERE `param`='sitelogo';";
		$dbo->setQuery($q);
		$dbo->execute();
		$s = $dbo->loadAssocList();
		return $s[0]['setting'];
	}
	
	public static function numCalendars() {
		$dbo = JFactory::getDBO();
		$q = "SELECT `setting` FROM `#__vikrentitems_config` WHERE `param`='numcalendars';";
		$dbo->setQuery($q);
		$dbo->execute();
		$s = $dbo->loadAssocList();
		return $s[0]['setting'];
	}

	public static function getThumbnailsWidth() {
		$dbo = JFactory::getDBO();
		$q = "SELECT `setting` FROM `#__vikrentitems_config` WHERE `param`='thumbswidth';";
		$dbo->setQuery($q);
		$dbo->execute();
		$s = $dbo->loadAssocList();
		return intval($s[0]['setting']);
	}

	public static function getIcalSecretKey() {
		$dbo = JFactory::getDBO();
		$q = "SELECT `setting` FROM `#__vikrentitems_config` WHERE `param`='icalkey';";
		$dbo->setQuery($q);
		$dbo->execute();
		$s=$dbo->loadAssocList();
		return $s[0]['setting'];
	}

	public static function getGoogleMapsKey() {
		$dbo = JFactory::getDBO();
		$q = "SELECT `setting` FROM `#__vikrentitems_config` WHERE `param`='gmapskey' LIMIT 1;";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			return $dbo->loadResult();
		}
		return '';
	}
	
	public static function showPartlyReserved() {
		$dbo = JFactory::getDBO();
		$q = "SELECT `setting` FROM `#__vikrentitems_config` WHERE `param`='showpartlyreserved';";
		$dbo->setQuery($q);
		$dbo->execute();
		$s = $dbo->loadAssocList();
		return intval($s[0]['setting']) == 1 ? true : false;
	}

	public static function getDisclaimer($vri_tn = null) {
		$dbo = JFactory::getDBO();
		$q = "SELECT `id`,`setting` FROM `#__vikrentitems_texts` WHERE `param`='disclaimer';";
		$dbo->setQuery($q);
		$dbo->execute();
		$ft = $dbo->loadAssocList();
		if (is_object($vri_tn)) {
			$vri_tn->translateContents($ft, '#__vikrentitems_texts');
		}
		return $ft[0]['setting'];
	}

	public static function showFooter() {
		$dbo = JFactory::getDBO();
		$q = "SELECT `setting` FROM `#__vikrentitems_config` WHERE `param`='showfooter';";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() == 1) {
			$s = $dbo->loadAssocList();
			return (intval($s[0]['setting']) == 1 ? true : false);
		} else {
			return false;
		}
	}
	
	public static function formatLocationClosingDays($clostr) {
		$ret = array();
		$x = explode(",", $clostr);
		foreach ($x as $y) {
			if (strlen(trim($y)) > 0) {
				$parts = explode("-", trim($y));
				$date = date('Y-n-j', mktime(0, 0, 0, $parts[1], $parts[2], $parts[0]));
				if (strlen($date) > 0) {
					$ret[] = '"'.$date.'"';
				}
			}
		}
		return $ret;
	}
	
	public static function getPriceName($idp, $vri_tn = null) {
		$dbo = JFactory::getDBO();
		$q = "SELECT `id`,`name` FROM `#__vikrentitems_prices` WHERE `id`='" . $idp . "';";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() == 1) {
			$n = $dbo->loadAssocList();
			if (is_object($vri_tn)) {
				$vri_tn->translateContents($n, '#__vikrentitems_prices');
			}
			return $n[0]['name'];
		}
		return "";
	}

	public static function getPriceAttr($idp, $vri_tn = null) {
		$dbo = JFactory::getDBO();
		$q = "SELECT `id`,`attr` FROM `#__vikrentitems_prices` WHERE `id`='" . $idp . "';";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() == 1) {
			$n = $dbo->loadAssocList();
			if (is_object($vri_tn)) {
				$vri_tn->translateContents($n, '#__vikrentitems_prices');
			}
			return $n[0]['attr'];
		}
		return "";
	}

	public static function getAliq($idal) {
		$dbo = JFactory::getDBO();
		$q = "SELECT `aliq` FROM `#__vikrentitems_iva` WHERE `id`='" . $idal . "';";
		$dbo->setQuery($q);
		$dbo->execute();
		$n = $dbo->loadAssocList();
		return $n[0]['aliq'];
	}

	public static function getTimeOpenStore($skipsession = false) {
		if ($skipsession) {
			$dbo = JFactory::getDBO();
			$q = "SELECT `setting` FROM `#__vikrentitems_config` WHERE `param`='timeopenstore';";
			$dbo->setQuery($q);
			$dbo->execute();
			$n = $dbo->loadAssocList();
			if (empty($n[0]['setting']) && $n[0]['setting'] != "0") {
				return false;
			} else {
				$x = explode("-", $n[0]['setting']);
				if (!empty($x[1]) && $x[1] != "0") {
					return $x;
				}
			}
		} else {
			$session = JFactory::getSession();
			$sval = $session->get('getTimeOpenStore', '');
			if (!empty($sval)) {
				return $sval;
			} else {
				$dbo = JFactory::getDBO();
				$q = "SELECT `setting` FROM `#__vikrentitems_config` WHERE `param`='timeopenstore';";
				$dbo->setQuery($q);
				$dbo->execute();
				$n = $dbo->loadAssocList();
				if (empty($n[0]['setting']) && $n[0]['setting'] != "0") {
					return false;
				} else {
					$x = explode("-", $n[0]['setting']);
					if (!empty($x[1]) && $x[1] != "0") {
						$session->set('getTimeOpenStore', $x);
						return $x;
					}
				}
			}
		}
		return false;
	}
	
	public static function getForcedPickDropTimes($skipsession = false) {
		if ($skipsession) {
			$dbo = JFactory::getDBO();
			$retval = array(0 => '', 1 => '');
			$q = "SELECT `param`,`setting` FROM `#__vikrentitems_config` WHERE `param`='globpickupt' OR `param`='globdropofft';";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() > 0) {
				$s = $dbo->loadAssocList();
				foreach ($s as $cf) {
					if ($cf['param'] == 'globpickupt') {
						if (!empty($cf['setting'])) {
							$parts = explode(':', $cf['setting']);
							$retval[0] = array(0 => $parts[0], 1 => $parts[1]);
						}
					} elseif ($cf['param'] == 'globdropofft') {
						if (!empty($cf['setting'])) {
							$parts = explode(':', $cf['setting']);
							$retval[1] = array(0 => $parts[0], 1 => $parts[1]);
						}
					}
				}
			}
			return $retval;
		} else {
			$session = JFactory::getSession();
			$sval = $session->get('getForcedPickDropTimes', '');
			if (is_array($sval) && count($sval) > 0) {
				return $sval;
			} else {
				$dbo = JFactory::getDBO();
				$retval = array(0 => '', 1 => '');
				$q = "SELECT `param`,`setting` FROM `#__vikrentitems_config` WHERE `param`='globpickupt' OR `param`='globdropofft';";
				$dbo->setQuery($q);
				$dbo->execute();
				if ($dbo->getNumRows() > 0) {
					$s = $dbo->loadAssocList();
					foreach ($s as $cf) {
						if ($cf['param'] == 'globpickupt') {
							if (!empty($cf['setting'])) {
								$parts = explode(':', $cf['setting']);
								$retval[0] = array(0 => $parts[0], 1 => $parts[1]);
							}
						} elseif ($cf['param'] == 'globdropofft') {
							if (!empty($cf['setting'])) {
								$parts = explode(':', $cf['setting']);
								$retval[1] = array(0 => $parts[0], 1 => $parts[1]);
							}
						}
					}
				}
				$session->set('getForcedPickDropTimes', $retval);
				return $retval;
			}
		}
	}
	
	public static function getGlobalClosingDays() {
		$dbo = JFactory::getDBO();
		$q = "SELECT `setting` FROM `#__vikrentitems_config` WHERE `param`='globalclosingdays';";
		$dbo->setQuery($q);
		$dbo->execute();
		$n = $dbo->loadAssocList();
		if (empty($n[0]['setting'])) {
			return '';
		}
		$ret = array('singleday' => array(), 'weekly' => array());
		$parts = explode(';', $n[0]['setting']);
		foreach ($parts as $p) {
			if (!empty($p)) {
				$dateparts = explode(':', $p);
				if (count($dateparts) == 2) {
					if (intval($dateparts[1]) == 1) {
						$ret['singleday'][] = $dateparts[0];
					} else {
						$ret['weekly'][] = $dateparts[0];
					}
				}
			}
		}
		if (count($ret['singleday']) > 0) {
			$ret['singleday'] = array_unique($ret['singleday']);
		}
		if (count($ret['weekly']) > 0) {
			$ret['weekly'] = array_unique($ret['weekly']);
		}
		return $ret;
	}
	
	public static function loadPreviousUserData($uid) {
		$ret = array();
		$ret['customfields'] = array();
		$dbo = JFactory::getDBO();
		if (!empty($uid) && intval($uid) > 0) {
			$q = "SELECT * FROM `#__vikrentitems_usersdata` WHERE `ujid`='".intval($uid)."';";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() > 0) {
				$olddata = $dbo->loadAssocList();
				return json_decode($olddata[0]['data'], true);
			}
		}
		return $ret;
	}
	
	public static function getHoursMinutes($secs) {
		if ($secs >= 3600) {
			$op = $secs / 3600;
			$hours = floor($op);
			$less = $hours * 3600;
			$newsec = $secs - $less;
			$optwo = $newsec / 60;
			$minutes = floor($optwo);
		} else {
			$hours = "0";
			$optwo = $secs / 60;
			$minutes = floor($optwo);
		}
		$x[] = $hours;
		$x[] = $minutes;
		return $x;
	}
	
	public static function getDeliveryBaseAddress($skipsession = false) {
		if ($skipsession) {
			$dbo = JFactory::getDBO();
			$q = "SELECT `setting` FROM `#__vikrentitems_config` WHERE `param`='deliverybaseaddress';";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() > 0) {
				$s = $dbo->loadAssocList();
				return $s[0]['setting'];
			} else {
				return '';
			}
		} else {
			$session = JFactory::getSession();
			$sval = $session->get('getDeliveryBaseAddress', '');
			if (strlen($sval) > 0) {
				return $sval;
			} else {
				$dbo = JFactory::getDBO();
				$q = "SELECT `setting` FROM `#__vikrentitems_config` WHERE `param`='deliverybaseaddress';";
				$dbo->setQuery($q);
				$dbo->execute();
				if ($dbo->getNumRows() > 0) {
					$s = $dbo->loadAssocList();
					$session->set('getDeliveryBaseAddress', $s[0]['setting']);
					return $s[0]['setting'];
				} else {
					return '';
				}
			}
		}
	}
	
	public static function getDeliveryBaseLatitude($skipsession = false) {
		if ($skipsession) {
			$dbo = JFactory::getDBO();
			$q = "SELECT `setting` FROM `#__vikrentitems_config` WHERE `param`='deliverybaselat';";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() > 0) {
				$s = $dbo->loadAssocList();
				return $s[0]['setting'];
			} else {
				return '';
			}
		} else {
			$session = JFactory::getSession();
			$sval = $session->get('getDeliveryBaseLatitude', '');
			if (strlen($sval) > 0) {
				return $sval;
			} else {
				$dbo = JFactory::getDBO();
				$q = "SELECT `setting` FROM `#__vikrentitems_config` WHERE `param`='deliverybaselat';";
				$dbo->setQuery($q);
				$dbo->execute();
				if ($dbo->getNumRows() > 0) {
					$s = $dbo->loadAssocList();
					$session->set('getDeliveryBaseLatitude', $s[0]['setting']);
					return $s[0]['setting'];
				} else {
					return '';
				}
			}
		}
	}
	
	public static function getDeliveryBaseLongitude($skipsession = false) {
		if ($skipsession) {
			$dbo = JFactory::getDBO();
			$q = "SELECT `setting` FROM `#__vikrentitems_config` WHERE `param`='deliverybaselng';";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() > 0) {
				$s = $dbo->loadAssocList();
				return $s[0]['setting'];
			} else {
				return '';
			}
		} else {
			$session = JFactory::getSession();
			$sval = $session->get('getDeliveryBaseLongitude', '');
			if (strlen($sval) > 0) {
				return $sval;
			} else {
				$dbo = JFactory::getDBO();
				$q = "SELECT `setting` FROM `#__vikrentitems_config` WHERE `param`='deliverybaselng';";
				$dbo->setQuery($q);
				$dbo->execute();
				if ($dbo->getNumRows() > 0) {
					$s = $dbo->loadAssocList();
					$session->set('getDeliveryBaseLongitude', $s[0]['setting']);
					return $s[0]['setting'];
				} else {
					return '';
				}
			}
		}
	}
	
	public static function getDeliveryCalcUnit($skipsession = false) {
		if ($skipsession) {
			$dbo = JFactory::getDBO();
			$q = "SELECT `setting` FROM `#__vikrentitems_config` WHERE `param`='deliverycalcunit';";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() > 0) {
				$s = $dbo->loadAssocList();
				return $s[0]['setting'];
			} else {
				return 'km';
			}
		} else {
			$session = JFactory::getSession();
			$sval = $session->get('getDeliveryCalcUnit', '');
			if (strlen($sval) > 0) {
				return $sval;
			} else {
				$dbo = JFactory::getDBO();
				$q = "SELECT `setting` FROM `#__vikrentitems_config` WHERE `param`='deliverycalcunit';";
				$dbo->setQuery($q);
				$dbo->execute();
				if ($dbo->getNumRows() > 0) {
					$s = $dbo->loadAssocList();
					$session->set('getDeliveryCalcUnit', $s[0]['setting']);
					return $s[0]['setting'];
				} else {
					return 'km';
				}
			}
		}
	}
	
	public static function getDeliveryCostPerUnit($skipsession = false) {
		if ($skipsession) {
			$dbo = JFactory::getDBO();
			$q = "SELECT `setting` FROM `#__vikrentitems_config` WHERE `param`='deliverycostperunit';";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() > 0) {
				$s = $dbo->loadAssocList();
				return $s[0]['setting'];
			} else {
				return '0.01';
			}
		} else {
			$session = JFactory::getSession();
			$sval = $session->get('getDeliveryCostPerUnit', '');
			if (strlen($sval) > 0) {
				return $sval;
			} else {
				$dbo = JFactory::getDBO();
				$q = "SELECT `setting` FROM `#__vikrentitems_config` WHERE `param`='deliverycostperunit';";
				$dbo->setQuery($q);
				$dbo->execute();
				if ($dbo->getNumRows() > 0) {
					$s = $dbo->loadAssocList();
					$session->set('getDeliveryCostPerUnit', $s[0]['setting']);
					return $s[0]['setting'];
				} else {
					return '0.01';
				}
			}
		}
	}
	
	public static function getDeliveryMaxDistance($skipsession = false) {
		if ($skipsession) {
			$dbo = JFactory::getDBO();
			$q = "SELECT `setting` FROM `#__vikrentitems_config` WHERE `param`='deliverymaxunitdist';";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() > 0) {
				$s = $dbo->loadAssocList();
				return $s[0]['setting'];
			} else {
				return '';
			}
		} else {
			$session = JFactory::getSession();
			$sval = $session->get('getDeliveryMaxDistance', '');
			if (strlen($sval) > 0) {
				return $sval;
			} else {
				$dbo = JFactory::getDBO();
				$q = "SELECT `setting` FROM `#__vikrentitems_config` WHERE `param`='deliverymaxunitdist';";
				$dbo->setQuery($q);
				$dbo->execute();
				if ($dbo->getNumRows() > 0) {
					$s = $dbo->loadAssocList();
					$session->set('getDeliveryMaxDistance', $s[0]['setting']);
					return $s[0]['setting'];
				} else {
					return '';
				}
			}
		}
	}
	
	public static function getDeliveryMaxCost($skipsession = false) {
		if ($skipsession) {
			$dbo = JFactory::getDBO();
			$q = "SELECT `setting` FROM `#__vikrentitems_config` WHERE `param`='deliverymaxcost';";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() > 0) {
				$s = $dbo->loadAssocList();
				return $s[0]['setting'];
			} else {
				return '';
			}
		} else {
			$session = JFactory::getSession();
			$sval = $session->get('getDeliveryMaxCost', '');
			if (strlen($sval) > 0) {
				return $sval;
			} else {
				$dbo = JFactory::getDBO();
				$q = "SELECT `setting` FROM `#__vikrentitems_config` WHERE `param`='deliverymaxcost';";
				$dbo->setQuery($q);
				$dbo->execute();
				if ($dbo->getNumRows() > 0) {
					$s = $dbo->loadAssocList();
					$session->set('getDeliveryMaxCost', $s[0]['setting']);
					return $s[0]['setting'];
				} else {
					return '';
				}
			}
		}
	}
	
	public static function getDeliveryRoundDistance($skipsession = false) {
		if ($skipsession) {
			$dbo = JFactory::getDBO();
			$q = "SELECT `setting` FROM `#__vikrentitems_config` WHERE `param`='deliveryrounddist';";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() > 0) {
				$s = $dbo->loadAssocList();
				return intval($s[0]['setting']) == 1 ? true : false;
			} else {
				return false;
			}
		} else {
			$session = JFactory::getSession();
			$sval = $session->get('getDeliveryRoundDistance', '');
			if (strlen($sval) > 0) {
				return intval($sval) == 1 ? true : false;
			} else {
				$dbo = JFactory::getDBO();
				$q = "SELECT `setting` FROM `#__vikrentitems_config` WHERE `param`='deliveryrounddist';";
				$dbo->setQuery($q);
				$dbo->execute();
				if ($dbo->getNumRows() > 0) {
					$s = $dbo->loadAssocList();
					$session->set('getDeliveryRoundDistance', $s[0]['setting']);
					return intval($s[0]['setting']) == 1 ? true : false;
				} else {
					return false;
				}
			}
		}
	}
	
	public static function getDeliveryRoundCost($skipsession = false) {
		if ($skipsession) {
			$dbo = JFactory::getDBO();
			$q = "SELECT `setting` FROM `#__vikrentitems_config` WHERE `param`='deliveryroundcost';";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() > 0) {
				$s = $dbo->loadAssocList();
				return intval($s[0]['setting']) == 1 ? true : false;
			} else {
				return false;
			}
		} else {
			$session = JFactory::getSession();
			$sval = $session->get('getDeliveryRoundCost', '');
			if (strlen($sval) > 0) {
				return intval($sval) == 1 ? true : false;
			} else {
				$dbo = JFactory::getDBO();
				$q = "SELECT `setting` FROM `#__vikrentitems_config` WHERE `param`='deliveryroundcost';";
				$dbo->setQuery($q);
				$dbo->execute();
				if ($dbo->getNumRows() > 0) {
					$s = $dbo->loadAssocList();
					$session->set('getDeliveryRoundCost', $s[0]['setting']);
					return intval($s[0]['setting']) == 1 ? true : false;
				} else {
					return false;
				}
			}
		}
	}
	
	public static function getDeliveryMapNotes() {
		$dbo = JFactory::getDBO();
		$q = "SELECT `setting` FROM `#__vikrentitems_config` WHERE `param`='deliverymapnotes';";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$s = $dbo->loadAssocList();
			return $s[0]['setting'];
		} else {
			return '';
		}
	}

	public static function isDeliveryPerOrder($skipsession = false) {
		if (!$skipsession) {
			$session = JFactory::getSession();
			$s = $session->get('vriDelivPerOrd', '');
			if (strlen($s)) {
				return ((int)$s == 1);
			}
		}
		$dbo = JFactory::getDBO();
		$q = "SELECT `setting` FROM `#__vikrentitems_config` WHERE `param`='deliveryperord';";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$s = $dbo->loadResult();
			if (!$skipsession) {
				$session->set('vriDelivPerOrd', $s);
			}
			return ((int)$s == 1);
		}
		$q = "INSERT INTO `#__vikrentitems_config` (`param`,`setting`) VALUES ('deliveryperord', '0');";
		$dbo->setQuery($q);
		$dbo->execute();
		if (!$skipsession) {
			$session->set('vriDelivPerOrd', '0');
		}
		return false;
	}

	public static function isDeliveryPerItemUnit($skipsession = false) {
		if (!$skipsession) {
			$session = JFactory::getSession();
			$s = $session->get('vriDelivPerItUnit', '');
			if (strlen($s)) {
				return ((int)$s == 1);
			}
		}
		$dbo = JFactory::getDBO();
		$q = "SELECT `setting` FROM `#__vikrentitems_config` WHERE `param`='deliveryperitunit';";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$s = $dbo->loadResult();
			if (!$skipsession) {
				$session->set('vriDelivPerItUnit', $s);
			}
			return ((int)$s == 1);
		}
		$q = "INSERT INTO `#__vikrentitems_config` (`param`,`setting`) VALUES ('deliveryperitunit', '0');";
		$dbo->setQuery($q);
		$dbo->execute();
		if (!$skipsession) {
			$session->set('vriDelivPerItUnit', '0');
		}
		return false;
	}

	public static function getDeliveryTaxId($skipsession = false) {
		if (!$skipsession) {
			$session = JFactory::getSession();
			$s = $session->get('vriDelivTaxId', '');
			if (strlen($s)) {
				return (int)$s;
			}
		}
		$dbo = JFactory::getDBO();
		$q = "SELECT `setting` FROM `#__vikrentitems_config` WHERE `param`='deliverytaxid';";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$s = $dbo->loadResult();
			if (!$skipsession) {
				$session->set('vriDelivTaxId', $s);
			}
			return (int)$s;
		}
		$q = "INSERT INTO `#__vikrentitems_config` (`param`,`setting`) VALUES ('deliverytaxid', '');";
		$dbo->setQuery($q);
		$dbo->execute();
		if (!$skipsession) {
			$session->set('vriDelivTaxId', '');
		}
		return '';
	}
	
	public static function showPlacesFront($skipsession = false) {
		if ($skipsession) {
			$dbo = JFactory::getDBO();
			$q = "SELECT `setting` FROM `#__vikrentitems_config` WHERE `param`='placesfront';";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() == 1) {
				$s = $dbo->loadAssocList();
				return (intval($s[0]['setting']) == 1 ? true : false);
			} else {
				return false;
			}
		} else {
			$session = JFactory::getSession();
			$sval = $session->get('showPlacesFront', '');
			if (strlen($sval) > 0) {
				return (intval($sval) == 1 ? true : false);
			} else {
				$dbo = JFactory::getDBO();
				$q = "SELECT `setting` FROM `#__vikrentitems_config` WHERE `param`='placesfront';";
				$dbo->setQuery($q);
				$dbo->execute();
				if ($dbo->getNumRows() == 1) {
					$s = $dbo->loadAssocList();
					$session->set('showPlacesFront', $s[0]['setting']);
					return (intval($s[0]['setting']) == 1 ? true : false);
				} else {
					return false;
				}
			}
		}
	}

	public static function showCategoriesFront($skipsession = false) {
		if ($skipsession) {
			$dbo = JFactory::getDBO();
			$q = "SELECT `setting` FROM `#__vikrentitems_config` WHERE `param`='showcategories';";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() == 1) {
				$s = $dbo->loadAssocList();
				return (intval($s[0]['setting']) == 1 ? true : false);
			} else {
				return false;
			}
		} else {
			$session = JFactory::getSession();
			$sval = $session->get('showCategoriesFront', '');
			if (strlen($sval) > 0) {
				return (intval($sval) == 1 ? true : false);
			} else {
				$dbo = JFactory::getDBO();
				$q = "SELECT `setting` FROM `#__vikrentitems_config` WHERE `param`='showcategories';";
				$dbo->setQuery($q);
				$dbo->execute();
				if ($dbo->getNumRows() == 1) {
					$s = $dbo->loadAssocList();
					$session->set('showCategoriesFront', $s[0]['setting']);
					return (intval($s[0]['setting']) == 1 ? true : false);
				} else {
					return false;
				}
			}
		}
	}

	public static function allowRent() {
		$dbo = JFactory::getDBO();
		$q = "SELECT `setting` FROM `#__vikrentitems_config` WHERE `param`='allowrent';";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() == 1) {
			$s = $dbo->loadAssocList();
			return (intval($s[0]['setting']) == 1 ? true : false);
		} else {
			return false;
		}
	}

	public static function getDisabledRentMsg($vri_tn = null) {
		$dbo = JFactory::getDBO();
		$q = "SELECT `id`,`setting` FROM `#__vikrentitems_texts` WHERE `param`='disabledrentmsg';";
		$dbo->setQuery($q);
		$dbo->execute();
		$s = $dbo->loadAssocList();
		if (is_object($vri_tn)) {
			$vri_tn->translateContents($s, '#__vikrentitems_texts');
		}
		return $s[0]['setting'];
	}

	public static function getDateFormat($skipsession = false) {
		if ($skipsession) {
			$dbo = JFactory::getDBO();
			$q = "SELECT `setting` FROM `#__vikrentitems_config` WHERE `param`='dateformat';";
			$dbo->setQuery($q);
			$dbo->execute();
			$s = $dbo->loadAssocList();
			return $s[0]['setting'];
		} else {
			$session = JFactory::getSession();
			$sval = $session->get('getDateFormat', '');
			if (!empty($sval)) {
				return $sval;
			} else {
				$dbo = JFactory::getDBO();
				$q = "SELECT `setting` FROM `#__vikrentitems_config` WHERE `param`='dateformat';";
				$dbo->setQuery($q);
				$dbo->execute();
				$s = $dbo->loadAssocList();
				$session->set('getDateFormat', $s[0]['setting']);
				return $s[0]['setting'];
			}
		}
	}
	
	public static function getTimeFormat($skipsession = false) {
		if ($skipsession) {
			$dbo = JFactory::getDBO();
			$q = "SELECT `setting` FROM `#__vikrentitems_config` WHERE `param`='timeformat';";
			$dbo->setQuery($q);
			$dbo->execute();
			$s = $dbo->loadAssocList();
			return $s[0]['setting'];
		} else {
			$session = JFactory::getSession();
			$sval = $session->get('getTimeFormat', '');
			if (!empty($sval)) {
				return $sval;
			} else {
				$dbo = JFactory::getDBO();
				$q = "SELECT `setting` FROM `#__vikrentitems_config` WHERE `param`='timeformat';";
				$dbo->setQuery($q);
				$dbo->execute();
				$s = $dbo->loadAssocList();
				$session->set('getTimeFormat', $s[0]['setting']);
				return $s[0]['setting'];
			}
		}
	}

	public static function getHoursMoreRb() {
		$dbo = JFactory::getDBO();
		$q = "SELECT `setting` FROM `#__vikrentitems_config` WHERE `param`='hoursmorerentback';";
		$dbo->setQuery($q);
		$dbo->execute();
		$s = $dbo->loadAssocList();
		return $s[0]['setting'];
	}

	public static function getHoursItemAvail() {
		$dbo = JFactory::getDBO();
		$q = "SELECT `setting` FROM `#__vikrentitems_config` WHERE `param`='hoursmoreitemavail';";
		$dbo->setQuery($q);
		$dbo->execute();
		$s = $dbo->loadAssocList();
		return $s[0]['setting'];
	}

	public static function getFrontTitle($vri_tn = null) {
		$dbo = JFactory::getDBO();
		$q = "SELECT `id`,`setting` FROM `#__vikrentitems_texts` WHERE `param`='fronttitle';";
		$dbo->setQuery($q);
		$dbo->execute();
		$ft = $dbo->loadAssocList();
		if (is_object($vri_tn)) {
			$vri_tn->translateContents($ft, '#__vikrentitems_texts');
		}
		return $ft[0]['setting'];
	}

	public static function getFrontTitleTag() {
		$dbo = JFactory::getDBO();
		$q = "SELECT `setting` FROM `#__vikrentitems_config` WHERE `param`='fronttitletag';";
		$dbo->setQuery($q);
		$dbo->execute();
		$ft = $dbo->loadAssocList();
		return $ft[0]['setting'];
	}

	public static function getFrontTitleTagClass() {
		$dbo = JFactory::getDBO();
		$q = "SELECT `setting` FROM `#__vikrentitems_config` WHERE `param`='fronttitletagclass';";
		$dbo->setQuery($q);
		$dbo->execute();
		$ft = $dbo->loadAssocList();
		return $ft[0]['setting'];
	}

	public static function getCurrencyName() {
		$dbo = JFactory::getDBO();
		$q = "SELECT `setting` FROM `#__vikrentitems_config` WHERE `param`='currencyname';";
		$dbo->setQuery($q);
		$dbo->execute();
		$ft = $dbo->loadAssocList();
		return $ft[0]['setting'];
	}

	public static function getCurrencySymb($skipsession = false) {
		if ($skipsession) {
			$dbo = JFactory::getDBO();
			$q = "SELECT `setting` FROM `#__vikrentitems_config` WHERE `param`='currencysymb';";
			$dbo->setQuery($q);
			$dbo->execute();
			$ft = $dbo->loadAssocList();
			return $ft[0]['setting'];
		} else {
			$session = JFactory::getSession();
			$sval = $session->get('getCurrencySymb', '');
			if (!empty($sval)) {
				return $sval;
			} else {
				$dbo = JFactory::getDBO();
				$q = "SELECT `setting` FROM `#__vikrentitems_config` WHERE `param`='currencysymb';";
				$dbo->setQuery($q);
				$dbo->execute();
				$ft = $dbo->loadAssocList();
				$session->set('getCurrencySymb', $ft[0]['setting']);
				return $ft[0]['setting'];
			}
		}
	}

	public static function getNumberFormatData($skipsession = false) {
		if ($skipsession) {
			$dbo = JFactory::getDBO();
			$q = "SELECT `setting` FROM `#__vikrentitems_config` WHERE `param`='numberformat';";
			$dbo->setQuery($q);
			$dbo->execute();
			$ft = $dbo->loadAssocList();
			return $ft[0]['setting'];
		} else {
			$session = JFactory::getSession();
			$sval = $session->get('getNumberFormatData', '');
			if (!empty($sval)) {
				return $sval;
			} else {
				$dbo = JFactory::getDBO();
				$q = "SELECT `setting` FROM `#__vikrentitems_config` WHERE `param`='numberformat';";
				$dbo->setQuery($q);
				$dbo->execute();
				$ft = $dbo->loadAssocList();
				$session->set('getNumberFormatData', $ft[0]['setting']);
				return $ft[0]['setting'];
			}
		}
	}
	
	public static function numberFormat($num, $skipsession = false) {
		if (is_string($num)) {
			// exploding values from templates may contain white-spaces
			$num = trim($num);
		}
		$formatvals = self::getNumberFormatData($skipsession);
		$formatparts = explode(':', $formatvals);
		return number_format($num, (int)$formatparts[0], $formatparts[1], $formatparts[2]);
	}

	public static function getCurrencyCodePp() {
		$dbo = JFactory::getDBO();
		$q = "SELECT `setting` FROM `#__vikrentitems_config` WHERE `param`='currencycodepp';";
		$dbo->setQuery($q);
		$dbo->execute();
		$ft = $dbo->loadAssocList();
		return $ft[0]['setting'];
	}

	public static function getSubmitName($skipsession = false) {
		if ($skipsession) {
			$dbo = JFactory::getDBO();
			$q = "SELECT `id`,`setting` FROM `#__vikrentitems_texts` WHERE `param`='searchbtnval';";
			$dbo->setQuery($q);
			$dbo->execute();
			$ft = $dbo->loadAssocList();
			if (!empty($ft[0]['setting'])) {
				return $ft[0]['setting'];
			} else {
				return "";
			}
		} else {
			$session = JFactory::getSession();
			$sval = $session->get('getSubmitName', '');
			if (!empty($sval)) {
				return $sval;
			} else {
				$dbo = JFactory::getDBO();
				$q = "SELECT `id`,`setting` FROM `#__vikrentitems_texts` WHERE `param`='searchbtnval';";
				$dbo->setQuery($q);
				$dbo->execute();
				$ft = $dbo->loadAssocList();
				if (!empty($ft[0]['setting'])) {
					return $ft[0]['setting'];
				} else {
					return "";
				}
			}
		}
	}

	public static function getSubmitClass($skipsession = false) {
		if ($skipsession) {
			$dbo = JFactory::getDBO();
			$q = "SELECT `setting` FROM `#__vikrentitems_config` WHERE `param`='searchbtnclass';";
			$dbo->setQuery($q);
			$dbo->execute();
			$ft = $dbo->loadAssocList();
			return $ft[0]['setting'];
		} else {
			$session = JFactory::getSession();
			$sval = $session->get('getSubmitClass', '');
			if (!empty($sval)) {
				return $sval;
			} else {
				$dbo = JFactory::getDBO();
				$q = "SELECT `setting` FROM `#__vikrentitems_config` WHERE `param`='searchbtnclass';";
				$dbo->setQuery($q);
				$dbo->execute();
				$ft = $dbo->loadAssocList();
				$session->set('getSubmitClass', $ft[0]['setting']);
				return $ft[0]['setting'];
			}
		}
	}

	public static function getIntroMain($vri_tn = null) {
		$dbo = JFactory::getDBO();
		$q = "SELECT `id`,`setting` FROM `#__vikrentitems_texts` WHERE `param`='intromain';";
		$dbo->setQuery($q);
		$dbo->execute();
		$ft = $dbo->loadAssocList();
		if (is_object($vri_tn)) {
			$vri_tn->translateContents($ft, '#__vikrentitems_texts');
		}
		return $ft[0]['setting'];
	}

	public static function getClosingMain($vri_tn = null) {
		$dbo = JFactory::getDBO();
		$q = "SELECT `id`,`setting` FROM `#__vikrentitems_texts` WHERE `param`='closingmain';";
		$dbo->setQuery($q);
		$dbo->execute();
		$ft = $dbo->loadAssocList();
		if (is_object($vri_tn)) {
			$vri_tn->translateContents($ft, '#__vikrentitems_texts');
		}
		return $ft[0]['setting'];
	}

	public static function getFullFrontTitle($vri_tn = null) {
		$dbo = JFactory::getDBO();
		$q = "SELECT `id`,`setting` FROM `#__vikrentitems_texts` WHERE `param`='fronttitle';";
		$dbo->setQuery($q);
		$dbo->execute();
		$ft = $dbo->loadAssocList();
		if (is_object($vri_tn)) {
			$vri_tn->translateContents($ft, '#__vikrentitems_texts');
		}
		$q = "SELECT `setting` FROM `#__vikrentitems_config` WHERE `param`='fronttitletag';";
		$dbo->setQuery($q);
		$dbo->execute();
		$fttag = $dbo->loadAssocList();
		$q = "SELECT `setting` FROM `#__vikrentitems_config` WHERE `param`='fronttitletagclass';";
		$dbo->setQuery($q);
		$dbo->execute();
		$fttagclass = $dbo->loadAssocList();
		if (empty($ft[0]['setting'])) {
			return "";
		} else {
			if (empty($fttag[0]['setting'])) {
				return $ft[0]['setting'] . "<br/>\n";
			} else {
				$tag = str_replace("<", "", $fttag[0]['setting']);
				$tag = str_replace(">", "", $tag);
				$tag = str_replace("/", "", $tag);
				$tag = trim($tag);
				return "<" . $tag . "" . (!empty($fttagclass) ? " class=\"" . $fttagclass[0]['setting'] . "\"" : "") . ">" . $ft[0]['setting'] . "</" . $tag . ">";
			}
		}
	}

	public static function dateIsValid($date) {
		$df = self::getDateFormat();
		if (strlen($date) != "10") {
			return false;
		}
		$x = explode("/", $date);
		if ($df == "%d/%m/%Y") {
			if (strlen($x[0]) != "2" || $x[0] > 31 || strlen($x[1]) != "2" || $x[1] > 12 || strlen($x[2]) != "4") {
				return false;
			}
		} elseif ($df == "%m/%d/%Y") {
			if (strlen($x[1]) != "2" || $x[1] > 31 || strlen($x[0]) != "2" || $x[0] > 12 || strlen($x[2]) != "4") {
				return false;
			}
		} else {
			if (strlen($x[2]) != "2" || $x[2] > 31 || strlen($x[1]) != "2" || $x[1] > 12 || strlen($x[0]) != "4") {
				return false;
			}
		}
		return true;
	}

	public static function sayDateFormat() {
		$dbo = JFactory::getDBO();
		$q = "SELECT `setting` FROM `#__vikrentitems_config` WHERE `param`='dateformat';";
		$dbo->setQuery($q);
		$dbo->execute();
		$s = $dbo->loadAssocList();
		if ($s[0]['setting'] == "%d/%m/%Y") {
			return JText::_('VRIONFIGONETWELVE');
		} elseif ($s[0]['setting'] == "%m/%d/%Y") {
			return JText::_('VRIONFIGUSDATEFORMAT');
		} else {
			return JText::_('VRIONFIGONETENTHREE');
		}
	}

	/**
	 * Calculates the Unix timestamp from the given date and
	 * time. Avoids DST issues thanks to mktime. With older
	 * versions, DST issues may occur due to the sum of seconds.
	 * 
	 * @param 	string 	$date 	the date string formatted with the current settings
	 * @param 	int 	$h 		hours from 0 to 23 for pick-up/drop-off
	 * @param 	int 	$m 		minutes from 0 to 59 for pick-up/drop-off
	 * 
	 * @return 	int 	the Unix timestamp of the date
	 * 
	 * @since 	1.6
	 */
	public static function getDateTimestamp($date, $h, $m) {
		if (empty($date)) {
			return 0;
		}
		$df = self::getDateFormat();
		$x = explode("/", $date);
		if ($df == "%d/%m/%Y") {
			$month = (int)$x[1];
			$mday = (int)$x[0];
			$year = (int)$x[2];
		} elseif ($df == "%m/%d/%Y") {
			$month = (int)$x[0];
			$mday = (int)$x[1];
			$year = (int)$x[2];
		} else {
			$month = (int)$x[1];
			$mday = (int)$x[2];
			$year = (int)$x[0];
		}
		return mktime((int)$h, (int)$m, 0, $month, $mday, $year);
	}

	public static function ivaInclusa($skipsession = false) {
		if ($skipsession) {
			$dbo = JFactory::getDBO();
			$q = "SELECT `setting` FROM `#__vikrentitems_config` WHERE `param`='ivainclusa';";
			$dbo->setQuery($q);
			$dbo->execute();
			$s = $dbo->loadAssocList();
			return (intval($s[0]['setting']) == 1 ? true : false);
		} else {
			$session = JFactory::getSession();
			$sval = $session->get('ivaInclusa', '');
			if (strlen($sval) > 0) {
				return (intval($sval) == 1 ? true : false);
			} else {
				$dbo = JFactory::getDBO();
				$q = "SELECT `setting` FROM `#__vikrentitems_config` WHERE `param`='ivainclusa';";
				$dbo->setQuery($q);
				$dbo->execute();
				$s = $dbo->loadAssocList();
				$session->set('ivaInclusa', $s[0]['setting']);
				return (intval($s[0]['setting']) == 1 ? true : false);
			}
		}
	}

	public static function tokenForm() {
		$dbo = JFactory::getDBO();
		$q = "SELECT `setting` FROM `#__vikrentitems_config` WHERE `param`='tokenform';";
		$dbo->setQuery($q);
		$dbo->execute();
		$s = $dbo->loadAssocList();
		return (intval($s[0]['setting']) == 1 ? true : false);
	}

	public static function getPaypalAcc() {
		$dbo = JFactory::getDBO();
		$q = "SELECT `setting` FROM `#__vikrentitems_config` WHERE `param`='ccpaypal';";
		$dbo->setQuery($q);
		$dbo->execute();
		$s = $dbo->loadAssocList();
		return $s[0]['setting'];
	}

	public static function getAccPerCent() {
		$dbo = JFactory::getDBO();
		$q = "SELECT `setting` FROM `#__vikrentitems_config` WHERE `param`='payaccpercent';";
		$dbo->setQuery($q);
		$dbo->execute();
		$s = $dbo->loadAssocList();
		return $s[0]['setting'];
	}

	public static function getTypeDeposit($skipsession = false) {
		if ($skipsession) {
			$dbo = JFactory::getDBO();
			$q = "SELECT `setting` FROM `#__vikrentitems_config` WHERE `param`='typedeposit';";
			$dbo->setQuery($q);
			$dbo->execute();
			$s = $dbo->loadAssocList();
			return $s[0]['setting'];
		} else {
			$session = JFactory::getSession();
			$sval = $session->get('vriTypeDeposit', '');
			if (strlen($sval) > 0) {
				return $sval;
			} else {
				$dbo = JFactory::getDBO();
				$q = "SELECT `setting` FROM `#__vikrentitems_config` WHERE `param`='typedeposit';";
				$dbo->setQuery($q);
				$dbo->execute();
				$s = $dbo->loadAssocList();
				$session->set('vriTypeDeposit', $s[0]['setting']);
				return $s[0]['setting'];
			}
		}
	}

	public static function getAdminMail() {
		$dbo = JFactory::getDBO();
		$q = "SELECT `setting` FROM `#__vikrentitems_config` WHERE `param`='adminemail';";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() < 1) {
			return '';
		}
		$s = $dbo->loadAssocList();
		return $s[0]['setting'];
	}

	public static function getSenderMail() {
		$dbo = JFactory::getDBO();
		$q = "SELECT `setting` FROM `#__vikrentitems_config` WHERE `param`='senderemail' LIMIT 1;";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$sendermail = $dbo->loadResult();
			if (!empty($sendermail)) {
				return $sendermail;
			}
		}
		return self::getAdminMail();
	}

	public static function getPaymentName() {
		$dbo = JFactory::getDBO();
		$q = "SELECT `id`,`setting` FROM `#__vikrentitems_texts` WHERE `param`='paymentname';";
		$dbo->setQuery($q);
		$dbo->execute();
		$s = $dbo->loadAssocList();
		return $s[0]['setting'];
	}

	public static function getMinutesLock($conv = false) {
		$dbo = JFactory::getDBO();
		$q = "SELECT `setting` FROM `#__vikrentitems_config` WHERE `param`='minuteslock';";
		$dbo->setQuery($q);
		$dbo->execute();
		$s = $dbo->loadAssocList();
		if ($conv) {
			$op = $s[0]['setting'] * 60;
			return (time() + $op);
		} else {
			return $s[0]['setting'];
		}
	}

	public static function itemNotLocked($iditem, $units, $first, $second, $itemquant = 1) {
		$dbo = JFactory::getDBO();
		$actnow = time();
		$booked = array ();
		$q = "DELETE FROM `#__vikrentitems_tmplock` WHERE `until`<" . $actnow . ";";
		$dbo->setQuery($q);
		$dbo->execute();
		//vikrentitems 1.1
		$secdiff = $second - $first;
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
				$maxhmore = self::getHoursMoreRb() * 3600;
				if ($maxhmore >= $newdiff) {
					$daysdiff = floor($daysdiff);
				} else {
					$daysdiff = ceil($daysdiff);
				}
			}
		}
		$groupdays = self::getGroupDays($first, $second, $daysdiff);
		// VRI 1.6 - Allow pick ups on drop offs
		$picksondrops = self::allowPickOnDrop();
		//
		$check = "SELECT `id`,`ritiro`,`realback` FROM `#__vikrentitems_tmplock` WHERE `iditem`=" . $dbo->quote($iditem) . " AND `until`>=" . $actnow . ";";
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
					}
				}
				if (($bfound + $itemquant) > $units) {
					return false;
				}
			}
		}
		//
		return true;
	}
	
	public static function getGroupDays($first, $second, $daysdiff) {
		$ret = array();
		$ret[] = $first;
		if ($daysdiff > 1) {
			$start = getdate($first);
			$end = getdate($second);
			$endcheck = mktime(0, 0, 0, $end['mon'], $end['mday'], $end['year']);
			for($i = 1; $i < $daysdiff; $i++) {
				$checkday = $start['mday'] + $i;
				$dayts = mktime(0, 0, 0, $start['mon'], $checkday, $start['year']);
				if ($dayts != $endcheck) {				
					$ret[] = $dayts;
				}
			}
		}
		$ret[] = $second;
		return $ret;
	}
	
	public static function checkValidClosingDays($groupdays, $pickup, $dropoff) {
		$errorstr = '';
		$compare = array();
		$compare[] = date('Y-m-d', $groupdays[0]);
		$compare[] = date('Y-m-d', end($groupdays));
		$compare = array_unique($compare);
		$dbo = JFactory::getDBO();
		if ($pickup == $dropoff) {
			$q = "SELECT `id`,`name`,`closingdays` FROM `#__vikrentitems_places` WHERE `id`='".intval($pickup)."';";
		} else {
			$q = "SELECT `id`,`name`,`closingdays` FROM `#__vikrentitems_places` WHERE `id`='".intval($pickup)."' OR `id`='".intval($dropoff)."';";
		}
		$dbo->setQuery($q);
		$dbo->execute();
		$getclosing = $dbo->loadAssocList();
		if (count($getclosing) > 0) {
			foreach ($getclosing as $closed) {
				if (!empty($closed['closingdays'])) {
					$closingdates = explode(",", $closed['closingdays']);
					foreach ($closingdates as $clod) {
						if (!empty($clod)) {
							if ((int)$closed['id'] == (int)$pickup && $clod == $compare[0]) {
								$dateparts = explode("-", $clod);
								$df = self::getDateFormat();
								$df = str_replace('%', '', $df);
								$errorstr = JText::sprintf('VRIERRLOCATIONCLOSEDON', $closed['name'], date($df, mktime(0, 0, 0, $dateparts[1], $dateparts[2], $dateparts[0])));
								break 2;
							} elseif ((int)$closed['id'] == (int)$dropoff && $clod == $compare[1]) {
								$dateparts = explode("-", $clod);
								$df = self::getDateFormat();
								$df = str_replace('%', '', $df);
								$errorstr = JText::sprintf('VRIERRLOCATIONCLOSEDON', $closed['name'], date($df, mktime(0, 0, 0, $dateparts[1], $dateparts[2], $dateparts[0])));
								break 2;
							}
						}
					}
				}
			}
		}
		return $errorstr;
	}
	
	public static function checkValidGlobalClosingDays($groupdays) {
		$errorstr = '';
		$df = self::getDateFormat();
		$df = str_replace('%', '', $df);
		$comparesd = array();
		$comparesd[0] = date('Y-m-d', $groupdays[0]);
		$comparesd[1] = date('Y-m-d', end($groupdays));
		$comparewd = array();
		$infofirst = getdate($groupdays[0]);
		$infosecond = getdate(end($groupdays));
		$comparewd[0] = $infofirst['wday'];
		$comparewd[1] = $infosecond['wday'];
		$globalclosingdays = self::getGlobalClosingDays();
		if (is_array($globalclosingdays)) {
			if (count($globalclosingdays['singleday']) > 0) {
				$gscdarr = array();
				foreach ($globalclosingdays['singleday'] as $kgcs => $gcdsd) {
					$gscdarr[] = date('Y-m-d', $gcdsd);
				}
				$gscdarr = array_unique($gscdarr);
				if (in_array($comparesd[0], $gscdarr)) {
					$errorstr = JText::sprintf('VRIERRGLOBCLOSEDON', date($df, $groupdays[0]));
				}
				if (in_array($comparesd[1], $gscdarr)) {
					$errorstr = JText::sprintf('VRIERRGLOBCLOSEDON', date($df, end($groupdays)));
				}
			}
			$arrwdayslang = array('VRIJQCALSUN','VRIJQCALMON','VRIJQCALTUE','VRIJQCALWED','VRIJQCALTHU','VRIJQCALFRI', 'VRIJQCALSAT');
			if (count($globalclosingdays['weekly']) > 0) {
				$gwcdarr = array();
				foreach ($globalclosingdays['weekly'] as $kgcw => $gcdwd) {
					$moregcdinfo = getdate($gcdwd);
					$gwcdarr[] = $moregcdinfo['wday'];
				}
				$gwcdarr = array_unique($gwcdarr);
				if (in_array($comparewd[0], $gwcdarr)) {
					$errorstr = JText::sprintf('VRIERRGLOBCLOSEDONWDAY', JText::_($arrwdayslang[$comparewd[0]]));
				}
				if (in_array($comparewd[1], $gwcdarr)) {
					$errorstr = JText::sprintf('VRIERRGLOBCLOSEDONWDAY', JText::_($arrwdayslang[$comparewd[1]]));
				}
			}
		}
		return $errorstr;
	}
	
	public static function itemBookable($iditem, $units, $first, $second, $itemquant = 1) {
		$dbo = JFactory::getDBO();
		//vikrentitems 1.1
		$secdiff = $second - $first;
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
				$maxhmore = self::getHoursMoreRb() * 3600;
				if ($maxhmore >= $newdiff) {
					$daysdiff = floor($daysdiff);
				} else {
					$daysdiff = ceil($daysdiff);
				}
			}
		}
		$groupdays = self::getGroupDays($first, $second, $daysdiff);
		// VRI 1.6 - Allow pick ups on drop offs
		$picksondrops = self::allowPickOnDrop();
		//
		$check = "SELECT `id`,`ritiro`,`realback` FROM `#__vikrentitems_busy` WHERE `iditem`=" . $dbo->quote($iditem) . ";";
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
						//VRI 1.1
						if ($groupdays[0] < $bu['ritiro'] && $groupdays[0] < $bu['realback'] && $groupdays[1] > $bu['ritiro'] && $groupdays[1] > $bu['realback']) {
							$bfound++;
						}
					}
				}
				if (($bfound + $itemquant) > $units) {
					return false;
				}
			}
		} elseif ($itemquant > $units) {
			return false;
		}
		//
		return true;
	}

	public static function payTotal() {
		$dbo = JFactory::getDBO();
		$q = "SELECT `setting` FROM `#__vikrentitems_config` WHERE `param`='paytotal';";
		$dbo->setQuery($q);
		$dbo->execute();
		$s = $dbo->loadAssocList();
		return (intval($s[0]['setting']) == 1 ? true : false);
	}
	
	public static function getCouponInfo($code) {
		$dbo = JFactory::getDBO();
		$q = "SELECT * FROM `#__vikrentitems_coupons` WHERE `code`=".$dbo->quote($code).";";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() == 1) {
			$c = $dbo->loadAssocList();
			return $c[0];
		} else {
			return "";
		}
	}
	
	public static function getItemInfo($iditem, $vri_tn = null) {
		$dbo = JFactory::getDBO();
		$q = "SELECT * FROM `#__vikrentitems_items` WHERE `id`='" . $iditem . "';";
		$dbo->setQuery($q);
		$dbo->execute();
		$s = $dbo->loadAssocList();
		if (is_object($vri_tn)) {
			$vri_tn->translateContents($s, '#__vikrentitems_items');
		}
		return $s[0];
	}

	/**
	 * Returns an array with the items records
	 * related to a specific order ID.
	 * 
	 * @param 	int 	$idorder 	the ID of the order to fetch
	 * 
	 * @return 	array 	the records in ordersitems with some info of the items.
	 * 
	 * @since 	1.6
	 */
	public static function loadOrdersItemsData($idorder) {
		$dbo = JFactory::getDBO();
		$q = "SELECT `oi`.*,`i`.`name` AS `item_name`,`i`.`params` FROM `#__vikrentitems_ordersitems` AS `oi` LEFT JOIN `#__vikrentitems_items` `i` ON `i`.`id`=`oi`.`iditem` WHERE `oi`.`idorder`=" . (int)$idorder . ";";
		$dbo->setQuery($q);
		$dbo->execute();
		return $dbo->getNumRows() > 0 ? $dbo->loadAssocList() : array();
	}

	public static function sayCategory($ids, $vri_tn = null) {
		$dbo = JFactory::getDBO();
		$split = explode(";", $ids);
		$say = "";
		foreach ($split as $k => $s) {
			if (strlen($s)) {
				$q = "SELECT `id`,`name` FROM `#__vikrentitems_categories` WHERE `id`='" . $s . "';";
				$dbo->setQuery($q);
				$dbo->execute();
				$nam = $dbo->loadAssocList();
				if (is_object($vri_tn)) {
					$vri_tn->translateContents($nam, '#__vikrentitems_categories');
				}
				$say .= $nam[0]['name'];
				$say .= (strlen($split[($k +1)]) && end($split) != $s ? ", " : "");
			}
		}
		return $say;
	}

	public static function getItemCarat($idc, $vri_tn = null) {
		$dbo = JFactory::getDBO();
		$split = explode(";", $idc);
		$carat = "";
		$dbo = JFactory::getDBO();
		$arr = array ();
		$where = array();
		foreach ($split as $s) {
			if (!empty($s)) {
				$where[]=$s;
			}
		}
		if (count($where) > 0) {
			$q = "SELECT `id`,`name`,`icon`,`align`,`textimg` FROM `#__vikrentitems_caratteristiche` WHERE `id` IN (".implode(",", $where).");";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() > 0) {
				$arr = $dbo->loadAssocList();
				if (is_object($vri_tn)) {
					$vri_tn->translateContents($arr, '#__vikrentitems_caratteristiche');
				}
			}
		}
		if (@ count($arr) > 0) {
			$carat .= "<table class=\"vrisearchcaratt\">";
			foreach ($arr as $a) {
				if (!empty($a['textimg'])) {
					if ($a['align'] == "left") {
						$carat .= "<tr><td align=\"center\">" . $a['textimg'] . "</td>" . (!empty($a['icon']) ? "<td align=\"center\"><img src=\"".JURI::root()."administrator/components/com_vikrentitems/resources/" . $a['icon'] . "\"/></td></tr>" : "</tr>");
					}
					elseif ($a['align'] == "center") {
						$carat .= "<tr><td align=\"center\">" . (!empty($a['icon']) ? "<img src=\"".JURI::root()."administrator/components/com_vikrentitems/resources/" . $a['icon'] . "\"/><br/>" : "") . $a['textimg'] . "</td></tr>";
					} else {
						$carat .= "<tr>" . (!empty($a['icon']) ? "<td align=\"center\"><img src=\"".JURI::root()."administrator/components/com_vikrentitems/resources/" . $a['icon'] . "\"/></td>" : "") . "<td align=\"center\">" . $a['textimg'] . "</td></tr>";
					}
				} else {
					$carat .= (!empty($a['icon']) ? "<tr><td align=\"center\"><img src=\"".JURI::root()."administrator/components/com_vikrentitems/resources/" . $a['icon'] . "\" alt=\"" . $a['name'] . "\" title=\"" . $a['name'] . "\"/></td></tr>" : "");
				}
			}
			$carat .= "</table>\n";
		}
		return $carat;
	}

	public static function getItemCaratFly($idc, $vri_tn = null) {
		$dbo = JFactory::getDBO();
		$split = explode(";", $idc);
		$carat = "";
		$dbo = JFactory::getDBO();
		$arr = array ();
		$where = array();
		foreach ($split as $s) {
			if (!empty($s)) {
				$where[]=$s;
			}
		}
		if (count($where) > 0) {
			$q = "SELECT * FROM `#__vikrentitems_caratteristiche` WHERE `id` IN (".implode(",", $where).") ORDER BY `#__vikrentitems_caratteristiche`.`ordering` ASC;";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() > 0) {
				$arr = $dbo->loadAssocList();
				if (is_object($vri_tn)) {
					$vri_tn->translateContents($arr, '#__vikrentitems_caratteristiche');
				}
			}
		}
		if (@ count($arr) > 0) {
			$carat .= "<table><tr>";
			foreach ($arr as $a) {
				if (!empty($a['textimg'])) {
					if ($a['align'] == "left") {
						$carat .= "<td valign=\"top\">" . $a['textimg'] . (!empty($a['icon']) ? " <img src=\"" . JURI::root() . "administrator/components/com_vikrentitems/resources/" . $a['icon'] . "\"/></td>" : "</td>");
					}
					elseif ($a['align'] == "center") {
						$carat .= "<td align=\"center\" valign=\"top\">" . (!empty($a['icon']) ? "<img src=\"" . JURI::root() . "administrator/components/com_vikrentitems/resources/" . $a['icon'] . "\"/><br/>" : "") . $a['textimg'] . "</td>";
					} else {
						$carat .= "<td valign=\"top\">" . (!empty($a['icon']) ? "<img src=\"" . JURI::root() . "administrator/components/com_vikrentitems/resources/" . $a['icon'] . "\"/> " : "") . $a['textimg'] . "</td>";
					}
				} else {
					$carat .= (!empty($a['icon']) ? "<td valign=\"top\"><img src=\"" . JURI::root() . "administrator/components/com_vikrentitems/resources/" . $a['icon'] . "\" alt=\"" . $a['name'] . "\" title=\"" . $a['name'] . "\"/></td>" : "");
				}
			}
			$carat .= "</tr></table>\n";
		}
		return $carat;
	}

	public static function getItemCaratOriz($idc, $vri_tn = null) {
		$dbo = JFactory::getDBO();
		$split = explode(";", $idc);
		$carat = "";
		$arr = array ();
		$where = array();
		foreach ($split as $s) {
			if (!empty($s)) {
				$where[] = $s;
			}
		}
		if (count($where) > 0) {
			$q = "SELECT * FROM `#__vikrentitems_caratteristiche` WHERE `id` IN (".implode(",", $where).") ORDER BY `#__vikrentitems_caratteristiche`.`ordering` ASC;";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() > 0) {
				$arr = $dbo->loadAssocList();
				if (is_object($vri_tn)) {
					$vri_tn->translateContents($arr, '#__vikrentitems_caratteristiche');
				}
			}
		}
		if (count($arr) > 0) {
			$carat .= "<ul class=\"vriulcarats\">\n";
			foreach ($arr as $a) {
				if (!empty($a['textimg'])) {
					//tooltip icon text is not empty
					if (!empty($a['icon'])) {
						//an icon has been uploaded: display the image
						$carat .= "<li><span class=\"vri-expl\" data-vri-expl=\"".$a['textimg']."\"><img src=\"".VRI_ADMIN_URI."resources/".$a['icon']."\" alt=\"" . $a['name'] . "\" /></span></li>\n";
					} else {
						if (strpos($a['textimg'], '</i>') !== false) {
							//the tooltip icon text is a font-icon, we can use the name as tooltip
							$carat .= "<li><span class=\"vri-expl\" data-vri-expl=\"".$a['name']."\">".$a['textimg']."</span></li>\n";
						} else {
							//display just the text
							$carat .= "<li>".$a['textimg']."</li>\n";
						}
					}
				} else {
					$carat .= (!empty($a['icon']) ? "<li><img src=\"".VRI_ADMIN_URI."resources/" . $a['icon'] . "\" alt=\"" . $a['name'] . "\" title=\"" . $a['name'] . "\"/></li>\n" : "<li>".$a['name']."</li>\n");
				}
			}
			$carat .= "</ul>\n";
		}
		return $carat;
	}

	public static function getItemOptionals($idopts, $vri_tn = null) {
		$split = explode(";", $idopts);
		$dbo = JFactory::getDBO();
		$arr = array ();
		$where = array ();
		foreach ($split as $s) {
			if (!empty($s)) {
				$where[] = $s;
			}
		}
		if (@ count($where) > 0) {
			$q = "SELECT * FROM `#__vikrentitems_optionals` WHERE `id` IN (".implode(", ", $where).") ORDER BY `#__vikrentitems_optionals`.`ordering` ASC;";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() > 0) {
				$arr = $dbo->loadAssocList();
				if (is_object($vri_tn)) {
					$vri_tn->translateContents($arr, '#__vikrentitems_optionals');
				}
			}
		}
		if (@ count($arr) > 0) {
			return $arr;
		}
		return "";
	}
	
	public static function loadOptionSpecifications($optionals) {
		$specifications = '';
		$pool = array();
		foreach ($optionals as $kopt => $opt) {
			if (!empty($opt['specifications'])) {
				$specifications = array();
				break;
			}
		}
		foreach ($optionals as $kopt => $opt) {
			if (!empty($opt['specifications'])) {
				$intervals = explode(';;', $opt['specifications']);
				foreach ($intervals as $intv) {
					if (empty($intv)) continue; 
					$parts = explode('_', $intv);
					if (count($parts) == 2) {
						$specifications[] = $optionals[$kopt];
						$pool[] = $opt['id'];
						break;
					}
				}
			}
		}
		if (is_array($specifications) && count($specifications) > 0) {
			foreach ($optionals as $kopt => $opt) {
				if (!empty($opt['specifications']) || in_array($opt['id'], $pool)) {
					unset($optionals[$kopt]);
				}
			}
			if (count($optionals) <= 0) {
				$optionals = '';
			}
		}
		return array($optionals, $specifications);
	}
	
	public static function getOptionSpecIntervalsCosts($intvstr) {
		$optcosts = array();
		$intervals = explode(';;', $intvstr);
		foreach ($intervals as $kintv => $intv) {
			if (empty($intv)) continue;
			$parts = explode('_', $intv);
			if (count($parts) == 2) {
				$optcosts[$kintv] = (float)$parts[1];
			}
		}
		return $optcosts;
	}
	
	public static function getOptionSpecIntervalsNames($intvstr) {
		$optnames = array();
		$intervals = explode(';;', $intvstr);
		foreach ($intervals as $kintv => $intv) {
			if (empty($intv)) continue;
			$parts = explode('_', $intv);
			if (count($parts) == 2) {
				$optnames[$kintv] = $parts[0];
			}
		}
		return $optnames;
	}

	public static function dayValidTs($days, $first, $second) {
		$secdiff = $second - $first;
		$daysdiff = $secdiff / 86400;
		if (is_int($daysdiff)) {
			if ($daysdiff < 1) {
				$daysdiff = 1;
			}
		} else {
			if ($daysdiff < 1) {
				$daysdiff = 1;
			} else {
				$dbo = JFactory::getDBO();
				$q = "SELECT `setting` FROM `#__vikrentitems_config` WHERE `param`='hoursmorerentback';";
				$dbo->setQuery($q);
				$dbo->execute();
				$s = $dbo->loadAssocList();
				$sum = floor($daysdiff) * 86400;
				$newdiff = $secdiff - $sum;
				$maxhmore = $s[0]['setting'] * 3600;
				if ($maxhmore >= $newdiff) {
					$daysdiff = floor($daysdiff);
				} else {
					$daysdiff = ceil($daysdiff);
				}
			}
		}
		return ($daysdiff == $days ? true : false);
	}
	
	public static function registerLocationTaxRate($idpickuplocation) {
		$dbo = JFactory::getDBO();
		$session = JFactory::getSession();
		$register = '';
		$q = "SELECT `p`.`name`,`i`.`aliq` FROM `#__vikrentitems_places` AS `p` LEFT JOIN `#__vikrentitems_iva` `i` ON `p`.`idiva`=`i`.`id` WHERE `p`.`id`='".intval($idpickuplocation)."';";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$getdata = $dbo->loadAssocList();
			if (!empty($getdata[0]['aliq'])) {
				$register = $getdata[0]['aliq'];
			}
		}
		$session->set('vriLocationTaxRate', $register);
		return true;
	}
	
	public static function sayCostPlusIva($cost, $idprice, $order=array()) {
		$dbo = JFactory::getDBO();
		$session = JFactory::getSession();
		$sval = $session->get('ivaInclusa', '');
		if (strlen($sval) > 0) {
			$ivainclusa = $sval;
		} else {
			$q = "SELECT `setting` FROM `#__vikrentitems_config` WHERE `param`='ivainclusa';";
			$dbo->setQuery($q);
			$dbo->execute();
			$iva = $dbo->loadAssocList();
			$session->set('ivaInclusa', $iva[0]['setting']);
			$ivainclusa = $iva[0]['setting'];
		}
		if (intval($ivainclusa) == 0) {
			//VRI 1.1 Rev.2
			$locationvat = isset($order['locationvat']) && strlen($order['locationvat']) > 0 ? $order['locationvat'] : (count($order) == 0 ? $session->get('vriLocationTaxRate', '') : '');
			if (strlen($locationvat) > 0) {
				$subt = 100 + $locationvat;
				$op = ($cost * $subt / 100);
				return $op;
			}
			//
			$q = "SELECT `idiva` FROM `#__vikrentitems_prices` WHERE `id`='" . $idprice . "';";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() == 1) {
				$pidiva = $dbo->loadAssocList();
				$q = "SELECT `aliq` FROM `#__vikrentitems_iva` WHERE `id`='" . $pidiva[0]['idiva'] . "';";
				$dbo->setQuery($q);
				$dbo->execute();
				if ($dbo->getNumRows() == 1) {
					$paliq = $dbo->loadAssocList();
					$subt = 100 + $paliq[0]['aliq'];
					$op = ($cost * $subt / 100);
					return $op;
				}
			}
		}
		return $cost;
	}

	public static function sayCostMinusIva($cost, $idprice, $order=array()) {
		$dbo = JFactory::getDBO();
		$session = JFactory::getSession();
		$sval = $session->get('ivaInclusa', '');
		if (strlen($sval) > 0) {
			$ivainclusa = $sval;
		} else {
			$q = "SELECT `setting` FROM `#__vikrentitems_config` WHERE `param`='ivainclusa';";
			$dbo->setQuery($q);
			$dbo->execute();
			$iva = $dbo->loadAssocList();
			$session->set('ivaInclusa', $iva[0]['setting']);
			$ivainclusa = $iva[0]['setting'];
		}
		if (intval($ivainclusa) == 1) {
			//VRI 1.1 Rev.2
			$locationvat = isset($order['locationvat']) && strlen($order['locationvat']) > 0 ? $order['locationvat'] : (count($order) == 0 ? $session->get('vriLocationTaxRate', '') : '');
			if (strlen($locationvat) > 0) {
				$subt = 100 + $locationvat;
				$op = ($cost * 100 / $subt);
				return $op;
			}
			//
			$q = "SELECT `idiva` FROM `#__vikrentitems_prices` WHERE `id`='" . $idprice . "';";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() == 1) {
				$pidiva = $dbo->loadAssocList();
				$q = "SELECT `aliq` FROM `#__vikrentitems_iva` WHERE `id`='" . $pidiva[0]['idiva'] . "';";
				$dbo->setQuery($q);
				$dbo->execute();
				if ($dbo->getNumRows() == 1) {
					$paliq = $dbo->loadAssocList();
					$subt = 100 + $paliq[0]['aliq'];
					$op = ($cost * 100 / $subt);
					return $op;
				}
			}
		}
		return $cost;
	}

	public static function sayCustCostMinusIva($cost, $aliq_id) {
		$dbo = JFactory::getDBO();
		$q = "SELECT `aliq` FROM `#__vikrentcar_iva` WHERE `id`='" . (int)$aliq_id . "';";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() == 1) {
			$paliq = $dbo->loadAssocList();
			$subt = 100 + $paliq[0]['aliq'];
			$op = ($cost * 100 / $subt);
			return $op;
		}
		return $cost;
	}

	public static function sayOptionalsPlusIva($cost, $idiva, $order=array()) {
		$dbo = JFactory::getDBO();
		$session = JFactory::getSession();
		$sval = $session->get('ivaInclusa', '');
		if (strlen($sval) > 0) {
			$ivainclusa = $sval;
		} else {
			$q = "SELECT `setting` FROM `#__vikrentitems_config` WHERE `param`='ivainclusa';";
			$dbo->setQuery($q);
			$dbo->execute();
			$iva = $dbo->loadAssocList();
			$session->set('ivaInclusa', $iva[0]['setting']);
			$ivainclusa = $iva[0]['setting'];
		}
		if (intval($ivainclusa) == 0) {
			//VRI 1.1 Rev.2
			$locationvat = isset($order['locationvat']) && strlen($order['locationvat']) > 0 ? $order['locationvat'] : (count($order) == 0 ? $session->get('vriLocationTaxRate', '') : '');
			if (strlen($locationvat) > 0) {
				$subt = 100 + $locationvat;
				$op = ($cost * $subt / 100);
				return $op;
			}
			//
			$q = "SELECT `aliq` FROM `#__vikrentitems_iva` WHERE `id`='" . $idiva . "';";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() == 1) {
				$piva = $dbo->loadAssocList();
				$subt = 100 + $piva[0]['aliq'];
				$op = ($cost * $subt / 100);
				return $op;
			}
		}
		return $cost;
	}

	public static function sayOptionalsMinusIva($cost, $idiva, $order = array()) {
		$dbo = JFactory::getDBO();
		$session = JFactory::getSession();
		$sval = $session->get('ivaInclusa', '');
		if (strlen($sval) > 0) {
			$ivainclusa = $sval;
		} else {
			$q = "SELECT `setting` FROM `#__vikrentitems_config` WHERE `param`='ivainclusa';";
			$dbo->setQuery($q);
			$dbo->execute();
			$iva = $dbo->loadAssocList();
			$session->set('ivaInclusa', $iva[0]['setting']);
			$ivainclusa = $iva[0]['setting'];
		}
		if (intval($ivainclusa) == 1) {
			//VRI 1.1 Rev.2
			$locationvat = isset($order['locationvat']) && strlen($order['locationvat']) > 0 ? $order['locationvat'] : (count($order) == 0 ? $session->get('vriLocationTaxRate', '') : '');
			if (strlen($locationvat) > 0) {
				$subt = 100 + $locationvat;
				$op = ($cost * 100 / $subt);
				return $op;
			}
			//
			$q = "SELECT `aliq` FROM `#__vikrentitems_iva` WHERE `id`='" . $idiva . "';";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() == 1) {
				$piva = $dbo->loadAssocList();
				$subt = 100 + $piva[0]['aliq'];
				$op = ($cost * 100 / $subt);
				return $op;
			}
		}
		return $cost;
	}

	/**
	 * Returns the cost for the delivery without tax.
	 * Delivery fees are always tax included.
	 *
	 * @param 	$cost 	float 	the delivery cost.
	 * @param 	$order 	array 	the order record (optional) for tax override.
	 *
	 * @return 	float 	the cost for the delivery without tax (if any tax rate is assigned).
	 *
	 * @since 	1.6
	 */
	public static function sayDeliveryMinusIva($cost, $order = array()) {
		$dbo = JFactory::getDBO();
		$session = JFactory::getSession();
		//VRI 1.1 Rev.2
		$locationvat = isset($order['locationvat']) && strlen($order['locationvat']) > 0 ? $order['locationvat'] : (count($order) == 0 ? $session->get('vriLocationTaxRate', '') : '');
		if (strlen($locationvat) > 0) {
			$subt = 100 + $locationvat;
			$op = ($cost * 100 / $subt);
			return $op;
		}
		//
		$delivery_tax_rate = self::getDeliveryTaxId();
		if (!empty($delivery_tax_rate)) {
			$q = "SELECT `aliq` FROM `#__vikrentitems_iva` WHERE `id`=" . (int)$delivery_tax_rate . ";";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() == 1) {
				$piva = $dbo->loadAssocList();
				$subt = 100 + $piva[0]['aliq'];
				$op = ($cost * 100 / $subt);
				return $op;
			}
		}
		return $cost;
	}

	public static function getSecretLink() {
		$sid = mt_rand();
		$dbo = JFactory::getDBO();
		$q = "SELECT `sid` FROM `#__vikrentitems_orders`;";
		$dbo->setQuery($q);
		$dbo->execute();
		if (@ $dbo->getNumRows() > 0) {
			$all = $dbo->loadAssocList();
			foreach ($all as $s) {
				$arr[] = $s['sid'];
			}
			if (in_array($sid, $arr)) {
				while (in_array($sid, $arr)) {
					$sid++;
				}
			}
		}
		return $sid;
	}

	public static function buildCustData($arr, $sep) {
		$cdata = "";
		foreach ($arr as $k => $e) {
			if (strlen($e)) {
				$cdata .= (strlen($k) > 0 ? $k . ": " : "") . $e . $sep;
			}
		}
		return $cdata;
	}

	public static function sendAdminMail($to, $subject, $ftitle, $orderid, $ts, $custdata, $vricart, $first, $second, $tot, $status, $place = "", $returnplace = "", $maillocfee = "", $payname = "", $couponstr = "", $totdelivery = 0) {
		$sendwhen = self::getSendEmailWhen();
		if ($sendwhen > 1 && $status == JText::_('VRINATTESA')) {
			return true;
		}
		$parts = explode(';;', $to);
		$to = $parts[0];
		$useremail = $parts[1];
		$dbo = JFactory::getDBO();
		$q = "SELECT `setting` FROM `#__vikrentitems_config` WHERE `param`='currencyname';";
		$dbo->setQuery($q);
		$dbo->execute();
		$currencyname = $dbo->loadResult();
		$q = "SELECT `setting` FROM `#__vikrentitems_config` WHERE `param`='dateformat';";
		$dbo->setQuery($q);
		$dbo->execute();
		$formdate = $dbo->loadResult();
		if ($formdate == "%d/%m/%Y") {
			$df = 'd/m/Y';
		} elseif ($formdate == "%m/%d/%Y") {
			$df = 'm/d/Y';
		} else {
			$df = 'Y/m/d';
		}
		$nowtf = VikRentItems::getTimeFormat();
		$msg = $ftitle . "\n\n";
		$msg .= JText::_('VRIORDERNUMBER') . " " . $orderid . "\n";
		$msg .= JText::_('VRLIBONE') . " " . date($df . ' '.$nowtf, $ts) . "\n";
		$msg .= JText::_('VRLIBTWO') . ":\n" . $custdata . "\n";
		$msg .= JText::_('VRLIBFOUR') . " " . date($df . ' '.$nowtf, $first) . "\n";
		$msg .= JText::_('VRLIBFIVE') . " " . date($df . ' '.$nowtf, $second) . "\n";
		$msg .= (!empty($place) ? JText::_('VRRITIROITEM') . ": " . $place . "\n" : "");
		$msg .= (!empty($returnplace) ? JText::_('VRRETURNITEMORD') . ": " . $returnplace . "\n" : "");
		$msg .= JText::_('VRLIBTHREE') . ": \n\n";
		foreach ($vricart as $iditem => $itemarrparent) {
			foreach ($itemarrparent as $k => $itemarr) {
				$msg .= $itemarr['info']['name'].($itemarr['itemquant'] > 1 ? " x".$itemarr['itemquant'] : "")."\n";
				$msg .= $itemarr['pricestr']."\n";
				$msg .= $itemarr['optstr']."\n";
				if (array_key_exists('delivery', $itemarr)) {
					$msg .= $itemarr['delivery']['vrideliveryaddress']."\n";
				}
				$msg .= "\n";
			}
		}
		if (!empty($maillocfee) && $maillocfee > 0) {
			$msg .= JText::_('VRLOCFEETOPAY') . ": " . self::numberFormat($maillocfee) . " " . $currencyname . "\n\n";
		}
		if ($totdelivery > 0) {
			$msg .= JText::_('VRIMAILTOTDELIVERY') . ": " . self::numberFormat($totdelivery) . " " . $currencyname . "\n\n";
		}
		//vikrentitems 1.1 coupon
		if (strlen($couponstr) > 0) {
			$expcoupon = explode(";", $couponstr);
			if (count($expcoupon) > 1) {
				$msg .= JText::_('VRICOUPON')." ".$expcoupon[2].": -" . $expcoupon[1] . " " . $currencyname . "\n\n";
			}
		}
		//
		$msg .= JText::_('VRLIBSIX') . ": " . $tot . " " . $currencyname . "\n\n";
		if (!empty($payname)) {
			$msg .= JText::_('VRLIBPAYNAME') . ": " . $payname . "\n\n";
		}
		$msg .= JText::_('VRLIBSEVEN') . ": " . $status;

		$subject = '=?UTF-8?B?' . base64_encode($subject) . '?=';
		
		$mailer = JFactory::getMailer();
		$adsendermail = self::getSenderMail();
		$sender = array($adsendermail, $adsendermail);
		$mailer->setSender($sender);
		$mailer->addRecipient($to);
		$mailer->addReplyTo($useremail);
		$mailer->setSubject($subject);
		$mailer->setBody($msg);
		$mailer->isHTML(false);
		$mailer->Encoding = 'base64';
		$mailer->Send();
		
		return true;
	}
	
	public static function loadEmailTemplate($orderid) {
		define('_VIKRENTITEMSEXEC', '1');
		$order_details = array();
		if (!empty($orderid)) {
			$dbo = JFactory::getDBO();
			$q = "SELECT * FROM `#__vikrentitems_orders` WHERE `id`=".(int)$orderid.";";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() > 0) {
				$order_details = $dbo->loadAssoc();
			}
		}
		ob_start();
		include VRI_SITE_PATH . DS . "helpers" . DS ."email_tmpl.php";
		$content = ob_get_contents();
		ob_end_clean();
		return $content;
	}
	
	public static function loadPdfTemplate($orderid) {
		defined('_VIKRENTITEMSEXEC') OR define('_VIKRENTITEMSEXEC', '1');
		$order_details = array();
		if (!empty($orderid)) {
			$dbo = JFactory::getDBO();
			$q = "SELECT * FROM `#__vikrentitems_orders` WHERE `id`=".(int)$orderid.";";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() > 0) {
				$order_details = $dbo->loadAssoc();
			}
		}
		ob_start();
		include VRI_SITE_PATH . DS . "helpers" . DS ."pdf_tmpl.php";
		$content = ob_get_contents();
		ob_end_clean();
		$default_params = array(
			'show_header' => 0,
			'header_data' => array(),
			'show_footer' => 0,
			'pdf_page_orientation' => 'PDF_PAGE_ORIENTATION',
			'pdf_unit' => 'PDF_UNIT',
			'pdf_page_format' => 'PDF_PAGE_FORMAT',
			'pdf_margin_left' => 'PDF_MARGIN_LEFT',
			'pdf_margin_top' => 'PDF_MARGIN_TOP',
			'pdf_margin_right' => 'PDF_MARGIN_RIGHT',
			'pdf_margin_header' => 'PDF_MARGIN_HEADER',
			'pdf_margin_footer' => 'PDF_MARGIN_FOOTER',
			'pdf_margin_bottom' => 'PDF_MARGIN_BOTTOM',
			'pdf_image_scale_ratio' => 'PDF_IMAGE_SCALE_RATIO',
			'header_font_size' => '10',
			'body_font_size' => '10',
			'footer_font_size' => '8'
		);
		if (defined('_VIKRENTITEMS_PAGE_PARAMS') && isset($page_params) && @count($page_params) > 0) {
			$default_params = array_merge($default_params, $page_params);
		}
		return array($content, $default_params);
	}
	
	public static function parseEmailTemplate($tmpl, $orderid, $currencyname, $status, $tlogo, $tcname, $todate, $tcustdata, $vricart, $tpickupdate, $tdropdate, $tpickupplace, $tdropplace, $tlocfee, $ttot, $tlink, $tfootm, $couponstr, $totdelivery) {
		$dbo = JFactory::getDBO();
		$vri_tn = self::getTranslator();
		$parsed = $tmpl;
		//get sid and ts
		$lparts = explode("&sid=", $tlink);
		$lpartstwo = explode("&ts=", $lparts[1]);
		$sid = $lpartstwo[0];
		$lpartsthree = explode("&", $lpartstwo[1]);
		$ts = $lpartsthree[0];
		//
		//Confirmation Number
		if ($status == JText::_('VRIOMPLETED')) {
			$parsed = str_replace("{confirmnumb}", $sid.'-'.$ts, $parsed);
		} else {
			$parsed = preg_replace('#('.preg_quote('{confirmnumb_delimiter}').')(.*)('.preg_quote('{/confirmnumb_delimiter}').')#si', '$1'.' '.'$3', $parsed);
		}
		$parsed = str_replace("{confirmnumb_delimiter}", "", $parsed);
		$parsed = str_replace("{/confirmnumb_delimiter}", "", $parsed);
		//
		$parsed = str_replace("{logo}", $tlogo, $parsed);
		$parsed = str_replace("{company_name}", $tcname, $parsed);
		$parsed = str_replace("{order_id}", $orderid, $parsed);
		$statusclass = $status == JText::_('VRIOMPLETED') ? "confirmed" : "standby";
		$parsed = str_replace("{order_status_class}", $statusclass, $parsed);
		$parsed = str_replace("{order_status}", $status, $parsed);
		$parsed = str_replace("{order_date}", $todate, $parsed);
		//PIN Code
		if ($status == JText::_('VRIOMPLETED') && self::customersPinEnabled()) {
			$cpin = self::getCPinIstance();
			$customer_pin = $cpin->getPinCodeByOrderId($orderid);
			if (!empty($customer_pin)) {
				$tcustdata .= '<h3>'.JText::_('VRYOURPIN').': '.$customer_pin.'</h3>';
			}
		}
		//
		$parsed = str_replace("{customer_info}", $tcustdata, $parsed);
		$parsed = str_replace("{pickup_date}", $tpickupdate, $parsed);
		if (strlen($tpickupplace) > 0) {
			$parsed = str_replace("{pickup_location}", $tpickupplace, $parsed);
		} else {
			$parsed = preg_replace('#('.preg_quote('{if_pickup_location}').')(.*)('.preg_quote('{/if_pickup_location}').')#si', '$1'.' '.'$3', $parsed);
		}
		$parsed = str_replace("{if_pickup_location}", "", $parsed);
		$parsed = str_replace("{/if_pickup_location}", "", $parsed);
		$parsed = str_replace("{dropoff_date}", $tdropdate, $parsed);
		if (strlen($tdropplace) > 0) {
			$parsed = str_replace("{dropoff_location}", $tdropplace, $parsed);
		} else {
			$parsed = preg_replace('#('.preg_quote('{if_dropoff_location}').')(.*)('.preg_quote('{/if_dropoff_location}').')#si', '$1'.' '.'$3', $parsed);
		}
		$parsed = str_replace("{if_dropoff_location}", "", $parsed);
		$parsed = str_replace("{/if_dropoff_location}", "", $parsed);
		//order details
		$orderdetails = "";
		foreach ($vricart as $iditem => $itemarrparent) {
			foreach ($itemarrparent as $k => $itemarr) {
				$expdet = explode("\n", $itemarr['pricestr']);
				$faredets = explode(":", $expdet[0]);
				$orderdetails .= '<div class="hireordata"><span class="Stile9"><strong>'.$itemarr['info']['name'].($itemarr['itemquant'] > 1 ? " x".$itemarr['itemquant'] : "").'</strong>: '.$faredets[0];
				if (!empty($expdet[1])) {
					$attrfaredets = explode(":", $expdet[1]);
					if (strlen($attrfaredets[1]) > 0) {
						$orderdetails .= ' - '.$attrfaredets[0].':'.$attrfaredets[1];
					}
				}
				$fareprice = trim(str_replace($currencyname, "", $faredets[1]));
				$orderdetails .= '</span><div align="right"><span class="Stile9">'.$currencyname.' '.self::numberFormat($fareprice).'</span></div></div>';
				//options
				if (strlen($itemarr['optstr']) > 0) {
					$expopts = explode("\n", $itemarr['optstr']);
					foreach ($expopts as $optinfo) {
						if (!empty($optinfo)) {
							$splitopt = explode(":", $optinfo);
							$optprice = trim(str_replace($currencyname, "", $splitopt[1]));
							$orderdetails .= '<div class="hireordata"><span class="Stile9">'.$splitopt[0].'</span><div align="right"><span class="Stile9">'.$currencyname.' '.self::numberFormat($optprice).'</span></div></div>';
						}
					}
				}
				//
				//delivery service
				if (array_key_exists('delivery', $itemarr)) {
					$orderdetails .= '<div class="hireordata"><span class="Stile9"><strong>'.JText::_('VRIMAILDELIVERYTO').'</strong>'.$itemarr['delivery']['vrideliveryaddress'].'</span><div align="right"><span class="Stile9"></span></div></div>';
				}
				//
			}
		}
		//locations fee
		if (!empty($tlocfee) && $tlocfee > 0) {
			$orderdetails .= '<div class="hireordata"><span class="Stile9">'.JText::_('VRLOCFEETOPAY').'</span><div align="right"><span class="Stile9">'.$currencyname.' '.self::numberFormat($tlocfee).'</span></div></div>';
		}
		//
		//delivery service
		if (!empty($totdelivery) && (int)$totdelivery > 0) {
			$orderdetails .= '<br/><div class="hireordata"><span class="Stile9">'.JText::_('VRIMAILTOTDELIVERY').'</span><div align="right"><span class="Stile9">'.$currencyname.' '.self::numberFormat($totdelivery).'</span></div></div>';
		}
		//
		//coupon
		if (strlen($couponstr) > 0) {
			$expcoupon = explode(";", $couponstr);
			$orderdetails .= '<br/><div class="hireordata"><span class="Stile9">'.JText::_('VRICOUPON').' '.$expcoupon[2].'</span><div align="right"><span class="Stile9">- '.$currencyname.' '.self::numberFormat($expcoupon[1]).'</span></div></div>';
		}
		//
		//discount payment method
		$q = "SELECT `idpayment` FROM `#__vikrentitems_orders` WHERE `id`=".(int)$orderid.";";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() == 1) {
			$idpayment = $dbo->loadResult();
			if (!empty($idpayment)) {
				$exppay = explode('=', $idpayment);
				$payment = self::getPayment($exppay[0], $vri_tn);
				if (is_array($payment)) {
					if ($payment['charge'] > 0.00 && $payment['ch_disc'] != 1) {
						//Discount (not charge)
						if ($payment['val_pcent'] == 1) {
							//fixed value
							$ttot -= $payment['charge'];
							$orderdetails .= '<br/><div class="hireordata"><span class="Stile9">'.$payment['name'].'</span><div align="right"><span class="Stile9">- '.$currencyname.' '.self::numberFormat($payment['charge']).'</span></div></div>';
						} else {
							//percent value
							$percent_disc = $ttot * $payment['charge'] / 100;
							$ttot -= $percent_disc;
							$orderdetails .= '<br/><div class="hireordata"><span class="Stile9">'.$payment['name'].'</span><div align="right"><span class="Stile9">- '.$currencyname.' '.self::numberFormat($percent_disc).'</span></div></div>';
						}
					}
				}
			}
		}
		//
		$parsed = str_replace("{order_details}", $orderdetails, $parsed);
		//
		$parsed = str_replace("{order_total}", $currencyname.' '.self::numberFormat($ttot), $parsed);
		$parsed = str_replace("{order_link}", '<a href="'.$tlink.'">'.$tlink.'</a>', $parsed);
		$parsed = str_replace("{footer_emailtext}", $tfootm, $parsed);
		//deposit
		$deposit_str = '';
		if ($status != JText::_('VRIOMPLETED') && !self::payTotal()) {
			$percentdeposit = self::getAccPerCent();
			if ($percentdeposit > 0) {
				if (self::getTypeDeposit() == "fixed") {
					$deposit_amount = $percentdeposit;
				} else {
					$deposit_amount = $ttot * $percentdeposit / 100;
				}
				if ($deposit_amount > 0) {
					$deposit_str = '<div class="hireordata hiredeposit"><span class="Stile9">'.JText::_('VRLEAVEDEPOSIT').'</span><div align="right"><strong>'.$currencyname.' '.self::numberFormat($deposit_amount).'</strong></div></div>';
				}
			}
		}
		$parsed = str_replace("{order_deposit}", $deposit_str, $parsed);
		//
		//Amount Paid - Remaining Balance
		$totpaid_str = '';
		$q = "SELECT `totpaid` FROM `#__vikrentitems_orders` WHERE `id`=".(int)$orderid.";";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() == 1) {
			$tot_paid = $dbo->loadResult();
			$diff_topay = (float)$ttot - (float)$tot_paid;
			if ((float)$tot_paid > 0) {
				$totpaid_str .= '<div class="hireordata hiredeposit"><span class="Stile9">'.JText::_('VRIAMOUNTPAID').'</span><div align="right"><strong>'.$currencyname.' '.self::numberFormat($tot_paid).'</strong></div></div>';
				//only in case the remaining balance is greater than 1 to avoid commissions issues
				if ($diff_topay > 1) {
					$totpaid_str .= '<div class="hireordata hiredeposit"><span class="Stile9">'.JText::_('VRITOTALREMAINING').'</span><div align="right"><strong>'.$currencyname.' '.self::numberFormat($diff_topay).'</strong></div></div>';
				}
			}
		}
		$parsed = str_replace("{order_total_paid}", $totpaid_str, $parsed);
		//
		
		return $parsed;
	}
	
	public static function parsePdfTemplate($tmpl, $orderid, $currencyname, $status, $tlogo, $tcname, $todate, $custdata, $vricart, $tpickupdate, $tdropdate, $tpickupplace, $tdropplace, $tlocfee, $ttot, $tlink, $tfootm, $couponstr, $totdelivery) {
		$dbo = JFactory::getDBO();
		$vri_tn = self::getTranslator();
		$parsed = $tmpl;
		// VRI 1.6 - to avoid cURL problems, images paths should be relative (only if in VRI_ADMIN_URI, other URLs/Dirs are ignored and HTTP is kept)
		$tlogo = !empty($tlogo) && strpos($tlogo, VRI_ADMIN_URI) !== false ? str_replace(VRI_ADMIN_URI, VRI_ADMIN_PATH.DS, str_replace('/', DS, $tlogo)) : $tlogo;
		$parsed = str_replace("{logo}", $tlogo, $parsed);
		//
		$parsed = str_replace("{company_name}", $tcname, $parsed);
		$parsed = str_replace("{order_id}", $orderid, $parsed);
		$statusclass = $status == JText::_('VRIOMPLETED') ? "green" : "red";
		$parsed = str_replace("{order_status_class}", $statusclass, $parsed);
		$parsed = str_replace("{order_status}", $status, $parsed);
		$parsed = str_replace("{order_date}", $todate, $parsed);
		$parsed = str_replace("{customer_info}", nl2br($custdata), $parsed);
		$parsed = str_replace("{pickup_date}", $tpickupdate, $parsed);
		if (strlen($tpickupplace) > 0) {
			$parsed = str_replace("{pickup_location}", $tpickupplace, $parsed);
		} else {
			$parsed = preg_replace('#('.preg_quote('{if_pickup_location}').')(.*)('.preg_quote('{/if_pickup_location}').')#si', '$1'.' '.'$3', $parsed);
			$parsed = preg_replace('#('.preg_quote('{if_pickup_location_label}').')(.*)('.preg_quote('{/if_pickup_location_label}').')#si', '$1'.' '.'$3', $parsed);
		}
		$parsed = str_replace("{if_pickup_location}", "", $parsed);
		$parsed = str_replace("{/if_pickup_location}", "", $parsed);
		$parsed = str_replace("{if_pickup_location_label}", "", $parsed);
		$parsed = str_replace("{/if_pickup_location_label}", "", $parsed);
		$parsed = str_replace("{dropoff_date}", $tdropdate, $parsed);
		if (strlen($tdropplace) > 0) {
			$parsed = str_replace("{dropoff_location}", $tdropplace, $parsed);
		} else {
			$parsed = preg_replace('#('.preg_quote('{if_dropoff_location}').')(.*)('.preg_quote('{/if_dropoff_location}').')#si', '$1'.' '.'$3', $parsed);
			$parsed = preg_replace('#('.preg_quote('{if_dropoff_location_label}').')(.*)('.preg_quote('{/if_dropoff_location_label}').')#si', '$1'.' '.'$3', $parsed);
		}
		$parsed = str_replace("{if_dropoff_location}", "", $parsed);
		$parsed = str_replace("{/if_dropoff_location}", "", $parsed);
		$parsed = str_replace("{if_dropoff_location_label}", "", $parsed);
		$parsed = str_replace("{/if_dropoff_location_label}", "", $parsed);
		//order details
		$totalnet = 0;
		$totdeliverynet = self::sayDeliveryMinusIva((float)$totdelivery);
		$totalnet += $totdeliverynet;
		$totaltax = 0;
		$totaltax += $totdelivery - $totdeliverynet;
		$arrayinfopdf = array();
		$orderdetails = "";
		foreach ($vricart as $iditem => $itemarrparent) {
			foreach ($itemarrparent as $k => $itemarr) {
				$arrayinfopdf = $itemarr['infopdf'];
				$totalnet += $itemarr['infopdf']['tarminusiva'];
				$totaltax += $itemarr['infopdf']['tartax'];
				$expdet = explode("\n", $itemarr['pricestr']);
				$faredets = explode(":", $expdet[0]);
				$orderdetails .= '<tr><td align="left" style="border: 1px solid #DDDDDD;">'.$itemarr['info']['name'].($itemarr['itemquant'] > 1 ? " x".$itemarr['itemquant'] : "").'<br/>'.$faredets[0];
				if (!empty($expdet[1])) {
					$attrfaredets = explode(":", $expdet[1]);
					if (strlen($attrfaredets[1]) > 0) {
						$orderdetails .= ' - '.$attrfaredets[0].':'.$attrfaredets[1];
					}
				}
				$fareprice = trim(str_replace($currencyname, "", $faredets[1]));
				$numdays = (array_key_exists('timeslot', $itemarr) ? $itemarr['timeslot']['name'] : $itemarr['infopdf']['days']);
				$orderdetails .= '</td><td align="center" style="border: 1px solid #DDDDDD;">'.$numdays.'</td><td align="left" style="border: 1px solid #DDDDDD;">'.$currencyname.' '.self::numberFormat($itemarr['infopdf']['tarminusiva']).'</td><td align="left" style="border: 1px solid #DDDDDD;">'.$currencyname.' '.self::numberFormat($itemarr['infopdf']['tartax']).'</td><td align="left" style="border: 1px solid #DDDDDD;">'.$currencyname.' '.self::numberFormat($fareprice).'</td></tr>';
				//options
				if (strlen($itemarr['optstr']) > 0) {
					$expopts = explode("\n", $itemarr['optstr']);
					foreach ($expopts as $kexpopt=>$optinfo) {
						if (!empty($optinfo)) {
							$splitopt = explode(":", $optinfo);
							$optprice = trim(str_replace($currencyname, "", $splitopt[1]));
							$orderdetails .= '<tr><td align="left" style="border: 1px solid #DDDDDD;">'.$splitopt[0].'</td><td align="center" style="border: 1px solid #DDDDDD;">'.$itemarr['infopdf']['days'].'</td><td align="left" style="border: 1px solid #DDDDDD;">'.$currencyname.' '.self::numberFormat($itemarr['infopdf']['opttaxnet'][$kexpopt]).'</td><td align="left" style="border: 1px solid #DDDDDD;">'.$currencyname.' '.self::numberFormat(($optprice - $itemarr['infopdf']['opttaxnet'][$kexpopt])).'</td><td align="left" style="border: 1px solid #DDDDDD;">'.$currencyname.' '.self::numberFormat($optprice).'</td></tr>';
							$totalnet += $itemarr['infopdf']['opttaxnet'][$kexpopt];
							$totaltax += ($optprice - $itemarr['infopdf']['opttaxnet'][$kexpopt]);
						}
					}
				}
				//
				//delivery service
				if (array_key_exists('delivery', $itemarr)) {
					$orderdetails .= '<tr><td colspan="5" align="left" style="border: 1px solid #DDDDDD;">'.JText::_('VRIMAILDELIVERYTO').' '.$itemarr['delivery']['vrideliveryaddress'].'</td></tr>';
				}
				//
			}
		}
		//locations fee
		if (!empty($tlocfee) && $tlocfee > 0) {
			$orderdetails .= '<tr><td align="left" style="border: 1px solid #DDDDDD;">'.JText::_('VRLOCFEETOPAY').'</td><td align="center" style="border: 1px solid #DDDDDD;">'.$vricart[key($vricart)][0]['infopdf']['days'].'</td><td align="left" style="border: 1px solid #DDDDDD;">'.$currencyname.' '.self::numberFormat($vricart[key($vricart)][0]['infopdf']['locfeenet']).'</td><td align="left" style="border: 1px solid #DDDDDD;">'.$currencyname.' '.self::numberFormat(($tlocfee - $vricart[key($vricart)][0]['infopdf']['locfeenet'])).'</td><td align="left" style="border: 1px solid #DDDDDD;">'.$currencyname.' '.self::numberFormat($tlocfee).'</td></tr>';
			$totalnet += $vricart[key($vricart)][0]['infopdf']['locfeenet'];
			$totaltax += ($tlocfee - $vricart[key($vricart)][0]['infopdf']['locfeenet']);
		}
		//
		//delivery service
		if ($totdelivery > 0) {
			$totdeliverytax = $totdelivery - $totdeliverynet;
			$orderdetails .= '<tr><td><br/></td><td></td><td></td><td></td><td></td></tr>';
			$orderdetails .= '<tr><td align="left" style="border: 1px solid #DDDDDD;">'.JText::_('VRIMAILTOTDELIVERY').'</td><td style="border: 1px solid #DDDDDD;"></td><td style="border: 1px solid #DDDDDD;">'.$currencyname.' '.self::numberFormat($totdeliverynet).'</td><td style="border: 1px solid #DDDDDD;">'.$currencyname.' '.self::numberFormat($totdeliverytax).'</td><td align="left" style="border: 1px solid #DDDDDD;">'.$currencyname.' '.self::numberFormat($totdelivery).'</td></tr>';
		}
		//
		//coupon
		if (strlen($couponstr) > 0) {
			$expcoupon = explode(";", $couponstr);
			$orderdetails .= '<tr><td><br/></td><td></td><td></td><td></td><td></td></tr>';
			$orderdetails .= '<tr><td align="left" style="border: 1px solid #DDDDDD;">'.JText::_('VRICOUPON').' '.$expcoupon[2].'</td><td style="border: 1px solid #DDDDDD;"></td><td style="border: 1px solid #DDDDDD;"></td><td style="border: 1px solid #DDDDDD;"></td><td align="left" style="border: 1px solid #DDDDDD;">- '.$currencyname.' '.self::numberFormat($expcoupon[1]).'</td></tr>';
			// VRI 1.6 - we need to re-calculate proportionally the net and tax based on the coupon discount applied ($ttot = actual total comprehensive of the discount)
			$coupon_disc = (float)$expcoupon[1];
			$prev_tot = $ttot + $coupon_disc;
			// totalnet : prev_tot = x : ttot
			$totalnet = $ttot * $totalnet / $prev_tot;
			// totaltax : prev_tot = x : ttot
			$totaltax = $ttot * $totaltax / $prev_tot;
			//
		}
		//
		$parsed = str_replace("{order_details}", $orderdetails, $parsed);
		//
		// VRI 1.6 - net and tax amounts may get rounded by numberFormat(), so we need to adjust them if they exceed the total
		$tempnet = round($totalnet, 2);
		$temptax = round($totaltax, 2);
		$temptot = round($ttot, 2);
		if (($tempnet + $temptax) != $temptot) {
			// since we don't know if the net or tax were rounded, we sacrifice the tax
			$totaltax = $ttot - $totalnet;
		}
		//
		//order total
		$strordtotal = '<tr><td><br/></td><td></td><td></td><td></td><td></td></tr>';
		$strordtotal .= '<tr><td align="left" bgcolor="#EFEFEF" style="border: 1px solid #DDDDDD;"><strong>'.JText::_('VRLIBSIX').'</strong></td><td bgcolor="#EFEFEF" style="border: 1px solid #DDDDDD;"></td><td bgcolor="#EFEFEF" style="border: 1px solid #DDDDDD;">'.$currencyname.' '.self::numberFormat($totalnet).'</td><td bgcolor="#EFEFEF" style="border: 1px solid #DDDDDD;">'.$currencyname.' '.self::numberFormat($totaltax).'</td><td align="left" bgcolor="#EFEFEF" style="border: 1px solid #DDDDDD;"><strong>'.$currencyname.' '.self::numberFormat($ttot).'</strong></td></tr>';
		if (array_key_exists('tot_paid', $arrayinfopdf) && floatval($arrayinfopdf['tot_paid']) > 0.00 && number_format($ttot, 2) != number_format($arrayinfopdf['tot_paid'], 2)) {
			$strordtotal .= '<tr><td align="left" bgcolor="#EFEFEF" style="border: 1px solid #DDDDDD;"><strong>'.JText::_('VRIAMOUNTPAID').'</strong></td><td bgcolor="#EFEFEF" style="border: 1px solid #DDDDDD;"></td><td bgcolor="#EFEFEF" style="border: 1px solid #DDDDDD;"> </td><td bgcolor="#EFEFEF" style="border: 1px solid #DDDDDD;"> </td><td align="left" bgcolor="#EFEFEF" style="border: 1px solid #DDDDDD;"><strong>'.$currencyname.' '.self::numberFormat($arrayinfopdf['tot_paid']).'</strong></td></tr>';
		}
		$parsed = str_replace("{order_total}", $strordtotal, $parsed);
		//
						
		$parsed = str_replace("{order_link}", '<a href="'.$tlink.'">'.$tlink.'</a>', $parsed);
		$parsed = str_replace("{footer_emailtext}", $tfootm, $parsed);
		
		//custom fields replace
		preg_match_all('/\{customfield ([0-9]+)\}/U', $parsed, $matches);
		if (is_array($matches[1]) && @count($matches[1]) > 0) {
			$dbo = JFactory::getDBO();
			$cfids = array();
			foreach ($matches[1] as $cfid ){
				$cfids[] = $cfid;
			}
			$q = "SELECT * FROM `#__vikrentitems_custfields` WHERE `id` IN (".implode(", ", $cfids).");";
			$dbo->setQuery($q);
			$dbo->execute();
			$cfields = $dbo->getNumRows() > 0 ? $dbo->loadAssocList() : "";
			if (is_array($cfields)) {
				$vri_tn->translateContents($cfields, '#__vikrentitems_custfields');
			}
			$cfmap = array();
			if (is_array($cfields)) {
				foreach ($cfields as $cf) {
					$cfmap[trim(JText::_($cf['name']))] = $cf['id'];
				}
			}
			$cfmapreplace = array();
			$partsreceived = explode("\n", $custdata);
			if (count($partsreceived) > 0) {
				foreach ($partsreceived as $pst) {
					if (!empty($pst)) {
						$tmpdata = explode(":", $pst);
						if (array_key_exists(trim($tmpdata[0]), $cfmap)) {
							$cfmapreplace[$cfmap[trim($tmpdata[0])]] = trim($tmpdata[1]);
						}
					}
				}
			}
			foreach ($matches[1] as $cfid ){
				if (array_key_exists($cfid, $cfmapreplace)) {
					$parsed = str_replace("{customfield ".$cfid."}", $cfmapreplace[$cfid], $parsed);
				} else {
					$parsed = str_replace("{customfield ".$cfid."}", "", $parsed);
				}
			}
		}
		//end custom fields replace
		
		return $parsed;
	}
	
	public static function sendCustMail($to, $subject, $ftitle, $ts, $custdata, $vricart, $first, $second, $tot, $link, $status, $place = "", $returnplace = "", $maillocfee = "", $orderid = "", $strcouponeff = "", $totdelivery = 0) {
		$sendwhen = self::getSendEmailWhen();
		if ($sendwhen > 1 && $status == JText::_('VRINATTESA')) {
			return true;
		}
		$origsubject = $subject;
		$subject = '=?UTF-8?B?' . base64_encode($subject) . '?=';
		$dbo = JFactory::getDBO();
		$vri_tn = self::getTranslator();
		$q = "SELECT `setting` FROM `#__vikrentitems_config` WHERE `param`='currencyname';";
		$dbo->setQuery($q);
		$dbo->execute();
		$currencyname = $dbo->loadResult();
		$q = "SELECT `setting` FROM `#__vikrentitems_config` WHERE `param`='adminemail';";
		$dbo->setQuery($q);
		$dbo->execute();
		$adminemail = $dbo->loadResult();
		$q = "SELECT `id`,`setting` FROM `#__vikrentitems_texts` WHERE `param`='footerordmail';";
		$dbo->setQuery($q);
		$dbo->execute();
		$ft = $dbo->loadAssocList();
		$vri_tn->translateContents($ft, '#__vikrentitems_texts');
		$q = "SELECT `id`,`setting` FROM `#__vikrentitems_config` WHERE `param`='sendjutility';";
		$dbo->setQuery($q);
		$dbo->execute();
		$sendmethod = $dbo->loadAssocList();
		$useju = intval($sendmethod[0]['setting']) == 1 ? true : false;
		$q = "SELECT `setting` FROM `#__vikrentitems_config` WHERE `param`='sitelogo';";
		$dbo->setQuery($q);
		$dbo->execute();
		$sitelogo = $dbo->loadResult();
		$q = "SELECT `setting` FROM `#__vikrentitems_config` WHERE `param`='dateformat';";
		$dbo->setQuery($q);
		$dbo->execute();
		$formdate = $dbo->loadResult();
		if ($formdate == "%d/%m/%Y") {
			$df = 'd/m/Y';
		} elseif ($formdate == "%m/%d/%Y") {
			$df = 'm/d/Y';
		} else {
			$df = 'Y/m/d';
		}
		$nowtf = VikRentItems::getTimeFormat();
		$footerordmail = $ft[0]['setting'];
		$textfooterordmail = strip_tags($footerordmail);
		//text part
		$msg = $ftitle . "\n\n";
		$msg .= JText::_('VRLIBEIGHT') . " " . date($df . ' '.$nowtf, $ts) . "\n";
		$msg .= JText::_('VRLIBNINE') . ":\n" . $custdata . "\n";
		$msg .= JText::_('VRLIBELEVEN') . " " . date($df . ' '.$nowtf, $first) . "\n";
		$msg .= JText::_('VRLIBTWELVE') . " " . date($df . ' '.$nowtf, $second) . "\n";
		$msg .= (!empty($place) ? JText::_('VRRITIROITEM') . ": " . $place . "\n" : "");
		$msg .= (!empty($returnplace) ? JText::_('VRRETURNITEMORD') . ": " . $returnplace . "\n" : "");
		$msg .= JText::_('VRLIBTEN') . ": \n\n";
		foreach ($vricart as $iditem => $itemarrparent) {
			foreach ($itemarrparent as $k => $itemarr) {
				$msg .= $itemarr['info']['name'].($itemarr['itemquant'] > 1 ? " x".$itemarr['itemquant'] : "")."\n";
				$msg .= $itemarr['pricestr']."\n";
				$msg .= $itemarr['optstr']."\n";
				$msg .= "\n";
			}
		}
		if (!empty($maillocfee) && $maillocfee > 0) {
			$msg .= JText::_('VRLOCFEETOPAY') . ": " . self::numberFormat($maillocfee) . " " . $currencyname . "\n\n";
		}
		$msg .= JText::_('VRLIBSIX') . " " . $tot . " " . $currencyname . "\n";
		$msg .= JText::_('VRLIBSEVEN') . ": " . $status . "\n\n";
		$msg .= JText::_('VRLIBTENTHREE') . ": \n" . $link;
		$msg .= (strlen(trim($textfooterordmail)) > 0 ? "\n" . $textfooterordmail : "");
		//
		//html part
		$from_name = $adminemail;
		$from_address = $adminemail;
		$reply_name = $from_name;
		$reply_address = $from_address;
		$reply_address = $from_address;
		$error_delivery_name = $from_name;
		$error_delivery_address = $from_address;
		$to_name = $to;
		$to_address = $to;
		//vikrentitems 1.1
		$tmpl = self::loadEmailTemplate($orderid);
		//
		if (!$useju) {
			require_once ("./components/com_vikrentitems/class/email_message.php");
			$email_message = new email_message_class;
			$email_message->SetEncodedEmailHeader("To", $to_address, $to_name);
			$email_message->SetEncodedEmailHeader("From", $from_address, $from_name);
			$email_message->SetEncodedEmailHeader("Reply-To", $reply_address, $reply_name);
			$email_message->SetHeader("Sender", $from_address);
			//			if (defined("PHP_OS")
			//			&& strcmp(substr(PHP_OS,0,3),"WIN"))
			//				$email_message->SetHeader("Return-Path",$error_delivery_address);

			$email_message->SetEncodedHeader("Subject", $subject);
			$attachlogo = false;
			if (!empty($sitelogo) && is_file(VRI_ADMIN_PATH.DS.'resources'.DS.$sitelogo)) {
				$image = array (
				"FileName" => VRI_ADMIN_URI . "resources/" . $sitelogo, "Content-Type" => "automatic/name", "Disposition" => "inline");
				$email_message->CreateFilePart($image, $image_part);
				$image_content_id = $email_message->GetPartContentID($image_part);
				$attachlogo = true;
			}
			$tlogo = ($attachlogo ? "<img src=\"cid:" . $image_content_id . "\" alt=\"imglogo\"/>\n" : "");
		} else {
			$attachlogo = false;
			if (!empty($sitelogo) && is_file(VRI_ADMIN_PATH.DS.'resources'.DS.$sitelogo)) {
				$attachlogo = true;
			}
			$tlogo = ($attachlogo ? "<img src=\"" . VRI_ADMIN_URI . "resources/" . $sitelogo . "\" alt=\"Logo\"/>\n" : "");
		}
		//vikrentitems 1.1
		$tcname = $ftitle."\n";
		$todate = date($df . ' '.$nowtf, $ts)."\n";
		$tcustdata = nl2br($custdata)."\n";
		$tpickupdate = date($df . ' '.$nowtf, $first)."\n";
		$tdropdate = date($df . ' '.$nowtf, $second)."\n";
		$tpickupplace = (!empty($place) ? $place."\n" : "");
		$tdropplace = (!empty($returnplace) ? $returnplace."\n" : "");
		$tlocfee = $maillocfee;
		$ttot = $tot."\n";
		$tlink = $link;
		$tfootm = $footerordmail;
		$hmess = self::parseEmailTemplate($tmpl, $orderid, $currencyname, $status, $tlogo, $tcname, $todate, $tcustdata, $vricart, $tpickupdate, $tdropdate, $tpickupplace, $tdropplace, $tlocfee, $ttot, $tlink, $tfootm, $strcouponeff, $totdelivery);
		//
		
		if (!$useju) {
			$email_message->CreateQuotedPrintableHTMLPart($hmess, "", $html_part);
			$email_message->CreateQuotedPrintableTextPart($email_message->WrapText($msg), "", $text_part);
			$alternative_parts = array (
				$text_part,
				$html_part
			);
			$email_message->CreateAlternativeMultipart($alternative_parts, $alternative_part);
			$related_parts = array (
				$alternative_part,
				$image_part
			);
			$email_message->AddRelatedMultipart($related_parts);
			$error = $email_message->Send();
			if (strcmp($error, "")) {
				//$msg = utf8_decode($msg);
				@ mail($to, $subject, $msg, "MIME-Version: 1.0" . "\r\n" . "Content-type: text/plain; charset=UTF-8");
			}
		} else {
			//VikRentItems 1.1 PDF
			$attachment = null;
			if ($status == JText::_('VRIOMPLETED') && VikRentItems::sendPDF() && file_exists(VRI_SITE_PATH . DS . "helpers" . DS . "tcpdf" . DS . 'tcpdf.php')) {
				list($pdfcont, $pdfparams) = self::loadPdfTemplate($orderid);
				$pdfhtml = self::parsePdfTemplate($pdfcont, $orderid, $currencyname, $status, $tlogo, $tcname, $todate, $custdata, $vricart, $tpickupdate, $tdropdate, $tpickupplace, $tdropplace, $tlocfee, $ttot, $tlink, $tfootm, $strcouponeff, $totdelivery);
				require_once(VRI_SITE_PATH . DS . "helpers" . DS . "tcpdf" . DS . 'tcpdf.php');
				$savepdfname = VRI_SITE_PATH . DS . "resources" . DS . "pdfs" . DS . $orderid.'_'.$ts.'.pdf';
				if (file_exists($savepdfname)) {
					unlink($savepdfname);
				}
				if (file_exists(VRI_SITE_PATH . DS . "helpers" . DS . "tcpdf" . DS . "fonts" . DS . "dejavusans.php")) {
					$usepdffont = 'dejavusans';
				} else {
					$usepdffont = 'helvetica';
				}
				//Encoding could be also 'ISO-8859-1' rather than 'UTF-8'
				$pdf_page_format = is_array($pdfparams['pdf_page_format']) ? $pdfparams['pdf_page_format'] : constant($pdfparams['pdf_page_format']);
				$pdf = new TCPDF(constant($pdfparams['pdf_page_orientation']), constant($pdfparams['pdf_unit']), $pdf_page_format, true, 'UTF-8', false);
				$pdf->SetTitle($origsubject);
				//Header for each page of the pdf. Img, Img width (default 30mm), Title, Subtitle
				if ($pdfparams['show_header'] == 1 && count($pdfparams['header_data']) > 0) {
					$pdf->SetHeaderData($pdfparams['header_data'][0], $pdfparams['header_data'][1], $pdfparams['header_data'][2], $pdfparams['header_data'][3], $pdfparams['header_data'][4], $pdfparams['header_data'][5]);
				}
				//Change some currencies to their unicode (decimal) value
				$unichr_map = array('EUR' => 8364, 'USD' => 36, 'AUD' => 36, 'CAD' => 36, 'GBP' => 163);
				if (array_key_exists($currencyname, $unichr_map)) {
					$pdfhtml = str_replace($currencyname, $pdf->unichr($unichr_map[$currencyname]), $pdfhtml);
				}
				//header and footer fonts
				$pdf->setHeaderFont(array($usepdffont, '', $pdfparams['header_font_size']));
				$pdf->setFooterFont(array($usepdffont, '', $pdfparams['footer_font_size']));
				//margins
				$pdf->SetMargins(constant($pdfparams['pdf_margin_left']), constant($pdfparams['pdf_margin_top']), constant($pdfparams['pdf_margin_right']));
				$pdf->SetHeaderMargin(constant($pdfparams['pdf_margin_header']));
				$pdf->SetFooterMargin(constant($pdfparams['pdf_margin_footer']));
				//
				$pdf->SetAutoPageBreak(true, constant($pdfparams['pdf_margin_bottom']));
				$pdf->setImageScale(constant($pdfparams['pdf_image_scale_ratio']));
				$pdf->SetFont($usepdffont, '', (int)$pdfparams['body_font_size']);
				//
				if ($pdfparams['show_header'] == 0 || !(count($pdfparams['header_data']) > 0)) {
					$pdf->SetPrintHeader(false);
				}
				if ($pdfparams['show_footer'] == 0) {
					$pdf->SetPrintFooter(false);
				}
				//
				$pdfhtmlpages = explode('{vri_add_pdf_page}', $pdfhtml);
				foreach ($pdfhtmlpages as $htmlpage) {
					if (strlen(str_replace(' ', '', trim($htmlpage))) > 0) {
						$pdf->AddPage();
						$pdf->writeHTML($htmlpage, true, false, true, false, '');
						$pdf->lastPage();
					}
				}
				$pdf->Output($savepdfname, 'F');
				$attachment = $savepdfname;
			}
			//end VikRentItems 1.1 PDF
			$hmess = '<html>'."\n".'<head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8"></head>'."\n".'<body>'.$hmess.'</body>'."\n".'</html>';
			$mailer = JFactory::getMailer();
			$adsendermail = self::getSenderMail();
			$sender = array($adsendermail, $adsendermail);
			//$sender = array($from_address, $from_name);
			$mailer->setSender($sender);
			$mailer->addRecipient($to);
			$mailer->addReplyTo($reply_address);
			if ($attachment) {
				$mailer->addAttachment($attachment);
			}
			$mailer->setSubject($subject);
			$mailer->setBody($hmess);
			$mailer->isHTML(true);
			$mailer->Encoding = 'base64';
			$mailer->Send();
		}
		//
		
		return true;
	}
public static function updatebusy_pdf_lib($to, $subject, $ftitle, $ts, $custdata, $vricart, $first, $second, $tot, $link, $status, $place = "", $returnplace = "", $maillocfee = "", $orderid = "", $strcouponeff = "", $sendpdf = true, $totdelivery = 0) {
		//this function is called in the administrator site
		$origsubject = $subject;
		$subject = '=?UTF-8?B?' . base64_encode($subject) . '?=';
		$dbo = JFactory::getDBO();
		$vri_tn = self::getTranslator();
		$q = "SELECT `setting` FROM `#__vikrentitems_config` WHERE `param`='currencyname';";
		$dbo->setQuery($q);
		$dbo->execute();
		$currencyname = $dbo->loadResult();
		$q = "SELECT `setting` FROM `#__vikrentitems_config` WHERE `param`='adminemail';";
		$dbo->setQuery($q);
		$dbo->execute();
		$adminemail = $dbo->loadResult();
		$q = "SELECT `id`,`setting` FROM `#__vikrentitems_texts` WHERE `param`='footerordmail';";
		$dbo->setQuery($q);
		$dbo->execute();
		$ft = $dbo->loadAssocList();
		$vri_tn->translateContents($ft, '#__vikrentitems_texts');
		$q = "SELECT `id`,`setting` FROM `#__vikrentitems_config` WHERE `param`='sendjutility';";
		$dbo->setQuery($q);
		$dbo->execute();
		$sendmethod = $dbo->loadAssocList();
		$useju = intval($sendmethod[0]['setting']) == 1 ? true : false;
		$q = "SELECT `setting` FROM `#__vikrentitems_config` WHERE `param`='sitelogo';";
		$dbo->setQuery($q);
		$dbo->execute();
		$sitelogo = $dbo->loadResult();
		$q = "SELECT `setting` FROM `#__vikrentitems_config` WHERE `param`='dateformat';";
		$dbo->setQuery($q);
		$dbo->execute();
		$formdate = $dbo->loadResult();
		if ($formdate == "%d/%m/%Y") {
			$df = 'd/m/Y';
		} elseif ($formdate == "%m/%d/%Y") {
			$df = 'm/d/Y';
		} else {
			$df = 'Y/m/d';
		}
		$nowtf = VikRentItems::getTimeFormat();
		$footerordmail = $ft[0]['setting'];
		$textfooterordmail = strip_tags($footerordmail);
		//text part
		$msg = $ftitle . "\n\n";
		$msg .= JText::_('VRLIBEIGHT') . " " . date($df . ' '.$nowtf, $ts) . "\n";
		$msg .= JText::_('VRLIBNINE') . ":\n" . $custdata . "\n";
		$msg .= JText::_('VRLIBELEVEN') . " " . date($df . ' '.$nowtf, $first) . "\n";
		$msg .= JText::_('VRLIBTWELVE') . " " . date($df . ' '.$nowtf, $second) . "\n";
		$msg .= (!empty($place) ? JText::_('VRRITIROITEM') . ": " . $place . "\n" : "");
		$msg .= (!empty($returnplace) ? JText::_('VRRETURNITEMORD') . ": " . $returnplace . "\n" : "");
		$msg .= JText::_('VRLIBTEN') . ": \n\n";
		foreach ($vricart as $iditem => $itemarrparent) {
			foreach ($itemarrparent as $k => $itemarr) {
				$msg .= $itemarr['info']['name'].($itemarr['itemquant'] > 1 ? " x".$itemarr['itemquant'] : "")."\n";
				$msg .= $itemarr['pricestr']."\n";
				$msg .= $itemarr['optstr']."\n";
				$msg .= "\n";
			}
		}
		if (!empty($maillocfee) && $maillocfee > 0) {
			$msg .= JText::_('VRLOCFEETOPAY') . ": " . self::numberFormat($maillocfee) . " " . $currencyname . "\n\n";
		}
		$msg .= JText::_('VRLIBSIX') . " " . $tot . " " . $currencyname . "\n";
		$msg .= JText::_('VRLIBSEVEN') . ": " . $status . "\n\n";
		$msg .= JText::_('VRLIBTENTHREE') . ": \n" . $link;
		$msg .= (strlen(trim($textfooterordmail)) > 0 ? "\n" . $textfooterordmail : "");
		//
		//html part
		$from_name = $adminemail;
		$from_address = $adminemail;
		$reply_name = $from_name;
		$reply_address = $from_address;
		$reply_address = $from_address;
		$error_delivery_name = $from_name;
		$error_delivery_address = $from_address;
		$to_name = $to;
		$to_address = $to;
		//vikrentitems 1.1
		$tmpl = self::loadEmailTemplate($orderid);
		//
		if (!$useju) {
			require_once ("../components/com_vikrentitems/class/email_message.php");
			$email_message = new email_message_class;
			$email_message->SetEncodedEmailHeader("To", $to_address, $to_name);
			$email_message->SetEncodedEmailHeader("From", $from_address, $from_name);
			$email_message->SetEncodedEmailHeader("Reply-To", $reply_address, $reply_name);
			$email_message->SetHeader("Sender", $from_address);
			//			if (defined("PHP_OS")
			//			&& strcmp(substr(PHP_OS,0,3),"WIN"))
			//				$email_message->SetHeader("Return-Path",$error_delivery_address);

			$email_message->SetEncodedHeader("Subject", $subject);
			$attachlogo = false;
			if (!empty($sitelogo) && is_file(VRI_ADMIN_PATH.DS.'resources'.DS.$sitelogo)) {
				$image = array (
				"FileName" => VRI_ADMIN_URI . "resources/" . $sitelogo, "Content-Type" => "automatic/name", "Disposition" => "inline");
				$email_message->CreateFilePart($image, $image_part);
				$image_content_id = $email_message->GetPartContentID($image_part);
				$attachlogo = true;
			}
			$tlogo = ($attachlogo ? "<img src=\"cid:" . $image_content_id . "\" alt=\"Logo\"/>\n" : "");
		} else {
			$attachlogo = false;
			if (!empty($sitelogo) && is_file(VRI_ADMIN_PATH.DS.'resources'.DS.$sitelogo)) {
				$attachlogo = true;
			}
			$tlogo = ($attachlogo ? "<img src=\"" . VRI_ADMIN_URI . "resources/" . $sitelogo . "\" alt=\"Logo\"/>\n" : "");
		}
		//vikrentitems 1.1
		$tcname = $ftitle."\n";
		$todate = date($df . ' '.$nowtf, $ts)."\n";
		$tcustdata = nl2br($custdata)."\n";
		$tpickupdate = date($df . ' '.$nowtf, $first)."\n";
		$tdropdate = date($df . ' '.$nowtf, $second)."\n";
		$tpickupplace = (!empty($place) ? $place."\n" : "");
		$tdropplace = (!empty($returnplace) ? $returnplace."\n" : "");
		$tlocfee = $maillocfee;
		$ttot = $tot."\n";
		$tlink = $link;
		$tfootm = $footerordmail;
		$hmess = self::parseEmailTemplate($tmpl, $orderid, $currencyname, $status, $tlogo, $tcname, $todate, $tcustdata, $vricart, $tpickupdate, $tdropdate, $tpickupplace, $tdropplace, $tlocfee, $ttot, $tlink, $tfootm, $strcouponeff, $totdelivery);
		//
		
		if (!$useju) {
			$email_message->CreateQuotedPrintableHTMLPart($hmess, "", $html_part);
			$email_message->CreateQuotedPrintableTextPart($email_message->WrapText($msg), "", $text_part);
			$alternative_parts = array (
				$text_part,
				$html_part
			);
			$email_message->CreateAlternativeMultipart($alternative_parts, $alternative_part);
			$related_parts = array (
				$alternative_part,
				$image_part
			);
			$email_message->AddRelatedMultipart($related_parts);
			$error = $email_message->Send();
			if (strcmp($error, "")) {
				//$msg = utf8_decode($msg);
				@ mail($to, $subject, $msg, "MIME-Version: 1.0" . "\r\n" . "Content-type: text/plain; charset=UTF-8");
			}
		} else {
			//VikRentItems 1.1 PDF
			$attachment = null;
			if ($status == JText::_('VRIOMPLETED') && $sendpdf && VikRentItems::sendPDF() && file_exists(VRI_SITE_PATH . DS . "helpers" . DS . "tcpdf" . DS . 'tcpdf.php')) {
				list($pdfcont, $pdfparams) = self::loadPdfTemplate($orderid);
				$pdfhtml = self::parsePdfTemplate($pdfcont, $orderid, $currencyname, $status, $tlogo, $tcname, $todate, $custdata, $vricart, $tpickupdate, $tdropdate, $tpickupplace, $tdropplace, $tlocfee, $ttot, $tlink, $tfootm, $strcouponeff, $totdelivery);
				//images with src images/ must be converted into ../images/ for the PDF
				$pdfhtml = str_replace('<img src="images/', '<img src="../images/', $pdfhtml);
				//
				require_once(VRI_SITE_PATH . DS . "helpers" . DS . "tcpdf" . DS . 'tcpdf.php');
				$savepdfname = VRI_SITE_PATH . DS . "resources" . DS . "pdfs" . DS . $orderid.'_'.$ts.'.pdf';
				if (file_exists($savepdfname)) {
					unlink($savepdfname);
				}
				if (file_exists(VRI_SITE_PATH . DS . "helpers" . DS . "tcpdf" . DS . "fonts" . DS . "dejavusans.php")) {
					$usepdffont = 'dejavusans';
				} else {
					$usepdffont = 'helvetica';
				}
				//Encoding could be also 'ISO-8859-1' rather than 'UTF-8'
				$pdf_page_format = is_array($pdfparams['pdf_page_format']) ? $pdfparams['pdf_page_format'] : constant($pdfparams['pdf_page_format']);
				$pdf = new TCPDF(constant($pdfparams['pdf_page_orientation']), constant($pdfparams['pdf_unit']), $pdf_page_format, true, 'UTF-8', false);
				$pdf->SetTitle($origsubject);
				//Header for each page of the pdf. Img, Img width (default 30mm), Title, Subtitle
				if ($pdfparams['show_header'] == 1 && count($pdfparams['header_data']) > 0) {
					$pdf->SetHeaderData($pdfparams['header_data'][0], $pdfparams['header_data'][1], $pdfparams['header_data'][2], $pdfparams['header_data'][3], $pdfparams['header_data'][4], $pdfparams['header_data'][5]);
				}
				//Change some currencies to their unicode (decimal) value
				$unichr_map = array('EUR' => 8364, 'USD' => 36, 'AUD' => 36, 'CAD' => 36, 'GBP' => 163);
				if (array_key_exists($currencyname, $unichr_map)) {
					$pdfhtml = str_replace($currencyname, $pdf->unichr($unichr_map[$currencyname]), $pdfhtml);
				}
				//header and footer fonts
				$pdf->setHeaderFont(array($usepdffont, '', $pdfparams['header_font_size']));
				$pdf->setFooterFont(array($usepdffont, '', $pdfparams['footer_font_size']));
				//margins
				$pdf->SetMargins(constant($pdfparams['pdf_margin_left']), constant($pdfparams['pdf_margin_top']), constant($pdfparams['pdf_margin_right']));
				$pdf->SetHeaderMargin(constant($pdfparams['pdf_margin_header']));
				$pdf->SetFooterMargin(constant($pdfparams['pdf_margin_footer']));
				//
				$pdf->SetAutoPageBreak(true, constant($pdfparams['pdf_margin_bottom']));
				$pdf->setImageScale(constant($pdfparams['pdf_image_scale_ratio']));
				$pdf->SetFont($usepdffont, '', (int)$pdfparams['body_font_size']);
				//
				if ($pdfparams['show_header'] == 0 || !(count($pdfparams['header_data']) > 0)) {
					$pdf->SetPrintHeader(false);
				}
				if ($pdfparams['show_footer'] == 0) {
					$pdf->SetPrintFooter(false);
				}
				//
				$pdfhtmlpages = explode('{vri_add_pdf_page}', $pdfhtml);
				foreach ($pdfhtmlpages as $htmlpage) {
					if (strlen(str_replace(' ', '', trim($htmlpage))) > 0) {
						$pdf->AddPage();
						$pdf->writeHTML($htmlpage, true, false, true, false, '');
						$pdf->lastPage();
					}
				}
				$pdf->Output($savepdfname, 'F');
				$attachment = $savepdfname;
			}
			//end VikRentItems 1.1 PDF
			
		}
		//
		
		return true;
	}





public static function recreatePDF($to, $subject, $ftitle, $ts, $custdata, $vricart, $first, $second, $tot, $link, $status, $place = "", $returnplace = "", $maillocfee = "", $orderid = "", $strcouponeff = "", $sendpdf = true, $totdelivery = 0, $type = "") {
        //this function is called in the administrator site
        $origsubject = $subject;
        $subject = '=?UTF-8?B?' . base64_encode($subject) . '?=';
        $dbo = JFactory::getDBO();
        $vri_tn = self::getTranslator();
        $q = "SELECT `setting` FROM `#__vikrentitems_config` WHERE `param`='currencyname';";
        $dbo->setQuery($q);
        $dbo->execute();
        $currencyname = $dbo->loadResult();
        $q = "SELECT `setting` FROM `#__vikrentitems_config` WHERE `param`='adminemail';";
        $dbo->setQuery($q);
        $dbo->execute();
        $adminemail = $dbo->loadResult();
        $q = "SELECT `id`,`setting` FROM `#__vikrentitems_texts` WHERE `param`='footerordmail';";
        $dbo->setQuery($q);
        $dbo->execute();
        $ft = $dbo->loadAssocList();
        $vri_tn->translateContents($ft, '#__vikrentitems_texts');
        $q = "SELECT `id`,`setting` FROM `#__vikrentitems_config` WHERE `param`='sendjutility';";
        $dbo->setQuery($q);
        $dbo->execute();
        $sendmethod = $dbo->loadAssocList();
        $useju = intval($sendmethod[0]['setting']) == 1 ? true : false;
        $q = "SELECT `setting` FROM `#__vikrentitems_config` WHERE `param`='sitelogo';";
        $dbo->setQuery($q);
        $dbo->execute();
        $sitelogo = $dbo->loadResult();
        $q = "SELECT `setting` FROM `#__vikrentitems_config` WHERE `param`='dateformat';";
        $dbo->setQuery($q);
        $dbo->execute();
        $formdate = $dbo->loadResult();
        if ($formdate == "%d/%m/%Y") {
            $df = 'd/m/Y';
        } elseif ($formdate == "%m/%d/%Y") {
            $df = 'm/d/Y';
        } else {
            $df = 'Y/m/d';
        }
        $nowtf = VikRentItems::getTimeFormat();
        $footerordmail = $ft[0]['setting'];
        $textfooterordmail = strip_tags($footerordmail);
        //text part
        $msg = $ftitle . "\n\n";
        $msg .= JText::_('VRLIBEIGHT') . " " . date($df . ' '.$nowtf, $ts) . "\n";
        $msg .= JText::_('VRLIBNINE') . ":\n" . $custdata . "\n";
        $msg .= JText::_('VRLIBELEVEN') . " " . date($df . ' '.$nowtf, $first) . "\n";
        $msg .= JText::_('VRLIBTWELVE') . " " . date($df . ' '.$nowtf, $second) . "\n";
        $msg .= (!empty($place) ? JText::_('VRRITIROITEM') . ": " . $place . "\n" : "");
        $msg .= (!empty($returnplace) ? JText::_('VRRETURNITEMORD') . ": " . $returnplace . "\n" : "");
        $msg .= JText::_('VRLIBTEN') . ": \n\n";
        foreach ($vricart as $iditem => $itemarrparent) {
            foreach ($itemarrparent as $k => $itemarr) {
                $msg .= $itemarr['info']['name'].($itemarr['itemquant'] > 1 ? " x".$itemarr['itemquant'] : "")."\n";
                $msg .= $itemarr['pricestr']."\n";
                $msg .= $itemarr['optstr']."\n";
                $msg .= "\n";
            }
        }
        if (!empty($maillocfee) && $maillocfee > 0) {
            $msg .= JText::_('VRLOCFEETOPAY') . ": " . self::numberFormat($maillocfee) . " " . $currencyname . "\n\n";
        }
        $msg .= JText::_('VRLIBSIX') . " " . $tot . " " . $currencyname . "\n";
        $msg .= JText::_('VRLIBSEVEN') . ": " . $status . "\n\n";
        $msg .= JText::_('VRLIBTENTHREE') . ": \n" . $link;
        $msg .= (strlen(trim($textfooterordmail)) > 0 ? "\n" . $textfooterordmail : "");
        //
        //html part
        $from_name = $adminemail;
        $from_address = $adminemail;
        $reply_name = $from_name;
        $reply_address = $from_address;
        $reply_address = $from_address;
        $error_delivery_name = $from_name;
        $error_delivery_address = $from_address;
        $to_name = $to;
        $to_address = $to;
        //vikrentitems 1.1
        $tmpl = self::loadEmailTemplate($orderid);

        //vikrentitems 1.1
        $tcname = $ftitle."\n";
        $todate = date($df . ' '.$nowtf, $ts)."\n";
        $tcustdata = nl2br($custdata)."\n";
        $tpickupdate = date($df . ' '.$nowtf, $first)."\n";
        $tdropdate = date($df . ' '.$nowtf, $second)."\n";
        $tpickupplace = (!empty($place) ? $place."\n" : "");
        $tdropplace = (!empty($returnplace) ? $returnplace."\n" : "");
        $tlocfee = $maillocfee;
        $ttot = $tot."\n";
        $tlink = $link;
        $tfootm = $footerordmail;
//        $hmess = self::parseEmailTemplate($tmpl, $orderid, $currencyname, $status, $tlogo, $tcname, $todate, $tcustdata, $vricart, $tpickupdate, $tdropdate, $tpickupplace, $tdropplace, $tlocfee, $ttot, $tlink, $tfootm, $strcouponeff, $totdelivery);
        //

        if (!$useju) {
//            $email_message->CreateQuotedPrintableHTMLPart($hmess, "", $html_part);
//            $email_message->CreateQuotedPrintableTextPart($email_message->WrapText($msg), "", $text_part);
//            $alternative_parts = array (
//                $text_part,
//                $html_part
//            );
//            $email_message->CreateAlternativeMultipart($alternative_parts, $alternative_part);
//            $related_parts = array (
//                $alternative_part,
//                $image_part
//            );
//            $email_message->AddRelatedMultipart($related_parts);
//            $error = $email_message->Send();
//            if (strcmp($error, "")) {
//                //$msg = utf8_decode($msg);
//                @ mail($to, $subject, $msg, "MIME-Version: 1.0" . "\r\n" . "Content-type: text/plain; charset=UTF-8");
//            }
        } else {
            //VikRentItems 1.1 PDF
            $attachment = null;
            if ($status == JText::_('VRIOMPLETED') && $sendpdf && VikRentItems::sendPDF() && file_exists(VRI_SITE_PATH . DS . "helpers" . DS . "tcpdf" . DS . 'tcpdf.php')) {
                list($pdfcont, $pdfparams) = self::loadPdfTemplate($orderid);
                $pdfhtml = self::parsePdfTemplate($pdfcont, $orderid, $currencyname, $status, $tlogo, $tcname, $todate, $custdata, $vricart, $tpickupdate, $tdropdate, $tpickupplace, $tdropplace, $tlocfee, $ttot, $tlink, $tfootm, $strcouponeff, $totdelivery);
                //images with src images/ must be converted into ../images/ for the PDF
                $pdfhtml = str_replace('<img src="images/', '<img src="../images/', $pdfhtml);
                //
                require_once(VRI_SITE_PATH . DS . "helpers" . DS . "tcpdf" . DS . 'tcpdf.php');
                $savepdfname = VRI_SITE_PATH . DS . "resources" . DS . "pdfs" . DS . $orderid.'_'.$ts.'.pdf';
                if (file_exists($savepdfname)) {
                    unlink($savepdfname);
                }
                if (file_exists(VRI_SITE_PATH . DS . "helpers" . DS . "tcpdf" . DS . "fonts" . DS . "dejavusans.php")) {
                    $usepdffont = 'dejavusans';
                } else {
                    $usepdffont = 'helvetica';
                }
                //Encoding could be also 'ISO-8859-1' rather than 'UTF-8'
                $pdf_page_format = is_array($pdfparams['pdf_page_format']) ? $pdfparams['pdf_page_format'] : constant($pdfparams['pdf_page_format']);
                $pdf = new TCPDF(constant($pdfparams['pdf_page_orientation']), constant($pdfparams['pdf_unit']), $pdf_page_format, true, 'UTF-8', false);
                $pdf->SetTitle($origsubject);
                //Header for each page of the pdf. Img, Img width (default 30mm), Title, Subtitle
                if ($pdfparams['show_header'] == 1 && count($pdfparams['header_data']) > 0) {
                    $pdf->SetHeaderData($pdfparams['header_data'][0], $pdfparams['header_data'][1], $pdfparams['header_data'][2], $pdfparams['header_data'][3], $pdfparams['header_data'][4], $pdfparams['header_data'][5]);
                }
                //Change some currencies to their unicode (decimal) value
                $unichr_map = array('EUR' => 8364, 'USD' => 36, 'AUD' => 36, 'CAD' => 36, 'GBP' => 163);
                if (array_key_exists($currencyname, $unichr_map)) {
                    $pdfhtml = str_replace($currencyname, $pdf->unichr($unichr_map[$currencyname]), $pdfhtml);
                }
                //header and footer fonts
                $pdf->setHeaderFont(array($usepdffont, '', $pdfparams['header_font_size']));
                $pdf->setFooterFont(array($usepdffont, '', $pdfparams['footer_font_size']));
                //margins
                $pdf->SetMargins(constant($pdfparams['pdf_margin_left']), constant($pdfparams['pdf_margin_top']), constant($pdfparams['pdf_margin_right']));
                $pdf->SetHeaderMargin(constant($pdfparams['pdf_margin_header']));
                $pdf->SetFooterMargin(constant($pdfparams['pdf_margin_footer']));
                //
                $pdf->SetAutoPageBreak(true, constant($pdfparams['pdf_margin_bottom']));
                $pdf->setImageScale(constant($pdfparams['pdf_image_scale_ratio']));
                $pdf->SetFont($usepdffont, '', (int)$pdfparams['body_font_size']);
                //
                if ($pdfparams['show_header'] == 0 || !(count($pdfparams['header_data']) > 0)) {
                    $pdf->SetPrintHeader(false);
                }
                if ($pdfparams['show_footer'] == 0) {
                    $pdf->SetPrintFooter(false);
                }
                //
                $pdfhtmlpages = explode('{vri_add_pdf_page}', $pdfhtml);
                foreach ($pdfhtmlpages as $htmlpage) {
                    if (strlen(str_replace(' ', '', trim($htmlpage))) > 0) {
                        $pdf->AddPage();
                        $pdf->writeHTML($htmlpage, true, false, true, false, '');
                        $pdf->lastPage();
                    }
                }
                $pdf->Output($savepdfname, 'F');
                $attachment = $savepdfname;
            }

        }
        return true;
    }





	public static function sendCustMailFromBack($to, $subject, $ftitle, $ts, $custdata, $vricart, $first, $second, $tot, $link, $status, $place = "", $returnplace = "", $maillocfee = "", $orderid = "", $strcouponeff = "", $sendpdf = true, $totdelivery = 0, $type = "") {
		//this function is called in the administrator site
		$origsubject = $subject;
		$subject = '=?UTF-8?B?' . base64_encode($subject) . '?=';
		$dbo = JFactory::getDBO();
		$vri_tn = self::getTranslator();
		$q = "SELECT `setting` FROM `#__vikrentitems_config` WHERE `param`='currencyname';";
		$dbo->setQuery($q);
		$dbo->execute();
		$currencyname = $dbo->loadResult();
		$q = "SELECT `setting` FROM `#__vikrentitems_config` WHERE `param`='adminemail';";
		$dbo->setQuery($q);
		$dbo->execute();
		$adminemail = $dbo->loadResult();
		$q = "SELECT `id`,`setting` FROM `#__vikrentitems_texts` WHERE `param`='footerordmail';";
		$dbo->setQuery($q);
		$dbo->execute();
		$ft = $dbo->loadAssocList();
		$vri_tn->translateContents($ft, '#__vikrentitems_texts');
		$q = "SELECT `id`,`setting` FROM `#__vikrentitems_config` WHERE `param`='sendjutility';";
		$dbo->setQuery($q);
		$dbo->execute();
		$sendmethod = $dbo->loadAssocList();
		$useju = intval($sendmethod[0]['setting']) == 1 ? true : false;
		$q = "SELECT `setting` FROM `#__vikrentitems_config` WHERE `param`='sitelogo';";
		$dbo->setQuery($q);
		$dbo->execute();
		$sitelogo = $dbo->loadResult();
		$q = "SELECT `setting` FROM `#__vikrentitems_config` WHERE `param`='dateformat';";
		$dbo->setQuery($q);
		$dbo->execute();
		$formdate = $dbo->loadResult();
		if ($formdate == "%d/%m/%Y") {
			$df = 'd/m/Y';
		} elseif ($formdate == "%m/%d/%Y") {
			$df = 'm/d/Y';
		} else {
			$df = 'Y/m/d';
		}
		$nowtf = VikRentItems::getTimeFormat();
		$footerordmail = $ft[0]['setting'];
		$textfooterordmail = strip_tags($footerordmail);
		//text part
		$msg = $ftitle . "\n\n";
		$msg .= JText::_('VRLIBEIGHT') . " " . date($df . ' '.$nowtf, $ts) . "\n";
		$msg .= JText::_('VRLIBNINE') . ":\n" . $custdata . "\n";
		$msg .= JText::_('VRLIBELEVEN') . " " . date($df . ' '.$nowtf, $first) . "\n";
		$msg .= JText::_('VRLIBTWELVE') . " " . date($df . ' '.$nowtf, $second) . "\n";
		$msg .= (!empty($place) ? JText::_('VRRITIROITEM') . ": " . $place . "\n" : "");
		$msg .= (!empty($returnplace) ? JText::_('VRRETURNITEMORD') . ": " . $returnplace . "\n" : "");
		$msg .= JText::_('VRLIBTEN') . ": \n\n";
		foreach ($vricart as $iditem => $itemarrparent) {
			foreach ($itemarrparent as $k => $itemarr) {
				$msg .= $itemarr['info']['name'].($itemarr['itemquant'] > 1 ? " x".$itemarr['itemquant'] : "")."\n";
				$msg .= $itemarr['pricestr']."\n";
				$msg .= $itemarr['optstr']."\n";
				$msg .= "\n";
			}
		}
		if (!empty($maillocfee) && $maillocfee > 0) {
			$msg .= JText::_('VRLOCFEETOPAY') . ": " . self::numberFormat($maillocfee) . " " . $currencyname . "\n\n";
		}
		$msg .= JText::_('VRLIBSIX') . " " . $tot . " " . $currencyname . "\n";
		$msg .= JText::_('VRLIBSEVEN') . ": " . $status . "\n\n";
		$msg .= JText::_('VRLIBTENTHREE') . ": \n" . $link;
		$msg .= (strlen(trim($textfooterordmail)) > 0 ? "\n" . $textfooterordmail : "");
		//
		//html part
		$from_name = $adminemail;
		$from_address = $adminemail;
		$reply_name = $from_name;
		$reply_address = $from_address;
		$reply_address = $from_address;
		$error_delivery_name = $from_name;
		$error_delivery_address = $from_address;
		$to_name = $to;
		$to_address = $to;
		//vikrentitems 1.1
		$tmpl = self::loadEmailTemplate($orderid);
		//
		if (!$useju) {
			require_once ("../components/com_vikrentitems/class/email_message.php");
			$email_message = new email_message_class;
			$email_message->SetEncodedEmailHeader("To", $to_address, $to_name);
			$email_message->SetEncodedEmailHeader("From", $from_address, $from_name);
			$email_message->SetEncodedEmailHeader("Reply-To", $reply_address, $reply_name);
			$email_message->SetHeader("Sender", $from_address);
			//			if (defined("PHP_OS")
			//			&& strcmp(substr(PHP_OS,0,3),"WIN"))
			//				$email_message->SetHeader("Return-Path",$error_delivery_address);

			$email_message->SetEncodedHeader("Subject", $subject);
			$attachlogo = false;
			if (!empty($sitelogo) && is_file(VRI_ADMIN_PATH.DS.'resources'.DS.$sitelogo)) {
				$image = array (
				"FileName" => VRI_ADMIN_URI . "resources/" . $sitelogo, "Content-Type" => "automatic/name", "Disposition" => "inline");
				$email_message->CreateFilePart($image, $image_part);
				$image_content_id = $email_message->GetPartContentID($image_part);
				$attachlogo = true;
			}
			$tlogo = ($attachlogo ? "<img src=\"cid:" . $image_content_id . "\" alt=\"Logo\"/>\n" : "");
		} else {
			$attachlogo = false;
			if (!empty($sitelogo) && is_file(VRI_ADMIN_PATH.DS.'resources'.DS.$sitelogo)) {
				$attachlogo = true;
			}
			$tlogo = ($attachlogo ? "<img src=\"" . VRI_ADMIN_URI . "resources/" . $sitelogo . "\" alt=\"Logo\"/>\n" : "");
		}
		//vikrentitems 1.1
		$tcname = $ftitle."\n";
		$todate = date($df . ' '.$nowtf, $ts)."\n";
		$tcustdata = nl2br($custdata)."\n";
		$tpickupdate = date($df . ' '.$nowtf, $first)."\n";
		$tdropdate = date($df . ' '.$nowtf, $second)."\n";
		$tpickupplace = (!empty($place) ? $place."\n" : "");
		$tdropplace = (!empty($returnplace) ? $returnplace."\n" : "");
		$tlocfee = $maillocfee;
		$ttot = $tot."\n";
		$tlink = $link;
		$tfootm = $footerordmail;
		$hmess = self::parseEmailTemplate($tmpl, $orderid, $currencyname, $status, $tlogo, $tcname, $todate, $tcustdata, $vricart, $tpickupdate, $tdropdate, $tpickupplace, $tdropplace, $tlocfee, $ttot, $tlink, $tfootm, $strcouponeff, $totdelivery);
		//
		
		if (!$useju) {
			$email_message->CreateQuotedPrintableHTMLPart($hmess, "", $html_part);
			$email_message->CreateQuotedPrintableTextPart($email_message->WrapText($msg), "", $text_part);
			$alternative_parts = array (
				$text_part,
				$html_part
			);
			$email_message->CreateAlternativeMultipart($alternative_parts, $alternative_part);
			$related_parts = array (
				$alternative_part,
				$image_part
			);
			$email_message->AddRelatedMultipart($related_parts);
			$error = $email_message->Send();
			if (strcmp($error, "")) {
				//$msg = utf8_decode($msg);
				@ mail($to, $subject, $msg, "MIME-Version: 1.0" . "\r\n" . "Content-type: text/plain; charset=UTF-8");
			}
		} else {
			//VikRentItems 1.1 PDF
			$attachment = null;
			if ($status == JText::_('VRIOMPLETED') && $sendpdf && VikRentItems::sendPDF() && file_exists(VRI_SITE_PATH . DS . "helpers" . DS . "tcpdf" . DS . 'tcpdf.php')) {
				list($pdfcont, $pdfparams) = self::loadPdfTemplate($orderid);
				$pdfhtml = self::parsePdfTemplate($pdfcont, $orderid, $currencyname, $status, $tlogo, $tcname, $todate, $custdata, $vricart, $tpickupdate, $tdropdate, $tpickupplace, $tdropplace, $tlocfee, $ttot, $tlink, $tfootm, $strcouponeff, $totdelivery);
				//images with src images/ must be converted into ../images/ for the PDF
				$pdfhtml = str_replace('<img src="images/', '<img src="../images/', $pdfhtml);
				//
				require_once(VRI_SITE_PATH . DS . "helpers" . DS . "tcpdf" . DS . 'tcpdf.php');
				$savepdfname = VRI_SITE_PATH . DS . "resources" . DS . "pdfs" . DS . $orderid.'_'.$ts.'.pdf';
				if (file_exists($savepdfname)) {
					unlink($savepdfname);
				}
				if (file_exists(VRI_SITE_PATH . DS . "helpers" . DS . "tcpdf" . DS . "fonts" . DS . "dejavusans.php")) {
					$usepdffont = 'dejavusans';
				} else {
					$usepdffont = 'helvetica';
				}
				//Encoding could be also 'ISO-8859-1' rather than 'UTF-8'
				$pdf_page_format = is_array($pdfparams['pdf_page_format']) ? $pdfparams['pdf_page_format'] : constant($pdfparams['pdf_page_format']);
				$pdf = new TCPDF(constant($pdfparams['pdf_page_orientation']), constant($pdfparams['pdf_unit']), $pdf_page_format, true, 'UTF-8', false);
				$pdf->SetTitle($origsubject);
				//Header for each page of the pdf. Img, Img width (default 30mm), Title, Subtitle
				if ($pdfparams['show_header'] == 1 && count($pdfparams['header_data']) > 0) {
					$pdf->SetHeaderData($pdfparams['header_data'][0], $pdfparams['header_data'][1], $pdfparams['header_data'][2], $pdfparams['header_data'][3], $pdfparams['header_data'][4], $pdfparams['header_data'][5]);
				}
				//Change some currencies to their unicode (decimal) value
				$unichr_map = array('EUR' => 8364, 'USD' => 36, 'AUD' => 36, 'CAD' => 36, 'GBP' => 163);
				if (array_key_exists($currencyname, $unichr_map)) {
					$pdfhtml = str_replace($currencyname, $pdf->unichr($unichr_map[$currencyname]), $pdfhtml);
				}
				//header and footer fonts
				$pdf->setHeaderFont(array($usepdffont, '', $pdfparams['header_font_size']));
				$pdf->setFooterFont(array($usepdffont, '', $pdfparams['footer_font_size']));
				//margins
				$pdf->SetMargins(constant($pdfparams['pdf_margin_left']), constant($pdfparams['pdf_margin_top']), constant($pdfparams['pdf_margin_right']));
				$pdf->SetHeaderMargin(constant($pdfparams['pdf_margin_header']));
				$pdf->SetFooterMargin(constant($pdfparams['pdf_margin_footer']));
				//
				$pdf->SetAutoPageBreak(true, constant($pdfparams['pdf_margin_bottom']));
				$pdf->setImageScale(constant($pdfparams['pdf_image_scale_ratio']));
				$pdf->SetFont($usepdffont, '', (int)$pdfparams['body_font_size']);
				//
				if ($pdfparams['show_header'] == 0 || !(count($pdfparams['header_data']) > 0)) {
					$pdf->SetPrintHeader(false);
				}
				if ($pdfparams['show_footer'] == 0) {
					$pdf->SetPrintFooter(false);
				}
				//
				$pdfhtmlpages = explode('{vri_add_pdf_page}', $pdfhtml);
				foreach ($pdfhtmlpages as $htmlpage) {
					if (strlen(str_replace(' ', '', trim($htmlpage))) > 0) {
						$pdf->AddPage();
						$pdf->writeHTML($htmlpage, true, false, true, false, '');
						$pdf->lastPage();
					}
				}
				$pdf->Output($savepdfname, 'F');
				$attachment = $savepdfname;
			}
			//end VikRentItems 1.1 PDF
			$hmess = '<html>'."\n".'<head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8"></head>'."\n".'<body>'.$hmess.'</body>'."\n".'</html>';
			$mailer = JFactory::getMailer();
			$adsendermail = self::getSenderMail();
			$sender = array($adsendermail, $adsendermail);
			//$sender = array($from_address, $from_name);
			$mailer->setSender($sender);
			$mailer->addRecipient($to);
			$mailer->addReplyTo($reply_address);
                        if($type == ""){
                          if ($attachment) {
				$mailer->addAttachment($attachment);
			  }
                        }
			
			$mailer->setSubject($subject);
			$mailer->setBody($hmess);
			$mailer->isHTML(true);
			$mailer->Encoding = 'base64';
			$mailer->Send();
		}
		//
		
		return true;
	}

	public static function parseSpecialTokens($order, $tmpl) {
		$dbo = JFactory::getDBO();
		$vri_tn = self::getTranslator();
		$currency = self::getCurrencyName();
		$vridateformat = self::getDateFormat();
		$nowtf = self::getTimeFormat();
		if ($vridateformat == "%d/%m/%Y") {
			$df = 'd/m/Y';
		} elseif ($vridateformat == "%m/%d/%Y") {
			$df = 'm/d/Y';
		} else {
			$df = 'Y/m/d';
		}
		$parsed = $tmpl;

		$cust_name = '';
		if (!empty($order['customer_name'])) {
			$cust_name = $order['customer_name'];
		}
		$pickloc = '';
		if (!empty($order['idplace'])) {
			$pickloc = self::getPlaceName($order['idplace'], $vri_tn);
		}
		$droploc = '';
		if (!empty($order['idreturnplace'])) {
			$droploc = self::getPlaceName($order['idreturnplace'], $vri_tn);
		}
		$items_name = array();
		if (isset($order['items']) && @count($order['items'])) {
			foreach ($order['items'] as $item) {
				$item_name = '';
				if (isset($item['item_name'])) {
					$item_name = $item['item_name'];
				} elseif (isset($item['name'])) {
					$item_name = $item['name'];
				} elseif (isset($item['iditem'])) {
					$item_info = self::getItemInfo($item['iditem'], $vri_tn);
					if (count($item_info)) {
						$item_name = $item_info['name'];
					}
				}
				if (!empty($item_name)) {
					$items_name[] = $item_name;
				}
			}
		}
		$remaining_bal = $order['order_total'] - (float)$order['totpaid'];

		$parsed = str_replace("{order_id}", $order['id'], $parsed);
		$parsed = str_replace("{customer_name}", $cust_name, $parsed);
		$parsed = str_replace("{pickup_date}", date($df.' '.$nowtf, $order['ritiro']), $parsed);
		$parsed = str_replace("{dropoff_date}", date($df.' '.$nowtf, $order['consegna']), $parsed);
		$parsed = str_replace("{pickup_place}", $pickloc, $parsed);
		$parsed = str_replace("{dropoff_place}", $droploc, $parsed);
		$parsed = str_replace("{num_days}", $order['days'], $parsed);
		$parsed = str_replace("{items_name}", implode(', ', $items_name), $parsed);
		$parsed = str_replace("{total}", $currency . ' ' . self::numberFormat($order['order_total']), $parsed);
		$parsed = str_replace("{total_paid}", $currency . ' ' . self::numberFormat($order['totpaid']), $parsed);
		$parsed = str_replace("{remaining_balance}", $currency . ' ' . self::numberFormat($remaining_bal), $parsed);

		return $parsed;
	}

	public static function paypalForm($imp, $tax, $sid, $ts, $itemname, $currencysymb = "") {
		$dbo = JFactory::getDBO();
		$depositmess = "";
		$q = "SELECT `setting` FROM `#__vikrentitems_config` WHERE `param`='paytotal';";
		$dbo->setQuery($q);
		$dbo->execute();
		$s = $dbo->loadAssocList();
		if (intval($s[0]['setting']) == 0) {
			$q = "SELECT `setting` FROM `#__vikrentitems_config` WHERE `param`='payaccpercent';";
			$dbo->setQuery($q);
			$dbo->execute();
			$per = $dbo->loadAssocList();
			if ($per[0]['setting'] > 0) {
				$imp = $imp * $per[0]['setting'] / 100;
				$tax = $tax * $per[0]['setting'] / 100;
				$depositmess = "<p><strong>" . JText::_('VRLEAVEDEPOSIT') . " " . (self::numberFormat($imp + $tax)) . " " . $currencysymb . "</strong></p><br/>";
			}
		}
		$q = "SELECT `setting` FROM `#__vikrentitems_config` WHERE `param`='ccpaypal';";
		$dbo->setQuery($q);
		$dbo->execute();
		$acc = $dbo->loadAssocList();
		$q = "SELECT `id`,`setting` FROM `#__vikrentitems_texts` WHERE `param`='paymentname';";
		$dbo->setQuery($q);
		$dbo->execute();
		$payname = $dbo->loadAssocList();
		$q = "SELECT `setting` FROM `#__vikrentitems_config` WHERE `param`='currencycodepp';";
		$dbo->setQuery($q);
		$dbo->execute();
		$paypalcurcode = trim($dbo->loadResult());
		$itname = (empty($payname[0]['setting']) ? $itemname : $payname[0]['setting']);
		$returl = JURI::root() . "index.php?option=com_vikrentitems&task=vieworder&sid=" . $sid . "&ts=" . $ts;
		$notifyurl = JURI::root() . "index.php?option=com_vikrentitems&task=notifypayment&sid=" . $sid . "&ts=" . $ts;
		$form = "<form action=\"https://www.paypal.com/cgi-bin/webscr\" method=\"post\">\n";
		$form .= "<input type=\"hidden\" name=\"business\" value=\"" . $acc[0]['setting'] . "\"/>\n";
		$form .= "<input type=\"hidden\" name=\"cmd\" value=\"_xclick\"/>\n";
		$form .= "<input type=\"hidden\" name=\"amount\" value=\"" . self::numberFormat($imp) . "\"/>\n";
		$form .= "<input type=\"hidden\" name=\"item_name\" value=\"" . $itname . "\"/>\n";
		$form .= "<input type=\"hidden\" name=\"item_number\" value=\"" . $itemname . "\"/>\n";
		$form .= "<input type=\"hidden\" name=\"quantity\" value=\"1\"/>\n";
		$form .= "<input type=\"hidden\" name=\"tax\" value=\"" . self::numberFormat($tax) . "\"/>\n";
		$form .= "<input type=\"hidden\" name=\"shipping\" value=\"0.00\"/>\n";
		$form .= "<input type=\"hidden\" name=\"currency_code\" value=\"" . $paypalcurcode . "\"/>\n";
		$form .= "<input type=\"hidden\" name=\"no_shipping\" value=\"1\"/>\n";
		$form .= "<input type=\"hidden\" name=\"rm\" value=\"2\"/>\n";
		$form .= "<input type=\"hidden\" name=\"notify_url\" value=\"" . $notifyurl . "\"/>\n";
		$form .= "<input type=\"hidden\" name=\"return\" value=\"" . $returl . "\"/>\n";
		$form .= "<input type=\"image\" src=\"https://www.paypal.com/en_US/i/btn/btn_paynow_SM.gif\" name=\"submit\" alt=\"PayPal - The safer, easier way to pay online!\">\n";
		$form .= "</form>\n";
		return $depositmess . $form;
	}
	
	public static function sendPDF() {
		$dbo = JFactory::getDBO();
		$q = "SELECT `setting` FROM `#__vikrentitems_config` WHERE `param`='sendpdf';";
		$dbo->setQuery($q);
		$dbo->execute();
		$s = $dbo->loadAssocList();
		return (intval($s[0]['setting']) == 1 ? true : false);
	}
	
	public static function sendJutility() {
		$dbo = JFactory::getDBO();
		$q = "SELECT `setting` FROM `#__vikrentitems_config` WHERE `param`='sendjutility';";
		$dbo->setQuery($q);
		$dbo->execute();
		$s = $dbo->loadAssocList();
		return (intval($s[0]['setting']) == 1 ? true : false);
	}

	public static function allowStats() {
		$dbo = JFactory::getDBO();
		$q = "SELECT `setting` FROM `#__vikrentitems_config` WHERE `param`='allowstats';";
		$dbo->setQuery($q);
		$dbo->execute();
		$s = $dbo->loadAssocList();
		return (intval($s[0]['setting']) == 1 ? true : false);
	}

	public static function sendMailStats() {
		$dbo = JFactory::getDBO();
		$q = "SELECT `setting` FROM `#__vikrentitems_config` WHERE `param`='sendmailstats';";
		$dbo->setQuery($q);
		$dbo->execute();
		$s = $dbo->loadAssocList();
		return (intval($s[0]['setting']) == 1 ? true : false);
	}

	public static function getPlaceName($idplace, $vri_tn = null) {
		$dbo = JFactory::getDBO();
		$q = "SELECT `id`,`name` FROM `#__vikrentitems_places` WHERE `id`=" . $dbo->quote($idplace) . ";";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() < 1) {
			return '';
		}
		$p = $dbo->loadAssocList();
		if (is_object($vri_tn)) {
			$vri_tn->translateContents($p, '#__vikrentitems_places');
		}
		return $p[0]['name'];
	}

	public static function getPlaceInfo($idplace, $vrc_tn = null) {
		$dbo = JFactory::getDBO();
		$q = "SELECT * FROM `#__vikrentitems_places` WHERE `id`=" . intval($idplace) . ";";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() < 1) {
			return array();
		}
		$p = $dbo->loadAssocList();
		if (is_object($vrc_tn)) {
			$vrc_tn->translateContents($p, '#__vikrentitems_places');
		}
		return $p[0];
	}

	public static function getCategoryName($idcat, $vri_tn = null) {
		$dbo = JFactory::getDBO();
		$q = "SELECT `id`,`name` FROM `#__vikrentitems_categories` WHERE `id`=" . $dbo->quote($idcat) . ";";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() < 1) {
			return '';
		}
		$p = $dbo->loadAssocList();
		if (is_object($vri_tn)) {
			$vri_tn->translateContents($p, '#__vikrentitems_categories');
		}
		return $p[0]['name'];
	}

	public static function getLocFee($from, $to) {
		$dbo = JFactory::getDBO();
		$q = "SELECT * FROM `#__vikrentitems_locfees` WHERE (`from`=" . $dbo->quote($from) . " AND `to`=" . $dbo->quote($to) . ") OR (`to`=" . $dbo->quote($from) . " AND `from`=" . $dbo->quote($to) . " AND `invert`='1');";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$res = $dbo->loadAssocList();
			return $res[0];
		}
		return false;
	}

	public static function sayLocFeePlusIva($cost, $idiva, $order=array()) {
		$dbo = JFactory::getDBO();
		$session = JFactory::getSession();
		$sval = $session->get('ivaInclusa', '');
		if (strlen($sval) > 0) {
			$ivainclusa = $sval;
		} else {
			$q = "SELECT `setting` FROM `#__vikrentitems_config` WHERE `param`='ivainclusa';";
			$dbo->setQuery($q);
			$dbo->execute();
			$iva = $dbo->loadAssocList();
			$session->set('ivaInclusa', $iva[0]['setting']);
			$ivainclusa = $iva[0]['setting'];
		}
		if (intval($ivainclusa) == 0) {
			//VRI 1.1 Rev.2
			$locationvat = isset($order['locationvat']) && strlen($order['locationvat']) > 0 ? $order['locationvat'] : (count($order) == 0 ? $session->get('vriLocationTaxRate', '') : '');
			if (strlen($locationvat) > 0) {
				$subt = 100 + $locationvat;
				$op = ($cost * $subt / 100);
				return $op;
			}
			//
			$q = "SELECT `aliq` FROM `#__vikrentitems_iva` WHERE `id`='" . $idiva . "';";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() == 1) {
				$piva = $dbo->loadAssocList();
				$subt = 100 + $piva[0]['aliq'];
				$op = ($cost * $subt / 100);
				return $op;
			}
		}
		return $cost;
	}

	public static function sayLocFeeMinusIva($cost, $idiva, $order=array()) {
		$dbo = JFactory::getDBO();
		$session = JFactory::getSession();
		$sval = $session->get('ivaInclusa', '');
		if (strlen($sval) > 0) {
			$ivainclusa = $sval;
		} else {
			$q = "SELECT `setting` FROM `#__vikrentitems_config` WHERE `param`='ivainclusa';";
			$dbo->setQuery($q);
			$dbo->execute();
			$iva = $dbo->loadAssocList();
			$session->set('ivaInclusa', $iva[0]['setting']);
			$ivainclusa = $iva[0]['setting'];
		}
		if (intval($ivainclusa) == 1) {
			//VRI 1.1 Rev.2
			$locationvat = isset($order['locationvat']) && strlen($order['locationvat']) > 0 ? $order['locationvat'] : (count($order) == 0 ? $session->get('vriLocationTaxRate', '') : '');
			if (strlen($locationvat) > 0) {
				$subt = 100 + $locationvat;
				$op = ($cost * 100 / $subt);
				return $op;
			}
			//
			$q = "SELECT `aliq` FROM `#__vikrentitems_iva` WHERE `id`='" . $idiva . "';";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() == 1) {
				$piva = $dbo->loadAssocList();
				$subt = 100 + $piva[0]['aliq'];
				$op = ($cost * 100 / $subt);
				return $op;
			}
		}
		return $cost;
	}
	
	public static function sortItemPrices($arr) {
		$newarr = array ();
		foreach ($arr as $k => $v) {
			$newarr[$k] = $v['cost'];
		}
		asort($newarr);
		$sorted = array ();
		foreach ($newarr as $k => $v) {
			$sorted[$k] = $arr[$k];
		}
		return $sorted;
	}
	
	public static function sortResults($arr) {
		$newarr = array ();
		foreach ($arr as $k => $v) {
			$newarr[$k] = $v[0]['cost'];
		}
		asort($newarr);
		$sorted = array ();
		foreach ($newarr as $k => $v) {
			$sorted[$k] = $arr[$k];
		}
		return $sorted;
	}

	public static function applySeasonalPrices($arr, $from, $to, $pickup) {
		$dbo = JFactory::getDBO();
		$vri_tn = self::getTranslator();
		$itemschange = array();
		$one = getdate($from);
		//leap years
		if (($one['year'] % 4) == 0 && ($one['year'] % 100 != 0 || $one['year'] % 400 == 0)) {
			$isleap = true;
		} else {
			$isleap = false;
		}
		//
		$baseone = mktime(0, 0, 0, 1, 1, $one['year']);
		$tomidnightone = intval($one['hours']) * 3600;
		$tomidnightone += intval($one['minutes']) * 60;
		$sfrom = $from - $baseone - $tomidnightone;
		$fromdayts = mktime(0, 0, 0, $one['mon'], $one['mday'], $one['year']);
		$two = getdate($to);
		$basetwo = mktime(0, 0, 0, 1, 1, $two['year']);
		$tomidnighttwo = intval($two['hours']) * 3600;
		$tomidnighttwo += intval($two['minutes']) * 60;
		$sto = $to - $basetwo - $tomidnighttwo;
		//Hourly Prices
		if ($sfrom === $sto) {
			$sto += 86399;
		}
		//End Hourly Prices
		//leap years, last day of the month of the season
		if ($isleap) {
			$leapts = mktime(0, 0, 0, 2, 29, $two['year']);
			if ($two[0] >= $leapts) {
				$sfrom -= 86400;
				$sto -= 86400;
			}
		}
		//
		//hourly prices
		if ($sfrom == $sto) {
			$sto++;
		}
		//
		$q = "SELECT * FROM `#__vikrentitems_seasons` WHERE (`locations`='0' OR `locations`='" . $pickup . "') AND (" .
		 ($sto > $sfrom ? "(`from`<=" . $sfrom . " AND `to`>=" . $sto . ") " : "") .
		 ($sto > $sfrom ? "OR (`from`<=" . $sfrom . " AND `to`>=" . $sfrom . ") " : "(`from`<=" . $sfrom . " AND `to`<=" . $sfrom . " AND `from`>`to`) ") .
		 ($sto > $sfrom ? "OR (`from`<=" . $sto . " AND `to`>=" . $sto . ") " : "OR (`from`>=" . $sto . " AND `to`>=" . $sto . " AND `from`>`to`) ") .
		 ($sto > $sfrom ? "OR (`from`>=" . $sfrom . " AND `from`<=" . $sto . " AND `to`>=" . $sfrom . " AND `to`<=" . $sto . ")" : "OR (`from`>=" . $sfrom . " AND `from`>" . $sto . " AND `to`<" . $sfrom . " AND `to`<=" . $sto . " AND `from`>`to`)") .
		 ($sto > $sfrom ? " OR (`from`<=" . $sfrom . " AND `from`<=" . $sto . " AND `to`<" . $sfrom . " AND `to`<" . $sto . " AND `from`>`to`) OR (`from`>" . $sfrom . " AND `from`>" . $sto . " AND `to`>=" . $sfrom . " AND `to`>=" . $sto . " AND `from`>`to`)" : " OR (`from` <=" . $sfrom . " AND `to` >=" . $sfrom . " AND `from` >" . $sto . " AND `to` >" . $sto . " AND `from` < `to`)") .
		 ($sto > $sfrom ? " OR (`from` >=" . $sfrom . " AND `from` <" . $sto . " AND `to` <" . $sfrom . " AND `to` <" . $sto . " AND `from` > `to`)" : " OR (`from` <" . $sfrom . " AND `to` >=" . $sto . " AND `from` <=" . $sto . " AND `to` <" . $sfrom . " AND `from` < `to`)"). //VRI 1.6 Else part is for Season Jan 6 to Feb 12 - Booking Dec 31 to Jan 8
		 ($sto > $sfrom ? " OR (`from` >" . $sfrom . " AND `from` >" . $sto . " AND `to` >=" . $sfrom . " AND `to` <" . $sto . " AND `from` > `to`)" : " OR (`from` >=" . $sfrom . " AND `from` >" . $sto . " AND `to` >" . $sfrom . " AND `to` >" . $sto . " AND `from` < `to`) OR (`from` <" . $sfrom . " AND `from` <" . $sto . " AND `to` <" . $sfrom . " AND `to` <=" . $sto . " AND `from` < `to`)"). //VRI 1.6 Else part for seasons Dec 25 to Dec 31, Jan 2 to Jan 5 - Booking Dec 20 to Jan 7
		");";
		$dbo->setQuery($q);
		$dbo->execute();
		$totseasons = $dbo->getNumRows();
		if ($totseasons > 0) {
			$seasons = $dbo->loadAssocList();
			$vri_tn->translateContents($seasons, '#__vikrentitems_seasons');
			$applyseasons = false;
			$mem = array();
			foreach ($arr as $k => $a) {
				$mem[$k]['daysused'] = 0;
				$mem[$k]['sum'] = array();
			}
			foreach ($seasons as $s) {
				//Special Price tied to the year
				if (!empty($s['year']) && $s['year'] > 0) {
					//VRI 1.6 - do not skip seasons tied to the year for bookings between two years
					if ($one['year'] != $s['year'] && $two['year'] != $s['year']) {
						//VRI 1.6 - tied to the year can be set for prev year (Dec 27 to Jan 3) and booking can be Jan 1 to Jan 3 - do not skip in this case
						if (($one['year'] - $s['year']) != 1 || $s['from'] < $s['to']) {
							continue;
						}
						//VRI 1.6 - tied to 2016 going through Jan 2017: dates of December 2017 should skip this speacial price
						if (($one['year'] - $s['year']) == 1 && $s['from'] > $s['to']) {
							$calc_ends = mktime(0, 0, 0, 1, 1, ($s['year'] + 1)) + $s['to'];
							if ($calc_ends < ($from - $tomidnightone)) {
								continue;
							}
						}
					} elseif ($one['year'] < $s['year'] && $two['year'] == $s['year']) {
						//VRI 1.6 - season tied to the year 2017 accross 2018 and we are parsing dates accross prev year 2016-2017
						if ($s['from'] > $s['to']) {
							continue;
						}
					} elseif ($one['year'] == $s['year'] && $two['year'] == $s['year'] && $s['from'] > $s['to']) {
						//VRI 1.6 - season tied to the year 2017 accross 2018 and we are parsing dates at the beginning of 2017 due to beginning loop in 2016 (Rates Overview)
						if (($baseone + $s['from']) > $to) {
							continue;
						}
					}
				}
				//
				$allitems = explode(",", $s['iditems']);
				$allprices = !empty($s['idprices']) ? explode(",", $s['idprices']) : array();
				$inits = $baseone + $s['from'];
				if ($s['from'] < $s['to']) {
					$ends = $basetwo + $s['to'];
					//VRI 1.6 check if the inits must be set to the year after
					//ex. Season Jan 6 to Feb 12 - Booking Dec 31 to Jan 8 to charge Jan 6,7
					if ($sfrom > $s['from'] && $sto >= $s['from'] && $sfrom > $s['to'] && $sto <= $s['to'] && $s['from'] < $s['to'] && $sfrom > $sto) {
						$tmpbase = mktime(0, 0, 0, 1, 1, ($one['year'] + 1));
						$inits = $tmpbase + $s['from'];
					} elseif ($sfrom >= $s['from'] && $sfrom <= $s['to'] && $sto < $s['from'] && $sto < $s['to'] && $sfrom > $sto) {
						//VRI 1.6 - Season Dec 23 to Dec 29 - Booking Dec 29 to Jan 5
						$ends = $baseone + $s['to'];
					} elseif ($sfrom <= $s['from'] && $sfrom <= $s['to'] && $sto < $s['from'] && $sto < $s['to'] && $sfrom > $sto) {
						//VRI 1.6 - Season Dec 30 to Dec 31 - Booking Dec 29 to Jan 5
						$ends = $baseone + $s['to'];
					} elseif ($sfrom > $s['from'] && $sfrom > $s['to'] && $sto >= $s['from'] && ($sto >= $s['to'] || $sto <= $s['to']) && $sfrom > $sto) {
						//VRI 1.6 - Season Jan 1 to Jan 2 - Booking Dec 29 to Jan 5
						$inits = $basetwo + $s['from'];
					}
				} else {
					//between 2 years
					if ($baseone < $basetwo) {
						//ex. 29/12/2012 - 14/01/2013
						$ends = $basetwo + $s['to'];
					} else {
						if (($sfrom >= $s['from'] && $sto >= $s['from']) OR ($sfrom < $s['from'] && $sto >= $s['from'] && $sfrom > $s['to'] && $sto > $s['to'])) {
							//ex. 25/12 - 30/12 with init season on 20/12 OR 27/12 for counting 28,29,30/12
							$tmpbase = mktime(0, 0, 0, 1, 1, ($one['year'] + 1));
							$ends = $tmpbase + $s['to'];
						} else {
							//ex. 03/01 - 09/01
							$ends = $basetwo + $s['to'];
							$tmpbase = mktime(0, 0, 0, 1, 1, ($one['year'] - 1));
							$inits = $tmpbase + $s['from'];
						}
					}
				}
				//leap years
				if ($isleap == true) {
					$infoseason = getdate($inits);
					$leapts = mktime(0, 0, 0, 2, 29, $infoseason['year']);
					//VRI 1.6 added below && $infoseason['year'] == $one['year']
					//for those seasons like 2015 Dec 14 to 2016 Jan 5 and booking dates like 2016 Jan 1 to Jan 6 where 2015 is not leap
					if ($infoseason[0] >= $leapts && $infoseason['year'] == $one['year']) {
						$inits += 86400;
						$ends += 86400;
					}
				}
				//
				//Promotions
				$promotion = array();
				if ($s['promo'] == 1) {
					$daysadv = (($inits - time()) / 86400);
					$daysadv = $daysadv > 0 ? (int)ceil($daysadv) : 0;
					if (!empty($s['promodaysadv']) && $s['promodaysadv'] > $daysadv) {
						continue;
					} else {
						$promotion['todaydaysadv'] = $daysadv;
						$promotion['promodaysadv'] = $s['promodaysadv'];
						$promotion['promotxt'] = $s['promotxt'];
					}
				}
				//
				//week days
				$filterwdays = !empty($s['wdays']) ? true : false;
				$wdays = $filterwdays == true ? explode(';', $s['wdays']) : '';
				if (is_array($wdays) && count($wdays) > 0) {
					foreach ($wdays as $kw=>$wd) {
						if (strlen($wd) == 0) {
							unset($wdays[$kw]);
						}
					}
				}
				//
				//pickup must be after the begin of the season
				if ($s['pickupincl'] == 1) {
					$pickupinclok = false;
					if ($s['from'] < $s['to']) {
						if ($sfrom >= $s['from'] && $sfrom <= $s['to']) {
							$pickupinclok = true;
						}
					} else {
						if (($sfrom >= $s['from'] && $sfrom > $s['to']) || ($sfrom < $s['from'] && $sfrom <= $s['to'])) {
							$pickupinclok = true;
						}
					}
				} else {
					$pickupinclok = true;
				}
				//
				if ($pickupinclok == true) {
					foreach ($arr as $k => $a) {
						//Applied only to some types of price
						if (count($allprices) > 0 && !empty($allprices[0])) {
							if (!in_array("-" . $a[0]['idprice'] . "-", $allprices)) {
								continue;
							}
						}
						//
						if (in_array("-" . $a[0]['iditem'] . "-", $allitems)) {
							$affdays = 0;
							$season_fromdayts = $fromdayts;
							$is_dst = date('I', $season_fromdayts);
							for ($i = 0; $i < $a[0]['days']; $i++) {
								$todayts = $season_fromdayts + ($i * 86400);
								$is_now_dst = date('I', $todayts);
								if ($is_dst != $is_now_dst) {
									//Daylight Saving Time has changed, check how
									if ((bool)$is_dst === true) {
										$todayts += 3600;
										$season_fromdayts += 3600;
									} else {
										$todayts -= 3600;
										$season_fromdayts -= 3600;
									}
									$is_dst = $is_now_dst;
								}
								//VRI 1.1 rev2
								if ($s['keepfirstdayrate'] == 1) {
									if ($fromdayts >= $inits && $fromdayts <= $ends) {
										$affdays = $a[0]['days'];
									} else {
										$affdays = 0;
									}
									break;
								}
								//end VRI 1.1 rev2
								if ($todayts >= $inits && $todayts <= $ends) {
									//week days
									if ($filterwdays == true) {
										$checkwday = getdate($todayts);
										if (in_array($checkwday['wday'], $wdays)) {
											$affdays++;
										}
									} else {
										$affdays++;
									}
									//
								}
							}
							if ($affdays > 0) {
								$applyseasons = true;
								$dailyprice = $a[0]['cost'] / $a[0]['days'];
								$a[0]['days'] = intval($a[0]['days']);
								$pctval = 0;
								$absval = 0;
								//VikRentItems 1.1 for abs or pcent and values overrides
								if (intval($s['val_pcent']) == 2) {
									//percentage value
									$pctval = $s['diffcost'];
									if (strlen($s['losoverride']) > 0) {
										//values overrides
										$arrvaloverrides = array();
										$valovrparts = explode('_', $s['losoverride']);
										foreach ($valovrparts as $valovr) {
											if (!empty($valovr)) {
												$ovrinfo = explode(':', $valovr);
												if (strpos($ovrinfo[0], '-i') !== false) {
													$ovrinfo[0] = str_replace('-i', '', $ovrinfo[0]);
													if ((int)$ovrinfo[0] < $a[0]['days']) {
														$arrvaloverrides[$a[0]['days']] = $ovrinfo[1];
													}
												}
												$arrvaloverrides[$ovrinfo[0]] = $ovrinfo[1];
											}
										}
										if (array_key_exists($a[0]['days'], $arrvaloverrides)) {
											$pctval = $arrvaloverrides[$a[0]['days']];
										}
									}
									if (intval($s['type']) == 1) {
										//charge
										$cpercent = 100 + $pctval;
									} else {
										//discount
										$cpercent = 100 - $pctval;
									}
									$newprice = ($dailyprice * $cpercent / 100) * $affdays;
								} else {
									//absolute value
									$absval = $s['diffcost'];
									if (strlen($s['losoverride']) > 0) {
										//values overrides
										$arrvaloverrides = array();
										$valovrparts = explode('_', $s['losoverride']);
										foreach ($valovrparts as $valovr) {
											if (!empty($valovr)) {
												$ovrinfo = explode(':', $valovr);
												if (strpos($ovrinfo[0], '-i') !== false) {
													$ovrinfo[0] = str_replace('-i', '', $ovrinfo[0]);
													if ((int)$ovrinfo[0] < $a[0]['days']) {
														$arrvaloverrides[$a[0]['days']] = $ovrinfo[1];
													}
												}
												$arrvaloverrides[$ovrinfo[0]] = $ovrinfo[1];
											}
										}
										if (array_key_exists($a[0]['days'], $arrvaloverrides)) {
											$absval = $arrvaloverrides[$a[0]['days']];
										}
									}
									if (intval($s['type']) == 1) {
										//charge
										$newprice = ($dailyprice + $absval) * $affdays;
									} else {
										//discount
										$newprice = ($dailyprice - $absval) * $affdays;
									}
								}
								//end VikRentItems 1.1 for abs or pcent and values overrides
								//VikRentItems 1.3
								if (!empty($s['roundmode'])) {
									$newprice = round($newprice, 0, constant($s['roundmode']));
								}
								//
								//Promotions (only if no value overrides set the amount to 0)
								if (count($promotion) > 0 && ($absval > 0 || $pctval > 0)) {
									$mem[$k]['promotion'] = $promotion;
								}
								//
								$mem[$k]['sum'][] = $newprice;
								$mem[$k]['daysused'] += $affdays;
								$itemschange[] = $a[0]['iditem'];
							}
						}
					}
				}
			}
			if ($applyseasons) {
				foreach ($mem as $k => $v) {
					if ($v['daysused'] > 0 && @ count($v['sum']) > 0) {
						$newprice = 0;
						$dailyprice = $arr[$k][0]['cost'] / $arr[$k][0]['days'];
						$restdays = $arr[$k][0]['days'] - $v['daysused'];
						$addrest = $restdays * $dailyprice;
						$newprice += $addrest;
						foreach ($v['sum'] as $add) {
							$newprice += $add;
						}
						//Promotions
						if (array_key_exists('promotion', $v)) {
							$arr[$k][0]['promotion'] = $v['promotion'];
						}
						//
						$arr[$k][0]['cost'] = $newprice;
						$arr[$k][0]['affdays'] = $v['daysused'];
					}
				}
			}
		}
		//week days with no season
		$itemschange = array_unique($itemschange);
		$q = "SELECT * FROM `#__vikrentitems_seasons` WHERE (`locations`='0' OR `locations`=" . $dbo->quote($pickup) . ") AND ((`from` = 0 AND `to` = 0) OR (`from` IS NULL AND `to` IS NULL));";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$specials = $dbo->loadAssocList();
			$vri_tn->translateContents($specials, '#__vikrentitems_seasons');
			$applyseasons = false;
			unset($mem);
			$mem = array();
			foreach ($arr as $k => $a) {
				$mem[$k]['daysused'] = 0;
				$mem[$k]['sum'] = array ();
			}
			foreach ($specials as $s) {
				//Special Price tied to the year
				if (!empty($s['year']) && $s['year'] > 0) {
					if ($one['year'] != $s['year']) {
						continue;
					}
				}
				//
				$allitems = explode(",", $s['iditems']);
				$allprices = !empty($s['idprices']) ? explode(",", $s['idprices']) : array();
				//week days
				$filterwdays = !empty($s['wdays']) ? true : false;
				$wdays = $filterwdays == true ? explode(';', $s['wdays']) : '';
				if (is_array($wdays) && count($wdays) > 0) {
					foreach ($wdays as $kw=>$wd) {
						if (strlen($wd) == 0) {
							unset($wdays[$kw]);
						}
					}
				}
				//
				foreach ($arr as $k => $a) {
					//only items with no price modifications from seasons
					//Applied only to some types of price
					if (count($allprices) > 0 && !empty($allprices[0])) {
						if (!in_array("-" . $a[0]['idprice'] . "-", $allprices)) {
							continue;
						}
					}
					//
					if (in_array("-" . $a[0]['iditem'] . "-", $allitems) && !in_array($a[0]['iditem'], $itemschange)) {
						$affdays = 0;
						$season_fromdayts = $fromdayts;
						$is_dst = date('I', $season_fromdayts);
						for ($i = 0; $i < $a[0]['days']; $i++) {
							$todayts = $season_fromdayts + ($i * 86400);
							$is_now_dst = date('I', $todayts);
							if ($is_dst != $is_now_dst) {
								//Daylight Saving Time has changed, check how
								if ((bool)$is_dst === true) {
									$todayts += 3600;
									$season_fromdayts += 3600;
								} else {
									$todayts -= 3600;
									$season_fromdayts -= 3600;
								}
								$is_dst = $is_now_dst;
							}
							//week days
							if ($filterwdays == true) {
								$checkwday = getdate($todayts);
								if (in_array($checkwday['wday'], $wdays)) {
									$affdays++;
								}
							}
							//
						}
						if ($affdays > 0) {
							$applyseasons = true;
							$dailyprice = $a[0]['cost'] / $a[0]['days'];
							$a[0]['days'] = intval($a[0]['days']);
							//VikRentCat 1.1 for abs or pcent and values overrides
							if (intval($s['val_pcent']) == 2) {
								//percentage value
								$pctval = $s['diffcost'];
								if (strlen($s['losoverride']) > 0) {
									//values overrides
									$arrvaloverrides = array();
									$valovrparts = explode('_', $s['losoverride']);
									foreach ($valovrparts as $valovr) {
										if (!empty($valovr)) {
											$ovrinfo = explode(':', $valovr);
											if (strpos($ovrinfo[0], '-i') !== false) {
												$ovrinfo[0] = str_replace('-i', '', $ovrinfo[0]);
												if ((int)$ovrinfo[0] < $a[0]['days']) {
													$arrvaloverrides[$a[0]['days']] = $ovrinfo[1];
												}
											}
											$arrvaloverrides[$ovrinfo[0]] = $ovrinfo[1];
										}
									}
									if (array_key_exists($a[0]['days'], $arrvaloverrides)) {
										$pctval = $arrvaloverrides[$a[0]['days']];
									}
								}
								if (intval($s['type']) == 1) {
									//charge
									$cpercent = 100 + $pctval;
								} else {
									//discount
									$cpercent = 100 - $pctval;
								}
								$newprice = ($dailyprice * $cpercent / 100) * $affdays;
							} else {
								//absolute value
								$absval = $s['diffcost'];
								if (strlen($s['losoverride']) > 0) {
									//values overrides
									$arrvaloverrides = array();
									$valovrparts = explode('_', $s['losoverride']);
									foreach ($valovrparts as $valovr) {
										if (!empty($valovr)) {
											$ovrinfo = explode(':', $valovr);
											if (strpos($ovrinfo[0], '-i') !== false) {
												$ovrinfo[0] = str_replace('-i', '', $ovrinfo[0]);
												if ((int)$ovrinfo[0] < $a[0]['days']) {
													$arrvaloverrides[$a[0]['days']] = $ovrinfo[1];
												}
											}
											$arrvaloverrides[$ovrinfo[0]] = $ovrinfo[1];
										}
									}
									if (array_key_exists($a[0]['days'], $arrvaloverrides)) {
										$absval = $arrvaloverrides[$a[0]['days']];
									}
								}
								if (intval($s['type']) == 1) {
									//charge
									$newprice = ($dailyprice + $absval) * $affdays;
								} else {
									//discount
									$newprice = ($dailyprice - $absval) * $affdays;
								}
							}
							//end VikRentItems 1.1 for abs or pcent and values overrides
							//VikRentItems 1.3
							if (!empty($s['roundmode'])) {
								$newprice = round($newprice, 0, constant($s['roundmode']));
							}
							//
							$mem[$k]['sum'][] = $newprice;
							$mem[$k]['daysused'] += $affdays;
						}
					}
				}
			}
			if ($applyseasons) {
				foreach ($mem as $k => $v) {
					if ($v['daysused'] > 0 && @ count($v['sum']) > 0) {
						$newprice = 0;
						$dailyprice = $arr[$k][0]['cost'] / $arr[$k][0]['days'];
						$restdays = $arr[$k][0]['days'] - $v['daysused'];
						$addrest = $restdays * $dailyprice;
						$newprice += $addrest;
						foreach ($v['sum'] as $add) {
							$newprice += $add;
						}
						$arr[$k][0]['cost'] = $newprice;
						$arr[$k][0]['affdays'] = $v['daysused'];
					}
				}
			}
		}
		//end week days with no season
		return $arr;
	}

	/**
	 * Applies the special prices over an array of tariffs for one item.
	 *
	 * @param 	array  		$arr 			array of tariffs taken from the DB
	 * @param 	int  		$from 			pick up timestamp
	 * @param 	int  		$to 			drop off timestamp
	 * @param 	int 		$pickup 		the ID of the pick up place, or null for the administrator
	 * @param 	array  		$parsed_season 	array of a season to parse (used to render the seasons calendars in back-end and front-end - VRI 1.6)
	 *
	 * @return 	array
	 */
	public static function applySeasonsItem($arr, $from, $to, $pickup = null, $parsed_season = array()) {
		$dbo = JFactory::getDBO();
		$vri_tn = self::getTranslator();
		$itemschange = array();
		$one = getdate($from);
		//leap years
		if ($one['year'] % 4 == 0 && ($one['year'] % 100 != 0 || $one['year'] % 400 == 0)) {
			$isleap = true;
		} else {
			$isleap = false;
		}
		//
		$baseone = mktime(0, 0, 0, 1, 1, $one['year']);
		$tomidnightone = intval($one['hours']) * 3600;
		$tomidnightone += intval($one['minutes']) * 60;
		$sfrom = $from - $baseone - $tomidnightone;
		$fromdayts = mktime(0, 0, 0, $one['mon'], $one['mday'], $one['year']);
		$two = getdate($to);
		$basetwo = mktime(0, 0, 0, 1, 1, $two['year']);
		$tomidnighttwo = intval($two['hours']) * 3600;
		$tomidnighttwo += intval($two['minutes']) * 60;
		$sto = $to - $basetwo - $tomidnighttwo;
		//Hourly Prices
		if ($sfrom === $sto) {
			$sto += 86399;
		}
		//End Hourly Prices
		//leap years, last day of the month of the season
		if ($isleap) {
			$leapts = mktime(0, 0, 0, 2, 29, $two['year']);
			if ($two[0] >= $leapts) {
				$sfrom -= 86400;
				$sto -= 86400;
			} elseif ($sto < $sfrom && $one['year'] < $two['year']) {
				//lower pickup date when in leap year but not for drop off
				$sfrom -= 86400;
			}
		}
		//
		//hourly prices
		if ($sfrom == $sto) {
			$sto++;
		}
		//
		$totseasons = 0;
		if (count($parsed_season) == 0) {
			$q = "SELECT * FROM `#__vikrentitems_seasons` WHERE ".($pickup !== null ? "(`locations`='0' OR `locations`='" . (int)$pickup . "') AND " : "")."(" .
		 	($sto > $sfrom ? "(`from`<=" . $sfrom . " AND `to`>=" . $sto . ") " : "") .
		 	($sto > $sfrom ? "OR (`from`<=" . $sfrom . " AND `to`>=" . $sfrom . ") " : "(`from`<=" . $sfrom . " AND `to`<=" . $sfrom . " AND `from`>`to`) ") .
		 	($sto > $sfrom ? "OR (`from`<=" . $sto . " AND `to`>=" . $sto . ") " : "OR (`from`>=" . $sto . " AND `to`>=" . $sto . " AND `from`>`to`) ") .
		 	($sto > $sfrom ? "OR (`from`>=" . $sfrom . " AND `from`<=" . $sto . " AND `to`>=" . $sfrom . " AND `to`<=" . $sto . ")" : "OR (`from`>=" . $sfrom . " AND `from`>" . $sto . " AND `to`<" . $sfrom . " AND `to`<=" . $sto . " AND `from`>`to`)") .
		 	($sto > $sfrom ? " OR (`from`<=" . $sfrom . " AND `from`<=" . $sto . " AND `to`<" . $sfrom . " AND `to`<" . $sto . " AND `from`>`to`) OR (`from`>" . $sfrom . " AND `from`>" . $sto . " AND `to`>=" . $sfrom . " AND `to`>=" . $sto . " AND `from`>`to`)" : " OR (`from` <=" . $sfrom . " AND `to` >=" . $sfrom . " AND `from` >" . $sto . " AND `to` >" . $sto . " AND `from` < `to`)") .
		 	($sto > $sfrom ? " OR (`from` >=" . $sfrom . " AND `from` <" . $sto . " AND `to` <" . $sfrom . " AND `to` <" . $sto . " AND `from` > `to`)" : " OR (`from` <" . $sfrom . " AND `to` >=" . $sto . " AND `from` <=" . $sto . " AND `to` <" . $sfrom . " AND `from` < `to`)"). //VRI 1.6 Else part is for Season Jan 6 to Feb 12 - Booking Dec 31 to Jan 8
		 	($sto > $sfrom ? " OR (`from` >" . $sfrom . " AND `from` >" . $sto . " AND `to` >=" . $sfrom . " AND `to` <" . $sto . " AND `from` > `to`)" : " OR (`from` >=" . $sfrom . " AND `from` >" . $sto . " AND `to` >" . $sfrom . " AND `to` >" . $sto . " AND `from` < `to`) OR (`from` <" . $sfrom . " AND `from` <" . $sto . " AND `to` <" . $sfrom . " AND `to` <=" . $sto . " AND `from` < `to`)"). //VRI 1.6 Else part for seasons Dec 25 to Dec 31, Jan 2 to Jan 5 - Booking Dec 20 to Jan 7
			");";
			$dbo->setQuery($q);
			$dbo->execute();
			$totseasons = $dbo->getNumRows();
		}
		if ($totseasons > 0 || count($parsed_season) > 0) {
			if ($totseasons > 0) {
				$seasons = $dbo->loadAssocList();
			} else {
				$seasons = array($parsed_season);
			}
			$vri_tn->translateContents($seasons, '#__vikrentitems_seasons');
			$applyseasons = false;
			$mem = array ();
			foreach ($arr as $k => $a) {
				$mem[$k]['daysused'] = 0;
				$mem[$k]['sum'] = array ();
				$mem[$k]['spids'] = array();
			}
			foreach ($seasons as $s) {
				//Special Price tied to the year
				if (!empty($s['year']) && $s['year'] > 0) {
					//VRI 1.6 - do not skip seasons tied to the year for bookings between two years
					if ($one['year'] != $s['year'] && $two['year'] != $s['year']) {
						//VRI 1.6 - tied to the year can be set for prev year (Dec 27 to Jan 3) and booking can be Jan 1 to Jan 3 - do not skip in this case
						if (($one['year'] - $s['year']) != 1 || $s['from'] < $s['to']) {
							continue;
						}
						//VRI 1.6 - tied to 2016 going through Jan 2017: dates of December 2017 should skip this speacial price
						if (($one['year'] - $s['year']) == 1 && $s['from'] > $s['to']) {
							$calc_ends = mktime(0, 0, 0, 1, 1, ($s['year'] + 1)) + $s['to'];
							if ($calc_ends < ($from - $tomidnightone)) {
								continue;
							}
						}
					} elseif ($one['year'] < $s['year'] && $two['year'] == $s['year']) {
						//VRI 1.6 - season tied to the year 2017 accross 2018 and we are parsing dates accross prev year 2016-2017
						if ($s['from'] > $s['to']) {
							continue;
						}
					} elseif ($one['year'] == $s['year'] && $two['year'] == $s['year'] && $s['from'] > $s['to']) {
						//VRI 1.6 - season tied to the year 2017 accross 2018 and we are parsing dates at the beginning of 2017 due to beginning loop in 2016 (Rates Overview)
						if (($baseone + $s['from']) > $to) {
							continue;
						}
					}
				}
				//
				$allitems = explode(",", $s['iditems']);
				$allprices = !empty($s['idprices']) ? explode(",", $s['idprices']) : array();
				$inits = $baseone + $s['from'];
				if ($s['from'] < $s['to']) {
					$ends = $basetwo + $s['to'];
					//VRI 1.6 check if the inits must be set to the year after
					//ex. Season Jan 6 to Feb 12 - Booking Dec 31 to Jan 8 to charge Jan 6,7
					if ($sfrom > $s['from'] && $sto >= $s['from'] && $sfrom > $s['to'] && $sto <= $s['to'] && $s['from'] < $s['to'] && $sfrom > $sto) {
						$tmpbase = mktime(0, 0, 0, 1, 1, ($one['year'] + 1));
						$inits = $tmpbase + $s['from'];
					} elseif ($sfrom >= $s['from'] && $sfrom <= $s['to'] && $sto < $s['from'] && $sto < $s['to'] && $sfrom > $sto) {
						//VRI 1.6 - Season Dec 23 to Dec 29 - Booking Dec 29 to Jan 5
						$ends = $baseone + $s['to'];
					} elseif ($sfrom <= $s['from'] && $sfrom <= $s['to'] && $sto < $s['from'] && $sto < $s['to'] && $sfrom > $sto) {
						//VRI 1.6 - Season Dec 30 to Dec 31 - Booking Dec 29 to Jan 5
						$ends = $baseone + $s['to'];
					} elseif ($sfrom > $s['from'] && $sfrom > $s['to'] && $sto >= $s['from'] && ($sto >= $s['to'] || $sto <= $s['to']) && $sfrom > $sto) {
						//VRI 1.6 - Season Jan 1 to Jan 2 - Booking Dec 29 to Jan 5
						$inits = $basetwo + $s['from'];
					}
				} else {
					//between 2 years
					if ($baseone < $basetwo) {
						//ex. 29/12/2012 - 14/01/2013
						$ends = $basetwo + $s['to'];
					} else {
						if (($sfrom >= $s['from'] && $sto >= $s['from']) OR ($sfrom < $s['from'] && $sto >= $s['from'] && $sfrom > $s['to'] && $sto > $s['to'])) {
							//ex. 25/12 - 30/12 with init season on 20/12 OR 27/12 for counting 28,29,30/12
							$tmpbase = mktime(0, 0, 0, 1, 1, ($one['year'] + 1));
							$ends = $tmpbase + $s['to'];
						} else {
							//ex. 03/01 - 09/01
							$ends = $basetwo + $s['to'];
							$tmpbase = mktime(0, 0, 0, 1, 1, ($one['year'] - 1));
							$inits = $tmpbase + $s['from'];
						}
					}
				}
				//leap years
				if ($isleap == true) {
					$infoseason = getdate($inits);
					$leapts = mktime(0, 0, 0, 2, 29, $infoseason['year']);
					//VRI 1.6 added below && $infoseason['year'] == $one['year']
					//for those seasons like 2015 Dec 14 to 2016 Jan 5 and booking dates like 2016 Jan 1 to Jan 6 where 2015 is not leap
					if ($infoseason[0] >= $leapts && $infoseason['year'] == $one['year']) {
						$inits += 86400;
						$ends += 86400;
					}
				}
				//
				//Promotions
				$promotion = array();
				if ($s['promo'] == 1) {
					$daysadv = (($inits - time()) / 86400);
					$daysadv = $daysadv > 0 ? (int)ceil($daysadv) : 0;
					if (!empty($s['promodaysadv']) && $s['promodaysadv'] > $daysadv) {
						continue;
					} else {
						$promotion['todaydaysadv'] = $daysadv;
						$promotion['promodaysadv'] = $s['promodaysadv'];
						$promotion['promotxt'] = $s['promotxt'];
					}
				}
				//
				//week days
				$filterwdays = !empty($s['wdays']) ? true : false;
				$wdays = $filterwdays == true ? explode(';', $s['wdays']) : '';
				if (is_array($wdays) && count($wdays) > 0) {
					foreach ($wdays as $kw=>$wd) {
						if (strlen($wd) == 0) {
							unset($wdays[$kw]);
						}
					}
				}
				//
				//pickup must be after the begin of the season
				if ($s['pickupincl'] == 1) {
					$pickupinclok = false;
					if ($s['from'] < $s['to']) {
						if ($sfrom >= $s['from'] && $sfrom <= $s['to']) {
							$pickupinclok = true;
						}
					} else {
						if (($sfrom >= $s['from'] && $sfrom > $s['to']) || ($sfrom < $s['from'] && $sfrom <= $s['to'])) {
							$pickupinclok = true;
						}
					}
				} else {
					$pickupinclok = true;
				}
				//
				if ($pickupinclok == true) {
					foreach ($arr as $k => $a) {
						//Applied only to some types of price
						if (count($allprices) > 0 && !empty($allprices[0])) {
							if (!in_array("-" . $a['idprice'] . "-", $allprices)) {
								continue;
							}
						}
						//
						if (in_array("-" . $a['iditem'] . "-", $allitems)) {
							$affdays = 0;
							$season_fromdayts = $fromdayts;
							$is_dst = date('I', $season_fromdayts);
							for ($i = 0; $i < $a['days']; $i++) {
								$todayts = $season_fromdayts + ($i * 86400);
								$is_now_dst = date('I', $todayts);
								if ($is_dst != $is_now_dst) {
									//Daylight Saving Time has changed, check how
									if ((bool)$is_dst === true) {
										$todayts += 3600;
										$season_fromdayts += 3600;
									} else {
										$todayts -= 3600;
										$season_fromdayts -= 3600;
									}
									$is_dst = $is_now_dst;
								}
								//VRI 1.1 rev2
								if ($s['keepfirstdayrate'] == 1) {
									if ($fromdayts >= $inits && $fromdayts <= $ends) {
										$affdays = $a['days'];
									} else {
										$affdays = 0;
									}
									break;
								}
								//end VRI 1.1 rev2
								if ($todayts >= $inits && $todayts <= $ends) {
									//week days
									if ($filterwdays == true) {
										$checkwday = getdate($todayts);
										if (in_array($checkwday['wday'], $wdays)) {
											$affdays++;
										}
									} else {
										$affdays++;
									}
									//
								}
							}
							if ($affdays > 0) {
								$applyseasons = true;
								$dailyprice = $a['cost'] / $a['days'];
								$a['days'] = intval($a['days']);
								$pctval = 0;
								$absval = 0;
								//VikRentItems 1.1 for abs or pcent and values overrides
								if (intval($s['val_pcent']) == 2) {
									//percentage value
									$pctval = $s['diffcost'];
									if (strlen($s['losoverride']) > 0) {
										//values overrides
										$arrvaloverrides = array();
										$valovrparts = explode('_', $s['losoverride']);
										foreach ($valovrparts as $valovr) {
											if (!empty($valovr)) {
												$ovrinfo = explode(':', $valovr);
												if (strpos($ovrinfo[0], '-i') !== false) {
													$ovrinfo[0] = str_replace('-i', '', $ovrinfo[0]);
													if ((int)$ovrinfo[0] < $a['days']) {
														$arrvaloverrides[$a['days']] = $ovrinfo[1];
													}
												}
												$arrvaloverrides[$ovrinfo[0]] = $ovrinfo[1];
											}
										}
										if (array_key_exists($a['days'], $arrvaloverrides)) {
											$pctval = $arrvaloverrides[$a['days']];
										}
									}
									if (intval($s['type']) == 1) {
										//charge
										$cpercent = 100 + $pctval;
									} else {
										//discount
										$cpercent = 100 - $pctval;
									}
									$dailysum = ($dailyprice * $cpercent / 100);
									$newprice = $dailysum * $affdays;
								} else {
									//absolute value
									$absval = $s['diffcost'];
									if (strlen($s['losoverride']) > 0) {
										//values overrides
										$arrvaloverrides = array();
										$valovrparts = explode('_', $s['losoverride']);
										foreach ($valovrparts as $valovr) {
											if (!empty($valovr)) {
												$ovrinfo = explode(':', $valovr);
												if (strpos($ovrinfo[0], '-i') !== false) {
													$ovrinfo[0] = str_replace('-i', '', $ovrinfo[0]);
													if ((int)$ovrinfo[0] < $a['days']) {
														$arrvaloverrides[$a['days']] = $ovrinfo[1];
													}
												}
												$arrvaloverrides[$ovrinfo[0]] = $ovrinfo[1];
											}
										}
										if (array_key_exists($a['days'], $arrvaloverrides)) {
											$absval = $arrvaloverrides[$a['days']];
										}
									}
									if (intval($s['type']) == 1) {
										//charge
										$dailysum = ($dailyprice + $absval);
										$newprice = $dailysum * $affdays;
									} else {
										//discount
										$dailysum = ($dailyprice - $absval);
										$newprice = $dailysum * $affdays;
									}
								}
								//end VikRentItems 1.1 for abs or pcent and values overrides
								//VikRentItems 1.3
								if (!empty($s['roundmode'])) {
									$newprice = round($newprice, 0, constant($s['roundmode']));
								}
								//
								//Promotions (only if no value overrides set the amount to 0)
								if (count($promotion) > 0 && ($absval > 0 || $pctval > 0)) {
									$mem[$k]['promotion'] = $promotion;
								}
								//
								if (!in_array($s['id'], $mem[$k]['spids'])) {
									$mem[$k]['spids'][] = $s['id'];
								}
								$mem[$k]['sum'][] = $newprice;
								$mem[$k]['daysused'] += $affdays;
								$itemschange[] = $a['iditem'];
							}
						}
					}
				}
			}
			if ($applyseasons) {
				foreach ($mem as $k => $v) {
					if ($v['daysused'] > 0 && @ count($v['sum']) > 0) {
						$newprice = 0;
						$dailyprice = $arr[$k]['cost'] / $arr[$k]['days'];
						$restdays = $arr[$k]['days'] - $v['daysused'];
						$addrest = $restdays * $dailyprice;
						$newprice += $addrest;
						foreach ($v['sum'] as $add) {
							$newprice += $add;
						}
						//Promotions
						if (array_key_exists('promotion', $v)) {
							$arr[$k]['promotion'] = $v['promotion'];
						}
						//
						$arr[$k]['cost'] = $newprice;
						$arr[$k]['affdays'] = $v['daysused'];
						if (array_key_exists('spids', $v) && count($v['spids']) > 0) {
							$arr[$k]['spids'] = $v['spids'];
						}
					}
				}
			}
		}
		//week days with no season
		$itemschange = array_unique($itemschange);
		$q = "SELECT * FROM `#__vikrentitems_seasons` WHERE ".($pickup !== null ? "(`locations`='0' OR `locations`=" . $dbo->quote($pickup) . ") AND " : "")."((`from` = 0 AND `to` = 0) OR (`from` IS NULL AND `to` IS NULL));";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$specials = $dbo->loadAssocList();
			$vri_tn->translateContents($specials, '#__vikrentitems_seasons');
			$applyseasons = false;
			unset($mem);
			$mem = array();
			foreach ($arr as $k => $a) {
				$mem[$k]['daysused'] = 0;
				$mem[$k]['sum'] = array();
				$mem[$k]['spids'] = array();
			}
			foreach ($specials as $s) {
				//Special Price tied to the year
				if (!empty($s['year']) && $s['year'] > 0) {
					if ($one['year'] != $s['year']) {
						continue;
					}
				}
				//
				$allitems = explode(",", $s['iditems']);
				$allprices = !empty($s['idprices']) ? explode(",", $s['idprices']) : array();
				//week days
				$filterwdays = !empty($s['wdays']) ? true : false;
				$wdays = $filterwdays == true ? explode(';', $s['wdays']) : '';
				if (is_array($wdays) && count($wdays) > 0) {
					foreach ($wdays as $kw=>$wd) {
						if (strlen($wd) == 0) {
							unset($wdays[$kw]);
						}
					}
				}
				//
				foreach ($arr as $k => $a) {
					//only items with no price modifications from seasons
					//Applied only to some types of price
					if (count($allprices) > 0 && !empty($allprices[0])) {
						if (!in_array("-" . $a['idprice'] . "-", $allprices)) {
							continue;
						}
					}
					//
					if (in_array("-" . $a['iditem'] . "-", $allitems) && !in_array($a['iditem'], $itemschange)) {
						$affdays = 0;
						$season_fromdayts = $fromdayts;
						$is_dst = date('I', $season_fromdayts);
						for ($i = 0; $i < $a['days']; $i++) {
							$todayts = $season_fromdayts + ($i * 86400);
							$is_now_dst = date('I', $todayts);
							if ($is_dst != $is_now_dst) {
								//Daylight Saving Time has changed, check how
								if ((bool)$is_dst === true) {
									$todayts += 3600;
									$season_fromdayts += 3600;
								} else {
									$todayts -= 3600;
									$season_fromdayts -= 3600;
								}
								$is_dst = $is_now_dst;
							}
							//week days
							if ($filterwdays == true) {
								$checkwday = getdate($todayts);
								if (in_array($checkwday['wday'], $wdays)) {
									$affdays++;
								}
							}
							//
						}
						if ($affdays > 0) {
							$applyseasons = true;
							$dailyprice = $a['cost'] / $a['days'];
							$a['days'] = intval($a['days']);
							//VikRentItems 1.1 for abs or pcent and values overrides
							if (intval($s['val_pcent']) == 2) {
								//percentage value
								$pctval = $s['diffcost'];
								if (strlen($s['losoverride']) > 0) {
									//values overrides
									$arrvaloverrides = array();
									$valovrparts = explode('_', $s['losoverride']);
									foreach ($valovrparts as $valovr) {
										if (!empty($valovr)) {
											$ovrinfo = explode(':', $valovr);
											if (strpos($ovrinfo[0], '-i') !== false) {
												$ovrinfo[0] = str_replace('-i', '', $ovrinfo[0]);
												if ((int)$ovrinfo[0] < $a['days']) {
													$arrvaloverrides[$a['days']] = $ovrinfo[1];
												}
											}
											$arrvaloverrides[$ovrinfo[0]] = $ovrinfo[1];
										}
									}
									if (array_key_exists($a['days'], $arrvaloverrides)) {
										$pctval = $arrvaloverrides[$a['days']];
									}
								}
								if (intval($s['type']) == 1) {
									//charge
									$cpercent = 100 + $pctval;
								} else {
									//discount
									$cpercent = 100 - $pctval;
								}
								$dailysum = ($dailyprice * $cpercent / 100);
								$newprice = $dailysum * $affdays;
							} else {
								//absolute value
								$absval = $s['diffcost'];
								if (strlen($s['losoverride']) > 0) {
									//values overrides
									$arrvaloverrides = array();
									$valovrparts = explode('_', $s['losoverride']);
									foreach ($valovrparts as $valovr) {
										if (!empty($valovr)) {
											$ovrinfo = explode(':', $valovr);
											if (strpos($ovrinfo[0], '-i') !== false) {
												$ovrinfo[0] = str_replace('-i', '', $ovrinfo[0]);
												if ((int)$ovrinfo[0] < $a['days']) {
													$arrvaloverrides[$a['days']] = $ovrinfo[1];
												}
											}
											$arrvaloverrides[$ovrinfo[0]] = $ovrinfo[1];
										}
									}
									if (array_key_exists($a['days'], $arrvaloverrides)) {
										$absval = $arrvaloverrides[$a['days']];
									}
								}
								if (intval($s['type']) == 1) {
									//charge
									$dailysum = ($dailyprice + $absval);
									$newprice = $dailysum * $affdays;
								} else {
									//discount
									$dailysum = ($dailyprice - $absval);
									$newprice = $dailysum * $affdays;
								}
							}
							//end VikRentItems 1.1 for abs or pcent and values overrides
							//VikRentItems 1.3
							if (!empty($s['roundmode'])) {
								$newprice = round($newprice, 0, constant($s['roundmode']));
							}
							//
							if (!in_array($s['id'], $mem[$k]['spids'])) {
								$mem[$k]['spids'][] = $s['id'];
							}
							$mem[$k]['sum'][] = $newprice;
							$mem[$k]['daysused'] += $affdays;
						}
					}
				}
			}
			if ($applyseasons) {
				foreach ($mem as $k => $v) {
					if ($v['daysused'] > 0 && @ count($v['sum']) > 0) {
						$newprice = 0;
						$dailyprice = $arr[$k]['cost'] / $arr[$k]['days'];
						$restdays = $arr[$k]['days'] - $v['daysused'];
						$addrest = $restdays * $dailyprice;
						$newprice += $addrest;
						foreach ($v['sum'] as $add) {
							$newprice += $add;
						}
						$arr[$k]['cost'] = $newprice;
						$arr[$k]['affdays'] = $v['daysused'];
						if (array_key_exists('spids', $v) && count($v['spids']) > 0) {
							$arr[$k]['spids'] = $v['spids'];
						}
					}
				}
			}
		}
		//end week days with no season
		return $arr;
	}
	
	public static function applyItemDiscounts($tar, $iditem, $quantity) {
		$dbo = JFactory::getDBO();
		$quantity = (int)$quantity < 1 ? 1 : $quantity;
		$q = "SELECT * FROM `#__vikrentitems_discountsquants` WHERE `iditems` LIKE '%-".intval($iditem)."-%' AND (`quantity`='".intval($quantity)."' OR (`quantity` < ".intval($quantity)." AND `ifmorequant` = 1)) ORDER BY `#__vikrentitems_discountsquants`.`quantity` DESC LIMIT 1;";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$discount = $dbo->loadAssocList();
			foreach ($tar as $k => $t) {
				$tar[$k]['beforediscount'] = $t['cost'];
				if ($discount[0]['val_pcent'] == 1) {
					//absolute value
					$tar[$k]['discount'] = $discount[0]['diffcost'];
					if ($discount[0]['diffcost'] > $t['cost']) {
						$tar[$k]['cost'] = 0;
					} else {
						$tar[$k]['cost'] = $t['cost'] - $discount[0]['diffcost'];
					}
				} else {
					//percentage value
					$tar[$k]['discount'] = $discount[0]['diffcost'].'%';
					$oper = 100 - $discount[0]['diffcost'];
					$tar[$k]['cost'] = $t['cost'] * $oper / 100;
				}
			}
		}
		return $tar;
	}
	
	public static function areTherePayments() {
		$dbo = JFactory::getDBO();
		$q = "SELECT `id` FROM `#__vikrentitems_gpayments` WHERE `published`='1';";
		$dbo->setQuery($q);
		$dbo->execute();
		return $dbo->getNumRows() > 0 ? true : false;
	}

	public static function getPayment($idp, $vri_tn = null) {
		if (!empty($idp)) {
			if (strstr($idp, '=') !== false) {
				$parts = explode('=', $idp);
				$idp = $parts[0];
			}
			$dbo = JFactory::getDBO();
			$q = "SELECT * FROM `#__vikrentitems_gpayments` WHERE `id`=" . $dbo->quote($idp) . ";";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() == 1) {
				$payment = $dbo->loadAssocList();
				if (is_object($vri_tn)) {
					$vri_tn->translateContents($payment, '#__vikrentitems_gpayments');
				}
				return $payment[0];
			} else {
				return false;
			}
		}
		return false;
	}
	
	public static function applyHourlyPrices($arrtar, $hoursdiff) {
		$dbo = JFactory::getDBO();
		$q = "SELECT * FROM `#__vikrentitems_dispcosthours` WHERE `hours`='" . $hoursdiff . "' ORDER BY `#__vikrentitems_dispcosthours`.`cost` ASC, `#__vikrentitems_dispcosthours`.`iditem` ASC;";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$hourtars = $dbo->loadAssocList();
			$hourarrtar = array();
			foreach ($hourtars as $tar) {
				$hourarrtar[$tar['iditem']][] = $tar;
			}
			foreach ($arrtar as $iditem => $tar) {
				if (array_key_exists($iditem, $hourarrtar)) {
					foreach ($tar as $ind => $fare) {
						//check if idprice exists in $hourarrtar
						foreach ($hourarrtar[$iditem] as $hind => $hfare) {
							if ($fare['idprice'] == $hfare['idprice']) {
								$arrtar[$iditem][$ind]['id'] = $hourarrtar[$iditem][$hind]['id'];
								$arrtar[$iditem][$ind]['cost'] = $hourarrtar[$iditem][$hind]['cost'];
								$arrtar[$iditem][$ind]['attrdata'] = $hourarrtar[$iditem][$hind]['attrdata'];
								$arrtar[$iditem][$ind]['hours'] = $hourarrtar[$iditem][$hind]['hours'];
							}
						}
					}
				}
			}
		}
		return $arrtar;
	}
	
	public static function applyHourlyPricesItem($arrtar, $hoursdiff, $iditem, $filterprice = false) {
		$dbo = JFactory::getDBO();
		$q = "SELECT * FROM `#__vikrentitems_dispcosthours` WHERE `hours`='" . $hoursdiff . "' AND `iditem`=" . $dbo->quote($iditem) . "".($filterprice == true ? "  AND `idprice`='".$arrtar[0]['idprice']."'" : "")." ORDER BY `#__vikrentitems_dispcosthours`.`cost` ASC;";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$arrtar = $dbo->loadAssocList();
			foreach ($arrtar as $k => $v) {
				$arrtar[$k]['days'] = 1;
			}
		}
		return $arrtar;
	}
	
	public static function extraHoursSetPreviousFare($arrtar, $ehours, $daysdiff) {
		//set the fare to the days of rental - 1 where hours charges exist
		//to be used when the hours charges need to be applied after the special prices
		$dbo = JFactory::getDBO();
		$iditems = array_keys($arrtar);
		if (count($iditems) > 0 && $daysdiff > 1) {
			$q = "SELECT * FROM `#__vikrentitems_hourscharges` WHERE `ehours`='".$ehours."' AND `iditem` IN (".implode(",", $iditems).");";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() > 0) {
				$ehcharges = $dbo->loadAssocList();
				$arrehcharges = array();
				foreach ($ehcharges as $ehc) {
					$arrehcharges[$ehc['iditem']][]=$ehc;
				}
				$iditems = array_keys($arrehcharges);
				$newdaysdiff = $daysdiff - 1;
				$q = "SELECT * FROM `#__vikrentitems_dispcost` WHERE `days`='".$newdaysdiff."' AND `iditem` IN (".implode(",", $iditems).");";
				$dbo->setQuery($q);
				$dbo->execute();
				if ($dbo->getNumRows() > 0) {
					//only if there are fares for ($daysdiff - 1) otherwise dont apply extra hours charges
					$prevdaytars = $dbo->loadAssocList();
					$prevdayarrtar = array();
					foreach ($prevdaytars as $pdtar) {
						$prevdayarrtar[$pdtar['iditem']][]=$pdtar;
					}
					//set fares for 1 day before of rental
					$newdispcostvals = array();
					$newdispcostattr = array();
					foreach ($arrehcharges as $idc => $ehc) {
						if (array_key_exists($idc, $prevdayarrtar)) {
							foreach ($prevdayarrtar[$idc] as $vp) {
								foreach ($ehc as $hc) {
									if ($vp['idprice'] == $hc['idprice']) {
										$newdispcostvals[$idc][$hc['idprice']] = $vp['cost'];
										$newdispcostattr[$idc][$hc['idprice']] = $vp['attrdata'];
									}
								}
							}
						}
					}
					if (count($newdispcostvals) > 0) {
						foreach ($arrtar as $idc => $tar) {
							if (array_key_exists($idc, $newdispcostvals)) {
								foreach ($tar as $krecp => $recp) {
									if (array_key_exists($recp['idprice'], $newdispcostvals[$idc])) {
										$arrtar[$idc][$krecp]['cost'] = $newdispcostvals[$idc][$recp['idprice']];
										$arrtar[$idc][$krecp]['attrdata'] = $newdispcostattr[$idc][$recp['idprice']];
										$arrtar[$idc][$krecp]['days'] = $newdaysdiff;
										$arrtar[$idc][$krecp]['ehours'] = $ehours;
									}
								}
							}
						}
					}
					//
				}
			}
		}
		return $arrtar;
	}
	
	public static function extraHoursSetPreviousFareItem($tar, $iditem, $ehours, $daysdiff, $filterprice = false) {
		//set the fare to the days of rental - 1 where hours charges exist
		//to be used when the hours charges need to be applied after the special prices
		$dbo = JFactory::getDBO();
		if ($daysdiff > 1) {
			$q = "SELECT * FROM `#__vikrentitems_hourscharges` WHERE `ehours`='".$ehours."' AND `iditem`='".$iditem."'".($filterprice == true ? " AND `idprice`='".$tar[0]['idprice']."'" : "").";";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() > 0) {
				$ehcharges = $dbo->loadAssocList();
				$newdaysdiff = $daysdiff - 1;
				$q = "SELECT * FROM `#__vikrentitems_dispcost` WHERE `days`='".$newdaysdiff."' AND `iditem`='".$iditem."'".($filterprice == true ? " AND `idprice`='".$tar[0]['idprice']."'" : "").";";
				$dbo->setQuery($q);
				$dbo->execute();
				if ($dbo->getNumRows() > 0) {
					//only if there are fares for ($daysdiff - 1) otherwise dont apply extra hours charges
					$prevdaytars = $dbo->loadAssocList();
					//set fares for 1 day before of rental
					$newdispcostvals = array();
					$newdispcostattr = array();
					foreach ($ehcharges as $ehc) {
						foreach ($prevdaytars as $vp) {
							if ($vp['idprice'] == $ehc['idprice']) {
								$newdispcostvals[$ehc['idprice']] = $vp['cost'];
								$newdispcostattr[$ehc['idprice']] = $vp['attrdata'];
							}
						}
					}
					if (count($newdispcostvals) > 0) {
						foreach ($tar as $kp => $f) {
							if (array_key_exists($f['idprice'], $newdispcostvals)) {
								$tar[$kp]['cost'] = $newdispcostvals[$f['idprice']];
								$tar[$kp]['attrdata'] = $newdispcostattr[$f['idprice']];
								$tar[$kp]['days'] = $newdaysdiff;
								$tar[$kp]['ehours'] = $ehours;
							}
						}
					}
					//
				}
			}
		}
		return $tar;
	}
	
	public static function applyExtraHoursChargesPrices($arrtar, $ehours, $daysdiff, $aftersp = false) {
		$dbo = JFactory::getDBO();
		$iditems = array_keys($arrtar);
		if (count($iditems) > 0 && $daysdiff > 1) {
			$q = "SELECT * FROM `#__vikrentitems_hourscharges` WHERE `ehours`='".$ehours."' AND `iditem` IN (".implode(",", $iditems).");";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() > 0) {
				$ehcharges = $dbo->loadAssocList();
				$arrehcharges = array();
				foreach ($ehcharges as $ehc) {
					$arrehcharges[$ehc['iditem']][]=$ehc;
				}
				$iditems = array_keys($arrehcharges);
				$newdaysdiff = $daysdiff - 1;
				if ($aftersp == true) {
					//after having applied special prices, dont consider the fares for ($daysdiff - 1)
					//apply extra hours charges
					$newdispcostvals = array();
					$newdispcostattr = array();
					foreach ($arrehcharges as $idc => $ehc) {
						if (array_key_exists($idc, $arrtar)) {
							foreach ($arrtar[$idc] as $vp) {
								foreach ($ehc as $hc) {
									if ($vp['idprice'] == $hc['idprice']) {
										$newdispcostvals[$idc][$hc['idprice']] = $vp['cost'] + $hc['cost'];
										$newdispcostattr[$idc][$hc['idprice']] = $vp['attrdata'];
									}
								}
							}
						}
					}
					if (count($newdispcostvals) > 0) {
						foreach ($arrtar as $idc => $tar) {
							if (array_key_exists($idc, $newdispcostvals)) {
								foreach ($tar as $krecp => $recp) {
									if (array_key_exists($recp['idprice'], $newdispcostvals[$idc])) {
										$arrtar[$idc][$krecp]['cost'] = $newdispcostvals[$idc][$recp['idprice']];
										$arrtar[$idc][$krecp]['attrdata'] = $newdispcostattr[$idc][$recp['idprice']];
										$arrtar[$idc][$krecp]['days'] = $newdaysdiff;
										$arrtar[$idc][$krecp]['ehours'] = $ehours;
									}
								}
							}
						}
					}
					//
				} else {
					//before applying special prices
					$q = "SELECT * FROM `#__vikrentitems_dispcost` WHERE `days`='".$newdaysdiff."' AND `iditem` IN (".implode(",", $iditems).");";
					$dbo->setQuery($q);
					$dbo->execute();
					if ($dbo->getNumRows() > 0) {
						//only if there are fares for ($daysdiff - 1) otherwise dont apply extra hours charges
						$prevdaytars = $dbo->loadAssocList();
						$prevdayarrtar = array();
						foreach ($prevdaytars as $pdtar) {
							$prevdayarrtar[$pdtar['iditem']][]=$pdtar;
						}
						//apply extra hours charges
						$newdispcostvals = array();
						$newdispcostattr = array();
						foreach ($arrehcharges as $idc => $ehc) {
							if (array_key_exists($idc, $prevdayarrtar)) {
								foreach ($prevdayarrtar[$idc] as $vp) {
									foreach ($ehc as $hc) {
										if ($vp['idprice'] == $hc['idprice']) {
											$newdispcostvals[$idc][$hc['idprice']] = $vp['cost'] + $hc['cost'];
											$newdispcostattr[$idc][$hc['idprice']] = $vp['attrdata'];
										}
									}
								}
							}
						}
						if (count($newdispcostvals) > 0) {
							foreach ($arrtar as $idc => $tar) {
								if (array_key_exists($idc, $newdispcostvals)) {
									foreach ($tar as $krecp => $recp) {
										if (array_key_exists($recp['idprice'], $newdispcostvals[$idc])) {
											$arrtar[$idc][$krecp]['cost'] = $newdispcostvals[$idc][$recp['idprice']];
											$arrtar[$idc][$krecp]['attrdata'] = $newdispcostattr[$idc][$recp['idprice']];
											$arrtar[$idc][$krecp]['days'] = $newdaysdiff;
											$arrtar[$idc][$krecp]['ehours'] = $ehours;
										}
									}
								}
							}
						}
						//
					}
				}
			}
		}
		return $arrtar;
	}
	
	public static function applyExtraHoursChargesItem($tar, $iditem, $ehours, $daysdiff, $aftersp = false, $filterprice = false, $retarray = false) {
		$dbo = JFactory::getDBO();
		$newdaysdiff = $daysdiff;
		if ($daysdiff > 1) {
			$q = "SELECT * FROM `#__vikrentitems_hourscharges` WHERE `ehours`='".$ehours."' AND `iditem`='".$iditem."'".($filterprice == true ? " AND `idprice`='".$tar[0]['idprice']."'" : "").";";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() > 0) {
				$ehcharges = $dbo->loadAssocList();
				$newdaysdiff = $daysdiff - 1;
				if ($aftersp == true) {
					//after having applied special prices, dont consider the fares for ($daysdiff - 1) because done already
					//apply extra hours charges
					$newdispcostvals = array();
					$newdispcostattr = array();
					foreach ($ehcharges as $ehc) {
						foreach ($tar as $vp) {
							if ($vp['idprice'] == $ehc['idprice']) {
								$newdispcostvals[$ehc['idprice']] = $vp['cost'] + $ehc['cost'];
								$newdispcostattr[$ehc['idprice']] = $vp['attrdata'];
							}
						}
					}
					if (count($newdispcostvals) > 0) {
						foreach ($tar as $kt => $f) {
							if (array_key_exists($f['idprice'], $newdispcostvals)) {
								$tar[$kt]['cost'] = $newdispcostvals[$f['idprice']];
								$tar[$kt]['attrdata'] = $newdispcostattr[$f['idprice']];
								$tar[$kt]['days'] = $newdaysdiff;
								$tar[$kt]['ehours'] = $ehours;
							}
						}
					}
					//
				} else {
					//before applying special prices
					$q = "SELECT * FROM `#__vikrentitems_dispcost` WHERE `days`='".$newdaysdiff."' AND `iditem`='".$iditem."'".($filterprice == true ? " AND `idprice`='".$tar[0]['idprice']."'" : "").";";
					$dbo->setQuery($q);
					$dbo->execute();
					if ($dbo->getNumRows() > 0) {
						//only if there are fares for ($daysdiff - 1) otherwise dont apply extra hours charges
						$prevdaytars = $dbo->loadAssocList();
						//apply extra hours charges
						$newdispcostvals = array();
						$newdispcostattr = array();
						foreach ($ehcharges as $ehc) {
							foreach ($prevdaytars as $vp) {
								if ($vp['idprice'] == $ehc['idprice']) {
									$newdispcostvals[$ehc['idprice']] = $vp['cost'] + $ehc['cost'];
									$newdispcostattr[$ehc['idprice']] = $vp['attrdata'];
								}
							}
						}
						if (count($newdispcostvals) > 0) {
							foreach ($tar as $kt => $f) {
								if (array_key_exists($f['idprice'], $newdispcostvals)) {
									$tar[$kt]['cost'] = $newdispcostvals[$f['idprice']];
									$tar[$kt]['attrdata'] = $newdispcostattr[$f['idprice']];
									$tar[$kt]['days'] = $newdaysdiff;
									$tar[$kt]['ehours'] = $ehours;
								}
							}
						}
						//
					}
				}
			}
		}
		if ($retarray == true) {
			$ret = array();
			$ret['return'] = $tar;
			$ret['days'] = $newdaysdiff;
			return $ret;
		} else {
			return $tar;
		}
	}
	
	public static function sayMonth($idm) {
		switch ($idm) {
			case '12' :
				$ret = JText::_('VRMONTHTWELVE');
				break;
			case '11' :
				$ret = JText::_('VRMONTHELEVEN');
				break;
			case '10' :
				$ret = JText::_('VRMONTHTEN');
				break;
			case '9' :
				$ret = JText::_('VRMONTHNINE');
				break;
			case '8' :
				$ret = JText::_('VRMONTHEIGHT');
				break;
			case '7' :
				$ret = JText::_('VRMONTHSEVEN');
				break;
			case '6' :
				$ret = JText::_('VRMONTHSIX');
				break;
			case '5' :
				$ret = JText::_('VRMONTHFIVE');
				break;
			case '4' :
				$ret = JText::_('VRMONTHFOUR');
				break;
			case '3' :
				$ret = JText::_('VRMONTHTHREE');
				break;
			case '2' :
				$ret = JText::_('VRMONTHTWO');
				break;
			default :
				$ret = JText::_('VRMONTHONE');
				break;
		}
		return $ret;
	}

	public static function valuecsv($value) {
		if (preg_match("/\"/", $value)) {
			$value = '"'.str_replace('"', '""', $value).'"';
		}
		$value = str_replace(',', ' ', $value);
		$value = str_replace(';', ' ', $value);
		return $value;
	}

	public static function setDropDatePlus($skipsession = false) {
		$dbo = JFactory::getDBO();
		if ($skipsession) {
			$q = "SELECT `setting` FROM `#__vikrentitems_config` WHERE `param`='setdropdplus';";
			$dbo->setQuery($q);
			$dbo->execute();
			$s = $dbo->loadAssocList();
			return $s[0]['setting'];
		} else {
			$session = JFactory::getSession();
			$sval = $session->get('setDropDatePlus', '');
			if (!empty($sval)) {
				return $sval;
			} else {
				$q = "SELECT `setting` FROM `#__vikrentitems_config` WHERE `param`='setdropdplus';";
				$dbo->setQuery($q);
				$dbo->execute();
				$s = $dbo->loadAssocList();
				$session->set('setDropDatePlus', $s[0]['setting']);
				return $s[0]['setting'];
			}
		}
	}
	
	public static function getMinDaysAdvance($skipsession = false) {
		$dbo = JFactory::getDBO();
		if ($skipsession) {
			$q = "SELECT `setting` FROM `#__vikrentitems_config` WHERE `param`='mindaysadvance';";
			$dbo->setQuery($q);
			$dbo->execute();
			$s = $dbo->loadAssocList();
			return (int)$s[0]['setting'];
		} else {
			$session = JFactory::getSession();
			$sval = $session->get('vriminDaysAdvance', '');
			if (!empty($sval)) {
				return (int)$sval;
			} else {
				$q = "SELECT `setting` FROM `#__vikrentitems_config` WHERE `param`='mindaysadvance';";
				$dbo->setQuery($q);
				$dbo->execute();
				$s = $dbo->loadAssocList();
				$session->set('vriminDaysAdvance', $s[0]['setting']);
				return (int)$s[0]['setting'];
			}
		}
	}
	
	public static function getMaxDateFuture($skipsession = false) {
		$dbo = JFactory::getDBO();
		if ($skipsession) {
			$q = "SELECT `setting` FROM `#__vikrentitems_config` WHERE `param`='maxdate';";
			$dbo->setQuery($q);
			$dbo->execute();
			$s = $dbo->loadAssocList();
			return $s[0]['setting'];
		} else {
			$session = JFactory::getSession();
			$sval = $session->get('vrimaxDateFuture', '');
			if (!empty($sval)) {
				return $sval;
			} else {
				$q = "SELECT `setting` FROM `#__vikrentitems_config` WHERE `param`='maxdate';";
				$dbo->setQuery($q);
				$dbo->execute();
				$s = $dbo->loadAssocList();
				$session->set('vrimaxDateFuture', $s[0]['setting']);
				return $s[0]['setting'];
			}
		}
	}

	public static function getFirstWeekDay($skipsession = false) {
		if ($skipsession) {
			$dbo = JFactory::getDBO();
			$q = "SELECT `setting` FROM `#__vikrentitems_config` WHERE `param`='firstwday';";
			$dbo->setQuery($q);
			$dbo->execute();
			$s = $dbo->loadAssocList();
			return $s[0]['setting'];
		} else {
			$session = JFactory::getSession();
			$sval = $session->get('vrifirstWeekDay', '');
			if (strlen($sval)) {
				return $sval;
			} else {
				$dbo = JFactory::getDBO();
				$q = "SELECT `setting` FROM `#__vikrentitems_config` WHERE `param`='firstwday';";
				$dbo->setQuery($q);
				$dbo->execute();
				$s = $dbo->loadAssocList();
				$session->set('vrifirstWeekDay', $s[0]['setting']);
				return $s[0]['setting'];
			}
		}
	}

	public static function getLoginReturnUrl($url = '', $xhtml = false) {
		if ( empty($url) ) {
			// get current URL
			$url = JURI::current();

			$qs = JFactory::getApplication()->input->server->get('QUERY_STRING', '', 'string');
			// concat query string is not empty
			return $url . (strlen($qs) ? '?'.$qs : '');
		}
		// parse given URL
		$parts = parse_url(Juri::root());
		// build host
		$host = (!empty($parts['scheme']) ? $parts['scheme'] . '://' : '') . (!empty($parts['host']) ? $parts['host'] : '');
		// concat host (use trailing slash if not exists) and routed URL (remove first slash if exists)
		return $host.(!strlen($host) || $host[strlen($host)-1] != '/' ? '/' : '').(strlen($route = JRoute::_($url, $xhtml)) && $route[0] == '/' ? substr($route, 1) : $route);
	}

	public static function getSendEmailWhen() {
		$dbo = JFactory::getDBO();
		$q = "SELECT `setting` FROM `#__vikrentitems_config` WHERE `param`='emailsendwhen';";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$cval = $dbo->loadAssocList();
			return intval($cval[0]['setting']) > 1 ? 2 : 1;
		} else {
			$q = "INSERT INTO `#__vikrentitems_config` (`param`,`setting`) VALUES ('emailsendwhen','1');";
			$dbo->setQuery($q);
			$dbo->execute();
		}
		return 1;
	}

	public static function getKitRelatedItems($iditem) {
		//VRI 1.5 - Get all the related items to this parent or child ID for the Group/Set of Items.
		$dbo = JFactory::getDBO();
		$relations = array();
		//check if it's a parent ID, so a Group/Set of Items
		$q = "SELECT * FROM `#__vikrentitems_groupsrel` WHERE `parentid`=".(int)$iditem.";";
		$dbo->setQuery($q);
		$dbo->execute();
		if ($dbo->getNumRows() > 0) {
			$rels = $dbo->loadAssocList();
			//get all the information about the children products
			foreach ($rels as $rel) {
				array_push($relations, array(
					'iditem' => $rel['childid'],
					'units' => $rel['units'],
					'isgroup' => 1
				));
			}
		} else {
			//check if it's a child ID, so part of a Group/Set of Items, to update its parent
			$q = "SELECT `parentid` FROM `#__vikrentitems_groupsrel` WHERE `childid`=".(int)$iditem." GROUP BY `parentid`;";
			$dbo->setQuery($q);
			$dbo->execute();
			if ($dbo->getNumRows() > 0) {
				$rels = $dbo->loadAssocList();
				//get all the information about the parent group product
				foreach ($rels as $rel) {
					array_push($relations, array(
						'iditem' => $rel['parentid'],
						'units' => 1,
						'isgroup' => 0
					));
				}
			}
		}
		return $relations;
	}
	
	public static function displayPaymentParameters($pfile, $pparams = '') {
		$html = '---------';
		$arrparams = !empty($pparams) ? json_decode($pparams, true) : array();
		if (file_exists(VRI_ADMIN_PATH.DS.'payments'.DS.$pfile) && !empty($pfile)) {
			require_once(VRI_ADMIN_PATH.DS.'payments'.DS.$pfile);
			if (method_exists('vikRentItemsPayment', 'getAdminParameters')) {
				$pconfig = vikRentItemsPayment::getAdminParameters();
				if (count($pconfig) > 0) {
					$html = '';
					foreach ($pconfig as $value => $cont) {
						if (empty($value)) {
							continue;
						}
						$labelparts = explode('//', $cont['label']);
						$label = $labelparts[0];
						$labelhelp = count($labelparts) > 1 ? $labelparts[1] : '';
						$html .= '<div class="vikpaymentparam">';
						if (strlen($label) > 0) {
							$html .= '<span class="vikpaymentparamlabel">'.$label.'</span>';
						}
						switch ($cont['type']) {
							case 'custom':
								$html .= $cont['html'];
								break;
							case 'select':
								$html .= '<span class="vikpaymentparaminput">' .
										'<select name="vikpaymentparams['.$value.']">';
								foreach ($cont['options'] as $poption) {
									$html .= '<option value="'.$poption.'"'.(array_key_exists($value, $arrparams) && $poption == $arrparams[$value] ? ' selected="selected"' : '').'>'.$poption.'</option>';
								}
								$html .= '</select></span>';
								break;
							default:
								$html .= '<span class="vikpaymentparaminput">' .
										'<input type="text" name="vikpaymentparams['.$value.']" value="'.(array_key_exists($value, $arrparams) ? $arrparams[$value] : '').'" size="20"/>' .
										'</span>';
								break;
						}
						if (strlen($labelhelp) > 0) {
							$html .= '<span class="vikpaymentparamlabelhelp">'.$labelhelp.'</span>';
						}
						$html .= '</div>';
					}
				}
			}
		}
		return $html;
	}

	public static function getVriApplication() {
		if (!class_exists('VriApplication')) {
			require_once(VRI_ADMIN_PATH.DS.'helpers'.DS.'jv_helper.php');
		}
		return new VriApplication();
	}

	public static function caniWrite($path) {
		if ($path[strlen($path) - 1] == '/') {
			// ricorsivo return a temporary file path
			return self::caniWrite($path . uniqid(mt_rand()) . '.tmp');
		}
		if (is_dir($path)) {
			return self::caniWrite($path . DIRECTORY_SEPARATOR . uniqid(mt_rand()) . '.tmp');
		}
		// check tmp file for read/write capabilities
		$rm = file_exists($path);
		$f = @fopen($path, 'a');
		if ($f === false) {
			return false;
		}
		fclose($f);
		if (!$rm) {
			unlink($path);
		}
		return true;
	}

	public static function totElements($arr) {
		$n = 0;
		if (is_array($arr)) {
			foreach ($arr as $a) {
				if (!empty($a)) {
					$n++;
				}
			}
		}
		return $n;
	}

	public static function validEmail($email) {
		$isValid = true;
		$atIndex = strrpos($email, "@");
		if (is_bool($atIndex) && !$atIndex) {
			$isValid = false;
		} else {
			$domain = substr($email, $atIndex +1);
			$local = substr($email, 0, $atIndex);
			$localLen = strlen($local);
			$domainLen = strlen($domain);
			if ($localLen < 1 || $localLen > 64) {
				// local part length exceeded
				$isValid = false;
			} else
				if ($domainLen < 1 || $domainLen > 255) {
					// domain part length exceeded
					$isValid = false;
				} else
					if ($local[0] == '.' || $local[$localLen -1] == '.') {
						// local part starts or ends with '.'
						$isValid = false;
					} else
						if (preg_match('/\\.\\./', $local)) {
							// local part has two consecutive dots
							$isValid = false;
						} else
							if (!preg_match('/^[A-Za-z0-9\\-\\.]+$/', $domain)) {
								// character not valid in domain part
								$isValid = false;
							} else
								if (preg_match('/\\.\\./', $domain)) {
									// domain part has two consecutive dots
									$isValid = false;
								} else
									if (!preg_match('/^(\\\\.|[A-Za-z0-9!#%&`_=\\/$\'*+?^{}|~.-])+$/', str_replace("\\\\", "", $local))) {
										// character not valid in local part unless 
										// local part is quoted
										if (!preg_match('/^"(\\\\"|[^"])+"$/', str_replace("\\\\", "", $local))) {
											$isValid = false;
										}
									}
			if ($isValid && !(checkdnsrr($domain, "MX") || checkdnsrr($domain, "A"))) {
				// domain not found in DNS
				$isValid = false;
			}
		}
		return $isValid;
	}

	/**
	 * Alias method of JFile::upload to unify any
	 * upload function into one.
	 * 
	 * @param   string   $src 			The name of the php (temporary) uploaded file.
	 * @param   string   $dest 			The path (including filename) to move the uploaded file to.
	 * @param   boolean  [$copy_only] 	Whether to skip the file upload and just copy the file.
	 * 
	 * @return  boolean  True on success.
	 * 
	 * @since 	1.6 - For compatibility with the VikWP Framework.
	 */
	public static function uploadFile($src, $dest, $copy_only = false) {
		// always attempt to include the File class
		jimport('joomla.filesystem.file');

		// upload the file
		if (!$copy_only) {
			$result = JFile::upload($src, $dest);
		} else {
			// this is to avoid the use of the PHP function copy() and allow files mirroring in WP (triggerUploadBackup)
			$result = JFile::copy($src, $dest);
		}

		// return upload result
		return $result;
	}

}

class VriImageResizer {

	public function __construct() {
		//objects of this class can also be instantiated without calling the methods statically.
	}

	/**
	 * Resizes an image proportionally. For PNG files it can optionally
	 * trim the image to exclude the transparency, and add some padding to it.
	 * All PNG files keep the alpha background in the resized version.
	 *
	 * @param 	string 		$fileimg 	path to original image file
	 * @param 	string 		$dest 		path to destination image file
	 * @param 	int 		$towidth 	
	 * @param 	int 		$toheight 	
	 * @param 	bool 		$trim_png 	remove empty background from image
	 * @param 	string 		$trim_pad 	CSS-style version of padding (top right bottom left) ex: '1 2 3 4'
	 *
	 * @return 	boolean
	 */
	public function proportionalImage($fileimg, $dest, $towidth, $toheight, $trim_png = false, $trim_pad = null) {
		if (!file_exists($fileimg)) {
			return false;
		}
		if (empty($towidth) && empty($toheight)) {
			copy($fileimg, $dest);
			return true;
		}

		list ($owid, $ohei, $type) = getimagesize($fileimg);

		if ($owid > $towidth || $ohei > $toheight) {
			$xscale = $owid / $towidth;
			$yscale = $ohei / $toheight;
			if ($yscale > $xscale) {
				$new_width = round($owid * (1 / $yscale));
				$new_height = round($ohei * (1 / $yscale));
			} else {
				$new_width = round($owid * (1 / $xscale));
				$new_height = round($ohei * (1 / $xscale));
			}

			$imageresized = imagecreatetruecolor($new_width, $new_height);

			switch ($type) {
				case '1' :
					$imagetmp = imagecreatefromgif ($fileimg);
					break;
				case '2' :
					$imagetmp = imagecreatefromjpeg($fileimg);
					break;
				default :
					//keep alpha for PNG files
					$background = imagecolorallocate($imageresized, 0, 0, 0);
					imagecolortransparent($imageresized, $background);
					imagealphablending($imageresized, false);
					imagesavealpha($imageresized, true);
					//
					$imagetmp = imagecreatefrompng($fileimg);
					break;
			}

			imagecopyresampled($imageresized, $imagetmp, 0, 0, 0, 0, $new_width, $new_height, $owid, $ohei);

			switch ($type) {
				case '1' :
					imagegif ($imageresized, $dest);
					break;
				case '2' :
					imagejpeg($imageresized, $dest);
					break;
				default :
					if ($trim_png) {
						$this->imageTrim($imageresized, $background, $trim_pad);
					}
					imagepng($imageresized, $dest);
					break;
			}

			imagedestroy($imageresized);
		} else {
			copy($fileimg, $dest);
		}
		return true;
	}

	/**
	 * (BETA) Resizes an image proportionally. For PNG files it can optionally
	 * trim the image to exclude the transparency, and add some padding to it.
	 * All PNG files keep the alpha background in the resized version.
	 *
	 * @param 	resource 	$im 		Image link resource (reference)
	 * @param 	int 		$bg 		imagecolorallocate color identifier
	 * @param 	string 		$pad 		CSS-style version of padding (top right bottom left) ex: '1 2 3 4'
	 *
	 * @return 	void
	 */
	public function imagetrim(&$im, $bg, $pad = null){
		// Calculate padding for each side.
		if (isset($pad)) {
			$pp = explode(' ', $pad);
			if (isset($pp[3])) {
				$p = array((int) $pp[0], (int) $pp[1], (int) $pp[2], (int) $pp[3]);
			} elseif (isset($pp[2])) {
				$p = array((int) $pp[0], (int) $pp[1], (int) $pp[2], (int) $pp[1]);
			} elseif (isset($pp[1])) {
				$p = array((int) $pp[0], (int) $pp[1], (int) $pp[0], (int) $pp[1]);
			} else {
				$p = array_fill(0, 4, (int) $pp[0]);
			}
		} else {
			$p = array_fill(0, 4, 0);
		}

		// Get the image width and height.
		$imw = imagesx($im);
		$imh = imagesy($im);

		// Set the X variables.
		$xmin = $imw;
		$xmax = 0;

		// Start scanning for the edges.
		for ($iy=0; $iy<$imh; $iy++) {
			$first = true;
			for ($ix=0; $ix<$imw; $ix++) {
				$ndx = imagecolorat($im, $ix, $iy);
				if ($ndx != $bg) {
					if ($xmin > $ix) {
						$xmin = $ix;
					}
					if ($xmax < $ix) {
						$xmax = $ix;
					}
					if (!isset($ymin)) {
						$ymin = $iy;
					}
					$ymax = $iy;
					if ($first) {
						$ix = $xmax;
						$first = false;
					}
				}
			}
		}

		// The new width and height of the image. (not including padding)
		$imw = 1+$xmax-$xmin; // Image width in pixels
		$imh = 1+$ymax-$ymin; // Image height in pixels

		// Make another image to place the trimmed version in.
		$im2 = imagecreatetruecolor($imw+$p[1]+$p[3], $imh+$p[0]+$p[2]);

		// Make the background of the new image the same as the background of the old one.
		$bg2 = imagecolorallocate($im2, ($bg >> 16) & 0xFF, ($bg >> 8) & 0xFF, $bg & 0xFF);
		imagefill($im2, 0, 0, $bg2);

		// Copy it over to the new image.
		imagecopy($im2, $im, $p[3], $p[0], $xmin, $ymin, $imw, $imh);

		// To finish up, we replace the old image which is referenced.
		$im = $im2;
	}

	public function bandedImage($fileimg, $dest, $towidth, $toheight, $rgb) {
		if (!file_exists($fileimg)) {
			return false;
		}
		if (empty($towidth) && empty($toheight)) {
			copy($fileimg, $dest);
			return true;
		}

		$exp = explode(",", $rgb);
		if (count($exp) == 3) {
			$r = trim($exp[0]);
			$g = trim($exp[1]);
			$b = trim($exp[2]);
		} else {
			$r = 0;
			$g = 0;
			$b = 0;
		}

		list ($owid, $ohei, $type) = getimagesize($fileimg);

		if ($owid > $towidth || $ohei > $toheight) {
			$xscale = $owid / $towidth;
			$yscale = $ohei / $toheight;
			if ($yscale > $xscale) {
				$new_width = round($owid * (1 / $yscale));
				$new_height = round($ohei * (1 / $yscale));
				$ydest = 0;
				$diff = $towidth - $new_width;
				$xdest = ($diff > 0 ? round($diff / 2) : 0);
			} else {
				$new_width = round($owid * (1 / $xscale));
				$new_height = round($ohei * (1 / $xscale));
				$xdest = 0;
				$diff = $toheight - $new_height;
				$ydest = ($diff > 0 ? round($diff / 2) : 0);
			}

			$imageresized = imagecreatetruecolor($towidth, $toheight);

			$bgColor = imagecolorallocate($imageresized, (int) $r, (int) $g, (int) $b);
			imagefill($imageresized, 0, 0, $bgColor);

			switch ($type) {
				case '1' :
					$imagetmp = imagecreatefromgif ($fileimg);
					break;
				case '2' :
					$imagetmp = imagecreatefromjpeg($fileimg);
					break;
				default :
					$imagetmp = imagecreatefrompng($fileimg);
					break;
			}

			imagecopyresampled($imageresized, $imagetmp, $xdest, $ydest, 0, 0, $new_width, $new_height, $owid, $ohei);

			switch ($type) {
				case '1' :
					imagegif ($imageresized, $dest);
					break;
				case '2' :
					imagejpeg($imageresized, $dest);
					break;
				default :
					imagepng($imageresized, $dest);
					break;
			}

			imagedestroy($imageresized);

			return true;
		} else {
			copy($fileimg, $dest);
		}
		return true;
	}

	public function croppedImage($fileimg, $dest, $towidth, $toheight) {
		if (!file_exists($fileimg)) {
			return false;
		}
		if (empty($towidth) && empty($toheight)) {
			copy($fileimg, $dest);
			return true;
		}

		list ($owid, $ohei, $type) = getimagesize($fileimg);

		if ($owid <= $ohei) {
			$new_width = $towidth;
			$new_height = ($towidth / $owid) * $ohei;
		} else {
			$new_height = $toheight;
			$new_width = ($new_height / $ohei) * $owid;
		}

		switch ($type) {
			case '1' :
				$img_src = imagecreatefromgif ($fileimg);
				$img_dest = imagecreate($new_width, $new_height);
				break;
			case '2' :
				$img_src = imagecreatefromjpeg($fileimg);
				$img_dest = imagecreatetruecolor($new_width, $new_height);
				break;
			default :
				$img_src = imagecreatefrompng($fileimg);
				$img_dest = imagecreatetruecolor($new_width, $new_height);
				break;
		}

		imagecopyresampled($img_dest, $img_src, 0, 0, 0, 0, $new_width, $new_height, $owid, $ohei);

		switch ($type) {
			case '1' :
				$cropped = imagecreate($towidth, $toheight);
				break;
			case '2' :
				$cropped = imagecreatetruecolor($towidth, $toheight);
				break;
			default :
				$cropped = imagecreatetruecolor($towidth, $toheight);
				break;
		}

		imagecopy($cropped, $img_dest, 0, 0, 0, 0, $owid, $ohei);

		switch ($type) {
			case '1' :
				imagegif ($cropped, $dest);
				break;
			case '2' :
				imagejpeg($cropped, $dest);
				break;
			default :
				imagepng($cropped, $dest);
				break;
		}

		imagedestroy($img_dest);
		imagedestroy($cropped);

		return true;
	}

}

function encryptCookie($str) {
	for ($i = 0; $i < 5; $i++) {
		$str = strrev(base64_encode($str));
	}
	return $str;
}

function decryptCookie($str) {
	for ($i = 0; $i < 5; $i++) {
		$str = base64_decode(strrev($str));
	}
	return $str;
}

function read($str) {
	$var = '';
	for ($i = 0; $i < strlen($str); $i += 2)
		$var .= chr(hexdec(substr($str, $i, 2)));
	return $var;
}

function checkComp($lf, $h, $n) {
	if (!count($lf)) {
		return false;
	}
	$a = $lf[0];
	$b = $lf[1];
	for ($i = 0; $i < 5; $i++) {
		$a = base64_decode(strrev($a));
		$b = base64_decode(strrev($b));
	}
	if ($a == $h || $b == $h || $a == $n || $b == $n) {
		return true;
	} else {
		$a = str_replace('www.', "", $a);
		$b = str_replace('www.', "", $b);
		if ((!empty($a) && (preg_match("/" . $a . "/i", $h) || preg_match("/" . $a . "/i", $n))) || (!empty($b) && (preg_match("/" . $b . "/i", $h) || preg_match("/" . $b . "/i", $n)))) {
			return true;
		}
	}
	return false;
}

define('CREATIVIKAPP', 'com_vikrentitems');
defined('E4J_SOFTWARE_VERSION') or define('E4J_SOFTWARE_VERSION', '1.6');

if (!class_exists('CreativikDotIt')) {
	class CreativikDotIt {
		function __construct() {
			$this->headers = array (
					"Referer" => "",
					"User-Agent" => "CreativikDotIt/1.0",
					"Connection" => "close"
			);
			$this->version = "1.1";
			$this->ctout = 15;
			$this->f_redha = false;
		}

		function exeqer($url) {
			$rcodes = array (
					301,
					302,
					303,
					307
			);
			$rmeth = array (
					'GET',
					'HEAD'
			);
			$rres = false;
			$this->fd_redhad = false;
			$ppred = array ();
			do {
				$rres = $this->sendout($url);
				$url = false;
				if ($this->f_redha && in_array($this->edocser, $rcodes)) {
					if (($this->edocser == 303) || in_array($this->method, $rmeth)) {
						$url = $this->resphh['Location'];
					}
				}
				if ($url && strlen($url)) {
					if (isset ($ppred[$url])) {
						$this->rore = "tceriderpool";
						$rres = false;
						break;
					}
					if (is_numeric($this->f_redha) && (count($ppred) > $this->f_redha)) {
						$this->rore = "tceriderynamoot";
						$rres = false;
						break;
					}
					$ppred[$url] = true;
				}
			} while ($url && strlen($url));
			$rep_qer_daeh = array (
					'Host',
					'Content-Length'
			);
			foreach ($rep_qer_daeh as $k => $v)
				unset ($this->headers[$v]);
			if (count($ppred) > 1)
				$this->fd_redhad = array_keys($ppred);
			return $rres;
		}

		function dliubh() {
			$daeh = "";
			foreach ($this->headers as $name => $value) {
				$value = trim($value);
				if (empty($value))
					continue;
				$daeh .= "{$name}: $value\r\n";
			}
			$daeh .= "\r\n";
			return $daeh;
		}

		function sendout($url) {
			$time_request_start = time();
			$urldata = parse_url($url);
			if (!isset($urldata["port"]) || !$urldata["port"])
				$urldata["port"] = ($urldata["scheme"] == "https") ? 443 : 80;
			if (!$urldata["path"])
				$urldata["path"] = '/';
			if ($this->version > "1.0")
				$this->headers["Host"] = $urldata["host"];
			unset ($this->headers['Authorization']);
			if (!empty($urldata["query"]))
				$urldata["path"] .= "?" . $urldata["query"];
			$request = $this->method . " " . $urldata["path"] . " HTTP/" . $this->version . "\r\n";
			$request .= $this->dliubh();
			$this->tise = "";
			$hostname = $urldata['host'];
			$time_connect_start = time();
			$fp = @ fsockopen($hostname, $urldata["port"], $errno, $errstr, $this->ctout);
			$connect_time = time() - $time_connect_start;
			if ($fp) {
				stream_set_timeout($fp, 3);
				fputs($fp, $request);
				$meta = stream_get_meta_data($fp);
				if ($meta['timed_out']) {
					$this->rore = "sdnoceseerhtfotuoemitetirwtekcosdedeecxe";
					return false;
				}
				$cerdaeh = false;
				$data_length = false;
				$chunked = false;
				while (!feof($fp)) {
					if ($data_length > 0) {
						$line = fread($fp, $data_length);
						$data_length -= strlen($line);
					} else {
						$line = fgets($fp, 10240);
						if ($chunked) {
							$line = trim($line);
							if (!strlen($line))
								continue;
							list ($data_length,) = explode(';', $line);
							$data_length = (int) hexdec(trim($data_length));
							if ($data_length == 0) {
								break;
							}
							continue;
						}
					}
					$this->tise .= $line;
					if ((!$cerdaeh) && (trim($line) == "")) {
						$cerdaeh = true;
						if (preg_match('/\nContent-Length: ([0-9]+)/i', $this->tise, $matches)) {
							$data_length = (int) $matches[1];
						}
						if (preg_match("/\nTransfer-Encoding: chunked/i", $this->tise, $matches)) {
							$chunked = true;
						}
					}
					$meta = stream_get_meta_data($fp);
					if ($meta['timed_out']) {
						$this->rore = "sceseerhttuoemitdaertekcos";
						return false;
					}
					if (time() - $time_request_start > 5) {
						$this->rore = "maxtransfertimefivesecs";
						return false;
						break;
					}
				}
				fclose($fp);
			} else {
				$this->rore = $urldata['scheme'] . " otdeliafnoitcennoc " . $hostname . " trop " . $urldata['port'];
				return false;
			}
			do {
				$neldaeh = strpos($this->tise, "\r\n\r\n");
				$serp_daeh = explode("\r\n", substr($this->tise, 0, $neldaeh));
				$pthats = trim(array_shift($serp_daeh));
				foreach ($serp_daeh as $line) {
					list ($k, $v) = explode(":", $line, 2);
					$this->resphh[trim($k)] = trim($v);
				}
				$this->tise = substr($this->tise, $neldaeh +4);
				if (!preg_match("/^HTTP\/([0-9\.]+) ([0-9]+) (.*?)$/", $pthats, $matches)) {
					$matches = array (
							"",
							$this->version,
							0,
							"HTTP request error"
					);
				}
				list (, $pserver, $this->edocser, $this->txet) = $matches;
			} while (($this->edocser == 100) && ($neldaeh));
			$ok = ($this->edocser == 200);
			return $ok;
		}

		function ksa($url) {
			$this->method = "GET";
			return $this->exeqer($url);
		}
	}
}