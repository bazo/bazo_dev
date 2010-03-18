<?php
class Menu extends Control
{
	protected $model, $translator, $navigation;
	public $url;
	public function __construct()
	{
		$this->model = new Front_MenuModel();
		$this->navigation = new NavigationBuilder();
		return $this;
	}
	
	public function setTranslator(ITranslator $translator)
	{
		$this->translator = $translator;
		return $this;
	}
	
	public function render($menu_name)
	{
		$template = $this->createTemplate();
		$this->navigation->template->setTranslator($this->translator);
		$menu = $this->model->getByName($menu_name);
        if($menu->template != '') $this->navigation->setTemplate(APP_DIR.$this->getPresenter()->pathToTheme.'/templates/Menus/menu-'.$menu->template.'.phtml');
        $this->navigation->template->url = $this->url;
        if($menu != false)
		{
			$items = $this->fillMenu($menu->id);
			$this->fillNavigation($items);
			$this->navigation->render();
		}
	}
		
	private function fillMenu($menu_id, $parent = 0)
	{
		return $this->model->getMenuItems($menu_id);
		
	}
	
	private function fillNavigation($items)
	{
		foreach($items as $key => $item)
		{
			$lang = $this->getPresenter()->lang;
			$parts = explode('/', $item->url);
			if($item->level == 1)
			{
                if($item->url == 'homepagelink') 
                    $this->navigation->add($item->title, $this->getPresenter()
                    ->link(':Front:HomePage:homepage'));
				elseif ( count($parts) == 1 ) 
					$this->navigation->add($item->title, $this->getPresenter()
					->link(':Front:page:categoryView', array('category' => $item->url, 'lang' => $lang)));
				elseif ( count($parts) == 2 ) 
					$this->navigation->add($item->title, $this->getPresenter()
					->link(':Front:page:pageView', array('category' => $parts[0], 'page' => $parts[1], 'lang' => $lang)));
			}
			else 
			{
				try{
                    if($item->url == 'homepagelink') 
                        $this->navigation->getR($item->parent)->add($item->title, $this->getPresenter()
                        ->link(':Front:HomePage:homepage'));
					elseif ( count($parts) == 1 ) 
						$this->navigation->getR($item->parent)->add($item->title, $this->getPresenter()
						->link(':Front:page:categoryView', array('category' => $item->url, 'lang' => $lang)));
					elseif ( count($parts) == 2 ) 
						$this->navigation->getR($item->parent)->add($item->title, $this->getPresenter()
						->link(':Front:page:pageView', array('category' => $parts[0], 'page' => $parts[1], 'lang' => $lang)));
				}
				catch(Exception $e)
				{
					echo $e->getMessage();
				}
				
			}
		}
			
	}
}
?>
