<?php 


// var_dump(date_create("2016-7-15"));
// var_dump(new DateTime());
// $interval = date_diff(date_create("2016-7-"), new DateTime());
// var_dump($interval->format("%h"));

// $date1 = new DateTime("2016-7-30");
// $date2 = new DateTime("today");

// $d1TimeStamp = $date1->getTimestamp();
// $d2TimeStamp = $date2->getTimestamp();

// $fedexExpToWarehouseTime = (new DateTime("2016-7-30") - new DateTime("today"));


// var_dump($fedexExpToWarehouseTime);


$a = 10;
$b = new stdClass();
$b->foo = 2;

$c = $b;

function foo($a){
	
	$a->foo = 3;
}

foo($b);

var_dump($c);

$c->foo = 5;

var_dump($b);


var_dump(isset($b->foo));


?>