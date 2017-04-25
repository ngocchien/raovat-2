<?php

namespace My\Auth;

use Zend\Authentication\Storage;

class MyStorage extends Storage\Session {

    //default remember password for 1w
    public function setRememberMe($rememberMe = 0, $time = 604800) {
        if ($rememberMe == 1) {
            return $this->session->getManager()->rememberMe($time);
        }
    }

    public function forgetMe() {
        $this->session->getManager()->forgetMe();
    }

}
