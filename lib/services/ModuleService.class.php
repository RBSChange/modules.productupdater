<?php
/**
 * @package modules.productupdater.lib.services
 */
class productupdater_ModuleService extends ModuleBaseService
{
	/**
	 * Singleton
	 * @var productupdater_ModuleService
	 */
	private static $instance = null;

	/**
	 * @return productupdater_ModuleService
	 */
	public static function getInstance()
	{
		if (is_null(self::$instance))
		{
			self::$instance = self::getServiceClassInstance(get_class());
		}
		return self::$instance;
	}
	
	/**
	 * @param Integer $documentId
	 * @return f_persistentdocument_PersistentTreeNode
	 */
//	public function getParentNodeForPermissions($documentId)
//	{
//		// Define this method to handle permissions on a virtual tree node. Example available in list module.
//	}

	
	private $formatterArray = array();
	
	/**
	 * @param string $name
	 * @return productupdater_Formatter
	 */
	public function getFormatterInstance($name)
	{
		if (!isset($this->formatterArray[$name]))
		{
			$class = "productupdater_" . ucfirst($name) . 'Formatter';
			$this->formatterArray[$name] = new $class();
		}
		return $this->formatterArray[$name];
	}
	
	private $dataModelArray = array();
	
	/**
	 * @param string $name
	 * @return productupdater_DataModel
	 */
	public function getDataModelInstance($name)
	{
		if (!isset($this->dataModelArray[$name]))
		{
			$class = "productupdater_" . ucfirst($name) . 'Data';
			$this->dataModelArray[$name] = new $class();
		}
		return $this->dataModelArray[$name];
	}	
}