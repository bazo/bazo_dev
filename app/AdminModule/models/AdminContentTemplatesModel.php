<?php
/**
 * Photon CMS
 *
 * @copyright  Copyright (c) 2009 Martin Bazik
 * @package    AdminModels
 */

/**
 * Admin Categories Model
 *
 * @author     Martin Bazik
 * @package    AdminModels
 */
class Admin_ContentTemplatesModel extends Admin_BaseModel
{
	public function get($content_type)
	{
		$appDir = Environment::getVariable('appDir');
		$moduleDir = 'FrontModule';
		$themesDir = Environment::getVariable('themesDir');
		$data = ConfigAdapterIni::load(APP_DIR.'/config/site.ini', 'site');
		$activeTheme = $data['theme'];
		$themesPath = $appDir.'/'.$moduleDir.'/'.$themesDir;
		$folder = $themesPath.'/'.$activeTheme.'/templates/Page/';
		$templates = glob($folder.$content_type.'*');
		$new_templates = array();
		foreach($templates as $index => $template)
		{
			$fn = basename($template);
			$fn = str_replace($content_type.'-', '', $fn);
			$fn = str_replace('.phtml', '', $fn);
			$new_templates[$fn] = $fn;
		}
		return $new_templates;
	}
}
