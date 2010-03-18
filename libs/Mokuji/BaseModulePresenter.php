<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of BaseModulePresenter
 *
 * @author Martin
 */
class AdminBaseModulePresenter extends Admin_SecurePresenter{

        public function formatLayoutTemplateFiles($presenter, $layout)
	{
            
            $themesDir = Environment::getVariable('themesDir');
            $theme = Environment::getVariable('theme');
            $appDir = Environment::getVariable('appDir');

            $pos = strpos($presenter, ':');
            $search = substr($presenter, 0, $pos);
            $presenter = str_replace($search, 'Admin', $presenter);

            $path = '/' . str_replace(':', 'Module/', $presenter);
            $pathP = substr_replace($path, '/'.$themesDir.'/'.$theme.'/templates', strrpos($path, '/'), 0);
            /*
            $list = array(
                    "$appDir$pathP/@$layout.phtml",
                    "$appDir$pathP.@$layout.phtml",
            );
             * 
             */
            while (($path = substr($path, 0, strrpos($path, '/'))) !== FALSE) {
                    $list[] = "$appDir$path".'/'.$themesDir.'/'.$theme.'/templates/'."@$layout.phtml";
            }

            
            $list = parent::formatLayoutTemplateFiles($presenter, $layout);
            //var_dump($list);exit;
            return $list;
	}


	public function formatTemplateFiles($presenter, $view)
	{
            $parts = explode(':', $presenter);
            $moduleDir = $parts[0];
            $presenterDir = $parts[1];
            $parts = null;
            $appDir = Environment::getVariable('appDir');
            $path = '/' . str_replace(':', 'Modules/', $presenter);
            $path = '/Modules/'.$moduleDir;
            $pathP = $path.'/templates/'.$presenterDir;
            $themesDir = Environment::getVariable('themesDir');
            $theme = Environment::getVariable('theme');
            $this->pathToTheme = '/AdminModule/'.$themesDir.'/'.$theme;
            $list = array(
                    "$appDir$pathP/$view.phtml",
                    "$appDir$pathP.$view.phtml",
                    "$appDir$path/templates/@global.$view.phtml",
            );
           //$list = parent::formatTemplateFiles($presenter, $view);
            //var_dump($list);exit;
            return $list;
	}
}
?>
