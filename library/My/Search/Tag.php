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

class Tag extends SearchAbstract {

    public function __construct() {
        $this->setSearchIndex(SEARCH_PREFIX . 'tag');
        $this->setSearchType('tagList');
    }

    public function createIndex() {
        $strIndexName = SEARCH_PREFIX . 'tag';

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
        $searchType = $searchIndex->getType('tagList');
        $mapping = new Mapping();
        $mapping->setType($searchType);
        $mapping->setProperties([
            'tag_id' => ['type' => 'integer', 'index' => 'not_analyzed'],
            'tag_name' => ['type' => 'string', 'store' => 'yes', 'analyzer' => 'translation_index_analyzer', 'search_analyzer' => 'translation_search_analyzer', 'term_vector' => 'with_positions_offsets'],
            'tag_slug' => ['type' => 'string', 'index' => 'not_analyzed'],
            'created_date' => ['type' => 'long', 'index' => 'not_analyzed'],
            'user_created' => ['type' => 'integer', 'index' => 'not_analyzed'],
            'updated_date' => ['type' => 'long', 'index' => 'not_analyzed'],
            'user_updated' => ['type' => 'integer', 'index' => 'not_analyzed']
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
        $detailAreaPrice = current($this->toArray());
        return $detailAreaPrice;
    }

    /**
     * Get List Limit
     */
    public function getListLimit($params = array(), $intPage = 1, $intLimit = 15, $sort = ['created_date' => ['order' => 'desc']]) {
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
    public function getList($params, $sort = [], $arrFields = []) {
        $boolQuery = new Bool();
        $boolQuery = $this->__buildWhere($params, $boolQuery);
        $query = new ESQuery();

        $total = $this->getTotal($params);

        if (empty($sort)) {
            $sort = $this->setSort($params);
        }

        $query->setSize($total)
                ->setSort($sort);
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
        return ['cont_id' => ['order' => 'desc']];
    }

    public function removeAllDoc() {
        $respond = $this->getSearchType()->deleteByQuery('_type:tagList');
        $this->getSearchType()->getIndex()->refresh();
        if ($respond->isOk()) {
            return true;
        }
        return false;
    }

    public function setHighLight(\Elastica\Query $query) {
        if (!$this->isHighlight()) {
            return $query;
        }

        $arrParams = $this->getParams();
        if ($arrParams['senderName']) {
            $query->setHighlight(array(
                'pre_tags' => array('<b style="background-color: #beedf9;">'),
                'post_tags' => array('</b>'),
                'fields' => array(
                    'sender_name' => array(
                        'fragment_size' => 200,
                        'number_of_fragments' => 1,
                    ),
                ),
            ));
        }
        return $query;
    }

    public function __buildWhere($params, $boolQuery) {

        if (empty($params)) {
            return $boolQuery;
        }

        if (!empty($params['cont_id'])) {
            $addQuery = new ESQuery\Term();
            $addQuery->setTerm('cont_id', $params['cont_id']);
            $boolQuery->addMust($addQuery);
        }

        if (!empty($params['in_cont_id'])) {
            $addQuery = new ESQuery\Terms();
            $addQuery->setTerms('cont_id', $params['in_cont_id']);
            $boolQuery->addMust($addQuery);
        }

        if (!empty($params['not_cont_id'])) {
            $addQuery = new ESQuery\Term();
            $addQuery->setTerm('cont_id', $params['not_cont_id']);
            $boolQuery->addMustNot($addQuery);
        }

        if (!empty($params['user_created'])) {
            $addQuery = new ESQuery\Term();
            $addQuery->setTerm('user_created', $params['user_created']);
            $boolQuery->addMust($addQuery);
        }

        if (!empty($params['cont_status'])) {
            $addQuery = new ESQuery\Term();
            $addQuery->setTerm('cont_status', $params['cont_status']);
            $boolQuery->addMust($addQuery);
        }

        if (!empty($params['not_cont_status'])) {
            $addQuery = new ESQuery\Term();
            $addQuery->setTerm('cont_status', $params['not_cont_status']);
            $boolQuery->addMustNot($addQuery);
        }

        if (!empty($params['ip_address'])) {
            $addQuery = new ESQuery\Term();
            $addQuery->setTerm('ip_address', $params['ip_address']);
            $boolQuery->addMust($addQuery);
        }

        if (!empty($params['cate_id'])) {
            $addQuery = new ESQuery\Term();
            $addQuery->setTerm('cate_id', $params['cate_id']);
            $boolQuery->addMust($addQuery);
        }

        if (!empty($params['in_cate_id'])) {
            $addQuery = new ESQuery\Terms();
            $addQuery->setTerms('cate_id', $params['in_cate_id']);
            $boolQuery->addMust($addQuery);
        }

        if (!empty($params['cont_slug'])) {
            $addQuery = new ESQuery\Term();
            $addQuery->setTerm('cont_slug', $params['cont_slug']);
            $boolQuery->addMust($addQuery);
        }

        if (!empty($params['created_date_today'])) {
            $date = date('d-m-Y', time());
            $firstSecond = strtotime($date);
            $lastSecond = ($firstSecond - 1) + (60 * 60 * 24);

            $addQuery = new ESQuery\Range();
            $addQuery->addField('created_date', array('lte' => $lastSecond, 'gte' => $firstSecond));
            $boolQuery->addMust($addQuery);
        }

        if (!empty($params['key_word'])) {
            $bool = new Bool();
            if ((int) $params['key_word'] > 0) {
                $queryTerm = new ESQuery\Term();
                $queryTerm->setTerm('cont_id', (int) $params['key_word']);
                $bool->addShould($queryTerm);
            }

            $strKeyword = trim($params['key_word']);

            $titleQueryString = new QueryString();
            $titleQueryString->setDefaultField('cont_title')
                    ->setQuery($strKeyword)
                    ->setAllowLeadingWildcard(1)
                    ->setDefaultOperator('AND');
            $bool->addShould($titleQueryString);

            $detailQueryString = new QueryString();
            $detailQueryString->setDefaultField('cont_detail_text')
                    ->setQuery($strKeyword)
                    ->setAllowLeadingWildcard(1)
                    ->setDefaultOperator('AND');
            $bool->addShould($detailQueryString);

            $boolQuery->addMust($bool);
        }

        if (!empty($params['dist_id'])) {
            $addQuery = new ESQuery\Term();
            $addQuery->setTerm('dist_id', $params['dist_id']);
            $boolQuery->addMust($addQuery);
        }

        if (!empty($params['prop_id'])) {
            $addQuery = new ESQuery\Term();
            $addQuery->setTerm('prop_id', $params['prop_id']);
            $boolQuery->addMust($addQuery);
        }

        if (!empty($params['is_vip'])) {
            $addQuery = new ESQuery\Term();
            $addQuery->setTerm('is_vip', $params['is_vip']);
            $boolQuery->addMust($addQuery);
        }

        if (!empty($params['vip_type'])) {
            $addQuery = new ESQuery\Term();
            $addQuery->setTerm('vip_type', $params['vip_type']);
            $boolQuery->addMust($addQuery);
        }

        if (!empty($params['not_type_vip'])) {
            $addQuery = new ESQuery\Term();
            $addQuery->setTerm('vip_type', $params['not_type_vip']);
            $boolQuery->addMustNot($addQuery);
        }


        if (!empty($params['more_expired_time'])) {
            $addQuery = new ESQuery\Range();
            $addQuery->addField('expired_time', array('gte' => $params['more_expired_time']));
            $boolQuery->addMust($addQuery);
        }

        if (!empty($params['less_expired_time'])) {
            $addQuery = new ESQuery\Range();
            $addQuery->addField('expired_time', array('lte' => $params['less_expired_time']));
            $boolQuery->addMust($addQuery);
        }

        if (isset($params['is_send'])) {
            $addQuery = new ESQuery\Term();
            $addQuery->setTerm('is_send', $params['is_send']);
            $boolQuery->addMust($addQuery);
        }

        if (isset($params['less_cont_id'])) {
            $addQuery = new ESQuery\Range();
            $addQuery->addField('cont_id', array('lt' => $params['less_cont_id']));
            $boolQuery->addMust($addQuery);
        }

        if (isset($params['full_text_title'])) {
            $math = new ESQuery\Match();
            $math->setParam('cont_title', trim($params['full_text_title']));
            $boolQuery->addMust($math);
        }
        
        if (isset($params['more_created_date'])) {
            $addQuery = new ESQuery\Range();
            $addQuery->addField('created_date', array('gte' => $params['more_created_date']));
            $boolQuery->addMust($addQuery);
        }

        return $boolQuery;
    }

}
