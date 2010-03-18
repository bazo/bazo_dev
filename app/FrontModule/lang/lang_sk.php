<?php
class Dictionary_sk
{
	protected static $dictionary = array(
		'cicik1' => '%d cicik',
		'cicik2' => '%d ciciky',
		'cicik5' => '%d cicikov',
		'there are %2$d messages for user %1$s5' => 'uzivatel %1$s ma %2$d sprav',
		'there are %2$d messages for user %1$s1' => 'uzivatel %1$s ma %2$d spravu',
		'there are %2$d messages for user %1$s2' => 'uzivatel %1$s ma %2$d spravy',
		'login' => 'Prihlasit',
		'dashboard' => 'Plocha',
		'student' => 'študent',
		'year' => 'ročník',
		'teacher' => 'učiteľ',
		'stranky' => 'pages'
	); 
	
	static function getDictionary()
	{
		return self::$dictionary;
	}
}
