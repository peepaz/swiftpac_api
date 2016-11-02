<?php
namespace lib\CargoTrack;

use lib\CargoTrack\DataTypes\CargoTrackCreateInvoiceRequest;
use lib\CargoTrack\DataTypes\CargoTrackCreateInvoiceResponse;
use lib\CargoTrack\DataTypes\CargoTrackGetAllCustInvoiceRequest;
use lib\CargoTrack\DataTypes\CargoTrackGetAllCustInvoiceResponse;
use lib\CargoTrack\DataTypes\CargoTrackGetAllWarehouseRequest;
use lib\CargoTrack\DataTypes\CargoTrackGetInvoiceDataRequest;
use lib\CargoTrack\DataTypes\CargoTrackGetTrackingRequest;
use lib\CargoTrack\DataTypes\CargoTrackGetTrackingResponse;
use lib\CargoTrack\DataTypes\CargoTrackGetUnknownPackagesRequest;
use lib\CargoTrack\DataTypes\CargoTrackGetUnknownPackagesResponse;
use lib\CargoTrack\DataTypes\CargoTrackPrealertRequest;
use lib\CargoTrack\DataTypes\CargoTrackPrealertResponse;
use lib\CargoTrack\DataTypes\CargoTrackUpdateInvoicePayRequest;
use lib\CargoTrack\DataTypes\CargoTrackGetAllWarehouseResponse;
use lib\CargoTrack\DataTypes\CargoTrackUpdateInvoicePayResponse;

// use lib\SwiftPacSoapClient;

ini_set ( "soap.wsdl_cache_enabled", "0" );

class CargoTrackApiClient {
	
	public $soapClient;
	public static $serverNonce;
	public static $authentication;
	
	function __construct($wsdl,$authentication) {
		$this->soapClient = new \SoapClient( $wsdl, array (
				"trace" => 1,
				"exceptions" => 1 
		) );
		
		self::$authentication = $authentication;
			
	}
	
	public function setDigestAuthHeader ($username = null, $password = null, $nonce = null){
		
		$namespace = "http://soap-authentication.org/basic/2001/10/";
		$realm = $_SERVER["HTTP_HOST"];
		$serverNonce = $nonce;
		$secret = md5($username . ":". md5($password). ":".$realm);
		$auth = md5($secret. ":". $serverNonce);
		
		$objSoapVarAuth = new \SoapVar($auth, XSD_STRING,NULL,$namespace, NULL,$namespace);
		$objSoapVarNonce = new \SoapVar($serverNonce, XSD_STRING,NULL,$namespace, NULL,$namespace);
		$objSoapVarUsername = new \SoapVar($username, XSD_STRING,NULL,$namespace, NULL,$namespace);
		$objSoapVarRealm = new \SoapVar($realm, XSD_STRING,NULL,$namespace, NULL,$namespace);
		
		$objClientAuth = new \stdClass();
		$objClientAuth->Nonce = $objSoapVarNonce;
		$objClientAuth->Auth = $objSoapVarAuth;
		$objClientAuth->Username = $objSoapVarUsername;
		$objClientAuth->Realm = $objSoapVarRealm;
		
		$objSoapVarClientAuth = new \SoapVar($objClientAuth, SOAP_ENC_OBJECT,NULL,$namespace,"ClientAuth",$namespace);
		$digestAuthHeader = new \SoapHeader($namespace, "ClientAuth",$objSoapVarClientAuth,true);
		$this->soapClient->__setSoapHeaders(array($digestAuthHeader));
		
		
	}
	// ===============Api Funcitons============
	/**
	 *Get Unknown Packges from CargoTrack
	 * @param CargoTrackGetUnknownPackagesRequest $cargoTrackGetUnknownPackagesRequest
	 * @return CargoTrackGetUnknownPackagesResponse     	
	 */
	public function CargoTrackGetUnknownPackages(CargoTrackGetUnknownPackagesRequest $cargoTrackGetUnknownPackagesRequest) {
		
// 		$this->validateSwiftPacRateRequest($swiftPacRate);
		
		
// 		var_dump($this->soapClient->__getFunctions());
// 		var_dump($swiftPacRate);
		$response =  $this->soapClient->CargoTrackGetUnknownPackages ( $cargoTrackGetUnknownPackagesRequest);
				
// 		if($response->rateResponse->errors){
// 			$serverNonce = $response->rateResponse->errors->errorDetails->nonce;
// 			$this->setDigestAuthHeader(self::$authentication['username'],self::$authentication["password"],$serverNonce);
// 			$response =  $this->soapClient->SwiftPacRate ( $swiftPacRate);
		
// 		}
// 		var_dump($response->rateResponse->errors->errorDetails);
		return $response;
		
	}
	
