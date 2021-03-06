<?php

// ----------------------------------------------------------------------------------
// Class: Object
// ----------------------------------------------------------------------------------

/**
 * An abstract object.
 * Root object with some general methods, that should be overloaded. 
 * 
 * <BR><BR>
 * History:<UL>
 * <li>09-10-2002 : First version of this class.</li>
 * </UL>
 *
 *
 * @version  V0.9.3
 * @author Chris Bizer <chris@bizer.de>
 *
 * @abstract
 * @package utility
 *
 */
 class Object {

  /**
   * Serializes a object into a string
   *
   * @access	public
   * @return	string		
   */    
	function toString() {
    	$objectvars = get_object_vars($this);
		foreach($objectvars as $key => $value) 
			$content .= $key ."='". $value. "'; ";
		return "Instance of " . get_class($this) ."; Properties: ". $content;
	}

 }


?>