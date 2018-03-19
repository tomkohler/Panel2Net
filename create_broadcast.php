<?php

// next functions needed for playlist attachment -- Google snipplets (unmodified)
function playlistItemsInsert($service, $properties, $part, $params) {
	$params = array_filter($params);
	$propertyObject = createResource($properties); // See full sample for function
	$resource = new Google_Service_YouTube_PlaylistItem($propertyObject);
	$response = $service->playlistItems->insert($part, $resource, $params);
	//print_r($response);
}

// Build a resource based on a list of properties given as key-value pairs.
function createResource($properties) {
    $resource = array();
    foreach ($properties as $prop => $value) {
        if ($value) {
            addPropertyToResource($resource, $prop, $value);
        }
    }
    return $resource;
}


// Add a property to the resource.
function addPropertyToResource(&$ref, $property, $value) {
    $keys = explode(".", $property);
    $is_array = false;
    foreach ($keys as $key) {
        // For properties that have array values, convert a name like
        // "snippet.tags[]" to snippet.tags, and set a flag to handle
        // the value as an array.
        if (substr($key, -2) == "[]") {
            $key = substr($key, 0, -2);
            $is_array = true;
        }
        $ref = &$ref[$key];
    }

    // Set the property value. Make sure array values are handled properly.
    if ($is_array && $value) {
        $ref = $value;
        $ref = explode(",", $value);
    } elseif ($is_array) {
        $ref = array();
    } else {
        $ref = $value;
    }
}

//Section 2: IMAP
//IMAP commands requires the PHP IMAP Extension, found at: https://php.net/manual/en/imap.setup.php
//Function to call which uses the PHP imap_*() functions to save messages: https://php.net/manual/en/book.imap.php
//You can use imap_getmailboxes($imapStream, '/imap/ssl') to get a list of available folders or labels, this can
//be useful if you are trying to get this working on a non-Gmail IMAP server.
function save_mail($mail)
{
    //You can change 'Sent Mail' to any other folder or tag
    $path = "{imap.gmail.com:993/imap/ssl}[Gmail]/Sent Mail";
    //Tell your server to open an IMAP connection using the same username and password as you used for SMTP
    $imapStream = imap_open($path, $mail->Username, $mail->Password);
    $result = imap_append($imapStream, $path, $mail->getSentMIMEMessage());
    imap_close($imapStream);
    return $result;
}

function setresolution($SBCode) {
	// set default
	$SBCode = trim($SBCode);
	//echo "SBCode: ".$SBCode."/n";
	$ret_resolution = '1080p';
	// list exceptions
	$resolutions = array (
		array ('SB_WINTERTHUR','720p'),
		array ('SB_MEYRIN','720p'),
		array ('SB_MASSAGNO','720p'),
		array ('SB_BELLINZONA','720p'),
		array ('SB_RIVA','720p'),
		array ('SB_ELITE','720p'),
		array ('SB_NYON','720p'),
		array ('SB_BIENNE','720p'),
		array ('SB_BADEN','720p'),
		array ('SB_LUGANO','720p'),
		);
		
	// go through all resolutions
	foreach ($resolutions as $resolution) {
		//echo "Resolution: ".$resolution[0]."-".$resolution[1];
		if ($resolution[0] == $SBCode) {
			// assign new resolution
			$ret_resolution = $resolution[1];
		}
	}
	return ($ret_resolution);
}

function getClient() {
	$client = new Google_Client();
	$client->setApplicationName('create_broadcast');
	$client->setScopes('https://www.googleapis.com/auth/youtube');
	$client->setAuthConfig('/var/www/html/ytprog/php/client_secret.json');
	$client->setRedirectUri('http://swb.world/ytprog/php/create_broadcast.php');
	$client->setAccessType('offline');
	$client->setApprovalPrompt('force');

	// Load previously authorized credentials from a file.
	$credentialsPath = expandHomeDirectory(CREDENTIALS_PATH);
	// echo "cr: ".$credentialsPath."/n";
	if (file_exists($credentialsPath)) {
		$accessToken = json_decode(file_get_contents($credentialsPath), true);
	} 
	else {
		// Request authorization from the user.
		$authUrl = $client->createAuthUrl();
		header('Location: ' . filter_var($authUrl, FILTER_SANITIZE_URL));

		if (isset($_GET['code'])) {
			$authCode = $_GET['code'];
			// Exchange authorization code for an access token.
			$accessToken = $client->fetchAccessTokenWithAuthCode($authCode);
			header('Location: ' . filter_var('http://swb.world/ytprog/php/list_broadcasts.php', FILTER_SANITIZE_URL));
			if(!file_exists(dirname($credentialsPath))) {
				mkdir(dirname($credentialsPath), 0700, true);
			}

			file_put_contents($credentialsPath, json_encode($accessToken));
		} 
		else {
			exit('No code found');
		}
    }
    $client->setAccessToken($accessToken);

    // Refresh the token if it's expired.
    if ($client->isAccessTokenExpired()) {
		$oldAccessToken=$client->getAccessToken();
		$client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
		$accessToken=$client->getAccessToken();
		$accessToken['refresh_token']=$oldAccessToken['refresh_token'];
		file_put_contents($credentialsPath, json_encode($accessToken));
    }
    return $client;
}

