<?php

namespace YOOtheme\Theme\Joomla;

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\User\User;
use Joomla\Input\Input;
use YOOtheme\Config;
use YOOtheme\Http\Request;

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
                'canCreate' =>
                    $user->authorise('core.manage', 'com_media') ||
                    $user->authorise('core.create', 'com_media'),
                'canDelete' =>
                    $user->authorise('core.manage', 'com_media') ||
                    $user->authorise('core.delete', 'com_media'),
            ],
        ]);
    }

    public static function beforeRespond(CMSApplication $cms, Input $input, Request $request)
    {
        if (version_compare(JVERSION, '4.0', '>')) {
            return;
        }

        if (
            $input->getCmd('option') !== 'com_media' ||
            !$request->isMethod('post') ||
            !str_contains($request->getHeaderLine('Accept'), 'application/json') ||
            headers_sent()
        ) {
            return;
        }

        $cms->clearHeaders();
        $cms->setHeader('Status', 200);
        $cms->mimeType = 'application/json';
        $cms->setBody(json_encode($cms->getMessageQueue(true)));
        $cms->set('gzip', false);

        $cms->getSession()->set('application.queue', []);
    }
}
