<?php

namespace Backend\Controller;

use My\Controller\MyController,
    My\Validator\Validate,
    My\General;

class ContentController extends MyController
{
    /* @var $serviceCategory \My\Models\Category */
    /* @var $serviceContent \My\Models\Content */

    public function __construct()
    {

        $this->externalCSS = [
            STATIC_URL . '/b/css/??bootstrap-wysihtml5.css'
        ];

        $this->externalJS = [
            STATIC_URL . '/b/js/library/??wysihtml5-0.3.0.js,bootstrap-wysihtml5.js',
            STATIC_URL . '/b/js/my/??content.js'
        ];
    }

    public function indexAction()
    {
        $params = array_merge($this->params()->fromQuery(), $this->params()->fromRoute());
        $intPage = $this->params()->fromRoute('page', 1);
        $intLimit = 15;
        $arrCondition = array(
            'not_cont_status' => -1
        );

        $instanceSearchContent = new \My\Search\Content();
        $arrContentList = $instanceSearchContent->getListLimit(
            $arrCondition,
            $intPage,
            $intLimit,
            ['cont_id' => ['order' => 'desc']],
            [
                'cont_title',
                'cont_slug',
                'created_date',
                'user_created',
                'cate_id',
                'cont_status',
                'updated_date',
                'cont_id',
                'cont_views'
            ]
        );

        $route = 'backend';
        $intTotal = $instanceSearchContent->getTotal($arrCondition);
        $helper = $this->serviceLocator->get('viewhelpermanager')->get('Paging');
        $paging = $helper($params['module'], $params['__CONTROLLER__'], $params['action'], $intTotal, $intPage, $intLimit, $route, $params);

        if (!empty($arrContentList)) {

            foreach ($arrContentList as $arrContent) {
                if (!empty($arrContent['user_created'])) {
                    $arrUserIdList[] = $arrContent['user_created'];
                }
                if (!empty($arrContent['cate_id'])) {
                    $arrCategoryIdList[] = $arrContent['cate_id'];
                }
            }

            $arrUserIdList = array_unique($arrUserIdList);
            $arrCategoryIdList = array_unique($arrCategoryIdList);

            if (!empty($arrUserIdList)) {
                $instanceSearchUser = new \My\Search\User();
                $arrUserListTemp = $instanceSearchUser->getList(
                    ['in_user_id' => $arrUserIdList],
                    [
                        'user_id',
                        'user_name',
                        'user_email'
                    ]
                );
            }
            if (!empty($arrCategoryIdList)) {
                $instanceSearchCategory = new \My\Search\Category();
                $arrCategoryListTemp = $instanceSearchCategory->getList(
                    ['in_cate_id' => $arrCategoryIdList],
                    [
                        'cate_id',
                        'cate_name',
                        'cate_slug'
                    ]
                );
            }

            //format lại 2 array
            if (!empty($arrUserListTemp)) {
                foreach ($arrUserListTemp as $arrUser) {
                    $arrUserList[$arrUser['user_id']] = $arrUser;
                }
            }
            if (!empty($arrCategoryListTemp)) {
                foreach ($arrCategoryListTemp as $arrCategory) {
                    $arrCategoryList[$arrCategory['cate_id']] = $arrCategory;
                }
            }
        }

        return array(
            'params' => $params,
            'paging' => $paging,
            'arrContentList' => $arrContentList,
            'arrUserList' => $arrUserList,
            'arrCategoryList' => $arrCategoryList
        );
    }

