<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Mokuji
 *
 * @author Martin
 */
class MokujiCMS extends Application {
   
    /** @var array */
    private $adminNodes = array(), $modulePermissions = array();

    public function getModulesPermissions()
    {
        return $this->modulePermissions;    
    }
    
    public function getAdminNodes()
    {
        return $this->adminNodes;
    }

    public function fire($event)
    {
        $params = func_get_args();
        array_shift($params);
        $eventMethod = 'on'.ucfirst($event);
        if(is_callable(array($this, $eventMethod))) call_user_func_array(array($this, $eventMethod), $params);
        else throw new InvalidStateException('Event '.$eventMethod.' is not defined!');
    }

    public function onModuleLoad($module_class)
    {
        $module_name = str_replace('module', '', $module_class);
        $module_name = String::capitalize($module_name);
        ModuleManager::register($module_name);
        $module_status =  ModuleManager::getStatus($module_name);
        if($module_status == 'enabled')
        {
            $router = $this->getRouter();
            $module = new $module_class();
            $new_routes = $module->getRoutes();
            $this->modulePermissions[$module_name] = $module->getActions();
            $module->onLoad();
            foreach ($new_routes as $route) {       
               $router[] = $route;
            }
            $this->adminNodes = array_merge($this->adminNodes, $module->getMenuItems());
        }
    }

    public static function createLoader($options)
    {
        require_once('MokujiLoader.php');
        $loader = new MokujiLoader();
        $loader->autoRebuild = !Environment::isProduction();
        $dirs = isset($options['directory']) ? $options['directory'] : array(Environment::getVariable('appDir'), Environment::getVariable('libsDir'));
        $loader->addDirectory($dirs);
        $loader->register();
        return $loader;
    }

    private function scanClasses($class_list)
    {
        foreach ($class_list as $class => $file) {
            try{
                $r = new ReflectionClass($class);
            if( $r->implementsInterface('IMokujiModule') && !$r->isInterface()) $this->onModuleLoad($class);
            $r = null;
            }
            catch(ReflectionException $e)
            {
                
            }
        }
    }

    public function installModules()
    {
        //$listCache = Environment::getCache('classList');
        //$class_list = $listCache['classList'];
        $service = Environment::getService('Nette\Loaders\RobotLoader');
        $class_list = $service->list;
        $this->scanClasses($class_list);
    }

    private function loadRoutes()
    {
        require_once APP_DIR.'/config/routes.php';
        $router = $this->getRouter();
        foreach ($routes as $route) {
            $router[] = $route;
        }
    }

    public function run()
    {
        $this->installModules();
        $this->loadRoutes();
        parent::run();
    }
}
?>
