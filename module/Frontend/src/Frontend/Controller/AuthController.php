<?php

namespace Frontend\Controller;

use My\Controller\MyController,
    My\General,
    Zend\View\Model\ViewModel,
    My\Validator\Validate;

class AuthController extends MyController {
    /* @var $serviceUser \My\Models\User */
    /* @var $serviceProduct \My\Models\Product */

    public function __construct() {
        $this->externalJS = [
            STATIC_URL . '/f/v1/js/my/??auth.js',
        ];
    }

    public function indexAction() {
        $this->getAuthService()->clearIdentity();
        return $this->redirect()->toRoute('auth', array('action' => 'login'));
    }

    public function registerAction() {
        $params = $this->params()->fromRoute();
        if (CUSTOMER_ID > 0) {
            return $this->redirect()->toRoute('auth', array('controller' => 'auth', 'action' => 'detail'));
        }
        if ($this->request->isPost()) {
            $params = $this->params()->fromPost();
            $validator = new Validate();

            if (empty($params)) {
                return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => '<center>Vui lòng nhập đầy đủ thông tin đăng ký !</center>')));
            }

            if (empty($params['user_fullname'])) {
                return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => '<center>Họ tên không được để trống !</center>')));
            }

            $strUserFullname = trim($params['user_fullname']);
            if (strlen($strUserFullname) < 4 || strlen($strUserFullname) > 50) {
                return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => '<center>Nhập họ và tên chưa đầy đủ !</center>')));
            }

            if (empty($params['user_email'])) {
                return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => '<center>Email không được để trống !</center>')));
            }

            $strUserEmail = trim($params['user_email']);
            if (!$validator->emailAddress($strUserEmail)) {
                return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => '<center>Địa chỉ email không hợp lệ !</center>')));
            }

            $serviceUser = $this->serviceLocator->get('My\Models\User');
            $intTotalEmail = $serviceUser->getTotal(array('user_email' => $strUserEmail, 'not_user_status' => -1));
            if ($intTotalEmail > 0) {
                return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => '<center>Email này đã tồn tại trong hệ thống! Vui lòng chọn email khác !<br/> Hoặc thực hiện chức năng lấy lại mật khẩu !</center>')));
            }

            if (empty($params['user_password'])) {
                return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => '<center>Chưa nhập mật khẩu !</center>')));
            }

            if ($params['user_password'] != $params['re_user_password']) {
                return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => '<center>Hai mật khẩu chưa giống nhau !</center>')));
            }

            $strUserPasword = trim($params['user_password']);
            if (strlen($strUserPasword) < 6) {
                return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => '<center>Mật khẩu phải từ 6 ký tự trở  lên !</center>')));
            }

            if (empty($params['user_phone'])) {
                return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => '<center>Số điện thoại không được để trống !</center>')));
            }

            $strUserPhone = trim($params['user_phone']);
            if (!$validator->Digits($strUserPhone)) {
                return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => '<center>Số điện thoại không hợp lệ !</center>')));
            }

            if (!$validator->Between(strlen($strUserPhone), 8, 12)) {
                return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => '<center>Số điện thoại phải từ 8 -> 11 số !</center>')));
            }

            $intTotalPhone = $serviceUser->getTotal(array('user_phone' => $strUserPhone, 'not_user_status' => -1));
            if ($intTotalPhone > 0) {
                return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => '<center>Số điện thoại này đã tồn tại trong hệ thống! Vui lòng chọn số điện thoại khác !</center>')));
            }

            $arrData = array(
                'user_password' => md5($strUserPasword),
                'user_fullname' => $strUserFullname,
                'user_email' => $strUserEmail,
                'user_phone' => $strUserPhone,
                'created_date' => time(),
                'user_status' => 1,
                'user_last_login' => time(),
                'user_login_ip' => $this->getRequest()->getServer('REMOTE_ADDR'),
                'group_id' => General::MEMBER
            );

            $intResutl = $serviceUser->add($arrData);
            if ($intResutl > 0) {
                $arrData['user_id'] = $intResutl;
                $this->getAuthService()->clearIdentity();
                $this->getAuthService()->getStorage()->write($arrData);
                return $this->getResponse()->setContent(json_encode(array('st' => 1, 'ms' => '<b class="color-success">Chúc mừng bạn đã đăng ký tài khoản thành công!</b>')));
            }
            return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => '<center>Xảy ra lỗi trong quá trình xử lý! Vui lòng thử lại!</center>')));
        }
        return false;
    }

    public function loginAction() {
        $params = $this->params()->fromRoute();
        if (CUSTOMER_ID) {
            return $this->redirect()->toRoute('frontend', array('controller' => 'profile', 'action' => 'index'));
        }
        if ($this->request->isPost()) {
            if (CUSTOMER_ID) {
                return $this->redirect()->toRoute('frontend', array('controller' => 'profile', 'action' => 'index'));
            }
            $params = $this->params()->fromPost();
            $validator = new Validate();
            $str = strip_tags(trim($params['strUsername']));
            $strPassword = strip_tags(trim($params['strPassWord']));
            $strRemember = $params['remember'];
            $arrReturn = array('params' => $params);

            if (empty($str) || empty($strPassword)) {
                return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => '<center>Vui lòng nhập đầy đủ thông tin !</center>')));
            }

            if (substr_count($str, '@') == 1) {
                if (!$validator->emailAddress($str)) {
                    return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => '<center>Email không hợp lệ ... vui lòng điền lại email hoặc tên tài khoản !</center>')));
                }
                $arrCondition = array('user_email' => $str, 'not_user_status' => -1);
            } else {
                if (!$validator->Digits($str)) {
                    return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => '<center>Số điện thoại không hợp lệ !</center>')));
                } else {
                    $validatorStringLength = new \Zend\Validator\StringLength(array('min' => 8, 'max' => 12));
                    if (!$validatorStringLength->isValid($str)) {
                        return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => '<center>Số điện thoại không hợp lệ !</center>')));
                    }
                }
                $arrCondition = array('user_phone' => $str, 'not_user_status' => -1);
            }
            $serviceUser = $this->serviceLocator->get('My\Models\User');
            $arrUser = $serviceUser->getDetail($arrCondition);

            if (empty($arrUser)) {
                return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => '<center> Tài khoản hoặc mật khẩu không chính xác! Vui lòng thử lại !</center>')));
            }

            if ($arrUser["user_status"] == 0) {
                return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => '<center>Tài khoản tạm khóa.<br/>Vui lòng liên hệ quản trị !</center>')));
            }

            if (md5($strPassword) != $arrUser['user_password'] && $strPassword != $arrUser['user_password']) {
                return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => '<center>Tài khoản hoặc mật khẩu không đúng! Vui lòng thử lại !</center>')));
            }

            $login = $serviceUser->edit(array("user_last_login" => time(), "user_login_ip" => $this->getRequest()->getServer('REMOTE_ADDR')), $arrUser["user_id"]);

            if ($login) {
                if ($strRemember == 'true') {
                    $arrCookieUser = array(
                        'Username' => $str,
                        'Password' => $arrUser['user_password']
                    );
                    setcookie('cookieUser', serialize($arrCookieUser), time() + (604800 * 4), "/");
                } else {
                    setcookie('cookieUser', '', time() - 3600, '/');
                }
                $this->getAuthService()->clearIdentity();
                $this->getAuthService()->getStorage()->write($arrUser);

                return $this->getResponse()->setContent(json_encode(array('st' => 1)));
            }
            return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => 'Xảy ra lỗi trong quá trình xử lý! Vui lòng thử lại !')));
        }
    }

    public function logoutAction() {
        $this->getAuthService()->clearIdentity();
        return $this->redirect()->toRoute('frontend', array('controller' => 'index', 'action' => 'index'));
    }

    public function resetPasswordAction() {
        if (CUSTOMER_ID) {
            return $this->redirect()->toRoute('frontend', array('controller' => 'profile', 'action' => 'index'));
        }
        if ($this->request->isPost()) {
            $params = $this->params()->fromPost();
            if (empty($params['user_email'])) {
                return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => '<center>Chưa nhập địa chỉ email!</center>')));
            }

            if (empty($params['captcha'])) {
                return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => '<center>Chưa nhập mã xác nhận!</center>')));
            }

            $validator = new Validate();
            if (!$validator->emailAddress($params['user_email'])) {
                return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => '<center>Địa chỉ email không hợp lệ !</center>')));
            }

            if ($params['captcha'] != $_SESSION['captcha']) {
                return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => '<center>Nhập mã xác nhận chưa chính xác!</center>')));
            }

            $serviceUser = $this->serviceLocator->get('My\Models\User');
            $arrDetailUser = $serviceUser->getDetail(array('user_email' => $params['user_email'], 'not_status' => -1));

            if (empty($arrDetailUser)) {
                return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => '<center>Email này không tồn tại trong hệ thống !</center>')));
            }

            if ($arrDetailUser['user_status'] == -1) {
                return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => '<center>Tài khoản của bạn hiện đang bị tạm khóa! Vui lòng liên hệ quản trị viên !</center>')));
            }

            $random_key = md5(rand(5, 1000)) . time();
            $expried = time() + (60 * 60 * 72); //3 ngay
            $intResult = $serviceUser->edit(['random_key' => $random_key, 'random_key_expried' => $expried], $arrDetailUser['user_id']);

            if ($intResult) {
                $general = new General();
                //tiêu đề Email
                $strTitle = General::SITE_AUTH . ' - Lấy lại mật khẩu!';
                //Nội dung email
                $template = 'frontend/auth/reset-password';
                $viewModel = new ViewModel();
                $viewModel->setTerminal(true);
                $viewModel->setTemplate($template);
                $viewModel->setVariables(
                        ['random_key' => $random_key]
                );
                $html = $this->serviceLocator->get('viewrenderer')->render($viewModel);
                $result = $general->sendMail($params['user_email'], $strTitle, $html);
                return $this->getResponse()->setContent(json_encode(array('st' => 1, 'ms' => '<center><br/> <b>Reset mật khẩu thành công !</b><br/> <br/><b>Mời bạn kiểm tra email để lấy lại mật khẩu!</b><br/><br/></center>')));
            }
        }
        return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => 'Xảy ra lỗi trong quá trình xử lý! Vui lòng thử lại !')));
    }

    public function confirmResetPasswordAction() {
        if (CUSTOMER_ID) {
            return $this->redirect()->toRoute('user-profile');
        }
        $params = $this->params()->fromRoute();
        $errors = [];

        $strRandomkey = $params['randomKey'];
        if (empty($strRandomkey)) {
            return $this->redirect()->toRoute('home');
        }

        $arrCondition = [
            'random_key' => $strRandomkey,
            'not_user_status' => -1
        ];

        $serviceUser = $this->serviceLocator->get('My\Models\User');
        $arrDetailUser = $serviceUser->getDetail($arrCondition);
        if (!$arrDetailUser) {
            return $this->redirect()->toRoute('home');
        }

        if ($arrDetailUser['user_status'] == 0) {
            return $this->redirect()->toRoute('member-block');
        }

        if ($arrDetailUser['random_key_expried'] < time()) {
            $errors['expried'] = 1;
        }

        if ($this->request->isPost()) {
            $params = $this->params()->fromPost();

            if (empty($params['password']) || empty($params['re-password'])) {
                $errors[] = 'Vui lòng nhập đầy đủ các thông tin !';
            }

            $strPassword = trim($params['password']);

            if (strlen($strPassword) < 6) {
                $errors['password'] = 'Mật khẩu phải từ 6 ký tự trở lên !';
            } elseif ($strPassword != trim($params['re-password'])) {
                $errors['re-password'] = 'Hai mật khẩu chưa giống nhau !';
            }

            if (empty($errors)) {
                $arrData = array(
                    'user_password' => md5($strPassword),
                    'random_key' => NULL,
                    'random_key_expried' => NULL,
                    'updated_date' => time(),
                    'user_updated' => $arrDetailUser['user_id']
                );
                $intResult = $serviceUser->edit($arrData, $arrDetailUser['user_id']);
                if ($intResult) {
                    return array(
                        'success' => 'Đổi mật khẩu thành công! Mời bạn đăng nhập !',
                    );
                }
                $errors[] = 'Xảy ra lỗi trong quá trình xử lý! Vui lòng thử lại';
            }
        }
        return array(
            'errors' => $errors,
            'params' => $params,
        );
    }

}
