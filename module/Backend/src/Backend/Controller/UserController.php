<?php

namespace Backend\Controller;

use My\Controller\MyController,
    My\Validator\Validate,
    My\General;

class UserController extends MyController {
    /* @var $serviceUser \My\Models\User */
    /* @var $serviceGroup \My\Models\Group */
    /* @var $serviceCity \My\Models\City */
    /* @var $serviceDistrict \My\Models\District */

    public function __construct() {
        $this->externalJS = [
            'backend:user:index' => STATIC_URL . '/b/js/my/??user.js',
            'backend:user:add' => STATIC_URL . '/b/js/my/??user.js',
            'backend:user:edit' => STATIC_URL . '/b/js/my/??user.js',
        ];
    }

    public function indexAction() {
        $params = array_merge($this->params()->fromQuery(), $this->params()->fromRoute());
        $intPage = $this->params()->fromQuery('page', 1);
        $intLimit = $this->params()->fromQuery('limit', 15);

        $arrConditionsUser = array(
            'not_user_status' => -1,
        );

        if (!FULL_ACCESS) {
            $arrConditionsUser['not_grou_id'] = 2;
        }

        $route = 'backend-user-search';

        $serviceUser = $this->serviceLocator->get('My\Models\User');
        $arrUserList = $serviceUser->getListLimit($arrConditionsUser, $intPage, $intLimit, 'user_id DESC');

        $intTotal = $serviceUser->getTotal($arrConditions);

        $helper = $this->serviceLocator->get('viewhelpermanager')->get('Paging');
        $paging = $helper($params['module'], $params['__CONTROLLER__'], $params['action'], $intTotal, $intPage, $intLimit, $route, $params);

        // get group list
        $groupCondition = array(
            'not_group_status=' => -1,
        );

        $serviceGroup = $this->serviceLocator->get('My\Models\Group');
        $groupList = $serviceGroup->getList($groupCondition);

        //format group
        foreach ($groupList as $arrGroup) {
            $arrGroupListFormat[$arrGroup['group_id']] = $arrGroup;
        }

        return array(
            'params' => $params,
            'paging' => $paging,
            'arrUserList' => $arrUserList,
            'arrGroupList' => $arrGroupListFormat
        );
    }

    public function addAction() {
        $arrParamsRoute = $params = $this->params()->fromRoute();
        $serviceGroup = $this->serviceLocator->get('My\Models\Group');
        $arrGroupList = $serviceGroup->getList(array('not_group_status' => -1));
        $errors = array();

        if ($this->request->isPost()) {
            $params = $this->params()->fromPost();
            $fullName = trim($params['fullName']);
            $email = trim($params['email']);
            $gender = $params['gender'];
            $birthdate = trim($params['birthdate']);
            $phoneNumber = trim($params['phoneNumber']);
            $password = $params['password'];
            $rePassword = $params['rePassword'];
            $group = (int) $params['group'];
            $userStatus = $params['user_status'];

            //validate full name
            if (empty($fullName)) {
                $errors['fullName'] = 'Họ tên không được bỏ trống !';
            } else {
                if (strlen($fullName) < 5) {
                    $errors['fullName'] = 'Họ tên không được nhỏ hơn 5 ký tự !';
                }
            }

            //Validate email
            if (empty($email)) {
                $errors['email'] = 'Email không được bỏ trống !';
            } else {
                $validatorEmail = new \Zend\Validator\EmailAddress();
                (!$validatorEmail->isValid($email)) ? $errors['email'] = 'Địa chỉ email không không đúng.' : null;
            }

            //Validate phone 
            if (empty($phoneNumber)) {
                $errors['phoneNumber'] = 'Số điện thoại không được bỏ trống !';
            } elseif (strlen($phoneNumber) > 11 || strlen($phoneNumber) < 8) {
                $errors['phoneNumber'] = 'Số điện thoại phải từ 8 đến 11 chữ số !';
            } else {
                $validatorDigits = new \Zend\Validator\Digits();
                if (!$validatorDigits->isValid($phoneNumber)) {
                    $errors['phoneNumber'] = 'Số điện thoại phải là số !';
                }
            }

            //validate Password
            if (empty($password) || strlen($password) < 6) {
                $errors['password'] = 'Mật khẩu không được bỏ trống và phải tử 6 ký tự trở lên!';
            }

            if (empty($rePassword)) {
                $errors['rePassword'] = 'Chưa xác nhận lại mật khẩu !';
            } else {
                if ($rePassword != $password) {
                    $errors['rePassword'] = 'Hai mật khẩu chưa giống nhau !';
                }
            }

            if (empty($group)) {
                $errors['group'] = 'Chưa chọn nhóm cho người dùng !';
            }

            if (empty($errors)) {
                $serviceUser = $this->serviceLocator->get('My\Models\User');
                //check email trong database
                $arrCondionEmail = array(
                    'user_email' => $email,
                    'not_user_status' => -1
                );
                $arrUsermail = $serviceUser->getList($arrCondionEmail);

                if (count($arrUsermail) > 0) {
                    $errors['email'] = 'Emal này đã tồn tại trong hệ thống.';
                }

                //check phone number in database
                $arrCondionPhone = array(
                    'user_phone' => $phoneNumber,
                    'not_user_status' => -1
                );
                $arrUserphone = $serviceUser->getList($arrCondionPhone);
                if (count($arrUserphone) > 0) {
                    $errors['phoneNumber'] = 'Số điện thoại này đã tồn tại trong hệ thống.';
                }

                if (empty($errors)) {
                    list($day, $month, $year) = explode('/', $birthdate);
                    $birthDate = mktime(0, 0, 0, $month, $day, $year);

                    $arrData = array(
                        'user_fullname' => $fullName,
                        'user_email' => $email,
                        'user_phone' => $phoneNumber,
                        'user_birthday' => $birthDate,
                        'user_gender' => $gender,
                        'user_password' => md5($password),
                        'group_id' => $group,
                        'user_status' => $userStatus,
                        'user_created' => UID,
                        'created_date' => time()
                    );
                    $intResult = $serviceUser->add($arrData);
                    if ($intResult > 0) {
                        $serviceLogs = $this->serviceLocator->get('My\Models\Logs');
                        $arrLog = General::createLogs($arrParamsRoute, $arrData, $intResult);
                        $serviceLogs->add($arrLog);

                        $this->flashMessenger()->setNamespace('success-add-user')->addMessage('Thêm người dùng thành công.');
                        $this->redirect()->toRoute('backend', array('controller' => 'user', 'action' => 'index'));
                    }

                    $errors[] = 'Không thể thêm dữ liệu. Hoặc email đã tồn tại. Xin vui lòng kiểm tra lại';
                }
            }
        }

        return array(
            'params' => $params,
            'errors' => $errors,
            'arrGroupList' => $arrGroupList,
        );
    }

