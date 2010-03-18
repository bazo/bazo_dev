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
class Admin_MenuItemsModel extends Admin_BaseModel
{
	protected $table = 'site_menu_items';
	
	public function getAll()
	{
		return db::select('*')->from('[:table:]')->fetchAll();
	}
	
	public function getById($id)
	{
		return db::select('*')->from('[:table:]')->where('id = %i', $id)->fetchAll();
	}
	
	public function getByParent($menu_id, $parent)
	{
		return db::select('*')->from('[:table:]')->where('menu_id = %i', $menu_id)->where('parent = %i', $parent)->orderBy('position asc')->fetchAll();
	}
	
	public function getByTitle($name)
	{
		return db::select('*')->from('[:table:]')->where('title = %s', $title)->fetch();
	}
	
	public function getByMenuId($menu_id)
	{
		return db::select('*, CONCAT_WS(\';\',id,level) id_level')->from('[:table:]')->where('menu_id = %i', $menu_id)->orderBy('parent asc')->orderBy('position asc');
	}
	
	public function createMenuItem($values)
	{
		db::insert(':table:', $values)->execute();
	}
	
	public function create($values)
	{
		$values['parent'] = (int)$values['parent'];
		$position = (int)db::select('max(position)')->from(':table:')->where('parent = %i', $values['parent'])->fetchSingle();
		$values['position'] = $position+1;
		db::insert(':table:', $values)->execute();
		return array('id' => db::getInsertId(), 'title' => $values['title'], 'parent_id' => $values['parent'], 'position' => $values['position']);
	}
	
	public function moveItem($item_id, $menu_id, $parent_id, $prev_position, $parent_level, $diff)
	{
		$values = array(
			'parent' => $parent_id,
			'position' => $prev_position + 1,
			'level' => $parent_level + 1
		);
		db::update(':table:', array('position%sql' => '[position]+1'))->where('menu_id = %i', $menu_id)->where('parent = %i', $parent_id)
		->where('position > %i', $prev_position)->execute();
		db::update(':table:', $values)->where('menu_id = %i', $menu_id)->where('id = %i', $item_id)
		->execute();
		/*
		db::update(':table:', array('level%sql' => '[level]+ '.$diff))->where('menu_id = %i', $menu_id)->where('parent = %i', $item_id)
		->execute();
		*/
		$items = $this->recursiveLevelUpdate($item_id, $parent_level+1);
		
	}
	
	public function deleteItem($item_id)
	{
		db::delete(':table:')->where('id = %i or parent = %i', $item_id, $item_id)->execute();
		
	}
	
	private function recursiveLevelUpdate($parent_id, $level)
	{
		static $counter = 0;
		$counter++;
		$items = db::select('*')->from(':table:')->where('parent = %i', $parent_id)->fetchAll();
		foreach($items as $key => $item)
		{
			$update = $level + $counter;
			$values = array('level' => $update);
			db::update(':table:', $values)->where('id = %i', $item->id)
				->execute();
			$items[$key]['items'] = $this->recursiveLevelUpdate($item->id, $level);
		}
		return $items;
	}
	
	public function deleteRelated($partial_slug)
	{
		$partial_slug = '%'.$partial_slug;
		db::delete(':table:')->where('url like %s', $partial_slug)->execute();
	}
}
?>