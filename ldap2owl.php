<?php
#
# ldap2owl.php - ldap to owl conversion tool
#
# $Id: ldap2owl.php 557 2009-03-27 23:30:26Z seebi $
#
# Copyright (C) 2005, Sebastian Dietzold http://sebastian.dietzold.de/
# 
# This file is Free Software.
# 
# It is licensed under the following three licenses as alternatives:
#   1. GNU Lesser General Public License (LGPL) V2.1 or any newer version
#   2. GNU General Public License (GPL) V2 or any newer version
#   3. Apache License, V2.0 or any newer version
# 
# You may not use this file except in compliance with at least one of
# the above three licenses.



define("RDFAPI_INCLUDE_DIR", "./api/");
include(RDFAPI_INCLUDE_DIR . "RdfAPI.php");

require './ldap2owl.hooks.inc.php';
require './ldap2owl.syslog.inc.php';
require './ldap2owl.base.inc.php';
require './ldap2owl.server.inc.php';
require './ldap2owl.schema.inc.php';
require './ldap2owl.lang.inc.php';

error_reporting(E_ERROR);

### ### ### ### ### ### ### ### ### ### ### ### ### ###
### config
###

# predefined namespaces
$NSpaces['xsd'] = 'http://www.w3.org/2001/XMLSchema#';
$NSpaces['rdf'] = 'http://www.w3.org/1999/02/22-rdf-syntax-ns#';
$NSpaces['rdfs'] = 'http://www.w3.org/2000/01/rdf-schema#';
$NSpaces['owl'] = 'http://www.w3.org/2002/07/owl#';
$NSpaces['xmls'] = 'http://www.w3.org/2001/XMLSchema#';
$NSpaces['dc'] = 'http://purl.org/dc/elements/1.1/';
$NSpaces['ldap'] = 'http://purl.org/net/ldap#';

### ### ### ### ### ### ### ### ### ### ### ### ### ###
### functions
###

################################################################################
# head of the html-output
################################################################################
function html_head() {
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en-EN" lang="en-EN">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
	<title>LDAP2OWL Conversion Tool</title>
	<link rel="meta" title="DOAP" type="application/rdf+xml" href="ldap2owl.rdf" />
	<meta name="description" content="Converts LDAP DIT and schema to RDF." />
	<link rel="meta" type="application/rdf+xml" title="FOAF" href="http://sebastian.dietzold.de/rdf/foaf.rdf" />
	<style type="text/css">
		table.conf th {text-align: right; font-weight:normal;}
		table.conf {display: none;}
		#ldapserver {display: block;}
		div.formError {color: red; padding: 1em;}
	</style>
	<link rel="stylesheet" href="http://sebastian.dietzold.de/wp/wp-content/themes/seebi/style.css" type="text/css" media="screen" />
	<link rel="stylesheet" href="http://sebastian.dietzold.de/wp/wp-content/themes/seebi/print.css" type="text/css" media="print" />
	<script language="JavaScript" type="text/javascript">
	<!--
	function inlineOutput() {
		document.getElementById('xmloutput').style.display='none';
	}
	function xmlOutput() {
		document.getElementById('xmloutput').style.display='block';
	}
	//-->
	</script>
	<!-- Google Analytics Tracking by Google Analyticator: http://cavemonkey50.com/code/google-analyticator/ -->
	<script src="http://www.google-analytics.com/urchin.js" type="text/javascript"></script><script type="text/javascript"> 
_uacct="UA-642218-1";  urchinTracker(); </script>
</head>
<?php
if ((!isset($_GET['outputtype']))||($_GET['outputtype']=="")) $_GET['outputtype'] = "inline";
if (isset($_GET['modeltype'])) echo "<body onload=\"" .$_GET['modeltype']. "Conf();" .$_GET['outputtype']. "Output();\">";
else echo "<body>\n";
?>
<div id="page">
<div id="header"><a href="ldap2owl.php">LDAP2OWL Conversion Tool</a></div>
<hr />
	<div id="content" class="fullcolumn">
	<p>LDAP2OWL converts a directory information tree (DIT) complete with schema information (which will translated into an OWL ontology) and directory objects.</p>
<?php
}

