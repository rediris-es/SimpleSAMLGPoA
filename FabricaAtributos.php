<?php

function gen_sPUC( $upattributes) {
	// CAMBIAR por el sHO de la institución
	$domain = "univ.es";
	$pref = "urn:mace:terena.org:schac:personalUniqueCode:es:rediris:sir:mbid:{sha1}";
	$uid = "";
	if( $upattributes["UID"]) 
		$uid = $upattributes["UID"][0];
	$sha1mail = sha1( $uid . "@" . $domain); 
	return( $pref . $sha1mail);
}

function gen_ePTI( $upattributes) {
	// CAMBIAR por el sHO de la institución
	$domain = "univ.es";
	$uid      = "";
	if( $upattributes["UID"]) 
		$uid = $upattributes["UID"][0];
	return( sha1( $uid . $domain));
}

function gen_ePTI_old( $upattributes) {
	$papiopoa = "";
	$uid      = "";
	if( $_REQUEST["PAPIOPOA"])
		$papiopoa = $_REQUEST["PAPIOPOA"];
	if( $upattributes["UID"]) 
		$uid = $upattributes["UID"][0];
	return( md5( $uid . ":" . $papiopoa));
}

?>
