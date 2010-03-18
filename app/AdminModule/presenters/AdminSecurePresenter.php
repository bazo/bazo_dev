<?php
class Admin_SecurePresenter extends Admin_BasePresenter
{
	public function startup()
	{
    	parent::startup();
		$this->storeRequest();
		$user = Environment::getUser();
		if( !$user->isAuthenticated())
		{
			$this->redirect(":Admin:Login:default");
		}
        try{
            $this->checkAuthorization();   
        }
        catch(AuthenticationException $e)
        {
            $this->forward(':Admin:Secure:deny');
        }
        
  	}
	
	protected function storeRequest()
	{
		$session = $this->getSession('backlink');
		$session['backlink'] = Environment::getApplication()->storeRequest();
	}
    
    private function checkAuthorization()
    {
        $presenter = String::lower($this->getReflection()->getName());
        $user = Environment::getUser();
        $user->setAuthorizationHandler(MokujiServiceLocator::getService('UserAuthorizator'));
        //if(Environment::getServiceLocator()->hasService('UserAuthorizator')) $user->setAuthorizationHandler(Environment::getService('UserAuthorizator'));
        //else $user->setAuthorizationHandler(new Admin_UserModel());
        if($this->formatActionMethod($this->action) == 'actiondeny') return;
        if( $user->isAllowed($presenter, $this->formatActionMethod($this->action)) === true)
        {
         if ( $user->isAllowed($presenter, $this->formatSignalMethod($this->signal)) === false )
         {
            throw new AuthenticationException('This action is not allowed');
         }
        } else throw new AuthenticationException('This action is not allowed');
    }
    
    public function actionDeny()
    {
        $this->view = 'not_allowed';
    }
}
?>