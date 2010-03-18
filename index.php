<?php
// absolute filesystem path to the web root
define('WWW_DIR', dirname(__FILE__));
define('DATA_DIR', dirname(__FILE__).'/');
// absolute filesystem path to the application root
define('APP_DIR', DATA_DIR.'app');
// absolute filesystem path to the libraries
define('LIBS_DIR', DATA_DIR.'libs');
// absolute filesystem path to the modules
define('MODULES_DIR', APP_DIR.'/Modules');
//text which will be show as the domain name in administration
define('WEBSITE', $_SERVER['HTTP_HOST']);
// load bootstrap file
require APP_DIR . '/bootstrap.php';
