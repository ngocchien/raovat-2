<?php

$strENV = APPLICATION_ENV;
$isPhpCli = PHP_SAPI === 'cli' ? true : false;
$arrModule = array('Frontend', 'Backend');

if ($strENV === 'dev' && $isPhpCli === false) {
    $arrModule = array_merge($arrModule, array('Whoops', 'ZendDeveloperTools', 'BjyProfiler', 'ThaConfigalyzer', 'SanSessionToolbar'));
}
$arrModuleListener = array(
    'module_paths' => array('./vendor', './module'),
    'config_glob_paths' => array(
        'config/autoload/{,*.}{global,local}.php',
    ),
);
if ($strENV === 'production') {
    $arrModuleListener = $arrModuleListener + array(
        'config_cache_enabled' => true,
        'config_cache_key' => 'cache',
        'module_map_cache_enabled' => true,
        'module_map_cache_key' => 'cache',
        'cache_dir' => CONFIG_CACHE_DIR,
    );
}
return array('modules' => $arrModule, 'module_listener_options' => $arrModuleListener);
