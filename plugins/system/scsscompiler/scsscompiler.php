<?php

/**
 * @package    scssCompiler
 * @subpackage C:
 * @author     LOMART 
 * @author     Created on 12-Nov-2014
 * @license    GNU/GPL
 */
//-- No direct access
defined('_JEXEC') || die('=;)');

use ScssPhp\ScssPhp\Compiler;

/**
 * System Plugin.
 *
 * @package    scssCompiler
 * @subpackage Plugin
 */
class plgSystemscssCompiler extends JPlugin {

    public function onBeforeRender() {

        if (!defined('DS')) {
            define('DS', DIRECTORY_SEPARATOR);
        }
        $app = JFactory::getApplication();
        if ($app->isClient('site') === false) {
            return;
        }
        $this->loadLanguage();

        /* LES CHEMINS A TRAITER
         * ---------------------
         * les autres chemins que celui du template
         */
        $tmp = str_replace('/', DS, trim($this->params->get('other_scss_folder')));
        $scss_folder = array_filter(explode(PHP_EOL, $tmp));
        $tmp = str_replace('/', DS, trim($this->params->get('other_css_folder')));
        $css_folder = array_filter(explode(PHP_EOL, $tmp));
        if (count($scss_folder) != count($css_folder)) {
            $app->enqueueMessage(JText::_('ERR_NB_OTHER_PATHS'), 'error');
            return false;
        }
        /* Ajout slash en fin chemin */
        $nb = count($scss_folder);
        for ($i = 0; $i < $nb; $i++) {
			$scss_folder[$i] = trim($scss_folder[$i]);
            $scss_folder[$i] .= (substr($scss_folder[$i], -1, 1) == DS) ? '' : DS;
			$css_folder[$i] = trim($css_folder[$i]);
            $css_folder[$i] .= (substr($css_folder[$i], -1, 1) == DS) ? '' : DS;
        }

        /* ajout chemin pour template */
        if ($this->params->get('tmpl_compile', 1)) {
            $templatePath = 'templates' . DS . $app->getTemplate() . DS;
            $scss_folder[] = $templatePath . $this->params->get('tmpl_scss_folder', 'scss') . DS;
            $css_folder[] = $templatePath . $this->params->get('tmpl_css_folder', 'css') . DS;
        }
        /*
         * $folder est un tableau contenant: $scss_folder => $css_folder
         */
        $folders = array_combine($scss_folder, $css_folder);

        /** RECHERCHE DES FICHIERS
         *  ----------------------
         */
        $file2compile = array();  // liste des fichiers à compiler par scssphp

        foreach ($folders as $scss_folder => $css_folder) {
            $scss_folder = JPATH_BASE . DS . str_replace('/', DS, $scss_folder);
            $css_folder = JPATH_BASE . DS . $css_folder;
            $files = array();  // fichiers SCSS sans CSS ou CSS plus ancien

            /* Si compilation forcée: on recompile tout */
            $ok = ($this->params->get('force_compile', 0));

            if (!$ok) {
                /* date du plus récent .SCSS
                 * note: les sous-dossier ne sont pas explorés
                 */
                $lastTimePartial = 0; // le datetime du partiel le plus récent
                foreach (glob($scss_folder . "[_]*.scss") as $fileScss) {
                    if (filemtime($fileScss) > $lastTimePartial) {
                        $lastTimePartial = filemtime($fileScss);
                    }
                }

                /* date du plus récent CSS correspondant à un SCSS
                 * si CSS inexistant, on oblige la compilation de tous les SCSS
                 */
                $lastTimeCss = '';
                foreach (glob($scss_folder . "[!_]*.scss") as $fileScss) {
                    $fileCss = $css_folder . basename($fileScss, '.scss') . '.css';
                    if (file_exists($fileCss)) {
                        if (filemtime($fileScss) > filemtime($fileCss)) {
                            $files[] = $fileScss;
                        } elseif (filemtime($fileCss) > $lastTimeCss) {
                            $lastTimeCss = filemtime($fileCss);
                        }
                    } else {
                        // fichier css inexistant
                        $files[] = $fileScss;
                    }
                }
            }
            if ($ok || ($lastTimePartial > $lastTimeCss)) {
                /* Ajout de tous les SCSS à la liste des fichiers à traiter  */
                $files = glob($scss_folder . "[!_]*.scss");
            }
            foreach ($files as $file) {
                $file2compile[$file] = $css_folder . basename($file, '.scss') . '.css';
            }
        }

        if (!class_exists('ScssPhp\ScssPhp\Compiler')) {
            require_once('scssphp/scss.inc.php');
        }
        $formatter = $this->params->get('scss_compress', 'Compressed');
		if (!in_array($formatter, array('Compact','Compressed','Crunched','Expanded','Nested'))) {
			$formatter = 'Compressed';
		}
        $formatter = 'ScssPhp\ScssPhp\Formatter\\' . $formatter;
        foreach ($file2compile as $fileScss => $fileCss) {
            $pathAbsolute = pathinfo($fileCss, PATHINFO_DIRNAME) . DS;
            $pathRelative = str_replace(JPATH_ROOT, '', $pathAbsolute);
            $ficName = pathinfo($fileCss, PATHINFO_FILENAME);
            
            $scss_compiler = new Compiler();
            $scss_compiler->setImportPaths(pathinfo($fileScss, PATHINFO_DIRNAME));
			// compilation 
            $scss_compiler->setFormatter($formatter);
            // source map
            if ($this->params->get('source_map', 1)) {
                $fileMap = $ficName . '.css.map';
                
                $scss_compiler->setSourceMap(Compiler::SOURCE_MAP_FILE);
                $scss_compiler->setSourceMapOptions(array(
                    // absolute path to write .map file : 
                    'sourceMapWriteTo'  => $pathAbsolute . $fileMap,
                    // relative or full url to the above .map file
                    'sourceMapURL'      => $pathRelative . $fileMap,
                    // partial path (server root) removed (normalized) to create a relative url
                    // difference between file & url locations, removed from ALL source files in .map
                    'sourceMapBasepath' => str_replace('\\','/',JPATH_ROOT),
                    // (optional) prepended to 'source' field entries for relocating source files
                    'sourceRoot'        => '/',
					));
            }
            try {
                $string_sass = file_get_contents($fileScss);
                $string_css = $scss_compiler->compile($string_sass);
                if ($string_css > '') {
                    file_put_contents($fileCss, $string_css);
                }
                if ($this->params->get('msg_ok', 0)) {
                    $app->enqueueMessage(JText::sprintf('SCSS_COMPILE_OK', $fileScss), 'notice');
                }
            } catch (Exception $e) {
                $app->enqueueMessage(JText::sprintf('SCSS_COMPILE_ERROR', $fileScss, $e->getmessage()), 'error');
            }
//             if (!is_null($scss_compiler->getSourceMap())) { 
// //			    $result = $scss_compiler->compileString('@import "'.$string_sass.'";');
// 			    file_put_contents($fileMap, $scss_compiler->getSourceMap());
// 			}
        }
    }

// onBeforeRender
}

//class

/*
 * 
use ScssPhp\ScssPhp\Compiler;

$compiler = new Compiler();
$compiler->setSourceMap(Compiler::SOURCE_MAP_FILE);
$compiler->setSourceMapOptions([
    // relative or full url to the above .map file
    'sourceMapURL' => './my-style.map',

    // (optional) relative or full url to the .css file
    'sourceMapFilename' => 'my-style.css',

    // partial path (server root) removed (normalized) to create a relative url
    'sourceMapBasepath' => '/var/www/vhost',

    // (optional) prepended to 'source' field entries for relocating source files
    'sourceRoot' => '/',
]);

$result = $compiler->compileString('@import "sub.scss";');

file_put_contents('/var/www/vhost/my-style.map', $result->getSourceMap());
file_put_contents('/var/www/vhost/my-style.css', $result->getCss());

// use Compiler::SOURCE_MAP_INLINE for inline (comment-based) source maps
 */


