<?php

namespace My\Models;

class District extends ModelAbstract {

    private function getParentTable() {
        $dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
        return new \My\Storage\storageDistrict($dbAdapter);
    }

    public function __construct() {
        $this->setTmpKeyCache('tmpDistrict');
        parent::__construct();
    }

    public function getList($arrCondition = array()) {
        $keyCaching = 'getListDistrict' . $this->cache->read($this->tmpKeyCache);
        if (count($arrCondition) > 0) {
            foreach ($arrCondition as $k => $val) {
                $keyCaching .= $k . ':' . $val . ':';
            }
        }
        $keyCaching = crc32($keyCaching);
        $arrResult = $this->cache->read($keyCaching);

        if (empty($arrResult)) {
            $arrResult = $this->getParentTable()->getList($arrCondition);
            $this->cache->add($keyCaching, $arrResult, 60 * 60 * 24 * 7);
        }
        return $arrResult;
    }

    public function getListLimit($arrCondition = array(), $intPage = 1, $intLimit = 15, $strOrder = 'dist_ordering ASC') {
        $keyCaching = 'getListLimitDistrict:' . $intPage . ':' . $intLimit . ':' . str_replace(' ', '_', $strOrder) . ':' . $this->cache->read($this->tmpKeyCache);
        if (count($arrCondition) > 0) {
            foreach ($arrCondition as $k => $val) {
                $keyCaching .= $k . ':' . $val . ':';
            }
        }
        $keyCaching = crc32($keyCaching);
        $arrResult = $this->cache->read($keyCaching);
        if (empty($arrResult)) {
            $arrResult = $this->getParentTable()->getListLimit($arrCondition, $intPage, $intLimit, $strOrder);
            $this->cache->add($keyCaching, $arrResult, 60 * 60 * 24 * 7);
        }
        return $arrResult;
    }

    public function getTotal($arrCondition = array()) {
        return $this->getParentTable()->getTotal($arrCondition);
    }

    public function getDetail($arrCondition = array()) {
        $keyCaching = 'getDetailDistrict:';
        if (count($arrCondition) > 0) {
            foreach ($arrCondition as $k => $condition) {
                $keyCaching .= $k . ':' . $condition . ':';
            }
        }
        $keyCaching .= 'tmp:' . $this->cache->read($this->tmpKeyCache);
        $keyCaching = crc32($keyCaching);
        $arrResult = $this->cache->read($keyCaching);
        if (empty($arrResult)) {
            $arrResult = $this->getParentTable()->getDetail($arrCondition);
            $this->cache->add($keyCaching, $arrResult, 60 * 60 * 24 * 7);
        }
        return $arrResult;
    }

    public function add($p_arrParams = array()) {
        //$p_arrParams = $this->filter($p_arrParams);
        $result = $this->getParentTable()->add($p_arrParams);
        if ($result) {
            $this->cache->increase($this->tmpKeyCache, 1);
        }
        return $result;
    }

    public function edit($p_arrParams, $intCityID) {
        //$p_arrParams = $this->filter($p_arrParams);
        $result = $this->getParentTable()->edit($p_arrParams, $intCityID);
        if ($result) {
            $this->cache->increase($this->tmpKeyCache, 1);
        }
        return $result;
    }

}

