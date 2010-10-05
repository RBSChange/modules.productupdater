<?php
/**
 * productupdater_ProductdataScriptDocumentElement
 * @package modules.productupdater.persistentdocument.import
 */
class productupdater_ProductdataScriptDocumentElement extends import_ScriptDocumentElement
{
    /**
     * @return productupdater_persistentdocument_productdata
     */
    protected function initPersistentDocument()
    {
    	return productupdater_ProductdataService::getInstance()->getNewDocumentInstance();
    }
    
    /**
	 * @return f_persistentdocument_PersistentDocumentModel
	 */
	protected function getDocumentModel()
	{
		return f_persistentdocument_PersistentDocumentModel::getInstanceFromDocumentModelName('modules_productupdater/productdata');
	}

	/**
	 * @return array
	 */
	protected function getDocumentProperties()
	{
		$properties = parent::getDocumentProperties();
		if (isset($properties['productQuery']))
		{
			$query = $this->replaceRefIdInString($properties['productQuery']) ;
			$properties['productQuery'] = $query;
		}
		return $properties;
	}
}