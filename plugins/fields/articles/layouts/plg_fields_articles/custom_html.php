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

defined('_JEXEC') or die();


/*
 * @var array  $displayData
 * @var object $article
 * @var object $settings
 */
extract($displayData);

$html = $settings->custom_html;

if (empty($html))
{
	return;
}

require_once JPATH_PLUGINS . '/fields/articles/helper.php';

$html = PlgFieldsArticlesHelper::replaceDataTags($html, $article);
$html = PlgFieldsArticlesHelper::runThroughArticlesAnywhere($html);

echo $html;
