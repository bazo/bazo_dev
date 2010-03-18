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
class Front_SearchModel extends Front_BaseModel
{
	protected $table = 'site_pages';
	
	public function search($query)
	{
		return db::select(':table:.title page_title, :table:.content page_content, :table:.slug page_slug, :categories:.title cat_title, :categories:.slug cat_slug')->from(':table:')->leftJoin(':categories:')->on(':table:.category = :categories:.id')->where('MATCH (:table:.title, :table:.content) AGAINST(%s IN BOOLEAN MODE)', $query)->fetchAll();
	}
}
?>