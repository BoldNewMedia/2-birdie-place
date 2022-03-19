<?php

defined('_JEXEC') or die();

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

        return "<yootheme-field-location><input type=\"hidden\" name=\"{$data['name']}\" value=\"{$data['value']}\"></yootheme-field-location>";
    }
}
