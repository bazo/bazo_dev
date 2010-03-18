<?php
class Dictionary
{
	private static $dictionaries = array();
	
	public static function getDictionary($lang)
	{
		$class = 'Dictionary_'.$lang;
		if (isset(self::$dictionaries[$class])) {
			$dict = self::$dictionaries[$class];
		}
		else $dict = call_user_func(array($class, 'getDictionary'));
		return $dict;
	}
}
