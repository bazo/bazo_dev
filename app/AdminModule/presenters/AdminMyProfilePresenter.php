<?php

/**
 * Description of AdminMyProfilePresenter
 *
 * @author Martin
 */
class Admin_MyProfilePresenter extends Admin_SecurePresenter {

    private $profile_data = array(), $config_data = array(), $mode; //$mode superadmin or db

    public function actionDefault()
    {
        if(Environment::getUser()->getIdentity()->role == 'super admin')
        {
            $this->config_data = ConfigAdapterIni::load(APP_DIR.'/config/admin.ini', 'admin');
            $this->profile_data = $this->config_data['admin'];
            $this->mode = 'superadmin';
        }
        $this->view = 'profile';
    }

    public function createComponentFormAdminProfile($name)
    {
        $form = new LiveForm();
        $form->addGroup('My profile');
        foreach ($this->profile_data as $field => $value) {
            if($field != 'password')
                $form->addText($field, String::capitalize($field))->setValue($value);
            else
            {
                $form->addPassword($field, 'Current '.$field);
                $form->addPassword($field.'_new', 'New '.$field);
                $form->addPassword($field.'_new_confirm', 'Confirm '.$field);
            }
        }
        $form->addSubmit('btnSave', 'Save')->onClick[] = array($this, 'SaveProfile');
        return $form;
    }

    public function SaveProfile(Button $button)
    {
        $form = $button->getForm();
        $values = $form->getValues();
        try{
            if($this->mode == 'superadmin')
            {
                    if(isset($values['password_new'])){
                        if($values['password'] == $this->profile_data['password'])
                        {
                            if($values['password_new'] == $values['password_new_confirm']) $values['password'] = $values['password_new'];
                            else throw new AuthenticationException('New password does not match.');
                        } else throw new AuthenticationException('Old password does not match with provided password.');
                    } else unset($values['password']);
                    unset($values['password_new']); unset($values['password_new_confirm']);
                    $this->config_data['admin'] = array_merge($this->config_data['admin'] , $values);
                    $config = new Config();
                    $config->import($this->config_data);
                    $config->save(APP_DIR.'/config/admin.ini', 'admin');
                    $this->flash('Super admin profile updated');
            }
        }
        catch (Exception $e)
        {
            $this->flash($e->getMessage(), 'error');
        }
        $this->end();
    }
}
?>
