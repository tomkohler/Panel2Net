<?php

function GetBetween($content, $start, $end) {
	$r = explode($start, $content);
	if (isset($r[1])) {
		$r = explode($end, end($r));
		return $r[0];
	}
	return '';
}


function hex2str($hex) {
    $str = '';
    for($i=0;$i<strlen($hex);$i+=2) $str .= chr(hexdec(substr($hex,$i,2)));
    return $str;
}

function generateLastAct($scoreA, $scoreB, $foulA, $foulB, $timeoutA, $timeoutB, $period, $gamestatus, $timeout, $timer, $timestamp, $panelname, $timeoutDuration, $shotClock) {

	$scoreA = preg_replace('/[\x00-\x1F\x7F]/u', '', $scoreA);
	$scoreB = preg_replace('/[\x00-\x1F\x7F]/u', '', $scoreB);
	$foulA = preg_replace('/[\x00-\x1F\x7F]/u', '', $foulA);
	$foulB = preg_replace('/[\x00-\x1F\x7F]/u', '', $foulB);
	$timeoutA = preg_replace('/[\x00-\x1F\x7F]/u', '', $timeoutA);
	$timeoutB = preg_replace('/[\x00-\x1F\x7F]/u', '', $timeoutB);
	$period = preg_replace('/[\x00-\x1F\x7F]/u', '', $period);
	$gamestatus = preg_replace('/[\x00-\x1F\x7F]/u', '', $gamestatus);
	$timeout = preg_replace('/[\x00-\x1F\x7F]/u', '', $timeout);
	$timer = preg_replace('/[\x00-\x1F\x7F]/u', '', $timer);
	$timestamp = preg_replace('/[\x00-\x1F\x7F]/u', '', $timestamp);
	$panelname = preg_replace('/[\x00-\x1F\x7F]/u', '', $panelname);
	$timeoutDuration = preg_replace('/[\x00-\x1F\x7F]/u', '', $timeoutDuration);
	$shotClock = preg_replace('/[\x00-\x1F\x7F]/u', '', $shotClock);

	//if($period == "" || $period == "0" || $period == ".") {
	//	$status = 1;
	//} 


	//if($timeoutA == "" || $timeoutB == "") {
	//	$status = 1;
	//}

	//if($foulA == "" || $foulB == "") {
	//	$status = 1;
	//}

	//if($scoreA == 0 || $scoreB == 0 || $scoreA > 140 || $scoreB > 140) {
	//	$status = 1;
	//}

	//if($status == 1) {
	//	die("An error has occured. The script couldn't continue");
	//}

	$document = "<document><event><TeamA>Home</TeamA><TeamB>Away</TeamB><ScoreTeamA>$scoreA</ScoreTeamA><ScoreTeamB>$scoreB</ScoreTeamB><TeamFoulA>$foulA</TeamFoulA><TeamFoulB>$foulB</TeamFoulB><TimeOutA>$timeoutA</TimeOutA><TimeOutB>$timeoutB</TimeOutB><Quarter>Q$period</Quarter><StartStop>$gamestatus</StartStop><Timeout>$timeout</Timeout><ClockTime>$timer</ClockTime><ClockTimeOut>$timeoutDuration</ClockTimeOut><ShotClock>$shotClock</ShotClock><UTCTime>$timestamp</UTCTime></event></document>";
	$filename = $panelname.'-lastaction.xml';
	file_put_contents($filename, $document);
	return "<TeamA>Home</TeamA><TeamB>Away</TeamB><ScoreTeamA>$scoreA</ScoreTeamA><ScoreTeamB>$scoreB</ScoreTeamB><TeamFoulA>$foulA</TeamFoulA><TeamFoulB>$foulB</TeamFoulB><TimeOutA>$timeoutA</TimeOutA><TimeOutB>$timeoutB</TimeOutB><Quarter>Q$period</Quarter><StartStop>$gamestatus</StartStop><Timeout>$timeout</Timeout><ClockTime>$timer</ClockTime><ClockTimeOut>$timeoutDuration</ClockTimeOut><ShotClock>$shotClock</ShotClock>";
}

function dbw($beginning, $end, $string) {
  $beginningPos = strpos($string, $beginning);
  $endPos = strpos($string, $end);
  if ($beginningPos === false || $endPos === false) {
    return $string;
  }

  $textToDelete = substr($string, $beginningPos, ($endPos + strlen($end)) - $beginningPos);

  return str_replace($textToDelete, '', $string);
}
//Stramatel Reader - 01/12/2017 - Thomas Gervaise - gervaise.thomas41@gmail.com

foreach(getallheaders() as $name => $value) {
	if($name == "Device_ID") {
		$panel_name = $value;
	}
}

$content = file_get_contents("php://input");


