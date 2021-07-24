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

use Joomla\CMS\Factory as JFactory;
use Joomla\CMS\Plugin\PluginHelper as JPluginHelper;
use Joomla\CMS\Router\Route as JRoute;
use RegularLabs\Library\ArrayHelper as RL_Array;
use RegularLabs\Library\Article as RL_Article;
use RegularLabs\Library\DB as RL_DB;
use RegularLabs\Library\ParametersNew as RL_Parameters;
use RegularLabs\Library\RegEx as RL_RegEx;
use RegularLabs\Plugin\System\ArticlesAnywhere\Replace as AA_Replace;

if (is_file(JPATH_LIBRARIES . '/regularlabs/autoload.php'))
{
	require_once JPATH_LIBRARIES . '/regularlabs/autoload.php';
}

class PlgFieldsArticlesHelper
{
	public static function getFullOrdering(
		$primary_ordering, $primary_direction,
		$secondary_ordering, $secondary_direction,
		$prefix_articles = 'a',
		$prefix_categories = 'c'
	)
	{
		$db = JFactory::getDbo();

		self::prepareOrdering($primary_ordering, $primary_direction);
		self::prepareOrdering($secondary_ordering, $secondary_direction);

		self::prefixOrdering($primary_ordering, $prefix_articles, $prefix_categories);
		self::prefixOrdering($secondary_ordering, $prefix_articles, $prefix_categories);

		return $db->quoteName($primary_ordering) . ' ' . $primary_direction . ','
			. $db->quoteName($secondary_ordering) . ' ' . $secondary_direction;
	}

	public static function getLinkedArticleIds($field_ids, $article_id, $field)
	{
		$article_ids = [];

		foreach ($field_ids as $field_id)
		{
			$field_article_ids = self::getLinkedArticleIdsByFieldId($field_id, $article_id, $field);

			$article_ids = array_merge($article_ids, $field_article_ids);
		}

		if (empty($field_ids))
		{
			$article_ids = self::getLinkedArticleIdsByFieldId(0, $article_id, $field);
		}

		$primary_ordering    = $field->fieldparams->get('linked_articles_ordering', 'title');
		$primary_direction   = $field->fieldparams->get('linked_articles_ordering_direction', 'ASC');
		$secondary_ordering  = $field->fieldparams->get('linked_articles_ordering_2', 'created');
		$secondary_direction = $field->fieldparams->get('linked_articles_ordering_direction_2', 'DESC');

		$ordering = self::getFullOrdering($primary_ordering, $primary_direction, $secondary_ordering, $secondary_direction);

		$db = JFactory::getDbo();

		$nullDate = $db->quote($db->getNullDate());
		$nowDate  = $db->quote(JFactory::getDate()->toSql());

		$query = $db->getQuery(true)
			->select('a.id')
			->from($db->quoteName('#__content', 'a'))
			->join('LEFT', $db->quoteName('#__categories', 'c') . ' ON c.id = a.catid')
			->where('a.id' . RL_DB::in($article_ids))
			->where('a.state = 1')
			->where('(a.publish_up IS NULL OR a.publish_up = ' . $nullDate . ' OR a.publish_up <= ' . $nowDate . ')')
			->where('(a.publish_down IS NULL OR a.publish_down = ' . $nullDate . ' OR a.publish_down >= ' . $nowDate . ')')
			->order($ordering);

		$db->setQuery($query);

		return $db->loadColumn() ?: [];
	}

	public static function prepareCustomField($context, $item, &$field)
	{
		JPluginHelper::importPlugin('fields');

		$dispatcher = JEventDispatcher::getInstance();

		// Event allow plugins to modify the output of the field before it is prepared
		$dispatcher->trigger('onCustomFieldsBeforePrepareField', [$context, $item, &$field]);

		// Gathering the value for the field
		$value = $dispatcher->trigger('onCustomFieldsPrepareField', [$context, $item, &$field]);

		if (is_array($value))
		{
			$value = implode($value, ' ');
		}

		// Event allow plugins to modify the output of the prepared field
		$dispatcher->trigger('onCustomFieldsAfterPrepareField', [$context, $item, $field, &$value]);

		// Assign the value
		$field->value = $value;
	}

	public static function renderLayout($ids, $layout, $field, $layout_type = 'title', $apply_ordering = true)
	{
		if (count($ids) === 1)
		{
			$ids = RL_Array::toArray($ids[0]);
		}

		$settings = (object) [];
		switch ($layout_type)
		{
			case 'custom_html':
				$settings->custom_html = $field->fieldparams->get('custom_html', '');
				break;
			case 'title_custom':
				$settings->custom_field = $field->fieldparams->get('custom_field', '');
				$settings->link_title   = $field->fieldparams->get('link_title', 1);
				break;
			case 'title':
			default:
				$settings->link_title = $field->fieldparams->get('link_title', 1);
				break;
		}

		$outputs   = self::getOutputs($ids, $layout, $field, $settings, $apply_ordering);
		$separator = $field->fieldparams->get('use_separator') ? $field->fieldparams->get('separator', ', ') : '';

		echo implode($separator, $outputs);
	}

