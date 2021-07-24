<?php
/**
 * @package         Articles Field
 * @version         3.6.0PRO
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            http://regularlabs.com
 * @copyright       Copyright © 2021 Regular Labs All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

use RegularLabs\Library\Document as RL_Document;
use RegularLabs\Library\Extension as RL_Extension;

defined('_JEXEC') or die;

if ( ! is_file(JPATH_LIBRARIES . '/regularlabs/autoload.php'))
{
	return;
}

require_once JPATH_LIBRARIES . '/regularlabs/autoload.php';

if ( ! RL_Document::isJoomlaVersion(3))
{
	RL_Extension::disable('articleslinked', 'plugin', 'fields');

	return;
}

if (true)
{
	JLoader::import('components.com_fields.libraries.fieldsplugin', JPATH_ADMINISTRATOR);

	JForm::addFieldPath(JPATH_PLUGINS . '/fields/articleslinked/fields');

	/**
	 * Fields Articles Linked Plugin
	 */
	class PlgFieldsArticlesLinked extends FieldsPlugin
	{
	}
}
