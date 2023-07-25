<?php
	
	
	class WB_Portal
	{

		private $supplier_id;

		private $access_token;
		
		private $host;
		
		private $client;
		
		private $refresh_token;
		
		private $user_agent = "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/114.0.0.0 Safari/537.36";
		
		private $device = "Macintosh";
		
		private $version = "Google Chrome 114.0";
		
		
	    public function __construct($supplier_id, $access_token, $host = 'https://seller.wildberries.ru/')
	    {
	        $this->supplier_id = $supplier_id;
	        
	        $this->access_token = $access_token;
	        
	        $this->host = trim($host, '/') . '/';
	    }



		public function refresh_cmp_token()
		{
			$this->getClient()->request(
				'OPTIONS',
				'https://passport.wildberries.ru/api/v2/auth/grant'
			);
			

			//Шаг 1
			$response = $this->getClient()->request(
				'POST',
				'https://passport.wildberries.ru/api/v2/auth/grant',
				[
					'body' => '{}',
			        'headers'  => [
				        'cookie' => "WBToken=".$this->refresh_token,
				        'content-type' => "application/json",
				        'origin' => "https://cmp.wildberries.ru",
				        'referer' => "https://cmp.wildberries.ru/",
				        'user-agent' => $this->user_agent
			        ]
				]
			);
			
			
			$body = $response->getBody();
			$json = json_decode($body, true);
			$tmp_refresh_token = $json['token'];

			$params = array(
				"device" => $this->device.", ".$this->version,
				"token" => $tmp_refresh_token,
			);
			
			
			//Шаг 2
			$response = $this->getClient()->request(
				'POST',
				"https://cmp.wildberries.ru/passport/api/v2/auth/login",
				[
					'body' => json_encode($params),
			        'headers'  => [
				        'Cookie' => "WBToken=".$this->refresh_token,
				        'Content-Type' => "application/json",
				        'Host' => "cmp.wildberries.ru",
				        'Origin' => "https://cmp.wildberries.ru",
				        'Referer' => "https://cmp.wildberries.ru/",
				        'User-Agent' => $this->user_agent
			        ]
				]
			);
			
				
			$headers = $response->getHeaders();
	
		
			$cookie 			= (isset($headers['set-cookie'])) ? $headers['set-cookie'][0] : $headers['Set-Cookie'][0];
			$cookie_data 		= explode("; ", $cookie);
			$cookie_name_data 	= explode("=", trim($cookie_data[0]));
			$expire_data 		= explode("=", trim($cookie_data[1]));
			$AccessToken		= $cookie_name_data[1];
			$max_age 			= $expire_data[1];
			$access_expire		= time() + $max_age;
			
	

			$upd_data = array(
				"access_token" => $AccessToken,
				"access_expire" => date("Y-m-d H:i:s", $access_expire),
				"access_domain" => "cmp.wildberries.ru"
			);
			
			return $upd_data;
			
		}




		public function refresh_external_token($domain)
		{
			$this->getClient()->request(
				'OPTIONS',
				'https://passport.wildberries.ru/api/v2/auth/grant'
			);
			
			//Шаг 1
			$response = $this->getClient()->request(
				'POST',
				'https://passport.wildberries.ru/api/v2/auth/grant',
				[
					'body' => '{}',
			        'headers'  => [
				        'Cookie' => "WBToken=".$this->refresh_token,
				        'Content-Type' => "application/json",
				        'Host' => "passport.wildberries.ru",
				        'Origin' => "https://seller.wildberries.ru",
				        'Referer' => "https://seller.wildberries.ru/",
				        'User-Agent' => $this->user_agent
			        ]
				]
			);
			
			
			$body = $response->getBody();
			$json = json_decode($body, true);
			$tmp_refresh_token = $json['token'];

			$params = array(
				"device" => $this->device,
				"token" => $tmp_refresh_token,
				"version" => $this->version
			);
			
			
			$this->getClient()->request(
				'OPTIONS',
				$domain . '/passport/api/v2/auth/login',
				[
			        'headers'  => [
				        'Origin' => "https://seller.wildberries.ru",
				        'Referer' => "https://seller.wildberries.ru/",
				        'User-Agent' => $this->user_agent
			        ]
				]
			);			

			//Шаг 2
			$response = $this->getClient()->request(
				'POST',
				$domain . "/passport/api/v2/auth/login",
				[
				    'version' => 2.0,
					'body' => json_encode($params),
			        'headers'  => [
				        'Cookie' => "WBToken=".$this->refresh_token,
				        'Content-Type' => "application/json",
				        'Origin' => "https://seller.wildberries.ru",
				        'Referer' => "https://seller.wildberries.ru/",
				        'User-Agent' => $this->user_agent
			        ]
				]
			);
			
				
			$headers = $response->getHeaders();
			
		
			$cookie 			= (isset($headers['set-cookie'])) ? $headers['set-cookie'][0] : $headers['Set-Cookie'][0];
			$cookie_data 		= explode("; ", $cookie);
			
			
			
			$access_token 	= "";
			$access_expire 	= "";
			$access_domain 	= str_replace("https://", "", $domain);
			
			foreach($cookie_data as $cookie_block)
			{
				$cookie_value_data = explode("=", trim($cookie_block));
				
				if(count($cookie_value_data)>1){
					$cookie_param_name = trim($cookie_value_data[0]);
					$cookie_param_value = trim($cookie_value_data[1]);
					
					switch($cookie_param_name){
						case "WBToken":
							$AccessToken = $cookie_param_value;
						break;
						case "max-age":
							$access_expire = time() + $cookie_param_value;
						break;
						case "domain":
							$access_domain = $cookie_param_value;
						break;
					}
					
				}
			}

			$upd_data = array(
				"access_token" => $AccessToken,
				"access_expire" => date("Y-m-d H:i:s", $access_expire),
				"access_domain" => $access_domain
			);
			
			return $upd_data;
			
		}
	




		public function refresh_access_token($type = "full")
		{
			$this->getClient()->request(
				'OPTIONS',
				'https://passport.wildberries.ru/api/v2/auth/grant',
				[
			        'version' => 2.0,
			        'headers'  => [
				        'Origin' => "https://seller.wildberries.ru",
				        'Referer' => "https://seller.wildberries.ru/",
				        'User-Agent' => $this->user_agent
			        ]
				]
			);
			

			//Шаг 1
			$response = $this->getClient()->request(
				'POST',
				'https://passport.wildberries.ru/api/v2/auth/grant',
				[
					'version' => 2.0,
					'body' => '{}',
			        'headers'  => [
				        'Cookie' => "WBToken=".$this->refresh_token,
				        'Content-Type' => "application/json",
				        'Origin' => "https://seller.wildberries.ru",
				        'Referer' => "https://seller.wildberries.ru/",
				        'User-Agent' => $this->user_agent
			        ]
				]
			);
			
			
			$body = $response->getBody();
			$json = json_decode($body, true);
			$tmp_refresh_token = $json['token'];

			$params = array(
				"device" =>$this->device,
				"token" => $tmp_refresh_token,
				"version" => $this->version
			);
			


			//Шаг 2
			$response = $this->getClient()->request(
				'POST',
				"https://seller.wildberries.ru/passport/api/v2/auth/login",
				[
					'body' => json_encode($params),
			        'headers'  => [
				        'Content-Type' => "application/json",
				        'Origin' => "https://seller.wildberries.ru",
				        'Referer' => "https://seller.wildberries.ru/",
				        'User-Agent' => $this->user_agent
			        ]
				]
			);
			
				
			$headers = $response->getHeaders();
	
		
			$cookie 			= (isset($headers['set-cookie'])) ? $headers['set-cookie'][0] : $headers['Set-Cookie'][0];
			$cookie_data 		= explode("; ", $cookie);
			$cookie_name_data 	= explode("=", trim($cookie_data[0]));
			$expire_data 		= explode("=", trim($cookie_data[1]));
			$AccessToken		= $cookie_name_data[1];
			$max_age 			= $expire_data[1];
			$access_expire		= time() + $max_age;
			
			
			
			//Шаг 3
			$response = $this->getClient()->request(
				'POST',
				"https://seller.wildberries.ru/passport/api/v2/auth/grant",
				[
					'body' => '{}',
			        'headers'  => [
				        'Cookie' => "WBToken=" . $AccessToken,
				        'Content-Type' => "application/json",
				        'Origin' => "https://seller.wildberries.ru",
				        'Referer' => "https://seller.wildberries.ru/",
				        'User-Agent' => $this->user_agent
			        ]
				]
			);
			

			$upd_data = array(
				"access_token" => $AccessToken,
				"access_expire" => date("Y-m-d H:i:s", $access_expire),
			);


			
			$body = $response->getBody();
			$json = json_decode($body, true);


			$params['token'] = $json['token'];


			$this->getClient()->request(
				'OPTIONS',
				'https://passport.wildberries.ru/api/v2/auth/login',
				[
					'version' => 2.0,
			        'headers'  => [
				        'Origin' => "https://seller.wildberries.ru",
				        'Referer' => "https://seller.wildberries.ru/",
				        'User-Agent' => $this->user_agent
			        ]
				]
			);			

			if($type == "full"){

				//Шаг 4;
				$response = $this->getClient()->request(
					'POST',
					"https://passport.wildberries.ru/api/v2/auth/login",
					[
						'version' => 2.0,
						'body' => json_encode($params),
				        'headers'  => [
					        'Cookie' => "WBToken=".$this->refresh_token,
					        'Content-Type' => "application/json",
					        'Origin' => "https://seller.wildberries.ru",
					        'Referer' => "https://seller.wildberries.ru/",
					        'User-Agent' => $this->user_agent
				        ]
					]
				);
	
				$headers = $response->getHeaders();
	
	
				$cookie 			= (isset($headers['set-cookie'])) ? $headers['set-cookie'][0] : $headers['Set-Cookie'][0];
				$cookie_data 		= explode("; ", $cookie);
				$cookie_name_data 	= explode("=", trim($cookie_data[0]));
				$expire_data 		= explode("=", trim($cookie_data[1]));
				$RefreshToken		= $cookie_name_data[1];
				$max_age 			= $expire_data[1];
				$refresh_expire		= time() + $max_age;


				$upd_data["refresh_token"] = $RefreshToken;
				$upd_data["refresh_expire"] = date("Y-m-d H:i:s", $refresh_expire);

			}
			
			return $upd_data;
			
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
		

	    protected function request($method, $uri = '', $options = null)
	    {
			try {
			    $response 			= $this->getClient()->request($method, $uri, $options);
				$responseBody 		= $response->getBody();
				$responseContents 	= $responseBody->getContents();

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