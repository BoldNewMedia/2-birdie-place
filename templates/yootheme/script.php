<?php

use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\File;

class yoothemeInstallerScript
{
    protected $db;
    protected $tmp;
    protected $name;
    protected $dest;

    public function __construct($parent)
    {
        $this->db = Factory::getDBO();
        $this->tmp = Factory::getApplication()->getCfg('tmp_path');
        $this->name = $parent->getName();
        $this->dest = $parent->getParent()->getPath('extension_root');
    }

    public function preflight($type, $parent)
    {
        if ($type == 'update') {

            // backup theme*.css
            $files = glob("{$this->dest}/css/theme*.css");

            foreach ($files as $file) {

                $filename = basename($file);

                if (strpos($file, 'update.css')) {
                    continue;
                }

                if (File::exists($file)) {
                    File::move($file, "{$this->tmp}/{$filename}");
                }
            }

            // clean folders
            foreach (array('less', 'vendor', 'templates') as $path) {
                if (JFolder::exists("{$this->dest}/{$path}")) {
                    JFolder::delete("{$this->dest}/{$path}");
                }
            }
        }
    }

    public function postflight($type, $parent)
    {
        if ($type == 'update') {

            // restore theme*.css
            foreach (glob("{$this->tmp}/theme*.css") as $file) {

                $filename = basename($file);

                if (File::exists($file)) {
                    File::move($file, "{$this->dest}/css/{$filename}");
                }
            }

            foreach ($this->loadTemplateStyles() as $id => $params) {

                $params = json_decode($params, true);

                // Add theme.support for uikit3
                if ($params && empty($params['uikit3'])) {
                    $params['uikit3'] = true;
                    $this->updateTemplateStyle($id, json_encode($params));
                }

                // Check child theme's "theme.js" for jQuery
                if ($params
                    AND isset($params['config'])
                    AND $config = json_decode($params['config'], true)
                    AND empty($config['jquery'])
                    AND !empty($config['child_theme'])
                    AND File::exists($path = JPATH_ROOT."/templates/{$this->name}_{$config['child_theme']}/js/theme.js")
                    AND $contents = File::read($path)
                    AND strpos($contents, 'jQuery') !== false
                ) {
                    $config['jquery'] = true;
                    $params['config'] = json_encode($config);
                    $this->updateTemplateStyle($id, json_encode($params));
                }
            }
        }
    }

    protected function loadTemplateStyles()
    {
        $query = "SELECT id, params FROM #__template_styles WHERE template=".$this->db->quote($this->name);
        return $this->db->setQuery($query)->loadAssocList('id', 'params');
    }

    protected function updateTemplateStyle($id, $params)
    {
        $query = "UPDATE #__template_styles SET params=".$this->db->quote($params)." WHERE id={$id}";
        $this->db->setQuery($query);
        $this->db->execute();
    }
}
