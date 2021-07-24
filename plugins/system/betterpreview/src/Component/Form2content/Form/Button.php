<?php
/**
 * @package         Better Preview
 * @version         6.5.0PRO
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            http://regularlabs.com
 * @copyright       Copyright © 2021 Regular Labs All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

namespace RegularLabs\Plugin\System\BetterPreview\Component\Form2content\Form;

defined('_JEXEC') or die;

use RegularLabs\Plugin\System\BetterPreview\Component\Button as Main_Button;
use RegularLabs\Plugin\System\BetterPreview\Component\Menu;

class Button extends Main_Button
{
	public function getExtraJavaScript($text)
	{
		return '
				cat = document.getElementById("jform_catid");
				category_title = cat == undefined ? "" : cat.options[cat.selectedIndex].text.replace(/^(\s*-\s+)*/, "").trim();
				overrides = {
						category_title: category_title,
					};
			';
	}

	public function getURL($name)
	{
		if ( ! $item = Helper::getArticle())
		{
			return false;
		}

		Menu::setItemId($item);

		return $item->url;
	}
}