    public function editAction() {
        $arrParamsRoute = $params = $this->params()->fromRoute();
        if (empty($params['id'])) {
            $this->redirect()->toRoute('backend', array('controller' => 'user', 'action' => 'index'));
        }
        $intUserID = (int) $params['id'];
        $arrCondition = array('user_id' => $intUserID, 'not_user_status' => -1);
        $serviceUser = $this->serviceLocator->get('My\Models\User');
        $arrUser = $serviceUser->getDetail($arrCondition);

        if (empty($arrUser)) {
            $this->redirect()->toRoute('backend', array('controller' => 'user', 'action' => 'index'));
        }

        $errors = array();
        if ($this->request->isPost()) {
            $params = $this->params()->fromPost();
            if ($params && is_array($params)) {

                $validator = new Validate();
                if (!$validator->notEmpty($params['fullName'])) {
                    $errors['fullname'] = 'Tên người dùng không được bỏ trống.';
                }
                if (!$validator->notEmpty($params['email'])) {
                    $errors['email'] = 'Email người dùng không được bỏ trống.';
                }
                if (!$validator->notEmpty($params['phoneNumber'])) {
                    $errors['phoneNumber'] = 'Số điện thoại không được bỏ trống.';
                }
                if (!$validator->notEmpty($params['birthdate'])) {
                    $errors['birthdate'] = 'Vui lòng nhập ngày sinh.';
                }
                if (!$validator->notEmpty($params['gender'])) {
                    $errors['gender'] = 'Vui lòng chọn giới tính.';
                }
                if (!$params['group']) {
                    $errors['group'] = 'Vui lòng chọn nhóm người dùng.';
                }

                if (!empty($params['password'])) {
                    if (strlen($params['password']) < 6) {
                        $errors['password'] = 'Mật khẩu phải từ 6 ký tự trở lên';
                    }
                }
                if (empty($errors)) {

                    //check email trong database
                    $arrCondionEmail = array(
                        'user_email' => $params['email'],
                        'not_user_status' => -1,
                        'not_user_id' => $intUserID
                    );
                    $arrUsermail = $serviceUser->getDetail($arrCondionEmail);

                    if (!empty($arrUsermail)) {
                        $errors['email'] = 'Emal này đã tồn tại trong hệ thống.';
                    }

                    //check phone number in database
                    $arrCondionPhone = array(
                        'user_phone' => $params['phoneNumber'],
                        'not_user_status' => -1,
                        'not_user_id' => $intUserID
                    );
                    $arrUserphone = $serviceUser->getDetail($arrCondionPhone);
                    if (!empty($arrUserphone)) {
                        $errors['phoneNumber'] = 'Số điện thoại này đã tồn tại trong hệ thống.';
                    }

                    if (empty($errors)) {

                        list($day, $month, $year) = explode('-', $params['birthdate']);
                        $birthDate = mktime(0, 0, 0, $month, $day, $year);

                        $arrData = array(
                            'user_fullname' => trim($params['fullName']),
                            'user_email' => trim($params['email']),
                            'user_phone' => $params['phoneNumber'],
                            'user_birthday' => $birthDate,
                            'user_updated' => UID,
                            'user_gender' => (int) $params['gender'],
                            'user_status' => (int) $params['user_status'],
                            'group_id' => (int) $params['group'],
                            'updated_date' => time(),
                        );
                        if (!empty($params['password'])) {
                            $arrData['user_password'] = md5($params['password']);
                        }
                        $intResult = $serviceUser->edit($arrData, $intUserID);
                        if ($intResult > 0) {
                            $serviceLogs = $this->serviceLocator->get('My\Models\Logs');
                            $arrLog = General::createLogs($arrParamsRoute, $params, $intUserID);
                            $serviceLogs->add($arrLog);

                            $this->flashMessenger()->setNamespace('success-edit-user')->addMessage('Chỉnh sửa người dùng thành công.');
                            $this->redirect()->toRoute('backend', array('controller' => 'user', 'action' => 'edit', 'id' => $intUserID));
                        } else {
                            $errors[] = 'Không thể sửa dữ liệu hoặc địa chỉ email đã tồn tại. Xin vui lòng kiểm tra lại';
                        }
                    }
                }
            }
        }

        $groupCondition = array(
            'not_group_status' => -1,
        );

        $serviceGroup = $this->serviceLocator->get('My\Models\Group');

        $arrGroupList = $serviceGroup->getList($groupCondition);
        return array(
            'params' => $params,
            'arrUser' => $arrUser,
            'message' => $this->flashMessenger()->getMessages(),
            'errors' => $errors,
            'arrGroupList' => $arrGroupList
        );
    }

