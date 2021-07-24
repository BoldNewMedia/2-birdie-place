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

use Joomla\CMS\Factory as JFactory;
use Joomla\CMS\HTML\HTMLHelper as JHtml;
use Joomla\CMS\Language\Text as JText;
use Joomla\Registry\Registry;
use RegularLabs\Library\ArrayHelper as RL_Array;
use RegularLabs\Library\DB as RL_DB;
use RegularLabs\Library\Document as RL_Document;
use RegularLabs\Library\Field as RL_Field;
use RegularLabs\Library\Form as RL_Form;
use RegularLabs\Library\ParametersNew as RL_Parameters;

if (is_file(JPATH_LIBRARIES . '/regularlabs/autoload.php'))
{
	require_once JPATH_LIBRARIES . '/regularlabs/autoload.php';
}

require_once JPATH_PLUGINS . '/fields/articles/helper.php';
require_once JPATH_PLUGINS . '/fields/articles/filters.php';

class JFormFieldArticles extends RL_Field
{
	public $attributes;
	public $type = 'Articles';

	protected function getInput()
	{
		$this->params = $this->element->attributes();

		$plugin_params = RL_Parameters::getPlugin('articles', 'fields');

		if ( ! is_array($this->value))
		{
			$this->value = explode(',', $this->value);
		}

		$attributes = [
			'fieldtype'            => 'articles',
			'multiple'             => $this->get('multiple', $plugin_params->multiple),
			'currentid'            => $this->getCurrentArticleId(),
			'ordering'             => $this->get('articles_ordering', 'title'),
			'ordering_direction'   => $this->get('articles_ordering_direction', 'ASC'),
			'ordering_2'           => $this->get('articles_ordering_2', 'created'),
			'ordering_direction_2' => $this->get('articles_ordering_direction_2', 'DESC'),
			'grouping'             => $this->get('articles_grouping', ''),
			'show_category'        => $this->get('show_category', '1'),
			'show_unpublished'     => $this->get('show_unpublished', '1'),
		];

		$filters = $this->getFilters();

		$attributes = array_merge($attributes, $filters);

		$this->attributes = $options = new Registry($attributes);

		$options = $this->getOptions();

		if (
			$this->attributes->get('multiple')
			&& $this->get('select_style', $plugin_params->select_style) == 'checkboxes'
		)
		{
			$options = $this->flattenGroups($options);

			return RL_Form::selectList(
				$options,
				$this->name,
				$this->value,
				$this->id,
				$this->attributes->get('size'),
				true,
				false,
				! FieldsHelper::canEditFieldValue($this)
			);
		}

		return RL_Form::selectListSimple(
			$options,
			$this->name,
			$this->value,
			$this->id,
			$this->attributes->get('size'),
			$this->attributes->get('multiple'),
			! FieldsHelper::canEditFieldValue($this)
		);
	}

