<?php
class Admin_PagesPresenter extends Admin_SecurePresenter
{
    protected $item, $html;
    private $categories, $new = false, $edit = false;
    
    public function actionDefault()
    {
        $this->view = 'pages';
        $this->template->new = $this->new;
        $this->template->edit = $this->edit;
    }
    
    public function createComponentPage($name)
    {
        $page = new Page($this,$name);

        $item = $page->addItem("Link");
        $item->contentFactory = array($this,'createAddNewLink');

        $item = $page->addItem("Pages");
        $item->contentFactory = array($this,'createComponentPagesGrid');
        $item->hasSnippets = true;    
    }
    
    public function createAddNewLink($name, $page)
    {
        $link = Html::el('a')->href($this->link('newPage!'))->class('ajax')->add(Html::el('div')->id('create-new-link')->add(Html::el('div')->id('icon'))->add(Html::el('span')->add('Create new')));
        return $link;
    }
    
    //GRID
    public function createComponentPagesGrid($name, $page)
    {
        $grid = new DataGrid($page, $name);
        $grid->rememberState = TRUE; // povolí ukládání stavů komponenty do session
        $grid->timeout = '+ 7 days';
        $ds = $this->model('Pages')->getDs();
        $grid->bindDataTable($ds);

        $grid->keyName = 'title';
        $grid->addColumn('homepage', '');
        $grid['homepage']->getHeaderPrototype()->class('homepage-column');
        $grid['homepage']->getCellPrototype()->class('homepage-column');;
        $grid['homepage']->formatCallback[] = array($this, 'homepageCallback');
        //$grid['creditLimit']->getCellPrototype()->style('text-align: center');
        $grid->addColumn('title', 'Title')->addFilter();

        $categories = $this->model('categories')->getPairs();
        $grid->addColumn('category', 'Category')->addSelectboxFilter($categories);
        $this->categories = array('0' => 'none') + $categories;
        $grid['category']->formatCallback[] = array($this, 'gridCategoryCallback');
        $grid['title']->formatCallback[] = array($this, 'createLink');

        $grid->addColumn('template', 'Template')->addSelectboxFilter();
        $grid['template']->getCellPrototype()->style('text-align: center');
        $grid['template']->formatCallback[] = array($this, 'templateFormatCallback');
        
        $grid->addColumn('published', 'Published')->addSelectboxFilter(array('0' => "No", '1' => "Yes"), TRUE);
        $grid['published']->getCellPrototype()->style('text-align: center');
        $grid['published']->formatCallback[] = array($this, 'publishFormatCallback');
        $grid->addDateColumn('publish_time', 'Publish time', '%d.%m.%Y %H:%M:%S');
        $grid['publish_time']->getHeaderPrototype()->style('text-align: center');
        $grid['publish_time']->getCellPrototype()->style('text-align: center');
        $grid->addActionColumn('Actions');
        $grid->addAction('Edit', 'editPage!', Html::el('span')->class('icon icon-edit'), $useAjax = TRUE);
        $grid->addAction('Delete', 'confirmForm:confirmDelete!', Html::el('span')->class('icon icon-delete'), $useAjax = TRUE);
        $renderer = $grid->getRenderer();
        $renderer->paginatorFormat = '%input%';
        $grid->setRenderer($renderer);
        return $grid;
    }

    public function templateFormatCallback($value, DibiRow $data)
    {
        $templates = $this->model('contentTemplates')->get('page');   
        $select = Html::el('select')->class('template-change')->{'data-id'}($data->id);
        foreach($templates as $template)
        {
            if($value == $template)
            $select->create('option')->value($template)->setText($template)->selected('selected');
            else $select->create('option')->value($template)->setText($template);
        }
        return $select;
    }
    
    public function homepageCallback($value, DibiRow $data) {
        if($value == 1) return Html::el('span')->class('icon icon-homepage')->title('Homepage');//->add('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;');
        if($value == 0) return Html::el('a')->add(Html::el('span')->class('icon icon-makehomepage')->title('Make homepage'))->href($this->link('makeHomepage!').'&title='.$data->title)->class('datagrid-ajax');
    }

    public function publishFormatCallback($value, DibiRow $data)
    {
        $checkbox = Html::el('input')->type('checkbox')->class('chboxPublished')->{'data-id'}($data->id);
        if ($value) $checkbox->checked = TRUE;
        return (string) $checkbox;
    }
    
    public function gridCategoryCallback($value, DibiRow $data)
    {
        $selected_cat = (int)$value;
        $select = Html::el('select')->class('category-change')->{'data-id'}($data->id);
        foreach($this->categories as $key => $category)
        {
            if($selected_cat == (int)$key)
            $select->create('option')->value($key)->setText($category)->selected('selected');
            else $select->create('option')->value($key)->setText($category);
        }
        return $select;
    }
    
