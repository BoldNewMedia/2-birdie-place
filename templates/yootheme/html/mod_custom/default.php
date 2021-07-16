<?php

defined('_JEXEC') or die;

$image = $params->get('backgroundimage');

?>

<?php if ($module->content) : ?>
<div class="uk-margin-remove-last-child custom" <?= $image ? " style=\"background-image:url({$image})\"" : '' ?>><?= $module->content ?></div>
<?php endif ?>