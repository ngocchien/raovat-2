<?php

namespace My\Storage;

use Zend\Db\TableGateway\AbstractTableGateway,
    Zend\Db\Adapter\Adapter,
    Zend\Db\Sql\Sql,
    My\Validator\Validate;

class storageTags extends AbstractTableGateway {

    protected $table = 'tbl_tags';
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
                    ->order(array('tags_sort ASC'));
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
    
    public function getListUnlike($arrCondition) {
        try {
            $strWhere = $this->_buildWhere($arrCondition);
            $adapter = $this->adapter;
            $sql = new Sql($adapter);
            $select = $sql->Select($this->table)
                    ->where('1=1' . $strWhere)
                    ->order(array('tags_sort ASC'));
            $query = $sql->getSqlStringForSqlObject($select);
            return $adapter->query($query, $adapter::QUERY_MODE_EXECUTE)->toArray();
        } catch (\Zend\Http\Exception $exc) {
            if (APPLICATION_ENV !== 'production') {
                throw new \Zend\Http\Exception($exc->getMessage());
            }
            return array();
        }
    }
    
    public function updateTree($dataUpdate) {
        $adapter = $this->adapter;
        $sql = new Sql($adapter);
        $query = "update " . $this->table . " set tags_grade = REPLACE(tags_grade,'" . $dataUpdate['tags_grade'] . "','" . $dataUpdate['grade_update']."'),tags_status =".$dataUpdate['tags_status'].",tags_sort = REPLACE(tags_sort,'" . $dataUpdate['tags_sort'] . "','" . $dataUpdate['sort_update']."') WHERE tags_grade LIKE '" . $dataUpdate['tags_grade'] . "%'";
        $result = $adapter->query($query, $adapter::QUERY_MODE_EXECUTE);
        $resultSet = new \Zend\Db\ResultSet\ResultSet();
        $resultSet->initialize($result);
        $result = $resultSet->count() ? true : false;
        return $result;
    }
    
    public function updateStatusTree($dataUpdate) {
        $adapter = $this->adapter;
        $sql = new Sql($adapter);
        $query = "update " . $this->table . " set tags_status = ".$dataUpdate['tags_status']." WHERE tags_grade LIKE '" . $dataUpdate['grade_update'] . "%'";
        $result = $adapter->query($query, $adapter::QUERY_MODE_EXECUTE);
        $resultSet = new \Zend\Db\ResultSet\ResultSet();
        $resultSet->initialize($result);
        $result = $resultSet->count() ? true : false;
        return $result;
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
            }
            return $result;
        } catch (\Zend\Http\Exception $exc) {
            if (APPLICATION_ENV !== 'production') {
                throw new \Zend\Http\Exception($exc->getMessage());
            }
            return false;
        }
    }

    public function edit($p_arrParams, $intTagID) {
        try {
            $result = array();
            if (!is_array($p_arrParams) || empty($p_arrParams) || empty($intTagID)) {
                return $result;
            }
            return $this->update($p_arrParams, 'tags_id=' . $intTagID);
        } catch (\Zend\Http\Exception $exc) {
            if (APPLICATION_ENV !== 'production') {
                die($exc->getMessage());
            }
            return false;
        }
    }

    private function _buildWhere($arrCondition) {
        
        $strWhere = null;
        
        if (isset($arrCondition['tags_id'])) {
            $strWhere .= " AND tags_id=" . $arrCondition['tags_id'];
        }
        
        if ($arrCondition['tags_slug'] !== '' && $arrCondition['tags_slug'] !== NULL) {
            $strWhere .= " AND tags_slug='" . $arrCondition['tags_slug']."'";
        }

        if (isset($arrCondition['tags_name']) && $arrCondition['tags_name']) {
            $strWhere .= " AND LOWER(tags_name)='" . strtolower($arrCondition['tags_name']) . "'";
        }

        if (isset($arrCondition['tags_desctiprion']) && $arrCondition['tag_desctiprion']) {
            $strWhere .= " AND tag_desctiprion=" . $arrCondition['tag_desctiprion'];
        }

        if (isset($arrCondition['tags_meta_title']) && $arrCondition['tag_meta_title']) {
            $strWhere .= " AND tag_meta_title=" . $arrCondition['tag_meta_title'];
        }

        if (isset($arrCondition['tags_meta_keyword']) && $arrCondition['tag_meta_keyword']) {
            $strWhere .= " AND tag_meta_keyword=" . $arrCondition['tag_meta_keyword'];
        }

        if (isset($arrCondition['tags_meta_desctiption']) && $arrCondition['tag_meta_desctiption']) {
            $strWhere .= " AND tag_meta_desctiption=" . $arrCondition['tag_meta_desctiption'];
        }

        if (isset($arrCondition['tags_status']) && $arrCondition['tags_status']) {
            $strWhere .= " AND tags_status=" . $arrCondition['tags_status'];
        }
        
        if (isset($arrCondition['not_tags_id'])) {
            $strWhere .= " AND tags_id !=" . $arrCondition['not_tags_id'];
        }
        
        if (isset($arrCondition['not_tags_status']) && $arrCondition['not_tags_status']) {
            $strWhere .= " AND tags_status != " . $arrCondition['not_tags_status'];
        }
        if (isset($arrCondition['tags_name_like']) && $arrCondition['tags_name_like']) {
            $strWhere .= " AND tags_name LIKE '%" . $arrCondition['tags_name_like'] . "%'";
        }

        if (isset($arrCondition['tags_meta_desctiption']) && $arrCondition['tag_meta_desctiption']) {
            $strWhere .= " AND tag_meta_desctiption=" . $arrCondition['tag_meta_desctiption'];
        }
        
        if (isset($arrCondition['in_tags_id'])) { 
            $strWhere .= " AND tags_id IN(" . $arrCondition['in_tags_id'] . ")";
        }
        
        if ($arrCondition['tags_grade'] !== '' && $arrCondition['tags_grade'] !== NULL) {
            $strWhere .= ' AND tags_grade NOT LIKE "%' . $arrCondition['tags_grade'] . ':%"';
        }
        
        if ($arrCondition['tagsgrade'] !== '' && $arrCondition['tagsgrade'] !== NULL) {
            $strWhere .= ' AND tags_grade LIKE "%' . $arrCondition['tagsgrade'] . ':%"';
        }
        return $strWhere;
    }

}
