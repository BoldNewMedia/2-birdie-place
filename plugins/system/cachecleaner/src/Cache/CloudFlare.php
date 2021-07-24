<?php
/**
 * @package         Cache Cleaner
 * @version         7.5.0PRO
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            http://regularlabs.com
 * @copyright       Copyright © 2021 Regular Labs All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

namespace RegularLabs\Plugin\System\CacheCleaner\Cache;

defined('_JEXEC') or die;


use CloudFlare as ApiCloudFlare;
use Joomla\CMS\Factory as JFactory;
use Joomla\CMS\Language\Text as JText;
use Joomla\CMS\Uri\Uri as JUri;
use RegularLabs\Plugin\System\CacheCleaner\Params;

class CloudFlare extends Cache
{
	public static function getAPI($username, $key, $token)
	{
		require_once __DIR__ . '/../Api/CloudFlare.php';

		return new ApiCloudFlare(trim($username), trim($key), trim($token));
	}

	public static function purge()
	{
		$input  = JFactory::getApplication()->input;
		$params = Params::get();

		$method  = $input->get('m', $params->clean_cloudflare_authorization_method);
		$domains = $input->getString('d', $params->cloudflare_domains);

		$username = '';
		$key      = '';
		$token    = '';

		switch ($method)
		{
			case 'username':
				$username = $input->getString('u', $params->cloudflare_username);
				$key      = $input->getString('k', $params->cloudflare_token);

				if ( ! $username)
				{
					self::addError(JText::sprintf('CC_ERROR_CDN_NO_USERNAME', JText::_('CC_CLOUDFLARE')));

					return -1;
				}
				if ( ! $key)
				{
					self::addError(JText::sprintf('CC_ERROR_CDN_NO_API_KEY', JText::_('CC_CLOUDFLARE')));

					return -1;
				}
				break;

			case 'token':
			default:
				$token = $input->getString('t', $params->cloudflare_api_token);

				if ( ! $token)
				{
					self::addError(JText::sprintf('CC_ERROR_CDN_NO_API_TOKEN', JText::_('CC_CLOUDFLARE')));

					return -1;
				}
				break;
		}

		$api = self::getAPI($username, $key, $token);

		if ( ! $api || is_string($api))
		{
			self::addError(JText::sprintf('CC_ERROR_CDN_COULD_NOT_INITIATE_API', JText::_('CC_CLOUDFLARE')));
			if (is_string($api))
			{
				self::addError($api);
			}

			return false;
		}

		$domains = explode(',', $domains);

		if (empty($domains) || empty($domains[0]))
		{
			$domains = [JUri::getInstance()->toString(['host'])];
		}

		$api_call = null;

		foreach ($domains as $domain)
		{
			$api_call = json_decode($api->purge($domain));

			if ( ! is_null($api_call) && ! empty($api_call->success))
			{
				continue;
			}

			self::addError(JText::sprintf('CC_ERROR_CDN_COULD_NOT_PURGE_ZONE', JText::_('CC_CLOUDFLARE'), $domain));

			if ( ! empty($api_call->messages))
			{
				foreach ($api_call->messages as $message)
				{
					self::addError(JText::_('CC_CLOUDFLARE') . ' Message: ' . $message);
				}
			}

			if ( ! empty($api_call->errors))
			{
				foreach ($api_call->errors as $error)
				{
					self::addError(JText::_('CC_CLOUDFLARE') . ' Error: ' . $error->code . ' ' . $error->message);
				}
			}

			return false;
		}

		if ( ! empty($api_call->messages))
		{
			self::setMessage(implode(', ', $api_call->messages));
		}

		return true;
	}
}
