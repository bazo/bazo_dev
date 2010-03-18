<?php
class Admin_Inflexion_sk
{
    public function inflex($count)
    {
        switch ($count) {
            case 1:
                $int = 1;
            break;
            case 2:
            case 3:
                $int = 2;
            break;
            default:
                $int = 5;
            break;
        }
        return $int;
    }
}

class Admin_Dictionary_sk
{
    public $dictionary = array(
        'you have %d page1' => 'máte %d stránku',
        'you have %d page2' => 'máte %d stránky',
        'you have %d page5' => 'máte %d stránok',
        'you have %d category1' => 'máte %d kategóriu',    
        'you have %d category2' => 'máte %d kategórii',
        'you have %d menu1' => 'máte %d menu',    
        'you have %d menu2' => 'máte %d menu',
        'you have %d module1' => 'máte %d modul',    
        'you have %d module2' => 'máte %d modulov',
    ); 
    
    public function getDictionary()
    {
        return $this->dictionary;
    }
}
?>
