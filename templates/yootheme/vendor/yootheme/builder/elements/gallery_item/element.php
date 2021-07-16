<?php

namespace YOOtheme;

return [

    'transforms' => [

        'render' => function ($node) {

            // Don't render element if content fields are empty
            return $node->props['image']
                || $node->props['hover_image'];

        },

    ],

    'updates' => [

        '2.5.0-beta.1.3' => function ($node) {
            if (!empty($node->props['tags'])) {
                $node->props['tags'] = ucwords($node->props['tags']);
            }
        },

        '1.18.0' => function ($node) {

            if (!isset($node->props['hover_image'])) {
                $node->props['hover_image'] = Arr::get($node->props, 'image2');
            }

        },

    ],

];
