<?php
// ----------------------------------------------------------------------------------
// Class: Dataset
// ----------------------------------------------------------------------------------

/**
* Dataset implementation.
* Superclass of datasetMem and datasetDb which contains shared functionality.
*
* <BR><BR>History:<UL>
* <LI>05-02-2005                : First version of this class.</LI>
* <LI>01-05-2006                : Function sparqlQuery() added.</LI>
*
* @version  V0.9.3
* @author Daniel Westphal (http://www.d-westphal.de)
* @author Chris Bizer <chris@bizer.de>
*
* @package 	dataset
* @access	public
**/

class Dataset
{
	/**
    * Load a Dataset from File
    *
    * @param string 
	* @access	public
    */	
	function loadFromFile($file)
	{
		$parser= new TriXParser(&$this);
		$parser->parseFile($file);	
	}
	
	/**
    * Load a Datset from a string
    *
    * @param string 
	* @access	public
    */
	function loadFromString($string)
	{
		$parser= new TriXParser(&$this);
		$parser->parseString($string);		
	}
	
	/**
    * Serialize the Dataset to File
    *
    * @param  string
	* @access	public
    */	
	function serializeToFile($fileName)
	{
		$serializer= new TriXSerializer(&$this);
		$serializer->serializeToFile($fileName);	
	}
	
	/**
    * Serialize the Dataset to string
    *
    * @return string 
	* @access	public
    */
	function serializeToString()
	{
		$serializer= new TriXSerializer(&$this);
		return $serializer->serializeToString();		
	}
	
 /**
 * Performs a SPARQL query against an RDF Dataset. 
 * The result can be retrived in SPARQL Query Results XML Format or
 * as an array containing the variables an their bindings.
 *
 * @param  String $query      the sparql query string
 * @param  String $resultform the result form ('xml' for SPARQL Query Results XML Format)
 * @return String/array       
 */
 function sparqlQuery($query,$resultform = false){
 	include_once(RDFAPI_INCLUDE_DIR.PACKAGE_SPARQL);
 	$parser = new SparqlParser();
 	$q = $parser->parse($query);
 	
 	$eng = new SparqlEngine();
 	return $eng->queryModel($this,$q,$resultform);
 }
 
	
	
}
?>