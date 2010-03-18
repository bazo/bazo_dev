<?php
require LIBS_DIR . '/nette-dev/loader.php';
require LIBS_DIR.'/debug.php';
require LIBS_DIR.'/Mokuji/Mokuji.php';
//enable Debugging
Environment::setMode(Environment::DEVELOPMENT);
Debug::enable(Debug::DEVELOPMENT);
Debug::enableProfiler();

//load configuration
Environment::loadConfig(APP_DIR.'/config/config.ini');
Environment::setVariable('website', WEBSITE);
db::connect();
//get Application
Environment::getSession()->start();
$app = Environment::getApplication();
$app->catchExceptions = FALSE;
$app->run();
//RoutingDebugger::enable();