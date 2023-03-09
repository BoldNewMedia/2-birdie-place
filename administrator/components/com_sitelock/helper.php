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
defined('_JEXEC') or die('Restricted access');
if(!defined('DS')){
   define('DS',DIRECTORY_SEPARATOR);
}
jimport('joomla.application.component.helper');

class sitelockHelper extends JComponentHelper
{		
	// add js/css files for backend section
	function add_sitelock_scripts()
	{
		$document	=  JFactory::getDocument();
		
		//add css and js to document		
		$document->addStyleSheet('components/com_sitelock/assets/css/icon.css');								
		//$document->addScript('');		
	} // end function		
	
		
	// Check info
	function &getInfo() {
       
        $info = array();
        // Get installed version
        $info['version_installed'] = sitelockHelper::getInstalledVersion();
       
        // Get latest version
      //  $manifest=JFactory::getXML('http://joomlashowroom.com/media/versionsxml/extension_versions.xml');
        $manifest=JFactory::getXML('http://www.joomlashowroom.com/updates_xml/site_lock3.xml');
   
		if($manifest) {
		/*	$i=0;
			foreach($manifest->children() as $child) {
				$c = count($child->extension); 
				$c = $c-1;
				if($i==0) {
					$string = $child->extension[$c];
					if($string->attributes()->{'name'}=="site lock")
						{
					$info['version_latest'] = $string->attributes()->{'version'};
				}
				}
				$i++;
			}*/
			$info['version_latest'] = $manifest->version;
		}
        // Set the version status
        $info['version_status'] = version_compare($info['version_installed'], $info['version_latest']);
        $info['version_enabled'] = 1;
       
        return $info;
    }
	
	function &getInstalledVersion()  {
	
		jimport('joomla.filesystem.file');
		jimport('joomla.filesystem.folder');
		
        static $version;

        if (!isset($version)) {
		$xml=JFactory::getXML(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_sitelock'.DS.'sitelock.xml');
		$version=(string)$xml->version;	

			
        }

        return $version;
    }
	
	function getRemoteData($url) {
	
            /* BGM remove function not need */
		
	}
	
}

?>