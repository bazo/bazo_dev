<?php
/**
 * Photon CMS
 *
 * @copyright  Copyright (c) 2009 Martin Bazik
 * @package    FrontModels
 */

/**
 * Front Menu Model
 *
 * @author     Martin Bazik
 * @package    FrontModels
 */
class Front_MenuModel extends Front_BaseModel
{
	protected $table = 'site_menus';
	
	protected function startup()
	{
		$this->table_aliases = array(
	'items' => 'site_menu_items');
	}
	
	public function getAll()
	{
		return db::select('*')->from('[:table:]')->fetchAll();
	}
	
	public function getById($id)
	{
		return db::select('*')->from('[:table:]')->where('id = %i', $id)->fetchAll();
	}
	
	public function getByName($name)
	{
		return db::select('*')->from('[:table:]')->where('name = %s', $name)->fetch();
	}
	
	public function getMenuItems($menu_id)
	{
		return db::select('c.`id`, c.`title`, c.`url`, c.parent as `parent_id`, p.title as `parent`, c.level, c.position')->from('[:items:] c')->leftJoin('[:items:] p')->on('c.parent = p.id')->where('c.menu_id = %i', $menu_id)->orderBy('c.level')->orderBy('c.position asc')->fetchAll();
	}
	
	public function createMenu($values)
	{
		db::insert(':table:', $values)->execute();
		return db::getInsertId();
	}
	
	public function getByParent($menu_id, $parent)
	{
		return db::select('c.`id`, c.`title`, c.`url`, c.parent as `parent_id`, p.title as `parent`')->from('[:items:] c')->leftJoin('[:items:] p')->on('c.parent = p.id')->where('c.menu_id = %i', $menu_id)->where('c.parent = %i', $parent)->orderBy('c.level')->orderBy('c.position asc')->fetchAll();
	}
}

?>