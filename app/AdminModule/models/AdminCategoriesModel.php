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
	
	public function getIdLevel()
	{
        $cache = Environment::getCache('categories');
        if(!isset($cache[__FUNCTION__])) $cache->save(__FUNCTION__, db::select('*, CONCAT_WS(\';\',id,level) id_level')->from('[:table:]')
        ->orderBy('parent asc')->orderBy('position asc')->fetchPairs('id_level','title') );  
        return $cache[__FUNCTION__];
	}
	
	public function save($values)
	{
		$values['parent'] = (int)$values['parent'];
		$values['slug'] = String::webalize($values['title']);
		$position = (int)db::select('max(position)')->from(':table:')->where('parent = %i', $values['parent'])->fetchSingle();
		$values['position'] = $position+1;
		db::insert(':table:', $values)->execute();
        $cache = Environment::getCache('categories')->clean();
		return array('id' => db::getInsertId(), 'title' => $values['title'], 'parent_id' => $values['parent'], 'position' => $values['position']);
	}
	
	public function getPairs($key ='id', $value = 'title')
    {
        $cache = Environment::getCache('categories');
        if(!isset($cache[__FUNCTION__.$key.$value])) $cache->save(__FUNCTION__.$key.$value,  db::select('*')->from('[:table:]')->fetchPairs($key, $value) );  
        return $cache[__FUNCTION__.$key.$value];
	}
	
	public function getAll()
	{
        $cache = Environment::getCache('categories');
        if(!isset($cache[__FUNCTION__])) $cache->save(__FUNCTION__, db::select('*')->from('[:table:]')->fetchAll() );  
        return $cache[__FUNCTION__];
	}
	
	public function getByName($category)
	{
        $cache = Environment::getCache('categories');
        if(!isset($cache[__FUNCTION__.$category])) $cache->save(__FUNCTION__.$category, db::select('*')->from('[:table:]')->where('title = %s', $category)->fetch());
		return $cache[__FUNCTION__.$category];
	}
	
	public function getById($id)
	{
        $cache = Environment::getCache('categories'); $ckey = __FUNCTION__.$id;
        if(!isset($cache[$ckey])) $cache->save($ckey, db::select('*')->from('[:table:]')->where('id = %i', $id)->fetch());
		return $cache[$ckey];
	}
	
	public function delete($id)
	{
		db::delete(':table:')->where('id = %i', $id)->execute();
		$values = array('category' => $id);
		db::update(':pages:', $values)->where(':pages:.category = %i', $id)->execute();
        $cache = Environment::getCache('categories')->clean(); 
	}
}
