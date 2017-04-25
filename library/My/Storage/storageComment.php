<?php

namespace My\Storage;

use Zend\Db\TableGateway\AbstractTableGateway,
    Zend\Db\Adapter\Adapter,
    Zend\Db\Sql\Sql,
    Zend\Db\Sql\Where,
    My\Validator\Validate;

class storageComment extends AbstractTableGateway {

    protected $table = 'tbl_comments';
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
                    ->order(array('comm_id DESC'));
            $query = $sql->getSqlStringForSqlObject($select);
            return $adapter->query($query, $adapter::QUERY_MODE_EXECUTE)->toArray();
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
                $p_arrParams['comm_id'] = $result;
                $instanceJob = new \My\Job\JobComment();
                $instanceJob->addJob(SEARCH_PREFIX . 'writeComment', $p_arrParams);
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
    public function getListParentLimit($arrCondition, $intPage, $intLimit) {
        try {
            $strWhere = $this->_buildWhere($arrCondition);
            $adapter = $this->adapter;
            $query =    'select *
                        from (select * from tbl_comments where 1=1' . $strWhere . ') as v1 
                        LEFT JOIN (SELECT count(comm_parent) as num, comm_parent, max(comm_created) as times
                        from tbl_comments
                        where comm_status = 0 and comm_parent != 0
                        GROUP BY comm_parent) as v2
                        on v1.comm_id = v2.comm_parent
                        ORDER BY times DESC'
                        . ' limit ' . $intLimit
                        . ' offset ' . ($intLimit * ($intPage - 1));
            //p($query);die;
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
            $result = $adapter->query($query, $adapter::QUERY_MODE_EXECUTE);
            return $result->toArray();
        } catch (\Zend\Http\Exception $exc) {
            if (APPLICATION_ENV !== 'production') {
                die($exc->getMessage());
            }
            return array();
        }
    }
    

    public function getListLimitInProduct($arrCondition, $intPage, $intLimit, $strOrder) {
        try {
            $strWhere = $this->_buildWhere($arrCondition);
            $strWhere .= " OR ( comm_ip='" . $arrCondition['comm_ip'] . "' AND comm_status =0 AND comm_parent=" . $arrCondition['comm_parent'] . " AND prod_id=" . $arrCondition['prod_id'] . ")";
//            p($strWhere);die;
            $adapter = $this->adapter;
            $sql = new Sql($adapter);
            $select = $sql->Select($this->table);
            $select->where('1=1' . $strWhere)
                    ->order($strOrder)
                    ->limit($intLimit)
                    ->offset($intLimit * ($intPage - 1));
            $query = $sql->getSqlStringForSqlObject($select);
            $result = $adapter->query($query, $adapter::QUERY_MODE_EXECUTE);
            return $result->toArray();
        } catch (\Zend\Http\Exception $exc) {
            if (APPLICATION_ENV !== 'production') {
                die($exc->getMessage());
            }
            return array();
        }
    }
    
    public function getListLimitCommentInContent($arrCondition, $intPage, $intLimit, $strOrder) {
        try {
            $strWhere = $this->_buildWhere($arrCondition);
            $strWhere .= " OR ( comm_ip='" . $arrCondition['comm_ip'] . "' AND comm_status =0 AND comm_parent=" . $arrCondition['comm_parent'] . " AND cont_id=" . $arrCondition['cont_id'] . ")";
//            p($strWhere);die;
            $adapter = $this->adapter;
            $sql = new Sql($adapter);
            $select = $sql->Select($this->table);
            $select->where('1=1' . $strWhere)
                    ->order($strOrder)
                    ->limit($intLimit)
                    ->offset($intLimit * ($intPage - 1));
            $query = $sql->getSqlStringForSqlObject($select);
            $result = $adapter->query($query, $adapter::QUERY_MODE_EXECUTE);
            return $result->toArray();
        } catch (\Zend\Http\Exception $exc) {
            if (APPLICATION_ENV !== 'production') {
                die($exc->getMessage());
            }
            return array();
        }
    }
    
    

