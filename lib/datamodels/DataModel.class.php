<?php
interface productupdater_DataModel
{
	/**
	 * @param productupdater_persistentdocument_productdata $productData
	 */
	function setContext($productData);
	
	/**
	 * @param array $headers
	 */
	function addHeaders(&$headers);
	
	/**
	 * @param f_persistentdocument_PersistentDocument $document
	 * @param array $values;
	 * @return boolean
	 */
	function exportValues($document, &$values);
	
	/**
	 * @param array $values
	 * @param f_persistentdocument_PersistentDocument $document
	 * @return boolean
	 */
	function importValues($values, &$document);
}