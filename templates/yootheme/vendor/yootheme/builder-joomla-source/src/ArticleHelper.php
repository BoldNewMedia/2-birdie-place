<?php

namespace YOOtheme\Builder\Joomla\Source;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Object\CMSObject;
use YOOtheme\Str;

class ArticleHelper
{
    /**
     * Gets the articles.
     *
     * @param int[] $ids
     * @param array $args
     *
     * @return CMSObject[]
     */
    public static function get($ids, array $args = [])
    {
        return $ids ? static::query(['article' => (array) $ids] + $args) : [];
    }

    /**
     * Query articles.
     *
     * @param array $args
     *
     * @return array
     */
    public static function query(array $args = [])
    {
        if (!class_exists('ContentModelArticles')) {
            require_once JPATH_ROOT . '/components/com_content/models/articles.php';
        }

        $model = new ContentModelArticles(['ignore_request' => true]);
        $model->setState('params', ComponentHelper::getParams('com_content'));
        $model->setState('filter.access', true);
        $model->setState('filter.published', 1);
        $model->setState('filter.language', Multilanguage::isEnabled());
        $model->setState('filter.subcategories', false);

        $args += [
            'article_operator' => 'IN',
            'cat_operator' => 'IN',
            'tag_operator' => 'IN',
            'users_operator' => 'IN',
        ];

        if (!empty($args['order'])) {

            if ($args['order'] === 'rand') {
                $args['order'] = Factory::getDbo()->getQuery(true)->Rand();
            } elseif ($args['order'] === 'front') {
                $args['order'] = 'fp.ordering';
            } else {
                $args['order'] = "a.{$args['order']}";
            }
        }

        if (!empty($args['featured'])) {
            $args['featured'] = 'only';
        }

        $props = [
            'offset' => 'list.start',
            'limit' => 'list.limit',
            'order' => 'list.ordering',
            'order_direction' => 'list.direction',
            'order_alphanum' => 'list.alphanum',
            'featured' => 'filter.featured',
            'subcategories' => 'filter.subcategories',
            'tags' => 'filter.tags',
            'tag_operator' => 'filter.tag_operator',
        ];

        foreach (array_intersect_key($props, $args) as $key => $prop) {
            $model->setState($prop, $args[$key]);
        }

        if (!empty($args['article'])) {
            $model->setState('filter.article_id', (array) $args['article']);
            $model->setState('filter.article_id.include', $args['article_operator'] === 'IN');
        }

        if (!empty($args['catid'])) {
            $model->setState('filter.category_id', (array) $args['catid']);
            $model->setState('filter.category_id.include', $args['cat_operator'] === 'IN');
        }

        if (!empty($args['users'])) {
            $model->setState('filter.author_id', (array) $args['users']);
            $model->setState('filter.author_id.include', $args['users_operator'] === 'IN');
        }

        return $model->getItems();
    }
}

if (!class_exists('ContentModelArticles')) {
    require_once JPATH_ROOT . '/components/com_content/models/articles.php';
}

class ContentModelArticles extends \ContentModelArticles
{
    protected function getListQuery()
    {
        $fieldId = false;
        $ordering = $this->getState('list.ordering');

        if (Str::startsWith($ordering, 'a.field:')) {
            $fieldId = (int) substr($ordering, 8);
            $this->setState('list.ordering', 'fields.value');
        }

        $tags = (array) $this->getState('filter.tags', []);
        $tagOperator = $this->getState('filter.tag_operator', 'IN');

        if ($tags && $tagOperator === 'IN') {
            $this->setState('filter.tag', (array) $tags);
        }

        $query = parent::getListQuery();

        if ($fieldId) {
            $query->leftJoin("#__fields_values AS fields ON a.id = fields.item_id AND fields.field_id = {$fieldId}");
        }

        if ($tags) {
            $tagCount = count($tags);
            $tags = implode(',', $tags);

            if ($tagOperator === 'NOT IN') {
                $query->where("a.id NOT IN (SELECT content_item_id FROM #__contentitem_tag_map WHERE tag_id IN ({$tags}))");
            }

            if ($tagOperator === 'AND') {
                $query->where("(SELECT COUNT(1) FROM #__contentitem_tag_map WHERE tag_id IN ({$tags}) AND content_item_id = a.id) = $tagCount");
            }
        }

        if ($this->getState('list.alphanum') && $ordering != 'RAND()') {
            $ordering = $this->getState('list.ordering', 'a.ordering');
            $order = $this->getState('list.direction', 'ASC');
            $query->clear('order');
            $query->order("(substr({$ordering}, 1, 1) > '9') {$order}, {$ordering}+0 {$order}, {$ordering} {$order}");
        }

        return $query;
    }
}
