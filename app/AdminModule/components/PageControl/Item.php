<?php

/**
 * This source file is subject to the "New BSD License".
 *
 * For more information please see http://nettephp.com
 *
 * @author     Jan Kuchař
 * @copyright  Copyright (c) 2009 Jan Kuchař (http://mujserver.net)
 * @license    New BSD License
 * @link       http://nettephp.com/extras/tabcontrol
 */



/**
 * Tab class
 *
  * show off @property, @property-read, @property-write
 * @property mixed $content Content of the tab
 */
class Item extends Control {

	/**************************************************************************/
	/*                               Variables                                */
	/**************************************************************************/

	/**
	 * Tab content factory
	 * @var array
	 */
	public $contentFactory = array();

	/**
	 * Tab content renderer
	 * @var array
	 */
	public $contentRenderer;

	/**
	 * Created component
	 * @var IComponent
	 */
	private $content;

	/**
	 * Has content some snippets?
	 * ! This parameter is very important when you use ajax!
	 * @var bool
	 */
	public $hasSnippets= false;

	function  __construct(Page $parent,$name) {
		parent::__construct($parent, $name);
		$this->contentRenderer = array($this,"renderContent"); // Default renderer
	}



	/**************************************************************************/
	/*                            Main methods                                */
	/**************************************************************************/

	/**
	 * Factory for components
	 * @param string $name
	 */
	function createComponent($name) {
		$this->getContent(); // This will also create content Components
	}

	/**
	 * Default renderer
	 */
	function renderContent() {
		$content = $this->getContent();
		if($content instanceof IComponent or $content instanceof ITemplate) {
			$content->render();
		}
		else
			echo (string)$content;
	}

	/**
	 * Creates (if need) content and returns
	 * @return IComponent
	 */
	function getContent() {
		if($this->content === null) {
			if(is_callable($this->contentFactory, FALSE)) {
				// Callback
				$component = call_user_func_array($this->contentFactory, array($this->name, $this));

				if($component instanceof IComponent) {
					$name = $this->name;
					if($component->name !== null) $name = $component->name;

					if($component->parent === null)
						$this->addComponent($component, $name);
					if($component->parent !== $this)
						throw new InvalidStateException("Component must be registred to \"Item\" control");
				}
				$this->content = $component;
			}
			else
				throw new InvalidStateException("Factory callback is not callable!");
		}
		return $this->content;
	}



	function setContent($content) {
		$this->content = $content;
	}



	/**
	 * Renders component
	 */
	function render() {
		if (SnippetHelper::$outputAllowed OR $this->hasSnippets) {
			if(is_callable($this->contentRenderer, FALSE)) {
				call_user_func_array($this->contentRenderer, array($this));
			}
			else throw new InvalidStateException("Renderer callback is not callable!");
		}
	}

	/**
	 * Generates URL to presenter, action or signal.
	 *
	 * This will try to generate URL on $this component. If fails try to generate url on handlerComponent.
	 * @param  string   destination in format "[[module:]presenter:]action" or "signal!"
	 * @param  array|mixed
	 * @return string
	 * @throws InvalidLinkException
	 */
	public function link($destination, $args = array()) {
		if (!is_array($args)) {
			$args = func_get_args();
			array_shift($args);
		}
		return $this->parent->handlerComponent->link($destination, $args);
	}
}