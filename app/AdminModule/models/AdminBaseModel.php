<?php
/**
 * Mokuji CMS
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
    
    public function getCache()
    {
        return Environment::getCache('Models'.$this->getReflection()->getName());
    }
    
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
    
    public function __call($method_name, $args)
    {
        $method_name = '_'.$method_name;
        $tags = array('tags' => array('Models'.$this->getReflection()->getName()));
        if( $this->reflection->hasMethod($method_name))
        {
            $method = $this->reflection->getMethod($method_name);
            if( $method->getAnnotation('cache') == 'update' || $method->getAnnotation('cache') == 'insert'
            || strpos(String::lower($method_name), 'update') > 0 || strpos(String::lower($method_name), 'insert') > 0 || strpos(String::lower($method_name), 'delete') > 0 || strpos(String::lower($method_name), 'edit') > 0
            || strpos(String::lower($method_name), 'save') > 0 || strpos(String::lower($method_name), 'create') > 0)
            {
                fd('cache cleaned');
                if(!empty($args)) $method->invokeArgs($this, $args); 
                else $this->$method_name();   
                $this->cache->clean(array(Cache::ALL => true));
            }
            else
            {   
                if(!empty($args))
                { 
                    $ckey = sha1($method_name.serialize($args));
                    if(!isset($this->cache[$ckey])) $this->cache->save($ckey, $method->invokeArgs($this, $args)); 
                }
                else
                {
                    $ckey = sha1($method_name);
                    if(!isset($this->cache[$ckey])) $this->cache->save($ckey, $this->$method_name());   
                }
                
                return $this->cache[$ckey];
            }
        } 
    }
}