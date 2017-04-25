<?php

namespace Backend\Controller;

namespace Backend\Controller;

//namespace Acelaya\Files;
use My\General;
use Zend\Filter\File\RenameUpload;
use Zend\InputFilter\FileInput;
use Zend\InputFilter\InputFilter;
use Zend\Validator\File\Size;
use My\Controller\MyController;

class UploaderController extends MyController {

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
//        $request = $this->getRequest();
//        $returnObject = new \stdClass();
//
//        if ($request->isPost()) {
//            $adapter = new \Zend\File\Transfer\Adapter\Http();
//            $files = $adapter->getFileInfo();
//            $imageFileHttpPostName = 'upload_file';
//            $imageFile = $files[$imageFileHttpPostName];
//            $filename = time() . mt_rand(1, 10) . $imageFile['name'];
//            $pathFile = '/uploads/' . date('Y') . '/' . date('m') . '/' . date('d') . '/';
//            if (!is_dir($pathFile)) {
//                mkdir($pathFile, 0755, true);
//                chmod($pathFile, 0755);
//            }
//
//            $adapter->addValidator('Extension', false, array('jpg', 'png', 'gif'), $imageFileHttpPostName);
//            $adapter->addFilter('Rename', array(
//                'target' => PUBLIC_PATH . $pathFile . $filename,
//                'overwrite' => false), $imageFileHttpPostName);
//
//            if (!$adapter->isValid()) {
//                $returnObject->errorMessage = $adapter->getMessages();
//                $returnObject->result = 0;
//            } else {
//
//                try {
//                    $adapter->receive($imageFileHttpPostName);
//                    $returnObject->result = 1;
//                } catch (\Zend\Filter\Exception\InvalidArgumentException $e) {
//                    $returnObject->errorMessage = $e->getMessage();
//                    $returnObject->result = 0;
//                }
//            }
//
//            $result = array();
//            if ($returnObject->result == 1) {
//                $result = array("resultcode" => 1, "file_name" => $pathFile . $filename, "result" => "file_uploaded");
//            } elsE {
//                $result = array("resultcode" => -1, "file_name" => "", "result" => "file_uploaded");
//            }
//
//            $view = new \Zend\View\Model\ViewModel($result);
//            $view->setTemplate('backend/uploader/images.phtml'); // path to phtml file under view folder
//            $viewRender = $this->getServiceLocator()->get('ViewRenderer');
//            echo $viewRender->render($view);
//            return $this->response;
//            die();
//        }
//
//        die();
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
