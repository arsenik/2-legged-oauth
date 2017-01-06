<?php

require_once('src/OAuth.php');

// Establish an OAuth consumer based on our admin 'credentials'
$CONSUMER_KEY = 'xxxxxxx';
$CONSUMER_SECRET = 'yyyyyyyyyy';
$consumer = new OAuthConsumer($CONSUMER_KEY, $CONSUMER_SECRET, NULL);

// Setup OAuth request based our previous credentials and query
$user = 'any.user@yourdomain.com';
$base_feed = 'http://whatever.com/api/get/something';
$params = array('max-results' => 10);
$request = OAuthRequest::from_consumer_and_token($consumer, NULL, 'GET', $base_feed, $params);

// Sign the constructed OAuth request using HMAC-SHA1
$request->sign_request(new OAuthSignatureMethod_HMAC_SHA1(), $consumer, NULL);

// Make signed OAuth request to the Contacts API server
$url = $base_feed . '?' . http_build_query($params);
echo send_request($request->get_normalized_http_method(), $url, $request->to_header());

/**
 * Makes an HTTP request to the specified URL
 * @param string $http_method The HTTP method (GET, POST, PUT, DELETE)
 * @param string $url Full URL of the resource to access
 * @param string $auth_header (optional) Authorization header
 * @param string $postData (optional) POST/PUT request body
 * @return string Response body from the server
 */
function send_request($http_method, $url, $auth_header = null, $postData = null) {
	$curl = curl_init($url);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_FAILONERROR, false);
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
	switch($http_method) {
		case 'GET':
			if($auth_header) {
				curl_setopt($curl, CURLOPT_HTTPHEADER, array($auth_header));
			}
			break;
		case 'POST':
			curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/atom+xml',
				$auth_header));
			curl_setopt($curl, CURLOPT_POST, 1);
			curl_setopt($curl, CURLOPT_POSTFIELDS, $postData);
			break;
		case 'PUT':
			curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/atom+xml',
				$auth_header));
			curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $http_method);
			curl_setopt($curl, CURLOPT_POSTFIELDS, $postData);
			break;
		case 'DELETE':
			curl_setopt($curl, CURLOPT_HTTPHEADER, array($auth_header));
			curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $http_method);
			break;
	}
	$response = curl_exec($curl);
	if(!$response) {
		$response = curl_error($curl);
	}
	curl_close($curl);
	return $response;
}
