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
class Front_BaseModel extends Model
{
	protected function startup()
	{
		$this->table_aliases = array(
			'categories' => 'site_categories'
		);
	}
}