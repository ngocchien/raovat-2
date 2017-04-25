<?php

namespace Backend\Controller;

use My\Controller\MyController,
    My\General;

class GeneralBeController extends MyController {

    public function __construct() {
//        $this->externalCSS = [
//            STATIC_URL . '/b/css/??bootstrap-wysihtml5.css'
//        ];
        $this->externalJS = [
            STATIC_URL . '/b/js/my/??general.js',
            STATIC_URL . '/f/v1/js/library/tinymce/tinymce.min.js'
        ];
    }

    public function indexAction() {
        $params = array_merge($this->params()->fromQuery(), $this->params()->fromRoute());
        $intPage = $this->params()->fromRoute('page', 1);
        $intLimit = 15;
        $arrCondition = array(
            'not_gene_status' => -1
        );
        $instanceSearchGeneral = new \My\Search\GeneralBqn();
        $arrGeneralList = $instanceSearchGeneral->getListLimit($arrCondition, $intPage, $intLimit, ['gene_id' => ['order' => 'desc']]);
        $route = 'backend';
        $intTotal = $instanceSearchGeneral->getTotal($arrCondition);
        $helper = $this->serviceLocator->get('viewhelpermanager')->get('Paging');
        $paging = $helper($params['module'], $params['__CONTROLLER__'], $params['action'], $intTotal, $intPage, $intLimit, $route, $params);
        if (!empty($arrGeneralList)) {
            $inst = new \My\Search\User();

            foreach ($arrGeneralList as $arrGeneral) {
                $arrUserIdList[] = $arrGeneral['user_created'];
            }
            if (!empty($arrUserIdList)) {
                $arrUserIdList = array_unique($arrUserIdList);
                $instanceSearchUser = new \My\Search\User();
                $arrConditionUser = [
                    'in_user_id' => $arrUserIdList
                ];
                $arrUserList = $instanceSearchUser->getList($arrConditionUser);
                if (!empty($arrUserList)) {
                    foreach ($arrUserList as $arrUser) {
                        $arrUserListFM[$arrUser['user_id']] = $arrUser;
                    }
                }
            }
        }
        return array(
            'params' => $params,
            'paging' => $paging,
            'arrGeneralList' => $arrGeneralList,
            'arrUserList' => $arrUserListFM
        );
    }

    public function addAction() {
        $arrParamsRoute = $this->params()->fromRoute();

        if ($this->request->isPost()) {
            $params = $this->params()->fromPost();
            $errors = [];
            if (empty($params['gene_title'])) {
                $errors['gene_title'] = 'Chưa nhập tiêu đề cho General';
            }
            if (empty($params['gene_content'])) {
                $errors['gene_content'] = 'Chưa nhập nội dung cho General';
            }

            if (empty($errors)) {
                //check in db
                $instanceSearchGeneral = new \My\Search\GeneralBqn();
                $arr_condition = [
                    'not_gene_status' => -1,
                    'gene_slug' => General::getSlug($params['gene_title'])
                ];
                $arr_general = $instanceSearchGeneral->getDetail($arr_condition);
                if ($arr_general) {
                    $errors['gene_title'] = 'Tiêu đề này đã tồn tại trong hệ thống';
                }
                if (empty($errors)) {
                    $arr_data = [
                        'gene_title' => $params['gene_title'],
                        'gene_slug' => MyGeneral::getSlug($params['gene_title']),
                        'gene_status' => (int) $params['gene_status'],
                        'gene_content' => $params['gene_content'],
                        'user_created' => UID,
                        'created_date' => time()
                    ];
                    $serviceGeneral = $this->serviceLocator->get('My\Models\GeneralBqn');
                    $intResult = $serviceGeneral->add($arr_data);
                    if ($intResult) {
                        $serviceLogs = $this->serviceLocator->get('My\Models\Logs');
                        $arrLog = General::createLogs($arrParamsRoute, $params, $intResult);
                        $serviceLogs->add($arrLog);
                        $this->flashMessenger()->setNamespace('add-success-general')->addMessage('Thêm General thành công !');
                        $this->redirect()->toRoute('backend', array('controller' => 'general-be', 'action' => 'add'));
                    }
                    $errors[] = 'Xảy ra lỗi trong quá trình xử lý! Vui lòng thử lại sau giây lát!';
                }
            }
        }

        return [
            'errors' => $errors,
            'params' => $params
        ];
    }

