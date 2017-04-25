<?php

namespace My\Job;

use My\General;

class JobCategory extends JobAbstract {
    /*
     * save Content
     */

    public function writeCategory($params) {
        if ($params->workload()) {
            $arrParams = unserialize($params->workload());
        }

        if (empty($arrParams)) {
            echo General::getColoredString("ERROR: Params is incorrent or empty ", 'light_cyan', 'red');
            return false;
        }

        $id = $arrParams['cate_id'];
        $instanceSearch = new \My\Search\Category();
        $arrDocument = new \Elastica\Document($id, $arrParams);
        $intResult = $instanceSearch->add($arrDocument);

        if (!$intResult) {
            echo General::getColoredString("ERROR: Cannot add  ID = {$id} to Search \n", 'light_cyan', 'red');
            return false;
        }

        echo General::getColoredString("Add ID: {$id} to Search Success", 'green');

        return true;
    }

    /* edit */

    public function editCategory($params) {

        if ($params->workload()) {
            $arrParams = unserialize($params->workload());
        }

        if (empty($arrParams)) {
            echo General::getColoredString("ERROR: Params is incorrent or empty ", 'light_cyan', 'red');
            return false;
        }

        $id = $arrParams['cate_id'];
        $updateData = new \Elastica\Document();
        $updateData->setData($arrParams);
        $document = new \Elastica\Document($id, $arrParams);
        $document->setUpsert($updateData);

        $instanceSearch = new \My\Search\Category();
        $resutl = $instanceSearch->edit($document);

        if (!$resutl) {
            echo General::getColoredString("ERROR: Cannot Edit id = {$id} to Search \n", 'light_cyan', 'red');
            return false;
        }

        echo General::getColoredString("Edit id: {$id} to Search Success", 'green');

        return true;
    }

    public function multiEditCategory($params) {
        if ($params->workload()) {
            $arrParams = unserialize($params->workload());
        }

        if (empty($arrParams)) {
            echo General::getColoredString("ERROR: Params is incorrent or empty ", 'light_cyan', 'red');
            return false;
        }
        $arrData = $arrParams['data'];
        $arrId = explode(',', $arrParams['condition']['in_cate_id']);
        $instanceSearch = new \My\Search\Category();

        foreach ($arrId as $id) {
            $arrData['cate_id'] = $id;
            $updateData = new \Elastica\Document();
            $updateData->setData($arrData);
            $document = new \Elastica\Document($id, $arrData);
            $document->setUpsert($updateData);
            $resutl = $instanceSearch->edit($document);
            if (!$resutl) {
                echo General::getColoredString("ERROR: Cannot Edit id = {$id} to Search \n", 'light_cyan', 'red');
            } else {
                echo General::getColoredString("Edit id: {$id} to Search Success", 'green');
            }
        }
        return true;
    }

}
