<?php

namespace YOOtheme\Theme\Joomla;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\User\User;
use YOOtheme\Config;

class FinderListener
{
    public static function initCustomizer(Config $config, User $user)
    {
        $params = ComponentHelper::getParams('com_media');

        // allow all allowable file extensions and MIME types in input field
        $accept = [];
        foreach (explode(',', $params->get('upload_extensions', '')) as $extension) {
            $accept[] = '.' . trim($extension);
        }
        foreach (explode(',', $params->get('upload_mime', '')) as $mime) {
            $accept[] = trim($mime);
        }

        $config->add('customizer', [

            'media' => [
                'accept' => implode(',', $accept),
                'legacy' => version_compare(JVERSION, '4.0', '<'),
                'canCreate' => $user->authorise('core.manage', 'com_media') || $user->authorise('core.create', 'com_media'),
                'canDelete' => $user->authorise('core.manage', 'com_media') || $user->authorise('core.delete', 'com_media'),
            ],

        ]);
    }
}
