<?php 
/**
 * @version       v3.0.5 Site Lock $
 * @package       Site Lock
 * @copyright     Copyright @ 2015 - All rights reserved.
 * @license       GNU/GPL       
 * @author        JoomlaShowroom.com
 * @author mail   info@JoomlaShowroom.com
 * @website       http://JoomlaShowroom.com
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );
if(!defined('DS')){
   define('DS',DIRECTORY_SEPARATOR);
}
$document = JFactory::getDocument();
if (file_exists(JPATH_ROOT."/media/jui/js/bootstrap.min.js")){
    JHtml::_('bootstrap.framework');
}
$document->addScript("components/com_sitelock/assets/js/bootstrap-colorpicker.min.js");
$document->addStyleSheet("components/com_sitelock/assets/css/bootstrap-colorpicker.css");
if($this->locked)
{
	JToolBarHelper::custom('unlock', 'cancel', 'cancel', JText::_('SITELOCK_UNLOCK_SITE'), false, false);
	JToolBarHelper::title(JText::_('SITELOCK_CURRENTLY_LOCKED'), 'sitelock_title');
}
else
{
	JToolBarHelper::custom('lock', 'apply', 'apply', JText::_('SITELOCK_LOCK_SITE'), false, false);
	JToolBarHelper::title(JText::_('SITELOCK_CURRENTLY_UNLOCKED'), 'siteunlock_title');
}
JToolBarHelper::custom('save', 'save', 'save', JText::_('SITELOCK_SAVE'), false, false);
JToolBarHelper::deleteList();

?>
<?php JHTML::_('behavior.tooltip'); ?>
<script>
function onBoxClicked(state)
{
    var dir = 0;
	var prev = parseInt(document.adminForm.boxchecked.value);
	if(state)
		dir = 1;
	else
		dir = -1;

    document.adminForm.boxchecked.value = prev + dir;
}
</script>
<form action="index.php?option=com_sitelock" method="post" name="adminForm" id="adminForm" class="form-horizontal" enctype="multipart/form-data" autocomplete="off">
<div class="container-fluid container-main">
 	<div class="row-fluid">
<div style="width:45%; float:left;">

<fieldset class="adminform">
<div class="alert alert-info"><strong><?php echo JText::_('SITELOCK_PLEASE_NOTE_HEADING'); ?></strong> <?php echo JText::_('SITELOCK_PLEASE_NOTE'); ?></div>
<legend><?php echo JText::_('SITELOCK_INSTRUCTIONS'); ?></legend>
<ul>
 <li><?php echo JText::_('SITELOCK_TO_ADD_USER'); ?></li>
 <li><?php echo JText::_('SITELOCK_TO_CHANGE_PASSWORD'); ?></li>
 <li><?php echo JText::_('SITELOCK_EMPTY_PASSWORD_FIELDS'); ?></li>
 <li><?php echo JText::_('SITELOCK_TO_DELETE_USER'); ?></li>
</ul>
</fieldset>

<fieldset class="adminform">
<legend><?php echo JText::_('SITELOCK_ALLOWED_USERS'); ?></legend>
<?php $i = 0; ?>
<table class="adminlist" cellpadding="1">
<thead>
	<tr>
		<th width="5%" class="title"></th>
		<th width="5%" class="title">#</th>
		<th width="30%" class="title"><?php echo JText::_('SITELOCK_USERNAME'); ?></th>
		<th width="30%" class="title"><?php echo JText::_('SITELOCK_PASSWORD'); ?></th>
	</tr>
</thead>
<tbody>
	<tr>
		<td colspan="2"><?php echo JText::_('SITELOCK_NEW_USER'); ?></td>
                <td><input type="text" name="newuser" id="newuser" autocomplete="off" value="" /></td>
                <td><input type="password" name="newpass" id="newpass" autocomplete="off" value=""/></td>
	</tr>
<?php foreach($this->users as $user) { ?>
	<tr class="row<?php echo $i % 2; ?>">
		<td><input type="checkbox" value="1" name="check<?php echo $user->user; ?>" onclick="onBoxClicked(this.checked);" /></td>
		<td><?php echo $i+1; ?></td>
		<td><?php echo $user->user; ?></td>
		<td><input type="password" value="" name="password<?php echo $user->user; ?>" /></td>

	</tr>
<?php $i++; } ?>
</tbody>
</table>
<input type="hidden" name="option" value="com_sitelock" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="id" value="" />
<input type="hidden" name="boxchecked" value="0" />
</fieldset>


<fieldset class="adminform">
<legend><?php echo JText::_('SITELOCK_OPTIONS'); ?></legend>
<div class="control-group">
  <label class="control-label" for="title"><?php echo JText::_('SITELOCK_PAGE_TITLE'); ?></label>
  <div class="controls">
    <input id="title" name="title" class="input-xlarge" value="<?php echo $this->options->title;?>" type="text">
  </div>
</div>
<div class="control-group">
  <label class="control-label" for="title"><?php echo JText::_('SITELOCK_BG_COLOR'); ?></label>
   <div class="controls">
    <div class="input-group demo2">
        <?php /*
        <input type="text" value="<?php echo $this->options->bgcolor;?>" class="form-control" name="bgcolor" />
        <span class="input-group-addon"><i></i></span>
         */ ?>
        <?php echo $this->form->getInput('bgcolor'); ?>
    </div>
   </div>
