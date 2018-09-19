<?php


function stripAccents($stripAccents){
  
  //return strtr($stripAccents,'àáâãäçèéêëìíîïñòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ','aaaaaceeeeiiiinooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY');
  //does only work if there is setlocale(LC_CTYPE, 'en_US.UTF8'); at the beginning of the script, replaced the above that did not work on AWS
  return iconv('utf-8', 'ascii//TRANSLIT', $stripAccents);
}

function shortname($teamname) {
	$sblist = array('LAKERS', 'MARTIGNY', 'LUZERN', 'ARLESHEIM', 'CASSARATE', 'MENDRISIOTTO', 'PRILLY', 'COSSONAY', 'CAROUGE', 'BLONAY', 'DEL', 'SION', 'BIENNE', 'BCKE', 'ARBEDO', 'HELIOS', 'MONTHEY', 'LAKERS', 'BASEL', 'VILLARS', 'WINTERTHUR', 'BELLINZONA', 'MEYRIN', 'BADEN', 'PULLY', 'BONCOURT', 'MASSAGNO', 'RIVA','ELITE', 'NEUCHATEL', 'NYON', 'CENTRAL', 'TROISTORRENTS', 'LUGANO', 'ZURICH', 'AARAU', 'MORGES', 'COLLOMBEY', 'SION', 'FRIBOURG', 'LAUSANNE', 'GENEVE', 'BERNEX', 'CHENE', 'AGAUNE', 'RENENS', 'SARINE');

	$returnvalue = '';
	$teamname = strtoupper(stripAccents($teamname));
	foreach ($sblist as $result) {
		//echo "Teamname:".$teamname." Comparison: ".$result."<br>";
		if (strpos($teamname, $result) !== FALSE) {
			$returnvalue = $result;
			//echo "Found<br>";
			break;
		}
	}
	return($returnvalue);

}

function convertleague ($league) {
	$league = strtoupper(stripAccents($league));
	$leaguelist = array(array('LNAM', 'SB League'), array('LNAF', 'SB League Women'), array('LNBM', 'NLB'), array('LNBF', 'NLBW'));
	// build some code here to find the right replacement
	for ($counter = 0; $counter < count($leaguelist); $counter++) {
		if ($leaguelist[$counter][0] === $league) {
			$league = $leaguelist[$counter][1];
			break;
		}
	}
	return($league);
}

function attributeSB($teamName, $league) {

	// list of all scorebugs
	
	$teamName = strtoupper(stripAccents($teamName));
	// echo "Teamname: ".$teamName."<br>";
	
	$sblist = array(
		array('SB_LAUSANNE', array('LNAM', 'LNBM', 'SBL CupM', 'Coupe Suisse M')), 
		array('SB_HELIOS', array('LNAF', 'SBL CupF', 'Coupe Suisse F')), 
		array('SB_MONTHEY', array('LNAM', 'SBL CupM', 'Coupe Suisse M')),
		array('SB_LAKERS', array('LNAM', 'SBL CupM', 'Coupe Suisse M')),
		array('SB_BASEL', array('LNAM', 'SBL CupM', 'Coupe Suisse M')),
		array('SB_ELITE', array('LNAF', 'SBL CupF', 'Coupe Suisse F')), 
		array('SB_GENEVE',array('LNAM', 'SBL CupM', 'Coupe Suisse M')),
		array('SB_VILLARS', array('LNBM', 'SBL CupM', 'Coupe Suisse M')),
		array('SB_WINTERTHUR', array('LNAM','LNAF', 'SBL CupM', 'SBL CupF', 'Coupe Suisse M', 'Coupe Suisse F')),
		array('SB_BELLINZONA', array('LNAF', 'SBL CupF', 'Coupe Suisse F')),
		array('SB_MEYRIN', array('LNBM', 'LNBF', 'SBL CupM', 'SBL CupM', 'SBL CupF', 'Coupe Suisse M', 'Coupe Suisse F')),
		array('SB_BADEN', array('1LNM', 'LNBF', 'SBL CupM', 'SBL CupF', 'Coupe Suisse F')),
		array('SB_PULLY',array('LNAF', 'SBL CupF', 'Coupe Suisse F')),
		array('SB_BIENNE', array('1LNM', 'SBL CupM', 'Coupe Suisse M')),
		array('SB_BONCOURT', array('LNAM', 'SBL CupM', 'Coupe Suisse M')),
		array('SB_MASSAGNO', array('LNAM', 'SBL CupM', 'Coupe Suisse M')),
		array('SB_RIVA', array('LNAF', 'SBL CupF', 'Coupe Suisse F')),
		array('SB_NEUCHATEL', array('LNAM', 'SBL CupM', 'Coupe Suisse M')),
		array('SB_NYON',array('LNBM', 'SBL CupM', 'Coupe Suisse M')),
		array('SB_CENTRAL', array('LNAM', 'SBL CupM', 'Coupe Suisse M')),
		array('SB_TROISTORRENTS', array('LNAF', 'SBL CupF', 'Coupe Suisse F')),
		array('SB_LUGANO', array('LNAM', 'LNBM', 'SBL CupM', 'Coupe Suisse M')),
		array('SB_ZURICH', array('LNBM', 'LNBF', 'SBL CupM', 'Coupe Suisse M')),
		array('SB_AARAU', array('LNBF', 'SBL CupF', 'Coupe Suisse F')),
		array('SB_MORGES', array('LNBM', 'SBL CupM', 'Coupe Suisse M')),
		array('SB_FRIBOURG', array('LNAM', 'LNBM', 'LNAF', 'LNBF', 'SBL CupM', 'SBL CupF', 'Coupe Suisse M', 'Coupe Suisse F'))
		);

	$returnvalue = '';
	// echo "count: ".count($sblist)."<br>";
	for ($counter=0; $counter < count($sblist);$counter++) {
		//echo "Club: ".substr($sblist[$counter][0],3)." - ";
		//echo "TeamName: ".$teamName."<br>";
		if (strpos($teamName, substr($sblist[$counter][0],3)) !== FALSE) {
			// if now the club has been found, let's check if it's the right league
			//echo "Team Found: ".$sblist[$counter][0]."<br>";
			//echo "Count: ".count($sblist[$counter][1])."<br>";
			for ($counter2 = 0; $counter2 < count($sblist[$counter][1]); $counter2++) {
				//echo "League: ".$league." - ";
				//echo $sblist[$counter][1][$counter2]."<br>";
				if ($league === $sblist[$counter][1][$counter2]){
					//echo "League Found: ".$league."<br>";
					// if found return SB_Name
					$returnvalue = $sblist[$counter][0];	
					break;
				}
			}
			break;
		}
	}
		
	//echo "Return: ".$returnvalue."<br>";
	return($returnvalue);
}

