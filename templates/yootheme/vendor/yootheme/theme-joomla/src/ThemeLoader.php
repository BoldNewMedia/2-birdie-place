<?php

namespace YOOtheme\Theme\Joomla;

use Joomla\CMS\Factory;
use Joomla\Registry\Registry;
use YOOtheme\Application;
use YOOtheme\Arr;
use YOOtheme\Config;
use YOOtheme\Container;
use YOOtheme\Event;
use YOOtheme\Path;
use YOOtheme\Theme\Updater;

class ThemeLoader
{
    protected static $configs = [];

    /**
     * Load theme configurations.
     *
     * @param Container $container
     * @param array     $configs
     */
    public static function load(Container $container, array $configs)
    {
        static::$configs = array_merge(static::$configs, $configs);
    }

    /**
     * Initialize current theme.
     *
     * @param Application $app
     * @param Config      $configuration
     */
    public static function initTheme(Application $app, Config $configuration)
    {
        $template = static::getTemplate();

        // is template active?
        if (!$template || !$template->params['yootheme']) {
            return;
        }

        // get config params
        $params = $template->params->get('config', '{}');
        $params = json_decode($params, true) ?: [];

        // load childtheme config
        if (!empty($params['child_theme'])) {
            $app->load(
                Path::get("~/templates/{$template->template}_{$params['child_theme']}/config.php")
            );
        }

        // add configurations
        $configuration->add('theme', [
            'id' => $template->id,
            'default' => !empty($template->home),
            'template' => $template->template,
        ]);

        foreach (static::$configs as $config) {
            if ($config instanceof \Closure) {
                $config = $config($configuration, $app);
            }

            $configuration->add('theme', (array) $config);
        }

        if (empty($params)) {
            $params['version'] = $configuration('theme.version');
        }

        /**
         * @var Updater $updater
         */
        $updater = $app(Updater::class);

        // merge defaults with configuration
        $configuration->set(
            '~theme',
            Arr::merge($configuration('theme.defaults', []), $updater->update($params, []))
        );

        Event::emit('theme.init');
    }

    /**
     * Gets the current template.
     *
     * @return object|null
     */
    public static function getTemplate()
    {
        $app = Factory::getApplication();
        $template = $app->getTemplate(true);

        // get site template
        if ($app->isClient('administrator')) {
            $view = $app->input->getCmd('view') === 'style';
            $option = $app->input->getCmd('option') === 'com_templates';
            $style = $app->input->getInt($view && $option ? 'id' : 'templateStyle');
            $query =
                'SELECT * FROM #__template_styles WHERE ' .
                ($style ? "id = {$style}" : "client_id = 0 AND home = '1'");

            if (
                $template = Factory::getDbo()
                    ->setQuery($query)
                    ->loadObject()
            ) {
                $template->params = new Registry($template->params);
            }
        }

        return $template;
    }
}
