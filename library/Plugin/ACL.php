<?php

/**
 * This is just basic sketch of ACL on resources which are controllers
 *  in this implementation, however it can be expanded
 */
Class Plugin_ACL extends Zend_Controller_Plugin_Abstract
{

    protected $__acl;
    protected $__auth;
    protected $currentRole;
    
    protected $resources            = [
        'artykuly',
        'auth'
    ];

    protected $restrictedResources  = [
        self::ADMIN => 'adder'
    ];

    const GUEST = 'guest';
    const USER  = 'user';
    const ADMIN = 'admin';
    
    protected function initACL(){
        $this->setRoles();
        $this->setResources();
        $this->setPriviledges();
        $this->setCurrentRole();
    }
    
    protected function setRoles(){
        $this->__acl->addRole(new Zend_Acl_Role(self::GUEST));
        $this->__acl->addRole(new Zend_Acl_Role(self::USER), self::GUEST);
        $this->__acl->addRole(new Zend_Acl_Role(self::ADMIN));        
    }
    
    protected function setResources(){
        foreach ($this->restrictedResources as $resource) {
            $this->__acl->addResource(new Zend_Acl_Resource($resource));
        }
    }
    
    protected function setPriviledges(){
        foreach ($this->restrictedResources as $role => $resource) {
            $this->__acl->allow($role, $resource);
        }
    }

    protected function isResource($r){
        $resources = array_merge($this->resources, $this->restrictedResources);
        return in_array($r, $resources);
    }
    
    protected function isRestrictedResource($r){
        return in_array($r, $this->restrictedResources);
    }
    
    protected function setCurrentRole () {
        if ($this->__auth->hasIdentity()) {
            $role = $this->__auth->getIdentity()->role;
            if (
               ($role === self::GUEST)  xor
               ($role === self::USER)   xor
               ($role === self::ADMIN)
               ) {
                $this->currentRole = $role;
            } else {
                $this->currentRole = self::GUEST;
            }
        } 
    }

    public function __construct(){
        $this->__acl    = new Zend_Acl();
        $this->__auth   = Zend_Auth::getInstance();
        $this->initACL();
    }    
    
    public function preDispatch(Zend_Controller_Request_Abstract $request)
    {
        $controller = $request->getControllerName();
        $action     = $request->getActionName();
        if ($this->isRestrictedResource($controller)) {
            if (!$this->__acl->isAllowed($this->currentRole, $controller)) {
                $redir = Zend_Controller_Action_HelperBroker::getStaticHelper('Redirector');         
                $redir->gotoUrl('/error/unpriviledged');
            }
        }
    }


}

?>
