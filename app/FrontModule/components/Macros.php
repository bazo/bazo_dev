<?php
class Macros
{
	public static function registerMacros()
	{
		LatteMacros::$defaultMacros['menu'] = '<?php $control->getWidget("menu")->render(%%); ?>';
		LatteMacros::$defaultMacros['searchBox'] = '<?php $control->getWidget("searchBox")->render(%%); ?>';
        LatteMacros::$defaultMacros['keywords'] = '<?php echo "<meta name=\"keywords\" content=\"$keywords\" />"; ?>';
        LatteMacros::$defaultMacros['description'] = '<?php echo "<meta name=\"description\" content=\"$description\" />"; ?>'; 
	}
}

?>