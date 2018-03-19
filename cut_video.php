<?php 
	function utf8_for_xml($string) {
		return preg_replace ('/[^\x{0009}\x{000a}\x{000d}\x{0020}-\x{D7FF}\x{E000}-\x{FFFD}]+/u', ' ', $string);
	}
	
	$htmlBody = '';
	// these values are not exact, depending on keyframe of video
	$preeventsecs = 10;
	$posteventsecs = 15;
	date_default_timezone_set('Europe/Zurich');
	libxml_use_internal_errors(true);
	
	// create directories if not existing
	
	if (!file_exists('/mnt/videostore/clips')) {
		echo 'create /mnt/videostore/clips<br>';
		mkdir('/mnt/videostore/clips', 0777, true);
	}
	
	if (!file_exists('/mnt/videostore/videos')) {
		echo 'create /mnt/videostore/videos<br>';
		mkdir('/mnt/videostore/videos', 0777, true);
	}
	
	$yt_cut = simplexml_load_file('/var/www/html/abcd/yt_cut.xml');
	if ($yt_cut != False) {
		foreach ($yt_cut->match as $match) {
			// get details of match, specifically the realstartdate
			$yt_all = simplexml_load_file('/var/www/html/abcd/yt_all.xml');
			if ($yt_all != False) {
				$starttime = 0;
				// echo "GameId: ".$match->GameId;
				foreach ($yt_all->broadcast as $broadcast) {
					$matchpos = strpos($broadcast->description, trim($match->GameId));
					// echo "matchid: ".$match->GameId." - matchpos: ".$matchpos." - description: ".$broadcast->description."<br>";
					if ($matchpos > 0) {
						// echo "<br>FOUND - matchpos: ".$matchpos."<br>";
						$starttime = trim($broadcast->starttime);
						break;
					}
				}
				
				if (($starttime != False) and ($starttime > 0)) {
					// load and save cutting file as string to get rid of unwanted UTF-8 characters
					$temp = utf8_for_xml(file_get_contents('/var/www/html/abcd/'.$match->SBFile));
					file_put_contents('/var/www/html/abcd/'.$match->SBFile, $temp);
					
					// load xml (clean)
					$yt_cut2 = simplexml_load_file('/var/www/html/abcd/'.$match->SBFile);
					if ($yt_cut2 != False) {
					
						// download video with youtube-dl
						$datecode = trim($match->Datecode);
						// echo "datecode: ".$datecode."<br>";
						$matchdate = date('Ymd_Hi', $datecode);
						$yt_name = htmlspecialchars("https://www.youtube.com/watch?v=".$match->Youtube);
						// echo "<br>yt_name: ".$yt_name."<br>";
						$videoname = $matchdate."_".substr($match->TeamAS,0,3)."_".substr($match->TeamBS,0,3);
						echo "videoname: ".$videoname."<br>";
						$livestatxml = False;
						if (trim($match->LiveStat) != '') {
							$livestat_name = htmlspecialchars('http://bskt2xml.info/swiss/'.$match->LiveStat);
							echo $livestat_name;
							$livestatdata = file_get_contents($livestat_name);
							//file_put_contents("debug45.txt", $livestatdata);
							$livestatxml = simplexml_load_string($livestatdata);
							/* if ($livestatxml != False) {
								echo "Livestats acquired<br>";
							} */
						}
						
							
						// download video -- this can take a long time
						$cmd = "youtube-dl --download-archive /mnt/videostore/videos/downloaded.txt -w -o '/mnt/videostore/videos/".$videoname.".mp4' -f mp4 ".$yt_name;
						echo "<br>".$cmd."<br>";
						exec($cmd, $output, $ret);
						
						// echo 'output: ';
						//var_dump($output);
						
						//echo "<br>ret: ".$ret."<br>";
						//var_export($ret);
						//echo "<br>";
						
						// simulation
						//$ret = 0;
						
						// as ret is not reliable, $output array needs to be parsed to find an error message
						if (!in_array("ERROR", $output)) {						
							// initialised                                       
							$ScoreTeamA = 0;
							$ScoreTeamB = 0;
							//echo "Starter ".$match->SBFile." ".trim($match->GameId)."<br>";
							$pathname = "/mnt/videostore/clips/".$videoname;
							echo $pathname."<br>";
							$ret2 = mkdir($pathname, 0777);
							
							$counter = 0;
							// cutting procedure
							foreach ($yt_cut2->playbyplay->event as $event) {
								// create a clip from each event if score has changed
								// echo $event->ScoreTeamA."-".$event->ScoreTeamB."<br>";
								
								// TODO: needs some more conditions here to take out the badly formed records, eventually before
								if (is_numeric(substr(trim($event->Quarter),1,1)) and (is_numeric(substr($event->ClockTime,0,2))) and (is_numeric(substr($event->ClockTime,3,2))) and (is_numeric(trim($event->ShotClock))) and (substr($event->ClockTime,2,1) == ':')) {
									if ((intval($event->ScoreTeamA) != $ScoreTeamA) or (intval($event->ScoreTeamB) != $ScoreTeamB)) {
										// somebody has scored, so let's cut
										$eventtime = strtotime($event->UTCTime)+3600;
										// EPOCH is already in seconds so results will be in seconds
										$difftime = $eventtime - $starttime - $preeventsecs;
										// if difftime > 0, then time tags are in the range of the youtube video
										if ($difftime > 0) {
											//echo "SUCCESS - Starttime: ".date('Ymd_Hi', $starttime)."/".$starttime." - Eventtime: ".date('Ymd_Hi', $eventtime)."/".$eventtime." difftime: ".$difftime." Quarter: ".$event->Quarter." Clocktime: ".$event->ClockTime." Shortclock: ".$event->ShotClock." -> ".$event->ScoreTeamA."-".$event->ScoreTeamB."<br>";
											$clipname = $matchdate."-".substr($match->TeamAS,0,3)."_".substr($match->TeamBS,0,3)."_".trim($event->Quarter)."_".date('Hi',strtotime($event->ClockTime))."_".$event->ScoreTeamA."-".$event->ScoreTeamB;
											
											// if livestats are available, then some complementary information will be added
											if ($livestatxml != False) {
												//echo "entering livestat compilation <br>";
													$scoreplayername = '';
													$scoreplayerno = '';
													$scoretype = '';
													$assistplayername = '';
													$assistplayerno = '';
													
												foreach ($livestatxml->playbyplay->event as $event2) {
													// find the necessary matches in the score, there might be several
												
													if ((trim($event2->ScoreTeamA) == trim($event->ScoreTeamA)) and (trim($event2->ScoreTeamB) == trim($event->ScoreTeamB)) and (preg_match('(2pt|3pt|freethrow|assist)',$event2->Action)) and (trim($event2->PlusMinus) == '+')) {
														//echo "*** hit *** <br>";
														switch(strtoupper(trim($event2->Action))) {
															case "2PT":
																//echo "HIT: 2PT<br>";
																$scoreplayername = str_replace(" ", "_", $event2->PlayerName);
																$scoreplayerno = trim($event2->PlayerNo);
																$scoretype = trim($event2->Action);
																//echo "Name: ".$scoreplayername."<br>";
																//echo "No: ".$scoreplayerno."<br>";
																break;
															case "3PT":
																//echo "HIT: 3PT<br>";
																$scoreplayername = str_replace(" ", "_", $event2->PlayerName);
																$scoreplayerno = trim($event2->PlayerNo);
																$scoretype = trim($event2->Action);
																//echo "Name: ".$scoreplayername."<br>";
																//echo "No: ".$scoreplayerno."<br>";
																break;
															case "ASSIST":
																//echo "HIT: ASS<br>";
																$assistplayername = str_replace(" ", "_", $event2->PlayerName);
																$assistplayerno = trim($event2->PlayerNo);
																//echo "Name: ".$assistplayername."<br>";
																//echo "No: ".$assistplayerno."<br>";
																break;
															case "FREETHROW":
																//echo "HIT: FT<br>";
																$scoreplayername = str_replace(" ", "_", $event2->PlayerName);
																$scoreplayerno = trim($event2->PlayerNo);
																$scoretype = 'FT';
																//echo "Name: ".$scoreplayername."<br>";
																//echo "No: ".$scoreplayerno."<br>";
																break;
														}
													}
														
												}
												// add additional text to filename
												$clipname .= "_".$scoretype."_SC-".$scoreplayerno."-".$scoreplayername."_AS-".$assistplayerno."-".$assistplayername;
												//echo $clipname."<br>";
											}
											
											$cmd = "ffmpeg -n -ss ".$difftime." -i /mnt/videostore/videos/".$videoname.".mp4 -c copy -t ".$posteventsecs.
											" ".$pathname."/".$clipname.".mp4";
											echo($cmd);
											exec($cmd, $output, $ret);
											//echo '<br>output: ';
											//print_r($output);
											//var_export($ret);
											echo "<br>";
							
											$ScoreTeamA = intval($event->ScoreTeamA);
											$ScoreTeamB = intval($event->ScoreTeamB);
											$counter += 1;
											
											// DEBUG STATEMENT: Artificially limiting the output to 5 good records - To be removed before production
											//if ($counter > 10) {
											//	break;
											//}
										}
									}
									
								}
								else {
									//echo "FAIL - Starttime: ".date('Ymd_Hi', $starttime)." - Eventtime: ".date('Ymd_Hi', $eventtime)." difftime: ".$difftime." Quarter: ".$event->Quarter." Clocktime: ".$event->ClockTime." Shortclock: ".$event->ShotClock."<br>";
									echo "";
								}
								
							}	
						}
						else {
							echo "Download of ".$yt_name." into ".$videoname." failed<br>";
						}
					}
					else {
						echo "opening ".$match->SBFile." failed...<br>";
						foreach(libxml_get_errors() as $error) {
							echo "<br>", $error->message;
						}
					}
				}
			}
			else {
				echo "opening yt_all.xml failed...<br>";
				foreach(libxml_get_errors() as $error) {
					echo "<br>", $error->message;
				}
			}
		}
	}
	else {
		echo "opening yt_cut.xml failed...<br>";
		foreach(libxml_get_errors() as $error) {
			echo "<br>", $error->message;
		}
	}
?>