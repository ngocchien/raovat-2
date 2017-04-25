<?php

namespace My\Models;

class Comment extends ModelAbstract {

    private function getParentTable() {
        $dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
        return new \My\Storage\storageComment($dbAdapter);
    }

    public function __construct() {
        $this->setTmpKeyCache('tmpComment');
        parent::__construct();
    }

    public function getList($arrCondition = array()) {
        return $this->getParentTable()->getList($arrCondition);
    }

    public function getListChildren($arrCondition = array()) {
        return $this->getParentTable()->getListChildren($arrCondition);
    }

    public function getDetail($arrCondition = array()) {
        return $this->getParentTable()->getDetail($arrCondition);
    }

    public function getTotal($arrCondition) {
        return $this->getParentTable()->getTotal($arrCondition);
    }

    public function getTotalInProduct($arrCondition) {
        return $this->getParentTable()->getTotalInProduct($arrCondition);
    }

    public function getTotalById($arrCondition) {
        return $this->getParentTable()->getTotalById($arrCondition);
    }

    public function getTotalCommentInContent($arrCondition) {
        return $this->getParentTable()->getTotalCommentInContent($arrCondition);
    }

    public function add($p_arrParams) {
        $intResult = $this->getParentTable()->add($p_arrParams);
        if ($intResult) {
            $this->cache->increase($this->tmpKeyCache, 1);
        }
        return $intResult;
    }

    public function getListLimit($arrCondition, $intPage, $intLimit, $strOrder) {
        $keyCaching = 'getListLimitComment:' . $intPage . ':' . $intLimit . ':' . str_replace(' ', '_', $strOrder) . ':' . $this->cache->read($this->tmpKeyCache);
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

    public function getListParentLimit($arrCondition, $intPage, $intLimit) {
        $keyCaching = 'getListLimitParent:' . $this->cache->read($this->tmpKeyCache);
        if (count($arrCondition) > 0) {
            foreach ($arrCondition as $k => $val) {
                $keyCaching .= $k . ':' . $val . ':';
            }
        }
        $keyCaching = crc32($keyCaching);
        $arrResult = $this->cache->read($keyCaching);
        if (empty($arrResult)) {
            $arrResult = $this->getParentTable()->getListParentLimit($arrCondition, $intPage, $intLimit);
            $this->cache->add($keyCaching, $arrResult, 60 * 60 * 24 * 7);
        }
        return $arrResult;
    }

    public function getListLimitInProduct($arrCondition, $intPage, $intLimit, $strOrder) {
        $keyCaching = 'getListLimitCommentInProduct:' . $intPage . ':' . $intLimit . ':' . str_replace(' ', '_', $strOrder) . ':' . $this->cache->read($this->tmpKeyCache);
        if (count($arrCondition) > 0) {
            foreach ($arrCondition as $k => $val) {
                $keyCaching .= $k . ':' . $val . ':';
            }
        }
        $keyCaching = crc32($keyCaching);
        $arrResult = $this->cache->read($keyCaching);
        if (empty($arrResult)) {
            $arrResult = $this->getParentTable()->getListLimitInProduct($arrCondition, $intPage, $intLimit, $strOrder);
            $this->cache->add($keyCaching, $arrResult, 60 * 60 * 24 * 7);
        }
        return $arrResult;
    }

    public function getListLimitCommentInContent($arrCondition, $intPage, $intLimit, $strOrder) {
        // return $this->getParentTable()->getListLimitCommentInContent($arrCondition, $intPage, $intLimit, $strOrder);
        $keyCaching = 'getListLimitCommentInContent:' . $intPage . ':' . $intLimit . ':' . str_replace(' ', '_', $strOrder) . ':' . $this->cache->read($this->tmpKeyCache);
        if (count($arrCondition) > 0) {
            foreach ($arrCondition as $k => $val) {
                $keyCaching .= $k . ':' . $val . ':';
            }
        }
        $keyCaching = crc32($keyCaching);
        $arrResult = $this->cache->read($keyCaching);
        if (empty($arrResult)) {
            $arrResult = $this->getParentTable()->getListLimitCommentInContent($arrCondition, $intPage, $intLimit, $strOrder);
            $this->cache->add($keyCaching, $arrResult, 60 * 60); // one H
        }
        return $arrResult;
    }

    public function edit($p_arrParams, $intCommentID) {
        $intResult = $this->getParentTable()->edit($p_arrParams, $intCommentID);
        if ($intResult) {
            $this->cache->increase($this->tmpKeyCache, 1);
        }
        return $intResult;
    }

    public function editlike($intCommentID) {
        $intResult = $this->getParentTable()->editlike($intCommentID);
        if ($intResult) {
            $this->cache->increase($this->tmpKeyCache, 1);
        }
        return $intResult;
    }

}
