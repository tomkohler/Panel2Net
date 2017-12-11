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

function generateLastAct($scoreA, $scoreB, $foulA, $foulB, $timeoutA, $timeoutB, $period, $gamestatus, $timeout, $timer, $timestamp, $panelname, $timeoutDuration, $ballTime) {
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
	$ballTime = preg_replace('/[\x00-\x1F\x7F]/u', '', $ballTime);

	if($period == "" || $period == "0" || $period == ".") {
		$status = 1;
	} 


	if($timeoutA == "" || $timeoutB == "") {
		$status = 1;
	}

	if($foulA == "" || $foulB == "") {
		$status = 1;
	}

	if($scoreA == 0 || $scoreB == 0 || $scoreA > 140 || $scoreB > 140) {
		$status = 1;
	}


	if($status == 1) {
		die("An error has occured. The script couldn't continue");
	}

	$document = "<document><event><TeamA>Home</TeamA><TeamB>Away</TeamB><ScoreTeamA>$scoreA</ScoreTeamA><ScoreTeamB>$scoreB</ScoreTeamB><TeamFoulA>$foulA</TeamFoulA><TeamFoulB>$foulB</TeamFoulB><TimeOutA>$timeoutA</TimeOutA><TimeOutB>$timeoutB</TimeOutB><Quarter>Q$period</Quarter><StartStop>$gamestatus</StartStop><Timeout>$timeout</Timeout><ClockTime>$timer</ClockTime><ClockTimeOut>$timeoutDuration</ClockTimeOut><ShotClock>$ballTime</ShotClock><UTCTime>$timestamp</UTCTime></event></document>";
	$filename = $panelname.'-lastaction.xml';
	file_put_contents($filename, $document);
	return "<TeamA>Home</TeamA><TeamB>Away</TeamB><ScoreTeamA>$scoreA</ScoreTeamA><ScoreTeamB>$scoreB</ScoreTeamB><TeamFoulA>$foulA</TeamFoulA><TeamFoulB>$foulB</TeamFoulB><TimeOutA>$timeoutA</TimeOutA><TimeOutB>$timeoutB</TimeOutB><Quarter>Q$period</Quarter><StartStop>$gamestatus</StartStop><Timeout>$timeout</Timeout><ClockTime>$timer</ClockTime><ClockTimeOut>$timeoutDuration</ClockTimeOut><ShotClock>$ballTime</ShotClock>";
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
//SwissTiming Reader - 01/12/2017 - Thomas Gervaise - gervaise.thomas41@gmail.com

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


$mainInfo = explode("D", $myinput);
$arrayLength = count($mainInfo);
$mainInfo = $mainInfo[$arrayLength-2];
$homeScore = trim(substr($mainInfo, 5, 3));
$extScore = trim(substr($mainInfo, 8, 3));

if($homeScore == "   ") {
	$homeScore = 0;
} elseif($extScore == "   ") {
	$extScore = 0;
}

$homeFouls = substr($mainInfo, 11, 1);
$extFouls = substr($mainInfo, 12, 1);


$homeTimeout = substr($mainInfo, 13, 1);
$extTimeout = substr($mainInfo, 14, 1);


$period = substr($mainInfo, 15, 1);
$gameStatus = substr($mainInfo, 17, 1);
if($gameStatus == 1) { 
	$gameStatus = "START";
} else {
	$gameStatus = "STOP";
}
$timer = substr($mainInfo, 0, 5);
$timestamp = date('Y-m-d H:i:s', time());


$runningTimeout = substr($mainInfo, 19, 2);
$ballTime = substr($mainInfo, 21, 2);
$timeoutDuration = substr($mainInfo, 19, 2);
if($runningTimeout == "  ") {
	$runningTimeout = "No";
	$timeoutDuration = 0;
} else {
	$runningTimeout = "Yes";
}

if($ballTime == "  ") {
	$ballTime = 0;
}


$lastActionXML = generateLastAct($homeScore, $extScore, $homeFouls, $extFouls, $homeTimeout, $extTimeout, $period, $gameStatus, $runningTimeout, $timer, $timestamp, $panel_name, $timeoutDuration, $ballTime);

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


$homePoints = explode("F3", $myinput);
$arrayLength2 = count($homePoints);
$homePoints = $homePoints[$arrayLength2-2];
$homePlayers = explode("F1", $myinput);
$arrayLength3 = count($homePlayers);
$homePlayers = $homePlayers[$arrayLength3-2];
$extPoints = explode("F4", $myinput);
$arrayLength4 = count($extPoints);
$extPoints = $extPoints[$arrayLength4-2];
$extPlayers = explode("F2", $myinput);
$arrayLength5 = count($extPlayers);
$extPlayers = $extPlayers[$arrayLength5-2];

$playerA = "";
for($i = 1; $i<=16; $i++) {
	$fouls = substr($homePlayers, 0+($i*3)-1, 1);
	if($fouls == " ") {
		$fouls = 0;
	}
	$shirtnum = substr($homePlayers, 0+($i*3)-3, 2);
	$shirtnum = str_replace("q", "1", $shirtnum);
	$shirtnum = str_replace("`", "", $shirtnum);
	if($shirtnum == "  ") {
		$shirtnum = "NA";
	}
	$number = substr($homePoints, 0+($i*2)-2, 2);
	if($number == "  ") {
		$number = 0;
	}
	$number = trim($number);
	$fouls = trim($fouls);
	$playerA .= "<ShirtNo>$shirtnum</ShirtNo><Points>$number</Points><Fouls>$fouls</Fouls>";
}

$playerB = "";
for($i = 1; $i<=13; $i++) {
	$fouls = substr($extPlayers, 0+($i*3)-1, 1);
	if($fouls == " ") {
		$fouls = 0;
	}
	$shirtnum = substr($extPlayers, 0+($i*3)-3, 2);
	$shirtnum = str_replace("q", "1", $shirtnum);
	$shirtnum = str_replace("`", "", $shirtnum);
	if($shirtnum == "  ") {
		$shirtnum = "NA";
	}
	$number = substr($extPoints, 0+($i*2)-2, 2);
	if($number == "  ") {
		$number = 0;
	}
	$number = trim($number);
	$fouls = trim($fouls);
	$playerB .= "<ShirtNo>$shirtnum</ShirtNo><Points>$number</Points><Fouls>$fouls</Fouls>";
}


$docRest = str_replace("%PINFOA%", $playerA, $docRest);
$docRest = str_replace("%PINFOB%", $playerB, $docRest);

$fileContent = dbw("<PlayerinfoA>","</PlayerinfoB>",$fileContent);
$lastPlayerPos = strrpos($fileContent, "</playerinfo>");
$fileContent = substr_replace($fileContent, $docRest, $lastPlayerPos, 0);

file_put_contents($fileName, $fileContent);






?>