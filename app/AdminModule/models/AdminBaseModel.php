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
class Admin_BaseModel extends Model
{
	protected function startup()
	{
		$this->table_aliases = array(
			'admin' => 'admin_',
			'site' => 'site_',
			'categories' => 'site_categories',
			'pages' => 'site_pages',
			'menu_items' => 'site_menu_items'
		);
	}
}