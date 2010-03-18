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
class Front_UserModel extends Admin_BaseModel implements IAuthenticator
{
	protected $salt = 'supertajnyavelmidlhysalt';
	
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
		$super_admin = Environment::getConfig('admin');
		
		if ($login == $super_admin['login'])
		{
			if( $password == $super_admin['password'] )
			{
				$super_admin_info['name'] = 'super admin';
				$row = new DibiRow($super_admin_info);
			}
			else throw new AuthenticationException("Invalid password.", self::INVALID_CREDENTIAL);
		}
		else
		{
			$row = db::select('*')->from('[:admin:users]')->where('login = %s', $login)->fetch();
	        if (!$row) 
			{ 
	            throw new AuthenticationException("Login '$login' not found.", self::IDENTITY_NOT_FOUND);
	        }
	        if ($row->password !== $password) 
			{
	            throw new AuthenticationException("Invalid password.", self::INVALID_CREDENTIAL);
	        }
		}
        return new Identity($row->name);
    }
}
?>