<?php

namespace My\Storage;

use Zend\Db\TableGateway\AbstractTableGateway,
    Zend\Db\Adapter\Adapter,
    Zend\Db\Sql\Sql,
    Zend\Db\Sql\Predicate\Like,
    My\Validator\Validate;

class storageProperties extends AbstractTableGateway {

    protected $table = 'tbl_properties';
    protected $adapter;

    public function __construct(Adapter $adapter) {
        $adapter->getDriver()->getConnection()->connect();
        $this->adapter = $adapter;
    }

    public function __destruct() {
        $this->adapter->getDriver()->getConnection()->disconnect();
    }

    public function getList($arrCondition = null) {
        try {
            $strWhere = $this->_buildWhere($arrCondition);
            $adapter = $this->adapter;
            $sql = new Sql($adapter);
            $select = $sql->Select($this->table)
                    ->where('1=1' . $strWhere)
                    ->order(array('prop_grade ASC'));
            $query = $sql->getSqlStringForSqlObject($select);
            return $adapter->query($query, $adapter::QUERY_MODE_EXECUTE)->toArray();
        } catch (\Zend\Http\Exception $exc) {
            if (APPLICATION_ENV !== 'production') {
                throw new \Zend\Http\Exception($exc->getMessage());
            }
            return array();
        }
    }

    public function getListLimit($arrCondition, $intPage, $intLimit, $strOrder) {
        try {
            $strWhere = $this->_buildWhere($arrCondition);
            $adapter = $this->adapter;
            $sql = new Sql($adapter);
            $select = $sql->Select($this->table);
            $select->where('1=1' . $strWhere)
                    ->order($strOrder)
                    ->limit($intLimit)
                    ->offset($intLimit * ($intPage - 1));
            $query = $sql->getSqlStringForSqlObject($select);
            return $adapter->query($query, $adapter::QUERY_MODE_EXECUTE)->toArray();
        } catch (\Zend\Http\Exception $exc) {
            if (APPLICATION_ENV !== 'production') {
                throw new \Zend\Http\Exception($exc->getMessage());
            }
            return array();
        }
    }

