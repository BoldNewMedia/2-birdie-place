<?php

namespace YOOtheme\Theme\Joomla;

use YOOtheme\View;

return [

    'actions' => [

        'onContentBeforeSave' => [
            ArticlesListener::class => 'beforeSave',
        ],

        'onContentPrepareData' => [
            ArticlesListener::class => 'prepareData',
        ],

    ],

    'extend' => [

        View::class => function (View $view) {
            $view->addLoader([ViewLoader::class, 'loadArticle'], '~theme/templates/article*');
        },

    ],

];
