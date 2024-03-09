<?php

	ini_set('display_errors', 0);
	ini_set('display_startup_errors', 0);
	error_reporting(0);
	
	require __DIR__ . '/libraries/vendor/autoload.php';


	
	class RefreshAPI extends API
	{
		protected function get_token()
		{
			
			if(empty($this->request['refresh_token'])){
		        return array(
		            "code" => 400,
		            "body" => "Error: need refresh_token"
				);
			}

			if(empty($this->request['device_id'])){
		        return array(
		            "code" => 400,
		            "body" => "Error: need device_id"
				);
			}

	
			$portal = new WB_Portal();
			
			$portal->set_refresh_token($this->request['refresh_token']);
			
			$portal->set_device_id($this->request['device_id']);
			
			$result = $portal->refresh_access_token();
			

			
	        return array(
	            "code" => 200,
	            "body" => $result
			);
		}
	
	
	}
	
	header("Access-Control-Allow-Orgin: *");
	header("Access-Control-Allow-Methods: *");
	header("Content-type: application/json; charset=utf-8;");
	
	$API = new RefreshAPI();
	echo $API->processAPI();
