<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of UsersModule
 *
 * @author Martin
 */
class UsersModuleModel extends Model implements IAuthenticator, IAuthorizator
{

    protected $table = 'admin_users';
    //MODULE FUNCTIONS
    public function __create()
    {
        db::addSubst('modules', 'admin_modules');
        db::addSubst('table', 'admin_users');
        db::addSubst('resources', 'admin_permission_resources');
    }

    public function install()
    {
        db::query('CREATE TABLE IF NOT EXISTS `admin_users` (
                  `id` int(11) NOT NULL AUTO_INCREMENT,
                  `login` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
                  `password` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
                  `group` tinyint(4) NOT NULL,
                  PRIMARY KEY (`id`),
                  UNIQUE KEY `login` (`login`),
                  UNIQUE KEY `login_2` (`login`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;');
    }

    public function uninstall()
    {
        return db::query('drop table :table:')->execute();
    }
    //USERS HANDLING
    public function authenticate(array $credentials)
    {
        $login = $credentials['username'];
        $password = $credentials['password'];
        $row = db::select('*')->from('[:table:]')->where('login = %s', $login)->fetch();
        if (!$row) 
        { 
            throw new AuthenticationException("Login and password combination failed.", self::IDENTITY_NOT_FOUND);
        }
        if ($row->password !== $password) 
        {
            throw new AuthenticationException("Login and password combination failed.", self::INVALID_CREDENTIAL);
        }
        $row->roles = array($row->group);
        $allowed = $this->getAllowedActions($row->group);
        $permissions = array();
        foreach($allowed as $record)
        {
            $permissions[$record->privilege][] = $record->resource;       
        }
        $row->permissions = $permissions;
        MokujiServiceLocator::addService('UserAuthorizator', new UsersModuleModel());
        return $row;
    }
    
    public function isAllowed($role = self::ALL, $resource = self::ALL, $privilege = self::ALL)
    {
        if($privilege == 'actionDeny') return true;
        $data = Environment::getUser()->getIdentity()->permissions;
        if(isset($data[String::lower($resource)]))
        {
            if($privilege == null) return true;
            foreach($data[String::lower($resource)] as $user_privilege)
            {
                if(String::lower($privilege) == String::lower($user_privilege)) return true;
            }
            return false;
        }
        else return false;
    }
    
    public function saveActions($actions)
    {
        db::addSubst('resources', 'admin_permission_resources');
        foreach($actions as $privilege => $resources)
        {
            foreach($resources as $resource)
            {
                $values = array(
                    'privilege' => $privilege,
                    'resource' => $resource
                );
                db::insert(':resources:', $values)->setFlag('IGNORE')->execute();
            }
        }    
    }
    
    public function getRoles()
    {
        db::addSubst('roles', 'admin_user_roles');
        return db::select('*')->from(':roles:')->fetchAll();
    }
    
    public function getRolesDs()
    {
        db::addSubst('roles', 'admin_user_roles');
        return db::select('*')->from(':roles:')->toDataSource();
    }
    
    public function getActions()
    {
        db::addSubst('actions', 'admin_permission_resources');
        return db::select('*')->from(':actions:')->fetchAll();    
    }
    
    public function getAllowedActions($id)
    {
        db::addSubst('actions', 'admin_users_groups_allowed');
        db::addSubst('resources', 'admin_permission_resources');
        return db::select(':actions:.group_id group_id, :actions:.resource_id resource_id, :resources:.*')->from(':actions:')->join(':resources:')->on(':actions:.resource_id = :resources:.id')->where(':actions:.group_id = %i', $id)->fetchAll();    
    }
    
    public function savePermissions($group_id, $allowed)
    {
        db::addSubst('actions', 'admin_users_groups_allowed');
        foreach($allowed as $resource_id)
        {
            $values = array(
                'group_id' => $group_id,
                'resource_id' => $resource_id
            );
            db::insert(':actions:', $values)->on('DUPLICATE KEY')->update($values)->execute();    
        }
    }
}
?>
