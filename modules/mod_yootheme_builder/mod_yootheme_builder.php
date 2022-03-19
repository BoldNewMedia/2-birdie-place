<?php

use Joomla\CMS\Helper\ModuleHelper;

defined('_JEXEC') or die();

$message = '';

if (!$module->content) {
    $module->content = '{}';
} else {
    $message = '<div class="uk-text-danger">Builder only supported on "top" and "bottom"</div>';
}

require ModuleHelper::getLayoutPath('mod_yootheme_builder', $params->get('layout', 'default'));
