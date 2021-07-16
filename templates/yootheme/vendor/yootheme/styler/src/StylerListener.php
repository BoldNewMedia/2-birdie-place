<?php

namespace YOOtheme\Theme;

use YOOtheme\Config;
use YOOtheme\Event;
use YOOtheme\File;
use YOOtheme\Metadata;
use YOOtheme\Path;
use YOOtheme\Url;

class StylerListener
{
    public static function initCustomizer(Config $config, Metadata $metadata, Styler $styler)
    {
        // check version in css file, if it needs to be updated
        $style = File::get("~theme/css/theme.{$config('theme.id')}.css");
        $header = $style ? file_get_contents($style, false, null, 0, 30) : '';
        $version = preg_match('/\sv([\w\d\.\-]+)\s/', $header, $match) ? $match[1] : '1.0.0';

        $styles = array_map(function ($theme) {
            unset($theme['file']);
            return $theme;
        }, $styler->getThemes());

        $config->add('customizer.sections.styler', [
            'route' => 'theme/styles',
            'worker' => Url::to(Path::get('../app/worker.min.js'), ['ver' => $config('theme.version')]),
            'update' => $version !== $config('theme.version'),
            'styles' => $styles,
        ]);

        $data = json_encode(Event::emit('styler.data|filter', []));

        $metadata->set('script:styler-data', "var \$styler = {$data};");
    }
}
