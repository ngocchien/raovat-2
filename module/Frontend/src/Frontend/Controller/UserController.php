<?php

namespace Frontend\Controller;

use My\Controller\MyController,
    My\General,
    Zend\View\Model\ViewModel,
    Zend\Session\Container,
    My\Validator\Validate;

class UserController extends MyController {
    /* @var $serviceUser \My\Models\User */
    /* @var $serviceProduct \My\Models\Product */

    public function __construct() {
        $this->externalJS = [
            STATIC_URL . '/f/v1/js/my/??user.js',
        ];
    }

    /*
     * @auth : ChienNguyen => ngocchien01@gmail.com
     * Thông tin cá nhân
     */

    public function indexAction() {
        if (CUSTOMER_ID == 0) {
            return $this->redirect()->toRoute('user-login');
        }

        $params = $this->params()->fromRoute();

        $instanceSearchUser = new \My\Search\User();
        $arrDetailUser = $instanceSearchUser->getDetail(array('user_id' => CUSTOMER_ID));

        if ($this->request->isPost()) {
            $params = $this->params()->fromPost();
            if (empty($params['user_fullname'])) {
                $errors['user_fullname'] = 'Họ tên không được bỏ trống!';
            } else {
                $validator = new \Zend\Validator\StringLength(array('min' => 5));
                if (!$validator->isValid($params['user_fullname'])) {
                    $errors['user_fullname'] = 'Nhập họ và tên không hợp lệ!';
                }
            }
            if (empty($params['user_email'])) {
                $errors['user_email'] = 'Email không được bỏ trống!';
            } else {
                $validator = new \Zend\Validator\EmailAddress();
                if (!$validator->isValid($params['user_email'])) {
                    $errors['user_email'] = 'Địa chỉ email không hợp lệ!';
                }
            }

            if (empty($params['user_phone'])) {
                $errors['user_phone'] = 'Số di động không được bỏ trống!';
            } else {
                $validator = new \Zend\Validator\Digits();
                if (!$validator->isValid($params['user_phone'])) {
                    $errors['user_phone'] = 'Số di động không không hợp lệ!';
                } else {
                    $validator = new \Zend\Validator\StringLength(array('min' => 5, 'max' => 12));
                    if (!$validator->isValid($params['user_phone'])) {
                        $errors['user_phone'] = 'Số di động không không hợp lệ!';
                    }
                }
            }
            if (empty($errors)) {
                //Check Phone
                $arrPhone = $instanceSearchUser->getDetail(['user_phone' => $params['user_phone'], 'not_status' => -1, 'not_user_id' => CUSTOMER_ID]);
                if (!empty($arrPhone)) {
                    $errors['user_phone'] = 'Số di động này đã tồn tại trong hệ thống của chúng tôi!';
                } else {
                    //check email
                    $arrEmail = $instanceSearchUser->getDetail(['user_email' => $params['user_email'], 'not_status' => -1, 'not_user_id' => CUSTOMER_ID]);
                    if (!empty($arrEmail)) {
                        $errors['user_email'] = 'Địa chỉ email này đã tồn tại trong hệ thống của chúng tôi!';
                    }
                }
                if (empty($errors)) {
                    $arrData = [
                        'user_phone' => $params['user_phone'],
                        'user_email' => $params['user_email'],
                        'user_updated' => CUSTOMER_ID,
                        'updated_date' => time(),
                        'user_fullname' => $params['user_fullname']
                    ];

                    $serviceUser = $this->serviceLocator->get('My\Models\User');
                    $intResult = $serviceUser->edit($arrData, CUSTOMER_ID);
                    if ($intResult) {
                        //get lại thông tin User 
                        $arrUser = $serviceUser->getDetail(['user_id' => CUSTOMER_ID]);
                        $this->getAuthService()->clearIdentity();
                        $this->getAuthService()->getStorage()->write($arrUser);
                        //redirect
                        $this->flashMessenger()->setNamespace('edit-info-success')->addMessage('Cập nhật thông tin cá nhân thành công !');
                        return $this->redirect()->toRoute('user-profile');
                    }
                    $errors[] = 'Xảy ra lỗi trong quá trình xử lý! Vui lòng thử lại sau giây lát!';
                }
            }
        }

        $this->renderer = $this->serviceLocator->get('Zend\View\Renderer\PhpRenderer');
        $this->renderer->headTitle(html_entity_decode('Tài khoản - Thông tin tài khoản') . General::TITLE_META);
        $this->renderer->headMeta()->appendName('keywords', html_entity_decode('chototquynhon.com, tài khoản, Thông tin, Thông tin tài khoản, Thông tin tài khoản chototquynhon.com'));
        $this->renderer->headMeta()->appendName('description', html_entity_decode('Tài khoản - Thông tin tài khoản tại' . General::TITLE_META));

        return array(
            'errors' => $errors,
            'params' => $params,
            'arrDetailUser' => $arrDetailUser,
        );
    }

    public function listPostAction() {
        if (!CUSTOMER_ID) {
            return $this->redirect()->toRoute('home');
        }
        $params = $this->params()->fromRoute();
        $intLimit = 15;
        $intPage = (int) $params['page'] > 0 ? (int) $params['page'] : 1;

        $arrCondition = [
            'user_created' => CUSTOMER_ID,
            'not_cont_status' => -1
        ];

        //content sẽ get từ elasticsearch
        $instanceSearchContent = new \My\Search\Content();
        $arrContentList = $instanceSearchContent->getListLimit($arrCondition, $intPage, $intLimit, ['created_date' => ['order' => 'desc']]);
        $intTotal = $instanceSearchContent->getTotal($arrCondition);
        $helper = $this->serviceLocator->get('viewhelpermanager')->get('Paging');
        $paging = $helper($params['module'], $params['__CONTROLLER__'], $params['action'], $intTotal, $intPage, $intLimit, 'user-list-post', $params);

        $this->renderer = $this->serviceLocator->get('Zend\View\Renderer\PhpRenderer');
        $this->renderer->headTitle(html_entity_decode('Tài khoản - Thông tin tài khoản') . General::TITLE_META);
        $this->renderer->headMeta()->appendName('keywords', html_entity_decode('chototquynhon.com, tài khoản, Thông tin, Thông tin tài khoản, Thông tin tài khoản chototquynhon.com'));
        $this->renderer->headMeta()->appendName('description', html_entity_decode('Tài khoản - Thông tin tài khoản tại' . General::TITLE_META));
        return array(
            'arrContentList' => $arrContentList,
            'params' => $params,
            'paging' => $paging
        );
    }

