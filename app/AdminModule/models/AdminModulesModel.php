<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of AdminModulesModel
 *
 * @author Martin
 */
class Admin_ModulesModel extends Admin_BaseModel {
    protected $table = "admin_modules";

    public function getAll()
    {
        return db::select('*')->from(':table:')->fetchAll();
    }

    public static function changeStatus($module_name, $new_status)
    {
        $values = array('status' => $new_status);
        db::addSubst('modules', 'admin_modules'); 
        db::update(':modules:', $values)->where('module_name = %s', $module_name)->execute();
    }
    
    public static function checkInstall($module_name)
    {
        db::addSubst('modules', 'admin_modules');
        return db::select('*')->from(':modules:')->where('module_name = %s', 'Users')->fetch();
    }
    
    public static function getStatus($module_name)
    {
        db::addSubst('modules', 'admin_modules');
        return db::select('status')->from(':modules:')->where('module_name = %s', $module_name)->fetch();
    }
}
?>