</div>
<div class="control-group">
  <label class="control-label" for="title"><?php echo JText::_('SITELOCK_BG_IMAGE'); ?></label>
  <div class="controls">
      <?php /*
      <input type="file" name="bgimage" id="control"><img src="<?php echo JURI::base().$this->options->bgimage; ?>" width="50" height="50" alt="" /> <a href="index.php?option=com_sitelock&controller=sitelock&task=clear_image" id="clear" class="btn btn-primary"><?php echo JText::_('SITELOCK_CLEAR_BUTTON'); ?></a>
      */ ?>
       <?php echo $this->form->getInput('bgimage'); ?>
       
  </div>
</div>
<div class="control-group">
  <label class="control-label" for="Show Login Form"><?php echo JText::_('SITELOCK_SHOW_LOGIN'); ?></label>
  <div class="controls">
  	<input type="radio" name="login" value="1" <?php echo  $this->options->login == 1 ? 'checked="checked"' : ''; ?>/><?php echo JText::_('SITELOCK_SHOW_LOGIN_YES'); ?>
	<input type="radio" name="login" value="0" <?php echo   $this->options->login == 0 ? 'checked="checked"' : ''; ?>/><?php echo JText::_('SITELOCK_SHOW_LOGIN_NO'); ?>
  </div>
</div>

<script>
    jQuery(function(){
        setTimeout(function(){
            jQuery('#newuser').val('');
            jQuery('#newpass').val('');
        },1000);
        
        
        jQuery('.demo2').colorpicker();
		
		var clearBn = jQuery("#clear");
		// Setup the clear functionality
		clearBn.on("click", function(){
			var control = jQuery("#control");
			control.replaceWith( control.val('').clone( true ) );
		});
    });
</script>

<fieldset class="adminform">
<legend><?php echo JText::_('LBLINSTALLED_VERSION'); ?></legend>
<?php
	$versioninfo = "";
	//$versioninfo =  sitelockHelper::getInfo();
	$sitelockHelper = new sitelockHelper;
		
	$versioninfo =  $sitelockHelper->getInfo();
	 
	if(is_array($versioninfo) && count($versioninfo) > 0) {
	
		echo "<table width='100%'>";
		if($versioninfo['version_status'] == -1) {
			// Version output in red			
			echo "<tr><td width='40%'><img src='".SITELOCK_ADMIN_IMG_PATH."/upgrade1.png' border='0' align='middle' /></td>";		
			//echo  '<td>'.JText::_('PRODUCT_VERSION_UPDATE_TEXT').'</td></tr>';				
			echo  '<td>'.JText::_('PRODUCT_VERSION_UPDATE_TEXT').'<br/><a href="http://www.joomlashowroom.com" target="_blank"><input type="button" value="'.JText::_('PRODUCT_VERSION_UPDATE').'"/> </a></td></tr>';
			
			echo "<tr><td>".JText::_('PRODUCT_INSTALLED_VERSION')."</td>";
			echo "<td>".$versioninfo['version_installed']."</td></tr>";							
			echo "<tr><td>".JText::_('PRODUCT_AVALIABLE_VERSION')."</td>";
			echo "<td>".$versioninfo['version_latest']."</td></tr>";		
		}else{						 						
			echo "<tr><td width='40%'>".JText::_('PRODUCT_VERSION_UPTODATE')."</td>";
			echo "<td><img src='".SITELOCK_ADMIN_IMG_PATH."/tick.png' border='0' align='middle' /></td></tr>";		
			echo "<tr><td>".JText::_('PRODUCT_INSTALLED_VERSION')."</td>";
			echo "<td>".$versioninfo['version_installed']."</td></tr>";							
			echo "<tr><td>".JText::_('PRODUCT_AVALIABLE_VERSION')."</td>";
			echo "<td>".$versioninfo['version_latest']."</td></tr>";			
		}
		echo "</table>";
	}					 					
?>
</fieldset>
</div>
<div class="width:55%; float:left;">
	<fieldset class="adminform">
		<legend><?php echo JText::_('SITELOCK_EXPLANATION_MESSAGE'); ?></legend>
		<?php 
		//jimport( 'joomla.html.editor' );
		$editor =JFactory::getEditor();
	//	$content = file_get_contents(JPATH_PLUGINS . DS . 'system' .DS . 'sitelock' . DS . 'sitelock.html');
		$content = $this->options->message;
		
		echo $editor->display('content_display', $content, '550', '400', '60', '20', true);
		?>
	</fieldset>
</div>
</div>
</div>

<p class="copyright">
	<?php //echo sitelockAdmin::footer( );
			$sitelockAdmin = new sitelockAdmin;
			echo $sitelockAdmin->footer( );
	?>
</p>
</form>
