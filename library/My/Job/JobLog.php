<?php

namespace My\Job;

use My\General;

class JobLog extends JobAbstract {
    /*
     * save User
     */

    public function writeLog($params) {
        if ($params->workload()) {
            $arrParams = unserialize($params->workload());
        }

        if (empty($arrParams)) {
            echo General::getColoredString("ERROR: Params is incorrent or empty ", 'light_cyan', 'red');
            return false;
        }

        $arrLog = $arrParams;

        $intLogId = $arrLog['log_id'];
        $instanceSearch = new \My\Search\Logs();
        $arrDocument = new \Elastica\Document($intLogId, $arrLog);
        $intResult = $instanceSearch->add($arrDocument);

        if (!$intResult) {
            echo General::getColoredString("ERROR: Cannot add Logs ID = {$intLogId} to Search \n", 'light_cyan', 'red');
            return false;
        }

        echo General::getColoredString("Add Logs ID: {$intLogId} to Search Success", 'green');

        return true;
    }
}
