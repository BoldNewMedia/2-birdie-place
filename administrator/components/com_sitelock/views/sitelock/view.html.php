<?php 
/**
 * @version       v3.0.5 Site Lock $
 * @package       Site Lock
 * @copyright     Copyright � 2015 - All rights reserved.
 * @license       GNU/GPL       
 * @author        JoomlaShowroom.com
 * @author mail   info@JoomlaShowroom.com
 * @website       http://JoomlaShowroom.com
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.view');
use Joomla\CMS\Form\Form;
use Joomla\CMS\Factory;


class SiteLockViewSiteLock extends JViewLegacy
{
	function display($tpl = null)
	{
		global $mainframe, $option;
		$db = JFactory::getDBO();
		$uri = JFactory::getURI();
		$db->setQuery('SELECT * FROM #__sitelock_users');
		$users = $db->loadObjectList();
		
		$db->setQuery('SELECT title, bgcolor, bgimage, login,message FROM #__sitelock_options where id=1');
		$options = $db->loadObject();

		$db->setQuery('SELECT enabled FROM #__extensions WHERE `folder`=\'system\' AND `element`=\'sitelock\'');
		$this->assign('locked', $db->loadResult());
                
                $form = Form::getInstance("bgm", __DIR__ . "/tmpl/bgm_form.xml", array("control" => "bgmform"));
                $form->bind($options);
                $this->assignRef('form', $form);
		$this->assignRef('users', $users);
		$this->assignRef('options', $options);
		$sitelockHelper = new sitelockHelper;
		$sitelockHelper->add_sitelock_scripts();
	//	sitelockHelper::add_sitelock_scripts();
		parent::display($tpl);
	}
        
        public function getForm(){
            
        }
        
}
