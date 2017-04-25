<?php

namespace My\Cache;

use Zend\Cache\Storage\Adapter\Memcached;

class Memcache {

    protected $cache;
    protected $options;
    protected $readable;
    protected $writable;

    public function __construct() {
        $arrMemcachedServer = array(array('127.0.0.1', 11211));
        $this->options = array(
            'servers' => $arrMemcachedServer,
            'namespace' => null,
            'lib_options' => array(
                'SERIALIZER' => \Memcached::SERIALIZER_IGBINARY,
                'HASH' => \Memcached::HASH_MD5,
                'DISTRIBUTION' => \Memcached::DISTRIBUTION_CONSISTENT,
                'LIBKETAMA_COMPATIBLE' => true,
                'NO_BLOCK' => true,
                'COMPRESSION' => true,
                'BUFFER_WRITES' => true,
                'TCP_NODELAY' => true,
                'CONNECT_TIMEOUT' => 3,
                'RETRY_TIMEOUT' => 3,
                'PREFIX_KEY' => 'QUYNHON247.VN_',
            ),
        );

        $this->cache = new Memcached();


        return $this->cache;
    }

    /**
     * @return the $readable
     */
    public function getReadable() {
        return $this->readable;
    }

    /**
     * @param field_type $readable
     */
    public function setReadable($readable) {
        $this->readable = (bool) $readable;
        $this->options['readable'] = $this->readable;
    }

    /**
     * @return the $writable
     */
    public function getWritable() {
        return $this->writable;
    }

    /**
     * @param field_type $writable
     */
    public function setWritable($writable) {
        $this->writable = (bool) $writable;
        $this->options['writable'] = $this->writable;
    }

    public function read($strKey) {
        $this->cache->setOptions($this->options);
        return $this->cache->getItem($strKey);
    }

    public function readMulti($arrKeys) {
        $this->cache->setOptions($this->options);
        return $this->cache->getItems($arrKeys);
    }

    public function add($strKey, $params, $ttl = 259200) {
        try {
            if (isset($ttl)) {
                $result=$this->cache->setOptions($this->options + array('ttl' => $ttl));
            }
            return $this->cache->addItem($strKey, $params);
        } catch (\Zend\Http\Exception $exc) {
            if (APPLICATION_ENV !== 'production') {
                die($exc->getMessage());
            }
            return false;
        }
    }

    public function increase($strKey, $params) {
        try {
            $this->cache->setOptions($this->options);
            return $this->cache->incrementItem($strKey, $params);
        } catch (\Zend\Http\Exception $exc) {
            if (APPLICATION_ENV !== 'production') {
                die($exc->getMessage());
            }
            return false;
        }
    }

    public function remove($strKey) {
        try {
            $this->cache->setOptions($this->options);
            return $this->cache->removeItem($strKey);
        } catch (\Zend\Http\Exception $exc) {
            if (APPLICATION_ENV !== 'production') {
                die($exc->getMessage());
            }
            return false;
        }
    }

    public function removeMulti($arrKeys) {
        try {
            $this->cache->setOptions($this->options);
            return $this->cache->removeItems($arrKeys);
        } catch (\Zend\Http\Exception $exc) {
            if (APPLICATION_ENV !== 'production') {
                die($exc->getMessage());
            }
            return false;
        }
    }

}
