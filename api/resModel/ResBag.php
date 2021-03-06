<?PHP
// ----------------------------------------------------------------------------------
// Class: ResBag
// ----------------------------------------------------------------------------------

/**
* This interface defines methods for accessing RDF Bag resources. 
* These methods operate on the RDF statements contained in a model.
*
* <BR><BR>History:<UL>
* <LI>10-01-2004                : First version of this class.</LI>
*
* @version  V0.9.3
* @author Daniel Westphal <mail at d-westphal dot de>
*
* @package 	resModel
* @access	public
**/
class ResBag extends ResContainer 
{
	
	/**
    * Constructor
	* You can supply a URI
    *
    * @param string $uri 
	* @access	public
    */		
	function ResBag($uri = null)
	{
		parent::ResContainer($uri);
		$this->containerType=new ResResource(RDF_NAMESPACE_URI.RDF_BAG);
	}
}
?>