################################################################################
# footer of the html-output
################################################################################
function html_footer() {
?>
</div>
<div id="footer">
	<p>
		Copyright © 2003, 2004, 2005, 2006
		<span class="author vcard">
		<a class="url n" href="http://sebastian.dietzold.de">
				<span class="given-name">Sebastian</span>
				<span class="family-name">Dietzold</span>
			</a>,
			<span class="adr">
				<span class="street-address">P.O.Box <span class="post-office-box">100 541</span></span>,
				<span class="postal-code">04005</span>

				<span class="locality">Leipzig</span>,
				<span class="country-name">Germany</span>
			</span>
		</span>.
	</p>
</div>
</div>
$Id: ldap2owl.php 557 2009-03-27 23:30:26Z seebi $
</body>
</html>
<?php
exit;
}

################################################################################
# footer of the html-output and an error message
################################################################################
function errorAndOut($string) {
	html_head();
	echo "<div class='formError'>$string</div>\n";
	html_form();
	html_footer();
}

################################################################################
# the html-form
################################################################################
function html_form() {
	global $NSpaces;
?>
<form method="get" action="" enctype="application/x-www-form-urlencoded">
<fieldset>
<legend><b>LDAP Connection Information:</b></legend>
<table class="conf" id="ldapserver">
<tr>
	<th><label for="ldaphost">Hostname</label>:</th>
	<td><input style="width: 30em" type="text" name="ldaphost" value="<?php ifPrint($_GET['ldaphost']) ?>"/></td>
</tr>
<tr>
	<th><label for="ldapuser">UserDN</label> / <label for="ldappassword">Pass</label>:</th>
	<td><input style="width: 20em" type="text" name="ldapuser" value="<?php ifPrint($_GET['ldapuser']) ?>"/><input style="width: 10em" type="text" name="ldappassword" value="<?php ifPrint($_GET['ldappassword']) ?>"/></td>
</tr>
<tr>
	<th><label for="ldapbase">BaseDN</label>:</th>
	<td><input style="width: 30em" type="text" name="ldapbase" value="<?php ifPrint($_GET['ldapbase']) ?>"/></td>
</tr>
</table>
</fieldset>

<fieldset><legend><b>Query String:</b></legend>
<input style="width: 30em;" type="text" name="query" value="<?php ifPrint($_GET['query']) ?>"/>
</fieldset>

<fieldset><legend><b>Usage Notes:</b></legend>
<ul class="notes">
<li>The password will be visible in the request-url. Maybe you use a read-only account?</li>
<li>Leave the UserDN blank to connect anonymous.</li>
<li>The script will use <a href="http://purl.org/net/ldap"><code>http://purl.org/net/ldap</code></a> if there is no readable schema-information on the server.</li>
<li>An empty query will be expanded to <code>(objectClass=*)</code>.</li>
<li>You can use one of these Examples for testing: <a href="?ldaphost=x500.bund.de&amp;ldapbase=o%3DBund%2Cc%3DDE&amp;query=%28objectClass%3DorganizationalUnit%29">German Government</a>, <a href="?ldaphost=directory.upenn.edu&amp;ldapbase=dc%3Dupenn%2Cdc%3Dedu&amp;query=%28%26%28sn%3DAuer%29%28givenname%3DSoren%29%29">Soren Auer</a></li>
</ul>
</fieldset>

<!--fieldset>
<legend><b>Output:</b>
<input onClick="inlineOutput();"
<?php if ($_GET['outputtype']=="inline") echo "checked "; ?>
type="radio" name="outputtype" value="inline"><label for="modeltype">Inline</label>
<input onClick="xmlOutput();"
<?php if ($_GET['outputtype']=="xml") echo "checked "; ?>
type="radio" name="outputtype" value="xml"><label for="modeltype">XML</label>
</legend>

<table class="conf" id="xmloutput">
<tr>
	<th><label for="xslturl">Stylesheet</label>:</th>
	<td><input style="width: 400px" type="text" name="xslturl" value="<?php ifPrint($_GET['xslturl']) ?>"/></td>
</tr>
</table>

</fieldset-->

<fieldset><legend><b>Start / Clear:</b></legend>
<input type="submit" name="button" value="Run Query" />
<a href="?">Clear Form</a>
</fieldset>
</form>
<?php
}

