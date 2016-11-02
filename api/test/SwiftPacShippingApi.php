<?php
require_once 'DataTypes.php';
require_once 'Helpers.php';
/*
 * Response Classes
 */
// ===================API Response Fucntions============================
class SwiftPacRateRateResponse {
	/**
	 *
	 * @var RateResponse
	 */
	public $rateResponse = null;
}
class RateResponse {
	
	/**
	 *
	 * @var RateResult
	 */
	public $rateResult = null;
}
// ==================End of API Response Functions=======================
class SwiftPacShippingApi {
	
	//==============API Request Functions================================
	function SwiftPacRate($params) {
		
		var_dump($params);
		die();
		$swiftPacRateResponse = new SwiftPacRateRateResponse ();
		
		
		$service = new Service();
		$service->carrier = "SwiftPac";
		$service->serviceCode = "001";
		$service->serviceDesc = "Small Box";
		
		$service1 = new Service();
		$service1->carrier = "SwiftPac";
		$service1->serviceCode = "002";
		$service1->serviceDesc = "Express";
		
		$service2 = new Service();
		$service2->carrier = "SwiftPac";
		$service2->serviceCode = "003";
		$service2->serviceDesc ="Ocean";
		
		$swiftPacRateResponse->rateResponse->rateResult->serviceList[] = $service;
		$swiftPacRateResponse->rateResponse->rateResult->serviceList[] = $service1;
		$swiftPacRateResponse->rateResponse->rateResult->serviceList[] = $service2;
		
		return $swiftPacRateResponse;
	}
	function MyRate($params) {
		$obj = new stdClass ();
		$obj->TestResponse = "twest";
		return $obj;
	}
	//===============End of API Functions
	
	//==============Helper Functions==================
}

$soap_server = new SoapServer ( 'SwiftPacShippingWDSL.wsdl', array (
		'cache_wsdl' => WSDL_CACHE_NONE 
) );
$soap_server->setClass ( SwiftPacShippingApi );
$soap_server->handle ();

?>