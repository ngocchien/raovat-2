<?php

namespace My\Models;

class Keyword extends ModelAbstract {

    private function getParentTable() {
        $dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
        return new \My\Storage\storageKeyword($dbAdapter);
    }

    public function __construct() {
        $this->setTmpKeyCache('tmpLogs');
        parent::__construct();
    }

    public function add($p_arrParams) {
        $intResult = $this->getParentTable()->add($p_arrParams);
        if ($intResult) {
            $this->cache->increase($this->tmpKeyCache, 1);
        }
        return $intResult;
    }

    public function getListLimit($arrCondition = [], $intPage = 1, $intLimit = 15, $strOrder = 'key_id ASC') {
        $arrResult = $this->getParentTable()->getListLimit($arrCondition, $intPage, $intLimit, $strOrder);
        return $arrResult;
    }
    
    public function edit($p_arrParams, $intContentID) {
        $intResult = $this->getParentTable()->edit($p_arrParams, $intContentID);
        return $intResult;
    }

}
