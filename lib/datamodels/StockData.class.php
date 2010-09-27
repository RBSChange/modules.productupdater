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
	 * @param catalog_StockableDocument $document
	 * @param array $values
	 * @return boolean
	 */
	function exportValues($document, &$values)
	{
		if ($document instanceof catalog_StockableDocument) 
		{
			$values['stocklevel'] = $document->getStockLevel();
			$values['stockquantity'] = $document->getStockQuantity();
			if (f_util_ClassUtils::methodExists($document, 'getStockAlertThreshold'))
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
		if ($document instanceof catalog_StockableDocument) 
		{
			if (f_util_ClassUtils::methodExists($document, 'setStockQuantity'))
			{
				$document->setStockQuantity($values['stockquantity']);
			}
			if (f_util_ClassUtils::methodExists($document, 'setStockLevel'))
			{
				$document->setStockLevel($values['stocklevel']);
			}	
			if (f_util_ClassUtils::methodExists($document, 'setStockAlertThreshold'))
			{
				$document->setStockAlertThreshold($values['stockalertthreshold']);
			}
			return true;		
		}
		return false;
	}
}