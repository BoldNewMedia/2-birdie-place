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

defined('_JEXEC') or die('Restricted access');
if(!defined('DS')){
   define('DS',DIRECTORY_SEPARATOR);
}

class sitelockAdmin {


	function _getversion()
	{
		$xml=JFactory::getXML(JPATH_COMPONENT_ADMINISTRATOR.DS.'registrationpro.xml');
		$version=(string)$xml->version;
		return $version;
	}
	
	function footer()
	{
	 ?>
	 	<table border="0" cellpadding="0" cellspacing="0" style ="margin-bottom: 0px; width: 100%; border-top: thin solid rgb(229, 229, 229);">
			<tr>
				<td style="width:25%; text-align:left; vertical-align:middle;"><a href="http://www.joomlashowroom.com" target="_blank"> <?php echo JText::_('PRODUCT_SUPPORT_CENTER'); ?> </a> <br /><a href="http://twitter.com/joomlashowroom" target="_blank"> <?php echo JText::_('PRODUCT_FOLLOW_US_TWITTER'); ?> </a> <br /><a href="http://extensions.joomla.org/extensions/extension/miscellaneous/offline/site-lock" target="_blank"> <?php echo JText::_('PRODUCT_JED_FEEDBACK'); ?> </a><br /><a href="https://billing.cloudaccess.net/aff.php?aff=21" target="_blank"> <?php echo JText::_('FREE JOOMLA CLOUD HOSTING'); ?> </a>
				</td>
				<td style="width:50%; text-align:center; vertical-align:middle;"> <?php echo JText::_('PRODUCT_NAME').": ".JText::_('PRODUCT_DESCRIPTION'); ?> <br/> <?php echo JText::_('LBLCOPYRIGHT').": ".JText::_('COPYRIGHT');?> <br/> <?php echo JText::_('LBLPHPVERSION').": (".JText::_('PRODUCT_PHP_VERSION_REQUIRED').") (".JText::_('PRODUCT_PHP_VERSION_CURRENT')." ".@phpversion().")";?> </td>
				<td style="width:25%; text-align:right; vertical-align:middle;"><a href="http://www.joomlashowroom.com" target="_blank"><img src="<?php echo SITELOCK_ADMIN_IMG_PATH;?>/Joomla_Showroom_logo.png" border="0" /></a></td>
			</tr>
		</table>		
	<?php
	}
	
}

?>