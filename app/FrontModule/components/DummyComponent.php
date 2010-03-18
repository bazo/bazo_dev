<?php
class DummyComponent extends Control
{
    
    public function render()
    {
        echo 'Component with name: '.$this->name.' not found.';    
    }    
}
?>
