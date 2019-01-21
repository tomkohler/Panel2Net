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
	// TK Insert: initialise status variable
	$status = 0;
	$scoreA = preg_replace('/[\x00-\x1F\x7F]/u', '', $scoreA);
	$scoreB = preg_replace('/[\x00-\x1F\x7F]/u', '', $scoreB);
	$foulA = preg_replace('/[\x00-\x1F\x7F]/u', '', $foulA);
	// TK Insert - Symbolic Foul Element
	$charact = 42;
	$charact2 = 45;
	$BonusA = ' ';
	$BonusB = ' ';
	switch ($foulA) {
		case 1:
			$foulAS = chr($charact);
			$foulAS2 = chr($charact2);
			break;
		case 2:
			$foulAS = chr($charact).chr($charact);
			$foulAS2 = chr($charact2).chr($charact2);
			break;
		case 3:
			$foulAS = chr($charact).chr($charact).chr($charact);
			$foulAS2 = chr($charact2).chr($charact2).chr($charact2);
			break;
		case 4:
			$foulAS = chr($charact).chr($charact).chr($charact).chr($charact);
			$foulAS2 = chr($charact2).chr($charact2).chr($charact2).chr($charact2);
			break;
		case 5:
			$foulAS = chr($charact).chr($charact).chr($charact).chr($charact).chr($charact);
			$foulAS2 = chr($charact2).chr($charact2).chr($charact2).chr($charact2).chr($charact2);
			$BonusA = 'Bonus';
			break;
		default:
			$foulAS = ' ';
			$foulAS2 = ' ';
			break;
	}
	$foulB = preg_replace('/[\x00-\x1F\x7F]/u', '', $foulB);
	// TK Insert - Symbolic Foul Element
	switch ($foulB) {
		case 1:
			$foulBS = chr($charact);
			$foulBS2 = chr($charact2);
			break;
		case 2:
			$foulBS = chr($charact).chr($charact);
			$foulBS2 = chr($charact2).chr($charact2);
			break;
		case 3:
			$foulBS = chr($charact).chr($charact).chr($charact);
			$foulBS2 = chr($charact2).chr($charact2).chr($charact2);
			break;
		case 4:
			$foulBS = chr($charact).chr($charact).chr($charact).chr($charact);
			$foulBS2 = chr($charact2).chr($charact2).chr($charact2).chr($charact2);
			break;
		case 5:
			$foulBS = chr($charact).chr($charact).chr($charact).chr($charact).chr($charact);
			$foulBS2 = chr($charact2).chr($charact2).chr($charact2).chr($charact2).chr($charact2);
			$BonusB = 'Bonus';
			break;
		default:
			$foulBS = ' ';
			$foulBS2 = ' ';
			break;
	}
	
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
	// TK insert: improved rule to detect faulty records
	if (!is_numeric($period)) {
		$period = 0;
	} 
	
	if (!is_numeric($shotClock)) {
		$shotClock = 0;
	}
		
	if (!is_numeric($timeoutA))  {
		$timeoutA = 0;
	}
	
	if (!is_numeric($timeoutB)) {
		$timeoutB = 0;
	}
	
	if (!is_numeric($foulA))  {
		$foulA = 0;
	}
	
	if (!is_numeric($foulB)) {
		$foulB = 0;
	}
	
	if (!is_numeric($scoreA))  {
		$scoreA = 0;
	}
	
	if (!is_numeric($scoreB)) {
		$scoreB = 0;
	}
	
	// Timer > 1 minute with : divider
	if (substr($timer,2,1) == ':') {
		if ((!is_numeric(substr($timer,0,2))) or (!is_numeric(substr($timer,3,2)))) {
			$status = 5;
		}
	}
	// Timer < 1 minute with . divider
	if (strpos(trim($timer),'.') !== false) {
		if (!is_numeric(trim($timer))) {
			$status = 6;
		}
	}
	
	//$temp = file_get_contents("debugswiss.txt");
	//$temp .= $status."\n";
	//file_put_contents("debugswiss.txt", $temp);
	
	//file_put_contents("debut.txt", $status);
	if($status > 0) {
		die("Invalid Input. Code: ".$status." - Aborted...");
		
	}
	
	// make START STOP sign
	if ($gamestatus == "STOP") {
		$gamestatus = chr(46);
	} else {
		$gamestatus = " ";
	}
	
	// push outside Timeout the normal clock
	if ($timeout == "No") {
		$timeoutDuration = $timer;
	}
	
	// test to insert the +1, +2, +3 in the score changes
	if (file_exists($panelname.'-lastaction.xml')) {
		$document = simplexml_load_file($panelname.'-lastaction.xml');
		//$temp = file_get_contents("score.txt");
		
		// treat ScoreA
		$prevscoreA = $document->event->ScoreTeamA;
		$scoreA2 = $document->event->ScoreTeamA2;
		$scoreAT = $document->event->ScoreTeamAT;
		if (!is_numeric($scoreAT)){
			$scoreAT = (int)$scoreAT;
		}
		$prevscoreB = $document->event->ScoreTeamB;
		$scoreB2 = $document->event->ScoreTeamB2;
		$scoreBT = $document->event->ScoreTeamBT;
		if (!is_numeric($scoreBT)){
			$scoreBT = (int)$scoreBT;
		}	
	
		//$temp .= "Pre: ".$prevscoreA."-".$scoreA."->".$scoreA2."-".$scoreAT."--".$prevscoreB."-".$scoreB."->".$scoreB2."-".$scoreBT."\n";
		if ((($scoreA - (int)$prevscoreA) > 0) and (($scoreA - (int)$prevscoreA) <= 3)) {
			// new difference, display
			$scoreA2 = "+".($scoreA - (int)$prevscoreA);
			// number of cycles that the +x should stand before returning to normal score
			$scoreAT = 8;
			//$temp .= "Case 1A\n";
		} elseif ($scoreAT > 0) {
			// old difference, display until ScoreAT decremented
			$scoreAT = $scoreAT - 1;
			//$temp .= "Case 2A\n";
		} else {
			// stop display
			$scoreA2 = $scoreA;
			//$temp .= "Case 3A\n";
		}
		
		
		if ((($scoreB - (int)$prevscoreB) > 0) and (($scoreB - (int)$prevscoreB) <= 3)) {
			// new difference, display
			$scoreB2 = "+".($scoreB - (int)$prevscoreB);
			// number of cycles that the +x should stand before returning to normal score
			$scoreBT = 8;
			//$temp .= "Case 1B\n";
		} elseif ($scoreBT > 0) {
			// old difference, display until ScoreAT decremented
			$scoreBT = $scoreBT - 1;
			//$temp .= "Case 2B\n";
		} else {
			// stop display
			$scoreB2 = $scoreB;
			//$temp .= "Case 3B\n";
		}
		
		//$temp .= "Post: ".$prevscoreA."-".$scoreA."->".$scoreA2."-".$scoreAT."--".$prevscoreB."-".$scoreB."->".$scoreB2."-".$scoreBT."\n";
		//$temp .= "-->".$scoreA."-".$scoreB."\n";
		//file_put_contents("score.txt", $temp);
	}  
	
	
	$document = "<document><event><TeamA>Home</TeamA><TeamB>Away</TeamB><ScoreTeamA>$scoreA</ScoreTeamA><ScoreTeamA2>$scoreA2</ScoreTeamA2><ScoreTeamAT>$scoreAT</ScoreTeamAT><ScoreTeamB>$scoreB</ScoreTeamB><ScoreTeamB2>$scoreB2</ScoreTeamB2><ScoreTeamBT>$scoreBT</ScoreTeamBT><TeamFoulA>$foulA</TeamFoulA><TeamFoulB>$foulB</TeamFoulB><TeamFoulAS>$foulAS</TeamFoulAS><TeamFoulBS>$foulBS</TeamFoulBS><TeamFoulAS2>$foulAS2</TeamFoulAS2><TeamFoulBS2>$foulBS2</TeamFoulBS2><BonusA>$BonusA</BonusA><BonusB>$BonusB</BonusB><TimeOutA>$timeoutA</TimeOutA><TimeOutB>$timeoutB</TimeOutB><Quarter>Q$period</Quarter><StartStop>$gamestatus</StartStop><Timeout>$timeout</Timeout><ClockTime>$timer</ClockTime><ClockTimeOut>$timeoutDuration</ClockTimeOut><ShotClock>$shotClock</ShotClock><UTCTime>$timestamp</UTCTime></event></document>";
	$filename = $panelname.'-lastaction.xml';
	file_put_contents($filename, $document);
		
	return "<TeamA>Home</TeamA><TeamB>Away</TeamB><ScoreTeamA>$scoreA</ScoreTeamA><ScoreTeamA2>$scoreA2</ScoreTeamA2><ScoreTeamAT>$scoreAT</ScoreTeamAT><ScoreTeamB>$scoreB</ScoreTeamB><ScoreTeamB2>$scoreB2</ScoreTeamB2><ScoreTeamBT>$scoreBT</ScoreTeamBT><TeamFoulA>$foulA</TeamFoulA><TeamFoulB>$foulB</TeamFoulB><TeamFoulAS>$foulAS</TeamFoulAS><TeamFoulBS>$foulBS</TeamFoulBS><TeamFoulAS2>$foulAS2</TeamFoulAS2><TeamFoulBS2>$foulBS2</TeamFoulBS2><BonusA>$BonusA</BonusA><BonusB>$BonusB</BonusB><TimeOutA>$timeoutA</TimeOutA><TimeOutB>$timeoutB</TimeOutB><Quarter>Q$period</Quarter><StartStop>$gamestatus</StartStop><Timeout>$timeout</Timeout><ClockTime>$timer</ClockTime><ClockTimeOut>$timeoutDuration</ClockTimeOut><ShotClock>$shotClock</ShotClock>";
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
//Enhanced by Thomas Kohler since
foreach(getallheaders() as $name => $value) {
	if($name == "Device_ID") {
		$panel_name = $value;
	}
}
$content = file_get_contents("php://input");
$hexfile = file_get_contents($panel_name.'-'.date('Y-m-d', time()).'-packets.txt');
$hexfile .= $content;
//$logfile = file_get_contents("logs.txt");
//$logfile .= "\n [".date('H:i:s', time())."] - Received ".file_get_contents("php://input");
//file_put_contents("logs.txt", $logfile);
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
$shotClock = substr($mainInfo, 21, 2);
$timeoutDuration = substr($mainInfo, 19, 2);
if($runningTimeout == "  ") {
	$runningTimeout = "No";
	$timeoutDuration = 0;
} else {
	$runningTimeout = "Yes";
}
if($shotClock == "  ") {
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
$playerA2 = "Home (#-Pts-Fouls): ";
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
	if (($number > 0) or ($fouls > 0)) {
		$playerA2 .= "#$shirtnum $number-$fouls   ";
	}
	$playerA .= "<ShirtNo>$shirtnum</ShirtNo><Points>$number</Points><Fouls>$fouls</Fouls>";
}

$playerB = "";
$playerB2 = "Away (#-Pts-Fouls): ";
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
	if (($number > 0) or ($fouls > 0)) {
		$playerB2 .= "#$shirtnum $number-$fouls   ";
	}
	$playerB .= "<ShirtNo>$shirtnum</ShirtNo><Points>$number</Points><Fouls>$fouls</Fouls>";
}
file_put_contents($panel_name.'-statistics.xml', "<home>".$playerA."</home>\n<away>".$playerB."</away>");
file_put_contents($panel_name.'-statistics2.xml', "<html>".$playerA2.$playerB2."</html>");
$docRest = str_replace("%PINFOA%", $playerA, $docRest);
$docRest = str_replace("%PINFOB%", $playerB, $docRest);
$fileContent = dbw("<PlayerinfoA>","</PlayerinfoB>",$fileContent);
$lastPlayerPos = strrpos($fileContent, "</playerinfo>");
$fileContent = substr_replace($fileContent, $docRest, $lastPlayerPos, 0);
//file_put_contents($fileName, $fileContent);
?>