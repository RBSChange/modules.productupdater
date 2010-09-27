<?php
/**
 * productupdater_ProductdataService
 * @package modules.productupdater
 */
class productupdater_ProductdataService extends f_persistentdocument_DocumentService
{
	/**
	 * @var productupdater_ProductdataService
	 */
	private static $instance;

	/**
	 * @return productupdater_ProductdataService
	 */
	public static function getInstance()
	{
		if (self::$instance === null)
		{
			self::$instance = self::getServiceClassInstance(get_class());
		}
		return self::$instance;
	}

	/**
	 * @return productupdater_persistentdocument_productdata
	 */
	public function getNewDocumentInstance()
	{
		return $this->getNewDocumentInstanceByModelName('modules_productupdater/productdata');
	}

	/**
	 * Create a query based on 'modules_productupdater/productdata' model.
	 * Return document that are instance of modules_productupdater/productdata,
	 * including potential children.
	 * @return f_persistentdocument_criteria_Query
	 */
	public function createQuery()
	{
		return $this->pp->createQuery('modules_productupdater/productdata');
	}
	
	/**
	 * Create a query based on 'modules_productupdater/productdata' model.
	 * Only documents that are strictly instance of modules_productupdater/productdata
	 * (not children) will be retrieved
	 * @return f_persistentdocument_criteria_Query
	 */
	public function createStrictQuery()
	{
		return $this->pp->createQuery('modules_productupdater/productdata', false);
	}
	
	
	/**
	 * @param productupdater_persistentdocument_productdata $document
	 * @param string $forModuleName
	 * @param array $allowedSections
	 * @return array
	 */
	public function getResume($document, $forModuleName, $allowedSections = null)
	{
		$resume = parent::getResume($document, $forModuleName, $allowedSections);
		$resume['actions'] = array('filedata' => '(' . $document->getDatamodel() . ').' . $document->getFormat());
		$date = $document->getLastexportdate();
		if ($date)
		{
			$resume['actions']['lastexport'] = date_Converter::convertDateToLocal($date);
		}
		
		$date = $document->getLastimportdate();
		if ($date)
		{
			$resume['actions']['lastimport'] = date_Converter::convertDateToLocal($date);
		}		
		return $resume;
	}
	
	/**
	 * @param productupdater_persistentdocument_productdata $document
	 * @param Integer $parentNodeId Parent node ID where to save the document.
	 * @return void
	 */
	protected function preUpdate($document, $parentNodeId = null)
	{
		if ($document->isPropertyModified('datamodel') || $document->isPropertyModified('format'))
		{
			$document->setLastexportdate(null);
			$document->setLastimportdate(null);
			$document->setLastimportfileid(null);
		}
	}
	
	/**
	 * @param productupdater_persistentdocument_productdata $document
	 * @return string
	 */
	public function generateExport($document)
	{
		$formater = productupdater_ModuleService::getInstance()->getFormatterInstance($document->getFormat());
		$dataModels = $this->getDataModels($document);
		
		$filePath = f_util_FileUtils::getTmpFile('export' . $document->getId());
		$formater->create($filePath);
		$headers = array();
		foreach($dataModels as $dataModel) 
		{
			$dataModel->addHeaders($headers);
		}
		$formater->writeHeader($headers);
		
		$ids = $this->getProductIds($document);
		$rc = RequestContext::getInstance();
		foreach ($ids as $id) 
		{
			$product = null;
			try 
			{
				$product = DocumentHelper::getDocumentInstance($id);
				$rc->beginI18nWork($product->getLang());
				$ok = true;
				$values = array();
				foreach($dataModels as $dataModel) 
				{
					$ok = $ok && $dataModel->exportValues($product, $values);
				}
				if ($ok)
				{
					$formater->writeLine($values);
				}
				else
				{
					Framework::info(__METHOD__ . ' Ignore document ' . $product->__toString());
				}
				$rc->endI18nWork();
			}
			catch (Exception $e)
			{
				if ($product) {$rc->endI18nWork($e);}
				throw $e;
			}
		}
		
		$formater->writeFooter(array());
		$formater->close($filePath);
		
		$document->setLastexportdate(date_Calendar::getInstance());
		$document->save();
		return $filePath;
	}
	
