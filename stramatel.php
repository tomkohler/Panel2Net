<?php

function stripAccents($stripAccents){
  return strtr($stripAccents,'àáâãäçèéêëìíîïñòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ','aaaaaceeeeiiiinooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY');
}

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
	//debug_backtrace
	//$timer2 = $timer;
	
	//$timer = preg_replace('/[\x00-\x1F\x7F]/u', '', $timer);
	
	// debug
	//$temp = file_get_contents('debug3.txt');
	//$temp .= $timer."-".$timer2."\n";
	//file_put_contents('debug3.txt', $temp);
	
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
	//if (strpos(trim($timer),'.') !== false) {
	//	if (!is_numeric(trim($timer))) {
	//		$status = 6;
	//	}
	//}

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
//Stramatel Reader - 01/12/2017 - Thomas Gervaise - gervaise.thomas41@gmail.com

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
	$timer = substr($mainInfo, 2, 2).'.'.substr($mainInfo, 3, 1);
} else {
	$timer = substr($mainInfo, 2, 2).':'.substr($mainInfo, 4, 2);
}

// debug
//$temp = file_get_contents('debug3.txt');
//$temp .= $timer."\n".substr($mainInfo, 2, 4)."-".substr($mainInfo, 2, 1).":".substr($mainInfo, 3, 1).":".substr($mainInfo, 4, 1).":".substr($mainInfo, 5, 1)."\n";
//file_put_contents('debug3.txt', $temp);

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

//$temp = file_get_contents("namesdebug.txt");
// Explode F8 38 - Individual Points HOME (List of entire team)
$homePoints = explode(chr(248)."8", $myinput);
$length1 = count($homePoints);
//$homePoints = implode(unpack("H*", substr($homePoints[$length1-2],0,53)));
$homePoints = $homePoints[$length1-2];

/* $homePoints = explode(chr(248).chr(38), $myinput);
$hpcount = count($homePoints);
if (strlen($homePoints) > 54) {
	$homePoints = substr($homePoints,0,53);
} else {
	$homePoints = "";
}
 */
//$temp .= "Homepoints: ".strlen($homePoints)."-->".$homePoints."\n";

// Explode F8 38 - Individual Points EXT (List of entire team)
$extPoints = explode(chr(248)."7", $myinput);
$length1 = count($extPoints);
//$extPoints = implode(unpack("H*", substr($extPoints[$length1-2],0,53)));
$extPoints = $extPoints[$length1-2];

$extPoints = explode(chr(248).chr(37), $myinput);
if (strlen($extPoints) > 54) {
	$extPoints = substr($extPoints,0,53);
} else {
	$extPoints = "";
}
//$temp .= "Extpoints: ".strlen($extPoints)."-->".$extPoints."\n";

// Explode F8 77 - Names HOME assign shirt number
$homeName = explode(chr(248).chr(119), $myinput);
$hncount = count($homeName);
//echo "HnCount: ".$hncount."<br>";
for($i = 0; $i<=$hncount-1; $i++) {
	$middle = explode(chr(13),$homeName[$i]);
	$rollingno = substr($middle[0],0,1);
	//echo $rollingno."<br>";
	if (!is_numeric($rollingno)) {
		if ($rollingno == ":") { $rollingno = 10; }
		if ($rollingno == ";") { $rollingno = 11; }
		if ($rollingno == "<") { $rollingno = 12; }
	} else {
		$rollingno = (int)$rollingno;
	}
	$shirtno = substr($middle[0],-3);
	if (substr($shirtno,0,1) == " ") {
		$homeshirtno[$rollingno] = trim($shirtno);
		//echo "Home Shirt ".$rollingno."= ".trim($shirtno)."<br>";
		
	}
}

// Explode F8 62 - Names EXT ((only one player per record)
$extName = explode(chr(248).chr(98), $myinput);
$encount = count($extName);
//echo "EnCount: ".$encount."<br>";
for($i = 0; $i<=$encount-1; $i++) {
	$middle = explode(chr(13),$extName[$i]);
	$rollingno = substr($middle[0],0,1);
	//echo $rollingno."<br>";
	if (!is_numeric($rollingno)) {
		if ($rollingno == ":") { $rollingno = 10; }
		if ($rollingno == ";") { $rollingno = 11; }
		if ($rollingno == "<") { $rollingno = 12; }
	} else {
		$rollingno = (int)$rollingno;
	}
	$shirtno = substr($middle[0],-3);
	if (substr($shirtno,0,1) == " ") {
		$extshirtno[$rollingno] = trim($shirtno);
		//echo "Ext Shirt ".$rollingno."= ".trim($shirtno)."<br>";
		
	}
}

