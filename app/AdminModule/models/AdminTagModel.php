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
class Admin_TagsModel extends Admin_BaseModel
{
	protected $table = "site_tags";
	
	public function getAll()
	{
		return db::select('*')->from('[:table:]')->fetchAll();
	}
}