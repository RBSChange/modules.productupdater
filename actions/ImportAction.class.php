<?php
/**
 * productupdater_ImportAction
 * @package modules.productupdater.actions
 */
class productupdater_ImportAction extends f_action_BaseJSONAction
{
	/**
	 * @param Context $context
	 * @param Request $request
	 */
	public function _execute($context, $request)
	{
		$document = $this->getProductData($request);
		if (!count($_FILES))
		{
			return $this->sendJSONError(f_Locale::translateUI('&modules.productupdater.bo.dialogs.import.error.No-file;'));
		}
		$formater = productupdater_ModuleService::getInstance()->getFormatterInstance($document->getFormat());
		$extension = $formater->getFileExtension();
		
		if ($_FILES['filename']['error'] != UPLOAD_ERR_OK || substr($_FILES['filename']['name'], - strlen($extension)) != $extension)
		{
			return $this->sendJSONError(f_Locale::translateUI('&modules.productupdater.bo.dialogs.import.error.Bad-file-type;'));
		}
		
		$tempPath = $_FILES['filename']['tmp_name'];
		

		$document->getDocumentService()->import($document, $tempPath);
		
		$this->logAction($document, array());
		
		return $this->sendJSON(array('message' => f_Locale::translateUI('&modules.productupdater.bo.dialogs.import.Success;')));
	}
	
	/**
	 * @param Request $request
	 * @return productupdater_persistentdocument_productdata
	 */
	private function getProductData($request)
	{
		return $this->getDocumentInstanceFromRequest($request);
	}
}