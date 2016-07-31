<?php 
/**
 * Class to handle all of request from WEBHOOK and forward to socket server.
 */
namespace Facebook;
class Request
{
	private $_aRequestHeaders = array();
	private $_aSegments = array();
	private $_aParams = array();
	private $_sMethod = "GET";
	public function __construct()
	{
		$this->_sMethod =  isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';
		$this->_aSegments = $this->getSegments();
		$this->_getParams();
	}
	public function getMethod()
	{
		return $this->_sMethod;
	}
	public function get($mKey)
	{
		return isset($this->_aParams[$mKey])?$this->_aParams[$mKey]:null;
	}
	public function setParam($mKey, $mValue)
	{
		$this->_aParams[$mKey] = $mValue;
		return $this;
	}
	public function setParams($aParams)
	{
		$this->_aParams = array_merge($this->_aParams,$aParams);
		return $this;
	}
	public function getParams()
	{
		return $this->_aParams;
	}
	public function seg($index)
	{
		return isset($this->_aSegments[$index])?$this->_aSegments[$index]:null;
	}
	public function getSegments()
	{
		if(isset($_SERVER['REQUEST_URI']))
		{
			$aSegments = explode('/', trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/'));
			return $aSegments;
		}
		return array();
	}
	protected function _getParams()
	{
		$this->_aParams = array_merge($_GET, $_POST, $_FILES);
	
		$sContent = file_get_contents("php://input");
		$aContentJSON = json_decode($sContent,true);
		if($sContent && $aContentJSON)
		{
			$this->_aParams = array_merge($this->_aParams,$aContentJSON);
		}
		return $this->_aParams;
	}
}