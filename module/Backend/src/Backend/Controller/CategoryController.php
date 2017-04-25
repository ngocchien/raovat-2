<?php

namespace Backend\Controller;

use My\Controller\MyController,
    My\Validator\Validate,
    My\General;

class CategoryController extends MyController {
    /* @var $serviceCategory \My\Models\Category */

    public function __construct() {
        $this->externalJS = [
            STATIC_URL . '/b/js/my/??category.js'
        ];
    }

    public function indexAction() {
        $params = array_merge($this->params()->fromQuery(), $this->params()->fromRoute());
        $intPage = $this->params()->fromRoute('page', 1);
        $intLimit = 15;
        $arrCondition = array(
            'not_cate_status' => -1
        );
        $instanceSearchCategory = new \My\Search\Category();
        $arrCategoryList = $instanceSearchCategory->getListLimit($arrCondition, $intPage, $intLimit, ['cate_sort' => ['order' => 'asc']]);

        $route = 'backend';

        $intTotal = $instanceSearchCategory->getTotal($arrCondition);
        $helper = $this->serviceLocator->get('viewhelpermanager')->get('Paging');
        $paging = $helper($params['module'], $params['__CONTROLLER__'], $params['action'], $intTotal, $intPage, $intLimit, $route, $params);

        if (!empty($arrCategoryList)) {
            foreach ($arrCategoryList as $arrCategory) {
                $arrUserIdList[] = $arrCategory['user_created'];
            }
            if (!empty($arrUserIdList)) {
                $arrUserIdList = array_unique($arrUserIdList);
                $instanceSearchUser = new \My\Search\User();
                $arrUserList = $instanceSearchUser->getList(['in_user_id' => $arrUserIdList]);
                if (!empty($arrUserList)) {
                    foreach ($arrUserList as $arrUser) {
                        $arrUserListFM[$arrUser['user_id']] = $arrUser;
                    }
                }
            }
        }
        return array(
            'params' => $params,
            'paging' => $paging,
            'arrCategoryList' => $arrCategoryList,
            'arrUserList' => $arrUserListFM
        );
    }

    public function addAction() {
        $arrParamsRoute = $params = $this->params()->fromRoute();

        //get parent 
        $instanceSearchCategory = new \My\Search\Category();
        $arrCategoryParent = $instanceSearchCategory->getList(['parent_id' => 0, 'not_cate_status' - 1]);

        if ($this->request->isPost()) {
            $params = $this->params()->fromPost();

            $cateName = trim($params['cate_name']);
            $cateIcon = trim($params['cate_icon']);
            $cateSort = (int) trim($params['cate_sort']);
            $cateMetaTitle = trim($params['cate_meta_title']);
            $cateMetaDescription = trim($params['cate_meta_description']);
            $cateMetaKeyword = trim($params['cate_meta_keyword']);
            $cateStatus = $params['cate_status'];

            if (empty($cateName)) {
                $errors['cate_name'] = 'Category name is not empty!';
            }

//            if (empty($cateIcon)) {
//                $errors['cate_icon'] = 'Icon is not empty';
//            }

            if (empty($cateMetaTitle)) {
                $errors['cate_meta_title'] = 'SEO Meta Title is not empty!';
            }

            if (empty($cateMetaDescription)) {
                $errors['cate_meta_description'] = 'SEO meta Description is not Empty!';
            }

            if (empty($cateMetaKeyword)) {
                $errors['cate_meta_keyword'] = 'SEO meta Keyword is not Empty!';
            }

            if (empty($errors)) {

                $arrResult = $instanceSearchCategory->getDetail(['cate_slug' => General::getSlug($cateName), 'not_cate_status' => -1, 'parent_id' => (int) $params['parent_id']]);

                if ($arrResult) {
                    $errors[] = 'This category is Exist!';
                }

                if (empty($errors)) {
                    $arrParams = [
                        'cate_name' => $cateName,
                        'cate_icon' => $cateIcon,
                        'cate_slug' => General::getSlug($cateName),
                        'cate_meta_title' => $cateMetaTitle,
                        'cate_meta_description' => $cateMetaDescription,
                        'cate_meta_keyword' => $cateMetaKeyword,
                        'cate_sort' => $cateSort,
                        'cate_status' => $cateStatus,
                        'created_date' => time(),
                        'user_created' => UID,
                        'parent_id' => (int) $params['parent_id']
                    ];
                    $serviceCategory = $this->serviceLocator->get('My\Models\Category');
                    $intResult = $serviceCategory->add($arrParams);
                    if ($intResult) {
                        $serviceLogs = $this->serviceLocator->get('My\Models\Logs');
                        $arrLog = General::createLogs($arrParamsRoute, $arrParams, $intResult);
                        $serviceLogs->add($arrLog);
                        $this->flashMessenger()->setNamespace('success-add-category')->addMessage('Add Category is success!');
                        $this->redirect()->toRoute('backend', array('controller' => 'category', 'action' => 'edit', 'id' => $intResult));
                    }
                    $errors[] = 'An error processing insert!';
                }
            }
        }
        return array(
            'params' => $params,
            'errors' => $errors,
            'arrCategoryParent' => $arrCategoryParent
        );
    }