	/**
	 * @param productupdater_persistentdocument_productdata $document
	 * @param string $tempPath
	 */
	public function import($document, $tempPath)
	{
		$formater = productupdater_ModuleService::getInstance()->getFormatterInstance($document->getFormat());
		$dataModels = $this->getDataModels($document);
		$headers = array();
		foreach($dataModels as $dataModel) 
		{
			$dataModel->addHeaders($headers);
		}
		$formater->open($tempPath);
		if ($formater->readHeader($headers))
		{
			$values = $formater->readLine();
			
			while ($values)
			{
				$product = null;
				foreach($dataModels as $dataModel) 
				{
					$dataModel->importValues($values, $product);
					if ($product === null) {break;}
				}
				
				if ($product instanceof catalog_persistentdocument_product && $product->isModified())
				{
					$product->save();
				}
				$values = $formater->readLine();
			}
			$formater->close();
			$document->setLastimportdate(date_Calendar::getInstance());
			$document->save();
		}
		else
		{
			$formater->close();
			throw new BaseException('Invalid-header', 'modules.productupdater.bo.dialogs.import.error.Invalid-header', array('headers' => implode(', ', $headers)));
		}
	}
	
	/**
	 * @param productupdater_persistentdocument_productdata $document
	 * @return integer[]
	 */	
	private function getProductIds($document)
	{
		$shop = $document->getShop();
		if ($shop === null)
		{
			return array();
		}
		$queryIntersection = f_persistentdocument_DocumentFilterService::getInstance()->getQueryIntersectionFromJson($document->getProductQuery());
		$query = catalog_ProductService::getInstance()->createQuery()->add(Restrictions::published());
		
		$query->createCriteria('compiledproduct')
			->add(Restrictions::eq('shopId', $shop->getId()))
			->add(Restrictions::published());
		
		$queryIntersection->add($query);
		return $queryIntersection->findIds();
	}
	
	/**
	 * @param productupdater_persistentdocument_productdata $document
	 * @return productupdater_DataModel[]
	 */
	private function getDataModels($document)
	{
		$dataModels = array(productupdater_ModuleService::getInstance()->getDataModelInstance('productKey'));
		foreach (explode(',', $document->getDatamodel()) as $value) 
		{
			$dm = productupdater_ModuleService::getInstance()->getDataModelInstance($value);
			$dm->setContext($document);
			$dataModels[] = $dm;
		}
		return $dataModels;
	}
	
	/**
	 * @param productupdater_persistentdocument_productdata $document
	 * @param Integer $parentNodeId Parent node ID where to save the document (optionnal => can be null !).
	 * @return void
	 */
//	protected function preSave($document, $parentNodeId = null)
//	{
//
//	}

	/**
	 * @param productupdater_persistentdocument_productdata $document
	 * @param Integer $parentNodeId Parent node ID where to save the document.
	 * @return void
	 */
//	protected function preInsert($document, $parentNodeId = null)
//	{
//	}

	/**
	 * @param productupdater_persistentdocument_productdata $document
	 * @param Integer $parentNodeId Parent node ID where to save the document.
	 * @return void
	 */
//	protected function postInsert($document, $parentNodeId = null)
//	{
//	}

	/**
	 * @param productupdater_persistentdocument_productdata $document
	 * @param Integer $parentNodeId Parent node ID where to save the document.
	 * @return void
	 */
//	protected function postUpdate($document, $parentNodeId = null)
//	{
//	}

	/**
	 * @param productupdater_persistentdocument_productdata $document
	 * @param Integer $parentNodeId Parent node ID where to save the document.
	 * @return void
	 */
//	protected function postSave($document, $parentNodeId = null)
//	{
//	}

	/**
	 * @param productupdater_persistentdocument_productdata $document
	 * @return void
	 */
//	protected function preDelete($document)
//	{
//	}

	/**
	 * @param productupdater_persistentdocument_productdata $document
	 * @return void
	 */
//	protected function preDeleteLocalized($document)
//	{
//	}

	/**
	 * @param productupdater_persistentdocument_productdata $document
	 * @return void
	 */
//	protected function postDelete($document)
//	{
//	}

	/**
	 * @param productupdater_persistentdocument_productdata $document
	 * @return void
	 */
//	protected function postDeleteLocalized($document)
//	{
//	}

	/**
	 * @param productupdater_persistentdocument_productdata $document
	 * @return boolean true if the document is publishable, false if it is not.
	 */
//	public function isPublishable($document)
//	{
//		$result = parent::isPublishable($document);
//		return $result;
//	}


