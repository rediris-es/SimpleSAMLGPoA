<?php

// Copyright (c) 2008, RedIRIS. All Rights Reserved.
//
// You may distribute under the terms of the GNU General Public License,
// as specified in the LICENSE file that was shipped with this distribution

// 2008.13.13: Updated for latest SimpleSAMLphp from trunk
// 2008.09.10: Case insensitive attribute names.
// 2008.09.11: Checks for SIGNOFF action and redirects to InitSLO.

require_once "crypt.php";

$config        = array();
$attr_mapping  = array();
$config_params = array(    // Name => mandatory?
	'LogFile'        => true,
	'AuthServerId'   => true,
	'TTL'            => true,
	'PrivateKey'     => true,
	'KeyLen'         => true,
	'SSP-authsource' => true,
	'Debug'          => false,
);


// If not defined, we'll use the same name as this script, but ended in '.cfg.php'
//$cfgFile = "SimpleSAMLGPoA.cfg.php";

$mv_separator = '+';

get_config();

if (!isset($_REQUEST["ACTION"]) && !isset($_REQUEST["ATTREQ"]))
	error("Unknown request. Use the PAPI 1.0 protocol");

// Modified (JAAD-2008.09.11)
if (isset($_REQUEST["ACTION"]) && $_REQUEST["ACTION"] != "SIGNOFF" && ($_REQUEST["ACTION"] != "CHECK" || !isset($_REQUEST["DATA"]) || !isset($_REQUEST["URL"])))
	error("Unknown request. Use the PAPI 1.0 protocol");

if (isset($_REQUEST["ATTREQ"]) && (!isset($_REQUEST["PAPIPOAREF"]) || !isset($_REQUEST["PAPIPOAURL"])))
	error("Unknown request. Use the PAPI 1.0 protocol");

if (isset($_REQUEST["ACTION"])) {
	$theURL = $_REQUEST["URL"];
	$theRef = $_REQUEST["DATA"];
} else {
	$theURL = $_REQUEST["PAPIPOAURL"];
	$theRef = $_REQUEST["PAPIPOAREF"];
}


