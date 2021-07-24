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
use RegularLabs\Library\ArrayHelper as RL_Array;
use RegularLabs\Library\DB as RL_DB;

if (is_file(JPATH_LIBRARIES . '/regularlabs/autoload.php'))
{
	require_once JPATH_LIBRARIES . '/regularlabs/autoload.php';
}

class PlgFieldsArticlesFilters
{
	private $db;
	private $form;
	private $params;

	public function __construct($params, $form = null)
	{
		$this->params = $params;
		$this->form   = $form;
		$this->db     = JFactory::getDbo();
	}

	public function addToQuery(&$query)
	{
		$this->addCategoriesToQuery($query);
		$this->addTagsToQuery($query);
		$this->addUsersToQuery($query);
		$this->addCustomFieldsToQuery($query);
		$this->addLanguageToQuery($query);
	}

	public function get()
	{
		$filters = [];

		if ($this->params->get('filter_categories'))
		{
			$categories = RL_Array::toArray($this->params->get('categories'));
			if ($this->params->get('filter_categories') === 'current')
			{
				$categories = [$this->getCurrentCategoryId()];
			}

			$filters['filter_categories'] = true;

			$filters['categories']              = $categories;
			$filters['categories_inc_children'] = $this->params->get('categories_inc_children');
		}

		if ($this->params->get('filter_tags'))
		{
			$filters['filter_tags'] = true;

			$filters['tags']              = RL_Array::toArray($this->params->get('tags'));
			$filters['tags_inc_children'] = $this->params->get('tags_inc_children');
		}

		if ($this->params->get('filter_users'))
		{
			$filters['filter_users'] = true;

			$filters['users'] = RL_Array::toArray($this->params->get('users'));
		}

		if ($this->params->get('filter_language'))
		{
			$filters['filter_language'] = true;

			$filters['language'] = $this->params->get('filter_language') === 'current'
				? $this->getCurrentArticleLanguage()
				: $this->params->get('language');
		}

		if ($this->params->get('filter_customfields'))
		{
			$filters['filter_customfields'] = true;

			$filters = array_merge($filters, [
				'customfield1_id'    => $this->params->get('customfield1_id'),
				'customfield1_value' => $this->params->get('customfield1_value'),
				'customfield2_id'    => $this->params->get('customfield2_id'),
				'customfield2_value' => $this->params->get('customfield2_value'),
				'customfield3_id'    => $this->params->get('customfield3_id'),
				'customfield3_value' => $this->params->get('customfield3_value'),
			]);
		}

		return $filters;
	}

	public function getCategories()
	{
		if ( ! $this->params->get('filter_categories'))
		{
			return [];
		}

		if ($this->params->get('filter_categories') === 'current')
		{
			return [$this->getCurrentCategoryId()];
		}

		$categories = (array) $this->params->get('categories', []);

		if (empty($categories))
		{
			return [];
		}

		$inc_children = $this->params->get('categories_inc_children');

		if ( ! $inc_children)
		{
			return $categories;
		}

		$children = $this->getCategoriesChildIds($categories);

		if ($inc_children == 2)
		{
			return $children;
		}

		return array_merge($categories, $children);
	}

	public function getCurrentArticleId()
	{
		$input = JFactory::getApplication()->input;

		if ($input->get('option') != 'com_content'
			|| ! in_array($input->get('view'), ['form', 'article'])
			|| ! in_array($input->get('layout'), ['edit', 'modal'])
		)
		{
			return 0;
		}

		if ($this->form && $this->form->getValue('id'))
		{
			return $this->form->getValue('id');
		}

		return $input->getInt('id');
	}

	private function addCategoriesToQuery(&$query)
	{
		$categories = $this->getCategories();

		if (empty($categories))
		{
			return $categories;
		}

		$query->where('a.catid ' . RL_DB::in($categories));

		return $categories;
	}

	private function addCustomFieldsToQuery(&$query)
	{
		$ids = $this->getArticlesByCustomFields();

		if (is_null($ids))
		{
			return;
		}

		$query->where('a.id' . RL_DB::in($ids));

		return;
	}

	private function addLanguageToQuery(&$query)
	{
		if ( ! $this->params->get('filter_language'))
		{
			return [];
		}

		if ($this->params->get('filter_language') === 'current')
		{
			return $this->getCurrentArticleLanguage();
		}

		$language = $this->params->get('language');

		if (empty($language) || $language == '*')
		{
			return '*';
		}

		$query->where('a.language' . RL_DB::in($language));

		return $language;
	}

	private function addTagsToQuery(&$query)
	{
		$tags = $this->getTags();

		if (empty($tags))
		{
			return;
		}

		$query->join('LEFT', $this->db->quoteName('#__contentitem_tag_map', 't') . ' ON t.content_item_id = a.id')
			->where('t.tag_id' . RL_DB::in($tags));

		return;
	}

