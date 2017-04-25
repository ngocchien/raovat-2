<?php

namespace My\Job;

use My\General;

class JobContent extends JobAbstract {
    /*
     * save Content
     */

    public function writeContent($params) {
        if ($params->workload()) {
            $arrParams = unserialize($params->workload());
        }

        if (empty($arrParams)) {
            echo General::getColoredString("ERROR: Params is incorrent or empty ", 'light_cyan', 'red');
            return false;
        }
        
        $id = $arrParams['cont_id'];
        $instanceSearch = new \My\Search\Content();
        $arrDocument = new \Elastica\Document($id, $arrParams);
        $intResult = $instanceSearch->add($arrDocument);

        if (!$intResult) {
            echo General::getColoredString("ERROR: Cannot add content ID = {$id} to Search \n", 'light_cyan', 'red');
            return false;
        }

        echo General::getColoredString("Add content ID: {$id} to Search Success", 'green');

        return true;
    }

    /* edit */

    public function editContent($params) {

        if ($params->workload()) {
            $arrParams = unserialize($params->workload());
        }

        if (empty($arrParams)) {
            echo General::getColoredString("ERROR: Params is incorrent or empty ", 'light_cyan', 'red');
            return false;
        }

        $id = $arrParams['cont_id'];
        $updateData = new \Elastica\Document();
        $updateData->setData($arrParams);
        $document = new \Elastica\Document($id, $arrParams);
        $document->setUpsert($updateData);

        $instanceSearch = new \My\Search\Content();
        $resutl = $instanceSearch->edit($document);

        if (!$resutl) {
            echo General::getColoredString("ERROR: Cannot Edit content id = {$id} to Search \n", 'light_cyan', 'red');
            return false;
        }

        echo General::getColoredString("Edit content id: {$id} to Search Success", 'green');

        return true;
    }

    public function multiEditContent($params) {
        if ($params->workload()) {
            $arrParams = unserialize($params->workload());
        }

        if (empty($arrParams)) {
            echo General::getColoredString("ERROR: Params is incorrent or empty ", 'light_cyan', 'red');
            return false;
        }
        $arrData = $arrParams['data'];
        $arrId = explode(',', $arrParams['condition']['in_cont_id']);
        $instanceSearch = new \My\Search\Content();

        foreach ($arrId as $id) {
            $arrData['cont_id'] = $id;
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
