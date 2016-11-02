<?php


ini_set('default_mimetype', 'text/plain');
ini_set('default_charset', 'ISO-8859-1');
define('NONCE_SECRET', 'jvTGophIQ108Pqw9Hej');


require_once('NonceUtil.php');

print "generating a nonce with a 1 second lifetime.\n";
$nonce = NonceUtil::generate(NONCE_SECRET, 1);

print "check nonce (nonce should be valid): ";
$r = NonceUtil::check(NONCE_SECRET, $nonce);
var_dump($r);
var_dump($nonce);

print "\n";

print "generating a nonce with a 3 second lifetime.\n";
$nonce = NonceUtil::generate(NONCE_SECRET, 3);

print "wait 4 seconds.\n";
sleep(4);

print "check nonce (nonce should be invalid): ";
$r = NonceUtil::check(NONCE_SECRET, $nonce);
var_dump($r);


?>