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

/* URI Constants for admin and site sections (with trailing slash) */
defined('VRI_ADMIN_URI') or define('VRI_ADMIN_URI', JUri::root().'administrator/components/com_vikrentitems/');
defined('VRI_SITE_URI') or define('VRI_SITE_URI', JUri::root().'components/com_vikrentitems/');
defined('VRI_ADMIN_URI_REL') or define('VRI_ADMIN_URI_REL', './administrator/components/com_vikrentitems/');
defined('VRI_SITE_URI_REL') or define('VRI_SITE_URI_REL', './components/com_vikrentitems/');

/* Path Constants for admin and site sections (with NO trailing directory separator) */
defined('VRI_ADMIN_PATH') or define('VRI_ADMIN_PATH', JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_vikrentitems');
defined('VRI_SITE_PATH') or define('VRI_SITE_PATH', JPATH_SITE . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_vikrentitems');

/* Other Constants that may not be available in the framework */
defined('JPATH_COMPONENT_SITE') or define('JPATH_COMPONENT_SITE', JPATH_SITE . DIRECTORY_SEPARATOR . 'com_vikrentitems');
defined('JPATH_COMPONENT_ADMINISTRATOR') or define('JPATH_COMPONENT_ADMINISTRATOR', JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'com_vikrentitems');
defined('DS') or define('DS', DIRECTORY_SEPARATOR);

/* Adapter for Controller and View Classes for compatiblity with the various frameworks */
if (!class_exists('JViewVikRentItems') && class_exists('JViewLegacy')) {

	class JViewVikRentItems extends JViewLegacy {
		/* adapter for JViewLegacy */
	}

	class JControllerVikRentItems extends JControllerLegacy {
		/* adapter for JControllerLegacy */
	}

} elseif (!class_exists('JViewVikRentItems') && class_exists('JView')) {

	class JViewVikRentItems extends JView {
		/* adapter for JView */
	}

	class JControllerVikRentItems extends JController {
		/* adapter for JController */
	}

}
