<?php

namespace YOOtheme\Theme\Joomla;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Version;
use YOOtheme\Config;
use YOOtheme\Path;
use YOOtheme\Theme\SystemCheck as SysCheck;
use YOOtheme\Theme\Updater;
use YOOtheme\View;

return [
    'theme' => function (Config $config) {
        $config->set('theme.styles.vars.@internal-joomla-version', (string) Version::MAJOR_VERSION);

        return $config->loadFile(Path::get('./config/theme.json'));
    },

    'routes' => [
        ['get', '/customizer', [CustomizerController::class, 'index'], ['customizer' => true]],
        ['post', '/customizer', [CustomizerController::class, 'save']],
    ],

    'events' => [
        'app.request' => [
            SystemListener::class => 'checkPermission',
        ],

        'url.resolve' => [
            UrlListener::class => 'routeQueryParams',
        ],

        'theme.init' => [
            ThemeListener::class => ['initTheme', 20],
            ChildThemeListener::class => ['initTheme', -10],
            CustomizerListener::class => ['initTheme', -20],
        ],

        'theme.head' => [
            ThemeListener::class => ['initHead'],
        ],

        'customizer.init' => [
            ChildThemeListener::class => ['initCustomizer', 20],
            CustomizerListener::class => ['initCustomizer', 10],
        ],

        'config.save' => [
            CustomizerListener::class => 'saveConfig',
        ],

        'styler.imports' => [
            StylerListener::class => 'stylerImports',
        ],

        'view.init' => [
            ThemeListener::class => ['beforeDisplay', -10],
            ChildThemeListener::class => 'beforeDisplay',
        ],
    ],

    'actions' => [
        'onAfterRoute' => [
            ThemeLoader::class => ['initTheme', 50],
        ],

        'onBeforeDisplay' => [
            ThemeListener::class => ['beforeDisplay', -10],
            ChildThemeListener::class => 'beforeDisplay',
        ],

        'onLoadTemplate' => [
            ThemeListener::class => 'loadTemplate',
            CacheListener::class => ['loadTemplate', -20],
        ],

        'onAfterDispatch' => [
            ThemeListener::class => 'afterDispatch',
            ChildThemeListener::class => 'afterDispatch',
            CacheListener::class => 'afterDispatch',
        ],

        'onContentPrepareData' => [
            CustomizerListener::class => 'prepareData',
        ],

        'onBeforeCompileHead' => [
            CustomizerListener::class => 'compileHead',
        ],

        'onAfterCleanModuleList' => [
            ChildThemeListener::class => ['loadModules', -5],
        ],
    ],

    'extend' => [
        View::class => function (View $view) {
            $view->addLoader([UrlListener::class, 'resolveRelativeUrl']);
            $view->addLoader([CacheListener::class, 'loadPosition'], '~theme/templates/position');

            $view->addFunction('trans', [Text::class, '_']);
            $view->addFunction('formatBytes', function ($bytes, $precision = 0) {
                return HTMLHelper::_('number.bytes', $bytes, 'auto', $precision);
            });
            $view->addFunction('cleanImageUrl', function ($url) {
                return version_compare(JVERSION, '4.0', '>')
                    ? HTMLHelper::cleanImageURL($url)->url
                    : $url;
            });
        },

        Updater::class => function (Updater $updater) {
            $updater->add(Path::get('./updates.php'));
            return $updater;
        },
    ],

    'services' => [
        ThemeLoader::class => '',
        SysCheck::class => SystemCheck::class,
    ],

    'loaders' => [
        'theme' => [ThemeLoader::class, 'load'],
    ],
];
