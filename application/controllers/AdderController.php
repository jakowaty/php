<?php

class AdderController extends Zend_Controller_Action implements Zend_Acl_Resource_Interface
{

    public static $_resourceId = __CLASS__;
    
    
    public function getResourceId(){
        return self::$_resourceId;
    }
    
    protected $http;
    
    public function init()
    {
        $this->http = new Zend_Controller_Request_Http();
    }

    public function addtagAction()
    {
        
        if($this->http->isPost()){
            
            try{
            
                $validator = new Jak_PCREValidator();
                $dummy = new Jak_DummyObj();
                $params = $this->getRequest()->getPost();
                
                if(!$dummy->array_keys_exists( $params, array('symbol','nazwa','opis'), true )){
                    throw new Exception('Błąd parametr&oacute;w żądania');
                }
                
                if(!$validator->validateTag($params['symbol'])){
                    throw new Exception('Nieprawidłowy symbol!');
                }
                
                $tags = new Application_Model_DbTable_Tags();
                $data = array(
                    'tags_id' => $params['symbol'],
                    'name' => $params['nazwa'],
                    'description' => $params['opis']
                );
                
                $tags->insert($data);
                
                $this->view->status = "Dodano kategorię tematyczną o tytule: \"" . $dummy->unhtml($params['nazwa']) . '"';
            }catch(Exception $ex){
            
                $this->view->status = $ex->getMessage();
            
            }
        }
    }

    public function addarticleAction()
    {
        
        $validator = new Jak_PCREValidator();
        $tags = new Application_Model_DbTable_Tags();
        $this->view->tags = $tags->selectColumns(array('tags_id', 'name'));
        
        if($this->http->isPost()){
            
            if(
                    !$this->http->getParam('title') OR
                    !$this->http->getParam('symbol') OR
                    !$this->http->getParam('text')
              ){
                
                $this->view->errors = array();
                $this->view->errors[] = 'Błąd parametrów żądania';
                return false;
            
                
            }else{
                
                if(!$validator->validateArticleTitle($this->http->getParam('title'))){
                    
                    $this->view->errors = array();
                    $this->view->errors[] = 'Błąd parametrów żądania';                    
                    return false;
                    
                }else{
                    $title = $this->http->getParam('title');
                }
                        
                $id     = sha1($title);
                $symbol = $this->http->getParam('symbol');
                $text   = $this->http->getParam('text');
                //if(strlen())
                $arts = new Application_Model_DbTable_Articles();
                $arts->insertArticle([
                    'tags_id'       => $symbol,
                    'id'            => $id,
                    'title'         => $title,
                    'autor'         => 'n_r',
                    'text'          => $text
                ]);
                
            }
            
        }
   
    }


}





