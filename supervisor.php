<?php
// Monitoring screen for all clubs using SB_Scorebug
// (C) Thomas Kohler 2018, V1.0 05-01-2018

// Auto Pagerefresh every 5 seconds
$url1=$_SERVER['http://scorebug.tv'];
header("Refresh: 5; URL=$url1");

// Define array with all clubs
$scorebugs = array ("SB_LAUSANNE","SB_HELIOS","SB_MONTHEY","SB_VEVEY","SB_BASEL","SB_GENEVE","SB_VILLARS","SB_WINTERTHUR","SB_BELLINZONA","SB_MEYRIN","SB_BADEN","SB_PULLY","SB_BONCOURT","SB_MASSAGNO","SB_RIVA","SB_GENELITE","SB_NEUCHATEL","SB_NYON","SB_LUZERN","SB_TROISTORRENTS","SB_LUGANO","SB_ZURICH","SB_AARAU","SB_MORGES");

// Write HTML Header
echo "<html><head><style>table {font-family: arial, sans-serif; border-collapse: collapse; width: 100%;} td, th {border: 1px solid #dddddd; text-align: right; padding: 6px;} tr:nth-child(even) {background-color: #dddddd;}";
echo "</style></head><title>SB Scorebug Supervisor</title><body>";
echo "<h2><div style='font-family: arial'>SB_Scorebug Supervisor - Last Update (UTC Time): " . date('d-m-Y H:i:s', strtotime('now'));
echo "</h2><table><tr><th><div style='text-align:left'>SBName</div></th><th><div style='text-align:center'>RAG</div></th><th><div style='text-align:left'>TeamA</div></th><th><div style='text-align:left'>TeamB</div></th><th>Quarter</th><th>Clock</th><th>24</th><th>ScoreA</th><th>ScoreB</th><th>FoulsA</th><th>FoulsB</th><th>TOutA</th><th>TOutB</th><th>START</th><th>TOut?</th><th>UTC</th></tr>";
// Loop over all clubs
for($counter=0;$counter<count($scorebugs);$counter++) {
	$fname = "/var/www/html/abcd/" . $scorebugs[$counter] . "-lastaction.xml";
	if (file_exists($fname)) {
		// file exists and will be handled
		$xml = simplexml_load_file($fname);
		// print_r($xml);
		if ($xml) {
			// Read XML Content and put it into HTML Table
			echo "<tr><td><div style='text-align:left'>" . $scorebugs[$counter] . "</div></td>";
			// check if XML is actually a live feed
			// echo "ago " . date('Y-m-d H:i:s', strtotime('20 seconds ago'))." - ";
			// echo "mod " . date('Y-m-d H:i:s', filemtime($fname))."<br>";
			if (filemtime($fname) > strtotime('20 seconds ago')) {
				echo "<td><div style='color:green; text-align:center;'>&#x25FC</div></td>";
			}
			else {
				echo "<td><div style='color:orange; text-align:center;'>&#x25FC</div></td>";
			}
			echo "<td><div style='text-align:left'>" . $xml->event->TeamA . "</div></td><td><div style='text-align:left'>" . $xml->event->TeamB . "</div></td><td>" . $xml->event->Quarter;
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
		echo "<tr><td><div style='text-align:left'>" . $scorebugs[$counter] . "</div></td><td><div style='color:red; text-align:center;'>&#x25FC</div></td><td> </td><td> </td><td> </td><td> </td><td> </td><td> </td><td> </td><td> </td><td> </td><td> </td><td> </td><td> </td><td> </td><td> </td></tr>";
	}

}
echo "</table>";
echo "<br><div style='font-family: arial'>Colors: RED = no data; AMBER = previous data; GREEN = live data - Autorefresh every 5 seconds<br></div></body></html>";
?>