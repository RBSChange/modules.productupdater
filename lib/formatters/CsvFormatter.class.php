<?php
class productupdater_CsvFormatter implements productupdater_Formatter 
{
	/**
	 * @return string
	 */
	public function getContentType()
	{
		return 'text/csv';
	}	
	
	/**
	 * @return string
	 */
	public function getFileExtension()
	{
		return '.csv';
	}

	private $res;
	
	/**
	 * @param string $filePath
	 */
	function create($filePath)
	{
		$this->res = fopen($filePath, "w");
	}
	
	/**
	 * @param array $array
	 */
	function writeHeader($array)
	{
		 fputcsv($this->res, $array, ';', '"');
	}
	
	/**
	 * @param array $array
	 */
	function writeLine($array)
	{
		 fputcsv($this->res, $array, ';', '"');
	}
	
	/**
	 * @param array $array
	 */
	function writeFooter($array)
	{
		
	}
	
	/**
	 * @return void
	 */
	function close()
	{
		fclose($this->res);
	}
	
	private $keys;
	
	/**
	 * @param string $filePath
	 */
	function open($filePath)
	{
		$this->res = fopen($filePath, "r");
	}	
	
	/**
	 * @param array $expectedHeader
	 * @return boolean;
	 */
	function readHeader($expectedHeader)
	{
		$this->keys = array_keys($expectedHeader);
		$array = fgetcsv($this->res, 5000, ';', '"');
		if (!is_array($array) || count($array) < count($expectedHeader))
		{
			return false;
		}
		
		foreach (array_values($expectedHeader) as $index => $value) 
		{
			if ($array[$index] != $value)
			{
				return false;
			}
		}
		
		return true;	
	}

	/**
	 * @return array
	 */
	function readLine()
	{
		$array = fgetcsv($this->res, 20000, ';', '"');
		if (!is_array($array)) {return false;};
		$result = array();
		foreach ($this->keys as $index => $name) 
		{
			$result[$name] = (!isset($array[$index]) || $array[$index] === '') ? null : $array[$index];
		}
		return $result;
	}
}