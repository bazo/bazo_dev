<?php
/**
 * Description of Users
 *
 * @author Martin
 */
class UsersModule implements IMokujiModule {

    private $model;

    public function __construct()
    {
        $this->model = new UsersModuleModel();
    }
    
    public function install()
    {
        $result = $this->model->install();
        return $result;
    }
    
    public function getRoutes()
    {
        $routes = array();
        $routes[] = new Route('admin/users/<action>', array(
                                'module' => 'Users',
                                'presenter' => 'Users',
                                'action' => 'default',
                        ));
        return $routes;
    }

    public function uninstall()
    {
        return $this->model->uninstall();
    }

    public function getMenuItems()
    {
        return array(
            'Users' => array(':Users:Users:','Groups' => ':Users:Users:Groups'),
        );
    }

    public function getActions()
    {
        return array(
            'Edit',    
        );
    }

    public function onStatusChange($new_status)
    {
        if($new_status == 'disabled')
        {
            $cache = Environment::getCache('modules.UsersModule');
            $cache->clean(array(Cache::ALL => TRUE));
        }
    }
    
    public function onLoad()
    {
        Environment::getServiceLocator()->addService('UserAuthenticator', new UsersModuleModel());
    }
}