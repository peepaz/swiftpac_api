<?php
/**
 * Class was generated by WSDL2PHP tool
 * Timestamp: 1371735568 
 *
 * PHP version 5
 * 
 * @category tools
 * @package  WSDL
 * @author   Andrey Filippov <afi.work@gmail.com>
 * @license  %license%
 * @version  SVN: $Id: TemplateWebService.php 9 2007-12-25 11:26:03Z afi $
 * @link     https://solo-framework-lib.googlecode.com
 */
namespace lib\IABOL;
use SoapClient;
use nusoap_client;
require_once "Helpers.php";
require_once "nusoap.php";

class MySoapClient extends SoapClient { 

  public function __construct($wsdl, $options = null) {
    $this->client = new nusoap_client($wsdl, 'wsdl');
    $this->options = $options;
    $err = $this->client->getError();
    if ($err) {
      throw new Exception($err);
    }
    $this->client->setUseCurl(true);
    $this->client->loadWSDL();
  }

  public function __soapCall($function_name, $arguments, $options = NULL, $input_headers = NULL, &$output_headers = NULL) {
    $result = $this->client->call($function_name, $arguments);
    if (isset($this->options['debug'] ) and $this->options['debug'] ) {
      echo '<h2>Request</h2><pre>' . htmlspecialchars($this->client->request, ENT_QUOTES) . '</pre>';
      echo '<h2>Response</h2><pre>' . htmlspecialchars($this->client->response, ENT_QUOTES) . '</pre>';
      // echo '<h2>Debug</h2><pre>' . htmlspecialchars($this->client->getDebug(), ENT_QUOTES) . '</pre>';
      if ($this->client->fault) {
        echo '<h2>Fault</h2><pre>';
        print_r($result);
        echo '</pre>';
      } else {
        // Check for errors
        $err = $this->client->getError();
        if ($err) {
          // Display the error
          echo '<h2>Error</h2><pre>' . $err . '</pre>';
        } else {
          // Display the result
          echo '<h2>Result</h2><pre>';
          print_r($result);
          echo '</pre>';
        }
      }
    }
//     var_dump($result);
    return arrayToObject($result);
//     return $result;
  }

  public function arrayResponse() {
    $xmlNode = simplexml_load_string($this->client->responseData);
    $arrayData = xmlToArray($xmlNode);
    return $arrayData['Envelope']['soap:Body'];
  }

  public function jsonReponse() {
    $xmlNode = simplexml_load_string($this->client->responseData);
    $arrayData = xmlToArray($xmlNode);
    return json_encode($arrayData['Envelope']['soap:Body']);
  }

  public function response(){
    $dom = new DOMDocument('1.0');
    $dom->preserveWhiteSpace = false;
    $dom->formatOutput = true;
    $dom->loadXML($this->client->responseData);
    return $dom->saveXML();
  }
}

