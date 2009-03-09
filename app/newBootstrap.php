<?php

/* Defines */
define('USVN_BASE_DIR', realpath(dirname(__FILE__) . '/..'));

/* ROOTS */
define('USVN_APP_DIR', USVN_BASE_DIR . '/app');
define('USVN_LIB_DIR', USVN_BASE_DIR . '/library');
define('USVN_PUB_DIR', USVN_BASE_DIR . '/public');
define('USVN_CONFIG_DIR', USVN_BASE_DIR . '/config');

//Libraries
define('USVN_DIRECTORY', USVN_LIB_DIR . '/USVN');
define('USVN_ROUTES_CONFIG_FILE', USVN_DIRECTORY . '/routes.ini');
define('ZEND_DIRECTORY', USVN_LIB_DIR . '/Zend');

//Application
define('USVN_CONTROLLERS_DIR',  USVN_APP_DIR . '/controllers');
define('USVN_HELPERS_DIR',      USVN_APP_DIR . '/helpers');
define('USVN_VIEWS_DIR',        USVN_APP_DIR . '/views');
define('USVN_LAYOUTS_DIR',      USVN_APP_DIR . '/layouts');
define('USVN_MODEL_DIR',        USVN_APP_DIR . '/models');
define('USVN_LOCALE_DIR',       USVN_APP_DIR . '/locale');
define('USVN_LOCALE_DIRECTORY', USVN_LOCALE_DIR);
define('USVN_MEDIAS_DIR',       USVN_PUB_DIR . '/medias/');

//Config
define('USVN_CONFIG_FILE', USVN_CONFIG_DIR . '/config.ini');
define('USVN_CONFIG_SECTION', 'general');

/* Misc */
define('USVN_URL_SEP', ':');
error_reporting(E_ALL | E_STRICT);

/* Necessary Includes */
set_include_path(
	USVN_LIB_DIR . PATH_SEPARATOR
	. get_include_path());

require_once 'Zend/Loader.php';
Zend_Loader::registerAutoload();
require_once 'functions.php';

/* Config Loading */
$config = new USVN_Config_Ini(USVN_CONFIG_FILE, USVN_CONFIG_SECTION);

/* Installation **FIXME** */


/* USVN Configuration  */
date_default_timezone_set($config->timezone);

USVN_ConsoleUtils::setLocale($config->system->locale);
USVN_Translation::initTranslation($config->translation->locale, USVN_LOCALE_DIRECTORY);
USVN_Template::initTemplate($config->template->name, USVN_MEDIAS_DIR);

/* Zend Configuration  */
Zend_Registry::set('config', $config);
Zend_Db_Table::setDefaultAdapter(Zend_Db::factory($config->database->adapterName, $config->database->options->toArray()));
if (isset($config->database->prefix)) {
	USVN_Db_Table::$prefix = $config->database->prefix;
}

$front = Zend_Controller_Front::getInstance();
Zend_Layout::startMvc(array('layoutPath' => USVN_LAYOUTS_DIR));

$front->setRequest(new USVN_Controller_Request_Http());
$front->throwExceptions(true);
$front->setBaseUrl($config->url->base);

/**
 * Initialize router
 */
$router = new Zend_Controller_Router_Rewrite();
$routes_config = new USVN_Config_Ini(USVN_ROUTES_CONFIG_FILE, USVN_CONFIG_SECTION);
$router->addConfig($routes_config, 'routes');
$front->setRouter($router);

$front->setControllerDirectory(USVN_CONTROLLERS_DIR);

