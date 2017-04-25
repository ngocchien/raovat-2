<?php

namespace My\Job;

use My\General;

class JobMail extends JobAbstract {
    /*
     * save Content
     */

    public function sendMail($params) {
        if ($params->workload()) {
            $arrParams = unserialize($params->workload());
        }

        if (empty($arrParams)) {
            echo General::getColoredString("ERROR: Params is incorrent or empty ", 'light_cyan', 'red');
            return false;
        }
        try {
            $result = General::sendMail($arrParams['user_email'], $arrParams['title'], $arrParams['html']);
        } catch (\Exception $exc) {
            echo $exc->getMessage();
            die();
        }

        if (!$result) {
            echo General::getColoredString("ERROR: Send Mail error", 'light_cyan', 'red');
            return;
        }

        echo General::getColoredString("SUCCESS : Send mail is success", 'green');

        return true;
    }
}