	public static function replaceDataTags($string, &$article)
	{
		$tags = self::getDataTags($string);

		foreach ($tags as $tag)
		{
			$result = self::getDataTagValue($article, $tag);

			if ($result === false || ! is_string($result))
			{
				continue;
			}

			$string = str_replace($tag[0], $result, $string);
		}

		return $string;
	}

	public static function runThroughArticlesAnywhere($string)
	{
		$articlesanywhere_params = RL_Parameters::getPlugin('articlesanywhere');

		if (empty($articlesanywhere_params) || ! isset($articlesanywhere_params->article_tag) || ! isset($articlesanywhere_params->articles_tag))
		{
			return $string;
		}

		AA_Replace::replaceTags($string);

		return $string;
	}

	private static function addFilters(&$query, $field)
	{
		require_once 'filters.php';
		$filters = new PlgFieldsArticlesFilters($field->fieldparams);

		$filters->addToQuery($query);
	}

	private static function getArticleFieldIds()
	{
		$db = JFactory::getDbo();

		$query = $db->getQuery(true)
			->select('a.id')
			->from($db->quoteName('#__fields', 'a'))
			->where('a.type = ' . $db->quote('articles'));

		$db->setQuery($query);

		return $db->loadColumn();
	}

	private static function getArticleOrderId($article, $ordering)
	{
		if ( ! isset($article->{$ordering}))
		{
			return 0;
		}

		switch ($ordering)
		{
			case 'ordering':
			case 'hits':
				return ($article->{$ordering} + 100000000);

			case 'created_time':
			case 'modified_time':
			case 'publish_up':
			case 'alias':
			case 'title':
			default:
				return strtolower($article->{$ordering});
		}
	}

	private static function getCategoriesByFieldId($field_id)
	{
		$db = JFactory::getDbo();

		$query = $db->getQuery(true)
			->select('a.category_id')
			->from($db->quoteName('#__fields_categories', 'a'))
			->where('a.field_id = ' . (int) $field_id);

		$db->setQuery($query);

		$categories       = $db->loadColumn();
		$child_categories = self::getChildCategories($categories);

		return array_merge($categories, $child_categories);
	}

	private static function getChildCategories($categories)
	{
		$db = JFactory::getDbo();

		$query = $db->getQuery(true)
			->select('a.id')
			->from($db->quoteName('#__categories', 'a'))
			->where('a.parent_id' . RL_DB::in($categories));

		$db->setQuery($query);

		$child_categories = $db->loadColumn();

		if (empty($child_categories))
		{
			return [];
		}

		$sub_child_categories = self::getChildCategories($child_categories);

		return array_merge($child_categories, $sub_child_categories);
	}

	private static function getDataTagValue(&$article, $tag)
	{
		if (isset($article->{$tag['key']}) && is_string($article->{$tag['key']}))
		{
			return $article->{$tag['key']};
		}

		if ($tag['key'] == 'url')
		{
			$slug = $article->alias ? ($article->id . ':' . $article->alias) : $article->id;

			if ( ! class_exists('ContentHelperRoute'))
			{
				require_once JPATH_SITE . '/components/com_content/helpers/route.php';
			}

			return JRoute::_(ContentHelperRoute::getArticleRoute($slug, $article->catid, $article->language));
		}

		$article->urls = is_object($article->urls) ? $article->urls : json_decode($article->urls);
		if (isset($article->urls->{$tag['key']}))
		{
			return $article->urls->{$tag['key']};
		}

		$article->images = is_object($article->images) ? $article->images : json_decode($article->images);
		if (isset($article->images->{$tag['key']}))
		{
			return $article->images->{$tag['key']};
		}

		return self::getDataTagValueCustomField($article, $tag['key']);
	}

	private static function getDataTagValueCustomField(&$article, $key)
	{
		if ( ! isset($article->fields))
		{
			$article->fields = FieldsHelper::getFields('com_content.article', $article);
		}

		foreach ($article->fields as $field)
		{
			if ($field->name != $key)
			{
				continue;
			}

			// Field has no value
			if (empty($field->value))
			{
				return '';
			}

			// Prepare the value
			if ($field->value == $field->rawvalue)
			{
				self::prepareCustomField('com_content.article', $article, $field);
			}

			return $field->value;
		}

		return false;
	}

	private static function getDataTags($html)
	{
		RL_RegEx::matchAll('\[(?<key>[^ ]+?)\]', $html, $matches);

		return $matches;
	}

