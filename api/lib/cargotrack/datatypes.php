<?php

namespace lib\CargoTrack {

	class DataTypes {

	}
}
namespace lib\CargoTrack\DataTypes {

	
	//==============================Requests================================
	//==================Warehouse Request=====================
	class CarogTrackCreateWarehouseRequest {
	
	}
	class CargoTrackGetWarehouseRequest {
	
	}
	
	//All Warehouses
	class CargoTrackGetAllWarehouseRequest {
	
		/**
		 *
		 * @var GetAllWarehouseRequest
		 */
		public $getAllWarehouseRequest = null;
	}
	
	class GetAllWarehouseRequest {
		/**
		 *
		 * @var string
		 */
		public $username = null;
		/**
		 *
		 * @var string
		 */
		public $password = null;
	}
	
	class CargoTrackUpdateWarehouseRequest {
	
	}
	class CargoTrackDeleteWarehouseRequest {
	
	}
	
	class UpdateWarehouseRequest {
	
	}
	class DeleteWarehouseRequest {
	
	}
	//==================End of Warehouse Request=====================
	
	//===================Invoice Requests============================
	//Create Invoices
	class CargoTrackCreateInvoiceRequest {
	
		/**
		 *
		 * @var CreateInvoiceRequest
		 */
		public $createInvoiceRequest = null;
	}
	class CreateInvoiceRequest {
		/**
		 *
		 * @var string
		 */
		public $date = null;
		/**
		 *
		 * @var string
		 */
		public $accountNum = null;
		/**
		 *
		 * @var string
		 */
		public $accountName = null;
		/**
		 *
		 * @var string
		 */
		public $currency = null;
		/**
		 *
		 * @var string
		 */
		public $branch = null;
		/**
		 *
		 * @var string
		 */
		public $warehouseNum = null;
		/**
		 *
		 * @var string
		 */
		public $reference = null;
		/**
		 *
		 * @var InvoiceEntryRecord
		 */
		public $entryRecord = array();
		/**
		 *
		 * @var string
		 */
		public $createdBy = null;
		/**
		 * @var string
		 */
		public $updatedBy = null;
		/**
		 *
		 * @param InvoiceEntryRecord $entryRecord
		 */
	
		public function addEntryRecord(InvoiceEntryRecord $entryRecord){
			$this->entryRecord[] = $entryRecord;
		}
	
	}
	class InvoiceEntryRecord {
		/**
		 *
		 * @var string
		 */
		public $units = null;
		/**
		 *
		 * @var string
		 */
		public $glAccount = null;
		/**
		 *
		 * @var string
		 */
		public $description = null;
		/**
		 *
		 * @var double
		 */
		public $priceUnit = null;
		/**
		 *
		 * @var double
		 */
		public $subtotal = null;
	
	}
	
	//Get Invoices
	class CargoTrackGetInvoiceDataRequest {
	
		/**
		 *
		 * @var GetInvoiceDataRequest
		 */
		public $getInvoiceDataRequest = null;
	}
	class GetInvoiceDataRequest {
		/**
		 *
		 * @var GetInvoiceDataRequestDetail
		 */
		public $getInvoiceDataRequestDetail;
	}
	class GetInvoiceDataRequestDetail {
	
		/**
		 * @var InvoiceDataRequest
		 */
		public $invoiceDataRequestList = array();
		
		function addInvoiceDataRequest($invoiceDataRequest) {
			$this->invoiceDataRequestList[] = $invoiceDataRequest;
		}
	}
	class InvoiceDataRequest {
		/**
		 *
		 * @var string
		 */
		public $branch = null;
		/**
		 *
		 * @var string
		 */
		public $invoiceNumber = null;
	
	}
	
	//Get all customer invoicves
	class CargoTrackGetAllCustInvoiceRequest {
	
		/**
		 *
		 * @var GetAllCustInvoiceRequest
		 */
		public $getAllCustInvoiceRequest = null;
	}
	class GetAllCustInvoiceRequest {
		/**
		 *
		 * @var string
		 */
		public $username = null;
		/**
		 *
		 * @var string
		 */
		public $password = null;
	}
	
	//===================End of Invoice Requests===========================
	
	
	//Prealert
	class CargoTrackPrealertRequest {
		
		/**
		 * 
		 * @var PrealertRequest
		 */
		public $prealertRequest = null;
		
		
	
	}
	class PrealertRequest {	
		/**
		 *
		 * @var string
		 */
		public $clientId = null;
		
		/**
		 *
		 * @var Prealert
		 */
		public $prealertList = array();
		
		/**
		 *
		 * @param Prealert $preleart
		 */
		public function addPreleart($preleart){
				
			$this->prealertList[] = $preleart;
		}
		
	
	}
	class Prealert {
		
		
		/**
		 *
		 * @var string
		 */
		public $store = null;
		/**
		 *
		 * @var string
		 */
		public $description = null;
		
