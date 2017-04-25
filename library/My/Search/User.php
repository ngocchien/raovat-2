<?php

namespace My\Search;

use Elastica\Query\QueryString,
    Elastica\Type\Mapping,
    Elastica\Query\Bool,
    Elastica\Search,
    Elastica\Status,
    Elastica\Query as ESQuery,
    My\Search\SearchAbstract,
    My\General;

class User extends SearchAbstract {

    public function __construct() {
        $this->setSearchIndex(SEARCH_PREFIX . 'user');
        $this->setSearchType('userList');
    }

    public function createIndex() {
        $strIndexName = SEARCH_PREFIX . 'user';

        $searchClient = General::getSearchConfig();
        $searchIndex = $searchClient->getIndex($strIndexName);

        $objStatus = new Status($searchIndex->getClient());
        $arrIndex = $objStatus->getIndexNames();

        //delete index
        if (in_array($strIndexName, $arrIndex)) {
            $searchIndex->delete();
        }

        //create new index
        $searchIndex->create([
            'name' => 'translations',
            'number_of_shards' => 5,
            'number_of_replicas' => 0,
            'analysis' => [
                'analyzer' => [
                    'translation_index_analyzer' => [
                        'type' => 'custom',
                        'tokenizer' => 'standard',
                        'filter' => ['standard', 'lowercase', 'asciifolding', 'trim']
                    ],
                    'translation_search_analyzer' => [
                        'type' => 'custom',
                        'tokenizer' => 'standard',
                        'filter' => ['standard', 'lowercase', 'asciifolding', 'trim']
                    ]
                ]
            ],
            'filter' => [
                'translation' => [
                    'type' => 'edgeNGram',
                    'token_chars' => ["letter", "digit", " whitespace"],
                    'min_gram' => 1,
                    'max_gram' => 30,
                ]
            ],
                ], true);

        //set search type
        $searchType = $searchIndex->getType('userList');
        $mapping = new Mapping();
        $mapping->setType($searchType);
        $mapping->setProperties([
            'user_id' => ['type' => 'integer', 'index' => 'not_analyzed'],
            'user_name' => ['type' => 'string', 'store' => 'yes', 'analyzer' => 'translation_index_analyzer', 'search_analyzer' => 'translation_search_analyzer', 'term_vector' => 'with_positions_offsets'],
            'user_email' => ['type' => 'string', 'index' => 'not_analyzed'],
            'user_phone' => ['type' => 'string', 'index' => 'not_analyzed'],
            'user_created' => ['type' => 'integer', 'index' => 'not_analyzed'],
            'created_date' => ['type' => 'long', 'index' => 'not_analyzed'],
            'user_updated' => ['type' => 'integer', 'index' => 'not_analyzed'],
            'updated_date' => ['type' => 'long', 'index' => 'not_analyzed'],
            'user_avatar' => ['type' => 'string', 'index' => 'not_analyzed'],
            'user_status' => ['type' => 'integer', 'index' => 'not_analyzed'],
            'group_id' => ['type' => 'integer', 'index' => 'not_analyzed'],
            'user_password' => ['type' => 'string', 'index' => 'not_analyzed'],
            'user_last_login' => ['type' => 'long', 'index' => 'not_analyzed'],
            'user_login_ip' => ['type' => 'string', 'index' => 'not_analyzed'],
            'user_fullname' => ['type' => 'string', 'store' => 'yes', 'analyzer' => 'translation_index_analyzer', 'search_analyzer' => 'translation_search_analyzer', 'term_vector' => 'with_positions_offsets'],
            'user_gender' => ['type' => 'integer', 'index' => 'not_analyzed'],
            'user_birthday' => ['type' => 'long', 'index' => 'not_analyzed'],
            'modified_date' => ['type' => 'long', 'index' => 'not_analyzed'],
        ]);
        $mapping->send();
    }

    public function add($arrDocument) {
        try {
            if (empty($arrDocument) && !$arrDocument instanceof \Elastica\Document) {
                throw new \Exception('Document cannot be blank or must be instance of \Elastica\Document class');
            }
            $arrDocument = is_array($arrDocument) ? $arrDocument : [$arrDocument];
            $this->getSearchType()->addDocuments($arrDocument);
            $this->getSearchType()->getIndex()->refresh();
            return true;
        } catch (\Zend\Http\Exception $exc) {
            if (APPLICATION_ENV !== 'production') {
                throw new \Zend\Http\Exception($exc->getMessage());
            }
            return false;
        }
    }

    public function edit($document) {
        $respond = $this->getSearchType()->updateDocument($document);
        $this->getSearchType()->getIndex()->refresh();
        if ($respond->isOk()) {
            return true;
        }
        return false;
    }

