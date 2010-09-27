<?php
class productupdater_ProductKeyData implements productupdater_DataModel 
{
	/**
	 * @var productupdater_persistentdocument_productdata
	 */
	private $productData;
	 
	/**
	 * @param productupdater_persistentdocument_productdata $productData
	 */
	function setContext($productData)
	{
		$this->productData = $productData;
	}
	
	/**
	 * @param array $headers
	 */
	function addHeaders(&$headers)
	{
		$headers['reference'] = 'reference';
	}
	
	/**
	 * @param catalog_persistentdocument_product $document
	 * @param array $values
	 * @return boolean
	 */
	function exportValues($document, &$values)
	{
		$reference = $document->getCodeReference();
		if (f_util_StringUtils::isNotEmpty($reference))
		{
			$values['reference'] = $reference;
			return true;
		}
		$values['reference'] = '';
		return false;	
	}
	
	/**
	 * @param array $values
	 * @param catalog_persistentdocument_product $document
	 * @return boolean
	 */
	function importValues($values, &$document)
	{
		if (isset($values['reference']))
		{
			$array = catalog_ProductService::getInstance()->getByCodeReference($values['reference']);
			if (count($array) === 1)
			{
				$document = $array[0];
				return true;
			}
		}
		$document = null;
		return false;
	}
}