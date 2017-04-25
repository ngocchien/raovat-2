<?php

namespace Backend;

use Zend\Authentication\AuthenticationService,
    Zend\Authentication\Adapter\DbTable as DbTableAuthAdapter;

class Module {

    public function getServiceConfig() {
        return array(
            'factories' => array(
                'My\Auth\MyStorage' => function($sm) {
                    return new \My\Auth\MyStorage('amazon247UserLogin' . session_id());
                },
                'AuthService' => function($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $dbTableAuthAdapter = new DbTableAuthAdapter($dbAdapter, 'tbl_users', 'email');
                    $authService = new AuthenticationService();
                    $authService->setAdapter($dbTableAuthAdapter);
                    $authService->setStorage($sm->get('My\Auth\MyStorage'));
                    return $authService;
                },
                'ACL' => function($sm) {
                    return new \My\Permission\MyAcl($sm);
                }
            ),
        );
    }

    public function getControllerConfig() {
        return array(
            'abstract_factories' => array(
                'My\Service\ControlAbstractFactory'
            ),
        );
    }

    public function getConfig() {
        return include __DIR__ . '/config/module.config.php';
    }

    public function getAutoloaderConfig() {
        return array(
            'Zend\Loader\ClassMapAutoloader' => array(
                __DIR__ . '/autoload_classmap.php',
            ),
        );
    }

}
