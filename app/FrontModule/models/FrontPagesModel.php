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
class Front_PagesModel extends Admin_BaseModel
{
	protected $table = 'site_pages';
	
	public function getAll()
	{
		return db::select('*')->from('[:table:]')->fetchAll();
	}
	
	public function getPairs($key = 'url', $value = 'title')
	{
		return db::select('*')->from('[:table:]')->fetchPairs($key, $value);
	}
	
	public function getById($id)
	{
		return db::select('*')->from('[:table:]')->where('id = %i', $id)->fetch();
	}
	
	public function getBySlug($slug)
	{
		return db::select('*')->from('[:table:]')->where('slug = %s', $slug)->and('published = 1')->fetch();
	}
	
	public function getByCategory($category)
	{
		$data = new stdClass();
		$res = db::select('*')->from('[:categories:]')->where('slug = %s', $category)->fetch();
		if($res == false) throw new Exception('Category '.$category.' not found');
		$cat_id = (int)$res->id;
		$pages = db::select('*')->from('[:table:]')->where('category = %i', $cat_id)->fetchAll();
		$data->category = $res;
		$data->pages = $pages;
		return $data;
	}
	
	public function getHomepage()
	{
		return db::select('*')->from('[:table:]')->where('[:table:].homepage = 1')->fetch();
	}
}
?>