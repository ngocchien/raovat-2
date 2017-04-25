<?php

namespace My\Search;

abstract class SearchAbstract {

    protected $_searchClient;
    protected $_searchIndex;
    protected $_searchType;
    protected $_params;
    protected $_resultSet;
    protected $_limit;
    protected $_highlight = true;

    /**
     * @return the $_searchIndex
     */
    public function getSearchIndex() {
        return $this->_searchIndex;
    }

    /**
     * @param field_type $_searchIndex
     */
    public function setSearchIndex($_searchIndex) {
        $this->_searchClient = \My\General::getSearchConfig();
        $this->_searchIndex = $this->_searchClient->getIndex($_searchIndex);
    }

    /**
     * @return the $_searchType
     */
    public function getSearchType() {
        return $this->_searchType;
    }

    /**
     * @param field_type $_searchType
     */
    public function setSearchType($_searchType) {
        $this->_searchType = $this->_searchIndex->getType($_searchType);
    }

    /**
     * @return the $_limit
     */
    public function getLimit() {
        return $this->_limit;
    }

    /**
     * @param field_type $_limit
     */
    public function setLimit($_limit) {
        $this->_limit = $_limit;
        return $this;
    }

    /**
     * @return the $_resultSet
     */
    public function getResultSet() {
        return $this->_resultSet;
    }

    /**
     * @param field_type $_resultSet
     */
    public function setResultSet($_resultSet) {
        $this->_resultSet = $_resultSet;
    }

    /**
     * @return the $_params
     */
    public function getParams() {
        return $this->_params;
    }

    /**
     * @param field_type $_params
     */
    public function setParams($_params) {
        $this->_params = $_params;
        return $this;
    }

    /**
     * @return the $_highlight
     */
    public function isHighlight($highlight = null) {
        if ($highlight !== null) {
            $this->_highlight = $highlight;
        }

        return $this->_highlight;
    }

    /**
     * Set search highlight
     * @param \Elastica\Query $query
     * @param array $arrParams
     */
    public function setHighLight(\Elastica\Query $query) {
        if (!$this->isHighlight()) {
            return $query;
        }

        $arrParams = $this->getParams();
        if ($arrParams['fullname']) {
            $query->setHighlight(array(
                'pre_tags' => array('<b style="background-color: #beedf9;">'),
                'post_tags' => array('</b>'),
                'fields' => array(
                    'fullname' => array(
                        'fragment_size' => 200,
                        'number_of_fragments' => 1,
                    ),
                ),
            ));
        }
        if ($arrParams['email']) {
            $query->setHighlight(array(
                'pre_tags' => array('<b style="background-color: #beedf9;">'),
                'post_tags' => array('</b>'),
                'fields' => array(
                    'email' => array(
                        'fragment_size' => 200,
                        'number_of_fragments' => 1,
                    ),
                ),
            ));
        }
        if ($arrParams['itemName']) {
            $query->setHighlight(array(
                'pre_tags' => array('<b style="background-color: #beedf9;">'),
                'post_tags' => array('</b>'),
                'fields' => array(
                    'product_name' => array(
                        'fragment_size' => 200,
                        'number_of_fragments' => 1,
                    ),
                ),
            ));
        }

        if ($arrParams['trackingNumber']) {
            $query->setHighlight(array(
                'pre_tags' => array('<b style="background-color: #beedf9;">'),
                'post_tags' => array('</b>'),
                'fields' => array(
                    'tracking_number' => array(
                        'fragment_size' => 200,
                        'number_of_fragments' => 1,
                    ),
                ),
            ));
        }

        if ($arrParams['newsName']) {
            $query->setHighlight(array(
                'pre_tags' => array('<b style="background-color: #beedf9;">'),
                'post_tags' => array('</b>'),
                'fields' => array(
                    'news_name' => array(
                        'fragment_size' => 200,
                        'number_of_fragments' => 1,
                    ),
                ),
            ));
        }
        if ($arrParams['phoneNumber']) {
            $query->setHighlight(array(
                'pre_tags' => array('<b style="background-color: #beedf9;">'),
                'post_tags' => array('</b>'),
                'fields' => array(
                    'phone' => array(
                        'fragment_size' => 200,
                        'number_of_fragments' => 1,
                    ),
                ),
            ));
        }

        if ($arrParams['userRole']) {
            $query->setHighlight(array(
                'pre_tags' => array('<b style="background-color: #beedf9;">'),
                'post_tags' => array('</b>'),
                'fields' => array(
                    'user_role' => array(
                        'fragment_size' => 200,
                        'number_of_fragments' => 1,
                    ),
                ),
            ));
        }
        return $query;
    }

    public function getTotalHits() {
        $resultSet = $this->getResultSet();
        return $resultSet->getTotalHits();
    }

    /**
     * Convert result set to array
     * @return array item list
     */
    protected function toArray() {
        $arrItemList = array();
        $params = $this->getParams();
        $resultSet = $this->getResultSet();

        foreach ($resultSet as $result) {
            $arrItem = $result->getSource();
            //var_dump($arrItem); exit;
            if ($params['fullname'] || $params['email'] || $params['itemName'] || $params['trackingNumber'] || $params['newsName'] || $params['phoneNumber']) {
                $highlight = $result->getHighlights();
                $arrItem['fullname'] = current($highlight['fullname']) ? current($highlight['fullname']) : $arrItem['fullname'];
                $arrItem['email'] = current($highlight['email']) ? current($highlight['email']) : $arrItem['email'];
                $arrItem['product_name'] = current($highlight['product_name']) ? current($highlight['product_name']) : $arrItem['product_name'];
                $arrItem['tracking_number'] = current($highlight['tracking_number']) ? current($highlight['tracking_number']) : $arrItem['tracking_number'];
                $arrItem['news_name'] = current($highlight['news_name']) ? current($highlight['news_name']) : $arrItem['news_name'];
                $arrItem['phone'] = current($highlight['phone']) ? current($highlight['phone']) : $arrItem['phone'];
            }
            $arrItem['order_id'] && empty($arrItem['order_item_id']) ? $arrItemList[$arrItem['order_id']] = $arrItem : $arrItemList[] = $arrItem;
        }

        return $arrItemList;
    }

}
