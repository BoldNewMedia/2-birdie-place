<?php

namespace YOOtheme\Builder\Joomla\Source;

use Joomla\CMS\Factory;
use Joomla\Component\Tags\Site\Model\TagsModel as BaseModel;

class TagsModel extends BaseModel
{
    protected function getListQuery()
    {
        $query = parent::getListQuery();
        $this->setState('list.start', $this->state->params->get('list.start'));

        if ($this->state->params->get('all_tags_orderby', 'title') == 'rand') {
            $query->clear('order');
            $query->order(
                Factory::getDbo()
                    ->getQuery(true)
                    ->Rand()
            );
        }

        return $query;
    }
}