################################################################################
# check if form content is ok
################################################################################
function isFormOk() {
	global $_GET, $NSpaces;
	if ((!isset($_GET['ldaphost']))||($_GET['ldaphost']==""))
		errorAndOut("Please type in a ldap-host.");

	$i=1;
	$servers[$i]['name'] = $_GET['ldaphost'];
	$servers[$i]['host'] = $_GET['ldaphost'];
	$servers[$i]['base'] = $_GET['ldapbase'];
	$servers[$i]['port'] = 389;
	$servers[$i]['auth_type'] = 'config';

	if ((isset($_GET['ldapuser']))&&($_GET['ldapuser']!="")) {
		$servers[$i]['login_dn'] = $_GET['ldapuser'];
		if (!isset($_GET['ldappassword']))
			errorAndOut("Cant use a user without pass. please type in pass too.");
		else
			$servers[$i]['login_pass'] = $_GET['ldappassword'];
	}
	else {
		$servers[$i]['login_dn'] = null;
		$servers[$i]['login_pass'] = null;
	}

	$servers[$i]['tls'] = false;
	$servers[$i]['low_bandwidth'] = false;
	$servers[$i]['default_hash'] = 'crypt';
	$servers[$i]['login_attr'] = 'dn';
	$servers[$i]['login_class'] = '';
	$servers[$i]['read_only'] = true;
	$servers[$i]['show_create'] = true;
	$servers[$i]['enable_auto_uid_numbers'] = false;
	$servers[$i]['auto_uid_number_mechanism'] = 'search';
	$servers[$i]['auto_uid_number_search_base'] = 'ou=People,dc=example,dc=com';
	$servers[$i]['auto_uid_number_min'] = 1000;
	$servers[$i]['auto_uid_number_uid_pool_dn'] = 'cn=uidPool,dc=example,dc=com';
	$servers[$i]['unique_attrs_dn'] = '';
	$servers[$i]['unique_attrs_dn_pass'] = '';

	return $servers;
}

################################################################################
# checks the given server connection
################################################################################
function isServerOk() {
	global $servers;
	$server_id = 1;
	$ldapserver = new LDAPServer($server_id);
	#$dn = $ldapserver->getSchemaDN();
	#if ($dn == "")
	#	errorAndOut("Cant see the Schema DN (maybe wrong server credentials?).");

	return $ldapserver;
}

################################################################################
# print var if set
################################################################################
function ifPrint($var) {
	if ((isset($var))&&($var!="")) echo $var;
}

################################################################################
# creates ressource node from uri or abbrev-uri
################################################################################
function sdrdf_r($uri, $ns=TRUE) {
	global $world, $NSpaces;

	if ($ns)
		foreach ($NSpaces as $key => $value )
			$uri = ereg_replace ("^$key:", $value, $uri);

	return new Resource ($uri); 
	#return librdf_new_node_from_uri($world, librdf_new_uri($world, $uri));
}

################################################################################
# creates typed literal node from string, lang and type-uri or abbrev-uri
################################################################################
function sdrdf_tl($string, $lang, $uri) {
	global $world, $NSpaces;
	foreach ($NSpaces as $key => $value )
		$uri = ereg_replace ("^$key:", $value, $uri);

	$Literal = new Literal($string); 
	$Literal->setDatatype($uri);
	$Literal->setLanguage($lang);

	return $Literal;
	#return librdf_new_node_from_typed_literal($world, $string, $lang, librdf_new_uri($world, $uri));
}

