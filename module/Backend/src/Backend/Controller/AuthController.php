<?php

namespace Backend\Controller;

use Zend\Mvc\MvcEvent,
    Zend\Crypt\Password\Bcrypt,
    Zend\Mvc\Controller\AbstractActionController;
use My\General;

class AuthController extends AbstractActionController {
    /* @var $serviceUser \My\Models\User */
    /* @var $serviceGroup \My\Models\Group */
    /* @var $servicePermission \My\Models\Permission */

    protected $storage;
    protected $authservice;

    public function onDispatch(MvcEvent $event) {
        $event->getViewModel()->setTemplate('backend/auth');
        return parent::onDispatch($event);
    }

    public function getAuthService() {
        if (!$this->authservice) {
            $this->authservice = $this->serviceLocator->get('AuthService');
        }
        return $this->authservice;
    }

    public function indexAction() {
        $this->getAuthService()->clearIdentity();
        return $this->redirect()->toRoute('backend', array('controller' => 'auth', 'action' => 'login'));
    }

    public function loginAction() {
        $params = $this->params()->fromRoute();
        if ($this->getAuthService()->hasIdentity()) {
            $arrUserData = $this->getAuthService()->getIdentity();
            if ($arrUserData['grou_id'] == General::MEMBER) {
                return $this->redirect()->toRoute('home');
            }
            return $this->redirect()->toRoute('backend');
        }
        if ($this->request->isPost()) {
            $params = $this->params()->fromPost();
            $strEmail = trim(strip_tags($params['email']));
            $strPassword = trim($params['password']);
            $params['remember'] ? $intRemember = (int) $params['remember'] : $intRemember = 0;

            $arrReturn = array('params' => $params);

            if (empty($strEmail) || empty($strPassword)) {
                $arrReturn['error']['empty-username-password'] = 'Please enter Email and Password.';
                return $arrReturn;
            }

            $validator = new \My\Validator\Validate();

            if (!$validator->emailAddress($strEmail)) {
                $arrReturn['error']['email'] = 'Email address is Valid!';
            }

            $arrConditionUser = array(
                'user_email' => $strEmail,
                'user_status' => 1
            );

            $instanceSearchUser = new \My\Search\User();
            $arrUser = $instanceSearchUser->getDetail($arrConditionUser);

            if (empty($arrUser)) {
                $arrReturn['error']['email'] = 'Find not found user in system.';
                return $arrReturn;
            }

            if (md5($strPassword) != $arrUser['user_password']) {
                $arrReturn['error']['password'] = 'Password is valid';
                return $arrReturn;
            }

            $arrUserUpdate = array(
                'user_last_login' => time(),
                'user_login_ip' => $this->getRequest()->getServer('REMOTE_ADDR')
            );
            $serviceUser = $this->serviceLocator->get('My\Models\User');
            $intResult = $serviceUser->edit($arrUserUpdate, $arrUser['user_id']);
            if (empty($intResult)) {
                return $this->redirect()->toRoute('backend', array('controller' => 'auth', 'action' => 'login'));
            }

            $serviceGroup = $this->serviceLocator->get('My\Models\Group');
            $arrConditionGroup = array(
                'group_id' => $arrUser['group_id'],
                'group_status' => 1
            );
            
            $arrGroup = $serviceGroup->getDetail($arrConditionGroup);
            $arrConditionPermission = array(
                'or_group_id' => $arrUser['group_id'],
                'or_user_id' => $arrUser['user_id'],
                'not_perm_status' => -1
            );
            
            $servicePermission = $this->serviceLocator->get('My\Models\Permission');
            $arrPermission = $servicePermission->getList($arrConditionPermission);
            $arrUser['is_acp'] = $arrGroup['is_acp'];
            $arrUser['is_full_access'] = $arrGroup['is_full_access'];
            $arrUser['permission'] = $arrPermission;
            $this->getAuthService()->clearIdentity();

            $this->getAuthService()->getStorage()->write($arrUser);

            return $this->redirect()->toRoute('backend');
        }

        return array(
            'params' => $params,
        );
    }

    public function logoutAction($redirect = true) {
        $this->getAuthService()->clearIdentity();
        if ($redirect) {
            return $this->redirect()->toRoute('backend', array('controller' => 'auth', 'action' => 'login'));
        }
    }

}
