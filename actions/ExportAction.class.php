<?php
/**
 * productupdater_ExportAction
 * @package modules.productupdater.actions
 */
class productupdater_ExportAction extends change_Action
{
	/**
	 * @param change_Context $context
	 * @param change_Request $request
	 */
	public function _execute($context, $request)
	{
		$document = $this->getDocumentInstanceFromRequest($request);
		$resourcePath = $document->getDocumentService()->generateExport($document);
		$this->logAction($document, array());
		
		$headers = array();
		$headers[] = 'Cache-Control: public, must-revalidate';
		$headers[] = 'Pragma: hack';
		$headers[] = 'Content-Transfer-Encoding: binary';
		$headers[] = 'Last-Modified: ' . gmdate("D, d M Y H:i:s", filemtime($resourcePath)) . " GMT";
		$headers[] = 'Content-Length: ' . filesize($resourcePath);
		$headers[] = 'Content-Type: ' . $document->getContentType();			
		$headers[] = 'Content-Disposition: attachment; filename="' . $document->getExportFileName() . '"';
		foreach ($headers as $header)
		{
			header($header);
		}
		readfile($resourcePath);
		
		unlink($resourcePath);
		return change_View::NONE;
	}
}