################################################################################
# show_array($array)
#
# This function will print all the keys of a multidimensional array in html
# tables. It will help to debug when you don´t have control of depths.
################################################################################

function show_array($array) {
	echo "<table width='100%' border='1' bordercolor='#6699CC' cellspacing='0' cellpadding='5'><tr valign='top'>";
	foreach ($array as $key => $value ) {
		echo "<td align='center' bgcolor='#EEEEEE'>" .
			"<table border='2' cellpadding='3'><tr><td bgcolor='#FFFFFF'>" .
			"$key (<code style='white-space:pre;'>$value</code>)</td></tr></table>";

		if (is_array($array[$key])) show_array ($array[$key]);
		echo "</td>";
	}
	echo "</tr></table>";
}

################################################################################
# readAllSubClasses($classID)
#
# reads recursively all subclasses
################################################################################

function readAllSubClasses($classID, $schema_object_classes) {
	#echo "start $classID<br />";
	$classID = strtolower($classID);
	if ($classID == 'top')
		$return = array('top' => TRUE);
	else
	{
		$schemaClass = $schema_object_classes[$classID];
		$subClassArray = $schemaClass->getSupClasses();
		if ($subClassArray == array('top'))
			$return = array('top' => TRUE);
		else
			foreach ($subClassArray as $newClassID )
			{
				$return[strtolower($newClassID)] = TRUE;
				$return = array_merge(readAllSubClasses($newClassID, $schema_object_classes), $return);
			}
	}
	#show_array($return);
	return $return;
}

function printr($s) {
		print '<pre>';
print_r($s);
print '</pre>';
}

################################################################################
# addTranslatedAttribute($currentAttribute, $schema_attrs, $model1)
#
# translates a specific attribute type to OWL
################################################################################

