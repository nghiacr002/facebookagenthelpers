<?php 
class Log
{
	public static function write($sFileName = "",$mContent,$sMode = "w")
	{
		if(empty($sFileName))
		{
			$sFileName = date("m-d-y").".txt";
		}
		$sFilePath = APP_PATH . "Log". APP_DS . $sFileName;
		
		if(is_object($mContent))
		{
			$sContent = var_export($mContent,true);
		}
		elseif(is_array($mContent))
		{
			$sContent = print_r($mContent, true);
		}
		else
		{
			$sContent = $mContent;
		}
		if($sMode == "a+")
		{
			$sContent = "\r\n--------".date("m-d-Y h:i:s")."-------\r\n". $sContent;
		}
		$oFile = @fopen($sFilePath,$sMode);
		@fwrite($oFile, $sContent);
		@fclose($oFile);
	}
	public static function append($sFileName,$mContent)
	{
		self::write($sFileName,$mContent,"a+");
	}
}