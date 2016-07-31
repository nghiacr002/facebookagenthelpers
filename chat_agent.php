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
	if(isset($_SESSION['facebook_access_token']))
	{
		$accessToken = $_SESSION['facebook_access_token'];
	}
	if(!$accessToken){
		$accessToken = $helper->getAccessToken();
	}
	
  
} catch(Facebook\Exceptions\FacebookResponseException $e) {
  // When Graph returns an error
  echo 'Graph returned an error: ' . $e->getMessage();
  exit;
} catch(Facebook\Exceptions\FacebookSDKException $e) {
  // When validation fails or other local issues
  echo 'Facebook SDK returned an error: ' . $e->getMessage();
  exit;
}

if (isset($accessToken) && $accessToken) {
  // Logged in!
  $_SESSION['facebook_access_token'] = (string) $accessToken;

  // Now you can redirect to another page and use the
  // access token from $_SESSION['facebook_access_token']
}else{
	$permissions = ['email', 'user_likes']; // optional
$loginUrl = $helper->getLoginUrl('https://ssl.fwebshop.com/thilathang/login-callback.php', $permissions);

echo '<a href="' . $loginUrl . '">Log in with Facebook!</a>';exit;
}
?>
<?php if (isset($accessToken)):?>
<!DOCTYPE html>
<html>
<head>
<title>Web Agent Chat</title>
<meta charset="utf-8" />
<script src="https://code.jquery.com/jquery-1.12.4.min.js"></script>
<script type="text/javascript">
var ws, url = 'ws://ssl.fwebshop.com/socket/';
var sender_id = "";
var page_id = "1169673899771529";
var receipt_id = "1169673899771529";
window.onbeforeunload = function() {
	ws.send('quit');
};

window.onload = function() {
	try {
		ws = new WebSocket(url);
		//write('Connecting... (readyState '+ws.readyState+')');
		ws.onopen = function(msg) {
			//write('Connection successfully opened (readyState ' + this.readyState+')');
		};
		ws.onmessage = function(msg) {
			
			write(msg);
		};
		ws.onclose = function(msg) {
			if(this.readyState == 2){
				//write('Closing... The connection is going throught the closing handshake (readyState '+this.readyState+')');	
			}
				
			else if(this.readyState == 3){
				//write('Connection closed... The connection has been closed or could not be opened (readyState '+this.readyState+')');
			}	
			else{
				//write('Connection closed... (unhandled readyState '+this.readyState+')');
			}
				
		};
		ws.onerror = function(event) {
			//terminal.innerHTML = '<li style="color: red;">'+event.data+'</li>'+terminal.innerHTML;
		};

	}
	catch(exception) {
		write(exception);
	}
};

function write(msg) {
	if(msg.data){
		
		var dataMessage = $.parseJSON(msg.data);
		if(dataMessage.action){
			console.log(msg);
			switch(dataMessage.action){
				case "broadcast_client":
					data = dataMessage.data;
					if(sender_id != "" && sender_id != data.sender.id){
						//do nothing
					}else{
						sender_id = data.sender.id;
						receipt_id = data.recipient.id;
						msg = data.message.text;
						var msg_html = '<div class="chat-body clearfix">' + 
	                        '<div class="header">'
	                 +   '<strong class="primary-font">FROM ' +sender_id + ' </strong>'
	                + '</div>'
	                + '<p>'
	                +    msg
	                + '</p>'
	            	+'</div>';
						var li = '<li class="left clearfix"><span class="chat-img pull-left"><img src="http://placehold.it/50/55C1E7/fff&text=U" alt="User Avatar" class="img-circle" /></span>'+msg_html+'</li>';
						$('#chat-holder-data').append(li);
						break;
					}
					
				default:
					break;
			}
		}
	}
	
}
function onlineCommand(e)
{
	var message = {
			"id": "1065880806826487",
			"action": "login",
		};
	ws.send(JSON.stringify(message));
	$(e).hide();
}
function sendCommand()
{
	
	/*
	var message = {
		"id": "1065880806826487",
		"action": "login",
	};
	var message = {
			"receipt_id": "1065880806826487",
			"page_id": "1169673899771529",
			"sender_id":"1065880806826487",
			"message":$("#btn-input").val()

		}
	*/
	var message = {
		"receipt_id": sender_id,
		"page_id": page_id,
		"sender_id":sender_id,
		"message":$("#btn-input").val(),
		"action": "chat",
	}
	msg = $("#btn-input").val();
	var msg_html = '<div class="chat-body clearfix">' + 
        '<div class="header">'
 +   '<strong class="pull-right primary-font">Me</strong>'
+ '</div>'
+ '<p>'
+    msg
+ '</p>'
+'</div>';
	var li = '<li class="right clearfix"><span class="chat-img pull-right"><img src="http://placehold.it/50/55C1E7/fff&text=U" alt="User Avatar" class="img-circle" /></span>'+msg_html+'</li>';
	$('#chat-holder-data').append(li);
	$("#btn-input").val("");
	ws.send(JSON.stringify(message));
}
</script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
<LINK href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet" type="text/css">
<style>
.chat
{
    list-style: none;
    margin-top: 0;
    padding: 0;
}

.chat li
{
    margin-bottom: 10px;
    padding-bottom: 5px;
    border-bottom: 1px dotted #B3A9A9;
}

.chat li.left .chat-body
{
    margin-left: 60px;
}

.chat li.right .chat-body
{
    margin-right: 60px;
}


.chat li .chat-body p
{
    margin: 0;
    color: #777777;
}

.panel .slidedown .glyphicon, .chat .glyphicon
{
    margin-right: 5px;
}

.panel-body
{
    overflow-y: scroll;
   	min-height: 400px;
}

::-webkit-scrollbar-track
{
    -webkit-box-shadow: inset 0 0 6px rgba(0,0,0,0.3);
    background-color: #F5F5F5;
}

::-webkit-scrollbar
{
    width: 12px;
    background-color: #F5F5F5;
}

::-webkit-scrollbar-thumb
{
    -webkit-box-shadow: inset 0 0 6px rgba(0,0,0,.3);
    background-color: #555;
}

</style>
</head>
<body>
<div class="container" style="margin:0;padding:0;width:100%;">
    <div class="row" style="margin:0;padding:0;width:100%;">
        <div class="col-md-12" style="margin:0;padding:0;width:100%;">
            <div class="panel panel-primary">
                <div class="panel-heading" id="accordion">
                    <span class="glyphicon glyphicon-comment"></span> Chat Agent
                     <button class="btn btn-warning btn-sm" id="btn-chat" onclick="onlineCommand(this);" style="float:right;margin-top:-5px;">
                                Online My Account</button>
                </div>
            <div class="panel-collapse" id="collapseOne">
                <div class="panel-body">
                    <ul class="chat" id="chat-holder-data">
                       
                    </ul>
                </div>
                <div class="panel-footer">
                    <div class="input-group">
                        <input id="btn-input" type="text" class="form-control input-sm" placeholder="Type your message here..." />
                        <span class="input-group-btn">
                            <button class="btn btn-warning btn-sm" id="btn-chat" onclick="sendCommand();">
                                Send</button>
                        </span>
                    </div>
                </div>
            </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>

<?php endif;?>