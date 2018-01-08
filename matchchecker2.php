<?php

// extraction of todays matches from basketplan

function stripAccents($stripAccents){
  return strtr($stripAccents,'àáâãäçèéêëìíîïñòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ','aaaaaceeeeiiiinooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY');
}

function attributeSB($teamName) {

	// list of all scorebugs
	$sbname = '';
	$teamName = strtoupper(stripAccents($teamName));
	//echo "Teamname: ".$teamName."<br>";
	$sblist = array('SB_LAUSANNE','SB_HELIOS','SB_MONTHEY','SB_VEVEY','SB_BASEL','SB_GENEVE','SB_VILLARS','SB_WINTERTHUR','SB_BELLINZONA','SB_MEYRIN','SB_BADEN','SB_PULLY','SB_BONCOURT', 'SB_MASSAGNO','SB_RIVA','SB_ELITE', 'SB_NEUCHATEL','SB_NYON','SB_CENTRAL','SB_TROISTORRENTS','SB_LUGANO','SB_ZURICH','SB_AARAU','SB_MORGES');

	//print_r($sblist);
	foreach ($sblist as $result) {
		//echo "T: ".$teamName." - R: ".substr($result,3)."<br>";
		$position = strpos($teamName, substr($result,3));
		if ($position !== FALSE) {
			$sbname = $result;
			// echo $sbname; 
			break;
		}
	}	
	// echo "sbname: ".$sbname;
	return($sbname);
}

function thisweeksmatches() {
	// Initialisation
	date_default_timezone_set('Europe/Zurich');
	$matchfilename = '/var/www/html/abcd/matches1week.xml';
	$todaydate = date('d.m.y');
	$modifdate = date('d.m.y', filemtime($matchfilename));
	$todaytime = date('H:i');
	// echo "Today: ". $todaydate . " - ". $todaytime. "<br>";
	// check if there is a cache file from the same day - jump over everything
	if (file_exists($matchfilename) && (date('d.m.y', filemtime($matchfilename)) == $todaydate)) {
		// echo "File available and actual<br>";
		return(TRUE);
		}
	else {
		// echo "File to be regenerated<br>";
			
		// Get list of links to check from basketplan
		// all FSBA Leagues
		$list1 = file_get_contents('http://www.basketplan.ch/findAllLeagueHoldings.do?federationId=11');
		// all SBL Leagues
		$list1 = $list1 . file_get_contents('http://www.basketplan.ch/findAllLeagueHoldings.do?federationId=12');
		
		// extract leagues links from two lists
		// echo $list1;
		$number = preg_match_all ('/(showLeagueSchedule\.do\?leagueHoldingId=\d*.*federationId=\d*)/', $list1, $matches, PREG_OFFSET_CAPTURE);
		if ($number != FALSE) {
		// echo "Matches Found: ".$number. "<br>";

			// concatenate matches from each of these leagues
			$list = '';
			$xml = '<?xml version="1.0"?><?xml-stylesheet type="text/xsl" href="table.xsl"?><document>';
			foreach($matches['0'] as $result) {
				// echo "Link: http://www.basketplan.ch/".$result['0']."<br>";
				$list2 = file_get_contents('http://www.basketplan.ch/'.$result['0']);
				$list .= $list2;
			}
			
			// now analyse with regex all the occurences for any date
			$number2 = preg_match_all ('/\s(\d\d\.\d\d\.\d\d)\s/', $list, $matches2, PREG_OFFSET_CAPTURE);
			// echo "Matches with today's date found: ".$number2."<br>";
			foreach($matches2['0'] as $result) {
				// get date and time
				// echo "Result: ".$result['0']." - ".$result['1']."<br>";

				$matchday = substr($list, $result['1']+ 1, 2);
				$matchmonth = substr($list, $result['1']+ 4, 2);
				$matchyear = substr($list, $result['1']+ 7, 2);
				$matchdate = $matchyear."-".$matchmonth."-".$matchday;
				$matchtime = substr($list, $result['1'] + 10, 5);
				//print_r($matches2);

				$matchdatecode = strtotime($matchdate);
				$fromcode = strtotime($todaydate);
				$tocode = strtotime($todaydate." + 1 week");
				//echo "match: ".$matchdatecode."-".$fromcode."-".$tocode."<br>";
				//echo "match: ".$matchdate."-".$matchtime."<br>";

				// if it's not between the date range, then move on
				if (($matchdatecode >= $fromcode) && ($matchdatecode <= $tocode)) {
					// echo "match: ".$matchdatecode."-".$fromcode."-".$tocode."<br>";
					// echo "match: ".$matchdate."-".$matchtime."<br>";
				
					// echo "Date and Time: ".$matchdate." ".$matchtime."<br>";

					// get the TeamA, TeamB, MatchNo and Location via Regex
					$number3 = preg_match_all ('/Id=\d*" class="a_txt8">(.*?)[&<]/', substr($list, $result['1'], 2000), $matches3, PREG_OFFSET_CAPTURE);
					if ($number !== FALSE) {
						$sbdevice = '';
						$xml = $xml . "<match><Date>".date('d.m.y',$matchdatecode)."</Date ><Time>".$matchtime."</Time>";
						$xml = $xml . "<Location>".strtoupper(stripAccents($matches3[1][0][0]))."</Location>";
						$xml = $xml . "<TeamA>".strtoupper(stripAccents($matches3[1][1][0]))."</TeamA>";
						$xml = $xml . "<TeamB>".strtoupper(stripAccents($matches3[1][2][0]))."</TeamB>";

						$number4 = preg_match('/www.youtube.com\/watch\?v=(.*)" target/', substr($list, $result['1'], 3100), $matches4, PREG_OFFSET_CAPTURE);
						if (($number4 !== FALSE) && ($number4 > 0)) {
							$xml = $xml . "<Youtube>".$matches4[1][0]."</Youtube>";
						}
						else {
							$xml = $xml . "<Youtube> </Youtube>";
						}

						$number4 = preg_match('/www.fibalivestats.com\/u\/SUI\/(\d*)\/"/', substr($list, $result['1'], 3100), $matches4, PREG_OFFSET_CAPTURE);
						if (($number4 !== FALSE) && ($number4 > 0)) {
							$xml = $xml . "<LiveStat>".$matches4[1][0]."</LiveStat>";
						}
						else {
							$xml = $xml . "<LiveStat> </LiveStat>";
						}
	
						$xml = $xml . "</match>";
					}
				}
			}

			$xml = $xml . "</document>";
			file_put_contents($matchfilename, $xml);
			// echo "Finished<br>";
			return(FALSE);
		}

	}
}

$answer = thisweeksmatches();

?>