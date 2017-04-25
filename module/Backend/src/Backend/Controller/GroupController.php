<?php

namespace Backend\Controller;

use My\Validator\Validate;
use My\General;

class GroupController extends \My\Controller\MyController {
    /* @var $serviceGroup \My\Models\Group */
    /* @var $serviceUser \My\Models\User */

    public function __construct() {
        $this->externalJS = [
            'backend:group:index' => STATIC_URL . '/b/js/my/??group.js',
        ];
    }

    public function indexAction() {
        $params = array_merge($this->params()->fromQuery(), $this->params()->fromRoute());
        $intLimit = 30;

        $route = 'backend';
        $intPage = $this->params()->fromRoute('page', 1);

        $arrConditions = array(
            'not_group_status' => -1,
        );

        $serviceGroup = $this->serviceLocator->get('My\Models\Group');
        $arrGroupList = $serviceGroup->getListLimit($arrConditions, $intPage, $intLimit, 'group_id DESC');
        $intTotal = $serviceGroup->getTotal($arrConditions);

        $helper = $this->serviceLocator->get('viewhelpermanager')->get('Paging');
        $paging = $helper($params['module'], $params['__CONTROLLER__'], $params['action'], $intTotal, $intPage, $intLimit, $route, $params);

        $arrUserList = array();
        if (!empty($arrGroupList)) {
            $arrUserIdList = array();

            foreach ($arrGroupList as $arrGroup) {
                $arrUserIdList[] = $arrGroup['user_created'];
            }

            if (!empty($arrUserIdList)) {
                $arrUserIdList = array_unique($arrUserIdList);
                $strUserIdList = implode(',', $arrUserIdList);
                $serviceUser = $this->serviceLocator->get('My\Models\User');
                $arrReturn = $serviceUser->getList(array('in_user_id' => $strUserIdList));
                if (!empty($arrReturn)) {
                    foreach ($arrReturn as $arrUser) {
                        $arrUserList[$arrUser['user_id']] = $arrUser;
                    }
                }
            }
        }

        return array(
            'params' => $params,
            'paging' => $paging,
            'arrGroupList' => $arrGroupList,
            'arrUserList' => $arrUserList
        );
    }

    public function addAction() {
        $arrParamsRoute = $params = $this->params()->fromRoute();
        $errors = array();
        if ($this->request->isPost()) {
            $params = $this->params()->fromPost();
            $groupName = trim($params['groupName']);
            $isAcp = (int) $params['isAcp'];
            $isFullAccess = (int) $params['isFullaccess'];
            $status = (int) $params['status'];

            if (empty($groupName)) {
                $errors['groupName'] = 'Tên nhóm không được bỏ trống.';
            }

            if (empty($errors)) {
                //kiểm tra có tồn tại nhóm như vậy chưa
                $arrConditionGroup = array(
                    'group_name' => $groupName,
                    'not_group_status' => -1
                );
                $serviceGroup = $this->serviceLocator->get('My\Models\Group');
                $arrGroup = $serviceGroup->getDetail($arrConditionGroup);
                if (!empty($arrGroup)) {
                    $errors['groupName'] = 'Tên nhóm này đã tồn tại trong hệ thống';
                }

                if (empty($errors)) {
                    $arrParams = array(
                        'group_name' => $groupName,
                        'is_acp' => $isAcp,
                        'is_full_access' => $isFullAccess,
                        'created_date' => time(),
                        'user_created' => UID,
                        'group_status' => $status
                    );
                    $intResult = $serviceGroup->add($arrParams);

                    if ($intResult > 0) {
                        $serviceLogs = $this->serviceLocator->get('My\Models\Logs');
                        $arrLog = General::createLogs($arrParamsRoute, $arrParams, $intResult);
                        $serviceLogs->add($arrLog);

                        $this->flashMessenger()->setNamespace('success-add-group')->addMessage('Thêm nhóm thành công.');
                        $this->redirect()->toRoute('backend', array('controller' => 'group', 'action' => 'index'));
                    } else {
                        $errors[] = 'Xảy ra lỗi trong quá trình xử lý! Vui lòng thử lại !';
                    }
                }
            }
        }
        return array(
            'params' => $params,
            'errors' => $errors,
        );
    }

