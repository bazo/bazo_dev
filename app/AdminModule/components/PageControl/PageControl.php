<?php
class Page extends Control
{
	
	/**
	 * Component where you have handlers
	 * @var PresenterComponent
	 */
	public $handlerComponent;
	
	public function __construct($parent = NULL, $name = NULL)
	{
		parent::__construct($parent, $name);
		$this->handlerComponent = $parent;
		return $this;
	}
	
	public function addItem($name)
	{
		return new Item($this, $name);
	}
	
	/**
	 * Template factory.
	 * @return ITemplate
	 */
	protected function createTemplate()
	{
		$template = parent::createTemplate();
		//$template->registerFilter('Nette\Templates\CurlyBracketsFilter::invoke');
		$template->setFile(dirname(__FILE__) . '/page.phtml');
		return $template;
	}
	
	public function render()
	{
		$this->template->components = $this->components;
		return $this->template->render();
	}
	
	public function invalidate($component_name = null)
	{
		$this->invalidateControl($component_name);
	}
	
	public function redraw($component_name = null)
	{
		$this->invalidateControl($component_name);
	}
	
	public function refresh($component_name = null)
	{
		$this->invalidateControl($component_name);
	}
}
