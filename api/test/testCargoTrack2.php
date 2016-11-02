<?php 

include_once '../../swiftpac1/simplehtmldom_1_5/simple_html_dom.php';

//username and password of account
$username = urlencode("Miami Swiftpac Unknown");
$password = urldecode("SPUSA0011");
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
curl_setopt($ch, CURLOPT_URL, "http://swiftpac.cargotrack.net/appl2.0/client/whs_detail.asp?id=125315");
// curl_setopt($ch, CURLOPT_URL, "http://swiftpac.cargotrack.net/appl2.0/client/whs_onhand.asp?sc=12190");
// curl_setopt($ch, CURLOPT_URL, "http://swiftpac.cargotrack.net/appl2.0/client/whs_detail.asp?id=127566");
//do stuff with the info with DomDocument() etc
$resp = curl_exec($ch);

$status = curl_getinfo($ch); //info on session that was connected

curl_close($ch);

// Parse document returned from cargotrack -- Getting warehouse numbers from on hand packages

// var_dump($html);


//Create DOM using data from cargotrack
$DOM = new DOMDocument();
$DOM->loadHTML($resp);
// $htmlStr = $DOM->saveHTMLFile(__DIR__.'/cargoTrack.html');
$htmlStr = $DOM->saveHTML();
$html = str_get_html($htmlStr);
// $mytables = $html->find('a');
// $tables = $DOM->getElementsByTagName('table');
// foreach ($html->find("table") as $key => $tbl){
// 	echo $key;
// 	echo $tbl;
// }
$table1 = $html->find('table')[9]; // table to get tracking
// $table3 = $html->find('table')[7]; // table to get tracking
$table2 = $html->find('table')[10]; // to get description

// $deliveredBy = $table3->children()[0]->children()[0]->children()[0]->children()[1]->children()[1]->
// children()[3]->children(0)->children(0)->value; //Delivered by 
$deliveredBy = $table1->children()[1]->children(3)->children(0)->children(0)->value;; //tracking number
echo $deliveredBy;
$trackingNum = $table1->children()[2]->children()[3]->plaintext; //tracking number
$trackingNum = trim(preg_replace('/[\0\x0B\s\t\n\r\s]+/', ' ', $trackingNum));
$description = $table2->children()[0]->children()[1]->plaintext;
$description = trim(preg_replace('/[\0\x0B\s\t\n\r\s]+/', ' ', $description));

// echo $table->children()[0]->children()[1]->children()[2]->children()[0]->children()[0]->children()[0]->children()[2];
// foreach ($table->children() as $tableRow){
// // 	echo $tableRow;
	
// 	foreach ($tableRow->children() as $tableRowData ){
		
// 		echo $tableRowData->children(1)->children();
// 	}
	
// }
// ?>