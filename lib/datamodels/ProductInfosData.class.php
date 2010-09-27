<?php
class productupdater_ProductInfosData implements productupdater_DataModel 
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
		$headers['voLabel'] = 'voLabel';
		$headers['voPublicationstatus'] = 'voPublicationstatus';
		$headers['voDescription'] = 'voDescription';
		$headers['visual'] = 'visual';
	}
	
	/**
	 * @param catalog_persistentdocument_product $document
	 * @param array $values
	 * @return boolean
	 */
	function exportValues($document, &$values)
	{
		$values['voLabel'] = $document->getLabel();
		$values['voPublicationstatus'] = $document->getPublicationstatus();
		$values['voDescription'] = $document->getDescription();
		$img = $document->getVisual();
		if ($img && $img->isPublished())
		{
			$values['visual'] = LinkHelper::getDocumentUrl($img, $img->getLang(), MediaHelper::getFormatPropertiesByName('modules.catalog.frontoffice/detailproduct'));
		}
		else
		{
			$values['visual'] = '';
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
		return false;
	}
}