class Client
{
	/**
	 * Mapping PHP classes and types from a WSDL
	 * Key - type defined in WSDL, value - PHP class name
	 * To avoid conflicts with another classes we have to add prefix for generated data types 
	 */
    private static $classmap = array(
        'AmountUnit' => 'AmountUnit',
        'RequestTypeEnum' => 'RequestTypeEnum',
        'OrderRequestType' => 'OrderRequestType',
        'CloseoutRequestType' => 'CloseoutRequestType',
        'ClassifyBy' => 'ClassifyBy',
        'Rate_Request' => 'Rate_Request',
        'Authentication' => 'Authentication',
        'Rate' => 'Rate',
        'RequestDetail' => 'RequestDetail',
        'Shipmentlist' => 'Shipmentlist',
        'Shipment' => 'Shipment',
        'Address' => 'Address',
        'DangerousGoodsDetail' => 'DangerousGoodsDetail',
        'ExportLineItems' => 'ExportLineItems',
        'ExportLineItem' => 'ExportLineItem',
        'Packages' => 'Packages',
        'Package' => 'Package',
        'InternationalDetail' => 'InternationalDetail',
        'Rate_Response' => 'Rate_Response',
        'Rate_Result' => 'Rate_Result',
        'error' => 'error',
        'RateResultList' => 'RateResultList',
        'RateResult' => 'RateResult',
        'ArrayOfRateError' => 'ArrayOfRateError',
        'RateError' => 'RateError',
        'Service' => 'Service',
        'ShipmentCharges' => 'ShipmentCharges',
        'FeeDetails' => 'FeeDetails',
        'ArrayOfFeeDetail' => 'ArrayOfFeeDetail',
        'FeeDetail' => 'FeeDetail',
        'AddShipper_Request' => 'AddShipper_Request',
        'Request' => 'Request',
        'User' => 'User',
        'AddShipper_Response' => 'AddShipper_Response',
        'ShipperResult' => 'ShipperResult',
        'ShipperInfo' => 'ShipperInfo',
        'UserInfo' => 'UserInfo',
        'AddVal_Request' => 'AddVal_Request',
        'AddVal_Response' => 'AddVal_Response',
        'AddVal_Result' => 'AddVal_Result',
        'AddressList' => 'AddressList',
        'ArrayOfAddressSuggestList' => 'ArrayOfAddressSuggestList',
        'AddressSuggestList' => 'AddressSuggestList',
        'StreetNumberRange' => 'StreetNumberRange',
        'ZipRange' => 'ZipRange',
        'AbolImportOrderRequest' => 'AbolImportOrderRequest',
        'Order_Request' => 'Order_Request',
        'OrderList' => 'OrderList',
        'OrderV3' => 'OrderV3',
        'LineItemList' => 'LineItemList',
        'OrderItemV3' => 'OrderItemV3',
        'OrderDangerousGoodsDetail' => 'OrderDangerousGoodsDetail',
        'PackagesList' => 'PackagesList',
        'OrderPackage' => 'OrderPackage',
        'OrderInternationalDetail' => 'OrderInternationalDetail',
        'AbolImportOrderResponse' => 'AbolImportOrderResponse',
        'AbolImportOrderResult' => 'AbolImportOrderResult',
        'ImportOrderResult' => 'ImportOrderResult',
        'Errors' => 'Errors',
        'Error' => 'Error',
        'ErrorType' => 'ErrorType',
        'Status' => 'Status',
        'order' => 'order',
        'OrderResponse' => 'OrderResponse',
        'AbolApiShipmentRequest' => 'AbolApiShipmentRequest',
        'AbolApiShipment_Request' => 'AbolApiShipment_Request',
        'AbolApiShipmentResponse' => 'AbolApiShipmentResponse',
        'ShipResult' => 'ShipResult',
        'ShipError' => 'ShipError',
        'Shipment_Response' => 'Shipment_Response',
        'ArrayOfDocument' => 'ArrayOfDocument',
        'Document' => 'Document',
        'ArrayOfPiece' => 'ArrayOfPiece',
        'Piece' => 'Piece',
        'LabelList' => 'LabelList',
        'AbolApiTrackRequest' => 'AbolApiTrackRequest',
        'Track' => 'Track',
        'ArrayOfRequestPackages' => 'ArrayOfRequestPackages',
        'RequestPackages' => 'RequestPackages',
        'AbolApiTrackResponse' => 'AbolApiTrackResponse',
        'TrackResult' => 'TrackResult',
        'TrackError' => 'TrackError',
        'ArrayOfResponsePackage' => 'ArrayOfResponsePackage',
        'ResponsePackage' => 'ResponsePackage',
        'Events' => 'Events',
        'Event' => 'Event',
        'AbolApiVoidRequest' => 'AbolApiVoidRequest',
        'AbolApiVoid_Authentication' => 'AbolApiVoid_Authentication',
        'AbolApiVoid_TrackingNo' => 'AbolApiVoid_TrackingNo',
        'AbolApiVoidResponse' => 'AbolApiVoidResponse',
        'AbolVoidResult' => 'AbolVoidResult',
        'ResponseError' => 'ResponseError',
        'AbolApiCloseOutRequest' => 'AbolApiCloseOutRequest',
        'AbolApiCloseOut_Request' => 'AbolApiCloseOut_Request',
        'Closeout' => 'Closeout',
        'CloseOutTrackingNbrs' => 'CloseOutTrackingNbrs',
        'AbolApiCloseOutResponse' => 'AbolApiCloseOutResponse',
        'AbolCloseoutResult' => 'AbolCloseoutResult',
        'CloseOutError' => 'CloseOutError',
        'CloseOutDetails' => 'CloseOutDetails',
        'DocumentList' => 'DocumentList',
        'AbolClassify_Request' => 'AbolClassify_Request',
        'AbolClassify_Response' => 'AbolClassify_Response',
        'AbolClassifyResult' => 'AbolClassifyResult',
        'CategoriesResultList' => 'CategoriesResultList',
        'ArrayOfCategory' => 'ArrayOfCategory',
        'Category' => 'Category',
        'ArrayOfSubCategory' => 'ArrayOfSubCategory',
        'SubCategory' => 'SubCategory',
        'ArrayOfSubCategoryItem' => 'ArrayOfSubCategoryItem',
        'SubCategoryItem' => 'SubCategoryItem',
        'AbolDuty_Request' => 'AbolDuty_Request',
        'DutyRequestDetail' => 'DutyRequestDetail',
        'ItemSubCategoryItemIds' => 'ItemSubCategoryItemIds',
        'ItemHarmonizedCodes' => 'ItemHarmonizedCodes',
        'ItemDescriptions' => 'ItemDescriptions',
        'ItemCodes' => 'ItemCodes',
        'ItemUnitPrices' => 'ItemUnitPrices',
        'ItemWeights' => 'ItemWeights',
        'ItemQuantities' => 'ItemQuantities',
        'ItemUnitQuantities' => 'ItemUnitQuantities',
        'ItemAmounts' => 'ItemAmounts',
        'ItemCountriesOfManufacture' => 'ItemCountriesOfManufacture',
        'ReferenceNumbers' => 'ReferenceNumbers',
        'AbolDuty_Response' => 'AbolDuty_Response',
        'DutyTaxResult' => 'DutyTaxResult',
        'ArrayOfItemResult' => 'ArrayOfItemResult',
        'ItemResult' => 'ItemResult',
        'CustomsValue' => 'CustomsValue',
        'Amount' => 'Amount',
        'Duty' => 'Duty',
        'SalesTax' => 'SalesTax',
        'AdditionalTaxes' => 'AdditionalTaxes',
        'ArrayOfTax' => 'ArrayOfTax',
        'Tax' => 'Tax',
        'Total' => 'Total',
        'TotalCharges' => 'TotalCharges',
        'AbolApiSimpleShipment' => 'AbolApiSimpleShipment',
        'AbolApiSimpleShipmentRequest' => 'AbolApiSimpleShipmentRequest',
        'AbolApiSimpleShipment_Request' => 'AbolApiSimpleShipment_Request',
        'SimpleShipment' => 'SimpleShipment',
        'SimpleExportLineItems' => 'SimpleExportLineItems',
        'SimpleExportLineItem' => 'SimpleExportLineItem',
        'SimpleInternationalShipment' => 'SimpleInternationalShipment',
        'AbolApiSimpleShipmentResponse' => 'AbolApiSimpleShipmentResponse',
        'SimpleShipmentResult' => 'SimpleShipmentResult',
        'SimpleShipmentResponse' => 'SimpleShipmentResponse',
        'ArrayOfSimpleDocument' => 'ArrayOfSimpleDocument',
        'SimpleDocument' => 'SimpleDocument',
        'Simple_FeeDetails' => 'Simple_FeeDetails',
        'SimpleFeeDetails' => 'SimpleFeeDetails',
        'SimpleFeeDetail' => 'SimpleFeeDetail',
        'SimpleLabelList' => 'SimpleLabelList',
        'AbolApiSimpleRate' => 'AbolApiSimpleRate',
        'AbolSimpleRateRequest' => 'AbolSimpleRateRequest',
        'AbolRateSimpleResponse' => 'AbolRateSimpleResponse',
        'Simple_RateResult' => 'Simple_RateResult',
        'SimpleRateResultList' => 'SimpleRateResultList',
        'SimpleRateResult' => 'SimpleRateResult',
        'SimpleService' => 'SimpleService',
        'ArrayOfFee' => 'ArrayOfFee',
        'Fee' => 'Fee',
        'AbolRate' => 'AbolRate',
        'AbolRateResponse' => 'AbolRateResponse',
        'AbolShipperSignup' => 'AbolShipperSignup',
        'AbolShipperSignupResponse' => 'AbolShipperSignupResponse',
        'AbolValidateAddress' => 'AbolValidateAddress',
        'AbolValidateAddressResponse' => 'AbolValidateAddressResponse',
        'AbolOrder' => 'AbolOrder',
        'AbolOrderResponse' => 'AbolOrderResponse',
        'AbolShipment' => 'AbolShipment',
        'AbolShipmentResponse' => 'AbolShipmentResponse',
        'AbolTrackPackage' => 'AbolTrackPackage',
        'AbolTrackPackageResponse' => 'AbolTrackPackageResponse',
        'AbolVoidPackage' => 'AbolVoidPackage',
        'AbolVoidPackageResponse' => 'AbolVoidPackageResponse',
        'AbolCloseOut' => 'AbolCloseOut',
        'AbolCloseOutResponse' => 'AbolCloseOutResponse',
        'AbolClassify' => 'AbolClassify',
        'AbolClassifyResponse' => 'AbolClassifyResponse',
        'AbolDuty' => 'AbolDuty',
        'AbolDutyResponse' => 'AbolDutyResponse',
        'AbolSimpleShipment' => 'AbolSimpleShipment',
        'AbolSimpleShipmentResponse' => 'AbolSimpleShipmentResponse',
        'AbolSimpleRate' => 'AbolSimpleRate',
        'AbolSimpleRateResponse' => 'AbolSimpleRateResponse',);
	
