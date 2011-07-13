<?php
class productupdater_StockData implements productupdater_DataModel 
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
		$headers['stocklevel'] = 'stocklevel';
		$headers['stockquantity'] = 'stockquantity';
		$headers['stockalertthreshold'] = 'stockalertthreshold';
	}
	
	/**
	 * @param catalog_persistentdocument_product $document
	 * @param array $values
	 * @return boolean
	 */
	function exportValues($document, &$values)
	{
		$stDoc = catalog_StockService::getInstance()->getStockableDocument($document);
		if ($stDoc !== null) 
		{
			$values['stocklevel'] = $stDoc->getCurrentStockLevel();
			$values['stockquantity'] = $document->getCurrentStockQuantity();
			if ($stDoc === $document)
			{
				$values['stockalertthreshold'] = $document->getStockAlertThreshold();
			}
			else
			{
				$values['stockalertthreshold'] = '';
			}
		}
		else
		{
			$values['stocklevel'] = '';
			$values['stockquantity'] = '';
			$values['stockalertthreshold'] = '';
		}
		return true;
	}
	
	/**
	 * @param array $values
	 * @param catalog_persistentdocument_product $document
	 * @return boolean
	 */
	function importValues($values, &$document)
	{
		$stDoc = catalog_StockService::getInstance()->getStockableDocument($document);
		if ($document === $stDoc) 
		{
			$document->setStockQuantity($values['stockquantity']);
			$document->setStockLevel($values['stocklevel']);
			$document->setStockAlertThreshold($values['stockalertthreshold']);
			return true;		
		}
		return false;
	}
}