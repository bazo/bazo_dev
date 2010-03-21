<?php
class Admin_MenuItemsPresenter extends Admin_SecurePresenter
{
	private /*$menu_items, $name,*/ $menu;
	
	public function actionDefault($id)
	{
		$this->menu = $this->model('menu')->getById($id);
		$this->template->menu = $this->menu->name;
		$this->template->menu_id = $this->menu->id;
		$this->view = 'menuitems';
		$this->invalidateControl('form');
	}
	
	public function handleItemMove()
	{
		$data = $this->getRequest()->getPost();
		$menu_id = $this->menu->id;
		if ($this->isAjax()) {
			$item_id = $data['item_id'];
			$parent_id = $data['parent_id'];
			$prev_position = $data['prev_position'];
			$parent_level = $data['parent_level'];
			$level = $data['level'];
			$diff = $parent_level - $level;
			$this->model('menuItems')->moveItem( (int)$item_id, (int)$menu_id, (int)$parent_id, (int)$prev_position, (int)$parent_level, $diff);
			$this->invalidateControl('menuDragDrop');
			$this->flash('Menu updated');
		}
	}
	
	public function handleItemDelete()
	{
		$data = $this->getRequest()->getPost();
		$item = (int)$data['item'];
		try
		{
			$this->model('menuItems')->deleteItem($item);
			$this->presenter->flashMessage('item '.$item.' deleted');
			$this->invalidateControl('menuDragDrop');
		}
		catch (DibiDriverException $e)
		{
			$this->flash($e->getMessage());
		}
	}
	
	public function createComponentFormNewMenuItem($name)
	{
		$form = new LiveForm($this, $name);
		$form->addGroup('Add new item');
        
		    $form->addText('title', 'Title')->addRule(Form::FILLED, 'Please enter a name.');
            
		    $pages = $this->model('pages')->getPairs();
		    $temp = array();
		    if (isset($pages[""]))
		    {
			    $temp = $pages[""];
			    unset($pages[""]);
		    }
		    $pages = array('none' => $temp) + $pages;
		    unset($temp);
		    $categories = $this->model('categories')->getPairs('slug', 'title');
		    $items = array('homepagelink'=> 'Link to Homepage');
		    foreach (array('none' => 'none') + $categories as $cat_slug => $cat_title) {
			    if($cat_title != 'none') $items[$cat_slug] = 'cat: '.$cat_title;
			    if( isset($pages[$cat_title]) ){
				    foreach ($pages[$cat_title] as $page_title => $data) {
					    if($cat_title == 'none')
						    $items[$data->page_slug] = 'page '.$page_title;		
					    else
						    $items[$cat_slug.'/'.$data->page_slug] = 'article in '.$cat_title.': '.$page_title;				}
			    }
		    }
		    $form->addSelect('url', 'Page', $items)->addRule(Form::FILLED, 'Please select a page.');
		    
		    $items = $this->model('menuItems')->getByMenuId($this->menu->id)->fetchPairs('id_level','title');
		    $items = array_merge(array('0;0' => 'none'), $items);
		    $form->addSelect('parent_data', 'Parent', $items);
                    $form->addButton('btnClose','Close');
		$form->addSubmit('save','Add')->onClick[] = array($this, 'formNewMenuItemSubmitted');
                $form['save']->getControlPrototype()->class('ajax');
		return $form;
	}
	
	public function formNewMenuItemSubmitted(Button $button)
	{
        $form = $button->getForm();
		$values = $form->getValues();
		$parent_data = explode(';',$values['parent_data']);
		$parent_id = $parent_data[0];
		$parent_level = $parent_data[1];
		unset($values['parent_data']);
        unset($values['btnClose']);
		$values['parent'] = $parent_id;
		$values['level'] = $parent_level + 1;
		$values['menu_id'] = $this->menu->id;
		try
		{
			$this->model('menuItems')->create($values);
			$this->invalidateControl('menuDragDrop');
			$this->invalidateControl('frmNewItem');
			$this->validateControl('formNewMenuItem');
			$this->flash('Item '.$values['title'].' created');
			//if(!$this->isAjax()) $this->redirect('this');
		}
		catch (DibiDriverException $e)
		{
			$this->flash($e->getMessage());
			
		}
		//$this->redirect('this');
	}
	
	public function createComponentMenuDragDrop()
	{
		$dragdrop = new MenuDragDrop();
		$dragdrop->setModel(new Admin_MenuItemsModel());
		$dragdrop->setTranslator(new Translator($this->lang));
		$dragdrop->onItemMove[] = array($this, 'handleItemMove');
		$dragdrop->onItemDelete[] = array($this, 'handleItemDelete');
		return $dragdrop;
	}
}
?>