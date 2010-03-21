<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of AdminUsersPresenter
 *
 * @author Martin
 */
class Users_UsersPresenter extends AdminBaseModulePresenter{
    
    public function createComponentUsersCss($name)
    {
       $css = parent::createComponentCss($name);
       $css->sourcePath = dirname(__FILE__).'/../css' ;
       return $css;
    }
    
    public function actionDefault()
    {
        $this->template->edit = false; 
    }
    
    public function createComponentPage($name)
    {
        $page = new Page($this,$name);

        $item = $page->addItem("Link");
        $item->contentFactory = array($this,'createAddNewLink');

        $item = $page->addItem("grid");
        $item->contentFactory = array($this,'createComponentUsersGrid');
        $item->hasSnippets = true;
    }

    public function createAddNewLink($name, $page)
    {
        $link = Html::el('a')->href($this->link('newUser!'))->class('ajax')->add(Html::el('div')->id('create-new-link')->add(Html::el('div')->id('icon'))->add(Html::el('span')->add('Create new')));
        return $link;
    }

    public function createComponentUsersGrid($name, $item)
    {
        $grid = new DataGrid($this, $name);
        $model = new UsersModuleModel();
        $ds = $model->getDs();
        $grid->bindDataTable($ds);
        return $grid;
    }

    public function createComponentFormUser($name)
    {
        $form = new LiveForm($this, $name);
        $form->addText('name', 'Name')->addRule(Form::FILLED);
        $form->addText('login', 'Login')->addRule(Form::FILLED);
        $form->addText('email', 'E-mail')->addRule(Form::EMAIL);
        $form->addButton('btnClose', 'Close');
        $form->addSubmit('btnSave', 'Save');
        return $form;
    }

    public function handleNewUser()
    {
        $this->template->edit = true;
        $this->invalidateControl('form'); 
    }
    
    private function gatherActions()
    {
        $service = Environment::getService('Nette\Loaders\RobotLoader');
        $class_list = $service->list;
        $actions = array();
        foreach($class_list as $class => $file)
        {   
            //zachtime annotation exception lebo nette si generuje nejake annotation claasy do robotloodera
            try{
                $r = new ReflectionClass($class);
                if($r->isSubclassOf('Admin_SecurePresenter') && $r->getName() != 'BaseModulePresenter')
                {
                     $methods = $r->getMethods(ReflectionMethod::IS_PUBLIC);
                     foreach($methods as $method)
                     {
                         if(String::lower($method->class) == $class)
                         {
                             if( strpos($method->getName(), 'action') !== false || strpos($method->getName(), 'handle') !== false)
                             {
                                 $actions[$class][] = $method->getName();
                             }
                         }
                     }    
                }
            }
            catch(ReflectionException $e) {}
        }
        $actions = array_merge($actions, Environment::getApplication()->getModulesPermissions());
        $model = new UsersModuleModel();
        $model->saveActions($actions);
        return $actions;    
    }
    
    function createComponentConfirmForm()
        {
            $form = new ConfirmationDialog();
            $form->addConfirmer(
                            'delete', // název signálu bude 'confirmDelete!'
                            array($this, 'deleteRole'), // callback na funkci při kliku na YES
                            'Really delete role? User will be left without any privileges!!'  // otázka (může být i callback vracející string)
                            );
            return $form;
    }
    
    public function createComponentDatagridRoles($name)
    {
        $grid = new DataGrid($this, $name);
        $model = new UsersModuleModel();
        $grid->bindDataTable($model->getRolesDs());
        
        $grid->keyName = 'id';
        $grid->addColumn('name', 'Name')->addFilter();

        $grid->addActionColumn('Actions');
        $grid->addAction('Permissions', 'editPermissions!', Html::el('span')->class('icon icon-edit'), $useAjax = TRUE);
        $grid->addAction('Delete', 'confirmForm:confirmDelete!', Html::el('span')->class('icon icon-delete'), $useAjax = TRUE);
        
        return $grid;
    }
    
    public function createComponentFormPermissionEditor($name)
    {
        $model = new UsersModuleModel();
        $all_actions = $model->getActions();
        $allowed_actions = $model->getAllowedActions(Environment::getSession('group_id')->value);
        $form = new AppForm($this, $name);
        $form->addHidden('group_id')->setValue(Environment::getSession('group_id')->value);
        $form = $this->addPermissionCheckboxes($form, $all_actions, $allowed_actions);
        foreach($allowed_actions as $row)
        {
            $form['allowed_'.$row->resource_id]->setValue(1);
        }
        $form->addGroup('  ')->setOption('container', Html::el('fieldset')->id('buttons'));
        $form->addButton('btnClose', 'Close');
        $form->addSubmit('btnSave', 'Save');
        $form->onSubmit[] = array($this, 'SavePermissions');
        return $form;
    }
    
    public function savePermissions(AppForm $form)
    {
        $values = $form->getValues();
        unset($values['btnSave']);
        $group_id = (int)$values['group_id'];
        unset($values['group_id']);
        $allowed = array();
        foreach($values as $cb =>$value)
        {
            if($value == true)
            {
                $allowed[] = (int)str_replace('allowed_','', $cb);
            }    
        }
        $model = new UsersModuleModel();
        $model->savePermissions($group_id, $allowed);
        $this->template->edit = false;
        $this->invalidateControl('form');
        $this->flash('Permissions saved');
    }
    
    private function addPermissionCheckboxes(AppForm $form, $all_actions, $allowed_actions)
    {
        $actions = array();
        foreach($all_actions as $row)
        {
            $actions[$row->privilege][] = array('resource' => $row->resource, 'id' => $row->id);    
        }

        foreach($actions as $privilege => $resources)
        {
            $privilege = str_replace('_', ':', $privilege);
            $privilege = str_replace('presenter', '', $privilege);
            $form->addGroup($privilege, true);
            foreach ($resources as $resource)
            {
                $form->addCheckbox('allowed_'.$resource['id'], $resource['resource']);
            }
        }
        return $form;
    }
    
    public function handleEditPermissions($id)
    {
        Environment::getSession('group_id')->value = $id;
        $this->template->edit = true;
        $this->invalidateControl('form');
    }
    
    public function actionGroups()
    {
        $model = new UsersModuleModel();
        $roles = $model->getRoles();
        $actions = $this->gatherActions();
        $this->template->edit = false;
        $this->template->actions = $actions;
    }
}
?>