	/**
	 * SoapClient instance
	 * 
	 * @var SoapClient
	 */
	protected $client = null;
	
	/**
	 * An array of headers to be sent along with the SOAP request
	 * 
	 * @var array
	 */
	protected $inputHeaders = null;
	
	/**
	 * If supplied, this array will be filled with the headers from the SOAP response
	 * 
	 * @var array
	 */
	protected $outputHeaders = null;
	
	
	/**
	 * Constructor
	 *
	 * @param string $wsdl Path or URL to WSDL file
	 * @param array $options Options like SoapClient->__construct()
	 * 
	 * @return void
	 */
	function __construct($wsdl, $options = null)
	{		
		foreach(self::$classmap as $key => $value)
			if(!isset($options["classmap"][$key]))
				$options["classmap"][$key] = $value;
		
		if($options === null)
			$options = array();			
		
		$this->client = new MySoapClient($wsdl, $options);
	}
	
	// Block of generated methods
	
	/**
	 *  Perform rating with abolapi
	 * 
	 * @param AbolRate $request
	 * 
	 * @return AbolRateResponse
	 */
	public function AbolRate(AbolRate $request)
	{
		// $request = array(__FUNCTION__ => $request);
		return $this->call(__FUNCTION__, $request);
	}

	/**
	 *  Shipper Signup
	 * 
	 * @param AbolShipperSignup $request
	 * 
	 * @return AbolShipperSignupResponse
	 */
	public function AbolShipperSignup(AbolShipperSignup $request)
	{
		// $request = array(__FUNCTION__ => $request);
		return $this->call(__FUNCTION__, $request);
	}

