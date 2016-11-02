<?php 
require_once '/home/vincyavi/public_html/swiftpac1/simplehtmldom_1_5/simple_html_dom.php';

function payInvoice($invoiceId,$date,$receipt_type,$check_number,$pay_amount){

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
	
	
	//get invoice number
	$action = urldecode("update");
	$update_invoice_postInfo = "id=".$invoiceId;
	curl_setopt($ch, CURLOPT_URL, "http://swiftpac.cargotrack.net/appl2.0/invoices/update.asp?id=".$invoiceId);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
// 	curl_setopt($ch, CURLOPT_POSTFIELDS, $update_invoice_postInfo);
// 	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	$resp = curl_exec($ch);
	
	
// 	echo $invoiceId. " ". $date. " ". $receipt_type . " ". $check_number . " ". $pay_amount;
// 	echo $resp;
	
// 	//get additional payment info
	$DOM = new DOMDocument();
	$DOM->loadHTML($resp);
	$htmlStr = $DOM->saveHTML();
	$html = str_get_html($htmlStr);
	
// 	$date = $html->find('*[id=date]')[0]->value;
	$clientId = $html->find('*[id=client_id]')[0]->value;
	$clientName = $html->find('*[id=client_name]')[0]->value;
	$branch = $html->find('*[id=branch]')[0]->find('*[selected=]')[0]->value;
	$currency = $html->find('*[id=currency]')[0]->find('*[selected=]')[0]->value;
	$amount = $html->find('*[id=textfield]')[0]->value;
	
// 	echo $clientId. " ". $clientName. " ". $branch. " " . $currency. " ". $amount. " ". $date. " ". $invoiceId. " ". $receipt_type
// 	. " " . $pay_amount. " ". $action; 
// 	echo "branch = ". $branch;
// 	$branch = $DOM->getElementById("branch");
	
// 	var_dump($branch);
	
	
// 	//upate Invoice with all payment info
	$action = urldecode("process");
	$update_invoice_postInfo =
	
	"invoice_id=".$invoiceId
	."&client_id=".$clientId
	."&client_name=".$clientName
	."&branch=".$branch
	."&amount=".$amount
	."&currency=".$currency
	."&date=".$date
	."&receipt_type=".$receipt_type
	."&check_number=".$check_number
	."&pay_amount=". number_format(floatval($pay_amount),2)
	."&action=".$action;
	
	curl_setopt($ch, CURLOPT_URL, "http://swiftpac.cargotrack.net/appl2.0/invoices/post_payment.asp");
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $update_invoice_postInfo);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	$resp = curl_exec($ch);
	
	echo $resp;
// 	$url = "http://swiftpac.cargotrack.net/appl2.0/invoices/post_payment.asp?invoice_id=47580&client_id=24007&branch=ITS&amount=100&client_name=ITSupport&currency=XCD"
	
// 	//Add invoice to cargotrack
// 	$action = urldecode("new");
// 	$invoice_postinfo = "date=".$date."&client_id=".$client_id."&client_name=".$client_name."&client_address1=".$client_address1
// 	."&client_address2=".$client_address2."&client_address3=".$client_address3."&client_phone=".$client_phone
// 	."&created_by=".$created_by."&updated_by=".$updated_by."&currency=".$currency."&branch=".$branch."&po=".$po."&action=".$action;
// 	curl_setopt($ch, CURLOPT_URL, "http://swiftpac.cargotrack.net/appl2.0/invoices/add_invoice.asp");
// 	curl_setopt($ch, CURLOPT_POST, 1);
// 	curl_setopt($ch, CURLOPT_POSTFIELDS, $invoice_postinfo);
// 	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
// 	$resp = curl_exec($ch);
	
// 	//get invoice number and line id
// 	$DOM = new DOMDocument();
// 	$DOM->loadHTML($resp);
// 	$htmlStr = $DOM->saveHTML();
// 	$html = str_get_html($htmlStr);
		
// 	$invoiceNumber = $html->find('*[id=invoice_number]')[0]->value;
	
// 	$lineId1 = $html->find('*[id=line_id1]')[0]->value;

// 	//update invoice with invoice details
// 	$invoice_post_fields = "&insurance=".$invoiceInfo['insurance']
// 	."&cr=".$invoiceInfo['cr']
// 	."&url=".$invoiceInfo['url']
// 	."&id=".$invoiceNumber
// 	."&close=".$invoiceInfo['close']
// 	."&branch=".$invoiceInfo['branch']
// 	."&currency=".$invoiceInfo['currency']
	
// 	."&units1=".$invoiceInfo['units1']
// 	."&gl_account1=".$invoiceInfo['gl_account1']
// 	."&cc_number1=".$invoiceInfo['cc_number1']
// 	."&vendor_id1=".$invoiceInfo['vendor_id1']
// 	."&expense1=".$invoiceInfo['expense1']
// 	."&gp1=".$invoiceInfo['gp1']
// 	."&type1=".$invoiceInfo['type1']
// 	."&payableline_id1=".$invoiceInfo['payableline_id1']
// 	."&line_id1=".$lineId1
// 	."&description1=".$invoiceInfo['description1']
// 	."&price_unit1=".$invoiceInfo['price_unit1']
// 	."&subtotal1=".$invoiceInfo['subtotal1']
	
// 	."&invoice_number=".$invoiceNumber
	
