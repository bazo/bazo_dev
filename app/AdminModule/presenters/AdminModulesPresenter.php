<?php
class Admin_ModulesPresenter extends Admin_SecurePresenter
{
	public function actionDefault()
	{
            $this->view = 'modules';
	}
	
	public function createComponentPage($name)
	{
            $page = new Page($this,$name);
            $item = $page->addItem("Link");
            $item->contentFactory = array($this,'createAddNewLink');
            $item = $page->addItem("modules");
            $item->contentFactory = array($this,'createComponentModulesGrid');
            $item->hasSnippets = true;
	}

	public function createAddNewLink($name, $page)
	{
            $link = Html::el('a')->href($this->link('install!'))->class('ajax')->add(Html::el('div')->id('create-new-link')->add(Html::el('div')->id('icon-install'))->add(Html::el('span')->add('Install')));
            return $link;
	}
	//GRID
	public function createComponentModulesGrid($name, $page)
	{
            $grid = new DataGrid($page, $name);
            $grid->rememberState = TRUE; // povolí ukládání stavů komponenty do session
            $grid->timeout = '+ 7 days';
            $ds = $this->model('Modules')->getDs();
            $grid->bindDataTable($ds);

            $grid->keyName = 'module_name';
            $grid->addColumn('module_name', 'Name')->addFilter();

            $grid->addColumn('status', 'Status')->addSelectboxFilter(array('active' => "Active", 'disabled' => "Disabled"), TRUE);
            $grid->addActionColumn('Actions');
            $grid->addAction('StatusToggle', 'changeStatus!', null, $useAjax = TRUE);

            $grid->addAction('Delete', 'confirmForm:confirmDelete!', Html::el('span')->class('icon icon-explode'), $useAjax = TRUE);
            $renderer = $grid->getRenderer();
            $renderer->paginatorFormat = '%input%';
            $renderer->onActionRender[] = array($this, 'formatActions');

            $grid->setRenderer($renderer);
            return $grid;
	}

        public function formatActions(Html $action, DibiRow $data)
        {
          if($action->attrs['title'] == 'StatusToggle')
          {
              if($data->status == 'enabled') {
                  $action->attrs['title'] = 'Disable';
                  $action->removeChildren();
                  $action->add(Html::el('span')->class('icon icon-stop'));
                  $action->href = $action->href.'&new_status=disabled';
              }
              if($data->status == 'disabled') {
                  $action->attrs['title'] = 'Enable';
                  $action->removeChildren();
                  $action->add(Html::el('span')->class('icon icon-play'));
                  $action->href = $action->href.'&new_status=enabled';
              }

              return $action;
          }
        }

	function createComponentConfirmForm()
        {
            $form = new ConfirmationDialog();
            $form->addConfirmer(
                        'delete', // název signálu bude 'confirmDelete!'
                        array($this, 'deleteModule'), // callback na funkci při kliku na YES
                        array($this, 'questionDelete') // otázka (může být i callback vracející string)
                        );
            return $form;
	}
	
	public function questionDelete($dialog, $params)
	{
            return 'Really delete module '.$params['module_name'].'?';
	}
	
	public function deleteModule($params, $dialog)
	{
            try{
                    $this['page']->redraw('grid');
                    $this->flash('Page '.$title.' deleted!');
                    if(!$this->isAjax()) {$this->redirect('Pages:');}
            }
            catch(DibiDriverException $e)
            {
                    $this->flash($e->getMessage());
            }	
	}

	public function handleChangeStatus($module_name)
	{
            $params = $this->getRequest()->getParams();
            $new_status = $params['new_status'];
            ModuleManager::changeStatus($module_name, $new_status);
            $this['page']->refresh('modules');
            if($new_status == 'enabled')
            {
                Environment::getApplication()->installModules();
                $this->invalidateControl('menu');
            }
            else $this->redirect('this');
	}
	
	public function handleInstall()
    {
        $this->view = 'installer';
        $this->invalidateControl('form');
    }
    
    public function createComponentFormModuleInstaller($name)
    {
        $form = new LiveForm($this, $name);
        $form->addFile('file', 'Select file')->addCondition(Form::FILLED, 'Please select a file');
        $form->addButton('btnClose', 'Close');
        $form->addSubmit('btnSubmit', 'Install')->onClick[] = array($this, 'formModuleInstallerSubmitted');
        return $form;
    }
    
    public function formModuleInstallerSubmitted(Button $button)
    {
        $form = $button->getForm();
        $values = $form->getValues();
        unset($values['btnClose']);
        $file = $values['file'];
        if($file->isOK())
        {
            $file->move(MODULES_DIR.'/'.$file->getName());
            
        }
    }
	
}
?>
