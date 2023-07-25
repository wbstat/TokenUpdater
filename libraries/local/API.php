<?php

	abstract class API {

	    protected $method = '';

	    protected $endpoint = '';

	    protected $args = Array();
	    
	    public $request = array();
	    
	    public $headers = array();
	        
	    public function __construct()
	    {
	        $this->args = explode('/', rtrim($_REQUEST['request'], '/'));
	        $this->endpoint = array_shift($this->args);
	        $this->method = $_SERVER['REQUEST_METHOD'];
	    }
	    
	    public function processAPI()
	    {
			$this->headers = getallheaders();
	        switch ($this->method) {
	            case 'POST':
	                if(strstr($this->headers['Content-Type'], "application/x-www-form-urlencoded")){
		                $this->request = $this->_prepare_vars($_POST);
	                }else{
		                $entityBody = file_get_contents('php://input');
		                $post_data = json_decode($entityBody, true);
		                $this->request = $this->_prepare_vars($post_data);
	                }
	                
					break;
	            
	            case 'GET':
	                $this->request = $this->_prepare_vars($_GET);
					break;
	            
	            default:
	                return $this->_response(array("code" => 405, "body" => array("error" => "Method not allowed")));
					break;
	        }
	
	        unset($this->request['request']);
	
	        if (!method_exists($this, $this->endpoint)) {
	            return $this->_response(
	                array("code" => 404, "body" => array("error" => "No Endpoint: $this->endpoint"))
	            );
	        }
	
	        return $this->_response($this->{$this->endpoint}());
	    }
	
	    private function _prepare_vars($data) {
	    	$result = array();
	    	foreach ($data as $key => $value) {
	    		$result[urldecode($key)] = urldecode($value);
	    	}
	
	    	return $result;
	    }
	
	    private function _response($data) {
	        header($_SERVER["SERVER_PROTOCOL"] . " " . $data["code"] . " " . $this->_requestStatus($data["code"]));
	        return json_encode($data["body"]);
	    }
	
	    private function _requestStatus($code) {
	        $status = array(  
	            200 => 'OK',
	            401 => 'Unauthorized',
	            404 => 'Not Found',   
	            405 => 'Method Not Allowed',
	            500 => 'Internal Server Error',
	        ); 
	        return ($status[$code])?$status[$code]:$status[500]; 
	    }
	}