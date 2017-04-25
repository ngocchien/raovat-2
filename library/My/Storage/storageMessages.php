<?php

namespace My\Storage;

use Zend\Db\TableGateway\AbstractTableGateway,
    Zend\Db\Sql\Sql,
    Zend\Db\Adapter\Adapter,
    Zend\Db\Sql\Where,
    Zend\Db\Sql\Select,
    My\Validator\Validate;

class storageMessages extends AbstractTableGateway {

    protected $table = 'tbl_messages';

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
                    ->order(array('mess_id ASC'));
            $query = $sql->getSqlStringForSqlObject($select);
            return $adapter->query($query, $adapter::QUERY_MODE_EXECUTE)->toArray();
        } catch (\Zend\Http\Exception $exc) {
            if (APPLICATION_ENV !== 'production') {
                die($exc->getMessage());
            }
            return array();
        }
    }

    public function getListLimit($arrCondition, $intPage, $intLimit, $strOrder) {
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

    public function add($p_arrParams) {
        try {
            if (!is_array($p_arrParams) || empty($p_arrParams)) {
                return false;
            }
            $result = $this->insert($p_arrParams);
            if ($result) {
                $result = $this->lastInsertValue;
                $p_arrParams['mess_id'] = $result;
                $instanceJob = new \My\Job\JobMessages();
                $instanceJob->addJob(SEARCH_PREFIX . 'writeMessages', $p_arrParams);

                //job send mail messages to user

                $instanceJob = new \My\Job\JobMessages();
                $instanceJob->addJob(SEARCH_PREFIX . 'writeMessages', $p_arrParams);
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

    public function edit($p_arrParams, $id) {
        try {
            if (!is_array($p_arrParams) || empty($p_arrParams) || empty($id)) {
                return false;
            }
            $result = $this->update($p_arrParams, 'mess_id=' . $id);
            if ($result) {
                $p_arrParams['mess_id'] = $id;
                $instanceJob = new \My\Job\JobMessages();
                $instanceJob->addJob(SEARCH_PREFIX . 'editMessages', $p_arrParams);
            }
            return $result;
        } catch (\Zend\Http\Exception $exc) {
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
                $instanceJob = new \My\Job\JobMessages();
                $instanceJob->addJob(SEARCH_PREFIX . 'multiEditMessages', $arrData);
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

        if (isset($arrCondition['favo_id'])) {
            $strWhere .= " AND favo_id=" . $arrCondition['favo_id'];
        }

        if (isset($arrCondition['status'])) {
            $strWhere .= " AND status=" . $arrCondition['status'];
        }

        if (isset($arrCondition['user_id'])) {
            $strWhere .= " AND user_id=" . $arrCondition['user_id'];
        }

        if (isset($arrCondition['in_mess_id'])) {
            $strWhere .= " AND mess_id IN (" . $arrCondition['in_mess_id'] . ")";
        }

        return $strWhere;
    }

}
