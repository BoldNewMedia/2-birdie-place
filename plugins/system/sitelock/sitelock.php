<?php
/**
* @version		$Id: cache.php 11616 2009-02-07 14:09:52Z kdevine $
* @package		Joomla
* @copyright	Copyright (C) 2009 GDR!
* @license		GNU/GPL, see LICENSE.php
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );
if(!defined('DS')){
   define('DS',DIRECTORY_SEPARATOR);
}
//jimport( 'joomla.plugin.plugin' );

/**
 * Joomla! Site LockPlugin
 *
 * @package		Joomla
 * @subpackage	System
 */

jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');

class  plgSystemSiteLock extends JPlugin
{
	/**
	* Locking the site if needed
	*
	*/
	function onAfterInitialise()
	{
		//global $mainframe;
		
		$this->loadLanguage();
		
		$mainframe = JFactory::getApplication();		

		if($mainframe->isAdmin() || JDEBUG) {
			return;
		}

		$cfg = new JConfig();

		if(isset($_REQUEST['username']) && isset($_REQUEST['passwd']))
		{
			$_SESSION['lockuser'] = $_REQUEST['username'];
			$_SESSION['lockpass'] = md5($cfg->secret . 'lock' . $_REQUEST['passwd']);
		}

		if(isset($_SESSION['lockuser']) && isset($_SESSION['lockpass']))
		{
			$db = JFactory::getDBO();
			$db->setQuery('SELECT COUNT(*) FROM #__sitelock_users WHERE `user`=' . $db->Quote($_SESSION['lockuser']) . ' AND `password`=' . $db->Quote($_SESSION['lockpass']));
			$db->query();
			if($db->loadResult())
			{
				return;
			}else{
				$error = JText::_('WRONG_USERNAME_PASSWORD');
			}
		}
		// Code for getting options
		  $db = JFactory::getDBO();
	      $sql="Select title, bgcolor, bgimage, login,message FROM #__sitelock_options";
		  $db->setQuery($sql);
		  $options=$db->loadObject();
                  
                  /* BGM called css from yootheme */ 
                  $template = $mainframe->getTemplate(true);
                  $params   = json_decode($template->params->get('config'));
                  $child_theme = $params->child_theme;
                  
                  $style = [];
                  $child_path = JPATH_ROOT . '/templates/yootheme_' . $child_theme . '/css';
                  $child_url = JURI::root() . 'templates/yootheme_' . $child_theme . '/css/';
                  if(is_dir($child_path)){
                      $style = JFolder::files($child_path);
                  }
                  
                  /* END */
        ?>
<html>
	<head>
		<meta http-equiv="content-type" content="text/html; charset=utf-8" />
		<title><?php echo $options->title; ?></title>
		<link rel="stylesheet" href="<?php echo JURI::root(); ?>templates/system/css/offline.css" type="text/css" />
		<link rel="stylesheet" href="<?php echo JURI::root(); ?>templates/system/css/system.css" type="text/css" />
                <?php if($mainframe->getTemplate() == 'yootheme'){ ?>
                    <script rel="stylesheet" href="<?php echo JURI::root(); ?>templates/yootheme/vendor/assets/uikit/dist/js/uikit.min.js"></script>
                    <?php foreach ($style as $file){ ?>
                        <link rel="stylesheet" href="<?php echo $child_url . $file; ?>"/>
                    <?php } ?>
                <?php } ?>
                <style type="text/css">
                    <?php if(!empty($options->bgcolor)){ ?>
			 body{
                            background-color:<?php echo $options->bgcolor; ?>;
			 }
                    <?php } ?> 
                    <?php if(!empty($options->bgimage)){ ?>
			 body{
                            background-image:url(<?php echo JURI::root(). $options->bgimage; ?>);
                            background-repeat: no-repeat;
                            background-position: top;
			 }
                    <?php } ?>
                </style>
	</head>
<body> 
    <div class="uk-section">
<?php
	//$file = JPATH_PLUGINS . DS . 'system' .DS . 'sitelock' . DS . 'sitelock.html';
//	echo '<div class="splash">'.(file_get_contents($file)).'</div>';
	$dispatcher = JEventDispatcher::getInstance();
	JPluginHelper::importPlugin('content');
	$options->text = $options->message;
	$params = array();
	$dispatcher->trigger('onContentPrepare', array ('', &$options, &$params,  0)); 
	echo '<div class="uk-text-default uk-margin-large uk-margin-large-top">'.$options->text.'</div>';
?>
<?php if($options->login==1): ?>
<div id="frame" class="outline">
	<h3 class="header uk-title">
		<?php echo JText::_('LOGIN_NOTE'); ?>
	</h3>
	<form action="index.php" method="post" name="login" id="form-login" class="uk-form">
		<div class="content">
			<p id="form-login-username">
				<input name="username" id="username" type="text" class="input" alt="Username" size="18" placeholder="<?php echo JText::_('USERNAME'); ?>" />
			</p>
			<p id="form-login-password">
				<input type="password" name="passwd" class="input" size="18" alt="Password" id="passwd" placeholder="<?php echo JText::_('PASSWORD'); ?>"/>
			</p>
			<div class="footer">
				<input type="submit" name="Submit" class="btn btn-info uk-button" value="<?php echo JText::_('LOGIN'); ?>"/>	
				<br>
				<?php
					if(isset($error))
					{
						echo "<p style='color: red;'>Error : ".$error."</p>";
					}
				?>
			</div>
		</div>
	</form>
</div>
<?php endif;?>
        </div>
</body>
</html>
		<?php

		die();

	}
}
?>
