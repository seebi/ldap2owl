<?PHP
// ----------------------------------------------------------------------------------
// Class: ResLiteral
// ----------------------------------------------------------------------------------

/**
* An RDF literal.
* The literal supports the xml:lang and rdf:datatype property.
* For XML datatypes see: http://www.w3.org/TR/xmlschema-2/
* 
*
* <BR><BR>History:<UL>
* <LI>10-01-2004                : First version of this class.</LI>
*
* @version  V0.9.3
* @author Daniel Westphal <mail at d-westphal dot de>

*
* @package resModel
* @access	public
**/

class ResLiteral extends Literal 
{
	/**
	* Holds a reference to the associated model
	* @var		ResModel
	* @access	private
	*/
	var $model;
	
	
	/**
    * Constructor
	* You have to supply a string.
    *
	* @param	string	$str		label of the literal
	* @param 	string $language	optional language identifier
    */	
	function ResLiteral($str,$language = null)
	{
		parent::Literal($str,$language);

	}
	
	/**
    * Sets the reference to the assocoated model.
    *
	* @param	object model	$model
	* @access public
    */
	function setAssociatedModel(& $model)
	{
		$this->model=& $model;
	}
	
	/**
    * Get the reference to the assocoated model.
    *
	* @return	object model	$model
	* @access public
    */
	function & getAssociatedModel()
	{
		return $this->model;
	}

} 
?>