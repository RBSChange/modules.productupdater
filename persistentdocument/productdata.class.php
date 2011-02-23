<?php
/**
 * Class where to put your custom methods for document productupdater_persistentdocument_productdata
 * @package modules.productupdater.persistentdocument
 */
class productupdater_persistentdocument_productdata extends productupdater_persistentdocument_productdatabase 
{
	/**
	 * @return productupdater_Formatter
	 */
	private function getFormatter()
	{
		return productupdater_ModuleService::getInstance()->getFormatterInstance($this->getFormat()); 
	}
	
	/**
	 * @return string;
	 */
	public function getContentType()
	{
		$this->getFormatter()->getContentType();
	}
	
	/**
	 * @return string;
	 */
	public function getExportFileName()
	{
		return f_util_FileUtils::cleanFilename($this->getLabel() . $this->getFormatter()->getFileExtension());
	}
}