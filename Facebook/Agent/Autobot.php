<?php 
namespace Facebook\Agent;
use Facebook\Bot;
class Autobot extends Agent
{
	protected $_sName = "Autobot";
	public function deliveryMessage($aMessage)
	{
		return $this->textMessage($aMessage);
	}
	public function broadCast($aEventMessage)
	{
		\Log::append($this->getLogNameFile(),"broadcast event");
		\Log::append($this->getLogNameFile(),$aEventMessage);
		if(isset($aEventMessage['optin']))
		{
			$this->receivedAuthentication($aEventMessage);
		}
		if(isset($aEventMessage['message']))
		{
			$this->receivedMessage($aEventMessage);
		}
		if(isset($aEventMessage['delivery']))
		{
			$this->receivedDeliveryConfirmation($aEventMessage);
		}
		if(isset($aEventMessage['postback']))
		{
			$this->receivedPostback($aEventMessage);
		}
		if(isset($aEventMessage['read']))
		{
			$this->receivedMessageRead($aEventMessage);
		}
		if(isset($aEventMessage['account_linking']))
		{
			$this->receivedAccountLink($aEventMessage);
		}
	}
	public function receivedMessage($aEventMessage)
	{
		$sMessageText = $aEventMessage['message']['text'];
		$bIsEcho = isset($aEventMessage['message']['is_echo']) ? $aEventMessage['message']['is_echo'] : false;
		$bIsQuickReply = isset($aEventMessage['message']['quick_reply']) ? $aEventMessage['message']['quick_reply'] : false;
		$this->buttonMessage($aEventMessage);
	}
	public function buttonMessage($mReplyMessage)
	{
		$aReplyMessage = array(
				"recipient" => array(
						"id" => $this->getSenderId(),
				),
				"message" => array(
						"attachment" => array(
								"type" => "template",
								"payload" => array(
										"template_type" => "button",
										"text" => "Bạn có muốn tư vấn tự động?",
										"buttons" => array(
												array(
														"type" => "postback",
														"title" => "Chat voi tu van vien",
														"payload" => serialize(array($this->getId(),$this->getPageId()))
												),
												array(
														"type" => "postback",
														"title" => "Tiep tuc",
														"payload" => serialize(array($this->getId(),$this->getPageId()))
												)
										)
								)
						)
				)
		);
		\Log::write($this->getLogNameFile(),"broadcast to facebook");
		\Log::append($this->getLogNameFile(),$aReplyMessage);
		$response = $this->postBack2Page($aReplyMessage);
		\Log::append($this->getLogNameFile(),"response from facebook side");
		\Log::append($this->getLogNameFile(),$response);
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
