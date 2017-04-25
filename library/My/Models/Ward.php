<?php

namespace My\Models;

class Ward extends ModelAbstract {

    private function getParentTable() {
        $dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
        return new \My\Storage\storageWard($dbAdapter);
    }

    public function __construct() {
        $this->setTmpKeyCache('tmpWard');
        parent::__construct();
    }

    public function filter($params) {
        $tmp = array();
        $fields = array('ward_id', 'city_id', 'dist_id', 'ward_name', 'ward_slug', 'ward_ordering', 'ward_is_focus', 'ward_status');
        foreach ($fields as $field) {
            if (isset($params[$field])) {
                if (($field == 'ward_name')) {
                    require_once VENDOR_DIR . 'HTMLPurifier/HTMLPurifier.auto.php';
                    $config = \HTMLPurifier_Config::createDefault();
                    $config->set('Attr.EnableID', true);
                    $config->set('HTML.Strict', true);
                    $purifier = new \HTMLPurifier($config);
                    $params[$field] = $purifier->purify($params[$field]);
                }
                $tmp[$field] = $params[$field];
            }
        }
        return $tmp;
    }

    public function getList($arrCondition = array()) {
        return $this->getParentTable()->getList($arrCondition);
    }

    public function getListLimit($arrCondition, $intPage, $intLimit, $strOrder) {
        $keyCaching = 'getListLimitWard:' . $intPage . ':' . $intLimit . ':' . str_replace(' ', '', $strOrder) . ':' . $this->cache->read($this->tmpKeyCache);
        if (count($arrCondition) > 0) {
            foreach ($arrCondition as $k => $val) {
                $keyCaching .= $k . ':' . $val . ':';
            }
        }
        $keyCaching = crc32($keyCaching);
        $result = $this->cache->read($keyCaching);
        if (empty($result)) {
            $result = $this->getParentTable()->getListLimit($arrCondition, $intPage, $intLimit, $strOrder);
            $this->cache->add($keyCaching, $result, 60 * 60 * 24 * 7);
        }
        return $result;
    }

    public function getTotal($arrCondition) {
        $result = $this->getParentTable()->getTotal($arrCondition);
        return $result;
    }

    public function getDetail($arrCondition) {
        $keyCaching = 'getDetailWard:';
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

    public function add($p_arrParams) {
        //$p_arrParams = $this->filter($p_arrParams);
        $result = $this->getParentTable()->add($p_arrParams);
        if ($result) {
            $this->cache->increase($this->tmpKeyCache, 1);
        }
        return $result;
    }

    public function edit($p_arrParams, $intWardID) {
        //$p_arrParams = $this->filter($p_arrParams);
        print_r($intWardID);die;
        $result = $this->getParentTable()->edit($p_arrParams, $intWardID);
        if ($result) {
            $this->cache->increase($this->tmpKeyCache, 1);
        }
        return $result;
    }

}
