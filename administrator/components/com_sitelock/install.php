<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
defined('DS') or define('DS',DIRECTORY_SEPARATOR);

/**
 * Script file of Sitelock component
 */
class com_sitelockInstallerScript
{
        /**
         * method to install the component
         *
         * @return void
         */
        function install($parent) 
        {
            $manifest = $parent->get("manifest");
            $parent = $parent->getParent();
            $source = $parent->getPath("source");
             
            $installer = new JInstaller();
            
            // Install plugins
            foreach($manifest->plugins->plugin as $plugin) {
                $attributes = $plugin->attributes();
                $plg = $source . DS . $attributes['folder'].DS.$attributes['plugin'];
                $installer->install($plg);
            }
                                   
            $db = JFactory::getDbo();
            $tableExtensions = $db->quoteName("#__extensions");
            $columnElement   = $db->quoteName("element");
            $columnType      = $db->quoteName("type");
            $columnEnabled   = $db->quoteName("enabled");
            
            // Enable plugins
            $db->setQuery(
                "UPDATE 
                    $tableExtensions
                SET
                    $columnEnabled=1
                WHERE
                    $columnElement='sitelock'
                AND
                    $columnType='plugin'"
            );
            
            $db->query();	
															
			$db->setQuery("CREATE TABLE IF NOT EXISTS `#__sitelock_users` (
			`user` varchar(64) NOT NULL,
			`password` varchar(33) NOT NULL,
			PRIMARY KEY  (`user`),
			KEY `password` (`password`)
			) ENGINE=MyISAM DEFAULT CHARSET=latin1;");
			$db->query();
			
			$db->setQuery("CREATE TABLE IF NOT EXISTS `#__sitelock_options` (
					  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
					  `title` varchar(255) NOT NULL,
					  `bgcolor` varchar(255) NOT NULL,
					  `bgimage` varchar(255) NOT NULL,
					  `login` tinyint(1) NOT NULL,
					  `message` text,
					  `created_by` int(11) NOT NULL,
					  PRIMARY KEY (`id`)
					) ENGINE=MyISAM DEFAULT CHARSET=latin1;");
			$db->query();	

			// change charset to utf8
			$db->setQuery("ALTER TABLE `#__sitelock_options` CONVERT TO CHARACTER SET utf8");	
			$db->query();

			$db->setQuery("INSERT INTO `#__sitelock_options` (`id`, `title`, `bgcolor`, `bgimage`, `login`,`message`, `created_by`) VALUES
			(NULL, 'Site Lock', '#ffffff', '', '1','Locked!!!', 0);");
			$db->query();
			
			
			if(!$db->Query())
			{
				$msg = "<p>Error installing component: " . $db->getErrorMsg() . "</p>";
				$msg .= "<ul><li>If you have previously used this component, chances are that you already have the users table and can ignore this message</li></ul>";
				JFactory::getApplication()->enqueueMessage($msg, 'error');
			}					            
        }
 
        /**
         * method to uninstall the component
         *
         * @return void
         */
        function uninstall($parent) 
        {
			//die("uninstall");
			$db = JFactory::getDBO();
			
			$db->setQuery('SELECT `extension_id` FROM #__extensions WHERE `type` = "plugin" AND `element` = "sitelock" AND `folder` = "system"');

			$id = $db->loadResult();
			if($id)
			{
				$installer = new JInstaller;
				$result = $installer->uninstall('plugin',$id,1);
				$status->plugins[] = array('name'=>'plg_srp','group'=>'system', 'result'=>$result);
			}

                // $parent is the class calling this method
                //echo '<p>' . JText::_('COM_HELLOWORLD_UNINSTALL_TEXT') . '</p>';
		?>
				<div class="header">Removed SiteLock</div>
				<a href="www.JoomlaShowroom.com"><img src="<?php echo JURI::root(); ?>administrator/components/com_sitelock/assets/images/logo.png" alt="JoomlaShowroom" /></a>	
		<?php		
        }
 
        /**
         * method to update the component
         *
         * @return void
         */
        function update($parent) 
        {
				
				$db = JFactory::getDbo();

				$manifest = $parent->get("manifest");
				$parent = $parent->getParent();
				$source = $parent->getPath("source");
				jimport('joomla.filesystem.file');
				/* Read the old file content */
				if(file_exists(JPATH_PLUGINS . DS . 'system' .DS . 'sitelock' . DS . 'sitelock.html'))
				{
					$content =  JFile::read(JPATH_PLUGINS . DS . 'system' .DS . 'sitelock' . DS . 'sitelock.html');
				}else{
					$content = "Locked!!!";
				}
				
				$installer = new JInstaller();
				
				// Install plugins
				foreach($manifest->plugins->plugin as $plugin) {
					$attributes = $plugin->attributes();
					$plg = $source . DS . $attributes['folder'].DS.$attributes['plugin'];
					$installer->install($plg);
				}   
				
				$db->setQuery("CREATE TABLE IF NOT EXISTS `#__sitelock_users` (
				`user` varchar(64) NOT NULL,
				`password` varchar(33) NOT NULL,
				PRIMARY KEY  (`user`),
				KEY `password` (`password`)
				) ENGINE=MyISAM DEFAULT CHARSET=latin1;");
				
				if(!$db->Query())
				{
					$msg = "<p>Error installing component: " . $db->getErrorMsg() . "</p>";
					$msg .= "<ul><li>If you have previously used this component, chances are that you already have the users table and can ignore this message</li></ul>";
					JFactory::getApplication()->enqueueMessage($msg, 'error');
				}  
				
				$db->setQuery("SHOW COLUMNS FROM `#__sitelock_options` WHERE field = 'message'");
				$result = $db->loadObjectlist();
	
				if(count($result)==0)
				{
					$explanation = htmlspecialchars($content);
					$db->setQuery("ALTER TABLE `#__sitelock_options` ADD `message` text");
					$db->query();
					$db->setQuery("UPDATE `#__sitelock_options` SET `message` = \"".$explanation."\"");
					$db->query();
					
				}                 
        }
 
        /**
         * method to run before an install/update/uninstall method
         *
         * @return void
         */
        function preflight($type, $parent) 
        {
                // $parent is the class calling this method
                // $type is the type of change (install, update or discover_install)
                //echo '<p>' . JText::_('COM_HELLOWORLD_PREFLIGHT_' . $type . '_TEXT') . '</p>';								
        }
 
        /**
         * method to run after an install/update/uninstall method
         *
         * @return void
         */
        function postflight($type, $parent) 
        {
                // $parent is the class calling this method
                // $type is the type of change (install, update or discover_install)
                //echo '<p>' . JText::_('COM_HELLOWORLD_POSTFLIGHT_' . $type . '_TEXT') . '</p>';
				//echo "Last";
				echo '<div class="header">SiteLock is sucessfully installed </div>';
				echo '<a href="www.JoomlaShowroom.com"><img src="'.JURI::root().'administrator/components/com_sitelock/assets/images/logo.png" alt="JoomlaShowroom" /></a>';
        }
}
?>