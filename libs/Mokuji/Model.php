<?php
class Model extends Object
{
	protected $prefix, $table_aliases = array();
	protected $table;
	
	public function __construct()
	{
		db::connect();
		$config = Environment::getConfig();
		$this->prefix = '';
		$this->startup();
		$this->__after_startup();
	}
	
	protected function startup()
	{
		
	}
	
	protected function __after_startup()
	{
		foreach ($this->table_aliases as $alias => $table) {
			db::addSubst($alias, $this->prefix.$table);
		}
		db::addSubst('table', $this->prefix.$this->table);
	}
	
	public function getDs()
	{
		return db::select('*')->from(':table:')->toDataSource();
	}
}
?>