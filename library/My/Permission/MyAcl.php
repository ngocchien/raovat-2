<?php

namespace My\Permission;

use Zend\Permissions\Acl\Acl,
    Zend\Permissions\Acl\Role\GenericRole as Role,
    Zend\Permissions\Acl\Resource\GenericResource as Resource;

class MyAcl {

    protected $acl;

    public function __construct($serviceLocator) {
        $this->serviceLocator = $serviceLocator;
        $this->acl = new Acl();
        $this->buildPermission();
    }

    private function buildPermission() {
        $arrPermissionList = json_decode(PERMISSION, true);

        if (is_array($arrPermissionList) && count($arrPermissionList) > 0) {
            
            foreach ($arrPermissionList as $arrPermission) {
                $arrResource[] = strtolower($arrPermission['module'] . ':' . $arrPermission['controller'] . ':' . $arrPermission['action']);
            }
            $arrResource = array_unique($arrResource);
            
            //check kiểm tra trùng giữa user và group
            foreach ($arrResource as $strResource) {
                if (!$this->acl->hasResource($strResource)) {
                    $this->acl->addResource(new Resource($strResource));
                }
            }
        }
    }

    public function checkPermission($strModuleName, $strControllerName, $strActionName = null) {

        if ($strActionName != null) {
            $strActionName = trim(strtolower($strActionName));
        }
        $strResource = trim(strtolower($strModuleName . ':' . $strControllerName . ':' . $strActionName));
        
        if (!$this->acl->hasResource($strResource)) {
            return false;
        }

        return true;
    }

}