	protected function getOptions()
	{
		$query = $this->db->getQuery(true)
			->select('COUNT(*)')
			->from($this->db->quoteName('#__content', 'a'))
			->where('a.access > -1');

		$filters = new PlgFieldsArticlesFilters($this->attributes, $this->form);
		$filters->addToQuery($query);

		$categories = $filters->getCategories();

		$this->db->setQuery($query);
		$total = $this->db->loadResult();

		if ($total > $this->max_list_count)
		{
			return -1;
		}

		$primary_ordering    = $this->attributes->get('ordering', 'title');
		$primary_direction   = $this->attributes->get('ordering_direction', 'ASC');
		$secondary_ordering  = $this->attributes->get('ordering_2', 'created');
		$secondary_direction = $this->attributes->get('ordering_direction_2', 'DESC');

		$ordering = PlgFieldsArticlesHelper::getFullOrdering($primary_ordering, $primary_direction, $secondary_ordering, $secondary_direction);

		$grouping      = $this->attributes->get('grouping', '');
		$show_category = $this->attributes->get('show_category', 1) && count($categories) != 1;
		$extras        = ['language'];

		$query->clear('select')
			->select('a.id, a.title as name, a.language, a.state as published')
			->join('LEFT', $this->db->quoteName('#__categories', 'c') . ' ON c.id = a.catid');

		if ($show_category
			|| $grouping == 'category'
		)
		{
			$query->select(['a.catid', 'c.title as cat']);
		}

		if ($show_category && $grouping != 'category')
		{
			$extras[] = 'cat';
		}

		if (RL_Document::isAdmin())
		{
			$extras[] = 'id';
		}

		if ($grouping == 'category')
		{
			$ordering = 'c.title ASC, ' . $ordering;
			if (strpos($ordering, '.ordering'))
			{
				$ordering = 'c.lft , ' . $ordering;
			}
		}

		$query->where($this->db->quoteName('a.state') . RL_DB::in([0, 1]));

		if ( ! $this->attributes->get('show_unpublished', 1))
		{
			// Filter by start and end dates.
			$nullDate = $this->db->quote($this->db->getNullDate());
			$date     = JFactory::getDate();

			$nowDate = $this->db->quote($date->toSql());

			$query->where($this->db->quoteName('a.state') . ' = 1')
				->where('(' . $this->db->quoteName('a.publish_up') . ' = ' . $nullDate . ' OR ' . $this->db->quoteName('a.publish_up') . ' <= ' . $nowDate . ')')
				->where('(' . $this->db->quoteName('a.publish_down') . ' = ' . $nullDate . ' OR ' . $this->db->quoteName('a.publish_down') . ' >= ' . $nowDate . ')');
		}

		$query->order($ordering);

		$this->db->setQuery($query);
		$list = $this->db->loadObjectList('id');

		switch ($grouping)
		{
			case  'category':
				$options = $this->getOptionsByListGroupedByCategory($list, $extras);
				break;

			default:
				$options = $this->getOptionsByList($list, $extras);
				break;
		}

		$currentid = $this->attributes->get('currentid');

		if (isset($options[$currentid]) && isset($options[$currentid]->text))
		{
			$options[$currentid]->disable = true;
			$options[$currentid]->text    .= ' (' . JText::_('RL_CURRENT') . ')';
			if (strpos($options[$currentid]->text, 'color:grey') === false)
			{
				$options[$currentid]->text = '[[:font-style:italic;color:grey;:]]' . $options[$currentid]->text;
			}
		}

		if ($this->attributes->get('multiple'))
		{
			return $options;
		}

		if ($grouping == 'category')
		{
			array_unshift($options, [
				'id'    => '',
				'text'  => '',
				'items' => [
					JHtml::_('select.option', '', '- ' . JText::_('Select Item') . ' -'),
					JHtml::_('select.option', '-', '&nbsp;', 'value', 'text', true),
				],
			]);

			return $options;
		}

		array_unshift($options, JHtml::_('select.option', '-', '&nbsp;', 'value', 'text', true));
		array_unshift($options, JHtml::_('select.option', '', '- ' . JText::_('Select Item') . ' -'));

		return $options;
	}

	private function flattenGroups($groups, $level = 0)
	{
		$groups = RL_Array::toArray($groups);

		$list = [];

		foreach ($groups as $id => $item)
		{
			$item = (object) $item;

			$item->level = $level;

			if ( ! isset($item->value))
			{
				$item->value       = '';
				$item->hide_select = true;
				$item->disable     = true;
			}

			$list[$id] = $item;

			if (empty($item->items))
			{
				continue;
			}

			$list = array_merge(
				$list,
				$this->flattenGroups($item->items, $level + 1)
			);
		}

		return $list;
	}

	private function getCurrentArticleId()
	{
		$filters = new PlgFieldsArticlesFilters($this, $this->form);

		return $filters->getCurrentArticleId();
	}

	private function getFilters()
	{
		$filters = new PlgFieldsArticlesFilters($this, $this->form);

		return $filters->get();
	}

	private function getOptionsByListGroupedByCategory($list, $extras = [])
	{
		$groups = [];

		$last_cat = '';

		foreach ($list as $id => $item)
		{
			if ($item->catid != $last_cat)
			{
				$groups[$item->catid] = [
					'id'    => $item->catid,
					'text'  => $item->cat,
					'level' => 0,
					'items' => [],
				];

				$last_cat = $item->catid;
			}

			$groups[$item->catid]['items'][$id] = $this->getOptionByListItem($item, $extras);
		}

		return $groups;
	}
}
