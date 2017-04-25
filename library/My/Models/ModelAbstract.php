<?php

namespace My\Models;

use My\Cache\Memcache,
    Zend\ServiceManager\ServiceLocatorInterface,
    Zend\ServiceManager\ServiceLocatorAwareInterface;

class ModelAbstract implements ServiceLocatorAwareInterface {

    protected $cache;
    protected $tmpKeyCache;
    protected $serviceLocator;

    public function __construct() {
        $this->cache = new Memcache();

        $isPhpCli = PHP_SAPI === 'cli' ? true : false;
        if ($isPhpCli === true) {
            //disable cache if env is CLI
            $this->cache->setReadable(false);
            $this->cache->setWritable(false);
        }
        if (empty($this->cache->read($this->getTmpKeyCache()))) {
            $this->cache->add($this->getTmpKeyCache(), 1, 60 * 60 * 24 * 10);
        }
    }

    public function setServiceLocator(ServiceLocatorInterface $serviceLocator) {
        $this->serviceLocator = $serviceLocator;
    }

    public function getServiceLocator() {
        return $this->serviceLocator;
    }

    /**
     * @param field_type $tmpKeyCache
     */
    public function setTmpKeyCache($tmpKeyCache) {
        $this->tmpKeyCache = $tmpKeyCache;
    }

    /**
     * @return the $tmpKeyCache
     */
    public function getTmpKeyCache() {
        return $this->tmpKeyCache;
    }

}