	/**
	 * Get Tracking Details on a warehouse 
	 * @param CargoTrackGetTrackingRequest $cargoTrackGetTrackingRequest
	 * @return CargoTrackGetTrackingResponse
	 */
	public function CargoTrackGetTracking (CargoTrackGetTrackingRequest $cargoTrackGetTrackingRequest){
		
		$response = $this->soapClient->CargoTrackGetTracking($cargoTrackGetTrackingRequest);
		
		return $response;
		
	}
	/**
	 * Create invoice on CargoTrack
	 * @param CargoTrackCreateInvoiceRequest $cargoTrackCreateInvoiceRequest
	 * @return CargoTrackCreateInvoiceResponse
	 */
	public function CargoTrackCreateInvoice (CargoTrackCreateInvoiceRequest $cargoTrackCreateInvoiceRequest){		
		$response = $this->soapClient->CargoTrackCreateInvoice($cargoTrackCreateInvoiceRequest);
		
		return $response;
		
	}
	/**
	 * 
	 * @param CargoTrackUpdateInvoicePayRequest $cargoTrackUpdateInvoicePayRequest
	 * @return CargoTrackUpdateInvoicePayResponse
	 */
	public function CargoTrackUpdateInvoicePay (CargoTrackUpdateInvoicePayRequest $cargoTrackUpdateInvoicePayRequest){
		
		$response = $this->soapClient->CargoTrackUpdateInvoicePay($cargoTrackUpdateInvoicePayRequest);
		return $response;
		
	}
	public function CargoTrackGetInvoiceData (CargoTrackGetInvoiceDataRequest $cargoTrackGetInvoiceDataRequest){
		
		$response = $this->soapClient->CargoTrackGetInvoiceData($cargoTrackGetInvoiceDataRequest);
		return $response;
		
	}
	
	/**
	 * Get warehousees for a specified customer
	 * @param CargoTrackGetAllWarehouseRequest $cargoTrackGetAllWarehouseRequest
	 * @return CargoTrackGetAllWarehouseResponse
	 */
	public function CargoTrackGetAllWarehouse (CargoTrackGetAllWarehouseRequest $cargoTrackGetAllWarehouseRequest){
		
		$response = $this->soapClient->CargoTrackGetAllWarehouse($cargoTrackGetAllWarehouseRequest);
		return $response;
		
	}
	/**
	 * Get All Customer Invoices from CargoTrack
	 * @param CargoTrackGetAllCustInvoiceRequest $cargoTrackGetAllCustInvoiceRequest
	 * @return CargoTrackGetAllCustInvoiceResponse
	 */
	public function CargoTrackGetAllCustInvoice ($cargoTrackGetAllCustInvoiceRequest){
		
		$response = $this->soapClient->CargoTrackGetAllCustInvoice($cargoTrackGetAllCustInvoiceRequest);
		return $response;
		
	}
	/**
	 * 
	 * @param CargoTrackPrealertRequest $cargoTrackPrealeartRequest
	 * @return CargoTrackPrealertResponse
	 */
	public function CargoTrackPrealeart($cargoTrackPrealeartRequest){
		
		$response = $this->soapClient->CargoTrackPrealert($cargoTrackPrealeartRequest);
		return $response;
		
		
	}
	
	
}