// Explode F8 62 - Messages 
 $messages = explode(chr(248).chr(77), $myinput);
$mcount = count($messages);
$z = $mcount-1;
$midx = substr($messages[$z],0,1);
while ($z > 0) {
	$integrity = 1;
	$midx = substr($messages[$z],0,1);
	$output = "";
	for ($i = ($z-$midx); $i<=$z; $i++) {
		$messg = substr($messages[$i],6,40);
		// check if messageindex is correct
		if (substr($messages[$i],0,1) == ($i-($z-$midx))) {
			for($j = 0; $j<= strlen($messg); $j+=2){
				if ($j != 12) {
					$output .= substr($messg,$j,1);
				}
			}
		} else {
			// integrity broken, need to check which is the next integer message block
			$integrity = 0;
			$output = " ";
			break;
		}
	}
	if ($integrity == 1) {
		$z = 0;
		break;
		//$z = $z - ($midx + 1);
	} else {
		// search for the next start of new message
		while ((substr($messages[$z],0,1)) < (substr($messages[$z - 1],0,1))) {
			$z = $z - 1;
		}
	}
	//echo $output."<br>";
}

$output = iconv('ISO-8859-1', 'UTF-8', $output);
//$output = stripAccents($output);

//file_put_contents("namesdebug.txt", $temp);



$playerA = "";
$playerA2 = "";
$stats = "Home: ";
for($i = 1; $i<=13; $i++) {
	$fouls = substr($mainInfo, 19+$i, 1);
	$number = substr($homePoints, 19+($i*2)-1, 2);
	if($number == "  ") {
		$number = 0;
	}
	$number = trim($number);
	$fouls = trim($fouls);
	$shirtno = $homeshirtno[$i];
	if ($shirtno >=0 and $shirtno <=99 and $shirtno != "") {
		//echo "ShirtNo ".$shirtno." Points ".$number." Fouls ".$fouls."<br>";
		if (($number > 0) or ($fouls > 0)) {
			$playerA2 .= "#$shirtno $number-$fouls  ";
		}
		$playerA .= "<ShirtNo>$shirtno</ShirtNo><Points>$number</Points><Fouls>$fouls</Fouls>";
	}
}

$playerB = "";
$playerB2 = "";
for($i = 1; $i<=13; $i++) {
	$fouls = substr($mainInfo, 31+$i, 1);
	$number = substr($extPoints, 19+($i*2)-1, 2);
	if($number == "  ") {
		$number = 0;
	}
	$number = trim($number);
	$fouls = trim($fouls);
	$shirtno = $extshirtno[$i];
	if ($shirtno >=0 and $shirtno <=99 and $shirtno != "") {
		//echo "ShirtNo ".$shirtno." Points ".$number." Fouls ".$fouls."<br>";
		if (($number > 0) or ($fouls > 0)) {
			$playerA2 .= "#$shirtno $number-$fouls  ";
		}
		$playerB .= "<ShirtNo>$shirtno</ShirtNo><Points>$number</Points><Fouls>$fouls</Fouls>";
	}
		
}
file_put_contents($panel_name.'-statistics.xml', "<home>".$playerA."</home>\n<away>".$playerB."</away>");
//file_put_contents($panel_name.'-statistics2.xml', "<html>".$playerA2." ".$playerB2."</html>");
file_put_contents($panel_name.'-statistics2.xml', "<html>".$output." </html>");

$docRest = str_replace("%PINFOA%", $playerA, $docRest);
$docRest = str_replace("%PINFOB%", $playerB, $docRest);

$fileContent = dbw("<PlayerinfoA>","</PlayerinfoB>",$fileContent);
$lastPlayerPos = strrpos($fileContent, "</playerinfo>");
$fileContent = substr_replace($fileContent, $docRest, $lastPlayerPos, 0);

//file_put_contents($fileName, $fileContent);






?>