	/**
	 *  Perform address validation with abolapi
	 * 
	 * @param AbolValidateAddress $request
	 * 
	 * @return AbolValidateAddressResponse
	 */
	public function AbolValidateAddress(AbolValidateAddress $request)
	{
		// $request = array(__FUNCTION__ => $request);
		return $this->call(__FUNCTION__, $request);
	}

	/**
	 *  Perform order import with abolapi
	 * 
	 * @param AbolOrder $request
	 * 
	 * @return AbolOrderResponse
	 */
	public function AbolOrder(AbolOrder $request)
	{
		// $request = array(__FUNCTION__ => $request);
		return $this->call(__FUNCTION__, $request);
	}

	/**
	 *  Perform shipment with abolapi
	 * 
	 * @param AbolShipment $request
	 * 
	 * @return AbolShipmentResponse
	 */
	public function AbolShipment(AbolShipment $request)
	{
		// $request = array(__FUNCTION__ => $request);
		return $this->call(__FUNCTION__, $request);
	}

	/**
	 *  Track package with abolapi
	 * 
	 * @param AbolTrackPackage $request
	 * 
	 * @return AbolTrackPackageResponse
	 */
	public function AbolTrackPackage(AbolTrackPackage $request)
	{
		// $request = array(__FUNCTION__ => $request);
        //print_r($request);
		return $this->call(__FUNCTION__, $request);
	}

