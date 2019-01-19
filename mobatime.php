<?php

// for whatever reason the programmer has taken the 2nd last sequence (and not the last)
// more testing is needed before replacing this also with the last sequence
function GetBetween($content, $start, $end) {
	$r = explode($start, $content);
	if (isset($r[1])) {
		$rcount = count($r);
		$r = explode($end, $r[$rcount-2]);
		return $r[0];
	}
	return '';
}

// new function that takes the last sequence available
function GetBetween2($content, $start, $end) {
	$r = explode($start, $content);
	if (isset($r[1])) {
		$rcount = count($r);
		// changed from -2 to -1
		$r = explode($end, $r[$rcount-1]);
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

//Mobatime Reader - 31/10/2017 - Thomas Gervaise - gervaise.thomas41@gmail.com
// TK: variable init
$panel_name = "";
$SBTime = 0;
foreach(getallheaders() as $name => $value) {
	// TK: gets Device_ID from header on which to build the filename
	if($name == "Device_ID") {
		$panel_name = $value;
	}
	// TK: gets the original UTC time from Scorebug that is closer to original time than the server time
	if($name == "UTCTime") {
		// TK: needs to be a numeric timestamp
		if (is_numeric($SBTime)) {
			$SBTime = $value;
		}
	}
}
if(strpos(file_get_contents("php://input"), "01 7F 02 47") !== false) {
	$content = file_get_contents("php://input");
} else {
	$content = strtoupper(bin2hex(file_get_contents("php://input")));
	$content = chunk_split($content, 2, ' ');
	$content = $content;
}

$hexfile = file_get_contents($panel_name.'-'.date('Y-m-d', time()).'-packets.txt');
$hexfile .= $content;
$logfile = file_get_contents("logs.txt");
$logfile .= "\n [".date('H:i:s', time())."] - Received ".$panel_name.'-'.file_get_contents("php://input");
file_put_contents("logs.txt", $logfile);
file_put_contents($panel_name.'-'.date('Y-m-d', time()).'-packets.txt', $hexfile);
$myinput = $hexfile;

$getScore = GetBetween($myinput, "01 7F 02 47 33 30 35 ", "01 7F 02 47");
$getScore = explode(" ", $getScore);
$homeScore = 0;
$extScore = 0;
if (count($getScore) > 5) {
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
	}
	if($extScore == "") {
		$extScore = 0;
	}
}
	
$fouls = GetBetween($myinput, "01 7F 02 47 33 31 35 ", "01 7F 02 47");
$fouls = explode(" ", $fouls);
$homeFouls = 0;
$extFouls = 0;
if (count($fouls) > 3) {
	$homeFouls = hex2str($fouls[1]);
	$extFouls = hex2str($fouls[3]);	
}

$timeout = GetBetween($myinput, "01 7F 02 47 31 38 ", "01 7F 02 47");
$timeout = explode(" ",$timeout);
$homeTimeout = 0;
$extTimeout = 0;
$period = 0;
$gameStatus = "STOP";
$timer = "";
if (count($timeout) > 10) {
	$homeTimeout = hex2str($timeout[6]);
	$extTimeout = hex2str($timeout[7]);
	$period = hex2str($timeout[10]);
	if(!is_numeric($period)) {
		$period = 0;
	}
	$gameStatus = $timeout[0];
	$tmp1 = $timeout[2];
	$tmp2 = $timeout[3];
	$tmp3 = $timeout[4];
	$tmp4 = $timeout[5];

	// TK: Debug
	// $temper = file_get_contents("debugtimer.txt");
	// $temper .= "\n3138 Sequence started\n";
	// $temper .= $gameStatus."-".$tmp1."-".$tmp2."-".$tmp3."-".$tmp4."\n";

	// ensure that all variables are numeric for calculations below
	// except if $tmp3 = 44, then we preserve this flag
	if(!is_numeric(hex2str($tmp1))) {
		$tmp1 = 30;
	} elseif(!is_numeric(hex2str($tmp2))) {
		$tmp2 = 30;
	} elseif($tmp3 == 44) {
		$tmp3 = 44;	
	} elseif(!is_numeric(hex2str($tmp3))) {
		$tmp3 = 30;
	} elseif(!is_numeric(hex2str($tmp4))) {
		$tmp4 = 30;
	}

	// $temper .= "3138 Sequence after cleaning\n";
	// $temper .= $gameStatus."-".$tmp1."-".$tmp2."-".$tmp3."-".$tmp4."\n";


	// TK: If the 31 38 record shows that we are about to enter the last minute (01:00) or already in it
	if ((($gameStatus == 80) and ($tmp1 == 30) and ($tmp2 == 31) and (($tmp3 == 30) or ($tmp3 == 44)) and ($tmp4 <= 31)) or ((($gameStatus == 90) or ($gameStatus == 92)) and ($tmp3 == 44))) {
		
		// calculate time
		if (($gameStatus == 90) or ($gameStatus == 92) or ($tmp3 == 44)) {
			// time arriving in SS.T format and disregard tenth of seconds in $tmp4
			$refSeconds = ($tmp1-30)*10 + ($tmp2-30) + ($tmp4-30)*0.1; 
		} else {
			// time arriving in MM:SS format
			$refSeconds = ($tmp1-30)*10*60 + ($tmp2-30)*60 + ($tmp3-30)*10 + ($tmp4-30); 
		}
		
		// $temper .= "3138 Reference Time\n";
		// $temper .= $refSeconds."\n";

		// get the last 33 36 record
		$getScore = GetBetween($myinput, "01 7F 02 47 33 36 ", "01 7F 02 47");
		$getScore = explode(" ", $getScore);
		$tmps1 = $getScore[0];
		$tmps2 = $getScore[1];
		$tmps3 = $getScore[2];
		
		// $temper .= "3336 Sequence started\n";
		// $temper .= $gameStatus."-".$tmps1."-".$tmps2."-".$tmps3."\n";

		// ensure that all variables are numeric for calculations below
		if(!is_numeric(hex2str($tmps1))) {
			$tmps1 = 30;
		} elseif(!is_numeric(hex2str($tmps2))) {
			$tmps2 = 30;
		} elseif(!is_numeric(hex2str($tmps3))) {
			$tmps3 = 30;
		}	
		
		// $temper .= "3336 Sequence after cleaning\n";
		// $temper .= $gameStatus."-".$tmps1."-".$tmps2."-".$tmps3."\n";
		
		// calculate time in 33 36 record that may not be zero and must be smaller than the reference time from 31 38
		// otherwise disregard 33 36
		$refSeconds2 = ($tmps1-30)*10 + ($tmps2-30) + ($tmps3-30)*0.1;
		
		// $temper .= "3336 Reference Time\n";
		// $temper .= $refSeconds2."\n";
		
		if (($refSeconds2 >= 0) and ($refSeconds2 <= $refSeconds)) {
			// use values from 33 36
			$tmp1 = $tmps1;
			$tmp2 = $tmps2;
			$tmp3 = 44;
			$tmp4 = $tmps3;
		} 
	}

	// $temper .= "Output\n";
	// $temper .= $tmp1."-".$tmp2."-".$tmp3."-".$tmp4."\n";

	if($tmp3 == 44) {
		// check if that is really needed
		// if ($tmp4 < 32) {
		// 	 $tmp4 = 30;
		// }
		$timer = trim(hex2str($tmp1.$tmp2.'2E'.$tmp4));
	} else {	
		$timer = trim(hex2str($tmp1.$tmp2.'3A'.$tmp3.$tmp4));
	}

	// $temper .= "Timer\n";
	// $temper .= $timer."\n";

	if($gameStatus == 80) { 
		$gameStatus = "START";
	} else {
		$gameStatus = "STOP";
	}
}

// TK: debug
// file_put_contents("debugtimer.txt", $temper);

// TK: put timestamp - originally the server time but better to take the scorebug time (without the latency)
if ($SBTime > 0) {
	$timestamp = date('Y-m-d H:i:s', $SBTime);
}
else {
	$timestamp = date('Y-m-d H:i:s', time());
}
// TK: debug
//$tsp = microtime(true);
//$tdiff = $tsp-$SBTime;
//$time_output = "SB: ".$SBTime." - Server: ".$tsp." - Diff: ".$tdiff;
//file_put_contents("timecomparison.txt", $time_output);
$runningTimeout = GetBetween($myinput, "01 7F 02 47 31 39 35 ", "01 7F 02 47");
$runningTimeout = explode(" ", $runningTimeout);
if (count($runningTimeout) > 3) {
	$timeoutTimer = hex2str($runningTimeout[2] . $runningTimeout[3]);
	if($timeoutTimer == "30") {
		$runningTimeout = "Yes";
	} else {
		$runningTimeout = "No";
	}	
} else {
	$runningTimeout = "No";
	$timeoutTimer = 0;
}

$timeouts = GetBetween($myinput, "01 7F 02 47 35 30 ", "01 7F 02 47");
$timeouts = explode(" ", $timeouts);
if (count($timeouts) > 2) {
	$flag = sprintf("%08d", decbin(hexdec($timeouts[0])));
	$flag = substr($flag, 3, 1);
	if($flag == 1) {
		$shotclock = hex2str($timeouts[1]).'.'.hex2str($timeouts[2]);
	} else {
		$shotclock = hex2str($timeouts[1].$timeouts[2]);
	}	
} else {
	$shotclock = 0;
}

$lastActionXML = generateLastAct($homeScore, $extScore, $homeFouls, $extFouls, $homeTimeout, $extTimeout, $period, $gameStatus, $runningTimeout, $timer, $timestamp, $panel_name, $timeoutTimer, $shotclock);
//debug
//$temp = file_get_contents('debug.txt');
$fileName = $panel_name.'-'.date('Y-m-d', time()).'.xml';
if(file_exists($fileName)) {
	$fileContent = file_get_contents($fileName);
} else {
	$fileContent = "<document><playerinfo></playerinfo><playbyplay></playbyplay></document>";
	file_put_contents($fileName, $fileContent);
}
if(strpos($fileContent, $lastActionXML)) {
	echo "No new event detected. Skipping this part";
	//debug
	//$temp .= "No new event detected. Skipping this part";
	
} else {
	//debug
	//$temp .= $lastActionXML;
	$lastEventPos = strrpos($fileContent, "</playbyplay>");
	$fileContent = substr_replace($fileContent, "<event>".$lastActionXML."<UTCTime>$timestamp</UTCTime></event>", $lastEventPos, 0);
}
//debugging data
//file_put_contents('debug.txt', $temp);
//$temp = file_get_contents("debugstats.txt");

$docRest = '<PlayerinfoA>%PINFOA%</PlayerinfoA><PlayerinfoB>%PINFOB%</PlayerinfoB>';
$homeFouls = GetBetween2($myinput, "01 7F 02 47 33 33 35 ", "01 7F 02 47");
$homeFoulsHx = $homeFouls;
//$temp .= "Home FoulsHx: ".$homeFoulsHx."\n";
$homeFouls = explode(" ", $homeFouls);
$extFouls = GetBetween2($myinput, "01 7F 02 47 33 34 35 ", "01 7F 02 47");
$extFoulsHx = $extFouls;
//$temp .= "Away FoulsHx: ".$extFoulsHx."\n";
$extFouls = explode(" ", $extFouls);
$homeNumbers = GetBetween2($myinput, "01 7F 02 47 33 37 ", "01 7F 02 47");
$homeNumbersHx = $homeNumbers;
//$temp .= "Home NumbersHx: ".$homeNumbersHx."\n";
$homeNumbers = explode(" ", $homeNumbers);
$extNumbers = GetBetween2($myinput, "01 7F 02 47 33 38 ", "01 7F 02 47");
$extNumbersHx = $extNumbers;
//$temp .= "Away NumbersHx: ".$extNumbersHx."\n";
$extNumbers = explode(" ", $extNumbers);
// TK: inserted $countmax to avoid counting outside bounds
$playerA = "";
$playerA2 = "Home (#-Pts-Fouls): ";
$countmaxF = count($homeFouls);
$countmaxN = count($homeNumbers);
//$temp .= "Hcountmax F: ".$countmaxF."; N: ".$countmaxN."\n";

for($i = 4; $i<=$countmaxF; $i++) {
	$number = 0;
	$scoreno = 0;
	
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
		$fouls = 0;
	}
	
	if (($countmaxN - (2*($i-4)+1)) > 0) {		
		// search the last matching 35 36 sequence to find the points shot by the home player
		$startsequence = "01 7F 02 47 35 36 35 31 ".$homeNumbers[2*($i-4)]." ".$homeNumbers[2*($i-4)+1]." ";
		//$temp .= "Home Startsequence: ".$startsequence."\n";
		$getScores = GetBetween2($myinput, $startsequence, "01 7F 02 47");
		//$temp .= "Away getScores: ".$getScores."\n";
		$getScores = explode(" ", $getScores);
		if (count($getScores) > 1) {
			$score10 = $getScores[0];
			$score1 = $getScores[1];
			if(!is_numeric(hex2str($score10)))
				$score10 = 30;
			if(!is_numeric(hex2str($score1))) 
				$score1 = 30;
			$scoreno = ($score10-30)*10 + ($score1-30);
			//$temp .= "Score10:".$score10."Score1:".$score1."-> scoreno: ".$scoreno;
		}
		else {
			$scoreno = 0;
		}
		
		if (($homeNumbers[2*($i-4)]) == 20) {
			$homeNumbers[2*($i-4)] = 30;
		}
		if (($homeNumbers[2*($i-4)+1]) == 20) {
			$homeNumbers[2*($i-4)+1] = 30;
		}	
		$number = ($homeNumbers[2*($i-4)]-30)*10 + ($homeNumbers[2*($i-4)+1]-30);
		//$temp .= "ExtNumber10:".$homeNumbers[2*($i-4)]." ".$homeNumbers[2*($i-4)+1]."-> number: ".$number."\n";
	}
	else {
		$number = 0;
	}
	
	if (($scoreno > 0) or ($fouls > 0)) {
		//$temp .= "HNumber: ".$number."; Score: ".$scoreno."; Fouls: ".$fouls."\n";
		$playerA2 .= "#$number $scoreno-$fouls   ";
	}
	$playerA .= "<Player><ShirtNo>$number</ShirtNo><Points>$scoreno</Points><Fouls>$fouls</Fouls></Player>\n";
}
// TK: section corrected as it referenced to homeFouls rather than extFouls (copy-paste error)
// TK: inserted $countmax to avoid counting outside bounds
$playerB = "";
$playerB2 = "Away (#-Pts-Fouls): ";
$countmaxF = count($extFouls);
$countmaxN = count($extNumbers);
//$temp .= "Acountmax F: ".$countmaxF."; N: ".$countmaxN."\n";