    public function rechargeAction() {
        if ($this->request->isPost()) {
            $params = $this->params()->fromPost();

            if (empty($params['type'])) {
                $errors['type'] = 'Bạn chưa chọn loại thẻ nạp !';
            } else {
                $type = (int) $params['type'];
                $arrCharge = General::getMethodRechargeId();
                if (!in_array($type, $arrCharge)) {
                    $errors['type'] = 'Loại thẻ nạp không hợp lệ !';
                }
            }


            if (empty($params['seri'])) {
                $errors['seri'] = 'Bạn chưa nhập số seri của thẻ nạp !';
            }

            if (empty($params['code'])) {
                $errors['code'] = 'Bạn chưa nhập mã thẻ nạp !';
            }

            if (empty($params['code_security'])) {
                $errors['code_security'] = 'Bạn chưa nhập mã bảo mật !';
            } else {
                if ($params['code_security'] != $_SESSION['captcha']) {
                    $errors['code_security'] = 'Nhập mã xác nhận chưa chính xác!';
                }
            }

            if (empty($errors)) {
                $gamebankConfig = General::infoRechargeGameBank();
                $gb_api = new \My\Recharge\GameBank\GameBank();
                $gb_api->setMerchantId($gamebankConfig['merchant_id']);
                $gb_api->setApiUser($gamebankConfig['api_user']);
                $gb_api->setApiPassword($gamebankConfig['api_password']);
                $gb_api->setPin(trim($params['code']));
                $gb_api->setSeri(trim($params['seri']));
                $gb_api->setCardType($type);
                $gb_api->setNote('user_id = ' . CUSTOMER_ID); // ghi chu giao dich ben ban tu sinh
                $gb_api->cardCharging();
                $code = intval($gb_api->getCode());
                $info_card = intval($gb_api->getInfoCard());
                if ($code === 0 && $info_card >= 10000) {
                    //tăng số tiền trong tài khoản lên
                    $balance = CUSTOMER_BALANCE + $info_card;
                    $arrUserUpdate = [
                        'user_balance' => $balance
                    ];
                    $serviceUser = $this->serviceLocator->get('My\Models\User');
                    $intResult = $serviceUser->edit($arrUserUpdate, CUSTOMER_ID);
                    if ($intResult) {
                        //lưu lại lịch sử nạp tiền
                        $arrData = [
                            'user_id' => CUSTOMER_ID,
                            'created_date' => time(),
                            'tran_type' => General::TRANS_INPUT,
                            'soucre_id' => $type,
                            'tran_deal' => $info_card,
                            'user_blance' => $balance,
                        ];
                        $serviceTrans = $this->serviceLocator->get('My\Models\TransactionHistory');
                        $serviceTrans->add($arrData);

                        //set lại session
                        $arrUser = [
                            'user_id' => CUSTOMER_ID,
                            'user_fullname' => CUSTOMER_FULLNAME,
                            'user_email' => CUSTOMER_EMAIL,
                            'user_phone' => CUSTOMER_PHONE,
                            'user_balance' => $balance
                        ];

                        $this->getAuthService()->clearIdentity();
                        $this->getAuthService()->getStorage()->write($arrUser);

                        //redirect
                        $this->flashMessenger()->setNamespace('recharge-success')->addMessage('Bạn đã nạp thành công ' . number_format($info_card, 0, ",", ".") . ' vnđ vào tài khoản!<br/>Số dư hiện tại của bạn là :' . number_format($balance, 0, ",", ".") . ' vnđ');
                        return $this->redirect()->toRoute('user-recharge');
                    } else {
                        //xảy ra lỗi trong quá trình xử lý!
                        //save log vào server
                        $str = '||' . date('h:i:s d/m/Y') . ' lỗi nạp thẻ từ user id = ' . CUSTOMER_ID . ' loại thẻ : ' . $type . ' mệnh giá =' . $info_card;
                        $filename = WEB_ROOT . '/data/json_log_recharge.txt';
                        file_put_contents($filename, json_encode($str), FILE_APPEND);
                        $errors[] = 'Xảy ra lỗi trong quá trình xử lý! Bạn vui lòng liên hệ quản trị viên để được hỗ trợ! Chân thành xin lỗi!';
                    }
                } else {
                    // get thong bao loi
                    $errors[] = $gb_api->getMsg();
                }
            }
        }
        $this->renderer = $this->serviceLocator->get('Zend\View\Renderer\PhpRenderer');
        $this->renderer->headTitle(html_entity_decode('Tài khoản - Nạp tiền tài khoản') . General::TITLE_META);
        $this->renderer->headMeta()->appendName('keywords', html_entity_decode('chototquynhon.com, tài khoản, Thông tin, Thông tin tài khoản, Thông tin tài khoản chototquynhon.com'));
        $this->renderer->headMeta()->appendName('description', html_entity_decode('Tài khoản - Thông tin tài khoản tại' . General::TITLE_META));

        return [
            'params' => $params,
            'errors' => $errors
        ];
    }

