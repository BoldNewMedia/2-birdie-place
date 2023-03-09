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

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );
if(!defined('DS')){
   define('DS',DIRECTORY_SEPARATOR);
}
// Require the base controller
require_once (JPATH_COMPONENT_ADMINISTRATOR.DS.'sitelock_constant.php'); // add constant file
require_once (JPATH_COMPONENT_ADMINISTRATOR.DS.'classes'.DS.'admin.class.php'); // add admin class file
require_once (JPATH_COMPONENT.DS.'controller.php');
require_once (JPATH_COMPONENT.DS.'helper.php' );
  
$controller	= JControllerLegacy::getInstance('Sitelock');
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();
