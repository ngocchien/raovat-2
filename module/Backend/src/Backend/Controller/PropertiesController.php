<?php

namespace Backend\Controller;

use My\Controller\MyController,
    My\Validator\Validate,
    My\General;

class PropertiesController extends MyController {
    /* @var $serviceCategory \My\Models\Category */

    public function __construct() {
        $this->externalJS = [
            STATIC_URL . '/b/js/my/??properties.js'
        ];
    }

    public function indexAction() {
        $params = array_merge($this->params()->fromQuery(), $this->params()->fromRoute());
        $intPage = $this->params()->fromRoute('page', 1);
        $intLimit = 15;
        $arrCondition = array(
            'not_prop_status' => -1
        );

        $serviceProperties = $this->serviceLocator->get('My\Models\Properties');
        $arrPropertiesList = $serviceProperties->getListLimit($arrCondition, $intPage, $intLimit, 'prop_grade ASC');

        $route = 'backend';

        $intTotal = $serviceProperties->getTotal($arrConditions);
        $helper = $this->serviceLocator->get('viewhelpermanager')->get('Paging');
        $paging = $helper($params['module'], $params['__CONTROLLER__'], $params['action'], $intTotal, $intPage, $intLimit, $route, $params);

        if (!empty($arrPropertiesList)) {
            foreach ($arrPropertiesList as $arrProperties) {
                $arrUserIdList[] = $arrProperties['user_created'];
            }
            if (!empty($arrUserIdList)) {
                $arrUserIdList = array_unique($arrUserIdList);
                $strUserList = implode(',', $arrUserIdList);
                $serviceUser = $this->serviceLocator->get('My\Models\User');
                $arrConditionUser = [
                    'user_id_list' => $strUserList
                ];
                $arrUserList = $serviceUser->getList($arrConditionUser);
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
            'arrPropertiesList' => $arrPropertiesList,
            'arrUserList' => $arrUserListFM
        );
    }

    public function addAction() {
        $arrParamsRoute = $params = $this->params()->fromRoute();

        $serviceProperties = $this->serviceLocator->get('My\Models\Properties');
        //danh sách danh mục cha
        $arrConditionParent = [
            'parent_id' => 0,
            'not_prop_status' => -1
        ];
        $arrParentList = $serviceProperties->getList($arrConditionParent);

        $errors = array();

        if ($this->request->isPost()) {
            $params = $this->params()->fromPost();
            $propName = trim($params['prop_name']);
            $propSort = (int) trim($params['prop_sort']);
            $propStatus = $params['prop_status'];
            $parentId = $params['parent_id'];

            if (empty($propName)) {
                $errors['prop_name'] = 'Tên nhu cầu không được bỏ trống!';
            }

            if (empty($errors)) {
                $arrCondition = array(
                    'prop_slug' => General::getSlug($propName),
                    'not_prop_status' => -1,
                    'parent_id' => $parentId
                );
                $arrResult = $serviceProperties->getList($arrCondition);

                if ($arrResult) {
                    $errors[] = 'Nhu cầu này đã tồn tại trong hệ thống!';
                }

                if (empty($errors)) {
                    $arrParams = [
                        'prop_name' => $propName,
                        'prop_slug' => General::getSlug($propName),
                        'prop_sort' => $propSort,
                        'prop_status' => $propStatus,
                        'created_date' => time(),
                        'user_created' => UID,
                        'parent_id' => $parentId
                    ];
                    $intResult = $serviceProperties->add($arrParams);
                    if ($intResult) {
                        if ($parentId > 0) {
                            foreach ($arrParentList as $value) {
                                if ($value['prop_id'] == $parentId) {
                                    $detailParent = $value;
                                    continue;
                                }
                            }
                            $dataUpdate = array(
                                'prop_grade' => $detailParent['prop_grade'] . sprintf("%04d", $propSort) . ':' . sprintf("%04d", $intResult) . ':',
                                'prop_status' => $detailParent['prop_status']
                            );
                        } else {
                            $dataUpdate = array(
                                'prop_grade' => sprintf("%04d", $propSort) . ':' . sprintf("%04d", $intResult) . ':',
                                'prop_status' => $propStatus
                            );
                        }
                        $serviceProperties->edit($dataUpdate, $intResult);

                        $serviceLogs = $this->serviceLocator->get('My\Models\Logs');
                        $arrLog = General::createLogs($arrParamsRoute, $arrParams, $intResult);
                        $serviceLogs->add($arrLog);
                        $this->flashMessenger()->setNamespace('success-add-properties')->addMessage('Thêm nhu cầu rao vặt thành công !');
                        $this->redirect()->toRoute('backend', array('controller' => 'properties', 'action' => 'edit', 'id' => $intResult));
                    }
                    $errors[] = 'Xảy ra lỗi trong quá trình thêm dữ liệu! Vui lòng thử lại';
                }
            }
        }
        return array(
            'params' => $params,
            'errors' => $errors,
            'arrParentList' => $arrParentList
        );
    }

    public function editAction() {
        $arrParamsRoute = $params = $this->params()->fromRoute();
        if (empty($params['id'])) {
            $this->redirect()->toRoute('backend', array('controller' => 'properties', 'action' => 'index'));
        }
        $intId = (int) $params['id'];
        $arrCondition = array('prop_id' => $intId, 'not_prop_status' => -1);
        $serviceProperties = $this->serviceLocator->get('My\Models\Properties');
        $arrProperties = $serviceProperties->getDetail($arrCondition);

        if (empty($arrProperties)) {
            $this->redirect()->toRoute('backend', array('controller' => 'user', 'action' => 'index'));
        }
        $errors = array();
        $arrParentList = [];
        if ($arrProperties['parent_id'] != 0) {
            //danh sách danh mục cha
            $arrConditionParent = [
                'parent_id' => 0,
                'not_prop_status' => -1
            ];
            $arrParentList = $serviceProperties->getList($arrConditionParent);
        }

        if ($this->request->isPost()) {
            $params = $this->params()->fromPost();

            $propName = trim($params['prop_name']);
            $propSort = (int) trim($params['prop_sort']);
            $propStatus = $params['prop_status'];
            $parentId = (int) $params['parent_id'];

            if (empty($propName)) {
                $errors['prop_name'] = 'Tên nhu cầu không được bỏ trống!';
            }

            if (empty($errors)) {
                $arrCondition = array(
                    'prop_slug' => General::getSlug($propName),
                    'not_prop_status' => -1,
                    'not_prop_id' => $intId,
                    'parent_id' => $parentId,
                    'not_prop_sort' => $propSort
                );
                $arrResult = $serviceProperties->getDetail($arrCondition);

                if ($arrResult) {
                    $errors[] = 'Nhu cầu rao vặt này đã tồn tại trong hệ thống!';
                }

                if (empty($errors)) {
                    $arrParams = array(
                        'prop_name' => $propName,
                        'prop_slug' => General::getSlug($propName),
                        'prop_sort' => $propSort,
                        'prop_status' => $propStatus,
                        'updated_date' => time(),
                        'user_updated' => UID,
                        'parent_id' => $parentId
                    );

                    $intResult = $serviceProperties->edit($arrParams, $intId);

                    if ($intResult) {
                        if ($arrProperties['parent_id'] != $parentId || $arrProperties['prop_sort'] != $propSort) {
                            $detailParent = $serviceProperties->getDetail(array('prop_id' => $parentId));

                            if (!empty($detailParent)) {
                                $dataUpdate = array(
                                    'prop_grade' => $arrProperties['prop_grade'],
                                    'grade_update' => $detailParent['prop_grade'] . sprintf("%04d", $propSort) . ':' . sprintf("%04d", $intId) . ':',
                                    'prop_status' => $detailParent['prop_status'],
                                    'parentID' => $parentId,
                                );
                            } else {
                                $dataUpdate = array(
                                    'prop_grade' => $arrProperties['prop_grade'],
                                    'grade_update' => sprintf("%04d", $propSort) . ':' . sprintf("%04d", $intId) . ':',
                                    'prop_status' => $propStatus,
                                    'parentID' => $parentId,
                                );
                            }

                            $serviceProperties->updateTree($dataUpdate);
                        }

                        if ($arrProperties['prop_status'] != $propStatus) {
                            $dataUpdate = array(
                                'prop_status' => $propStatus,
                                'grade_update' => $arrProperties['prop_grade'],
                            );
                            $serviceProperties->updateStatusTree($dataUpdate);
                        }

                        $serviceLogs = $this->serviceLocator->get('My\Models\Logs');
                        $arrLog = General::createLogs($arrParamsRoute, $arrParams, $intId);
                        $serviceLogs->add($arrLog);

                        $this->flashMessenger()->setNamespace('success-edit-properties')->addMessage('Chỉnh sửa nhu cầu rao vặt thành công !');
                        $this->redirect()->toRoute('backend', array('controller' => 'properties', 'action' => 'edit', 'id' => $intId));
                    } else {
                        $errors[] = 'Xảy ra lỗi trong quá trình thêm dữ liệu! Vui lòng thử lại';
                    }
                }
            }
        }
        return array(
            'params' => $params,
            'arrProperties' => $arrProperties,
            'errors' => $errors,
            'arrParentList' => $arrParentList
        );
    }

    public function deleteAction() {
        $arrParamsRoute = $this->params()->fromRoute();
        if ($this->request->isPost()) {
            $params = $this->params()->fromPost();
            if (empty($params['id'])) {
                return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => 'Xảy ra lỗi ! Vui lòng thử lại!')));
            }
            $id = (int) $params['id'];
            //find Category in system
            $serviceProperties = $this->serviceLocator->get('My\Models\Properties');
            $arrCondition = array(
                'prop_id' => $id,
                'not_prop_status' => -1
            );
            $arrProperties = $serviceProperties->getDetail($arrCondition);

