<?php 
namespace Facebook\Agent;
use Facebook\Bot;
class Agent
{
	protected $_sName = "Agent";
	protected $_sPageId;
	protected $_sClientId;
	protected $_sAgentId;
	protected $_sSenderId;
	protected $_sSocketId;
	protected $_sSocketName;
	const ONLINE = 1;
	const OFFLINE = 0;
	protected $_aServedClients = array();
	
	public function __construct($sAgentId)
	{
		$this->_sAgentId = $sAgentId;
		$this->init();
	}
	public function init()
	{
		$this->_aServedClients = $this->getClients();
		list($this->_sSocketId,$this->_sSocketName) = $this->getSocketInfo();
	}
	public function getSocketId()
	{
		return $this->_sSocketId;
	}
	public function getDataFile()
	{
		$sClientHash = $this->getHashName();
		$sRealFile = APP_PATH."Data". APP_DS . $sClientHash.'.php';
		return $sRealFile;
	}
	public function register($client_socket)
	{
		$aCacheData = array(
			"clients" => array(),
			"socket" => array(
				"id" => $client_socket->getId(),
				"socket" => $client_socket->getSocket(),
			),
		);
		$sRealFile = $this->getDataFile();
		$sContent= "<?php \$aCacheData = ".var_export($aCacheData,true)."; \n?>";
		$oFile = @fopen($sRealFile,'w+');
		@fwrite($oFile, $sContent);
		@fclose($oFile);
		return true;
	}
	public function setSenderId($id)
	{
		$this->_sSenderId = $id;
		return $this;
	}
	public function getSenderId()
	{
		return $this->_sSenderId;
	}
	public function getLogNameFile()
	{
		return $this->getName().".txt";
	}
	public function getName()
	{
		return $this->_sName."_".$this->getId();
	}
	public function getId()
	{
		return $this->_sAgentId;
	}
	public function setCurrentClientId($sClientId)
	{
		$this->_sClientId = $sClientId;
		return $this;
	}
	public function setCurrentPageId($sPageId)
	{
		$this->_sPageId = $sPageId;
		return $this;
	}
	public function getClientId()
	{
		return $this->_sClientId;
	}
	public function getPageId()
	{
		return $this->_sPageId;
	}
	public function reply()
	{
		return false;
	}
	public function isAvalible($sPageId, $sClientId)
	{
		\Log::append(\Facebook\Bot::$instance->getLogName(), $this);
		if(isset($this->_aServedClients[$sClientId]) && $this->_aServedClients[$sClientId] == $sPageId)
		{
			return true;
		}
		if(count($this->_aServedClients) >= Bot::MAX_CLIENT)
		{
			return false;
		}
		\Log::append("client-access.txt","is online ".$this->getName()."  ".$sPageId. " ". $sClientId);
		return true;
	}
	private function getHashName()
	{
		$sName = "Agent_".$this->_sAgentId;
		return $sName;
	}
	public function getSocketInfo()
	{
		$sRealFile = $this->getDataFile();
		if (file_exists($sRealFile))
		{
			require($sRealFile);
			if(isset($aCacheData['clients']))
			{
				return array($aCacheData['socket']['id'],$aCacheData['socket']['socket']);
			}
		}
		return array(null,null);
	}
	public function getClients()
	{
		\Log::append(\Facebook\Bot::$instance->getLogName(), "try to get clients list");
		$sRealFile = $this->getDataFile();
		if (file_exists($sRealFile))
		{
			require($sRealFile);
			if(isset($aCacheData['clients']))
			{
				return $aCacheData['clients'];
			}
			else
			{
				return null;
			}
		}
		return null;
	}
	public function addClient($sPageId, $sClientId)
	{
		$this->_aServedClients[$sClientId] = $sPageId;
		$aCacheData = array(
				"clients" => $this->_aServedClients,
				"socket" => array(
						"id" => $this->_sSocketId,
						"socket" => $this->_sSocketName,
				),
		);
		$sRealFile = $this->getDataFile();
		$sContent= "<?php \$aCacheData = ".var_export($aCacheData,true)."; \n?>";
		$oFile = @fopen($sRealFile,'w+');
		@fwrite($oFile, $sContent);
		@fclose($oFile);
		return true;
	
	}
	public function broadCast($aEventMessage)
	{
		return false;
	}
	public function receivedAuthentication($aEventMessage)
	{
		return false;
	}
	public function receivedMessage($aEventMessage)
	{
		return false;
	}
	public function receivedDeliveryConfirmation($aEventMessage)
	{
		return false;
	}
	public function receivedPostback($aEventMessage)
	{
		return false;
	}
	public function receivedMessageRead($aEventMessage)
	{
		return false;
	}
	public function receivedAccountLink($aEventMessage)
	{
		return false;
	}
	public function postBack2Page($aData)
	{
		$aHeader = array(
				"Content-Type:application/json"
		);
		$curl = curl_init(FACEBOOK_ENDPOINT."?access_token=".PAGE_TOKEN);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_HTTPHEADER, $aHeader);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, false);
		curl_setopt($curl, CURLOPT_VERBOSE, 1);
			
		curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($aData));
		curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($curl, CURLOPT_USERAGENT, 'Agent BOT');
		$response = curl_exec($curl);
		$j_response = json_decode($response,true);
		if(!$j_response)
		{
			$j_response = $response;
		}
		return $j_response;
	}
}