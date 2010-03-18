<?php
class Admin_CategoriesPresenter extends Admin_SecurePresenter
{
	protected $item;
	
	public function actionDefault()
	{
		$this->view = 'categories';
                $this->template->edit = false;
	}
	
	public function createComponentPage($name)
	{
            $page = new Page($this,$name);

            $item = $page->addItem("Link");
            $item->contentFactory = array($this,'createAddNewLink');

            $item = $page->addItem("categories");
            $item->contentFactory = array($this,'createComponentCategoriesGrid');
            $item->hasSnippets = true;
	}

    public function createAddNewLink($name, $page)
    {
        $link = Html::el('a')->href($this->link('newCategory!'))->class('ajax')->add(Html::el('div')->id('create-new-link')->add(Html::el('div')->id('icon'))->add(Html::el('span')->add('Create new')));
        return $link;
    }

	//GRID
	public function createComponentCategoriesGrid($name, $page)
	{
            $grid = new DataGrid($page, $name);
            $ds = $this->model('categories')->getDs();
            $grid->bindDataTable($ds);

            $grid->keyName = 'id';
            $grid->addColumn('title', 'Title')->addFilter();

            $grid->addActionColumn('Actions');
            $grid->addAction('Edit', 'editCategory!', Html::el('span')->class('icon icon-edit'), $useAjax = TRUE);
            $grid->addAction('Delete', 'confirmForm:confirmDelete!', Html::el('span')->class('icon icon-delete'), $useAjax = TRUE);

            return $grid;
	}
	
	function createComponentConfirmForm()
        {
            $form = new ConfirmationDialog();
            $form->addConfirmer(
                            'delete', // název signálu bude 'confirmDelete!'
                            array($this, 'deleteCategory'), // callback na funkci při kliku na YES
                            'Really delete category?'  // otázka (může být i callback vracející string)
                            );
            return $form;
	}
	
	public function deleteCategory($params, $dialog)
	{
            $id = $params;
            try{
                    $this->model('categories')->delete($id);
                    $this->flash('Category deleted!');
                    if(!$this->isAjax()) $this->redirect('Categories:');
                    else
                    {
                            $this['page']->refresh('categories');
                    }
            }
            catch(DibiDriverException $e)
            {
                    $this->flash($e->getMessage());
            }
		
	}
	//END GRID
		
	//Form New Category
	public function createComponentFormCategory($name)
	{
            $form = new LiveForm($this, $name);
            $form->addText('title','Title')->addRule(Form::FILLED, 'Fill title');
            try{
                    $categories = $this->model('categories')->getIdLevel();
            }
            catch(DibiDriverException $e)
            {
                    $this->flash($e->getMessage());
                    $categories = array();
            }
            $categories = array('0;0' => 'none') + $categories;
            $form->addSelect('parent', 'Parent', $categories);
            $form->addTextarea('description', 'Description');
            $templates = array('category' => 'default');
            $form->addSelect('template', 'template', $templates);
            $form->addButton('btnClose', 'Close');
            $form->addSubmit('btnSave', 'Save')->onClick[] =array($this, 'formNewCategorySubmitted');
	    return $form;
	} 
	
	public function formNewCategorySubmitted($button)
	{
            $form = $button->getForm();
            $values = $form->getValues();
            unset($values['btnClose']);
            try
            {
                $this->model('categories')->save($values);
                $this->flash('Category '.$values['title'].' created');
                if(!$this->isAjax())$this->redirect('Categories:');
                else
                {
                    $this['page']->refresh('categories');
                    $this->invalidateControl('form');
                }
            }
            catch(DibiDriverException $e)
            {
                    $this->flash($e->getMessage());
            }
	}
	//END Form New Category
    public function formClose(Button $button)
    {
        $this->invalidateControl('form');
    }

    public function handleNewCategory()
    {
        $this->template->edit = true;
        $this->invalidateControl('form');
        $this->flashCmd('openForm');
    }

    public function handleEditCategory()
    {
        $this->template->edit = true;
        $this->invalidateControl('form');
        $this->flashCmd('openForm');
    }
}
?>