	private static function getLinkedArticleIdsByFieldId($field_id, $article_id, $field)
	{
		$db = JFactory::getDbo();

		$field_ids = RL_Array::toArray($field_id);

		if (empty($field_ids))
		{
			$field_ids = self::getArticleFieldIds();
		}

		$nullDate = $db->quote($db->getNullDate());
		$nowDate  = $db->quote(JFactory::getDate()->toSql());

		$query = $db->getQuery(true)
			->select('a.item_id')
			->from($db->quoteName('#__fields_values', 'a'))
			->where('a.value  = ' . (int) $article_id)
			->where('a.field_id' . RL_DB::in($field_ids));

		$db->setQuery($query);

		$article_ids = $db->loadColumn();

		$categories = self::getCategoriesByFieldId($field_id);

		$query = $db->getQuery(true)
			->select('a.id')
			->from($db->quoteName('#__content', 'a'))
			->where('a.id' . RL_DB::in($article_ids))
			->where('a.state = 1')
			->where('(a.publish_up IS NULL OR a.publish_up = ' . $nullDate . ' OR a.publish_up <= ' . $nowDate . ')')
			->where('(a.publish_down IS NULL OR a.publish_down = ' . $nullDate . ' OR a.publish_down >= ' . $nowDate . ')');

		if ( ! empty($categories))
		{
			$query->where('a.catid' . RL_DB::in($categories));
		}

		self::addFilters($query, $field);

		$db->setQuery($query);

		return $db->loadColumn();
	}

	private static function getOrderedOutputs($ids, $layout, $field, $settings)
	{
		$articles = [];

		foreach ($ids as $id)
		{
			if ( ! $id)
			{
				continue;
			}

			$article = RL_Article::get($id);

			if (empty($article->id))
			{
				continue;
			}

			$articles[] = $article;
		}

		$primary_ordering    = $field->fieldparams->get('articles_ordering', 'title');
		$primary_direction   = $field->fieldparams->get('articles_ordering_direction', 'ASC');
		$secondary_ordering  = $field->fieldparams->get('articles_ordering_2', 'created');
		$secondary_direction = $field->fieldparams->get('articles_ordering_direction_2', 'DESC');

		self::orderArticles($articles, $primary_ordering, $primary_direction, $secondary_ordering, $secondary_direction);

		$texts = [];

		foreach ($articles as $article)
		{
			$text = $layout->render(compact('article', 'settings'));

			if (empty($text))
			{
				continue;
			}

			$texts[] = $text;
		}

		return $texts;
	}

	private static function getOutputs($ids, $layout, $field, $settings, $apply_ordering)
	{
		$ids = array_unique($ids);

		if ($apply_ordering)
		{
			return self::getOrderedOutputs($ids, $layout, $field, $settings);
		}

		return self::getUnOrderedOutputs($ids, $layout, $field, $settings);
	}

	private static function getUnOrderedOutputs($ids, $layout, $field, $settings)
	{
		$outputs = [];

		foreach ($ids as $id)
		{
			if ( ! $id)
			{
				continue;
			}

			$article = RL_Article::get($id);

			if (empty($article->id))
			{
				continue;
			}

			$output = $layout->render(compact('article', 'settings'));

			if (empty($output))
			{
				continue;
			}

			$outputs[] = $output;
		}

		return $outputs;
	}

	private static function orderArticles(&$articles, $primary_ordering, $primary_direction = 'ASC', $secondary_ordering = '', $secondary_direction = 'ASC')
	{
		$ordered = self::orderArticlesSet($articles, $primary_ordering, $primary_direction);

		if ( ! $secondary_ordering)
		{
			$articles = RL_Array::flatten($ordered);

			return;
		}

		foreach ($ordered as &$ordered_set)
		{
			$ordered_set = self::orderArticlesSet($ordered_set, $secondary_ordering, $secondary_direction);
		}

		$articles = RL_Array::flatten($ordered);
	}

	private static function orderArticlesSet($articles, $ordering, $direction = 'ASC')
	{
		if ( ! is_array($articles) || count($articles) < 2)
		{
			return $articles;
		}

		self::prepareOrdering($ordering, $direction);

		$ordered = [];

		// Handle 1st ordering
		foreach ($articles as $article)
		{
			$order_id = self::getArticleOrderId($article, $ordering);

			if ( ! isset($ordered[$order_id]))
			{
				$ordered[$order_id] = [];
			}

			$ordered[$order_id][] = $article;
		}

		switch ($direction)
		{
			case 'DESC':
				krsort($ordered);
				break;
			case 'ASC':
			default:
				ksort($ordered);
				break;
		}

		return array_values($ordered);
	}

	private static function prefixOrdering(&$ordering, $prefix_articles = 'a', $prefix_categories = 'c')
	{
		if (strpos($ordering, 'category_') === 0)
		{
			$ordering = $prefix_categories . '.' . substr($ordering, strlen('category_'));

			return;
		}

		$ordering = $prefix_articles . '.' . $ordering;
	}

	private static function prepareOrdering(&$ordering, &$direction)
	{
		if ($ordering == 'featured')
		{
			$direction = $direction == 'DESC' ? 'ASC' : 'DESC';
		}
	}
}