    public function editAction() {
        $arrParamsRoute = $params = $this->params()->fromRoute();

        if (empty($params['id'])) {
            $this->redirect()->toRoute('backend', array('controller' => 'group', 'action' => 'index'));
        }

        //Lấy thông tin nhóm
        $intGroupID = (int) $params['id'];
        $arrCondition = array(
            'group_id' => $intGroupID,
            'not_group_status' => -1
        );
        $serviceGroups = $this->serviceLocator->get('My\Models\Group');
        $arrGroups = $serviceGroups->getDetail($arrCondition);
        if (empty($arrGroups)) {
            $this->redirect()->toRoute('backend', array('controller' => 'group', 'action' => 'index'));
        }

        $errors = array();
        if ($this->request->isPost()) {
            $params = $this->params()->fromPost();
            if ($params && is_array($params)) {
                $validator = new Validate();
                if (!$validator->notEmpty($params['groupName'])) {
                    $errors['groupName'] = 'Tên nhóm không được bỏ trống.';
                }

                //kiểm tra có tồn tại nhóm như vậy chưa
                $arrConditionExist = array(
                    'group_name' => trim($params['groupName']),
                    'not_group_status' => -1,
                    'not_group_id' => $intGroupID
                );
                $arrExist = $serviceGroups->getDetail($arrConditionExist);

                if (!empty($arrExist)) {
                    $errors['groupName'] = 'Nhóm này đã tồn tại trong hệ thống!';
                }

                if (empty($errors)) {
                    $arrData = array(
                        'group_name' => trim($params['groupName']),
//                        'group_css' => trim($params['groupCss']),
                        'is_full_access' => (int) $params['isFullaccess'],
                        'is_acp' => trim($params['isAcp']),
                        'group_status' => (int) $params['status'],
                        'user_updated' => UID,
                        'updated_date' => time(),
                    );

                    $intResult = $serviceGroups->edit($arrData, $intGroupID);
                    if ($intResult > 0) {
                        $serviceLogs = $this->serviceLocator->get('My\Models\Logs');
                        $arrLog = General::createLogs($arrParamsRoute, $arrData, $intGroupID);
                        $serviceLogs->add($arrLog);

                        $this->flashMessenger()->setNamespace('success-edit-group')->addMessage('Chỉnh sửa nhóm thành công.');
                        $this->redirect()->toRoute('backend', array('controller' => 'group', 'action' => 'edit', 'id' => $intGroupID));
                    } else {
                        $errors[] = 'Không thể sửa dữ liệu. Xin vui lòng kiểm tra lại';
                    }
                }
            }
        }
        return array(
            'params' => $params,
            'arrGroup' => $arrGroups,
            'message' => $this->flashMessenger()->getMessages(),
            'errors' => $errors,
        );
    }

    public function deleteAction() {

        $arrParamsRoute = $this->params()->fromRoute();

        if ($this->request->isPost()) {

            $params = $this->params()->fromPost();
            if (empty($params['groupId'])) {
                return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => 'Xảy ra lỗi trong quá trình xử lý, Vui lòng refresh trình duyệt và thử lại!')));
            }

            $groupId = (int) $params['groupId'];
            $serviceGroup = $this->serviceLocator->get('My\Models\Group');

            //Kiểm tra sự tồn tại của group
            $arrConditionGroup = array(
                'not_group_status' => -1,
                'group_id' => $groupId
            );

            $arrGroup = $serviceGroup->getDetail($arrConditionGroup);

            if (empty($arrGroup)) {
                return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => 'Xảy ra lỗi trong quá trình xử lý, Vui lòng refresh trình duyệt và thử lại!')));
            }

            $result = $serviceGroup->edit(['group_status' => -1], $groupId);

            if ($result) {
                $serviceLogs = $this->serviceLocator->get('My\Models\Logs');
                $arrLog = General::createLogs($arrParamsRoute, ['group_status' => -1], $groupId);
                $serviceLogs->add($arrLog);

                return $this->getResponse()->setContent(json_encode(array('st' => 1, 'ms' => 'Xóa nhóm người dùng thành công!')));
            }
        }

        return $this->getResponse()->setContent(array('st' => -1, 'ms' => 'Xảy ra lỗi trong quá trình xử lý, Vui lòng refresh trình duyệt và thử lại!'));
    }

}
