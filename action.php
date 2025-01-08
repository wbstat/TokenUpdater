<?php

require __DIR__ . '/libraries/vendor/autoload.php';

$action = $_POST['action'];

switch ($action)
{
	case 'get_token':
		$output 		= array();
		
		$device_id 		= @$_POST['device_id'];
		$refresh_token 	= @$_POST['refresh_token'];
		
		if(empty($refresh_token)){
			throw new Exception("Need refresh_token");
		}
		

	    $client = new \GuzzleHttp\Client();
		$response = $client->request(
			'POST',
			'https://token.wbstat.ru/v1/get_token',
			[
				'body' => json_encode(['device_id' => trim($device_id), 'refresh_token' => trim($refresh_token)]),
		        'headers'  => [
			        'Content-Type' => "application/json",
		        ]
			]
		);

		$body = $response->getBody();
		$result = json_decode($body, true);
		
	
		$html = "";
		foreach($result as $key => $val){
			if($html != ""){
				$html .= "<br/><br/>";
			}
			$html .= $key . ": " . $val;
		}
		
		$output['html'] = "<code>".$html."</code>";
		exit(json_encode($output));		
		
		break;


	case 'get_api_token_details':
		$output 		= array();
		
		$wb_api_token 	= @$_POST['wb_api_token'];
		
		if(empty($wb_api_token)){
			throw new Exception("Need API Token");
		}

		
	    $client = new \GuzzleHttp\Client(['Content-Type' => 'application/json']);
		$response = $client->request(
			'POST',
			'https://token.wbstat.ru/v1/get_api_token_details',
			[
				'body' => json_encode(['wb_api_token' => trim($wb_api_token)]),
		        'headers'  => [
			        'Content-Type' => "application/json",
		        ]
			]
		);

		$body = $response->getBody();

		$token_details = json_decode($body, true);

        $is_expired = $token_details['is_expired'];

		$html = "";
		foreach($token_details['token_data'] as $key => $val){
			if($html != ""){
				$html .= "<br/><br/>";
			}
			
			if($is_expired === true && $key == "expire"){
                $html .= "<kbd>".$key . ": " . $val."</kbd>";
			}else{
			    $html .= $key . ": " . $val;
			}
		}
		
		$output['html'] = "<code>".$html."</code>";
		exit(json_encode($output));	

		
		break;



    default:
        throw new Exception("Method not exists");
        break;

}