		/**
		 *
		 * @var string
		 */
		public $value = null;
		/**
		 *
		 * @var string
		 */
		public $carrier = null;
		/**
		 *
		 * @var string
		 */
		public $tracking = null;
		/**
		 *
		 * @var string
		 */
		public $shippingInstruction = null;
		/**
		 *
		 * @var string
		 */
		public $shippingType = null;
		
	
	}
	
	
	
	//Tracking
	class CargoTrackGetTrackingRequest {
	
		/**
		 *
		 * @var GetTrackingRequest
		 */
		public $getTrackingRequest = null;
	
	
	
	}
	class GetTrackingRequest {
	
		/**
		 *
		 * @var TrackingNumber
		 */
		public $trackingNumberList = array();
		/**
		 *
		 * @param $trackingNumber
		 */
		function addTrackingNumber ($trackingNumber){
	
			$this->trackingNumberList[] = $trackingNumber;
		}
	}
	
	//Unknown Packges
	class CargoTrackGetUnknownPackagesRequest {
	
		/**
		 *
		 * @var GetUnknownPackagesRequest
		 */
		public $getUnknownPackagesRequest = null;
	
	}
	class GetUnknownPackagesRequest {
	
		/**
		 *
		 * @var string
		 */
		public $requestType = null;
	}
	
	//Pay Invoices (Update)
	class CargoTrackUpdateInvoicePayRequest {
	
		/**
		 *
		 * @var UpdateInvoicePayRequest
		 */
		public $updateInvoicePayRequest =  null;
	}
	class UpdateInvoicePayRequest {
	
		/**
		 *
		 * @var UpdateInvoicePayRequestDetail
		 */
		public $updateInvoicePayRequestDetail;
	}
	class UpdateInvoicePayRequestDetail {
	
		/**
		 *
		 * @var InvoicePayRequest
		 */
		public $invoicePayRequestList = array();
	
		function addInvoicePayRequest($invoicePayRequest){
				
			$this->invoicePayRequestList[] = $invoicePayRequest;
		}
	}
	class InvoicePayRequest {
		/**
		 *
		 * @var string
		 */
		public $date = null;
		/**
		 *
		 * @var string
		 */
		public $invoiceId = null;
		/**
		 *
		 * @var string
		 */
		public $recieptType = null;
		/**
		 *
		 * @var string
		 */
		public $recieptNumber = null;
		/**
		 *
		 * @var string
		 */
		public $payAmount = null;
	
	}
	
	//==============================End of Requests ========================
	
	//==============================Responses ================================
	//===============Warehouse Responses================
	class CreateWarehouseResponse {
	
		/**
		 *
		 * @var boolean
		 */
		public $isCreated = null;
	
	}
	class GetWarehouseResponse {
	
	
	}
	class UpdateWarehouseResponse {}
	
	//All Warehouse
	class CargoTrackGetAllWarehouseResponse {
	
		/**
		 *
		 * @var GetAllWarehouseRequest
		 */
		public $getAllWarehouseResponse = null;
	
	
	}
	class GetAllWarehouseResponse {
	
		/**
		 *
		 * @var Warehouse
		 */
		public $warehousePackagesList = array();
		
		/**
		 *
		 * @var Error
		 */
		public $error = null;
	
		/**
		 *
		 * @param Warehouse  $warehouse
		 */
		function addWarehouse (Warehouse $warehouse) {
	
			$this->warehousePackagesList[] = $warehouse;
		}
	
	}
	class Warehouse {
	
		/**
		 *
		 * @var string
		 */
		public $packageDescription = null;
	
		/**
		 *
		 * @var string
		 */
		public $dateReceived = null;
	
		/**
		 *
		 * @var string
		 */
		public $warehouseNumber = null;
	
		/**
		 *
		 * @var string
		 */
		public $shipper = null;
	
		/**
		 *
		 * @var string
		 */
		public $pieces = null;
	
		/**
		 *
		 * @var string
		 */
		public $consignee = null;
	
		/**
		 *
		 * @var string
		 */
		public $trackingNumber = null;
	
		/**
		 *
		 * @var string
		 */
		public $deliveredBy = null;
		/**
		 *
		 * @var string
		 */
		public $weight = null;
		/**
		 *
		 * @var string
		 */
		public $branch = null;
		/**
		 *
		 * @var string
		 */
		public $supplier = null;
	}
	
	class DeleteWarehouseResponse {
	
	
	}
	//===============End of Warehouse Responses================
	
	//===============Invoice Responses======================
	
	//Create Invoices
	class CargoTrackCreateInvoiceResponse {
	