function addTranslatedAttribute($currentAttribute, $schema_attrs, $model1) {
	#echo "start translate $currentAttribute<br>";
	if (!isset($schema_attrs[$currentAttribute]) or !is_object($schema_attrs[$currentAttribute])) {
		return $model1;
	}
	
	$attr = $schema_attrs[$currentAttribute];
	
	# Local names are used as part of the URI of the new property.
	#$localName = $attr->getName();
	#$uriAttr = sdrdf_r("#$localName");

	$localName = $currentAttribute;
	$uriAttr = sdrdf_r("ldap:".strtolower($currentAttribute));

	$supAttribute = $attr->getSupAttribute();
	if ($supAttribute)
		$syntaxOID = $schema_attrs[strtolower($supAttribute)]->getSyntaxOID();
	else
		$syntaxOID = $attr->getSyntaxOID();
	#echo $syntaxOID."-<br>";

	$description = $attr->getDescription();

	# The local name should also be used as the value of a rdfs:label property
	$model1->add(new Statement ($uriAttr, sdrdf_r("rdfs:label"),  sdrdf_tl($localName, "", "xsd:string")));

  # The description is used as rdfs:comment
	$model1->add(new Statement ($uriAttr, sdrdf_r("rdfs:comment"),  sdrdf_tl($description, "", "xsd:string")));

	# any other URI and the OID (in URN form) are mapped with owl:equivalentProperty to the first one.
	$oid = $attr->getOID();
	$model1->add(new Statement ($uriAttr, sdrdf_r("owl:equivalentProperty"),  sdrdf_r("urn:oid:$oid")));

	$aliases = $attr->getAliases();
	#show_array($aliases);
	 if (count($aliases) > 0)
	 	foreach( $aliases as $alias) {
			$model1->add(new Statement ($uriAttr, sdrdf_r("rdfs:label"),  sdrdf_tl($alias, "", "xsd:string")));
	 		$model1->add(new Statement ($uriAttr, sdrdf_r("owl:equivalentProperty"),  sdrdf_r(strtolower("ldap:$alias"))));
	 	}

	switch ($syntaxOID) {
		case "1.3.6.1.4.1.1466.115.121.1.12": # Distinguished Name
		case "1.3.6.1.4.1.1466.115.121.1.38": # OID
			# Every attribute with the syntax of an OID or a DN is created as an object property while all others become datatype properties
			$model1->add(new Statement ($uriAttr, sdrdf_r("rdf:type"),  sdrdf_r("owl:ObjectProperty")));
			# Object properties get as range the class top ...
			$model1->add(new Statement ($uriAttr, sdrdf_r("rdfs:range"),  sdrdf_r("ldap:top")));
			break;
		case "1.3.6.1.4.1.1466.115.121.1.4": # Audio
		case "1.3.6.1.4.1.1466.115.121.1.5": # Binary
		case "1.3.6.1.4.1.1466.115.121.1.28": # JPEG
			$model1->add(new Statement ($uriAttr, sdrdf_r("rdf:type"),  sdrdf_r("owl:DatatypeProperty")));
			$model1->add(new Statement ($uriAttr, sdrdf_r("rdfs:range"),  sdrdf_r("xsd:base64Binary")));
			break;
		case "1.3.6.1.4.1.1466.115.121.1.27": # Integer
			$model1->add(new Statement ($uriAttr, sdrdf_r("rdf:type"),  sdrdf_r("owl:DatatypeProperty")));
			$model1->add(new Statement ($uriAttr, sdrdf_r("rdfs:range"),  sdrdf_r("xsd:integer")));
			break;
		default:
			# default is datatype-prop with xsd:string
			$model1->add(new Statement ($uriAttr, sdrdf_r("rdf:type"),  sdrdf_r("owl:DatatypeProperty")));
			$model1->add(new Statement ($uriAttr, sdrdf_r("rdfs:range"),  sdrdf_r("xsd:string")));
	}

	# If there is a single value indicator in the schema definition, then a cardinality restriction ($\le 1$) for the class top must be created on this property.
	if ($attr->getIsSingleValue() == true)
	{
		$model1->add(new Statement ($uriAttr, sdrdf_r("rdfs:comment"),  sdrdf_tl("isSingleValued", "", "xsd:string")));
		$bNode = new BlankNode($model1);
		$model1->add(new Statement (sdrdf_r("ldap:top"),  sdrdf_r("rdfs:subClassOf"), $bNode));
		$model1->add(new Statement ($bNode, sdrdf_r("rdf:type"), sdrdf_r("owl:Restriction")));
		$model1->add(new Statement ($bNode, sdrdf_r("owl:onProperty"), $uriAttr));
		$model1->add(new Statement ($bNode, sdrdf_r("owl:maxCardinality"), sdrdf_tl("1", "", "xsd:integer")));
	}

	# The domain of the property will be generated with the owl:unionOf class constructor

	return $model1;
}


################################################################################
# addTranslatedClass($currentObjectClass, $model1)
#
# translates a specific object class to OWL
################################################################################

