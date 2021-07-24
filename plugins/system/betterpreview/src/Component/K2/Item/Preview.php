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

namespace RegularLabs\Plugin\System\BetterPreview\Component\K2\Item;

defined('_JEXEC') or die;

use Joomla\CMS\Factory as JFactory;
use RegularLabs\Plugin\System\BetterPreview\Component\Preview as Main_Preview;

class Preview extends Main_Preview
{
	public function renderPreview(&$article, $context)
	{
		if ($context != 'com_k2.item' || ! isset($article->id) || $article->id != JFactory::getApplication()->input->get('id'))
		{
			return;
		}

		parent::render($article, $context);
	}

	public function states()
	{
		parent::initStates(
			'k2_items',
			[
				'publish_up'   => 'publish_up',
				'publish_down' => 'publish_down',
				'parent'       => 'catid',
			],
			'k2_categories',
			[]
		);
	}

	public function getShowIntro(&$article)
	{
		if ( ! isset($article->params))
		{
			return 1;
		}

		if ( ! is_object($article->params))
		{
			$params = (object) json_decode($article->params);

			return $params->itemIntroText;
		}

		return $article->params->get('itemIntroText', '1');
	}
}
