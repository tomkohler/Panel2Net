<?php

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

function getClient() {
	$client = new Google_Client();
	$client->setApplicationName('list_broadcasts');
	$client->setScopes('https://www.googleapis.com/auth/youtube');
	$client->setAuthConfig('/var/www/html/ytprog/php/client_secret.json');
	$client->setRedirectUri('http://swb.world/ytprog/php/create_report.php');
	$client->setAccessType('offline');
	//$client->setApprovalPrompt('force');

	// Load previously authorized credentials from a file.
	$credentialsPath = expandHomeDirectory(CREDENTIALS_PATH);
	if (file_exists($credentialsPath)) {
		$accessToken = json_decode(file_get_contents($credentialsPath), true);
	} 
	else {
		// Request authorization from the user.
		$authUrl = $client->createAuthUrl();
		header('Location: ' . filter_var($authUrl, FILTER_SANITIZE_URL));
		// debug
		// file_put_contents("debug1.txt", 'Location: ' . filter_var($authUrl, FILTER_SANITIZE_URL));
		if (isset($_GET['code'])) {
			$authCode = $_GET['code'];
			// Exchange authorization code for an access token.
			$accessToken = $client->fetchAccessTokenWithAuthCode($authCode);
			header('Location: ' . filter_var('http://swb.world/ytprog/php/create_report.php', FILTER_SANITIZE_URL));
			// debug
			// file_put_contents("debug2.txt", 'Location: ' . filter_var('http://swb.world/ytprog/php/create_report.php', FILTER_SANITIZE_URL));
			
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
	// file_put_contents("debug3.txt", print_r($client));
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

use PHPMailer\PHPMailer\PHPMailer;
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
	// $client is your Google_Client obj
	// here we set some params
	
	$channel = 'channel==MINE';
	$fromdate = date('Y-m-d', strtotime("-8 days"));
	$todate = date('Y-m-d');
	
	$metrics = 'views,estimatedMinutesWatched,likes,dislikes,shares,subscribersGained,subscribersLost';
	$api_response = $metrics;
	$analytics = new Google_Service_YouTubeAnalytics($client);
 
	$output = $analytics->reports->query(
		$channel,
		$fromdate,
		$todate,
		$metrics, array(
			// 'sort' => '-estimatedMinutesWatched',
			//'max-results' => '200',
			'dimensions' => 'day'
			)
		);
	
	$t1=0;
	$t2=0;
	$t3=0;
	$t4=0;
	$t5=0;
	$t6=0;
	$t7=0;
	
	//print_r($output);
	
	$htmloutput = "<html><style>table {font-family: arial, sans-serif; border-collapse: collapse; width: 100%;} td, th {border: 1px solid #dddddd; text-align: right; padding: 6px;} tr:nth-child(even) {background-color: #dddddd;}</style><title>Weekly Statistics</title>";
	
	
	$htmloutput .= "<body><h2>Weekly Statistics from ".date('d.m.Y', strtotime("-8 days"))." to ".date('d.m.Y')."</h2><table><tr><th>DATE</th><th>VIEWS</th><th>MINS</th><th>LIKE</th><th>DLIKE</th><th>SHARE</th><th>+SUBS</th><th>-SUBS</th></tr>";
	foreach($output['rows'] as $iter) { 
		//print_r($iter);
		 $htmloutput .= "<tr><td>".$iter[0]."</td><td>".$iter[1]."</td><td>".$iter[2]."</td><td>".$iter[3]."</td><td>".$iter[4]."</td><td>".$iter[5]."</td><td>".$iter[6]."</td><td>".$iter[7]."</td></tr>";
		$t1 += $iter[1];
		$t2 += $iter[2];
		$t3 += $iter[3];
		$t4 += $iter[4];
		$t5 += $iter[5];
		$t6 += $iter[6];
		$t7 += $iter[7]; 
	} 
	$htmloutput .= "<tr><td>TOTAL</td><td>".$t1."</td><td>".$t2."</td><td>".$t3."</td><td>".$t4."</td><td>".$t5."</td><td>".$t6."</td><td>".$t7."</td></tr></table></body></html>";	
	
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
	$mail->Subject = 'Weekly swissbaskettv statistics (test)';
	// echo "Message/n";
	// echo $message;
	$mail->msgHTML($htmloutput);
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
		
	//echo "Ended";
	echo $htmloutput;
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