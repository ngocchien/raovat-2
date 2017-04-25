<?php

namespace My\Job;

use My\General;

class JobTransactionHistory extends JobAbstract {
    /*
     * save User
     */

    public function writeTran($params) {
        if ($params->workload()) {
            $arrParams = unserialize($params->workload());
        }

        if (empty($arrParams)) {
            echo General::getColoredString("ERROR: Params is incorrent or empty ", 'light_cyan', 'red');
            return false;
        }


        $intId = $params['tran_id'];
        $instanceSearch = new \My\Search\TransactionHistory();
        $arrDocument = new \Elastica\Document($intId, $arrParams);
        $intResult = $instanceSearch->add($arrDocument);

        if (!$intResult) {
            echo General::getColoredString("ERROR: Cannot add ID = {$intId} to Search \n", 'light_cyan', 'red');
            return false;
        }

        echo General::getColoredString("Add ID: {$intId} to Search Success", 'green');

        return true;
    }
}
