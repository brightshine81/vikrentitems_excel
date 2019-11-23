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


jimport('joomla.application.component.view');

class VikrentitemsViewSearch extends JViewLegacy {
	function display($tpl = null) {
		if (VikRentItems::allowRent()) {
			$session = JFactory::getSession();
			$vri_tn = VikRentItems::getTranslator();
			$pplace = VikRequest::getString('place', '', 'request');
			$returnplace = VikRequest::getString('returnplace', '', 'request');
			$ppickupdate = VikRequest::getString('pickupdate', '', 'request');
			$ppickupm = VikRequest::getString('pickupm', '', 'request');
			$ppickuph = VikRequest::getString('pickuph', '', 'request');
			$preleasedate = VikRequest::getString('releasedate', '', 'request');
			$preleasem = VikRequest::getString('releasem', '', 'request');
			$preleaseh = VikRequest::getString('releaseh', '', 'request');
			$pcategories = VikRequest::getString('categories', '', 'request');
			//itemdetails
			$pitemquant = VikRequest::getInt('itemquant', '', 'request');
			$pitemquant = empty($pitemquant) || $pitemquant < 1 ? 1 : $pitemquant;
			$pitemdetail = VikRequest::getInt('itemdetail', '', 'request');
			$pitemid = VikRequest::getInt('Itemid', '', 'request');
			//time slots
			$nowdf = VikRentItems::getDateFormat();
			if ($nowdf == "%d/%m/%Y") {
				$df = 'd/m/Y';
			} elseif ($nowdf == "%m/%d/%Y") {
				$df = 'm/d/Y';
			} else {
				$df = 'Y/m/d';
			}
			$nowtf = VikRentItems::getTimeFormat();
			$ptimeslot = VikRequest::getString('timeslot', '', 'request');
			$usetimeslot = '';
			if (strlen($ptimeslot) > 0) {
				$usetimeslot = VikRentItems::loadTimeSlot($ptimeslot, $vri_tn);
				if (is_array($usetimeslot) && count($usetimeslot) > 0) {
					$usefirst = VikRentItems::getDateTimestamp($ppickupdate, 0, 0);
					$usefirst += 86400 * $usetimeslot['days'];
					$ppickuph = $usetimeslot['fromh'] < 10 ? '0'.$usetimeslot['fromh'] : $usetimeslot['fromh'];
					$ppickupm = $usetimeslot['fromm'] < 10 ? '0'.$usetimeslot['fromm'] : $usetimeslot['fromm'];
					$preleaseh = $usetimeslot['toh'] < 10 ? '0'.$usetimeslot['toh'] : $usetimeslot['toh'];
					$preleasem = $usetimeslot['tom'] < 10 ? '0'.$usetimeslot['tom'] : $usetimeslot['tom'];
					if ($usetimeslot['fromh'] > $usetimeslot['toh']) {
						//day after
						$preleasedate = date($df, $usefirst + 86400);
					} else {
						$preleasedate = date($df, $usefirst);
					}
				}
			}
			//
			if (!empty($ppickupdate) && !empty($preleasedate)) {
				if (VikRentItems::dateIsValid($ppickupdate) && VikRentItems::dateIsValid($preleasedate)) {
					$first = VikRentItems::getDateTimestamp($ppickupdate, $ppickuph, $ppickupm);
					$second = VikRentItems::getDateTimestamp($preleasedate, $preleaseh, $preleasem);
					$actnow = time();
					$today_bookings = VikRentItems::todayBookings();
					if ($today_bookings) {
						$actnow = mktime(0, 0, 0, date('n'), date('j'), date('Y'));
					}
					$mindaysadv = VikRentItems::getMinDaysAdvance();
					$lim_mindays = $actnow;
					if ($mindaysadv > 0) {
						$todaybasets = mktime(0, 0, 0, date('n'), date('j'), date('Y'));
						$lim_mindays = $mindaysadv > 1 ? strtotime("+$mindaysadv days", $todaybasets) : strtotime("+1 day", $todaybasets);
					}
					$checkhourly = false;
					//vikrentitems 1.1
					$checkhourscharges = 0;
					//
					$hoursdiff = 0;
					if ($second > $first && $first >= $actnow && $first >= $lim_mindays) {
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
									//vikrentitems 1.1
									$ehours = intval(round(($newdiff - $maxhmore) / 3600));
									$checkhourscharges = $ehours;
									if ($checkhourscharges > 0) {
										$aehourschbasp = VikRentItems::applyExtraHoursChargesBasp();
									}
									//
								}
							}
						}
						$dbo = JFactory::getDBO();
						$q = "SELECT * FROM `#__vikrentitems_dispcost` WHERE `days`='" . $daysdiff . "' ORDER BY `#__vikrentitems_dispcost`.`cost` ASC, `#__vikrentitems_dispcost`.`iditem` ASC;";
						$dbo->setQuery($q);
						$dbo->execute();
						if ($dbo->getNumRows() > 0) {
							$tars = $dbo->loadAssocList();
							$arrtar = array();
							foreach ($tars as $tar) {
								$arrtar[$tar['iditem']][] = $tar;
							}
							//vikrentitems 1.1
							if ($checkhourly) {
								$arrtar = VikRentItems::applyHourlyPrices($arrtar, $hoursdiff);
							}
							//
							//vikrentitems 1.1
							if ($checkhourscharges > 0 && $aehourschbasp == true) {
								$arrtar = VikRentItems::applyExtraHoursChargesPrices($arrtar, $checkhourscharges, $daysdiff);
							}
							//
							$filterplace = (!empty($pplace) ? true : false);
							$filtercat = (!empty($pcategories) && $pcategories != "all" ? true : false);
							//vikrentitems 1.1
							$groupdays = VikRentItems::getGroupDays($first, $second, $daysdiff);
							$morehst = VikRentItems::getHoursItemAvail() * 3600;
							//
							//vikrentitems 1.1 location closing days
							$errclosingdays = '';
							if ($filterplace) {
								$errclosingdays = VikRentItems::checkValidClosingDays($groupdays, $pplace, $returnplace);
							}
							if (empty($errclosingdays)) {
								$errclosingdays = VikRentItems::checkValidGlobalClosingDays($groupdays);
							}
							if (empty($errclosingdays)) {
								// VRI 1.6 - Allow pick ups on drop offs
								$picksondrops = VikRentItems::allowPickOnDrop();
								//
								foreach ($arrtar as $kk => $tt) {
									$check = "SELECT `id`,`idcat`,`idplace`,`avail`,`units`,`idretplace`,`askquantity` FROM `#__vikrentitems_items` WHERE `id`=" . (int)$kk . ";";
									$dbo->setQuery($check);
									$dbo->execute();
									$item = $dbo->loadAssocList();
									if (intval($item[0]['avail']) == 0 || $pitemquant > $item[0]['units']) {
										unset ($arrtar[$kk]);
										continue;
									} else {
										if ($filterplace) {
											$actplaces = explode(";", $item[0]['idplace']);
											if (!in_array($pplace, $actplaces)) {
												unset ($arrtar[$kk]);
												continue;
											}
											$actretplaces = explode(";", $item[0]['idretplace']);
											if (!in_array($returnplace, $actretplaces)) {
												unset ($arrtar[$kk]);
												continue;
											}
										}
										if ($filtercat) {
											$cats = explode(";", $item[0]['idcat']);
											if (!in_array($pcategories, $cats)) {
												unset ($arrtar[$kk]);
												continue;
											}
										}
									}
									$check = "SELECT `b`.`id`,`b`.`ritiro`,`b`.`consegna`,`ob`.`idorder` FROM `#__vikrentitems_busy` AS `b` LEFT JOIN `#__vikrentitems_ordersbusy` AS `ob` ON `b`.`id`=`ob`.`idbusy` WHERE `b`.`iditem`='" . $kk . "';";
									$dbo->setQuery($check);
									$dbo->execute();
									if ($dbo->getNumRows() > 0) {
										$busy = $dbo->loadAssocList();
										foreach ($groupdays as $kgd => $gday) {
											$bfound = 0;
											foreach ($busy as $bu) {
												if ($gday >= $bu['ritiro'] && $gday <= ($morehst + $bu['consegna'])) {
													if ($picksondrops && !($gday > $bu['ritiro'] && $gday < ($morehst + $bu['consegna']))) {
														// VRI 1.6 - pick ups on drop offs allowed
														continue;
													}
													$bfound++;
												} elseif (count($groupdays) == 2 && $gday == $groupdays[0]) {
													//VRI 1.1
													if ($groupdays[0] < $bu['ritiro'] && $groupdays[0] < ($morehst + $bu['consegna']) && $groupdays[1] > $bu['ritiro'] && $groupdays[1] > ($morehst + $bu['consegna'])) {
														$bfound++;
													}
												} elseif (!empty($groupdays[($kgd + 1)]) && (($bu['consegna'] - $bu['ritiro']) < 86400) && $gday < $bu['ritiro'] && $groupdays[($kgd + 1)] > $bu['consegna']) {
													//VRI 1.3 availability check whith hourly rentals
													$bfound++;
												} elseif (count($groupdays) > 2 && array_key_exists(($kgd - 1), $groupdays) && array_key_exists(($kgd + 1), $groupdays)) {
													//VRI 1.4 gday is at midnight and the pickup for this date may be at a later time
													if ($groupdays[($kgd - 1)] < $bu['ritiro'] && $groupdays[($kgd - 1)] < ($morehst + $bu['consegna']) && $gday < $bu['ritiro'] && $groupdays[($kgd + 1)] > $bu['ritiro'] && $gday <= ($morehst + $bu['consegna'])) {
														// VRI 1.6 - daily rentals with a pickup hour after the drop off hour should check the time
														// ex. 3 units in total, 2 orders from April 16 22:00 to April 23 08:00,
														// and 1 order from April 23 22:00 to April 30 08:00
														// must give availability on April 23 to orders like April 17 22:00 to April 24 08:00
														$pickinfo = getdate($groupdays[0]);
														$dropinfo = getdate($groupdays[($kgd + 1)]);
														// needed to check if drop off hours and minutes is earlier than pickup hours and minutes
														$pickseconds = ($pickinfo['hours'] * 3600) + ($pickinfo['minutes'] * 60);
														$dropseconds = ($dropinfo['hours'] * 3600) + ($dropinfo['minutes'] * 60);
														if ($dropseconds < $pickseconds && $bu['ritiro'] > $gday) {
															// this pickup is at a later time than midnight, so this loop should not occupy a unit
															continue;
														}
														$bfound++;
													}
												}
											}
											if (($bfound + $pitemquant) > $item[0]['units']) {
												unset ($arrtar[$kk]);
												break;
											}
										}
									}
									if (!VikRentItems::itemNotLocked($kk, $item[0]['units'], $first, $second, $pitemquant)) {
										unset ($arrtar[$kk]);
									}
								}
								if (@ count($arrtar) > 0) {
									if (VikRentItems::allowStats()) {
										$q = "INSERT INTO `#__vikrentitems_stats` (`ts`,`ip`,`place`,`cat`,`ritiro`,`consegna`,`res`) VALUES('" . time() . "','" . getenv('REMOTE_ADDR') . "'," . $dbo->quote($pplace . ';' . $returnplace) . "," . $dbo->quote($pcategories) . ",'" . $first . "','" . $second . "','" . count($arrtar) . "');";
										$dbo->setQuery($q);
										$dbo->execute();
									}
									if (VikRentItems::sendMailStats()) {
										$admsg = VikRentItems::getFrontTitle() . ", " . JText::_('VRSRCHNOTM') . "\n\n";
										$admsg .= JText::_('VRDATE') . ": " . date($df . ' '.$nowtf) . "\n";
										$admsg .= JText::_('VRIP') . ": " . getenv('REMOTE_ADDR') . "\n";
										$admsg .= (!empty($pplace) ? JText::_('VRPLACE') . ": " . VikRentItems::getPlaceName($pplace) : "") . (!empty($returnplace) ? " - " . VikRentItems::getPlaceName($returnplace) : "") . "\n";
										if (!empty($pcategories)) {
											$admsg .= ($pcategories == "all" ? JText::_('VRIAT') . ": " . JText::_('VRANY') : JText::_('VRIAT') . ": " . VikRentItems::getCategoryName($pcategories)) . "\n";
										}
										$admsg .= JText::_('VRPICKUP') . ": " . date($df . ' '.$nowtf, $first) . "\n";
										$admsg .= JText::_('VRRETURN') . ": " . date($df . ' '.$nowtf, $second) . "\n";
										$admsg .= JText::_('VRSRCHRES') . ": " . count($arrtar);
										$adsubj = JText::_('VRSRCHNOTM') . ' ' . VikRentItems::getFrontTitle();
										$adsubj = '=?UTF-8?B?' . base64_encode($adsubj) . '?=';
										@ mail(VikRentItems::getAdminMail(), $adsubj, $admsg, "MIME-Version: 1.0" . "\r\n" . "Content-type: text/plain; charset=UTF-8");
									}
									//vikrentitems 1.1
									if ($checkhourscharges > 0 && $aehourschbasp == false) {
										$arrtar = VikRentItems::extraHoursSetPreviousFare($arrtar, $checkhourscharges, $daysdiff);
										$arrtar = VikRentItems::applySeasonalPrices($arrtar, $first, $second, $pplace);
										$arrtar = VikRentItems::applyExtraHoursChargesPrices($arrtar, $checkhourscharges, $daysdiff, true);
									} else {
										$arrtar = VikRentItems::applySeasonalPrices($arrtar, $first, $second, $pplace);
									}
									//
									//apply locations fee and store it in session
									if (!empty($pplace) && !empty($returnplace)) {
										$session->set('vriplace', $pplace);
										$session->set('vrireturnplace', $returnplace);
										//VRI 1.1 Rev.2
										VikRentItems::registerLocationTaxRate($pplace);
										//
										$locfee = VikRentItems::getLocFee($pplace, $returnplace);
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
											$lfarr = array ();
											foreach ($arrtar as $kat => $at) {
												$newcost = $at[0]['cost'] + $locfeecost;
												$at[0]['cost'] = $newcost;
												$lfarr[$kat] = $at;
											}
											$arrtar = $lfarr;
										}
									}
									//
									//save in session pickup and drop off timestamps
									$session->set('vripickupts', $first);
									$session->set('vrireturnts', $second);
									$session->set('vridays', $daysdiff);
									//
									$arrtar = VikRentItems::sortResults($arrtar);
									//check wether the user is coming from itemdetails
									if (!empty($pitemdetail) && array_key_exists($pitemdetail, $arrtar)) {
										$returnplace = VikRequest::getInt('returnplace', '', 'request');
										$mainframe=JFactory::getApplication();
										$mainframe->redirect("index.php?option=com_vikrentitems&task=showprc&itemopt=" . $pitemdetail . "&days=" . $daysdiff . "&pickup=" . $first . "&release=" . $second . "&place=" . $pplace . "&returnplace=" . $returnplace . "&itemquant=" . $pitemquant . (is_array($usetimeslot) && count($usetimeslot) > 0 ? "&timeslot=".$usetimeslot['id'] : "") . (!empty($pitemid) ? "&Itemid=" . $pitemid : ""));
									} else {
										if (!empty($pitemdetail)) {
											$q="SELECT `id`,`name` FROM `#__vikrentitems_items` WHERE `id`=".$dbo->quote($pitemdetail).";";
											$dbo->setQuery($q);
											$dbo->execute();
											if ($dbo->getNumRows() > 0) {
												$cdet=$dbo->loadAssocList();
												$vri_tn->translateContents($cdet, '#__vikrentitems_items');
												VikError::raiseWarning('', $cdet[0]['name']." ".JText::_('VRIDETAILCNOTAVAIL'));
											}
										}
										//pagination
										$mainframe = JFactory::getApplication();
										$lim = $mainframe->getUserStateFromRequest("com_vikrentitems.limit", 'limit', $mainframe->get('list_limit'), 'int'); //results limit
										$lim0 = VikRequest::getVar('limitstart', 0, '', 'int');
										jimport('joomla.html.pagination');
										$pageNav = new JPagination(count($arrtar), $lim0, $lim);
										$navig = $pageNav->getPagesLinks();
										$this->navig = $navig;
										$tot_res = count($arrtar);
										$arrtar = array_slice($arrtar, $lim0, $lim, true);
										//
										$this->res = $arrtar;
										$this->days = $daysdiff;
										$this->hours = $hoursdiff;
										$this->pickup = $first;
										$this->release = $second;
										$this->place = $pplace;
										$this->timeslot = $usetimeslot;
										$this->tot_res = $tot_res;
										$this->vri_tn = $vri_tn;
										//theme
										$theme = VikRentItems::getTheme();
										if ($theme != 'default') {
											$thdir = VRI_SITE_PATH.DS.'themes'.DS.$theme.DS.'search';
											if (is_dir($thdir)) {
												$this->_setPath('template', $thdir.DS);
											}
										}
										//
										parent::display($tpl);
									}
									//
								} else {
									if (VikRentItems::allowStats()) {
										$q = "INSERT INTO `#__vikrentitems_stats` (`ts`,`ip`,`place`,`cat`,`ritiro`,`consegna`,`res`) VALUES('" . time() . "','" . getenv('REMOTE_ADDR') . "'," . $dbo->quote($pplace . ';' . $returnplace) . "," . $dbo->quote($pcategories) . ",'" . $first . "','" . $second . "','0');";
										$dbo->setQuery($q);
										$dbo->execute();
									}
									if (VikRentItems::sendMailStats()) {
										$admsg = VikRentItems::getFrontTitle() . ", " . JText::_('VRSRCHNOTM') . "\n\n";
										$admsg .= JText::_('VRDATE') . ": " . date($df . ' '.$nowtf) . "\n";
										$admsg .= JText::_('VRIP') . ": " . getenv('REMOTE_ADDR') . "\n";
										$admsg .= (!empty($pplace) ? JText::_('VRPLACE') . ": " . VikRentItems::getPlaceName($pplace) : "") . (!empty($returnplace) ? " - " . VikRentItems::getPlaceName($returnplace) : "") . "\n";
										if (!empty($pcategories)) {
											$admsg .= ($pcategories == "all" ? JText::_('VRIAT') . ": " . JText::_('VRANY') : JText::_('VRIAT') . ": " . VikRentItems::getCategoryName($pcategories)) . "\n";
										}
										$admsg .= JText::_('VRPICKUP') . ": " . date($df . ' '.$nowtf, $first) . "\n";
										$admsg .= JText::_('VRRETURN') . ": " . date($df . ' '.$nowtf, $second) . "\n";
										$admsg .= JText::_('VRSRCHRES') . ": 0";
										$adsubj = JText::_('VRSRCHNOTM') . ' ' . VikRentItems::getFrontTitle();
										$adsubj = '=?UTF-8?B?' . base64_encode($adsubj) . '?=';
										@ mail(VikRentItems::getAdminMail(), $adsubj, $admsg, "MIME-Version: 1.0" . "\r\n" . "Content-type: text/plain; charset=UTF-8");
									}
									showSelectVRI(JText::_('VRNOITEMSINDATE'));
								}
							} else {
								//closing days error
								showSelectVRI($errclosingdays);
							}
						} else {
							if (VikRentItems::allowStats()) {
								$q = "INSERT INTO `#__vikrentitems_stats` (`ts`,`ip`,`place`,`cat`,`ritiro`,`consegna`,`res`) VALUES('" . time() . "','" . getenv('REMOTE_ADDR') . "'," . $dbo->quote($pplace . ';' . $returnplace) . "," . $dbo->quote($pcategories) . ",'" . $first . "','" . $second . "','0');";
								$dbo->setQuery($q);
								$dbo->execute();
							}
							if (VikRentItems::sendMailStats()) {
								$admsg = VikRentItems::getFrontTitle() . ", " . JText::_('VRSRCHNOTM') . "\n\n";
								$admsg .= JText::_('VRDATE') . ": " . date($df . ' '.$nowtf) . "\n";
								$admsg .= JText::_('VRIP') . ": " . getenv('REMOTE_ADDR') . "\n";
								$admsg .= (!empty($pplace) ? JText::_('VRPLACE') . ": " . VikRentItems::getPlaceName($pplace) : "") . (!empty($returnplace) ? " - " . VikRentItems::getPlaceName($returnplace) : "") . "\n";
								if (!empty($pcategories)) {
									$admsg .= ($pcategories == "all" ? JText::_('VRIAT') . ": " . JText::_('VRANY') : JText::_('VRIAT') . ": " . VikRentItems::getCategoryName($pcategories)) . "\n";
								}
								$admsg .= JText::_('VRPICKUP') . ": " . date($df . ' '.$nowtf, $first) . "\n";
								$admsg .= JText::_('VRRETURN') . ": " . date($df . ' '.$nowtf, $second) . "\n";
								$admsg .= JText::_('VRSRCHRES') . ": 0";
								$adsubj = JText::_('VRSRCHNOTM') . ' ' . VikRentItems::getFrontTitle();
								$adsubj = '=?UTF-8?B?' . base64_encode($adsubj) . '?=';
								@ mail(VikRentItems::getAdminMail(), $adsubj, $admsg, "MIME-Version: 1.0" . "\r\n" . "Content-type: text/plain; charset=UTF-8");
							}
							$errormess = JText::_('VRNOITEMAVFOR') . " " . $daysdiff . " " . ($daysdiff > 1 ? JText::_('VRDAYS') : JText::_('VRDAY'));
							if (!empty($pitemdetail)) {
								VikError::raiseWarning('', $errormess);
								$mainframe = JFactory::getApplication()->redirect(JRoute::_("index.php?option=com_vikrentitems&view=itemdetails&elemid=" . $pitemdetail . (!empty($pitemid) ? "&Itemid=" . $pitemid : ""), false));
								exit;
							}
							showSelectVRI($errormess);
						}
					} else {
						if ($first <= $actnow) {
							if (date('d/m/Y', $first) == date('d/m/Y', $actnow)) {
								$errormess = JText::_('VRIERRPICKPASSED');
							} else {
								$errormess = JText::_('VRPICKINPAST');
							}
						} elseif ($first < $lim_mindays) {
							//error with minimum days in advance for bookings
							$errormess = JText::sprintf('VRIERRMINDAYSADV', $mindaysadv);
						} else {
							$errormess = JText::_('VRPICKBRET');
						}
						showSelectVRI($errormess);
					}
				} else {
					showSelectVRI(JText::_('VRWRONGDF') . ": " . VikRentItems::sayDateFormat());
				}
			} else {
				showSelectVRI(JText::_('VRSELPRDATE'));
			}
		} else {
			echo VikRentItems::getDisabledRentMsg();
		}
	}
}
