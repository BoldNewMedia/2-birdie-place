<?php

defined('_JEXEC') or die;

use Joomla\CMS\Form\FormField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;

class JFormFieldLocation extends FormField
{
    public $type = 'location';

    public function getInput()
    {
        HTMLHelper::_('script', Uri::root() . 'plugins/fields/location/app/location.min.js');

        $data = parent::getLayoutData();

        return "<div data-name=\"{$data['name']}\" data-location=\"{$data['value']}\"></div>";
    }
}
