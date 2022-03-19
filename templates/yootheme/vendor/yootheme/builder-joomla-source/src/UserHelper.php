<?php

namespace YOOtheme\Builder\Joomla\Source;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Router\Route;
use Joomla\Component\Contact\Site\Helper\RouteHelper;
use function YOOtheme\app;
use YOOtheme\Database;

class UserHelper
{
    /**
     * Gets the user's contact.
     *
     * @param int $id
     *
     * @return object
     */
    public static function getContact($id)
    {
        static $contacts = [];

        if (!isset($contacts[$id])) {
            /**
             * @var Database $db
             */
            $db = app(Database::class);

            $query = 'SELECT id AS contactid, alias, catid
                FROM #__contact_details
                WHERE published = 1 AND user_id = :id';

            $params = ['id' => $id];

            if (Multilanguage::isEnabled() === true) {
                $query .= ' AND (language in (:lang) OR language IS NULL)';
                $params += ['lang' => [Factory::getLanguage()->getTag(), '*']];
            }

            $query .= ' ORDER BY id DESC LIMIT 1';

            $contacts[$id] = $db->fetchObject($query, $params);
        }

        return $contacts[$id];
    }

    /**
     * Gets the user's contact link.
     *
     * @param int $id
     *
     * @return string|void
     */
    public static function getContactLink($id)
    {
        $contact = self::getContact($id);

        if (empty($contact->contactid)) {
            return;
        }

        return Route::_(
            RouteHelper::getContactRoute("{$contact->contactid}:{$contact->alias}", $contact->catid)
        );
    }

    /**
     * Query users.
     *
     * @param array $args
     *
     * @return array
     */
    public static function query(array $args = [])
    {
        BaseDatabaseModel::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_users/models/');
        $model = BaseDatabaseModel::getInstance('users', 'UsersModel', ['ignore_request' => true]);
        $model->setState('params', ComponentHelper::getParams('com_users'));
        $model->setState('filter.active', true);
        $model->setState('filter.state', 0);

        $props = [
            'offset' => 'list.start',
            'limit' => 'list.limit',
            'order' => 'list.ordering',
            'order_direction' => 'list.direction',
            'groups' => 'filter.groups',
        ];

        if (empty($args['groups'])) {
            unset($args['groups']);
        }

        foreach (array_intersect_key($props, $args) as $key => $prop) {
            $model->setState($prop, $args[$key]);
        }

        return $model->getItems();
    }
}
