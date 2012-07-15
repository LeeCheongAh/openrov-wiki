<?php
class UniRequest 
{ 
	public function getBool($name)
	{
		global $wgRequest;
		$output = $wgRequest->getBool($name);
		if(isset($output)) return $output;
			else return false;
	}

	public function getInt($name)
	{
		global $wgRequest;
		return $wgRequest->getInt($name);
	}

	public function getString($name)
	{
		global $wgRequest;
		return $wgRequest->getVal($name);
	}

	public function getArray($name)
	{
		global $wgRequest;
		return $wgRequest->getArray($name);
	}

	public function get($name)
	{
		global $wgRequest;
		return $wgRequest->getVal($name);
	}
}
?>