    public function addAction()
    {
        $params = $this->params()->fromRoute();
        $serviceCategory = $this->serviceLocator->get('My\Models\Category');
        $arrConditionCategory = array(
            'not_cate_status' => -1
        );
        $arrCategoryList = $serviceCategory->getList($arrConditionCategory);

        $errors = array();

        if ($this->request->isPost()) {
            $params = $this->params()->fromPost();
            $contName = trim($params['cont_name']);
            $contContent = trim($params['cont_content']);
            $contSort = (int)trim($params['cont_sort']);
            $contDescription = trim($params['cont_description']);
            $contMetaTitle = trim($params['cont_meta_title']);
            $contMetaDescription = trim($params['cont_meta_description']);
            $contMetaKeyword = trim($params['cont_meta_keyword']);
            $contStatus = $params['cont_status'];
            $cateId = $params['cate_id'];

            if (empty($contName)) {
                $errors['cont_name'] = 'Tiêu đề bài viết không được bỏ trống!';
            }

            if (empty($contContent)) {
                $errors['cont_content'] = 'Nội dung bài viết không được bỏ trống!';
            }

            if (empty($contDescription)) {
                $errors['cont_meta_description'] = 'Chưa nhập mô tả cho bài viết!';
            }

            if (empty($contMetaTitle)) {
                $errors['cont_meta_title'] = 'Meta title không được bỏ trống!';
            }

            if (empty($contMetaDescription)) {
                $errors['cont_meta_description'] = 'Meta Description không được bỏ trống!';
            }

            if (empty($contMetaKeyword)) {
                $errors['cont_meta_keyword'] = 'Meta Keyword không được bỏ trống!';
            }

            if (empty($cateId)) {
                $errors['cate_id'] = 'Chưa chọn danh mục cho bài đăng!';
            }

            if (empty($errors)) {
                $serviceContent = $this->serviceLocator->get('My\Models\Content');
                $arrCondition = array(
                    'cont_slug' => General::getSlug($contName),
                    'not_cont_status' => -1,
                    'cate_id' => $cateId
                );

                $arrResult = $serviceContent->getList($arrCondition);

                if ($arrResult) {
                    $errors[] = 'Tiêu đề bài đăng này đã tồn tại trong danh mục này!';
                }

                if (empty($errors)) {
                    $arrParams = array(
                        'cont_title' => $contName,
                        'cont_slug' => General::getSlug($contName),
                        'cate_id' => $cateId,
                        'cont_description' => $contDescription,
                        'cont_content' => $contContent,
                        'cont_meta_title' => $contMetaTitle,
                        'cont_meta_description' => $contMetaDescription,
                        'cont_meta_keyword' => $contMetaKeyword,
                        'cont_sort' => $contSort,
                        'cont_status' => $contStatus,
                        'created_date' => time(),
                        'user_created' => UID
                    );

                    $intResult = $serviceContent->add($arrParams);

                    if ($intResult) {
                        $this->flashMessenger()->setNamespace('success-add-content')->addMessage('Thêm bài đăng thành công !');
                        $this->redirect()->toRoute('backend', array('controller' => 'content', 'action' => 'index'));
                    }
                    $errors[] = 'Xảy ra lỗi trong quá trình thêm dữ liệu! Vui lòng thử lại';
                }
            }
        }

        return array(
            'params' => $params,
            'arrCategoryList' => $arrCategoryList,
            'errors' => $errors
        );
    }

    public function editAction()
    {
        $params = $this->params()->fromRoute();
        if (empty($params['id'])) {
            $this->redirect()->toRoute('backend', array('controller' => 'content', 'action' => 'index'));
        }
        $intContentId = (int)$params['id'];

        //get content detail
        $arrConditionContent = array(
            'cont_id' => $intContentId
        );

        $instanceSearchContent = new \My\Search\Content();
        $arrContent = $instanceSearchContent->getDetail($arrConditionContent);

        if (empty($arrContent)) {
            $this->redirect()->toRoute('backend', array('controller' => 'content', 'action' => 'index'));
        }

        //get list category
        $instanceSearchCategory = new \My\Search\Category();
        $arrCategoryList = $instanceSearchCategory->getList(
            [
                'not_cate_status' => -1
            ],
            [
                'cate_id',
                'cate_name',
                'parent_id'
            ]
        );

        $errors = array();

        if ($this->request->isPost()) {
            $params = $this->params()->fromPost();
            $contName = trim($params['cont_name']);
            $contContent = trim($params['cont_content']);
            $contSort = (int)trim($params['cont_sort']);
            $contDescription = trim($params['cont_description']);
            $contMetaTitle = trim($params['cont_meta_title']);
            $contMetaDescription = trim($params['cont_meta_description']);
            $contMetaKeyword = trim($params['cont_meta_keyword']);
            $contStatus = $params['cont_status'];
            $cateId = $params['cate_id'];

            if (empty($contName)) {
                $errors['cont_name'] = 'Tiêu đề bài viết không được bỏ trống!';
            }

            if (empty($contContent)) {
                $errors['cont_content'] = 'Nội dung bài viết không được bỏ trống!';
            }

            if (empty($contDescription)) {
                $errors['cont_meta_description'] = 'Chưa nhập mô tả cho bài viết!';
            }

            if (empty($contMetaTitle)) {
                $errors['cont_meta_title'] = 'Meta title không được bỏ trống!';
            }

            if (empty($contMetaDescription)) {
                $errors['cont_meta_description'] = 'Meta Description không được bỏ trống!';
            }

            if (empty($contMetaKeyword)) {
                $errors['cont_meta_keyword'] = 'Meta Keyword không được bỏ trống!';
            }

            if (empty($cateId)) {
                $errors['cate_id'] = 'Chưa chọn danh mục cho bài đăng!';
            }

            if (empty($errors)) {
                $arrCondition = array(
                    'cont_slug' => General::getSlug($contName),
                    'not_cont_status' => -1,
                    'cate_id' => $cateId,
                    'not_cont_id' => $intContentId
                );
                $arrResult = $instanceSearchContent->getList($arrCondition);

                if ($arrResult) {
                    $errors[] = 'Tiêu đề bài đăng này đã tồn tại trong danh mục này!';
                }

                if (empty($errors)) {
                    $arrParams = array(
                        'cont_title' => $contName,
                        'cont_slug' => General::getSlug($contName),
                        'cate_id' => $cateId,
                        'cont_description' => $contDescription,
                        'cont_content' => $contContent,
                        'cont_meta_title' => $contMetaTitle,
                        'cont_meta_description' => $contMetaDescription,
                        'cont_meta_keyword' => $contMetaKeyword,
                        'cont_sort' => $contSort,
                        'cont_status' => $contStatus,
                        'updated_date' => time(),
                        'user_updated' => UID
                    );

                    $serviceContent = $this->serviceLocator->get('My\Models\Content');
                    $intResult = $serviceContent->edit($arrParams, $intContentId);

                    if ($intResult) {
                        $this->flashMessenger()->setNamespace('success-edit-content')->addMessage('Chỉnh sửa bài đăng thành công !');
                        $this->redirect()->toRoute('backend', array('controller' => 'content', 'action' => 'edit', 'id' => $intContentId));
                    } else
                        $errors[] = 'Xảy ra lỗi trong quá trình cập nhật dữ liệu! Vui lòng thử lại';
                }
            }
        }

        return array(
            'params' => $params,
            'arrContent' => $arrContent,
            'errors' => $errors,
            'arrCategoryList' => $arrCategoryList
        );
    }