function todaysmatches() {
	// Initialisation
	setlocale(LC_CTYPE, 'en_US.UTF8');
	date_default_timezone_set ('Europe/Zurich');
	$futuretimestamp = strtotime('+24 hours');	
	$matchfilename = '/var/www/html/abcd/matches2day.xml';
	if(isset($_GET["range"])) {
		switch (strtoupper($_GET["range"])) {
			case 'LDAY':
				//echo "Day";
				$futuretimestamp = strtotime('-24 hours');	
				$matchfilename = '/var/www/html/abcd/matchesLday.xml';
				break;
			case 'LWEEK':
				//echo "Week";
				$futuretimestamp = strtotime('-1 week');	
				$matchfilename = '/var/www/html/abcd/matchesLweek.xml';
				break;
			case 'LMONTH':
				//echo "Month";
				$futuretimestamp = strtotime('-1 month');	
				$matchfilename = '/var/www/html/abcd/matchesLmonth.xml';
				break;
			case 'LBIWEEK':
				//echo "Biweek";
				$futuretimestamp = strtotime('-2 weeks');	
				$matchfilename = '/var/www/html/abcd/matchesL2weeks.xml';
				break;
			case 'LBIMONTH':
				//echo "Bimonth";
				$futuretimestamp = strtotime('-2 months');	
				$matchfilename = '/var/www/html/abcd/matchesL2months.xml';
				break;
			case 'DAY':
				//echo "Day";
				$futuretimestamp = strtotime('+3 hours');	
				$matchfilename = '/var/www/html/abcd/matches2day.xml';
				break;
			case 'WEEK':
				//echo "Week";
				$futuretimestamp = strtotime('+1 week');	
				$matchfilename = '/var/www/html/abcd/matches1week.xml';
				break;
			case 'MONTH':
				//echo "Month";
				$futuretimestamp = strtotime('+1 month');	
				$matchfilename = '/var/www/html/abcd/matches1month.xml';
				break;
			case 'BIWEEK':
				//echo "Biweek";
				$futuretimestamp = strtotime('+2 weeks');	
				$matchfilename = '/var/www/html/abcd/matches2weeks.xml';
				break;
			case 'BIMONTH':
				//echo "Bimonth";
				$futuretimestamp = strtotime('+2 months');	
				$matchfilename = '/var/www/html/abcd/matches2months.xml';
				break;
		}
	}
	
	// $youtube_filename = '/var/www/html/abcd/ytlist.xml';
	$ytcreate_filename = '/var/www/html/abcd/yt_create.xml';
	$ytcut_filename = '/var/www/html/abcd/yt_cut.xml';
	
	$youtube_streamlist = '';
	/* if (file_exists($youtube_filename)) {
			$youtube_streamlist = file_get_contents($youtube_filename);
	} */
	
	$todaytimestamp = strtotime('-3 hours');
	// ensure that every time file is written (disabling the cache file routine)
	$modiftimestamp = $todaytimestamp - 1;
	//$modiftimestamp = filemtime($matchfilename);
	//echo "Today: ". date('d.h.m H:i', $todaytimestamp). "<br>";
	//echo "Modif: ". date('d.h.m H:i', $modiftimestamp). "<br>";
	// check if there is a cache file from the same day - jump over everything
	if (file_exists($matchfilename) && ($modiftimestamp > $todaytimestamp)) {
		//echo "File available and actual<br>";
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
		$number = preg_match_all ('/this\)">[\S\s]+?1">([\S\s]+?)<\/td>[\S\s]+?<td class="txt8"[\S\s]+?(showLeagueSchedule\.do\?leagueHoldingId=\d*.*federationId=\d*)/', $list1, $matches, PREG_OFFSET_CAPTURE);
		if ($number != FALSE) {
		// echo "Matches Found: ".$number. "<br>";

			// concatenate matches from each of these leagues
			$list = '';
			$listlen = array();
			$xml = '';
			$xml_cut = '';
			$xml_create = '';
			
			for($counter = 0; $counter < $number; $counter++) {
				// echo "Link: http://www.basketplan.ch/".$matches[2][$counter][0]."; ";
				$list2 = file_get_contents('http://www.basketplan.ch/'.$matches[2][$counter][0]);
				$list .= $list2;
				// calculate the endpoint of the current list for later comparison
				$listlen[$counter] = strlen($list);
			}
			
			// now analyse with regex all the occurences for any date
			$number2 = preg_match_all ('/\s(\d\d\.\d\d\.\d\d)\s/', $list, $matches2, PREG_OFFSET_CAPTURE);
			// echo "Matches with today's date found: ".$number2."<br>";
			foreach($matches2['0'] as $result) {
				// get date and time
				// echo "Result: ".$result['0']." - ".$result['1']."<br>";
				
				// $result1 is the position of the element found, now see in what section this is and determine league
				$league = '';
				$yt_create = 0;
				$yt_cut = 0;

				for ($counter2 = 0; $counter2 < $counter; $counter2++) {
						if ($result['1'] < $listlen[$counter2]) {
							$league = trim($matches[1][$counter2][0]);
							break;
						}
				}

				$matchday = substr($list, $result['1']+ 1, 2);
				$matchmonth = substr($list, $result['1']+ 4, 2);
				$matchyear = substr($list, $result['1']+ 7, 2);
				$matchdate = $matchyear."-".$matchmonth."-".$matchday;
				$matchdate2 = $matchday.".".$matchmonth.".".$matchyear;
				$matchtime = substr($list, $result['1'] + 10, 5);
				//print_r($matches2);

				$matchdatecode = strtotime($matchdate." ".$matchtime);
				if ($todaytimestamp > $futuretimestamp) {
					$tocode = $todaytimestamp;
					$fromcode = $futuretimestamp;
				}
				else {
					$fromcode = $todaytimestamp;
					$tocode = $futuretimestamp;
				}
				// echo "match: ".$matchdatecode."-".$fromcode."-".$tocode."<br>";
				// echo "match: ".$matchdate."-".$matchtime."<br>";

				// echo "Date and Time: ".$matchdate." ".$matchtime."<br>";

				// if it's not between the date range, then move on
				if (($matchdatecode >= $fromcode) && ($matchdatecode <= $tocode)) {
					// echo "match: ".$matchdatecode."-".$fromcode."-".$tocode."<br>";
					// echo "match: ".$matchdate."-".$matchtime."<br>";
				
					// echo "Date and Time: ".$matchdate." ".$matchtime."<br>";

					// get the TeamA, TeamB, MatchNo and Location via Regex
					
					//echo $output."<br>";
					$input = preg_replace ('!\s+!', ' ', substr($list, $result['1'], 2000));
					//$debug = file_get_contents('input.txt');
					//$debug .= $input;
					//file_put_contents('input.txt', $debug);
					
					$number3 = preg_match_all ('/Id=\d*" class="a_txt8">(.*?)[&<]/', $input, $matches3, PREG_OFFSET_CAPTURE);
					// write match date and time
					// echo "Loc: ".."<br>";
					// echo "TeamA: ".strtoupper(stripAccents($matches3[1][3][0]))."->".$league."<br>";
					$sbname = attributeSB($matches3[1][3][0], $league);	
					echo $sbname."<br>";
					
					$xmlrec = "<match>\n";
					$xmlrec .= "<Datecode id='".$sbname."'>".$matchdatecode."</Datecode>\n";
					$xmlrec .= "<Date id='".$sbname."'>".$matchdate2."</Date>\n";
					$xmlrec .= "<Time id='".$sbname."'>".$matchtime."</Time>\n";
					$xmlrec .= "<League id='".$sbname."'>".substr($league,0,15)."</League>\n";
					$xmlrec .= "<League2 id='".$sbname."'>".convertleague(substr($league,0,15))."</League2>\n";
					$xmlrec .= "<Location id='".$sbname."'>".strtoupper(stripAccents($matches3[1][2][0]))."</Location>\n";
					//echo "Location: ".$matches3[1][2][0]." - ".stripAccents($matches3[1][2][0])." - ".strtoupper(stripAccents($matches3[1][2][0]));
					if (is_numeric(filter_var($matches3[1][0][0], FILTER_SANITIZE_NUMBER_INT))) {
						$xmlrec .= "<Gameday id='".$sbname."'>".filter_var($matches3[1][0][0], FILTER_SANITIZE_NUMBER_INT)."</Gameday>\n";
					} else {
						$xmlrec .= "<Gameday id='".$sbname."'> </Gameday>\n";
					}						
					if (substr(trim($matches3[1][1][0]),2,1) == '-') {
						$xmlrec .= "<GameId id='".$sbname."'>".trim($matches3[1][1][0])."</GameId>\n";
					} else {
						$xmlrec .= "<GameId id='".$sbname."'> </GameId>\n";
					}
					$xmlrec .= "<TeamA id='".$sbname."'>".strtoupper(stripAccents($matches3[1][3][0]))."</TeamA>\n";
					$xmlrec .= "<TeamAS id='".$sbname."'>".shortname(strtoupper(stripAccents($matches3[1][3][0])))."</TeamAS>\n";
					$xmlrec .= "<TeamB id='".$sbname."'>".strtoupper(stripAccents($matches3[1][4][0]))."</TeamB>\n";
					$xmlrec .= "<TeamBS id='".$sbname."'>".shortname(strtoupper(stripAccents($matches3[1][4][0])))."</TeamBS>\n";

					

					// build the name under which there would be Scorebug data on the server
					if ($sbname !== '') {
						// echo $sbname . "-20" . $matchdate.".xml<br>";
						// check if file is existing
						if (($matchdatecode <= $todaytimestamp) and (file_exists("/var/www/html/abcd/" . $sbname."-20" . $matchdate . ".xml"))) {		
							$sbdata = $sbname . "-20" . $matchdate.".xml";
							$yt_cut = 1;
						}
						else {	
							// if match is in the future and $sbname is set
							if ($matchdatecode > $todaytimestamp) {
								$yt_create = 1;
							}
							else {
								$sbdata = "";
							}
						}
					}
					else {	
						$sbdata = "";
					}
					$xmlrec .= "<SBFile id='".$sbname."'>".$sbdata."</SBFile>\n";
					
					// get livestat ID
					$number4 = preg_match('/www.fibalivestats.com\/u\/SUI\/(\d*)\/"/', substr($list, $result['1'], 3100), $matches4, PREG_OFFSET_CAPTURE);
					if (($number4 !== FALSE) && ($number4 > 0)) {
						$xmlrec .= "<LiveStat id='".$sbname."'>".$matches4[1][0]."</LiveStat>\n";
					}
					else {
						$xmlrec .= "<LiveStat id='".$sbname."'> </LiveStat>\n";
					}
					
					// get youtube ID
					$number5 = preg_match('/www.youtube.com\/watch\?v=(.*)" target/', substr($list, $result['1'], 3100), $matches5, PREG_OFFSET_CAPTURE);
					if (($number5 !== FALSE) && ($number5 > 0)) {
						$xmlrec .= "<Youtube id='".$sbname."'>".$matches5[1][0]."</Youtube>\n";
					}
					else {
						$xmlrec .= "<Youtube id='".$sbname."'> </Youtube>\n";
					}

					$xmlrec .= "</match>\n";
					$xml .= $xmlrec;
					
					if($yt_create == 1) {
						$xml_create .= $xmlrec;
						
					}
					
					if($yt_cut == 1) {
						$xml_cut .= $xmlrec;
						 
					}
					
				}			
			}

			//echo "XML_CUT: ".$xml_cut;
			//echo "XML_CRE: ".$xml_create;
			if ($xml_cut != '') {
				file_put_contents($ytcut_filename, '<?xml version="1.0"?><document>'.$xml_cut."</document>");
			}
			if ($xml_create != '') {
				file_put_contents($ytcreate_filename, '<?xml version="1.0"?><document>'.$xml_create."</document>");
			}
			//if ($xml != '') {
			file_put_contents($matchfilename, '<?xml version="1.0"?><?xml-stylesheet type="text/xsl" href="table.xsl"?><document>'.$xml.'</document>');
			//} 
			
			// echo "Finished<br>";
			return(FALSE);
		}

	}
}

//echo "Start: ".$_GET["range"]."<br>";
$answer = todaysmatches();
//echo "Answer: ".$answer;

?>
