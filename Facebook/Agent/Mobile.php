<?php 
namespace Facebook\Agent;
use Facebook\Bot;

require_once APP_PATH. 'socket_client.php';

class Mobile extends Agent
{
	public function broadCast($aEventMessage)
	{
// 		global $server_sock;
		if(isset($aEventMessage['message']))
		{

			$server_sock = new \WebsocketClient();
			$server_sock->connect(SERVER_IP, SERVER_PORT, "/",true);
			
			$aMessage = array(
					"action" => "broadcast_client",
					"data" => $aEventMessage
			);
			$server_sock->sendData(json_encode($aMessage));
			$server_sock->disconnect();
		}
		
	}
	public function deliveryMessage($aMessage)
	{
		return $this->textMessage($aMessage);
	}
	public function textMessage($mReplyMessage)
	{
		//$sReceiveId = $this->getClientId();
		$aReplyMessage = array(
				"recipient" => array(
						"id" => $this->getSenderId(),
				),
				"message" => array(
						"text" => $mReplyMessage['text'],
						"metadata" => serialize(array($this->getId(),$this->getPageId()))
				)
		);
		\Log::write($this->getLogNameFile(),"broadcast to facebook");
		\Log::append($this->getLogNameFile(),$aReplyMessage);
		$response = $this->postBack2Page($aReplyMessage);
		\Log::append($this->getLogNameFile(),"response from facebook side");
		\Log::append($this->getLogNameFile(),$response);
		return $response;
	}
}