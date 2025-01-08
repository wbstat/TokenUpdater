<?php
	
	
	class WB_Portal
	{

		private $validation_key;

		private $access_token;
		
		private $host;
		
		private $client;
		
		private $refresh_token;
		
		private $device_id;
		
		private $user_agent = "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/114.0.0.0 Safari/537.36";
		

		
		
	    public function __construct($access_token = "", $validation_key = "", $host = 'https://seller.wildberries.ru/')
	    {
	        $this->validation_key 	= $validation_key;
	        
	        $this->access_token 	= $access_token;
	        
	        $this->host = trim($host, '/') . '/';
	    }



		public function get_user_suppliers()
		{
			$params = array(
				array("method" => "getUserSuppliers", "params" => (object)array(), "id" => "json-rpc_3", "jsonrpc" => "2.0"),
				//array("method" => "listCountries",  "params" => (object)array(), "id" => "json-rpc_4", "jsonrpc" => "2.0")	
			);

			$response = $this->request(
				'POST',
				'/ns/suppliers/suppliers-portal-core/suppliers',
				[
					'body' => json_encode($params),
			        'headers'  => [
				        'Cookie' => "WBTokenV3=".$this->access_token."; wbx-validation-key=".$this->validation_key,
				        'Content-Type' => "application/json"
			        ]
				]
			);
			return $response;
		}

	



		public function upgrade_access_token()
		{
			$response = $this->getClient()->request(
				'POST',
				'https://seller.wildberries.ru/upgrade-cookie-authv3',
				[
					'version' => 2.0,
			        'headers'  => [
				        'Authorizev3' => $this->access_token,
				        'Cookie' => "WBTokenV3=".$this->access_token,
				        'Content-Type' => "application/json",
				        'Origin' => "https://seller.wildberries.ru",
				        'Referer' => "https://seller.wildberries.ru/",
				        'User-Agent' => $this->user_agent
			        ]
				]
			);
			
			$body = $response->getBody();
			$json = json_decode($body, true);

			
			$headers = $response->getHeaders();
			
			$cookie 			= (isset($headers['set-cookie'])) ? $headers['set-cookie'][0] : $headers['Set-Cookie'][0];
			$cookie_data 		= explode("; ", $cookie);
			$cookie_name_data 	= explode("=", trim($cookie_data[0]));
			$AccessToken		= $cookie_name_data[1];
			
			$cookie_data 		= explode(";", $AccessToken);
			
			$AccessToken 		= $cookie_data[0];

			$upd_data = array(
				"wb_token_v3" => $AccessToken,
			);
			
			return $upd_data;
		}
	




		public function refresh_access_token()
		{
			$cookies = [
				"wbx-seller-device-id=".$this->device_id,
				"external-locale=ru",
				"wbx-refresh=".$this->refresh_token
			];
			
			
			//echo "Step1 \n";
			$response = $this->getClient()->request(
				'POST',
				'https://seller-auth.wildberries.ru/auth/v2/auth/slide-v3',
				[
					'version' => 2.0,
			        'headers'  => [
				        'Cookie' => "wbx-seller-device-id=".$this->device_id."; external-locale=ru; wbx-refresh=".$this->refresh_token,
				        'Content-Type' => "application/json",
				        'Origin' => "https://seller.wildberries.ru",
				        'Referer' => "https://seller.wildberries.ru/",
				        'User-Agent' => $this->user_agent
			        ]
				]
			);
			
            
			$body = $response->getBody();
			$json = json_decode($body, true);
			

			$payload 			= $json['payload'];
			
		
			$AccessToken 	= $payload['access_token'];
			$Sticker 		= $payload['sticker'];

			$headers = $response->getHeaders();
			

			$response_cookies 	= (isset($headers['set-cookie'])) ? $headers['set-cookie'] : $headers['Set-Cookie'];


			$RefreshToken = "";
			$ValidationKey = "";
			
			foreach($response_cookies as $cookie)
			{

				$cookie_data 		= explode("; ", $cookie);

				$cookie_name_data 	= explode("=", trim($cookie_data[0]));
                
                foreach($cookie_data as $cookie_arr)
                {
                    $cookie_arr_data = explode("=", trim($cookie_arr));
                    if($cookie_arr_data[0] == "Expires"){
    					$max_age = $cookie_arr_data[1];                        
                    }
                }

				if($cookie_name_data[0] == "wbx-refresh"){
					$RefreshToken = $cookie_name_data[1];
				}
				
				if($cookie_name_data[0] == "wbx-validation-key"){
					$ValidationKey = $cookie_name_data[1];

					if(is_numeric($max_age)){
						$tmp_date		= time() + $max_age;
						$wbx_expire 	= date("Y-m-d H:i:s", $tmp_date);
					}else{
						$tmp_date = new DateTime($max_age);
						$wbx_expire = $tmp_date->format("Y-m-d H:i:s");
					}
				}

			}


			$cookies = [
				"external-locale=ru",
				"wbx-seller-device-id=".$this->device_id,
				"wbx-refresh=".$RefreshToken,
				"wbx-validation-key=".$ValidationKey
			];
			
			$params = [
				"sticker" => $Sticker
			];

			

			//echo "Step2 \n";
			$response = $this->getClient()->request(
				'POST',
				'https://seller-auth.wildberries.ru/auth/v2/auth/slide-v3-confirm',
				[
					'version' => 2.0,
					'body' => json_encode($params),
			        'headers'  => [
				        'Cookie' => implode("; ", $cookies),
				        'Content-Type' => "application/json",
				        'Origin' => "https://seller.wildberries.ru",
				        'Referer' => "https://seller.wildberries.ru/",
				        'User-Agent' => $this->user_agent
			        ]
				]
			);
			
				
			$upd_data = array(
				"wb_token_v3" => $AccessToken,
				"wbx_refresh" => $RefreshToken,
				"wbx_validation_key" => $ValidationKey,
				"expire" => $wbx_expire
			);
			

			return $upd_data;
			
		}

		public function set_device_id($device_id)
		{
			$this->device_id = $device_id;
		}


		public function set_refresh_token($refresh_token)
		{
			$this->refresh_token = $refresh_token;
		}
	
				
	    protected function getClient()
	    {
	        if ($this->client === null) {
	            $this->client = new \GuzzleHttp\Client([
	                'base_uri' => $this->host,
	                'cookies' => true
	            ]);
	        }
	        return $this->client;
	    }
		

	    protected function request($method, $uri = '', $options = null, $parseResultAsJson = true)
	    {
			try {
			    $response = $this->getClient()->request($method, $uri, $options);
				$responseBody = $response->getBody();
				$responseContents 	= $responseBody->getContents();
				$statusCode 		= $response->getStatusCode();
				
	            if (!$parseResultAsJson) {
	                return $responseContents;
	            }

	            $arr = json_decode($responseContents, true);
	            if (JSON_ERROR_NONE !== json_last_error()) {
		            return false;
	            }
	
	            return $arr;
	            
			} catch (\GuzzleHttp\Exception\ClientException $e) {
				if ($e->hasResponse()){
					$response 		= $e->getResponse();
					$statusCode 	= $response->getStatusCode();
					$responseBody 	= $response->getBody()->getContents();
				
		            $arr = json_decode($responseBody, true);
		            if (JSON_ERROR_NONE !== json_last_error()) {
		                return false;
		            }
		            
		            return $arr;
		            
				}
				return false;				
			}
        }
	}


//eof