    public function deleteAction()
    {
        $paramsRoute = $this->params()->fromRoute();
        if ($this->request->isPost()) {
            $params = $this->params()->fromPost();
            if (empty($params['cont_id'])) {
                return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => 'Xảy ra lỗi ! Vui lòng thử lại!')));
            }

            //find Content in system
            $instanceSearchContent = new \My\Search\Content();
            $content = $instanceSearchContent->getDetail(
                [
                    'cont_id' => $params['cont_id'],
                    'not_cont_status' => -1
                ],
                [
                    'cont_id'
                ]
            );

            if (empty($content)) {
                return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => 'Tin này không tồn tại trong hệ thống!')));
            }

            $serviceContent = $this->serviceLocator->get('My\Models\Content');
            $result = $serviceContent->edit(['cont_status' => -1, 'updated_date' => time(), 'user_updated' => UID], $content['cont_id']);

            if ($result) {
                $serviceLogs = $this->serviceLocator->get('My\Models\Logs');
                $arrLog = General::createLogs($paramsRoute, $params, $content['cont_id']);
                $serviceLogs->add($arrLog);
                return $this->getResponse()->setContent(json_encode(array('st' => 1, 'ms' => 'Xoá tin thành công!')));
            }
            return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => 'Xảy ra lỗi trong quá trình xử lý ! Vui lòng thử lại!')));
        }
    }

    public function upvipAction()
    {
        $params_route = $this->params()->fromRoute();
        if ($this->request->isPost()) {
            $params = $this->params()->fromPost();

            if (empty($params['cont_id']) || empty($params['num_date']) || empty($params['type_vip'])) {
                return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => 'Lỗi tham số truyền lên!')));
            }

            $instanceSearchContent = new \My\Search\Content();
            $content = $instanceSearchContent->getDetail(['cont_id' => $params['cont_id'], 'not_cont_status' => -1]);

            if (empty($content)) {
                return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => 'Rao vặt này không tồn tại trong hệ thống!')));
            }

            $data = [
                'is_vip' => 1,
                'vip_type' => $params['type_vip'],
                'expired_time' => time() + ((int)$params['num_date'] * 60 * 60 * 24),
                'updated_date' => time(),
                'user_updated' => UID
            ];
            $serviceContent = $this->serviceLocator->get('My\Models\Content');
            $result = $serviceContent->edit($data, $content['cont_id']);

            if ($result) {
                $serviceLogs = $this->serviceLocator->get('My\Models\Logs');
                $arrLog = General::createLogs($params_route, $params, $content['cont_id']);
                $serviceLogs->add($arrLog);

                $image = $params['type_vip'] == \My\General::VIP_ALL_PAGE ? STATIC_URL . '/f/v1/images/s-vip.gif' : STATIC_URL . '/f/v1/images/vip2.gif';

                return $this->getResponse()->setContent(json_encode(array('st' => 1, 'ms' => '<b>Úp thành công ' . $params['num_date'] . ' ngày <img src="' . $image . '"> cho tin <b style="color:red"> RV' . sprintf("%04d", $content['cont_id']) . '</b>!</b>')));
            }
            return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => 'Xảy ra lỗi trong quá trình xử lý ! Vui lòng thử lại!')));
        }
    }

    public function testAction()
    {
        echo 'Thành cmn công rồi nhé!';
        die();
    }

}
