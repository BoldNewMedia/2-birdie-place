<?php

namespace YOOtheme\Builder\Joomla\Source\Type;

use Joomla\CMS\Factory;
use function YOOtheme\trans;

class SiteQueryType
{
    /**
     * @return array
     */
    public static function config()
    {
        return [
            'fields' => [
                'site' => [
                    'type' => 'Site',
                    'metadata' => [
                        'label' => trans('Site'),
                    ],
                    'extensions' => [
                        'call' => __CLASS__ . '::resolve',
                    ],
                ],
            ],
        ];
    }

    public static function resolve()
    {
        $config = Factory::getConfig();
        $document = Factory::getDocument();
        $user = Factory::getUser();

        return [
            'title' => $config->get('sitename'),
            'page_title' => $document->getTitle(),
            'user' => $user,
            'is_guest' => $user->guest,
        ];
    }
}
