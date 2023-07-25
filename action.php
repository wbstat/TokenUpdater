<?php

require __DIR__ . '/libraries/vendor/autoload.php';

$action = $_POST['action'];

switch ($action)
{
	case 'get_token':
		$output 		= array();
		
		$token_type 	= @$_POST['token_type'];
		$refresh_token 	= @$_POST['refresh_token'];
		
		if(empty($refresh_token)){
			throw new Exception("Need refresh_token");
		}
		

	    $client = new \GuzzleHttp\Client(['Content-Type' => 'application/json']);
		$response = $client->request(
			'POST',
			'https://token.wbstat.ru/v1/get_token',
			[
				'body' => json_encode(['token_type' => $token_type, 'refresh_token' => $refresh_token])
			]
		);

		$body = $response->getBody();
		$result = json_decode($body, true);
	
		$html = "";
		foreach($result as $key => $val){
			if($html != ""){
				$html .= "<br/>";
			}
			$html .= $key . ": " . $val;
		}
		
		$output['html'] = "<code>".$html."</code>";
		exit(json_encode($output));		
		
		break;



    default:
        throw new Exception("Method not exists");
        break;

}