            if (empty($arrProperties)) {
                return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => 'Find not found Properties in DB!')));
            }

            /**/
            $arrPropertiesChild = [];
            if ($arrProperties['parent_id'] == 0) {
                $arrConditionChild = [
                    'parent_id' => $id,
                    'not_cate_status' => -1
                ];
                $arrPropertiesChild = $serviceProperties->getDetail($arrConditionChild);
            }

            if (!empty($arrPropertiesChild)) {
                return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => 'Nhóm nhu cầu này có nhiều nhu cầu con! Vui lòng xóa các nhu cầu con trước!')));
            }

            $arrParams = array(
                'prop_status' => -1,
                'user_updated' => UID,
                'updated_date' => time()
            );

            $result = $serviceProperties->edit($arrParams, $id);

            if ($result) {
                $serviceLogs = $this->serviceLocator->get('My\Models\Logs');
                $arrLog = General::createLogs($arrParamsRoute, $arrParams, $id);
                $serviceLogs->add($arrLog);

                return $this->getResponse()->setContent(json_encode(array('st' => 1, 'ms' => 'Deleted Properties Success!')));
            }

            return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => 'Xảy ra lỗi trong quá trình xử lý ! Vui lòng thử lại!')));
        }
    }

    public function changeIconAction() {
        return;
    }

}
