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
class Admin_PagesModel extends Admin_BaseModel
{
	protected $table = 'site_pages';
	
	public function _getAll()
	{
        return db::select('*')->from('[:table:]')->fetchAll();
	}
	
	public function _getPairs($key = 'slug', $value = 'title')
	{
        return db::select(':table:.title `page_title`, :table:.slug `page_slug`, :categories:.title `category_title`, :categories:.slug `category_slug`')->from('[:table:]')->leftJoin(':categories:')->on(':table:.category = :categories:.id')->where(':table:.homepage = 0')->orderBy(':table:.category ASC')->fetchAssoc('category_slug|page_title');
    }
	
	public function _getById($id)
    {
		return  db::select(':table:.*,UNIX_TIMESTAMP(:table:.added) `added`, :categories:.id `category_id`, :categories:.title `category_title`, :categories:.slug `category_slug`')->from('[:table:]')->leftJoin(':categories:')->on(':table:.category = :categories:.id')->where(':table:.id = %i', $id)->fetch();
    }
	
	public function _getByTitle($title)
	{
		return db::select('*')->from('[:table:]')->where('title = %s', $title)->fetch();
	}
	
	public function _getBySlug($slug)
	{
		return db::select('*')->from('[:table:]')->where('slug = %s', $slug)->and('content_type = %s', 'page')->fetch();
	}
	
	public function _getRecent($count)
	{
		return db::select(':table:.*,UNIX_TIMESTAMP(:table:.added) `added`, :categories:.title `category_title`, :categories:.slug `category_slug`')->from('[:table:]')->leftJoin(':categories:')->on(':table:.category = :categories:.id')->orderBy('added DESC')->limit('%i', $count)->fetchAll();
	}
	
	public function _save($values)
	{
        $values['slug'] = String::webalize($values['title']);
        if (isset($values['link']))
        {
            if($values['link'] > 0)
            {
                    $link = array();
                    $slug = $values['slug'];
                    if($values['category'] > 0)
                    {
                            $cat_model = new Admin_CategoriesModel();
                            $cat = $cat_model->getById((int)$values['category']);
                            $slug = $cat->slug.'/'.$slug;
                    }
                    $link['title'] = $values['title'];
                    $link['url'] = $slug;
                    $link['parent'] = 0;
                    $link['level'] = 1;
                    $link['menu_id'] = (int)$values['link'];
                    $link_model = new Admin_MenuItemsModel();
                    $link_model->create($link);
                    //refresh table alias
                    $this->__after_startup();
            }
            unset($values['link']);
        }
        $values['content_type'] = 'page';
        if(!isset($values['publish_time'])) $values['publish_time'] = time();
        $values['publish_time'] = db::datetime($values['publish_time']);
        if(isset($values['homepage']))
            if($values['homepage'] == 1)
            {
                    $update = array('homepage' => 0);
                    db::update(':table:', $update)->execute();
            }
        db::insert(':table:', $values)->execute();
        return db::getInsertId();
	}
	
	public function _update($values)
	{
        if(isset($values['publish_time'])) $values['publish_time'] = db::datetime($values['publish_time']);
        if(isset($values['homepage']) && $values['homepage'] == 1)
        {
                $update = array('homepage' => 0);
                db::update(':table:', $update)->execute();
        }
        db::update(':table:', $values)->where('id = %i', $values['id'])->execute();
	}
	
	public function _updateByTitle($values)
	{
        if(isset($values['publish_time'])) $values['publish_time'] = db::datetime($values['publish_time']);
        db::update(':table:', $values)->where('title = %s', $values['title'])->execute();
	}
	
	public function _delete($title)
	{
        db::delete(':table:')->where('title = %s', $title)->execute();
	}
	
	public function _deleteById($id)
	{
        return db::delete(':table:')->where('id = %i', $id)->execute();
	}
    
    /**
     * @cache update
     */
    public function _MakeHomepage($title)
	{
        db::update(':table:',array('homepage' => 0))->where('homepage = %i', 1)->execute();
        db::update(':table:',array('homepage' => 1))->where('title = %s', $title)->execute();
	}
}
?>