    public function getListChildren($arrCondition) {
        try {
            $strWhere = $this->_buildWhere($arrCondition);
            $strWhere .= " OR ( comm_ip='" . $arrCondition['comm_ip'] . "' AND comm_status =0 AND comm_parent IN (" . $arrCondition['listIdParen'] . ')) ORDER BY comm_id DESC';
//            p($strWhere);DIE;
            $adapter = $this->adapter;
            $sql = new Sql($adapter);
            $select = $sql->Select($this->table);
            $select->where('1=1' . $strWhere);
            $query = $sql->getSqlStringForSqlObject($select);
//            p($query);die;
            $result = $adapter->query($query, $adapter::QUERY_MODE_EXECUTE);
            return $result->toArray();
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
            //     $strWhere .= " OR ( comm_ip='" . $arrCondition['comm_ip'] . "' AND comm_status =0 AND comm_parent=".$arrCondition['comm_parent']." AND prod_id=".$arrCondition['prod_id'].")";
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
    
    public function getTotalInProduct($arrCondition) {
        try {
            $strWhere = $this->_buildWhere($arrCondition);
            $strWhere .= " OR ( comm_ip='" . $arrCondition['comm_ip'] . "' AND comm_status =0 AND comm_parent=".$arrCondition['comm_parent']." AND prod_id=".$arrCondition['prod_id'].")";
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
    
    public function getTotalCommentInContent($arrCondition) {
        try {
            $strWhere = $this->_buildWhere($arrCondition);
            $strWhere .= " OR ( comm_ip='" . $arrCondition['comm_ip'] . "' AND comm_status =0 AND comm_parent=".$arrCondition['comm_parent']." AND cont_id=".$arrCondition['cont_id'].")";
            $adapter = $this->adapter;
            $sql = new Sql($adapter);
            $select = $sql->Select($this->table)
                    ->columns(array('total' => new \Zend\Db\Sql\Expression('COUNT(*)')))
                    ->where('1=1' . $strWhere);
            $query = $sql->getSqlStringForSqlObject($select);
           // p($query);die;
            return (int) current($adapter->query($query, $adapter::QUERY_MODE_EXECUTE)->toArray())['total'];
        } catch (\Zend\Http\Exception $exc) {
            if (APPLICATION_ENV !== 'production') {
                die($exc->getMessage());
            }
            return false;
        }
    }
    
    
    
    
    
    public function edit($p_arrParams, $intCommentID) {
        try {
            if (!is_array($p_arrParams) || empty($p_arrParams) || empty($intCommentID)) {
                return false;
            }
            $result = $this->update($p_arrParams, 'comm_id=' . $intCommentID);
            if($result){
                $p_arrParams['comm_id'] = $intCommentID;
                $instanceJob = new \My\Job\JobComment();
                $instanceJob->addJob(SEARCH_PREFIX . 'editComment', $p_arrParams);
            }
            return $result;
        } catch (\Zend\Http\Exception $exc) {
            if (APPLICATION_ENV !== 'production') {
                die($exc->getMessage());
            }
            return false;
        }
    }

    public function editlike($intCommentID) {
        $adapter = $this->adapter;
        $sql = new Sql($adapter);
        $query = "update " . $this->table . " set comm_like = comm_like + 1 WHERE comm_id=" . $intCommentID;
//        p($query);die;
        $result = $adapter->query($query, $adapter::QUERY_MODE_EXECUTE);
        $resultSet = new \Zend\Db\ResultSet\ResultSet();
        $resultSet->initialize($result);
        $result = $resultSet->count() ? true : false;
        return $result;
    }

    private function _buildWhere($arrCondition) {
        $strWhere = '';

        if (isset($arrCondition['user_id'])) {
            $strWhere .= " AND user_id=" . $arrCondition['user_id'];
        }

        if (isset($arrCondition['comm_parent'])) {
            $strWhere .= " AND comm_parent=" . $arrCondition['comm_parent'];
        }
        if (isset($arrCondition['listIdParen'])) {
            $strWhere .= " AND comm_parent in (" . $arrCondition['listIdParen'] . ')';
        }

        if (isset($arrCondition['prod_id'])) {
            $strWhere .= " AND prod_id=" . $arrCondition['prod_id'];
        }
        if (isset($arrCondition['cont_id'])) {
            $strWhere .= " AND cont_id=" . $arrCondition['cont_id'];
        }
        if (isset($arrCondition['user_email'])) {
            $strWhere .= " AND user_email=" . (int) $arrCondition['user_email'];
        }

        if (isset($arrCondition['comm_status'])) {
            $strWhere .= " AND comm_status =" . (int) $arrCondition['comm_status'];
        }

        if (isset($arrCondition['not_comm_status'])) {
            $strWhere .= " AND comm_status !=" . (int) $arrCondition['not_comm_status'];
        }

//        if (isset($arrCondition['comm_ip'])) {
//            $strWhere .= " OR ( comm_ip='" . $arrCondition['comm_ip'] . "' AND comm_status =0 AND comm_parent=".$arrCondition['comm_parent']." AND prod_id=".$arrCondition['prod_id'].")";
//        }

        if (isset($arrCondition['content_or_fullname_or_email']) && $arrCondition['content_or_fullname_or_email']) {
            $keyword = '*' . $arrCondition['content_or_fullname_or_email'] . '*';
            $strWhere .= " AND ( MATCH(comm_content) AGAINST ('" . $keyword . "'  IN BOOLEAN MODE))";
        }
        
        if (isset($arrCondition['comm_type'])) {
            $strWhere .= " AND comm_type=" . $arrCondition['comm_type'];
        }
        
        if (isset($arrCondition['comm_id'])) {
            $strWhere .= " AND comm_id=" . $arrCondition['comm_id'];
        }
        if (isset($arrCondition['comm_id_or_parent_id'])) {
            $strWhere .= " AND ( comm_id = " . $arrCondition['comm_id_or_parent_id'] . " Or comm_parent = " . $arrCondition['comm_id_or_parent_id'] . " )";
        }
        
        if (isset($arrCondition['cont_id'])) {
            $strWhere .= " AND cont_id=" . $arrCondition['cont_id'];
        }
        


//        if (isset($arrCondition['comm_ip'])) {
//            $strWhere .= " AND comm_ip=" . (int) $arrCondition['comm_ip'];
//        }
//        if()
//        ->join('tbl_products', 'tbl_comments.prod_id = tbl_products.prod_id')
//                    ->join('tbl_users', 'tbl_comments.user_id = tbl_users.user_id')
//        
        //content_of_fullname_or_email
        return $strWhere;
    }

}
