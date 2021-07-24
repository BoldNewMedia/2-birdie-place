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

defined('_JEXEC') or die;

require_once __DIR__ . '/script.install.helper.php';

class PlgEditorsXtdBetterPreviewInstallerScript extends PlgEditorsXtdBetterPreviewInstallerScriptHelper
{
	public $alias          = 'betterpreview';
	public $extension_type = 'plugin';
	public $name           = 'BETTERPREVIEW';
	public $plugin_folder  = 'editors-xtd';

	public function uninstall($adapter)
	{
		$this->uninstallPlugin($this->extname, 'system');
	}
}
