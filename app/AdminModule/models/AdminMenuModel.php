<?php
/**
 * Photon CMS
 *
 * @copyright  Copyright (c) 2009 Martin Bazik
 * @package    AdminModels
 */

/**
 * Admin Menu Model
 *
 * @author     Martin Bazik
 * @package    AdminModels
 */
class Admin_MenuModel extends Admin_BaseModel
{
	protected $table = 'site_menus';
	
	public function getAll()
	{
		return db::select('*')->from('[:table:]')->fetchAll();
	}
	
	public function getById($id)
	{
		return db::select('*')->from('[:table:]')->where('id = %i', $id)->fetch();
	}
	
	public function getByName($name)
	{
		return db::select('*')->from('[:table:]')->where('name = %s', $name)->fetch();
	}
	
	public function getByNameWithItems($name)
	{
		return db::select('*')->from('[:table:]')->join(':site:menu_items')->on(':table:.id = :site:menu_items.menu_id')->where('name = %s', $name)->orderBy(':site:menu_items.parent asc')->fetchAll();
	}
	
	public function createMenu($values)
	{
		db::insert(':table:', $values)->execute();
		return db::getInsertId();
	}
	
    public function update($values)
    {
        db::update(':table:', $values)->where('id = %i', $values['id'])->execute();
    }
    
	public function getPairs($key = 'id', $value = 'name')
	{
		return db::select('*')->from('[:table:]')->fetchPairs($key, $value);
	}
	
	public function deleteById($id)
	{
		db::delete(':table:')->where('id = %i', $id)->execute();
		db::delete(':menu_items:')->where('menu_id = %i', $id)->execute();
	}
    public function getTemplates()
    {
        $appDir = Environment::getVariable('appDir');
        $moduleDir = 'FrontModule';
        $themesDir = Environment::getVariable('themesDir');
        $data = ConfigAdapterIni::load(APP_DIR.'/config/site.ini', 'site');
        $activeTheme = $data['theme'];
        $themesPath = $appDir.'/'.$moduleDir.'/'.$themesDir;
        $folder = $themesPath.'/'.$activeTheme.'/templates/Menus/';
        $templates = glob($folder.'menu*');
        $new_templates = array();
        foreach($templates as $index => $template)
        {
            $fn = basename($template);
            $fn = str_replace('menu-', '', $fn);
            $fn = str_replace('.phtml', '', $fn);
            $new_templates[$fn] = $fn;
        }
        return $new_templates;
    }
}
?>