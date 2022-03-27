<?php
/**
* @version   $Id: [script.php] 2020-04-08 [developer1] $
* @author By George Media https://georgemedia.com.au.com.au
* @copyright Copyright (C) 2019 - 2021 By George Media (BGM)
* @support support@georgemedia.com.au
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

class PlgContentBgm_Yoothemepro_TabsInstallerScript
{
	public function install($parent)
	{
		
                $db = JFactory::getDbo();
                $db->setQuery('UPDATE #__extensions set enabled =1 WHERE element ="bgm_yoothemepro_tabs"')->execute();
		return true;
	}
}