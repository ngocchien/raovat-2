<?php

namespace My\Storage;

use Zend\Db\TableGateway\AbstractTableGateway,
    Zend\Db\Sql\Sql,
    Zend\Db\Adapter\Adapter,
    My\Validator\Validate,
    Zend\Db\TableGateway\TableGateway;

class storageContent extends AbstractTableGateway {

    protected $table = 'tbl_contents';

    public function __construct(Adapter $adapter) {
        $adapter->getDriver()->getConnection()->connect();
        $this->adapter = $adapter;
    }

    public function __destruct() {
        $this->adapter->getDriver()->getConnection()->disconnect();
    }

    public function getList($arrCondition = array()) {

        try {
            $strWhere = $this->_buildWhere($arrCondition);
            $adapter = $this->adapter;
            $sql = new Sql($adapter);
            $select = $sql->Select($this->table)
                    ->where('1=1' . $strWhere)
                    ->order(array('cont_id DESC'));

            $query = $sql->getSqlStringForSqlObject($select);
            return $adapter->query($query, $adapter::QUERY_MODE_EXECUTE)->toArray();
        } catch (\Zend\Http\Exception $exc) {
            if (APPLICATION_ENV !== 'production') {
                die($exc->getMessage());
            }
            return array();
        }
    }

    public function getListLimit($arrCondition = [], $intPage = 1, $intLimit = 15, $strOrder = 'cont_id DESC') {
        try {
            $strWhere = $this->_buildWhere($arrCondition);
            $adapter = $this->adapter;
            $sql = new Sql($adapter);
            $select = $sql->Select($this->table)
                    ->where('1=1' . $strWhere)
                    ->order($strOrder)
                    ->limit($intLimit)
                    ->offset($intLimit * ($intPage - 1));
            $query = $sql->getSqlStringForSqlObject($select);
            return $adapter->query($query, $adapter::QUERY_MODE_EXECUTE)->toArray();
        } catch (\Zend\Http\Exception $exc) {
            if (APPLICATION_ENV !== 'production') {
                die($exc->getMessage());
            }
            return array();
        }
    }

    public function getDetail($arrCondition = array()) {
        try {
            $strWhere = $this->_buildWhere($arrCondition);
            $adapter = $this->adapter;
            $sql = new Sql($adapter);
            $select = $sql->Select($this->table)
                    ->where('1=1' . $strWhere);
            $query = $sql->getSqlStringForSqlObject($select);

            return current($adapter->query($query, $adapter::QUERY_MODE_EXECUTE)->toArray());
        } catch (\Zend\Http\Exception $exc) {
            if (APPLICATION_ENV !== 'production') {
                die($exc->getMessage());
            }
            return array();
        }
    }

    public function getTotal($arrCondition = []) {
        try {
            $strWhere = $this->_buildWhere($arrCondition);
            $adapter = $this->adapter;
            $sql = new Sql($adapter);
            $select = $sql->Select($this->table)
                    ->columns(array('total' => new \Zend\Db\Sql\Expression('COUNT(*)')))
                    ->where('1=1' . $strWhere);
            $query = $sql->getSqlStringForSqlObject($select);
            return (int) current($adapter->query($query, $adapter::QUERY_MODE_EXECUTE)->toArray())['total'];
        } catch (\Zend\Http\Exception $exc) {
            if (APPLICATION_ENV !== 'production') {
                die($exc->getMessage());
            }
            return false;
        }
    }

    public function add($p_arrParams) {
        try {
            if (!is_array($p_arrParams) || empty($p_arrParams)) {
                return false;
            }
            
            $adapter = $this->adapter;
            $sql = new Sql($adapter);
            $insert = $sql->insert($this->table)->values($p_arrParams);
            $query = $sql->getSqlStringForSqlObject($insert);
            $adapter->createStatement($query)->execute();
            $result = $adapter->getDriver()->getLastGeneratedValue();
            if ($result) {
                $p_arrParams['cont_id'] = $result;
                $instanceJob = new \My\Job\JobContent();
                $instanceJob->addJob(SEARCH_PREFIX . 'writeContent', $p_arrParams);
            }
            return $result;
        } catch (\Exception $exc) {
            echo '<pre>';
            print_r($exc->getMessage());
            echo '</pre>';
            die();
            if (APPLICATION_ENV !== 'production') {
                die($exc->getMessage());
            }
            return false;
        }
    }

    public function edit($p_arrParams, $intProductID) {
        try {
            if (!is_array($p_arrParams) || empty($p_arrParams) || empty($intProductID)) {
                return false;
            }
            $result = $this->update($p_arrParams, 'cont_id=' . $intProductID);
            if ($result) {
                $p_arrParams['cont_id'] = $intProductID;
                $instanceJob = new \My\Job\JobContent();
                $instanceJob->addJob(SEARCH_PREFIX . 'editContent', $p_arrParams);
            }
            return $result;
        } catch (\Exception $exc) {
            echo '<pre>';
            print_r($exc->getMessage());
            echo '</pre>';
            die();
            if (APPLICATION_ENV !== 'production') {
                die($exc->getMessage());
            }
            return false;
        }
    }

    public function multiEdit($p_arrParams, $arrCondition) {
        try {
            if (!is_array($p_arrParams) || empty($p_arrParams) || empty($arrCondition) || !is_array($arrCondition)) {
                return false;
            }
            $strWhere = $this->_buildWhere($arrCondition);
            $result = $this->update($p_arrParams, '1=1 ' . $strWhere);
            
            if ($result) {
                $arrData = [
                    'data' => $p_arrParams,
                    'condition' => $arrCondition
                ];
                $instanceJob = new \My\Job\JobContent();
                $instanceJob->addJob(SEARCH_PREFIX . 'multiEditContent', $arrData);
            }
            return $result;
        } catch (\Exception $exc) {
            if (APPLICATION_ENV !== 'production') {
                throw new \Exception($exc->getMessage());
            }
            return false;
        }
    }

    private function _buildWhere($arrCondition) {
        $strWhere = '';

        if (!empty($arrCondition['cont_slug'])) {
            $strWhere .= " AND cont_slug='" . $arrCondition['cont_slug'] . "'";
        }

        if (!empty($arrCondition['cont_id'])) {
            $strWhere .= " AND cont_id=" . $arrCondition['cont_id'];
        }

        if (!empty($arrCondition['cont_title'])) {
            $strWhere .= " AND cont_title=" . $arrCondition['cont_title'];
        }

        if (!empty($arrCondition['cont_status'])) {
            $strWhere .= " AND cont_status =" . $arrCondition['cont_status'];
        }

        if (!empty($arrCondition['cate_id'])) {
            $strWhere .= " AND cate_id=" . $arrCondition['cate_id'];
        }

        if (!empty($arrCondition['not_cont_id'])) {
            $strWhere .= " AND cont_id !=" . $arrCondition['not_cont_id'];
        }

        if (!empty($arrCondition['in_cont_id'])) {
            $strWhere .= " AND cont_id IN (" . $arrCondition['in_cont_id'] . ")";
        }

        return $strWhere;
    }

}
