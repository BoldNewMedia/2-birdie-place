<?php

namespace YOOtheme\Builder\Joomla\Source\Type;

use function YOOtheme\trans;

class ImagesType
{
    /**
     * @return array
     */
    public static function config()
    {
        return [

            'fields' => [

                'image_intro' => [
                    'type' => 'String',
                    'metadata' => [
                        'label' => trans('Intro Image'),
                    ],
                ],

                'image_intro_alt' => [
                    'type' => 'String',
                    'metadata' => [
                        'label' => trans('Intro Image Alt'),
                        'filters' => ['limit'],
                    ],
                ],

                'image_intro_caption' => [
                    'type' => 'String',
                    'metadata' => [
                        'label' => trans('Intro Image Caption'),
                        'filters' => ['limit'],
                    ],
                ],

                'image_fulltext' => [
                    'type' => 'String',
                    'metadata' => [
                        'label' => trans('Full Article Image'),
                    ],
                ],

                'image_fulltext_alt' => [
                    'type' => 'String',
                    'metadata' => [
                        'label' => trans('Full Article Image Alt'),
                        'filters' => ['limit'],
                    ],
                ],

                'image_fulltext_caption' => [
                    'type' => 'String',
                    'metadata' => [
                        'label' => trans('Full Article Image Caption'),
                        'filters' => ['limit'],
                    ],
                ],

            ],

        ];
    }
}
