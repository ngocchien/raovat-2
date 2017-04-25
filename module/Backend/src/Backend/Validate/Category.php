<?php

namespace Backend\Validate;

use Zend\Validator;
use My\General;

class Category {

    // chua thong bao loi
    protected $_messagesError = null;
    // mang du lieu sau khi kiem tra
    protected $_arrData;

    public function __construct($arrParam = null, $service) {

        $validatorEmpty = new Validator\NotEmpty();
        $validatorStringLength = new \Zend\Validator\StringLength();

        // ========================================
        // KIEM TRA prod_name
        // ========================================
        if (!$validatorEmpty->isValid($arrParam['cate_name'])) {
            $this->_messagesError['cate_name'] = 'Tên Danh mục không được bỏ trống!';
        } else {
            $slug = General::getSlug($arrParam['cate_name']);
            $arrCondition = array(
                'cate_name' => $slug,
                'not_cate_status' => -1
            );

            if ($arrParam['cate_id']) {
                $arrCondition['not_cate_id'] = $arrParam['cate_id'];
                unset($arrParam['cate_id']);

                $arrParam['cate_updated'] = time();
                $arrParam['user_updated'] = UID;
            } else {
                $arrParam['user_created'] = UID;
                $arrParam['cate_created'] = time();
            }

            $arrDetail = $service->getDetail($arrCondition);

            if ($arrDetail) {
                $isExisted = true;
            } else {
                $arrParam ['cate_slug'] = $slug;
            }
        }

        if ($isExisted) {
            $this->_messagesError ['cate_name'] = 'Tên Danh mục này đã tồn tại trong hệ thống !';
        }

        if (!$validatorEmpty->isValid($arrParam['cate_description'])) {
            $this->_messagesError['cate_description'] = 'Chưa nhập mô tả cho Danh mục!';
        } else {
            $validatorStringLength->setMin(10);
            if (!$validatorStringLength->isValid($arrParam['cate_description'])) {
                $this->_messagesError['cate_description'] = 'Mô tả cho Danh mục phải 10 ký tự trở lên!';
            }
        }

        if ($validatorEmpty->isValid($arrParam['cate_parent'])) {
            
        }
        
        $arrParam['cate_parent'] = (int) $arrParam['cate_parent'];
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