for($i = 4; $i<=$countmaxF; $i++) {
	$number = 0;
	$scoreno = 0;
	
	$fouls = $extFouls[$i-4];
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
		$fouls = 0;
	}
	
	if (($countmaxN - (2*($i-4)+1)) > 0) {
	// search the last matching 35 36 sequence to find the points shot by the away player
		$startsequence = "01 7F 02 47 35 36 35 32 ".$extNumbers[2*($i-4)]." ".$extNumbers[2*($i-4)+1]." ";
		//$temp .= "Away Startsequence: ".$startsequence."\n";
		$getScores = GetBetween2($myinput, $startsequence, "01 7F 02 47");
		//$temp .= "Away getScores: ".$getScores."\n";
		$getScores = explode(" ", $getScores);
		if (count($getScores) > 1) {
			$score10 = $getScores[0];
			$score1 = $getScores[1];
			if(!is_numeric(hex2str($score10)))
				$score10 = 30;
			if(!is_numeric(hex2str($score1))) 
				$score1 = 30;
			$scoreno = ($score10-30)*10 + ($score1-30);
			//$temp .= "Score10:".$score10."Score1:".$score1."-> scoreno: ".$scoreno;
		}
		else {
			$scoreno = 0;
		}
		
		if(!is_numeric(hex2str($extNumbers[2*($i-4)]))) {
			$extNumbers[2*($i-4)] = 30;
		}
		if(!is_numeric(hex2str($extNumbers[2*($i-4)+1]))) {
			$extNumbers[2*($i-4)+1] = 30;
		}	
		$number = ($extNumbers[2*($i-4)]-30)*10 + ($extNumbers[2*($i-4)+1]-30);
		//$temp .= "ExtNumber10:".$extNumbers[2*($i-4)]." ".$extNumbers[2*($i-4)+1]."-> number: ".$number."\n";
	}
	else {
		$number = 0;
	}
	if (($scoreno > 0) or ($fouls > 0)) {
		//$temp .= "ANumber: ".$number."; Score: ".$scoreno."; Fouls: ".$fouls."\n";
		$playerB2 .= "#$number $scoreno-$fouls   ";
	}
	$playerB .= "<Player><ShirtNo>$number</ShirtNo><Points>$scoreno</Points><Fouls>$fouls</Fouls></Player>\n";
}