$hexfile = file_get_contents($panel_name.'-'.date('Y-m-d', time()).'-packets.txt');
$hexfile .= $content;
$logfile = file_get_contents("logs.txt");
$logfile .= "\n [".date('H:i:s', time())."] - Received ".file_get_contents("php://input");
file_put_contents("logs.txt", $logfile);
file_put_contents($panel_name.'-'.date('Y-m-d', time()).'-packets.txt', $hexfile);
$myinput = $hexfile;


$mainInfo = explode(chr(248)."3", $myinput);
$length1 = count($mainInfo);
$mainInfo = $mainInfo[$length1-2];
$homeScore = trim(substr($mainInfo, 6, 3));
$extScore = trim(substr($mainInfo, 9, 3));

if($homeScore == "   ") {
	$homeScore = 0;
} elseif($extScore == "   ") {
	$extScore = 0;
}

$homeFouls = substr($mainInfo, 13, 1);
$extFouls = substr($mainInfo, 14, 1);


$homeTimeout = substr($mainInfo, 15, 1);
$extTimeout = substr($mainInfo, 16, 1);


$period = substr($mainInfo, 12, 1);
$gameStatus = substr($mainInfo, 18, 1);
if($gameStatus == 1) { 
	$gameStatus = "STOP";
} else {
	$gameStatus = "START";
}
$testCond = trim(substr($mainInfo, 4, 2));
if(strlen($testCond) == 1) {
	$timer = substr($maininfo, 2, 2).'.'.substr($mainInfo, 4, 2);
} else {
	$timer = substr($mainInfo, 2, 2).':'.substr($mainInfo, 4, 2);
}
$timestamp = date('Y-m-d H:i:s', time());


$runningTimeout = substr($mainInfo, 19, 1);
if($runningTimeout == " ") {
	$runningTimeout = "No";
} else {
	$runningTimeout = "Yes";
}

$timeoutDuration = substr($mainInfo, 44, 2);
$shotClock = substr($mainInfo, 46, 2);

if($timeoutDuration == "  ") {
	$timeoutDuration = 0;
} elseif($shotClock == "  ") {
	$shotClock = 0;
}



$lastActionXML = generateLastAct($homeScore, $extScore, $homeFouls, $extFouls, $homeTimeout, $extTimeout, $period, $gameStatus, $runningTimeout, $timer, $timestamp, $panel_name, $timeoutDuration, $shotClock);

$fileName = $panel_name.'-'.date('Y-m-d', time()).'.xml';
if(file_exists($fileName)) {
	$fileContent = file_get_contents($fileName);
} else {
	$fileContent = "<document><playerinfo></playerinfo><playbyplay></playbyplay></document>";
	file_put_contents($fileName, $fileContent);
}
if(strpos($fileContent, $lastActionXML)) {
	echo "No new event detected. Skipping this part";
} else {
	$lastEventPos = strrpos($fileContent, "</playbyplay>");
	$fileContent = substr_replace($fileContent, "<event>".$lastActionXML."<UTCTime>$timestamp</UTCTime></event>", $lastEventPos, 0);
}

$docRest = '<PlayerinfoA>%PINFOA%</PlayerinfoA><PlayerinfoB>%PINFOB%</PlayerinfoB>';


$homePoints = explode(chr(248)."8", $myinput);
$length2 = count($homePoints);
$homePoints = $homePoints[$length2-2];
$extPoints = explode(chr(248)."7", $myinput);
$length3 = count($extPoints);
$extPoints = $extPoints[$length3-2];

$playerA = "";
for($i = 1; $i<=13; $i++) {
	$fouls = substr($mainInfo, 19+$i, 1);
	$number = substr($homePoints, 19+($i*2)-1, 2);
	if($number == "  ") {
		$number = 0;
	}
	$number = trim($number);
	$fouls = trim($fouls);
	$playerA .= "<ShirtNo>$i</ShirtNo><Points>$number</Points><Fouls>$fouls</Fouls>";
}

$playerB = "";
for($i = 1; $i<=13; $i++) {
	$fouls = substr($mainInfo, 31+$i, 1);
	$number = substr($extPoints, 19+($i*2)-1, 2);
	if($number == "  ") {
		$number = 0;
	}
	$number = trim($number);
	$fouls = trim($fouls);
	$playerB .= "<ShirtNo>$i</ShirtNo><Points>$number</Points><Fouls>$fouls</Fouls>";
}


$docRest = str_replace("%PINFOA%", $playerA, $docRest);
$docRest = str_replace("%PINFOB%", $playerB, $docRest);

$fileContent = dbw("<PlayerinfoA>","</PlayerinfoB>",$fileContent);
$lastPlayerPos = strrpos($fileContent, "</playerinfo>");
$fileContent = substr_replace($fileContent, $docRest, $lastPlayerPos, 0);

file_put_contents($fileName, $fileContent);






?>