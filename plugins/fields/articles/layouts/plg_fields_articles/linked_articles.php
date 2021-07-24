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

defined('_JEXEC') or die;


if (is_file(JPATH_LIBRARIES . '/regularlabs/autoload.php'))
{
	require_once JPATH_LIBRARIES . '/regularlabs/autoload.php';
}

/*
 * @var array  $displayData
 * @var object $item
 * @var object $layout
 * @var object $field
 * @var string $layout_type
 */
extract($displayData);

if (empty($item->id))
{
	return;
}

require_once JPATH_PLUGINS . '/fields/articles/helper.php';

$custom_fields = (array) $field->fieldparams->get('linked_custom_fields', '');

$ids = PlgFieldsArticlesHelper::getLinkedArticleIds($custom_fields, $item->id, $field);

echo PlgFieldsArticlesHelper::renderLayout($ids, $layout, $field, $layout_type, false);