if ((strlen($homeNumbersHx) > 4) and ((strlen($extNumbersHx) > 4))) {
	//file_put_contents("debugstats.txt", $temp);
	file_put_contents($panel_name.'-statistics.xml', "<home>".$playerA."</home>\n<away>".$playerB."</away>");
	file_put_contents($panel_name.'-statistics2.xml', "<html>".$playerA2.$playerB2."</html>");
}

/* 	//file_put_contents("players.txt", "Count: ".count($score)."Score: ".$score."\n");
	
	if (count($score) == 1){
		$playerId = hex2str($score[1]);
	}
	else {
		$playerId = hex2str($score[1].$score[2]);
	}
	$stringtofind = "$playerId</ShirtNo><Points>";
	//$lenstring = count($stringtofind);
	if(hex2str($score[0]) == 1) {
		$position = strpos($playerA, $stringtofind);
		$playerA = substr_replace($playerA, hex2str($score[3].$score[4]), $position, 0);
	} elseif(hex2str($score[0]) == 2) {
		$position = strpos($playerB, $stringtofind);
		$playerB = substr_replace($playerB, hex2str($score[3].$score[4]), $position, 0);
	}  
} */

$docRest = str_replace("%PINFOA%", $playerA, $docRest);
$docRest = str_replace("%PINFOB%", $playerB, $docRest);
$fileContent = dbw("<PlayerinfoA>","</PlayerinfoB>",$fileContent);
$lastPlayerPos = strrpos($fileContent, "</playerinfo>");
$fileContent = substr_replace($fileContent, $docRest, $lastPlayerPos, 0);
file_put_contents($fileName, $fileContent);
?>