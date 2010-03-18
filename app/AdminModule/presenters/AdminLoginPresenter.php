<?php
class Admin_LoginPresenter extends Admin_BasePresenter
{
	public function startup()
	{
		parent::startup();
	}
	
	
	public function actionJson()
	{
		$this->payload->cmds[] = 'login';
		$this->view = 'login_form_JSON';
		$this->invalidateControl('form');
	}
	
	protected function createComponentFormLogin()
	{
	    $form = new LiveForm($this, 'formLogin');
	    $form->addText('login', 'login')->addRule(Form::FILLED, 'Fill username');
	    $form->addPassword('password', 'Password')->addRule(Form::FILLED, 'Fill password');
	    $form->addSubmit('btn_login', 'Login');
		$form->onSubmit[] = array($this, 'formLoginSubmitted');
	    return $form;
  	}
	
	public function formLoginSubmitted($form)
  	{
  		$values = $form->getValues();
		if ($form->isValid()) 
		{
			$login = $values['login'];
			$password = $values['password'];
			$user = Environment::getUser();
			$user->setAuthenticationHandler(new Admin_UserModel());
			try
			{
				$user->authenticate($login, $password);
				$session_conf = Environment::getVariable('session');
				$user->setExpiration($session_conf['expiration'], true);
				$session = $this->getSession('backlink');
				$session->in_application = true;
				$this->getApplication()->restoreRequest($session['backlink']);
				$this->redirect('Dashboard:default');
				//if($this->isAjax())	$this->terminate();
			}
			catch (AuthenticationException $e) 
			{
				$form->setValues($values);
				$this->invalidateControl('frmLogin');
                                $this->flash('Error: '. $e->getMessage());
                                if($this->isAjax())
                                {
                                        $this->invalidateControl('frmLogin');
                                        $this->invalidateControl('flashes');
                                }
                                else $this->redirect('this');

			}
		}
 	}
	
	protected function createComponentFormLoginAjax()
	{
	    $form = new LiveForm($this, 'formLoginAjax');
	    $form->addText('login', 'login')->addRule(Form::FILLED, 'Fill username');
	    $form->addPassword('password', 'Password')->addRule(Form::FILLED, 'Fill password');
	    $form->addSubmit('btn_login', 'Login');
		$form->onSubmit[] = array($this, 'formLoginAjaxSubmitted');
	    return $form;
  	}
	
	public function formLoginAjaxSubmitted($form)
  	{
  		$values = $form->getValues();
		if ($form->isValid()) 
		{
			$login = $values['login'];
			$password = $values['password'];
			$user = Environment::getUser();
			$user->setAuthenticationHandler(new Admin_UserModel());
			try
			{
				$user->authenticate($login, $password);
				$session_conf = Environment::getVariable('session');
				$user->setExpiration($session_conf['expiration'], true);
				//$session = $this->getSession('backlink');
				//$session->in_application = true;
				//$this->getApplication()->restoreRequest($session['backlink']);
				//$this->redirect('Dashboard:default');
				$this->payload->cmds = array();
				$this->payload->cmds[] = 'login_ok';
				$this->flash('Login OK');
				if($this->isAjax())
				{
					$this->sendPayload();
					$this->terminate();
				}
			}
			catch (AuthenticationException $e) 
			{
				$form->setValues($values);
				$this->invalidateControl('frmLogin');
        		if($this->isAjax())
				{
					$this->invalidateControl('formLoginAjax');
				}
				//$this->flash('Error: '. $e->getMessage());
				$this->payload->error = $e-getMessage();
			}
		}
 	}
}
?>