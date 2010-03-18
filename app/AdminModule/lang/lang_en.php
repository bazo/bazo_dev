<?php
class Admin_Inflexion_en
{
    public function inflex($count)
    {
        switch ($count) {
            case 1:
                $int = 1;
            break;
            default:
                $int = 2;
            break;
        }
        return $int;
    }
}

class Admin_Dictionary_en
{
	public $dictionary = array(
	    'you have %d page2' => 'You have %d pages',
        'you have %d category1' => 'You have %d category',	
        'you have %d category2' => 'You have %d categories',
        'you have %d menu1' => 'You have %d menu',    
        'you have %d menu2' => 'You have %d menus',
        'you have %d module1' => 'You have %d module',    
        'you have %d module2' => 'You have %d modules',
	); 
	
	public function getDictionary()
	{
		return $this->dictionary;
	}
}
