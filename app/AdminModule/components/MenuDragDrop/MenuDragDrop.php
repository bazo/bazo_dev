<?php
class MenuDragDrop extends Control
{
	protected $model, $translator;
	public $onItemMove = array(), $onItemDelete = array();
	
	public function __construct()
	{
		//parent::__construct();
	}
	
	public function setModel($model)
	{
		$this->model = $model;
	}
	
	public function setTranslator(ITranslator $translator)
	{
		$this->translator = $translator;
		return $this;
	}
	
	public function render($menu, $menu_id)
	{
		$template = $this->createTemplate();
		$this->template->setTranslator($this->translator);
		$template->menu = $menu;
		$template->menu_items = $this->fillMenu($menu_id);
		$template->setFile(dirname(__FILE__) . '/menuDragDrop.phtml');
		$template->render();
	}
	
	public function handleItemMove()
	{
		foreach($this->onItemMove as $func)
		{
			call_user_func($func);
		}
		//var_dump($this->getSnippetId());
		$this->getPresenter()->invalidateControl('menuDragDrop');
	}
	
	public function handleItemDelete($item)
	{
		foreach($this->onItemDelete as $func)
		{
			call_user_func($func, $item);
		}
		$this->getPresenter()->invalidateControl('menuDragDrop');
	}
	
	private function fillMenu($menu_id, $parent = 0)
	{
		$items = $this->model->getByParent($menu_id, $parent);
		foreach($items as $key => $item)
		{
			$items[$key]['items'] = $this->fillMenu($menu_id, $item->id);
		}
		return $items;
	}
}
?>