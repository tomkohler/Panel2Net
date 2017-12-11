<?php


$data = file_get_contents("php://input");
if(strpos($data, chr("0xF8")) !== false) {
	include('stramatel.php');
} elseif(strpos($data, chr("0x01")) !== false) {
	include('mobatime.php');
} else {
	include('swisstiming.php');
}


?>