    public function editAction() {
        $arrParamsRoute = $params = $this->params()->fromRoute();
        if (empty($params['id'])) {
            $this->redirect()->toRoute('backend', array('controller' => 'category', 'action' => 'index'));
        }
        $intCategoryId = (int) $params['id'];
        $arrCondition = array('cate_id' => $intCategoryId);
        $instanceSearchCategory = new \My\Search\Category();
        $arrCategory = $instanceSearchCategory->getDetail($arrCondition);

        if (empty($arrCategory)) {
            $this->redirect()->toRoute('backend', array('controller' => 'user', 'action' => 'index'));
        }

        $arrCategoryParent = $instanceSearchCategory->getList(['parent_id' => 0, 'not_cate_status' - 1]);
        $errors = array();

        if ($this->request->isPost()) {
            $params = $this->params()->fromPost();
            $cateName = trim($params['cate_name']);
            $cateIcon = trim($params['cate_icon']);
            $cateSort = (int) trim($params['cate_sort']);
            $cateMetaTitle = trim($params['cate_meta_title']);
            $cateMetaDescription = trim($params['cate_meta_description']);
            $cateMetaKeyword = trim($params['cate_meta_keyword']);
            $cateStatus = $params['cate_status'];

            if (empty($cateName)) {
                $errors['cate_name'] = 'Category name is not empty!';
            }

//            if (empty($cateIcon)) {
//                $errors['cate_icon'] = 'Icon is not empty!';
//            }

            if (empty($cateMetaTitle)) {
                $errors['cate_meta_title'] = 'SEO Meta Title is not empty!';
            }

            if (empty($cateMetaDescription)) {
                $errors['cate_meta_description'] = 'SEO meta Description is not Empty!';
            }

            if (empty($cateMetaKeyword)) {
                $errors['cate_meta_keyword'] = 'SEO meta Keyword is not Empty!';
            }

            if (empty($errors)) {

                $isExist = $instanceSearchCategory->getDetail(['cate_slug' => General::getSlug($cateName), 'not_cate_status' => -1, 'not_cate_id' => $intCategoryId, 'parent_id' => (int) $params['parent_id']]);

                if ($isExist) {
                    $errors[] = 'This category is exist in system!';
                }

                if (empty($errors)) {
                    $arrParams = array(
                        'cate_name' => $cateName,
                        'cate_icon' => $cateIcon,
                        'cate_slug' => General::getSlug($cateName),
                        'cate_meta_title' => $cateMetaTitle,
                        'cate_meta_description' => $cateMetaDescription,
                        'cate_meta_keyword' => $cateMetaKeyword,
                        'cate_sort' => $cateSort,
                        'cate_status' => $cateStatus,
                        'updated_date' => time(),
                        'user_updated' => UID,
                        'parent_id' => (int) $params['parent_id']
                    );

                    $serviceCategory = $this->serviceLocator->get('My\Models\Category');
                    $intResult = $serviceCategory->edit($arrParams, $intCategoryId);

                    if ($intResult) {
                        $serviceLogs = $this->serviceLocator->get('My\Models\Logs');
                        $arrLog = General::createLogs($arrParamsRoute, $arrParams, $intCategoryId);
                        $serviceLogs->add($arrLog);

                        $this->flashMessenger()->setNamespace('success-edit-category')->addMessage('Edit category success!');
                        $this->redirect()->toRoute('backend', array('controller' => 'category', 'action' => 'edit', 'id' => $intCategoryId));
                    } else {
                        $errors[] = 'An error processing edit! Please try again!';
                    }
                }
            }
        }
        return array(
            'params' => $params,
            'arrCategory' => $arrCategory,
            'errors' => $errors,
            'arrCategoryParent' => $arrCategoryParent
        );
    }

    public function deleteAction() {
        $arrParamsRoute = $this->params()->fromRoute();
        if ($this->request->isPost()) {
            $params = $this->params()->fromPost();
            if (empty($params['categoryId'])) {
                return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => 'Params input is valid!')));
            }
            $intCategoryId = (int) $params['categoryId'];

            $instanceSearchCategory = new \My\Search\Category();
            //find category children 

            $totalChildren = $instanceSearchCategory->getTotal(['parent_id' => $intCategoryId, 'not_cate_status' => -1]);
            if ($totalChildren > 0) {
                return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => 'Exist children category!')));
            }

            //find Category in system
            $arrCategory = $instanceSearchCategory->getDetail(['cate_id' => $intCategoryId]);

            if (empty($arrCategory)) {
                return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => 'Find not found Category in DB!')));
            }

            $arrParams = array(
                'cate_status' => -1,
                'user_updated' => UID,
                'updated_date' => time()
            );

            $serviceCategory = $this->serviceLocator->get('My\Models\Category');
            $result = $serviceCategory->edit($arrParams, $intCategoryId);

            if ($result) {
                $serviceLogs = $this->serviceLocator->get('My\Models\Logs');
                $arrLog = General::createLogs($arrParamsRoute, $arrParams, $intCategoryId);
                $serviceLogs->add($arrLog);

                return $this->getResponse()->setContent(json_encode(array('st' => 1, 'ms' => 'Deleted Category Success!')));
            }

            return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => 'An error processing edit! Please try again!')));
        }
    }

    public function changeIconAction() {
        return;
    }

}
