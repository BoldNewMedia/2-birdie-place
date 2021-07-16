<?php

namespace YOOtheme;

return [

    '2.4.0-beta.5' => function ($node, array $params) {

        if (isset($node->source->props)) {

            // refactor show_category argument into show_taxonomy argument
            foreach ((array) $node->source->props as $prop) {
                if (isset($prop->name) && $prop->name === 'metaString' && isset($prop->arguments->show_category)) {
                    $prop->arguments->show_taxonomy = $prop->arguments->show_category ? 'category' : '';
                    unset($prop->arguments->show_category);
                }
            }

        }

    },

];
