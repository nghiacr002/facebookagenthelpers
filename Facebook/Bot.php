<?php 
/**
 * Class handle all of request from Facebook and delivery to correct connected client agent 
 */
namespace Facebook;
use Facebook\Bot;
use Facebook\Agent\Agent as Agent;
use Facebook\Agent\Web;
use Facebook\Agent\AutoBot;
use Facebook\Agent\Mobile;
class Bot
{
	const VERSION = "1.0";
	const MAX_CLIENT = 5;
	const AUTOBOT_ID = "1234567890817auto";
	private $_oRequest;
	private $_sLogName;
	private $_aAgents = array();
	public static $instance;
	public function __construct( Request $callable)
	{
		$this->_oRequest = $callable;
		$this->init();
	}
	public function init()
	{
		self::$instance = $this;
		$this->getAgents();
		$this->_sLogName = date("m-d-y").".txt";
		return $this;
	}
	public function getLogName()
	{
		return $this->_sLogName;
	}
	public function getAgents()
	{
		$this->_aAgents = array();
		$aFiles = scandir(APP_PATH."Data". APP_DS);
		
		foreach($aFiles as $iKey => $sFile)
		{
			if(strpos($sFile,"Agent_") !== false)
			{
				$id = str_replace("Agent_", "", $sFile);
				$id = str_replace(".php","",$id);
				$this->_aAgents[$id] = array(
					"name" => "Agent ID ".$id,
					"type" => "mobile",
					"is_online" => 1,
				);
			}
		}
		if(!isset($this->_aAgents[Bot::AUTOBOT_ID]))
		{
			$this->_aAgents[Bot::AUTOBOT_ID] = array(
					"name" =>  "AUTO DEMO BOT",
					"type" => "bot",
					"is_online" => 1,
			);
		}
		return $this->_aAgents;
	}
	public function handle()
	{
		$sSeg1 = $this->_oRequest->seg(1);
		$sRequestMethod = $this->_oRequest->getMethod();
		$sFunctionName = strtolower($sRequestMethod). ucfirst($sSeg1);
		\Log::write($this->_sLogName,"call name ".$sFunctionName);
		\Log::append($this->_sLogName,$this->_oRequest);
		if(!method_exists($this, $sFunctionName))
		{
			throw new \Exception("FORBIDEN", HTTP_CODE_FORBIDDEN);
		}
		$sMessage = $this->{$sFunctionName}();
		//global  $server_sock;
		//$server_sock->disconnect();
		echo $sMessage;
		
		exit;
	}
	public function getAuthorize()
	{
		$aParams = $this->_oRequest->getParams();
		\Log::write($this->_sLogName. "authorize",$aParams);
	}
	public function getWebhook()
	{
		$aParams = $this->_oRequest->getParams();
		\Log::write($this->_sLogName,$aParams);
		if( isset($aParams['hub_mode']) && $aParams['hub_mode'] == "subscribe"
				&& isset($aParams['hub_verify_token']) && $aParams['hub_verify_token'] == APP_TOKEN
		)
		{
			return $aParams['hub_challenge'];
		}
		return HTTP_CODE_FORBIDDEN;
	}
	public function postWebhook()
	{
		$aParams = $this->_oRequest->getParams();
		\Log::write($this->_sLogName,$aParams);
		if( isset($aParams['object']) && $aParams['object'] == "page")
		{
			$aPageEntries = isset($aParams['entry']) ? $aParams['entry'] : array();
			$oAgent = null;
			if(count($aPageEntries))
			{
				foreach($aPageEntries as $aEntry)
				{
					$sPageId = $aEntry['id'];
						
					foreach($aEntry['messaging'] as $aEventMessage)
					{
						$sRecipientId = $aEventMessage['recipient']['id'];
						$sSenderId = $aEventMessage['sender']['id'];
						\Log::append($this->_sLogName,"Page ID = ".$sPageId . " AND client ID = ".$sSenderId );
						if(!$oAgent)
						{
							$oAgent = $this->selectAgent($sPageId, $sSenderId);
						}
						\Log::append($this->_sLogName, "Going here");
						if($oAgent)
						{
							$oAgent->addClient($sPageId, $sSenderId);
							\Log::append($this->_sLogName,"current select agent is ". $oAgent->getName());
							$oAgent->setCurrentClientId($sSenderId);
							$oAgent->setCurrentPageId($sPageId);
							$oAgent->setSenderId($sSenderId);
							\Log::append($this->_sLogName,"start broadcast message ". $oAgent->getName());
							$oAgent->broadCast($aEventMessage);
						}
						else
						{
							\Log::write($this->_sLogName,"No agent found");
						}
					}
				}
			}
			else
			{
				\Log::append($this->_sLogName,"No Page Entries");
			}
			return HTTP_CODE_OK;
		}
		return HTTP_CODE_OK;
	}
	public function selectAgent($sPageId, $sRecipientId)
	{
		// select free agent and assign this client to him/her
		$oAgent = null;
		
		foreach($this->_aAgents as $sAgentId => $aAgentInfo)
		{
			if($aAgentInfo['is_online'] != Agent::ONLINE)
			{
				continue;
			}
			switch ($aAgentInfo['type'])
			{
				case "web":
					\Log::append($this->_sLogName, "create web instance");
					$oAgent = new \Facebook\Agent\Web($sAgentId);
					break;
				case "mobile":
					$oAgent = new \Facebook\Agent\Mobile($sAgentId);
					\Log::append($this->_sLogName, "create mobile instance ".$oAgent->getName());
					break;
				default:
					\Log::append($this->_sLogName, "create autobot instance");
					$oAgent = new \Facebook\Agent\Autobot($sAgentId);
					break;
			}
			\Log::append($this->_sLogName, "create autobot completed ".$oAgent->getName());
			if($oAgent->isAvalible($sPageId, $sRecipientId))
			{
				echo "available Log";
				var_dump($oAgent);
				$oAgent->init();
				return $oAgent;
			}
			else
			{
				$oAgent = null;
			}
			//\Log::append($this->_sLogName, "Check agent : ". $oAgent->getName(). " is not available ".$sPageId." -- ".$sRecipientId);
		}
		return null;
	}
	public static function getInstace()
	{
		if(!self::$instance)
		{
			self::$instance = new Bot(new \Facebook\Request());
		}
		return self::$instance;
	}
	public static function socketBroadCastMessages($client_socket, $data)
	{
		$sefl = self::getInstace();
		$mData = json_decode($data,true);
		if(!is_array($mData))
		{
			\Log::append($sefl->getLogName(),"Client post invalid Data ");
			echo "log frail";
			\Log::append($sefl->getLogName(),$data);
			return false;
		}
		if(isset($mData['action']) && !empty($mData['action']))
		{
			$sAction = strtolower($mData['action']);
			$mReturn = array(
				"action" => $sAction,
			);
			switch ($sAction)
			{
				case "login":
					$sAgentId = isset($mData['id'])?$mData['id']:"";
					if(!empty($sAgentId))
					{
						(new Agent($sAgentId))->register($client_socket);
						$mReturn["message"] = "Login and register successfully";
						$mReturn["page_id"] = PAGE_ID;
					}
					break;
				case "broadcast_client":
					global $server;
					$rdata = $mData['data'];
					$sPageId = $sRecipientId = $rdata['recipient']['id'];
					$sSenderId = $rdata['sender']['id'];
					$oAgent = self::$instance->init()->selectAgent($sPageId, $sSenderId);
					if($oAgent)
					{
						$server->broadCast2Clients($data,$oAgent->getSocketId());
					}
					break;
				case "chat":
					\Log::append($sefl->getLogName(),"Client post Message Data ".$data);
					\Log::append($sefl->getLogName(),"Delivery Message Data");
					\Log::append($sefl->getLogName(),$mData);
					if(isset($mData['receipt_id']) && isset($mData['page_id']))
					{
						$sPageId = $mData['page_id'];
						$sRecipientId = $mData['receipt_id'];
						$sSenderId = $mData['sender_id'];
						$oAgent = self::$instance->init()->selectAgent($sPageId, $sSenderId);
						if($oAgent)
						{
							$oAgent->addClient($sPageId, $sSenderId);
							\Log::append($sefl->getLogName(),"current select agent is ". $oAgent->getName());
							$oAgent->setCurrentClientId($sSenderId);
							$oAgent->setCurrentPageId($sPageId);
							$oAgent->setSenderId($sSenderId);
							\Log::append($sefl->getLogName(),"start broadcast message ". $oAgent->getName());
							$mBroadCastMessage = array("text" => $mData["message"]);
							\Log::append($sefl->getLogName(),$mBroadCastMessage);
							$response = $oAgent->deliveryMessage($mBroadCastMessage);
							var_dump($response,true);
						}
						else
						{
							\Log::write($sefl->getLogName(),"No agent found");
						}
					}
					break;
				
			}
			echo json_encode($mReturn);
			
		}
		/*
		
		*/
	}
}