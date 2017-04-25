<?php

namespace Frontend\Controller;

use My\Controller\MyController,
 My\General,
 My\Validator\Validate,
 Zend\Validator\File\Size;

class CommentController extends MyController {
/* @var $serviceCategory \My\Models\Category */
/* @var $serviceProduct \My\Models\Product */
/* @var $serviceProperties \My\Models\Properties */

    public function __construct() {

    }

    public function addAction(){
        if($this->request->isPost()){
            $params = $this->params()->fromPost();
//            p($params);die;
            if(empty($params['ProdID'])){
                return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => 'Xảy ra lỗi trong quá trình xử lý ! Vui lòng thử lại!')));
            }
            if(UID < 1){
                $validator = new Validate();
                if(empty($params['strFullname'])){
                    return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => 'Họ và tên không được để trống !')));
                }
                $strFullname = trim($params['strFullname']);
                
                if(empty($params['strEmail'])){
                    return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => 'Email không được để trống !')));
                }
                $strEmail = trim($params['strEmail']);
                if (!$validator->emailAddress($strEmail)) {
                    return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => '<center>Địa chỉ email không hợp lệ !</center>')));
                }
            }
            
            if(empty($params['strCommentContent'])){
                return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => 'Nội dung bình luận phải từ 3 ký tự trở lên !')));
            }
            $strCommentContent = trim($params['strCommentContent']);
            if(strlen($strCommentContent) < 3){
                return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => 'Nội dung bình luận phải từ 3 ký tự trở lên !')));
            }

//            if(empty($params['strCaptcha'])){
//                return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => 'Chưa nhập mã xác nhận !')));
//            }
//            $strCaptcha = trim($params['strCaptcha']);
//            if($strCaptcha != $_SESSION['captcha']){
//                return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => 'Nhập mã xác nhận không chính xác !')));
//            }
            
            $groupName = '';
            if (UID) {
                $serviceGroup = $this->serviceLocator->get('My\Models\Group');
                $arrGroup = $serviceGroup->getDetail(array('grou_id' => GROU_ID));
                $groupName = $arrGroup['grou_name'];
            }
            
            $client = @$_SERVER['HTTP_CLIENT_IP'];
            $forward = @$_SERVER['HTTP_X_FORWARDED_FOR'];
            $remote = $_SERVER['REMOTE_ADDR'];
            if (filter_var($client, FILTER_VALIDATE_IP)) {
                $ipaddress = $client;
            }
            if (filter_var($forward, FILTER_VALIDATE_IP)) {
                $ipaddress = $forward;
            }
            if (filter_var($remote, FILTER_VALIDATE_IP)) {
                $ipaddress = $remote;
            }
            
            $arrData = array(
                'user_id' => UID > 0 ? UID : 0,
                'comm_content' => $strCommentContent,
                'comm_created' =>  time(),
                'prod_id'=>(int) $params['ProdID'],
                'user_fullname' => UID > 0 ? FULLNAME : strip_tags($strFullname),
                'user_email' => UID > 0 ? EMAIL : strip_tags($strEmail),
                'user_name_group' => $groupName,
                'comm_ip' => $ipaddress,
                'comm_parent'=> empty($params['strParent']) ? 0 : (int) $params['strParent']
            );
//            p($arrData);die;
            $serviceComment = $this->serviceLocator->get('My\Models\Comment');
            $intResult = $serviceComment->add($arrData);
            if ($intResult > 0) {
                $arrData['time'] = \My\General::formatDateString(time());
                $arrData['user_avatar'] = UID >0 ? AVATAR : STATIC_URL.'/f/v1/images/noavatar.jpg' ;
                return $this->getResponse()->setContent(json_encode(array('st' => 1, 'data' => $arrData, 'commentID' => $intResult,'parent'=>(int) $params['strParent'])));
            }
            return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => 'Xảy ra lỗi trong qua trình xử lý, vui lòng thử lại !')));
            die();
        }
    }
}