	/**
	 * Methode à surcharger pour effectuer des post traitement apres le changement de status du document
	 * utiliser $document->getPublicationstatus() pour retrouver le nouveau status du document.
	 * @param productupdater_persistentdocument_productdata $document
	 * @param String $oldPublicationStatus
	 * @param array<"cause" => String, "modifiedPropertyNames" => array, "oldPropertyValues" => array> $params
	 * @return void
	 */
//	protected function publicationStatusChanged($document, $oldPublicationStatus, $params)
//	{
//	}

	/**
	 * Correction document is available via $args['correction'].
	 * @param f_persistentdocument_PersistentDocument $document
	 * @param Array<String=>mixed> $args
	 */
//	protected function onCorrectionActivated($document, $args)
//	{
//	}

	/**
	 * @param productupdater_persistentdocument_productdata $document
	 * @param String $tag
	 * @return void
	 */
//	public function tagAdded($document, $tag)
//	{
//	}

	/**
	 * @param productupdater_persistentdocument_productdata $document
	 * @param String $tag
	 * @return void
	 */
//	public function tagRemoved($document, $tag)
//	{
//	}

	/**
	 * @param productupdater_persistentdocument_productdata $fromDocument
	 * @param f_persistentdocument_PersistentDocument $toDocument
	 * @param String $tag
	 * @return void
	 */
//	public function tagMovedFrom($fromDocument, $toDocument, $tag)
//	{
//	}

	/**
	 * @param f_persistentdocument_PersistentDocument $fromDocument
	 * @param productupdater_persistentdocument_productdata $toDocument
	 * @param String $tag
	 * @return void
	 */
//	public function tagMovedTo($fromDocument, $toDocument, $tag)
//	{
//	}

	/**
	 * Called before the moveToOperation starts. The method is executed INSIDE a
	 * transaction.
	 *
	 * @param f_persistentdocument_PersistentDocument $document
	 * @param Integer $destId
	 */
//	protected function onMoveToStart($document, $destId)
//	{
//	}

	/**
	 * @param productupdater_persistentdocument_productdata $document
	 * @param Integer $destId
	 * @return void
	 */
//	protected function onDocumentMoved($document, $destId)
//	{
//	}

	/**
	 * this method is call before saving the duplicate document.
	 * If this method not override in the document service, the document isn't duplicable.
	 * An IllegalOperationException is so launched.
	 *
	 * @param productupdater_persistentdocument_productdata $newDocument
	 * @param productupdater_persistentdocument_productdata $originalDocument
	 * @param Integer $parentNodeId
	 *
	 * @throws IllegalOperationException
	 */
//	protected function preDuplicate($newDocument, $originalDocument, $parentNodeId)
//	{
//		throw new IllegalOperationException('This document cannot be duplicated.');
//	}

	/**
	 * this method is call after saving the duplicate document.
	 * $newDocument has an id affected.
	 * Traitment of the children of $originalDocument.
	 *
	 * @param productupdater_persistentdocument_productdata $newDocument
	 * @param productupdater_persistentdocument_productdata $originalDocument
	 * @param Integer $parentNodeId
	 *
	 * @throws IllegalOperationException
	 */
//	protected function postDuplicate($newDocument, $originalDocument, $parentNodeId)
//	{
//	}

	/**
	 * Returns the URL of the document if has no URL Rewriting rule.
	 *
	 * @param productupdater_persistentdocument_productdata $document
	 * @param string $lang
	 * @param array $parameters
	 * @return string
	 */
//	public function generateUrl($document, $lang, $parameters)
//	{
//	}

	/**
	 * @param productupdater_persistentdocument_productdata $document
	 * @return integer | null
	 */
//	public function getWebsiteId($document)
//	{
//	}

	/**
	 * @param productupdater_persistentdocument_productdata $document
	 * @return website_persistentdocument_page | null
	 */
//	public function getDisplayPage($document)
//	{
//	}



	/**
	 * @param productupdater_persistentdocument_productdata $document
	 * @param string $bockName
	 * @return array with entries 'module' and 'template'. 
	 */
//	public function getSolrserachResultItemTemplate($document, $bockName)
//	{
//		return array('module' => 'productupdater', 'template' => 'Productupdater-Inc-ProductdataResultDetail');
//	}
}