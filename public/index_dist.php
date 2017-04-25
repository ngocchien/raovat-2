<?php

error_reporting(E_COMPILE_ERROR | E_RECOVERABLE_ERROR | E_ERROR | E_CORE_ERROR);
mb_internal_encoding('UTF-8');
mb_http_output('UTF-8');
mb_http_input('UTF-8');
mb_language('uni');
mb_regex_encoding('UTF-8');
ob_start('mb_output_handler');
chdir(dirname(__DIR__));
defined('APPLICATION_ENV') || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production'));

$staticURL = APPLICATION_ENV === 'production' ?  'http://static.khampha.tech' : 'http://dev.st.khampha.tech';
$baseURL = APPLICATION_ENV === 'production' ? 'http://khampha.tech' : 'http://dev.khampha.tech';

//define variable
defined('WEB_ROOT') || define('WEB_ROOT', realpath(dirname(dirname(__FILE__))));
define('FRONTEND_TEMPLATE', 'v1');
define('STATIC_URL', $staticURL);
define('BASE_URL', $baseURL);
define('PUBLIC_PATH', WEB_ROOT . '/public');
define('STATIC_PATH', WEB_ROOT . '/static');
define('UPLOAD_PATH', STATIC_PATH . '/uploads/');
define('UPLOAD_URL', STATIC_URL . '/uploads/');
define('CAPTCHA_PATH', UPLOAD_PATH . 'captcha/');
define('CAPTCHA_URL', UPLOAD_URL . 'captcha');
define('FRONTEND_FONT_PATH', STATIC_PATH . '/f/' . FRONTEND_TEMPLATE . '/fonts/');
define('VENDOR_DIR', WEB_ROOT . '/vendor/');
define('CONFIG_CACHE_DIR', WEB_ROOT . '/config/config-cache');
define('SES_EXPIRED', 7776000);
define('CLI_DEBUG', 0);
define('SEARCH_PREFIX', 'news_');
define('WORKER_PREFIX', 'news');

// Setup autoloading
require 'init_autoloader.php';
//require_once 'vendor/zendframework/library/Zend/Loader/StandardAutoloader.php';
// Run the application!
try {
    \Zend\Mvc\Application::init(require 'config/application.config.php')->run();
} catch (\Exception $exc) {
    die($exc->getMessage());
}