    public function dealHistoryAction() {
        if (!CUSTOMER_ID) {
            return $this->redirect()->toRoute('home');
        }
        $params = $this->params()->fromRoute();
        $intLimit = 15;
        $intPage = (int) $params['page'] > 0 ? (int) $params['page'] : 1;

        $arrCondition = [
            'user_id' => CUSTOMER_ID,
        ];

        $instanceSearchTran = new \My\Search\TransactionHistory();
        $arrTranList = $instanceSearchTran->getListLimit($arrCondition, $intPage, $intLimit, ['created_date' => ['order' => 'desc']]);
        $intTotal = $instanceSearchTran->getTotal($arrCondition);
        $helper = $this->serviceLocator->get('viewhelpermanager')->get('Paging');
        $paging = $helper($params['module'], $params['__CONTROLLER__'], $params['action'], $intTotal, $intPage, $intLimit, 'user-list-post', $params);
        
        return [
            'arrTranList' => $arrTranList,
            'paging'=>$paging
        ];
    }

    public function blockAction() {
        
    }

    public function changePasswordAction() {
        if (!CUSTOMER_ID) {
            return $this->redirect()->toRoute('home');
        }
        $params = $this->params()->fromRoute();

        $errors = array();
        if ($this->request->isPost()) {
            $params = $this->params()->fromPost();

            if (empty($params['old_password'])) {
                $errors['old_password'] = 'Vui lòng nhập mật khẩu hiện tại !';
            }

            if (empty($params['new_password'])) {
                $errors['new_password'] = 'Vui lòng nhập mật khẩu mới !';
            } else {
                if (strlen($params['new_password']) < 6) {
                    $errors['new_password'] = 'Mật khẩu mới phải từ 6 ký tự trở lên !';
                } else {
                    if ($params['new_password'] != $params['re_new_password']) {
                        $errors['new_password'] = 'Nhập lại mật khẩu mới chưa chính xác!';
                    }
                }
            }
            if (empty($errors)) {
                //check old pass
                $serviceUser = $this->serviceLocator->get('My\Models\User');
                $arrDetailUser = $serviceUser->getDetail(array('user_id' => CUSTOMER_ID));

                if (md5($params['old_password']) != $arrDetailUser['user_password']) {
                    $errors['old_password'] = 'Nhập mật khẩu cũ chưa chính xác!';
                }

                if (empty($errors)) {
                    $arrData = array(
                        'user_password' => md5($params['new_password']),
                        'updated_date' => time(),
                        'user_updated' => CUSTOMER_ID
                    );

                    $inResult = $serviceUser->edit($arrData, CUSTOMER_ID);
                    if ($inResult) {
                        $this->flashMessenger()->setNamespace('change-pass-success')->addMessage('Cập nhật mật khẩu thành công !');
                        return $this->redirect()->toRoute('user-change-password');
                    }
                    $errors[] = 'Xảy ra lỗi trong quá trình xử lý ! Vui lòng thử lại !';
                }
            }
        }
        $this->renderer = $this->serviceLocator->get('Zend\View\Renderer\PhpRenderer');
        $this->renderer->headTitle(html_entity_decode('Thành viên - Đổi mật khẩu tài khoản ') . General::TITLE_META);
        $this->renderer->headMeta()->appendName('description', html_entity_decode('Chototquynhon.Com - Đổi mật khẩu tài khoản !'));
        return array(
            'params' => $params,
            'errors' => $errors
        );
    }

