<?php
class ContactForm extends LiveForm
{
    public function __construct(Presenter $parent = null, $name = null)
    {
        parent::__construct($parent, $name);
        $this->addText('from', 'From')->addRule(Form::EMAIL, 'Please enter a valid email.');
        $this->addTextArea('text', 'Message');
        $this->addSubmit('btnSend', 'Send')->onClick[] = callback($this, 'FormSent');
    }  
    
    public function FormSent(Button $btn)
    {
        $values = $btn->getForm()->getValues();
        $this->parent->flashMessage('Message Sent', 'success');
        $this->parent->redirect('this');
    }
}
?>