    public function viewAction() {
        $params = $this->params()->fromRoute();
        if (empty($params['id'])) {
            $this->redirect()->toRoute('backend', array('controller' => 'user', 'action' => 'index'));
        }
        $intUserID = (int) $params['id'];
        $arrCondition = array('user_id' => $intUserID);
        $serviceUser = $this->serviceLocator->get('My\Models\User');
        $arrUser = $serviceUser->getDetail($arrCondition);
        if (empty($arrUser)) {
            $this->redirect()->toRoute('backend', array('controller' => 'user', 'action' => 'index'));
        }
        $serviceGroup = $this->serviceLocator->get('My\Models\Group');
        $arrGroup = $serviceGroup->getDetail(['group_id' => $arrUser['group_id']]);
        return array(
            'arrUser' => $arrUser,
            'arrGroup' => $arrGroup
        );
    }

    public function deleteAction() {
        $arrParamsRoute = $this->params()->fromRoute();

        if ($this->request->isPost()) {

            $params = $this->params()->fromPost();
            if (empty($params['userId'])) {
                return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => 'Xảy ra lỗi trong quá trình xử lý, Vui lòng refresh trình duyệt và thử lại!')));
            }

            $userId = (int) $params['userId'];
            $serviceUser = $this->serviceLocator->get('My\Models\User');

            //Kiểm tra sự tồn tại của user
            $arrConditionUser = array(
                'not_user_status' => -1,
                'user_id' => $userId
            );
            $arrUser = $serviceUser->getDetail($arrConditionUser);

            if (empty($arrUser)) {
                return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => 'Xảy ra lỗi trong quá trình xử lý, Vui lòng refresh trình duyệt và thử lại!')));
            }

            $result = $serviceUser->edit(array('user_status' => -1), $userId);

            if ($result) {
                $serviceLogs = $this->serviceLocator->get('My\Models\Logs');
                $arrLog = General::createLogs($arrParamsRoute, ['user_status' => -1], $userId);
                $serviceLogs->add($arrLog);

                return $this->getResponse()->setContent(json_encode(array('st' => 1, 'ms' => 'Xóa người dùng thành công!')));
            }
        }

        return $this->getResponse()->setContent(array('st' => -1, 'ms' => 'Xảy ra lỗi trong quá trình xử lý, Vui lòng refresh trình duyệt và thử lại!'));
    }

}
