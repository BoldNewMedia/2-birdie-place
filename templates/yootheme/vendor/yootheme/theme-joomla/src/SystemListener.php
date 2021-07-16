<?php

namespace YOOtheme\Theme\Joomla;

use Joomla\CMS\Router\Route;
use Joomla\CMS\User\User;
use YOOtheme\Http\Response;
use YOOtheme\Str;

class SystemListener
{
    public static function checkPermission(User $user, Response $response, $request, callable $next)
    {
        // check user permissions
        if (!$request->getAttribute('allowed')
            && !(
                $user->authorise('core.edit', 'com_content')
                || $user->authorise('core.edit.own', 'com_content')
                || $user->authorise('core.edit', 'com_templates')
            )
        ) {
            // redirect guest user to user login
            if ($user->guest && Str::contains($request->getHeaderLine('Accept'), 'text/html')) {
                return $response->withRedirect(Route::_('index.php?option=com_users&view=login', false));
            }

            $request->abort(403, 'Insufficient User Rights.');
        }

        return $next($request);
    }
}
