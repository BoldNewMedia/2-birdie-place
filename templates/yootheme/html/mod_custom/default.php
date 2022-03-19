<?php

use Joomla\CMS\HTML\HTMLHelper;

defined('_JEXEC') or die;

$image = HTMLHelper::_('image', $params->get('backgroundimage'), null, [], false, 1);

?>

<?php if ($module->content) : ?>
<div class="uk-margin-remove-last-child custom" <?= $image ? " style=\"background-image:url({$image})\"" : '' ?>><?= $module->content ?></div>
<?php endif ?>