// 	."&date=".$invoiceInfo['date']
// 	."&client_id=".$invoiceInfo['client_id']
// 	."&client_name=".$invoiceInfo['client_name']
// 	."&client_address1=".$invoiceInfo['client_address1']
// 	."&client_address2=".$invoiceInfo['client_address2']
// 	."&client_address3=".$invoiceInfo['client_address3']
// 	."&client_phone=".$invoiceInfo['client_phone']
	
// 	."&cc=".$invoiceInfo['cc']
// 	."&shipper=".$invoiceInfo['shipper']
// 	."&consignee=".$invoiceInfo['consignee']
// 	."&carrier=".$invoiceInfo['carrier']
// 	."&entry=".$invoiceInfo['entry']
// 	."&etd=".$invoiceInfo['etd']
// 	."&eta=".$invoiceInfo['eta']
// 	."&credit=".$invoiceInfo['credit']
// 	."&rs=".$invoiceInfo['rs']
// 	."&incoterm=".$invoiceInfo['incoterm']
// 	."&paystatus=".$invoiceInfo['paystatus']
// 	."&exchange_rate2=".$invoiceInfo['exchange_rate2']
// 	."&warehouse_id=".$invoiceInfo['warehouse_id']
// 	."&purchasing_id=".$invoiceInfo['purchasing_id']
// 	."&house_number=".$invoiceInfo['house_number']
// 	."&house_id=".$invoiceInfo['house_id']
// 	."&master_idv=".$invoiceInfo['master_idv']
// 	."&master_id=".$invoiceInfo['master_id']
// 	."&master_number=".$invoiceInfo['master_number']
// 	."&pieces=".$invoiceInfo['pieces']
// 	."&gweight=".$invoiceInfo['gweight']
// 	."&volume=".$invoiceInfo['volume']
// 	."&cweight=".$invoiceInfo['cweight']
// 	."&origin=".$invoiceInfo['origin']
// 	."&destination=".$invoiceInfo['destination']
// 	."&po=".$invoiceInfo['po']
// 	."&reference=".$invoiceInfo['reference']
// 	."&total_records=".$invoiceInfo['total_records']
// 	."&num_lines=".$invoiceInfo['num_lines']
// 	."&remarks1=".$invoiceInfo['remarks1']
// 	."&remarks2=".$invoiceInfo['remarks2']
// 	."&remarks3=".$invoiceInfo['remarks3']
// 	."&docs=".$invoiceInfo['docs']
// 	."&action=".$invoiceInfo['action'];
	
// 	curl_setopt($ch, CURLOPT_URL,"http://swiftpac.cargotrack.net/appl2.0/invoices/update.asp?");
// 	curl_setopt($ch, CURLOPT_POST, 1);
// 	curl_setopt($ch, CURLOPT_POSTFIELDS, $invoice_post_fields);
// 	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	
// 	$resp = curl_exec($ch);

// 	echo $resp;

}

if (isset($_POST["submit_payment"])){
	
// 	echo "data sent";
	
	extract($_POST);
	
	$paymentInfo = array ();
			
	
// 	echo floatval($pay_amount);
	
// 	var_dump($_POST);
	
// echo $date;	
payInvoice($invoiceId,$date,$receipt_type,$check_number,$pay_amount);

header('Location: confirmation.php');

}


?>

<html><!--  -->
<head>

</head>
<body>
<p> Pay an Invoice on CargoTrack Test Form: 
Please note that validation is not done on this form. It is assumed that all data entered is valid </p>
<form action="test_cargotrack_pay_invoice.php" method="post" name="form1" >
  <br>
  
<!--   <input name="action" type="hidden" id="action" value="process"> -->
<!--   <input name="client_id" type="hidden" id="client_id" value="24007"> -->
<!--   <input name="client_name" type="hidden" id="client_name" value="ITSupport"> -->
<!--   <input name="branch" type="hidden" id="branch" value="ITS"> -->
<!--   <input name="amount" type="hidden" id="amount" value="100"> -->
<!--   <input name="currency" type="hidden" id="currency" value="XCD"> -->
  <table width="95%" border="0" align="center" cellpadding="2" cellspacing="2" class="insert">
    <tbody><tr>
    <tr>
      <td>Date</td>
      <td><input name="date"  type="date" class="ntext" id="date" value="" size="10"></td>
    </tr>
      <td>Invoice Number</td>
      <td>  <input name="invoiceId" type="text" id="invoiceId" value="">
      </td>
    </tr>
    <tr>
      <td>Type</td>
      <td><select name="receipt_type" class="ntext" id="receipt_type">
        <option value="Check">Check</option>
        <option value="Wire Transfer">Wire Transfer</option>
        <option value="Cash">Cash</option>
        <option value="Credit Card">Credit Card</option>
        <option value="Misc">Miscellaneous</option>
      </select></td>
    </tr>
    <tr>
      <td width="75">Number</td>
      <td><input name="check_number" type="text" class="ntext" id="check_number" value=""></td>
    </tr>
    <tr>
      <td>Amount</td>
      <td><input name="pay_amount" type="text" class="formnum" id="pay_amount" value="" size="12"></td>
    </tr>
<!--     <tr> -->
<!--       <td>Reference</td> -->
<!--       <td><input name="reference" type="text" class="ntext" id="reference" value="" size="25" ></td> -->
<!--     </tr> -->
    <tr>
      <td>&nbsp;</td>
      <td><input name="submit_payment" type="submit" value="Submit"></td>
    </tr>
  </tbody></table>
</form>


</body>


</html>