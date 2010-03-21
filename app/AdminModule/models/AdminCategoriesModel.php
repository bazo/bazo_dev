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
class Admin_CategoriesModel extends Admin_BaseModel
{
	protected $table = "site_categories"; 
	
	public function _getIdLevel()
	{
        return db::select('*, CONCAT_WS(\';\',id,level) id_level')->from('[:table:]')
        ->orderBy('parent asc')->orderBy('position asc')->fetchPairs('id_level','title');  
	}
	
	public function _save($values)
	{
		$values['parent'] = (int)$values['parent'];
		$values['slug'] = String::webalize($values['title']);
		$position = (int)db::select('max(position)')->from(':table:')->where('parent = %i', $values['parent'])->fetchSingle();
		$values['position'] = $position+1;
		db::insert(':table:', $values)->execute();
		return array('id' => db::getInsertId(), 'title' => $values['title'], 'parent_id' => $values['parent'], 'position' => $values['position']);
	}
	
	public function _getPairs($key ='id', $value = 'title')
    {
        return db::select('*')->from('[:table:]')->fetchPairs($key, $value);  
	}
	
	public function _getAll()
	{
       return db::select('*')->from('[:table:]')->fetchAll();  
	}
	
	public function _getByName($category)
	{
        return db::select('*')->from('[:table:]')->where('title = %s', $category)->fetch();
	}
	
	public function _getById($id)
	{
        return db::select('*')->from('[:table:]')->where('id = %i', $id)->fetch();
	}
	
	public function _delete($id)
	{
		db::delete(':table:')->where('id = %i', $id)->execute();
		$values = array('category' => $id);
		db::update(':pages:', $values)->where(':pages:.category = %i', $id)->execute();
	}
}
