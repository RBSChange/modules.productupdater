<?php
class productupdater_XmlFormatter implements productupdater_Formatter 
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
		return '.xml';
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
		 fwrite($this->res, '<?xml version="1.0" encoding="utf-8"?>' . "\n");
		 fwrite($this->res, '<rows>' . "\n");
	}
	
	/**
	 * @param array $array
	 */
	function writeLine($array)
	{
		$memXmlWriter = new XMLWriter();
		$memXmlWriter->openMemory();
		$memXmlWriter->setIndent(true);
		$memXmlWriter->startElement('row');
		foreach ($array as $name => $value) 
		{
			$memXmlWriter->startElement($name);
			if ($value !== null)
			{
				$memXmlWriter->text($value);
			}
			$memXmlWriter->endElement();
			
		}
		$memXmlWriter->endElement();
		fwrite($this->res, $memXmlWriter->outputMemory(true));
		unset($memXmlWriter);
	}
	
	/**
	 * @param array $array
	 */
	function writeFooter($array)
	{
		fwrite($this->res, '</rows>');
	}
	
	/**
	 * @return void
	 */
	function close()
	{
		if ($this->res !== null)
		{
			fclose($this->res);
			$this->res = null;
		}
		if ($this->xmlReader !== null)
		{
			$this->xmlReader->close();
			$this->xmlReader = null;
		}
	}

	/**
	 * @var array
	 */
	private $keys;
	
	/**
	 * @var XMLReader
	 */
	private $xmlReader;
	
	/**
	 * @param string $filePath
	 */
	function open($filePath)
	{
		$this->xmlReader = new XMLReader();
		$this->xmlReader->open($filePath);
	}	
	
	/**
	 * @param array $expectedHeader
	 * @return boolean;
	 */
	function readHeader($expectedHeader)
	{
		$this->xmlReader->read();
		if ($this->xmlReader->nodeType == XMLReader::ELEMENT && $this->xmlReader->name == 'rows')
		{
			$this->keys = array_keys($expectedHeader);
			return true;
		}
		return false;	
	}

	/**
	 * @return array
	 */
	function readLine()
	{
		if ($this->findRow())
		{
			$result = array();
			$deep = 0;
			foreach ($this->keys as $name) {$result[$name] = null;}
			while ($this->xmlReader->read())
			{
				$nt = $this->xmlReader->nodeType;
				if ($deep === 0 && $nt == XMLReader::END_ELEMENT && $this->xmlReader->name == 'row')
				{
					break;
				}
				else if ($nt == XMLReader::ELEMENT && !$this->xmlReader->isEmptyElement)
				{
					$deep = 1;
					$name = $this->xmlReader->name;
					$value = '';
				}
				else if ($nt == XMLReader::END_ELEMENT)
				{
					$result[$name] = $value !== '' ? $value : null;
					$deep = 0;					
				}
				else if ($deep === 1 && in_array($nt, array(XMLReader::TEXT, XMLReader::CDATA, XMLReader::WHITESPACE, XMLReader::SIGNIFICANT_WHITESPACE)))
				{
               	 	$value .= $this->xmlReader->value;
				}
			}
			return $result;
		}
		return false;
	}
	
	private function findRow()
	{
		while($this->xmlReader->read())
		{
			if ($this->xmlReader->nodeType == XMLReader::ELEMENT && $this->xmlReader->name == 'row')
			{
				return true;
			}
		}
		return false;
	}
}