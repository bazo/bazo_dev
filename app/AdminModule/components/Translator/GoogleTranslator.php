<?php

/**
* Google Translator
*
* Copyright (c) 2009, 2010 Roman Novák
*
* This source file is subject to the New-BSD licence.
*
* For more information please see http://nettephp.com
*
* @copyright Copyright (c) 2009, 2010 Roman Novák
* @license   New-BSD
* @link      http://nettephp.com/cs/extras/googletranslator
* @version   0.1
*/

/**
* Google translator service class
*
* @author Roman Novák
* @copyright Copyright (c) 2009, 2010 Roman Novák
* @package nette-google-translator
*/
class GoogleTranslator extends Object implements ITranslator
{
	/** LANGUAGES */
	const AFRIKAANS = 'af';
	const ALBANIAN = 'sq';
	const AMHARIC = 'am';
	const ARABIC = 'ar';
	const ARMENIAN = 'hy';
	const AZERBAIJANI = 'az';
	const BASQUE = 'eu';
	const BELARUSIAN = 'be';
	const BENGALI = 'bn';
	const BIHARI = 'bh';
	const BULGARIAN = 'bg';
	const BURMESE = 'my';
	const CATALAN = 'ca';
	const CHEROKEE = 'chr';
	const CHINESE = 'zh';
	const CHINESE_SIMPLIFIED = 'zh-CN';
	const CHINESE_TRADITIONAL = 'zh-TW';
	const CROATIAN = 'hr';
	const CZECH = 'cs';
	const DANISH = 'da';
	const DHIVEHI = 'dv';
	const DUTCH = 'nl';  
	const ENGLISH = 'en';
	const ESPERANTO = 'eo';
	const ESTONIAN = 'et';
	const FILIPINO = 'tl';
	const FINNISH = 'fi';
	const FRENCH = 'fr';
	const GALICIAN = 'gl';
	const GEORGIAN = 'ka';
	const GERMAN = 'de';
	const GREEK = 'el';
	const GUARANI = 'gn';
	const GUJARATI = 'gu';
	const HEBREW = 'iw';
	const HINDI = 'hi';
	const HUNGARIAN = 'hu';
	const ICELANDIC = 'is';
	const INDONESIAN = 'id';
	const INUKTITUT = 'iu';
	const ITALIAN = 'it';
	const JAPANESE = 'ja';
	const KANNADA = 'kn';
	const KAZAKH = 'kk';
	const KHMER = 'km';
	const KOREAN = 'ko';
	const KURDISH = 'ku';
	const KYRGYZ = 'ky';
	const LAOTHIAN = 'lo';
	const LATVIAN = 'lv';
	const LITHUANIAN = 'lt';
	const MACEDONIAN = 'mk';
	const MALAY = 'ms';
	const MALAYALAM = 'ml';
	const MALTESE = 'mt';
	const MARATHI = 'mr';
	const MONGOLIAN = 'mn';
	const NEPALI = 'ne';
	const NORWEGIAN = 'no';
	const ORIYA = 'or';
	const PASHTO = 'ps';
	const PERSIAN = 'fa';
	const POLISH = 'pl';
	const PORTUGUESE = 'pt-PT';
	const PUNJABI = 'pa';
	const ROMANIAN = 'ro';
	const RUSSIAN = 'ru';
	const SANSKRIT = 'sa';
	const SERBIAN = 'sr';
	const SINDHI = 'sd';
	const SINHALESE = 'si';
	const SLOVAK = 'sk';
	const SLOVENIAN = 'sl';
	const SPANISH = 'es';
	const SWAHILI = 'sw';
	const SWEDISH = 'sv';
	const TAJIK = 'tg';
	const TAMIL = 'ta';
	const TAGALOG = 'tl';
	const TELUGU = 'te';
	const THAI = 'th';
	const TIBETAN = 'bo';
	const TURKISH = 'tr';
	const UKRAINIAN = 'uk';
	const URDU = 'ur';
	const UZBEK = 'uz';
	const UIGHUR = 'ug';
	const VIETNAMESE = 'vi';
	const UNKNOWN = '';
	const AUTODETECT = null;
	
	/** CONTENT TYPES */
	const TEXT = 'text';
	const HTML = 'html';

	public $disableCache = false;	
	public $fromCache;
	
	/** URL's */
	
	/** @var string translation service url */
	protected $translationServiceUrl = 'http://ajax.googleapis.com/ajax/services/language/translate';
	/** @var string detect language service url */
	protected $detectServiceUrl = 'http://ajax.googleapis.com/ajax/services/language/detect';
	