    public function editAction() {
        $arrParamsRoute = $this->params()->fromRoute();
        $id = $arrParamsRoute['id'];

        if (empty($id)) {
            $this->redirect()->toRoute('backend', array('controller' => 'general', 'action' => 'index'));
        }

        $instanceSearchGeneral = new \My\Search\GeneralBqn();
        $arr_general = $instanceSearchGeneral->getDetail(['gene_id' => $id, 'not_gene_status' => -1]);

        if (empty($arr_general)) {
            $this->redirect()->toRoute('backend', array('controller' => 'general', 'action' => 'index'));
        }

        if ($this->request->isPost()) {
            $params = $this->params()->fromPost();
            $errors = [];
            if (empty($params['gene_title'])) {
                $errors['gene_title'] = 'Chưa nhập tiêu đề cho General';
            }
            if (empty($params['gene_content'])) {
                $errors['gene_content'] = 'Chưa nhập nội dung cho General';
            }

            if (empty($errors)) {
                //check in db
                $arr_condition = [
                    'not_gene_status' => -1,
                    'not_gene_id' => $id,
                    'gene_slug' => General::getSlug($params['gene_title'])
                ];
                $arr_esxit = $instanceSearchGeneral->getDetail($arr_condition);

                if ($arr_esxit) {
                    $errors['gene_title'] = 'Tiêu đề này đã tồn tại trong hệ thống';
                }

                if (empty($errors)) {
                    $arr_data = [
                        'gene_title' => $params['gene_title'],
                        'gene_slug' => General::getSlug($params['gene_title']),
                        'gene_status' => (int) $params['gene_status'],
                        'gene_content' => $params['gene_content'],
                        'user_updated' => UID,
                        'updated_date' => time()
                    ];
                    $serviceGeneral = $this->serviceLocator->get('My\Models\GeneralBqn');
                    $intResult = $serviceGeneral->edit($arr_data, $id);
                    if ($intResult) {
                        $serviceLogs = $this->serviceLocator->get('My\Models\Logs');
                        $arrLog = General::createLogs($arrParamsRoute, $params, $intResult);
                        $serviceLogs->add($arrLog);
                        $this->flashMessenger()->setNamespace('edit-success-general')->addMessage('Sửa general thành công !');
                        $this->redirect()->toRoute('backend', array('controller' => 'general-be', 'action' => 'edit', 'id' => $id));
                    }
                    $errors[] = 'Xảy ra lỗi trong quá trình xử lý! Vui lòng thử lại sau giây lát!';
                }
            }
        }

        return [
            'errors' => $errors,
            'params' => $params,
            'arr_general' => $arr_general
        ];
    }

    public function deleteAction() {
        $params_route = $this->params()->fromRoute();
        if ($this->request->isPost()) {
            $params = $this->params()->fromPost();
            if (empty($params['id'])) {
                return $this->getResponse()->setContent(array('st' => -1, 'ms' => 'Tham số truyên lên không chính xác!'));
            }

            $instanceSearchGeneral = new \My\Search\GeneralBqn();
            $arr_gene = $instanceSearchGeneral->getDetail(['gene_id' => $params['id'], 'not_gene_status' => -1]);

            if (empty($arr_gene)) {
                return $this->getResponse()->setContent(array('st' => -1, 'ms' => 'General không tồn tại trong hệ thống!'));
            }

            $arr_data = [
                'gene_status' => -1,
                'user_updated' => UID,
                'updated_date' => time()
            ];
            $serviceGeneral = $this->serviceLocator->get('My\Models\General');
            $intResult = $serviceGeneral->edit($arr_data, $params['id']);
            if ($intResult) {
                $serviceLogs = $this->serviceLocator->get('My\Models\Logs');
                $arrLog = General::createLogs($params_route, $params, $params['id']);
                $serviceLogs->add($arrLog);
                return $this->getResponse()->setContent(array('st' => 1, 'ms' => 'Xóa General thành công!'));
            }
            return $this->getResponse()->setContent(array('st' => -1, 'ms' => 'Xảy ra lỗi trong quá trình xử lý!'));
        }
    }

}
