<?php 
require_once '/home/vincyavi/public_html/swiftpac1/simplehtmldom_1_5/simple_html_dom.php';

function getAllInvoices(){

	//username and password of account
	$username = urlencode("svd24007");
	$password = urldecode("test1234");
	$action = urldecode("login");


	//login form action url
	$url="http://swiftpac.cargotrack.net/default.asp";
	$postinfo = "user=".$username."&password=".$password."&action=".$action;


	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt ($ch, CURLOPT_COOKIEJAR, 'cookie.txt');
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $postinfo);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	curl_exec($ch);
	

	//page with the content I want to grab
	// 		curl_setopt($ch, CURLOPT_URL, "http://swiftpac.cargotrack.net/appl2.0/index/default.asp");
	curl_setopt($ch, CURLOPT_URL, "http://swiftpac.cargotrack.net/appl2.0/client/invoices.asp");
// 	curl_setopt($ch, CURLOPT_URL, "http://swiftpac.cargotrack.net/appl2.0/warehouse/add_warehouse.asp?action=create&branch=ITS");
// 	curl_setopt($ch, CURLOPT_URL, "http://swiftpac.cargotrack.net/appl2.0/print/label_whs_courier.asp?id=128618");
	// curl_setopt($ch, CURLOPT_URL, "http://swiftpac.cargotrack.net/appl2.0/client/whs_onhand.asp?sc=12190");
	// curl_setopt($ch, CURLOPT_URL, "http://swiftpac.cargotrack.net/appl2.0/client/whs_detail.asp?id=127566");
	//do stuff with the info with DomDocument() etc
	$resp = curl_exec($ch);

	$status = curl_getinfo($ch); //info on session that was connected


	// Parse document returned from cargotrack -- Getting$ warehouse numbers from on hand packages

	//Create DOM using data from cargotrack
	$DOM = new DOMDocument();
	$DOM->loadHTML($resp);
	$htmlStr = $DOM->saveHTML();
	$html = str_get_html($htmlStr);
	$tables = $html->find('table');
	$offSetTable = $html->find('table')[6]; //get numnber of offsets
	
	$numOfOffsets = sizeof($offSetTable->children()[0]->children()[1]->children()[0]->children());
	
// 	echo "Number of Offsets" . $numOfOffsets . "<br>";
// 	foreach ($tables as $key=> $table){
// 		echo $key. " ". $table;
// 	}
	
//	echo $numOfOffsets;
	
	$offset = 0;
	for ($i=0; $i<$numOfOffsets; $i++, $offset += 20){
		
		$invoiceData = getInvoiceData ( $ch, $resp, $DOM, $htmlStr, $html);
		
		
		if ($numOfOffsets >100){
			
			curl_setopt($ch, CURLOPT_URL, "http://swiftpac.cargotrack.net/appl2.0/client/invoices.asp?offset=$offset");
			$resp = curl_exec($ch);
			
			//Create DOM using data from cargotrack
			$DOM->loadHTML($resp);
			$htmlStr = $DOM->saveHTML();
			$html = str_get_html($htmlStr);
// 			$tables = $html->find('table');
			
			$invoiceData1 = getInvoiceData ( $ch, $resp, $DOM, $htmlStr, $html);
			unset($warehousePackageData1[0]);
			$invoiceData = array_merge($invoiceData,$invoiceData1);
				
		}
		
	}
	
	
	
	var_dump($invoiceData);
	
	curl_close($ch);
	



}


/**
 * @param ch
 * @param resp
 * @param DOM
 * @param htmlStr
 * @param html
*/

function getInvoiceData($ch, $resp, $DOM, $htmlStr, $html) {
	// 	foreach ($tables as  $key => $table ){
	// 		echo $key . " " .$table;
	// 	}
		
		/*
		curl_setopt($ch, CURLOPT_URL, "http://swiftpac.cargotrack.net/appl2.0/client/warehouse.asp?offset=60");
		$resp = curl_exec($ch);
		
		//Create DOM using data from cargotrack
		$DOM->loadHTML($resp);
		$htmlStr = $DOM->saveHTML();
		$html = str_get_html($htmlStr);
		$tables = $html->find('table');
		
			foreach ($tables as  $key => $table ){
					echo $key . " " .$table;
				}
				
				*/
		
		
	
	// 	$base64 = $resp;
		// 	$binary = base64_decode($resp);
	
		// 	header("Content-Disposition: attachment; filename='". __DIR__."/label.pdf'");
	
		// 	echo "file_get_contents(__DIR__."/label.pdf");
		
		$table= $html->find('table')[8];
		
		$warehousePackageData = array(array());
		
		foreach ($table->children() as $tblKey => $tblRow){
			foreach($tblRow->children() as  $tblRowKey => $tblRowData) {
		
				$tblRowData = trim(preg_replace('/[\0\x0B\s\t\n\r\s]+/', ' ', $tblRowData->plaintext));
				$warehousePackageData[$tblKey][$tblRowKey] =  $tblRowData;
		
				/*
				if ($tblRowKey == 2 && is_numeric($tblRowData)){ //invoice get details
		
					curl_setopt($ch, CURLOPT_URL, self::$warehouseDetailUrl."?id=" . $tblRowData);
					$resp = curl_exec($ch);
		
		
					//Create DOM using data from cargotrack
					$DOM = new DOMDocument();
					$DOM->loadHTML($resp);
					$htmlStr = $DOM->saveHTML();
					$html = str_get_html($htmlStr);
					$table1 = $html->find('table')[9]; // table to get tracking
					$table2 = $html->find('table')[10]; // to get description
		
					$deliveredBy = $table1->children()[1]->children(3)->children(0)->children(0)->value;; //tracking number
					$trackingNum = $table1->children()[2]->children()[3]->plaintext; //tracking number
					$trackingNum = trim(preg_replace('/[\0\x0B\s\t\n\r\s]+/', ' ', $trackingNum));
					$description = $table2->children()[0]->children()[1]->plaintext;
					$description = trim(preg_replace('/[\0\x0B\s\t\n\r\s]+/', ' ', $description));
		
		
					$warehousePackageData[$tblKey][10] = $description;
					$warehousePackageData[$tblKey][9]= $trackingNum;
					$warehousePackageData[$tblKey][11]= $deliveredBy;
		
		
				}
				*/
// 				if ($tblKey == 0) {
// 					$warehousePackageData[$tblKey][10] = "Description";
// 					$warehousePackageData[$tblKey][9]= "Tracking";
// 					$warehousePackageData[$tblKey][11]= "Delivered By";
// 				}
			}
		}
	return $warehousePackageData;
}

getAllInvoices();


?>