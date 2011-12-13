<?php
class productupdater_PriceData implements productupdater_DataModel 
{
	/**
	 * @var productupdater_persistentdocument_productdata
	 */
	private $productData;
	
	private $keys = array('valueWithTax', 'oldValueWithTax',  'taxCode',  'valueWithoutTax', 'oldValueWithoutTax', 'taxCategory', 'ecoTax');
	 
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
		$headers['valueWithTax'] = 'valueWithTax (RO)';
		$headers['oldValueWithTax'] = 'oldValueWithTax (RO)';
		$headers['taxCode'] = 'taxCode (RO)';
		$headers['valueWithoutTax'] = 'valueWithoutTax';
		$headers['oldValueWithoutTax'] = 'oldValueWithoutTax';
		$headers['taxCategory'] = 'taxCategory';
		$headers['ecoTax'] = 'ecoTax';
	}
	
	/**
	 * @param catalog_persistentdocument_product $document
	 * @param array $values
	 * @return boolean
	 */
	function exportValues($document, &$values)
	{
		$price = $document->getPrice($this->productData->getShop(), null);
		if ($price instanceof catalog_persistentdocument_price && !$price->isNew()) 
		{
			$values['valueWithTax'] = $price->getValueWithTax();
			$values['oldValueWithTax'] = $price->getOldValueWithTax();
			$values['taxCode'] = $price->getTaxCode();
			$values['valueWithoutTax'] = $price->getValueWithoutTax();
			$values['oldValueWithoutTax'] = $price->getOldValueWithoutTax();
			$values['taxCategory'] = $price->getTaxCategory();
			$values['ecoTax'] = $price->getEcoTax();
		}
		else
		{
			$values['valueWithTax'] = '';
			$values['oldValueWithTax'] = '';
			$values['taxCode'] = '';
			$values['valueWithoutTax'] = '';
			$values['oldValueWithoutTax'] = '';
			$values['taxCategory'] = '';
			$values['ecoTax'] = '';
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
		if ($document instanceof catalog_persistentdocument_product) 
		{
			$price = $document->getPrice($this->productData->getShop(), null);
			if ($price instanceof catalog_persistentdocument_price && !$price->isNew()) 
			{				
				$price->setTaxCategory($values['taxCategory'] == '' ? null : $values['taxCategory']);			
				$val = doubleval($values['valueWithoutTax']);
				$price->setValueWithoutTax($val > 0 ? $val : null);	
				$val = doubleval($values['oldValueWithoutTax']);
				$price->setOldValueWithoutTax($val > 0 ? $val : null);
				
				$price->setEcoTax($values['ecoTax'] == '' ? null : doubleval($values['ecoTax']));
				if ($price->isModified())
				{
					$price->save();
				}
				return true;
			}					
		}
		return false;
	}
}