function addTranslatedClass($currentObjectClass, $model1) {
	#echo "start translate class $currentObjectClass<br>";
	global $schema_object_classes, $schema_attrs, $usedAttributes;
	$class = $schema_object_classes[$currentObjectClass];
	# The local name must used as part of the URI of the new OWL class.
	$localName = $class->getName();
#	$uriClass = $model1->createOntClass( "" . "$localName" ); 
	$uriClass = sdrdf_r("ldap:".strtolower($localName));
	$model1->add(new Statement ($uriClass, sdrdf_r("rdf:type"), sdrdf_r("owl:Class")));

	# Additionally, the OID (in URN form) is mapped with owl:equivalentClass to the class.
	$oid = $class->getOID();
	#$model1->add(new Statement ($uriClass, sdrdf_r("owl:equivalentClass"),  sdrdf_r("urn:oid:$oid")));

	# The local name should also be used as the value of a rdfs:label property.
	$model1->add(new Statement ($uriClass, sdrdf_r("rdfs:label"),  sdrdf_tl($localName, "en", "xsd:string")));

	# For every mandatory attribute of the object class a new cardinality restriction ($= 1$) on the OWL class has to be created.
	$mustAttrs = $class->getMustAttrNames();
	foreach( $mustAttrs as $mustAttr )
	{
		$mustAttr = strtolower($mustAttr);
		if (!$usedAttributes[$mustAttr]) {
			$mustAttr = strtolower($schema_attrs[$mustAttr]->getName());
			if (!$usedAttributes[$mustAttr])
				unset($mustAttr);
		}

		if ($mustAttr) {
			$bNode = new BlankNode($model1);
			$model1->add(new Statement ($uriClass,  sdrdf_r("rdfs:subClassOf"), $bNode));
			$model1->add(new Statement ($bNode, sdrdf_r("owl:onProperty"), sdrdf_r("ldap:$mustAttr")));
			$model1->add(new Statement ($bNode, sdrdf_r("owl:minCardinality"), sdrdf_tl("1", "en", "xsd:integer")));
			$model1->add(new Statement ($bNode, sdrdf_r("rdf:type"), sdrdf_r("owl:Restriction")));			
		}
	}


	# The new class has to be in rdfs:subClassOf-relation with its superclass(es).
	$superClasses = $class->getSupClasses();
	foreach( $superClasses as $superClass )
		$model1->add(new Statement ($uriClass, sdrdf_r("rdfs:subClassOf"),  sdrdf_r("ldap:".strtolower($superClass))));

	# top must be defined as a subclass of the generic LDAP object class ldap:object from \cite{dietzold-s-2005--c}.
	if ($currentObjectClass == "top")
		$model1->add(new Statement (sdrdf_r("ldap:top"),  sdrdf_r("rdfs:subClassOf"), sdrdf_r("ldap:object")));

	return $model1;
}

################################################################################
# addTranslatedEntry ($entry, $allSubClasses, $model1)
#
# translates a specific entry to OWL
################################################################################
function addTranslatedEntry ($entry, $allSubClasses, $model1) {
	#echo "start translate entry ".$entry['dn']."<br>";
	#show_array($entry);
	$uriEntry = dn2uri($entry['dn']);
	$rEntry = sdrdf_r($uriEntry, FALSE);

	# type of all classes and subclasses
	for($j=0; $j<count($entry['objectclass']); $j++) {
		$classID = strtolower($entry['objectclass'][$j]);
		$model1->add(new Statement ($rEntry,  sdrdf_r("rdf:type"), sdrdf_r("ldap:$classID")));
		foreach ($allSubClasses[$classID] as $subClassID => $TRUE)
			$model1->add(new Statement ($rEntry,  sdrdf_r("rdf:type"), sdrdf_r("ldap:".strtolower($subClassID))));
	}
	unset($entry['objectclass']); unset($entry['dn']);

	# for every attribute add every value
	#for ($j=0; $j<count$entry['count']; $j++) {
	foreach ($entry as $aName => $aValueA) {
		# split attripbute name and ignore attribute options (like cn;lang-en)
		$aNameA = explode ( ";", $aName);
		$aName = $aNameA[0];
		# is property an object property?
		if ($model1->findFirstMatchingStatement(sdrdf_r("ldap:$aName"), sdrdf_r("rdf:type"), sdrdf_r("owl:ObjectProperty"))) {
			foreach ($aValueA as $aValue) {
				$model1->add(new Statement ($rEntry, sdrdf_r("ldap:$aName"),  sdrdf_r(dn2uri($aValue), FALSE)));
			}
		# all non object property are datatype properties
		} else {
			# datatype of the literal is the datatype of the property
			$rangeStatement = $model1->findFirstMatchingStatement(sdrdf_r("ldap:$aName"), sdrdf_r("rdfs:range"), NULL);
			if ($rangeStatement) $dtUri = $rangeStatement->getLabelObject();
			else $dtUri = "xsd:string"; # xsd:string as default

			#for ($k=0; $k<$entry[$aName]['count']; $k++) {
			foreach ($aValueA as $aValue) {
				if ($dtUri == "http://www.w3.org/2001/XMLSchema#base64Binary")
					$aValue = base64_encode($aValue);
				$model1->add(new Statement ($rEntry, sdrdf_r("ldap:$aName"),  sdrdf_tl($aValue, "", $dtUri)));
			}
		}
	}
	#show_array($entry); exit;
	return $model1;
}