// Check for signoff request (JAAD-2008.09.11)
if (isset($_REQUEST["ACTION"]) && $_REQUEST["ACTION"] == "SIGNOFF") {

	if (isset ($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on')
		$protocol = 'https://';
	else
		$protocol = 'http://';

	// We need a better solution for that. Maybe using SSp stuff?
	$SLOpath = "/simplesaml/saml2/sp/initSLO.php" ;

	$redirectTo = $protocol.$_SERVER['SERVER_NAME'] . $SLOpath . "?RelayState=" . $theURL . "&ACTION=SIGNEDOFF" ;
	header("Location: $redirectTo");
	exit;
}

// Search for the PoA in the config. file
foreach ( array_keys( $config['PoA']) as $poadef) {
	if (preg_match("|".$poadef."|", $theURL)) {
		$reqPoA = $poadef;
		break;
	}
}

if ( !isset( $reqPoA))
	error("No matching PoA found for:" . $theURL);

get_config_poa( $reqPoA);

// PoA found, so we can now start with the SimpleSAML stuff
require_once('/var/simplesamlphp/lib/_autoload.php');

try {
	$ssp_as = new SimpleSAML_Auth_Simple( $config["SSP-authsource"]);
	$ssp_as->requireAuth();
	$attributes = $ssp_as->getAttributes();
} catch( Exception $e) {
	error( "simpleSAMLphp exception:" . $e->getMessage());
}

doLog( "Received attributes:");
foreach ($attributes as $key => $value)
	doLog("  $key => " . implode( "|", $value));

// Convert attribute keys to upper case (JAAD-2008.09.10)
$upattributes = array();
foreach ($attributes as $key => $value)
	$upattributes[strtoupper($key)] = $value;


// If attributes are right, we can start mapping them to requested configuration
$assertion = "";

foreach ($attr_mapping as $key => $value) {
	$fragmentValues = array();
	$vparts = explode( $mv_separator, $value);

	foreach( $vparts as $vpart) {
		// starts and ends with single or double quotes: literal
		if( preg_match("/^(['\"])(.*)\\1\$/", $vpart, $matches)) {
			$fragmentValues[] = $matches[2];
		// PHP file -> function invocation
		} else if( preg_match("/^(.*)->(.*)/", $vpart, $matches)) {
			if( file_exists( $matches[1])) {
				include_once( $matches[1]);
				if( function_exists( $matches[2]))
					$fragmentValues[] = $matches[2]( $upattributes);
			} else;
		// Direct mapping
 		} else if( isset( $upattributes[strtoupper($vpart)][0])) {
			$fragmentValues += $upattributes[strtoupper($vpart)];
		}
	}

	if( !count($fragmentValues))
		continue;
	$assertFragment = $key . "=" . implode( '|', $fragmentValues);
	if ($assertion != "")
		$assertion .= "," . $assertFragment;
	else
		$assertion = $assertFragment;
}


$assertion .= "@" . $config["AuthServerId"];
// Diego, poaCrypt class is not defined in latest svn crypt.file (as of 2007.10.08) :-)
// $poac = new poaCrypt();

$now = time();
$ext = $now + $config["TTL"];
$reply = $assertion . ":" . $ext . ":" . $now . ":" . $theRef;

// No class available, so we'll use a normal function call, instead
// $safe = $poac->openssl_encrypt($reply, $pKey);
$safe = openssl_encrypt($reply, $config["PrivateKey"], $config["KeyLen"]);

doLog( "Reply:$reply");

if (strpos($theURL, "?")) 
	$redirectTo = $theURL . "&";
else
	$redirectTo = $theURL . "?";


if (isset($_REQUEST["ACTION"])) {
	$redirectTo .= "ACTION=CHECKED" . "&" . "DATA=" . urlencode($safe);
	doLog("GPoA response to " . $theURL . ": " . $reply . "SAFE: " . $safe);
}
else {
	$redirectTo .= "AS=" . $config["AuthServerId"] . "&ACTION=CHECKED" . "&" . "DATA=" . urlencode($safe);
	doLog("AS response to " . $theURL . ": " . $reply);
}

doLog( "Redirecting to:$redirectTo"); 
header("Location: $redirectTo");
exit;

function get_config() {
	global $cfgFile, $config, $config_params;
	$errmsg = "";

	if( !isset( $cfgFile)) {
		$path_parts = pathinfo( $_SERVER["SCRIPT_FILENAME"]);
		// The "filename" element is only present in PHP >=5.2
		$cfgFile = $path_parts["dirname"] . "/" . 
			basename( $_SERVER["SCRIPT_FILENAME"],'.'.$path_parts['extension']) . ".cfg.php";
	}

	if( !file_exists( $cfgFile))
		error( "The config file does not exist:$cfgFile");

	require_once( $cfgFile);

	// Sanity check: All mandatory parameters must be present
	foreach( $config_params as $param_name => $mandatory) {
		if( $mandatory && !isset( $config[$param_name]))
			$missing[] = $param_name;
	}
	if( isset( $missing) && count( $missing))
		$errmsg .= " Missing mandatory parameters:" . implode( ',', $missing);

	// Sanity check: Detect unknown parameters
	foreach( $config as $name => $value) {
		if( $name == "PoA")
			continue;
		if( !array_key_exists( $name, $config_params))
			$unknown[] = $name;
	}
	if( isset( $unknown) && count( $unknown))
		$errmsg .= " Unknown parameters:" . implode( ',', $unknown);

	// Detect problems as soon as possible
	if( !openssl_get_privatekey( $config["PrivateKey"]))
		$errmsg .= " Cannot read PrivateKey:" . $config["PrivateKey"];

	if( $errmsg)
		error( "Fatal error reading config file ($cfgFile):$errmsg");
}

// When we know the PoA, make its settings override the global ones,
// and set attribute mapping
function get_config_poa( $poa) {
	global $config, $attr_mapping;
	
	// Override and/or set config. params
	if( isset( $config['PoA'][$poa]['config'])) {
		foreach( $config['PoA'][$poa]['config'] as $k => $v)
			$config[$k] = $v;
	}

	// Attribute mapping
	// TODO: Make sure there is a attr_mapping key
	$attr_mapping = $config['PoA'][$poa]['attr_mapping'];
}

function error($msg) {
	doLog($msg);
	header("HTTP/1.0 500 Server error: $msg");
	exit();
}

function debug( $msg) {
	global $config;
	if( $config["Debug"])
		doLog( $msg);
}

function doLog($msg) {
	global $config;
	if( !isset( $config["LogFile"])) {  // Before we have read config, and so LogFile
		error_log($msg);
		return;
	}
	if ($config["LogFile"] != "/dev/null") {
		$emsg = date("d-M-Y H:i:s") . ", " . $config["AuthServerId"] . ": " . $msg . "\n";
		error_log($emsg, 3, $config["LogFile"]);
	}
}
?>
