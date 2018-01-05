<?php
// Monitoring screen for all clubs using SB_Scorebug
// (C) Thomas Kohler 2018, V1.0 05-01-2018

// Define array with all clubs
$scorebugs = array ("SB_LAUSANNE","SB_HELIOS","SB_MONTHEY","SB_VEVEY","SB_BASEL","SB_GENEVE","SB_VILLARS","SB_WINTERTHUR","SB_BELLINZONA","SB_MEYRIN","SB_BADEN","SB_PULLY","SB_BONCOURT","SB_MASSAGNO","SB_RIVA","SB_GENELITE","SB_NEUCHATEL","SB_NYON","SB_LUZERN","SB_TROISTORRENTS","SB_LUGANO","SB_ZURICH","SB_AARAU","SB_MORGES");

// Write HTML Header
echo "<html><head><style>table {font-family: arial, sans-serif; border-collapse: collapse; width: 100%;} td, th {border: 1px solid #dddddd; text-align: right; padding: 8px;} tr:nth-child(even) {background-color: #dddddd;}";
echo "</style></head><title>SB Scorebug Supervisor</title><body><table>";
echo "<tr><th><div style='text-align:left'>SBName</div></th><th><div style='text-align:left'>TeamA</div></th><th><div style='text-align:left'>TeamB</div></th><th>Quarter</th><th>Clock</th><th>24</th><th>ScoreA</th><th>ScoreB</th><th>FoulsA</th><th>FoulsB</th><th>TOutA</th><th>TOutB</th><th>START</th><th>TOut?</th><th>UTC</th></tr>";
// Loop over all clubs
for($counter=0;$counter<count($scorebugs);$counter++) {
	$fname = "/var/www/html/abcd/" . $scorebugs[$counter] . "-lastaction.xml";
	if (file_exists($fname)) {
		// file exists and will be handled
		$xml = simplexml_load_file($fname);
		// print_r($xml);
		if ($xml) {
			// Read XML Content and put it into HTML Table
			echo "<tr><td><div style='text-align:left'>" . $scorebugs[$counter] . "</div></td><td><div style='text-align:left'>" . $xml->event->TeamA . "</div></td><td><div style='text-align:left'>" . $xml->event->TeamB . "</div></td><td>" . $xml->event->Quarter;
			echo "</td><td>" . $xml->event->ClockTime . "</td><td>" . $xml->event->ShotClock . "</td><td>" . $xml->event->ScoreTeamA . "</td><td>" . $xml->event->ScoreTeamB;
			echo "</td><td>" . $xml->event->TeamFoulA . "</td><td>" . $xml->event->TeamFoulB . "</td><td>" . $xml->event->TimeOutA . "</td><td>" . $xml->event->TimeOutB;
			echo "</td><td>" . $xml->event->StartStop . "</td><td>" . $xml->event->Timeout . "</td><td>" . $xml->event->UTCTime . "</td></tr>";
		}
		else {
			echo "Failed loading XML\n";
		        foreach(libxml_get_errors() as $error) {
                        echo "\t", $error->message;
			}
		}

	}
	else {
		echo "<tr><td><div style='text-align:left'>" . $scorebugs[$counter] . "</div></td><td> </td><td> </td><td> </td><td> </td><td> </td><td> </td><td> </td><td> </td><td> </td><td> </td><td> </td><td> </td><td> </td><td> </td></tr>";
	}

}
echo "</table></body></html>"
?>