<?php
/**
 * @package         Regular Labs Library
 * @version         21.7.10061
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            http://regularlabs.com
 * @copyright       Copyright © 2021 Regular Labs All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

namespace RegularLabs\Library\Condition;

defined('_JEXEC') or die;

/**
 * Class EasyblogKeyword
 * @package RegularLabs\Library\Condition
 */
class EasyblogKeyword extends Easyblog
{
	public function pass()
	{
		parent::passContentKeyword();
	}
}