function expandHomeDirectory($path) {
  $homeDirectory = getenv('HOME');
  if (empty($homeDirectory)) {
    $homeDirectory = getenv("HOMEDRIVE") . getenv("HOMEPATH");
  }
  return str_replace('~', realpath($homeDirectory), $path);
}

// MAIN PART OF THE SCRIPT

/**
 * Library Requirements
 *
 * 1. Install composer (https://getcomposer.org)
 * 2. On the command line, change to this directory (api-samples/php)
 * 3. Require the google/apiclient library
 *    $ composer require google/apiclient:~2.0
 */
if (!file_exists(__DIR__ . '/vendor/autoload.php')) {
  throw new \Exception('please run "composer require google/apiclient:~2.0" in "' . __DIR__ .'"');
}

require_once __DIR__ . '/vendor/autoload.php';
session_start();
define('CREDENTIALS_PATH', '/var/www/html/ytprog/php/php-yt-oauth2.json');


use PHPMailer\PHPMailer\PHPMailer;
require '/var/www/html/ytprog/php/vendor/autoload.php';

/*
 * You can acquire an OAuth 2.0 client ID and client secret from the
 * {{ Google Cloud Console }} <{{ https://cloud.google.com/console }}>
 * For more information about using OAuth 2.0 to access Google APIs, please see:
 * <https://developers.google.com/youtube/v3/guides/authentication>
 * Please ensure that you have enabled the YouTube Data API for your project.
 */

 // set timezone
date_default_timezone_set('Europe/Zurich');
 
$client = getClient();
// Define an object that will be used to make all API requests.
$youtube = new Google_Service_YouTube($client);

// Check if an auth token exists for the required scopes
$tokenSessionKey = 'token-' . $client->prepareScopes();
if (isset($_GET['code'])) {
  if (strval($_SESSION['state']) !== strval($_GET['state'])) {
	die('The session state did not match.');
  }

  $client->authenticate($_GET['code']);
  $_SESSION[$tokenSessionKey] = $client->getAccessToken();
  header('Location: ' . $redirect);
}

if (isset($_SESSION[$tokenSessionKey])) {
  $client->setAccessToken($_SESSION[$tokenSessionKey]);
}

