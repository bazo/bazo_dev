<?php

/**
 * Css loader
 *
 * @author Jan Marek
 * @license MIT
 */
class CssLoader extends WebLoader
{
	/** @var string */
	public $media;

	/** @var bool */
	public $absolutizeUrls = true;

	/**
	 * Make relative url absolute
	 * @param string $url
	 * @param string $quote
	 * @param string $file
	 * @param string $sourceUri
	 * @return string
	 */
	public static function absolutizeUrl($url, $quote, $file, $sourceUri) {
		// is already absolute
		//var_dump($url, $quote, $file, $sourceUri);
		if (@ereg("^([a-z]+:/)?/", $url)) return $url;
		$lastPos = strrpos($file, "/");
		$fileFolder = $lastPos === false ? "" : "/" . substr($file, 0, $lastPos);
		//$path = $sourceUri . $fileFolder . "/" . $url;
		$path = $sourceUri . "/" . $url;
		$pathPieces = explode("/", $path);
		$piecesOut = array();
		//var_dump($pathPieces);exit;
		foreach ($pathPieces as $piece) {
			if ($piece === ".") continue;

			if ($piece === "..") {
				array_pop($piecesOut);
				continue;
			}

			$piecesOut[] = $piece;
		}

		$out = implode("/", $piecesOut);

		if ($quote === '"') $out = addslashes($out);

		return $out;
	}

	private function absolutizeUrls($s, $file) {
		// thanks to kravco
		//var_dump($file);exit;
		$regexp = '~
			(?<![a-z])
			url\(                                     ## url(
				\s*                                   ##   optional whitespace
				([\'"])?                              ##   optional single/double quote
				(   (?: (?:\\\\.)+                    ##     escape sequences
					|   [^\'"\\\\,()\s]+              ##     safe characters
					|   (?(1)   (?!\1)[\'"\\\\,() \t] ##       allowed special characters
						|       ^                     ##       (none, if not quoted)
						)
					)*                                ##     (greedy match)
				)
				(?(1)\1)                              ##   optional single/double quote
				\s*                                   ##   optional whitespace
			\)                                        ## )
		~xs';

		return preg_replace_callback(
			$regexp,
			create_function(
				'$matches',
				'return "url(\'" . CssLoader::absolutizeUrl($matches[2], $matches[1], "' .
				addslashes($file) . '", "' . addslashes($this->sourceUri) .
				'") . "\')";'
			),
			$s
		);
	}

	/**
	 * Load file
	 * @param string $path
	 * @return string
	 */
	protected function loadFile($file) {
		$content = parent::loadFile($file);
		//var_dump($this->absolutizeUrls, !empty($this->sourceUri));exit;
		if ($this->absolutizeUrls && !empty($this->sourceUri)) {
			$content = $this->absolutizeUrls($content, $file);
		}

		return $content;
	}

	/**
	 * Filename of generated CSS file
	 * @param array $files
	 * @return string
	 */
	public function getGeneratedFilename(array $files = null)
	{
		return parent::getGeneratedFilename($files) . ".css";
	}
	
	/**
	 * Get link element
	 * @param string $source
	 * @return Html
	 */
	public function getElement($source)
	{
		return Html::el("link")
			->rel("stylesheet")
			->type("text/css")
			->media($this->media)
			->href($source);
	}
}