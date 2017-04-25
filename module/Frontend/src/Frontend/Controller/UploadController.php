<?php

namespace Frontend\Controller;

//namespace Acelaya\Files;
use My\General;
use Zend\Filter\File\RenameUpload;
use Zend\InputFilter\FileInput;
use Zend\InputFilter\InputFilter;
use Zend\Validator\File\Size;
use My\Controller\MyController;

class UploadController extends MyController {

    const FILE = 'file';

    public function __construct() {
        $input = new FileInput(self::FILE);
        $input->getValidatorChain()->attach(new Size(['max' => "1024"]));
        $input->getFilterChain()->attach(new RenameUpload([
            'overwrite' => false,
            'use_upload_name' => true,
            'target' => '/'
        ]));
    }

    public function indexAction() {
        $view = new \Zend\View\Model\ViewModel();
        $view->setTemplate('backend/uploader/index.phtml'); // path to phtml file under view folder
        $viewRender = $this->getServiceLocator()->get('ViewRenderer');
        echo $viewRender->render($view);
        die();
    }

    public function imagesAction() {
        $request = $this->getRequest();
        if ($request->isPost()) {
            $result = array();
            if ($this->getRequest()->isPost()) {
                $files = $this->params()->fromFiles();
                $folder = "content";
                $filename = "upload_file";
                
                if ((empty($files) || !is_array($files)) && empty($folder) && empty($filename)) {
                    echo 0;
                    return false;
                }
                $result = General::ImageUpload($files[$filename], $folder);
            }


            if (!empty($result[0]["sourceImage"])) {
                $result = array("resultcode" => 1, "file_name" => $result[0]["sourceImage"], "result" => "");
            } else {
                $result = array("resultcode" => -1, "file_name" => "", "result" => "");
            }

            $view = new \Zend\View\Model\ViewModel($result);
            $view->setTemplate('backend/uploader/images.phtml'); // path to phtml file under view folder
            $viewRender = $this->getServiceLocator()->get('ViewRenderer');
            echo $viewRender->render($view);
            return $this->response;
        }
        die();
    }

    public function uploadContentAction() {
        $result = array();
        if ($this->getRequest()->isPost()) {
            $files = $this->params()->fromFiles();
            $folder = $this->params()->fromQuery('folder');
            $filename = $this->params()->fromQuery('filename');
            if ((empty($files) || !is_array($files)) && empty($folder) && empty($filename)) {
                echo 0;
                return false;
            }
            $result = General::ImageUpload($files[$filename], $folder);
        }
        return $this->getResponse()->setContent(json_encode($result));
    }

}

?>