// Check to ensure that the access token was successfully acquired.
if ($client->getAccessToken()) {
	
	$param = 'upcoming';
	$htmlBody = '';
	$yt_create = simplexml_load_file('/var/www/html/abcd/yt_create.xml');
	if ($yt_create != False) {
		$matchcounter = 0;
		$message = "";
		foreach ($yt_create->match as $match) {
			try {
				// Create an object for the liveBroadcast resource's snippet. Specify values
				// for the snippet's title, scheduled start time, and scheduled end time.
				
				// STEP 0: PREPARE DATA
				//   TITLE (format: 'SB League - Day x: TeamA vs. TeamB')
				$yt_title = $match->League2." - Day ".$match->Gameday.": ".$match->TeamAS." vs. ".$match->TeamBS;
				//echo $yt_title."/n";
				
				//   DESCRIPTION (format: LongTeamA vs. LongTeamB <newline> Location - MatchNo)
				$yt_description = $match->TeamA." vs. ".$match->TeamB."\n".$match->Location." - MatchNo: ".$match->GameId."\n";
				//echo $yt_description."/n";
			
				// Youtube seems to add 1h when programming the matches
				$Correction = -60 * 60;
				
				//   START AND END DATE (format: 15 mins before the match start and 2.5h duration)
				$yt_starttime = date('Y-m-d\TH:i:s.s\Z', $match->Datecode + $Correction - (15*60));
				$yt_endtime = date('Y-m-d\TH:i:s.s\Z', $match->Datecode + $Correction + (150*60));
				//echo $yt_starttime."-".$yt_endtime."/n";
				
				//   CHANNEL
				$yt_channel_id = 'UCgJw4GIqhkaIF7nYYqRI84w';
				
				//	TAGS
				$yt_tags = array($match->TeamA, $match->TeamB, $match->Location, $match->GameId, "Day - ".$match->Gameday);
				
				// STEP 1: CHECK IF BROADCAST IS ALREADY EXISTING
				//echo "X: ".$yt_title."/n";
				$counter = 0;
				$broadcastid = '';
				
				$broadcastsResponse = $youtube->liveBroadcasts->listLiveBroadcasts(
					'id, snippet, contentDetails, status',
					array(
						'broadcastStatus' => $param,
						'maxResults' => 50,
					));
					
				foreach ($broadcastsResponse['items'] as $broadcastItem) {
					if ((trim(htmlspecialchars($broadcastItem['snippet']['title'])) === trim($yt_title)) and (trim(htmlspecialchars($broadcastItem['snippet']['description'])) === trim($yt_description))) {
						// found existing broadcast, break look
						$broadcastid = trim(htmlspecialchars($broadcastItem['id']));
						$counter = 51;
						// echo "Existing broadcast ".$yt_title." with Id ".$broadcastItem['id']." found. No need to create/n";
						break;
					}
				}	
				
				$nextPageToken = $broadcastsResponse['nextPageToken'];
				
				while (($nextPageToken != '') and ($counter < 50)) {
					$counter += 1;
					
					foreach ($broadcastsResponse['items'] as $broadcastItem) {
						if ((trim(htmlspecialchars($broadcastItem['snippet']['title'])) === trim($yt_title)) and (trim(htmlspecialchars($broadcastItem['snippet']['description'])) === trim($yt_description))) {
							// found existing broadcast, break look
							$broadcastid = trim(htmlspecialchars($broadcastItem['id']));
							$counter = 51;
							// echo "Existing broadcast ".$yt_title." with Id ".$broadcastItem['id']." found. No need to create/n";
							break;
						}
					}
					$broadcastsResponse = $youtube->liveBroadcasts->listLiveBroadcasts(
					'id, snippet, contentDetails, status',
					array(
						'broadcastStatus' => $param,
						'maxResults' => 50,
						'pageToken' => $nextPageToken,
					));
					$nextPageToken = $broadcastsResponse['nextPageToken'];
					
				}
				
				// if no broadcast has been found
				if ($broadcastid == '') {
					
					// STEP 1: WRITE BROADCAST
					$broadcastSnippet = new Google_Service_YouTube_LiveBroadcastSnippet();
					$broadcastSnippet->setTitle($yt_title);
					$broadcastSnippet->setScheduledStartTime($yt_starttime);
					$broadcastSnippet->setScheduledEndTime($yt_endtime);
					$broadcastSnippet->setDescription($yt_description);
					$broadcastSnippet->setChannelId($yt_channel_id);
					// Tags seem not to exist
					//$broadcastSnippet->setTags($yt_tags);
					
					// Create an object for the liveBroadcast resource's status, and set the
					// broadcast's status to "private".
					$status = new Google_Service_YouTube_LiveBroadcastStatus();
					$status->setPrivacyStatus('private');

					// Create the API request that inserts the liveBroadcast resource.
					$broadcastInsert = new Google_Service_YouTube_LiveBroadcast();
					$broadcastInsert->setSnippet($broadcastSnippet);
					$broadcastInsert->setStatus($status);
					$broadcastInsert->setKind('youtube#liveBroadcast');

					// Execute the request and return an object that contains information
					// about the new broadcast.
					/* echo "Broadcast ".$yt_title." to be created/n";
					echo $yt_title."/n";
					echo $yt_description."/n";
					echo $yt_starttime."/n";
					echo $yt_endtime."/n"; */
					
					$broadcastsResponse = $youtube->liveBroadcasts->insert('snippet,status',
							$broadcastInsert, array());
					
					// STEP 2: CHECK STREAM AND CREATE STREAM IF IT DOES NOT EXIST
					$resolution = setresolution($match->TeamAS['id']);
					$yt_streamname = substr($match->TeamAS,0,3)."_".$resolution;
					// echo "Streamname: ".$yt_streamname;
					$streamsResponse = $youtube->liveStreams->listLiveStreams('id,snippet', array(
						'mine' => 'true', 'maxResults' => 50,
						));

					// check if stream already exists
						foreach ($streamsResponse['items'] as $streamItem) {
						if ($streamItem['snippet']['title'] == $yt_streamname) {
							// if found, then keep id for the bind later
							// echo "Stream ".$yt_streamname." already existing/n";
							break;
						}
						else {
							// need to create stream
							
							// Create an object for the liveStream resource's snippet. Specify a value
							// for the snippet's title.
							$streamSnippet = new Google_Service_YouTube_LiveStreamSnippet();
							$streamSnippet->setTitle($yt_streamname);

							// Create an object for content distribution network details for the live
							// stream and specify the stream's format and ingestion type.
							$cdn = new Google_Service_YouTube_CdnSettings();
							$cdn->setFormat($resolution);
							$cdn->setIngestionType('rtmp');

							// Create the API request that inserts the liveStream resource.
							$streamInsert = new Google_Service_YouTube_LiveStream();
							$streamInsert->setSnippet($streamSnippet);
							$streamInsert->setCdn($cdn);
							$streamInsert->setKind('youtube#liveStream');

							// Execute the request and return an object that contains information
							// about the new stream.
							
							// echo "stream ".$yt_streamname." creation/n";
							$streamsResponse = $youtube->liveStreams->insert('snippet,cdn',	$streamInsert, array());
							break;
						}	
					}
		  
					// STEP 3: BIND BROADCAST TO LIVESTREAM
					$broadcastid = $broadcastsResponse['id'];
					$bindBroadcastResponse = $youtube->liveBroadcasts->bind(
					$broadcastsResponse['id'],'id,contentDetails',
						array(
						'streamId' => $streamsResponse['id'],
						));
					// echo "ResponseID: ".$broadcastsResponse['id']."/".$streamsResponse['id']."/n";
					
					// STEP 4: INSERT VIDEO IN PLAYLIST
					switch(strtoupper($match->League2)) {
							case "SB LEAGUE":
								$playlistid = 'PLehi_sVuvkbf32PAsjYaY1U1p-IdVGtPW';
								break;
							case "SB LEAGUE WOMEN":
								$playlistid = 'PLehi_sVuvkbeNT0V1IQuH6ElwSwyfiGxS';
								break;
							case "NLB":
								$playlistid = 'PLehi_sVuvkbfXE-xpDhKypda_JjaAtW6V';
								break;
							case "NLB WOMEN":
								$playlistid = 'PLehi_sVuvkbfLd-c76ku4Q6P6Em76QfdW';
								break;
							case "1LM":
								$playlistid = 'PLehi_sVuvkbeMsMfiGAfZ4-zrSJuZ1y52';
								break;
							default:
								$playlistid = '';
								break;
					}
					
					if ($playlistid != '') {
						playlistItemsInsert($youtube,
							array('snippet.playlistId' => $playlistid,
								   'snippet.resourceId.kind' => 'youtube#video',
								   'snippet.resourceId.videoId' => $broadcastsResponse['id'],
								   'snippet.position' => ''),
							'snippet', 
							array('onBehalfOfContentOwner' => ''));						
					}
									
					// STEP 5: UPDATE THUMBNAILS AFTER HAVING OBTAINED THE VIDEO ID
					$imagePath = "/var/www/html/thumbs/".$match->League."/".substr($match->TeamAS,0,3)."_".substr($match->TeamBS,0,3).".png";
					// echo $imagePath."/n";
					if (file_exists($imagePath)) {
						// echo "Path valid/n";
						
						// Specify the size of each chunk of data, in bytes. Set a higher value for
						// reliable connection as fewer chunks lead to faster uploads. Set a lower
						// value for better recovery on less reliable connections.
						$chunkSizeBytes = 1 * 1024 * 1024;

						// Setting the defer flag to true tells the client to return a request which can be called
						// with ->execute(); instead of making the API call immediately.
						$client->setDefer(true);

						// Create a request for the API's thumbnails.set method to upload the image and associate
						// it with the appropriate video.
						
						$setRequest = $youtube->thumbnails->set($broadcastsResponse['id']);
						
						// Create a MediaFileUpload object for resumable uploads.
						 $media = new Google_Http_MediaFileUpload(
							$client,
							$setRequest,
							'image/png',
							null,
							true,
							$chunkSizeBytes
						);
						$media->setFileSize(filesize($imagePath));

						// Read the media file and upload it chunk by chunk.
						$status = false;
						$handle = fopen($imagePath, "rb");
						while (!$status && !feof($handle)) {
						  $chunk = fread($handle, $chunkSizeBytes);
						  $status = $media->nextChunk($chunk);
						}

						fclose($handle);

						// If you want to make other calls after the file upload, set setDefer back to false
						$client->setDefer(false);
						
					}
					
				}
				if ($matchcounter == 0) {
					// write header
					$message =  '<html><body><h2>swissbasket.tv programmed matches for the forthcoming period</h2>';
					$message .= '<table><tr><th>Date</th><th>Time</th><th>League</th><th>Location</th><th>Gameday</th>';
					$message .= '<th>GameId</th><th>TeamA</th><th>TeamB</th><th>Livestat</th><th>Youtube</th>';
				}
				
				// write table input
				$message .= '<tr>';
				$message .= '<td>'.$match->Date.'</td><td>'.$match->Time.'</td><td>'.$match->League2.'</td>';
				$message .= '<td>'.$match->Location.'</td><td>Day '.$match->Gameday.'</td><td>'.$match->GameId.'</td>';
				$message .= '<td>'.$match->TeamAS.'</td><td>'.$match->TeamBS.'</td><td>'.$match->LiveStat.'</td>';
				$message .= '<td>'.$broadcastid.'</td>';
				$message .= '</tr>';
			
				$matchcounter += 1;
				// echo "counter: ".$matchcounter."/n";
				
				
			} catch (Google_Service_Exception $e) {
				$htmlBody = sprintf('<p>A service error occurred: <code>%s</code></p>',
				htmlspecialchars($e->getMessage()));
			} catch (Google_Exception $e) {
				$htmlBody = sprintf('<p>An client error occurred: <code>%s</code></p>',
				htmlspecialchars($e->getMessage()));
			}	
		
		}
		
		// Send Mail

		$mail = new PHPMailer;
		$mail->isSMTP();
		$mail->SMTPDebug = 0;
		$mail->Host = 'smtp.gmail.com';
		$mail->Port = 587;
		$mail->SMTPSecure = 'tls';
		$mail->SMTPAuth = true;
		$mail->Username = "swissbaskettv@gmail.com";
		$mail->Password = "basket.ball";
		$mail->setFrom('swissbaskettv@gmail.com', 'Thomas Kohler');
		$mail->addReplyTo('swissbaskettv@gmail.com', 'Thomas Kohler');
		$mail->addAddress('gilles.delessert@swissbasketball.ch', 'Gilles Delessert');
		$mail->addAddress('thomas.kohler@swissbasketball.ch', 'Thomas Kohler');
		$mail->addAddress('swissbasketball@diya.pro', 'Andriy Kryvoruchko');
		$mail->Subject = 'Weekly swissbaskettv programming on youtube';
		// echo "Message/n";
		// echo $message;
		$mail->msgHTML($message);
		$mail->AltBody = 'Weekly swissbaskettv programming on youtube';

		//send the message, check for errors
		if (!$mail->send()) {
			echo "Mailer Error: " . $mail->ErrorInfo;
		} else {
			echo "Message sent!";
			//Section save via IMAP
			if (save_mail($mail)) {
			    echo "Message saved!";
			}
		}
	}
	else {
		$htmlBody = "XML Faulty";
	}
	$_SESSION[$tokenSessionKey] = $client->getAccessToken();
} elseif ($OAUTH2_CLIENT_ID == 'REPLACE_ME') {
$htmlBody = <<<END
<h3>Client Credentials Required</h3>
<p>
You need to set <code>\$OAUTH2_CLIENT_ID</code> and
<code>\$OAUTH2_CLIENT_ID</code> before proceeding.
<p>
END;
} else {
  // If the user hasn't authorized the app, initiate the OAuth flow
  $state = mt_rand();
  $client->setState($state);
  $_SESSION['state'] = $state;
  $authUrl = $client->createAuthUrl();
  $htmlBody = <<<END
  <h3>Authorization Required</h3>
  <p>You need to <a href="$authUrl">authorize access</a> before proceeding.<p>
END;
}
?>