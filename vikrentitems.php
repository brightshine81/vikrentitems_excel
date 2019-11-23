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

/* --- Joomla portability --- */
include(JPATH_SITE . DIRECTORY_SEPARATOR ."components". DIRECTORY_SEPARATOR ."com_vikrentitems". DIRECTORY_SEPARATOR . "helpers" . DIRECTORY_SEPARATOR . "adapter" . DIRECTORY_SEPARATOR ."defines.php");
include(JPATH_SITE . DIRECTORY_SEPARATOR ."components". DIRECTORY_SEPARATOR ."com_vikrentitems". DIRECTORY_SEPARATOR . "helpers" . DIRECTORY_SEPARATOR . "adapter" . DIRECTORY_SEPARATOR ."request.php");
include(JPATH_SITE . DIRECTORY_SEPARATOR ."components". DIRECTORY_SEPARATOR ."com_vikrentitems". DIRECTORY_SEPARATOR . "helpers" . DIRECTORY_SEPARATOR . "adapter" . DIRECTORY_SEPARATOR ."error.php");
/* --- */

/* Main library */
require_once(VRI_SITE_PATH . DS . "helpers" . DS . "lib.vikrentitems.php");

/* Load assets */
$document = JFactory::getDocument();
VikRentItems::loadFontAwesome();
$document->addStyleSheet(VRI_SITE_URI.'vikrentitems_styles.css', array('version' => E4J_SOFTWARE_VERSION));
$document->addStyleSheet(VRI_SITE_URI.'vikrentitems_custom.css');

/* Framework Rendering */
jimport('joomla.application.component.controller');
$controller = JControllerVikRentItems::getInstance('Vikrentitems');
$controller->execute(VikRequest::getCmd('task'));
$controller->redirect();
