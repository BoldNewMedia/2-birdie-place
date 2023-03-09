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
if(!defined('DS')){
   define('DS',DIRECTORY_SEPARATOR);
}
jimport( 'joomla.application.component.controller' );
class SitelockController extends JControllerLegacy
{
	function __construct()
	{
		parent::__construct();
	}

	function save()
	{
		
		 $msg = '';
		/* $file = JPATH_PLUGINS . DS . 'system' .DS . 'sitelock' . DS . 'sitelock.html';
		if(file_put_contents($file, JRequest::getVar('content_display', 'Locked', 'default', STRING, JREQUEST_ALLOWRAW)) === false)
		{
			$msg = 'Could not write to ' . $file;
		} */

		$db = JFactory::getDBO();
		//$content = $db->quote(JRequest::getVar('content_display'));
		$content = $_POST['content_display'];
		$changed = array();
		$cfg = new JConfig();
		foreach($_REQUEST as $var => $val)
		{
			if(!strncmp($var, 'password', strlen('password')))
			{
				if(strlen(trim($val)))
				{
					$name = substr($var, strlen('password'));
					$changed[$name] = $val;
					$db->setQuery('UPDATE #__sitelock_users SET `password`='.$db->Quote(md5($cfg->secret . 'lock' . $val)).' WHERE `user`=' . $db->Quote($name));
					if(!$db->query())
					{
						$msg .= JText::_('SITELOCK_CHANGE_USER_PASSWORD_ERROR') . $name . ': ' . $db->getErrorMsg() . '<br />';
					}
					else
					{
						$msg .= JText::_('SITELOCK_CHANGE_USER_PASSWORD') . $name . '<br />';
					}
				}
			}
		}

		if(isset($_REQUEST['newuser']) && isset($_REQUEST['newpass']))
		{
			if(!strlen($_REQUEST['newuser']) || !strlen($_REQUEST['newpass']))
			{
			}
			else
			{
				$db->setQuery('SELECT * FROM #__sitelock_users WHERE `user`='.$db->Quote(JRequest::getVar('newuser')));
				$result = $db->loadObjectList();
				if(count($result)==0)
				{
					$db->setQuery('INSERT INTO #__sitelock_users(`user`, `password`) VALUES(' . $db->Quote(JRequest::getVar('newuser')) . ', ' . $db->Quote(md5($cfg->secret . 'lock' . JRequest::getVar('newpass'))) . ')');
					if(!$db->query())
					{
						$msg .= JText::_('SITELOCK_ADD_USER_ERROR') . $db->getErrorMsg() . '<br />';
					}
					else
					{
						$msg .= JText::_('SITELOCK_ADD_USER') . JRequest::getVar('newuser') . '<br />';
					}
				}else{
						$application = JFactory::getApplication();
						$application->enqueueMessage(JText::_('SITELOCK_ADD_DUPLICATE_USER_ERROR'). JRequest::getVar('newuser'), 'error');
					}
			}
			
		}
		//echo "Content <br/>";echo $content;
		
		$explanation = addslashes($content);
               
		if(isset($_REQUEST['title']) || isset($_REQUEST['bgcolor']) ||  isset( $_FILES["bgimage"]) || isset($_REQUEST['login'])){
			
			
                        $db->setQuery('UPDATE #__sitelock_options SET `title`='.$db->Quote($_REQUEST['title']).', `bgcolor`='.$db->Quote($_REQUEST['bgmform']['bgcolor']).', `bgimage`='.$db->Quote($_REQUEST['bgmform']['bgimage']).', `login`='.$_REQUEST['login'].',`message`= "'.$explanation.'" WHERE `id`=1');
			
			
			if(!$db->query())
			{
				$msg .= JText::_('SITELOCK_CHANGE_OPTIONS_ERROR') . $db->getErrorMsg() . '<br />';
			}
			else
			{
				$msg .= JText::_('SITELOCK_CHANGE_OPTIONS');
			}
		}

		$this->setRedirect(JURI::root(true) . '/administrator/index.php?option=com_sitelock', strlen($msg) ? $msg : NULL);
	}
	
	function clear_image(){
		$db = JFactory::getDBO();
		$db->setQuery('UPDATE #__sitelock_options SET `bgimage`="" WHERE `id`=1');
			
			if(!$db->query())
			{
				$msg .= JText::_('SITELOCK_DELETE_BACKGROUND_IMAGE_ERROR') . $db->getErrorMsg() . '<br />';
			}
			else
			{
				$msg .= JText::_('SITELOCK_DELETE_BACKGROUND_IMAGE');
			}
			
			$this->setRedirect(JURI::root(true) . '/administrator/index.php?option=com_sitelock', strlen($msg) ? $msg : NULL);
		
		}

	function remove()
	{
		$msg = '';

		/* Array of checked usernames */
		$checked = array();
		foreach($_REQUEST as $var => $val)
		{
			if(!strncmp($var, 'check', strlen('check')))
			{
				if($val)
				{
					$name = substr($var, strlen('check'));
					$checked[] = $name;
				}
			}
		}

		$db = JFactory::getDBO();

		foreach($checked as $name)
		{
			//Replace '_' with 'blank space' in the name
			$strname = str_replace("_"," ",$name); 
			$db->setQuery('DELETE FROM #__sitelock_users WHERE `user`=' . $db->Quote($strname));
			if(!$db->Query())
			{
				$msg .= JText::_('SITELOCK_DELETE_USER_ERROR') . $name . ': ' . $db->getErrorMsg() . '<br />';
			}
			else
			{
				$msg .= JText::_('SITELOCK_DELETE_USER') . $name . '<br />';
			}
		}
		
		$this->setRedirect(JURI::root(true) . '/administrator/index.php?option=com_sitelock', strlen($msg) ? $msg : NULL);
	}

	function lock()
	{
        $db = JFactory::getDBO();
		$db->setQuery('UPDATE #__extensions SET `enabled`=\'1\' WHERE `folder`=\'system\' AND `element`=\'sitelock\'');
		if(!$db->query())
		{
			$msg = JText::_('SITELOCK_SITE_LOCKED_ERROR'). $db->getErrorMsg() . '<br />';
		}
		else
		{
			//$msg = 'Site locked';
			$msg = JText::_('SITELOCK_SITE_LOCKED');
		}
		$this->setRedirect(JURI::root(true) . '/administrator/index.php?option=com_sitelock', strlen($msg) ? $msg : NULL);
	}

	function unlock()
	{
        $db = JFactory::getDBO();
		$db->setQuery('UPDATE #__extensions SET `enabled`=\'0\' WHERE `folder`=\'system\' AND `element`=\'sitelock\'');
		if(!$db->query())
		{
			$msg = JText::_('SITELOCK_SITE_UNLOCKED_ERROR') . $db->getErrorMsg() . '<br />';
		}
		else
		{
			//$msg = 'Site unlocked';
			$msg = JText::_('SITELOCK_SITE_UNLOCKED');
		}
		$this->setRedirect(JURI::root(true) . '/administrator/index.php?option=com_sitelock', strlen($msg) ? $msg : NULL);
	}

}

