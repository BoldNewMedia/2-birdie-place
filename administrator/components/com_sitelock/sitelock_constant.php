<?php
/**
 * @version       v3.0.5 Site Lock $
 * @package       Site Lock
 * @copyright     Copyright © 2015 - All rights reserved.
 * @license       GNU/GPL       
 * @author        JoomlaShowroom.com
 * @author mail   info@JoomlaShowroom.com
 * @website       http://JoomlaShowroom.com
*/

/* 
 * Define constants for all pages 
 */

// site constants
define('SITELOCK_SITE_URL',JURI::root());
define('SITELOCK_SITE_BASE',JPATH_ROOT);

// front end constants
define( 'SITELOCK_BASE_URL', JURI::root().'components/com_sitelock');
define( 'SITELOCK_IMG_PATH', SITELOCK_BASE_URL.'/assets/images');
define( 'SITELOCK_BASE_PATH', JPATH_ROOT.DS.'components'.DS.'com_sitelock');

// admin constants
define( 'SITELOCK_ADMIN_BASE_URL', JURI::root().'administrator/components/com_sitelock');
define( 'SITELOCK_ADMIN_IMG_PATH', SITELOCK_ADMIN_BASE_URL.'/assets/images');
define( 'SITELOCK_ADMIN_BASE_PATH', JPATH_ROOT.DS.'administrator'.DS.'components'.DS.'com_sitelock');

?>