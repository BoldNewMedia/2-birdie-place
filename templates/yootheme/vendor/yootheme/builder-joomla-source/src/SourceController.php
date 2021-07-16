<?php

namespace YOOtheme\Builder\Joomla\Source;

use Joomla\CMS\Factory;
use YOOtheme\Http\Request;
use YOOtheme\Http\Response;

class SourceController
{
    /**
     * @param Request  $request
     * @param Response $response
     *
     * @throws \Exception
     *
     * @return Response
     */
    public static function articles(Request $request, Response $response)
    {
        $titles = [];

        foreach (ArticleHelper::get($request('ids')) as $article) {
            $titles[$article->id] = $article->title;
        }

        return $response->withJson((object) $titles);
    }

    /**
     * @param Request  $request
     * @param Response $response
     *
     * @throws \Exception
     *
     * @return Response
     */
    public static function users(Request $request, Response $response)
    {
        $titles = [];

        foreach ($request('ids') as $id) {
            if ($user = Factory::getUser($id)) {
                $titles[$id] = $user->name;
            }
        }

        return $response->withJson((object) $titles);
    }
}
