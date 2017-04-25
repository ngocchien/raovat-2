<?php

namespace My\Job;

use My\General;

class JobPermission extends JobAbstract {
    /*
     * save Content
     */

    public function writePermission($params) {
        if ($params->workload()) {
            $arrParams = unserialize($params->workload());
        }

        if (empty($arrParams)) {
            echo General::getColoredString("ERROR: Params is incorrent or empty ", 'light_cyan', 'red');
            return false;
        }
        
        $id = $arrParams['perm_id'];
        $instanceSearch = new \My\Search\Permission();
        $arrDocument = new \Elastica\Document($id, $arrParams);
        $intResult = $instanceSearch->add($arrDocument);

        if (!$intResult) {
            echo General::getColoredString("ERROR: Cannot add ID = {$id} to Search \n", 'light_cyan', 'red');
            return false;
        }

        echo General::getColoredString("Add ID: {$id} to Search Success", 'green');

        return true;
    }

    /* edit */

    public function editPermission($params) {

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

        $instanceSearch = new \My\Search\Permission();
        $resutl = $instanceSearch->edit($document);

        if (!$resutl) {
            echo General::getColoredString("ERROR: Cannot Edit id = {$id} to Search \n", 'light_cyan', 'red');
            return false;
        }

        echo General::getColoredString("Edit id: {$id} to Search Success", 'green');

        return true;
    }
}
