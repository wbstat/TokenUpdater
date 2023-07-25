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
			
			$token_type = @$this->request['token_type'];
			

			$check_domains 	= array("seller" => "seller.wildberries.ru", "supply" => "seller-supply.wildberries.ru", "weekly-report" => "seller-weekly-report.wildberries.ru", "cmp" => "cmp.wildberries.ru");
			$check_domain 	= (array_key_exists($token_type, $check_domains)) ? $check_domains[$token_type] : $check_domains["seller"];


	
			$portal = new WB_Portal(null, null);
			$portal->set_refresh_token($this->request['refresh_token']);
			
			
			switch($token_type){
				case "seller":
					$result = $portal->refresh_access_token();
					break;
				case "cmp":
					$result = $portal->refresh_cmp_token();
					break;
				default:
					$result = $portal->refresh_external_token("https://" . $check_domain);
					break;
			}
			
			
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