		/**
		 *
		 * @var CreateInvoiceResponse
		 */
		public $createInvoiceResponse = null;
		/**
		 * 
		 * @var Error
		 */
		public $error = null;
	
	}
	class CreateInvoiceResponse {
	
		/**
		 *
		 * @var boolean
		 */
		public $isCreated = null;
	
		/**
		 * @var Error
		 */
		public $error = null;
	
	}
	
	//All Customer Invoices
	class CargoTrackGetAllCustInvoiceResponse {
		/**
		 *
		 * @var GetAllCustInvoiceResponse
		 */
		public $getAllCustInvoiceResponse = null;
	
	}
	class GetAllCustInvoiceResponse {
	
		/**
		 *
		 * @var Invoice
		 */
		public $custInvoiceList = array();
	
		/**
		 *
		 * @var Error
		 */
		public $error = null;
	
		/**
		 *
		 * @param Invoice  $invoice
		 */
		public function addInvoice (Invoice $invoice) {
	
			$this->custInvoiceList[] = $invoice;
		}
	
	}
	
	
	class Invoice {
	
		/**
		 *
		 * @var string
		 */
		public $dateCreated = null;
	
		/**
		 *
		 * @var string
		 */
		public $invoiceNumber = null;
	
		/**
		 *
		 * @var string
		 */
		public $accountName = null;
	
		/**
		 *
		 * @var string
		 */
		public $currency = null;
	
		/**
		 *
		 * @var string
		 */
		public $invoiceTotal = null;
	
		/**
		 *
		 * @var string
		 */
		public $amountPaid = null;
		/**
		 *
		 * @var string
		 */
		public $warehouseNumber = null;
	
		/**
		 *
		 * @var string
		 */
		public $referenceNumber = null;
		/**
		 *
		 * @var string
		 */
		public $branch = null;
		/**
		 *
		 * @var InvoiceEntryRecord
		 */
		public $invoiceEntryRecord = null;
		
	
	}
	
	
	//Get Invoices
	class CargoTrackGetInvoiceDataResponse {
	
		/**
		 *
		 * @var GetInvoiceDataResponse
		 */
		public $getInvoiceDataResponse = null;
		/**
		 * 
		 * @var Error
		 */
		public $error = null;
	}
	class GetInvoiceDataResponse {
	
		/**
		 *
		 * @var GetInvoiceDataResult
		 */
		public $getInvoiceDataResult = null;
	}
	class GetInvoiceDataResult {
	
		/**
		 *
		 * @var InvoiceDataResult
		 */
		public $invoiceDataResultList = array();
	
		function addInvoiceDataResult($invoiceDataResult){
				
			$this->invoiceDataResultList[] = $invoiceDataResult;
		}
	}
	class InvoiceDataResult {

		/**
		 *
		 * @var string
		 */
		public $invoiceId = null;
		/**
		 *
		 * @var string
		 */
		public $date = null;
		/**
		 *
		 * @var string
		 */
		public $branch = null;
		/**
		 *
		 * @var string
		 */
		public $currency = null;
		/**
		 *
		 * @var string
		 */
		public $accountId = null;
		/**
		 *
		 * @var string
		 */
		public $accountName = null;
		/**
		 *
		 * @var string
		 */
		public $totalInvoiced = null;
		/**
		 *
		 * @var string
		 */
		public $totalPaid = null;
		/**
		 *
		 * @var string
		 */
		public $warehouseNum = null;
		/**
		 *
		 * @var string
		 */
		public $reference = null;
		/**
		 *
		 * @var InvoiceEntryRecord
		 */
		public $invoiceEntry = null;
		/**
		 *
		 * @var string
		 */
		public $cargoTrackInvoicePDFLink = null;
		/**
		 * 
		 * @var Error
		 */
		public $error = null;
	
	}
	//===============End of Warehouse Responses================
	
	//prealert
	class CargoTrackPrealertResponse {
	
		/**
		 *
		 * @var prealertResponse
		 */
		public $prealertResponse = null;
	
	}
	class PrealertResponse {
	
		/**
		 *
		 * @var boolean
		 */
		public $isSubmitted = null;
	
		/**
		 *
		 * @var Error
		 */
		public $error = null;
	}
	
	//Tracking
	class CargoTrackGetTrackingResponse {
	
		/**
		 * @var GetTrackingResponse
		 */
		public $getTrackingResponse = null;
		/**
		 * 
		 * @var Error
		 */
		public $error = null;
	
	}
	class GetTrackingResponse {
	
		/**
		 * @var TrackingResult
		 */
		public $trackingResultList = array();
	
		/**
		 *
		 * @var Error
		 */
		public $error = null;
	
	
		/**
		 *
			* @param TrackingResult $trackingResult
			*/
		function addTrackingResult (TrackingResult $trackingResult) {
			$this->trackingResultList [] = $trackingResult;
		}
	
	}
	class TrackingResult {
	
