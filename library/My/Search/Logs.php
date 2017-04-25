<?php

namespace My\Search;

use Elastica\Query\QueryString,
    Elastica\Filter\BoolAnd,
    Elastica\Filter\Term,
    Elastica\Filter\Terms,
    Elastica\Query\Bool,
    Elastica\Search,
    Elastica\Query,
    My\General;

class Logs extends SearchAbstract {

    public function __construct() {
        $this->setSearchIndex(SEARCH_PREFIX . 'tbl_logs');
        $this->setSearchType('logsList');
    }

    public function createIndex() {
        $strIndexName = SEARCH_PREFIX . 'tbl_logs';

        $searchClient = General::getSearchConfig();

        $searchIndex = $searchClient->getIndex($strIndexName);

        $objStatus = new \Elastica\Status($searchIndex->getClient());

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
        $searchType = $searchIndex->getType('logsList');
        $mapping = new \Elastica\Type\Mapping();
        $mapping->setType($searchType);
        $mapping->setProperties([
            'log_id' => ['type' => 'integer', 'index' => 'not_analyzed'],
            'user_id' => ['type' => 'integer', 'index' => 'not_analyzed'],
            'module' => ['type' => 'string', 'store' => 'yes', 'analyzer' => 'translation_index_analyzer', 'search_analyzer' => 'translation_search_analyzer', 'term_vector' => 'with_positions_offsets'],
            'controller' => ['type' => 'string', 'store' => 'yes', 'analyzer' => 'translation_index_analyzer', 'search_analyzer' => 'translation_search_analyzer', 'term_vector' => 'with_positions_offsets'],
            'action' => ['type' => 'string', 'store' => 'yes', 'analyzer' => 'translation_index_analyzer', 'search_analyzer' => 'translation_search_analyzer', 'term_vector' => 'with_positions_offsets'],
            'created_date' => ['type' => 'long', 'index' => 'not_analyzed'],
            'log_content' => ['type' => 'string', 'store' => 'yes', 'analyzer' => 'translation_index_analyzer', 'search_analyzer' => 'translation_search_analyzer', 'term_vector' => 'with_positions_offsets'],
            'table_id' => ['type' => 'integer', 'index' => 'not_analyzed']
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

    public function removeAllDoc() {
        $respond = $this->getSearchType()->deleteByQuery('_type:logsList');
        $this->getSearchType()->getIndex()->refresh();
        if ($respond->isOk()) {
            return true;
        }
        return false;
    }

    public function getList() {
        $params = $this->getParams();
        $intLimit = 10000;
        $boolQuery = new Bool();
        $filter = new BoolAnd();

        $wordNameQueryString = new QueryString();
        $wordNameQueryString->setDefaultField('cont_title')
                ->setQuery('*');
        $boolQuery->addMust($wordNameQueryString);
        $arrSort = array('_score');
        if (!empty($params['sort'])) {
            foreach ($params['sort'] as $key => $value) {
                $arrSort = array($key => array('order' => $value));
            }
        }
        $boolQuery = $this->_buildWhere($params, $boolQuery);

        $query = new Query();
        $query->setQuery($boolQuery)
                ->setSort($arrSort)
                ->setSize($intLimit);
        if (!empty($params['source'])) {
            $query->setSource($params['source']);
        }
        // p(json_encode($query->getParams()));die;
        $instanceSearch = new Search(General::getSearchConfig());
        $resultSet = $instanceSearch->addIndex($this->getSearchIndex())
                ->addType($this->getSearchType())
                ->search($query);
        $this->setResultSet($resultSet);
        return $this->toArray();
    }

    public function getListLimit() {
        $params = $this->getParams();
        $intLimit = $this->getLimit();
        $intPage = $params['page'] ? $params['page'] : 1;
        $intFrom = $intLimit * ($intPage - 1);
        $boolQuery = new Bool();
        $filter = new BoolAnd();

        $wordNameQueryString = new QueryString();
        $wordNameQueryString->setDefaultField('cont_title')
                ->setQuery('*');
        $boolQuery->addMust($wordNameQueryString);
        $arrSort = array('_score');
        if (!empty($params['sort'])) {
            foreach ($params['sort'] as $key => $value) {
                $arrSort = array($key => array('order' => $value));
            }
        }
        $boolQuery = $this->_buildWhere($params, $boolQuery);

        $query = new Query();
        $query->setQuery($boolQuery)
                ->setFrom($intFrom)
                ->setSize($intLimit)
                ->setSort($arrSort);
        if (!empty($params['source'])) {
            $query->setSource($params['source']);
        }
        //p(json_encode($query->getParams()));die;
        $instanceSearch = new Search(General::getSearchConfig());
        $resultSet = $instanceSearch->addIndex($this->getSearchIndex())
                ->addType($this->getSearchType())
                ->search($query);
        $this->setResultSet($resultSet);
        return $this->toArray();
    }

    public function getDetail() {
        $params = $this->getParams();
        $boolQuery = new Bool();

        $wordNameQueryString = new QueryString();
        $wordNameQueryString->setDefaultField('cont_title')
                ->setQuery('*');
        $boolQuery->addMust($wordNameQueryString);
        $boolQuery = $this->_buildWhere($params, $boolQuery);
        $query = new Query();
        $query->setQuery($boolQuery);

        $instanceSearch = new Search(General::getSearchConfig());
        $resultSet = $instanceSearch->addIndex($this->getSearchIndex())
                ->addType($this->getSearchType())
                ->search($query);
        $this->setResultSet($resultSet);
        return current($this->toArray());
    }

    public function _buildWhere($params, $boolQuery) {

        if (empty($params)) {
            return $boolQuery;
        }
        if (!empty($params['cont_id_smaller'])) {
            $addQuery = new Query\Range();
            $addQuery->addField('cont_id', array('lt' => $params['cont_id_smaller']));
            $boolQuery->addMust($addQuery);
        }
        if (!empty($params['cont_title_match'])) {
            $math = new Query\Match();
            $math->setParam('cont_title', $params['cont_title_match']);
            $boolQuery->addMust($math);
        }
        if (!empty($params['search'])) {
            $bool = new Bool();
            $math = new Query\Match();
            $math->setParam('cont_title', $params['search']);
            $bool->addShould($math);
            $math = new Query\Match();
            $math->setParam('cont_content', $params['search']);
            $bool->addShould($math);
            $math = new Query\Match();
            $math->setParam('cont_summary', $params['search']);
            $bool->addShould($math);
            $boolQuery->addMust($bool);
        }
        if (!empty($params['cont_id'])) {
            $addQuery = new Query\Term();
            $addQuery->setTerm('cont_id', $params['cont_id']);
            $boolQuery->addMust($addQuery);
        }
        if (!empty($params['cont_title'])) {
            $addQuery = new Query\Term();
            $addQuery->setTerm('cont_title', $params['cont_title']);
            $boolQuery->addMust($addQuery);
        }
        if (isset($params['cont_status'])) {
            $addQuery = new Query\Term();
            $addQuery->setTerm('cont_status', $params['cont_status']);
            $boolQuery->addMust($addQuery);
        }
        if (isset($params['tags_id_not'])) {
            $bool = new Bool();
            $addQuery = new Query\Term();
            $addQuery->setTerm('tags_cont_id', NULL);
            $bool->addShould($addQuery);
            $addQuery = new Query\Term();
            $addQuery->setTerm('tags_cont_id', "");
            $bool->addShould($addQuery);
            $boolNot = new Bool();
            $regex = $params['tags_id_not'] . '|' . $params['tags_id_not'] . ',.*|.*,' . $params['tags_id_not'] . ',.*|.*(,)' . $params['tags_id_not'];
            $addQuery = new Query\Regexp();
            $addQuery->setValue('tags_cont_id', $regex);
            $boolNot->addMustNot($addQuery);
            $bool->addShould($boolNot);
            $boolQuery->addShould($bool);
        }
        if (isset($params['not_cont_status'])) {
            $addQuery = new Query\Term();
            $addQuery->setTerm('cont_status', $params['not_cont_status']);
            $boolQuery->addMustNot($addQuery);
        }
        if (!empty($params['user_id'])) {
            $addQuery = new Query\Term();
            $addQuery->setTerm('user_id', $params['user_id']);
            $boolQuery->addMust($addQuery);
        }
        if (!empty($params['cate_id_or_main_cate_id'])) {
            $listCate = explode(',', $params['cate_id_or_main_cate_id']);

            $bool = new Bool();
            foreach ($listCate as $value) {
                $regex = $value . '|' . $value . ',.*|.*,' . $value . ',.*|.*(,)' . $value;
                $addQuery = new Query\Regexp();
                $addQuery->setValue('cate_id', $regex);
                $bool->addShould($addQuery);
                $addQuery = new Query\Term();
                $addQuery->setTerm('main_cate_id', $value);
                $bool->addShould($addQuery);
            }
            $boolQuery->addMust($bool);
        }
        if (!empty($params['cont_title_like'])) {
            $wildcard = new Query\Wildcard;
            $wildcard->setParam('cont_title', $params['cont_title_like']);
            $boolQuery->addMust($wildcard);
        }
        if (!empty($params['main_cate_id'])) {
            $addQuery = new Query\Term();
            $addQuery->setTerm('main_cate_id', $params['main_cate_id']);
            $boolQuery->addMust($addQuery);
        }
        if (!empty($params['not_main_cate_id'])) {
            $addQuery = new Query\Term();
            $addQuery->setTerm('main_cate_id', $params['not_main_cate_id']);
            $boolQuery->addMustNot($addQuery);
        }
        if (!empty($params['cont_meta_robot'])) {
            $wildcard = new Query\Wildcard;
            $wildcard->setParam('cont_meta_robot', $params['cont_meta_robot']);
            $boolQuery->addMust($wildcard);
        }
        if (!empty($params['listContentID'])) {
            $addQuery = new Query\Terms();
            $addQuery->setTerms('cont_id', $params['listContentID']);
            $boolQuery->addMust($addQuery);
        }
        if (!empty($params['listCategoryID'])) {
            $addQuery = new Query\Terms();
            $addQuery->setTerms('main_cate_id', $params['listCategoryID']);
            $boolQuery->addMust($addQuery);
        }
        if (!empty($params['cate_id'])) {
            $regex = $params['cate_id'] . '|' . $params['cate_id'] . ',.*|.*,' . $params['cate_id'] . ',.*|.*(,)' . $params['cate_id'];
            $addQuery = new Query\Regexp();
            $addQuery->setValue('cate_id', $regex);
            $boolQuery->addMust($addQuery);
        }
        if (!empty($params['not_cate_id'])) {
            $regex = $params['not_cate_id'] . '|' . $params['not_cate_id'] . ',.*|.*,' . $params['not_cate_id'] . ',.*|.*(,)' . $params['not_cate_id'];
            $addQuery = new Query\Regexp();
            $addQuery->setValue('cate_id', $regex);
            $boolQuery->addMustNot($addQuery);
        }
        if (!empty($params['tags_cont_id'])) {
            $regex = $params['tags_cont_id'] . '|' . $params['tags_cont_id'] . ',.*|.*,' . $params['tags_cont_id'] . ',.*|.*(,)' . $params['tags_cont_id'];
            $addQuery = new Query\Regexp();
            $addQuery->setValue('tags_cont_id', $regex);
            $boolQuery->addMust($addQuery);
        }

        return $boolQuery;
    }

}
