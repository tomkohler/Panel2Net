<?php

function broadcast2xml($yt_handle, $param) {
	$htmlBody = '';
	if (strtoupper($param) != 'ALL') {
		$param = 'upcoming';
	}
	
	try {
    // Execute an API request that lists broadcasts owned by the user who
    // authorized the request.
		$broadcastsResponse = $yt_handle->liveBroadcasts->listLiveBroadcasts(
			'id, snippet',
			array(
				'broadcastStatus' => $param,
				'maxResults' => 50,
			));
		
		// echo "entering function<br>";
		$htmlBody = '<?xml version="1.0"?><broadcasts>';	
		$counter = 0;
		
		foreach ($broadcastsResponse['items'] as $broadcastItem) {				
			$htmlBody .= "<broadcast><title>".htmlspecialchars($broadcastItem['snippet']['title']);
			$htmlBody .= "</title><description>".htmlspecialchars($broadcastItem['snippet']['description']);
			$htmlBody .= "</description><starttime>".strtotime($broadcastItem['snippet']['actualStartTime']);
			$htmlBody .= "</starttime><endtime>".strtotime($broadcastItem['snippet']['actualEndTime']);
			$htmlBody .= "</endtime><thumbnail>".htmlspecialchars($broadcastItem['snippet']['thumbnails']['high']['url']);
			$htmlBody .= "</thumbnail><id>".htmlspecialchars($broadcastItem['id'])."</id></broadcast>";
		}
		// echo $htmlBody."<br>";
		$broadcastsResponse = $yt_handle->liveBroadcasts->listLiveBroadcasts(
		'id, snippet, contentDetails, status',
		array(
			'broadcastStatus' => $param,
			'maxResults' => 50,
		));

		$nextPageToken = $broadcastsResponse['nextPageToken'];
		
		while (($nextPageToken != '') and ($counter < 50)) {
			$counter += 1;
			
			foreach ($broadcastsResponse['items'] as $broadcastItem) {
				
				$htmlBody .= "<broadcast><title>".htmlspecialchars($broadcastItem['snippet']['title']);
				$htmlBody .= "</title><description>".htmlspecialchars($broadcastItem['snippet']['description']);
				$htmlBody .= "</description><starttime>".strtotime($broadcastItem['snippet']['actualStartTime']);
				$htmlBody .= "</starttime><endtime>".strtotime($broadcastItem['snippet']['actualEndTime']);
				$htmlBody .= "</endtime><thumbnail>".htmlspecialchars($broadcastItem['snippet']['thumbnails']['high']['url']);
				$htmlBody .= "</thumbnail><id>".htmlspecialchars($broadcastItem['id'])."</id></broadcast>";
			}
			$broadcastsResponse = $yt_handle->liveBroadcasts->listLiveBroadcasts(
			'id, snippet, contentDetails, status',
			array(
				'broadcastStatus' => $param,
				'maxResults' => 50,
				'pageToken' => $nextPageToken,
			));
			$nextPageToken = $broadcastsResponse['nextPageToken'];
			
		}
		$htmlBody .= '</broadcasts>';

	} catch (Google_Service_Exception $e) {
		echo sprintf('<p>Error: <code>%s</code></p>',
        htmlspecialchars($e->getMessage()));
  } catch (Google_Exception $e) {
		echo sprintf('<p>Error: <code>%s</code></p>',
        htmlspecialchars($e->getMessage()));
  }
  return ($htmlBody);
}

function getClient() {
	$client = new Google_Client();
	$client->setApplicationName('list_broadcasts');
	$client->setScopes('https://www.googleapis.com/auth/youtube');
	$client->setAuthConfig('/var/www/html/ytprog/php/client_secret.json');
	$client->setRedirectUri('http://swb.world/ytprog/php/list_broadcasts.php');
	$client->setAccessType('offline');
	$client->setApprovalPrompt('force');

	// Load previously authorized credentials from a file.
	$credentialsPath = expandHomeDirectory(CREDENTIALS_PATH);
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
/*
 * You can acquire an OAuth 2.0 client ID and client secret from the
 * {{ Google Cloud Console }} <{{ https://cloud.google.com/console }}>
 * For more information about using OAuth 2.0 to access Google APIs, please see:
 * <https://developers.google.com/youtube/v3/guides/authentication>
 * Please ensure that you have enabled the YouTube Data API for your project.
 */

$client = getClient();
// Define an object that will be used to make all API requests.
$youtube = new Google_Service_YouTube($client);
// Check if an auth token exists for the required scopes
$tokenSessionKey = 'token-' . $client->prepareScopes();

if (isset($_GET['code'])) {
  //var_dump($_GET['code']);
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
	// echo "routine starting<br>";
	$upcoming_xml = broadcast2xml($youtube, 'upcoming');	
	file_put_contents('/var/www/html/abcd/yt_upcoming.xml', $upcoming_xml);
	$all_xml = broadcast2xml($youtube, 'all');    	
	file_put_contents('/var/www/html/abcd/yt_all.xml', $all_xml);
	$_SESSION[$tokenSessionKey] = $client->getAccessToken();
	echo "Ended";
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