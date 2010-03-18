<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 *
 * @author Martin
 */
interface IMokujiModule {
    public function install();
    public function uninstall();
    public function getRoutes();
    public function getMenuItems();
    public function onLoad();
}
?>