################################################################################
# addOntologyInfo ($model1)
################################################################################
function addOntologyInfo ($model1, $comment1) {
	$date = date("Y-m-d");
	$comment2 = "This RDF model was created with the ldap2owl tool from Sebastian Dietzold " .
			"at http://sebastian.dietzold.de/archive/2005/04/13/ldap2owl.php";
	$model1->add(new Statement (sdrdf_r(""), sdrdf_r("rdf:type"), sdrdf_r("owl:Ontology")));
	$model1->add(new Statement (sdrdf_r(""), sdrdf_r("owl:imports"), sdrdf_r("http://purl.org/net/ldap")));
	$model1->add(new Statement (sdrdf_r(""), sdrdf_r("rdfs:comment"),  sdrdf_tl($comment1, "en", "xsd:string")));
	$model1->add(new Statement (sdrdf_r(""), sdrdf_r("rdfs:comment"),  sdrdf_tl($comment2, "en", "xsd:string")));
	$model1->add(new Statement (sdrdf_r(""), sdrdf_r("dc:date"),  sdrdf_tl($date, "", "xsd:date")));
	$model1->add(new Statement (sdrdf_r(""), sdrdf_r("rdfs:seeAlso"), sdrdf_r("http://sebastian.dietzold.de/archive/2005/04/13/ldap2owl.rdf")));
	return $model1;
}

################################################################################
# dn2uri ($dn, $server)
# translates an LDAP DN to an LDAP URI according RFC2255 
################################################################################
function dn2uri ($dn) {
	global $servers;
	$server = $servers[1]['host'];
	$dnParts = array_reverse(split(",", $dn));
	for ($i = count($dnParts)-1; $i >= 0; $i--){
		$uri = $uri . trim($dnParts[$i]);
		if ($i != 0) $uri = $uri . ",";
	}
	$uri = "ldap://$server/" . urlencode($uri);
	$uri = str_replace ("%3D", "=", $uri);
	$uri = str_replace ("%2C", ",", $uri);
	$uri = str_replace ("%20", "+", $uri);
	return $uri;
}

### ### ### ### ### ### ### ### ### ### ### ### ### ###
### GO GO GO
###

# html stuff
if (!isset($_GET["button"])) {
	html_head();
	html_form();
	html_footer();
}

$servers = isFormOk(); $ldapserver = isServerOk();

# set namespace for
$NSpaces['host'] = 'ldap://'.$servers[$i]['host'].":".$servers[$i]['port']."/";

# create a new memmodel
$model1 = ModelFactory::getDefaultModel();

# do the LDAP search
$serverRessource = $ldapserver->connect();
#if (!$serverRessource) $ldapserver->connect(TRUE, TRUE);

