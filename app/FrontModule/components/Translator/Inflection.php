<?php
class Inflexion
{
	public function inflex($count, $lang)
	{
		$func = 'inflex_'.$lang;
		return $this->$func($count);
	}
	
	public function __call($name, $args)
	{
		return $args[0];
	}
	
	private function inflex_sk($count)
	{
		switch ($count) {
			case 1:
				$int = 1;
			break;
			case 2:
			case 3:
			case 4:
				$int = 2;
			break;
			default:
				$int = 5;
			break;
		}
		return $int;
	}
}