    public function handleChangeCategory($page, $new_cat)
    {
        $id = (int)$page;
        $new_cat = $new_cat;
        fd($page, $new_cat);
        try{
            $this->model('pages')->update(array('id'=>$id, 'category'=>$new_cat));
            $this->flash('Category updated!');
        }
        catch(DibiDriverException $e)
        {
            $this->flash($e->getMessage());
        }
        $this->sendPayload();
    }
    
    public function handleChangeTemplate($page, $template)
    {
        $id = (int)$page;
        try{
            $this->model('pages')->update(array('id'=>$id, 'template'=>$template));
            $this->flash('Template updated!');
        }
        catch(DibiDriverException $e)
        {
            $this->flash($e->getMessage());
        }
        $this->sendPayload();
    }
    
    public function handleChangePublished()
    {
        $data = $this->getRequest()->getPost();
        $id = (int)$data['page'];
        $published = ($data['published'] == 'true') ? 1 : 0;
        $this->model('pages')->update(array('id'=>$id, 'published'=>$published));
        if($published == 1) $this->flash('Page published!');
        if($published == 0) $this->flash('Page set as draft!');
        $this->sendPayload();
    }
    
    public function handleNewPage()
    {
        $this->template->new = true;
        $this->invalidateControl('form');
        $this->flashCmd('openForm');    
    }

    public function handleMakeHomepage($title)
    {
        $this->model('pages')->makeHomepage($title);
        $this['page']->refresh('Pages');
    }

    public function createLink($value, DibiRow $data)
    {
        return Html::el('a',$value)->href($this->link('editPage!',array('title' => $data['title'])))->class('inline-edit ajax');
    }
    
    function createComponentConfirmForm()
    {
        $form = new ConfirmationDialog();
        $form->addConfirmer(
                        'delete', // název signálu bude 'confirmDelete!'
                        array($this, 'deletePage'), // callback na funkci při kliku na YES
                        array($this, 'questionDelete') // otázka (může být i callback vracející string)
                        );
        return $form;
    }
    
    public function questionDelete($dialog, $params)
    {
        return 'Really delete Page '.$params['title'].'?';
    }
    
    public function deletePage($params, $dialog)
    {
        $title = $params;
        $webalized_title = String::webalize($title);
        try{
            $this->model('pages')->delete($title);
            $this->model('menuItems')->deleteRelated($webalized_title);
            $this['page']->redraw('Pages');
            $this->flash('Page '.$title.' deleted!');
            if(!$this->isAjax()) {$this->redirect('Pages:');}
        }
        catch(DibiDriverException $e)
        {
            $this->flash($e->getMessage());
        }    
    }

    public function handleEditPage()
    {
        $session = Environment::getSession('params');
        $session->params = $this->getRequest()->getParams();
        $this->template->edit = true;
        $this->invalidateControl('form');
        $this->flashCmd('openForm');
    }
    
    
    //END GRID
    
    //Form New Page
    
    public function createComponentFormNewPage($name)
    {
        $form = new LiveForm($this, $name);
        $form = $this->formNewEditFields($form);
        $form->addSubmit('btnSaveAsDraft', 'Save as draft')->onClick[] = array($this, 'formNewPageSave');
        $form->addSubmit('btnPublish', 'Publish')->onClick[] = array($this, 'formNewPageSave');
        $form['btnSaveAsDraft']->getControlPrototype()->class('ajax');
        $form['btnPublish']->getControlPrototype()->class('ajax');
        return $form;
    } 
    
    public function createComponentFormEditPage($name)
    {
        $session = Environment::getSession('params');
        $title = ($this->getParam('title') == '') ? $session->params['title'] : $this->getParam('title');
        
        if($title == '')
        {
            return 'Please select a page to edit.';
        }
        else{
            $form = new LiveForm($this, $name);
            $data = array();
            if($this->isAjax())$data = $this->model('pages')->getByTitle($title);
            $form = $this->formNewEditFields($form);
            $form->addHidden('id', $data->id);
            $form->addSubmit('btnSaveAsDraft', 'Save as draft')->onClick[] = array($this, 'formEditSubmitted');
            $form->addSubmit('btnPublish', 'Save')->onClick[] = array($this, 'formEditSubmitted');
            $form['btnSaveAsDraft']->getControlPrototype()->class('ajax');
            $form['btnPublish']->getControlPrototype()->class('ajax');
            $form->setDefaults( (array)$data);
            return $form;
        }
    } 
    
