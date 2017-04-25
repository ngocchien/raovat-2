<?php

namespace My\Storage;

use Zend\Db\Sql\Sql,
    Zend\Db\Adapter\Adapter,
    Zend\Db\TableGateway\AbstractTableGateway;

class storagePermission extends AbstractTableGateway {

    protected $table = 'tbl_permisions';

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
                    ->order(array('perm_id ASC'));
            $query = $sql->getSqlStringForSqlObject($select);
            return $adapter->query($query, $adapter::QUERY_MODE_EXECUTE)->toArray();
        } catch (\Exception $exc) {
            echo '<pre>';
            print_r($exc->getMessage());
            echo '</pre>';
            die();
            if (APPLICATION_ENV !== 'production') {
                die($exc->getMessage());
            }
            return array();
        }
    }

    public function getListjoinRole($arrCondition = array()) {
        try {

            $strWhere = $this->_buildWhere($arrCondition);

            $adapter = $this->adapter;
            $sql = new Sql($adapter);
            $select = $sql->Select($this->table)
                    ->join('tbl_roles', 'tbl_roles.role_id = ' . $this->table . '.role_id')
                    ->where('1=1' . $strWhere)
                    //  ->join('tbl_roles', 'tbl_roles.id = ' .  . '.role_id')
                    ->order(array('perm_id DESC'));
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
            $select = $sql->Select($this->table);
            $select->where('1=1' . $strWhere)
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
                die($exc->getMessage());
            }
            return false;
        }
    }

    public function getDetail($arrCondition) {
        try {
            $strWhere = $this->_buildWhere($arrCondition);

            $adapter = $this->adapter;
            $sql = new Sql($adapter);
            $select = $sql->Select($this->table)
                    ->where('1=1' . $strWhere)
                    ->order(array('perm_id DESC'));
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
                $p_arrParams['perm_id'] = $result;
                $instanceJob = new \My\Job\JobPermission();
                $instanceJob->addJob(SEARCH_PREFIX . 'writePermission', $p_arrParams);
            }
            return $result;
        } catch (\Exception $exc) {
            echo '<pre>';
            print_r($exc->getMessage());
            echo '</pre>';
            die();
        }
    }

    public function addAll($p_arrParams) {
        if (!is_array($p_arrParams) || empty($p_arrParams)) {
            return false;
        }
        $adapter = $this->adapter;

        $p_arrParams = array_reverse($p_arrParams);

        $strInsertQuery = 'INSERT INTO ' . $this->table . ' (perm_id,role_id,) VALUES ';

        foreach ($p_arrParams as $item) {
            $permissionID = $item['perm_id'] != '' ? $item['perm_id'] : 'NULL';
            $userRole = $item['role_id'] != '' ? $item['role_id'] : 'NULL';
            //$userID = $item['user_id'] != '' ? $item['user_id'] : 'NULL';
            $strInsertQuery .= "("
                    . "{$permissionID}, "
                    . "{$userRole}, "
                    //. "{$userID}, "
                    //. "'{$item['module_name']}', "
                    //. "'{$item['controller_name']}', "
                    //. "'{$item['action_name']}', "
                    //. "{$item['is_allowed']} "
                    . "), ";
        }
        $strInsertQuery = rtrim($strInsertQuery, ', ') . ';';
        try {
            $adapter->createStatement($strInsertQuery)->execute();
            $result = $this->adapter->getDriver()->getLastGeneratedValue();

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

    public function edit($p_arrParams, $intPermissionID) {
        try {
            $result = array();
            if (!is_array($p_arrParams) || empty($p_arrParams) || empty($intPermissionID)) {
                return $result;
            }
            $result = $this->update($p_arrParams, 'perm_id=' . $intPermissionID);
            if($result){
                $p_arrParams['perm_id'] = $intPermissionID;
                $instanceJob = new \My\Job\JobPermission();
                $instanceJob->addJob(SEARCH_PREFIX . 'editPermission', $p_arrParams);
            }
            return $result;

        } catch (\Zend\Http\Exception $exc) {
            if (APPLICATION_ENV !== 'production') {
                die($exc->getMessage());
            }
            return false;
        }
    }

    public function editBy($p_arrParams, $strParams) {

        try {
            $result = array();
            if (!is_array($p_arrParams) || empty($p_arrParams) || empty($strParams)) {
                return $result;
            }
            return $this->update($p_arrParams, $strParams);
        } catch (\Zend\Http\Exception $exc) {
            if (APPLICATION_ENV !== 'production') {
                die($exc->getMessage());
            }
            return false;
        }
    }

    public function remove($arrCondition) {
        try {
            if (!is_array($arrCondition) || empty($arrCondition)) {
                return false;
            }
            $where = array();
            if (isset($arrCondition['role_id'])) {
                $where = array_merge($where, array('role_id' => $arrCondition['role_id']));
            }
            if (isset($arrCondition['grou_id'])) {
                $where = array_merge($where, array('user_id' => $arrCondition['grou_id']));
            }
            return $this->delete($where);
        } catch (\Zend\Http\Exception $exc) {
            if (APPLICATION_ENV !== 'production') {
                die($exc->getMessage());
            }
            return false;
        }
    }

    private function _buildWhere($arrCondition) {
        $strWhere = '';

        if (isset($arrCondition['group_id'])) {
            $strWhere .= ' AND group_id=' . $arrCondition['group_id'];
        }

        if (isset($arrCondition['perm_status'])) {
            $strWhere .= ' AND perm_status=' . $arrCondition['perm_status'];
        }

        if (isset($arrCondition['user_id'])) {
            $strWhere .= ' AND user_id=' . $arrCondition['user_id'];
        }

        if (isset($arrCondition['module'])) {
            $strWhere .= ' AND module="' . $arrCondition['module'] . '"';
        }

        if (isset($arrCondition['controller'])) {
            $strWhere .= ' AND controller="' . $arrCondition['controller'] . '"';
        }

        if (isset($arrCondition['action'])) {
            $strWhere .= ' AND action="' . $arrCondition['action'] . '"';
        }

        if (isset($arrCondition['not_perm_status'])) {
            $strWhere .= ' AND perm_status !=' . $arrCondition['not_perm_status'];
        }

        if (isset($arrCondition['or_group_id']) && isset($arrCondition['or_user_id'])) {
            $strWhere .= ' AND ( group_id = ' . $arrCondition['or_group_id'] . ' OR user_id = ' . $arrCondition['or_user_id'] . ')';
        }

        return $strWhere;
    }

}
