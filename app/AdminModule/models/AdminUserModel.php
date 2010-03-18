<?php
/**
 * Photon CMS
 *
 * @copyright  Copyright (c) 2009 Martin Bazik
 * @package    AdminModels
 */

/**
 * Admin User Model
 *
 * @author     Martin Bazik
 * @package    AdminModels
 */
class Admin_UserModel extends Admin_BaseModel implements IAuthenticator, IAuthorizator
{
	protected $salt = 'supertajnyavelmidlhysalt';
	protected $table = 'admin_users';
	protected function make_seed() {
	  list($usec, $sec) = explode(' ', microtime());
	  return (float) $sec + ((float) $usec * 100000);
	}

	protected function pwdGen($password_length = 6)
	{
		srand($this->make_seed());
		$alfa = "1234567890qwertyuiopasdfghjklzxcvbnmQWERTYUIOPASDFGHJKLZXCVBNM";
		$token = "";
		for($i = 0; $i < $password_length; $i ++) {
		  $token .= $alfa[rand(0, strlen($alfa))];
		}    
		return $token;
	}

	public function phash($pwd)
	{
		return $pwd;
		return hash('sha256',$pwd.$this->salt);
	}

	public function saveUser($values)
	{
		return true;
	}

	public function authenticate(array $credentials)
    {
        $login = $credentials['username'];
        $password = $this->phash($credentials['password']);
		$super_admin = Environment::getVariable('admin');
		if ($login == $super_admin['login'])
		{
			if( $password == $super_admin['password'] )
			{
				$super_admin['roles'] = array('super admin');
				$super_admin['id'] = 0;
				$row = new DibiRow($super_admin);
                MokujiServiceLocator::addService('UserAuthorizator', new Admin_UserModel());
			}
			else throw new AuthenticationException("Invalid password.", self::INVALID_CREDENTIAL);
		}
		else
		{
            try
            {
                $login_manager = Environment::getService('UserAuthenticator');
                $row = $login_manager->authenticate($credentials);  
            }
            catch(InvalidStateException $e)
            {
               throw new AuthenticationException("Login and password combination failed.", self::INVALID_CREDENTIAL); 
            }
		}
        $identity = new Identity($row->id, $row->roles, $row);
		$identity->id = $row->id;
		return $identity;
    }
    
    public function isAllowed($role = self::ALL, $resource = self::ALL, $privilege = self::ALL)
    {
        return true;
    }
}
?>