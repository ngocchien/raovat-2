<?php

namespace My\Job;

use My\General;

class JobFavourite extends JobAbstract {
    /*
     * save Content
     */

    public function writeFavourite($params) {
        if ($params->workload()) {
            $arrParams = unserialize($params->workload());
        }

        if (empty($arrParams)) {
            echo General::getColoredString("ERROR: Params is incorrent or empty ", 'light_cyan', 'red');
            return false;
        }

        $id = $arrParams['favo_id'];
        $instanceSearch = new \My\Search\Favourite();
        $arrDocument = new \Elastica\Document($id, $arrParams);
        $intResult = $instanceSearch->add($arrDocument);

        if (!$intResult) {
            echo General::getColoredString("ERROR: Cannot add favo ID = {$id} to Search \n", 'light_cyan', 'red');
            return false;
        }

        echo General::getColoredString("Add favo ID: {$id} to Search Success", 'green');

        return true;
    }

    /* edit */

    public function editFavourite($params) {

        if ($params->workload()) {
            $arrParams = unserialize($params->workload());
        }

        if (empty($arrParams)) {
            echo General::getColoredString("ERROR: Params is incorrent or empty ", 'light_cyan', 'red');
            return false;
        }

        $id = $arrParams['favo_id'];
        $updateData = new \Elastica\Document();
        $updateData->setData($arrParams);
        $document = new \Elastica\Document($id, $arrParams);
        $document->setUpsert($updateData);

        $instanceSearch = new \My\Search\Favourite();
        $resutl = $instanceSearch->edit($document);

        if (!$resutl) {
            echo General::getColoredString("ERROR: Cannot Edit id = {$id} to Search \n", 'light_cyan', 'red');
            return false;
        }

        echo General::getColoredString("Edit id: {$id} to Search Success", 'green');

        return true;
    }

    public function multiEditFavourite($params) {
        if ($params->workload()) {
            $arrParams = unserialize($params->workload());
        }

        if (empty($arrParams)) {
            echo General::getColoredString("ERROR: Params is incorrent or empty ", 'light_cyan', 'red');
            return false;
        }
        
        $arrData = $arrParams['data'];
        $arrId = explode(',', $arrParams['condition']['in_favo_id']);
        $instanceSearch = new \My\Search\Favourite();

        foreach ($arrId as $id) {
            $arrData['favo_id'] = $id;
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