    public function changeAvatarAction() {
        if (!CUSTOMER_ID) {
            return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => '<p style="color:red">Chưa đăng nhập!</b></p>')));
        }

        if ($this->request->isPost()) {
            $files = $this->params()->fromFiles();
            $user_avatar = General::ImageUpload($files['file-0'], 'user');

            if (empty($user_avatar)) {
                return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => '<br>Hình up lên phải là định dạng hình ảnh (.jpg, .png , ...) ! Và dung lượng không được quá 2MB !</br>')));
            }

            //save lại cho user
            $serviceUser = $this->serviceLocator->get('My\Models\User');
            $arrData = array(
                'user_avatar' => json_encode($user_avatar),
                'updated_date' => time(),
            );

            $intResult = $serviceUser->edit($arrData, CUSTOMER_ID);
            if ($intResult) {
                return $this->getResponse()->setContent(json_encode(array('st' => 1, 'images' => $user_avatar[0]['thumbImage']['150x150'])));
            }
        }

        return $this->getResponse()->setContent(json_encode(array('st' => -1, 'images' => 'Xảy ra lỗi trong quá trình xử lý! Xin vui lòng thử lại sau giây lát!')));
    }

    public function socialAction() {
        if (CUSTOMER_ID) {
            return $this->redirect()->toRoute('user-profile');
        }
        $paramsRoute = $this->params()->fromRoute();
        $type = $paramsRoute['type'];

        if (empty($type)) {
            return $this->redirect()->toRoute('home');
        }

        $paramsQuery = $this->params()->fromQuery();

        $this->renderer = $this->serviceLocator->get('Zend\View\Renderer\PhpRenderer');
        $this->renderer->headMeta()->appendName('dc.description', html_entity_decode('Đăng nhập website với mạng xã hội!') . General::TITLE_META);
        $this->renderer->headMeta()->appendName('dc.subject', html_entity_decode('Đăng nhập website với mạng xã hội!') . General::TITLE_META);
        $this->renderer->headTitle('Đăng nhập website bằng mạng xã hội!' . General::TITLE_META);
        $this->renderer->headMeta()->appendName('keywords', html_entity_decode('Đăng nhập website với mạng xã hội!'));
        $this->renderer->headMeta()->appendName('description', html_entity_decode('Đăng nhập website với mạng xã hội!'));
        $this->renderer->headMeta()->setProperty('og:title', html_entity_decode('Đăng nhập website với mạng xã hội!'));
        $this->renderer->headMeta()->setProperty('og:description', html_entity_decode('Đăng nhập website với mạng xã hội!'));

        $instanceSearchUser = new \My\Search\User();
        $serviceUser = $this->serviceLocator->get('My\Models\User');

        if ($this->request->isGet()) {
            $code = $paramsQuery['code'];
            if (empty($code)) {
                return $this->redirect()->toRoute('home');
            }
            $completeSession = new Container('authTemp');
            if ($type == 'google') {
                if ($completeSession->ref == 'google.com') {
                    return [
                        'completeSession' => $completeSession
                    ];
                }
                try {
                    /*
                     * Service Google
                     */
                    $googleClient = new \Google_Client();
                    $gpInfo = General::$ggConfig;
                    $googleClient->setClientId($gpInfo['client_id']);
                    $googleClient->setClientSecret($gpInfo['client_secret']);
                    $googleClient->addScope('email');
                    $googleClient->addScope('profile');
                    $googleClient->setRedirectUri($gpInfo['redirect_uri']);
                    $googleClient->authenticate($code);

                    $accessToken = json_decode($googleClient->getAccessToken(), true);
                    $urlGoogleGet = 'https://www.googleapis.com/oauth2/v1/userinfo?access_token=' . $accessToken['access_token'];
                    $fileContent = json_decode(file_get_contents($urlGoogleGet), true);
                    $email = $fileContent['email'];

                    if ($fileContent['id'] && $fileContent['email'] && $fileContent['name']) {
                        /*
                         * Kiểm tra người dùng đã tồn tại trong hệ thống hay chưa, nếu đã tồn tại thì cho login thành công
                         */
                        $userInfo = $instanceSearchUser->getDetail(['user_email' => $fileContent['email'], 'not_status' => -1]);
                        if ($userInfo) {
                            if ($userInfo['user_status'] == 0) {
                                return $this->redirect()->toRoute('member-block');
                            }

                            $arrUpdate = [
                                'user_last_login' => time(),
                                'user_login_ip' => $this->getRequest()->getServer('REMOTE_ADDR'),
                            ];
                            if (empty($userInfo['social_profile_url'])) {
                                $arrUpdate['social_profile_url'] = $fileContent['link'];
                            }
                            $login = $serviceUser->edit($arrUpdate, $userInfo["user_id"]);
                            if ($login) {
                                /*
                                 * set session
                                 */
                                $this->getAuthService()->clearIdentity();
                                $this->getAuthService()->getStorage()->write($userInfo);
                                return $this->redirect()->toRoute('user-profile');
                            }
                        }

                        /*
                         * Nếu chưa có thông tin trong hệ thống thì set session để Nhập tên cá nhân(Doanh Nghiệp) và mật khẩu
                         */
                        $completeSession = new Container('authTemp');

                        $completeSession->name = $fileContent['name'];
                        $completeSession->linkProfile = $fileContent['link'];
                        $completeSession->avatar = $fileContent['picture'];
                        $completeSession->ref = 'google.com';
                        $completeSession->email = $fileContent['email'];
                    }
                } catch (\Exception $exc) {
//                    echo '<pre>';
//                    print_r($exc);
//                    die;
//                    echo '</pre>';
//                    die();
                    /*
                     * return to page register
                     */
                    $_SESSION['error_social'] = 'google';
                    return $this->redirect()->toRoute('frontend', ['controller' => 'user', 'action' => 'error-social']);
                }
            }

            if ($type == 'facebook') {
                if ($completeSession->ref == 'facebook.com') {
                    return [
                        'completeSession' => $completeSession
                    ];
                }
                $state = $paramsQuery['state'];

                /*
                 * load config
                 */
                $fbInfo = General::$fbConfig;
                $facebookClient = new \Facebook\Facebook([
                    'app_id' => $fbInfo['appId'],
                    'app_secret' => $fbInfo['secret'],
                    'default_graph_version' => 'v2.5'
                ]);
                $helper = $facebookClient->getRedirectLoginHelper();

                try {
                    $accessToken = $helper->getAccessToken();
                } catch (\Facebook\Exceptions\FacebookResponseException $e) {
                    // When Graph returns an error
//                    echo 'Graph returned an error: ' . $e->getMessage();
//                    die();
                    /*
                     * catch return to login
                     */
                    $_SESSION['error_social'] = 'facebook';
                    return $this->redirect()->toRoute('frontend', ['controller' => 'user', 'action' => 'error-social']);
                } catch (\Facebook\Exceptions\FacebookSDKException $e) {
                    // When validation fails or other local issues
//                    echo 'Facebook SDK returned an error: ' . $e->getMessage();
//                    die();
                    /*
                     * catch return to register
                     */
                    $_SESSION['error_social'] = 'facebook';
                    return $this->redirect()->toRoute('frontend', ['controller' => 'user', 'action' => 'error-social']);
                }

                if (!isset($accessToken)) {
                    $_SESSION['error_social'] = 'facebook';
                    return $this->redirect()->toRoute('frontend', ['controller' => 'user', 'action' => 'error-social']);
                }

                try {
                    $response = $facebookClient->get('/me?' . $fbInfo['field_profile'], $accessToken);
                    $userInfoFacebook = $response->getGraphUser();
                } catch (\Exception $exc) {
//                    echo $exc->getMessage();
//                    die();
                    $_SESSION['error_social'] = 'facebook';
                    return $this->redirect()->toRoute('frontend', ['controller' => 'user', 'action' => 'error-social']);
                }

                if (!isset($userInfoFacebook)) {
                    $_SESSION['error_social'] = 'facebook';
                    return $this->redirect()->toRoute('frontend', ['controller' => 'user', 'action' => 'error-social']);
                }

                $userEmail = $userInfoFacebook['email'];

                if (empty($userEmail)) {
                    $_SESSION['error_social'] = 'facebook';
                    return $this->redirect()->toRoute('frontend', ['controller' => 'user', 'action' => 'error-social']);
                }

                /*
                 * Kiểm tra người dùng đã tồn tại trong hệ thống hay chưa, nếu đã tồn tại thì cho login thành công
                 */
                $userInfo = $instanceSearchUser->getDetail(['user_email' => $userEmail, 'not_status' => -1]);

                if ($userInfo) {
                    $arrUpdate = [
                        'user_last_login' => time(),
                        'user_login_ip' => $this->getRequest()->getServer('REMOTE_ADDR'),
                    ];
                    if (empty($userInfo['social_profile_url'])) {
                        $arrUpdate['social_profile_url'] = $userInfoFacebook['link'];
                    }
                    $login = $serviceUser->edit($arrUpdate, $userInfo["user_id"]);
                    if ($login) {
                        /*
                         * set session
                         */
                        $this->getAuthService()->clearIdentity();
                        $this->getAuthService()->getStorage()->write($userInfo);
                        return $this->redirect()->toRoute('user-profile');
                    }
                }
                /*
                 * Nếu chưa có thông tin trong hệ thống thì set session để Nhập tên cá nhân(Doanh Nghiệp) và mật khẩu
                 */
                $completeSession = new Container('authTemp');
                $completeSession->name = $userInfoFacebook['name'];
                $completeSession->linkProfile = $userInfoFacebook['link'];
                $completeSession->avatar = $userInfoFacebook['picture']['url'];
                $completeSession->ref = 'facebook.com';
                $completeSession->email = $userInfoFacebook['email'];
            }
        }

        if ($this->request->isPost()) {

            $arrParams = $this->params()->fromPost();

            if (empty($arrParams['fullname'])) {
                $errors['fullname'] = 'Họ và tên không được bỏ trống!';
            } else {
                if (strlen($arrParams['fullname']) < 5) {
                    $errors['fullname'] = 'Nhập họ và tên chưa chính xác!';
                }
            }

            if (empty($arrParams['phone'])) {
                $errors['phone'] = 'Số điện thoại không được bỏ trống!';
            } else {
                $validator = new \Zend\Validator\Digits();
                if (!$validator->isValid($arrParams['phone'])) {
                    $errors['phone'] = 'Số di động không không hợp lệ!';
                } else {
                    $validator = new \Zend\Validator\StringLength(array('min' => 8, 'max' => 12));
                    if (!$validator->isValid($arrParams['phone'])) {
                        $errors['phone'] = 'Số di động không không hợp lệ!';
                    }
                }
            }

            if (empty($arrParams['password'])) {
                $errors['password'] = 'Vui lòng nhập mật khẩu!';
            } else {
                if (strlen($arrParams['password']) < 6) {
                    $errors['password'] = 'Mật khẩu phải từ 6 ký tự trở lên !';
                }
            }

            if (empty($errors)) {
                //Check Phone
                $arrPhone = $serviceUser->getDetail(['user_phone' => $arrParams['phone'], 'not_user_status' => -1]);
                if (!empty($arrPhone)) {
                    $errors['phone'] = 'Số di động này đã tồn tại trong hệ thống của chúng tôi! Vui lòng  chọn số điện thoại khác';
                }

                if (empty($errors)) {
                    $completeSession = new Container('authTemp');
                    $arrData = [
                        'user_email' => $completeSession->email,
                        'user_phone' => $arrParams['phone'],
                        'user_status' => 1,
                        'user_fullname' => $arrParams['fullname'],
                        'user_password' => md5($arrParams['password']),
                        'created_date' => time(),
                        'user_login_ip' => $this->getRequest()->getServer('REMOTE_ADDR'),
                        'user_last_login' => time(),
                        'social_profile_url' => $completeSession->ref,
                        'user_avatar_social' => $completeSession->avatar
                    ];
                    $intResult = $serviceUser->add($arrData);
                    if ($intResult) {
                        $completeSession->getManager()->getStorage()->clear('authTemp');
                        $arrData['user_id'] = $intResult;
                        //return to reg success 
                        $this->getAuthService()->clearIdentity();
                        $this->getAuthService()->getStorage()->write($arrData);
                        return $this->redirect()->toRoute('user-profile');
                    }
                    $errors[] = 'Xảy ra lỗi trong quá trình xử lý! Vui lòng thử lại sau giây lát!';
                }
            }
        }

        return [
            'errors' => $errors,
            'completeSession' => $completeSession,
            'params' => $arrParams
        ];
    }

    public function errorSocialAction() {
        
    }

    public function listMessagesAction() {
        if (!CUSTOMER_ID) {
            return $this->redirect()->toRoute('home');
        }
        $params = $this->params()->fromRoute();
        $intLimit = 10;
        $intPage = (int) $params['page'] > 0 ? (int) $params['page'] : 1;
        $arrCondition = [
            'to_user_id' => CUSTOMER_ID,
            'not_is_view' => -1
        ];

        //content sẽ get từ elasticsearch
        $instanceSearchMessages = new \My\Search\Messages();
        $arrMessagesList = $instanceSearchMessages->getListLimit($arrCondition, $intPage, $intLimit, ['created_date' => 'desc']);
        $intTotal = $instanceSearchMessages->getTotal($arrCondition);
        $params = array_merge($params, $this->params()->fromQuery());
        $helper = $this->serviceLocator->get('viewhelpermanager')->get('Paging');

        $paging = $helper($params['module'], $params['__CONTROLLER__'], $params['action'], $intTotal, $intPage, $intLimit, 'user-list-messages', $params);

        $this->renderer = $this->serviceLocator->get('Zend\View\Renderer\PhpRenderer');
        $this->renderer->headTitle(html_entity_decode('Tài khoản - Danh sách tin nhắn') . General::TITLE_META);
        $this->renderer->headMeta()->appendName('keywords', html_entity_decode('chototquynhon.com, tài khoản, Thông tin, Thông tin tài khoản, danh sách tin nhắn'));
        $this->renderer->headMeta()->appendName('description', html_entity_decode('Tài khoản - Danh sách tin nhắn' . General::TITLE_META));

        //Lấy thông tin người gửi
        $arrUserIdList = [];
        if (!empty($arrMessagesList)) {
            foreach ($arrMessagesList as $arr) {
                $arrUserIdList[$arr['user_created']] = $arr['user_created'];
            }
        }
        $arrUserList = [];
        if (!empty($arrUserIdList)) {
            $instanceSearchUser = new \My\Search\User();
            $arrUserTemp = $instanceSearchUser->getList(['in_user_id' => $arrUserIdList]);
            if (!empty($arrUserTemp)) {
                foreach ($arrUserTemp as $user) {
                    $arrUserList[$user['user_id']] = $user;
                }
            }
        }

        return array(
            'arrMessagesList' => $arrMessagesList,
            'params' => $params,
            'paging' => $paging,
            'arrUserList' => $arrUserList
        );
    }

    public function getMessagesAction() {
        if ($this->request->isPost()) {
            if (!CUSTOMER_ID) {
                return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => '<p style="color:red">Chưa đăng nhập!</b></p>')));
            }

            $params = $this->params()->fromPost();

            if (empty($params['id'])) {
                return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => '<p style="color:red">Xảy ra lỗi trong quá trình xử lý! Vui lòng thử lại sau giây lát</b></p>')));
            }

            $instanceSearchMessages = new \My\Search\Messages();
            $arrMessges = $instanceSearchMessages->getDetail(['to_user_id' => CUSTOMER_ID, 'not_is_view' => -1, 'mess_id' => (int) $params['id']]);

            if (empty($arrMessges)) {
                return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => '<p style="color:red">Tin nhắn này không tồn tại trong hệ thống!</b></p>')));
            }

            $isAcitve = false;

            if ($arrMessges['is_view'] == 0) {
                $isAcitve = true;
                try {
                    $serviceMessages = $this->serviceLocator->get('My\Models\Messages');
                    $serviceMessages->edit(['is_view' => 1, 'updated_date' => time()], $arrMessges['mess_id']);
                } catch (\Exception $exc) {
                    echo $exc->getMessage();
                    die();
                }
            }

            $instanceSearchUser = new \My\Search\User();
            $arrUserInfo = $instanceSearchUser->getDetail(['user_id' => $arrMessges['user_created']]);

            $template = 'frontend/user/get-messages';
            $viewModel = new ViewModel();
            $viewModel->setTerminal(true);
            $viewModel->setTemplate($template);
            $viewModel->setVariables(
                    [
                        'mess_content' => $arrMessges['mess_content'],
                        'from_user_name' => $arrUserInfo['user_fullname'],
                        'from_user_email' => $arrUserInfo['user_email'],
                        'mess_id' => $params['id']
                    ]
            );
            $html = $this->serviceLocator->get('viewrenderer')->render($viewModel);

            return $this->getResponse()->setContent(json_encode(array('st' => 1, 'html' => $html, 'data' => ['from_user_name' => $arrUserInfo['user_fullname'], 'from_user_email' => $arrUserInfo['user_email'], 'mess_id' => $params['id'], 'is_active' => $isAcitve])));
        }
    }

    public function replayMessagesAction() {
        if ($this->request->isPost()) {
            if (!CUSTOMER_ID) {
                return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => '<p style="color:red">Chưa đăng nhập!</b></p>')));
            }

            $params = $this->params()->fromPost();

            if (empty($params['mess_content']) || empty($params['mess_title']) || empty($params['mess_id'])) {
                return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => '<p style="color:red">Xảy ra lỗi trong quá trình xử lý! Vui lòng thử lại sau giây lát</b></p>')));
            }

            $instanceSearchMessages = new \My\Search\Messages();
            $arrMessges = $instanceSearchMessages->getDetail(['to_user_id' => CUSTOMER_ID, 'not_is_view' => -1, 'mess_id' => (int) $params['mess_id']]);

            if (empty($arrMessges)) {
                return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => '<p style="color:red">Tin nhắn này không tồn tại trong hệ thống!</b></p>')));
            }

            $instanceSearchUser = new \My\Search\User();
            $arrUserInfo = $instanceSearchUser->getDetail(['user_id' => $arrMessges['user_created']]);

            $arrData = [
                'mess_title' => $params['mess_title'],
                'mess_content' => $params['mess_content'],
                'created_date' => time(),
                'user_created' => CUSTOMER_ID,
                'is_view' => 0,
                'to_user_id' => $arrUserInfo['user_id'],
                'cont_id' => (int) $arrMessges['cont_id'],
                'parent_id' => (int) $arrMessges['mess_id']
            ];

            $serviceMessages = $this->serviceLocator->get('My\Models\Messages');
            $intMessgesId = $serviceMessages->add($arrData);

            if (empty($intMessgesId)) {
                return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => '<p style="color:red">Xảy ra lỗi trong quá trình xử lý! Vui lòng thử lại sau giây lát</b></p>')));
            }

            $template = 'frontend/email-replay-messages';
            $viewModel = new ViewModel();
            $viewModel->setTerminal(true);
            $viewModel->setTemplate($template);
            $viewModel->setVariables(
                    [
                        'from_user_name' => CUSTOMER_FULLNAME,
                        'arrUserInfo' => $arrUserInfo
                    ]
            );
            $html = $this->serviceLocator->get('viewrenderer')->render($viewModel);

            $arrEmail = [
                'user_email' => $arrUserInfo['user_email'],
                'html' => $html,
                'title' => 'Nhận được tin nhắn mới từ ' . CUSTOMER_FULLNAME,
            ];

            $instanceJob = new \My\Job\JobMail();
            $instanceJob->addJob(SEARCH_PREFIX . 'sendMail', $arrEmail);

            return $this->getResponse()->setContent(json_encode(array('st' => 1, 'ms' => 'Gửi phản hồi tin nhắn thành công!')));
        }
        return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => 'Xảy ra lỗi trong quá trình xử lý! Vui lòng thử lại sau giây lát!')));
    }

    public function deleteMessagesAction() {
        if ($this->request->isPost()) {
            if (!CUSTOMER_ID) {
                return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => '<p style="color:red">Chưa đăng nhập!</b></p>')));
            }
            $params = $this->params()->fromPost();
            if (empty($params['arrItem']) || !is_array($params['arrItem'])) {
                return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => '<p style="color:red">Xảy ra lỗi trong quá trình xử lý!Vui lòng thử lại sau giây lát!</b></p>')));
            }
            $arrItem = array_unique($params['arrItem']);
            //find messges
            $instanceSearchMessages = new \My\Search\Messages();
            $arrList = $instanceSearchMessages->getList(['in_mess_id' => $arrItem, 'to_user_id' => CUSTOMER_ID, 'not_status' => -1]);

            $arrIdList = [];
            if (!empty($arrList)) {
                foreach ($arrList as $value) {
                    $arrIdList[] = $value['mess_id'];
                }
            }

            if (empty($arrIdList)) {
                return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => '<p style="color:red">Không tìm thấy các tin nhắn này trong hệ thống!</b></p>')));
            }

            $arrParams = [
                'updated_date' => time(),
                'is_view' => -1
            ];
            $serviceMessages = $this->serviceLocator->get('My\Models\Messages');
            $intResult = $serviceMessages->multiEdit($arrParams, ['in_mess_id' => implode(',', $arrIdList)]);

            if ($intResult <= 0) {
                return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => '<p style="color:red">Xảy ra lỗi trong quá trình xử lý!Vui lòng thử lại sau giây lát!</b></p>')));
            }

            return $this->getResponse()->setContent(json_encode(array('st' => 1, 'data' => $arrIdList, 'ms' => 'Xóa thành công ' . count($arrIdList) . ' tin nhắn!')));
        }
    }

    public function listSavePostAction() {
        if (!CUSTOMER_ID) {
            return $this->redirect()->toRoute('home');
        }
        $params = $this->params()->fromRoute();
        $intLimit = 10;
        $intPage = (int) $params['page'] > 0 ? (int) $params['page'] : 1;

        $arrCondition = [
            'user_id' => CUSTOMER_ID,
            'not_status' => -1
        ];

        $instanceSearchFavourite = new \My\Search\Favourite();
        $arrFavouriteList = $instanceSearchFavourite->getListLimit($arrCondition, $intPage, $intLimit, ['updated_date' => ['order' => 'desc']]);

        $intTotal = $instanceSearchFavourite->getTotal($arrCondition);
        $helper = $this->serviceLocator->get('viewhelpermanager')->get('Paging');
        $paging = $helper($params['module'], $params['__CONTROLLER__'], $params['action'], $intTotal, $intPage, $intLimit, 'user-list-save-post', $params);

        $this->renderer = $this->serviceLocator->get('Zend\View\Renderer\PhpRenderer');
        $this->renderer->headTitle(html_entity_decode('Tài khoản - Danh sách rao vặt đã lưu ') . General::TITLE_META);
        $this->renderer->headMeta()->appendName('keywords', html_entity_decode('quynhon247.com, tài khoản, Thông tin, Thông tin tài khoản, Thông tin tài khoản quynhon247.com'));
        $this->renderer->headMeta()->appendName('description', html_entity_decode('Tài khoản - Danh sách rao vặt đã lưu' . General::TITLE_META));


        if (!empty($arrFavouriteList)) {
            $arrContIdList = [];
            foreach ($arrFavouriteList as $favourite) {
                $arrContIdList[] = $favourite['cont_id'];
            }
            $instanceSearchContent = new \My\Search\Content();
            $arrContentList = $instanceSearchContent->getList(['in_cont_id' => $arrContIdList]);
            $arrContentListFormat = [];
            if ($arrContentList) {
                $arrUserIdList = [];
                foreach ($arrContentList as $arrContent) {
                    $arrUserIdList[] = $arrContent['user_created'];
                    $arrContentListFormat[$arrContent['cont_id']] = $arrContent;
                }
                $instanceSearchUser = new \My\Search\User();
                $arrUserList = $instanceSearchUser->getList(['in_user_id' => $arrUserIdList]);
                $arrUserListFormat = [];
                if (!empty($arrUserList)) {
                    foreach ($arrUserList as $user) {
                        $arrUserListFormat[$user['user_id']] = $user;
                    }
                }
                unset($arrContentList);
                unset($arrUserList);
            }
        }
        return [
            'params' => $params,
            'arrFavouriteList' => $arrFavouriteList,
            'arrContentList' => $arrContentListFormat,
            'arrUserList' => $arrUserListFormat,
            'paging' => $paging
        ];
    }

    public function deleteSavePostAction() {
        if (!CUSTOMER_ID) {
            return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => '<p style="color:red">Please login before delete!</b></p>')));
        }

        $params = $this->params()->fromPost();
        if (empty($params['arrItem']) || !is_array($params['arrItem'])) {
            return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => '<p style="color:red">Can not get params from Post! Please try again!</b></p>')));
        }
        $arrItem = array_unique($params['arrItem']);

        //find favourite
        $instanceSearchFavourite = new \My\Search\Favourite();
        $arrList = $instanceSearchFavourite->getList(['in_favo_id' => $arrItem, 'user_id' => CUSTOMER_ID, 'not_status' => -1]);

        $arrIdList = [];
        if (!empty($arrList)) {
            foreach ($arrList as $value) {
                $arrIdList[] = $value['favo_id'];
            }
        }

        if (empty($arrIdList)) {
            return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => '<p style="color:red">Không tìm thấy các rao vặt này trong hệ thống!</b></p>')));
        }

        $arrParams = [
            'updated_date' => time(),
            'status' => -1
        ];

        $serviceFavourite = $this->serviceLocator->get('My\Models\Favourite');
        $intResult = $serviceFavourite->multiEdit($arrParams, ['in_favo_id' => implode(',', $arrIdList)]);

        if ($intResult <= 0) {
            return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => '<p style="color:red">Xảy ra lỗi trong quá trình xử lý!Vui lòng thử lại sau giây lát!</b></p>')));
        }

        return $this->getResponse()->setContent(json_encode(array('st' => 1, 'data' => $arrIdList, 'ms' => 'Xóa thành công ' . count($arrIdList) . ' tin nhắn!')));
    }

    public function viewUserAction() {
        $params = $this->params()->fromRoute();

        if (empty($params['userId']) || !is_numeric($params['userId'])) {
            return $this->redirect()->toRoute('404', array());
        }
        $instanceSearchUser = new \My\Search\User();
        $arrUserDetail = $instanceSearchUser->getDetail(['user_id' => (int) $params['userId'], 'not_status' => -1]);

        if (empty($arrUserDetail)) {
            return $this->redirect()->toRoute('404', array());
        }

        $instaceSearchContent = new \My\Search\Content();
        $arrConditionContent = [
            'user_created' => $arrUserDetail['user_id'],
            'not_cont_status' => -1
        ];

        $intPage = (int) $params['page'] > 0 ? (int) $params['page'] > 0 : 1;
        $intLimit = 20;
        $arrContentList = $instaceSearchContent->getListLimit($arrConditionContent, $intPage, $intLimit, ['created_date' => ['order' => 'desc']]);
        $intTotal = $instaceSearchContent->getTotal($arrConditionContent);
        $helper = $this->serviceLocator->get('viewhelpermanager')->get('Paging');
        $paging = $helper($params['module'], $params['__CONTROLLER__'], $params['action'], $intTotal, $intPage, $intLimit, 'view-user-info', $params);

        $this->renderer = $this->serviceLocator->get('Zend\View\Renderer\PhpRenderer');
        $this->renderer->headMeta()->appendName('dc.description', html_entity_decode('Thông tin tài khoản : ' . $arrUserDetail['user_fullname']) . General::TITLE_META);
        $this->renderer->headMeta()->appendName('dc.subject', html_entity_decode('Thông tin tài khoản : ' . $arrUserDetail['user_fullname']) . General::TITLE_META);
        $this->renderer->headTitle(html_entity_decode('Danh sách rao vặt tài khoản : ' . $arrUserDetail['user_fullname']) . General::TITLE_META);
        $this->renderer->headMeta()->appendName('keywords', html_entity_decode('Danh sách rao vặt tài khoản : ' . $arrUserDetail['user_fullname']) . General::TITLE_META);
        $this->renderer->headMeta()->appendName('description', html_entity_decode('Danh sách rao vặt tài khoản : ' . $arrUserDetail['user_fullname']) . General::TITLE_META);
//        $this->renderer->headMeta()->appendName('social', $metaSocial);
        $this->renderer->headMeta()->setProperty('og:url', $this->url()->fromRoute('view-user-info', ['fullname' => $params['fullname'], 'userId' => $params['userId'], 'page' => $intPage]));
        $this->renderer->headMeta()->setProperty('og:title', html_entity_decode('Danh sách rao vặt tài khoản : ' . $arrUserDetail['user_fullname']));
        $this->renderer->headMeta()->setProperty('og:description', html_entity_decode('Danh sách rao vặt tài khoản : ' . $arrUserDetail['user_fullname']));

        $arrPropertiesFormat = [];
        if (!empty($arrContentList)) {
            $arrIdList = [];
            foreach ($arrContentList as $prop) {
                $arrIdList[] = $prop['prop_id'];
            }
            $instaceSearchProperties = new \My\Search\Properties();
            $arrPropertiesList = $instaceSearchProperties->getList(['in_prop_id' => $arrIdList]);
            if (!empty($arrPropertiesList)) {
                foreach ($arrPropertiesList as $value) {
                    $arrPropertiesFormat[$value['prop_id']] = $value;
                }
                unset($arrPropertiesList);
            }
        }

        return [
            'params' => $params,
            'paging' => $paging,
            'arrContentList' => $arrContentList,
            'arrUserDetail' => $arrUserDetail,
            'arrPropertiesList' => $arrPropertiesFormat
        ];
    }

    public function refreshContentAction() {
        if (empty(CUSTOMER_ID)) {
            return $this->getResponse()->setContent(json_encode(['st' => -1, 'ms' => '<b class="color-red">Chưa đăng nhập không thể làm mới tin!</b>']));
        }

        if ($this->request->isPost()) {
            $params = $this->params()->fromPost();
            if (empty($params['cont_id'])) {
                return $this->getResponse()->setContent(json_encode(['st' => -1, 'ms' => '<b class="color-red">Tham số truyền lên không chính xác!</b>']));
            }

            $instanceSearchContent = new \My\Search\Content();
            $arrContent = $instanceSearchContent->getDetail(['cont_id', 'not_cont_status' => -1, 'user_created' => CUSTOMER_ID]);

            if (empty($arrContent)) {
                return $this->getResponse()->setContent(json_encode(['st' => -1, 'ms' => '<b class="color-red">Không tìm thấy rao vặt này trong hệ thống của chúng tôi!</b>']));
            }

            $serviceContent = $this->serviceLocator->get('My\Models\Content');
            $intResult = $serviceContent->edit(['updated_date' => time(), 'user_updated' => CUSTOMER_ID], $arrContent['cont_id']);

            if ($intResult) {
                return $this->getResponse()->setContent(json_encode(['st' => 1, 'ms' => '<b class="color-red">Làm mới rao vặt thành công!</b>']));
            }
        }
        return $this->getResponse()->setContent(json_encode(['st' => -1, 'ms' => '<b class="color-red">Xảy ra lỗi trong quá trình xử lý!Vui lòng thử lại sau giây lát!</b>']));
    }

}
