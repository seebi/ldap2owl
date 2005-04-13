<?
// ---------------------------------------------
// class: SparqlParserExecption
// ---------------------------------------------
/**
* A SPARQL Parser Execption for better errorhandling.
*
* <BR><BR>History:<UL>
* <LI>08.09.2005: Initial version</LI>
*
* @author   Tobias Gauss <tobias.gauss@web.de>
* @version	 0.9.3
*
* @package sparql
*/
Class SparqlParserException extends Exception{

	private $tokenPointer;

	public function __construct($message, $code = 0, $pointer){

		$this->tokenPointer = $pointer;
		parent::__construct($message, $code);
	}

	/**
	* Returns a pointer to the token which caused the exception.
	* @return int
	*/
	public function getPointer(){
		return $this->tokenPointer;
	}

}
?>