		/**
		 *
		 * @var TrackingNumberEntry
		 */
		public $trackingNumberEntryList = array();
	
		/**
		 *
		 * @var string
		 */
		public $trackingNumber = null;
	
	}
	class TrackingNumberEntry {
	
		/**
		 *
		 * @var string
		 */
		public $date = null;
		/**
		 *
		 * @var string
		 */
		public $status = null;
		/**
		 *
		 * @var string
		 */
		public $location = null;
	}
	
	//Unknown Packages
	class CargoTrackGetUnknownPackagesResponse {
	
		/**
		 *
		 * @var GetUnknownPackagesResponse
		 */
		public $getUnknownPackagesResponse = null;
	
		 /**
		  * 
		  * @var Error
		  */
		public $error = null;
	
	}
	class GetUnknownPackagesResponse {
	
		/**
		 *
		 * @var UnknownPackage
		 */
		public $unknownPackagesList = array();
		
		/**
		 *
		 * @param UnknownPackage $unknownPackage
		 */
		function addUnknownPackage (UnknownPackage $unknownPackage) {
	
			$this->unknownPackagesList[] = $unknownPackage;
		}
	
	}
	class UnknownPackage {
	
		/**
		 *
		 * @var string
		 */
		public $packageDescription = null;
	
		/**
		 *
		 * @var string
		 */
		public $dateReceived = null;
	
		/**
		 *
		 * @var string
		 */
		public $warehouseNumber = null;
	
		/**
		 *
		 * @var string
		 */
		public $shipper = null;
	
		/**
		 *
		 * @var string
		 */
		public $pieces = null;
	
		/**
		 *
		 * @var string
		 */
		public $consignee = null;
	
		/**
		 *
		 * @var string
		 */
		public $trackingNumber = null;
	
		/**
		 *
		 * @var string
		 */
		public $deliveredBy = null;
		/**
		 *
		 * @var string
		 */
		public $weight = null;
		/**
		 *
		 * @var string
		 */
		public $branch = null;
		/**
		 *
		 * @var string
		 */
		public $supplier = null;
	}
	
	//Pay Invoices (update)
	class CargoTrackUpdateInvoicePayResponse {
		/**
		 *
		 * @var UpdateInvoicePayResponse
		 */
		public $updateInvoicePayResponse = null;
		/**
		 * 
		 * @var Error
		 */
		public $error = null;
	}
	class UpdateInvoicePayResponse{
	
		/**
		 *
		 * @var UpdateInvoicePayResult
		 */
		public $updateInvoicePayResult = null;
	}
	class UpdateInvoicePayResult{
	
		/**
		 *
		 * @var InvoicePayResult
		 */
		public $invoicePayResultList = array();
		function addInvoicePayResult ($invoicePayResult){
				
			$this->invoicePayResultList[] = $invoicePayResult;
		}
	
	}
	class InvoicePayResult{
	
		/**
		 *
		 * @var string
		 */
		public $invoiceId = null;
		/**
		 *
		 * @var boolean
		 */
		public $isPaid = null;
	
		/**
		 *
		 * @var Error
		 */
		public $error = null;
	}
	
	//Error
	class Error {
		/**
		 * @var ErrorDetails
		 */
		public $errorDetails = null;
	
	}
	class ErrorDetails {
	
		/**
		 *
		 * @var string
		 */
		public $type = null;
		/**
		 *
		 * @var string
		 */
		public $message = null;
	
	
	}
	//==============================End of Responses ================================
	
	//Helper Classes
	
	class CargoTrackCurrency {
	
		const XCD = "XCD";
		const BBD = "BBD";
		const TTD = "TTD";
		const USD = "USD";
	}
	
	class CargoTrackGLAccount {
	
		const AIR_FREIGHT = "4100";
		const OCEAN_FREIGHT = "4200";
		const INSURANCE = "4112";
	}
	
	class CargoTrackPaymentType {
	
		const CASH = "Cash";
		const CREDIT_CARD = "Credit Card";
		const WIRE_TRANSFER = "Wire Transfer";
		const CHECK = "Check";
		const MISC = "Miscellaneous";
	}
	
	class CargoTrackBranch {
	
		const ITS = "ITS";
		const MIA = "MIA";
		const SVD = "SVD";
		const BGI = "BGI";
		const SLU = "SLU";
	
	}
	class CargoTrackPrealertShipInstr {
			
			const AIR = "A";
			const OCEAN = "O";
			const EXPRESS = "E";
	}
	class CargoTrackPrealertShipType{
			
			const SHIP = "D";
			const CONSOLIDATE = "C";
			const REPACK = "R";
	}
}