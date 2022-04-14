<?php
/**
* @version   $Id: [bgm_yoothemepro_tabs.php] 2020-04-08 [developer1] $
* @author By George Media https://georgemedia.com.au.com.au
* @copyright Copyright (C) 2019 - 2021 By George Media (BGM)
* @support support@georgemedia.com.au
*/
// no direct access
defined('_JEXEC') or die('Restricted access');
// add fucntion print for debug
 if(!function_exists('k')){
        function k($str){
			   if($str){
					   echo "<pre>";
					   print_R($str);
					   echo "</pre>";
			   }else{
					   echo "<pre>";
					   var_dump($str);
					   echo "</pre>";
			   }
        }
}

class PlgContentBgm_Yoothemepro_Tabs extends JPlugin{
   
	public function __construct(&$subject, $config){
		parent::__construct($subject, $config);

	}
	
	public function getClassTab($class){
		$remove = ['bgm-tab' ,'uk-section-default', 'uk-section'];
		return trim(str_replace($remove, ['bgmtab_replaced','','','', ''], $class));
	}
	
	public function BGM_Custom_Tab($items, $classtab = ''){
        ob_start(); ?>
        <div class="uk-section bgm-tabs-section other-<?php echo $classtab;?>">
            <div class="uk-container uk-container-expand">
                <ul class="el-nav uk-subnav uk-subnav-pill uk-flex-center" uk-switcher="animation: uk-animation-fade;">
                    <?php foreach ($items as $i=>$dom){ 
						$tabclass = $this->getClassTab($dom->attr['class']);
						?>
                        <li class="<?php echo $tabclass; ?>"><a href="#"><?php echo isset($dom->attr['tabtitle'])?$dom->attr['tabtitle']:'TAB ' + $i; ?></a></li>
                    <?php } ?>
                </ul>
                <ul class="uk-switcher uk-margin">
                    <?php foreach ($items as $i=>$dom){  
						$tabclass = $this->getClassTab($dom->attr['class']);
						?>
						<li class="<?php echo $tabclass; ?>-content">
							<?php echo str_replace($classtab, 'bgm-tab_replaced',$dom->outertext()); ?>
						</li>
                    <?php } ?>
                </ul>
            </div>   
        </div>
        <?php
        return ob_get_clean();
    }
    public function onContentBeforeDisplay($context, &$row, &$params, $page = 0)
	{
		$content = $row->text;
		#include simple_html_dom
		include_once dirname(__FILE__) . '/library/simple_html_dom.php';
		$dom_content = str_get_html($content);
		if(!$dom_content || empty($content)) return false;
		$tabs = $dom_content->find('.bgm-tab');
		#BGM check if has format custom tab
		if(count($tabs) > 0){
			$tab_content = $this->BGM_Custom_Tab($tabs, 'bgm-tab');
			foreach ($dom_content->find('.bgm-tab') as $i=> $tab){
				if($i == 0) $tab->outertext  = $tab_content;
				else $tab->remove();
			}
			$content = $dom_content;
		}
                
                #add code for other tab
                $tabsOther = $dom_content->find('[class^=bgm-tab-]');
                if(count($tabsOther) > 0){
                    $groups = [];
                    foreach ($tabsOther as $tItems){
                        $class = explode(' ', $tItems->attr['class']);
                        $groups[] = $class[0];
                    }
                }
                
                if($groups){
                    foreach ($groups as $classTab){
                        $dom_content = str_get_html($content);
                        $tabItem = $dom_content->find('.'.$classTab);
                        if(count($tabItem) > 0){
                            $tab_content = $this->BGM_Custom_Tab($tabItem, $classTab);
                            foreach ($dom_content->find('.'.$classTab) as $i=> $tab){
                                    if($i == 0) $tab->outertext  = $tab_content;
                                    else $tab->remove();
                            }
                            $content = $dom_content;
                        }
                        
                    }
                }

                #and other tab
		$row->text = $content;
	}
}


