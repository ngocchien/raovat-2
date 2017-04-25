<?php

namespace Frontend\Controller;

use My\Controller\MyController,
    My\General;

class CaptchaController extends MyController {

    public function __construct() {
        
    }

    public function indexAction() {
        if ($this->request->isPost()) {
            $general = new General();
            $maxWordLength = 6;
            $width = 80;
            $height = 30;
            $captcha = $general->generateCaptcha($maxWordLength, $width, $height);
            $_SESSION['captcha'] = $captcha['word'];
            return $this->getResponse()->setContent(json_encode(array('st' => 1, 'url' => $captcha['url'])));
        }
    }

}
