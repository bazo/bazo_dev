<?php

/**
 * NavigationBuilder
 * 
 * Works best with Nette Framework
 *
 * This source file is subject to the New BSD License.
 *
 * @copyright  Copyright (c) 2009 Karel Klima, modified by Martin Bazik
 * @license    New BSD License
 * @package    Nette Extras
 * @version    NavigationBuilder 1.0, 2009-10-18
 */


/**
 * NavigationBuilder control
 *
 * @author     Karel Klima
 * @copyright  Copyright (c) 2009 Karel Klíma
 * @package    Nette Extras
 */
class AdminNavigationBuilder extends NavigationNode
{
	public function __construct()
	{
		$this->items = new ArrayList();
	}
	
	/**
	 * Shortcut for Template::setFile();
	 * @param string $file
	 * @return NavigationBuilder
	 */
	public function setTemplate($file)
	{
		$this->template->setFile($file);
		return $this;
	}
	
	/**
	 * Shortcut for Template::setTranslator();
	 * @param ITranslator $translator
	 * @return NavigationBuilder
	 */
	public function setTranslator(ITranslator $translator)
	{
		$this->template->setTranslator($translator);
		return $this;
	}
	
	/**
	 * Renders navigation
	 * @return void
	 */
	public function render()
	{
		// Puts navigation items into the template
		$this->template->items = $this->items;
		
		if (!is_file($this->template->getFile())) {
			$helpers = $this->template->getHelpers();
			// Sets default template according to availability of ITranslator
			if (isset($helpers['translate'])) {
				$this->template->setFile(dirname(__FILE__) . '/template_translate.phtml');
			} else {
				$this->template->setFile(dirname(__FILE__) . '/template.phtml');
			}
		}
			
		$this->template->render();
	}
}