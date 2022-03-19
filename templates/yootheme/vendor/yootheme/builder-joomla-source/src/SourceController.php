<?php

namespace YOOtheme\Builder\Joomla\Source;

use Joomla\CMS\Factory;
use YOOtheme\Database;
use YOOtheme\Http\Request;
use YOOtheme\Http\Response;

class SourceController
{
    /**
     * @param Request  $request
     * @param Response $response
     * @param Database $db
     *
     * @throws \Exception
     *
     * @return Response
     */
    public static function articles(Request $request, Response $response, Database $db)
    {
        $titles = [];
        $ids = implode(',', array_map('intval', (array) $request('ids')));

        if (!empty($ids)) {
            $query = "SELECT id, title
                FROM #__content
                WHERE id IN ({$ids})";

            foreach ($db->fetchAll($query) as $article) {
                $titles[$article['id']] = $article['title'];
            }
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
