<?php
class Admin_Translator implements ITranslator
{
	
	protected $dict;
	
	public function __construct($lang)
	{
		$this->lang = $lang;
		$this->dict = Admin_Dictionary::getDictionary($this->lang);
	}
	
	protected function inflexion($count)
	{
		$int = null;
		$inflexion = Admin_Dictionary::getInflexion($this->lang);
		$int = $inflexion->inflex($count);
		return $int;
	}
	
	protected function detectCase($msg)
	{
		if($msg == String::lower($msg)) return 'lower';
		if($msg == String::upper($msg)) return 'upper';
		if($msg == String::capitalize($msg)) return 'capitalize';
		return 'default';
	}
	
	public function translate($message, $count = NULL)
	{
		$case = $this->detectCase($message);
		$args = func_get_args();
		if ( is_string($count) ) {
			$count = $args[2];
		}
		$counter = $count;
		if (is_numeric($count)) $counter = $this->inflexion($count);
		$node = String::lower($message.$counter);
		if (isset($this->dict[$node])) {
			$message = $this->dict[$node];
		}
		else
		{
			$message = implode(' ', explode('_', $message));
		}
        if (count($args) > 1)
        {
            array_shift($args);
            $message = vsprintf($message, $args);
        }
		if ($case == 'default') {
			return $message;
		}else
		return call_user_func(array('String', $case), $message);
	}
	
}