	/** VARIABLES */
	
	/** @var string locale */
	protected $locale = null;
	/** @var string from locale */
	protected $from = null;
	/** @var string google service protocol version */
	public $protocolVersion = '1.0';
	/** @var string application key */
	public $key = '';
	
	/** @var array last service request */
	protected $lastRequest = null;
	/** @var array last service response */
	protected $lastResponse = null;
	/** @var Cache cache */
	protected $cache = null;

	/** METHODS */
	
	/** 
	 * GoogleTranslator contructor 
	 * @param string to locale
	 * @param string from locale
	 */
	function __construct($locale = null, $from = null)
	{
		$this->locale = $locale;
		$this->from = $from;
	}
	
	/**
	 * Gets translation
	 * @param string text to translate
	 * @param string item count (google doesn't support)
	 * @return string translation
	 */
	function translate($text, $count = null)
	{
		$cache = $this->getCache();
		if(!$this->disableCache && isset($cache[$this->locale]) && isset($cache[$this->locale][$text])) {				
			$this->fromCache = true;
			return $cache[$this->locale][$text];
		}
		$this->fromCache = false;

		$from = $this->from;
		if(empty($this->from)) {
			$from = $this->detect($text);
		}

		$query = array(
			'q' => $text,
			'langpair' => $from . '|' . $this->locale,
			'v' => $this->protocolVersion);

		if(!empty($this->key)) {
			$query['key'] = $this->key;
		}
		
		$translation = $this->translateByQuery($query);
		if(empty($translation->responseData)) {
			throw new InvalidTranslationException($translation->responseDetails, $translation->responseStatus, $this->getLastRequest(), $this->getLastResponse());	
		}
		$translate = $translation->responseData->translatedText;
		if(!$this->disableCache) {
			$cache->save($this->locale, array($text => $translate));
		}
		return $translate;
	}
	
	/** 
	 * Gets translation by given query array
	 * @param array request query
	 * @return array translation service response
	 */
	function translateByQuery($query)
	{
		$this->lastRequest = $query;
		$queryString = http_build_query($query);
		$url = $this->translationServiceUrl . '?' . $queryString;
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		//curl_setopt($ch, CURLOPT_REFERER, $_SERVER['HTTP_HOST']); 
		$body = curl_exec($ch);
		curl_close($ch);
		
		$json = json_decode($body);
		$this->lastResponse = $json;
		return $json;
	}
	
	/*
	 * Detect language
	 * @param string text to detect
	 * @return string language code
	 */
	function detect($text)
	{
		$query = array(
			'q' => $text,
			'v' => $this->protocolVersion);

		if(!empty($this->key)) {
			$query['key'] = $this->key;
		}
		
		$language = $this->detectByQuery($query);
		
		if(empty($language->responseData)) {
			throw new InvalidTranslationException($language->responseDetails, $language->responseStatus, $this->getLastRequest(), $this->getLastResponse());	
		}
		
		return $language->responseData->language;
	}
	
	/* Detect language by given query array
	 * @param array request query
	 * @return array response
	 */
	function detectByQuery($query)
	{
		$this->lastRequest = $query;
		
		$queryString = http_build_query($query);
		$url = $this->detectServiceUrl . '?' . $queryString;
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_REFERER, $_SERVER['HTTP_HOST']);
		$body = curl_exec($ch);
		curl_close($ch);

		$json = json_decode($body);

		$this->lastResponse = $json;
		return $json;
	}
	
	/** PROPERTIES */
	
	/**
	 * Gets last service request
	 * @return array http://code.google.com/apis/ajaxlanguage/documentation/reference.html#_fonje_args
	 */
	function getLastRequest()
	{
		return $this->lastRequest;
	}
	
	/**
	 * Gets last service response
	 * @return array http://code.google.com/apis/ajaxlanguage/documentation/reference.html#_fonje_response
	 */
	function getLastResponse()
	{
		return $this->lastResponse;
	}

	
	function getCache()
	{
		if(null === $this->cache) {
			$this->cache = Environment::getCache('google-translator');
		}
		return $this->cache;
	}

	function setCache(Cache $cache)
	{
		$this->cache = $cache;
	}
}

/** Google translator alias */
class GT extends GoogleTranslator {}

class InvalidTranslationException extends Exception
{
	private $request;
	private $response;
	
	function __construct($message = '', $code = 0, $request = null, $response = null)
	{
		parent::__construct($message, $code);
		$this->request = $request;
		$this->response = $response;		
	}
	
	function getRequest()
	{
		return $this->request;
	}
	
	function getResponse()
	{
		return $this->response;
	}
}
