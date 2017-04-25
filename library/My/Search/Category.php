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

class Category extends SearchAbstract
{

    public function __construct()
    {
        $this->setSearchIndex(SEARCH_PREFIX . 'category');
        $this->setSearchType('categoryList');
    }

    public function createIndex()
    {
        $strIndexName = SEARCH_PREFIX . 'category';

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
        $searchType = $searchIndex->getType('categoryList');
        $mapping = new Mapping();
        $mapping->setType($searchType);
        $mapping->setProperties([
            'cate_id' => ['type' => 'integer', 'index' => 'not_analyzed'],
            'cate_name' => ['type' => 'string', 'store' => 'yes', 'analyzer' => 'translation_index_analyzer', 'search_analyzer' => 'translation_search_analyzer', 'term_vector' => 'with_positions_offsets'],
            'cate_slug' => ['type' => 'string', 'store' => 'yes', 'analyzer' => 'translation_index_analyzer', 'search_analyzer' => 'translation_search_analyzer', 'term_vector' => 'with_positions_offsets'],
            'cate_sort' => ['type' => 'integer', 'index' => 'not_analyzed'],
            'cate_icon' => ['type' => 'string', 'index' => 'not_analyzed'],
            'created_date' => ['type' => 'long', 'index' => 'not_analyzed'],
            'user_created' => ['type' => 'integer', 'index' => 'not_analyzed'],
            'updated_date' => ['type' => 'long', 'index' => 'not_analyzed'],
            'user_updated' => ['type' => 'integer', 'index' => 'not_analyzed'],
            'cate_meta_title' => ['type' => 'string', 'store' => 'yes', 'analyzer' => 'translation_index_analyzer', 'search_analyzer' => 'translation_search_analyzer', 'term_vector' => 'with_positions_offsets'],
            'cate_meta_keyword' => ['type' => 'string', 'store' => 'yes', 'analyzer' => 'translation_index_analyzer', 'search_analyzer' => 'translation_search_analyzer', 'term_vector' => 'with_positions_offsets'],
            'cate_meta_description' => ['type' => 'string', 'store' => 'yes', 'analyzer' => 'translation_index_analyzer', 'search_analyzer' => 'translation_search_analyzer', 'term_vector' => 'with_positions_offsets'],
            'cate_description' => ['type' => 'string', 'store' => 'yes', 'analyzer' => 'translation_index_analyzer', 'search_analyzer' => 'translation_search_analyzer', 'term_vector' => 'with_positions_offsets'],
            'cate_status' => ['type' => 'integer', 'index' => 'not_analyzed'],
            'total_content' => ['type' => 'integer', 'index' => 'not_analyzed'],
            'parent_id' => ['type' => 'integer', 'index' => 'not_analyzed'],
            'cate_img_url' => ['type' => 'string', 'store' => 'yes', 'analyzer' => 'translation_index_analyzer', 'search_analyzer' => 'translation_search_analyzer', 'term_vector' => 'with_positions_offsets'],
            'cate_crawler_url' => ['type' => 'string', 'store' => 'yes', 'analyzer' => 'translation_index_analyzer', 'search_analyzer' => 'translation_search_analyzer', 'term_vector' => 'with_positions_offsets'],
        ]);
        $mapping->send();
    }

    public function add($arrDocument)
    {
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

    public function edit($document)
    {
        $respond = $this->getSearchType()->updateDocument($document);
        $this->getSearchType()->getIndex()->refresh();
        if ($respond->isOk()) {
            return true;
        }
        return false;
    }

    public function getDetail($params, $arrFields = [])
    {
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
    public function getListLimit($params = array(), $intPage = 1, $intLimit = 15, $sort = ['updated_date' => ['order' => 'desc']], $arrFields = [])
    {
        try {
            $intFrom = $intLimit * ($intPage - 1);
            $boolQuery = new Bool();
            $boolQuery = $this->__buildWhere($params, $boolQuery);
            $query = new ESQuery();
            $query->setFrom($intFrom)
                ->setSize($intLimit)
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
        } catch (\Exception $exc) {
            echo $exc->getMessage();
            die;
        }
    }

    /**
     * Get List
     */
    public function getList($params, $arrFields = [], $sort = ['cate_id' => ['order' => 'asc']])
    {
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
    public function getTotal($arrConditions = array())
    {
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

    private function setSort($params)
    {
        //copy
        return ['updated_date' => ['order' => 'desc']];
    }

    public function removeAllDoc()
    {
        $respond = $this->getSearchType()->deleteByQuery('_type:categoryList');
        $this->getSearchType()->getIndex()->refresh();
        if ($respond->isOk()) {
            return true;
        }
        return false;
    }

    public function __buildWhere($params, $boolQuery)
    {

        if (empty($params)) {
            return $boolQuery;
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

        if (!empty($params['cate_name'])) {
            $addQuery = new ESQuery\Term();
            $addQuery->setTerm('cate_name', $params['cate_name']);
            $boolQuery->addMust($addQuery);
        }

        if (!empty($params['cate_status'])) {
            $addQuery = new ESQuery\Term();
            $addQuery->setTerm('cate_status', $params['cate_status']);
            $boolQuery->addMust($addQuery);
        }

        if (!empty($params['not_cate_status'])) {
            $addQuery = new ESQuery\Term();
            $addQuery->setTerm('cate_status', $params['not_cate_status']);
            $boolQuery->addMustNot($addQuery);
        }

        if (!empty($params['not_cate_id'])) {
            $addQuery = new ESQuery\Term();
            $addQuery->setTerm('cate_id', $params['not_cate_id']);
            $boolQuery->addMustNot($addQuery);
        }

        if (isset($params['parent_id'])) {
            $addQuery = new ESQuery\Term();
            $addQuery->setTerm('parent_id', $params['parent_id']);
            $boolQuery->addMust($addQuery);
        }

        if (isset($params['cate_slug'])) {
            $addQuery = new ESQuery\Term();
            $addQuery->setTerm('cate_slug', $params['cate_slug']);
            $boolQuery->addMust($addQuery);
        }

        return $boolQuery;
    }

}
