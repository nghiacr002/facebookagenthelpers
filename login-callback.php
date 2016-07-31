<?php
if(!session_id()) {
	session_start();
}
require_once __DIR__ . '/vendor/autoload.php';
	$fb = new Facebook\Facebook([
  'app_id' => '161666920922824',
  'app_secret' => '45f5493391e0875512f4c42eca1efb11',
  'default_graph_version' => 'v2.5',
]);

$helper = $fb->getRedirectLoginHelper();
try {
  $accessToken = $helper->getAccessToken();
} catch(Facebook\Exceptions\FacebookResponseException $e) {
  // When Graph returns an error
  echo 'Graph returned an error: ' . $e->getMessage();
  exit;
} catch(Facebook\Exceptions\FacebookSDKException $e) {
  // When validation fails or other local issues
  echo 'Facebook SDK returned an error: ' . $e->getMessage();
  exit;
}

if (isset($accessToken)) {
  // Logged in!
  $_SESSION['facebook_access_token'] = (string) $accessToken;
	header('Location:'."https://ssl.fwebshop.com/thilathang/chat_agent.php");
  // Now you can redirect to another page and use the
  // access token from $_SESSION['facebook_access_token']
}else{
	
}
?>