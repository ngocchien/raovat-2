<?php

namespace My\Job;

use My\General;
use Zend\ServiceManager\ServiceLocatorInterface;

class JobSendEmail {

    protected $client;
    protected $worker;
    protected $serviceLocator;

    public function __construct() {
        $this->client = General::getClientConfig();
    }

    public function getServiceLocator(ServiceLocatorInterface $serviceLocator) {
        return $serviceLocator;
    }

    public function addJob($funcName, $arrParams) {
        try {
            $this->client->addTaskBackground($funcName, serialize($arrParams));
            $this->client->runTasks();
        } catch (\Exception $exc) {
            die($exc->getMessage());
        }
    }

    public function send($params, $serviceLocator) {
        $arrParams = array();
        if ($params->workload()) {
            $arrParams = unserialize($params->workload());
        }
        if (!is_array($arrParams) || empty($arrParams)) {
            $strError = General::getColoredString("ERROR: Params is incorrent or empty ", 'light_cyan', 'red');
            die($strError);
        }
        if (APPLICATION_ENV !== 'dev') {
            $replyTo = $arrParams['reply_to'] ? $arrParams['reply_to'] : 'megavita.vn@gmail.com';
            General::sendMail($arrParams['to'], $arrParams['subject'], $arrParams['body'], $replyTo);
        }
    }

}