    private function formNewEditFields($form)
    {
        $form->addGroup('Content')->setOption('container', Html::el('fieldset')->id('contents'));
            $form->addText('title','Title')->addRule(Form::FILLED, 'Fill title');
            
            $form->addTextarea('content', 'Text');
            $templates = $this->model('contentTemplates')->get('page');
        $form->addGroup('Properties')->setOption('container', Html::el('fieldset')->id('properties'));
            $categories = $this->model('categories')->getPairs();
            $categories = array('0' => 'none') + $categories;
            $form->addSelect('category', 'Category', $categories);
            $form->addCheckBox('homepage', 'Set as homepage?');
            $form->addSelect('template', 'Template', $templates);
            $form->addCheckBox('has_widgets', 'Has widgets');
            $form->addText('publish_time', 'Publish time');
            //$form->addText('time', '');
            $form->addText('keywords', 'keywords');
            $form->addTextarea('description', 'description');
        $form->addHidden('content_type')->setValue('page');
        if($form->name == 'formNewPage')
        {
            //$form->addGroup('Link')->setOption('container', Html::el('fieldset')->id('link'));
                $menus = $this->model('menu')->getPairs();
                $menus = array(0 => 'none') + $menus;
                $form->addSelect('link','Link in menu', $menus);
        }
        $form->addGroup('')->setOption('container', Html::el('fieldset')->id('buttons'));
            $form->addButton('btnClose', 'Close');
            $form->addSubmit('btnPreview', 'Preview')->onClick[] = array($this, 'formNewPagePreview');
            $form['btnPreview']->getControlPrototype()->class('ajax');
        return $form;
    }
    
    public function formNewPagePreview(Button $button)
    {
        $form = $button->getForm();
        $values = $form->getValues();
        try{
            $request = new PresenterRequest('Front:Preview', 'POST', array('action' => 'preview'), array('values' => $values));
            $presenter = new Front_PreviewPresenter();
            $response = $presenter->run($request);
            ob_start();
            $response->send();
            $html = ob_get_clean();
            $html = preg_replace('~(\<a.*href\=")(?:.*)(".*\>)~iU', '$1#$2', $html);
            $session = Environment::getSession('preview');
            $session->html = $html;
            
            $this->template->response = $html;
            $this->view = 'preview_wrap';
            
            $this->refreshConfig(); //preview changes theme, could be fixed by using admin_theme, front_theme
            if(!$this->isAjax())
            {
                
            }
            else
            {
                $this->invalidateControl('preview');
                $this->flashCmd('preview');
            }
        }
        catch(DibiDriverException $e)
        {
            $this->flash($e->getMessage());
        }
    }
    
    public function formClose(Button $button)
    {
        $this->invalidateControl('form');
    }
    
    public function handlePreview()
    {
        $session = Environment::getSession('preview');
        $html  = $session->html;
        $session->remove();
        $this->view = 'preview';
        $files = $this->formatTemplateFiles($this->getName(), $this->view);
        foreach ($files as $file) 
        {
            if (is_file($file)) {
                $this->template->setFile($file);
                break;
            }
        }
        $this->template->response = $html;
        $this->getTemplate()->render();
        $this->terminate();
    }
    
    public function createComponentFormPreviewToolbar($name)
    {
        $form = new LiveForm($this, $name);
        $renderer = $form->getRenderer();
        $renderer->wrappers['controls']['container'] = null;;
        $renderer->wrappers['pair']['container'] = NULL;
        $renderer->wrappers['label']['container'] = null;
        $renderer->wrappers['control']['container'] = null;
        $form->addButton('btnClose', 'Close');
        $form->addSubmit('btnUpdate', 'Update')->onClick[] = array($this, 'formPreviewToolbarUpdate');
        $form['btnUpdate']->getControlPrototype()->class('ajax');
        return $form;
    } 
    
    public function formPreviewToolbarCancel(Button $button)
    {
        $this->flashCmd('closePreview');
        $this->sendPayload();
    }
    
    public function formPreviewToolbarUpdate(Button $button)
    {
        $this->flashCmd('copyToEditor');
        $this->flashCmd('closePreview');
        $this->sendPayload();
    }
    
    public function formNewPageSave(Button $button)
    {
        $form = $button->getForm();
        $values = $form->getValues();
        unset($values['btnClose']);
        if($form['btnPublish']->isSubmittedBy()) $values['published'] = 1;
        if($form['btnSaveAsDraft']->isSubmittedBy()) $values['published'] = 0;
        try{
            $this->model('pages')->save($values);
            $this->flash('Page created');
            if(!$this->isAjax())$this->redirect('Pages:');
            else
            {
                $this->invalidateControl('form');
                $this['page']->refresh('Pages');
            }
        }
        catch(DibiDriverException $e)
        {
            $this->flash($e->getMessage());
        }
    }
    //END Form New Page
    
    public function formEditSubmitted(Button $button)
    {
        $session = Environment::getSession('params')->remove();
        $form = $button->getForm();
        if ($form->isValid()) {
            $values = $form->getValues();
            unset($values['btnClose']);
            try{
                $this->model('pages')->update($values);
                $this->flash('Page updated');
                if(!$this->isAjax())$this->redirect('Pages:');
            else
            {
                $this->invalidateControl('form');
                $this['page']->refresh('pages');
            }
            }
            catch(DibiDriverException $e)
            {
                $this->flash($e->getMessage());
            }
        }
    }
}
?>
