<?php
/**
 * @package         Better Trash
 * @version         1.5.0PRO
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            http://regularlabs.com
 * @copyright       Copyright © 2021 Regular Labs All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

namespace RegularLabs\Plugin\System\BetterTrash;

defined('_JEXEC') or die;

use RegularLabs\Library\ParametersNew as RL_Parameters;

class Params
{
	protected static $params;

	public static function get()
	{
		if ( ! is_null(self::$params))
		{
			return self::$params;
		}

		self::$params = RL_Parameters::getPlugin('bettertrash');

		return self::$params;
	}
}
