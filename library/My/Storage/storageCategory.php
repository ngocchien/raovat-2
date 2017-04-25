<?php

namespace My\Storage;

use Zend\Db\TableGateway\AbstractTableGateway,
    Zend\Db\Sql\Sql,
    Zend\Db\Adapter\Adapter,
    Zend\Db\Sql\Where,
    Zend\Db\Sql\Select,
    My\Validator\Validate;

class storageCategory extends AbstractTableGateway {

    protected $table = 'tbl_categories';

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
                    ->order(array('cate_sort ASC', 'cate_slug ASC'));
            $query = $sql->getSqlStringForSqlObject($select);
            return $adapter->query($query, $adapter::QUERY_MODE_EXECUTE)->toArray();
        } catch (\Zend\Http\Exception $exc) {
            echo '<pre>';
            print_r($exc->getMesseges());
            echo '</pre>';
            die();
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
                $p_arrParams['cate_id'] = $result;
                $instanceJob = new \My\Job\JobCategory();
                $instanceJob->addJob(SEARCH_PREFIX . 'writeCategory', $p_arrParams);
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

    public function edit($p_arrParams, $intCateID) {
        try {
            if (!is_array($p_arrParams) || empty($p_arrParams) || empty($intCateID)) {
                return false;
            }
            $result = $this->update($p_arrParams, 'cate_id=' . $intCateID);
            if ($result) {
                $p_arrParams['cate_id'] = $intCateID;
                $instanceJob = new \My\Job\JobCategory();
                $instanceJob->addJob(SEARCH_PREFIX . 'editCategory', $p_arrParams);
            }
            return $result;
        } catch (\Zend\Http\Exception $exc) {
            if (APPLICATION_ENV !== 'production') {
                die($exc->getMessage());
            }
            return false;
        }
    }

    public function updateTree($dataUpdate) {
        $adapter = $this->adapter;
        $sql = new Sql($adapter);
        $query = "update " . $this->table . " set cate_grade = REPLACE(cate_grade,'" . $dataUpdate['cate_grade'] . "','" . $dataUpdate['grade_update'] . "'),cate_status =" . $dataUpdate['cate_status'] . " WHERE cate_grade LIKE '" . $dataUpdate['cate_grade'] . "%'";
        $result = $adapter->query($query, $adapter::QUERY_MODE_EXECUTE);
        $resultSet = new \Zend\Db\ResultSet\ResultSet();
        $resultSet->initialize($result);
        $result = $resultSet->count() ? true : false;
        return $result;
    }

    public function updateStatusTree($dataUpdate) {
        $adapter = $this->adapter;
        $sql = new Sql($adapter);
        $query = "update " . $this->table . " set cate_status = " . $dataUpdate['cate_status'] . " WHERE cate_grade LIKE '" . $dataUpdate['grade_update'] . "%'";
        $result = $adapter->query($query, $adapter::QUERY_MODE_EXECUTE);
        $resultSet = new \Zend\Db\ResultSet\ResultSet();
        $resultSet->initialize($result);
        $result = $resultSet->count() ? true : false;
        return $result;
    }

    private function _buildWhere($arrCondition) {

        $strWhere = '';

        if (isset($arrCondition['cate_id'])) {
            $strWhere .= " AND cate_id=" . $arrCondition['cate_id'];
        }

        if (isset($arrCondition['cate_status'])) {
            $strWhere .= " AND cate_status=" . $arrCondition['cate_status'];
        }

        if (!empty($arrCondition['cate_name'])) {
            $strWhere .= " AND cate_name = '" . $arrCondition['cate_name'] . "'";
        }

        if (!empty($arrCondition['cate_slug'])) {
            $strWhere .= " AND cate_slug = '" . $arrCondition['cate_slug'] . "'";
        }

        if (isset($arrCondition['cate_parent'])) {
            $strWhere .= " AND cate_parent=" . $arrCondition['cate_parent'];
        }

        if (isset($arrCondition['cate_type'])) {
            $strWhere .= " AND cate_type=" . $arrCondition['cate_type'];
        }

        if (isset($arrCondition['not_cate_status'])) {
            $strWhere .= " AND cate_status !=" . $arrCondition['not_cate_status'];
        }

        if (isset($arrCondition['not_cate_id'])) {
            $strWhere .= " AND cate_id !=" . $arrCondition['not_cate_id'];
        }

        if (isset($arrCondition['parent_id'])) {
            $strWhere .= " AND parent_id =" . $arrCondition['parent_id'];
        }



        return $strWhere;
    }

}
