<?php

namespace My\Validator;

use Zend\Validator;

class Validate {

    public function notEmpty($strValidate) {
        $validator = new Validator\NotEmpty();
        if ($validator->isValid($strValidate)) {
            return true;
        } else {
            return false;
        }
    }

    public function emailAddress($strValidate) {
        $validator = new Validator\EmailAddress();
        if ($validator->isValid($strValidate)) {
            return true;
        } else {
            return false;
        }
    }

    public function Digits($strValidate) {
        $validator = new Validator\Digits();
        if ($validator->isValid($strValidate)) {
            return true;
        } else {
            return false;
        }
    }

    public function Between($intValidate, $intMin, $intMax, $inclusive = true) {
        $arrValidate = array(
            'min' => $intMin,
            'max' => $intMax,
        );
        $inclusive = false ? $arrValidate['inclusive'] = false : $arrValidate;
        $validator = new Validator\Between($arrValidate);
        if ($validator->isValid($intValidate)) {
            return true;
        } else {
            return false;
        }
    }

    public function Regex($strValidate,$strPattern,$strSetPattern = null) {
        if(empty($strValidate) || empty($strPattern))
            return false;
        
        $arrValidate = array(
            'pattern' => $strPattern
        );
        
        $validator = new Validator\Regex($arrValidate);
        
        $strSetPattern ? $validator->setPattern($strSetPattern) : '';
        
        if ($validator->isValid($strValidate)) {
            return true;
        } else {
            return false;
        }
    }

    public function noRecordExists($strValidate, $strTable, $strField, $dbAdapter, $arrExclude = array()) {
        $arrValidate = array(
            'table' => $strTable,
            'field' => $strField,
            'adapter' => $dbAdapter
        );
        $arrExclude ? $arrValidate['exclude'] = $arrExclude : $arrValidate;
        $validator = new Validator\Db\NoRecordExists($arrValidate);
        if ($validator->isValid($strValidate)) {
            return true;
        } else {
            return false;
        }
    }

    public function isUnicode($str) {
        $isUnicode = preg_match('/[ạáàảãẠÁÀẢÃâậấầẩẫÂẬẤẦẨẪăặắằẳẵẫĂẮẰẲẴẶẴêẹéèẻẽÊẸÉÈẺẼếềểễệẾỀỂỄỆọộổỗốồỌỘỔỖỐỒÔôóòỏõÓÒỎÕơợớờởỡƠỢỚỜỞỠụưứừửữựỤƯỨỪỬỮỰúùủũÚÙỦŨịíìỉĩỊÍÌỈĨỵýỳỷỹỴÝỲỶỸđĐ]/u', $str);
        return $isUnicode;
    }

}