    /**
	 *  Void package with abolapi
	 * 
	 * @param AbolVoidPackage $request
	 * 
	 * @return AbolVoidPackageResponse
	 */
	public function AbolVoidPackage(AbolVoidPackage $request)
	{
		// $request = array(__FUNCTION__ => $request);
		return $this->call(__FUNCTION__, $request);
	}

	/**
	 *  Perform CloseOut with abolapi
	 * 
	 * @param AbolCloseOut $request
	 * 
	 * @return AbolCloseOutResponse
	 */
	public function AbolCloseOut(AbolCloseOut $request)
	{
		// $request = array(__FUNCTION__ => $request);
		return $this->call(__FUNCTION__, $request);
	}

	/**
	 *  Get duty categories
	 * 
	 * @param AbolClassify $request
	 * 
	 * @return AbolClassifyResponse
	 */
	public function AbolClassify(AbolClassify $request)
	{
		// $request = array(__FUNCTION__ => $request);
		return $this->call(__FUNCTION__, $request);
	}

	/**
	 *  Calculate duty
	 * 
	 * @param AbolDuty $request
	 * 
	 * @return AbolDutyResponse
  */
	public function AbolDuty(AbolDuty $request)
	{
		// $request = array(__FUNCTION__ => $request);
		return $this->call(__FUNCTION__, $request);
	}

    /**
     *  Simple Abol Shipment
     *
     * @param AbolSimpleShipment $request
     *
     * @return AbolSimpleShipmentResponse
     */
    public function AbolSimpleShipment(AbolSimpleShipment $request)
    {
        $request = array($request);
        return $this->call(__FUNCTION__, $request);
    }

    /**
     *  Simple Abol Rate
     *
     * @param AbolSimpleRate $request
     *
     * @return AbolSimpleRateResponse
     */
    public function AbolSimpleRate(AbolSimpleRate $request)
    {
        $request = array($request);
        return $this->call(__FUNCTION__, $request);
    }

    // end of block



	/**
	 * Add header to array of headers to be sent along with the SOAP request.  
	 * 
	 * @param mixed $header
	 * 
	 * @return void
	 */
	public function addInputHeader($header)
	{
		$this->inputHeaders[] = $header;
	}
	
	/**
	 * If supplied, this array will be filled with the headers from the SOAP response
	 * 
	 * @return array
	 */
	public function getOutputHeaders()
	{
		return $this->outputHeaders;
	}
	

	/**
	 * Call method of web-service
	 * 
	 * @param string $method Method name
	 * @param mixed $data Data 
	 * @param mixed $options Options corresponding like SoapClient->__soapCall() 
	 * 
	 * @return mixed
	 */
	protected function call($method, $data, $options = null)
	{
    $arrayData = objectToArray($data);
    $response = $this->client->__soapCall($method, $arrayData, $options, $this->inputHeaders, $this->outputHeaders);
    
    //var_dump($response);
    return $response;
  }

  public function arrayResponse() {
    return $this->client->arrayResponse();
  }

  public function jsonReponse() {
    return $this->client->jsonReponse();
  }

  public function response(){
    return $this->client->response();
  }

}

?>