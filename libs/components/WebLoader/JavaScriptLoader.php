<?php

/**
 * JavaScript loader
 *
 * @author Jan Marek
 * @license MIT
 */
class JavaScriptLoader extends WebLoader
{
	/**
	 * Filename of generated JS file
	 * @param array $files
	 * @return string
	 */
	public function getGeneratedFilename(array $files = null)
	{
		return parent::getGeneratedFilename($files) . ".js";
	}

	/**
	 * Get script element
	 * @param string $source
	 * @return Html
	 */
	public function getElement($source)
	{
		return Html::el("script")->type("text/javascript")->src($source);
	}
}