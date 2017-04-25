<?php

namespace Frontend\Controller;

use My\Controller\MyController;

class SitemapController extends MyController {
    /* @var $serviceCategory \My\Models\Category */
    /* @var $serviceTags \My\Models\Tags */
    /* @var $serviceTagsContent \My\Models\TagsContent */
    /* @var $serviceProduct \My\Models\Product */
    /* @var $serviceTags \My\Models\Tags */
    /* @var $serviceKeyword \My\Models\Keyword */

    public function __construct() {
        
    }

    public function indexAction() {
        $this->layout('layout/empty');
        $params = $this->params()->fromRoute();
    }

    public function categoryAction() {
        $this->layout('layout/empty');
        $params = $this->params()->fromRoute();
        $arrCategoryList = unserialize(ARR_CATEGORY);
        return array(
            'params' => $params,
            'arrCategoryList' => $arrCategoryList,
            'title' => 'Danh mục'
        );
    }

//    public function tagsAction() {
//        $this->layout('layout/empty');
//        $params = $this->params()->fromRoute();
//        $intPage = is_numeric($this->params()->fromQuery('page', 1)) ? $this->params()->fromQuery('page', 1) : 1;
//        $intLimit = 200;
//        $serviceTags = $this->serviceLocator->get('My\Models\Tags');
//        $arrCondition = array(
//            'not_tags_status' => -1,
//        );
//        $arrTagsList = $serviceTags->getListLimit($arrCondition, $intPage, $intLimit, 'tags_sort ASC');
//        $intTotal = $serviceTags->getTotal($arrCondition);
//        $helper = $this->serviceLocator->get('viewhelpermanager')->get('Paging');
//        $paging = $helper($params['module'], $params['__CONTROLLER__'], $params['action'], $intTotal, $intPage, $intLimit, 'sitemap', $params);
//        return array(
//            'params' => $params,
//            'arrTagsList' => $arrTagsList,
//            'paging' => $paging,
//            'intPage' => $intPage,
//            'title' => 'Tags sản phẩm'
//        );
//    }

//    public function tagsContentAction() {
//        $this->layout('layout/empty');
//        $params = $this->params()->fromRoute();
//        $intPage = is_numeric($this->params()->fromQuery('page', 1)) ? $this->params()->fromQuery('page', 1) : 1;
//        $intLimit = 200;
//        $serviceTagsContent = $this->serviceLocator->get('My\Models\TagsContent');
//        $arrCondition = array(
//            'not_tags_cont_status' => -1,
//        );
//        $arrTagsContentList = $serviceTagsContent->getListLimit($arrCondition, $intPage, $intLimit, 'tags_cont_sort ASC');
//        $intTotal = $serviceTagsContent->getTotal($arrCondition);
//        $helper = $this->serviceLocator->get('viewhelpermanager')->get('Paging');
//        $paging = $helper($params['module'], $params['__CONTROLLER__'], $params['action'], $intTotal, $intPage, $intLimit, 'sitemap', $params);
//        return array(
//            'params' => $params,
//            'arrTagsContentList' => $arrTagsContentList,
//            'paging' => $paging,
//            'intPage' => $intPage,
//            'title' => 'Tags bài viết'
//        );
//    }


    public function contentAction() {
        $this->layout('layout/empty');
        $params = $this->params()->fromRoute();
        $intPage = is_numeric($params['page']) ? $params['page'] : 1;
        $intLimit = 200;

        $instanceSearchContent = new \My\Search\Content();
        $arrContentList = $instanceSearchContent->getListLimit(['not_cont_status'=>-1],$intPage,$intLimit,['created_date'=>['order'=>'desc']]);
        $intTotal = $instanceSearchContent->getTotal(['not_cont_status'=>-1]);
        
        $helper = $this->serviceLocator->get('viewhelpermanager')->get('Paging');
        $paging = $helper($params['module'], $params['__CONTROLLER__'], $params['action'], $intTotal, $intPage, $intLimit, 'sitemap', $params);
        
        return array(
            'params' => $params,
            'arrContentList' => $arrContentList,
            'paging' => $paging,
            'intPage' => $intPage,
            'title' => 'Bài viết'
        );
    }

    public function otherAction() {
        $this->layout('layout/empty');
        $arrData = array(
            array('url' => 'http://megavita.vn/', 'name' => "Trang chủ")
        );
        return array(
            'arrData' => $arrData
        );
    }

    public function keywordAction(){
        $this->layout('layout/empty');
        $params = $this->params()->fromRoute();
        $intPage = is_numeric($params['page']) ? $params['page'] : 1;
        $intLimit = 200;

        $instanceSearch = new \My\Search\Keyword();

        $arrCondition = array(
            'word_id_less' => round((time()-1465036100)/4)
        );
        $arrKeywordList = $instanceSearch->getListLimit($arrCondition, $intPage, $intLimit, ['key_id'=>['order'=>'desc']]);
        $intTotal = $instanceSearch->getTotal($arrCondition);
        $helper = $this->serviceLocator->get('viewhelpermanager')->get('Paging');
        $paging = $helper($params['module'], $params['__CONTROLLER__'], $params['action'], $intTotal, $intPage, $intLimit, 'sitemap', $params);
        return array(
            'params' => $params,
            'arrKeywordList' => $arrKeywordList,
            'paging' => $paging,
            'intPage' => $intPage,
            'intLimit' => $intLimit,
            'intTotal' => $intTotal,
            'title' => 'Keyword'
        );
    }
}
