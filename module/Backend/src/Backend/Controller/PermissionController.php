<?php

namespace Backend\Controller;

class PermissionController extends \My\Controller\MyController {
    /* @var $serviceGroup \My\Models\Group */
    /* @var $servicePer \My\Models\Permission */

    public function __construct() {

        $this->externalJS = [
            'backend:permission:grant' => STATIC_URL . '/b/js/my/??permission.js',
        ];
    }

    public function indexAction() {
        return;
    }

    public function grantAction() {
        $params = $this->params()->fromRoute();
        $intPermissionGroupId = (int) $params['gid'];
        $intPermissionUserId = (int) $params['pid'];

        if (empty($intPermissionGroupId) && empty($intPermissionUserId)) {
            $this->flashMessenger()->setNamespace('empty-or-wrong-group')->addMessage('Permission is not correct');
            return $this->redirect()->toRoute('backend', array('controller' => 'permission', 'action' => 'index'));
        }

        if ($intPermissionUserId) {
            $intUserRole = $intPermissionUserId;
        }

        if ($intPermissionGroupId) {
            $intUserRole = $intPermissionGroupId;
            $serviceGroup = $this->serviceLocator->get('My\Models\Group');
            $currentPart = 'gid';

            $arrConditionGroup = array(
                'group_id' => $intPermissionGroupId,
                'not_grou_status' => -1
            );
            $arrGroup = $serviceGroup->getDetail($arrConditionGroup);

            if (empty($arrGroup)) {
                $this->flashMessenger()->setNamespace('empty-or-wrong-group')->addMessage('Permission is not correct');
                return $this->redirect()->toRoute('backend', array('controller' => 'group', 'action' => 'index'));
            }
            $arrGroupName = $arrGroup['group_name'];
            $arrCoditionPermission = array(
                'group_id' => $intPermissionGroupId,
                'not_perm_status' => -1
            );
        }
        
        if($intPermissionUserId){
            $intUserRole = $intPermissionUserId;
            $serviceUser = $this->serviceLocator->get('My\Models\User');
            $currentPart = 'pid';

            $arrConditionUser = array(
                'user_id' => $intPermissionUserId,
                'not_user_status' => -1
            );
            $arrUser = $serviceUser->getDetail($arrConditionUser);

            if (empty($arrUser)) {
                $this->flashMessenger()->setNamespace('empty-or-wrong-group')->addMessage('Permission is not correct');
                return $this->redirect()->toRoute('backend', array('controller' => 'permission', 'action' => 'index'));
            }
            
            $arrGroupName = $arrUser['user_fullname'];

            $arrCoditionPermission = array(
                'user_id' => $intPermissionUserId,
                'not_perm_status' => -1
            );
        }

        $servicePermission = $this->serviceLocator->get('My\Models\Permission');
        $arrResourceList = $servicePermission->getAllResource();

        $arrPermissionList = $servicePermission->getList($arrCoditionPermission);
        $arrAllowedResource = array();

        if (!empty($arrPermissionList)) {
            foreach ($arrPermissionList as $permission) {
                $arrAllowedResource[] = strtolower($permission['module']) . ':' . strtolower($permission['controller']) . ':' . strtolower($permission['action']);
            }
        }

        return array(
            'params' => $params,
            'arrResourceList' => $arrResourceList,
            'arrPermissionList' => $arrPermissionList,
            'currentPart' => $currentPart,
            'arrGroupName' => $arrGroupName,
            'intUserRole' => $intUserRole,
            'arrAllowedResource' => $arrAllowedResource
        );
    }

    public function addAction() {
        if ($this->request->isPost()) {
            $params = $this->params()->fromPost();

            $intUserRole = (int) $params['intUserRole'];
            $currentPart = $params['currentPart'];
            $resource = $params['resource'];

            if (empty($intUserRole) || empty($currentPart) || empty($resource)) {
                return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => 'Xảy ra lỗi trong quá trình xử lý, Vui lòng refresh trình duyệt và thử lại!')));
            }

            $arrResource = explode(':', $resource);
            $module = $arrResource[0];
            $controller = $arrResource[1];
            $action = $arrResource[2];

            $arrParams = array(
                'module' => $module,
                'controller' => $controller,
                'action' => $action
            );

            if ($currentPart == 'gid') {
                $arrParams['group_id'] = $intUserRole;
            } else {
                $arrParams['user_id'] = $intUserRole;
            }
            $servicePermission = $this->serviceLocator->get('My\Models\Permission');
            $intResult = $servicePermission->add($arrParams);

            if ($intResult) {
                return $this->getResponse()->setContent(json_encode(array('st' => 1)));
            }

            return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => 'Xảy ra lỗi trong quá trình xử lý, Vui lòng refresh trình duyệt và thử lại!')));
        }
    }

    public function deleteAction() {
        if ($this->request->isPost()) {
            $params = $this->params()->fromPost();

            $intUserRole = (int) $params['intUserRole'];
            $currentPart = $params['currentPart'];
            $resource = $params['resource'];

            if (empty($intUserRole) || empty($currentPart) || empty($resource)) {
                return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => 'Xảy ra lỗi trong quá trình xử lý, Vui lòng refresh trình duyệt và thử lại!')));
            }

            $arrResource = explode(':', $resource);
            $module = $arrResource[0];
            $controller = $arrResource[1];
            $action = $arrResource[2];

            $arrConditionPermission = array(
                'module' => $module,
                'controller' => $controller,
                'action' => $action,
                'perm_status' => 1
            );

            if ($currentPart == 'gid') {
                $arrConditionPermission['grou_id'] = $intUserRole;
            } else {
                $arrConditionPermission['user_id'] = $intUserRole;
            }

            $servicePermission = $this->serviceLocator->get('My\Models\Permission');
            $arrPermission = $servicePermission->getDetail($arrConditionPermission);

            if (empty($arrPermission)) {
                return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => 'Xảy ra lỗi trong quá trình xử lý, vui lòng refresh trình duyệt và thử lại!')));
            }

            $arrParams = array(
                'perm_status' => -1
            );

            $intResult = $servicePermission->edit($arrParams, $arrPermission['perm_id']);

            if ($intResult) {
                return $this->getResponse()->setContent(json_encode(array('st' => 1)));
            }

            return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => 'Xảy ra lỗi trong quá trình xử lý, Vui lòng refresh trình duyệt và thử lại!')));
        }
    }

}