	private function addUsersToQuery(&$query)
	{
		$users = $this->getUsers();

		if (empty($users))
		{
			return;
		}

		if (in_array('current', $users))
		{
			$user = JFactory::getApplication()->getIdentity() ?: JFactory::getUser();

			$users[] = $user->id;
			$users   = array_diff($users, ['current']);
		}

		$query->where('a.created_by' . RL_DB::in($users));

		return;
	}

	private function filterDownArticlesByCustomFields(&$ids, $id, $value)
	{
		if (empty($id))
		{
			return;
		}

		if (is_array($ids) && empty($ids))
		{
			return;
		}

		$value = RL_Array::toArray($value);

		$query = $this->db->getQuery(true)
			->select($this->db->quoteName('item_id'))
			->from($this->db->quoteName('#__fields_values'))
			->where('('
				. $this->db->quoteName('field_id') . ' = ' . (int) $id
				. ' AND '
				. $this->db->quoteName('value') . RL_DB::in($value)
				. ')');

		if (is_array($ids))
		{
			$query->where($this->db->quoteName('item_id') . RL_DB::in($ids));
		}

		$this->db->setQuery($query);

		$ids = $this->db->loadColumn();
	}

	private function getArticlesByCustomFields()
	{
		if ( ! $this->params->get('filter_customfields'))
		{
			return null;
		}

		$customfield1_id    = $this->params->get('customfield1_id');
		$customfield1_value = $this->params->get('customfield1_value');
		$customfield2_id    = $this->params->get('customfield2_id');
		$customfield2_value = $this->params->get('customfield2_value');
		$customfield3_id    = $this->params->get('customfield3_id');
		$customfield3_value = $this->params->get('customfield3_value');

		if (empty($customfield1_id) && empty($customfield2_id) && empty($customfield3_id))
		{
			return null;
		}

		$ids = null;

		$this->filterDownArticlesByCustomFields($ids, $customfield1_id, $customfield1_value);
		$this->filterDownArticlesByCustomFields($ids, $customfield2_id, $customfield2_value);
		$this->filterDownArticlesByCustomFields($ids, $customfield3_id, $customfield3_value);

		return $ids;
	}

	private function getCategoriesChildIds($categories = [])
	{
		$children = [];

		$query = $this->db->getQuery(true)
			->select('a.id')
			->from($this->db->quoteName('#__categories', 'a'))
			->where('a.extension = ' . $this->db->quote('com_content'))
			->where('a.published = 1');

		while ( ! empty($categories))
		{
			$query->clear('where')
				->where('a.parent_id' . RL_DB::in($categories));
			$this->db->setQuery($query);
			$categories = $this->db->loadColumn();

			$children = array_merge($children, $categories);
		}

		return $children;
	}

	private function getCurrentArticleLanguage()
	{
		$id = $this->getCurrentArticleId();

		if ( ! $id)
		{
			return '*';
		}

		$query = $this->db->getQuery(true)
			->select('a.language')
			->from($this->db->quoteName('#__content', 'a'))
			->where('a.id = ' . (int) $id);
		$this->db->setQuery($query);

		return $this->db->loadResult() ?: '*';
	}

	private function getCurrentCategoryId()
	{
		$id = $this->getCurrentArticleId();

		if ( ! $id)
		{
			return 0;
		}

		if ($this->form && $this->form->getValue('catid'))
		{
			return $this->form->getValue('catid');
		}

		$query = $this->db->getQuery(true)
			->select('a.catid')
			->from($this->db->quoteName('#__content', 'a'))
			->where('a.id = ' . (int) $id);
		$this->db->setQuery($query);

		return $this->db->loadResult();
	}

	private function getTags()
	{
		if ( ! $this->params->get('filter_tags'))
		{
			return [];
		}

		$tags = $this->params->get('tags', []);

		if (empty($tags))
		{
			return [];
		}

		$inc_children = $this->params->get('tags_inc_children');

		if ( ! $inc_children)
		{
			return $tags;
		}

		$children = $this->getTagsChildIds($tags);

		if ($inc_children == 2)
		{
			return $children;
		}

		return array_merge($tags, $children);
	}

	private function getTagsChildIds($tags = [])
	{
		$children = [];

		$query = $this->db->getQuery(true)
			->select($this->db->quoteName('id'))
			->from('#__tags')
			->where($this->db->quoteName('published') . ' = 1');

		while ( ! empty($tags))
		{
			$query->clear('where')
				->where($this->db->quoteName('parent_id') . RL_DB::in($tags));
			$this->db->setQuery($query);
			$tags = $this->db->loadColumn();

			$children = array_merge($children, $tags);
		}

		return $children;
	}

	private function getUsers()
	{
		if ( ! $this->params->get('filter_users'))
		{
			return [];
		}

		return $this->params->get('users', []);
	}
}