if (trim($_GET['query'])!="") $searchquery = trim($_GET['query']);
else $searchquery = '(objectClass=*)';
$search = @ldap_search( $serverRessource, $servers[1]['base'], $searchquery );
if ($search == FALSE){
	pla_error( $lang['ldap_search_failed'], ldap_err2str( $serverRessource ), $serverRessource );
} else {
	$entries = array();
	$dns = array();
	//get the first entry identifier
	if( $entry_id = ldap_first_entry($ldapserver->connect(false),$search) )
		while($entry_id) { //iterate over the entries
			//get the distinguished name of the entry
			$dn = ldap_get_dn($ldapserver->connect(false),$entry_id);
			//get the attributes of the entry
			$attrs = ldap_get_attributes($ldapserver->connect(false),$entry_id);
			$entries[$dn]['dn'] = $dn;
			$URIs[dn2uri($dn)] = TRUE;
			//get the first attribute of the entry

			if($attr = ldap_first_attribute($ldapserver->connect(false),$entry_id,$attrs))
				//iterate over the attributes
				while($attr) {
					if( is_attr_binary($ldapserver,$attr))
						$values = ldap_get_values_len($ldapserver->connect(false),$entry_id,$attr);
					else
						$values = ldap_get_values($ldapserver->connect(false),$entry_id,$attr);

					unset($values['count']);
					$entries[$dn][strtolower($attr)] = $values;

					$attr = ldap_next_attribute($ldapserver->connect(false),$entry_id,$attrs);
				}// end while attr
			$entry_id = ldap_next_entry($ldapserver->connect(false),$entry_id);
		} // end while entry_id

	#	ksort( $entries );
	#show_array($entries);
}

# get LDAP schema
$schema_attrs = get_schema_attributes( $ldapserver, null, true );
$schema_object_classes = get_schema_objectclasses( $ldapserver, null, true );

if ($schema_attrs && $schema_object_classes) {
	# fetch all used objectClasses and attribute types
	$usedObjectClasses=array('top' => TRUE);
	$usedAttributes=array();
	foreach ($entries as $dn => $entry) {
		for ($j=0; $j<count($entry['objectclass']); $j++) {
			$classID = strtolower($entry['objectclass'][$j]);
			$usedObjectClasses[$classID] = TRUE;
			$allSubClasses[$classID] = readAllSubClasses($classID, $schema_object_classes);
			foreach ($allSubClasses[$classID] as $subClassID => $TRUE)
				$usedObjectClasses[strtolower($subClassID)] = TRUE;
		}

		# fetch the used attributeTypes
		foreach ($entry as $name => $value)
			$usedAttributes[strtolower($name)] = TRUE;
		unset($usedAttributes['dn']);
	}
	#show_array($usedObjectClasses);
	#show_array($usedAttributes);
	#show_array($allSubClasses);

	# translate the attributes
	foreach( $usedAttributes as $currentAttribute => $True)
		$model1 = addTranslatedAttribute($currentAttribute, $schema_attrs, $model1);

	# translate the objectclasses
	foreach( $usedObjectClasses as $currentObjectClass => $True )
		$model1 = addTranslatedClass($currentObjectClass, $model1);
}

# finally, translate the DIT entries and link the hierarchy
foreach($entries as $dn => $entry) {
	$model1 = addTranslatedEntry ($entry, $allSubClasses, $model1);
	$parentURI = dn2uri(get_container($dn));
	if ($URIs[$parentURI] == TRUE) {
		$model1->add(new Statement(
			sdrdf_r(dn2uri($dn), FALSE),
			sdrdf_r("ldap:hasDITParent"),
			sdrdf_r($parentURI, FALSE)));
		$model1->add(new Statement(
			sdrdf_r($parentURI, FALSE),
			sdrdf_r("ldap:hasDITChild"),
			sdrdf_r(dn2uri($dn), FALSE)));
	}
}

# add some ontology info and other statements
$comment = "This RDF model is a translated LDAP directory information " .
	"tree and its schema information. It was created from the LDAP server '" .
	$servers[1]['host'] ."' and the search query '$searchquery' from the " .
	"base DN '".$servers[1]['base']."'.";
$model1 = addOntologyInfo($model1, $comment);

# send header info and xml out
header("Content-type: application/xml");
header("Content-Disposition: attachment; filename=".$_GET['ldaphost'].".rdf");
echo $model1->writeRDFtoString();

?>
