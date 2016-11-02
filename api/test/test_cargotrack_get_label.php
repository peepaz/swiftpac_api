<?php 
require_once '/home/vincyavi/public_html/swiftpac1/simplehtmldom_1_5/simple_html_dom.php';

function getLabel(){

	//username and password of account
	$username = urlencode("spsupport");
	$password = urldecode("svdAdmin123");
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
// 		curl_setopt($ch, CURLOPT_URL, "http://swiftpac.cargotrack.net/appl2.0/warehouse/add_warehouse.asp?action=create&branch=ITS");
	curl_setopt($ch, CURLOPT_URL, "http://swiftpac.cargotrack.net/appl2.0/print/label_whs_courier.asp?id=128618");
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
	// 	$table = $html->find('table')[7];
	// 		echo $table->children();

	$base64 = $resp;
	// 	$binary = base64_decode($resp);
	file_put_contents(__DIR__."/label2.pdf", $resp);

	header('Content-type: application/pdf');
	// 	header("Content-Disposition: attachment; filename='". __DIR__."/label.pdf'");

	// 	echo "file_get_contents(__DIR__."/label.pdf");

	echo "<p><a href = 'label2.pdf'>Click to View Label in PDF Format</a></p>";


}

getLabel();


?>