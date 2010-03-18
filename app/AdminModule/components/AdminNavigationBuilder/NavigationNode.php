<?php
/**
 * NavigationNode control, base of NavigationBuilder
 *
 * @author     Karel Klima
 * @copyright  Copyright (c) 2009 Karel Klíma
 * @package    Nette Extras
 */
class AdminNavigationNode extends Control
{
	/** @var string */
	public $label;
	/** @var string */
	public $url;
	/** @var ArrayList */
	public $items;

	/**
	 * Navigation item setup
	 * @param string $label
	 * @param string $url
	 */
	public function __construct($label, $url)
	{
		$this->url = $url;
		$this->label = $label;
		$this->items = new ArrayList();
	}

	/**
	 * Adds an item to the navigation tree
	 * @param string $label
	 * @param string $url
	 * @return NavigationNode
	 */
	public function add($label, $url)
	{
		$this->items[] = new AdminNavigationNode($label, $url);
		return $this;
	}

	/**
	 * Gets an item from the navigation tree
	 * @param string $label
	 * @return NavigationNode|FALSE false if item not found
	 */
	public function get($label)
	{
		foreach ($this->items as $item) {
			if ($item->label == $label) return $item;
		}
		$error = 'item '.$label.' not found';
		throw new Exception($error);
	}
	/**
	 * Gets an item from the naviagtion tree RECURSIVE, finds it anywhere
	 * @param object $label, $items
	 * @return NavigationNode|FALSE false if item not found
	 */
	private function find($label, $items)
	{
        echo 'kokoot';
		//$result = new AdminNavigationNode("","");
		foreach ($items as $item) {
			if ($item->label == $label) $result = $item;
			else
			{
				if(count($item->items) > 0)
				{
					$result = $this->find($label, $item->items);
				}
			}
		}
        if($label == 'Users') {var_dump($result);exit;}
		return $result;
	}
	/**
	 * Gets an item from the naviagtion tree RECURSIVE, finds it anywhere
	 * @param object $label
	 * @return NavigationNode|FALSE false if item not found
	 */
	public function getR($label)
	{
        var_dump($label);exit;
		return $this->find($label, $this->items);
	}

	/**
	 * Removes an item (or items) from the navigation tree
	 * @param string $label
	 * @param string $url
	 * @return NavigationNode
	 */
	public function remove($label)
	{
		foreach ($this->items as $item) {
			if ($item->label == $label) $this->items->remove($item);
		}
		return $this;
	}
}