    public function getDetail($params, $arrFields = []) {
        try {
            $boolQuery = new Bool();
            $boolQuery = $this->__buildWhere($params, $boolQuery);
            $query = new ESQuery();
            $query->setQuery($boolQuery);

            if ($arrFields && is_array($arrFields)) {
                $query->setSource($arrFields);
            }

            $instanceSearch = new Search(General::getSearchConfig());
            $resultSet = $instanceSearch->addIndex($this->getSearchIndex())
                    ->addType($this->getSearchType())
                    ->search($query);
            $this->setResultSet($resultSet);
            return current($this->toArray());
        } catch (\Exception $exc) {
            echo '<pre>';
            print_r($exc->getMessage());
            echo '</pre>';
            die();
        }
    }

    /**
     * Get List Limit
     */
    public function getListLimit($params = array(), $intPage = 1, $intLimit = 15, $sort = ['updated_date' => ['order' => 'desc']]) {
        try {
            $intFrom = $intLimit * ($intPage - 1);
            $boolQuery = new Bool();
            $boolQuery = $this->__buildWhere($params, $boolQuery);
            $query = new ESQuery();
            $query->setFrom($intFrom)
                    ->setSize($intLimit)
                    ->setSort($sort);
            $query->setQuery($boolQuery);
            $instanceSearch = new Search(General::getSearchConfig());
            $resultSet = $instanceSearch->addIndex($this->getSearchIndex())
                    ->addType($this->getSearchType())
                    ->search($query);
            $this->setResultSet($resultSet);
            return $this->toArray();
        } catch (\Exception $exc) {
            echo $exc->getMessage();
            die;
        }
    }

    /**
     * Get List
     */
    public function getList($params, $arrFields = []) {
        $boolQuery = new Bool();
        $boolQuery = $this->__buildWhere($params, $boolQuery);
        $query = new ESQuery();

        $total = $this->getTotal($params);

        $query->setSize($total)
                ->setSort($this->setSort($params));
        $query->setQuery($boolQuery);
        if ($arrFields && is_array($arrFields)) {
            $query->setSource($arrFields);
        }

        $instanceSearch = new Search(General::getSearchConfig());
        $resultSet = $instanceSearch->addIndex($this->getSearchIndex())
                ->addType($this->getSearchType())
                ->search($query);
        $this->setResultSet($resultSet);
        return $this->toArray();
    }

    /**
     * get Total
     * @param array $arrConditions
     * @return integer
     */
    public function getTotal($arrConditions = array()) {
        $boolQuery = new Bool();
        $boolQuery = $this->__buildWhere($arrConditions, $boolQuery);

        $query = new ESQuery();
        $query->setQuery($boolQuery);
        $instanceSearch = new Search(General::getSearchConfig());
        $resultSet = $instanceSearch->addIndex($this->getSearchIndex())
                ->addType($this->getSearchType())
                ->count($query);
        return $resultSet;
    }

    private function setSort($params) {
        //copy
        return ['user_id' => ['order' => 'desc']];
    }

    public function removeAllDoc() {
        $respond = $this->getSearchType()->deleteByQuery('_type:categoryList');
        $this->getSearchType()->getIndex()->refresh();
        if ($respond->isOk()) {
            return true;
        }
        return false;
    }

    public function __buildWhere($params, $boolQuery) {
        if (empty($params)) {
            return $boolQuery;
        }

        if (!empty($params['user_id'])) {
            $addQuery = new ESQuery\Term();
            $addQuery->setTerm('user_id', $params['user_id']);
            $boolQuery->addMust($addQuery);
        }

        if (!empty($params['user_email'])) {
            $addQuery = new ESQuery\Term();
            $addQuery->setTerm('user_email', $params['user_email']);
            $boolQuery->addMust($addQuery);
        }

        if (!empty($params['user_phone'])) {
            $addQuery = new ESQuery\Term();
            $addQuery->setTerm('user_phone', $params['user_phone']);
            $boolQuery->addMust($addQuery);
        }

        if (!empty($params['user_status'])) {
            $addQuery = new ESQuery\Term();
            $addQuery->setTerm('user_status', $params['user_status']);
            $boolQuery->addMust($addQuery);
        }

        if (!empty($params['not_status'])) {
            $addQuery = new ESQuery\Term();
            $addQuery->setTerm('user_status', $params['not_status']);
            $boolQuery->addMustNot($addQuery);
        }

        if (!empty($params['in_user_id'])) {
            $addQuery = new ESQuery\Terms();
            $addQuery->setTerms('user_id', $params['in_user_id']);
            $boolQuery->addMust($addQuery);
        }

        if (!empty($params['user_login_ip'])) {
            $addQuery = new ESQuery\Term();
            $addQuery->setTerm('user_login_ip', $params['user_login_ip']);
            $boolQuery->addMust($addQuery);
        }

        if (!empty($params['not_user_id'])) {
            $addQuery = new ESQuery\Term();
            $addQuery->setTerm('user_id', $params['not_user_id']);
            $boolQuery->addMustNot($addQuery);
        }
        
        return $boolQuery;
    }

}
