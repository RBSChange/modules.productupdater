<?php
interface productupdater_Formatter 
{
	/**
	 * @return string
	 */
	function getContentType();
	
	/**
	 * @return string
	 */
	function getFileExtension();
	
	/**
	 * @param string $filePath
	 */
	function create($filePath);
	
	/**
	 * @param array $array
	 */
	function writeHeader($array);
	
	/**
	 * @param array $array
	 */
	function writeLine($array);
	
	/**
	 * @param array $array
	 */
	function writeFooter($array);
	
	/**
	 * @return void
	 */
	function close();
	
	
	/**
	 * @param string $filePath
	 */
	function open($filePath);	
	
	/**
	 * @param array $expectedHeader
	 * @return boolean;
	 */
	function readHeader($expectedHeader);

	/**
	 * @return array
	 */
	function readLine();
	
	
}