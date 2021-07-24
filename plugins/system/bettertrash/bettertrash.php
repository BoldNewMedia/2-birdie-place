<?php
/**
 * @package         Better Trash
 * @version         1.5.0PRO
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            http://regularlabs.com
 * @copyright       Copyright © 2021 Regular Labs All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory as JFactory;
use Joomla\CMS\Language\Text as JText;
use RegularLabs\Library\Document as RL_Document;
use RegularLabs\Library\Extension as RL_Extension;
use RegularLabs\Library\Language as RL_Language;
use RegularLabs\Library\SystemPlugin as RL_SystemPlugin;
use RegularLabs\Plugin\System\BetterTrash\Buttons;
use RegularLabs\Plugin\System\BetterTrash\Storage;
use RegularLabs\Plugin\System\BetterTrash\Trash;

// Do not instantiate plugin on install pages
// to prevent installation/update breaking because of potential breaking changes
$input = JFactory::getApplication()->input;
if (in_array($input->get('option'), ['com_installer', 'com_regularlabsmanager']) && $input->get('action') != '')
{
	return;
}

if ( ! is_file(__DIR__ . '/vendor/autoload.php'))
{
	return;
}

require_once __DIR__ . '/vendor/autoload.php';

if ( ! is_file(JPATH_LIBRARIES . '/regularlabs/autoload.php')
	|| ! is_file(JPATH_LIBRARIES . '/regularlabs/src/SystemPlugin.php')
)
{
	JFactory::getLanguage()->load('plg_system_bettertrash', __DIR__);
	JFactory::getApplication()->enqueueMessage(
		JText::sprintf('BT_EXTENSION_CAN_NOT_FUNCTION', JText::_('BETTERTRASH'))
		. ' ' . JText::_('BT_REGULAR_LABS_LIBRARY_NOT_INSTALLED'),
		'error'
	);

	return;
}

require_once JPATH_LIBRARIES . '/regularlabs/autoload.php';

if ( ! RL_Document::isJoomlaVersion(3, 'BETTERTRASH'))
{
	RL_Extension::disable('bettertrash', 'plugin');

	RL_Language::load('plg_system_regularlabs');

	JFactory::getApplication()->enqueueMessage(
		JText::sprintf('RL_PLUGIN_HAS_BEEN_DISABLED', JText::_('BETTERTRASH')),
		'error'
	);

	return;
}

if (true)
{
	class PlgSystemBetterTrash extends RL_SystemPlugin
	{
		public $_lang_prefix           = 'BT';
		public $_enable_in_frontend    = false;
		public $_enable_in_admin       = true;
		public $_disable_on_components = true;
		public $_page_types            = ['html'];
		public $_jversion              = 3;

		private $storage;
		private $buttons;
		private $trash;

		public function __construct(&$subject, $config = [])
		{
			parent::__construct($subject, $config);

			$this->trash   = new Trash;
			$this->storage = new Storage;
			$this->buttons = new Buttons;
		}

		protected function handleOnAfterInitialise()
		{
			$this->trash->remove();
		}

		protected function handleOnContentAfterSave($context, $item, $isNew)
		{
			$this->storage->updateItem($item, $isNew, $context);
		}

		protected function handleOnContentAfterDelete($context, $item)
		{
			$this->storage->removeItem($item, $context);
		}

		protected function handleOnContentChangeState($context, $ids, $state)
		{
			$this->storage->updateList($ids, $state, $context);
		}

		protected function handleOnAfterRender()
		{
			$this->buttons->change();
		}
	}
}
