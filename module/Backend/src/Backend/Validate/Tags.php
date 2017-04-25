<?php

namespace Backend\Validate;

use Zend\Validator;
use My\General;

class Tags {

    // chua thong bao loi
    protected $_messagesError = null;
    // mang du lieu sau khi kiem tra
    protected $_arrData;

    public function __construct($arrParam = null, $serviceTags) {

        $validatorEmpty = new Validator\NotEmpty();
        $validatorStringLength = new \Zend\Validator\StringLength();

        // ========================================
        // KIEM TRA prod_name
        // ========================================
        if (!$validatorEmpty->isValid($arrParam['tags_name'])) {
            $this->_messagesError['tags_name'] = 'Tên tags không được bỏ trống!';
        } else {
            $tagsSlug = General::getSlug($arrParam['tags_name']);
            $arrCondition = array(
                'tags_slug' => $tagsSlug,
                'not_tags_status' => -1
            );
            
            if ($arrParam['tags_id']) {
                $arrCondition['not_tags_id'] = $arrParam['tags_id'];
                unset($arrParam['tags_id']);

                $arrParam['tags_updated'] = time();
                $arrParam['user_updated'] = UID;
            } else {
                $arrParam['user_created'] = UID;
                $arrParam['tags_created'] = time();
            }

            $arrTags = $serviceTags->getDetail($arrCondition);

            if ($arrTags) {
                $isExisted = true;
            } else {
                $arrParam ['tags_slug'] = $tagsSlug;
            }
        }

        if ($isExisted) {
            $this->_messagesError ['tags_name'] = 'Tên tags này đã tồn tại trong hệ thống !';
        }

        if (!$validatorEmpty->isValid($arrParam['tags_description'])) {
            $this->_messagesError['tags_description'] = 'Chưa nhập mô tả cho tags!';
        } else {
            $validatorStringLength->setMin(10);
            if (!$validatorStringLength->isValid($arrParam['tags_description'])) {
                $this->_messagesError['tags_description'] = 'Mô tả tags phải 10 ký tự trở lên!';
            }
        }

        $this->_arrData = $arrParam;
    }

    // Kiem tra Error
    // return true neu co loi xuat hien
    public function isError() {
        if ($this->_messagesError !== null) {
            return true;
        } else {
            return false;
        }
    }

    // Tra ve mot mang cac loi
    public function getMessageError() {
        return $this->_messagesError;
    }

    // Tra ve mot mang du lieu sau khi kiem tra
    public function getData() {
        return $this->_arrData;
    }

}
