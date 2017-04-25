<?php

namespace Backend\Controller;

use My\Controller\MyController,
    My\Validator\Validate,
    My\General,
    Zend\View\Model\ViewModel;

class ToolController extends MyController {
    /* @var $serviceCategory \My\Models\Category */

//    public function __construct() {
//        $this->externalJS = [
//            STATIC_URL . '/b/js/my/??category.js'
//        ];
//    }

    public function indexAction() {
        $instanceSearchContent = new \My\Search\Content();
        $condition = [
            'is_send' => 0
        ];
        $arrContentList = $instanceSearchContent->getList($condition);

        if (!empty($arrContentList)) {
            foreach ($arrContentList as $arr_data) {
                $template = 'backend/layout/send-mail-when-crawler';
                $viewModel = new ViewModel();
                $viewModel->setTerminal(true);
                $viewModel->setTemplate($template);
                $viewModel->setVariables(
                        [
                            'arrContent' => $arr_data,
                        ]
                );
                $html = $this->serviceLocator->get('viewrenderer')->render($viewModel);
                $arrEmail = [
                    'user_email' => json_decode($arr_data['user_info'], true)['user_email'],
                    'html' => $html,
                    'title' => 'Tin : ' . $arr_data['cont_title'] . ' đã được đăng tại bestquynhon.com',
                ];
                $instanceJob = new \My\Job\JobMail();
                $instanceJob->addJob(SEARCH_PREFIX . 'sendMail', $arrEmail);

                $serviceContent = $this->serviceLocator->get('My\Models\Content');
                $serviceContent->edit(['is_send' => 1], $arr_data['cont_id']);
                unset($serviceContent);
            }
        }
        die('done');
    }

}
