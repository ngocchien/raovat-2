<?php

namespace Frontend\Controller;

use My\Controller\MyController,
    My\General;

class CategoryController extends MyController
{
    /* @var $serviceCategory \My\Models\Category */
    /* @var $serviceProduct \My\Models\Product */
    /* @var $serviceProperties \My\Models\Properties */

    public function __construct()
    {
//        $this->externalJS = [
//            STATIC_URL . '/f/v1/js/library/??jquery.swipemenu.init.js'
//        ];
    }

    public function indexAction()
    {
        $params = $this->params()->fromRoute();

        if (empty($params['cateId'])) {
            return $this->redirect()->toRoute('404', array());
        }

        $arrCategoryList = unserialize(ARR_CATEGORY);

        if (empty($arrCategoryList[(int)$params['cateId']])) {
            return $this->redirect()->toRoute('404', array());
        }

        $arrCategoryDetail = $arrCategoryList[(int)$params['cateId']];

        if ($arrCategoryDetail['cate_slug'] != $params['cateSlug']) {
            $this->redirect()->toRoute('category', ['cateSlug' => $arrCategoryDetail['cate_slug'], 'cateId' => $arrCategoryDetail['cate_id']]);
        }

        $intPage = (int)$params['page'] > 0 ? (int)$params['page'] : 1;
        $intLimit = 20;

        $arrCondition = [
            'cont_status' => 1
        ];
        $arrCategoryChildren = [];
        if ($arrCategoryDetail['parent_id'] == 0) {
            $arrCateByParent = unserialize(ARR_CATEGORY_BY_PARENT)[$arrCategoryDetail['cate_id']];
            if (!empty($arrCateByParent)) {
                foreach ($arrCateByParent as $cate) {
                    $arrCategoryChildren[$cate['cate_id']] = $cate;
                    $arrCondition['in_cate_id'][] = $cate['cate_id'];
                }
            } else {
                $arrCondition['cate_id'] = $arrCategoryDetail['cate_id'];
            }
        } else {
            $arrCondition['cate_id'] = $arrCategoryDetail['cate_id'];
        }

        $instanceSearchContent = new \My\Search\Content();
        $arrContentList = $instanceSearchContent->getListLimit(
            $arrCondition,
            $intPage,
            $intLimit,
            ['created_date' => ['order' => 'desc']],
            [
                'cont_title',
                'cont_slug',
                'cont_main_image',
                'cont_description',
                'cont_id'
            ]
        );

        $intTotal = $instanceSearchContent->getTotal($arrCondition);
        $helper = $this->serviceLocator->get('viewhelpermanager')->get('Paging');
        $paging = $helper($params['module'], $params['__CONTROLLER__'], $params['action'], $intTotal, $intPage, $intLimit, 'category', $params);

        $metaTitle = $arrCategoryDetail['cate_meta_title'] ? $arrCategoryDetail['cate_meta_title'] : $arrCategoryDetail['cate_name'];
        $metaKeyword = $arrCategoryDetail['cate_meta_keyword'] ? $arrCategoryDetail['cate_meta_keyword'] : NULL;
        $metaDescription = $arrCategoryDetail['cate_meta_description'] ? $arrCategoryDetail['cate_meta_description'] : 'Danh sách bài viết trong danh mục : ' . $arrCategoryDetail['cate_name'] . General::TITLE_META;

        $this->renderer = $this->serviceLocator->get('Zend\View\Renderer\PhpRenderer');
        $this->renderer->headTitle(html_entity_decode($metaTitle) . General::TITLE_META);
        $this->renderer->headMeta()->setProperty('url', \My\General::SITE_DOMAIN_FULL . $this->url()->fromRoute('category', array('cateSlug' => $params['cateSlug'], 'cateId' => $params['cateId'], 'page' => $intPage)));
        $this->renderer->headMeta()->appendName('og:url', \My\General::SITE_DOMAIN_FULL . $this->url()->fromRoute('category', array('cateSlug' => $params['cateSlug'], 'cateId' => $params['cateId'], 'page' => $intPage)));
        $this->renderer->headMeta()->appendName('title', html_entity_decode($metaTitle) . General::TITLE_META);
        $this->renderer->headMeta()->setProperty('og:title', html_entity_decode($metaTitle) . General::TITLE_META);
        $this->renderer->headMeta()->appendName('keywords', html_entity_decode($metaKeyword));
        $this->renderer->headMeta()->appendName('description', html_entity_decode($metaDescription));
        $this->renderer->headMeta()->setProperty('og:description', html_entity_decode($metaDescription));
        $this->renderer->headLink(array('rel' => 'amphtml', 'href' => \My\General::SITE_DOMAIN_FULL . $this->url()->fromRoute('category', array('cateSlug' => $params['cateSlug'], 'cateId' => $params['cateId']))));
        $this->renderer->headLink(array('rel' => 'canonical', 'href' => \My\General::SITE_DOMAIN_FULL . $this->url()->fromRoute('category', array('cateSlug' => $params['cateSlug'], 'cateId' => $params['cateId']))));

        //50 KEYWORD :)
        $instanceSearchKeyword = new \My\Search\Keyword();
        $arrKeywordList = $instanceSearchKeyword->getListLimit(
            ['full_text_keyname' => $arrCategoryDetail['cate_name']],
            $intPage,
            40,
            ['_score' => ['order' => 'desc']],
            [
                'key_id',
                'key_name',
                'key_slug'
            ]
        );

        return array(
            'params' => $params,
            'paging' => $paging,
            'arrCategoryDetail' => $arrCategoryDetail,
            'arrContentList' => $arrContentList,
            'arrCategoryChildren' => $arrCategoryChildren,
            'intTotal' => $intTotal,
            'arrKeywordList' => $arrKeywordList
        );
    }

}
