<?php

namespace YOOtheme;

return [
    'updates' => [
        '2.7.0-beta.0.6' => function ($node) {
            if (empty($node->props['nav'])) {
                $node->props['nav'] = 'tab';
            }

            unset($node->props['slidenav']);
            unset($node->props['slidenav_hover']);
            unset($node->props['slidenav_large']);
            unset($node->props['slidenav_margin']);
            unset($node->props['slidenav_breakpoint']);
            unset($node->props['slidenav_color']);
            unset($node->props['slidenav_outside_breakpoint']);
            unset($node->props['slidenav_outside_color']);
            unset($node->props['slidenav_outside_color']);
            unset($node->props['slidenav_outside_color']);
            unset($node->props['slidenav_outside_color']);
        },

        '2.7.0-beta.0.3' => function ($node) {
            if (isset($node->props['switcher_thumbnail_height'])) {
                $node->props['thumbnav_height'] = $node->props['switcher_thumbnail_height'];
                unset($node->props['switcher_thumbnail_height']);
            }
        },

        '2.7.0-beta.0.2' => function ($node) {
            if (isset($node->props['switcher_style'])) {
                $node->props['nav'] = $node->props['switcher_style'];
                unset($node->props['switcher_style']);
            }

            if (isset($node->props['switcher_thumbnail_nowrap'])) {
                $node->props['thumbnav_nowrap'] = $node->props['switcher_thumbnail_nowrap'];
                unset($node->props['switcher_thumbnail_nowrap']);
            }

            if (isset($node->props['switcher_thumbnail_width'])) {
                $node->props['thumbnav_width'] = $node->props['switcher_thumbnail_width'];
                unset($node->props['switcher_thumbnail_width']);
            }

            if (isset($node->props['switcher_thumbnail_height'])) {
                $node->props['thumbnav_height'] = $node->props['switcher_thumbnail_height'];
                unset($node->props['switcher_thumbnail_height']);
            }

            if (isset($node->props['switcher_thumbnail_svg_inline'])) {
                $node->props['thumbnav_svg_inline'] = $node->props['switcher_thumbnail_svg_inline'];
                unset($node->props['switcher_thumbnail_svg_inline']);
            }

            if (isset($node->props['switcher_thumbnail_svg_color'])) {
                $node->props['thumbnav_svg_color'] = $node->props['switcher_thumbnail_svg_color'];
                unset($node->props['switcher_thumbnail_svg_color']);
            }

            if (isset($node->props['switcher_position'])) {
                $node->props['nav_position'] = $node->props['switcher_position'];
                unset($node->props['switcher_position']);
            }

            if (isset($node->props['nav_primary'])) {
                $node->props['nav_style_primary'] = $node->props['nav_primary'];
                unset($node->props['nav_primary']);
            }

            if (isset($node->props['switcher_align'])) {
                $node->props['nav_align'] = $node->props['switcher_align'];
                unset($node->props['switcher_align']);
            }

            if (isset($node->props['switcher_margin'])) {
                $node->props['nav_margin'] = $node->props['switcher_margin'];
                unset($node->props['switcher_margin']);
            }

            if (isset($node->props['switcher_grid_width'])) {
                $node->props['nav_grid_width'] = $node->props['switcher_grid_width'];
                unset($node->props['switcher_grid_width']);
            }

            if (isset($node->props['switcher_grid_column_gap'])) {
                $node->props['nav_grid_column_gap'] = $node->props['switcher_grid_column_gap'];
                unset($node->props['switcher_grid_column_gap']);
            }

            if (isset($node->props['switcher_grid_row_gap'])) {
                $node->props['nav_grid_row_gap'] = $node->props['switcher_grid_row_gap'];
                unset($node->props['switcher_grid_row_gap']);
            }

            if (isset($node->props['switcher_grid_breakpoint'])) {
                $node->props['nav_grid_breakpoint'] = $node->props['switcher_grid_breakpoint'];
                unset($node->props['switcher_grid_breakpoint']);
            }

            if (isset($node->props['switcher_grid_breakpoint'])) {
                $node->props['nav_grid_breakpoint'] = $node->props['switcher_grid_breakpoint'];
                unset($node->props['switcher_grid_breakpoint']);
            }

            if (isset($node->props['switcher_vertical_align'])) {
                $node->props['nav_vertical_align'] = $node->props['switcher_vertical_align'];
                unset($node->props['switcher_vertical_align']);
            }
        },

        '2.1.0-beta.0.1' => function ($node) {
            if (Arr::get($node->props, 'title_grid_width') === 'xxlarge') {
                $node->props['title_grid_width'] = '2xlarge';
            }

            if (Arr::get($node->props, 'image_grid_width') === 'xxlarge') {
                $node->props['image_grid_width'] = '2xlarge';
            }
        },

        '1.22.0-beta.0.1' => function ($node) {
            if (isset($node->props['switcher_gutter'])) {
                $node->props['switcher_grid_column_gap'] = $node->props['switcher_gutter'];
                $node->props['switcher_grid_row_gap'] = $node->props['switcher_gutter'];
                unset($node->props['switcher_gutter']);
            }

            if (isset($node->props['switcher_breakpoint'])) {
                $node->props['switcher_grid_breakpoint'] = $node->props['switcher_breakpoint'];
                unset($node->props['switcher_breakpoint']);
            }

            if (isset($node->props['title_gutter'])) {
                $node->props['title_grid_column_gap'] = $node->props['title_gutter'];
                $node->props['title_grid_row_gap'] = $node->props['title_gutter'];
                unset($node->props['title_gutter']);
            }

            if (isset($node->props['title_breakpoint'])) {
                $node->props['title_grid_breakpoint'] = $node->props['title_breakpoint'];
                unset($node->props['title_breakpoint']);
            }

            if (isset($node->props['image_gutter'])) {
                $node->props['image_grid_column_gap'] = $node->props['image_gutter'];
                $node->props['image_grid_row_gap'] = $node->props['image_gutter'];
                unset($node->props['image_gutter']);
            }

            if (isset($node->props['image_breakpoint'])) {
                $node->props['image_grid_breakpoint'] = $node->props['image_breakpoint'];
                unset($node->props['image_breakpoint']);
            }
        },

        '1.20.0-beta.1.1' => function ($node) {
            if (isset($node->props['maxwidth_align'])) {
                $node->props['block_align'] = $node->props['maxwidth_align'];
                unset($node->props['maxwidth_align']);
            }
        },

        '1.20.0-beta.0.1' => function ($node) {
            /**
             * @var Config $config
             */
            $config = app(Config::class);

            list($style) = explode(':', $config('~theme.style'));

            if (Arr::get($node->props, 'title_style') === 'heading-primary') {
                $node->props['title_style'] = 'heading-medium';
            }

            if (
                in_array($style, [
                    'craft',
                    'district',
                    'jack-backer',
                    'tomsen-brody',
                    'vision',
                    'florence',
                    'max',
                    'nioh-studio',
                    'sonic',
                    'summit',
                    'trek',
                ])
            ) {
                if (
                    Arr::get($node->props, 'title_style') === 'h1' ||
                    (empty($node->props['title_style']) &&
                        Arr::get($node->props, 'title_element') === 'h1')
                ) {
                    $node->props['title_style'] = 'heading-small';
                }
            }

            if (in_array($style, ['florence', 'max', 'nioh-studio', 'sonic', 'summit', 'trek'])) {
                if (Arr::get($node->props, 'title_style') === 'h2') {
                    $node->props['title_style'] =
                        Arr::get($node->props, 'title_element') === 'h1' ? '' : 'h1';
                } elseif (
                    empty($node->props['title_style']) &&
                    Arr::get($node->props, 'title_element') === 'h2'
                ) {
                    $node->props['title_style'] = 'h1';
                }
            }

            if (in_array($style, ['fuse', 'horizon', 'joline', 'juno', 'lilian', 'vibe', 'yard'])) {
                if (Arr::get($node->props, 'title_style') === 'heading-medium') {
                    $node->props['title_style'] = 'heading-small';
                }
            }

            if (in_array($style, ['copper-hill'])) {
                if (Arr::get($node->props, 'title_style') === 'heading-medium') {
                    $node->props['title_style'] =
                        Arr::get($node->props, 'title_element') === 'h1' ? '' : 'h1';
                } elseif (Arr::get($node->props, 'title_style') === 'h1') {
                    $node->props['title_style'] =
                        Arr::get($node->props, 'title_element') === 'h2' ? '' : 'h2';
                } elseif (
                    empty($node->props['title_style']) &&
                    Arr::get($node->props, 'title_element') === 'h1'
                ) {
                    $node->props['title_style'] = 'h2';
                }
            }

            if (in_array($style, ['trek', 'fjord'])) {
                if (Arr::get($node->props, 'title_style') === 'heading-medium') {
                    $node->props['title_style'] = 'heading-large';
                }
            }
        },

        '1.19.0-beta.0.1' => function ($node) {
            if (Arr::get($node->props, 'meta_align') === 'top') {
                $node->props['meta_align'] = 'above-title';
            }

            if (Arr::get($node->props, 'meta_align') === 'bottom') {
                $node->props['meta_align'] = 'below-title';
            }
        },

        '1.18.10.3' => function ($node) {
            if (Arr::get($node->props, 'meta_align') === 'top') {
                if (!empty($node->props['meta_margin'])) {
                    $node->props['title_margin'] = $node->props['meta_margin'];
                }
                $node->props['meta_margin'] = '';
            }
        },

        '1.18.10.1' => function ($node) {
            if (isset($node->props['image_inline_svg'])) {
                $node->props['image_svg_inline'] = $node->props['image_inline_svg'];
                unset($node->props['image_inline_svg']);
            }

            if (isset($node->props['image_animate_svg'])) {
                $node->props['image_svg_animate'] = $node->props['image_animate_svg'];
                unset($node->props['image_animate_svg']);
            }

            if (isset($node->props['switcher_thumbnail_inline_svg'])) {
                $node->props['switcher_thumbnail_svg_inline'] =
                    $node->props['switcher_thumbnail_inline_svg'];
                unset($node->props['switcher_thumbnail_inline_svg']);
            }
        },

        '1.18.0' => function ($node) {
            if (Arr::get($node->props, 'switcher_style') === 'thumbnail') {
                $node->props['switcher_style'] = 'thumbnav';
            }

            if (
                !isset($node->props['image_box_decoration']) &&
                Arr::get($node->props, 'image_box_shadow_bottom') === true
            ) {
                $node->props['image_box_decoration'] = 'shadow';
            }

            if (
                !isset($node->props['meta_color']) &&
                Arr::get($node->props, 'meta_style') === 'muted'
            ) {
                $node->props['meta_color'] = 'muted';
                $node->props['meta_style'] = '';
            }
        },
    ],
];
