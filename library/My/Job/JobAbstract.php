<?php

namespace My\Job;

use My\General;

abstract class JobAbstract {

    protected $client;
    protected $serviceLocator;

    public function __construct($serviceLocator = null) {
        if ($serviceLocator !== null) {
            $this->setServiceLocator($serviceLocator);
        }

        if (!$this->client) {
            $this->setClient(General::getClientConfig());
        }
    }

    public function addJob($funcName, $arrParams) {
        try {
            $client = $this->getClient();
            $client->addTaskBackground($funcName, serialize($arrParams));
            $client->runTasks();
        } catch (\Exception $exc) {
            die($exc->getMessage());
        }
    }

    /**
     * @return the $client
     */
    public function getClient() {
        return $this->client;
    }

    /**
     * @param field_type $client
     */
    public function setClient($client) {
        $this->client = $client;
    }

    /**
     * @return the $serviceLocator
     */
    public function getServiceLocator() {
        return $this->serviceLocator;
    }

    /**
     * @param field_type $serviceLocator
     */
    public function setServiceLocator($serviceLocator) {
        $this->serviceLocator = $serviceLocator;
    }
}
