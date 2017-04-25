<?php

/**
 * Global Configuration Override
 *
 * You can use this file for overriding configuration values from modules, etc.
 * You would place values in here that are agnostic to the environment and not
 * sensitive to security.
 *
 * @NOTE: In practice, this file will typically be INCLUDED in your source
 * control, so do not include passwords or other sensitive information in this
 * file.
 */
//if enviroment is develop. Enable trace for MySQL

$dbParams = array(
    'database' => 'raovat',
    'username' => 'root',
    'password' => 'iloveu#huelinh@1009',
    'hostname' => 'localhost',
    // buffer_results - only for mysqli buffered queries, skip for others
    'options' => array('buffer_results' => true),
    'driver_options' => array(
        PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES UTF8'
    )
);

$isPhpCli = PHP_SAPI === 'cli' ? true : false;

if (APPLICATION_ENV === 'dev' && $isPhpCli === false) {
    $arrServiceManager = array(
        'factories' => array(
            'Zend\Db\Adapter\Adapter' => function ($sm) use ($dbParams) {
                $adapter = new \BjyProfiler\Db\Adapter\ProfilingAdapter(array(
                    'driver' => 'PDO',
                    'dsn' => 'mysql:dbname=' . $dbParams['database'] . ';host=' . $dbParams['hostname'],
                    'database' => $dbParams['database'],
                    'username' => $dbParams['username'],
                    'password' => $dbParams['password'],
                    'hostname' => $dbParams['hostname'],
                    'driver_options' => $dbParams['driver_options'],
                ));
                // write queries profiling info to stdout in CLI mode
                if (php_sapi_name() == 'cli' && CLI_DEBUG === 1) {
                    $logger = new \Zend\Log\Logger();
                    $writer = new \Zend\Log\Writer\Stream('php://output');
                    $logger->addWriter($writer, \Zend\Log\Logger::DEBUG);
                    $adapter->setProfiler(new \BjyProfiler\Db\Profiler\LoggingProfiler($logger));
                } else {
                    $adapter->setProfiler(new \BjyProfiler\Db\Profiler\Profiler());
                }
                if (isset($dbParams['options']) && is_array($dbParams['options'])) {
                    $options = $dbParams['options'];
                } else {
                    $options = array();
                }
                $adapter->injectProfilingStatementPrototype($options);
                return $adapter;
            },
        ),
    );
} else {
    $arrServiceManager = array(
        'factories' => array(
            'Zend\Db\Adapter\Adapter' => 'Zend\Db\Adapter\AdapterServiceFactory',
        ));
}

return array(
    'db' => array(
        'driver' => 'PDO',
        'dsn' => 'mysql:dbname=raovat;host=localhost',
        'driver_options' => array(
            PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES UTF8',
        ),
    ),
    'service_manager' => $arrServiceManager,
    'di' => array(),
);
        