    public function getTotal($arrCondition) {
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
                throw new \Zend\Http\Exception($exc->getMessage());
            }
            return false;
        }
    }

    public function getDetail($arrCondition) {
        try {
            $strWhere = $this->_buildWhere($arrCondition);
            $adapter = $this->adapter;
            $sql = new Sql($adapter);
            $select = $sql->Select($this->table)->where('1=1' . $strWhere);
            $query = $sql->getSqlStringForSqlObject($select);
            return current($adapter->query($query, $adapter::QUERY_MODE_EXECUTE)->toArray());
        } catch (\Zend\Http\Exception $exc) {
            if (APPLICATION_ENV !== 'production') {
                throw new \Zend\Http\Exception($exc->getMessage());
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
                $p_arrParams['prop_id'] = $result;
                $instanceJob = new \My\Job\JobProperties();
                $instanceJob->addJob(SEARCH_PREFIX . 'writeProperties', $p_arrParams);
            }
            return $result;
        } catch (\Zend\Http\Exception $exc) {
            if (APPLICATION_ENV !== 'production') {
                throw new \Zend\Http\Exception($exc->getMessage());
            }
            return false;
        }
    }

    public function edit($p_arrParams, $intPropertiID) {
        if (!is_array($p_arrParams) || empty($p_arrParams) || empty($intPropertiID)) {
            return false;
        }

        try {
            $result = $this->update($p_arrParams, 'prop_id=' . $intPropertiID);
            if ($result) {
                $p_arrParams['prop_id'] = $intPropertiID;
                $instanceJob = new \My\Job\JobProperties();
                $instanceJob->addJob(SEARCH_PREFIX . 'editProperties', $p_arrParams);
            }
            return $result;
        } catch (\Exception $exc) {
            echo '<pre>';
            print_r($exc->getMessage());
            echo '</pre>';
            die();
            if (APPLICATION_ENV !== 'production') {
                throw new \Zend\Http\Exception($exc->getMessage());
            }
            return false;
        }
    }

    public function updateTree($dataUpdate) {
        $adapter = $this->adapter;
        $sql = new Sql($adapter);
        $query = "update " . $this->table . " set prop_grade = REPLACE(prop_grade,'" . $dataUpdate['prop_grade'] . "','" . $dataUpdate['grade_update'] . "'),prop_status =" . $dataUpdate['prop_status'] . " WHERE prop_grade LIKE '" . $dataUpdate['prop_grade'] . "%'";
        $result = $adapter->query($query, $adapter::QUERY_MODE_EXECUTE);
        $resultSet = new \Zend\Db\ResultSet\ResultSet();
        $resultSet->initialize($result);
        $result = $resultSet->count() ? true : false;
        return $result;
    }

    public function updateStatusTree($dataUpdate) {
        $adapter = $this->adapter;
        $sql = new Sql($adapter);
        $query = "update " . $this->table . " set prop_status = " . $dataUpdate['prop_status'] . " WHERE prop_grade LIKE '" . $dataUpdate['grade_update'] . "%'";
        $result = $adapter->query($query, $adapter::QUERY_MODE_EXECUTE);
        $resultSet = new \Zend\Db\ResultSet\ResultSet();
        $resultSet->initialize($result);
        $result = $resultSet->count() ? true : false;
        return $result;
    }

    private function _buildWhere($arrCondition) {
        $strWhere = null;

        if (empty($arrCondition)) {
            return $strWhere;
        }
        if ($arrCondition['prop_id'] !== '' && $arrCondition['prop_id'] !== NULL) {
            $strWhere .= " AND prop_id=" . $arrCondition['prop_id'];
        }

        if ($arrCondition['not_prop_id'] !== '' && $arrCondition['not_prop_id'] !== NULL) {
            $strWhere .= " AND prop_id !=" . $arrCondition['not_prop_id'];
        }

        if (isset($arrCondition['prop_name']) && $arrCondition['prop_name']) {
            $strWhere .= " AND prop_name='" . $arrCondition['prop_name'] . "'";
        }
        if (isset($arrCondition['prop_slug']) && $arrCondition['prop_slug']) {
            $strWhere .= " AND prop_slug='" . $arrCondition['prop_slug'] . "'";
        }

        if ($arrCondition['parent_id'] !== '' && $arrCondition['parent_id'] !== NULL) {
            $strWhere .= " AND parent_id=" . $arrCondition['parent_id'];
        }

        if ($arrCondition['not_parent_id'] !== '' && $arrCondition['not_parent_id'] !== NULL) {
            $strWhere .= " AND parent_id !=" . $arrCondition['not_parent_id'];
        }

        if ($arrCondition['prop_status'] !== '' && $arrCondition['prop_status'] !== NULL) {
            $strWhere .= " AND prop_status=" . $arrCondition['prop_status'];
        }

        if ($arrCondition['not_prop_sort'] !== '' && $arrCondition['not_prop_sort'] !== NULL) {
            $strWhere .= " AND prop_sort !=" . $arrCondition['not_prop_sort'];
        }

        if ($arrCondition['not_prop_status'] !== '' && $arrCondition['not_prop_status'] !== NULL) {
            $strWhere .= " AND prop_status !=" . $arrCondition['not_prop_status'];
        }

        if ($arrCondition['prop_grade'] !== '' && $arrCondition['prop_grade'] !== NULL) {
            $strWhere .= ' AND prop_grade NOT LIKE "%' . $arrCondition['prop_grade'] . ':%"';
        }

        if ($arrCondition['propgrade'] !== '' && $arrCondition['propgrade'] !== NULL) {
            $strWhere .= ' AND prop_grade LIKE "%' . $arrCondition['propgrade'] . ':%"';
        }

        if (isset($arrCondition['prop_name_like']) && $arrCondition['prop_name_like']) {
            $keyword = "'%" . $arrCondition['prop_name_like'] . "%'";
            $strWhere .= ' AND prop_name LIKE ' . $keyword;
        }

        if (isset($arrCondition['listPropertiesID'])) {
            $strWhere .= " AND prop_id in (" . $arrCondition['listPropertiesID'] . ')';
        }

        if (!empty($arrCondition['listAllPropertiesID'])) {
            $strWhere .= " AND (prop_id in (" . $arrCondition['listAllPropertiesID'] . ') OR FIND_IN_SET(prop_parent,\'' . $arrCondition['listAllPropertiesID'] . '\'))';
        }

        return $strWhere;
    }

}
