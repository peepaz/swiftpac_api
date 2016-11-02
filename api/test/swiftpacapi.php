<?php

/*
 * Definition of responses for each function.
 */
class getDateResponse {
	public $return;
}
class getRateResponse {
	public $none;
}
class sendAckResponse {
	public $return;
}

/**
 * Functions to be execued by users of the API
 *
 * @author SwiftPac Logistics
 *        
 */
class SwiftPacApi {
	public static $rates = array (
			
			"swiftpac" => array (
					"regular" => "1.5",
					"express" => "15.5" 
			),
			"dhl" => array (
					"worldwide" => "20" 
			),
			"usps" => array (
					"priority" => "5.05",
					"priority express" => "10.5" 
			) 
	);
	public static $credentials = array (
			
			"jason" => "lovelove1",
			"andy" => "lovelove2" 
	);
	public function getDate() {
		$response = new getDateResponse ();
		$response->return = "Today's date is " . date ( 'Y-m-d H:i:s' );
		return $response;
	}
	public function sendAck($params) {
		$message = $params->message;
		$response = new sendAckResponse ();
		$response->return = "Message recieved :" . $message . " at " . date ( 'Y-m-d H:i:s' );
		return $response;
	}
	public function demoRate($params) {
		if (! isset ( $params->carrier ))
			throw new SoapFault ( "401", "Carrier is missing" );
		$authenticate = $this->Authenticate ( $params->login );
		if ($authenticate) {
			$demoRateResponse = new stdClass ();
			$demoRateResponse->rate0 = $params->carrier . '5.50';
			$demoRateResponse->rate1 = $params->carrier . '3.50';
			
			return $demoRateResponse;
		}
	}
	public function getSwiftPacRate($params) {
		$swiftPacRates = self::$rates ['swiftpac'];
		$obj = new stdClass ();
		
		// $obj->express = $swiftPacRates['express'];
		// $obj->regular = $swiftPacRates['regular'];
		// return $obj;
		if (isset ( $params->rateName )) {
			
			$rateName = $params->rateName;
			switch ($rateName) {
				
				case 'regular' :
					$obj->$rateName = $swiftPacRates [$rateName];
					break;
				case 'express' :
					$obj->$rateName = $swiftPacRates [$rateName];
					break;
				default :
					
					throw new SoapFault ( "rate name not found", 401 );
			}
		} else {
			$obj->regular = $swiftPacRates ['regular'];
			$obj->express = $swiftPacRates ['express'];
		}
		return $obj;
	}
	public function getRate($params) {
		if (isset ( $params->carrier )) {
			
			$carrier = $params->carrier;
			// $response = new getRateResponse ();
			if (isset ( self::$rates [$carrier] )) {
				
				// foreach ( self::$rates [$carrier] as $carrier_name => $method ) {
				
				// $response->return->rate0 = $method;
				// }
				
				// $response->rate0 = "0";
				// $response->rate1 = "20";
				// $response->none = "dsfasd";
				
				$obj = new stdClass ();
				$obj->ratetwo = "20";
				$obj->return = "20";
				$obj->rateone = "10";
				
				return $obj;
			} else
				throw new SoapFault ( 401, "Carrier not found" );
			
			// $obj = new stdClass();
			// $obj->fname = "SwiftPac";
			// $obj->lname = "demo";
			// $obj->mname = "demo";
			
			// $arr = array("fname" => "test");
			
			// return $arr;
		} else {
			throw new SoapFault ( 401, "Carrier is not supplied." );
		}
	}
	/**
	 * Authenticates the SOAP request.
	 * (This one is the key to the authentication, it will be called upon the server request)
	 *
	 * @param
	 *        	array
	 * @return array
	 */
	public function Authenticate($params) {
		if (! empty ( $params->username ) && ! empty ( $params->password )) {
			
			$found = false;
			foreach ( self::$credentials as $username => $password ) {
				
				if ($username == $params->username && $password == $params->password) {
					$found = true;
					break;
				}
			}
			// $query = "SELECT authentication_id FROM authentication WHERE username = ? AND password = ?";
			
			// add your own auth code here. I have it check against a database table and return a value if found.
			
			if ($found) {
				
				$obj = new stdClass ();
				$obj->authenticate = true;
				return $obj;
			} else {
				
				throw new SOAPFault ( "401", "Incorrect username and or password." );
			}
		} else {
			
			throw new SOAPFault ( "401", "Invalid username and password format. Values may not be empty and are case-sensitive." );
		}
	}
}
$soap_server = new SoapServer ( 'DemoWSDL.wsdl', array (
		'cache_wsdl' => WSDL_CACHE_NONE 
) );
$soap_server->setClass ( SwiftPacApi );
$soap_server->handle ();

?>