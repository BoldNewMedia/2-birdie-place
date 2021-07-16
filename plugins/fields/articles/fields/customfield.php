<?php
/**
 * @package         Articles Field
 * @version         3.5.3
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            http://www.regularlabs.com
 * @copyright       Copyright © 2021 Regular Labs All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper as JHtml;
use Joomla\CMS\Language\Text as JText;
use RegularLabs\Library\ArrayHelper as RL_Array;
use RegularLabs\Library\Form;

if (is_file(JPATH_LIBRARIES . '/regularlabs/autoload.php'))
{
	require_once JPATH_LIBRARIES . '/regularlabs/autoload.php';
}

class JFormFieldCustomField extends \RegularLabs\Library\Field
{
	public $type = 'CustomField';

	protected function getInput()
	{
		$this->params = $this->element->attributes();

		if ( ! is_array($this->value))
		{
			$this->value = explode(',', $this->value);
		}

		$options = $this->getOptions();

		return Form::selectListSimple(
			$options,
			$this->name,
			$this->value,
			$this->id,
			0,
			false,
			! FieldsHelper::canEditFieldValue($this)
		);
	}

	function getOptions()
	{
		$excludes = RL_Array::toArray($this->get('exclude'));

		$options = parent::getOptions();

		$fields = FieldsHelper::getFields('com_content.article');

		foreach ($fields as $field)
		{
			if (in_array($field->type, $excludes))
			{
				continue;
			}

			$options[] = JHtml::_('select.option', $field->id, $field->title);
		}

		if ( ! empty($options))
		{
			array_unshift($options, JHtml::_('select.option', '-', '&nbsp;', 'value', 'text', true));
			array_unshift($options, JHtml::_('select.option', '', '- ' . JText::_('JSELECT') . ' -', 'value', 'text', false));
		}

		return $options;
	}
}
