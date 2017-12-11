<?php

function GetBetween($content, $start, $end) {
	$r = explode($start, $content);
	if (isset($r[1])) {
		$rcount = count($r);
		$r = explode($end, $r[$rcount-2]);
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

	if($period == "" || $period == "0") {
		$status = 1;
	} 

	if(strpos($timer, ":") !== false) {
		$temptimer = str_replace(":", "", $timer);
		if(strlen($temptimer) != 4) {
			$status = 1;
		}
	} elseif(strpos($timer, ",") !== false) {
		$temptimer = str_replace(",", "", $timer);
		if(strlen($temptimer) != 3 && strlen($temptimer) != 2) {
			$status = 1;
		}
	} else {
		$status = 1;
	}


	if($timeoutA == "" || $timeoutB == "") {
		$status = 1;
	}

	if($foulA == "" || $foulB == "") {
		$status = 1;
	}

	if($scoreA == 0 || $scoreB == 0) {
		$status = 1;
	}

	if($status == 1) {
		die("An error has occured. The script couldn't continue");
	}

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
//Mobatime Reader - 31/10/2017 - Thomas Gervaise - gervaise.thomas41@gmail.com

foreach(getallheaders() as $name => $value) {
	if($name == "Device_ID") {
		$panel_name = $value;
	}
}

if(strpos(file_get_contents("php://input"), "01 7F 02 47") !== false) {
	$content = file_get_contents("php://input");
} else {
	$content = strtoupper(bin2hex(file_get_contents("php://input")));
	$content = chunk_split($content, 2, ' ');
}

$hexfile = file_get_contents($panel_name.'-'.date('Y-m-d', time()).'-packets.txt');
$hexfile .= $content;
$logfile = file_get_contents("logs.txt");
$logfile .= "\n [".date('H:i:s', time())."] - Received ".file_get_contents("php://input");
file_put_contents("logs.txt", $logfile);
file_put_contents($panel_name.'-'.date('Y-m-d', time()).'-packets.txt', $hexfile);
$myinput = $hexfile;
//$myinput = file_get_contents("matchrecord.txt");

$getScore = GetBetween($myinput, "01 7F 02 47 33 30 35 ", "01 7F 02 47");
$getScore = explode(" ", $getScore);
$homeScore = $getScore[0].$getScore[1].$getScore[2];
$homeScore = trim(hex2str($homeScore));
if(!is_numeric($homeScore)) {
	$homeScore = 0;
}
$extScore = $getScore[3].$getScore[4].$getScore[5];
$extScore = trim(hex2str($extScore));
if(!is_numeric($extScore)) {
	$extScore = 0;
}

if($homeScore == "") {
	$homeScore = 0;
} elseif($extScore == "") {
	$extScore = 0;
}

$fouls = GetBetween($myinput, "01 7F 02 47 33 31 35 ", "01 7F 02 47");
$fouls = explode(" ", $fouls);
$homeFouls = hex2str($fouls[1]);
$extFouls = hex2str($fouls[3]);

$timeout = GetBetween($myinput, "01 7F 02 47 31 38 ", "01 7F 02 47");
$timeout = explode(" ",$timeout);
$homeTimeout = hex2str($timeout[6]);
$extTimeout = hex2str($timeout[7]);
$period = hex2str($timeout[10]);
if(!is_numeric($period)) {
	$period = 0;
}
$gameStatus = $timeout[0];
if($gameStatus == 80) { 
	$gameStatus = "START";
} else {
	$gameStatus = "STOP";
}
$tmp1 = $timeout[2];
$tmp2 = $timeout[3];
$tmp3 = $timeout[4];
$tmp4 = $timeout[5];
if(!is_numeric(hex2str($tmp1))) {
	$tmp1 = 30;
} elseif(!is_numeric(hex2str($tmp2))) {
	$tmp2 = 30;
} elseif(!is_numeric(hex2str($tmp3))) {
	$tmp3 = 30;
} elseif(!is_numeric(hex2str($tmp4))) {
	$tmp4 = 30;
}

if($timeout[4] == '44') {
	$timer = trim(hex2str($tmp1.$tmp2.'2E'.$tmp4));
} else {
	$timer = trim(hex2str($tmp1.$tmp2.'3A'.$tmp3.$tmp4));
}
$timestamp = date('Y-m-d H:i:s', time());

$runningTimeout = GetBetween($myinput, "01 7F 02 47 31 39 35 ", "01 7F 02 47");
$runningTimeout = explode(" ", $runningTimeout);
$timeoutTimer = hex2str($runningTimeout[2] . $runningTimeout[3]);
if($timeoutTimer == "30") {
	$runningTimeout = "Yes";
} else {
	$runningTimeout = "No";
}

$timeouts = GetBetween($myinput, "01 7F 02 47 35 30 ", "01 7F 02 47");
$timeouts = explode(" ", $timeouts);
$flag = sprintf("%08d", decbin(hexdec($timeouts[0])));
$flag = substr($flag, 3, 1);
if($flag == 1) {
	$shotclock = hex2str($timeouts[1]).'.'.hex2str($timeouts[2]);
} else {
	$shotclock = hex2str($timeouts[1].$timeouts[2]);
}



$lastActionXML = generateLastAct($homeScore, $extScore, $homeFouls, $extFouls, $homeTimeout, $extTimeout, $period, $gameStatus, $runningTimeout, $timer, $timestamp, $panel_name, $timeoutTimer, $shotclock);

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


$homeFouls = GetBetween($myinput, "01 7F 02 47 33 33 35 ", "01 7F 02 47");
$homeFouls = explode(" ", $homeFouls);
$extFouls = GetBetween($myinput, "01 7F 02 47 33 34 35 ", "01 7F 02 47");
$extFouls = explode(" ", $extFouls);
$homeNumbers = GetBetween($myinput, "01 7F 02 47 33 37 ", "01 7F 02 47");
$homeNumbers = explode(" ", $homeNumbers);
$extNumbers = GetBetween($myinput, "01 7F 02 47 33 38 ", "01 7F 02 47");
$extNumbers = explode(" ", $extNumbers);

$playerA = "";
for($i = 4; $i<=15; $i++) {

	$fouls = $homeFouls[$i-4];
	if($fouls == "80") {
		$fouls = 0;
	} elseif($fouls == "84") {
		$fouls = 1;
	} elseif($fouls == "86") {
		$fouls = 2;
	} elseif($fouls == "87") {
		$fouls = 3;
	} elseif($fouls == "A7") {
		$fouls = 4;
	} elseif($fouls == "B7") {
		$fouls = 5;
	} elseif($fouls == "BF") {
		$fouls = 6;
	} else {
		$fouls = "Unknown";
	}
	$number = hex2str($homeNumbers[$i-4].$homeNumbers[$i-3]);
	$playerA .= "<ShirtNo>$number</ShirtNo><Points>0</Points><Fouls>$fouls</Fouls>";
}

$playerB = "";
for($i = 4; $i<=15; $i++) {

	$fouls = $homeFouls[$i-4];
	if($fouls == "80") {
		$fouls = 0;
	} elseif($fouls == "84") {
		$fouls = 1;
	} elseif($fouls == "86") {
		$fouls = 2;
	} elseif($fouls == "87") {
		$fouls = 3;
	} elseif($fouls == "A7") {
		$fouls = 4;
	} elseif($fouls == "B7") {
		$fouls = 5;
	} elseif($fouls == "BF") {
		$fouls = 6;
	} else {
		$fouls = "Unknown";
	}
	$number = hex2str($homeNumbers[$i-4].$homeNumbers[$i-3]);
	$playerB .= "<ShirtNo>$number</ShirtNo><Points>0</Points><Fouls>$fouls</Fouls>";
}

$getScores = explode("01 7F 02 47 35 36 35 ", $myinput);
foreach($getScores as $score) {
	$score = explode("01 7F", $score);
	$score = explode(" ", $score[0]);
	$playerId = hex2str($score[1].$score[2]);
	$stringtofind = "$playerId</ShirtNo><Points>";
	$lenstring = count($stringtofind);
	if(hex2str($score[0]) == 1) {
		$position = strpos($playerA, $stringtofind);
		$playerA = substr_replace($playerA, hex2str($score[3].$score[4]), $position+$count, 0);
	} elseif(hex2str($score[0]) == 2) {
		$position = strpos($playerB, $stringtofind);
		$playerB = substr_replace($playerB, hex2str($score[3].$score[4]), $position+$count, 0);
	} 
}

$docRest = str_replace("%PINFOA%", $playerA, $docRest);
$docRest = str_replace("%PINFOB%", $playerB, $docRest);

$fileContent = dbw("<PlayerinfoA>","</PlayerinfoB>",$fileContent);
$lastPlayerPos = strrpos($fileContent, "</playerinfo>");
$fileContent = substr_replace($fileContent, $docRest, $lastPlayerPos, 0);

file_put_contents($fileName, $fileContent);






?>