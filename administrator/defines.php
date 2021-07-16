<?php

use Joomla\CMS\Factory;

define('_JDEFINES', true);
define('JPATH_BASE', getcwd());

require_once JPATH_BASE . '/includes/defines.php';
require_once JPATH_BASE . '/includes/framework.php';

Closure::bind(
    function () {
        unset(JLoader::$classAliasesInverse['Joomla\CMS\Factory']);
    },
    null,
    JLoader::class
)();

class JFactory extends Factory
{
    public static function getApplication($id = null, array $config = [], $prefix = 'J')
    {
        if (!self::$application) {
            $app = parent::getApplication($id, $config, $prefix);

            // remove realpath to allow symlinks in com_media
            $loader = function ($class) {
                $classes = [
                    'MediaModelList' => [
                        '/components/com_media/models/list.php',
                        'realpath($basePath)',
                        '$basePath',
                    ],

                    'MediaControllerFile' => [
                        '/components/com_media/controllers/file.php',
                        'realpath($fileparts[\'dirname\'])',
                        '$fileparts[\'dirname\']',
                    ],

                    'MediaControllerFolder' => [
                        '/components/com_media/controllers/folder.php',
                        'realpath($fullPath)',
                        '$fullPath',
                    ],
                ];

                $evaluate = function ($file, $search, $replace) {
                    $code = file_get_contents(JPATH_BASE . $file);
                    $code = str_replace('<?php', '', $code);
                    $code = str_replace($search, $replace, $code);
                    eval($code);
                };

                if (isset($classes[$class])) {
                    $evaluate(...$classes[$class]);
                }
            };

            spl_autoload_register($loader, true, true);
        }

        return self::$application;
    }
}
