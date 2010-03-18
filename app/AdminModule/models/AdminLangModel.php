<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of AdminLangModel
 *
 * @author Martin
 */
class Admin_LangModel extends Admin_BaseModel{
    public function getAll()
    {
        $langs = array();
        foreach(glob(APP_DIR.'/AdminModule/lang/lang_*.php') as $file)
        {
                $lang = basename($file);
                $lang = str_ireplace('lang_', '', $lang);
                $lang = str_ireplace('.php', '', $lang);
                $langs[$lang] = $lang;
        }
        return $langs;
    }
}
?>
